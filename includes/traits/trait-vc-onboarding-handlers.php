<?php

if (!defined('ABSPATH')) exit;

trait VC_Onboarding_Wizard_Handlers {
  public function handle_email_start() {
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $pass  = isset($_POST['password']) ? (string) $_POST['password'] : '';
    $pass2 = isset($_POST['password_confirm']) ? (string) $_POST['password_confirm'] : '';

    if ($pass !== $pass2) {
      wp_safe_redirect(add_query_arg(['email' => $email, 'err' => 'pass_mismatch'], $this->step_url('registro-email')));
      exit;
    }

    $nonce = '';
    if (isset($_POST['vc_onboard_email_nonce'])) {
      $nonce = sanitize_text_field(wp_unslash($_POST['vc_onboard_email_nonce']));
    } elseif (isset($_POST['_wpnonce'])) {
      $nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
    }

    if (empty($nonce) || !wp_verify_nonce($nonce, 'vc_onboard_email_start')) {
      wp_safe_redirect(add_query_arg(['email' => $email, 'err' => 'invalid_nonce'], $this->step_url('registro-email')));
      exit;
    }

    if (empty($email) || empty($pass) || strlen($pass) < 8) {
      wp_safe_redirect(add_query_arg('err', '1', $this->step_url('registro-email')));
      exit;
    }

    if (email_exists($email)) {
      $existing_user = get_user_by('email', $email);
      if ($existing_user instanceof WP_User) {
        $existing_user_id = (int) $existing_user->ID;

        if (!$this->is_verified($existing_user_id)) {
          // Keep the latest password submitted by user for pending accounts.
          wp_set_password($pass, $existing_user_id);

          $token = wp_generate_password(32, false, false);
          $hash  = wp_hash($token);
          update_user_meta($existing_user_id, self::META_TOKEN, $hash);
          update_user_meta($existing_user_id, self::META_TOKEN_EXPIRES, time() + 24 * 3600);
          $this->send_verification_email($existing_user_id, $token);

          wp_set_current_user($existing_user_id);
          wp_set_auth_cookie($existing_user_id);

          wp_safe_redirect($this->check_email_step_url());
          exit;
        }
      }

      wp_safe_redirect(add_query_arg(['email' => $email, 'err' => 'email_exists'], $this->step_url('registro-email')));
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

    wp_safe_redirect($this->check_email_step_url());
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

    wp_safe_redirect($this->step_url('registro-final'));
    exit;
  }

  public function handle_save_account_profile() {
    if (!is_user_logged_in()) {
      wp_safe_redirect($this->step_url('register'));
      exit;
    }

    $nonce = isset($_POST['vc_account_profile_nonce']) ? sanitize_text_field(wp_unslash($_POST['vc_account_profile_nonce'])) : '';
    if (empty($nonce) || !wp_verify_nonce($nonce, 'vc_onboard_save_account_profile')) {
      wp_safe_redirect(add_query_arg('profile', 'invalid_nonce', $this->dashboard_view_url('profile')));
      exit;
    }

    $uid = get_current_user_id();
    $user = get_userdata($uid);
    if (!$user instanceof WP_User) {
      wp_safe_redirect($this->dashboard_view_url('profile'));
      exit;
    }

    $first_name = isset($_POST['first_name']) ? sanitize_text_field(wp_unslash($_POST['first_name'])) : '';
    $last_name = isset($_POST['last_name']) ? sanitize_text_field(wp_unslash($_POST['last_name'])) : '';
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
    $location = isset($_POST['location']) ? sanitize_text_field(wp_unslash($_POST['location'])) : '';
    $bio = isset($_POST['bio']) ? sanitize_textarea_field(wp_unslash($_POST['bio'])) : '';
    $profile_action = isset($_POST['profile_action']) ? sanitize_key(wp_unslash($_POST['profile_action'])) : 'save_profile';
    $bio = function_exists('mb_substr') ? mb_substr($bio, 0, 200) : substr($bio, 0, 200);

    update_user_meta($uid, 'first_name', $first_name);
    update_user_meta($uid, 'last_name', $last_name);
    update_user_meta($uid, self::META_PHONE, $phone);
    update_user_meta($uid, self::META_LOCATION, $location);
    update_user_meta($uid, self::META_BIO, $bio);

    $display_name = trim($first_name . ' ' . $last_name);
    if ($display_name === '') {
      $display_name = $user->display_name ?: $user->user_login;
    }

    wp_update_user([
      'ID' => $uid,
      'display_name' => $display_name,
    ]);

    if (!empty($_POST['remove_avatar'])) {
      delete_user_meta($uid, self::META_AVATAR_ID);
    } elseif (!empty($_FILES['profile_photo']['name'])) {
      require_once ABSPATH . 'wp-admin/includes/file.php';
      require_once ABSPATH . 'wp-admin/includes/image.php';
      require_once ABSPATH . 'wp-admin/includes/media.php';

      $attachment_id = media_handle_upload('profile_photo', 0);
      if (is_wp_error($attachment_id)) {
        wp_safe_redirect(add_query_arg('profile', 'upload_error', $this->dashboard_view_url('profile')));
        exit;
      }

      update_user_meta($uid, self::META_AVATAR_ID, (int) $attachment_id);
    }

    $current_email = (string) $user->user_email;
    $pending_email = $this->get_pending_email($uid);
    if ($profile_action === 'request_email_change') {
      if (!is_email($email)) {
        wp_safe_redirect(add_query_arg('profile', 'email_invalid', $this->dashboard_view_url('profile')));
        exit;
      }

      if (strcasecmp($email, $current_email) === 0) {
        $this->clear_pending_email_change($uid);
        wp_safe_redirect(add_query_arg('profile', 'saved', $this->dashboard_view_url('profile')));
        exit;
      }

      if (email_exists($email)) {
        wp_safe_redirect(add_query_arg('profile', 'email_exists', $this->dashboard_view_url('profile')));
        exit;
      }

      $token = wp_generate_password(32, false, false);
      update_user_meta($uid, self::META_PENDING_EMAIL, $email);
      update_user_meta($uid, self::META_PENDING_EMAIL_TOKEN, wp_hash($token));
      update_user_meta($uid, self::META_PENDING_EMAIL_EXPIRES, time() + DAY_IN_SECONDS);
      $this->send_email_change_confirmation($uid, $email, $token);

      wp_safe_redirect(add_query_arg('profile', 'email_sent', $this->dashboard_view_url('profile')));
      exit;
    }

    if ($email !== '' && strcasecmp($email, $current_email) !== 0 && strcasecmp($email, $pending_email) !== 0) {
      wp_safe_redirect(add_query_arg('profile', 'email_verify_required', $this->dashboard_view_url('profile')));
      exit;
    }

    wp_safe_redirect(add_query_arg('profile', 'saved', $this->dashboard_view_url('profile')));
    exit;
  }

  public function handle_confirm_email_change() {
    $uid = isset($_GET['uid']) ? absint($_GET['uid']) : 0;
    $token = isset($_GET['token']) ? (string) wp_unslash($_GET['token']) : '';

    if ($uid < 1 || $token === '') {
      wp_safe_redirect(add_query_arg('profile', 'email_invalid_token', $this->dashboard_view_url('profile')));
      exit;
    }

    $pending_email = $this->get_pending_email($uid);
    $hash = (string) get_user_meta($uid, self::META_PENDING_EMAIL_TOKEN, true);
    $expires = (int) get_user_meta($uid, self::META_PENDING_EMAIL_EXPIRES, true);

    if ($pending_email === '' || $hash === '' || $expires < time()) {
      $this->clear_pending_email_change($uid);
      wp_safe_redirect(add_query_arg('profile', 'email_expired', $this->dashboard_url()));
      exit;
    }

    $token_decoded = rawurldecode($token);
    $is_valid_token = hash_equals($hash, wp_hash($token)) || hash_equals($hash, wp_hash($token_decoded));
    if (!$is_valid_token) {
      wp_safe_redirect(add_query_arg('profile', 'email_invalid_token', $this->dashboard_url()));
      exit;
    }

    if (email_exists($pending_email)) {
      $this->clear_pending_email_change($uid);
      wp_safe_redirect(add_query_arg('profile', 'email_exists', $this->dashboard_view_url('profile')));
      exit;
    }

    $result = wp_update_user([
      'ID' => $uid,
      'user_email' => $pending_email,
    ]);

    if (is_wp_error($result)) {
      wp_safe_redirect(add_query_arg('profile', 'email_invalid', $this->dashboard_view_url('profile')));
      exit;
    }

    update_user_meta($uid, self::META_VERIFIED, 1);
    $this->clear_pending_email_change($uid);
    wp_set_current_user($uid);
    wp_set_auth_cookie($uid);

    wp_safe_redirect(add_query_arg('profile', 'email_confirmed', $this->dashboard_view_url('profile')));
    exit;
  }

  public function handle_change_password() {
    if (!is_user_logged_in()) {
      wp_safe_redirect($this->step_url('register'));
      exit;
    }

    $nonce = isset($_POST['vc_change_password_nonce']) ? sanitize_text_field(wp_unslash($_POST['vc_change_password_nonce'])) : '';
    if (empty($nonce) || !wp_verify_nonce($nonce, 'vc_onboard_change_password')) {
      wp_safe_redirect(add_query_arg('profile', 'invalid_nonce', $this->dashboard_view_url('privacy')));
      exit;
    }

    $uid = get_current_user_id();
    $user = get_userdata($uid);
    if (!$user instanceof WP_User) {
      wp_safe_redirect($this->dashboard_view_url('privacy'));
      exit;
    }

    $current_password = isset($_POST['current_password']) ? (string) wp_unslash($_POST['current_password']) : '';
    $new_password = isset($_POST['new_password']) ? (string) wp_unslash($_POST['new_password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? (string) wp_unslash($_POST['confirm_password']) : '';

    if (!wp_check_password($current_password, $user->user_pass, $uid)) {
      wp_safe_redirect(add_query_arg('profile', 'password_invalid', $this->dashboard_view_url('privacy')));
      exit;
    }

    if (strlen($new_password) < 8) {
      wp_safe_redirect(add_query_arg('profile', 'password_short', $this->dashboard_view_url('privacy')));
      exit;
    }

    if ($new_password !== $confirm_password) {
      wp_safe_redirect(add_query_arg('profile', 'password_mismatch', $this->dashboard_view_url('privacy')));
      exit;
    }

    wp_set_password($new_password, $uid);
    wp_set_current_user($uid);
    wp_set_auth_cookie($uid);

    wp_safe_redirect(add_query_arg('profile', 'password_updated', $this->dashboard_view_url('privacy')));
    exit;
  }
}
