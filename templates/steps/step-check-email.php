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
    <p class="subtitle vc-check-email__subtitle">
      <?php echo (!empty($is_email_verification)) ? "We&rsquo;ve sent a verification link to" : "We&rsquo;ve sent a recovery link to"; ?>
    </p>
  </header>

  <?php if (!empty($user_email)): ?>
    <div class="vc-check-email__address">
      <span class="vc-check-email__address-icon-wrap" aria-hidden="true">
<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M16.6667 3.33325H3.33341C2.41294 3.33325 1.66675 4.07944 1.66675 4.99992V14.9999C1.66675 15.9204 2.41294 16.6666 3.33341 16.6666H16.6667C17.5872 16.6666 18.3334 15.9204 18.3334 14.9999V4.99992C18.3334 4.07944 17.5872 3.33325 16.6667 3.33325Z" stroke="#1447E6" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M18.3334 5.83325L10.8584 10.5833C10.6011 10.7444 10.3037 10.8299 10.0001 10.8299C9.69648 10.8299 9.39902 10.7444 9.14175 10.5833L1.66675 5.83325" stroke="#1447E6" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
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
      <li>
        <?php
          if (!empty($is_email_verification)) {
            echo "Click the link in the email to verify your account";
          } else {
            echo "Click the link in the email to reset your password";
          }
        ?>
      </li>
      <li>If you don&rsquo;t receive the email within 5 minutes, request a new one</li>
    </ul>
  </div>

  <a class="button button-primary vc-check-email__button" href="<?php echo esc_url($sign_in_url); ?>">Back to sign in</a>

  <p class="vc-check-email__resend">
    Didn&rsquo;t receive the email?
    <a class="vc-check-email__resend-link" href="<?php echo esc_url($resend_url); ?>">Resend</a>
  </p>
</section>
