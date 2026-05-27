<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
$test_result = get_transient( 'hem_newsletter_test_result_' . get_current_user_id() );
if ( $test_result ) {
    delete_transient( 'hem_newsletter_test_result_' . get_current_user_id() );
}
?>
<div class="wrap hem-nl-wrap">

  <div class="hem-nl-header">
    <div class="hem-nl-header-left">
      <h1 class="hem-nl-title">
        <span class="dashicons dashicons-admin-settings"></span>
        <?php esc_html_e( 'Newsletter Settings', 'hem-newsletter' ); ?>
      </h1>
      <p class="hem-nl-tagline"><?php esc_html_e( 'Complete the settings to start using the plugin.', 'hem-newsletter' ); ?></p>
    </div>
  </div>

  <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="hem-nl-settings-form">
    <?php wp_nonce_field( 'hem_nl_save_settings' ); ?>
    <input type="hidden" name="action" value="hem_nl_save_settings">
    <input type="hidden" name="magic_smtp" value="1">

    <div class="hem-nl-card hem-nl-magic-card">
      <h2><?php esc_html_e( 'Email Sending', 'hem-newsletter' ); ?></h2>
      <p><?php esc_html_e( 'Choose how newsletter emails should be sent from your website.', 'hem-newsletter' ); ?></p>

      <label class="hem-nl-checkbox-line">
        <input type="checkbox" name="use_wp_mail" value="1" <?php checked( $settings['use_wp_mail'], '1' ); ?>>
        <?php esc_html_e( 'Send emails using WordPress wp_mail / hosting server', 'hem-newsletter' ); ?>
      </label>
      <p class="description"><?php esc_html_e( 'Simple option. Works on many hosts, but SMTP is usually better for delivery.', 'hem-newsletter' ); ?></p>

      <hr>

      <h3><?php esc_html_e( 'SMTP or Google/Gmail', 'hem-newsletter' ); ?></h3>
      <p class="description"><?php esc_html_e( 'Leave wp_mail unchecked to send using Gmail or your domain SMTP account.', 'hem-newsletter' ); ?></p>

      <table class="form-table">
        <tr>
          <th><label for="smtp_provider"><?php esc_html_e( 'Sending Service', 'hem-newsletter' ); ?></label></th>
          <td>
            <select id="smtp_provider" name="smtp_provider">
              <option value="smtp" <?php selected( $settings['smtp_provider'], 'smtp' ); ?>><?php esc_html_e( 'Domain SMTP', 'hem-newsletter' ); ?></option>
              <option value="gmail" <?php selected( $settings['smtp_provider'], 'gmail' ); ?>><?php esc_html_e( 'Google / Gmail', 'hem-newsletter' ); ?></option>
            </select>
            <div id="hem-nl-gmail-help" class="notice notice-info inline hem-nl-gmail-help" style="<?php echo ( $settings['smtp_provider'] === 'gmail' ) ? '' : 'display:none;'; ?>">
              <p><?php esc_html_e( 'To send emails via Google SMTP, configure SMTP settings below with SMTP Host smtp.gmail.com, port 587 (TLS) or 465 (SSL), using your full Gmail address as the username. Important: You must generate an "App Password" in your Google Account security settings to use as the password, as regular passwords are not supported.', 'hem-newsletter' ); ?></p>
            </div>
          </td>
        </tr>
        <tr>
          <th><label for="from_name"><?php esc_html_e( 'From Name', 'hem-newsletter' ); ?></label></th>
          <td><input type="text" id="from_name" name="from_name" value="<?php echo esc_attr( $settings['from_name'] ); ?>" class="regular-text"></td>
        </tr>
        <tr>
          <th><label for="from_email"><?php esc_html_e( 'From Email', 'hem-newsletter' ); ?></label></th>
          <td><input type="email" id="from_email" name="from_email" value="<?php echo esc_attr( $settings['from_email'] ); ?>" class="regular-text" placeholder="you@yourdomain.com"></td>
        </tr>
        <tr>
          <th><label for="smtp_user"><?php esc_html_e( 'Username', 'hem-newsletter' ); ?></label></th>
          <td>
            <input type="text" id="smtp_user" name="smtp_user" value="<?php echo esc_attr( $settings['username'] ); ?>" class="regular-text" autocomplete="off" placeholder="Usually same as From Email">
          </td>
        </tr>
        <tr>
          <th><label for="smtp_pass"><?php esc_html_e( 'Password / App Password', 'hem-newsletter' ); ?></label></th>
          <td>
            <input type="password" id="smtp_pass" name="smtp_pass" value="<?php echo esc_attr( $settings['password'] ); ?>" class="regular-text" autocomplete="new-password">
            <p class="description"><?php esc_html_e( 'For Gmail, use a Google App Password. For domain email, use the mailbox password.', 'hem-newsletter' ); ?></p>
          </td>
        </tr>
      </table>
    </div>


    <?php
      $display_smtp_encryption = ! empty( $settings['host'] ) ? $settings['encryption'] : ( $detected_smtp['encryption'] ?? $settings['encryption'] );
    ?>

    <details class="hem-nl-card">
      <summary><strong><?php esc_html_e( 'SMTP Settings', 'hem-newsletter' ); ?></strong></summary>
      <p class="description"><?php esc_html_e( 'We have tried to fill this in for you. Use exact host outgoing server details if different than the ones we detected.', 'hem-newsletter' ); ?></p>
      <table class="form-table">
        <tr>
          <th><label for="smtp_host"><?php esc_html_e( 'SMTP Host', 'hem-newsletter' ); ?></label></th>
          <td><input type="text" id="smtp_host" name="smtp_host" value="<?php echo esc_attr( ! empty( $settings['host'] ) ? $settings['host'] : ( $detected_smtp['host'] ?? '' ) ); ?>" class="regular-text" placeholder="mail.yourdomain.com or smtp.gmail.com"></td>
        </tr>
        <tr>
          <th><label for="smtp_port"><?php esc_html_e( 'SMTP Port', 'hem-newsletter' ); ?></label></th>
          <td><input type="number" id="smtp_port" name="smtp_port" value="<?php echo esc_attr( ! empty( $settings['host'] ) ? $settings['port'] : ( $detected_smtp['port'] ?? '' ) ); ?>" class="small-text"></td>
        </tr>
        <tr>
          <th><label for="smtp_encryption"><?php esc_html_e( 'Encryption', 'hem-newsletter' ); ?></label></th>
          <td>
            <select id="smtp_encryption" name="smtp_encryption">
              <option value="tls" <?php selected( $display_smtp_encryption, 'tls' ); ?>>TLS</option>
              <option value="ssl" <?php selected( $display_smtp_encryption, 'ssl' ); ?>>SSL</option>
              <option value="" <?php selected( $display_smtp_encryption, '' ); ?>><?php esc_html_e( 'None', 'hem-newsletter' ); ?></option>
            </select>
          </td>
        </tr>
      </table>
    </details>



    <details class="hem-nl-card" open>
      <summary><strong><?php esc_html_e( 'Frontend Style', 'hem-newsletter' ); ?></strong></summary>
      <p class="description"><?php esc_html_e( 'Adjust subscription form appearance to your liking.', 'hem-newsletter' ); ?></p>
      <table class="form-table">
        <tr>
          <th><label for="button_color"><?php esc_html_e( 'Button Color', 'hem-newsletter' ); ?></label></th>
          <td><input type="color" id="button_color" name="button_color" value="<?php echo esc_attr( $settings['button_color'] ); ?>"></td>
        </tr>
        <tr>
          <th><label for="button_text_color"><?php esc_html_e( 'Button Text Color', 'hem-newsletter' ); ?></label></th>
          <td><input type="color" id="button_text_color" name="button_text_color" value="<?php echo esc_attr( $settings['button_text_color'] ); ?>"></td>
        </tr>
        <tr>
          <th><label for="button_hover_color"><?php esc_html_e( 'Button Hover Color', 'hem-newsletter' ); ?></label></th>
          <td><input type="color" id="button_hover_color" name="button_hover_color" value="<?php echo esc_attr( $settings['button_hover_color'] ); ?>"></td>
        </tr>
        <tr>
          <th><label for="input_radius"><?php esc_html_e( 'Text Bar Corner Radius', 'hem-newsletter' ); ?></label></th>
          <td><input type="number" id="input_radius" name="input_radius" value="<?php echo esc_attr( absint( $settings['input_radius'] ) ); ?>" min="0" max="50" class="small-text"> px</td>
        </tr>
        <tr>
          <th><label for="button_radius"><?php esc_html_e( 'Button Corner Radius', 'hem-newsletter' ); ?></label></th>
          <td><input type="number" id="button_radius" name="button_radius" value="<?php echo esc_attr( absint( $settings['button_radius'] ) ); ?>" min="0" max="50" class="small-text"> px</td>
        </tr>

        <tr>
          <th><label for="form_title"><?php esc_html_e( 'Message Above Box', 'hem-newsletter' ); ?></label></th>
          <td><input type="text" id="form_title" name="form_title" value="<?php echo esc_attr( $settings['form_title'] ); ?>" class="regular-text" placeholder="Subscribe for new post alerts."></td>
        </tr>

        <tr>
          <th><label for="input_placeholder"><?php esc_html_e( 'Textbox Placeholder Text', 'hem-newsletter' ); ?></label></th>
          <td><input type="text" id="input_placeholder" name="input_placeholder" value="<?php echo esc_attr( $settings['input_placeholder'] ); ?>" class="regular-text" placeholder="email address"></td>
        </tr>
        <tr>
          <th><label for="button_label"><?php esc_html_e( 'Subscribe Button Text', 'hem-newsletter' ); ?></label></th>
          <td><input type="text" id="button_label" name="button_label" value="<?php echo esc_attr( $settings['button_label'] ); ?>" class="regular-text" placeholder="Subscribe"></td>
        </tr>
        <tr>
          <th><label for="form_width"><?php esc_html_e( 'Subscribe Box Width', 'hem-newsletter' ); ?></label></th>
          <td><input type="number" id="form_width" name="form_width" value="<?php echo esc_attr( absint( $settings['form_width'] ) ); ?>" min="240" max="900" class="small-text"> px <span class="description"><?php esc_html_e( 'Reasonable range: 240–900 px.', 'hem-newsletter' ); ?></span></td>
        </tr>
        <tr>
          <th><label for="form_height"><?php esc_html_e( 'Subscribe Box Height', 'hem-newsletter' ); ?></label></th>
          <td><input type="number" id="form_height" name="form_height" value="<?php echo esc_attr( absint( $settings['form_height'] ) ); ?>" min="36" max="80" class="small-text"> px <span class="description"><?php esc_html_e( 'Reasonable range: 36–80 px.', 'hem-newsletter' ); ?></span></td>
        </tr>
        <tr>
          <th><label for="button_width"><?php esc_html_e( 'Subscribe Button Width', 'hem-newsletter' ); ?></label></th>
          <td><input type="number" id="button_width" name="button_width" value="<?php echo esc_attr( absint( $settings['button_width'] ) ); ?>" min="80" max="260" class="small-text"> px <span class="description"><?php esc_html_e( 'Reasonable range: 80–260 px.', 'hem-newsletter' ); ?></span></td>
        </tr>
        <tr>
          <th><label for="button_height"><?php esc_html_e( 'Subscribe Button Height', 'hem-newsletter' ); ?></label></th>
          <td><input type="number" id="button_height" name="button_height" value="<?php echo esc_attr( absint( $settings['button_height'] ) ); ?>" min="28" max="78" class="small-text"> px <span class="description"><?php esc_html_e( 'When button is inside, keep it slightly smaller than subscribe box height.', 'hem-newsletter' ); ?></span></td>
        </tr>
        <tr>
          <th><label for="button_position"><?php esc_html_e( 'Button Position', 'hem-newsletter' ); ?></label></th>
          <td>
            <select id="button_position" name="button_position">
              <option value="outside" <?php selected( $settings['button_position'], 'outside' ); ?>><?php esc_html_e( 'Outside text bar', 'hem-newsletter' ); ?></option>
              <option value="inside" <?php selected( $settings['button_position'], 'inside' ); ?>><?php esc_html_e( 'Inside text bar', 'hem-newsletter' ); ?></option>
            </select>
          </td>
        </tr>
      </table>

      <div class="hem-nl-style-preview">
        <h3><?php esc_html_e( 'Live Preview', 'hem-newsletter' ); ?></h3>
        <div id="hem-nl-preview-wrap" class="hem-nl-preview-form hem-nl-preview-button-<?php echo esc_attr( $settings['button_position'] ); ?>" style="--hem-preview-button-color: <?php echo esc_attr( $settings['button_color'] ); ?>; --hem-preview-button-text-color: <?php echo esc_attr( $settings['button_text_color'] ); ?>; --hem-preview-button-hover-color: <?php echo esc_attr( $settings['button_hover_color'] ); ?>; --hem-preview-input-radius: <?php echo esc_attr( absint( $settings['input_radius'] ) ); ?>px; --hem-preview-button-radius: <?php echo esc_attr( absint( $settings['button_radius'] ) ); ?>px; --hem-preview-form-width: <?php echo esc_attr( absint( $settings['form_width'] ) ); ?>px; --hem-preview-form-height: <?php echo esc_attr( absint( $settings['form_height'] ) ); ?>px; --hem-preview-button-width: <?php echo esc_attr( absint( $settings['button_width'] ) ); ?>px; --hem-preview-button-height: <?php echo esc_attr( absint( $settings['button_height'] ) ); ?>px;">
          <p id="hem-nl-preview-title" class="hem-nl-preview-title"><?php echo esc_html( $settings['form_title'] ); ?></p>
          <div class="hem-nl-preview-row">
            <input id="hem-nl-preview-input" type="email" value="" placeholder="<?php echo esc_attr( $settings['input_placeholder'] ); ?>" readonly>
            <button id="hem-nl-preview-button" type="button"><?php echo esc_html( $settings['button_label'] ); ?></button>
          </div>
        </div>
      </div>
    </details>

    <div class="hem-nl-card">
      <h2><?php esc_html_e( 'Outgoing Email Footer', 'hem-newsletter' ); ?></h2>
      <p class="description"><?php esc_html_e( 'Customize the footer added to newsletter emails. Keep {unsubscribe_link} in the text so subscribers can opt out.', 'hem-newsletter' ); ?></p>
      <textarea name="email_footer" rows="4" class="large-text"><?php echo esc_textarea( $settings['email_footer'] ); ?></textarea>
      <p class="description"><?php esc_html_e( 'Available placeholders: {site_name}, {unsubscribe_link}', 'hem-newsletter' ); ?></p>
    </div>

    <?php submit_button( __( 'Save Settings', 'hem-newsletter' ) ); ?>
  </form>

  <div class="hem-nl-card">
    <h2><?php esc_html_e( 'Send Test Email', 'hem-newsletter' ); ?></h2>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
      <?php wp_nonce_field( 'hem_nl_send_test_email' ); ?>
      <input type="hidden" name="action" value="hem_nl_send_test_email">
      <input type="email" name="test_email" value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" class="regular-text">
      <?php submit_button( __( 'Send Test Email', 'hem-newsletter' ), 'secondary', 'submit', false ); ?>
      <p class="description"><?php esc_html_e( 'Save settings first, then send a test email. The test email includes your outgoing email footer so you can see how newsletter emails will look.', 'hem-newsletter' ); ?></p>
    </form>

    <?php if ( is_array( $test_result ) ) : ?>
      <?php if ( ! empty( $test_result['ok'] ) && $test_result['ok'] === '1' ) : ?>
        <div class="notice notice-success inline hem-nl-inline-result"><p><?php esc_html_e( 'Test email sent successfully. Please check whether it is received and review the footer to see how outgoing email will look.', 'hem-newsletter' ); ?></p></div>
      <?php else : ?>
        <div class="notice notice-error inline hem-nl-inline-result"><p><?php esc_html_e( 'Test mail not sent. Test failed.', 'hem-newsletter' ); ?> <?php echo ! empty( $test_result['error'] ) ? esc_html( $test_result['error'] ) : ''; ?></p></div>
      <?php endif; ?>
    <?php endif; ?>
  </div>

</div>

<script>
(function(){
  var provider = document.getElementById('smtp_provider');
  var help = document.getElementById('hem-nl-gmail-help');
  function hemNlGmailHelp(){ if (provider && help) help.style.display = provider.value === 'gmail' ? '' : 'none'; }
  if (provider && help) { provider.addEventListener('change', hemNlGmailHelp); hemNlGmailHelp(); }

  var preview = document.getElementById('hem-nl-preview-wrap');
  if (preview) {
    var buttonColor = document.getElementById('button_color');
    var buttonTextColor = document.getElementById('button_text_color');
    var buttonHoverColor = document.getElementById('button_hover_color');
    var inputRadius = document.getElementById('input_radius');
    var buttonRadius = document.getElementById('button_radius');
    var buttonPosition = document.getElementById('button_position');
    var formWidth = document.getElementById('form_width');
    var formHeight = document.getElementById('form_height');
    var buttonWidth = document.getElementById('button_width');
    var buttonHeight = document.getElementById('button_height');
    var inputPlaceholder = document.getElementById('input_placeholder');
    var buttonLabel = document.getElementById('button_label');
    var previewInput = document.getElementById('hem-nl-preview-input');
    var previewButton = document.getElementById('hem-nl-preview-button');
    var formTitle = document.getElementById('form_title');
    var previewTitle = document.getElementById('hem-nl-preview-title');
    function hemNlUpdatePreview(){
      preview.style.setProperty('--hem-preview-button-color', buttonColor ? buttonColor.value : '#1a1a1a');
      preview.style.setProperty('--hem-preview-button-text-color', buttonTextColor ? buttonTextColor.value : '#ffffff');
      preview.style.setProperty('--hem-preview-button-hover-color', buttonHoverColor ? buttonHoverColor.value : '#2563eb');
      preview.style.setProperty('--hem-preview-input-radius', (inputRadius ? inputRadius.value : 6) + 'px');
      preview.style.setProperty('--hem-preview-button-radius', (buttonRadius ? buttonRadius.value : 6) + 'px');
      preview.style.setProperty('--hem-preview-form-width', (formWidth ? formWidth.value : 520) + 'px');
      preview.style.setProperty('--hem-preview-form-height', (formHeight ? formHeight.value : 44) + 'px');
      preview.style.setProperty('--hem-preview-button-width', (buttonWidth ? buttonWidth.value : 120) + 'px');
      preview.style.setProperty('--hem-preview-button-height', (buttonHeight ? buttonHeight.value : 42) + 'px');
      if (previewTitle && formTitle) { previewTitle.textContent = formTitle.value; previewTitle.style.display = formTitle.value.trim() ? '' : 'none'; }
      if (previewInput && inputPlaceholder) { previewInput.placeholder = inputPlaceholder.value || 'email address'; }
      if (previewButton && buttonLabel) { previewButton.textContent = buttonLabel.value || 'Subscribe'; }
      if (buttonPosition) {
        preview.classList.remove('hem-nl-preview-button-outside', 'hem-nl-preview-button-inside');
        preview.classList.add('hem-nl-preview-button-' + buttonPosition.value);
      }
    }
    [buttonColor, buttonTextColor, buttonHoverColor, inputRadius, buttonRadius, buttonPosition, formWidth, formHeight, buttonWidth, buttonHeight, formTitle, inputPlaceholder, buttonLabel].forEach(function(el){ if(el) el.addEventListener('input', hemNlUpdatePreview); if(el) el.addEventListener('change', hemNlUpdatePreview); });
    hemNlUpdatePreview();
  }
})();
</script>
