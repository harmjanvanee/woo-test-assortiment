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
        add_filter('woocommerce_add_to_cart_validation', array($this, 'validate_add_to_cart'), 10, 3);
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

        $behavior = get_option('wta_cart_behavior', 'block');
        $exists = self::check_if_parent_in_cart($product_id);

        if ($exists) {
            if ($behavior === 'block') {
                wp_send_json_error(array('message' => get_option('wta_error_message', __('Je kunt per product maar 1 testverpakking kiezen.', 'woo-test-assortiment'))));
            } else {
                // Replace: Remove existing parent products first
                self::remove_parent_from_cart($product_id);
            }
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
     * Enforce rule when adding normally (fallback if they try to bypass AJAX)
     */
    public function validate_add_to_cart($passed, $product_id, $quantity)
    {
        $behavior = get_option('wta_cart_behavior', 'block');
        if ($behavior !== 'block') {
            return $passed;
        }

        $exists = self::check_if_parent_in_cart($product_id);
        if ($exists) {
            wc_add_notice(get_option('wta_error_message', __('Je kunt per product maar 1 testverpakking kiezen.', 'woo-test-assortiment')), 'error');
            return false;
        }

        return $passed;
    }

    /**
     * Helper: Check if any variation of this parent is in cart
     */
    public static function check_if_parent_in_cart($parent_id)
    {
        foreach (WC()->cart->get_cart() as $cart_item_key => $values) {
            if ($values['product_id'] == $parent_id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Helper: Remove all variations of this parent from cart
     */
    public static function remove_parent_from_cart($parent_id)
    {
        foreach (WC()->cart->get_cart() as $cart_item_key => $values) {
            if ($values['product_id'] == $parent_id) {
                WC()->cart->remove_cart_item($cart_item_key);
            }
        }
    }
}
