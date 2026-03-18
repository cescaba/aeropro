<?php
if (!defined('ABSPATH')) exit;
?>
<section class="vc-profile-shell">
  <?php if (!empty($notice_html)): ?>
    <div class="vc-profile-notice-wrap">
      <?php echo $notice_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </div>
  <?php endif; ?>

  <form class="vc-profile-card" method="post" action="<?php echo esc_url($action_url); ?>" enctype="multipart/form-data">
    <input type="hidden" name="action" value="vc_onboard_save_account_profile">
    <?php echo $nonce_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

    <div class="vc-profile-hero">
      <div class="vc-profile-avatar-wrap" data-vc-profile-avatar>
        <?php if (!empty($avatar_url)): ?>
          <img class="vc-profile-avatar-image" src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($member_name); ?>">
        <?php else: ?>
          <span class="vc-profile-avatar-fallback"><?php echo esc_html($avatar_initials); ?></span>
        <?php endif; ?>
      </div>

      <div class="vc-profile-hero-copy">
        <h2 class="h2-profile"><?php echo esc_html($member_name); ?></h2>
        <p><?php echo esc_html($membership_label); ?><?php if (!empty($member_since)): ?> · <?php echo esc_html('Member since ' . $member_since); ?><?php endif; ?></p>

        <div class="vc-profile-photo-actions">
          <label class="vc-profile-photo-button" for="vc_profile_photo"><?php esc_html_e('Edit photo', 'vc-onboarding-wizard'); ?></label>
          <?php if (!empty($show_delete_photo)): ?>
            <button type="submit" class="vc-profile-photo-delete" name="remove_avatar" value="1"><?php esc_html_e('Delete', 'vc-onboarding-wizard'); ?></button>
          <?php endif; ?>
        </div>

        <input id="vc_profile_photo" type="file" name="profile_photo" accept="image/*" hidden>
      </div>
    </div>

    <div class="vc-profile-divider"></div>

    <div class="vc-profile-grid vc-profile-grid--two">
      <label class="vc-profile-field">
        <span><?php esc_html_e('Name', 'vc-onboarding-wizard'); ?></span>
        <input type="text" name="first_name" value="<?php echo esc_attr($first_name); ?>" placeholder="<?php esc_attr_e('First name', 'vc-onboarding-wizard'); ?>">
      </label>

      <label class="vc-profile-field">
        <span><?php esc_html_e('Last name', 'vc-onboarding-wizard'); ?></span>
        <input type="text" name="last_name" value="<?php echo esc_attr($last_name); ?>" placeholder="<?php esc_attr_e('Last name', 'vc-onboarding-wizard'); ?>">
      </label>
    </div>

    <div class="vc-profile-email-row">
      <label class="vc-profile-field vc-profile-field--email">
        <span><?php esc_html_e('E-mail', 'vc-onboarding-wizard'); ?></span>
        <input
          type="email"
          name="email"
          value="<?php echo esc_attr($email); ?>"
          data-current-email="<?php echo esc_attr($current_email); ?>"
          data-pending-email="<?php echo esc_attr($pending_email); ?>"
        >
      </label>

      <button
        type="submit"
        class="vc-profile-verify-button"
        name="profile_action"
        value="request_email_change"
        data-default-label="<?php echo esc_attr($pending_email !== '' ? __('Pending', 'vc-onboarding-wizard') : (!empty($email_verified) ? __('Verified', 'vc-onboarding-wizard') : __('Verify', 'vc-onboarding-wizard'))); ?>"
        <?php echo ($pending_email === '' && strcasecmp($email, $current_email) === 0) ? 'disabled' : ''; ?>
      >
        <?php echo $pending_email !== '' ? esc_html__('Pending', 'vc-onboarding-wizard') : (!empty($email_verified) ? esc_html__('Verified', 'vc-onboarding-wizard') : esc_html__('Verify', 'vc-onboarding-wizard')); ?>
      </button>
    </div>

    <p class="vc-profile-email-status<?php echo !empty($email_verified) ? ' is-verified' : ''; ?>">
      <span class="vc-profile-email-status-dot"></span>
      <?php
      if ($pending_email !== '') {
        esc_html_e('Email verification pending', 'vc-onboarding-wizard');
      } elseif (!empty($email_verified)) {
        esc_html_e('Email verified', 'vc-onboarding-wizard');
      } else {
        esc_html_e('Email verification pending', 'vc-onboarding-wizard');
      }
      ?>
    </p>

    <div class="vc-profile-grid vc-profile-grid--two">
      <label class="vc-profile-field">
        <span><?php esc_html_e('Phone', 'vc-onboarding-wizard'); ?></span>
        <input type="text" name="phone" value="<?php echo esc_attr($phone); ?>" placeholder="<?php esc_attr_e('+1 555 123 4567', 'vc-onboarding-wizard'); ?>">
      </label>

      <label class="vc-profile-field">
        <span><?php esc_html_e('Location', 'vc-onboarding-wizard'); ?></span>
        <input type="text" name="location" value="<?php echo esc_attr($location); ?>" placeholder="<?php esc_attr_e('City, Country', 'vc-onboarding-wizard'); ?>">
      </label>
    </div>

    <label class="vc-profile-field vc-profile-field--textarea">
      <span><?php esc_html_e('Bio', 'vc-onboarding-wizard'); ?></span>
      <textarea name="bio" rows="7" maxlength="200" placeholder="<?php esc_attr_e('Tell us a bit about yourself', 'vc-onboarding-wizard'); ?>"><?php echo esc_textarea($bio); ?></textarea>
      <small><?php esc_html_e('Maximum 200 characters', 'vc-onboarding-wizard'); ?></small>
    </label>

    <div class="vc-profile-actions">
      <a class="vc-profile-cancel" href="<?php echo esc_url($cancel_url); ?>"><?php esc_html_e('Cancel', 'vc-onboarding-wizard'); ?></a>
      <button type="submit" class="vc-profile-save" name="profile_action" value="save_profile"><?php esc_html_e('Save Changes', 'vc-onboarding-wizard'); ?></button>
    </div>
  </form>
</section>
