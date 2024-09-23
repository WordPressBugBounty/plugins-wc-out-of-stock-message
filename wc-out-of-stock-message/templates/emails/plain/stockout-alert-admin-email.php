<?php
/**
 * Stock Out Alert Email
 *
 * @author 	  Coderstime
 * @version   1.0.1
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$postType = get_post_type_object('product');
$post_type_name = 'product';

if ( $postType ) {
  $post_type_name = esc_html( $postType->labels->singular_name );
}

echo $email_heading . "\n\n";

printf( __( "Hi admin. One of your %s sold out", 'wcosm' ), $post_type_name ) . "\n\n";

echo "\n****************************************************\n\n";

$product_obj = wc_get_product( $product_id );

if( $product_obj->is_type('variation') ) {
	$parent_id = $product_obj->get_parent_id();
	$product_link = admin_url('post.php?post=' . $parent_id . '&action=edit');
	$product_name = $product_obj->get_formatted_name();
	$product_price = $product_obj->get_price_html();
} else {
	$product_link = admin_url('post.php?post=' . $product_id . '&action=edit');
	$product_name = $product_obj->get_formatted_name();
	$product_price = $product_obj->get_price_html();
}

echo '\n '.$post_type_name.' Name : '.$product_name;

if($product_obj->get_type() == 'variation'){
  foreach ($product_obj->get_attributes() as $label => $value) {
    echo "\n".ucfirst(wc_attribute_label($label)).": ".ucfirst($value)."\n";
  }
} 

echo '\n\n '.$post_type_name.' link : '.$product_link;

echo "\n\n\n****************************************************\n\n";

echo "\n\n\n****************************************************\n\n";


echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
