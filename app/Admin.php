<?php
namespace EC\CouponManager;

defined( 'ABSPATH' ) || exit;

use EC\CouponManager\Admin\Menu;
use EC\CouponManager\Admin\Assets;
use EC\CouponManager\Admin\Coupons;

class Admin {
    public function __construct() {
        $assets = new Assets();
        $menu   = new Menu();
        $list   = new Coupons();
    }
}