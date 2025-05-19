<?php

/**
 * Plugin Name: Flexi Product Slider & Grid for WooCommerce
 * Plugin URI:  https://wordpress.org/plugins/flexi-product-slider-grid/
 * Description: Beautiful WooCommerce product carousel and grid layout with responsive slider and custom display options. Use this Shortcode => [flexipsg_carousel title="Product Carousel Title" category="" limit=""]
 * Version: 1.0.3
 * Author: wpdecent
 * Author URI: https://profiles.wordpress.org/wpdecent
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: flexi-product-slider-grid
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 */

defined('ABSPATH') || exit;

// Hook to add a notice after plugin row if WooCommerce is not active
add_action('after_plugin_row_' . plugin_basename(__FILE__), 'flexipsg_plugin_row_notice');

function flexipsg_plugin_row_notice()
{
    if (!class_exists('WooCommerce')) {
        $wp_list_table = _get_list_table('WP_Plugins_List_Table');
        echo '<tr class="plugin-update-tr active">
                 <td colspan="' . esc_attr($wp_list_table->get_column_count()) . '" class="plugin-update colspanchange">
                     <div class="notice inline notice-error notice-alt">
                         <p>';
        echo wp_kses_post(sprintf(
            /* translators: %s: URL to the WooCommerce plugin page */
            __('<strong>Flexi Product Slider & Grid</strong> requires <a href="%s" target="_blank">WooCommerce</a> to be installed and active.', 'flexi-product-slider-grid'),
            esc_url('https://wordpress.org/plugins/woocommerce/')
        ));
        echo        '</p>
                     </div>
                 </td>
               </tr>';
    }
}


/**
 * Initialize the plugin tracker
 *
 * @return void
 */
function appsero_init_tracker_flexi_product_slider_grid()
{

    if (! class_exists('Appsero\Client')) {
        require_once __DIR__ . '/appsero/src/Client.php';
    }

    $client = new Appsero\Client('dae3d370-69e0-47c7-b79f-9d65a428824d', 'Flexi Product Slider &amp; Grid for WooCommerce', __FILE__);

    // Active insights
    $client->insights()->init();
}

appsero_init_tracker_flexi_product_slider_grid();



// Main plugin class
final class Flexi_Product_Slider_Grid
{
    const VERSION = '1.0.3';
    private static $instance = null;

    public $plugin_slug = 'flexi-product-slider-grid';
    public $plugin_dir;
    public $plugin_url;

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('plugins_loaded', [$this, 'init_plugin']);
    }
    // Prevent cloning (must be public in PHP 8+)
    public function __clone()
    {
        throw new \Exception('Cloning is not allowed.');
    }

    // Prevent unserializing (must be public in PHP 8+)
    public function __wakeup()
    {
        throw new \Exception('Unserializing is not allowed.');
    }

    public function init_plugin()
    {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error"><p>' .
                    wp_kses_post(
                        sprintf(
                            // translators: %s is the URL to the WooCommerce plugin.
                            __('<strong>Flexi Product Slider & Grid</strong> requires <a href="%s" target="_blank">WooCommerce</a> to be installed and active.', 'flexi-product-slider-grid'),
                            esc_url('https://wordpress.org/plugins/woocommerce/')
                        )
                    ) .
                    '</p></div>';
            });
            return;
        }

        $this->define_constants();
        $this->load_dependencies();
        $this->setup_hooks();
    }

    private function define_constants()
    {
        $this->plugin_dir = plugin_dir_path(__FILE__);
        $this->plugin_url = plugin_dir_url(__FILE__);

        if (!defined('FLEXIPSG_VERSION')) {
            define('FLEXIPSG_VERSION', self::VERSION);
        }
        if (!defined('FLEXIPSG_PLUGIN_PATH')) {
            define('FLEXIPSG_PLUGIN_PATH', $this->plugin_dir);
        }
        if (!defined('FLEXIPSG_PLUGIN_URL')) {
            define('FLEXIPSG_PLUGIN_URL', $this->plugin_url);
        }
    }

    private function load_dependencies()
    {
        require_once FLEXIPSG_PLUGIN_PATH . 'includes/class-flexipsg-enqueue.php';
        require_once FLEXIPSG_PLUGIN_PATH . 'includes/class-flexipsg-shortcode.php';
        require_once FLEXIPSG_PLUGIN_PATH . 'includes/class-flexipsg-helper.php';
    }
    private function setup_hooks()
    {
        register_activation_hook(__FILE__, [$this, 'activate_plugin']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate_plugin']);

        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('init', [$this, 'init']);
    }

    public function activate_plugin()
    {
        if (!class_exists('WooCommerce')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                wp_kses_post(
                    sprintf(
                        // translators: %s is the URL to the WooCommerce plugin.
                        __('<strong>Flexi Product Slider & Grid</strong> requires <a href="%s" target="_blank">WooCommerce</a> to be installed and active. Plugin has been deactivated.', 'flexi-product-slider-grid'),
                        esc_url('https://wordpress.org/plugins/woocommerce/')
                    )
                ),
                esc_html__('Plugin Activation Error', 'flexi-product-slider-grid'),
                ['back_link' => true]
            );
        }
    }
    public function deactivate_plugin()
    {
        // You can add cleanup code here if needed (like flushing rewrite rules)
    }

    public function load_textdomain()
    {
        load_plugin_textdomain('flexi-product-slider-grid', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function init()
    {
        Flexipsg_Enqueue::instance();
        Flexipsg_Shortcode::instance();
    }
}

// Initialize the plugin
Flexi_Product_Slider_Grid::instance();
