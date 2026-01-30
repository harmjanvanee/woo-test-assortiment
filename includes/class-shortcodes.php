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
        add_shortcode('test_assortiment_grid', array($this, 'render_assortiment_grid'));
    }

    /**
     * [test_assortiment_grid]
     */
    public function render_assortiment_grid()
    {
        $category_slug = get_option('wta_assortiment_category');
        if (!$category_slug) {
            return current_user_can('manage_options') ? '<p>' . __('Selecteer een categorie in de instellingen.', 'woo-test-assortiment') . '</p>' : '';
        }

        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => $category_slug,
                ),
            ),
        );

        $products = new WP_Query($args);

        if (!$products->have_posts()) {
            return '<p>' . __('Geen producten gevonden in deze categorie.', 'woo-test-assortiment') . '</p>';
        }

        ob_start();
        ?>
        <div class="wta-assortiment-container">
            <div class="wta-product-grid">
                <?php
                while ($products->have_posts()):
                    $products->the_post();
                    $product = wc_get_product(get_the_ID());
                    $test_variant_id = WTA_Product_Helper::get_instance()->get_test_variant_id($product->get_id());

                    if (!$test_variant_id)
                        continue;

                    $test_variant = wc_get_product($test_variant_id);
                    $price = $test_variant->get_price();
                    $image = wp_get_attachment_image_src($product->get_image_id(), 'medium');
                    $image_url = $image ? $image[0] : wc_placeholder_img_src();
                    $in_stock = $test_variant->is_in_stock();
                    ?>
                    <div class="wta-product-card" data-product-id="<?php echo esc_attr($product->get_id()); ?>"
                        data-variant-id="<?php echo esc_attr($test_variant_id); ?>" data-price="<?php echo esc_attr($price); ?>">
                        <a href="<?php the_permalink(); ?>" class="wta-product-link" target="_blank">
                            <div class="wta-product-image" style="background-image: url('<?php echo esc_url($image_url); ?>');">
                                <div class="wta-image-overlay">
                                    <span><?php _e('productdetails', 'woo-test-assortiment'); ?></span>
                                </div>
                            </div>
                        </a>

                        <div class="wta-stock-indicator <?php echo $in_stock ? 'in-stock' : 'out-of-stock'; ?>">
                            <span
                                class="wta-stock-text"><?php echo $in_stock ? __('Op voorraad', 'woo-test-assortiment') : __('Niet op voorraad', 'woo-test-assortiment'); ?></span>
                        </div>

                        <h3 class="wta-product-title"><?php the_title(); ?></h3>
                        <div class="wta-product-variant-name"><?php echo esc_html($test_variant->get_name()); ?></div>
                        <div class="wta-product-price"><?php echo wc_price($price); ?></div>

                        <button class="wta-toggle-select-button" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                            <span class="wta-btn-text-add"><?php _e('Toevoegen', 'woo-test-assortiment'); ?></span>
                            <span class="wta-btn-text-added"><?php _e('Toegevoegd', 'woo-test-assortiment'); ?></span>
                        </button>
                    </div>
                <?php endwhile;
                wp_reset_postdata(); ?>
            </div>

            <div class="wta-sticky-bar">
                <div class="wta-sticky-bar-content">
                    <div class="wta-totals">
                        <span class="wta-total-count">0</span> <?php _e('producten geselecteerd', 'woo-test-assortiment'); ?>
                        <div class="wta-total-price">€ 0,00</div>
                    </div>
                    <button class="wta-bulk-add-button button button-filled-hookers-green">
                        <span class="wta-loader"></span>
                        <span class="wta-bulk-btn-text"><?php _e('In winkelwagen', 'woo-test-assortiment'); ?></span>
                    </button>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
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
