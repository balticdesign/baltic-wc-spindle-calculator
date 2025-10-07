=== WC Spindle Calculator by Baltic Digital ===
Contributors: Dan Cotugno-Cregin
Tags: woocommerce, calculator, spindles, staircase, balustrading
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Calculate spindle spacing for staircases and balustrading with WooCommerce integration.

== Description ==

WC Spindle Calculator helps customers calculate the correct number of spindles needed for their staircase or landing projects. The calculator integrates seamlessly with WooCommerce product variations and provides real-time calculations based on spindle diameter and measurement specifications.

= Features =

* Two display modes: Accordion (collapsible) and Modal (popup)
* Automatic integration with WooCommerce product variations
* Support for both wooden spindles (32mm, 41mm, 45mm, 50mm, 55mm) and metal spindles
* Calculations for both stairs and landings
* Option for double-sided stair spindles
* One-click quantity update to cart
* Fully responsive design
* Accessible with ARIA attributes and keyboard navigation
* Smart validation with helpful user guidance

= Display Modes =

**Accordion Mode (Default)**
Expandable/collapsible panel that sits inline with product content.

**Modal Mode (Recommended)**
Professional popup dialog with:
* Click outside to close
* Escape key support
* Smart validation - warns if spindle width not selected
* Visual feedback with variation selector highlighting
* Auto-dismiss warnings
* Body scroll lock when open

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/baltic-wc-spindle-calculator/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add the shortcode to your WooCommerce product pages

== Usage ==

= Basic Shortcode =

Accordion version (default):
`[spindle_calculator]`

Or explicitly:
`[spindle_calculator display="accordion"]`

= Modal Version =

For a better user experience with validation:
`[spindle_calculator display="modal"]`

= In PHP Templates =

`<?php echo do_shortcode('[spindle_calculator display="modal"]'); ?>`

= Product Category Requirements =

The calculator only displays on products in the following categories:
* spindles
* metal

== Modal Validation Behavior ==

When using the modal version, the calculator provides smart validation:

**Before Width Selection:**
* Button appears at 60% opacity (visual hint)
* Clicking shows warning: "Please select a spindle width first"
* Variation selector pulses for 2 seconds to guide user
* Warning auto-dismisses after 4 seconds

**After Width Selection:**
* Button becomes fully opaque (100%)
* Warning disappears instantly
* Modal opens normally when clicked

**Special Cases:**
* Metal products: Button always enabled (no width needed)
* Single variations: Width auto-selected, button enabled immediately
* Pre-filled widths: Button enabled from start

== Calculation Method ==

The calculator uses a divisor-based calculation system:

**Wooden Spindles:**
* 32mm: Stairs divisor 172, Landing divisor 127
* 41mm: Stairs divisor 180, Landing divisor 148
* 45mm: Stairs divisor 187, Landing divisor 160
* 50mm: Stairs divisor 196, Landing divisor 172
* 55mm: Stairs divisor 196, Landing divisor 180

**Metal Spindles:**
* Stairs divisor: 141
* Landing divisor: 112

Formula: `Number of spindles = ceiling(Total Length / Divisor)`

For double-sided stairs, the result is multiplied by 2.

== Frequently Asked Questions ==

= Why doesn't the calculator appear on my product? =

Ensure your product is in either the 'spindles' or 'metal' product category.

= Can I customize the divisor values? =

Currently, divisor values are hardcoded in the plugin. Future versions may include an admin interface for customization.

= Does this work with variable products? =

Yes! The calculator automatically detects and integrates with WooCommerce product variations, specifically the `pa_diameter` or `pa_sd_width` attributes.

= What happens if I click the modal button without selecting a width? =

The calculator will show a helpful warning message and highlight the variation selector to guide you. The modal won't open until a width is selected.

= Can I use both accordion and modal on the same site? =

Yes, you can use different display modes on different pages by specifying the `display` attribute in each shortcode.

== Screenshots ==

1. Accordion calculator in collapsed state
2. Accordion calculator expanded with calculation
3. Modal button with validation warning
4. Modal calculator open with results
5. Mobile responsive layout

== Changelog ==

= 1.2.0 =
* Added modal display mode
* Implemented smart validation for modal version
* Added button state management (disabled/enabled visual feedback)
* Improved accessibility with ARIA attributes
* Enhanced keyboard navigation
* Added auto-close on quantity update
* Improved responsive design for tablets and mobile
* Better integration with WooCommerce variations

= 1.1.0 =
* Added accordion display mode
* Improved WooCommerce variation integration
* Added Query Monitor debugging support
* Enhanced responsive design

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.2.0 =
Major update with new modal display mode and smart validation. Improved user experience and accessibility.

== Technical Details ==

= Browser Compatibility =
* All modern browsers (Chrome, Firefox, Safari, Edge)
* Mobile responsive
* Touch-friendly
* Keyboard accessible

= Customization =

The modal appearance can be customized via CSS:
* `.ssc-modal-overlay` - Background overlay
* `.ssc-modal-container` - Modal box
* `.ssc-modal-header` - Header section
* `.ssc-modal-body` - Content area
* `.ssc-modal-trigger` - Button that opens modal
* `.ssc-width-warning` - Validation warning message

= Filters & Hooks =

Currently, the plugin does not expose filters or hooks. Future versions may include action hooks for extensibility.

== Support ==

For bug reports and feature requests, please visit:
https://github.com/yourusername/baltic-wc-spindle-calculator

== Credits ==

Developed by Dan Cotugno-Cregin for Baltic Digital.
