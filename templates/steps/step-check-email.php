<?php
if (!defined('ABSPATH')) exit;
?>
<div class="vc-check-email">
  <div class="vc-check-email__stepper">
    <?php echo $stepper_html; ?>
  </div>

  <p class="h3">Check your email</p>
  <p class="subtitle">We have sent you an email to confirm your address.</p>

  <?php if (!empty($user_email)): ?>
    <p class="vc-check-email__address"><?php echo esc_html($user_email); ?></p>
  <?php endif; ?>

  <div class="vc-check-email__tips">
    <p>Please check your inbox and click the verification link.</p>
    <p>If you do not see it, please check your spam folder.</p>
  </div>

  <a class="button button-primary vc-check-email__button" href="<?php echo esc_url($continue_url); ?>">I already verified my email</a>
</div>
