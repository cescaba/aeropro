<?php
if (!defined('ABSPATH')) exit;
?>
<div class="vc-login-page<?php echo !empty($is_logged_in) ? ' vc-login-page--logged' : ''; ?>">
  <main class="vc-login-card">
    <?php if (!empty($is_logged_in)): ?>
      <section class="vc-login-state vc-login-state--logged" aria-labelledby="vc-login-logged-title">
        <header class="vc-login-header vc-login-state__header">
          <p id="vc-login-logged-title" class="h3 vc-login-title vc-login-title--logged">You are already logged in</p>
          <p class="subtitle vc-login-subtitle vc-login-subtitle--logged">Go to your account to continue.</p>
        </header>

        <div class="vc-login-state__actions">
          <a class="vc-login-btn" href="<?php echo esc_url($account_url); ?>">Go to account</a>
        </div>
      </section>

    <?php elseif (!empty($is_set_new_password)): ?>
      <section class="vc-login-reset vc-login-reset--set-password" aria-labelledby="vc-login-set-password-title">
        <div class="vc-login-reset__badge" aria-hidden="true">
          <svg width="24" height="24" viewBox="0 0 24 24" role="img" focusable="false" aria-hidden="true">
            <path d="M8 11V8a4 4 0 1 1 8 0v3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <rect x="5" y="11" width="14" height="10" rx="2" fill="none" stroke="currentColor" stroke-width="2"/>
          </svg>
        </div>

        <header class="vc-login-header vc-login-header--reset vc-login-reset__header">
          <p id="vc-login-set-password-title" class="h3 vc-login-reset__title vc-login-reset__title--set-password">Create a new password</p>
          <p class="subtitle vc-login-reset__subtitle vc-login-reset__subtitle--set-password">Choose a strong password to secure your account and finish signing in.</p>
        </header>

        <div class="vc-login-reset__body">
          <div class="vc-login-form-wrap vc-login-form-wrap--set-password">
            <?php echo $pmpro_login_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
          </div>
        </div>
      </section>

    <?php elseif (!empty($is_reset_confirm)): ?>
      <section class="vc-login-reset vc-login-reset--confirm" aria-labelledby="vc-login-reset-confirm-title">
        <div class="vc-login-reset__badge vc-login-reset__badge--success" aria-hidden="true">
          <svg class="check-circle-icon" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
            <circle cx="60" cy="60" r="45" fill="none" stroke="currentColor" stroke-width="8" />
            <path d="M40 62L54 76L82 48" fill="none" stroke="currentColor" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </div>

        <header class="vc-login-header vc-login-header--reset vc-login-reset__header">
          <p id="vc-login-reset-confirm-title" class="h3 vc-login-reset__title vc-login-reset__title--confirm">Check your email!</p>
          <p class="subtitle vc-login-reset__subtitle vc-login-reset__subtitle--confirm">We've sent a recovery link to</p>
        </header>

        <div class="vc-login-reset__body">
          <div class="vc-login-email-box">
            <div class="vc-login-email-box__icon" aria-hidden="true">
              <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M16.6667 3.33325H3.33341C2.41294 3.33325 1.66675 4.07944 1.66675 4.99992V14.9999C1.66675 15.9204 2.41294 16.6666 3.33341 16.6666H16.6667C17.5872 16.6666 18.3334 15.9204 18.3334 14.9999V4.99992C18.3334 4.07944 17.5872 3.33325 16.6667 3.33325Z" stroke="#1447E6" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M18.3334 5.83325L10.8584 10.5833C10.6011 10.7444 10.3037 10.8299 10.0001 10.8299C9.69648 10.8299 9.39902 10.7444 9.14175 10.5833L1.66675 5.83325" stroke="#1447E6" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
</svg>

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
        </div>

        <footer class="vc-login-reset__actions">
          <a class="vc-login-btn vc-login-btn--primary" href="<?php echo esc_url($sign_in_url); ?>">Back to sign in</a>

          <p class="vc-login-resend">
            Didn't receive the email?
            <a href="<?php echo esc_url($reset_password_url); ?>">Resend</a>
          </p>
        </footer>
      </section>

    <?php elseif (!empty($is_reset_password)): ?>
      <section class="vc-login-reset vc-login-reset--forgot" aria-labelledby="vc-login-reset-forgot-title">
        <div class="vc-login-reset__badge" aria-hidden="true">
          <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" focusable="false" aria-hidden="true">
            <rect x="2.5" y="6.5" width="27" height="19" rx="2.5" stroke="currentColor" stroke-width="2.5"/>
            <path d="M3 9L16 18.5L29 9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>

        <header class="vc-login-header vc-login-header--reset vc-login-reset__header">
          <p id="vc-login-reset-forgot-title" class="h3 vc-login-reset__title vc-login-reset__title--forgot">Forgot your password?</p>
          <p class="subtitle vc-login-reset__subtitle vc-login-reset__subtitle--forgot">No worries, we'll send you a link to reset it</p>
        </header>

        <div class="vc-login-reset__body vc-login-reset__body--forgot">
          <div class="vc-login-form-wrap vc-login-form-wrap--reset">
            <?php echo $pmpro_login_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
          </div>
        </div>

        <footer class="vc-login-reset__actions vc-login-reset__actions--forgot">
          <a class="vc-login-reset__back-link" href="<?php echo esc_url($sign_in_url); ?>">Back to sign in</a>
        </footer>
      </section>

    <?php else: ?>
      <section class="vc-login-state vc-login-state--sign-in" aria-labelledby="vc-login-sign-in-title">
        <header class="vc-login-header vc-login-state__header">
          <p id="vc-login-sign-in-title" class="h3 vc-login-title vc-login-title--sign-in">Welcome back</p>
          <p class="subtitle vc-login-subtitle vc-login-subtitle--sign-in">Sign in to your account to continue studying</p>
        </header>

        <div class="vc-login-state__social">
          <a class="button button-google vc-login-state__google" href="<?php echo esc_url($google_login_url); ?>">
            <img class="vc-login-state__google-icon" src="<?php echo esc_url($google_logo_url); ?>" alt="">
            <span class="vc-login-state__google-label">Continue with Google</span>
          </a>
        </div>

        <div class="divider vc-login-state__divider" aria-hidden="true">
          <span>or</span>
        </div>

        <div class="vc-login-state__body">
          <div class="vc-login-form-wrap">
            <?php echo $pmpro_login_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
          </div>
        </div>

        <footer class="vc-login-state__footer">
          <p class="mensaje vc-login-state__legal">By creating an account, you agree to our <a href="<?php echo esc_url($terms_url); ?>">Terms of Service</a><br class="mensaje-break"> and <a href="<?php echo esc_url($privacy_url); ?>">Privacy Policy</a></p>
          <p class="mensaje-bottom vc-login-state__register">Not Account yet?<a href="<?php echo esc_url(home_url('/register/')); ?>">Create Account</a></p>
        </footer>
      </section>
    <?php endif; ?>
  </main>
</div>
