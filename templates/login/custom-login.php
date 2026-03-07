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
    <?php elseif (!empty($is_reset_password)): ?>
      <div class="vc-login-reset">
        <a class="vc-login-back" href="<?php echo esc_url($sign_in_url); ?>">
          <span aria-hidden="true">&larr;</span>
          Back to sign in
        </a>

        <div class="vc-login-reset__badge" aria-hidden="true">
          <svg width="24" height="24" viewBox="0 0 24 24" role="img" focusable="false" aria-hidden="true">
            <path d="M4 7h16v10H4z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
            <path d="m4 8 8 6 8-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>

        <div class="vc-login-header vc-login-header--reset">
          <p class="h3">Forgot your password?</p>
          <p class="subtitle">No worries, we'll send you a link to reset it</p>
        </div>

        <div class="vc-login-form-wrap vc-login-form-wrap--reset">
          <?php echo $pmpro_login_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
      </div>
    <?php else: ?>
      <div class="vc-login-header">
        <p class="h3">Welcome back</p>
        <p class="subtitle">Sign in to access your account.</p>
      </div>

      <a class="button button-google" href="<?php echo esc_url($google_login_url); ?>">
        <img src="<?php echo esc_url($google_logo_url); ?>" alt="Google">
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
