=== Out of Stock Message Manage ===
Contributors: coderstime, lincolndu
Donate link: https://buymeacoffee.com/coderstime
Tags: out of stock, sold out badge, variation swatches, woocommerce
Requires at least: 4.9 or higher
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 2.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

Out of Stock Message Manage is an official plugin maintained by the Coderstime that add features on the woocommerce produt stock out. 

== Description ==

Out of Stock Manage for WooCommerce plugin is used to write out of stock custom message with different background and text color. This stock out message can be set on woocommerce specific product or all global products. You can inform your customer product stock status in product details page. How many product on your stock will show on product page.

You can change default stock out status message and change colors with where message will be shown.

FEATURES
Allow product specific message.
Allow global message from plugin settings.
Sold out badge for category and shop page also single product page
Can customize message showing position in product page.
Admin will receive when a product stock out.
In Stock product quantity message on product page


### USEFULL LINKS:
> * [Live Demo Free Version](https://wordpress.org/plugins/wc-out-of-stock-message/?preview=1)
> * [Video Tutorial](https://youtu.be/guh-hkrJF_E)
> * [Documentation](https://coders-time.com/out-of-stock-documentation/)

= How it works ? =
[youtube https://youtu.be/guh-hkrJF_E]

== # 🏆 Users' Feedback For Out of Stock Message Manage ## ==

[iconMatrix](https://wordpress.org/support/topic/excellent-support-easy-to-use-6/): 
> 'Excellent Support & Easy to use

>I needed to get rid of the “Out of Stock” message and the “Sold Out” bubble completely and they helped me check the correct box to achieve what we needed for that single product. IMO if you are running a Woo system then you need this plugin. We sell tournaments and “Tournament is Closed” is better than “Out of Stock” 🙂

I love this plugin,
Steve

[Ruben Zuidervaart](https://wordpress.org/support/topic/getting-rid-of-out-of-stock-message/): 
> 'Getting Rid Of “OUT OF STOCK” Message'

> After completing the build on my site for a digital product I disliked it saying out of stock because the product never is. This plugin was easy to use to remove that wording completely or replace it with something more appropriate.
> It works as advertised and was quite easy. Keep up the good work.

> Carl M.
> BusinessByWEB


== Why does this plugin? ==
This plugin allows you to supply a literal message for stock out product. 

Default "Out of Stock" Message
1. Go to Dashboard > Out of Stock Menu 
2. Set Message, Badge, settings, get shortcode details
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
3. Go to Dashboard and select Out of stock menu.
4. Go to Add/Edit product panel. Open Inventory setting of product data, select \"Out of Stock\" on \"Stock Status\" field. Then check global note or set individual note in \"Out of Stock Note\" field.

== Variable Product Stock Out Message == 
Yes, We did it. Now you can set stock out message for your variable each product. You can also show global message for variable product. If you want to show custom message then you have to check for custom message. 

== Where Show Variable Message ==
On product page, When user select Like Color and Size then If that product of stock then Stock Out message will be shown below the variable product price.

== Sold out Badge == 
We bring sold out badge on product image corner in 1.0.5 version. It will show on loop product and details product page. You can change 'sold out' text and it's Background color. Also you can change it's font color. 

== In stock feaute ==
On our 1.0.5 version we bring in stock message with how many product on your inventory. You can set it background and text color from Dashboard woocommerce inventory settings.


== Admin Email Alert ==
1. Go to WooCommerce > Settings > Emails. Then manage 'Stock Out Alert' email system.


== Frequently Asked Questions ==
When activated this plugin will allow the admin to make changes in the custom fields. These settings can be changed at the Out of Stock Message Manage Plugin.

= Support Block Theme ? =

Yes, Our Stock out message manage plugin now support block based any theme. Our engineering team extend it's features for block based theme from 2.0 version.

= What this plugin for? =

It's mainly for who want to show out of stock product message in single product page.

= Support Variation variable product ? =

Yes, Our Stock out message manage plugin now support woocommerce variable product based custom and global stock out message system. Our engineering team extend it's features for block based theme from 2.5 version.

= Whats the facility? =

Admin can quickly type and set out of stock message. It can be set global message for all out of stock product and can custom message for special product.

= What is Out of Stock plugin ? =

Out of stock plugin is a quick solution for woocommerce product inventory system. When a product will be out of stock it will show a custom message which is one time set from woocommerce setting page. So it's totally hassle free and easy to use. 

= Is it compatible with WooCommerce Theme? =
Yes, Our plugin compatible with any kind of WooCommerce based theme including woostify / OceanWP / Astra / Flatsome / X-Theme / Avada / Storefront / Labomba / WR Nitro / Divi / BeTheme / blocksy / redlens / hever / swiftstore / kadence / justwrite / bard / shoppingcart / cosmetro / eona / graceful / Pink Theme / shoptimizer / wildwood. But sometimes it may require small css tweak. if you face any kind of problem, our dediated support team ready to help you. 

== Screenshots ==
1. Stock out message with bg and color settings page
2. Stock Out Badge settings page
3. Stock Out Settings page for badge show, hide sale, message position
4. Product wise stock out message setting also set global message show/hide
5. Product Details page 
6. Category Page Stock out badge and hide price with custom message
7. Shop Page Stock out badge and hide price with custom message
8. Out of Stock Admin Alert ⤴︎
9. Variable Product Stock Out Message 
10. Variable Product Edit Panel Screenshot

== Changelog ==
= 2.6 = 2024-10-9
* Back Inventory Settings
* Classic theme and block theme different result
* Variable product wise message modification
* Variable stock out message bottom 10px margin
* Badge Postion Left and right Top
* Language support for bangla, USA, partial France

= 2.5 = 2024-10-4
* Variable Product Custom Message Addition
* Variable wise custom message
* Classic theme message modification 
* Woocommerce Latest upgrade

= 2.4 = 2024-10-2
* fix bug

= 2.3 = 2024-10-1
* product page price
* category page price back

= 2.3 = 2024-10-1
* fix $product global variable issue created by third party
* $wcosm_product = is_object($product) ? $product : wc_get_product() ;
* wp_enqueue_script('wp-api-fetch'); /* wp.apiFetch */	

= 2.2 = 2024-9-30
* is_object($product) && method_exists($product, 'is_in_stock') ? $product->is_in_stock() : $product->get_stock_quantity();
* $stock_status 	 = $product->get_stock_status();
* execute code when (!$is_in_stock || 'outofstock' == $stock_status)

= 2.1 = 2024-9-30
* bug fix
* method_exists($product, 'is_in_stock') check in message.php

= 2.0 = 2024-9-30
* Lots of change
* Block theme upgrade
* Plugin will work on your block theme
* Separate settings page with separate menu
* Stock out products list sub menu under "Stock Out" menu 
* Fix previous error
* Stock out message with JoditEditor
* Stock out badge with JoditEditor
* Product wise message with WP Classical editor
* If you uninstall, you can feedback why you leave the plugin
* Contact details on stock out menu
* Premium plugin feature details
* Allow Tracking feature for better service 
* Name change to "Out of Stock Message Manage" from "Out of Stock Message for Woocommerce"

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