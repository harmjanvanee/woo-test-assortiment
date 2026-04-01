jQuery(document).ready(function ($) {
    let selectedProducts = [];

    /**
     * Helper: Update Sticky Bar
     */
    function updateStickyBar() {
        const $stickyBar = $('.wta-sticky-bar');
        const $count = $('.wta-total-count');
        const $totalNow = $('.wta-sticky-total-now');
        const $couponValue = $('.wta-sticky-coupon-value');

        const totalCount = selectedProducts.length;
        let totalPrice = 0;

        selectedProducts.forEach(product => {
            totalPrice += parseFloat(product.price);
        });

        const discountRate = wta_vars.discount_rate / 100;
        const totalActionPrice = totalPrice * discountRate;

        const formatPrice = (p) => '€ ' + p.toLocaleString('nl-NL', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        $count.text(totalCount);
        $totalNow.text(formatPrice(totalPrice));
        $couponValue.text(formatPrice(totalActionPrice));
        
        // Hide/Show logic for the bar itself remains the same

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
        const level = $btn.data('level');
        const category = $btn.data('category');
        const $container = $('.wta-assortiment-container');
        const mainCategory = $container.data('main-category');
        const $grid = $('.wta-product-grid');
        const $secondaryRow = $('.wta-filter-bar.secondary');

        // Handle Primary Level
        if (level === 'primary') {
            $('.wta-filter-bar.primary .wta-filter-button').removeClass('active');
            $btn.addClass('active');

            // Reset secondary row
            $('.wta-secondary-group').hide();
            $('.wta-filter-bar.secondary .wta-filter-button').removeClass('active');
            
            if (category === '') {
                $secondaryRow.hide();
            } else {
                const $group = $(`.wta-secondary-group[data-parent="${category}"]`);
                if ($group.length && $group.find('.wta-filter-button').length > 0) {
                    $secondaryRow.slideDown(200);
                    $group.show();
                } else {
                    $secondaryRow.hide();
                }
            }
        } 
        // Handle Secondary Level
        else {
            $btn.toggleClass('active');
        }

        // Collect all active categories for filtering
        let filterCategories = [];
        if (level === 'primary' && category === '') {
            // 'Alles' selected
            filterCategories = [];
        } else if ($('.wta-secondary-group:visible').length) {
            // Get all active sub-categories from the currently visible group
            $('.wta-secondary-group:visible .wta-filter-button.active').each(function() {
                filterCategories.push($(this).data('category'));
            });
            
            // If no sub-categories are active, fall back to the primary category (parent)
            if (filterCategories.length === 0) {
                filterCategories.push($('.wta-filter-bar.primary .wta-filter-button.active').data('category'));
            }
        } else {
            // No secondary groups, just use primary
            filterCategories.push($('.wta-filter-bar.primary .wta-filter-button.active').data('category'));
        }

        const skeletonHtml = `
            <div class="wta-skeleton-grid">
                ${Array(8).fill(0).map(() => `
                    <div class="wta-skeleton-card">
                        <div class="wta-skeleton-img wta-skeleton-shimmer"></div>
                        <div class="wta-skeleton-title wta-skeleton-shimmer"></div>
                        <div class="wta-skeleton-meta wta-skeleton-shimmer"></div>
                        <div class="wta-skeleton-price wta-skeleton-shimmer"></div>
                        <div class="wta-skeleton-button wta-skeleton-shimmer"></div>
                    </div>
                `).join('')}
            </div>
        `;

        $grid.addClass('loading').html(skeletonHtml);

        // AJAX call
        $.ajax({
            url: wta_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'wta_get_filtered_products',
                nonce: wta_vars.nonce,
                main_category: mainCategory,
                category: filterCategories // Send as array
            },
            success: function(response) {
                if (response.success) {
                    $grid.html(response.data.html);
                    $grid.removeClass('loading');
                    // Re-apply selections if needed
                    updateSelectionUI();
                } else {
                    console.error('WTA Filter Error:', response.data.message);
                    $grid.removeClass('loading');
                }
            },
            error: function() {
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

    /**
     * Info Modal Logic
     */
    function setupInfoModal() {
        const $modal = $('#wta-info-modal');
        if (!$modal.length) return;

        $(document).on('click', '.wta-info-trigger', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $modal.addClass('is-active');
            $('body').addClass('wta-modal-open');
        });

        $(document).on('click', '.wta-modal-close, .wta-modal-overlay', function(e) {
            // If click inside content (but not the close button), do nothing
            if ($(e.target).closest('.wta-modal-content').length && !$(e.target).is('.wta-modal-close')) {
                return;
            }
            $modal.removeClass('is-active');
            $('body').removeClass('wta-modal-open');
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $modal.hasClass('is-active')) {
                $modal.removeClass('is-active');
                $('body').removeClass('wta-modal-open');
            }
        });
    }

    // Events
    $(document).on('click', '.wta-product-card', toggleProductSelection);
    $(document).on('click', '.wta-filter-button', handleFilterClick);
    $(document).on('click', '.wta-bulk-add-button', handleBulkAdd);

    // Initial Load
    loadSelections();
    setupInfoModal();
});
