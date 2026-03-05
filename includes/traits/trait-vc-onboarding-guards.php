<?php

if (!defined('ABSPATH')) exit;

trait VC_Onboarding_Wizard_Guards {
  public function guard_routes() {
    $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

    if ($path === 'verificar') {
      $uid = isset($_GET['uid']) ? absint($_GET['uid']) : 0;
      $token = isset($_GET['token']) ? (string) $_GET['token'] : '';

      if (!$uid || !$token) {
        wp_safe_redirect($this->step_url('register'));
        exit;
      }

      $hash = (string) get_user_meta($uid, self::META_TOKEN, true);
      $exp  = (int) get_user_meta($uid, self::META_TOKEN_EXPIRES, true);

      if (!$hash || $exp < time()) {
        wp_safe_redirect(add_query_arg('verify', 'expired', $this->step_url('register')));
        exit;
      }

      if (!hash_equals($hash, wp_hash($token))) {
        wp_safe_redirect(add_query_arg('verify', 'invalid', $this->step_url('register')));
        exit;
      }

      update_user_meta($uid, self::META_VERIFIED, 1);
      delete_user_meta($uid, self::META_TOKEN);
      delete_user_meta($uid, self::META_TOKEN_EXPIRES);

      $this->assign_trial_level($uid);

      wp_set_current_user($uid);
      wp_set_auth_cookie($uid);

      if (!$this->is_onboard_done($uid)) {
        wp_safe_redirect($this->step_url('registro-datos'));
      } else {
        wp_safe_redirect($this->dashboard_url());
      }
      exit;
    }

    if ($path === self::DASHBOARD_SLUG) {
      if (!is_user_logged_in()) {
        wp_safe_redirect($this->step_url('register'));
        exit;
      }

      $uid = get_current_user_id();

      if (!$this->is_verified($uid)) {
        wp_safe_redirect(add_query_arg('need_verify', '1', $this->step_url('register')));
        exit;
      }

      if (!$this->current_user_has_trial_active()) {
        wp_safe_redirect(add_query_arg('trial', 'inactive', $this->step_url('register')));
        exit;
      }

      if (!$this->is_onboard_done($uid)) {
        wp_safe_redirect($this->step_url('registro-datos'));
        exit;
      }
    }

    if (is_user_logged_in() && $path === 'registro-email') {
      wp_safe_redirect($this->step_url('registro-datos'));
      exit;
    }
  }

  public function login_redirect($redirect_to, $requested, $user) {
    if ($user instanceof WP_User) {
      return $this->dashboard_url();
    }
    return $redirect_to;
  }

  public function block_wp_admin_for_unverified() {
    if (!is_user_logged_in()) return;

    if (current_user_can('manage_options')) return;

    if (defined('DOING_AJAX') && DOING_AJAX) return;

    $uid = get_current_user_id();
    if (!$this->is_verified($uid)) {
      wp_safe_redirect($this->step_url('register'));
      exit;
    }
  }
}
