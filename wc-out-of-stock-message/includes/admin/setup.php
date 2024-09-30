<?php

namespace Outofstockmanage\Admin;

use Outofstockmanage\Admin\Adminemail;

/**
 * Outofstockmanage Setup Class
 */
class Setup {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() 
	{
		add_action( 'admin_enqueue_scripts', [$this, 'register_scripts' ] );
		add_action( 'admin_menu', [$this, 'register_page'] );
		add_action('wp_dashboard_setup', [$this,'add_stockout_msg_dashboard'] );
		/*Woocommerce Email structure*/
		add_filter('woocommerce_email_classes', [$this, 'wcosm_product_stock_alert_mail']);
	}

	/**
	 * Add Stock Alert Email Class
	 *
	 */
	public function wcosm_product_stock_alert_mail( $emails ) 
	{
		// require_once( 'stock-alert-admin-email.php' );
		$emails['Adminemail'] = new Adminemail();
		return $emails;
	}

	/**
	 * Load all necessary dependencies.
	 *
	 * @since 1.0.0
	 */
	public function register_scripts($hook) 
	{
		/* check current page is out of stock settings page */
		if( 'toplevel_page_ct-out-of-stock' == $hook) {
			$script_path       = '/build/admin.js';
			$script_asset_path = dirname( WCOSM_PLUGIN_FILE ) . '/build/admin.asset.php';
			$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			:[
				'dependencies' => [],
				'version'      => filemtime( $script_path ),
			];
			$script_url        = plugins_url( $script_path, WCOSM_PLUGIN_FILE );

			wp_register_script(
				'outofstockmanage',
				$script_url,
				$script_asset['dependencies'],
				$script_asset['version'],
				true
			);

			wp_register_style(
				'outofstockmanage',
				plugins_url( '/build/admin.css', WCOSM_PLUGIN_FILE ),
				[],
				filemtime( dirname( WCOSM_PLUGIN_FILE ) . '/build/admin.css' )
			);

			wp_enqueue_script('wp-api-fetch'); /* wp.apiFetch */
			wp_enqueue_script( 'outofstockmanage' );
			wp_enqueue_style( 'outofstockmanage' );
			wp_enqueue_style( 'wp-components' );

			if ( class_exists( 'OutofstockPremium' ) ) {
				$premium = true;
			}
			
			// Pass user data to React
			$user = wp_get_current_user();
			$user_data = [
				'displayName' => $user->display_name,
				'nonce' => wp_create_nonce('wcosm'),
				'rest_url' => esc_url(rest_url()),
				'version' => wcosm_ver,
				'premium' => isset($premium) ? true : false
			];
		
			wp_localize_script(
				'outofstockmanage', // Handle of the script
				'wcosm', // Object name in JavaScript
				$user_data // Data to pass
			);
		}

		if('out-of-stock_page_ct-out-products'==$hook){
			/* style and script here for stock out products page */
			// Enqueue the DataTables CSS>
			wp_enqueue_style('datatable', plugins_url('/assets/css/jquery.dataTables.min.css', WCOSM_PLUGIN_FILE),[], '1.11.5');
			wp_enqueue_style('bootstrap', plugins_url('/assets/css/bootstrap.min.css', WCOSM_PLUGIN_FILE),[], '5.3.3');
			// Enqueue the DataTables JS
			wp_enqueue_script('datatable', plugins_url('/assets/js/jquery.dataTables.min.js', WCOSM_PLUGIN_FILE),['jquery'], '1.11.5', true);
			wp_enqueue_script('bootstrap', plugins_url('/assets/js/bootstrap.bundle.min.js', WCOSM_PLUGIN_FILE),['jquery'], '5.3.3', true);
			// Initialize DataTables on the specific table class or ID
			wp_add_inline_script('datatable', '
				jQuery(document).ready(function($) {
					$("#myTable").DataTable();
				});
			');
		}		
	}	

	/**
	 * Register page in wc-admin.
	 *
	 * @since 1.0.0
	 */
	public function register_page() 
	{

		if ( ! function_exists( 'wc_admin_register_page' ) ) {
			return;
		}

		add_menu_page(
			__( 'Out of Stock Manage Page', 'wcosm' ), // Page title
			__( 'Out of Stock', 'wcosm' ),      // Menu title
			'manage_woocommerce',      // Capability required to see the menu
			'ct-out-of-stock', // Menu slug
			[$this,'outofStockManageSettings'], // Callback function that displays the page content
			'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="128" height="100" viewBox="0 14 128 100"><image width="128" height="128" xlink:href="data:img/png;base64,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"/></svg>'), // Icon URL (or use a Dashicons class)
			58 // Position in the menu order
		);
		// Create a sub-menu under the top-level menu
		add_submenu_page(
			'ct-out-of-stock',  // The slug name for the parent menu
			__('Stock Out Products','wcosm'),   // Page title
			__('Products','wcosm'),         // Sub-menu title in the admin dashboard
			'manage_woocommerce',    // Capability required to access this sub-menu
			'ct-out-products',  // Sub-menu slug
			[$this,'viewOutStockProducts'] // Function to display the sub-menu content
		);
	}

	/**
	 * Out of Stock view page
	 *   
	*/
	public function outofStockManageSettings()
	{
		echo "<div id='outofstockmanage'></div>";
	}

	/**
		* Add Dashboard metabox for quick review and go to settings page
		* @version 1.0.4
		* @author Lincoln Mahmud
	*/

	public function add_stockout_msg_dashboard() 
	{
		add_meta_box(
			'stockout_msg_widget', __('Stock Out Manage','wcosm'), 
			array($this,'stockout_msg_dashboard_widget'), 'dashboard', 'side', 'high');
	}
	/** 
	 * *Dashboard metabox details info 
	 * @version 1.0.4
	 * @author Lincoln Mahmud
	 * 
	*/
	public function stockout_msg_dashboard_widget() 
	{
		$global_msg = get_option('woocommerce_out_of_stock_message');
	?>
		<div class="rss-widget">
			<h3> <?php esc_html_e('Stock Out Global Message :','wcosm');  ?> </h3>
			<?php printf('<p> %s </p>',$global_msg); ?>
			
			<p class="text-center">
				<a href="<?php echo admin_url( 'admin.php?page=ct-out-of-stock' ) ?>"><button style="padding:5px 20px;margin:10px 0px;font-size:16px;background:#607d8b;color:#fff;border:none;border-radius:5px;"> <?php echo __( 'Change Global Message', 'wcosm' ) ?> </button></a>
			</p>
			
		</div>

		<div class="rss-widget">
			<div class="data_area mt-3">
				<table id="example" class="table table-striped display" style="width:100%">
					<thead>
						<tr>
							<th> <?php esc_html_e('Product','wcosm'); ?> </th>
							<th> <?php esc_html_e('Stock','wcosm'); ?> </th>
							<th> <?php esc_html_e('Message','wcosm'); ?> </th>
						</tr>
					</thead>
					<tbody>

						<?php 
							$args = array( 
								'limit' 		=> -1, 
								'orderby' 		=> 'name', 
								'order' 		=> 'ASC', 
								// 'stock_quantity'=> 1,
								// 'status' 		=> 'publish',
								'manage_stock' 	=> 1,
								// 'stock_status' 	=> 'outofstock',
							);
							$out_products = wc_get_products( $args );

							foreach( $out_products as $out_product ):
								$get_saved_val 		= get_post_meta( $out_product->get_id(), '_out_of_stock_msg', true);
								$global_checkbox 	= get_post_meta( $out_product->get_id(), '_wcosm_use_global_note', true);

							?>

							<tr>
								<td> <?php echo $out_product->get_name(); ?> </td>
								<td> <?php echo $out_product->get_stock_quantity(); ?> </td>
								<td> 
									<?php 
										if( $get_saved_val && $global_checkbox != 'yes') {
											printf( '%s', $get_saved_val );
										}
										if( $global_checkbox == 'yes' ) {
											printf( '%s', $global_msg );
										}
									?> 
								</td>
							</tr>	
						<?php endforeach; ?>

					</tbody>
					<tfoot>
						<tr>
							<th> <?php esc_html_e('Product','wcosm'); ?> </th>
							<th> <?php esc_html_e('Stock','wcosm'); ?> </th>
							<th> <?php esc_html_e('Message','wcosm'); ?> </th>
						</tr>
					</tfoot>
				</table>
			</div>
			
		</div>
		<?php 
	}

	public function viewOutStockProducts ( )
	{
		$args = array(
			'post_type' => 'product',
			'posts_per_page' => -1, // Get all products
			'meta_query' => array(
				array(
					'key' => '_stock_status',
					'value' => 'outofstock'
				)
			)
		);	
		$query = new \WP_Query($args);
	?>

	<style>
		.img_icon img{width:40px;height: 40px;}
	</style>

	<div class="container-fluid">
		<div class="row mt-2">
			<div class="col-md-12 mt-5">
				<table id="myTable" class="display">
					<thead>
						<tr> 
							<th> <?php _e('Image','wcosm'); ?> </th>
							<th> <?php _e('Name','wcosm'); ?></th>
							<th> <?php _e('Price','wcosm'); ?> </th>
							<th> <?php _e('Message','wcosm'); ?> </th> 
							<th> <?php _e('Global','wcosm'); ?> </th> 
							<th> <?php _e('Published','wcosm'); ?> </th> 
						</tr>
					</thead>
					<tbody>
						<?php 
						if ($query->have_posts()) {
							while ($query->have_posts()) {
								$query->the_post();
								// Get product ID
								$product_id = get_the_ID();            
								// Get product object
								$product = wc_get_product($product_id);
								$product_name = esc_html($product->get_name());
								$product_price = esc_html($product->get_price());
								$thumbnail_html = $product->get_image('thumbnail'); // This returns the image HTML directly
								$out_of_stock_msg = get_post_meta($product_id,'_out_of_stock_msg')[0]?:' ';
								$wcosm_use_global = get_post_meta($product_id,'_wcosm_use_global_note')?:'No';
								$product_published = esc_html($product->get_date_created()->date('Y/m/d \a\t H:i a')); //2024/07/13 at 7:30 am

								echo <<<WCOSMPRODUCT
									<tr>
										<td class="img_icon"> $thumbnail_html </td>
										<td> $product_name </td>
										<td> $product_price </td>
										<td> $out_of_stock_msg </td>
										<td> $wcosm_use_global </td>
										<td> $product_published </td>
									</tr>
									WCOSMPRODUCT;
								}
							wp_reset_postdata();
						} else {
							echo "<tr><td> ". __('No data found','wcosm') . "</td></tr>";
						}
						?>	
					</tbody>
				</table>
			</div>
		</div>
	</div>
		<?php 

	}

}
