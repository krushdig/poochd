=== Listing Payments for WP Job Manager ===
Contributors: astoundify
Requires at least: 4.7.0
Tested up to: 4.9.0
Stable tag: 2.2.0
WC requires at least: 3.0.0
WC tested up to: 3.2.0
License: GNU General Public License v3.0

Add paid listing functionality via WooCommerce. Create listing packages as WooCommerce products with their own price, listing duration, listing limit, featured status, and other attributes. Sell them via your store or during the listing submission process. 

= Documentation =

Usage instructions for this plugin can be found on our documentation: [http://docs.astoundify.com/](http://docs.astoundify.com/).

= Support Policy =

Please contact https://astoundify.com/account/new-ticket/ for technical support regarding the plugin. We are partnered with and highly recommend WP Curve (https://astoundify.com/go/wpcurve) Envato Studio (https://astoundify.com/go/envato-studio/) or Codeable (https://astoundify.com/go/codeable/) if you need help customizing your website.

== Installation ==

To install this plugin, please refer to the guide here: [http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation)

== Other Notes ==

This plugin is a fork from:

**WP Job Manager - WC Paid Listing**
Copyright: 2015 Automattic

== Changelog ==

= 2.2.0: October 11, 2017 =

* New: WooCommerce 3.2.0 compatibility.
* Fix: Avoid querying packages on each page load.

= 2.1.1: August 30, 2017 =

* Fix: Use proper arguments in wc_get_products().
* Fix: Choose package on "Payment required before listing details" flow.
* Fix: Add WooCommerce 3.2 compatibility headers.

= 2.1.0: August 15, 2017 =

* New: Listing packages can be switched when editing an existing listing.
* New: Set the number of times a user can purchase a specific package.
* New: Register account during purchase in WooCommerce.
* New: Remove extra billing fields for free listing packages.

= 2.0.1: July 5, 2017 =

* Fix: Ensure chosen package exists before goinng to the next step.
* Fix: Append package name to submission page when using choose_package URL.
* Fix: Use ID parameter when updating a listing.

= 2.0.0: June 21, 2017 =

* New: Listing Payments for WP Job Manager. 
       Update project structure, coding standards, and overall code improvements.

= 1.1.1: April 19, 2017 =

* Fix: WooCommerce 3.0.x compatibility fixes.

= 1.1.0: April 11, 2017 = 

* New: Link directly to the submission page with a chosen package. Append ?chosen_package=123 to the listing submission page.
* Fix: Ignore set product visibility when querying package selection on submission page.
* Fix: WooCommerce 3.0+ compatibility.
* Fix: Update README

= 1.0.0: March 2, 2017 =

* First release.
* Fork from WP Job Manager - WC Paid Listing.
