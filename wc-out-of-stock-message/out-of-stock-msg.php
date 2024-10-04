<?php
/**
 * Plugin Name: Out Of Stock Message Manage
 * Requires Plugins: woocommerce
 * Plugin URI: https://coders-time.com/plugins/out-of-stock/
 * Version: 2.5
 * Author: coderstime
 * Author URI: https://www.facebook.com/coderstime
 * Text Domain: wcosm
 * Description: Out Of Stock Message for WooCommerce plugin for those stock out or sold out message for product details page. Also message can be show with shortcode support. Message can be set for specific 						   product or globally for all products when it sold out. You can change message background and text 						color from woocommerce inventory settings and customizer woocommerce section. It will show message on single product where admin select to show. Admin also will be notified by email when product stock out. 
 * Domain Path: /languages
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package extension
 */

 /* test code section */
 /* test code section */

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
define ( 'wcosm_ver', '2.5' );
define ( 'WCOSM_TEXT_DOMAIN', 'wcosm' );
define ( 'WCOSM_PLUGIN_Name', 'Out Of Stock Manage for WooCommerce' );

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
use Outofstockmanage\Setup;
use Outofstockmanage\Api;
use Outofstockmanage\Settings;
use Outofstockmanage\Lib_API;
use Outofstockmanage\Message;

function outofstockmanage_activate() {
	add_option( 'wcosm_active',time() );
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
	add_option('woocommerce_out_of_stock',$default_options);
	if( 'no' == get_option('woocommerce_manage_stock') ){
		update_option('woocommerce_manage_stock','yes');
	}
}
register_activation_hook( __FILE__, 'outofstockmanage_activate' );

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
			/* both classic and block theme */
			register_deactivation_hook( __FILE__, [$this,'outofstockmanage_deactivate'] ); /*plugin deactivation hook*/
			if ( is_admin() ) {
				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
				new Setup();
			}
			new Api(); /* public api call */
			new Settings(); /* public settings call */
			$Lib_API = new Lib_API();
			$Lib_API->init();
			new Message(); /* show message on frontend for block them */			
			add_shortcode( 'wcosm_stockout_msg', [$this,'plugin_shortcode'] );

			// if(wp_is_block_theme()){				
			// }else{
			// 	if( $this->wcosm_option('position') ) {
			// 		add_action( $this->wcosm_option('position'),[$this,'wc_single_product_msg'], 6);
			// 	} else {
			// 		add_action('woocommerce_single_product_summary',[$this,'wc_single_product_msg'], 6);
			// 	}
			// 	/*Stock out badge*/
			// 	add_action( 'woocommerce_before_shop_loop_item_title', [ $this, 'display_sold_out_in_loop' ], 10 );
			// 	add_action( 'woocommerce_before_single_product_summary', [ $this, 'display_sold_out_in_single' ], 30 );
			// 	add_filter( 'woocommerce_locate_template', [ $this, 'woocommerce_locate_template' ], 1, 3 );
			// }
		}

		/*Get shortcode result*/
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
		 * Deactivation hook.
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

		/*Display message*/
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
		 * Display Sold Out badge in products loop
		 */
		public function display_sold_out_in_loop() 
		{
			if ( $this->wcosm_option( 'show_badge' ) === 'yes' ) {
				wc_get_template( 'single-product/sold-out.php', $this->wcosm_options() );
			}		
		}

		/**
		 * Display Sold Out badge in single product
		 */
		public function display_sold_out_in_single() 
		{
			if ( $this->wcosm_option( 'show_badge' ) === 'yes' ) {
				wc_get_template( 'single-product/sold-out.php', $this->wcosm_options() );
			}
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
		 * Locate plugin WooCommerce templates to override WooCommerce default ones
		 *
		 * @param $template
		 * @param $template_name
		 * @param $template_path
		 *
		 * @return string
		 */
		public function woocommerce_locate_template( $template, $template_name, $template_path ) {
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