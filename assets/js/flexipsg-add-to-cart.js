jQuery(function($) {
    // When the add to cart button is clicked
    $(document).on('click', '.add_to_cart_button', function(e) {
        e.preventDefault();
        var $button = $(this);
        var productID = $button.data('product_id');
        
        // Add loading class
        $button.addClass('loading');

        // Send AJAX request to add product to cart
        var data = {
            action: 'woocommerce_add_to_cart',
            product_id: productID,
        };

        $.post(wc_add_to_cart_params.ajax_url, data, function(response) {
            if (response.error && response.product_url) {
                window.location = response.product_url;
                return;
            }

            // Handle button text change
            $button.removeClass('loading ajax_add_to_cart add_to_cart_button')
                .addClass('added-to-cart')
                .attr('href', wc_add_to_cart_params.cart_url)
                .text('View Cart')
                .prop('disabled', true)
                .addClass('view-cart-red'); // Add the class to change the color

            // Trigger WooCommerce update for cart fragments (such as cart total)
            $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $button]);
        });
    });

    // On page load, check if the product is in the cart (to handle refresh)
    $(document).ready(function() {
        // Safely parse the cart contents, use empty array if invalid
        var cartContents = (wc_add_to_cart_params.cart_contents && wc_add_to_cart_params.cart_contents !== 'undefined') ? JSON.parse(wc_add_to_cart_params.cart_contents) : [];

        // Loop through each add-to-cart button to update the status
        $('.add_to_cart_button').each(function() {
            var $button = $(this);
            var productID = $button.data('product_id');

            // If the product is in the cart
            if (cartContents.some(function(item) { return item.product_id === productID; })) {
                $button.removeClass('ajax_add_to_cart add_to_cart_button')
                    .addClass('added-to-cart')
                    .attr('href', wc_add_to_cart_params.cart_url)
                    .text('View Cart')
                    .prop('disabled', true)
                    .addClass('view-cart-red'); // Add the class to change the color
            }
        });
    });
});
