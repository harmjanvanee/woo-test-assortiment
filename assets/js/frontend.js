jQuery(document).ready(function ($) {
    $(document).on('click', '.wta-add-test-button', function (e) {
        e.preventDefault();

        var $btn = $(this);
        var productId = $btn.data('product-id');
        var originalText = $btn.text();

        if ($btn.hasClass('loading')) return;

        $btn.addClass('loading').text('...');

        $.ajax({
            url: wta_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'wta_add_test_variant',
                nonce: wta_vars.nonce,
                product_id: productId
            },
            success: function (response) {
                $btn.removeClass('loading');
                if (response.success) {
                    $btn.text(response.data.message).addClass('added');

                    // Trigger WooCommerce fragments refresh
                    if (response.data.fragments) {
                        $(document.body).trigger('added_to_cart', [response.data.fragments, response.data.cart_hash, $btn]);
                    }

                    setTimeout(function () {
                        $btn.text(originalText).removeClass('added');
                    }, 3000);
                } else {
                    alert(response.data.message || 'Er is een fout opgetreden.');
                    $btn.text(originalText);
                }
            },
            error: function () {
                $btn.removeClass('loading').text(originalText);
                alert('Er is een fout opgetreden.');
            }
        });
    });
});
