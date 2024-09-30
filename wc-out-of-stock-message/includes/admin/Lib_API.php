<?php

namespace Outofstockmanage\Admin;

/**
 * Class Lib_API
 */
final class Lib_API {

    /**
     * Singleton instance
     *
     * @var Lib_API
     */
    protected static $instance;

    /**
     * @var OutOfStock\Public\Client
     */
    protected $client = null;

    /**
     * @var OutOfStock\Public\Insights
     */
    protected $insights = null;

    /**
     * Promotions Class Instance
     *
     * @var OutOfStock\Public\Promotions
     */
    public $promotion = null;

    /**
     * Initialize
     *
     * @return Lib_API
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Class constructor
     *
     * @return void
     * @since 1.0.0
     */
    public function init() {        

        if ( ! class_exists( '\Outofstockmanage\Admin\Client' ) ) {
            /** @noinspection PhpIncludeInspection */
            require_once WCOSM_LIBS_PATH . 'admin/Client.php';
        } 
        // Load Client
        $this->client = new \Outofstockmanage\Admin\Client( 'dec06622', WCOSM_PLUGIN_Name, WCOSM_PLUGIN_FILE );
        // Load
        $this->insights  = $this->client->insights(); // Plugin Insights
        $this->promotion = $this->client->promotions(); // Promo offers

        $this->promotion->set_source( 'https://raw.githubusercontent.com/coderstimes/outofstockmessage/main/wcosm-pro.json' );

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
     * Exclude License data from option dropdown
     *
     * @param $exclude
     *
     * @return array
     */
    public function __exclude_license_option( $exclude ) {
        $exclude[] = 'CodersTime_%_manage_license';

        return $exclude;
    }

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.2
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning is forbidden.', 'wcosm' ), '1.0.2' );
    }

    /**
     * Initialize Insights
     *
     * @return void
     */
    private function insightInit() {

        $projectSlug = $this->client->getSlug();
        add_filter( $projectSlug . '_what_tracked', array( $this, 'data_we_collect' ), 10, 1 );
        add_filter(
            "CodersTime_{$projectSlug}_Support_Ticket_Recipient_Email",
            function () {
                return 'coderstime@gmail.com';
            },
            10
        );
        add_filter(
            "CodersTime_{$projectSlug}_Support_Ticket_Email_Template",
            array(
                $this,
                'supportTicketTemplate',
            ),
            10
        );
        add_filter(
            "CodersTime_{$projectSlug}_Support_Request_Ajax_Success_Response",
            array(
                $this,
                'supportResponse',
            ),
            10
        );
        add_filter(
            "CodersTime_{$projectSlug}_Support_Request_Ajax_Error_Response",
            array(
                $this,
                'supportErrorResponse',
            ),
            10
        );
        add_filter(
            "CodersTime_{$projectSlug}_Support_Page_URL",
            function () {
                return 'https://coders-time.com/contact-us/';
            },
            10
        );
        $this->insights->init();
    }

    /**
     * Generate Support Ticket Email Template
     *
     * @return string
     */
    public function supportTicketTemplate() {
        /** @noinspection HtmlUnknownTarget */
        $template  = sprintf( '<div style="margin: 10px auto;"><p>Website : <a href="__WEBSITE__">__WEBSITE__</a><br>Plugin : %s (v%s)</p></div>', $this->client->getName(), $this->client->getProjectVersion() );
        $template .= '<div style="margin: 10px auto;"><hr></div>';
        $template .= '<div style="margin: 10px auto;"><h3>__SUBJECT__</h3></div>';
        $template .= '<div style="margin: 10px auto;">__MESSAGE__</div>';
        $template .= '<div style="margin: 10px auto;"><hr></div>';
        $template .= sprintf(
            '<div style="margin: 50px auto 10px auto;"><p style="font-size: 12px;color: #009688">%s</p></div>',
            'Message Processed With Coders Time Service Library (v.' . $this->client->getClientVersion() . ')'
        );

        return $template;
    }

    /**
     * Generate Support Ticket Ajax Response
     *
     * @return string
     */
    public function supportResponse() {
        $response        = '';
        $response       .= sprintf( '<h3>%s</h3>', esc_html__( 'Thank you -- Support Ticket Submitted.', 'wcosm' ) );
        $ticketSubmitted = esc_html__( 'Your ticket has been successfully submitted.', 'wcosm' );
        $twenty4Hours    = sprintf( '<strong>%s</strong>', esc_html__( '24 hours', 'wcosm' ) );
        /* translators: %s: Approx. time to response after ticket submission. */
        $notification = sprintf( esc_html__( 'You will receive an email notification from "coderstime@gmail.com" in your inbox within %s.', 'wcosm' ), $twenty4Hours );
        $followUp     = esc_html__( 'Please Follow the email and Coders Time Support Team will get back with you shortly.', 'wcosm' );
        $response    .= sprintf( '<p>%s %s %s</p>', $ticketSubmitted, $notification, $followUp );
        $docLink      = sprintf( '<a class="button button-primary" href="https://coders-time.com/plugins/out-of-stock/" target="_blank"><span class="dashicons dashicons-media-document" aria-hidden="true"></span> %s</a>', esc_html__( 'Documentation', 'wcosm' ) );
        $vidLink      = sprintf( '<a class="button button-primary" href="https://www.youtube.com/@coderstime5894" target="_blank"><span class="dashicons dashicons-video-alt3" aria-hidden="true"></span> %s</a>', esc_html__( 'Video Tutorials', 'wcosm' ) );
        $response    .= sprintf( '<p>%s %s</p>', $docLink, $vidLink );
        $response    .= '<br><br><br>';
        $toc          = sprintf( '<a href="https://coders-time.com/terms-condition-wcosm/" target="_blank">%s</a>', esc_html__( 'Terms & Conditions', 'wcosm' ) );
        $pp           = sprintf( '<a href="https://coders-time.com/privacy-policy/" target="_blank">%s</a>', esc_html__( 'Privacy Policy', 'wcosm' ) );
        /* translators: 1: Link to the Trams And Condition Page, 2: Link to the Privacy Policy Page */
        $policy    = sprintf( esc_html__( 'Please read our %1$s and %2$s', 'wcosm' ), $toc, $pp );
        $response .= sprintf( '<p style="font-size: 12px;">%s</p>', $policy );

        return $response;
    }

    /**
     * Set Error Response Message For Support Ticket Request
     *
     * @return string
     */
    public function supportErrorResponse() {
        return sprintf(
            '<div class="mui-error"><p>%s</p><p>%s</p><br><br><p style="font-size: 12px;">%s</p></div>',
            esc_html__( 'Something Went Wrong. Please Try The Support Ticket Form On Our Website.', 'wcosm' ),
            sprintf( '<a class="button button-primary" href="https://coders-time.com/contact-us/" target="_blank">%s</a>', esc_html__( 'Get Support', 'wcosm' ) ),
            esc_html__( 'Support Ticket form will open in new tab in 5 seconds.', 'wcosm' )
        );
    }

    /**
     * Set Data Collection description for the tracker
     *
     * @param $data
     *
     * @return array
     */
    public function data_we_collect( $data ) {
        $data = array_merge(
            $data,
            array(
                esc_html__( 'Site name, language and url.', 'wcosm' ),
                esc_html__( 'Number of active and inactive plugins.', 'wcosm' ),
                esc_html__( 'Your name and email address.', 'wcosm' ),
            )
        );

        return $data;
    }

    /**
     * Get Tracker Data Collection Description Array
     *
     * @return array
     */
    public function get_data_collection_description() {
        return $this->insights->get_data_collection_description();
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
                    'profile'      => 'https://fb.com/lincolndu',
                    'avatar'       => 'https://avatars.githubusercontent.com/u/10120362?v=4',
                    'display_name' => 'Lincoln Mahmud',
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

    /**
     * Update Tracker OptIn
     *
     * @param bool $override optional. ignore last send datetime settings if true.
     *
     * @see Insights::send_tracking_data()
     * @return void
     */
    public function trackerOptIn( $override = false ) {
        $this->insights->optIn( $override );
    }

    /**
     * Update Tracker OptOut
     *
     * @return void
     */
    public function trackerOptOut() {
        $this->insights->optOut();
    }

    /**
     * Check if tracking is enable
     *
     * @return bool
     */
    public function is_tracking_allowed() {
        return $this->insights->is_tracking_allowed();
    }

}

