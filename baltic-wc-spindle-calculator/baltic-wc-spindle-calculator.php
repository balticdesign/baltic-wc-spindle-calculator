<?php
 /**
 * Plugin Name: WC Spindle Calculator by Baltic Digital
 * Description: Calculate spindle spacing for staircases and balustrading
 * Version: 1.1.0
 * Author: Dan Cotugno-Cregin
 * Text Domain: spindle-spacing-calculator
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SSC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SSC_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Include main calculator class
require_once SSC_PLUGIN_PATH . 'includes/class-calculator.php';

// Initialize plugin
add_action('plugins_loaded', function() {
    new SpindleSpacingCalculator();
});