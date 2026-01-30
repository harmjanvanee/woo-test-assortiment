<?php

if (!defined('ABSPATH')) {
    exit;
}

class WTA_Shortcodes
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
        add_shortcode('test_add_button', array($this, 'render_add_button'));
        add_shortcode('test_coupon_from_order', array($this, 'render_coupon_from_order'));
    }

    /**
     * [test_add_button product_id="123" label="Probeer"]
     */
    public function render_add_button($atts)
    {
        $atts = shortcode_atts(array(
            'product_id' => 0,
            'label' => __('Probeer', 'woo-test-assortiment'),
            'class' => '',
        ), $atts, 'test_add_button');

        $product_id = absint($atts['product_id']);

        // If no ID provided, try to detect from context (WooCommerce loop or global post)
        if (!$product_id) {
            global $product, $post;
            if (is_object($product) && method_exists($product, 'get_id')) {
                $product_id = $product->get_id();
            } elseif (isset($post->post_type) && $post->post_type === 'product') {
                $product_id = $post->ID;
            } else {
                $product_id = get_the_ID();
            }
        }

        $product = wc_get_product($product_id);

        if (!$product) {
            return current_user_can('manage_options') ? '<!-- WTA Debug: No product found for ID ' . $product_id . ' -->' : '';
        }

        if (!$product->is_type('variable')) {
            return current_user_can('manage_options') ? '<!-- WTA Debug: Product ' . $product_id . ' (' . $product->get_type() . ') is not variable -->' : '';
        }

        // Check if test variant even exists
        $test_id = WTA_Product_Helper::get_instance()->get_test_variant_id($product_id);
        if (!$test_id) {
            return current_user_can('manage_options') ? '<!-- WTA Debug: No test variant found for Product ' . $product_id . ' -->' : '';
        }

        ob_start();
        ?>
        <button class="wta-add-test-button button button-filled-hookers-green <?php echo esc_attr($atts['class']); ?>"
            data-product-id="<?php echo esc_attr($product_id); ?>">
            <?php echo esc_html($atts['label']); ?>
        </button>
        <?php
        return ob_get_clean();
    }

    /**
     * [test_coupon_from_order] - Works on thank you page or where global $wp exists
     */
    public function render_coupon_from_order()
    {
        global $wp;

        $order_id = 0;
        if (isset($wp->query_vars['order-received'])) {
            $order_id = $wp->query_vars['order-received'];
        } elseif (isset($_GET['order_id'])) {
            $order_id = absint($_GET['order_id']);
        }

        if (!$order_id)
            return '';

        $order = wc_get_order($order_id);
        if (!$order)
            return '';

        $code = $order->get_meta('_wta_coupon_code');
        $amount = $order->get_meta('_wta_coupon_amount');

        if (!$code)
            return '';

        return '<div class="wta-shortcode-coupon">
			<span class="wta-label">' . __('Kortingscode:', 'woo-test-assortiment') . '</span>
			<strong class="wta-code">' . esc_html($code) . '</strong>
			<span class="wta-amount">(' . wc_price($amount) . ')</span>
		</div>';
    }
}
