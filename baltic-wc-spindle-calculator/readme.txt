=== Spindle Spacing Calculator ===
Contributors: yourname
Tags: calculator, spindles, staircase, balustrade, woocommerce
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Calculate spindle spacing for staircases and balustrading with UK building regulations compliance.

== Description ==

The Spindle Spacing Calculator plugin provides an easy-to-use calculator for determining the correct number of spindles needed for staircases and balustrading projects.

Features:
* Calculate spindle requirements based on floor height (for stairs) or direct length (for landings/balustrades)
* Automatic detection of spindle diameter from WooCommerce product variations
* UK Building Regulations compliance checking (99mm maximum gap)
* Customizable spindle ratios via ACF settings
* AJAX-powered calculations without page reload
* Responsive design

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/spindle-spacing-calculator` directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the `[spindle_calculator]` shortcode to display the calculator
4. Optional: Pass product_id to auto-detect spindle diameter: `[spindle_calculator product_id="123"]`

== Usage ==

Basic usage:
`[spindle_calculator]`

With WooCommerce product:
`[spindle_calculator product_id="123"]`

The calculator will automatically detect the spindle diameter from the product's `pa_diameter` attribute.

== Configuration ==

If ACF Pro is installed, you can configure spindle ratios in Settings > Spindle Calculator.

Default ratios:
* 32mm: 3.5
* 41mm: 3.0
* 50mm: 2.5
* 55mm: 2.3
* 62mm: 2.0

== Changelog ==

= 1.0.0 =
* Initial release