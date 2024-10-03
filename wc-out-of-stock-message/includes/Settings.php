<?php

namespace Outofstockmanage;

/**
 * Outofstockmanage Settings Class
 */
class Settings {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() 
	{
		add_action('admin_bar_menu', [$this, 'wcosm_toolbar_link'], 999);
		add_filter('admin_footer_text', [$this,'wcosm_dashboard_footer_credit']);
		add_filter('update_footer', [$this,'wcosm_wp_version_dashboard_footer'], 9999);
		add_action('woocommerce_product_options_inventory_product_data', [$this,'wcosm_textbox'], 11);
		add_action('woocommerce_process_product_meta', [$this, 'wcosm_product_save_data'], 10, 2);
		/* variation product */
		add_action('woocommerce_variation_options_inventory', [$this,'wcosm_variation_inventory_field'], 10, 3);
		add_action('woocommerce_save_product_variation', [$this, 'save_variation_field'], 10, 2 );
		add_filter('woocommerce_available_variation', [$this,'variation_data'], 10, 3);
	}

	public function variation_data( $av_var, $product, $variation )
	{
		
		if( !$av_var['is_in_stock'] ){
			$colors = get_option('woocommerce_out_of_stock');
			$stock_bgcolor = $colors['stock_bgcolor']?:'#eed4c1';
			$stock_color = $colors['stock_color']?:'#000';

			$wcosm_is_var = get_post_meta($variation->get_id(), '_wcosm_is_var', true);
			$wcosm_msg = $wcosm_is_var ? get_post_meta($variation->get_id(), '_wcosm_var_msg', true) : get_option('woocommerce_out_of_stock_message');
$wcosm_var_msg = <<<WCOSM_VARIATION
<div style="background:$stock_bgcolor;clear:both;border-radius:5px;margin:10px auto 0;padding:10px 15px;color:$stock_color">
$wcosm_msg
</div>
WCOSM_VARIATION;
			$av_var['availability_html'] = $wcosm_var_msg;
		}
		return $av_var;
	}

	/*
	* Product edit variation out of stock data
	*/
	public function wcosm_variation_inventory_field ( $loop, $variation_data, $variation )
	{
		woocommerce_wp_textarea_input( array(
			'id' => "_wcosm_var_msg{$loop}",
			'name'  => "_wcosm_var_msg[{$loop}]",
			'class' => 'short',
			'css' => 'width:100%',
			'label' => __( 'Out of Stock Message for this variation product', 'wcosm' ),
			'value' => get_post_meta( $variation->ID, '_wcosm_var_msg', true ),
			'desc_tip' => true,
			'description' => __('Enter text to show when this product will be out of stock', 'wcosm'),
			'rows' => 3,
		) );
	
		woocommerce_wp_checkbox(
			array(
				'id' 			=> "_wcosm_is_var{$loop}",
				'name'  		=> "_wcosm_is_var[{$loop}]",
				'label' 		=> __( 'Use Custom Message', 'wcosm' ),
				'cbvalue' 		=> 'yes',
				'value' 		=> get_post_meta($variation->ID, '_wcosm_is_var', true),
				'desc_tip' 		=> true,
				'description' 	=> __( 'Tick this if you want to show global out of stock message.', 'wcosm' ),
			)
		);
	}

	public function save_variation_field( $variation_id, $i  ) 
	{
		$_wcosm_var_msg = $_REQUEST['_wcosm_var_msg'][$i];
		$_wcosm_is_var = $_REQUEST['_wcosm_is_var'][$i];

		if ( isset( $_wcosm_var_msg ) ) update_post_meta( $variation_id, '_wcosm_var_msg', esc_attr( $_wcosm_var_msg ) );
		if ( isset( $_wcosm_is_var ) || is_null($_wcosm_is_var) ) update_post_meta( $variation_id, '_wcosm_is_var', esc_attr( $_wcosm_is_var ) );
	}
	
	public function wcosm_dashboard_footer_credit() 
	{
		if ( isset($_GET['page']) && 'ct-out-of-stock' === $_GET['page'] ) {
			echo 'Out of Stock Manage Plugin Powered by <a href="https://coders-time.com"> Coders Time </a>';
		}		
	}
	
	public function wcosm_wp_version_dashboard_footer() 
	{
		if ( isset($_GET['page']) && 'ct-out-of-stock' === $_GET['page'] ) {
			return "Out of Stock : <strong>v". wcosm_ver."</strong>"; // Replace with your custom version text
		}
	}

	/*
	* Product edit inventory out of stock box
	*/
	public function wcosm_textbox ( )
	{
		global $post;

		$get_saved_val = get_post_meta($post->ID, '_out_of_stock_msg', true);
		
		$this->wcosm_wp_editor_with_label(
			$get_saved_val,
			'Out of Stock Message',
			'_out_of_stock_msg',
			'_out_of_stock_msg'
		);

		woocommerce_wp_checkbox( array(
				'id' 			=> '_wcosm_use_global_note',
				'wrapper_class' => 'outofstock_field',
				'label' 		=> __( 'Use Global Message', 'wcosm' ),
				'cbvalue' 		=> 'yes',
				'value' 		=> esc_attr( $post->_wcosm_use_global_note ),
				'desc_tip' 		=> true,
				'description' 	=> __( 'Tick this if you want to show global out of stock message.', 'wcosm' ),
			)
		);
	}

	public function  wcosm_wp_editor_with_label($content, $label, $editor_id, $_name) 
	{
		?>
		<div class="form-field _out_of_stock_msg_field" style="padding:5px 20px 5px 162px!important;text-wrap:pretty">
			<label for="<?php echo $editor_id; ?>" class="custom-editor-label">
				<?php _e($label, 'wcosm'); ?>
			</label>
			<?php
				$settings = array(
					'textarea_name' => $_name,
					'media_buttons' => false,
					'textarea_rows' => 10,
					'quicktags'     => array( 'buttons' => 'em,strong,link' ),
					'tinymce'       => array(
						'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
						'theme_advanced_buttons2' => '',
					),
					'teeny' => false,
					'editor_css'=> '<style>#wp-_out_of_stock_msg-wrap{width:80%;}</style>'
				);
				wp_editor(htmlspecialchars_decode( $content, ENT_QUOTES ), $editor_id, $settings);
			?>
		</div>
		<?php
	}

	/*Saving the value*/
	public function wcosm_product_save_data( $post_id, $post )
	{
		$note = wp_filter_post_kses( $_POST['_out_of_stock_msg'] );
		$global_checkbox = wc_clean( $_POST['_wcosm_use_global_note'] );

		// save the data to the database
		update_post_meta($post_id, '_out_of_stock_msg', $note);
		update_post_meta($post_id, '_wcosm_use_global_note', $global_checkbox);
	}

	public function wcosm_toolbar_link($wp_admin_bar) {
		$args = array(
			'id'    => 'outofstockmanage',
			'title' => 'Out of Stock',
			'href'  => admin_url( 'admin.php?page=ct-out-of-stock' ),
			'meta'  => array(
				'class' => 'outofstock-toolbar',
				'title' => 'Out of Stock Manage'
			)
		);
		$wp_admin_bar->add_node($args);
	}

}
