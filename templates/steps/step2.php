<?php
if (!defined('ABSPATH')) exit;
?>
<section class="vc-password-step" aria-labelledby="vc-password-step-title">
  <header class="vc-password-step__header">
    <p id="vc-password-step-title" class="h3 vc-password-step__title">Set up your password</p>
    <p class="subtitle vc-password-step__subtitle">Create a secure password for your account</p>
  </header>

  <form id="step-2" class="vc-password-step__form" method="post" action="<?php echo esc_url($action_url); ?>">
    <input type="hidden" name="action" value="vc_onboard_email_start" />
    <?php echo $nonce_html; ?>

    <input type="hidden" name="email" value="<?php echo esc_attr($prefill_email); ?>" />

    <p class="vc-password-step__field">
      <label class="vc-password-step__label" for="vc_password">Password*</label>
      <input class="vc-password-step__control" type="password" id="vc_password" placeholder="Your password" name="password" minlength="8" required>
    </p>

    <p class="vc-password-step__field vc-password-step__field--confirm">
      <label class="vc-password-step__label" for="vc_password_confirm">Confirm password*</label>
      <input class="vc-password-step__control" type="password" id="vc_password_confirm" placeholder="Confirm password" name="password_confirm" minlength="8" required>
    </p>

    <p class="vc-password-step__actions">
      <button class="button button-primary vc-password-step__submit" type="submit">Continue</button>
    </p>

    <p class="vc-step2-back-wrap">
      <a class="vc-step2-back" href="<?php echo esc_url(home_url('/register/')); ?>">Back</a>
    </p>
  </form>

  <?php if (!empty($error_html)): ?>
    <div class="vc-password-step__notices">
      <?php echo $error_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </div>
  <?php endif; ?>
</section>
