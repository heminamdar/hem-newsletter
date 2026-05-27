<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Hem_Newsletter_Admin {

    public function __construct() {
        add_action( 'admin_menu',            array( $this, 'register_menus' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_post_hem_nl_delete_subscriber', array( $this, 'handle_delete' ) );
        add_action( 'admin_post_hem_nl_save_settings',     array( $this, 'handle_save_settings' ) );
        add_action( 'admin_post_hem_nl_send_test_email',    array( $this, 'handle_send_test_email' ) );
        add_action( 'add_meta_boxes',        array( $this, 'add_broadcast_meta_box' ) );
        add_action( 'save_post_post',        array( $this, 'save_broadcast_meta' ) );
        add_action( 'transition_post_status', array( $this, 'send_broadcast_on_publish' ), 20, 3 );
        add_action( 'admin_notices',         array( $this, 'admin_notices' ) );
    }

    // ------------------------------------------------------------ menus

    public function register_menus() {
        add_menu_page(
            __( 'Newsletter by Hem', 'hem-newsletter' ),
            __( 'Newsletter by Hem', 'hem-newsletter' ),
            'manage_options',
            'hem-newsletter',
            array( $this, 'page_subscribers' ),
            'dashicons-email-alt',
            26
        );
        add_submenu_page(
            'hem-newsletter',
            __( 'Subscribers', 'hem-newsletter' ),
            __( 'Subscribers', 'hem-newsletter' ),
            'manage_options',
            'hem-newsletter',
            array( $this, 'page_subscribers' )
        );
        add_submenu_page(
            'hem-newsletter',
            __( 'Settings', 'hem-newsletter' ),
            __( 'Settings', 'hem-newsletter' ),
            'manage_options',
            'hem-newsletter-settings',
            array( $this, 'page_settings' )
        );
        add_submenu_page(
            'hem-newsletter',
            __( 'Readme', 'hem-newsletter' ),
            __( 'Readme', 'hem-newsletter' ),
            'manage_options',
            'hem-newsletter-readme',
            array( $this, 'page_readme' )
        );
    }

    public function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'hem-newsletter' ) === false && $hook !== 'post.php' && $hook !== 'post-new.php' ) return;
        wp_enqueue_style(
            'hem-newsletter-admin',
            HEM_NL_PLUGIN_URL . 'admin/admin.css',
            array(),
            HEM_NL_VERSION
        );
    }

    // --------------------------------------------------- subscribers page

    public function page_subscribers() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( __( 'Unauthorized.', 'hem-newsletter' ) );

        $filter      = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';
        $subscribers = Hem_Newsletter_DB::get_all_subscribers( $filter );
        $total_conf  = Hem_Newsletter_DB::count_subscribers( 'confirmed' );
        $total_pend  = Hem_Newsletter_DB::count_subscribers( 'pending' );

        include HEM_NL_PLUGIN_DIR . 'admin/views/subscribers.php';
    }

    // ---------------------------------------------------- settings page

    public function page_settings() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( __( 'Unauthorized.', 'hem-newsletter' ) );
        $raw_settings = get_option( 'hem_newsletter_smtp', array() );
        $settings = wp_parse_args( $raw_settings, array(
            'from_name'     => get_bloginfo( 'name' ),
            'from_email'    => get_option( 'admin_email' ),
            'host'          => '',
            'port'          => 587,
            'encryption'    => 'tls',
            'username'      => '',
            'password'      => '',
            'use_smtp'      => '1',
            'use_wp_mail'   => '0',
            'magic_smtp'    => '1',
            'smtp_provider' => 'smtp',
            'stripe_url'    => 'https://donate.stripe.com/dRm3cv1h08Jx0tneJC0Fi00',
            'button_color'  => '#1a1a1a',
            'button_text_color' => '#ffffff',
            'button_hover_color' => '#2563eb',
            'input_radius'  => 6,
            'button_radius' => 6,
            'button_position' => 'outside',
            'form_width'    => 520,
            'form_height'   => 44,
            'button_width'  => 120,
            'button_height' => 42,
            'form_title'    => 'Subscribe for new post alerts.',
            'input_placeholder' => 'email address',
            'button_label' => 'Subscribe',
            'email_footer'  => 'You are receiving this email as you have subscribed to {site_name}. Click here to unsubscribe: {unsubscribe_link}',
        ) );
        $detected_smtp = array();
        if ( class_exists( 'Hem_Newsletter_Mailer' ) ) {
            $detected_smtp = Hem_Newsletter_Mailer::get_detected_smtp_for_display();
        }
        include HEM_NL_PLUGIN_DIR . 'admin/views/settings.php';
    }

    public function page_readme() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( __( 'Unauthorized.', 'hem-newsletter' ) );
        include HEM_NL_PLUGIN_DIR . 'admin/views/readme.php';
    }

    // ----------------------------------------------- action handlers

    public function handle_delete() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( __( 'Unauthorized.', 'hem-newsletter' ) );
        check_admin_referer( 'hem_nl_delete_subscriber' );
        $id = isset( $_POST['subscriber_id'] ) ? absint( wp_unslash( $_POST['subscriber_id'] ) ) : 0;
        if ( $id ) Hem_Newsletter_DB::delete_subscriber( $id );
        wp_redirect( add_query_arg( array( 'page' => 'hem-newsletter', 'hem_deleted' => 1 ), admin_url( 'admin.php' ) ) );
        exit;
    }

    public function handle_save_settings() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( __( 'Unauthorized.', 'hem-newsletter' ) );
        check_admin_referer( 'hem_nl_save_settings' );

        $existing = get_option( 'hem_newsletter_smtp', array() );

        $smtp_provider = sanitize_key( wp_unslash( $_POST['smtp_provider'] ?? 'smtp' ) );
        $use_wp_mail  = isset( $_POST['use_wp_mail'] ) ? '1' : '0';
        $from_email   = sanitize_email( wp_unslash( $_POST['from_email'] ?? '' ) );
        if ( ! is_email( $from_email ) ) {
            $from_email = get_option( 'admin_email' );
        }
        $data = array(
            'from_name'     => sanitize_text_field( wp_unslash( $_POST['from_name']  ?? '' ) ),
            'from_email'    => $from_email,
            'host'          => sanitize_text_field( wp_unslash( $_POST['smtp_host']   ?? '' ) ),
            'port'          => absint( wp_unslash( $_POST['smtp_port']                ?? 587 ) ),
            'encryption'    => sanitize_key( wp_unslash( $_POST['smtp_encryption']    ?? 'tls' ) ),
            'username'      => sanitize_text_field( wp_unslash( $_POST['smtp_user']   ?? '' ) ),
            'password'      => sanitize_text_field( wp_unslash( $_POST['smtp_pass'] ?? '' ) ),
            'use_smtp'      => $use_wp_mail === '1' ? '0' : '1',
            'use_wp_mail'   => $use_wp_mail,
            'magic_smtp'    => isset( $_POST['magic_smtp'] ) ? '1' : '0',
            'smtp_provider' => in_array( $smtp_provider, array( 'smtp', 'gmail' ), true ) ? $smtp_provider : 'smtp',
            'button_color'  => sanitize_hex_color( wp_unslash( $_POST['button_color'] ?? '#1a1a1a' ) ) ?: '#1a1a1a',
            'button_text_color' => sanitize_hex_color( wp_unslash( $_POST['button_text_color'] ?? '#ffffff' ) ) ?: '#ffffff',
            'button_hover_color' => sanitize_hex_color( wp_unslash( $_POST['button_hover_color'] ?? '#2563eb' ) ) ?: '#2563eb',
            'input_radius'  => min( 50, max( 0, absint( wp_unslash( $_POST['input_radius'] ?? 6 ) ) ) ),
            'button_radius' => min( 50, max( 0, absint( wp_unslash( $_POST['button_radius'] ?? 6 ) ) ) ),
            'button_position' => in_array( sanitize_key( wp_unslash( $_POST['button_position'] ?? 'outside' ) ), array( 'outside', 'inside' ), true ) ? sanitize_key( wp_unslash( $_POST['button_position'] ?? 'outside' ) ) : 'outside',
            'form_width'    => min( 900, max( 240, absint( wp_unslash( $_POST['form_width'] ?? 520 ) ) ) ),
            'form_height'   => min( 80, max( 36, absint( wp_unslash( $_POST['form_height'] ?? 44 ) ) ) ),
            'button_width'  => min( 260, max( 80, absint( wp_unslash( $_POST['button_width'] ?? 120 ) ) ) ),
            'button_height' => min( 78, max( 28, absint( wp_unslash( $_POST['button_height'] ?? 42 ) ) ) ),
            'form_title'    => sanitize_text_field( wp_unslash( $_POST['form_title'] ?? 'Subscribe for new post alerts.' ) ),
            'input_placeholder' => sanitize_text_field( wp_unslash( $_POST['input_placeholder'] ?? 'email address' ) ),
            'button_label' => sanitize_text_field( wp_unslash( $_POST['button_label'] ?? 'Subscribe' ) ),
            'email_footer'  => sanitize_textarea_field( wp_unslash( $_POST['email_footer'] ?? '' ) ),
            'stripe_url'    => esc_url_raw( $existing['stripe_url'] ?? 'https://donate.stripe.com/dRm3cv1h08Jx0tneJC0Fi00' ),
        );

        update_option( 'hem_newsletter_smtp', $data );
        if ( class_exists( 'Hem_Newsletter_Mailer' ) ) { Hem_Newsletter_Mailer::clear_detection_cache(); }
        wp_redirect( add_query_arg( array(
            'page'      => 'hem-newsletter-settings',
            'hem_saved' => 1,
        ), admin_url( 'admin.php' ) ) );
        exit;
    }


    public function handle_send_test_email() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( __( 'Unauthorized.', 'hem-newsletter' ) );
        check_admin_referer( 'hem_nl_send_test_email' );

        $to = sanitize_email( wp_unslash( $_POST['test_email'] ?? get_option( 'admin_email' ) ) );
        $ok = Hem_Newsletter_Mailer::send_test( $to );
        $error = $ok ? '' : Hem_Newsletter_Mailer::get_last_error();
        set_transient( 'hem_newsletter_test_result_' . get_current_user_id(), array(
            'ok'    => $ok ? '1' : '0',
            'email' => $to,
            'error' => $error,
        ), MINUTE_IN_SECONDS * 5 );

        wp_redirect( add_query_arg( array(
            'page'         => 'hem-newsletter-settings',
            'hem_testmail' => $ok ? '1' : '0',
        ), admin_url( 'admin.php' ) ) );
        exit;
    }

    // -------------------------------------------------- post meta box

    public function add_broadcast_meta_box() {
        add_meta_box(
            'hem_newsletter_broadcast',
            __( 'Newsletter Broadcast', 'hem-newsletter' ),
            array( $this, 'render_broadcast_meta_box' ),
            'post',
            'side',
            'high'
        );
    }

    public function render_broadcast_meta_box( $post ) {
        wp_nonce_field( 'hem_nl_broadcast_meta', 'hem_nl_broadcast_nonce' );
        $checked     = get_post_meta( $post->ID, '_hem_nl_broadcast', true );
        $was_sent    = get_post_meta( $post->ID, '_hem_nl_broadcast_sent', true );
        $sent_count  = get_post_meta( $post->ID, '_hem_nl_broadcast_count', true );
        $total       = Hem_Newsletter_DB::count_subscribers( 'confirmed' );
        ?>
        <div class="hem-nl-meta-box">
          <?php if ( $was_sent ) : ?>
            <p class="hem-nl-sent-notice">
              ✅ <?php printf( esc_html__( 'Sent to %d subscriber(s).', 'hem-newsletter' ), (int) $sent_count ); ?>
            </p>
          <?php else : ?>
            <label>
              <input type="checkbox" name="hem_nl_broadcast" value="1" <?php checked( $checked, '1' ); ?>>
              <?php printf( esc_html__( 'Send to %d newsletter subscriber(s) on publish/update', 'hem-newsletter' ), $total ); ?>
            </label>
            <p class="description"><?php esc_html_e( 'Uncheck if you do not want to broadcast this post.', 'hem-newsletter' ); ?></p>
          <?php endif; ?>
        </div>
        <?php
    }

    public function save_broadcast_meta( $post_id ) {
        if ( ! isset( $_POST['hem_nl_broadcast_nonce'] ) ) return;
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hem_nl_broadcast_nonce'] ) ), 'hem_nl_broadcast_meta' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        $already_sent = get_post_meta( $post_id, '_hem_nl_broadcast_sent', true );
        if ( $already_sent ) return; // Only send once

        $send = isset( $_POST['hem_nl_broadcast'] ) ? '1' : '0';
        update_post_meta( $post_id, '_hem_nl_broadcast', $send );

        // If this is already a published post, send now.
        if ( $send === '1' && get_post_status( $post_id ) === 'publish' ) {
            $this->send_broadcast_for_post( $post_id );
        }
    }

    /**
     * Gutenberg/WordPress often changes the post status after save_post.
     * This catches the actual publish transition so the checkbox reliably sends.
     */
    public function send_broadcast_on_publish( $new_status, $old_status, $post ) {
        if ( ! $post || $post->post_type !== 'post' ) return;
        if ( $new_status !== 'publish' || $old_status === 'publish' ) return;
        if ( get_post_meta( $post->ID, '_hem_nl_broadcast', true ) !== '1' ) return;

        $this->send_broadcast_for_post( $post->ID );
    }

    private function send_broadcast_for_post( $post_id ) {
        if ( get_post_meta( $post_id, '_hem_nl_broadcast_sent', true ) ) return;

        $count = Hem_Newsletter_Mailer::broadcast_post( $post_id );
        update_post_meta( $post_id, '_hem_nl_broadcast_sent', '1' );
        update_post_meta( $post_id, '_hem_nl_broadcast_count', $count );
    }

    // --------------------------------------------------- admin notices

    public function admin_notices() {
        if ( isset( $_GET['hem_deleted'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['hem_deleted'] ) ) ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Subscriber deleted.', 'hem-newsletter' ) . '</p></div>';
        }
        if ( isset( $_GET['hem_saved'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['hem_saved'] ) ) ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'hem-newsletter' ) . '</p></div>';
        }
    }
}
