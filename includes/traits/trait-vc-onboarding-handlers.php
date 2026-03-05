<?php

if (!defined('ABSPATH')) exit;

trait VC_Onboarding_Wizard_Handlers {
  public function handle_email_start() {
    $nonce = '';
    if (isset($_POST['vc_onboard_email_nonce'])) {
      $nonce = sanitize_text_field(wp_unslash($_POST['vc_onboard_email_nonce']));
    } elseif (isset($_POST['_wpnonce'])) {
      $nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
    }

    if (empty($nonce) || !wp_verify_nonce($nonce, 'vc_onboard_email_start')) {
      $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
      wp_safe_redirect(add_query_arg(['email' => $email, 'err' => 'invalid_nonce'], $this->step_url('registro-email')));
      exit;
    }

    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $pass  = isset($_POST['password']) ? (string) $_POST['password'] : '';
    $pass2 = isset($_POST['password_confirm']) ? (string) $_POST['password_confirm'] : '';

    if ($pass !== $pass2) {
      wp_safe_redirect(add_query_arg(['email' => $email, 'err' => 'pass_mismatch'], $this->step_url('registro-email')));
      exit;
    }

    if (empty($email) || empty($pass) || strlen($pass) < 8) {
      wp_safe_redirect(add_query_arg('err', '1', $this->step_url('registro-email')));
      exit;
    }

    if (email_exists($email)) {
      wp_safe_redirect(wp_login_url($this->step_url('register')));
      exit;
    }

    $username = sanitize_user(current(explode('@', $email)));
    if (username_exists($username)) {
      $username .= '_' . wp_generate_password(4, false, false);
    }

    $user_id = wp_create_user($username, $pass, $email);
    if (is_wp_error($user_id)) {
      wp_safe_redirect(add_query_arg('err', '2', $this->step_url('registro-email')));
      exit;
    }

    update_user_meta($user_id, self::META_VERIFIED, 0);

    $token = wp_generate_password(32, false, false);
    $hash  = wp_hash($token);
    update_user_meta($user_id, self::META_TOKEN, $hash);
    update_user_meta($user_id, self::META_TOKEN_EXPIRES, time() + 24 * 3600);

    $this->send_verification_email($user_id, $token);

    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);

    wp_safe_redirect($this->step_url('registro-verifica-email'));
    exit;
  }

  public function handle_save_profile() {
    if (!is_user_logged_in()) {
      wp_safe_redirect($this->step_url('register'));
      exit;
    }

    $nonce = '';
    if (isset($_POST['vc_onboard_profile_nonce'])) {
      $nonce = sanitize_text_field(wp_unslash($_POST['vc_onboard_profile_nonce']));
    } elseif (isset($_POST['_wpnonce'])) {
      $nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
    }

    if (empty($nonce) || !wp_verify_nonce($nonce, 'vc_onboard_save_profile')) {
      wp_die('Invalid nonce');
    }

    $uid = get_current_user_id();
    if (!$this->is_verified($uid)) {
      wp_safe_redirect($this->step_url('register'));
      exit;
    }

    $first = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last  = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
    $cert  = isset($_POST['cert_track']) ? sanitize_text_field($_POST['cert_track']) : '';
    $stage = isset($_POST['user_stage']) ? sanitize_text_field($_POST['user_stage']) : '';

    $allowed_certs = ['Airframe', 'Powerplant', 'Both (A&P)'];
    $allowed_stage = ['Student', 'Graduate', 'Technician'];

    if (!$first || !$last || !in_array($cert, $allowed_certs, true) || !in_array($stage, $allowed_stage, true)) {
      wp_safe_redirect(add_query_arg('err', '1', $this->step_url('registro-datos')));
      exit;
    }

    update_user_meta($uid, 'first_name', $first);
    update_user_meta($uid, 'last_name', $last);
    update_user_meta($uid, self::META_CERT, $cert);
    update_user_meta($uid, self::META_ROLE, $stage);

    $this->assign_trial_level($uid);

    update_user_meta($uid, self::META_ONBOARD_DONE, 1);

    wp_safe_redirect($this->dashboard_url());
    exit;
  }
}
