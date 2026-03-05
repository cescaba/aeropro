<?php

if (!defined('ABSPATH')) exit;

trait VC_Onboarding_Wizard_Helpers {
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

  private function dashboard_url(): string {
    return home_url('/' . self::DASHBOARD_SLUG . '/');
  }

  private function step_url(string $slug): string {
    return home_url('/' . trim($slug, '/') . '/');
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

  private function send_verification_email(int $user_id, string $token): bool {
    $user = get_userdata($user_id);
    if (!$user) return false;

    $verify_url = add_query_arg([
      'uid'   => $user_id,
      'token' => rawurlencode($token),
    ], $this->step_url('verificar'));

    $subject = 'Verify your email to start your 14-day trial';
    $message = "Hi {$user->display_name},\n\n";
    $message .= "Click the link below to verify your email and activate your 14-day trial:\n\n";
    $message .= $verify_url . "\n\n";
    $message .= "If you didn't request this, you can ignore this email.\n\n";
    $message .= "Thanks.";

    return wp_mail($user->user_email, $subject, $message);
  }

  private function render_notice(string $msg, string $type = 'info'): string {
    $class = 'vc-notice vc-notice--' . esc_attr($type);
    return '<div class="' . $class . '">' . esc_html($msg) . '</div>';
  }
}
