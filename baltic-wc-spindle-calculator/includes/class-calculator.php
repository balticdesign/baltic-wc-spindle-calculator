<?php

class SpindleSpacingCalculator {
    
    private $available_diameters = array(32, 41, 45, 50, 55);
    
    // Divisors table - the core of the calculation
    private $divisors = [
        32 => ['stairs' => 172, 'length' => 127],
        41 => ['stairs' => 180, 'length' => 140],
        45 => ['stairs' => 187, 'length' => 148],
        50 => ['stairs' => 196, 'length' => 150],
        55 => ['stairs' => 196, 'length' => 158],
        'metal' => ['stairs' => 141, 'length' => 112]
    ];

    public function __construct() {
        add_shortcode('spindle_calculator', array($this, 'render_calculator'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_calculate_spindles', array($this, 'ajax_calculate'));
        add_action('wp_ajax_nopriv_calculate_spindles', array($this, 'ajax_calculate'));
        
        // Query Monitor debugging
        if (defined('QM_VERSION')) {
            do_action('qm/debug', 'SSC: Spindle Calculator initialized');
            do_action('qm/debug', 'SSC: Available diameters: ' . json_encode($this->available_diameters));
            do_action('qm/debug', 'SSC: Divisors table: ' . json_encode($this->divisors));
        }
    }
    
    public function enqueue_assets() {
        global $post;
        
        $should_enqueue = false;
        
        // Check if we should enqueue assets
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'spindle_calculator')) {
            $should_enqueue = true;
        } elseif (is_product()) {
            // Also enqueue on product pages
            $should_enqueue = true;
        }
        
        if (defined('QM_VERSION')) {
            do_action('qm/debug', 'SSC: Should enqueue assets: ' . ($should_enqueue ? 'YES' : 'NO'));
            if ($post) {
                do_action('qm/debug', 'SSC: Current post ID: ' . $post->ID);
            }
        }
        
        if ($should_enqueue) {
            // Use file modification time as version to force refresh on changes
            $css_version = file_exists(SSC_PLUGIN_PATH . 'assets/css/spin_calc_v1.css') 
                ? filemtime(SSC_PLUGIN_PATH . 'assets/css/spin_calc_v1.css') 
                : '1.2.0';
                
            $js_version = file_exists(SSC_PLUGIN_PATH . 'assets/js/spin_calc_v1.js') 
                ? filemtime(SSC_PLUGIN_PATH . 'assets/js/spin_calc_v1.js') 
                : '1.2.0';
            
            wp_enqueue_style(
                'ssc-calculator', 
                SSC_PLUGIN_URL . 'assets/css/spin_calc_v1.css', 
                array(), 
                $css_version
            );
            
            wp_enqueue_script(
                'ssc-calculator', 
                SSC_PLUGIN_URL . 'assets/js/spin_calc_v1.js', 
                array('jquery'), 
                $js_version, 
                true
            );
                    
            wp_localize_script('ssc-calculator', 'ssc_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ssc_calculator_nonce')
            ));
            
            if (defined('QM_VERSION')) {
                do_action('qm/debug', 'SSC: Assets enqueued successfully');
                do_action('qm/debug', 'SSC: CSS version: ' . $css_version);
                do_action('qm/debug', 'SSC: JS version: ' . $js_version);
            }
        }
    }
    
    public function render_calculator($atts) {
        $product_id = get_the_ID();
        $atts = shortcode_atts(array(
            'product_id' => $product_id,
            'display' => 'accordion' // 'accordion' or 'modal'
        ), $atts);
        
        // Only allow calculator on 'spindles' or 'metal' products
        if (!has_term(['spindles', 'metal'], 'product_cat', $product_id)) {
            return ''; // Don't render calculator at all
        }

        $is_metal = has_term('metal', 'product_cat', $product_id);
        $spindle_diameter = $this->get_spindle_diameter($product_id);
        $display_mode = $atts['display'];
        
        ob_start();
        
        if ($display_mode === 'modal') {
            // Render modal button and modal
            $this->render_modal_version($is_metal, $spindle_diameter);
        } else {
            // Render accordion version (default)
            $this->render_accordion_version($is_metal, $spindle_diameter);
        }
        
        return ob_get_clean();
    }
    
    private function render_accordion_version($is_metal, $spindle_diameter) {
        ?>
        <div class="ssc-calculator">
            <h4 class="ssc-acc-header" role="button"
                aria-expanded="false"
                aria-controls="ssc-acc-panel"
                tabindex="0">
                Spindle Calculator
                <svg class="ssc-acc-chevron" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2"/>
                </svg>
            </h4>
            <div id="ssc-acc-panel" class="ssc-acc-panel" style="display:none;">
                <?php $this->render_calculator_content($is_metal, $spindle_diameter); ?>
            </div>
        </div>
        <?php
    }
    
    private function render_modal_version($is_metal, $spindle_diameter) {
        ?>
        <div class="ssc-calculator ssc-modal-version">
            <button type="button" class="button ssc-modal-trigger">
                Spindle Calculator
            </button>
            
            <div class="ssc-modal-overlay" style="display: none;">
                <div class="ssc-modal-container" role="dialog" aria-modal="true" aria-labelledby="ssc-modal-title">
                    <div class="ssc-modal-header">
                        <h3 id="ssc-modal-title">Spindle Calculator</h3>
                        <button type="button" class="ssc-modal-close" aria-label="Close calculator">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    <div class="ssc-modal-body">
                        <?php $this->render_calculator_content($is_metal, $spindle_diameter); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function render_calculator_content($is_metal, $spindle_diameter) {
        ?>
        <div class="ssc-calculator-wrapper">
            <form id="ssc-calculator-form">
                <?php if ($is_metal): ?>
                    <input type="hidden" id="ssc-is-metal" value="1">
                    <input type="hidden" id="ssc-spindle-diameter" value="metal">
                <?php else: ?>
                    <div class="ssc-form-group">
                        <label for="ssc-spindle-diameter">Spindle Width (mm)</label>
                        <?php if ($spindle_diameter): ?>
                            <input type="text" id="ssc-spindle-diameter" value="<?php echo esc_attr($spindle_diameter); ?>" readonly>
                        <?php else: ?>
                            <select id="ssc-spindle-diameter" class="ssc-disabled-select" name="spindle_diameter" disabled>
                                <option value="">Width / Diameter</option>
                                <?php foreach ($this->available_diameters as $diameter): ?>
                                    <option value="<?php echo $diameter; ?>"><?php echo $diameter; ?>mm</option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                        <div id="ssc-both-sides-toggle" class="ssc-form-group">
                            <label style="font-size:12px">
                                <input type="checkbox" id="ssc-both-sides" name="both_sides">
                                Spindles required on both sides?
                            </label>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="ssc-form-group">
                    <label>Stairs or Landing?</label>
                    <div class="ssc-image-toggle-group">
                        <label class="ssc-image-toggle">
                            <input type="radio" name="calc_type" value="stairs" checked>
                            <img src="/wp-content/plugins/baltic-wc-spindle-calculator/assets/img/stairs_icon.svg" alt="Stair Spindles" />
                            <span>Stair Spindles</span>
                        </label>
                        <label class="ssc-image-toggle">
                            <input type="radio" name="calc_type" value="length">
                            <img src="/wp-content/plugins/baltic-wc-spindle-calculator/assets/img/landing_icon.svg" alt="Landing Spindles" />
                            <span>Landing Spindles</span>
                        </label>
                    </div>
                </div>
                
                <div class="ssc-form-group" id="ssc-length-input">
                    <label for="ssc-total-length">Total Length (mm)</label>
                    <input type="number" id="ssc-total-length" name="total_length" min="0" step="1">
                </div>
            </form>
            
            <div class="ssc-results-wrapper">
                <div id="ssc-results" class="ssc-results">
                    <label for="ssc-spindle-no">Spindle No.</label>
                    <div class="ssc-results-content"></div>
                </div>
                <div class="ssc-button-row" style="display: none;">
                    <button type="button" class="button button-primary ssc-update-qty">Update Quantity</button>
                </div>
            </div>
        </div>
        <small class="ssc-acc-footnote">Standard stair going: 240mm per tread. Estimate based on a rake of 42 degrees</small>
        <?php
    }
    
    private function get_spindle_diameter($product_id) {
        if (!$product_id || !function_exists('wc_get_product')) {
            return null;
        }
        
        $product = wc_get_product($product_id);
        if (!$product) {
            return null;
        }
        
        // Check if product has pa_diameter attribute
        $diameter = $product->get_attribute('pa_diameter');
        
        // Extract numeric value from diameter
        if ($diameter) {
            preg_match('/\d+/', $diameter, $matches);
            return isset($matches[0]) ? $matches[0] : null;
        }
        
        return null;
    }
    
    public function ajax_calculate() {
        if (defined('QM_VERSION')) {
            do_action('qm/debug', 'SSC AJAX: Request received');
            do_action('qm/debug', 'SSC AJAX: POST data: ' . json_encode($_POST));
        }

        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ssc_calculator_nonce')) {
            if (defined('QM_VERSION')) {
                do_action('qm/error', 'SSC AJAX: Nonce verification failed');
            }
            wp_send_json_error('Invalid nonce');
            return;
        }

        // Get inputs
        $spindle_diameter = isset($_POST['spindle_diameter']) ? 
            (is_numeric($_POST['spindle_diameter']) ? intval($_POST['spindle_diameter']) : 'metal') : 
            null;
        $calc_type = sanitize_text_field($_POST['calc_type']);
        $total_length = floatval($_POST['total_length']);
        $both_sides = isset($_POST['both_sides']) && $_POST['both_sides'] == '1';

        if (defined('QM_VERSION')) {
            do_action('qm/debug', 'SSC AJAX: Diameter: ' . $spindle_diameter . ', Type: ' . $calc_type . ', Length: ' . $total_length);
        }

        // Calculate spindles
        $result = $this->calculate_spindles($spindle_diameter, $total_length, $calc_type, $both_sides);

        if (defined('QM_VERSION')) {
            do_action('qm/debug', 'SSC AJAX: Calculation result: ' . json_encode($result));
        }

        wp_send_json_success($result);
    }
    
    /**
     * Simple spindle calculation based on divisors table
     * 
     * @param mixed $diameter - spindle diameter (32, 41, 45, 50, 55) or 'metal'
     * @param float $length - total length in mm
     * @param string $type - 'stairs' or 'length' (landing)
     * @param bool $both_sides - whether spindles needed on both sides (stairs only)
     * @return array
     */
    private function calculate_spindles($diameter, $length, $type = 'stairs', $both_sides = false) {
        // Get the divisor from our table
        $divisor = null;
        
        if (isset($this->divisors[$diameter][$type])) {
            $divisor = $this->divisors[$diameter][$type];
        } else {
            // Use default if diameter not found
            $default_divisors = ['stairs' => 180, 'length' => 145];
            $divisor = $default_divisors[$type];
            
            if (defined('QM_VERSION')) {
                do_action('qm/debug', 'SSC: Using default divisor for unknown diameter');
            }
        }
        
        if (defined('QM_VERSION')) {
            do_action('qm/debug', 'SSC Calc: Diameter: ' . $diameter . ', Type: ' . $type . ', Length: ' . $length);
            do_action('qm/debug', 'SSC Calc: Using divisor: ' . $divisor);
            do_action('qm/debug', 'SSC Calc: Both sides: ' . ($both_sides ? 'YES' : 'NO'));
        }
        
        // Simple calculation: divide length by divisor and round up
        $num_spindles = ceil($length / $divisor);
        
        // Apply both sides multiplier for stairs if needed
        if ($type === 'stairs' && $both_sides) {
            $num_spindles = $num_spindles * 2;
        }
        
        // Ensure at least 1 spindle
        if ($num_spindles < 1) {
            $num_spindles = 1;
        }
        
        // Calculate actual spacing for information purposes
        $actual_spacing = $num_spindles > 0 ? round($length / $num_spindles, 1) : 0;
        
        if (defined('QM_VERSION')) {
            do_action('qm/debug', 'SSC Calc: Calculated spindles: ' . $num_spindles);
            do_action('qm/debug', 'SSC Calc: Actual spacing: ' . $actual_spacing);
        }
        
        return [
            'num_spindles' => $num_spindles,
            'spindle_diameter' => $diameter,
            'total_length' => round($length, 1),
            'divisor_used' => $divisor,
            'actual_spacing' => $actual_spacing,
            'calculation_type' => $type,
            'both_sides' => $both_sides
        ];
    }
}