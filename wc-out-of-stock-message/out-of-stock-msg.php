<?php
/**
 * Plugin Name: Out Of Stock Message Manage
 * Requires Plugins: woocommerce
 * Plugin URI: https://coders-time.com/plugins/out-of-stock/
 * Version: 2.3
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
define ( 'wcosm_ver', '2.3' );
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
			if ( ! class_exists( 'WooCommerce' ) ) {
				add_action( 'admin_notices', [$this,'missing_wc_notice'] );
			}
						
			
			register_deactivation_hook( __FILE__, [$this,'outofstockmanage_deactivate'] ); /*plugin deactivation hook*/

			if ( is_admin() ) {
				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
				new Setup();
			}
			new Api(); /* public api call */
			new Settings(); /* public settings call */
			$Lib_API = new Lib_API();
			$Lib_API->init();
			new Message(); /* show message on frontend */
			add_shortcode( 'wcosm_stockout_msg', [$this,'plugin_shortcode'] );
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