<?php
/**
 * Plugin Name: EasyCommerce Coupon Manager
 * Plugin URI: https://sadekurrahman.com
 * Description: A simple plugin to manage EasyCommerce coupons with list table and API functionality
 * Version: 1.0.0
 * Author: Sadekur Rahman
 * License: GPL v2 or later
 * Requires Plugins: easycommerce
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ECCM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ECCM_PLUGIN_PATH', plugin_dir_path(__FILE__));

class EasyCommerceCouponManager {
    
    public function __construct() {
        add_action('wp_ajax_eccm_create_coupon', array($this, 'create_coupon_ajax'));
        add_action('wp_ajax_eccm_delete_coupon', array($this, 'delete_coupon_ajax'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    private function is_easycommerce_active() {
        return is_plugin_active('easycommerce/easycommerce.php') || class_exists('EasyCommerce');
    }
    
    public function easycommerce_missing_notice() {
        echo '<div class="notice notice-error"><p>EasyCommerce Coupon Manager requires EasyCommerce plugin to be installed and activated.</p></div>';
    }
    
    public function enqueue_scripts($hook) {
        if ($hook !== 'settings_page_ec-coupon-manager') {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_enqueue_script('eccm-admin', ECCM_PLUGIN_URL . 'assets/admin.js', array('jquery'), '1.0.0', true);
        wp_localize_script('eccm-admin', 'eccm_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('eccm_nonce')
        ));
        
        wp_enqueue_style('eccm-admin', ECCM_PLUGIN_URL . 'assets/admin.css', array(), '1.0.0');
    }
    
    public function admin_page() {
        if (!$this->is_easycommerce_active()) {
            echo '<div class="wrap"><h1>EasyCommerce Coupons</h1><p>EasyCommerce plugin is required.</p></div>';
            return;
        }
        
        $list_table = new ECCM_Coupon_List_Table();
        $list_table->prepare_items();
        
        ?>
        <div class="wrap">
            <h1>EasyCommerce Coupons</h1>
            
            <!-- Add New Coupon Form -->
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
            
            <!-- Coupons List -->
            <form method="post">
                <?php
                $list_table->display();
                ?>
            </form>
        </div>
        
        <div id="eccm-messages"></div>
        <?php
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

// Custom WP_List_Table class for coupons
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class ECCM_Coupon_List_Table extends WP_List_Table {
    
    public function __construct() {
        parent::__construct(array(
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
            'id' => array('id', false),
            'name' => array('name', false),
            'code' => array('code', false),
            'created_at' => array('created_at', false)
        );
    }
    
    public function prepare_items() {
        global $wpdb;
        
        $per_page = 20;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;
        
        $orderby = !empty($_GET['orderby']) ? sanitize_sql_orderby($_GET['orderby']) : 'id';
        $order = !empty($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';
        
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->prefix}ec_coupons");
        
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page
        ));
        
        $this->items = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ec_coupons ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ));
        
        $this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());
    }
    
    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'id':
            case 'name':
            case 'code':
                return $item->$column_name;
            case 'discount_type':
                return ucfirst($item->discount_type);
            case 'amount':
                return number_format($item->amount, 2);
            case 'active':
                return $item->active ? '<span style="color: green;">Active</span>' : '<span style="color: red;">Inactive</span>';
            case 'created_at':
                return date('Y-m-d H:i', strtotime($item->created_at));
            case 'actions':
                return sprintf(
                    '<button type="button" class="button eccm-delete-coupon" data-id="%s">Delete</button>',
                    $item->id
                );
            default:
                return '';
        }
    }
    
    public function column_cb($item) {
        return sprintf('<input type="checkbox" name="coupon[]" value="%s" />', $item->id);
    }
}

// Initialize the plugin
new EasyCommerceCouponManager();

// Create assets directory and files on activation
register_activation_hook(__FILE__, 'eccm_create_assets');

function eccm_create_assets() {
    $upload_dir = wp_upload_dir();
    $plugin_dir = plugin_dir_path(__FILE__);
    $assets_dir = $plugin_dir . 'assets/';
    
    if (!file_exists($assets_dir)) {
        wp_mkdir_p($assets_dir);
    }
    
    // Create admin.js
    $admin_js = "jQuery(document).ready(function($) {
    // Handle add coupon form submission
    $('#eccm-add-coupon-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        formData += '&action=eccm_create_coupon&nonce=' + eccm_ajax.nonce;
        
        $.post(eccm_ajax.ajax_url, formData, function(response) {
            if (response.success) {
                showMessage(response.data, 'success');
                $('#eccm-add-coupon-form')[0].reset();
                location.reload();
            } else {
                showMessage(response.data, 'error');
            }
        });
    });
    
    // Handle delete coupon
    $(document).on('click', '.eccm-delete-coupon', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to delete this coupon?')) {
            return;
        }
        
        var couponId = $(this).data('id');
        
        $.post(eccm_ajax.ajax_url, {
            action: 'eccm_delete_coupon',
            coupon_id: couponId,
            nonce: eccm_ajax.nonce
        }, function(response) {
            if (response.success) {
                showMessage(response.data, 'success');
                location.reload();
            } else {
                showMessage(response.data, 'error');
            }
        });
    });
    
    function showMessage(message, type) {
        var messageClass = type === 'success' ? 'notice-success' : 'notice-error';
        var messageHtml = '<div class=\"notice ' + messageClass + ' is-dismissible\"><p>' + message + '</p></div>';
        $('#eccm-messages').html(messageHtml);
        
        setTimeout(function() {
            $('#eccm-messages').empty();
        }, 5000);
    }
});";
    
    file_put_contents($assets_dir . 'admin.js', $admin_js);
    
    // Create admin.css
    $admin_css = ".eccm-add-coupon-form {
    border-radius: 4px;
}

.eccm-add-coupon-form h2 {
    margin-top: 0;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.eccm-delete-coupon {
    color: #a00;
}

.eccm-delete-coupon:hover {
    color: #dc3232;
}

#eccm-messages {
    margin: 20px 0;
}

#eccm-messages .notice {
    margin: 5px 0 15px;
}";
    
    file_put_contents($assets_dir . 'admin.css', $admin_css);
}