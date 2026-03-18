<?php
if (!defined('ABSPATH')) exit;
?>
<section class="vc-privacy-shell">
  <?php if (!empty($notice_html)): ?>
    <div class="vc-profile-notice-wrap">
      <?php echo $notice_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </div>
  <?php endif; ?>

  <form class="vc-profile-card vc-privacy-card" method="post" action="<?php echo esc_url($action_url); ?>">
    <input type="hidden" name="action" value="vc_onboard_change_password">
    <?php echo $nonce_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

    <div class="vc-privacy-header">
      <h2><?php esc_html_e('Change Password', 'vc-onboarding-wizard'); ?></h2>
    </div>

    <label class="vc-profile-field">
      <span><?php esc_html_e('Current password', 'vc-onboarding-wizard'); ?></span>
      <input type="password" name="current_password" autocomplete="current-password" required>
    </label>

    <label class="vc-profile-field">
      <span><?php esc_html_e('New password', 'vc-onboarding-wizard'); ?></span>
      <input type="password" name="new_password" autocomplete="new-password" required>
      <small><?php esc_html_e('Minimum 8 characters', 'vc-onboarding-wizard'); ?></small>
    </label>

    <label class="vc-profile-field">
      <span><?php esc_html_e('Confirm new password', 'vc-onboarding-wizard'); ?></span>
      <input type="password" name="confirm_password" autocomplete="new-password" required>
    </label>

    <div class="vc-privacy-actions">
      <button type="submit" class="vc-privacy-save"><?php esc_html_e('Update password', 'vc-onboarding-wizard'); ?></button>
    </div>
  </form>
</section>
