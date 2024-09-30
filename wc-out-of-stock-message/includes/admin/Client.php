<?php
/**
 * Outofstockmanage Client
 * @version 1.0.2
 * @package TTA
 * @subpackage Services
 * This Package is based on AppSero project by weDevs
 * @license MIT
 */

namespace Outofstockmanage\Admin;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Client
 */
class Client {
	
	/**
	 * The client version.
	 *
	 * @var string
	 */
	protected $clientVersion = '1.0.0';
	
	/**
	 * API EndPoint.
	 *
	 * @var string
	 */
	protected $API_EndPoint =  'https://coders-time.com/wp-json/tracker/';
	
	/**
	 * API Version.
	 *
	 * @var string
	 */
	protected $apiVersion = 'v1';
	
	/**
	 * Hash identifier of the Plugin/Theme.
	 *
	 * @var string
	 */
	protected $hash;
	
	/**
	 * Name of the Plugin/Theme.
	 *
	 * @var string
	 */
	protected $name;
	
	/**
	 * The Plugin/Theme file path.
	 * Example .../wp-content/Plugin/test-slug/test-slug.php.
	 *
	 * @var string
	 */
	protected $file;
	
	/**
	 * Main Plugin/Theme file.
	 * Example: test-slug/test-slug.php.
	 *
	 * @var string
	 */
	protected $basename;
	
	/**
	 * Slug of the Plugin/Theme.
	 * Example: test-slug.
	 *
	 * @var string
	 */
	protected $slug;
	
	/**
	 * The project version.
	 *
	 * @var string
	 */
	protected $project_version;
	
	/**
	 * Store Product (unique) id for current Product
	 * Required by WooCommerce API Manager > 2.0.
	 *
	 * @var bool|int
	 */
	protected $product_id;
	
	/**
	 * Instance of Insights class.
	 *
	 * @since 1.0.3
	 * @var Insights
	 */
	private $insights;
	
	/**
	 * Instance of Promotions class.
	 *
	 * @since 1.0.3
	 * @var Promotions
	 */
	private $promotions;
	
	
	/**
	 * Initialize the class.
	 *
	 * @param string $hash       hash of the Plugin/Theme.
	 * @param string $name       readable name of the Plugin/Theme.
	 * @param string $file       main Plugin/Theme file path.
	 * @param string $product_id Store Product id for pro product.
	 *                           If null license page will show field for product id input.
	 * @return void
	 */
	public function __construct( $hash, $name, $file, $product_id = null ) 
	{
		$this->hash = $hash;
		$this->name = $name;
		$this->file = $file;
		$this->product_id = ! empty( $product_id ) ? (int) $product_id : false;
		$this->set_basename_and_slug();
	}
	
	/**
	 * Initialize insights class.
	 *
	 * @return Insights
	 */
	public function insights() 
	{
		if ( ! is_null( $this->insights ) ) {
			return $this->insights;
		}
		
		if ( ! class_exists( __NAMESPACE__ . '\Insights' ) ) {
			require_once __DIR__ . '/Insights.php';
		}
		$this->insights = new Insights( $this );
		
		return $this->insights;
	}
	
	/**
	 * Initialize Promotions class.
	 *
	 * @return Promotions
	 */
	public function promotions() 
	{
		if ( ! is_null( $this->promotions ) ) {
			return $this->promotions;
		}
		if ( ! class_exists( __NAMESPACE__ . '\Promotions' ) ) {
			require_once __DIR__ . '/Promotions.php';
		}
		$this->promotions = new Promotions( $this );
		
		return $this->promotions;
	}
		
	/**
	 * API Endpoint.
	 *
	 * @param string $route route to send the request.
	 *
	 * @return string
	 */
	private function endpoint( $route = '' ) 
	{
		/**
		 * Filter Request Route string
		 * @param string    $route
		 * @param array     $params
		 */
		$route = apply_filters( $this->slug . '_request_route', $route );
		/**
		 * API EndPoint
		 * @since 1.0.0
		 * @param string $url
		 *
		 */
		$this->API_EndPoint = apply_filters( $this->slug . 'CodersTime_API_EndPoint', $this->API_EndPoint, $this->apiVersion, $this->clientVersion );
		$this->API_EndPoint = untrailingslashit( $this->API_EndPoint );
		$route = rtrim( $route, '/\\' );
		$route = ltrim( $route, '/\\' );
		$URL = "{$this->API_EndPoint}/{$this->apiVersion}/$route";
		/**
		 * Filter Final API URL for request
		 * @since 1.0.0
		 *
		 * @param string $URL
		 * @param string $API_EndPoint
		 * @param string $route
		 * @param string $apiVersion
		 * @param string $clientVersion
		 */
		$URL = apply_filters( $this->slug . 'CodersTime_API_URL', $URL, $this->API_EndPoint, $route, $this->apiVersion, $this->clientVersion );

		return untrailingslashit( $URL );
	}
	
	/**
	 * Set project basename, slug and version.
	 *
	 * @return void
	 */
	protected function set_basename_and_slug() 
	{
		
		// if ( false === strpos( $this->file, WP_CONTENT_DIR . '/themes/' ) ) {
			$this->basename = plugin_basename( $this->file );
			/** @noinspection SpellCheckingInspection, PhpUnusedLocalVariableInspection */
			list( $this->slug, $mainfile ) = explode( '/', $this->basename );
			if ( ! function_exists( 'get_plugin_data' ) )  require_once ABSPATH . 'wp-admin/includes/plugin.php';
			$plugin_data = get_plugin_data( $this->file );
			$this->project_version = $plugin_data['Version'];
		// } else {
		// 	$this->basename = str_replace( WP_CONTENT_DIR . '/themes/', '', $this->file );
			
		// 	list( $this->slug, $main_file ) = explode( '/', $this->basename );
		// 	$theme = wp_get_theme( $this->slug );
		// 	$this->project_version = $theme->version;
		// 	$this->type = 'theme';
		// }
	}
	
	/**
	 * Client UserAgent String.
	 *
	 * @return string
	 */
	private function __user_agent() 
	{
		return 'CodersTime/' . md5( esc_url( home_url() ) ) . ';';
	}
	
	/**
	 * Send request to remote endpoint.
	 *
	 * @param array  $params        Parameters/Data that being sent.
	 * @param string $route         Route to send the request to.
	 * @param bool   $blocking     Block Execution Until the server response back or timeout.
	 *
	 * @return array|WP_Error   Array of results including HTTP headers or WP_Error if the request failed.
	 */
	public function send_request( $params, $route = '', $blocking = false ) 
	{
		$url = $this->endpoint( $route );		
		$headers = array(
			'user-agent' => $this->__user_agent(),
			'Accept'     => 'application/json',
		);

		// update_option('send-request-params',$params);
		
		/**
		 * Request Blocking mode.
		 * Set it to true for debugging the response with after request action.
		 *
		 * @since 1.0.2
		 * @param bool $blocking
		 */
		$blocking = (bool) apply_filters( $this->getSlug() . '_request_blocking_mode', $blocking );
		$response = wp_safe_remote_post(
			esc_url( $url ),
			[
				'method'      => 'POST',
				'timeout'     => 45,   // phpcs:ignore
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => $blocking,
				'headers'     => $headers,
				'body'        => array_merge( $params, [ 'client' => $this->clientVersion ] ),
				'cookies'     => [],
			]
		);
		return $response;
	}
	
	/**
	 * Get Version of this client.
	 *
	 * @return string
	 */
	public function getClientVersion() 
	{
		return $this->clientVersion;
	}
	
	/**
	 * Get API URI.
	 *
	 * @return string
	 */
	public function getApi() 
	{
		return $this->API_EndPoint;
	}
	
	/**
	 * Get API Version using by this client.
	 *
	 * @return string
	 */
	public function getApiVersion() 
	{
		return $this->apiVersion;
	}
	
	/**
	 * Get Hash of current Plugin/Theme.
	 *
	 * @return string
	 */
	public function getHash() 
	{
		return $this->hash;
	}
	
	/**
	 * Get Plugin/Theme Name.
	 *
	 * @return string
	 */
	public function getName() 
	{
		return $this->name;
	}
	
	/**
	 * Store Product ID.
	 *
	 * @return bool|int
	 */
	public function getProductId() 
	{
		return $this->product_id;
	}
	
	/**
	 * Get Plugin/Theme file.
	 *
	 * @return string
	 */
	public function getFile() 
	{
		return $this->file;
	}
	
	/**
	 * Get Plugin/Theme base name.
	 *
	 * @return string
	 */
	public function getBasename() 
	{
		return $this->basename;
	}
	
	/**
	 * Get Plugin/Theme Slug.
	 *
	 * @return string
	 */
	public function getSlug() 
	{
		return $this->slug;
	}
	
	/**
	 * Get Plugin/Theme Project Version.
	 *
	 * @return string
	 */
	public function getProjectVersion() 
	{
		return $this->project_version;
	}
}
// End of file Client.php.