<?php
defined('ABSPATH') || exit;


class Flexipsg_Enqueue
{

    private static $instance = null;

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'load_assets']);
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


    public function load_assets()
    {

        // Load Font Icons & Swiper Carousel CSS/JS
        wp_enqueue_style(
            'flexipsg-font-icons',
            FLEXIPSG_PLUGIN_URL . 'assets/libs/fonts/font-icons.css',
            [],
            FLEXIPSG_VERSION
        );

        wp_enqueue_style(
            'flexipsg-swiper',
            FLEXIPSG_PLUGIN_URL . 'assets/libs/swiper/swiper-bundle.min.css',
            [],
            FLEXIPSG_VERSION
        );

        wp_enqueue_script(
            'flexipsg-swiper',
            FLEXIPSG_PLUGIN_URL . 'assets/libs/swiper/swiper-bundle.min.js',
            [],
            FLEXIPSG_VERSION,
            true
        );

        // Custom CSS & JS
        wp_enqueue_style(
            'flexipsg-style',
            FLEXIPSG_PLUGIN_URL . 'assets/css/style.css',
            [],
            FLEXIPSG_VERSION
        );

        wp_enqueue_script(
            'flexipsg-script',
            FLEXIPSG_PLUGIN_URL . 'assets/js/main.js',
            ['jquery'],
            FLEXIPSG_VERSION,
            true
        );
        // WooCommerce: AJAX Add to Cart Support
        if (class_exists('WooCommerce')) {
            wp_enqueue_script(
                'flexipsg-ajax-cart',
                FLEXIPSG_PLUGIN_URL . 'assets/js/flexipsg-add-to-cart.js',
                ['jquery', 'wc-add-to-cart'], // add wc-add-to-cart as dependency
                FLEXIPSG_VERSION,
                true
            );

            $cart_contents = WC()->cart ? WC()->cart->get_cart() : [];

            wp_localize_script('flexipsg-ajax-cart', 'flexipsg_ajax_cart_params', [
                'ajax_url'      => admin_url('admin-ajax.php'),
                'cart_url'      => wc_get_cart_url(),
                'cart_contents' => wp_json_encode($cart_contents),
            ]);
        }
    }
}
