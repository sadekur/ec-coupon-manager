<?php
namespace EC\CouponManager\Admin;
use EC\CouponManager\Admin\Coupons as ECCM_Coupons;
use EC\CouponManager\Classes\Trait\Hook;

defined( 'ABSPATH' ) || exit;

class Menu {
    use Hook;

    public function __construct() {
        $this->action( 'admin_menu', array( $this, 'add_admin_menu' ) );
    }

    public function add_admin_menu() {
        add_menu_page(
            'EasyCommerce Coupons',
            'EC Coupons', 
            'manage_options',
            'ec-coupon-manager',
            array( $this, 'admin_page' ),
            'dashicons-tag',
            4
        );
    }

    public function admin_page() {
        // Instantiate the list table
        $list_table = new ECCM_Coupons();
        $list_table->prepare_items();
        ?>
        <div class="wrap">
            <h1>EasyCommerce Coupons</h1>

            <div class="eccm-add-coupon-form" style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccd0d4;">
                <h2>Add New Coupon</h2>
                <form id="eccm-add-coupon-form">
                    <table class="form-table">
                        <tr>
                            <th><label for="coupon_name">Coupon Name</label></th>
                            <td><input type="text" id="coupon_name" name="name" required class="regular-text"></td>
                        </tr>
                        <tr>
                            <th><label for="coupon_code">Coupon Code</label></th>
                            <td><input type="text" id="coupon_code" name="code" required class="regular-text"></td>
                        </tr>
                        <tr>
                            <th><label for="discount_type">Discount Type</label></th>
                            <td>
                                <select id="discount_type" name="discount_type" required>
                                    <option value="percentage">Percentage</option>
                                    <option value="fixed">Fixed Amount</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="amount">Amount</label></th>
                            <td><input type="number" id="amount" name="amount" step="0.01" required class="regular-text"></td>
                        </tr>
                        <tr>
                            <th><label for="active">Status</label></th>
                            <td>
                                <select id="active" name="active">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <?php wp_nonce_field('eccm_nonce', 'eccm_nonce'); ?>
                    <p class="submit">
                        <input type="submit" class="button-primary" value="Add Coupon">
                    </p>
                </form>
            </div>

            <form method="post">
                <?php
                $list_table->display();
                ?>
            </form>
        </div>

        <div id="eccm-messages"></div>
        <?php
    }
}