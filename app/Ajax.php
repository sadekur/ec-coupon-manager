<?php 
namespace EC\CouponManager;

defined( 'ABSPATH' ) || exit;

use EC\CouponManager\Classes\Trait\Hook;

class Ajax {
    use Hook;
    public function __construct() {
        $this->action( 'wp_ajax_eccm_create_coupon', array( $this, 'create_coupon_ajax' ) );
        $this->action( 'wp_ajax_eccm_delete_coupon', array( $this, 'delete_coupon_ajax' ) );
    }

    public function create_coupon_ajax() {
        check_ajax_referer('eccm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        global $wpdb;
        
        $name = sanitize_text_field($_POST['name']);
        $code = sanitize_text_field($_POST['code']);
        $discount_type = sanitize_text_field($_POST['discount_type']);
        $amount = floatval($_POST['amount']);
        $active = intval($_POST['active']);
        
        // Check if coupon code already exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}ec_coupons WHERE code = %s",
            $code
        ));
        
        if ($existing) {
            wp_send_json_error('Coupon code already exists!');
        }
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'ec_coupons',
            array(
                'name' => $name,
                'code' => $code,
                'discount_type' => $discount_type,
                'amount' => $amount,
                'active' => $active,
                'status' => 1
            ),
            array('%s', '%s', '%s', '%f', '%d', '%d')
        );
        
        if ($result === false) {
            wp_send_json_error('Failed to create coupon: ' . $wpdb->last_error);
        }
        
        wp_send_json_success('Coupon created successfully!');
    }
    
    public function delete_coupon_ajax() {
        check_ajax_referer('eccm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        global $wpdb;
        
        $coupon_id = intval($_POST['coupon_id']);
        
        if (!$coupon_id) {
            wp_send_json_error('Invalid coupon ID');
        }
        
        $result = $wpdb->delete(
            $wpdb->prefix . 'ec_coupons',
            array('id' => $coupon_id),
            array('%d')
        );
        
        if ($result === false) {
            wp_send_json_error('Failed to delete coupon');
        }
        
        wp_send_json_success('Coupon deleted successfully!');
    }
}