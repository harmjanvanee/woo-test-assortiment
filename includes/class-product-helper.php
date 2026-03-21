<?php

if (!defined('ABSPATH')) {
    exit;
}

class WTA_Product_Helper
{

    private static $instance = null;
    private $cache = array();

    public static function get_instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // Private constructor
    }

    /**
     * Find the test variant ID for a given parent product
     */
    public function get_test_variant_id($product_id)
    {
        if (isset($this->cache[$product_id])) {
            return $this->cache[$product_id];
        }

        $product = wc_get_product($product_id);
        if (!$product || !$product->is_type('variable')) {
            return 0;
        }

        $method = get_option('wta_id_method', 'attribute');
        $variant_id = 0;

        switch ($method) {
            case 'attribute':
                $variant_id = $this->find_by_attribute($product);
                break;
            case 'meta':
                $variant_id = $this->find_by_meta($product);
                break;
            case 'fallback':
                $variant_id = $this->find_by_fallback($product);
                break;
        }

        $this->cache[$product_id] = $variant_id;
        return $variant_id;
    }

    private function find_by_attribute($product)
    {
        $attr_key = get_option('wta_attribute_key', 'pa_test-variant');
        $attr_value = get_option('wta_attribute_value', 'yes');
        $variations = $product->get_available_variations();

        foreach ($variations as $variation_data) {
            $variation = wc_get_product($variation_data['variation_id']);
            $attr_slug = str_replace('attribute_', '', $attr_key);

            // Check variation attributes directly
            $variation_attrs = $variation->get_attributes();
            if (isset($variation_attrs[$attr_key]) && $variation_attrs[$attr_key] === $attr_value) {
                return $variation->get_id();
            }

            // Handle case where key might be different in variation data
            if (isset($variation_data['attributes']['attribute_' . $attr_slug]) && $variation_data['attributes']['attribute_' . $attr_slug] === $attr_value) {
                return $variation->get_id();
            }
        }

        return 0;
    }

    private function find_by_meta($product)
    {
        $meta_key = get_option('wta_meta_key', '_is_test_variant');
        $variations = $product->get_children();

        foreach ($variations as $variation_id) {
            $is_test = get_post_meta($variation_id, $meta_key, true);
            if ($is_test === 'yes' || $is_test === '1') {
                return $variation_id;
            }
        }

        return 0;
    }

    private function find_by_fallback($product)
    {
        $fallback_key = get_option('wta_fallback_key', 'pa_inhoud-ml');
        $variations = $product->get_available_variations();

        $min_val = PHP_INT_MAX;
        $best_id = 0;

        foreach ($variations as $variation_data) {
            $variation = wc_get_product($variation_data['variation_id']);
            $val = $variation->get_attribute($fallback_key);

            // Try to parse numeric value
            $numeric_val = (float) preg_replace('/[^0-9.]/', '', $val);

            if ($numeric_val > 0 && $numeric_val < $min_val) {
                $min_val = $numeric_val;
                $best_id = $variation->get_id();
            }
        }

        return $best_id;
    }

    /**
     * Get all unique sub-categories from products in the main assortment category,
     * organized by their top-level parent (Honden, Katten, etc.)
     */
    public function get_assortment_subcategories($main_category_slug)
    {
        if (!$main_category_slug) {
            return array();
        }

        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => $main_category_slug,
                ),
            ),
            'fields' => 'ids',
        );

        $product_ids = get_posts($args);
        $hierarchy = array();

        if (!empty($product_ids)) {
            foreach ($product_ids as $product_id) {
                $terms = get_the_terms($product_id, 'product_cat');
                if ($terms && !is_wp_error($terms)) {
                    foreach ($terms as $term) {
                        // Skip the main category itself
                        if ($term->slug === $main_category_slug) {
                            continue;
                        }

                        // Find the top-level parent of this term
                        $parent_id = $this->get_top_level_parent_id($term->term_id);
                        $parent = get_term($parent_id, 'product_cat');

                        if (!$parent || is_wp_error($parent) || $parent->slug === $main_category_slug) {
                            // If this IS the top-level term (or no parent found)
                            if (!isset($hierarchy[$term->slug])) {
                                $hierarchy[$term->slug] = array(
                                    'name' => $term->name,
                                    'children' => array()
                                );
                            }
                        } else {
                            // It has a top-level parent
                            if (!isset($hierarchy[$parent->slug])) {
                                $hierarchy[$parent->slug] = array(
                                    'name' => $parent->name,
                                    'children' => array()
                                );
                            }
                            // Add this term as a child if it's not the parent itself
                            if ($term->term_id !== $parent_id) {
                                $hierarchy[$parent->slug]['children'][$term->slug] = $term->name;
                            }
                        }
                    }
                }
            }
        }

        // Sort parents and children
        ksort($hierarchy);
        foreach ($hierarchy as $slug => $data) {
            asort($hierarchy[$slug]['children']);
        }

        return $hierarchy;
    }

    /**
     * Helper to find the highest parent ID for a term
     */
    private function get_top_level_parent_id($term_id)
    {
        $parent = get_term($term_id, 'product_cat');
        if (!$parent || is_wp_error($parent)) {
            return $term_id;
        }

        while ($parent->parent != 0) {
            $next_parent = get_term($parent->parent, 'product_cat');
            if (!$next_parent || is_wp_error($next_parent)) {
                break;
            }
            $parent = $next_parent;
        }
        return $parent->term_id;
    }

    /**
     * Log debug message
     */
    public function log($message)
    {
        if (get_option('wta_debug') === 'yes') {
            $logger = wc_get_logger();
            $logger->debug($message, array('source' => 'woo-test-assortiment'));
        }
    }
}
