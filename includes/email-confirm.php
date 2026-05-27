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
  .body p { margin:0 0 20px; }
  .btn-wrap { text-align:center; margin:32px 0; }
  .btn { display:inline-block; background:#1a1a1a; color:#ffffff !important; text-decoration:none; padding:14px 36px; border-radius:3px; font-size:15px; letter-spacing:.5px; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1><?php echo esc_html( $blog ); ?></h1>
  </div>
  <div class="body">
    <p><?php esc_html_e( 'Hello,', 'hem-newsletter' ); ?></p>
    <p><?php printf( esc_html__( 'Thank you for subscribing to %s. Please click the button below to confirm your email address and complete your subscription.', 'hem-newsletter' ), '<strong>' . esc_html( $blog ) . '</strong>' ); ?></p>
    <div class="btn-wrap">
      <a class="btn" href="<?php echo esc_url( $confirm ); ?>"><?php esc_html_e( 'Confirm My Subscription', 'hem-newsletter' ); ?></a>
    </div>
    <p><?php esc_html_e( 'If you did not subscribe, you can safely ignore this email.', 'hem-newsletter' ); ?></p>
    <p><?php esc_html_e( 'Warm regards,', 'hem-newsletter' ); ?><br><?php echo esc_html( $blog ); ?></p>
  </div>
</div>
</body>
</html>
