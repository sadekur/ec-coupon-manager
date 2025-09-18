<?php 
namespace EC\CouponManager\Admin\Assets;

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
            THRAIL_COMMERCE_ASSETS . '/js/admin.js',
            ['jquery'],
            filemtime(THRAIL_COMMERCE_PATH . 'assets/js/admin.js'),
            true
        );

        wp_localize_script('thrail-commerce-admin-script', 'THRAILCOMMERCE', [
            'nonce'    => wp_create_nonce( 'wp_rest' ),
            'adminurl' => admin_url(),
            'ajaxurl'  => admin_url( 'admin-ajax.php' ),
            'apiurl'   => untrailingslashit( rest_url( 'thrail/v1' ) ),
            'error'    => __( 'Something went wrong', 'thrail-commerce' ),
        ]);

        wp_enqueue_style(
            'thrail-commerce-admin-style',
            THRAIL_COMMERCE_ASSETS . '/css/admin.css',
            [],
            filemtime(THRAIL_COMMERCE_PATH . 'assets/css/admin.css')
        );

        wp_enqueue_style(
            'thrail-commerce-init-style',
            THRAIL_COMMERCE_ASSETS . '/css/init.css',
            [],
            filemtime(THRAIL_COMMERCE_PATH . 'assets/css/init.css')
        );

        wp_enqueue_style(
            'jquery-ui',
            'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css',
            [],
            null
        );

    }
}