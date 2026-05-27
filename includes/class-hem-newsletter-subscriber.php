<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Hem_Newsletter_Subscriber {

    /**
     * Subscribe an email address. Returns array with 'success' and 'message'.
     */
    public static function subscribe( $email ) {
        $email = sanitize_email( $email );

        if ( ! is_email( $email ) ) {
            return array( 'success' => false, 'message' => __( 'Please enter a valid email address.', 'hem-newsletter' ) );
        }

        $existing = Hem_Newsletter_DB::get_subscriber_by_email( $email );

        if ( $existing ) {
            if ( $existing->status === 'confirmed' ) {
                return array( 'success' => false, 'message' => __( 'This email is already subscribed.', 'hem-newsletter' ) );
            }
            // Resend confirmation and report any mail error clearly.
            $token = ! empty( $existing->token ) ? $existing->token : Hem_Newsletter_DB::refresh_confirmation_token( $email );
            $sent = $token ? Hem_Newsletter_Mailer::send_confirmation( $email, $token ) : false;
            if ( ! $sent ) {
                return array( 'success' => false, 'message' => __( 'The confirmation email could not be sent. Please check the newsletter email/SMTP settings.', 'hem-newsletter' ) );
            }
            return array( 'success' => true, 'message' => __( 'A confirmation email has been resent. Please check your inbox.', 'hem-newsletter' ) );
        }

        $token = Hem_Newsletter_DB::add_subscriber( $email );
        if ( ! $token ) {
            return array( 'success' => false, 'message' => __( 'Something went wrong. Please try again.', 'hem-newsletter' ) );
        }

        $sent = Hem_Newsletter_Mailer::send_confirmation( $email, $token );
        if ( ! $sent ) {
            return array( 'success' => false, 'message' => __( 'You were added, but the confirmation email could not be sent. Please check the newsletter email/SMTP settings.', 'hem-newsletter' ) );
        }

        return array( 'success' => true, 'message' => __( 'Thank you! Please check your email to confirm your subscription.', 'hem-newsletter' ) );
    }

    /**
     * Confirm via token.
     */
    public static function confirm( $token ) {
        $subscriber = Hem_Newsletter_DB::get_subscriber_by_token( $token );
        if ( ! $subscriber ) {
            return array( 'success' => false, 'message' => __( 'Invalid or expired confirmation link.', 'hem-newsletter' ) );
        }
        Hem_Newsletter_DB::confirm_subscriber( $token );
        return array( 'success' => true, 'message' => __( 'Your subscription has been confirmed. Welcome!', 'hem-newsletter' ) );
    }

    /**
     * Unsubscribe via private token.
     */
    public static function unsubscribe( $token ) {
        $subscriber = Hem_Newsletter_DB::get_subscriber_by_unsubscribe_token( $token );
        if ( ! $subscriber ) {
            return array( 'success' => false, 'message' => __( 'Invalid or expired unsubscribe link.', 'hem-newsletter' ) );
        }

        Hem_Newsletter_DB::unsubscribe_by_token( $token );
        return array( 'success' => true, 'message' => __( 'You have been unsubscribed successfully.', 'hem-newsletter' ) );
    }
}

