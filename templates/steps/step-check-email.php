<?php
if (!defined('ABSPATH')) exit;
?>
<section class="vc-check-email" aria-labelledby="vc-check-email-title">
  <?php if (!empty($notices_html)): ?>
    <?php echo $notices_html; ?>
  <?php endif; ?>

  <div class="vc-check-email__stepper">
    <svg
      class="vc-check-email__status-icon"
      viewBox="0 0 37 37"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      aria-hidden="true"
      focusable="false"
    >
      <path
        d="M18.5 35C27.6127 35 35 27.6127 35 18.5C35 9.3873 27.6127 2 18.5 2C9.3873 2 2 9.3873 2 18.5C2 27.6127 9.3873 35 18.5 35Z"
        stroke="currentColor"
        stroke-width="3"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
      <path
        d="M11 18.5L16 23.5L26 13.5"
        stroke="currentColor"
        stroke-width="3"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
    </svg>
  </div>

  <header class="vc-check-email__header">
    <p id="vc-check-email-title" class="h3 vc-check-email__title">Check your email</p>
    <p class="subtitle vc-check-email__subtitle">We&rsquo;ve sent a recovery link to</p>
  </header>

  <?php if (!empty($user_email)): ?>
    <div class="vc-check-email__address">
      <span class="vc-check-email__address-icon-wrap" aria-hidden="true">
        <svg
          class="vc-check-email__address-icon"
          viewBox="0 0 64 64"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
          focusable="false"
        >
          <rect
            x="6"
            y="14"
            width="52"
            height="36"
            rx="4"
            stroke="currentColor"
            stroke-width="3"
          />
          <path
            d="M8 16L32 35L56 16"
            stroke="currentColor"
            stroke-width="3"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
          <path
            d="M8 48L24 32"
            stroke="currentColor"
            stroke-width="3"
            stroke-linecap="round"
          />
          <path
            d="M56 48L40 32"
            stroke="currentColor"
            stroke-width="3"
            stroke-linecap="round"
          />
        </svg>
      </span>
      <span class="vc-check-email__address-content">
        <span class="vc-check-email__address-email"><?php echo esc_html($user_email); ?></span>
        <span class="vc-check-email__address-expiry">The link expires in 1 hour</span>
      </span>
    </div>
  <?php endif; ?>

  <div class="vc-check-email__tips">
    <p class="vc-check-email__tips-title">Instructions:</p>
    <ul class="vc-check-email__tips-list">
      <li>Check your inbox and spam folder</li>
      <li>Click the link in the email to reset your password</li>
      <li>If you don&rsquo;t receive the email within 5 minutes, request a new one</li>
    </ul>
  </div>

  <a class="button button-primary vc-check-email__button" href="<?php echo esc_url($sign_in_url); ?>">Back to sign in</a>

  <p class="vc-check-email__resend">
    Didn&rsquo;t receive the email?
    <a class="vc-check-email__resend-link" href="<?php echo esc_url($resend_url); ?>">Resend</a>
  </p>
</section>
