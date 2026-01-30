<?php

if (!defined('ABSPATH')) {
    exit;
}

class WTA_Elementor_Widget extends \Elementor\Widget_Base
{

    public function get_name()
    {
        return 'wta_add_button';
    }

    public function get_title()
    {
        return __('Test Add Button', 'woo-test-assortiment');
    }

    public function get_icon()
    {
        return 'eicon-cart-button';
    }

    public function get_categories()
    {
        return ['woocommerce-elements'];
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Content', 'woo-test-assortiment'),
            ]
        );

        $this->add_control(
            'label',
            [
                'label' => __('Label', 'woo-test-assortiment'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Probeer', 'woo-test-assortiment'),
            ]
        );

        $this->add_control(
            'product_id',
            [
                'label' => __('Product ID (leeg = huidig)', 'woo-test-assortiment'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('Laat leeg om het product van de huidige loop te gebruiken.', 'woo-test-assortiment'),
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $product_id = !empty($settings['product_id']) ? $settings['product_id'] : get_the_ID();

        echo do_shortcode(sprintf(
            '[test_add_button product_id="%s" label="%s"]',
            esc_attr($product_id),
            esc_attr($settings['label'])
        ));
    }
}
