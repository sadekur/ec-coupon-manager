<?php 
namespace EC\CouponManager\Admin;
use EC\CouponManager\Classes\Trait\Hook;

defined( 'ABSPATH' ) || exit;

// Ensure we're in admin context and load required files
if ( is_admin() ) {
    // Load required WordPress admin files
    if ( ! class_exists( 'WP_List_Table' ) ) {
        require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
    }
    
    // Load screen functions if not already loaded
    if ( ! function_exists( 'convert_to_screen' ) ) {
        require_once ABSPATH . 'wp-admin/includes/screen.php';
    }
    
    // Load template functions if needed
    if ( ! function_exists( 'get_column_headers' ) ) {
        require_once ABSPATH . 'wp-admin/includes/template.php';
    }
}

/**
 * Custom WP_List_Table class for coupons.
 *
 * This class is now properly defined outside of the Menu class method.
 */
class Coupons extends \WP_List_Table {
    
    public function __construct() {
        // Only instantiate if we're in admin and required functions exist
        if ( ! is_admin() || ! function_exists( 'convert_to_screen' ) ) {
            return;
        }
        
        parent::__construct( array(
            'singular' => 'coupon',
            'plural' => 'coupons',
            'ajax' => false
        ));
    }
    
    public function get_columns() {
        return array(
            'cb' => '<input type="checkbox" />',
            'id' => 'ID',
            'name' => 'Name',
            'code' => 'Code',
            'discount_type' => 'Type',
            'amount' => 'Amount',
            'active' => 'Status',
            'created_at' => 'Created',
            'actions' => 'Actions'
        );
    }
    
    public function get_sortable_columns() {
        return array(
            'id'         => array( 'id', false ),
            'name'       => array( 'name', false ),
            'code'       => array('code', false),
            'created_at' => array('created_at', false)
        );
    }
    
    public function prepare_items() {
        global $wpdb;
        
        $per_page = 20;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;
        
        // Sanitize orderby parameter
        $allowed_orderby = array('id', 'name', 'code', 'created_at');
        $orderby = isset($_GET['orderby']) && in_array($_GET['orderby'], $allowed_orderby) ? $_GET['orderby'] : 'id';
        $order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';
        
        // Get total items count
        $total_items = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(id) FROM {$wpdb->prefix}ec_coupons WHERE 1 = %d",
            1
        ));
        
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page
        ));
        
        // Build the query safely
        $query = $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ec_coupons ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
            $per_page,
            $offset
        );
        
        $this->items = $wpdb->get_results($query);
        
        $this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());
    }
    
    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'id':
            case 'name':
            case 'code':
                return esc_html($item->$column_name);
            case 'discount_type':
                return esc_html(ucfirst($item->discount_type));
            case 'amount':
                return esc_html(number_format($item->amount, 2));
            case 'active':
                return $item->active ? '<span style="color: green;">Active</span>' : '<span style="color: red;">Inactive</span>';
            case 'created_at':
                return esc_html(date('Y-m-d H:i', strtotime($item->created_at)));
            case 'actions':
                return sprintf(
                    '<button type="button" class="button eccm-delete-coupon" data-id="%s">Delete</button>',
                    esc_attr($item->id)
                );
            default:
                return '';
        }
    }
    
    public function column_cb($item) {
        return sprintf('<input type="checkbox" name="coupon[]" value="%s" />', esc_attr($item->id));
    }
    
    /**
     * Static method to safely create instance
     */
    public static function create_instance() {
        if ( is_admin() && function_exists( 'convert_to_screen' ) ) {
            return new self();
        }
        return null;
    }
}