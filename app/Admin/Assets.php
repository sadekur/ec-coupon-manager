<?php 
namespace EC\CouponManager\Admin;

defined( 'ABSPATH' ) || exit;

use EC\CouponManager\Classes\Trait\Hook;
class Assets {
    use Hook;
    public function __construct() {
        $this->action( 'admin_enqueue_scripts', [ $this, 'eccm_enqueue_admin_assets' ] );
    }

    public function eccm_enqueue_admin_assets() {
        wp_enqueue_script(
            'eccm-admin-script',
            ECCM_ASSETS . '/js/admin.js',
            ['jquery'],
            filemtime( ECCM_PLUGIN_PATH . 'assets/js/admin.js' ),
            true
        );

        wp_localize_script('eccm-admin-script', 'ECCM', [
            'nonce'    => wp_create_nonce( 'wp_rest' ), // Use a general REST nonce
            'adminurl' => admin_url(),
            'ajaxurl'  => admin_url( 'admin-ajax.php' ),
            'apiurl'   => untrailingslashit( rest_url( 'eccm/v1' ) ), // <-- Corrected namespace
            'error'    => __( 'Something went wrong', 'ec-coupon-manager' ),
        ]);

        wp_enqueue_style(
            'eccm-admin-style',
            ECCM_ASSETS . '/css/admin.css',
            [],
            filemtime( ECCM_PLUGIN_PATH . 'assets/css/admin.css' )
        );

        wp_enqueue_style(
            'jquery-ui',
            'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css',
            [],
            null
        );
    }
}