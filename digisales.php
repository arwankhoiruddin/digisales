<?php
/**
 * Plugin Name: DigiSales
 * Plugin URI: https://yourdomain.com
 * Description: Comprehensive Digital Product Sales Plugin for WordPress. Transform your WordPress installation into a complete digital product sales platform.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourdomain.com
 * Text Domain: digisales
 * Domain Path: /languages
 * License: Commercial
 * License URI: https://yourdomain.com/license
 * Requires at least: 5.8
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DIGISALES_VERSION', '1.0.0');
define('DIGISALES_PLUGIN_FILE', __FILE__);
define('DIGISALES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DIGISALES_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DIGISALES_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main DigiSales Class
 */
class DigiSales {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Initialize plugin
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        
        // Load text domain
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create necessary database tables if needed
        $this->create_tables();
        
        // Set default options
        $this->set_default_options();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Initialize custom post types
        $this->register_post_types();
        
        // Initialize capabilities
        $this->setup_capabilities();
    }
    
    /**
     * Load text domain for internationalization
     */
    public function load_textdomain() {
        load_plugin_textdomain('digisales', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_scripts($hook) {
        // Only load on our plugin pages
        if (strpos($hook, 'digisales') !== false) {
            wp_enqueue_style(
                'digisales-admin',
                DIGISALES_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                DIGISALES_VERSION
            );
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu page
        add_menu_page(
            __('DigiSales', 'digisales'),
            __('DigiSales', 'digisales'),
            'manage_options',
            'digisales',
            array($this, 'admin_page'),
            'dashicons-cart',
            30
        );
        
        // Dashboard submenu
        add_submenu_page(
            'digisales',
            __('Dashboard', 'digisales'),
            __('Dashboard', 'digisales'),
            'manage_options',
            'digisales',
            array($this, 'admin_page')
        );
        
        // Products submenu
        add_submenu_page(
            'digisales',
            __('Products', 'digisales'),
            __('Products', 'digisales'),
            'manage_options',
            'digisales-products',
            array($this, 'products_page')
        );
        
        // Orders submenu
        add_submenu_page(
            'digisales',
            __('Orders', 'digisales'),
            __('Orders', 'digisales'),
            'manage_options',
            'digisales-orders',
            array($this, 'orders_page')
        );
        
        // Settings submenu
        add_submenu_page(
            'digisales',
            __('Settings', 'digisales'),
            __('Settings', 'digisales'),
            'manage_options',
            'digisales-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Initialize admin settings
     */
    public function admin_init() {
        // Register settings
        register_setting('digisales_settings', 'digisales_options');
        
        // Add settings sections
        add_settings_section(
            'digisales_general',
            __('General Settings', 'digisales'),
            array($this, 'general_section_callback'),
            'digisales_settings'
        );
        
        // Add settings fields
        add_settings_field(
            'license_key',
            __('License Key', 'digisales'),
            array($this, 'license_key_callback'),
            'digisales_settings',
            'digisales_general'
        );
    }
    
    /**
     * Main admin page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <div class="digisales-dashboard">
                <div class="digisales-welcome">
                    <h2><?php _e('Welcome to DigiSales', 'digisales'); ?></h2>
                    <p><?php _e('Transform your WordPress site into a complete digital product sales platform.', 'digisales'); ?></p>
                </div>
                
                <div class="digisales-stats-grid">
                    <div class="digisales-stat-card">
                        <h3><?php _e('Total Products', 'digisales'); ?></h3>
                        <span class="digisales-stat-number">0</span>
                    </div>
                    
                    <div class="digisales-stat-card">
                        <h3><?php _e('Total Orders', 'digisales'); ?></h3>
                        <span class="digisales-stat-number">0</span>
                    </div>
                    
                    <div class="digisales-stat-card">
                        <h3><?php _e('Total Revenue', 'digisales'); ?></h3>
                        <span class="digisales-stat-number">$0</span>
                    </div>
                </div>
                
                <div class="digisales-quick-actions">
                    <h3><?php _e('Quick Actions', 'digisales'); ?></h3>
                    <a href="<?php echo admin_url('admin.php?page=digisales-products'); ?>" class="button button-primary">
                        <?php _e('Add New Product', 'digisales'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=digisales-settings'); ?>" class="button">
                        <?php _e('Configure Settings', 'digisales'); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Products page
     */
    public function products_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <p><?php _e('Manage your digital products here.', 'digisales'); ?></p>
            <div class="digisales-products-placeholder">
                <p><?php _e('Product management interface will be implemented here.', 'digisales'); ?></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Orders page
     */
    public function orders_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <p><?php _e('View and manage customer orders.', 'digisales'); ?></p>
            <div class="digisales-orders-placeholder">
                <p><?php _e('Order management interface will be implemented here.', 'digisales'); ?></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('digisales_settings');
                do_settings_sections('digisales_settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * General settings section callback
     */
    public function general_section_callback() {
        echo '<p>' . __('Configure your DigiSales plugin settings.', 'digisales') . '</p>';
    }
    
    /**
     * License key field callback
     */
    public function license_key_callback() {
        $options = get_option('digisales_options');
        $license_key = isset($options['license_key']) ? $options['license_key'] : '';
        ?>
        <input type="text" 
               name="digisales_options[license_key]" 
               value="<?php echo esc_attr($license_key); ?>" 
               placeholder="<?php _e('Enter your license key', 'digisales'); ?>"
               class="regular-text" />
        <p class="description"><?php _e('Enter your DigiSales license key to activate the plugin.', 'digisales'); ?></p>
        <?php
    }
    
    /**
     * Register custom post types
     */
    private function register_post_types() {
        // Register digital product post type
        $args = array(
            'labels' => array(
                'name' => __('Digital Products', 'digisales'),
                'singular_name' => __('Digital Product', 'digisales'),
                'add_new' => __('Add New Product', 'digisales'),
                'add_new_item' => __('Add New Digital Product', 'digisales'),
                'edit_item' => __('Edit Digital Product', 'digisales'),
                'new_item' => __('New Digital Product', 'digisales'),
                'view_item' => __('View Digital Product', 'digisales'),
                'search_items' => __('Search Digital Products', 'digisales'),
                'not_found' => __('No digital products found', 'digisales'),
                'not_found_in_trash' => __('No digital products found in trash', 'digisales'),
            ),
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-download',
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
            'capability_type' => 'post',
            'rewrite' => array('slug' => 'digital-product'),
        );
        
        register_post_type('digisales_product', $args);
    }
    
    /**
     * Setup user capabilities
     */
    private function setup_capabilities() {
        // Add capabilities for managing digital products
        $role = get_role('administrator');
        if ($role) {
            $role->add_cap('manage_digisales');
            $role->add_cap('edit_digisales_products');
            $role->add_cap('delete_digisales_products');
        }
    }
    
    /**
     * Create necessary database tables
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Orders table
        $table_name = $wpdb->prefix . 'digisales_orders';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            order_key varchar(255) NOT NULL,
            customer_email varchar(255) NOT NULL,
            product_id mediumint(9) NOT NULL,
            amount decimal(10,2) NOT NULL,
            status varchar(50) NOT NULL DEFAULT 'pending',
            payment_method varchar(50) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY order_key (order_key)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Set default plugin options
     */
    private function set_default_options() {
        $default_options = array(
            'license_key' => '',
            'currency' => 'USD',
            'test_mode' => true,
        );
        
        add_option('digisales_options', $default_options);
    }
}

// Initialize the plugin
DigiSales::get_instance();