<?php

if (!defined('ABSPATH')) {
    exit;
}

class WTA_Cart_Manager
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
        // Grouping filters
        add_filter('woocommerce_cart_item_class', array($this, 'add_cart_item_group_class'), 10, 3);
        add_filter('woocommerce_cart_item_name', array($this, 'add_cart_item_group_indent'), 10, 3);
        add_filter('woocommerce_cart_item_thumbnail', array($this, 'hide_parent_cart_item_thumbnail'), 10, 3);
        add_filter('woocommerce_cart_item_price', array($this, 'hide_parent_cart_item_price'), 10, 3);
        add_filter('woocommerce_cart_item_subtotal', array($this, 'hide_parent_cart_item_price'), 10, 3);
        add_filter('woocommerce_cart_item_quantity', array($this, 'handle_cart_item_quantity'), 10, 3);

        // Logic filters
        add_action('woocommerce_cart_item_removed', array($this, 'auto_remove_children_when_parent_removed'), 10, 2);
    }

    /**
     * AJAX handler for bulk adding test variants
     */
    public static function ajax_bulk_add_test_variants()
    {
        check_ajax_referer('wta_nonce', 'nonce');

        $variant_ids = isset($_POST['variant_ids']) ? array_map('absint', $_POST['variant_ids']) : array();
        $product_ids = isset($_POST['product_ids']) ? array_map('absint', $_POST['product_ids']) : array();

        if (empty($variant_ids)) {
            wp_send_json_error(array('message' => __('Geen producten geselecteerd.', 'woo-test-assortiment')));
        }

        $behavior = get_option('wta_cart_behavior', 'block');
        $probeerbox_id = get_option('wta_probeerbox_id');
        $added_count = 0;
        $parent_cart_key = '';

        // Add Probeerbox parent if selected
        if ($probeerbox_id) {
            $parent_cart_key = WC()->cart->add_to_cart($probeerbox_id, 1, 0, array(), array('wta_is_parent' => true));
        }

        foreach ($variant_ids as $index => $variant_id) {
            $parent_id = $product_ids[$index];

            // Using WC() global to add to cart with parent reference
            $cart_item_data = array();
            if ($parent_cart_key) {
                $cart_item_data['wta_parent_key'] = $parent_cart_key;
            }

            $added = WC()->cart->add_to_cart($parent_id, 1, $variant_id, array(), $cart_item_data);
            if ($added) {
                $added_count++;
            }
        }

        if ($added_count > 0) {
            $data = array(
                'message' => sprintf(__('%d producten toegevoegd!', 'woo-test-assortiment'), $added_count),
                'fragments' => apply_filters('woocommerce_add_to_cart_fragments', array()),
                'cart_hash' => WC()->cart->get_cart_hash(),
            );
            wp_send_json_success($data);
        } else {
            wp_send_json_error(array('message' => __('Kon geen producten toevoegen.', 'woo-test-assortiment')));
        }

        wp_die();
    }

    /**
     * AJAX handler for adding test variant
     */
    public static function ajax_add_test_variant()
    {
        check_ajax_referer('wta_nonce', 'nonce');

        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        if (!$product_id) {
            wp_send_json_error(array('message' => __('Ongeldig product.', 'woo-test-assortiment')));
        }

        $test_variant_id = WTA_Product_Helper::get_instance()->get_test_variant_id($product_id);
        if (!$test_variant_id) {
            wp_send_json_error(array('message' => __('Geen testverpakking gevonden voor dit product.', 'woo-test-assortiment')));
        }

        $added = WC()->cart->add_to_cart($product_id, 1, $test_variant_id);

        if ($added) {
            $data = array(
                'message' => __('Toegevoegd!', 'woo-test-assortiment'),
                'fragments' => apply_filters('woocommerce_add_to_cart_fragments', array()),
                'cart_hash' => WC()->cart->get_cart_hash(),
            );
            wp_send_json_success($data);
        } else {
            wp_send_json_error(array('message' => __('Kon product niet toevoegen.', 'woo-test-assortiment')));
        }

        wp_die();
    }


    /**
     * Add class to cart items for grouping display
     */
    public function add_cart_item_group_class($class, $cart_item, $cart_item_key)
    {
        if (isset($cart_item['wta_parent_key'])) {
            $class .= ' wta-child-item';
        }
        if (isset($cart_item['wta_is_parent'])) {
            $class .= ' wta-parent-item';
        }
        return $class;
    }

    /**
     * Add indentation to grouped items in cart
     */
    public function add_cart_item_group_indent($name, $cart_item, $cart_item_key)
    {
        if (isset($cart_item['wta_parent_key'])) {
            $name = '<span class="wta-cart-indent">↳ </span>' . $name;
        }
        return $name;
    }

    /**
     * Hide thumbnail for parent Probeerbox
     */
    public function hide_parent_cart_item_thumbnail($thumbnail, $cart_item, $cart_item_key)
    {
        if (isset($cart_item['wta_is_parent'])) {
            return '';
        }
        return $thumbnail;
    }

    /**
     * Hide price/subtotal for parent Probeerbox
     */
    public function hide_parent_cart_item_price($price, $cart_item, $cart_item_key)
    {
        if (isset($cart_item['wta_is_parent'])) {
            return '';
        }
        return $price;
    }

    /**
     * Handle quantity display/logic for parent and child items
     */
    public function handle_cart_item_quantity($product_quantity, $cart_item_key, $cart_item)
    {
        // Hide quantity for parent
        if (isset($cart_item['wta_is_parent'])) {
            return '';
        }

        // Limit quantity to 1 for children
        if (isset($cart_item['wta_parent_key'])) {
            return '1'; // Just display 1, no input field
        }

        return $product_quantity;
    }

    /**
     * Auto-remove child items when the parent Probeerbox is removed
     */
    public function auto_remove_children_when_parent_removed($cart_item_key, $cart)
    {
        $removed_item = $cart->removed_cart_contents[$cart_item_key];

        if (isset($removed_item['wta_is_parent'])) {
            // Remove all items that have this key as their parent
            foreach ($cart->cart_contents as $child_key => $values) {
                if (isset($values['wta_parent_key']) && $values['wta_parent_key'] === $cart_item_key) {
                    $cart->remove_cart_item($child_key);
                }
            }
        }
    }
}
