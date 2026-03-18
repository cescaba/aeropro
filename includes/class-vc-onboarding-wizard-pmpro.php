<?php

if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/traits/trait-vc-onboarding-helpers.php';
require_once __DIR__ . '/traits/trait-vc-onboarding-shortcodes.php';
require_once __DIR__ . '/traits/trait-vc-onboarding-handlers.php';
require_once __DIR__ . '/traits/trait-vc-onboarding-guards.php';

if (!class_exists('VC_Onboarding_Wizard_PMPro')) {
  class VC_Onboarding_Wizard_PMPro {
    use VC_Onboarding_Wizard_Helpers;
    use VC_Onboarding_Wizard_Shortcodes;
    use VC_Onboarding_Wizard_Handlers;
    use VC_Onboarding_Wizard_Guards;

    const META_VERIFIED = 'vc_email_verified';
    const META_TOKEN = 'vc_email_verification_token';
    const META_TOKEN_EXPIRES = 'vc_email_verification_expires';
    const META_ONBOARD_DONE = 'vc_onboard_done';
    const META_CERT = 'vc_cert_track';
    const META_ROLE = 'vc_user_stage';
    const META_PHONE = 'vc_profile_phone';
    const META_LOCATION = 'vc_profile_location';
    const META_BIO = 'vc_profile_bio';
    const META_AVATAR_ID = 'vc_profile_avatar_id';
    const META_PENDING_EMAIL = 'vc_profile_pending_email';
    const META_PENDING_EMAIL_TOKEN = 'vc_profile_pending_email_token';
    const META_PENDING_EMAIL_EXPIRES = 'vc_profile_pending_email_expires';

    // Ajusta estos 2 valores en tu instalación:
    const TRIAL_LEVEL_ID = 1; // <-- CAMBIA ESTO al ID del level "Trial 14 días"
    const DASHBOARD_SLUG = 'dashboard'; // página /dashboard/

    public function __construct() {
      add_shortcode('vc_onboard_step1', [$this, 'shortcode_step1']);
      add_shortcode('vc_onboard_step2', [$this, 'shortcode_step2']);
      add_shortcode('vc_onboard_step3', [$this, 'shortcode_step3']);
      add_shortcode('vc_onboard_check_email', [$this, 'shortcode_check_email']);
      add_shortcode('vc_onboard_final', [$this, 'shortcode_final']);
      add_shortcode('vc_onboard_verify', [$this, 'shortcode_verify']);
      add_shortcode('vc_custom_login', [$this, 'shortcode_custom_login']);
      add_shortcode('vc_member_dashboard', [$this, 'shortcode_member_dashboard']);

      add_action('admin_post_nopriv_vc_onboard_email_start', [$this, 'handle_email_start']);
      add_action('admin_post_vc_onboard_email_start', [$this, 'handle_email_start']);

      add_action('admin_post_nopriv_vc_onboard_save_profile', [$this, 'handle_save_profile']);
      add_action('admin_post_vc_onboard_save_profile', [$this, 'handle_save_profile']);
      add_action('admin_post_vc_onboard_save_account_profile', [$this, 'handle_save_account_profile']);
      add_action('admin_post_vc_onboard_change_password', [$this, 'handle_change_password']);
      add_action('admin_post_nopriv_vc_onboard_confirm_email_change', [$this, 'handle_confirm_email_change']);
      add_action('admin_post_vc_onboard_confirm_email_change', [$this, 'handle_confirm_email_change']);

      add_action('template_redirect', [$this, 'guard_routes']);
      add_filter('login_redirect', [$this, 'login_redirect'], 10, 3);
      add_filter('template_include', [$this, 'use_blank_template_for_onboarding'], 99);

      add_action('admin_init', [$this, 'block_wp_admin_for_unverified']);
      add_action('wp_enqueue_scripts', [$this, 'enqueue_public_assets']);
    }
  }
}
