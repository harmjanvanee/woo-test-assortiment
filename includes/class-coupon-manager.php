<?php

if (!defined('ABSPATH')) {
    exit;
}

class WTA_Coupon_Manager
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
        $trigger = get_option('wta_coupon_trigger', 'processing');
        add_action('woocommerce_order_status_' . $trigger, array($this, 'generate_coupon_for_order'), 10, 1);

        // Display coupon on thank you page
        add_action('woocommerce_thankyou', array($this, 'display_coupon_on_thankyou'), 10, 1);

        // Add coupon to emails
        add_action('woocommerce_email_after_order_table', array($this, 'add_coupon_to_email'), 10, 4);
    }

    /**
     * Generate coupon based on test variants in order
     */
    public function generate_coupon_for_order($order_id)
    {
        $order = wc_get_order($order_id);
        if (!$order)
            return;

        // Check if coupon already exists for this order
        if ($order->get_meta('_wta_coupon_code'))
            return;

        $test_variants_total = 0;
        $calc_base = get_option('wta_coupon_calc_base', 'total');

        foreach ($order->get_items() as $item_id => $item) {
            $product_id = $item->get_product_id();
            $variation_id = $item->get_variation_id();

            // Verify if this is a test variant
            $expected_test_id = WTA_Product_Helper::get_instance()->get_test_variant_id($product_id);

            if ($variation_id > 0 && $variation_id == $expected_test_id) {
                if ($calc_base === 'total') {
                    $test_variants_total += $item->get_total() + $item->get_total_tax();
                } else {
                    $test_variants_total += $item->get_total();
                }
            }
        }

        if ($test_variants_total <= 0)
            return;

        $coupon_amount = $test_variants_total * 0.5;
        $coupon_code = get_option('wta_coupon_prefix', 'TEST-') . strtoupper(wp_generate_password(8, false));

        // Create WooCommerce Coupon
        $coupon = new WC_Coupon();
        $coupon->set_code($coupon_code);
        $coupon->set_amount($coupon_amount);
        $coupon->set_discount_type('fixed_cart');
        $coupon->set_description(sprintf(__('Test Assortiment Coupon voor Bestelling #%s', 'woo-test-assortiment'), $order_id));
        $coupon->set_individual_use(get_option('wta_coupon_individual') === 'yes');
        $coupon->set_usage_limit(1);

        // Expiry
        $days = get_option('wta_coupon_expiry', 30);
        if ($days > 0) {
            $coupon->set_date_expires(date('Y-m-d', strtotime("+{$days} days")));
        }

        // Email restriction
        $email = $order->get_billing_email();
        $coupon->set_email_restrictions(array($email));

        $coupon->save();

        // Save to order meta
        $order->update_meta_data('_wta_coupon_code', $coupon_code);
        $order->update_meta_data('_wta_coupon_amount', $coupon_amount);
        $order->save();

        WTA_Product_Helper::get_instance()->log("Coupon generated for order #{$order_id}: {$coupon_code} (Amount: {$coupon_amount})");
    }

    public function display_coupon_on_thankyou($order_id)
    {
        $order = wc_get_order($order_id);
        $code = $order->get_meta('_wta_coupon_code');
        $amount = $order->get_meta('_wta_coupon_amount');

        if ($code) {
            echo '<section class="wta-coupon-wrapper" style="margin-top: 2em; padding: 1.5em; border: 2px dashed #ccc; background: #f9f9f9; text-align: center;">';
            echo '<h2>' . __('Bedankt voor je test-aankoop!', 'woo-test-assortiment') . '</h2>';
            echo '<p>' . sprintf(__('Hier is je kortingscode van %s voor je volgende bestelling:', 'woo-test-assortiment'), wc_price($amount)) . '</p>';
            echo '<code style="font-size: 1.5em; font-weight: bold; background: #eee; padding: 5px 15px; border-radius: 4px;">' . esc_html($code) . '</code>';
            echo '</section>';
        }
    }

    public function add_coupon_to_email($order, $sent_to_admin, $plain_text, $email)
    {
        if ($sent_to_admin)
            return;

        $code = $order->get_meta('_wta_coupon_code');
        $amount = $order->get_meta('_wta_coupon_amount');

        if ($code) {
            if ($plain_text) {
                echo "\n" . __('KORTINGSCODE', 'woo-test-assortiment') . "\n";
                echo sprintf(__('Bedankt voor het proberen! Gebruik code %s voor %s korting op je volgende bestelling.', 'woo-test-assortiment'), $code, wc_price($amount)) . "\n";
            } else {
                echo '<div style="margin-top: 20px; padding: 20px; border: 1px solid #eee;">';
                echo '<h3 style="color: #333;">' . __('Kortingscode voor je volgende bestelling', 'woo-test-assortiment') . '</h3>';
                echo '<p>' . sprintf(__('Gebruik code <strong>%s</strong> voor <strong>%s</strong> korting op je volgende bestelling!', 'woo-test-assortiment'), $code, wc_price($amount)) . '</p>';
                echo '</div>';
            }
        }
    }
}
