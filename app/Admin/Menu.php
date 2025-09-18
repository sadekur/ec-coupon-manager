<?php
namespace EC\CouponManager\Admin;
use EC\CouponManager\Classes\Trait\Hook;

defined( 'ABSPATH' ) || exit;

class Menu {
    use Hook;
    public function __construct() {
        $this->action('admin_menu', array( $this, 'add_admin_menu' ));
    }

    public function add_admin_menu() {
        add_submenu_page(
            'options-general.php',
            'EasyCommerce Coupons',
            'EC Coupons',
            'manage_options',
            'ec-coupon-manager',
            array( $this, 'admin_page' )
        );
    }
}