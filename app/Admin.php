<?php
namespace EC\CouponManager\Admin;

defined( 'ABSPATH' ) || exit;

use EC\CouponManager\Admin\Menu;
use EC\CouponManager\Admin\Assets;

class Admin {
    public function __construct() {
        $menu = new Menu();
        $assets = new Assets();
    }
}