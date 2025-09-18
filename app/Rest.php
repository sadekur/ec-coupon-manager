<?php 
namespace EC\CouponManager;
defined( 'ABSPATH' ) || exit;
use EC\CouponManager\Classes\Trait\Hook;
class REST {
    use Hook;
    public function __construct() {
        // Register the REST API endpoints on the rest_api_init hook.
        $this->action( 'rest_api_init', array( $this, 'register_routes' ) );
    }
    public function register_routes() {
        register_rest_route( 'eccm/v1', '/coupons', array(
            'methods'  => \WP_REST_Server::CREATABLE, // Use CREATABLE for POST requests
            'callback' => array( $this, 'create_coupon_api' ),
            'permission_callback' => array( $this, 'api_permission_check' ),
            'args' => array(
                'name' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'code' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'discount_type' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'amount' => array(
                    'required' => true,
                    'validate_callback' => function($value, $request, $param) {
                        return is_numeric( $value );
                    },
                ),
                'active' => array(
                    'required' => true,
                    'validate_callback' => function($value, $request, $param) {
                        return is_numeric( $value ) && ($value == 0 || $value == 1);
                    },
                ),
            ),
        ) );
        register_rest_route( 'eccm/v1', '/coupons/(?P<id>\d+)', array(
            'methods'  => \WP_REST_Server::DELETABLE, // Use DELETABLE for DELETE requests
            'callback' => array( $this, 'delete_coupon_api' ),
            'permission_callback' => array( $this, 'api_permission_check' ),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($value, $request, $param) {
                        return is_numeric( $value );
                    }
                ),
            ),
        ) );
    }
    public function api_permission_check() {
        // Check if the current user can manage options
        return current_user_can( 'manage_options' );
    }
    public function create_coupon_api( \WP_REST_Request $request ) {
        global $wpdb;
        $name = $request->get_param( 'name' );
        $code = $request->get_param( 'code' );
        $discount_type = $request->get_param( 'discount_type' );
        $amount = $request->get_param( 'amount' );
        $active = $request->get_param( 'active' );
        // Check if coupon code already exists
        $existing = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}ec_coupons WHERE code = %s",
            $code
        ) );
        if ( $existing ) {
            return new \WP_Error( 'coupon_exists', 'Coupon code already exists!', array( 'status' => 409 ) );
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
            array( '%s', '%s', '%s', '%f', '%d', '%d' )
        );
        if ( $result === false ) {
            return new \WP_Error( 'db_insert_error', 'Failed to create coupon.', array( 'status' => 500 ) );
        }
        return new \WP_REST_Response( array( 'message' => 'Coupon created successfully!' ), 201 );
    }
    public function delete_coupon_api( \WP_REST_Request $request ) {
        global $wpdb;
        $coupon_id = $request->get_param( 'id' );
        $result = $wpdb->delete(
            $wpdb->prefix . 'ec_coupons',
            array( 'id' => $coupon_id ),
            array( '%d' )
        );
        if ( $result === false ) {
            return new \WP_Error( 'db_delete_error', 'Failed to delete coupon.', array( 'status' => 500 ) );
        }
        if ( $result === 0 ) {
            return new \WP_Error( 'not_found', 'Coupon not found.', array( 'status' => 404 ) );
        }
        return new \WP_REST_Response( array( 'message' => 'Coupon deleted successfully!' ), 200 );
    }
}