<?php
/**
 * Stock Out Alert Email
 *
 * @author 	  coderstime
 * @version   1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
do_action( 'woocommerce_email_header', $email_heading, $email ); 
$postType = get_post_type_object('product');
$post_type_name = 'product';

if ( $postType ) {
  $post_type_name = esc_html( $postType->labels->singular_name );
}

?>

<p>
	<?php printf( __( "Hi admin. One of your %s sold out", 'wcosm' ), $post_type_name ); ?>
</p>

<?php
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
	$is_prices_including_tax = get_option( 'woocommerce_prices_include_tax' );

?>


<h3>
		<?php printf( __( "%s Details", 'wcosm' ), $post_type_name ); ?>
	</h3>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
	<thead>
		<tr>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php echo $post_type_name; ?></th>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php esc_html_e( 'Price', 'wcosm' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php echo esc_html( $product_obj->get_name() ); ?>
			<?php if( $product_obj->get_type() == 'variation'){
              foreach ( $product_obj->get_attributes() as $label => $value) {
                echo "<br>".ucfirst(wc_attribute_label($label)).": <strong>".ucfirst($value)."</strong>";
              }
            } ?>
			</th>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php echo wp_kses_post( $product_price); echo ( isset( $is_prices_including_tax ) && ($is_prices_including_tax != "yes" )) ? WC()->countries->ex_tax_or_vat() : WC()->countries->inc_tax_or_vat(); ?></th>
		</tr>
	</tbody>
</table>

<p style="margin-top: 15px !important;"><?php printf( __( "Following is the %s link : ", 'wcosm' ), $post_type_name ); ?><a href="<?php echo esc_url($product_link); ?>"><?php echo esc_html($product_name); ?></a></p>


<?php do_action( 'woocommerce_email_footer' ); ?>
