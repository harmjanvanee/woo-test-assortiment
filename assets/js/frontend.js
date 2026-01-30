jQuery(document).ready(function ($) {
    let selectedProducts = [];

    // Helper: Update Sticky Bar
    function updateStickyBar() {
        const $stickyBar = $('.wta-sticky-bar');
        const $count = $('.wta-total-count');
        const $price = $('.wta-total-price');

        const totalCount = selectedProducts.length;
        let totalPrice = 0;

        selectedProducts.forEach(product => {
            totalPrice += parseFloat(product.price);
        });

        $count.text(totalCount);
        $price.text('€ ' + totalPrice.toLocaleString('nl-NL', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

        if (totalCount > 0) {
            $stickyBar.addClass('active');
        } else {
            $stickyBar.removeClass('active');
        }
    }

    // Single Button Add (Legacy)
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
                    if (response.data.fragments) {
                        $(document.body).trigger('added_to_cart', [response.data.fragments, response.data.cart_hash, $btn]);
                    }
                    setTimeout(function () { $btn.text(originalText).removeClass('added'); }, 3000);
                } else {
                    alert(response.data.message || 'Er is een fout opgetreden.');
                    $btn.text(originalText);
                }
            }
        });
    });

    // Multi-select Toggle
    $(document).on('click', '.wta-product-card', function (e) {
        // Don't toggle if clicking the link to product page
        if ($(e.target).closest('.wta-product-link').length) return;

        const $card = $(this);
        const productId = $card.data('product-id');
        const variantId = $card.data('variant-id');
        const price = $card.data('price');

        $card.toggleClass('selected');

        if ($card.hasClass('selected')) {
            selectedProducts.push({ productId, variantId, price });
        } else {
            selectedProducts = selectedProducts.filter(p => p.productId !== productId);
        }

        updateStickyBar();
    });

    // Bulk Add
    $('.wta-bulk-add-button').on('click', function () {
        const $btn = $(this);
        if ($btn.hasClass('loading') || selectedProducts.length === 0) return;

        $btn.addClass('loading');

        const variantIds = selectedProducts.map(p => p.variantId);
        const productIds = selectedProducts.map(p => p.productId);

        $.ajax({
            url: wta_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'wta_bulk_add_test_variants',
                nonce: wta_vars.nonce,
                variant_ids: variantIds,
                product_ids: productIds
            },
            success: function (response) {
                $btn.removeClass('loading');
                if (response.success) {
                    // Refresh cart fragments
                    if (response.data.fragments) {
                        $(document.body).trigger('added_to_cart', [response.data.fragments, response.data.cart_hash, $btn]);
                    }

                    // Redirect to cart or checkout as is common for bulk adds
                    window.location.href = wc_add_to_cart_params.cart_url || '/winkelwagen';
                } else {
                    alert(response.data.message || 'Er is een fout opgetreden.');
                }
            },
            error: function () {
                $btn.removeClass('loading');
                alert('Er is een fout opgetreden bij het toevoegen aan de winkelwagen.');
            }
        });
    });
});
