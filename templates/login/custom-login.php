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
    <?php elseif (!empty($is_set_new_password)): ?>
      <div class="vc-login-reset vc-login-reset--set-password">
        <div class="vc-login-reset__badge" aria-hidden="true">
          <svg width="24" height="24" viewBox="0 0 24 24" role="img" focusable="false" aria-hidden="true">
            <path d="M8 11V8a4 4 0 1 1 8 0v3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <rect x="5" y="11" width="14" height="10" rx="2" fill="none" stroke="currentColor" stroke-width="2"/>
          </svg>
        </div>

        <div class="vc-login-header vc-login-header--reset">
          <p class="h3">Create a new password</p>
          <p class="subtitle">Choose a strong password to secure your account and finish signing in.</p>
        </div>

        <div class="vc-login-form-wrap vc-login-form-wrap--set-password">
          <?php echo $pmpro_login_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
      </div>
    <?php elseif (!empty($is_reset_confirm)): ?>
      <div class="vc-login-reset vc-login-reset--confirm">
        <div class="vc-login-back-row">
          <a class="vc-login-back" href="<?php echo esc_url($sign_in_url); ?>">
            <img src="<?php echo esc_url($back_icon_url); ?>" alt="" aria-hidden="true">
            Back to sign in
          </a>
        </div>

        <div class="vc-login-reset__badge vc-login-reset__badge--success" aria-hidden="true">
          <img src="<?php echo esc_url($check_email_icon_url); ?>" alt="" aria-hidden="true">
        </div>

        <div class="vc-login-header vc-login-header--reset">
          <p class="h3">Check your email!</p>
          <p class="subtitle">We've sent a recovery link to</p>
        </div>

        <div class="vc-login-email-box">
          <div class="vc-login-email-box__icon" aria-hidden="true">
            <img src="<?php echo esc_url($check_email_body_icon_url); ?>" alt="" aria-hidden="true">
          </div>
          <div class="vc-login-email-box__content">
            <p class="vc-login-email-box__value"><?php echo esc_html($reset_login_hint !== '' ? $reset_login_hint : 'your email address'); ?></p>
            <p class="vc-login-email-box__meta">The link expires in 1 hour</p>
          </div>
        </div>

        <div class="vc-login-instructions">
          <p class="vc-login-instructions__title">Instructions:</p>
          <ul>
            <li>Check your inbox and spam folder</li>
            <li>Click the link in the email to reset your password</li>
            <li>If you don't receive the email within 5 minutes, request a new one</li>
          </ul>
        </div>

        <a class="vc-login-btn vc-login-btn--primary" href="<?php echo esc_url($sign_in_url); ?>">Back to sign in</a>

        <p class="vc-login-resend">
          Didn't receive the email?
          <a href="<?php echo esc_url($reset_password_url); ?>">Resend</a>
        </p>
      </div>
    <?php elseif (!empty($is_reset_password)): ?>
      <div class="vc-login-reset">
        <div class="vc-login-back-row">
          <a class="vc-login-back" href="<?php echo esc_url($sign_in_url); ?>">
            <img src="<?php echo esc_url($back_icon_url); ?>" alt="" aria-hidden="true">
            Back to sign in
          </a>
        </div>

        <div class="vc-login-reset__badge" aria-hidden="true">
          <img src="<?php echo esc_url($email_forget_icon_url); ?>" alt="" aria-hidden="true">
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
      <p class="mensaje-bottom">Not Account yet?<a href="<?php echo esc_url(home_url('/register/')); ?>">Create Account</a></p>
    <?php endif; ?>
  </div>
</div>
