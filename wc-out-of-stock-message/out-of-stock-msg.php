<?php
/*
 * Plugin Name: 		Out of Stock Message for WooCommerce
 * Requires Plugins: 	woocommerce
 * Plugin URI: 			https://coders-time.com/plugins/out-of-stock/
 * Description: 		Out Of Stock Message for WooCommerce plugin for those stock out or sold out message for product 					   details page. Also message can be show with shortcode support. Message can be set for specific 						   product or globally for all products when it sold out. You can change message background and text 						color from woocommerce inventory settings and customizer woocommerce section. It will show message 						  on single product where admin select to show. Admin also will be notified by email when product 						   stock out. 
 * Version: 			1.0.6
 * Author: 				coderstime
 * Author URI: 			https://www.facebook.com/coderstime
 * Domain Path: 		/languages
 * Text Domain: 		wcosm
 * License:             GPLv2 or later
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.html
*/

if(!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Woocommerce Out Of Stock Message class
 *
 * The class that holds the entire Woocommerce Out Of Stock Message plugin
 *
 * @author Coders Time <coderstime@gmail.com>
 */

define( 'WP_WCSM_FILE', __FILE__ );
define( 'WP_WCSM_PLUGIN_PATH', __DIR__ );
define( 'WP_WCSM_BASENAME', plugin_basename( WP_WCSM_FILE ) );
define( 'WP_WCSM_DIR', plugin_dir_url( WP_WCSM_FILE ) );

/*StockOut_Msg_CodersTime exists check anywhere else*/
if (!class_exists('StockOut_Msg_CodersTime')) :

/*StockOut_Msg_CodersTime class defination*/

	final class StockOut_Msg_CodersTime {

		/**
	     *
	     * construct method description
	     * @version 1.0.4
	     * @author Coders Time
	    */
	    public function __construct ( ) 
	    {
	        register_activation_hook( WP_WCSM_FILE, array( $this, 'wcosm_activate' ) ); /*plugin activation hook*/
	        register_deactivation_hook( WP_WCSM_FILE, array( $this, 'wcosm_deactivate' ) ); /*plugin deactivation hook*/
	        
	        add_action( 'init', array( $this, 'localization_setup' ) ); /*Localize our plugin*/
	        add_filter( 'plugin_action_links_' . WP_WCSM_BASENAME, array( $this, 'action_links' ) );        
	        add_action( 'admin_enqueue_scripts', array( $this, 'wcosm_admin_scripts' ) );
	        add_action( 'wp_enqueue_scripts', array( $this, 'wcosm_scripts_frontend' ) );

	        add_action('woocommerce_product_options_inventory_product_data', array( $this,'wcosm_textbox'), 11);
	        add_action('woocommerce_process_product_meta', array( $this, 'wcosm_product_save_data'), 10, 2);

	        if( $this->wcosm_option('position') ) {
	        	add_action( $this->wcosm_option('position'),array( $this,'wc_single_product_msg'), 6);
	        } else {
	        	add_action('woocommerce_single_product_summary',array( $this,'wc_single_product_msg'), 6);
	        }
	        add_filter( 'woocommerce_inventory_settings', array( $this, 'wcosm_setting' ), 1 );
	        add_action('wp_dashboard_setup', array( $this,'add_stockout_msg_dashboard') );

	        /*Woocommerce Email structure*/
	        add_filter('woocommerce_email_classes', array($this, 'wcosm_product_stock_alert_mail'));

	        add_shortcode( 'wcosm_stockout_msg', array($this,'wcosm_get_shortcode') );

	        /*customizer settings*/
	        add_action( 'customize_register', array( $this,'wcosm_customize_register' ) );

	        /*widget load*/
	        add_action( 'widgets_init', array( $this, 'wcosm_load_widget') );

	        /*Stock out badge*/
	        add_action( 'woocommerce_before_shop_loop_item_title', [ $this, 'display_sold_out_in_loop' ], 10 );
			add_action( 'woocommerce_before_single_product_summary', [ $this, 'display_sold_out_in_single' ], 30 );
			add_filter( 'woocommerce_locate_template', [ $this, 'woocommerce_locate_template' ], 1, 3 );

	    }

	    /*Out of stock message widget method*/
	    public function wcosm_load_widget( $value='' )
	    {
	    	/*widget area*/
	        include('widget-out-of-stock.php');
	        register_widget( 'WCOSM_StockOut_Widget' );
	    }

	    /**
	    	* Plugin customizer settings
	    	* @author Coders Time
	    */
	    public function wcosm_customize_register ( $wp_customize ) 
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

	    /*Get shortcode result*/

	    public function wcosm_get_shortcode (  $atts, $key = "" ) 
	    {
	    	/*get output*/
	    	global $post, $product;
			$get_saved_val 		= get_post_meta( $post->ID, '_out_of_stock_msg', true);
			$global_checkbox 	= get_post_meta($post->ID, '_wcosm_use_global_note', true);
			$global_note 		= get_option('woocommerce_out_of_stock_message');

			if( $get_saved_val && !$product->is_in_stock() && $global_checkbox != 'yes') {
				return sprintf( '<div class="outofstock-message">%s</div> <!-- /.outofstock-product_message -->', $get_saved_val );
			}

			if( $global_checkbox == 'yes' && !$product->is_in_stock() ) {
				return sprintf( '<div class="outofstock-message">%s</div> <!-- /.outofstock_global-message -->', $global_note );
			}
			return false;
	    }

	    /**
	     * Add Stock Alert Email Class
	     *
	     */
	    public function wcosm_product_stock_alert_mail( $emails ) 
	    {
	        require_once( 'out-of-stock-alert-admin-email.php' );
	        $emails['StockOut_Stock_Alert'] = new StockOut_Stock_Alert( __FILE__ );

	        return $emails;
	    }


	    /**
	     * Initialize plugin for localization
	     *
	     * @uses load_plugin_textdomain()
	     */
	    public function localization_setup() 
	    {
	        load_plugin_textdomain( 'wcsm', false, dirname( WP_WCSM_BASENAME ) . '/languages/' );
	    }


	    /*
		 * Scripts
		 * Admin screen
		 */

		public function wcosm_admin_scripts ( $hook )
		{
			$screen = get_current_screen();

			if ( 'dashboard' === $screen->base ) 
			{
				wp_enqueue_style( 'bootstrap', WP_WCSM_DIR . 'assets/bootstrap/bootstrap.min.css',array(), '5.0.2' );
				wp_enqueue_style( 'datatable', WP_WCSM_DIR . 'assets/datatable/dataTables.bootstrap5.min.css',array(), '1.10.25' );
				wp_enqueue_script( 'datatable-jquery', WP_WCSM_DIR . 'assets/datatable/jquery.dataTables.min.js',array('jquery'), '1.10.25', true );
				wp_enqueue_script( 'datatable-bootstrap', WP_WCSM_DIR . 'assets/datatable/dataTables.bootstrap5.min.js',array('jquery'), '1.10.25', true );
				wp_enqueue_script( 'plugin-datatable', WP_WCSM_DIR . 'assets/datatable/plugin-datatable.js',array('jquery'),true );
			}
			
			if( $screen->post_type == 'product' &&  $screen->base == 'post') 
			{
				?>
				<style>
					._out_of_stock_note_field, ._wc_sm_use_global_note_field { display: none; }
					._out_of_stock_note_field.visible, ._wc_sm_use_global_note_field.visible {display: block; }
					#_out_of_stock_note {min-width: 70%;min-height: 120px; }
				</style>	
				<?php
				wp_enqueue_script( 'wcosm-msg', WP_WCSM_DIR . 'assets/wc-sm.js', array('jquery'), filemtime( WP_WCSM_PLUGIN_PATH .'/assets/wc-sm.js') );
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
			$stockcolor = $this->wcosm_option('stock_color');
			$stockbgcolor = $this->wcosm_option('stock_bgcolor');
			?>
			<style>
				.outofstock-message {margin-top: 20px;margin-bottom: 20px;background-color: <?php echo $bg_color; ?>;padding: 20px;color: <?php echo $text_color; ?>;clear:both; }
				.outofstock-message a { font-style: italic; }
				.woocommerce div.product .stock { color: <?php echo $stockcolor;?> !important; background-color: <?php echo $stockbgcolor; ?>;padding:10px 20px;font-weight: 700; border-radius: 5px; }
				.instock_hidden {display: none;}
			</style>
			<?php
		}

		/*
		 * Fields
		 */

		public function wcosm_textbox ( )
		{
			global $post;

		    $val = '';
		    $get_saved_val = get_post_meta($post->ID, '_out_of_stock_msg', true);
		    if($get_saved_val != ''){
		      $val = $get_saved_val;
		    }

			woocommerce_wp_textarea_input(  array(
					'id' 			=> '_out_of_stock_msg',
					'wrapper_class' => 'outofstock_field',
					'label' 		=> __( 'Out of Stock Message', 'wcosm' ),
					'desc_tip' 		=> true,
					'value' 		=> $val,
					'description' 	=> __( 'Enter an optional note to out of stock item.', 'wcosm' ),
					'style' 		=> 'width:70%;'
				)
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

		/*Saving the value*/
		public function wcosm_product_save_data( $post_id, $post )
		{
			$note = wp_filter_post_kses( $_POST['_out_of_stock_msg'] );
			$global_checkbox = wc_clean( $_POST['_wcosm_use_global_note'] );

	    	// save the data to the database
			update_post_meta($post_id, '_out_of_stock_msg', $note);
			update_post_meta($post_id, '_wcosm_use_global_note', $global_checkbox);
		}

		/*Display message*/
		public function wc_single_product_msg ( ) 
		{
			global $post, $product;
			$get_saved_val 		= get_post_meta( $post->ID, '_out_of_stock_msg', true);
			$global_checkbox 	= get_post_meta($post->ID, '_wcosm_use_global_note', true);
			$global_note 		= get_option('woocommerce_out_of_stock_message');

			$wcosm_email_admin 	= get_option('wcosm_email_admin');

			if( $get_saved_val && !$product->is_in_stock() && $global_checkbox != 'yes') {
				printf( '<div class="outofstock-message">%s</div> <!-- /.outofstock-product_message -->', $get_saved_val );
			}

			if( $global_checkbox == 'yes' && !$product->is_in_stock() ) {
				printf( '<div class="outofstock-message">%s</div> <!-- /.outofstock_global-message -->', $global_note );
			}

			/*stock out message veriable product*/
			add_filter('woocommerce_get_stock_html', function( $msg ) {
				global $product;

	        	if ( !$product->is_in_stock() ) {
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

			if ( !$product->is_in_stock() && 'false' === $wcosm_email_admin  ) {
				$email = WC()->mailer()->emails['StockOut_Stock_Alert'];
	        	$email->trigger( null, $product->get_id());
			}

			if ( $product->is_in_stock() && 'true' == $wcosm_email_admin ) {
				update_option( 'wcosm_email_admin', 'false');
			}

			
		}

		/*WooCommerce settings->product inverntory tab new settings field for out-of-stock message/note*/
		public function wcosm_setting( $setting ) 
		{
			$out_stock[] = array (
				'title' => __( 'Out of Stock Message', 'wcosm' ),
				'desc' 		=> __( 'Message for out of stock product.', 'wcosm' ),
				'id' 		=> 'woocommerce_out_of_stock_message',
				'css' 		=> 'width:60%; height: 125px;margin-top:10px;',
				'type' 		=> 'textarea',
				'autoload'  => false
			);

			$out_stock[] = array (
				'title' 	=> __( 'Out of Stock BG Color', 'wcosm' ),
				'desc' 		=> __( 'Background Color for out of stock message.', 'wcosm' ),
				'id' 		=> 'woocommerce_out_of_stock[color]',
				'css' 		=> 'width:50%;height:31px;',
				'type' 		=> 'color',
				'autoload'  => false
			);

			$out_stock[] = array (
				'title' 	=> __( 'Out of Stock Text Color', 'wcosm' ),
				'desc' 		=> __( 'Text Color for out of stock message.', 'wcosm' ),
				'id' 		=> 'woocommerce_out_of_stock[textcolor]',
				'css' 		=> 'width:50%;height:31px;',
				'type' 		=> 'color',
				'autoload'  => false
			);

			$out_stock[] = array (
				'title' 	=> __( 'Stock Out Badge Show', 'wcosm' ),
				'desc' 		=> __( ' Enable Stock Out Badge', 'wcosm' ),
				'id' 		=> 'woocommerce_out_of_stock[show_badge]',
				'default'	=> 'yes',
				'css' 		=> 'margin-top:10px;',
				'type' 		=> 'checkbox',
				'autoload'  => false
			);

			$out_stock[] = array (
				'title' 	=> __( 'Stock Out Badge', 'wcosm' ),
				'desc' 		=> __( 'Stock Out Badge Text', 'wcosm' ),
				'id' 		=> 'woocommerce_out_of_stock[badge]',
				'css' 		=> 'width:53%; height:30px;margin-top:10px;',
				'type' 		=> 'text',
				'autoload'  => false
			);	
		

			$out_stock[] = array (
				'title' 	=> __( 'Badge BG Color', 'wcosm' ),
				'desc' 		=> __( 'Background Color for badge', 'wcosm' ),
				'id' 		=> 'woocommerce_out_of_stock[badge_bg]',
				'css' 		=> 'width:50%;height:31px;',
				'type' 		=> 'color',
				'autoload'  => false
			);

			$out_stock[] = array (
				'title' 	=> __( 'Badge Text Color', 'wcosm' ),
				'desc' 		=> __( 'Text Color for badge.', 'wcosm' ),
				'id' 		=> 'woocommerce_out_of_stock[badge_color]',
				'css' 		=> 'width:50%;height:31px;',
				'type' 		=> 'color',
				'autoload'  => false
			);	

			$out_stock[] = array (
				'title' 	=> __( 'Hide Sale Badge?', 'wcosm' ),
				'desc' 		=> __( 'Do you want to hide the "Sale" badge when a product is sold out?', 'wcosm' ),
				'id' 		=> 'woocommerce_out_of_stock[hide_sale]',
				'default'	=> 'yes',
				'css' 		=> 'margin-top:10px;',
				'type' 		=> 'checkbox',
				'autoload'  => false
			);

			$out_stock[] = array (
			    'name'    => __( 'Out of Stock Display Position', 'wcosm' ),
			    'desc'    => __( 'This controls the position of out of stock message.', 'wcosm' ),
			    'id'      => 'woocommerce_out_of_stock[position]',
			    'css'     => 'min-width:150px;',
			    'std'     => 'woocommerce_single_product_summary', /*WooCommerce < 2.0*/
			    'default' => 'woocommerce_single_product_summary',
			    'type'    => 'select',
			    'options' => array(
			      'woocommerce_single_product_summary' 			=> __( 'WC Single Product Summary', 'wcosm' ),
			      'woocommerce_before_single_product_summary'	=> __( 'WC Before Single Product Summary', 'wcosm' ),
			      'woocommerce_after_single_product_summary'	=> __( 'WC After Single Product Summary', 'wcosm' ),
			      'woocommerce_before_single_product' 			=> __( 'WC Before Single Product', 'wcosm' ),
			      'woocommerce_after_single_product' 			=> __( 'WC After Single Product', 'wcosm' ),
			      'woocommerce_product_meta_start' 				=> __( 'WC product meta start', 'wcosm' ),
			      'woocommerce_product_meta_end' 				=> __( 'WC product meta end', 'wcosm' ),
			      'woocommerce_product_thumbnails' 				=> __( 'WC product thumbnails', 'wcosm' ),
			      'woocommerce_product_thumbnails' 				=> __( 'WC product thumbnails', 'wcosm' ),
			    ),
			    'desc_tip' =>  true,
			);

			$out_stock[] = array (
				'title' 	=> __( 'Show Stock Quantity', 'wcosm' ),
				'desc' 		=> __( ' In Stock Quantity Message', 'wcosm' ),
				'id' 		=> 'woocommerce_out_of_stock[stock_qty_show]',
				'default'	=> 'yes',
				'css' 		=> 'margin-top:10px;',
				'type' 		=> 'checkbox',
				'autoload'  => false
			);

			$out_stock[] = array (
				'title' 	=> __( 'In Stock Quantity Color', 'wcosm' ),
				'desc' 		=> __( 'In Stock Qunatity Color', 'wcosm' ),
				'id' 		=> 'woocommerce_out_of_stock[stock_color]',
				'css' 		=> 'width:50%;height:31px;',
				'default' 	=> '#fff',
				'type' 		=> 'color',
				'autoload'  => false
			);

			$out_stock[] = array (
				'title' 	=> __( 'Stock Quantity Background', 'wcosm' ),
				'desc' 		=> __( 'In Stock Qunatity Background Color', 'wcosm' ),
				'id' 		=> 'woocommerce_out_of_stock[stock_bgcolor]',
				'css' 		=> 'width:50%;height:31px;',
				'default' 	=> '#77a464',
				'type' 		=> 'color',
				'autoload'  => false
			);

			array_splice( $setting, 2, 0, $out_stock );
			return $setting;
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
	                '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=products&section=inventory' ) . '">' . __( 'Settings', 'wcosm' ) . '</a>',
	                '<a href="' . esc_url( 'https://www.facebook.com/coderstime' ) . '">' . __( 'Support', 'wcosm' ) . '</a>'
	            ], $links );
	    }

	    /**
	    	* Add Dashboard metabox for quick review and go to settings page
	    	* @version 1.0.4
	    	* @author Lincoln Mahmud
	    */

	    public function add_stockout_msg_dashboard() 
	    {
	        add_meta_box('stockout_msg_widget', __('Stock Out Message','wcosm'), array($this,'stockout_msg_dashboard_widget'), 'dashboard', 'side', 'low');
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
	    			<a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=products&section=inventory' ) ?>"><button style="padding: 5px 20px;margin: 10px 0px;font-size: 16px;background: #607d8b;color: #fff;border: none;border-radius: 5px;"> <?php echo __( 'Change Global Message', 'wcosm' ) ?> </button></a>
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

		    /**
	     *
	     * run when plugin install
	     * install time store on option table
	     */
	    public function wcosm_activate ( ) 
	    {
	        add_option( 'wcosm_active',time() );
	    }

	    /**
	     *
	     * run when deactivate the plugin
	     * store deactivate time on database option table
	    */
	    public function wcosm_deactivate ( ) 
	    {
	        update_option( 'wcosm_deactive',time() );
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
			$plugin_options = wp_parse_args( get_option( 'woocommerce_out_of_stock', array() ), $this->plugin_default() );

			/*Return plugin options.*/
			return apply_filters( 'woocommerce_out_of_stock', $plugin_options );
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

/*plugin load with woocommerce plugin installation check */
add_action( 'plugins_loaded',function(){
	/*Include the main WooCommerce class.*/
	if ( ! class_exists( 'WooCommerce', false ) ) {
		add_action( 'admin_notices', function(){
			$message = __('please install <strong>woocommerce </strong> plugin first to use <strong>Out of Stock Message </strong> plugin', 'wcosm');
			printf('<div class="notice notice-warning is-dismissible">
				<p>%s</p>
			</div>', $message);
		});
	} else {
		new StockOut_Msg_CodersTime;
	}
} );
