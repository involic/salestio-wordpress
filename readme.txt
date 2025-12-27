=== Salestio ===
Contributors: salestio
Tags: woocommerce, amazon, ebay, etsy, marketplace
Requires at least: 5.8
Tested up to: 6.9
Stable tag: 1.2.1
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect WooCommerce to Salestio to link your store with Amazon, eBay, Etsy, and other marketplaces.

== Description ==
Salestio connects WooCommerce with online marketplaces such as Amazon, eBay, and Etsy. Use your Salestio account to manage listings, inventory, and orders across channels.

= External Service =
This plugin connects to the Salestio service at https://app.salest.io/.

It sends your store URL, WordPress version, store ID, a timestamp, and an HMAC signature to authenticate requests. Data is sent when you connect the plugin, open the Salestio admin screens, or deactivate the plugin. The service is required for the plugin to function.

Terms of Service: https://salest.io/terms
Privacy Policy: https://salest.io/privacy

= Privacy =
The plugin stores your Salestio store ID and merchant secret in the WordPress options table. It does not store customer data locally. The plugin adds recommended privacy policy text under Settings -> Privacy.

== Installation ==
1. Upload the `salestio` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to Salestio in the WooCommerce menu and click "Connect Salestio".
4. Open the Salestio app in a new tab to finish setup.

== Frequently Asked Questions ==
= Do I need a Salestio account? =
Yes. The plugin connects your store to the Salestio service.

= Does this plugin work without WooCommerce? =
No. WooCommerce must be installed and active.

= Where is data stored? =
The plugin stores your Salestio store ID and merchant secret in the WordPress options table.

== Changelog ==
= 1.2.1 =
* Fixed sync workflow for public repository.

= 1.1.0 =
* Initial release.

== Upgrade Notice ==
= 1.2.1 =
Fixed sync workflow for public repository.

= 1.1.0 =
Initial release.
