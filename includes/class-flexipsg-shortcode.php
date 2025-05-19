<?php
defined('ABSPATH') || exit;

class Flexipsg_Shortcode
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
        add_shortcode('flexipsg_carousel', [$this, 'flexipsg_render_shortcode_callback']);
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

    public function flexipsg_render_shortcode_callback($atts)
    {
        $atts = shortcode_atts([
            'title' => 'Carousel Title',
            'layout' => 'carousel',
            'limit' => 8,
            'category' => '',
        ], $atts);

        $args = [
            'post_type' => 'product',
            'posts_per_page' => intval($atts['limit']),
            'post_status' => 'publish',
            'no_found_rows' => true, // Skip counting total rows for pagination
            'update_post_term_cache' => false, // Improve performance by skipping term cache
            'update_post_meta_cache' => false, // Improve performance by skipping meta cache
        ];

        if (!empty($atts['category'])) {
            // Get category ID first to make query more efficient
            $category = get_term_by('slug', sanitize_text_field($atts['category']), 'product_cat');

            if ($category && !is_wp_error($category)) {
                $args['tax_query'] = [
                    [
                        'taxonomy' => 'product_cat',
                        'field' => 'term_id', // Query by ID is faster than slug
                        'terms' => $category->term_id,
                    ]
                ];
            }
        }

        $products = new WP_Query($args);

        ob_start();
?>

        <!-- Carousel One -->
        <div class="flexipsg-products flexipsg-theme-1 flexipsg-container">

            <div class="swiper flexipsgSwiper">
                <!-- Carousel Preloader -->
                <div class="flexipsg-swiper-preloader">
                    <div class="flexipsg-preloader-spinner"></div>
                </div>
                <!-- Carousel Preloader -->
                <!-- WooCommerce Carousel Product Title -->
                <h2 class="flexipsg-product_slider_section_title"><?php echo esc_attr($atts['title'])  ?></h2>
                <!-- WooCommerce Carousel Product Title -->
                <div class="swiper-wrapper">


                    <!-- Start swiper-slide Product -->
                    <?php if ($products->have_posts()) : ?>
                        <?php while ($products->have_posts()) : $products->the_post();
                            global $product; ?>
                            <div class="swiper-slide flexipsg-product-slide">
                                <div class="flexipsg-product_content text-center">
                                    <div class="flexipsg-product_thumbnail">
                                        <a href="<?php echo esc_url(get_permalink()); ?>">
                                            <!-- Main product image -->
                                            <?php
                                            $main_image = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), 'woocommerce_thumbnail');
                                            $gallery_ids = $product->get_gallery_image_ids();
                                            $hover_image = !empty($gallery_ids) ? wp_get_attachment_image_src($gallery_ids[0], 'woocommerce_thumbnail') : '';

                                            if ($main_image && !empty($main_image[0])) {
                                                echo '<img src="' . esc_url($main_image[0]) . '" class="img-fluid wp-post-image" alt="' . esc_attr(get_the_title()) . '">';
                                            }

                                            if ($hover_image && !empty($hover_image[0])) {
                                                echo '<img src="' . esc_url($hover_image[0]) . '" class="flexipsg-product-hover" alt="' . esc_attr(get_the_title()) . '">';
                                            }

                                            ?>
                                        </a>


                                        <div class="flexipsg-product_badge flexipsg-badge_top_left">
                                            <?php
                                            global $product;
                                            $discount = FLEXIPSG_Helper::flexipsg_get_discount_percentage($product);

                                            if ($discount !== false) {
                                                echo '<span class="flexipsg-less-price flexipsg-badge">-' . esc_html($discount) . '%</span>';
                                            }
                                            ?>
                                        </div>
                                        <?php if ($product->is_on_sale()) : ?>
                                            <div class="flexipsg-product_badge flexipsg-badge_top_right">
                                                <span class="flexipsg-less-price flexipsg-badge flexipsg-badge-sale">Sale</span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="flexipsg-overlay_button">
                                            <?php

                                            echo wp_kses_post(FLEXIPSG_Helper::flexipsg_get_add_to_cart_button($product));

                                            ?>
                                        </div>
                                    </div>
                                    <div class="flexipsg-product_details">


                                        <h3 class="flexipsg-product_title">
                                            <a
                                                href="<?php echo esc_url(get_permalink()); ?>"><?php echo esc_html(get_the_title()); ?></a>


                                        </h3>
                                        <?php
                                        global $product;
                                        echo wp_kses_post(FLEXIPSG_Helper::flexipsg_get_product_price_html($product));
                                        echo wp_kses_post(FLEXIPSG_Helper::flexipsg_get_product_rating_html($product));
                                        ?>


                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <p><?php esc_html__('No products found.', 'flexi-product-slider-grid'); ?></p>
                    <?php endif; ?>
                    <!-- End swiper-slide Product -->



                </div>
                <!-- Pagination -->
                <div class="flexipsg-pagination"></div>

                <!-- Navigation -->
                <div class="flexipsg-navigation-button">
                    <div class="flexipsg-button-prev flexipsg-carousel-nav_btn">
                        <i class="icon-arrLeft"></i>
                    </div>
                    <div class="flexipsg-button-next flexipsg-carousel-nav_btn">
                        <i class="icon-arrRight"></i>
                    </div>
                </div>

            </div>
        </div>

<?php
        wp_reset_postdata();
        return ob_get_clean();
    }
}
