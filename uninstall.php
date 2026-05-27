<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

global $wpdb;
$table = $wpdb->prefix . 'hem_newsletter_subscribers';
$wpdb->query( "DROP TABLE IF EXISTS {$table}" );

delete_option( 'hem_newsletter_version' );
delete_option( 'hem_newsletter_smtp' );

delete_post_meta_by_key( '_hem_nl_broadcast' );
delete_post_meta_by_key( '_hem_nl_broadcast_sent' );
delete_post_meta_by_key( '_hem_nl_broadcast_count' );
