<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post, $product;
$wcosm_product = is_object($product) ? $product : wc_get_product();
?>
<?php if ( !$wcosm_product->is_in_stock()  ) :
	$position = get_option('wcosm_badge_position','right-top');
	$postion_css = 'top: -5px;right: -5px;';
	switch($position){
		case 'right-top':
			$postion_css = 'right: -5px;top: -5px;';
			break;
		case 'left-top':
			$postion_css = 'left: -5px;top: -5px;';
			break;
		default:
	}
	?>
	<?php echo apply_filters( 'wcosm_soldout', '<span class="wcosm_soldout">' . esc_html__( strip_tags($badge), 'wcosm' ) . '</span>', $post, $wcosm_product ); ?>
	<style> .woocommerce .product span.wcosm_soldout{background-color: <?php echo $badge_bg; ?>; color:<?php echo $badge_color; ?>; position: absolute;z-index: 999;padding: 5px 10px;border-radius:2px; <?php echo $postion_css; ?> }</style>
	<?php

endif;
