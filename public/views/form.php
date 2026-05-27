<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="hem-nl-form-wrap <?php echo ( isset( $style_settings['button_position'] ) && $style_settings['button_position'] === 'inside' ) ? 'hem-nl-button-inside' : 'hem-nl-button-outside'; ?>" id="hem-nl-form-<?php echo esc_attr( uniqid() ); ?>" style="--hem-nl-button-color: <?php echo esc_attr( $style_settings['button_color'] ); ?>; --hem-nl-button-text-color: <?php echo esc_attr( $style_settings['button_text_color'] ); ?>; --hem-nl-button-hover-color: <?php echo esc_attr( $style_settings['button_hover_color'] ); ?>; --hem-nl-input-radius: <?php echo esc_attr( absint( $style_settings['input_radius'] ) ); ?>px; --hem-nl-button-radius: <?php echo esc_attr( absint( $style_settings['button_radius'] ) ); ?>px; --hem-nl-form-width: <?php echo esc_attr( absint( $style_settings['form_width'] ) ); ?>px; --hem-nl-form-height: <?php echo esc_attr( absint( $style_settings['form_height'] ) ); ?>px; --hem-nl-button-width: <?php echo esc_attr( absint( $style_settings['button_width'] ) ); ?>px; --hem-nl-button-height: <?php echo esc_attr( absint( $style_settings['button_height'] ) ); ?>px;">

  <?php if ( ! empty( $atts['title'] ) ) : ?>
    <p class="hem-nl-form-title"><?php echo esc_html( $atts['title'] ); ?></p>
  <?php endif; ?>

  <div class="hem-nl-form-inner">
    <div class="hem-nl-row">
      <input
        type="email"
        class="hem-nl-email-input"
        placeholder="<?php echo esc_attr( $atts['placeholder'] ); ?>"
        required
        autocomplete="email"
      >
      <button type="button" class="hem-nl-submit-btn">
        <span class="hem-nl-btn-label"><?php echo esc_html( $atts['button'] ); ?></span>
        <span class="hem-nl-btn-spinner" aria-hidden="true"></span>
      </button>
    </div>
    <div class="hem-nl-message" role="alert" aria-live="polite"></div>
  </div>


</div>
