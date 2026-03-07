<?php
if (!defined('ABSPATH')) exit;
?>
<div class="vc-login-page<?php echo !empty($is_logged_in) ? ' vc-login-page--logged' : ''; ?>">
  <div class="vc-login-card">
    <?php if (!empty($is_logged_in)): ?>
      <div class="vc-login-header">
        <p class="h3">You are already logged in</p>
        <p class="subtitle">Go to your account to continue.</p>
      </div>

      <a class="vc-login-btn" href="<?php echo esc_url($account_url); ?>">Go to account</a>
    <?php else: ?>
      <div class="vc-login-header">
        <p class="h3">Welcome back</p>
        <p class="subtitle">Sign in to access your account.</p>
      </div>
<a class="button button-google" href="<?php echo esc_url($google_login_url); ?>">
  <img src="<?php echo esc_url($google_logo_url); ?>" alt="Aeropro">
  Continue with Google
</a>

<div class="divider"><span>o</span></div>
      <div class="vc-login-form-wrap">
        <?php echo $pmpro_login_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      </div>
      <p class="mensaje-bottom">Not Account yet?<a href="#">Create Account</a></p>
    <?php endif; ?>
  </div>
</div>
