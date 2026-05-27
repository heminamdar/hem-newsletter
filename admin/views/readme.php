<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap hem-nl-wrap">
  <div class="hem-nl-header">
    <div class="hem-nl-header-left">
      <h1 class="hem-nl-title"><span class="dashicons dashicons-media-document"></span> <?php esc_html_e( 'Hem Newsletter Readme', 'hem-newsletter' ); ?></h1>
      <p class="hem-nl-tagline"><?php esc_html_e( 'A quick guide to email sending, delivery, and subscriber privacy.', 'hem-newsletter' ); ?></p>
    </div>
  </div>

  <div class="hem-nl-card">
    <h2><?php esc_html_e( 'How email sending works', 'hem-newsletter' ); ?></h2>
    <p><?php esc_html_e( 'wp_mail sends email through your WordPress hosting/server setup. It is easy, but if the server IP is weak, blacklisted, or compromised, your emails may go to spam or may not be delivered reliably.', 'hem-newsletter' ); ?></p>
    <p><?php esc_html_e( 'SMTP sends email through a real mailbox such as your domain email account or Gmail. This is usually better for delivery because the email is authenticated through that account.', 'hem-newsletter' ); ?></p>
  </div>

  <div class="hem-nl-card">
    <h2><?php esc_html_e( 'Recommended setup', 'hem-newsletter' ); ?></h2>
    <ol>
      <li><?php esc_html_e( 'Go to Settings.', 'hem-newsletter' ); ?></li>
      <li><?php esc_html_e( 'Enter From Name and From Email.', 'hem-newsletter' ); ?></li>
      <li><?php esc_html_e( 'Choose Domain SMTP or Google / Gmail.', 'hem-newsletter' ); ?></li>
      <li><?php esc_html_e( 'Enter the mailbox username and password. For Gmail, use a Google App Password.', 'hem-newsletter' ); ?></li>
      <li><?php esc_html_e( 'Save settings and send a test email.', 'hem-newsletter' ); ?></li>
    </ol>
    <p><?php esc_html_e( 'If the test fails, open SMTP Settings and enter the exact SMTP host, port, and encryption shown in your hosting control panel.', 'hem-newsletter' ); ?></p>
  </div>

  <div class="hem-nl-card">
    <h2><?php esc_html_e( 'Subscriber privacy and unsubscribe', 'hem-newsletter' ); ?></h2>
    <p><?php esc_html_e( 'The plugin stores only the subscriber email address, subscription status, confirmation token, unsubscribe token, and subscription dates in your WordPress database.', 'hem-newsletter' ); ?></p>
    <p><?php esc_html_e( 'Broadcast emails include an unsubscribe link so subscribers can remove themselves. Site owners can also delete subscribers from the Subscribers tab.', 'hem-newsletter' ); ?></p>
    <p><?php esc_html_e( 'For best compliance, mention this newsletter signup in your website privacy policy.', 'hem-newsletter' ); ?></p>
  </div>
</div>
