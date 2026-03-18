<?php

if (!defined('ABSPATH')) exit;

trait VC_Onboarding_Wizard_Helpers {
  public function enqueue_public_assets() {
    if (is_page()) {
      wp_enqueue_script(
        'vc-onboarding-js',
        VC_OW_PLUGIN_URL . 'templates/assets/js/onboarding.js',
        [],
        file_exists(VC_OW_PLUGIN_DIR . 'templates/assets/js/onboarding.js') ? (string) filemtime(VC_OW_PLUGIN_DIR . 'templates/assets/js/onboarding.js') : '1.0.0',
        true
      );

      wp_enqueue_script(
        'vc-dashboard-js',
        VC_OW_PLUGIN_URL . 'templates/assets/js/dashboard.js',
        [],
        file_exists(VC_OW_PLUGIN_DIR . 'templates/assets/js/dashboard.js') ? (string) filemtime(VC_OW_PLUGIN_DIR . 'templates/assets/js/dashboard.js') : '1.0.0',
        true
      );
    }
  }

  private function render_template(string $template_relative_path, array $vars = []): string {
    $template_file = VC_OW_PLUGIN_DIR . ltrim($template_relative_path, '/');
    if (!file_exists($template_file)) return '';

    if (!empty($vars)) {
      extract($vars, EXTR_SKIP);
    }

    ob_start();
    include $template_file;
    return (string) ob_get_clean();
  }

  private function legacy_dashboard_url(): string {
    return home_url('/' . self::DASHBOARD_SLUG . '/');
  }

  private function dashboard_url(): string {
    return $this->legacy_dashboard_url();
  }

  private function membership_account_url(): string {
    if (function_exists('pmpro_url')) {
      $account_url = pmpro_url('account');
      if (!empty($account_url)) {
        return $account_url;
      }
    }

    return $this->legacy_dashboard_url();
  }

  private function normalize_path(string $path): string {
    return trim($path, '/');
  }

  private function path_from_url(string $url): string {
    $path = parse_url($url, PHP_URL_PATH);
    return $this->normalize_path((string) $path);
  }

  private function is_dashboard_request(string $path): bool {
    $normalized_path = $this->normalize_path($path);

    $dashboard_paths = [
      $this->normalize_path(self::DASHBOARD_SLUG),
    ];

    $dashboard_paths = array_filter(array_unique($dashboard_paths));

    return in_array($normalized_path, $dashboard_paths, true);
  }

  private function step_url(string $slug): string {
    return home_url('/' . trim($slug, '/') . '/');
  }

  private function check_email_step_url(): string {
    return add_query_arg('check_email', '1', $this->step_url('register'));
  }

  private function is_verified(int $user_id): bool {
    return (bool) get_user_meta($user_id, self::META_VERIFIED, true);
  }

  private function is_onboard_done(int $user_id): bool {
    return (bool) get_user_meta($user_id, self::META_ONBOARD_DONE, true);
  }

  private function current_user_has_trial_active(): bool {
    if (!function_exists('pmpro_hasMembershipLevel')) return false;
    return pmpro_hasMembershipLevel(self::TRIAL_LEVEL_ID, get_current_user_id());
  }

  private function user_has_trial_active(int $user_id): bool {
    if (!function_exists('pmpro_hasMembershipLevel')) return false;
    return pmpro_hasMembershipLevel(self::TRIAL_LEVEL_ID, $user_id);
  }

  private function assign_trial_level(int $user_id): bool {
    if (!function_exists('pmpro_changeMembershipLevel')) return false;

    if ($this->user_has_trial_active($user_id)) return true;

    return (bool) pmpro_changeMembershipLevel(self::TRIAL_LEVEL_ID, $user_id);
  }

  private function get_user_membership_levels(int $user_id): array {
    if (!function_exists('pmpro_getMembershipLevelsForUser')) {
      return [];
    }

    $levels = pmpro_getMembershipLevelsForUser($user_id);

    return is_array($levels) ? $levels : [];
  }

  private function get_primary_membership_level(int $user_id) {
    $levels = $this->get_user_membership_levels($user_id);

    if (empty($levels)) {
      return null;
    }

    return reset($levels);
  }

  private function get_membership_label(int $user_id): string {
    $level = $this->get_primary_membership_level($user_id);

    if (!empty($level) && !empty($level->name)) {
      return (string) $level->name;
    }

    if ($this->user_has_trial_active($user_id)) {
      return 'Trial Access';
    }

    return 'Free Account';
  }

  private function get_user_meta_string(int $user_id, string $meta_key): string {
    return trim((string) get_user_meta($user_id, $meta_key, true));
  }

  private function get_user_display_label(WP_User $user): string {
    $first_name = $this->get_user_meta_string($user->ID, 'first_name');
    $last_name = $this->get_user_meta_string($user->ID, 'last_name');

    if ($first_name !== '' && $last_name !== '') {
      return $first_name . ' ' . strtoupper(substr($last_name, 0, 1)) . '.';
    }

    if ($first_name !== '') {
      return $first_name;
    }

    if ($last_name !== '') {
      return strtoupper(substr($last_name, 0, 1)) . '.';
    }

    $display_name = trim((string) $user->display_name);
    if ($display_name !== '') {
      $parts = preg_split('/\s+/', $display_name);
      if (is_array($parts) && !empty($parts)) {
        $first_part = trim((string) ($parts[0] ?? ''));
        $second_part = trim((string) ($parts[1] ?? ''));

        if ($first_part !== '' && $second_part !== '') {
          return $first_part . ' ' . strtoupper(substr($second_part, 0, 1)) . '.';
        }

        if ($first_part !== '') {
          return $first_part;
        }
      }
    }

    return (string) $user->user_login;
  }

  private function get_user_initials(WP_User $user): string {
    $first_name = trim((string) get_user_meta($user->ID, 'first_name', true));
    $last_name = trim((string) get_user_meta($user->ID, 'last_name', true));

    $initials = '';

    if ($first_name !== '') {
      $initials .= strtoupper(substr($first_name, 0, 1));
    }

    if ($last_name !== '') {
      $initials .= strtoupper(substr($last_name, 0, 1));
    }

    if ($initials !== '') {
      return $initials;
    }

    $display_name = trim((string) $user->display_name);
    if ($display_name !== '') {
      $words = preg_split('/\s+/', $display_name);
      if (is_array($words)) {
        foreach (array_slice($words, 0, 2) as $word) {
          if ($word !== '') {
            $initials .= strtoupper(substr($word, 0, 1));
          }
        }
      }
    }

    if ($initials !== '') {
      return $initials;
    }

    return strtoupper(substr((string) $user->user_login, 0, 2));
  }

  private function get_profile_avatar_id(int $user_id): int {
    return (int) get_user_meta($user_id, self::META_AVATAR_ID, true);
  }

  private function get_pending_email(int $user_id): string {
    return $this->get_user_meta_string($user_id, self::META_PENDING_EMAIL);
  }

  private function clear_pending_email_change(int $user_id): void {
    delete_user_meta($user_id, self::META_PENDING_EMAIL);
    delete_user_meta($user_id, self::META_PENDING_EMAIL_TOKEN);
    delete_user_meta($user_id, self::META_PENDING_EMAIL_EXPIRES);
  }

  private function get_email_change_confirmation_url(int $user_id, string $token): string {
    return add_query_arg([
      'action' => 'vc_onboard_confirm_email_change',
      'uid' => $user_id,
      'token' => rawurlencode($token),
    ], admin_url('admin-post.php'));
  }

  private function send_email_change_confirmation(int $user_id, string $pending_email, string $token): bool {
    $user = get_userdata($user_id);
    if (!$user instanceof WP_User) {
      return false;
    }

    $confirm_url = $this->get_email_change_confirmation_url($user_id, $token);
    $recipient_name = $this->get_user_display_label($user);
    $subject = 'Confirm your new email address';

    $message = sprintf(
      "Hi %s,\n\nPlease confirm your new email address by clicking the link below:\n\n%s\n\nIf you didn't request this change, you can ignore this email.\n",
      $recipient_name,
      $confirm_url
    );

    return wp_mail($pending_email, $subject, $message);
  }

  private function get_profile_avatar_url(int $user_id, string $size = 'thumbnail'): string {
    $attachment_id = $this->get_profile_avatar_id($user_id);
    if ($attachment_id < 1) {
      return '';
    }

    $url = wp_get_attachment_image_url($attachment_id, $size);
    return $url ? (string) $url : '';
  }

  private function get_member_since_label(WP_User $user): string {
    if (empty($user->user_registered)) {
      return '';
    }

    return date_i18n('F Y', strtotime((string) $user->user_registered));
  }

  private function get_profile_notice_from_query(): string {
    $status = isset($_GET['profile']) ? sanitize_key(wp_unslash($_GET['profile'])) : '';

    if ($status === 'saved') {
      return $this->render_notice('Profile updated successfully.', 'success');
    }

    if ($status === 'invalid_nonce') {
      return $this->render_notice('The profile form expired. Please try again.', 'error');
    }

    if ($status === 'upload_error') {
      return $this->render_notice('The photo could not be uploaded. Please try another image.', 'error');
    }

    if ($status === 'email_sent') {
      return $this->render_notice('We sent a confirmation link to your new email address.', 'success');
    }

    if ($status === 'email_exists') {
      return $this->render_notice('That email is already in use by another account.', 'error');
    }

    if ($status === 'email_invalid') {
      return $this->render_notice('Please enter a valid email address before requesting verification.', 'error');
    }

    if ($status === 'email_verify_required') {
      return $this->render_notice('Use Verify to confirm the new email before it replaces the current one.', 'info');
    }

    if ($status === 'email_confirmed') {
      return $this->render_notice('Your email address has been updated successfully.', 'success');
    }

    if ($status === 'email_expired') {
      return $this->render_notice('That email confirmation link has expired. Request a new one from your profile.', 'error');
    }

    if ($status === 'email_invalid_token') {
      return $this->render_notice('The email confirmation link is invalid.', 'error');
    }

    if ($status === 'password_updated') {
      return $this->render_notice('Your password has been updated successfully.', 'success');
    }

    if ($status === 'password_invalid') {
      return $this->render_notice('Your current password is incorrect.', 'error');
    }

    if ($status === 'password_short') {
      return $this->render_notice('Your new password must be at least 8 characters long.', 'error');
    }

    if ($status === 'password_mismatch') {
      return $this->render_notice('The new password and confirmation do not match.', 'error');
    }

    return '';
  }

  private function send_verification_email(int $user_id, string $token): bool {
    $user = get_userdata($user_id);
    if (!$user) return false;

    $verify_url = add_query_arg([
      'uid'   => $user_id,
      'token' => rawurlencode($token),
    ], $this->step_url('verificar'));

    $subject = 'Verify your email to start your 14-day trial';
    $recipient_name = trim((string) $user->display_name);
    if ($recipient_name === '') {
      $recipient_name = trim((string) $user->user_login);
    }

    $message = $this->render_template('templates/emails/verification-email.php', [
      'subject' => $subject,
      'recipient_name' => $recipient_name,
      'verify_url' => $verify_url,
      'site_name' => wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES),
    ]);

    if ($message === '') {
      $message = "Hi {$recipient_name},\n\n";
      $message .= "Click the link below to verify your email and activate your 14-day trial:\n\n";
      $message .= $verify_url . "\n\n";
      $message .= "If you didn't request this, you can ignore this email.\n\n";
      $message .= "Thanks.";

      return wp_mail($user->user_email, $subject, $message);
    }

    $headers = ['Content-Type: text/html; charset=UTF-8'];

    return wp_mail($user->user_email, $subject, $message, $headers);
  }

  private function render_notice(string $msg, string $type = 'info'): string {
    $class = 'vc-notice vc-notice--' . esc_attr($type);
    return '<div class="' . $class . '">' . esc_html($msg) . '</div>';
  }
}
