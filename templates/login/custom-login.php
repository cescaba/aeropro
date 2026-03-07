<?php
if (!defined('ABSPATH')) exit;
?>
<div class="vc-login-page<?php echo !empty($is_logged_in) ? ' vc-login-page--logged' : ''; ?>">
  <div class="vc-login-card">
    <?php if (!empty($is_logged_in)): ?>
      <div class="vc-login-header">
        <h1 class="vc-login-title">You are already logged in</h1>
        <p class="vc-login-subtitle">Go to your account to continue.</p>
      </div>

      <a class="vc-login-btn" href="<?php echo esc_url($account_url); ?>">Go to account</a>
    <?php else: ?>
      <div class="vc-login-header">
        <h1 class="vc-login-title">Welcome back</h1>
        <p class="vc-login-subtitle">Sign in to access your account.</p>
      </div>

      <div class="vc-login-form-wrap">
        <?php echo $pmpro_login_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      </div>
    <?php endif; ?>
  </div>
</div>
