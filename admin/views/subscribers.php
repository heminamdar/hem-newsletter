<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap hem-nl-wrap">

  <div class="hem-nl-header">
    <div class="hem-nl-header-left">
      <h1 class="hem-nl-title">
        <span class="dashicons dashicons-email-alt"></span>
        <?php esc_html_e( 'Newsletter Subscribers', 'hem-newsletter' ); ?>
      </h1>
      <p class="hem-nl-tagline">
        <?php esc_html_e( 'Complete the settings to start using the plugin.', 'hem-newsletter' ); ?>
      </p>
      <p class="hem-nl-tagline">
        <?php esc_html_e( 'Free Forever — by Hem Inamdar', 'hem-newsletter' ); ?>
      </p>
      <p class="hem-nl-free-note">
        <?php esc_html_e( "No 'hidden ads' in outgoing emails — no 'free to try' then buy!", 'hem-newsletter' ); ?><br>
        <strong><?php esc_html_e( 'This newsletter plugin is really free!', 'hem-newsletter' ); ?></strong>
      </p>
      <p class="hem-nl-donation-note">
        <?php esc_html_e( 'If you like this plugin, consider donating.', 'hem-newsletter' ); ?>
        <a href="<?php echo esc_url( 'https://donate.stripe.com/dRm3cv1h08Jx0tneJC0Fi00' ); ?>" target="_blank" rel="noopener noreferrer">
          <?php esc_html_e( 'Donation link', 'hem-newsletter' ); ?>
        </a>
      </p>
    </div>
    <div class="hem-nl-stats">
      <div class="hem-nl-stat">
        <span class="hem-nl-stat-number"><?php echo esc_html( $total_conf ); ?></span>
        <span class="hem-nl-stat-label"><?php esc_html_e( 'Confirmed', 'hem-newsletter' ); ?></span>
      </div>
      <div class="hem-nl-stat">
        <span class="hem-nl-stat-number"><?php echo esc_html( $total_pend ); ?></span>
        <span class="hem-nl-stat-label"><?php esc_html_e( 'Pending', 'hem-newsletter' ); ?></span>
      </div>
    </div>
  </div>

  <div class="hem-nl-tabs">
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=hem-newsletter' ) ); ?>" class="<?php echo $filter === '' ? 'active' : ''; ?>">
      <?php esc_html_e( 'All', 'hem-newsletter' ); ?>
    </a>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=hem-newsletter&status=confirmed' ) ); ?>" class="<?php echo $filter === 'confirmed' ? 'active' : ''; ?>">
      <?php esc_html_e( 'Confirmed', 'hem-newsletter' ); ?>
    </a>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=hem-newsletter&status=pending' ) ); ?>" class="<?php echo $filter === 'pending' ? 'active' : ''; ?>">
      <?php esc_html_e( 'Pending', 'hem-newsletter' ); ?>
    </a>
  </div>

  <?php if ( empty( $subscribers ) ) : ?>
    <div class="hem-nl-empty">
      <span class="dashicons dashicons-groups"></span>
      <p><?php esc_html_e( 'No subscribers yet. Add the shortcode [newsletter_by_hem] to any page to start collecting subscribers.', 'hem-newsletter' ); ?></p>
      <code>[newsletter_by_hem]</code>
    </div>
  <?php else : ?>
    <table class="hem-nl-table widefat">
      <thead>
        <tr>
          <th><?php esc_html_e( 'Email', 'hem-newsletter' ); ?></th>
          <th><?php esc_html_e( 'Status', 'hem-newsletter' ); ?></th>
          <th><?php esc_html_e( 'Subscribed', 'hem-newsletter' ); ?></th>
          <th><?php esc_html_e( 'Confirmed', 'hem-newsletter' ); ?></th>
          <th><?php esc_html_e( 'Actions', 'hem-newsletter' ); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $subscribers as $sub ) : ?>
        <tr>
          <td><strong><?php echo esc_html( $sub->email ); ?></strong></td>
          <td>
            <span class="hem-nl-badge hem-nl-badge--<?php echo esc_attr( $sub->status ); ?>">
              <?php echo $sub->status === 'confirmed' ? '✓ ' . esc_html__( 'Confirmed', 'hem-newsletter' ) : '⏳ ' . esc_html__( 'Pending', 'hem-newsletter' ); ?>
            </span>
          </td>
          <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $sub->subscribed_at ) ) ); ?></td>
          <td><?php echo $sub->confirmed_at ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $sub->confirmed_at ) ) ) : '—'; ?></td>
          <td>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php esc_attr_e( 'Delete this subscriber?', 'hem-newsletter' ); ?>')">
              <?php wp_nonce_field( 'hem_nl_delete_subscriber' ); ?>
              <input type="hidden" name="action" value="hem_nl_delete_subscriber">
              <input type="hidden" name="subscriber_id" value="<?php echo esc_attr( $sub->id ); ?>">
              <button type="submit" class="button button-small hem-nl-btn-delete"><?php esc_html_e( 'Delete', 'hem-newsletter' ); ?></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <div class="hem-nl-shortcode-hint">
    <strong><?php esc_html_e( 'Shortcode:', 'hem-newsletter' ); ?></strong>
    <code id="hem-nl-shortcode">[newsletter_by_hem]</code>
    <button type="button" class="button button-small hem-nl-copy-shortcode" onclick="navigator.clipboard && navigator.clipboard.writeText(document.getElementById('hem-nl-shortcode').innerText).then(() => { this.innerText='✅ Copied'; setTimeout(() => this.innerText='📋 Copy', 1400); });">📋 <?php esc_html_e( 'Copy', 'hem-newsletter' ); ?></button>
    &nbsp;—&nbsp;
    <?php esc_html_e( 'Place this anywhere on your site to display the subscription form.', 'hem-newsletter' ); ?>
  </div>

</div>
