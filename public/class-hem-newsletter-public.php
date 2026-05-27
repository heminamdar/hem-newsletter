<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Hem_Newsletter_Public {

    public function __construct() {
        add_shortcode( 'newsletter_by_hem', array( $this, 'render_form' ) );
        add_action( 'wp_enqueue_scripts',  array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_hem_nl_subscribe',        array( $this, 'handle_ajax_subscribe' ) );
        add_action( 'wp_ajax_nopriv_hem_nl_subscribe', array( $this, 'handle_ajax_subscribe' ) );
        add_action( 'template_redirect',   array( $this, 'handle_confirm' ) );
    }

    public function enqueue_assets() {
        wp_enqueue_style(
            'hem-newsletter-public',
            HEM_NL_PLUGIN_URL . 'public/public.css',
            array(),
            HEM_NL_VERSION
        );
        wp_enqueue_script(
            'hem-newsletter-public',
            HEM_NL_PLUGIN_URL . 'public/public.js',
            array( 'jquery' ),
            HEM_NL_VERSION,
            true
        );
        wp_localize_script( 'hem-newsletter-public', 'hemNL', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'hem_nl_subscribe' ),
        ) );
    }


    private function get_style_settings() {
        return wp_parse_args( get_option( 'hem_newsletter_smtp', array() ), array(
            'button_color'       => '#1a1a1a',
            'button_text_color'  => '#ffffff',
            'button_hover_color' => '#2563eb',
            'input_radius'       => 6,
            'button_radius'      => 6,
            'button_position'    => 'outside',
            'form_width'         => 520,
            'form_height'        => 44,
            'button_width'       => 120,
            'button_height'      => 42,
            'form_title'         => 'Subscribe for new post alerts.',
            'input_placeholder'  => 'email address',
            'button_label'       => 'Subscribe',
        ) );
    }

    /**
     * Newsletter subscription form shortcode.
     */
    public function render_form( $atts ) {
        $style_settings = $this->get_style_settings();

        $atts = shortcode_atts( array(
            'placeholder' => isset( $style_settings['input_placeholder'] ) ? $style_settings['input_placeholder'] : 'email address',
            'button'      => isset( $style_settings['button_label'] ) ? $style_settings['button_label'] : 'Subscribe',
            'title'       => isset( $style_settings['form_title'] ) ? $style_settings['form_title'] : 'Subscribe for new post alerts.',
        ), $atts, 'newsletter_by_hem' );

        ob_start();
        include HEM_NL_PLUGIN_DIR . 'public/views/form.php';
        return ob_get_clean();
    }

    /**
     * AJAX subscription handler.
     */
    public function handle_ajax_subscribe() {
        check_ajax_referer( 'hem_nl_subscribe', 'nonce' );
        $email  = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        $result = Hem_Newsletter_Subscriber::subscribe( $email );
        wp_send_json( $result );
    }

    /**
     * Handle confirmation and unsubscribe links.
     */
    public function handle_confirm() {
        $action = isset( $_GET['hem_nl_action'] ) ? sanitize_key( wp_unslash( $_GET['hem_nl_action'] ) ) : '';
        if ( ! in_array( $action, array( 'confirm', 'unsubscribe' ), true ) ) return;
        if ( empty( $_GET['token'] ) ) return;

        $token  = sanitize_text_field( wp_unslash( $_GET['token'] ) );
        $result = ( 'unsubscribe' === $action ) ? Hem_Newsletter_Subscriber::unsubscribe( $token ) : Hem_Newsletter_Subscriber::confirm( $token );

        $blog    = get_bloginfo( 'name' );
        $icon    = ! empty( $result['success'] ) ? '✅' : '❌';
        $message = ! empty( $result['message'] ) ? $result['message'] : __( 'Request processed.', 'hem-newsletter' );

        wp_die(
            "<div style='text-align:center;font-family:Georgia,serif;padding:40px 20px;max-width:500px;margin:0 auto;'>
                <p style='font-size:48px;margin:0 0 16px;'>" . esc_html( $icon ) . "</p>
                <h1 style='font-size:24px;font-weight:400;color:#1a1a1a;margin:0 0 12px;'>" . esc_html( $blog ) . "</h1>
                <p style='font-size:16px;color:#444;line-height:1.6;margin:0 0 24px;'>" . esc_html( $message ) . "</p>
                <a href='" . esc_url( home_url( '/' ) ) . "' style='display:inline-block;background:#1a1a1a;color:#fff;text-decoration:none;padding:12px 28px;border-radius:4px;font-size:14px;'>
                    " . esc_html__( 'Go to Homepage', 'hem-newsletter' ) . "
                </a>
            </div>",
            esc_html( $blog ) . ' — ' . esc_html__( 'Newsletter', 'hem-newsletter' ),
            array( 'response' => 200 )
        );
    }
}
