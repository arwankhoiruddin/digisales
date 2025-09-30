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
        
        // Initialize taxonomies
        $this->register_taxonomies();
        
        // Initialize meta boxes
        add_action('add_meta_boxes', array($this, 'add_product_meta_boxes'));
        add_action('save_post', array($this, 'save_product_meta'));
        
        // Initialize REST API
        add_action('rest_api_init', array($this, 'register_rest_endpoints'));
        
        // Customize admin columns
        add_filter('manage_digital_product_posts_columns', array($this, 'add_product_admin_columns'));
        add_action('manage_digital_product_posts_custom_column', array($this, 'display_product_admin_columns'), 10, 2);
        add_filter('manage_edit-digital_product_sortable_columns', array($this, 'sortable_product_admin_columns'));
        
        // Add admin filters
        add_action('restrict_manage_posts', array($this, 'add_product_type_filter'));
        add_filter('parse_query', array($this, 'filter_products_by_type'));
        
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
        // Only load on our plugin pages or digital product edit pages
        if (strpos($hook, 'digisales') !== false || 
            (in_array($hook, array('post.php', 'post-new.php')) && 
             isset($_GET['post_type']) && $_GET['post_type'] === 'digital_product') ||
            (in_array($hook, array('post.php')) && 
             get_post_type(isset($_GET['post']) ? $_GET['post'] : 0) === 'digital_product')) {
            
            wp_enqueue_style(
                'digisales-admin',
                DIGISALES_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                DIGISALES_VERSION
            );
            
            // Enqueue media uploader for file uploads
            wp_enqueue_media();
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
            'edit.php?post_type=digital_product'
        );
        
        // Add New Product submenu
        add_submenu_page(
            'digisales',
            __('Add New Product', 'digisales'),
            __('Add New Product', 'digisales'),
            'manage_options',
            'post-new.php?post_type=digital_product'
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
                        <span class="digisales-stat-number"><?php echo wp_count_posts('digital_product')->publish; ?></span>
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
                    <a href="<?php echo admin_url('post-new.php?post_type=digital_product'); ?>" class="button button-primary">
                        <?php _e('Add New Product', 'digisales'); ?>
                    </a>
                    <a href="<?php echo admin_url('edit.php?post_type=digital_product'); ?>" class="button">
                        <?php _e('View All Products', 'digisales'); ?>
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
                'view_items' => __('View Digital Products', 'digisales'),
                'search_items' => __('Search Digital Products', 'digisales'),
                'not_found' => __('No digital products found', 'digisales'),
                'not_found_in_trash' => __('No digital products found in trash', 'digisales'),
                'all_items' => __('All Digital Products', 'digisales'),
                'archives' => __('Digital Product Archives', 'digisales'),
            ),
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-download',
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
            'capability_type' => 'post',
            'rewrite' => array('slug' => 'digital-product'),
            'show_in_rest' => true,
            'rest_base' => 'digital-products',
            'menu_position' => 25,
        );
        
        register_post_type('digital_product', $args);
    }
    
    /**
     * Register taxonomies
     */
    private function register_taxonomies() {
        // Register product type taxonomy
        $args = array(
            'labels' => array(
                'name' => __('Product Types', 'digisales'),
                'singular_name' => __('Product Type', 'digisales'),
                'menu_name' => __('Product Types', 'digisales'),
                'all_items' => __('All Product Types', 'digisales'),
                'edit_item' => __('Edit Product Type', 'digisales'),
                'view_item' => __('View Product Type', 'digisales'),
                'update_item' => __('Update Product Type', 'digisales'),
                'add_new_item' => __('Add New Product Type', 'digisales'),
                'new_item_name' => __('New Product Type Name', 'digisales'),
                'search_items' => __('Search Product Types', 'digisales'),
                'not_found' => __('No product types found', 'digisales'),
            ),
            'public' => true,
            'hierarchical' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => false,
            'show_in_rest' => true,
            'rest_base' => 'product-types',
            'rewrite' => array('slug' => 'product-type'),
        );
        
        register_taxonomy('product_type', 'digital_product', $args);
        
        // Create default product types
        add_action('init', array($this, 'create_default_product_types'), 20);
    }
    
    /**
     * Create default product types
     */
    public function create_default_product_types() {
        if (!taxonomy_exists('product_type')) {
            return;
        }
        
        $default_types = array(
            'video' => __('Video', 'digisales'),
            'ebook' => __('E-book', 'digisales'),
            'design' => __('Design', 'digisales'),
            'web_series' => __('Web Series', 'digisales'),
        );
        
        foreach ($default_types as $slug => $name) {
            if (!term_exists($slug, 'product_type')) {
                wp_insert_term($name, 'product_type', array('slug' => $slug));
            }
        }
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
    
    /**
     * Add product meta boxes
     */
    public function add_product_meta_boxes() {
        // Remove default taxonomy metabox
        remove_meta_box('product_typediv', 'digital_product', 'side');
        
        // Add custom product type selector
        add_meta_box(
            'digisales_product_type',
            __('Product Type', 'digisales'),
            array($this, 'product_type_meta_box'),
            'digital_product',
            'side',
            'high'
        );
        
        add_meta_box(
            'digisales_product_details',
            __('Product Details', 'digisales'),
            array($this, 'product_details_meta_box'),
            'digital_product',
            'normal',
            'high'
        );
        
        add_meta_box(
            'digisales_product_files',
            __('Digital Assets', 'digisales'),
            array($this, 'product_files_meta_box'),
            'digital_product',
            'normal',
            'high'
        );
    }
    
    /**
     * Product type meta box
     */
    public function product_type_meta_box($post) {
        wp_nonce_field('digisales_product_type_meta', 'digisales_product_type_meta_nonce');
        
        $product_types = get_terms(array(
            'taxonomy' => 'product_type',
            'hide_empty' => false,
        ));
        
        $current_types = wp_get_post_terms($post->ID, 'product_type', array('fields' => 'slugs'));
        $current_type = !empty($current_types) ? $current_types[0] : '';
        
        ?>
        <div class="digisales-product-type-selector">
            <?php foreach ($product_types as $type) : ?>
                <label class="digisales-product-type-option">
                    <input type="radio" 
                           name="digisales_product_type" 
                           value="<?php echo esc_attr($type->slug); ?>" 
                           <?php checked($current_type, $type->slug); ?>
                           class="digisales-product-type-radio" />
                    <span class="digisales-product-type-label"><?php echo esc_html($type->name); ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Trigger field visibility on product type change
            $('.digisales-product-type-radio').on('change', function() {
                $(document).trigger('digisales-product-type-changed');
            });
        });
        </script>
        <?php
    }
    
    /**
     * Product details meta box
     */
    public function product_details_meta_box($post) {
        wp_nonce_field('digisales_product_meta', 'digisales_product_meta_nonce');
        
        $price = get_post_meta($post->ID, '_digisales_price', true);
        $youtube_url = get_post_meta($post->ID, '_digisales_youtube_url', true);
        $video_urls = get_post_meta($post->ID, '_digisales_video_urls', true);
        $video_urls = is_array($video_urls) ? $video_urls : array();
        
        ?>
        <div class="digisales-meta-box">
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="digisales_price"><?php _e('Price', 'digisales'); ?></label>
                </th>
                <td>
                    <input type="number" 
                           id="digisales_price" 
                           name="digisales_price" 
                           value="<?php echo esc_attr($price); ?>" 
                           step="0.01" 
                           min="0" 
                           placeholder="0.00" 
                           style="width: 150px;" />
                    <p class="description"><?php _e('Enter the price for this digital product.', 'digisales'); ?></p>
                </td>
            </tr>
            
            <tr id="youtube_url_row" style="display: none;">
                <th scope="row">
                    <label for="digisales_youtube_url"><?php _e('YouTube URL', 'digisales'); ?></label>
                </th>
                <td>
                    <input type="url" 
                           id="digisales_youtube_url" 
                           name="digisales_youtube_url" 
                           value="<?php echo esc_attr($youtube_url); ?>" 
                           placeholder="https://www.youtube.com/watch?v=..." 
                           style="width: 100%;" />
                    <p class="description"><?php _e('Enter an unlisted YouTube video URL for this video product.', 'digisales'); ?></p>
                </td>
            </tr>
            
            <tr id="video_urls_row" style="display: none;">
                <th scope="row">
                    <label><?php _e('Video URLs', 'digisales'); ?></label>
                </th>
                <td>
                    <div id="video_urls_container">
                        <?php if (!empty($video_urls)) : ?>
                            <?php foreach ($video_urls as $index => $url) : ?>
                                <div class="video-url-item" style="margin-bottom: 10px;">
                                    <input type="url" 
                                           name="digisales_video_urls[]" 
                                           value="<?php echo esc_attr($url); ?>" 
                                           placeholder="https://www.youtube.com/watch?v=..." 
                                           style="width: 85%;" />
                                    <button type="button" class="remove-video-url button" style="margin-left: 5px;"><?php _e('Remove', 'digisales'); ?></button>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="video-url-item" style="margin-bottom: 10px;">
                                <input type="url" 
                                       name="digisales_video_urls[]" 
                                       placeholder="https://www.youtube.com/watch?v=..." 
                                       style="width: 85%;" />
                                <button type="button" class="remove-video-url button" style="margin-left: 5px;"><?php _e('Remove', 'digisales'); ?></button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" id="add_video_url" class="button"><?php _e('Add Video URL', 'digisales'); ?></button>
                    <p class="description"><?php _e('Add multiple unlisted YouTube video URLs for this web series.', 'digisales'); ?></p>
                </td>
            </tr>
        </table>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Show/hide fields based on product type
            function toggleProductFields() {
                var selectedType = $('input[name="digisales_product_type"]:checked').val();
                
                // Hide all product-specific fields
                $('#youtube_url_row, #video_urls_row').hide();
                
                // Show relevant fields based on product type
                if (selectedType === 'video') {
                    $('#youtube_url_row').show();
                } else if (selectedType === 'web_series') {
                    $('#video_urls_row').show();
                }
            }
            
            // Monitor product type changes
            $(document).on('change', 'input[name="digisales_product_type"]', toggleProductFields);
            $(document).on('digisales-product-type-changed', toggleProductFields);
            
            // Initial call
            toggleProductFields();
            
            // Add video URL functionality
            $('#add_video_url').on('click', function() {
                var newItem = $('<div class="video-url-item" style="margin-bottom: 10px;">' +
                    '<input type="url" name="digisales_video_urls[]" placeholder="https://www.youtube.com/watch?v=..." style="width: 85%;" />' +
                    '<button type="button" class="remove-video-url button" style="margin-left: 5px;"><?php _e('Remove', 'digisales'); ?></button>' +
                    '</div>');
                $('#video_urls_container').append(newItem);
            });
            
            // Remove video URL functionality
            $(document).on('click', '.remove-video-url', function() {
                $(this).closest('.video-url-item').remove();
            });
        });
        </script>
        <?php
    }
    
    /**
     * Product files meta box
     */
    public function product_files_meta_box($post) {
        $file_attachments = get_post_meta($post->ID, '_digisales_file_attachments', true);
        $file_attachments = is_array($file_attachments) ? $file_attachments : array();
        
        ?>
        <div id="digisales_file_attachments_container">
            <?php if (!empty($file_attachments)) : ?>
                <?php foreach ($file_attachments as $index => $attachment_id) : ?>
                    <?php
                    $attachment = get_post($attachment_id);
                    if ($attachment) :
                        $file_url = wp_get_attachment_url($attachment_id);
                        $file_name = basename($file_url);
                    ?>
                    <div class="file-attachment-item" style="margin-bottom: 10px; padding: 10px; border: 1px solid #ddd;">
                        <input type="hidden" name="digisales_file_attachments[]" value="<?php echo esc_attr($attachment_id); ?>" />
                        <strong><?php echo esc_html($file_name); ?></strong>
                        <a href="<?php echo esc_url($file_url); ?>" target="_blank"><?php _e('View', 'digisales'); ?></a>
                        <button type="button" class="remove-file-attachment button" style="margin-left: 10px;"><?php _e('Remove', 'digisales'); ?></button>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <button type="button" id="upload_file_button" class="button"><?php _e('Upload File', 'digisales'); ?></button>
        <p class="description"><?php _e('Upload digital files (PDFs, images, design files) for this product.', 'digisales'); ?></p>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Media uploader
            $('#upload_file_button').on('click', function(e) {
                e.preventDefault();
                
                var mediaUploader = wp.media({
                    title: '<?php _e('Select Digital File', 'digisales'); ?>',
                    button: {
                        text: '<?php _e('Use this file', 'digisales'); ?>'
                    },
                    multiple: false
                });
                
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    var newItem = $('<div class="file-attachment-item" style="margin-bottom: 10px; padding: 10px; border: 1px solid #ddd;">' +
                        '<input type="hidden" name="digisales_file_attachments[]" value="' + attachment.id + '" />' +
                        '<strong>' + attachment.filename + '</strong>' +
                        '<a href="' + attachment.url + '" target="_blank"><?php _e('View', 'digisales'); ?></a>' +
                        '<button type="button" class="remove-file-attachment button" style="margin-left: 10px;"><?php _e('Remove', 'digisales'); ?></button>' +
                        '</div>');
                    $('#digisales_file_attachments_container').append(newItem);
                });
                
                mediaUploader.open();
            });
            
            // Remove file attachment
            $(document).on('click', '.remove-file-attachment', function() {
                $(this).closest('.file-attachment-item').remove();
            });
        });
        </script>
        <?php
    }
    
    /**
     * Save product meta data
     */
    public function save_product_meta($post_id) {
        // Verify nonce for product type
        if (isset($_POST['digisales_product_type_meta_nonce']) && 
            wp_verify_nonce($_POST['digisales_product_type_meta_nonce'], 'digisales_product_type_meta')) {
            
            // Save product type
            if (isset($_POST['digisales_product_type'])) {
                $product_type = sanitize_text_field($_POST['digisales_product_type']);
                wp_set_object_terms($post_id, $product_type, 'product_type');
            }
        }
        
        // Verify nonce for product details
        if (!isset($_POST['digisales_product_meta_nonce']) || 
            !wp_verify_nonce($_POST['digisales_product_meta_nonce'], 'digisales_product_meta')) {
            return;
        }
        
        // Check if user has permission to edit the post
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Avoid autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check post type
        if (get_post_type($post_id) !== 'digital_product') {
            return;
        }
        
        // Save price
        if (isset($_POST['digisales_price'])) {
            $price = sanitize_text_field($_POST['digisales_price']);
            $price = is_numeric($price) ? floatval($price) : 0;
            update_post_meta($post_id, '_digisales_price', $price);
        }
        
        // Save YouTube URL (for video products)
        if (isset($_POST['digisales_youtube_url'])) {
            $youtube_url = esc_url_raw($_POST['digisales_youtube_url']);
            if ($this->is_valid_youtube_url($youtube_url)) {
                update_post_meta($post_id, '_digisales_youtube_url', $youtube_url);
            } else {
                delete_post_meta($post_id, '_digisales_youtube_url');
            }
        }
        
        // Save video URLs (for web series)
        if (isset($_POST['digisales_video_urls']) && is_array($_POST['digisales_video_urls'])) {
            $video_urls = array();
            foreach ($_POST['digisales_video_urls'] as $url) {
                $url = esc_url_raw($url);
                if (!empty($url) && $this->is_valid_youtube_url($url)) {
                    $video_urls[] = $url;
                }
            }
            update_post_meta($post_id, '_digisales_video_urls', $video_urls);
        }
        
        // Save file attachments
        if (isset($_POST['digisales_file_attachments']) && is_array($_POST['digisales_file_attachments'])) {
            $file_attachments = array();
            foreach ($_POST['digisales_file_attachments'] as $attachment_id) {
                $attachment_id = intval($attachment_id);
                if ($attachment_id > 0 && get_post($attachment_id)) {
                    $file_attachments[] = $attachment_id;
                }
            }
            update_post_meta($post_id, '_digisales_file_attachments', $file_attachments);
        }
    }
    
    /**
     * Validate YouTube URL
     */
    private function is_valid_youtube_url($url) {
        if (empty($url)) {
            return false;
        }
        
        $pattern = '/^(https?:\/\/)?(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/)[\w\-]+/';
        return preg_match($pattern, $url);
    }
    
    /**
     * Register REST API endpoints
     */
    public function register_rest_endpoints() {
        // Get all products
        register_rest_route('digisales/v1', '/products', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_products_rest'),
            'permission_callback' => array($this, 'check_read_permission'),
        ));
        
        // Get single product
        register_rest_route('digisales/v1', '/products/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_product_rest'),
            'permission_callback' => array($this, 'check_read_permission'),
        ));
        
        // Create product
        register_rest_route('digisales/v1', '/products', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_product_rest'),
            'permission_callback' => array($this, 'check_edit_permission'),
        ));
        
        // Update product
        register_rest_route('digisales/v1', '/products/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_product_rest'),
            'permission_callback' => array($this, 'check_edit_permission'),
        ));
        
        // Delete product
        register_rest_route('digisales/v1', '/products/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_product_rest'),
            'permission_callback' => array($this, 'check_delete_permission'),
        ));
    }
    
    /**
     * Check read permission for REST API
     */
    public function check_read_permission() {
        return true; // Allow public access for reading products
    }
    
    /**
     * Check edit permission for REST API
     */
    public function check_edit_permission() {
        return current_user_can('edit_posts');
    }
    
    /**
     * Check delete permission for REST API
     */
    public function check_delete_permission() {
        return current_user_can('delete_posts');
    }
    
    /**
     * Get products REST endpoint
     */
    public function get_products_rest($request) {
        $args = array(
            'post_type' => 'digital_product',
            'post_status' => 'publish',
            'posts_per_page' => $request->get_param('per_page') ?: 10,
            'paged' => $request->get_param('page') ?: 1,
        );
        
        // Filter by product type if specified
        $product_type = $request->get_param('product_type');
        if ($product_type) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_type',
                    'field' => 'slug',
                    'terms' => $product_type,
                ),
            );
        }
        
        $posts = get_posts($args);
        $products = array();
        
        foreach ($posts as $post) {
            $products[] = $this->format_product_for_rest($post);
        }
        
        return rest_ensure_response($products);
    }
    
    /**
     * Get single product REST endpoint
     */
    public function get_product_rest($request) {
        $product_id = $request->get_param('id');
        $post = get_post($product_id);
        
        if (!$post || $post->post_type !== 'digital_product') {
            return new WP_Error('product_not_found', __('Product not found', 'digisales'), array('status' => 404));
        }
        
        return rest_ensure_response($this->format_product_for_rest($post));
    }
    
    /**
     * Create product REST endpoint
     */
    public function create_product_rest($request) {
        $title = sanitize_text_field($request->get_param('title'));
        $content = wp_kses_post($request->get_param('content'));
        $product_type = sanitize_text_field($request->get_param('product_type'));
        
        if (empty($title)) {
            return new WP_Error('missing_title', __('Product title is required', 'digisales'), array('status' => 400));
        }
        
        $post_data = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_type' => 'digital_product',
            'post_status' => 'publish',
        );
        
        $product_id = wp_insert_post($post_data);
        
        if (is_wp_error($product_id)) {
            return $product_id;
        }
        
        // Set product type
        if ($product_type) {
            wp_set_object_terms($product_id, $product_type, 'product_type');
        }
        
        // Save additional meta data
        $this->save_product_meta_from_rest($product_id, $request);
        
        $post = get_post($product_id);
        return rest_ensure_response($this->format_product_for_rest($post));
    }
    
    /**
     * Update product REST endpoint
     */
    public function update_product_rest($request) {
        $product_id = $request->get_param('id');
        $post = get_post($product_id);
        
        if (!$post || $post->post_type !== 'digital_product') {
            return new WP_Error('product_not_found', __('Product not found', 'digisales'), array('status' => 404));
        }
        
        $post_data = array('ID' => $product_id);
        
        if ($request->has_param('title')) {
            $post_data['post_title'] = sanitize_text_field($request->get_param('title'));
        }
        
        if ($request->has_param('content')) {
            $post_data['post_content'] = wp_kses_post($request->get_param('content'));
        }
        
        wp_update_post($post_data);
        
        // Update product type
        if ($request->has_param('product_type')) {
            $product_type = sanitize_text_field($request->get_param('product_type'));
            wp_set_object_terms($product_id, $product_type, 'product_type');
        }
        
        // Save additional meta data
        $this->save_product_meta_from_rest($product_id, $request);
        
        $post = get_post($product_id);
        return rest_ensure_response($this->format_product_for_rest($post));
    }
    
    /**
     * Delete product REST endpoint
     */
    public function delete_product_rest($request) {
        $product_id = $request->get_param('id');
        $post = get_post($product_id);
        
        if (!$post || $post->post_type !== 'digital_product') {
            return new WP_Error('product_not_found', __('Product not found', 'digisales'), array('status' => 404));
        }
        
        $result = wp_delete_post($product_id, true);
        
        if (!$result) {
            return new WP_Error('delete_failed', __('Failed to delete product', 'digisales'), array('status' => 500));
        }
        
        return rest_ensure_response(array('deleted' => true));
    }
    
    /**
     * Format product for REST API response
     */
    private function format_product_for_rest($post) {
        $product_types = wp_get_post_terms($post->ID, 'product_type', array('fields' => 'slugs'));
        $product_type = !empty($product_types) ? $product_types[0] : '';
        
        $product = array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'content' => $post->post_content,
            'excerpt' => $post->post_excerpt,
            'status' => $post->post_status,
            'product_type' => $product_type,
            'price' => get_post_meta($post->ID, '_digisales_price', true),
            'featured_image' => get_the_post_thumbnail_url($post->ID, 'medium'),
            'date_created' => $post->post_date,
            'date_modified' => $post->post_modified,
        );
        
        // Add type-specific data
        if ($product_type === 'video') {
            $product['youtube_url'] = get_post_meta($post->ID, '_digisales_youtube_url', true);
        } elseif ($product_type === 'web_series') {
            $product['video_urls'] = get_post_meta($post->ID, '_digisales_video_urls', true) ?: array();
        } elseif (in_array($product_type, array('ebook', 'design'))) {
            $file_attachments = get_post_meta($post->ID, '_digisales_file_attachments', true) ?: array();
            $product['files'] = array();
            foreach ($file_attachments as $attachment_id) {
                $attachment = get_post($attachment_id);
                if ($attachment) {
                    $product['files'][] = array(
                        'id' => $attachment_id,
                        'name' => $attachment->post_title,
                        'url' => wp_get_attachment_url($attachment_id),
                    );
                }
            }
        }
        
        return $product;
    }
    
    /**
     * Save product meta from REST request
     */
    private function save_product_meta_from_rest($product_id, $request) {
        // Save price
        if ($request->has_param('price')) {
            $price = floatval($request->get_param('price'));
            update_post_meta($product_id, '_digisales_price', $price);
        }
        
        // Save YouTube URL
        if ($request->has_param('youtube_url')) {
            $youtube_url = esc_url_raw($request->get_param('youtube_url'));
            if ($this->is_valid_youtube_url($youtube_url)) {
                update_post_meta($product_id, '_digisales_youtube_url', $youtube_url);
            }
        }
        
        // Save video URLs
        if ($request->has_param('video_urls')) {
            $video_urls = $request->get_param('video_urls');
            if (is_array($video_urls)) {
                $validated_urls = array();
                foreach ($video_urls as $url) {
                    $url = esc_url_raw($url);
                    if ($this->is_valid_youtube_url($url)) {
                        $validated_urls[] = $url;
                    }
                }
                update_post_meta($product_id, '_digisales_video_urls', $validated_urls);
            }
        }
        
        // Save file attachments
        if ($request->has_param('file_attachments')) {
            $file_attachments = $request->get_param('file_attachments');
            if (is_array($file_attachments)) {
                $validated_attachments = array();
                foreach ($file_attachments as $attachment_id) {
                    $attachment_id = intval($attachment_id);
                    if ($attachment_id > 0 && get_post($attachment_id)) {
                        $validated_attachments[] = $attachment_id;
                    }
                }
                update_post_meta($product_id, '_digisales_file_attachments', $validated_attachments);
            }
        }
    }
    
    /**
     * Add custom columns to product admin list
     */
    public function add_product_admin_columns($columns) {
        $new_columns = array();
        
        // Keep checkbox and title
        if (isset($columns['cb'])) {
            $new_columns['cb'] = $columns['cb'];
        }
        if (isset($columns['title'])) {
            $new_columns['title'] = $columns['title'];
        }
        
        // Add custom columns
        $new_columns['product_type'] = __('Product Type', 'digisales');
        $new_columns['price'] = __('Price', 'digisales');
        $new_columns['thumbnail'] = __('Image', 'digisales');
        
        // Keep date column
        if (isset($columns['date'])) {
            $new_columns['date'] = $columns['date'];
        }
        
        return $new_columns;
    }
    
    /**
     * Display custom column content
     */
    public function display_product_admin_columns($column, $post_id) {
        switch ($column) {
            case 'product_type':
                $product_types = wp_get_post_terms($post_id, 'product_type');
                if (!empty($product_types)) {
                    $type_names = array();
                    foreach ($product_types as $type) {
                        $type_names[] = $type->name;
                    }
                    echo esc_html(implode(', ', $type_names));
                } else {
                    echo '—';
                }
                break;
                
            case 'price':
                $price = get_post_meta($post_id, '_digisales_price', true);
                if ($price) {
                    echo '$' . number_format((float)$price, 2);
                } else {
                    echo '—';
                }
                break;
                
            case 'thumbnail':
                $thumbnail = get_the_post_thumbnail($post_id, array(50, 50));
                if ($thumbnail) {
                    echo $thumbnail;
                } else {
                    echo '—';
                }
                break;
        }
    }
    
    /**
     * Make custom columns sortable
     */
    public function sortable_product_admin_columns($columns) {
        $columns['price'] = 'price';
        $columns['product_type'] = 'product_type';
        return $columns;
    }
    
    /**
     * Add product type filter to admin list
     */
    public function add_product_type_filter() {
        global $typenow;
        
        if ($typenow === 'digital_product') {
            $selected = isset($_GET['product_type_filter']) ? $_GET['product_type_filter'] : '';
            $terms = get_terms(array(
                'taxonomy' => 'product_type',
                'hide_empty' => false,
            ));
            
            if (!empty($terms)) {
                echo '<select name="product_type_filter">';
                echo '<option value="">' . __('All Product Types', 'digisales') . '</option>';
                foreach ($terms as $term) {
                    $selected_attr = selected($selected, $term->slug, false);
                    echo "<option value='{$term->slug}' {$selected_attr}>{$term->name}</option>";
                }
                echo '</select>';
            }
        }
    }
    
    /**
     * Filter products by type in admin list
     */
    public function filter_products_by_type($query) {
        global $pagenow, $typenow;
        
        if ($pagenow === 'edit.php' && $typenow === 'digital_product' && 
            isset($_GET['product_type_filter']) && !empty($_GET['product_type_filter'])) {
            
            $query->query_vars['tax_query'] = array(
                array(
                    'taxonomy' => 'product_type',
                    'field' => 'slug',
                    'terms' => sanitize_text_field($_GET['product_type_filter']),
                ),
            );
        }
    }
}

// Initialize the plugin
DigiSales::get_instance();