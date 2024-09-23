=== Out of Stock Message for WooCommerce ===
Contributors: coderstime, lincolndu
Donate link: https://buymeacoffee.com/coderstime
Tags: out of stock, sold out, badge, stock, stock alert email, stock alert
Requires at least: 4.9 or higher
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

Out of Stock Message for WooCommerce is an official plugin maintained by the Coderstime that add an extra feature on the “woocommerce inventory settings” option on the woocommerce.

== Description ==

Out of Stock Message for WooCommerce plugin is used to write out of stock custom message with different background and text color. This stock out message can be set on woocommerce specific product or all global products. You can inform your customer product stock status in product details page. How many product on your stock will show on product page.

You can change default stock out status message and change colors with where message will be shown.

FEATURES
Allows specific product message.
Allows global message from WooCommerce inventory setting.
Sold out badge for product loop and single product
Can customize message showing position in product page.
Admin will receive when a product stock out.
In Stock product quantity message on product page


### USEFULL LINKS:
> * [Live Demo Pro Version](https://coders-time.com/product/out-of-stock-pro-demo/)
> * [Live Demo Free Version](https://wordpress.org/plugins/wc-out-of-stock-message/?preview=1)
> * [Video Tutorial](https://youtu.be/guh-hkrJF_E)
> * [Documentation](https://coders-time.com/out-of-stock-documentation/)

= How it works ? =
[youtube https://youtu.be/guh-hkrJF_E]

Why does this plugin?
This plugin allows you to supply a literal message for stock out product. 

Default "Out of Stock" Message
1. Go to WooCommerce > Settings > Products > Inventory
2. Type your message on "Out of Stock Message" field
3. Save Changes

Individual "Out of Stock" Message
1. Go to Add/Edit product panel
2. Open Inventory settings of product panel
3. On Stock Status, check 'Out of Stock'
4. The Out-of-Stock Note field is displayed. Type your note/message in input field.
5. Click Publish or Update


For Developers
By default, you don\'t have to modify any code of template file. Because the plugin automatically displays out of stock note right after product title in single product page (as seen above).
If you want to display the out of stock note at other places, use the codes below.
Getting individual note value: get_post_meta($post->ID, \'_out_of_stock_note\', true);
Getting global note value: get_option(\'woocommerce_out_of_stock_note\');

Use this shortcode to output stock out message 

`
	[wcosm_stockout_msg][/wcosm_stockout_msg]
`

== Installation ==

= Minimum Requirements =

* PHP 7.4 or greater is required (PHP 8.0 or greater is recommended)
* MySQL 5.6 or greater

= Automatic installation =
Automatic installation is the easiest option -- WordPress will handle the file transfer, and you won’t need to leave your web browser. To do an automatic install of Out of Stock Message, log in to your WordPress dashboard, navigate to the Plugins menu, and click “Add New.”

In the search field type “Out of Stock Message,” then click “Search Plugins.” Once you’ve found us,  you can view details about it such as the point release, rating, and description. Most importantly of course, you can install it by! Click “Install Now,” and WordPress will take it from there.

== Manual installation ==
Manual installation method requires downloading the Out of Stock Message plugin and uploading it to your web server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](https://wordpress.org/support/article/managing-plugins/#manual-plugin-installation).

1. Upload this plugin to the /wp-content/plugins/ directory.
2. Activate the plugin through the Plugins menu in WordPress.
3. Go to WooCommerce > Settings > Products > Inventory. Then type your note on \"Out of Stock Note\" field. Then Save your setting.
4. Go to Add/Edit product panel. Open Inventory setting of product data, select \"Out of Stock\" on \"Stock Status\" field. Then check global note or set individual note in \"Out of Stock Note\" field.

== Sold out Badge == 
We bring sold out badge on product image corner in 1.0.5 version. It will show on loop product and details product page. You can change 'sold out' text and it's Background color. Also you can change it's font color. 

== In stock feaute ==
On our 1.0.5 version we bring in stock message with how many product on your inventory. You can set it background and text color from Dashboard woocommerce inventory settings.


== Admin Email Alert ==
1. Go to WooCommerce > Settings > Emails. Then manage 'Stock Out Alert' email system.


== Frequently Asked Questions ==
When activated this plugin will allow the admin to make changes in the custom fields. These settings can be changed at the WC Out of Stock Message Plugin.

= What this plugin for? =

It's mainly for who want to show out of stock product message in single product page.

= Whats the facility? =

Admin can quickly type and set out of stock message. It can be set global message for all out of stock product and can custom message for special product.

= What is Out of Stock plugin ? =

Out of stock plugin is a quick solution for woocommerce product inventory system. When a product will be out of stock it will show a custom message which is one time set from woocommerce setting page. So it's totally hassle free and easy to use. 



== Screenshots ==
1. Image for the Plugin Position
2. Image for the Plugin Form
3. Result of the Plugin Action
4. Dashboard Metabox for Quick View
5. StockOut Admin Alert Email

== Changelog ==

= 1.0.6 =
* Variable product false issue fixed
* In stock feature with color settings
* blueprint file added

= 1.0.5 =
* Sold Out Badge
* Sold Out hide/show
* Sold Out background color
* Sold Out Text color

= 1.0.4 =
* Dashboard metabox product quantity and stock statics add

= 1.0.3 =
* bug fix for default data

= 1.0.2 =
* add customizer settings on woocommerce section
* add out of stock message widget 
* woocommerce default stock out recipient use for email notice 
* woocommerce plugin not install admin notice 
* fix class StockOut_Msg_CodersTime when not exist issue

= 1.0.1 =
* Admin Email alert when stock out
* Change message Background color
* Change message Text color
* Product page message showing area 
* add shortcode option 
 
= 1.0.0 =
* Initial release.