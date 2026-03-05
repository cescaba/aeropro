<?php
if (!defined('ABSPATH')) exit;
?>
<div class="vc-check-email">
  <p class="h3">Check your email</p>
  <p class="subtitle">We sent you a verification link to activate your account.</p>

  <?php if (!empty($user_email)): ?>
    <p class="vc-check-email__address"><?php echo esc_html($user_email); ?></p>
  <?php endif; ?>

  <div class="vc-check-email__tips">
    <p>Open your inbox and click the verification link.</p>
    <p>If you do not see it, check your spam or promotions folder.</p>
  </div>

  <a class="button button-primary vc-check-email__button" href="<?php echo esc_url($continue_url); ?>">I already verified my email</a>
</div>
