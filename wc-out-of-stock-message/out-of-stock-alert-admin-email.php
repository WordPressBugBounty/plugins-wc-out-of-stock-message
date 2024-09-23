<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'StockOut_Stock_Alert' ) ) :

/**
 * Email to Admin for stock alert
 *
 * An email will be sent to the admin when customer subscribe an out of stock product.
 *
 * @class 		StockOut_Stock_Alert
 * @version		1.3.0
 * @author 		WC Marketplace
 * @extends 	WC_Email
 */
class StockOut_Stock_Alert extends WC_Email {
	
	public $product_id;
	public $product_name;
	public $recipient;

	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 * @author coderstime
	 */
	function __construct() {
		
		$this->id 				= 'stockout_alert_admin';
		$this->title 			= __( 'Stock Out Alert', 'wcosm' );
		$this->description		= __( 'Admin will get an alert when a product stockout', 'wcosm' );
		
		$this->template_html 	= 'emails/stockout-alert-admin-email.php';
		$this->template_plain 	= 'emails/plain/stockout-alert-admin-email.php';
		$this->template_base 	= WP_WCSM_PLUGIN_PATH . '/templates/';

		/*Call parent constuctor*/
		parent::__construct();

		/*Other settings.*/
		$this->recipient = $this->get_option( 'recipient', get_option( 'woocommerce_stock_email_recipient' ) );
	}

	/**
	 * trigger function.
	 *
	 * @access public
	 * @return void
	 * @author coderstime
	 */
	function trigger( $recipient, $product_id ) 
	{
		$this->recipient = $recipient ?: $this->get_recipient();
		$this->product_id = $product_id;
		
		if ( ! $this->is_enabled() || ! $this->get_recipient() || ! $this->product_id ) {
			return;
		}
		
		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

		update_option( 'wcosm_email_admin', 'true'); /* email sending status update*/
	}

	/**
	 * Get email subject.
	 *
	 * @since  1.4.7
	 * @return string
	 */
	public function get_default_subject() {
		return apply_filters( 'woocommerce_email_subject_stock_alert', __( 'A Product stock out on {site_title}', 'wcosm'), $this->object );
	}

	/**
	 * Get email heading.
	 *
	 * @since  1.4.7
	 * @return string
	 */
	public function get_default_heading() {
		return apply_filters( 'woocommerce_email_heading_stock_alert', __( 'Welcome to {site_title}', 'wcosm'), $this->object );
	}

	/**
	 * get_content_html function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_html() {
		return wc_get_template_html(
			$this->template_html,
			array(
				'email_heading' => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'product_id' => $this->product_id,
				'sent_to_admin' => true,
				'plain_text' => false,
				'email' => $this,
			)
		);

	}

	/**
	 * get_content_plain function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_plain() {
		return wc_get_template_html(
			$this->template_plain,
			array(
				'email_heading' 		=> $this->get_heading(),
				'additional_content' 	=> $this->get_additional_content(),
				'product_id' 			=> $this->product_id,
				'sent_to_admin' 		=> true,
				'plain_text' 			=> true,
				'email'              	=> $this,
			) 
		);
	}

	/**
	 * Default content to show below main email content.
	 *
	 * @since 3.7.0
	 * @return string
	 */
	public function get_default_additional_content() {
		return __( 'Congratulations! All your products sold out.', 'woocommerce' );
	}

	/**
		 * Initialise settings form fields.
		 */
		public function init_form_fields() {
			/* translators: %s: list of placeholders */
			$placeholder_text  = sprintf( __( 'Available placeholders: %s', 'woocommerce' ), '<code>' . implode( '</code>, <code>', array_keys( $this->placeholders ) ) . '</code>' );
			$this->form_fields = array(
				'enabled'            => array(
					'title'   => __( 'Enable/Disable', 'woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this email notification', 'woocommerce' ),
					'default' => 'yes',
				),
				'recipient'          => array(
					'title'       => __( 'Recipient(s)', 'woocommerce' ),
					'type'        => 'text',
					/* translators: %s: WP admin email */
					'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'woocommerce' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
					'placeholder' => '',
					'default'     => '',
					'desc_tip'    => true,
				),
				'subject'            => array(
					'title'       => __( 'Subject', 'woocommerce' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => $placeholder_text,
					'placeholder' => $this->get_default_subject(),
					'default'     => '',
				),
				'heading'            => array(
					'title'       => __( 'Email heading', 'woocommerce' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => $placeholder_text,
					'placeholder' => $this->get_default_heading(),
					'default'     => '',
				),
				'additional_content' => array(
					'title'       => __( 'Additional content', 'woocommerce' ),
					'description' => __( 'Text to appear below the main email content.', 'woocommerce' ) . ' ' . $placeholder_text,
					'css'         => 'width:400px; height: 75px;',
					'placeholder' => __( 'N/A', 'woocommerce' ),
					'type'        => 'textarea',
					'default'     => $this->get_default_additional_content(),
					'desc_tip'    => true,
				),
				'email_type'         => array(
					'title'       => __( 'Email type', 'woocommerce' ),
					'type'        => 'select',
					'description' => __( 'Choose which format of email to send.', 'woocommerce' ),
					'default'     => 'html',
					'class'       => 'email_type wc-enhanced-select',
					'options'     => $this->get_email_type_options(),
					'desc_tip'    => true,
				),
			);
		}

}

endif;

