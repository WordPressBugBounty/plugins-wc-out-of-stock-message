<?php

namespace Outofstockmanage;

/**
 * Outofstockmanage Setup Class
 */
class Api {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() 
	{
		add_action( 'rest_api_init', array( $this, 'register_routes') );
	}

    /* *
        * Handle request to route
    */
	public function register_routes() 
	{
		$namespace = 'wcosm/v1';
		$endpoint = '/submit-form';

		register_rest_route( 
			$namespace, 
			$endpoint, 
			array(
				'methods' => 'POST',
				'callback' => [$this,'handle_form_submission'],
                'permission_callback' => function() {
                    return current_user_can('manage_woocommerce'); // Adjust permissions as needed
                },
			)
		);
        register_rest_route( 
			$namespace, 
			'get-data', 
			array(
				'methods' => 'get',
				'callback' => [$this,'handle_get_data']
			)
		);
	}

    /***
     * Process request data
     *  */ 
	
	
	public function handle_form_submission( \WP_REST_Request $request ) 
	{
        // Check for nonce security 
        $nonce = $request->get_header('x-ct-nonce');

        if ( !isset($nonce) || !wp_verify_nonce($nonce, 'wcosm')) {
            return new \WP_REST_Response([
				'message'   => __('Form Verification error','wcosm'),
				'status'	=> false,
				'data'      => ['Nonce verification',$nonce]
            ], 200);
        }

        // Check if the user has permission to save the data
        if (!current_user_can('manage_woocommerce', $post_id)) {
            return new \WP_REST_Response([
				'message'   => __('User Permission error','wcosm'),
				'status'	=> false,
				'data'      => ['User Permission']
            ], 200);
        }

		$out_of_stock_message   = wp_kses_post($request->get_param('woocommerce_out_of_stock_message'));
		$wcosm   				= sanitize_text_field($request->get_param('wcosm'));

		if( $wcosm == 'messageonly' ) {
			$msg_updated = update_option('woocommerce_out_of_stock_message', $out_of_stock_message);
            $data = get_option('woocommerce_out_of_stock');
            $data['color']      = sanitize_text_field($request->get_param('color'))?:$data['color'];
            $data['textcolor']  = sanitize_text_field($request->get_param('textcolor'))?:$data['textcolor'];

            $updated = update_option('woocommerce_out_of_stock', $data);

			return new \WP_REST_Response([
				'message'   => __('Message data updated successfully','wcosm'),
				'status'	=> [$updated, $msg_updated],
				'data'      => $request->get_params()
            ], 200);
		}

        if( $wcosm == 'settings' ) {
            $data = get_option('woocommerce_out_of_stock');
            $data['show_badge']     = sanitize_text_field($request->get_param('show_badge'))?:$data['show_badge'];
            $data['hide_sale']      = sanitize_text_field($request->get_param('hide_sale'))?:$data['hide_sale'];
            $data['stock_qty_show'] = sanitize_text_field($request->get_param('stock_qty_show'))?:$data['stock_qty_show'];
            $data['position']       = sanitize_text_field($request->get_param('position'))?:$data['position'];

            $updated = update_option('woocommerce_out_of_stock', $data);
            return new \WP_REST_Response([
				'message'   => __('Settings Data updated successfully','wcosm'),
				'status'	=> $updated,
				'data'      => $request->get_params()
			], 200);
        }

        if( $wcosm == 'badge' ) {
            $data = get_option('woocommerce_out_of_stock');
            $data['badge']          = wp_kses_post($request->get_param('badge'))?:$data['badge'];
            $data['badge_bg']       = sanitize_text_field($request->get_param('badge_bg'))?:$data['badge_bg'];
            $data['badge_color']    = sanitize_text_field($request->get_param('badge_color'))?:$data['badge_color'];

            $updated = update_option('woocommerce_out_of_stock', $data);
            return new \WP_REST_Response([
				'message'   => __('Badge Data updated successfully','wcosm'),
				'status'	=> $updated,
				'data'      => [$request->get_param('badge')]
			], 200);
        }

		// Example response:
		return new \WP_REST_Response([
            'message'   => __('Data reached successfully not saved any','wcosm'),
            'data'      => $request->get_params(),
            'status'    => false,
            'wosm'		=> $wosm
        ],200);
	}
	
	public function handle_get_data( \WP_REST_Request $request ) 
	{
		$data = get_option('woocommerce_out_of_stock');
		$data['woocommerce_out_of_stock_message'] = get_option('woocommerce_out_of_stock_message');

        // Check for nonce security 
        // $nonce = $request->get_header('x-ct-nonce');
        // if ( isset($nonce) || wp_verify_nonce($nonce, 'wcosm')) {
            return new \WP_REST_Response($data, 200);
        // }
	}

	/**
     * Class constructor
     *
     * @return void
     * @since 1.0.0
     */
    public function init() {
        if ( ! class_exists( 'Client' ) ) {
            /** @noinspection */
            require_once WP_WCSM_PLUGIN_PATH . 'includes/public/Client.php';
        }
        // Load Client
        $this->client = new Client( 'dec06622', OutOfStock_Name, MAIN_PLUGIN_FILE );
        // Load
        $this->insights  = $this->client->insights(); // Plugin Insights
        $this->promotion = $this->client->promotions(); // Promo offers

        // $this->promotion->set_source( 'https://gist.githubusercontent.com/azizulhasan/afcc74f398b290e586f3a4578341b699/raw/text-to-speech-pro.json' );

        // Initialize
        $this->insightInit();
        $this->promotion->init();

        // Filter updater api data
        add_filter(
            'CodersTime_' . $this->client->getSlug() . '_plugin_api_info',
            array(
                $this,
                '__plugin_api_info',
            ),
            10,
            1
        );
    }

	/**
     * Add Missing Info for plugin details after fetching through api
     *
     * @param $data
     *
     * @return array
     */
    public function __plugin_api_info( $data ) {
        // house keeping
        if ( isset( $data['homepage'], $data['author'] ) && false === strpos( $data['author'], '<a' ) ) {
            /** @noinspection HtmlUnknownTarget */
            $data['author'] = sprintf( '<a href="%s">%s</a>', $data['homepage'], $data['author'] );
        }

        if ( ! isset( $data['contributors'] ) ) {
            $data['contributors'] = array(
                'lincolndu' => array(
                    'profile'      => 'https://coders-time.com/',
                    'avatar'       => 'https://avatars.githubusercontent.com/u/10120362?v=4',
                    'display_name' => 'Al Mahmud',
                ),
            );
        }
        $sections = array( 'description', 'installation', 'faq', 'screenshots', 'changelog', 'reviews', 'other_notes' );
        foreach ( $sections as $section ) {
            if ( isset( $data['sections'][ $section ] ) && empty( $data['sections'][ $section ] ) ) {
                unset( $data['sections'][ $section ] );
            }
        }

        return $data;
    }
	

}
