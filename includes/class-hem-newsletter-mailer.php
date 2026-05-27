<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Hem_Newsletter_Mailer {

    private static $last_error = '';

    private static function ensure_phpmailer_loaded() {
        if ( ! class_exists( '\PHPMailer\PHPMailer\PHPMailer' ) ) {
            require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
            require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
            require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
        }
    }

    private static function get_settings() {
        return wp_parse_args( get_option( 'hem_newsletter_smtp', array() ), array(
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
            'email_footer'  => 'You are receiving this email as you have subscribed to {site_name}. Click here to unsubscribe: {unsubscribe_link}',
        ) );
    }

    private static function normalize_host( $host ) {
        $host = trim( strtolower( (string) $host ) );
        $host = preg_replace( '#^ssl://#', '', $host );
        $host = preg_replace( '#^tls://#', '', $host );
        return $host;
    }

    private static function domain_from_email( $email ) {
        $parts = explode( '@', sanitize_email( $email ) );
        return isset( $parts[1] ) ? strtolower( $parts[1] ) : '';
    }

    private static function add_candidate( &$candidates, $host, $port, $encryption ) {
        $host = self::normalize_host( $host );
        if ( empty( $host ) ) return;
        $key = $host . ':' . (int) $port . ':' . $encryption;
        $candidates[ $key ] = array(
            'host'       => $host,
            'port'       => (int) $port,
            'encryption' => $encryption,
        );
    }

    private static function smtp_candidates( $settings ) {
        $candidates = array();
        $domain = self::domain_from_email( $settings['from_email'] );

        if ( ! empty( $settings['smtp_provider'] ) && $settings['smtp_provider'] === 'gmail' ) {
            self::add_candidate( $candidates, 'smtp.gmail.com', 587, 'tls' );
            self::add_candidate( $candidates, 'smtp.gmail.com', 465, 'ssl' );
        }

        if ( ! empty( $settings['host'] ) ) {
            self::add_candidate( $candidates, $settings['host'], $settings['port'], $settings['encryption'] );
        }

        // Hosting-control-panel servers often expose the real mail host through these values.
        $server_values = array(
            gethostname(),
            php_uname( 'n' ),
            isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '',
            isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '',
        );
        foreach ( $server_values as $server_host ) {
            $server_host = self::normalize_host( preg_replace( '/:\d+$/', '', (string) $server_host ) );
            if ( $server_host && false === strpos( $server_host, $domain ) ) {
                self::add_candidate( $candidates, $server_host, 465, 'ssl' );
                self::add_candidate( $candidates, $server_host, 587, 'tls' );
            }
        }

        if ( $domain ) {
            foreach ( array( 'mail.' . $domain, 'smtp.' . $domain, $domain ) as $host ) {
                self::add_candidate( $candidates, $host, 465, 'ssl' );
                self::add_candidate( $candidates, $host, 587, 'tls' );
                self::add_candidate( $candidates, $host, 25, '' );
            }

            if ( function_exists( 'dns_get_record' ) ) {
                $mx_records = @dns_get_record( $domain, DNS_MX );
                if ( is_array( $mx_records ) ) {
                    usort( $mx_records, function( $a, $b ) {
                        return (int) ( $a['pri'] ?? 0 ) <=> (int) ( $b['pri'] ?? 0 );
                    } );
                    foreach ( $mx_records as $mx ) {
                        if ( ! empty( $mx['target'] ) ) {
                            self::add_candidate( $candidates, $mx['target'], 465, 'ssl' );
                            self::add_candidate( $candidates, $mx['target'], 587, 'tls' );
                        }
                    }
                }
            }
        }

        return array_values( $candidates );
    }

    private static function socket_works( $host, $port ) {
        $host = self::normalize_host( $host );
        if ( empty( $host ) || empty( $port ) ) return false;
        $target = ( (int) $port === 465 ? 'ssl://' : '' ) . $host;
        $errno = 0;
        $errstr = '';
        $conn = @fsockopen( $target, (int) $port, $errno, $errstr, 5 );
        if ( is_resource( $conn ) ) {
            fclose( $conn );
            return true;
        }
        return false;
    }

    private static function smtp_login_works( $candidate, $settings ) {
        $from_email = sanitize_email( $settings['from_email'] );
        $username   = ! empty( $settings['username'] ) ? sanitize_text_field( $settings['username'] ) : $from_email;
        $password   = (string) ( $settings['password'] ?? '' );
        if ( empty( $username ) || empty( $password ) ) return false;

        self::ensure_phpmailer_loaded();
        $mail = new \PHPMailer\PHPMailer\PHPMailer( true );
        try {
            $mail->isSMTP();
            $mail->Host       = self::normalize_host( $candidate['host'] );
            $mail->Port       = (int) $candidate['port'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $username;
            $mail->Password   = $password;
            $mail->Timeout    = 8;
            $mail->SMTPDebug  = 0;
            $secure = in_array( $candidate['encryption'], array( 'tls', 'ssl' ), true ) ? $candidate['encryption'] : '';
            $mail->SMTPSecure = $secure;
            $mail->SMTPAutoTLS = ( $secure === 'tls' );
            // Many shared hosts use a server-name certificate while users enter mail.domain.com.
            // This keeps SMTP usable on those hosts while still using encrypted transport.
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ),
            );
            $ok = $mail->smtpConnect();
            if ( $ok ) {
                $mail->smtpClose();
                return true;
            }
        } catch ( \Throwable $e ) {
            self::$last_error = $e->getMessage();
        } catch ( \Exception $e ) {
            self::$last_error = $e->getMessage();
        }
        return false;
    }

    private static function detect_smtp_server( $settings ) {
        $has_login = ! empty( $settings['password'] ) && ( ! empty( $settings['username'] ) || is_email( $settings['from_email'] ) );
        $cache_key = 'hem_newsletter_detected_smtp_' . md5( sanitize_email( $settings['from_email'] ) . '|' . home_url() . '|' . ( $has_login ? md5( (string) $settings['password'] ) : 'no-login' ) );
        $cached = get_transient( $cache_key );
        if ( is_array( $cached ) && ! empty( $cached['host'] ) ) {
            return $cached;
        }

        $fallback = array(
            'host'       => self::normalize_host( $settings['host'] ),
            'port'       => (int) $settings['port'],
            'encryption' => $settings['encryption'],
        );

        foreach ( self::smtp_candidates( $settings ) as $candidate ) {
            $works = $has_login ? self::smtp_login_works( $candidate, $settings ) : self::socket_works( $candidate['host'], $candidate['port'] );
            if ( $works ) {
                set_transient( $cache_key, $candidate, DAY_IN_SECONDS );
                return $candidate;
            }
        }

        return $fallback;
    }

    public static function clear_detection_cache() {
        global $wpdb;
        $transient_like = $wpdb->esc_like( '_transient_hem_newsletter_detected_smtp_' ) . '%';
        $timeout_like   = $wpdb->esc_like( '_transient_timeout_hem_newsletter_detected_smtp_' ) . '%';
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                $transient_like,
                $timeout_like
            )
        );
    }

    public static function get_last_error() {
        return self::$last_error;
    }

    public static function configure_smtp( $phpmailer ) {
        $s = self::get_settings();
        if ( ! empty( $s['use_wp_mail'] ) && $s['use_wp_mail'] === '1' ) return;
        $use_smtp = ! empty( $s['use_smtp'] ) && $s['use_smtp'] === '1';
        if ( ! $use_smtp ) return;

        $from_email = sanitize_email( $s['from_email'] );
        $username   = ! empty( $s['username'] ) ? sanitize_text_field( $s['username'] ) : $from_email;
        $password   = (string) ( $s['password'] ?? '' );

        // SMTP authentication requires a mailbox password. Without it, fall back to WordPress mail.
        if ( empty( $username ) || empty( $password ) ) return;

        $detected = ( ! empty( $s['magic_smtp'] ) && $s['magic_smtp'] === '1' ) ? self::detect_smtp_server( $s ) : array(
            'host'       => self::normalize_host( $s['host'] ),
            'port'       => (int) $s['port'],
            'encryption' => $s['encryption'],
        );

        if ( empty( $detected['host'] ) ) return;

        $phpmailer->isSMTP();
        $phpmailer->Host       = $detected['host'];
        $phpmailer->Port       = (int) $detected['port'];
        $phpmailer->SMTPAuth   = true;
        $phpmailer->Username   = $username;
        $phpmailer->Password   = $password;
        $phpmailer->SMTPSecure = in_array( $detected['encryption'], array( 'tls', 'ssl' ), true ) ? $detected['encryption'] : '';
        $phpmailer->SMTPAutoTLS = ( $phpmailer->SMTPSecure === 'tls' );
        $phpmailer->Timeout    = 15;
        $phpmailer->SMTPOptions = array(
            'ssl' => array(
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ),
        );
    }

    public static function get_detected_smtp_for_display() {
        $s = self::get_settings();
        return self::detect_smtp_server( $s );
    }

    private static function get_from() {
        $s = self::get_settings();
        $from_email = sanitize_email( $s['from_email'] );
        if ( ! is_email( $from_email ) ) $from_email = get_option( 'admin_email' );

        $from_name = sanitize_text_field( $s['from_name'] );
        if ( $from_name === '' ) $from_name = get_bloginfo( 'name' );

        return array( 'name' => $from_name, 'email' => $from_email );
    }

    private static function headers() {
        $from = self::get_from();
        return array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from['name'] . ' <' . $from['email'] . '>',
            'Reply-To: ' . $from['name'] . ' <' . $from['email'] . '>',
        );
    }

    public static function capture_wp_mail_failed( $wp_error ) {
        if ( is_wp_error( $wp_error ) ) {
            self::$last_error = $wp_error->get_error_message();
        }
    }

    private static function send_html_mail( $to, $subject, $body, $extra_headers = array() ) {
        self::$last_error = '';
        add_action( 'phpmailer_init', array( __CLASS__, 'configure_smtp' ) );
        add_action( 'wp_mail_failed', array( __CLASS__, 'capture_wp_mail_failed' ) );
        $headers = array_merge( self::headers(), (array) $extra_headers );
        $sent = wp_mail( $to, $subject, $body, $headers );
        remove_action( 'wp_mail_failed', array( __CLASS__, 'capture_wp_mail_failed' ) );
        remove_action( 'phpmailer_init', array( __CLASS__, 'configure_smtp' ) );
        if ( ! $sent && self::$last_error === '' ) {
            self::$last_error = __( 'WordPress could not send the email. Please check your mail settings.', 'hem-newsletter' );
        }
        return $sent;
    }

    public static function send_test( $to = '' ) {
        $to = sanitize_email( $to ?: get_option( 'admin_email' ) );
        if ( ! is_email( $to ) ) return false;
        $blog = get_bloginfo( 'name' );
        $subject = sprintf( __( 'Test email from %s', 'hem-newsletter' ), $blog );
        $sample_unsubscribe = add_query_arg( array(
            'hem_nl_action' => 'unsubscribe',
            'token'         => 'sample-test-link',
        ), home_url( '/' ) );
        $footer = self::get_email_footer_html( $sample_unsubscribe );
        $body = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head><body style="margin:0;padding:0;background:#f4f4f4;font-family:Georgia,Times New Roman,serif;"><div style="max-width:600px;margin:40px auto;background:#ffffff;border-radius:4px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);"><div style="background:#1a1a1a;padding:36px 40px;text-align:center;"><h1 style="color:#ffffff;font-size:22px;margin:0;font-weight:400;letter-spacing:1px;">' . esc_html( $blog ) . '</h1></div><div style="padding:40px;color:#333333;font-size:16px;line-height:1.7;"><h2 style="font-size:24px;font-weight:400;color:#1a1a1a;margin:0 0 16px;line-height:1.3;">Test Email</h2><div style="color:#555555;font-style:italic;border-left:3px solid #e0e0e0;padding-left:16px;margin:24px 0;"><p style="margin:0 0 20px;">This is a test email. If you have received this email - your configuration is OK.</p><p style="margin:0;">When you publish new posts, your post excerpt will appear here instead of this message.</p></div><div style="text-align:center;margin:32px 0;"><a href="' . esc_url( home_url( '/' ) ) . '" style="display:inline-block;background:#1a1a1a;color:#ffffff;text-decoration:none;padding:14px 36px;border-radius:3px;font-size:15px;letter-spacing:.5px;">Read Full Post →</a></div></div><div style="padding:0 40px 34px;color:#777777;font-size:12px;line-height:1.6;text-align:center;">' . $footer . '</div></div></body></html>';
        return self::send_html_mail( $to, $subject, $body );
    }

    public static function send_confirmation( $email, $token ) {
        $confirm = add_query_arg( array(
            'hem_nl_action' => 'confirm',
            'token'         => rawurlencode( $token ),
        ), home_url( '/' ) );
        $blog    = get_bloginfo( 'name' );
        $subject = sprintf( __( 'Confirm your subscription to %s', 'hem-newsletter' ), $blog );
        ob_start();
        include HEM_NL_PLUGIN_DIR . 'includes/email-confirm.php';
        $body = ob_get_clean();
        return self::send_html_mail( $email, $subject, $body );
    }


    public static function get_email_footer_html( $unsubscribe_url = '' ) {
        $s = self::get_settings();
        $template = isset( $s['email_footer'] ) ? (string) $s['email_footer'] : '';
        if ( trim( $template ) === '' ) {
            $template = 'You are receiving this email as you have subscribed to {site_name}. Click here to unsubscribe: {unsubscribe_link}';
        }
        $site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
        $unsubscribe_url = esc_url( $unsubscribe_url );
        $link_html = $unsubscribe_url ? '<a href="' . $unsubscribe_url . '">' . esc_html__( 'unsubscribe', 'hem-newsletter' ) . '</a>' : esc_html__( 'unsubscribe', 'hem-newsletter' );

        $escaped = esc_html( $template );
        $escaped = str_replace( '{site_name}', esc_html( $site_name ), $escaped );
        $escaped = str_replace( '{unsubscribe_link}', $link_html, $escaped );
        $escaped = nl2br( $escaped );

        // Ensure every broadcast footer contains an unsubscribe link.
        if ( $unsubscribe_url && false === strpos( $escaped, $unsubscribe_url ) ) {
            $escaped .= '<br><a href="' . $unsubscribe_url . '">' . esc_html__( 'Unsubscribe', 'hem-newsletter' ) . '</a>';
        }

        return $escaped;
    }

    public static function broadcast_post( $post_id ) {
        $post = get_post( $post_id );
        if ( ! $post ) return 0;
        $subscribers = Hem_Newsletter_DB::get_all_subscribers( 'confirmed' );
        if ( empty( $subscribers ) ) return 0;
        $subject = sprintf( __( 'New post: %s', 'hem-newsletter' ), get_the_title( $post ) );
        $sent = 0;
        foreach ( $subscribers as $subscriber ) {
            $unsubscribe = '';
            if ( ! empty( $subscriber->unsubscribe_token ) ) {
                $unsubscribe = add_query_arg( array(
                    'hem_nl_action' => 'unsubscribe',
                    'token'         => rawurlencode( $subscriber->unsubscribe_token ),
                ), home_url( '/' ) );
            }
            $email_footer_html = self::get_email_footer_html( $unsubscribe );
            ob_start();
            include HEM_NL_PLUGIN_DIR . 'includes/email-broadcast.php';
            $body = ob_get_clean();
            $extra_headers = array();
            if ( ! empty( $unsubscribe ) ) {
                $extra_headers[] = 'List-Unsubscribe: <' . esc_url_raw( $unsubscribe ) . '>';
            }
            if ( self::send_html_mail( $subscriber->email, $subject, $body, $extra_headers ) ) $sent++;
        }
        return $sent;
    }
}
