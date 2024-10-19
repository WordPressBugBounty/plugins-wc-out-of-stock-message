<?php
namespace Outofstockmanage;

/**
 * Outofstockmanage Settings Class
 */
class Message {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() 
	{
		add_action('wp_enqueue_scripts', [$this,'enqueue_react_for_woocommerce']); /* frontend view js load */
		add_filter('woocommerce_get_price_html', [$this,'custom_out_of_stock_text'], 10, 2);
	}

	public function custom_out_of_stock_text($price, $product) 
	{
		if( is_product() || is_admin()){
			return $price;
		}

		if (!$product->is_in_stock()) {
			$data = get_option('woocommerce_out_of_stock');
			
			if( $data['badge'] && $data['show_badge'] ){
				$stock_text = $data['badge'];
			}else{
				$stock_text = strip_tags(get_option('woocommerce_out_of_stock_message')) ?: __('Out of Stock','wcosm');
			}
			
			$product_id = $product->get_id();
			$out_of_stock_msg = get_post_meta($product_id,'_out_of_stock_msg',true);
			if($out_of_stock_msg){
				$out_of_stock_msg = strip_tags($out_of_stock_msg);
			}
			return $price . "<span style='display:block'> ${out_of_stock_msg} </span><div style='display:none' class='wcosm-stock-out-msg'>${stock_text}</div>";
		}
		return $price;
	}

	public function enqueue_react_for_woocommerce()
	{
		global $product;
		$wcosm_product = is_object($product) ? $product : wc_get_product() ;
		wp_enqueue_script('wp-api-fetch'); /* wp.apiFetch */	

		if( is_product_category() ){
			$data = $this->message_options();
			if($data['badge'] && $data['show_badge']){
				$data['badge_pos'] = get_option('wcosm_badge_position','left-top');
				wp_enqueue_script(
					'wcosm-category',
					plugins_url( '/build/category.js', WCOSM_PLUGIN_FILE ), // Path to your custom React script
					['wp-element'], // Dependency on wp-element which includes React
					wcosm_ver,
					true
				);
				wp_localize_script( 'wcosm-category', 'wcosm', [
					'badge' => $data['badge'],
					'show'	=> $data['show_badge'],
					'data'	=> $data,
					'nonce' => wp_create_nonce('wcosm-lincolndu'),
				]);
				wp_enqueue_style(
					'wcosm-msg',
					plugins_url('/assets/css/outofstock.css', WCOSM_PLUGIN_FILE),
					[], // Dependencies (optional)
					wcosm_ver, // Version number (optional)
					'all' // Media type (optional)
				);
			}			
		}

		if(is_front_page() ){
			$data = $this->message_options();
			if(false && $data['badge'] && $data['show_badge']){
				$data['badge_pos'] = get_option('wcosm_badge_position','left-top');
				wp_enqueue_script(
					'wcosm-home',
					plugins_url( '/build/home.js', WCOSM_PLUGIN_FILE ), // Path to your custom React script
					['wp-element'], // Dependency on wp-element which includes React
					wcosm_ver,
					true
				);
				wp_localize_script( 'wcosm-home', 'wcosm', [
					'badge' => $data['badge'],
					'show'	=> $data['show_badge'],
					'data'	=> $data,
					'nonce' => wp_create_nonce('wcosm-lincolndu'),
				]);
				wp_enqueue_style(
					'wcosm-msg',
					plugins_url('/assets/css/outofstock.css', WCOSM_PLUGIN_FILE),
					[], // Dependencies (optional)
					wcosm_ver, // Version number (optional)
					'all' // Media type (optional)
				);				
			}			
		}

		if(is_shop()){
			// Code for shop page
			$data = $this->message_options();
			if($data['badge'] && $data['show_badge']){
				$data['badge_pos'] = get_option('wcosm_badge_position','left-top');
				wp_enqueue_script(
					'wcosm-shop',
					plugins_url( '/build/shop.js', WCOSM_PLUGIN_FILE ), // Path to your custom React script
					['wp-element'], // Dependency on wp-element which includes React
					wcosm_ver,
					true
				);
				wp_localize_script( 'wcosm-shop', 'wcosm', [
					'badge' => $data['badge'],
					'show'	=> $data['show_badge'],
					'data'	=> $data,
					'nonce' => wp_create_nonce('wcosm-lincolndu'),
				]);
				wp_enqueue_style(
					'wcosm-msg',
					plugins_url('/assets/css/outofstock.css', WCOSM_PLUGIN_FILE),
					[], // Dependencies (optional)
					wcosm_ver, // Version number (optional)
					'all' // Media type (optional)
				);
			}					
		}

		if(is_product()){
			
			if( !$wcosm_product->is_in_stock() || 'outofstock' == $wcosm_product->get_stock_status() ) {
				wp_enqueue_script(
					'wcosm-product',
					plugins_url( '/build/frontend.js', WCOSM_PLUGIN_FILE ), // Path to your custom React script
					['wp-element'], // Dependency on wp-element which includes React
					filemtime( dirname( WCOSM_PLUGIN_FILE ) . '/build/frontend.js' ),
					true
				);
				$global = get_post_meta($wcosm_product->get_id(),'_wcosm_use_global_note',true);
				if(!$global){
					wp_localize_script( 'wcosm-product', 'wcosm', [
						'data'=> $wcosm_product->get_id(),
						'nonce' => wp_create_nonce('wcosm'),
						'pid' => $wcosm_product->get_id(), // Pass the product ID to JavaScript
						'text'=> get_post_meta($wcosm_product->get_id(),'_out_of_stock_msg', true),
					]);
				}
				wp_enqueue_style(
					'wcosm-msg',
					plugins_url('/assets/css/outofstock.css', WCOSM_PLUGIN_FILE),
					[], // Dependencies (optional)
					wcosm_ver, // Version number (optional)
					'all' // Media type (optional)
				);
			}
			
		}
		
	}

	/**
	 * Get saved user settings from database or plugin defaults
	 *
	 * @return array
	 */
	public function message_options() 
	{
		/*Merge plugin options array from database with default options array.*/
		$plugin_options = wp_parse_args( get_option( 'woocommerce_out_of_stock', [] ), $this->mssage_default() );

		/*Return plugin options.*/
		return apply_filters( 'woocommerce_out_of_stock', $plugin_options );
	}

	/**
	 * Returns the default settings of the plugin
	 *
	 * @return array
	 */
	public function mssage_default() 
	{
		$default_options = array(
			'color'    			=> '#fff999',
			'textcolor'    		=> '#000',
			'position'    		=> 'woocommerce_single_product_summary',
			'show_badge'		=> 'yes',
			'badge'				=> 'Sold out!',
			'badge_bg'			=> '#77a464',
			'badge_color'		=> '#fff',
			'hide_sale'			=> 'yes',
			'stock_qty_show'	=> 'yes',
			'stock_color'		=> '#fff',
			'stock_bgcolor'		=> '#77a464',
			'stock_padding'		=> '20px',
			'stock_bradius'		=> '10px',
		);

		return apply_filters( 'wcosm_default', $default_options );
	}

}
