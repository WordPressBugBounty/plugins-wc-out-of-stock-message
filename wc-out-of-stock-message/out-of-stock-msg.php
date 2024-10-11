<?php
/**
 * Plugin Name: Out Of Stock Message Manage
 * Requires Plugins: woocommerce
 * Plugin URI: https://coders-time.com/plugins/out-of-stock/
 * Version: 2.6
 * Author: coderstime
 * Author URI: https://www.facebook.com/coderstime
 * Text Domain: wcosm
 * Description: Out Of Stock Message for WooCommerce plugin for those stock out or sold out message for product details page. Also message can be show with shortcode support. Message can be set for specific 						   product or globally for all products when it sold out. You can change message background and text 						color from woocommerce inventory settings and customizer woocommerce section. It will show message on single product where admin select to show. Admin also will be notified by email when product stock out. 
 * Domain Path: /languages
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package wcosm
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WCOSM_PLUGIN_FILE' ) ) {
	define( 'WCOSM_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'WP_WCSM_PLUGIN_PATH' ) ) {
	define( 'WP_WCSM_PLUGIN_PATH', __DIR__ );
}

if ( ! defined( 'WCOSM_LIBS_PATH' ) ) {
	define( 'WCOSM_LIBS_PATH', dirname( WCOSM_PLUGIN_FILE ) . '/includes/' );
}
define ( 'wcosm_ver', '2.6' );
define ( 'WCOSM_TEXT_DOMAIN', 'wcosm' );
define ( 'WCOSM_PLUGIN_Name', 'Out Of Stock Manage for WooCommerce' );

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

use Outofstockmanage\Setup;
use Outofstockmanage\Api;
use Outofstockmanage\Settings;
use Outofstockmanage\Lib_API;
use Outofstockmanage\Message;

if ( ! class_exists( 'outofstockmanage' ) ) :
	/**
	 * The outofstockmanage class.
	 */
	class outofstockmanage 
	{
		/**
		 * This class instance.
		 *
		 * @var \outofstockmanage single instance of this class.
		 */
		private static $instance;
		/**
		 * Constructor.
		*/
		public function __construct() 
		{	
			/* check curreent theme is block theme or classic var_dump(wp_is_block_theme()); */			
			if ( ! class_exists( 'WooCommerce' ) ) {
				add_action( 'admin_notices', [$this,'missing_wc_notice'] );
			}
			register_activation_hook( __FILE__, [$this,'outofstockmanage_activate'] );
			register_deactivation_hook( __FILE__, [$this,'outofstockmanage_deactivate'] ); /*plugin deactivation hook*/
			/* both classic and block theme */
			
			if ( is_admin() ) {
				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
				new Setup();
			}

			add_shortcode( 'wcosm_stockout_msg', [$this,'plugin_shortcode'] );

			new Settings(); /* public settings call */
			new Api(); /* public api call */
			(new Lib_API())->init(); /* Lib_API api call for tracking*/

			add_filter( 'woocommerce_inventory_settings', [$this, 'wcosm_setting'], 1 );

			if(wp_is_block_theme()){				
				new Message(); /* show message on frontend for block them */	
			}else{
				add_action( 'admin_enqueue_scripts', [$this, 'wcosm_admin_scripts'] );
	        	add_action( 'wp_enqueue_scripts', [$this, 'wcosm_scripts_frontend'] );
				/*widget load*/
				add_action( 'widgets_init', [$this, 'wcosm_load_widget'] );
				// var_dump($this->wcosm_option('position'));
				if( $this->wcosm_option('position') ) {
					add_action( $this->wcosm_option('position'),[$this,'wc_single_product_msg'], 6);
				} else {
					add_action('woocommerce_single_product_summary',[$this,'wc_single_product_msg'], 6);
				}
				/*customizer settings*/
				add_action( 'customize_register', [$this,'customize_register_method'] );
				// /*Stock out badge*/
				add_action( 'woocommerce_before_shop_loop_item_title', [ $this, 'display_sold_out_in_loop' ], 10 );
				add_action( 'woocommerce_before_single_product_summary', [ $this, 'display_sold_out_in_single' ], 30 );
				add_filter( 'woocommerce_locate_template', [ $this, 'woocommerce_locate_template_method' ], 1, 3 );
			}
		}

		/**
		 * plugin Activation hook.
		 *
		 * @since 0.1.0
		*/
		public function outofstockmanage_activate()
		{
			add_option( 'wcosm_active',time() );		
			if( 'no' == get_option('woocommerce_manage_stock') ){
				update_option('woocommerce_manage_stock','yes');
			}
		}	

		/**
		 * Plugin Deactivation hook.
		 *
		 * @since 0.1.0
		*/
		public function outofstockmanage_deactivate() {
			update_option( 'wcosm_deactive',time() );
		}

		/**
	     * Show action links on the plugin screen
	     *
	     * @param mixed $links
	     * @return array
	     */
	    public function action_links( $links ) 
	    {
	        return array_merge(
	            [
	                '<a href="' . admin_url( 'admin.php?page=ct-out-of-stock' ) . '">' . __( 'Settings', 'wcosm' ) . '</a>',
	                '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=products&section=inventory' ) . '">' . __( 'Woo Inventory', 'wcosm' ) . '</a>',
	                '<a href="' . esc_url( 'https://www.facebook.com/coderstime' ) . '">' . __( 'Support', 'wcosm' ) . '</a>'
	            ], $links );
	    }

		/**
		 * Get shortcode result
		*/
	    public function plugin_shortcode (  $atts, $key = "" ) 
	    {
	    	/*get output*/
	    	global $post, $product;
			$get_saved_val 	 = get_post_meta( $post->ID, '_out_of_stock_msg', true);
			$global_checkbox = get_post_meta( $post->ID, '_wcosm_use_global_note', true);
			$global_note 	 = get_option( 'woocommerce_out_of_stock_message' );

			if( $get_saved_val && !$product->is_in_stock() && $global_checkbox != 'yes') {
				return sprintf( '<div class="outofstock-message">%s</div> <!-- /.outofstock-product_message -->', $get_saved_val );
			}

			if( $global_checkbox == 'yes' && !$product->is_in_stock() ) {
				return sprintf( '<div class="outofstock-message">%s</div> <!-- /.outofstock_global-message -->', $global_note );
			}

			return false;
	    }

		/**
		 * WooCommerce settings->product inverntory tab new settings field for out-of-stock message/note
		*/
		public function wcosm_setting( $setting ) 
		{
			?>
				<h4> 
					<?php _e('You can change below settings with separate menu.','wcosm'); ?> 
					<a href="<?php echo admin_url( 'admin.php?page=ct-out-of-stock' );  ?>"> Click Here </a> 
				</h4>
			<?php
			$out_stock[] = [
				'title' 	=> __( 'Out of Stock Message', 'wcosm' ),
				'desc' 		=> __( 'Message for out of stock product.', 'wcosm' ),
				'id' 		=> 'woocommerce_out_of_stock_message',
				'css' 		=> 'width:53%; height: 125px;margin-top:10px;',
				'type' 		=> 'textarea',
				'autoload'  => false
			];

			$out_stock[] = [
				'title' 	=> __( 'Out of Stock BG Color', 'wcosm' ),
				'desc' 		=> __( 'Background Color for out of stock message.', 'wcosm' ),
				'id' 		=> 'woocommerce_out_of_stock[color]',
				'css' 		=> 'width:50%;height:31px;',
				'type' 		=> 'color',
				'autoload'  => false
			];

			$out_stock[] = [
				'title' 	=> __( 'Out of Stock Text Color', 'wcosm' ),
				'desc' 		=> __( 'Text Color for out of stock message.', 'wcosm' ),
				'id' 		=> 'woocommerce_out_of_stock[textcolor]',
				'css' 		=> 'width:50%;height:31px;',
				'type' 		=> 'color',
				'autoload'  => false
			];

			$out_stock[] = [
				'title' 	=> __( 'Stock Out Badge Show', 'wcosm' ),
				'desc' 		=> __( ' Enable Stock Out Badge', 'wcosm' ),
				'id' 		=> 'woocommerce_out_of_stock[show_badge]',
				'default'	=> 'yes',
				'css' 		=> 'margin-top:10px;',
				'type' 		=> 'checkbox',
				'autoload'  => false
			];

			$out_stock[] = [
				'title' 	=> __( 'Stock Out Badge', 'wcosm' ),
				'desc' 		=> __( 'Stock Out Badge Text', 'wcosm' ),
				'id' 		=> 'woocommerce_out_of_stock[badge]',
				'css' 		=> 'width:53%; height:150px;margin-top:10px;',
				'type' 		=> 'textarea',
				'autoload'  => false
			];

			$out_stock[] = [
				'title' 	=> __( 'Badge BG Color', 'wcosm' ),
				'desc' 		=> __( 'Background Color for badge', 'wcosm' ),
				'id' 		=> 'woocommerce_out_of_stock[badge_bg]',
				'css' 		=> 'width:50%;height:31px;',
				'type' 		=> 'color',
				'autoload'  => false
			];

			$out_stock[] = [
				'title' 	=> __( 'Badge Text Color', 'wcosm' ),
				'desc' 		=> __( 'Text Color for badge.', 'wcosm' ),
				'id' 		=> 'woocommerce_out_of_stock[badge_color]',
				'css' 		=> 'width:50%;height:31px;',
				'type' 		=> 'color',
				'autoload'  => false
			];

			$out_stock[] = [
			    'name'    => __( 'Badge Position', 'wcosm' ),
			    'desc'    => __( 'Select Where show Badge text', 'wcosm' ),
			    'id'      => 'wcosm_badge_position',
			    'css'     => 'min-width:150px;',
			    'std'     => 'right-top', /*WooCommerce < 2.0*/
			    'default' => 'right-top',
			    'type'    => 'select',
			    'options' => [
			      'right-top' 		=> __( 'Right Top Position', 'wcosm' ),
			      'left-top' 		=> __( 'Left Top Position', 'wcosm' )
				],
			    'desc_tip' =>  true,
			];

			$out_stock[] = [
				'title' 	=> __( 'Hide Sale Badge?', 'wcosm' ),
				'desc' 		=> __( 'Do you want to hide the "Sale" badge when a product is sold out?', 'wcosm' ),
				'id' 		=> 'woocommerce_out_of_stock[hide_sale]',
				'default'	=> 'yes',
				'css' 		=> 'margin-top:10px;',
				'type' 		=> 'checkbox',
				'autoload'  => false
			];

			$out_stock[] = [
			    'name'    => __( 'Out of Stock Display Position', 'wcosm' ),
			    'desc'    => __( 'This controls the position of out of stock message.', 'wcosm' ),
			    'id'      => 'woocommerce_out_of_stock[position]',
			    'css'     => 'min-width:150px;',
			    'std'     => 'woocommerce_single_product_summary', /*WooCommerce < 2.0*/
			    'default' => 'woocommerce_single_product_summary',
			    'type'    => 'select',
			    'options' => [
			      'woocommerce_single_product_summary' 			=> __( 'WC Single Product Summary', 'wcosm' ),
			      'woocommerce_before_single_product_summary'	=> __( 'WC Before Single Product Summary', 'wcosm' ),
			      'woocommerce_after_single_product_summary'	=> __( 'WC After Single Product Summary', 'wcosm' ),
			      'woocommerce_before_single_product' 			=> __( 'WC Before Single Product', 'wcosm' ),
			      'woocommerce_after_single_product' 			=> __( 'WC After Single Product', 'wcosm' ),
			      'woocommerce_product_meta_start' 				=> __( 'WC product meta start', 'wcosm' ),
			      'woocommerce_product_meta_end' 				=> __( 'WC product meta end', 'wcosm' ),
			      'woocommerce_product_thumbnails' 				=> __( 'WC product thumbnails', 'wcosm' ),
			      'woocommerce_product_thumbnails' 				=> __( 'WC product thumbnails', 'wcosm' ),
				],
			    'desc_tip' =>  true,
			];

			$out_stock[] = [
				'title' 	=> __( 'Show Stock Quantity', 'wcosm' ),
				'desc' 		=> __( ' In Stock Quantity Message', 'wcosm' ),
				'id' 		=> 'woocommerce_out_of_stock[stock_qty_show]',
				'default'	=> 'yes',
				'css' 		=> 'margin-top:10px;',
				'type' 		=> 'checkbox',
				'autoload'  => false
			];

			$out_stock[] = [
				'title' 	=> __( 'In Stock Quantity Color', 'wcosm' ),
				'desc' 		=> __( 'In Stock Qunatity Color', 'wcosm' ),
				'id' 		=> 'woocommerce_out_of_stock[stock_color]',
				'css' 		=> 'width:50%;height:31px;',
				'default' 	=> '#fff',
				'type' 		=> 'color',
				'autoload'  => false
			];

			$out_stock[] = [
				'title' 	=> __( 'Stock Quantity Background', 'wcosm' ),
				'desc' 		=> __( 'In Stock Qunatity Background Color', 'wcosm' ),
				'id' 		=> 'woocommerce_out_of_stock[stock_bgcolor]',
				'css' 		=> 'width:50%;height:31px;',
				'default' 	=> '#77a464',
				'type' 		=> 'color',
				'autoload'  => false
			];

			array_splice( $setting, 2, 0, $out_stock );
			return $setting;
		}

		/*
		 * Scripts
		 * Admin screen
		 */
		public function wcosm_admin_scripts ( $hook )
		{
			$screen = get_current_screen();

		   //  if ( 'dashboard' === $screen->base ) 
		   //  {
		   // 	 wp_enqueue_style( 'bootstrap', WP_WCSM_PLUGIN_PATH . 'assets/css/bootstrap.min.css',[], '5.0.2' );
		   // 	 wp_enqueue_style( 'datatable', WP_WCSM_PLUGIN_PATH . 'assets/css/dataTables.bootstrap5.min.css',[], '1.10.25' );
		   // 	 wp_enqueue_script( 'datatable-jquery', WP_WCSM_PLUGIN_PATH . 'assets/js/jquery.dataTables.min.js',['jquery'], '1.10.25', true );
		   // 	 wp_enqueue_script( 'datatable-bootstrap', WP_WCSM_PLUGIN_PATH . 'assets/js/dataTables.bootstrap5.min.js',['jquery'], '1.10.25', true );
		   // 	 wp_enqueue_script( 'plugin-datatable', WP_WCSM_PLUGIN_PATH . 'assets/js/plugin-datatable.js',['jquery'],true );
		   //  }
			
			if( is_product() || ($screen->post_type == 'product' &&  $screen->base == 'post') ) 
			{
				?>
				<style>
					._out_of_stock_note_field, ._wc_sm_use_global_note_field { display: none; }
					._out_of_stock_note_field.visible, ._wc_sm_use_global_note_field.visible {display: block; }
					#_out_of_stock_note {min-width: 70%;min-height: 120px; }
				</style>	
				<?php
				wp_enqueue_script( 'wcosm-msg', WP_WCSM_PLUGIN_PATH . 'assets/js/wc-sm.js', ['jquery'], filemtime( WP_WCSM_PLUGIN_PATH .'/assets/js/wc-sm.js') );
			}
			
		}

		/**
		 * Scripts
		 * Front end
		*/
		public function wcosm_scripts_frontend()
		{
				$bg_color 	= $this->wcosm_option('color');
				$text_color = $this->wcosm_option('textcolor');				
				$stockbgcolor = $this->wcosm_option('stock_bgcolor');
				$stockcolor = $this->wcosm_option('stock_color');
			?>
			<style>
				.outofstock-message {margin-top: 20px;margin-bottom: 20px;background-color: <?php echo $bg_color ? :$stockbgcolor; ?>;padding: 20px;color: <?php echo $text_color?:$stockcolor; ?>;clear:both;border-radius:5px; }
				.stock.out-of-stock{display:none;}
				.outofstock-message a { font-style: italic; }
				.woocommerce div.product .stock { color: <?php echo $stockcolor;?> !important; background-color: <?php echo $stockbgcolor; ?>;padding:10px 20px;font-weight: 700; border-radius: 5px; }
				.instock_hidden {display: none;}
			</style>
			<?php
		}

		/**
		 * Out of stock message widget method
		*/
	    public function wcosm_load_widget()
	    {
	    	/*widget area*/
	        include('includes/widget-wcosm.php');
	        register_widget( 'WCOSM_Widget' );
	    }

		/**
		 * Display message
		*/
		public function wc_single_product_msg ( ) 
		{
			global $post, $product;
			$wcosm_product = is_object($product) ? $product : wc_get_product() ;

			$get_saved_val 		= get_post_meta( $post->ID, '_out_of_stock_msg', true);
			$global_checkbox 	= get_post_meta($post->ID, '_wcosm_use_global_note', true);
			$global_note 		= get_option('woocommerce_out_of_stock_message');

			$wcosm_email_admin 	= get_option('wcosm_email_admin');

			if( $get_saved_val && !$wcosm_product->is_in_stock() && $global_checkbox != 'yes') {
				printf( '<div class="outofstock-message">%s</div> <!-- /.outofstock-product_message -->', $get_saved_val );
			}

			if( $global_checkbox == 'yes' && !$wcosm_product->is_in_stock() ) {
				printf( '<div class="outofstock-message">%s</div> <!-- /.outofstock_global-message -->', $global_note );
			}

			/*stock out message veriable product*/
			add_filter('woocommerce_get_stock_html', function( $msg ) {
				global $product;
				$wcosm_product = is_object($product) ? $product : wc_get_product();				

	        	if ( !$wcosm_product->is_in_stock() ) {
	        		$msg = '';
	        	}

	        	return $msg;
	        });

	        add_filter( 'woocommerce_get_availability_class', function( $class ){
				$stock_qty_show = $this->wcosm_option('stock_qty_show');

				if ( $class ==='in-stock' && $stock_qty_show === 'no' ) {
					$class .= ' instock_hidden';
				}
				return $class;			
			});

			if ( !$wcosm_product->is_in_stock() && 'false' === $wcosm_email_admin  ) {
				$email = WC()->mailer()->emails['StockOut_Stock_Alert'];
	        	$email->trigger( null, $wcosm_product->get_id());
			}

			if ( $wcosm_product->is_in_stock() && 'true' == $wcosm_email_admin ) {
				update_option( 'wcosm_email_admin', 'false');
			}			
		}

		/**
	    	* Plugin customizer settings
	    	* @author Coders Time
	    */
	    public function customize_register_method ( $wp_customize ) 
	    {
	    	$wp_customize->add_section(
				'wcosm_stock_out_message',
				array(
					'title'    => __( 'Stock Out Message', 'wcosm' ),
					'priority' => 50,
					'panel'    => 'woocommerce',
				)
			);

		    $wp_customize->add_setting(
				'woocommerce_out_of_stock_message',
				array(
					'default'           => __( 'Sorry, This product now out of stock, Check again later. (global Message)', 'wcosm' ),
					'type'              => 'option',
					'capability'        => 'manage_woocommerce',
					'sanitize_callback' => 'wp_kses_post',
					'transport'         => 'postMessage',
				)
			);

			$wp_customize->add_control(
				'woocommerce_out_of_stock_message',
				array(
					'label'       => __( 'Out of Stock Message', 'wcosm' ),
					'description' => __( 'Message for out of stock product.', 'wcosm' ),
					'section'     => 'wcosm_stock_out_message',
					'settings'    => 'woocommerce_out_of_stock_message',
					'type'        => 'textarea',
					'priority' 	  => 20,
				)
			);

			/*Stock out display box Background Color*/
			$wp_customize->add_setting(
				'woocommerce_out_of_stock[color]', array(
				  'default' 		  => '#fff999',
				  'sanitize_callback' => 'sanitize_hex_color',
				  'type' 			  => 'option',
				  'transport'         => 'postMessage',
				  'capability' 		  => 'manage_woocommerce'
				)
			);  

			$wp_customize->add_control( new WP_Customize_Color_Control( 
				$wp_customize, 'woocommerce_out_of_stock[color]', array(
					'label' 		=> esc_html__( 'Out of Stock Background Color', 'wcosm' ),
					'description' 	=> esc_html__( 'Stock Out message display are Background Color', 'wcosm' ),
					'section'   	=> 'wcosm_stock_out_message',
					'settings'  	=> 'woocommerce_out_of_stock[color]',
					'priority' 		=> 30,
				)
				)
			);

			/*Stock out display box Text Color*/
			$wp_customize->add_setting(
				'woocommerce_out_of_stock[textcolor]', array(
				  'default' 		  => '#000',
				  'sanitize_callback' => 'sanitize_hex_color',
				  'type' 			  => 'option',
				  'transport'         => 'postMessage',
				  'capability' 		  => 'manage_woocommerce'
				)
			);  

			$wp_customize->add_control( new WP_Customize_Color_Control( 
				$wp_customize, 'woocommerce_out_of_stock[textcolor]', array(
					'label' 		=> esc_html__( 'Out of Stock Background Color', 'wcosm' ),
					'description' 	=> esc_html__( 'Stock Out message display are Background Color', 'wcosm' ),
					'section'   	=> 'wcosm_stock_out_message',
					'settings'  	=> 'woocommerce_out_of_stock[textcolor]',
					'priority' 		=> 30,
				)
				)
			);

			$wp_customize->add_setting(
				'woocommerce_out_of_stock[position]',
				array(
					'default'    => $this->wcosm_option('position'),
					'type'       => 'option',
					'capability' => 'manage_woocommerce',
				)
			);

			$stockout_position_choice = array(
		      'woocommerce_single_product_summary' 			=> __( 'WC Single Product Summary', 'wcosm' ),
		      'woocommerce_before_single_product_summary'	=> __( 'WC Before Single Product Summary', 'wcosm' ),
		      'woocommerce_after_single_product_summary'	=> __( 'WC After Single Product Summary', 'wcosm' ),
		      'woocommerce_before_single_product' 			=> __( 'WC Before Single Product', 'wcosm' ),
		      'woocommerce_after_single_product' 			=> __( 'WC After Single Product', 'wcosm' ),
		      'woocommerce_product_meta_start' 				=> __( 'WC product meta start', 'wcosm' ),
		      'woocommerce_product_meta_end' 				=> __( 'WC product meta end', 'wcosm' ),
		      'woocommerce_product_thumbnails' 				=> __( 'WC product thumbnails', 'wcosm' ),
		      'woocommerce_product_thumbnails' 				=> __( 'WC product thumbnails', 'wcosm' ),
		    );

			$wp_customize->add_control(
				'woocommerce_out_of_stock[position]',
				array(
					'label'    => __( 'Out of Stock Display Position', 'wcosm' ),
					'section'  => 'wcosm_stock_out_message',
					'settings' => 'woocommerce_out_of_stock[position]',
					'type'     => 'select',
					'choices'  => $stockout_position_choice,
					'priority' => 40,
				)
			);

		    /*Stock out display stock Color*/
			$wp_customize->add_setting(
				'woocommerce_out_of_stock[stock_color]', array(
				  'default' 		  => '#fff',
				  'sanitize_callback' => 'sanitize_hex_color',
				  'type' 			  => 'option',
				  'transport'         => 'postMessage',
				  'capability' 		  => 'manage_woocommerce'
				)
			); 

			$wp_customize->add_control( new WP_Customize_Color_Control( 
				$wp_customize, 'woocommerce_out_of_stock[stock_color]', array(
					'label' 		=> esc_html__( 'Stock Text Color', 'wcosm' ),
					'description' 	=> esc_html__( 'In Stock Text color', 'wcosm' ),
					'section'   	=> 'wcosm_stock_out_message',
					'settings'  	=> 'woocommerce_out_of_stock[stock_color]',
					'priority' 		=> 55,
				)
				)
			);

		    /*Stock out display stock Background Color*/
			$wp_customize->add_setting(
				'woocommerce_out_of_stock[stock_bgcolor]', array(
				  'default' 		  => '#77a464',
				  'sanitize_callback' => 'sanitize_hex_color',
				  'type' 			  => 'option',
				  'transport'         => 'postMessage',
				  'capability' 		  => 'manage_woocommerce'
				)
			); 

			$wp_customize->add_control( new WP_Customize_Color_Control( 
				$wp_customize, 'woocommerce_out_of_stock[stock_bgcolor]', array(
					'label' 		=> esc_html__( 'Stock Background Color', 'wcosm' ),
					'description' 	=> esc_html__( 'In stock background color', 'wcosm' ),
					'section'   	=> 'wcosm_stock_out_message',
					'settings'  	=> 'woocommerce_out_of_stock[stock_bgcolor]',
					'priority' 		=> 60,
				)
				)
			);

	    }

		/**
		 * Display Sold Out badge in products loop
		 */
		public function display_sold_out_in_loop() 
		{
			if ( in_array($this->wcosm_option( 'show_badge' ), ['yes','true',true]) ) {
				add_filter( 'woocommerce_sale_flash', [$this,'remove_on_sale_badge_for_out_of_stock'], 10, 2);
				wc_get_template( 'single-product/sold-out.php', $this->wcosm_options() );
			}		
		}

		/**
		 * Display Sold Out badge in single product
		*/
		public function display_sold_out_in_single() 
		{
			if ( in_array($this->wcosm_option( 'show_badge' ), ['yes','true',true]) ) {
				add_filter('woocommerce_sale_flash', [$this,'remove_on_sale_badge_for_out_of_stock'], 10, 2);
				wc_get_template( 'single-product/sold-out.php', $this->wcosm_options() );
			}
		}
		
		/**
		 * Cloning is forbidden.
		 */
		public function __clone() 
		{
			wc_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'wcosm' ), $this->version );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 */
		public function __wakeup() 
		{
			wc_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'wcosm' ), $this->version );
		}

		/**
		 * Gets the main instance.
		 *
		 * Ensures only one instance can be loaded.
		 *
		 * @return \outofstockmanage
		 */
		public static function instance() 
		{
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		// phpcs:disable WordPress.Files.FileName
		/**
		 * WooCommerce fallback notice.
		 *
		 * @since 0.1.0
		 */
		public function missing_wc_notice() 
		{
			echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Outofstockmanage requires WooCommerce to be installed and active. You can download %s here.', 'outofstockmanage' ), '<a href="https://woo.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
		}

		/**
		* Get a single plugin option
		*
		* @return mixed
		*/
		public function wcosm_option( $option_name = '' ) 
		{
			/*Get all Plugin Options from Database.*/
			$plugin_options = $this->wcosm_options();

			/*Return single option.*/
			if ( isset( $plugin_options[ $option_name ] ) ) {
				return $plugin_options[ $option_name ];
			}

			return false;
		}

		/**
		 * Get saved user settings from database or plugin defaults
		 *
		 * @return array
		 */
		public function wcosm_options() 
		{
			/*Merge plugin options array from database with default options array.*/
			$plugin_options = wp_parse_args( get_option( 'woocommerce_out_of_stock', [] ), $this->plugin_default() );

			/*Return plugin options.*/
			return apply_filters( 'woocommerce_out_of_stock', $plugin_options );
		}
		
		/**
		 * Returns the default settings of the plugin
		 *
		 * @return array
		 */
		public function plugin_default() 
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

		/**
		 * Checkbox sanitization callback
		 *
		 * @param bool $checked Whether the checkbox is checked.
		 * @return bool Whether the checkbox is checked.
		*/
		public function wcosm_sanitize_checkbox( $checked ) 
		{
			/*Boolean check.*/
			return ( ( isset( $checked ) && 'true' == $checked ) ? 'true' : 'no' );
		}

		public function remove_on_sale_badge_for_out_of_stock($html, $post) {
			$product = wc_get_product($post->ID);
			if (!$product->is_in_stock()) {
				return ''; // Return empty to remove the badge for out-of-stock products
			}
			return $html; // Otherwise, return the original badge
		}
 

		/**
		 * Locate plugin WooCommerce templates to override WooCommerce default ones
		 *
		 * @param $template
		 * @param $template_name
		 * @param $template_path
		 *
		 * @return string
		 */
		public function woocommerce_locate_template_method( $template, $template_name, $template_path ) 
		{
			global $woocommerce;
			$_template = $template;
			if ( ! $template_path ) {
				$template_path = $woocommerce->template_url;
			}

			$plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/';

			// Look within passed path within the theme - this is priority
			$template = locate_template(
				array(
					$template_path . $template_name,
					$template_name
				)
			);

			if ( ! $template && file_exists( $plugin_path . $template_name ) ) {
				$template = $plugin_path . $template_name;
			}

			if ( ! $template ) {
				$template = $_template;
			}

			return $template;
		}

	}
endif;

add_action( 'plugins_loaded', 'outofstockmanage_init', 10 );

/**
 * Initialize the plugin.
 *
 * @since 0.1.0
 */
function outofstockmanage_init() 
{
	load_plugin_textdomain( 'wcosm', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );	
	outofstockmanage::instance();
}
/* file end here */