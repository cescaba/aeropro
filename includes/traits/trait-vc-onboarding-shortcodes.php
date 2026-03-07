<?php

if (!defined('ABSPATH')) exit;

trait VC_Onboarding_Wizard_Shortcodes {
  public function use_blank_template_for_onboarding($template) {
    if (!is_page()) return $template;

    global $post;
    if (!$post) return $template;

    $slugs = ['register', 'registro-email', 'registro-datos', 'registro-final', 'verificar'];

    $uses_blank_template = in_array($post->post_name, $slugs, true) || has_shortcode($post->post_content, 'vc_custom_login');
    if (!$uses_blank_template) return $template;

    foreach (['page-blank.php', 'blank.php', 'templates/blank.php'] as $file) {
      $found = locate_template($file);
      if ($found) return $found;
    }

    $fallback = VC_OW_PLUGIN_DIR . 'templates/vc-blank-page.php';
    if (file_exists($fallback)) return $fallback;

    return $template;
  }

  public function shortcode_step1(): string {
    if (is_user_logged_in()) {
      $uid = get_current_user_id();
      if (!$this->is_verified($uid)) {
        return $this->shortcode_check_email();
      }

      if (!$this->is_onboard_done($uid)) {
        wp_safe_redirect($this->step_url('registro-datos'));
        exit;
      }

      wp_safe_redirect($this->dashboard_url());
      exit;
    }

    $notices_html = '';
    if (isset($_GET['check_email']) && $_GET['check_email'] === '1') {
      $notices_html .= $this->render_notice('Check your email to verify your account. You must verify before accessing the dashboard.', 'info');
    }
    if (isset($_GET['verify']) && $_GET['verify'] === 'expired') {
      $notices_html .= $this->render_notice('Verification link expired. Please sign up again.', 'error');
    }
    if (isset($_GET['verify']) && $_GET['verify'] === 'invalid') {
      $notices_html .= $this->render_notice('Invalid verification link. Please sign up again.', 'error');
    }

    $html = $this->render_template('templates/steps/step1.php', [
      'google_login_url' => wp_login_url($this->step_url('registro-datos')),
      'google_logo_url' => VC_OW_PLUGIN_URL . 'templates/assets/logo-google.svg',
      'action_email_url' => $this->step_url('registro-email'),
      'notices_html' => $notices_html,
    ]);

    return $html . $this->inline_css();
  }

  public function shortcode_step2(): string {
    if (is_user_logged_in()) {
      wp_safe_redirect($this->step_url('registro-datos'));
      exit;
    }

    $prefill_email = isset($_GET['email']) ? sanitize_email($_GET['email']) : '';
    if (empty($prefill_email)) {
      wp_safe_redirect($this->step_url('register'));
      exit;
    }

    $error_html = '';
    if (isset($_GET['err']) && $_GET['err'] === 'pass_mismatch') {
      $error_html = $this->render_notice('Passwords do not match. Please try again.', 'error');
    }
    if (isset($_GET['err']) && $_GET['err'] === 'invalid_nonce') {
      $error_html = $this->render_notice('Session expired. Please try again.', 'error');
    }
    if (isset($_GET['err']) && $_GET['err'] === 'email_exists') {
      $error_html = $this->render_notice('This email already has an account. Please use Log in.', 'error');
    }

    $html = $this->render_template('templates/steps/step2.php', [
      'action_url' => admin_url('admin-post.php'),
      'nonce_html' => wp_nonce_field('vc_onboard_email_start', 'vc_onboard_email_nonce', false, false),
      'prefill_email' => $prefill_email,
      'error_html' => $error_html,
    ]);

    return $html . $this->inline_css();
  }

  public function shortcode_step3(): string {
    if (!is_user_logged_in()) {
      wp_safe_redirect($this->step_url('register'));
      exit;
    }

    $uid = get_current_user_id();
    if (!$this->is_verified($uid)) {
      wp_safe_redirect($this->check_email_step_url());
      exit;
    }

    if ($this->is_onboard_done($uid)) {
      wp_safe_redirect($this->step_url('registro-final'));
      exit;
    }

    $this->assign_trial_level($uid);

    $stepper_html = $this->render_template('templates/steps/stepper.php', [
      'steps' => [
        ['key' => 'account', 'label' => 'Account'],
        ['key' => 'profile', 'label' => 'Profile'],
        ['key' => 'home', 'label' => 'Home'],
      ],
      'current_step' => 2,
    ]);

    $html = $this->render_template('templates/steps/step3.php', [
      'stepper_html' => $stepper_html,
      'action_url' => admin_url('admin-post.php'),
      'nonce_html' => wp_nonce_field('vc_onboard_save_profile', 'vc_onboard_profile_nonce', false, false),
      'first_name' => get_user_meta($uid, 'first_name', true),
      'last_name' => get_user_meta($uid, 'last_name', true),
      'cert_track' => get_user_meta($uid, self::META_CERT, true),
      'user_stage' => get_user_meta($uid, self::META_ROLE, true),
    ]);

    return $html . $this->inline_css();
  }

  public function shortcode_check_email(): string {
    if (!is_user_logged_in()) {
      wp_safe_redirect($this->step_url('register'));
      exit;
    }

    $uid = get_current_user_id();
    if ($this->is_verified($uid)) {
      if ($this->is_onboard_done($uid)) {
        wp_safe_redirect($this->dashboard_url());
      } else {
        wp_safe_redirect($this->step_url('registro-datos'));
      }
      exit;
    }

    $current_user = wp_get_current_user();
    $user_email = $current_user instanceof WP_User ? $current_user->user_email : '';
    $notices_html = '';
    if (isset($_GET['verify']) && $_GET['verify'] === 'expired') {
      $notices_html .= $this->render_notice('Verification link expired. Please request a new email.', 'error');
    }
    if (isset($_GET['verify']) && $_GET['verify'] === 'invalid') {
      $notices_html .= $this->render_notice('Invalid verification link. Please use the latest email we sent.', 'error');
    }
    $stepper_html = $this->render_template('templates/steps/stepper.php', [
      'steps' => [
        ['key' => 'account', 'label' => 'Account'],
        ['key' => 'profile', 'label' => 'Profile'],
        ['key' => 'home', 'label' => 'Home'],
      ],
      'current_step' => 1,
    ]);

    $html = $this->render_template('templates/steps/step-check-email.php', [
      'stepper_html' => $stepper_html,
      'user_email' => $user_email,
      'continue_url' => $this->step_url('registro-datos'),
      'notices_html' => $notices_html,
    ]);

    return $html . $this->inline_css();
  }

  public function shortcode_final(): string {
    if (!is_user_logged_in()) {
      wp_safe_redirect($this->step_url('register'));
      exit;
    }

    $uid = get_current_user_id();
    if (!$this->is_verified($uid) || !$this->user_has_trial_active($uid) || !$this->is_onboard_done($uid)) {
      wp_safe_redirect($this->step_url('register'));
      exit;
    }

    $html = $this->render_template('templates/steps/step-final.php', [
      'dashboard_url' => $this->dashboard_url(),
    ]);

    return $html . $this->inline_css();
  }

  public function shortcode_verify(): string {
    return $this->render_notice('Verifying…', 'info') . $this->inline_css();
  }

  public function shortcode_custom_login($atts = [], $content = null): string {
    wp_enqueue_style(
      'vc-custom-login-css',
      VC_OW_PLUGIN_URL . 'templates/assets/login.css',
      [],
      file_exists(VC_OW_PLUGIN_DIR . 'templates/assets/login.css') ? (string) filemtime(VC_OW_PLUGIN_DIR . 'templates/assets/login.css') : '1.0.0'
    );
    wp_enqueue_script(
      'vc-custom-login-js',
      VC_OW_PLUGIN_URL . 'templates/assets/login.js',
      [],
      file_exists(VC_OW_PLUGIN_DIR . 'templates/assets/login.js') ? (string) filemtime(VC_OW_PLUGIN_DIR . 'templates/assets/login.js') : '1.0.0',
      true
    );

    $account_url = home_url('/account/');
    $is_reset_password = isset($_GET['action']) && sanitize_key(wp_unslash($_GET['action'])) === 'reset_pass';
    $is_set_new_password = isset($_GET['action']) && in_array(sanitize_key(wp_unslash($_GET['action'])), ['rp', 'resetpass'], true);
    $is_reset_confirm = isset($_GET['checkemail']) && sanitize_key(wp_unslash($_GET['checkemail'])) === 'confirm';
    $sign_in_url = get_permalink()
      ? remove_query_arg(['action', 'checkemail', 'login', 'key', 'wp_lang'], get_permalink())
      : home_url('/');
    $reset_password_url = add_query_arg('action', 'reset_pass', $sign_in_url);
    $reset_login_hint = isset($_COOKIE['vc_reset_login_hint']) ? sanitize_text_field(wp_unslash($_COOKIE['vc_reset_login_hint'])) : '';

    $html = $this->render_template('templates/login/custom-login.php', [
      'is_logged_in' => is_user_logged_in(),
      'is_reset_password' => $is_reset_password,
      'is_set_new_password' => $is_set_new_password,
      'is_reset_confirm' => $is_reset_confirm,
      'account_url' => $account_url,
      'sign_in_url' => $sign_in_url,
      'reset_password_url' => $reset_password_url,
      'reset_login_hint' => $reset_login_hint,
      'back_icon_url' => VC_OW_PLUGIN_URL . 'templates/assets/icons/flecha_back.svg',
      'check_email_icon_url' => VC_OW_PLUGIN_URL . 'templates/assets/icons/check_email.svg',
      'check_email_body_icon_url' => VC_OW_PLUGIN_URL . 'templates/assets/icons/check_email_body.svg',
      'email_forget_icon_url' => VC_OW_PLUGIN_URL . 'templates/assets/icons/email-forget.svg',
      'google_login_url' => wp_login_url($this->dashboard_url()),
      'google_logo_url' => VC_OW_PLUGIN_URL . 'templates/assets/logo-google.svg',
      'pmpro_login_html' => do_shortcode('[pmpro_login]'),
    ]);

    return $html;
  }

  private function inline_css(): string {
    return '<style>
      .vc-onboard{max-width:520px;margin:40px auto;padding:24px;border:1px solid #e5e5e5;border-radius:12px;background:#fff}
      .vc-onboard h2{margin:0 0 10px}
      .vc-onboard input,.vc-onboard select{width:100%;padding:10px;border:1px solid #ccc;border-radius:8px}
      .vc-notice{max-width:520px;margin:20px auto;padding:12px 16px;border-radius:10px}
      .vc-notice--error{background:#ffe9e9;border:1px solid #ffb3b3}
      .vc-notice--info{background:#eef6ff;border:1px solid #b7d7ff}
      .vc-notice--success{background:#eaffea;border:1px solid #b9f2b9}
    </style>';
  }
}
