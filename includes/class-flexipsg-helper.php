<?php
defined('ABSPATH') || exit;

class FLEXIPSG_Helper
{

    public static function is_woocommerce_active()
    {
        return class_exists('WooCommerce');
    }

    // Get Discount percentage
    public static function flexipsg_get_discount_percentage($product)
    {
        if (!$product->is_on_sale()) {
            return false;
        }

        if ($product->is_type('variable')) {
            $percentages = array();
            $variation_prices = $product->get_variation_prices();

            $regular_prices = $variation_prices['regular_price'];
            $sale_prices = $variation_prices['sale_price'];

            foreach ($regular_prices as $key => $regular_price) {
                if ($regular_price > 0) {
                    $sale_price = $sale_prices[$key];
                    $percentages[] = round(100 - ($sale_price / $regular_price * 100));
                }
            }

            return !empty($percentages) ? max($percentages) : false;
        } else {
            $regular_price = (float) $product->get_regular_price();
            $sale_price = (float) $product->get_sale_price();

            if ($regular_price > 0) {
                return round(100 - ($sale_price / $regular_price * 100));
            }
        }

        return false;
    }

    // Get custom Product price
    public static function flexipsg_get_product_price_html($product)
    {
        ob_start();
?>
        <div class="flexipsg-product_price">
            <?php
            $regular_price = $product->get_regular_price();
            $sale_price = $product->get_sale_price();
            $price = $product->get_price();

            if ($product->is_on_sale() && $regular_price && $sale_price) {
                $percentage = round(100 - ($sale_price / $regular_price * 100));
                echo '<span class="flexipsg-product_price_sale">' . wp_kses_post(wc_price($sale_price)) . '</span>';
                echo '<s>' . wp_kses_post(wc_price($regular_price)) . '</s>';
                echo '<span class="flexipsg-badge_rounded flexipsg-badge_sm flexipsg-badge">-' . esc_html($percentage) . '%</span>';
            } else {
                echo '<span class="flexipsg-product_price_sale">' . wp_kses_post(wc_price($price)) . '</span>';
            }
            ?>
        </div>
    <?php
        return ob_get_clean();
    }

    // Get Product rating 
    public static function flexipsg_get_product_rating_html($product)
    {
        ob_start();
    ?>
        <div class="flexipsg-product_rating_wrap">
            <?php

            if ($product->get_rating_count() > 0) {
                echo wp_kses_post(wc_get_rating_html($product->get_average_rating(), $product->get_rating_count()));
            } else {
                echo '<div class="flexipsg-no-ratings">' . esc_html__('No reviews yet', 'flexi-product-slider-grid') . '</div>';
            }


            ?>
        </div>
<?php
        return ob_get_clean();
    }




    /**
     * Get AJAX add to cart button HTML
     * 
     * @param WC_Product $product The product object
     * @param array $args Additional arguments
     * @return string Button HTML
     */
    public static function flexipsg_get_add_to_cart_button($product, $args = array())
    {
        $product_id = $product->get_id();
        $in_cart = false;


        // Ensure WooCommerce is loaded and the cart is available
        if (class_exists('WooCommerce') && WC()->cart) {
            // Get the cart contents

            foreach (WC()->cart->get_cart() as $cart_item) {
                if ($cart_item['product_id'] == $product_id) {
                    $in_cart = true;
                    break;
                }
            }

            // Now you can safely use $cart_contents
        } else {
            // WooCommerce or the cart is not available, handle the error or fallback
            $cart_contents = []; // Empty cart as fallback
        }




        $args = wp_parse_args($args, [
            'class' => 'flexipsg-btn-main-product',
            'attributes' => [
                'data-product_id' => $product_id,
                'aria-label' => $product->add_to_cart_description(),
                'rel' => 'nofollow',
            ]
        ]);

        if ($product->is_purchasable() && $product->is_in_stock()) {
            if ($in_cart) {
                return sprintf(
                    '<a href="%s" class="%s added-to-cart" disabled>%s</a>',
                    esc_url(wc_get_cart_url()),
                    esc_attr($args['class']),
                    esc_html__('View Cart', 'flexi-product-slider-grid')
                );
            } else {
                $args['class'] .= ' product_type_' . $product->get_type() . ' ajax_add_to_cart add_to_cart_button';

                return sprintf(
                    '<a href="%s" class="%s" %s>%s</a>',
                    esc_url($product->add_to_cart_url()),
                    esc_attr($args['class']),
                    wc_implode_html_attributes($args['attributes']),
                    esc_html__('Add to Cart', 'flexi-product-slider-grid')
                );
            }
        }

        return '<a class="flexipsg-btn-main-product out-of-stock" disabled>' . __('Out of Stock', 'flexi-product-slider-grid') . '</a>';
    }
}
