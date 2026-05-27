<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo esc_html( $subject ); ?></title>
<style>
  body { margin:0; padding:0; background:#f4f4f4; font-family: Georgia, 'Times New Roman', serif; }
  .wrapper { max-width:600px; margin:40px auto; background:#ffffff; border-radius:4px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08); }
  .header { background:#1a1a1a; padding:36px 40px; text-align:center; }
  .header h1 { color:#ffffff; font-size:22px; margin:0; font-weight:400; letter-spacing:1px; }
  .body { padding:40px; color:#333333; font-size:16px; line-height:1.7; }
  .body h2 { font-size:24px; font-weight:400; color:#1a1a1a; margin:0 0 16px; line-height:1.3; }
  .body p { margin:0 0 20px; }
  .excerpt { color:#555555; font-style:italic; border-left:3px solid #e0e0e0; padding-left:16px; margin:24px 0; }
  .btn-wrap { text-align:center; margin:32px 0; }
  .btn { display:inline-block; background:#1a1a1a; color:#ffffff !important; text-decoration:none; padding:14px 36px; border-radius:3px; font-size:15px; letter-spacing:.5px; }
  .footer { padding:0 40px 34px; color:#777777; font-size:12px; line-height:1.6; text-align:center; }
  .footer a { color:#555555; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
  </div>
  <div class="body">
    <h2><?php echo esc_html( get_the_title( $post ) ); ?></h2>

    <?php if ( has_post_thumbnail( $post ) ) : ?>
      <p><img src="<?php echo esc_url( get_the_post_thumbnail_url( $post, 'large' ) ); ?>" alt="" style="max-width:100%;border-radius:3px;"></p>
    <?php endif; ?>

    <div class="excerpt">
      <p><?php echo esc_html( wp_trim_words( strip_tags( $post->post_content ), 40, '…' ) ); ?></p>
    </div>

    <div class="btn-wrap">
      <a class="btn" href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php esc_html_e( 'Read Full Post →', 'hem-newsletter' ); ?></a>
    </div>
  </div>
  <?php if ( ! empty( $unsubscribe ) ) : ?>
    <div class="footer">
      <?php echo wp_kses_post( $email_footer_html ); ?>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
