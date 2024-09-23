<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post, $product;

?>
<?php if ( ! $product->is_in_stock()  ) : ?>

	<?php echo apply_filters( 'wcosm_soldout', '<span class="wcosm_soldout onsale">' . esc_html__( $badge, 'wcosm' ) . '</span>', $post, $product ); ?>
	<style> .woocommerce .product span.wcosm_soldout{background-color: <?php echo $badge_bg; ?>; color:<?php echo $badge_color; ?> }	</style>
	<?php
endif;
