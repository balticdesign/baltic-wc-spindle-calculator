jQuery(document).ready(function($) {
    console.log('SSC: Spindle Calculator JS loaded');
    
    var calculationTimeout;
    
    // WooCommerce variations integration
    const $form = $('form.variations_form');
    const $calcField = $('#ssc-spindle-diameter');

    // Accordion functionality
    $('.ssc-calculator').on('click keydown', '.ssc-acc-header', function (e) {
        // Trigger on click, or Enter/Space
        if (e.type === 'click' || (e.type === 'keydown' && (e.key === 'Enter' || e.key === ' '))) {
            e.preventDefault();

            const $header = $(this);
            const $panel = $('#ssc-acc-panel');
            const expanded = $header.attr('aria-expanded') === 'true';

            $panel.stop(true, true).slideToggle(200);
            $header.attr('aria-expanded', expanded ? 'false' : 'true');
        }
    });

    // Modal functionality
    $('.ssc-calculator').on('click', '.ssc-modal-trigger', function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const $modal = $button.siblings('.ssc-modal-overlay');
        const $diameter = $('#ssc-spindle-diameter');
        const isMetal = $('#ssc-is-metal').val() === '1';
        
        // Check if width/diameter is selected (unless it's metal or auto-filled)
        if (!isMetal && (!$diameter.val() || $diameter.val() === '')) {
            // Show warning message
            const $warning = $('<div class="ssc-width-warning">Please select a spindle width first</div>');
            
            // Remove any existing warnings
            $('.ssc-width-warning').remove();
            
            // Insert warning after button
            $button.after($warning);
            
            // Fade in the warning
            $warning.hide().fadeIn(200);
            
            // Highlight the variations form
            const $variationsForm = $('form.variations_form');
            if ($variationsForm.length) {
                $variationsForm.addClass('ssc-highlight-needed');
                setTimeout(function() {
                    $variationsForm.removeClass('ssc-highlight-needed');
                }, 2000);
            }
            
            // Auto-remove warning after 4 seconds
            setTimeout(function() {
                $warning.fadeOut(200, function() {
                    $(this).remove();
                });
            }, 4000);
            
            return;
        }
        
        // Width is selected, open modal
        $modal.fadeIn(200);
        $('body').css('overflow', 'hidden');
        
        // Focus trap
        $modal.find('.ssc-modal-close').focus();
    });

    $('.ssc-calculator').on('click', '.ssc-modal-close', function(e) {
        e.preventDefault();
        closeModal();
    });

    $('.ssc-calculator').on('click', '.ssc-modal-overlay', function(e) {
        // Close when clicking outside the modal container
        if ($(e.target).is('.ssc-modal-overlay')) {
            closeModal();
        }
    });

    // Close modal with Escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('.ssc-modal-overlay:visible').length) {
            closeModal();
        }
    });

    function closeModal() {
        const $modal = $('.ssc-modal-overlay:visible');
        $modal.fadeOut(200);
        $('body').css('overflow', '');
        
        // Return focus to trigger button
        $('.ssc-modal-trigger').focus();
    }
    
    console.log('SSC: Calculator field found:', $calcField.length > 0);
    console.log('SSC: Variations form found:', $form.length > 0);
    
    // Helper function to check if modal button should be enabled
    function updateModalButtonState() {
        const $modalButton = $('.ssc-modal-trigger');
        const $diameter = $('#ssc-spindle-diameter');
        const isMetal = $('#ssc-is-metal').val() === '1';
        
        if ($modalButton.length) {
            if (isMetal || ($diameter.val() && $diameter.val() !== '')) {
                $modalButton.removeClass('ssc-disabled-state').addClass('ssc-enabled-state');
            } else {
                $modalButton.removeClass('ssc-enabled-state').addClass('ssc-disabled-state');
            }
        }
    }
    
    // Initialize button state on load
    updateModalButtonState();
    
    if ($form.length > 0) {
        // Get all variations data
        const productData = $form.data('product_variations');
        console.log('SSC: Product variations data:', productData);
        
        // Helper: Extract numeric diameter from attribute value
        const getDiameter = (variation) => {
            const diameterAttr = variation.attributes['attribute_pa_sd_width'] || '';
            const match = diameterAttr.match(/\d+/);
            console.log('SSC: Extracting diameter from:', diameterAttr, '→', match ? match[0] : 'not found');
            return match ? match[0] : '';
        };
        
        // 1. On page load – Check how many variations are available
        if (productData && productData.length === 1) {
            console.log('SSC: Single variation found, auto-selecting');
            const diameter = getDiameter(productData[0]);
            if (diameter) {
                console.log('SSC: Setting diameter to:', diameter);
                $calcField.val(diameter).trigger('change');
                // Update button state after auto-selection
                updateModalButtonState();
            }
        } else {
            console.log('SSC: Multiple variations or no data, waiting for selection');
        }
        
        // 2. On variation selection
        $form.on('found_variation', function(event, variation) {
            console.log('SSC: Variation selected:', variation);
            const diameterAttr = variation.attributes['attribute_pa_sd_width'] || '';
            const match = diameterAttr.match(/\d+/);
            const selectedDiameter = match ? match[0] : '';
            
            console.log('SSC: Selected diameter:', selectedDiameter);
            
            if (selectedDiameter && $calcField.find('option[value="' + selectedDiameter + '"]').length) {
                console.log('SSC: Updating calculator with diameter:', selectedDiameter);
                $calcField.val(selectedDiameter).trigger('change');
                
                // Remove any width warnings when width is selected
                $('.ssc-width-warning').fadeOut(200, function() {
                    $(this).remove();
                });
                
                // Update modal button state
                updateModalButtonState();
            } else {
                console.log('SSC: Diameter not found in calculator options:', selectedDiameter);
            }
        });
        
        // 3. Reset on "clear" click
        $form.on('reset_data', function() {
            console.log('SSC: Variation reset');
            $calcField.val('').trigger('change');
            
            // Update modal button state
            updateModalButtonState();
        });
    }
    
    // Toggle between stairs and length input
    $('input[name="calc_type"]').on('change', function() {
        console.log('SSC: Calc type changed to:', $(this).val());
        if ($(this).val() === 'stairs') {
            $('#ssc-stairs-input').show();
            $('#ssc-both-sides-toggle').show();
            $('#ssc-length-input').hide();
            $('#ssc-total-length').val('');
        } else {
            $('#ssc-stairs-input').hide();
            $('#ssc-both-sides-toggle').hide();
            $('#ssc-length-input').show();
            $('#ssc-floor-height').val('');
        }
        // Clear results when switching
        $('#ssc-results .ssc-button-row').hide();
    });
    
    // Auto-calculate on input changes
    $('#ssc-spindle-diameter, #ssc-floor-height, #ssc-both-sides, #ssc-total-length').on('input change', function() {
        console.log('SSC: Input changed:', this.id, '=', $(this).val());
        
        // Update button state when diameter changes
        if (this.id === 'ssc-spindle-diameter') {
            updateModalButtonState();
        }
        
        clearTimeout(calculationTimeout);
        calculationTimeout = setTimeout(function() {
            performCalculation();
        }, 500); // Debounce for 500ms
    });
    
    // Also calculate when radio button changes
    $('input[name="calc_type"]').on('change', function() {
        performCalculation();
    });
    
    function performCalculation() {
        var spindleDiameter = $('#ssc-spindle-diameter').val();
        var calcType = $('input[name="calc_type"]:checked').val();
        var hasRequiredInput = false;
        
        console.log('SSC: Performing calculation - Diameter:', spindleDiameter, 'Type:', calcType);
        
        const isMetal = $('#ssc-is-metal').val() === '1';

        if (!spindleDiameter && !isMetal) {
            console.log('SSC: No diameter selected, hiding results');
            $('#ssc-results .ssc-button-row').hide();
            return;
        }

        // Provide a fallback value for metal so PHP knows what to do
        if (isMetal) {
            spindleDiameter = 'metal';
        }
        
        if ((calcType === 'stairs' || calcType === 'length') && $('#ssc-total-length').val()) {
            hasRequiredInput = true;
        }
        
        if (!hasRequiredInput) {
            console.log('SSC: Missing required input, hiding results');
            $('#ssc-results .ssc-button-row').hide();
            return;
        }
        
        var $results = $('#ssc-results');
        var $resultsContent = $results.find('.ssc-results-content');
        
        // Show loading state
        $resultsContent.html('<div style="text-align: center;">Calculating...</div>');
        $results.show();
        
        // Prepare data
        var data = {
            action: 'calculate_spindles',
            nonce: ssc_ajax.nonce,
            spindle_diameter: spindleDiameter,
            calc_type: calcType,
            both_sides: $('#ssc-both-sides').is(':checked') ? 1 : 0
        };
        
        if (calcType === 'stairs') {
            data.total_length = $('#ssc-total-length').val();
        } else {
            data.total_length = $('#ssc-total-length').val();
        }
        
        console.log('SSC: Sending AJAX request with data:', data);
        console.log('SSC: AJAX URL:', ssc_ajax.ajax_url);
       
        // Make AJAX request
        $.post(ssc_ajax.ajax_url, data, function(response) {
            console.log('SSC: AJAX response received:', response);

            if (response.success) {
                var result = response.data;

                // Inject just the number into the existing content div
                $resultsContent
                    .text(result.num_spindles)
                    .fadeIn(); // or .show()

                // Show the update quantity button
                $('.ssc-button-row').fadeIn();

                // Bind the update quantity button (clean and safe)
                $('.ssc-update-qty').off('click').on('click', function () {
                    const qty = parseInt(result.num_spindles, 10);
                    const $qtyInput = $('form.variations_form input.qty');

                    if ($qtyInput.length && !isNaN(qty)) {
                        $qtyInput.val(qty).trigger('change');
                        
                        // Close modal if it's open
                        if ($('.ssc-modal-overlay:visible').length) {
                            closeModal();
                        }
                    }
                });

            } else {
                console.error('SSC: AJAX error:', response);
                $resultsContent.html('<div class="ssc-error">Error calculating spindles. Please try again.</div>');
            }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            console.error('SSC: AJAX failed:', textStatus, errorThrown);
            console.error('SSC: Response:', jqXHR.responseText);
            $resultsContent.html('<div class="ssc-error">Network error. Please try again.</div>');
        });
    }
});