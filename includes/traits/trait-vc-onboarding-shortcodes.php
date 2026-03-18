<?php

if (!defined('ABSPATH')) exit;

trait VC_Onboarding_Wizard_Shortcodes {
  public function use_blank_template_for_onboarding($template) {
    if (!is_page()) return $template;

    global $post;
    if (!$post) return $template;

    if ($post->post_name === self::DASHBOARD_SLUG || has_shortcode($post->post_content, 'vc_member_dashboard')) {
      $dashboard_template = VC_OW_PLUGIN_DIR . 'templates/vc-dashboard-page.php';
      if (file_exists($dashboard_template)) {
        return $dashboard_template;
      }
    }

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

    $account_url = $this->dashboard_url();
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

  public function shortcode_member_dashboard(): string {
    if (!is_user_logged_in()) {
      wp_safe_redirect($this->step_url('register'));
      exit;
    }

    $uid = get_current_user_id();
    if (!$this->is_verified($uid) || !$this->user_has_trial_active($uid) || !$this->is_onboard_done($uid)) {
      wp_safe_redirect($this->step_url('register'));
      exit;
    }

    $user = wp_get_current_user();
    if (!$user instanceof WP_User || !$user->exists()) {
      return '';
    }

    $view = isset($_GET['view']) ? sanitize_key(wp_unslash($_GET['view'])) : 'flashcards';
    $allowed_views = ['flashcards', 'mock-test', 'profile', 'privacy', 'subscription', 'help'];
    if (!in_array($view, $allowed_views, true)) {
      $view = 'flashcards';
    }

    $view_data = $this->get_dashboard_view_data($view, $uid);

    return $this->render_template('templates/dashboard/dashboard.php', [
      'logo_url' => VC_OW_PLUGIN_URL . 'templates/assets/aeropro-logo2.svg',
      'search_placeholder' => apply_filters('vc_onboarding_dashboard_search_placeholder', 'Search courses, resources...', $uid),
      'display_name' => $this->get_user_display_label($user),
      'membership_label' => $this->get_membership_label($uid),
      'initials' => $this->get_user_initials($user),
      'avatar_url' => $this->get_profile_avatar_url($uid, 'medium'),
      'nav_items' => $this->get_dashboard_nav_items($view),
      'active_view' => $view,
      'page_title' => $view_data['title'],
      'page_subtitle' => $view_data['subtitle'],
      'content_html' => $view_data['content'],
      'logout_url' => wp_logout_url($this->step_url('register')),
    ]);
  }

  private function get_dashboard_nav_items(string $active_view): array {
    $items = [
      ['view' => 'flashcards', 'label' => 'Flashcards', 'group' => 'A&P Study tools', 'icon' => 'cards'],
      ['view' => 'mock-test', 'label' => 'A&P Mock test', 'group' => 'A&P Study tools', 'icon' => 'clipboard'],
      ['view' => 'profile', 'label' => 'Profile', 'group' => 'My profile', 'icon' => 'user'],
      ['view' => 'privacy', 'label' => 'Privacy', 'group' => 'My profile', 'icon' => 'lock'],
      ['view' => 'subscription', 'label' => 'Subscription', 'group' => 'My profile', 'icon' => 'wallet'],
      ['view' => 'help', 'label' => 'Help', 'group' => 'Support', 'icon' => 'help'],
    ];

    foreach ($items as &$item) {
      $item['url'] = $this->dashboard_view_url($item['view']);
      $item['is_active'] = $item['view'] === $active_view;
    }

    return $items;
  }

  private function dashboard_view_url(string $view): string {
    if ($view === 'flashcards') {
      return $this->dashboard_url();
    }

    return add_query_arg('view', $view, $this->dashboard_url());
  }

  private function get_dashboard_view_data(string $view, int $user_id): array {
    switch ($view) {
      case 'mock-test':
        return [
          'title' => 'A&P Mock test',
          'subtitle' => 'Simulate the FAA exam flow with timed practice and review mode.',
          'content' => $this->render_dashboard_mock_test_view(),
        ];
      case 'profile':
        return [
          'title' => 'My profile',
          'subtitle' => 'Manage your personal information and account details',
          'content' => $this->render_dashboard_profile_view($user_id),
        ];
      case 'privacy':
        return [
          'title' => 'Privacy & Security',
          'subtitle' => 'Control access, password recovery and the security status of your account.',
          'content' => $this->render_dashboard_privacy_view($user_id),
        ];
      case 'subscription':
        return [
          'title' => 'Subscription',
          'subtitle' => 'Manage your plan, invoices and subscription settings.',
          'content' => $this->render_dashboard_subscription_view($user_id),
        ];
      case 'help':
        return [
          'title' => 'Help',
          'subtitle' => 'Support resources and the fastest way to get assistance.',
          'content' => $this->render_dashboard_help_view(),
        ];
      case 'flashcards':
      default:
        return [
          'title' => 'Flashcards',
          'subtitle' => 'Practice under real exam conditions and track your progress.',
          'content' => $this->render_dashboard_flashcards_view($user_id),
        ];
    }
  }

  private function render_dashboard_flashcards_view(int $user_id): string {
    if (shortcode_exists('vc_flashcards_app')) {
      return do_shortcode('[vc_flashcards_app]');
    }

    $default_stats = [
      ['label' => 'Best score', 'value' => '85%', 'icon' => 'trophy'],
      ['label' => 'Average', 'value' => '78%', 'icon' => 'trend'],
      ['label' => 'Passed attempts', 'value' => '3/5', 'icon' => 'badge'],
    ];

    $default_subjects = [
      ['title' => 'General', 'progress' => 63, 'meta' => '12 subtopics · 142 reviewed', 'cta_label' => 'Study', 'cta_url' => '#'],
      ['title' => 'Airframe', 'progress' => 45, 'meta' => '12 subtopics · 98 reviewed', 'cta_label' => 'Study', 'cta_url' => '#'],
      ['title' => 'Powerplant', 'progress' => 38, 'meta' => '12 subtopics · 76 reviewed', 'cta_label' => 'Study', 'cta_url' => '#'],
    ];

    $summary_stats = apply_filters('vc_onboarding_dashboard_summary_stats', $default_stats, $user_id);
    $subjects = apply_filters('vc_onboarding_dashboard_flashcard_subjects', $default_subjects, $user_id);
    $global_card = apply_filters('vc_onboarding_dashboard_global_practice', [
      'title' => 'Global Random Practice',
      'description' => 'Mix cards from all categories for a comprehensive review',
      'cta_label' => 'Start study',
      'cta_url' => '#',
    ], $user_id);

    return $this->render_template('templates/dashboard/flashcards.php', [
      'summary_stats' => is_array($summary_stats) ? $summary_stats : $default_stats,
      'subjects' => is_array($subjects) ? $subjects : $default_subjects,
      'global_card' => is_array($global_card) ? $global_card : [],
    ]);
  }

  private function render_dashboard_mock_test_view(): string {
    $checkout_url = function_exists('pmpro_url') ? pmpro_url('levels') : '#';

    return '<section class="vc-dashboard-panel-grid vc-dashboard-panel-grid--single">'
      . '<article class="vc-dashboard-card vc-dashboard-card--callout">'
      . '<h3>Exam mode coming next</h3>'
      . '<p>Use this area for timed tests, graded attempts and review mode. The layout is already wired into the dashboard so you can connect the real test engine here.</p>'
      . '<div class="vc-dashboard-card__actions"><a class="vc-dashboard-button vc-dashboard-button--secondary" href="' . esc_url($checkout_url) . '">View plans</a></div>'
      . '</article>'
      . '</section>';
  }

  private function render_dashboard_profile_view(int $user_id): string {
    $user = get_userdata($user_id);
    if (!$user instanceof WP_User) {
      return '';
    }

    $full_name = trim($this->get_user_meta_string($user_id, 'first_name') . ' ' . $this->get_user_meta_string($user_id, 'last_name'));
    if ($full_name === '') {
      $full_name = trim((string) $user->display_name);
    }
    if ($full_name === '') {
      $full_name = $user->user_login;
    }

    return $this->render_template('templates/dashboard/profile.php', [
      'notice_html' => $this->get_profile_notice_from_query(),
      'action_url' => admin_url('admin-post.php'),
      'nonce_html' => wp_nonce_field('vc_onboard_save_account_profile', 'vc_account_profile_nonce', false, false),
      'member_name' => $full_name,
      'membership_label' => $this->get_membership_label($user_id),
      'member_since' => $this->get_member_since_label($user),
      'avatar_url' => $this->get_profile_avatar_url($user_id, 'medium'),
      'avatar_initials' => $this->get_user_initials($user),
      'first_name' => $this->get_user_meta_string($user_id, 'first_name'),
      'last_name' => $this->get_user_meta_string($user_id, 'last_name'),
      'email' => $this->get_pending_email($user_id) ?: (string) $user->user_email,
      'current_email' => (string) $user->user_email,
      'pending_email' => $this->get_pending_email($user_id),
      'email_verified' => $this->is_verified($user_id) && $this->get_pending_email($user_id) === '',
      'phone' => $this->get_user_meta_string($user_id, self::META_PHONE),
      'location' => $this->get_user_meta_string($user_id, self::META_LOCATION),
      'bio' => $this->get_user_meta_string($user_id, self::META_BIO),
      'cancel_url' => $this->dashboard_view_url('profile'),
      'show_delete_photo' => $this->get_profile_avatar_id($user_id) > 0,
    ]);
  }

  private function render_dashboard_privacy_view(int $user_id): string {
    return $this->render_template('templates/dashboard/privacy.php', [
      'notice_html' => $this->get_profile_notice_from_query(),
      'action_url' => admin_url('admin-post.php'),
      'nonce_html' => wp_nonce_field('vc_onboard_change_password', 'vc_change_password_nonce', false, false),
    ]);
  }

  private function render_dashboard_subscription_view(int $user_id): string {
    $level = $this->get_primary_membership_level($user_id);
    $subscription = null;
    $next_payment_date = '';
    $price_label = 'N/A';
    $billing_date_label = 'Next invoice';
    $plan_name = $level && !empty($level->name) ? (string) $level->name : 'No active plan';
    $status_label = 'Inactive';
    $status_class = 'is-inactive';
    $is_free_plan = false;

    if (!empty($level)) {
      $billing_amount = isset($level->billing_amount) ? (float) $level->billing_amount : 0.0;
      $initial_payment = isset($level->initial_payment) ? (float) $level->initial_payment : 0.0;
      $is_free_plan = $billing_amount <= 0 && $initial_payment <= 0;

      if (function_exists('pmpro_formatPrice') && $billing_amount > 0) {
        $price_label = pmpro_formatPrice($billing_amount) . '/month';
      } elseif (function_exists('pmpro_formatPrice') && $initial_payment > 0) {
        $price_label = pmpro_formatPrice($initial_payment);
      } elseif ($is_free_plan) {
        $price_label = 'Free';
      }

      if (class_exists('PMPro_Subscription')) {
        $subscriptions = PMPro_Subscription::get_subscriptions_for_user($user_id, (int) $level->id);
        if (!empty($subscriptions)) {
          $subscription = $subscriptions[0];
        }
      }

      $status_label = 'Active';
      $status_class = 'is-active';
    }

    if ($is_free_plan) {
      $billing_date_label = 'Expiration date';
    }

    if ($subscription && method_exists($subscription, 'get_next_payment_date')) {
      $next_payment_date = (string) $subscription->get_next_payment_date(get_option('date_format'));
    }

    if ($next_payment_date === '') {
      $normalized_enddate = $this->get_dashboard_membership_timestamp($level->enddate ?? null);

      if ($normalized_enddate > 0) {
        $next_payment_date = date_i18n(get_option('date_format'), $normalized_enddate);
      } elseif (
        $is_free_plan
        && !empty($level->startdate)
        && !empty($level->expiration_number)
        && !empty($level->expiration_period)
      ) {
        $start_timestamp = $this->get_dashboard_membership_timestamp($level->startdate);
        $expires_timestamp = strtotime(
          '+ ' . (int) $level->expiration_number . ' ' . (string) $level->expiration_period,
          $start_timestamp > 0 ? $start_timestamp : 0
        );

        $next_payment_date = $start_timestamp > 0 && $expires_timestamp
          ? date_i18n(get_option('date_format'), $expires_timestamp)
          : 'Not scheduled';
      } else {
        $next_payment_date = 'Not scheduled';
      }
    }

    $orders = [];
    if (class_exists('MemberOrder')) {
      $orders = MemberOrder::get_orders([
        'limit' => 3,
        'status' => ['success', 'pending', 'refunded'],
        'user_id' => $user_id,
      ]);
    }

    $invoice_items = [];
    if (!empty($orders)) {
      foreach ($orders as $order_summary) {
        $order = new MemberOrder();
        $order->getMemberOrderByID($order_summary->id);
        $invoice_items[] = [
          'code' => (string) $order->code,
          'date' => date_i18n(get_option('date_format'), $order->getTimestamp()),
          'status' => $order->status === 'pending' ? 'Pending' : ($order->status === 'refunded' ? 'Refunded' : 'Paid'),
          'status_class' => $order->status === 'pending' ? 'is-pending' : ($order->status === 'refunded' ? 'is-refunded' : 'is-paid'),
          'amount' => method_exists($order, 'get_formatted_total') ? wp_strip_all_tags((string) $order->get_formatted_total()) : '',
          'url' => function_exists('pmpro_url') ? pmpro_url('invoice', '?invoice=' . $order->code) : '#',
        ];
      }
    }

    return $this->render_template('templates/dashboard/subscription.php', [
      'plan_name' => $plan_name,
      'price_label' => $price_label,
      'billing_date_label' => $billing_date_label,
      'next_payment_date' => $next_payment_date,
      'status_label' => $status_label,
      'status_class' => $status_class,
      'billing_url' => function_exists('pmpro_url') ? pmpro_url('billing') : '#',
      'levels_url' => function_exists('pmpro_url') ? pmpro_url('levels') : '#',
      'cancel_url' => function_exists('pmpro_url') ? pmpro_url('cancel') : '#',
      'invoice_items' => $invoice_items,
    ]);
  }

  private function get_dashboard_membership_timestamp($value): int {
    if (is_numeric($value)) {
      $timestamp = (int) $value;
      return $timestamp > 0 ? $timestamp : 0;
    }

    $date = trim((string) $value);
    if ($date === '' || strpos($date, '0000-00-00') === 0) {
      return 0;
    }

    $timestamp = strtotime($date);
    return $timestamp ?: 0;
  }

  private function render_dashboard_help_view(): string {
    $support_email = apply_filters('vc_onboarding_dashboard_support_email', get_option('admin_email'));

    return '<section class="vc-dashboard-panel-grid vc-dashboard-panel-grid--single">'
      . '<article class="vc-profile-card vc-help-card"><h2>Need help?</h2><p>If a user cannot access plan features or has billing questions, direct them here first.</p>'
      . '<ul class="vc-dashboard-detail-list">'
      . '<li><span>Support email</span><strong>' . esc_html($support_email) . '</strong></li>'
      . '</ul></article>'
      . '</section>';
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
