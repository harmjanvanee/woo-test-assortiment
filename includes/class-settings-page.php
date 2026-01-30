<?php

if (!defined('ABSPATH')) {
    exit;
}

class WTA_Settings_Page extends WC_Settings_Page
{

    public function __construct()
    {
        $this->id = 'test_assortiment';
        $this->label = __('Test Assortiment', 'woo-test-assortiment');

        parent::__construct();
    }

    public function get_sections()
    {
        return array(
            '' => __('Algemeen', 'woo-test-assortiment'),
        );
    }

    public function get_settings()
    {
        $settings = array(
            array(
                'title' => __('Identificatie Test-variant', 'woo-test-assortiment'),
                'type' => 'title',
                'id' => 'wta_id_section',
            ),
            array(
                'title' => __('Methode', 'woo-test-assortiment'),
                'id' => 'wta_id_method',
                'type' => 'select',
                'default' => 'meta',
                'options' => array(
                    'attribute' => __('Variatie Attribuut (test_variant=yes)', 'woo-test-assortiment'),
                    'meta' => __('Variatie Meta Key (_is_test_variant=yes)', 'woo-test-assortiment'),
                    'fallback' => __('Fallback (Kleinste waarde van attribuut)', 'woo-test-assortiment'),
                ),
            ),
            array(
                'title' => __('Attribute Key (voor methode A)', 'woo-test-assortiment'),
                'id' => 'wta_attribute_key',
                'type' => 'text',
                'default' => 'pa_test-variant',
            ),
            array(
                'title' => __('Attribute Value (voor methode A)', 'woo-test-assortiment'),
                'id' => 'wta_attribute_value',
                'type' => 'text',
                'default' => 'yes',
            ),
            array(
                'title' => __('Meta Key (voor methode B)', 'woo-test-assortiment'),
                'id' => 'wta_meta_key',
                'type' => 'text',
                'default' => '_is_test_variant',
            ),
            array(
                'title' => __('Fallback Attribute Key (voor methode C)', 'woo-test-assortiment'),
                'id' => 'wta_fallback_key',
                'type' => 'text',
                'default' => 'pa_inhoud-ml',
            ),
            array(
                'title' => __('Assortiment Categorie', 'woo-test-assortiment'),
                'desc' => __('Kies de categorie die getoond moet worden in het [test_assortiment_grid].', 'woo-test-assortiment'),
                'id' => 'wta_assortiment_category',
                'type' => 'select',
                'options' => $this->get_category_options(),
            ),
            array(
                'title' => __('Probeerbox Hoofdproduct', 'woo-test-assortiment'),
                'desc' => __('Kies het product dat als ouder dient voor de geselecteerde items in de winkelwagen.', 'woo-test-assortiment'),
                'id' => 'wta_probeerbox_id',
                'type' => 'select',
                'options' => $this->get_product_options(),
            ),
            array(
                'type' => 'sectionend',
                'id' => 'wta_id_section',
            ),
            array(
                'title' => __('Winkelwagen Gedrag', 'woo-test-assortiment'),
                'type' => 'title',
                'id' => 'wta_cart_section',
            ),
            array(
                'title' => __('Gedrag bij dubbele toevoeging', 'woo-test-assortiment'),
                'id' => 'wta_cart_behavior',
                'type' => 'select',
                'default' => 'block',
                'options' => array(
                    'block' => __('Blokkeren met melding', 'woo-test-assortiment'),
                    'replace' => __('Vervangen bestaand item', 'woo-test-assortiment'),
                ),
            ),
            array(
                'title' => __('Foutmelding bij blokkeren', 'woo-test-assortiment'),
                'id' => 'wta_error_message',
                'type' => 'text',
                'default' => __('Je kunt per product maar 1 testverpakking kiezen.', 'woo-test-assortiment'),
            ),
            array(
                'type' => 'sectionend',
                'id' => 'wta_cart_section',
            ),
            array(
                'title' => __('Coupon Instellingen', 'woo-test-assortiment'),
                'type' => 'title',
                'id' => 'wta_coupon_section',
            ),
            array(
                'title' => __('Trigger Status', 'woo-test-assortiment'),
                'id' => 'wta_coupon_trigger',
                'type' => 'select',
                'default' => 'processing',
                'options' => array(
                    'processing' => 'Processing',
                    'completed' => 'Completed',
                ),
            ),
            array(
                'title' => __('Bedrag Berekening', 'woo-test-assortiment'),
                'id' => 'wta_coupon_calc_base',
                'type' => 'select',
                'default' => 'total',
                'options' => array(
                    'subtotal' => __('Subtotaal (excl. BTW)', 'woo-test-assortiment'),
                    'total' => __('Totaal (incl. BTW)', 'woo-test-assortiment'),
                ),
            ),
            array(
                'title' => __('Geldigheid (dagen)', 'woo-test-assortiment'),
                'id' => 'wta_coupon_expiry',
                'type' => 'number',
                'default' => 30,
            ),
            array(
                'title' => __('Individueel gebruik?', 'woo-test-assortiment'),
                'id' => 'wta_coupon_individual',
                'type' => 'checkbox',
                'default' => 'yes',
            ),
            array(
                'title' => __('Code Prefix', 'woo-test-assortiment'),
                'id' => 'wta_coupon_prefix',
                'type' => 'text',
                'default' => 'TEST-',
            ),
            array(
                'title' => __('Debug Mode?', 'woo-test-assortiment'),
                'id' => 'wta_debug',
                'type' => 'checkbox',
                'default' => 'no',
            ),
            array(
                'type' => 'sectionend',
                'id' => 'wta_coupon_section',
            ),
        );

        return apply_filters('woocommerce_get_settings_' . $this->id, $settings);
    }

    /**
     * Get product categories for select options
     */
    private function get_category_options()
    {
        $options = array('' => __('Kies een categorie', 'woo-test-assortiment'));
        $categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        ));

        if (!is_wp_error($categories) && !empty($categories)) {
            foreach ($categories as $category) {
                $options[$category->slug] = $category->name;
            }
        }

        return $options;
    }

    /**
     * Get products for select options
     */
    private function get_product_options()
    {
        $options = array('' => __('Kies een product', 'woo-test-assortiment'));
        $products = wc_get_products(array(
            'limit' => -1,
            'status' => 'publish',
        ));

        foreach ($products as $product) {
            $options[$product->get_id()] = $product->get_name();
        }

        return $options;
    }
}

return new WTA_Settings_Page();
