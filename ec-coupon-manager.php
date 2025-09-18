<?php
/**
 * Plugin Name: EasyCommerce Coupon Manager
 * Plugin URI: https://sadekurrahman.com
 * Description: A simple plugin to manage EasyCommerce coupons with list table and API functionality
 * Version: 1.0.0
 * Author: Sadekur Rahman
 * License: GPL v2 or later
 * Requires Plugins: easycommerce
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
require_once __DIR__ . '/vendor/autoload.php';

final class EasyCommerceCouponManager {

    /**
	 * Plugin version
	 *
	 * @var string
	 */
	const version = '1.0.0';
    
    public function __construct() {
        $this->define_constants();
		add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );
    }

    public static function init() {
        static $instance = null;
        if ( $instance === null ) {
            $instance = new self();
        }
        return $instance;
    }

    /**
	 * Define the required plugin constants
	 *
	 * @return void
	 */
	public function define_constants() {
		define( 'ECCM_PLUGIN_VERSION', self::version );
		define( 'ECCM_PLUGIN_FILE', __FILE__ );
		define( 'ECCM_PLUGIN_PATH', plugin_dir_path(__FILE__) );
		define( 'ECCM_PLUGIN_URL', plugin_dir_url(__FILE__) );
		define( 'ECCM_ASSETS', ECCM_PLUGIN_URL . 'assets' );
	}

    /**
	 * Initialize the plugin
	 *
	 * @return void
	 */
	public function init_plugin() {
        // new EC\CouponManager\Admin\Menu();
        new EC\CouponManager\Admin\Assets();
        new EC\CouponManager\REST();
        // if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		// 	new EC\CouponManager\Ajax();
		// }
        if ( is_admin() ) {
		    new EC\CouponManager\Admin();
		}
    }
}
function ec_coupon_manager() {
    return EasyCommerceCouponManager::init();
}
ec_coupon_manager();