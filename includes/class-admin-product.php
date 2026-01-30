<?php

if (!defined('ABSPATH')) {
    exit;
}

class WTA_Admin_Product
{

    private static $instance = null;

    public static function get_instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('woocommerce_variation_options_pricing', array($this, 'add_variation_test_checkbox'), 10, 3);
        add_action('woocommerce_save_product_variation', array($this, 'save_variation_test_checkbox'), 10, 2);
    }

    /**
     * Add checkbox to variation edit panel
     */
    public function add_variation_test_checkbox($loop, $variation_data, $variation)
    {
        woocommerce_wp_checkbox(array(
            'id' => "_is_test_variant{$loop}",
            'name' => "_is_test_variant[{$loop}]",
            'class' => 'checkbox',
            'label' => __('Test-variant (kleinste)', 'woo-test-assortiment'),
            'value' => get_post_meta($variation->ID, '_is_test_variant', true),
            'wrapper_class' => 'form-row form-row-full',
        ));

        // Add a hidden field to store the variation ID to map the loop index correctly during save if needed
        echo '<input type="hidden" name="wta_variation_ids[' . $loop . ']" value="' . esc_attr($variation->ID) . '" />';
    }

    /**
     * Save meta value and ensure only one per parent
     */
    public function save_variation_test_checkbox($variation_id, $i)
    {
        $is_test = isset($_POST['_is_test_variant'][$i]) ? 'yes' : '';

        if ('yes' === $is_test) {
            // Get parent product ID
            $variation = wc_get_product($variation_id);
            $parent_id = $variation->get_parent_id();

            if ($parent_id) {
                $parent = wc_get_product($parent_id);
                $children = $parent->get_children();

                // Clear _is_test_variant for all other variations of this parent
                foreach ($children as $child_id) {
                    if ($child_id != $variation_id) {
                        delete_post_meta($child_id, '_is_test_variant');
                    }
                }
            }

            update_post_meta($variation_id, '_is_test_variant', 'yes');
        } else {
            delete_post_meta($variation_id, '_is_test_variant');
        }
    }
}
