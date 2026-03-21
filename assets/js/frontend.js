jQuery(document).ready(function ($) {
    let selectedProducts = [];

    /**
     * Helper: Update Sticky Bar
     */
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

    /**
     * Load from LocalStorage
     */
    function loadSelections() {
        const saved = localStorage.getItem('wta_selected_products');
        if (saved) {
            try {
                selectedProducts = JSON.parse(saved);
                updateSelectionUI();
            } catch (e) {
                console.error('Error loading WTA selections:', e);
                selectedProducts = [];
            }
        }
    }

    /**
     * Save to LocalStorage
     */
    function saveSelections() {
        localStorage.setItem('wta_selected_products', JSON.stringify(selectedProducts));
    }

    /**
     * Update UI based on stored selections
     */
    function updateSelectionUI() {
        $('.wta-product-card').removeClass('selected');
        
        selectedProducts.forEach(product => {
            $(`.wta-product-card[data-product-id="${product.productId}"]`).addClass('selected');
        });

        updateStickyBar();
    }

    /**
     * Handle category filter clicks
     */
    function handleFilterClick(e) {
        e.preventDefault();
        const $btn = $(this);
        if ($btn.hasClass('active')) return;

        const category = $btn.data('category');
        const mainCategory = $('.wta-filter-bar').data('main-category');
        const $grid = $('.wta-product-grid');

        // Update UI
        $('.wta-filter-button').removeClass('active');
        $btn.addClass('active');
        $grid.addClass('loading');

        // AJAX call
        $.ajax({
            url: wta_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'wta_get_filtered_products',
                nonce: wta_vars.nonce,
                main_category: mainCategory,
                category: category
            },
            success: function(response) {
                if (response.success) {
                    $grid.html(response.data.html);
                    // Re-apply selections
                    updateSelectionUI();
                } else {
                    console.error('WTA Filter Error:', response.data.message);
                }
            },
            error: function() {
                console.error('WTA Filter Error: AJAX failed');
            },
            complete: function() {
                $grid.removeClass('loading');
            }
        });
    }

    /**
     * Multi-select Toggle
     */
    function toggleProductSelection(e) {
        // Don't toggle if clicking the link to product page
        if ($(e.target).closest('.wta-product-link').length) return;

        const $card = $(this);
        const productId = $card.data('product-id');
        const variantId = $card.data('variant-id');
        const price = $card.data('price');

        $card.toggleClass('selected');

        if ($card.hasClass('selected')) {
            // Check if already in list to avoid duplicates
            if (!selectedProducts.find(p => p.productId === productId)) {
                selectedProducts.push({ productId, variantId, price });
            }
        } else {
            selectedProducts = selectedProducts.filter(p => p.productId !== productId);
        }

        saveSelections();
        updateStickyBar();
    }

    /**
     * Bulk Add to Cart
     */
    function handleBulkAdd() {
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
                    // Clear local storage on success
                    localStorage.removeItem('wta_selected_products');
                    selectedProducts = [];

                    // Refresh cart fragments
                    if (response.data.fragments) {
                        $(document.body).trigger('added_to_cart', [response.data.fragments, response.data.cart_hash, $btn]);
                    }

                    // Redirect to cart
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
    }

    // Events
    $(document).on('click', '.wta-product-card', toggleProductSelection);
    $(document).on('click', '.wta-filter-button', handleFilterClick);
    $('.wta-bulk-add-button').on('click', handleBulkAdd);

    // Initial Load
    loadSelections();
});
