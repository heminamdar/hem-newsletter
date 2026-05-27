<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Hem_Newsletter_DB {

    const TABLE_SUBSCRIBERS = 'hem_newsletter_subscribers';

    public static function install() {
        global $wpdb;
        $table      = $wpdb->prefix . self::TABLE_SUBSCRIBERS;
        $charset    = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id                BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            email             VARCHAR(191)        NOT NULL,
            status            VARCHAR(20)         NOT NULL DEFAULT 'pending',
            token             VARCHAR(64)         NOT NULL DEFAULT '',
            unsubscribe_token VARCHAR(64)         NOT NULL DEFAULT '',
            subscribed_at     DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
            confirmed_at      DATETIME            NULL,
            PRIMARY KEY (id),
            UNIQUE KEY email (email),
            KEY status (status),
            KEY token (token),
            KEY unsubscribe_token (unsubscribe_token)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        self::ensure_unsubscribe_tokens();
        update_option( 'hem_newsletter_version', HEM_NL_VERSION );
    }

    public static function token() {
        return wp_generate_password( 64, false, false );
    }

    public static function ensure_unsubscribe_tokens() {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_SUBSCRIBERS;
        $rows = $wpdb->get_results( "SELECT id FROM {$table} WHERE unsubscribe_token = '' OR unsubscribe_token IS NULL" );
        if ( empty( $rows ) ) return;
        foreach ( $rows as $row ) {
            $wpdb->update(
                $table,
                array( 'unsubscribe_token' => self::token() ),
                array( 'id' => absint( $row->id ) ),
                array( '%s' ),
                array( '%d' )
            );
        }
    }

    // ------------------------------------------------------------------ read

    public static function get_all_subscribers( $status = '' ) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_SUBSCRIBERS;
        if ( $status ) {
            return $wpdb->get_results(
                $wpdb->prepare( "SELECT * FROM {$table} WHERE status = %s ORDER BY subscribed_at DESC", sanitize_key( $status ) )
            );
        }
        return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY subscribed_at DESC" );
    }

    public static function get_subscriber_by_email( $email ) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_SUBSCRIBERS;
        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table} WHERE email = %s", sanitize_email( $email ) )
        );
    }

    public static function get_subscriber_by_token( $token ) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_SUBSCRIBERS;
        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table} WHERE token = %s", sanitize_text_field( $token ) )
        );
    }

    public static function get_subscriber_by_unsubscribe_token( $token ) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_SUBSCRIBERS;
        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table} WHERE unsubscribe_token = %s", sanitize_text_field( $token ) )
        );
    }

    // ----------------------------------------------------------------- write

    public static function add_subscriber( $email ) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_SUBSCRIBERS;
        $token = self::token();
        $unsubscribe_token = self::token();

        $inserted = $wpdb->insert( $table, array(
            'email'             => sanitize_email( $email ),
            'status'            => 'pending',
            'token'             => $token,
            'unsubscribe_token' => $unsubscribe_token,
        ), array( '%s', '%s', '%s', '%s' ) );

        return $inserted ? $token : false;
    }

    public static function refresh_confirmation_token( $email ) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_SUBSCRIBERS;
        $token = self::token();
        $updated = $wpdb->update(
            $table,
            array( 'token' => $token ),
            array( 'email' => sanitize_email( $email ) ),
            array( '%s' ),
            array( '%s' )
        );
        return false === $updated ? false : $token;
    }

    public static function confirm_subscriber( $token ) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_SUBSCRIBERS;
        return $wpdb->update(
            $table,
            array(
                'status'       => 'confirmed',
                'confirmed_at' => current_time( 'mysql' ),
                'token'        => '',
            ),
            array( 'token' => sanitize_text_field( $token ) ),
            array( '%s', '%s', '%s' ),
            array( '%s' )
        );
    }

    public static function delete_subscriber( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_SUBSCRIBERS;
        return $wpdb->delete( $table, array( 'id' => absint( $id ) ), array( '%d' ) );
    }

    public static function unsubscribe_by_token( $token ) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_SUBSCRIBERS;
        return $wpdb->delete( $table, array( 'unsubscribe_token' => sanitize_text_field( $token ) ), array( '%s' ) );
    }

    public static function count_subscribers( $status = 'confirmed' ) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_SUBSCRIBERS;
        return (int) $wpdb->get_var(
            $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE status = %s", sanitize_key( $status ) )
        );
    }
}
