<?php
namespace EC\CouponManager\Admin;

defined( 'ABSPATH' ) || exit;

use EC\CouponManager\Admin\Menu;

class Admin {
    public function __construct() {
        $menu = new Menu();
        $menu->register();
    }
}