<?php
/**
 * Plugin Name: WooCommerce Test Assortiment
 * Description: Hiermee kunnen klanten een test-variant van een product toevoegen aan hun winkelwagen en ontvangen ze een kortingscode.
 * Version: 1.4.1
 * Author: Antigravity
 * Text Domain: woo-test-assortiment
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 */

if (!defined('ABSPATH')) {
	exit;
}

// Define constants
define('WTA_VERSION', '1.4.1');
define('WTA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WTA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WTA_CSS_URL', WTA_PLUGIN_URL . 'assets/css/');
define('WTA_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
final class WTA_Plugin
{

	/**
	 * Instance of this class
	 * @var WTA_Plugin
	 */
	private static $instance = null;

	/**
	 * Get instance of this class
	 */
	public static function get_instance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct()
	{
		$this->autoloader();
		$this->init();
	}

	/**
	 * Simple PSR-4ish autoloader
	 */
	private function autoloader()
	{
		spl_autoload_register(function ($class) {
			$prefix = 'WTA_';
			if (strpos($class, $prefix) !== 0) {
				return;
			}

			$relative_class = substr($class, strlen($prefix));
			$file = WTA_PLUGIN_DIR . 'includes/class-' . strtolower(str_replace('_', '-', $relative_class)) . '.php';

			if (file_exists($file)) {
				require_once $file;
			}
		});
	}

	/**
	 * Initialize plugin
	 */
	private function init()
	{
		// Initialize components
		if (is_admin()) {
			WTA_Settings::get_instance();
			WTA_Admin_Product::get_instance();
		}

		WTA_Product_Helper::get_instance();
		WTA_Cart_Manager::get_instance();
		WTA_Coupon_Manager::get_instance();
		WTA_Shortcodes::get_instance();

		// AJAX hooks
		add_action('wp_ajax_wta_add_test_variant', array('WTA_Cart_Manager', 'ajax_add_test_variant'));
		add_action('wp_ajax_nopriv_wta_add_test_variant', array('WTA_Cart_Manager', 'ajax_add_test_variant'));
		add_action('wp_ajax_wta_bulk_add_test_variants', array('WTA_Cart_Manager', 'ajax_bulk_add_test_variants'));
		add_action('wp_ajax_nopriv_wta_bulk_add_test_variants', array('WTA_Cart_Manager', 'ajax_bulk_add_test_variants'));

		// Elementor initialization
		add_action('elementor/widgets/register', array($this, 'register_elementor_widgets'));

		// Frontend scripts
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
	}

	/**
	 * Register Elementor widgets
	 */
	public function register_elementor_widgets($widgets_manager)
	{
		require_once WTA_PLUGIN_DIR . 'includes/class-elementor-widget.php';
		$widgets_manager->register(new \WTA_Elementor_Widget());
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_style('wta-frontend', WTA_CSS_URL . 'frontend.css', array(), WTA_VERSION);
		wp_enqueue_script('wta-frontend', WTA_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), WTA_VERSION, true);
		wp_localize_script('wta-frontend', 'wta_vars', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('wta_nonce'),
		));
	}
}

/**
 * Initialize Plugin
 */
function WTA()
{
	return WTA_Plugin::get_instance();
}

add_action('plugins_loaded', 'WTA');
