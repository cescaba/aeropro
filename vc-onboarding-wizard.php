<?php
/**
 * Plugin Name: VC Onboarding Wizard (PMPro)
 * Description: Registro multi-paso + verificación email + trial 14 días sin tarjeta (PMPro level) + bloqueo dashboard.
 * Version: 1.0.0
 * Author: VC Studio
 */

if (!defined('ABSPATH')) exit;

class VC_Onboarding_Wizard_PMPro {
  const META_VERIFIED = 'vc_email_verified';
  const META_TOKEN = 'vc_email_verification_token';
  const META_TOKEN_EXPIRES = 'vc_email_verification_expires';
  const META_ONBOARD_DONE = 'vc_onboard_done';
  const META_CERT = 'vc_cert_track';
  const META_ROLE = 'vc_user_stage';

  // Ajusta estos 2 valores en tu instalación:
  const TRIAL_LEVEL_ID = 1; // <-- CAMBIA ESTO al ID del level "Trial 14 días"
  const DASHBOARD_SLUG = 'dashboard'; // página /dashboard/

  public function __construct() {
    add_shortcode('vc_onboard_step1', [$this, 'shortcode_step1']);
    add_shortcode('vc_onboard_step2', [$this, 'shortcode_step2']);
    add_shortcode('vc_onboard_step3', [$this, 'shortcode_step3']);
    add_shortcode('vc_onboard_final', [$this, 'shortcode_final']);
    add_shortcode('vc_onboard_verify', [$this, 'shortcode_verify']); // opcional, normalmente /verificar/ solo redirige

    add_action('admin_post_nopriv_vc_onboard_email_start', [$this, 'handle_email_start']);
    add_action('admin_post_vc_onboard_email_start', [$this, 'handle_email_start']);

    add_action('admin_post_nopriv_vc_onboard_save_profile', [$this, 'handle_save_profile']);
    add_action('admin_post_vc_onboard_save_profile', [$this, 'handle_save_profile']);

    add_action('template_redirect', [$this, 'guard_routes']);
    add_filter('login_redirect', [$this, 'login_redirect'], 10, 3);
add_filter('template_include', [$this, 'use_blank_template_for_onboarding'], 99);

    // Seguridad básica: si un usuario no verificado intenta usar wp-admin, lo sacamos
    add_action('admin_init', [$this, 'block_wp_admin_for_unverified']);
    add_action('wp_enqueue_scripts', function() {

    if (is_page()) { // opcional si quieres limitar
        wp_enqueue_script(
            'vc-onboarding-js',
            plugin_dir_url(__FILE__) . 'templates/assets/js/onboarding.js',
            [],
            '1.0.0',
            true
        );
    }

});

  }

  /* -------------------------
   * Helpers
   * ------------------------- */

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

    // Ya lo tiene
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

  /* -------------------------
   * Shortcodes (UI)
   * ------------------------- */
public function use_blank_template_for_onboarding($template) {
  if (!is_page()) return $template;

  global $post;
  if (!$post) return $template;

  $slugs = ['register', 'registro-email', 'registro-datos', 'registro-final', 'verificar'];

  if (!in_array($post->post_name, $slugs, true)) return $template;

  // Intenta usar una plantilla "blank" del tema (si existe)
  $candidates = [
    'page-blank.php',
    'blank.php',
    'templates/blank.php',
    'elementor_canvas', // algunos temas/Elementor
  ];

  // Si hay un archivo real en el tema, úsalo
  foreach (['page-blank.php', 'blank.php', 'templates/blank.php'] as $file) {
    $found = locate_template($file);
    if ($found) return $found;
  }

  // Fallback: usar un template minimalista desde el plugin
  $fallback = plugin_dir_path(__FILE__) . 'templates/vc-blank-page.php';
  if (file_exists($fallback)) return $fallback;

  return $template;
}

public function shortcode_step1(): string {
  // Si ya está logueado
  if (is_user_logged_in()) {
    $uid = get_current_user_id();
    $steps = [
      ['key' => 'account', 'label' => 'Account'],
      ['key' => 'profile', 'label' => 'Profile'],
      ['key' => 'home',    'label' => 'Home'],
    ];
    if (!$this->is_verified($uid)) {
      //return $this->render_notice('Please verify your email to continue. Check your inbox (and spam).', 'error') . $this->inline_css();
    // 1 = step actual (0-based). En tu imagen, el step actual es "Profile" (índice 1)
      $current_step = 1;

      $html  = '<nav class="vc-stepper" aria-label="Wizard steps">';
      $html .= '  <ol class="vc-stepper__list">';

      foreach ($steps as $i => $step) {
        $state = 'is-upcoming';
        if ($i < $current_step) $state = 'is-done';
        if ($i === $current_step) $state = 'is-current';

        $html .= '    <li class="vc-stepper__item ' . esc_attr($state) . '">';

        // Círculo (check / número)
        $html .= '      <div class="vc-stepper__circle" aria-hidden="true">';
        if ($state === 'is-done') {
          // check (SVG)
          $html .= '        <svg class="vc-stepper__check" viewBox="0 0 24 24" role="img" focusable="false" aria-hidden="true">';
          $html .= '          <path d="M20 6L9 17l-5-5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>';
          $html .= '        </svg>';
        } else {
          $html .= '        <span class="vc-stepper__num">' . (int)($i + 1) . '</span>';
        }
        $html .= '      </div>';

        // Línea (conector) excepto último
        if ($i < count($steps) - 1) {
          $connector_state = ($i < $current_step) ? 'is-filled' : 'is-empty';
          $html .= '      <span class="vc-stepper__line ' . esc_attr($connector_state) . '" aria-hidden="true"></span>';
        }

        // Label
        $aria_current = ($state === 'is-current') ? ' aria-current="step"' : '';
        $html .= '      <span class="vc-stepper__label"' . $aria_current . '>' . esc_html($step['label']) . '</span>';

        $html .= '    </li>';
      }

      $html .= '  </ol>';
      $html .= '</nav>';

      $html .= $this->inline_css();

      return $html;
    }

    if (!$this->is_onboard_done($uid)) {
      wp_safe_redirect($this->step_url('registro-datos'));
      exit;
    }

    wp_safe_redirect($this->dashboard_url());
    exit;
  }
  $assets_url = plugin_dir_url(__FILE__) . 'templates/assets/';
  $google = $assets_url . 'logo-google.svg';

  $action_email = esc_url($this->step_url('registro-email'));
  $html .= '<p class="h3">Start your preparation</p>';
  $html .= '<p class="subtitle">Join hundreds of students preparing for the A&P exam</p>';
  // Google (placeholder)

  $html .= '<a class="button button-google" href="' . esc_url(wp_login_url($this->step_url('registro-datos'))) . '">
      <img src="' . esc_url($google) . '" alt="Aeropro">
      Continue with Google
  </a>';
  $html .= '<div class="divider"><span>o</span></div>';
  // Email: solo capturamos email y enviamos al paso 2
  $html .= '<form id="step-1" method="get" action="' . $action_email . '">';
$html .= '<p><input type="email" name="email" placeholder="Your email address" required></p>';
  $html .= '<p><button class="button button-primary" type="submit">Create account</button></p>';
  $html .= '</form>';
  $html .= '<p class="mensaje">By creating an account, you agree to our Terms of Service and Privacy Policy</p>';
  $html .= '<p class="mensaje-bottom">Already have an account?<a href="#">Log in</a></p>';

  // Avisos del flujo
  if (isset($_GET['check_email']) && $_GET['check_email'] === '1') {
    $html .= $this->render_notice('Check your email to verify your account. You must verify before accessing the dashboard.', 'info');
  }
  if (isset($_GET['verify']) && $_GET['verify'] === 'expired') {
    $html .= $this->render_notice('Verification link expired. Please sign up again.', 'error');
  }
  if (isset($_GET['verify']) && $_GET['verify'] === 'invalid') {
    $html .= $this->render_notice('Invalid verification link. Please sign up again.', 'error');
  }

  $html .= $this->inline_css();

  return $html;
}


public function shortcode_step2(): string {
  // Si está logueado, al paso 3
  if (is_user_logged_in()) {
    wp_safe_redirect($this->step_url('registro-datos'));
    exit;
  }

  $prefill_email = isset($_GET['email']) ? sanitize_email($_GET['email']) : '';
  if (empty($prefill_email)) {
    wp_safe_redirect($this->step_url('register'));
    exit;
  }

  $action = esc_url(admin_url('admin-post.php'));
  $html .= '<p class="h3">Set up your password</p>';
  $html .= '<p class="subtitle">Create a secure password for your account</p>';
  $html .= '<form id="step-2" method="post" action="' . $action . '">';
  $html .= '<input type="hidden" name="action" value="vc_onboard_email_start" />';
  $html .= wp_nonce_field('vc_onboard_email_start', '_wpnonce', true, false);

  // Enviamos el email oculto
  $html .= '<input type="hidden" name="email" value="' . esc_attr($prefill_email) . '" />';

  $html .= '<p style="text-align: left;" ><label>Password*</label><br><input type="password" placeholder="Your password" name="password" minlength="8" required></p>';
  $html .= '<p style="text-align: left;" class="last"><label>Confirm password<br></label><input type="password" placeholder="Confirm password" name="password_confirm" minlength="8" required></p>';

  $html .= '<p><button class="button button-primary" type="submit">Continue</button></p>';
  $html .= '</form>';
  if (isset($_GET['err']) && $_GET['err'] === 'pass_mismatch') {
  $html .= $this->render_notice('Passwords do not match. Please try again.', 'error');
}
  $html .= $this->inline_css();

  return $html;
}

  public function shortcode_step3(): string {
    if (!is_user_logged_in()) {
      wp_safe_redirect($this->step_url('register'));
      exit;
    }

    $uid = get_current_user_id();
    $steps = [
      ['key' => 'account', 'label' => 'Account'],
      ['key' => 'profile', 'label' => 'Profile'],
      ['key' => 'home',    'label' => 'Home'],
    ];
    if (!$this->is_verified($uid)) {

      // 1 = step actual (0-based). En tu imagen, el step actual es "Profile" (índice 1)
      $current_step = 1;

      $html  = '<nav class="vc-stepper" aria-label="Wizard steps">';
      $html .= '  <ol class="vc-stepper__list">';

      foreach ($steps as $i => $step) {
        $state = 'is-upcoming';
        if ($i < $current_step) $state = 'is-done';
        if ($i === $current_step) $state = 'is-current';

        $html .= '    <li class="vc-stepper__item ' . esc_attr($state) . '">';

        // Círculo (check / número)
        $html .= '      <div class="vc-stepper__circle" aria-hidden="true">';
        if ($state === 'is-done') {
          // check (SVG)
          $html .= '        <svg class="vc-stepper__check" viewBox="0 0 24 24" role="img" focusable="false" aria-hidden="true">';
          $html .= '          <path d="M20 6L9 17l-5-5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>';
          $html .= '        </svg>';
        } else {
          $html .= '        <span class="vc-stepper__num">' . (int)($i + 1) . '</span>';
        }
        $html .= '      </div>';

        // Línea (conector) excepto último
        if ($i < count($steps) - 1) {
          $connector_state = ($i < $current_step) ? 'is-filled' : 'is-empty';
          $html .= '      <span class="vc-stepper__line ' . esc_attr($connector_state) . '" aria-hidden="true"></span>';
        }

        // Label
        $aria_current = ($state === 'is-current') ? ' aria-current="step"' : '';
        $html .= '      <span class="vc-stepper__label"' . $aria_current . '>' . esc_html($step['label']) . '</span>';

        $html .= '    </li>';
      }

      $html .= '  </ol>';
      $html .= '</nav>';

      $html .= $this->inline_css();

      return $html;
      //return $this->render_notice('You must verify your email before continuing. Check your inbox.', 'error') . $this->inline_css();
    }

    // Asegurar trial activo (por si entró por Google y ya está verificado)
    $this->assign_trial_level($uid);

    $action = esc_url(admin_url('admin-post.php'));
    $first = get_user_meta($uid, 'first_name', true);
    $last  = get_user_meta($uid, 'last_name', true);
    $cert  = get_user_meta($uid, self::META_CERT, true);
    $stage = get_user_meta($uid, self::META_ROLE, true);

    $html  = '<div class="vc-onboard">';
    $html .= '<h2>Your details</h2>';
    $html .= '<form method="post" action="' . $action . '">';
    $html .= '<input type="hidden" name="action" value="vc_onboard_save_profile" />';
    $html .= wp_nonce_field('vc_onboard_save_profile', '_wpnonce', true, false);

    $html .= '<p><label>First name<br><input type="text" name="first_name" value="' . esc_attr($first) . '" required></label></p>';
    $html .= '<p><label>Last name<br><input type="text" name="last_name" value="' . esc_attr($last) . '" required></label></p>';

    $html .= '<p><label>Certificate track<br>';
    $html .= '<select name="cert_track" required>';
    $html .= '<option value="">Select…</option>';
    $html .= $this->opt('Airframe', $cert);
    $html .= $this->opt('Powerplant', $cert);
    $html .= $this->opt('Both (A&P)', $cert);
    $html .= '</select></label></p>';

    $html .= '<p><label>I am a…<br>';
    $html .= '<select name="user_stage" required>';
    $html .= '<option value="">Select…</option>';
    $html .= $this->opt('Student', $stage);
    $html .= $this->opt('Graduate', $stage);
    $html .= $this->opt('Technician', $stage);
    $html .= '</select></label></p>';

    $html .= '<p><button class="button button-primary" type="submit">Continue</button></p>';
    $html .= '</form>';
    $html .= '</div>';

    $html .= $this->inline_css();

    return $html;
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

    // Redirige al dashboard automáticamente
    wp_safe_redirect($this->dashboard_url());
    exit;
  }

  public function shortcode_verify(): string {
    // Esta página normalmente solo redirige vía guard_routes()
    return $this->render_notice('Verifying…', 'info') . $this->inline_css();
  }

  private function opt(string $value, $current): string {
    $sel = ($current === $value) ? ' selected' : '';
    return '<option value="' . esc_attr($value) . '"' . $sel . '>' . esc_html($value) . '</option>';
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

  /* -------------------------
   * Handlers
   * ------------------------- */

  public function handle_email_start() {
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'vc_onboard_email_start')) {
      wp_die('Invalid nonce');
    }

    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $pass  = isset($_POST['password']) ? (string) $_POST['password'] : '';
$pass2 = isset($_POST['password_confirm']) ? (string) $_POST['password_confirm'] : '';

if ($pass !== $pass2) {
  // volver al paso 2 con el email preservado
  wp_safe_redirect(add_query_arg(['email' => $email, 'err' => 'pass_mismatch'], $this->step_url('registro-email')));
  exit;
}
    if (empty($email) || empty($pass) || strlen($pass) < 8) {
      wp_safe_redirect(add_query_arg('err', '1', $this->step_url('registro-email')));
      exit;
    }

    if (email_exists($email)) {
      // Si ya existe, forzar login y luego verify
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

    // Marcar no verificado
    update_user_meta($user_id, self::META_VERIFIED, 0);

    // Token
    $token = wp_generate_password(32, false, false);
    $hash  = wp_hash($token);
    update_user_meta($user_id, self::META_TOKEN, $hash);
    update_user_meta($user_id, self::META_TOKEN_EXPIRES, time() + 24 * 3600);

    // Enviar email
    $this->send_verification_email($user_id, $token);

    // Loguear al usuario (pero sin acceso hasta verificar)
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);

    // Mostrar aviso en /registro/ (paso 1) o redirigir a una página “revisa email”
    wp_safe_redirect(add_query_arg('check_email', '1', $this->step_url('register')));
    exit;
  }

  public function handle_save_profile() {
    if (!is_user_logged_in()) {
      wp_safe_redirect($this->step_url('register'));
      exit;
    }

    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'vc_onboard_save_profile')) {
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

    // Asegurar trial (por si no se asignó aún)
    $this->assign_trial_level($uid);

    // Marcar onboarding completado
    update_user_meta($uid, self::META_ONBOARD_DONE, 1);

    // Ir al dashboard (paso final)
    wp_safe_redirect($this->dashboard_url());
    exit;
  }

  /* -------------------------
   * Guards: bloqueo acceso
   * ------------------------- */

  public function guard_routes() {
    $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

    // Verificación: /verificar/?uid=&token=
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

      // OK => marcar verificado
      update_user_meta($uid, self::META_VERIFIED, 1);
      delete_user_meta($uid, self::META_TOKEN);
      delete_user_meta($uid, self::META_TOKEN_EXPIRES);

      // Asignar trial 14 días
      $this->assign_trial_level($uid);

      // Loguear por si no lo estaba
      wp_set_current_user($uid);
      wp_set_auth_cookie($uid);

      // Si faltan datos, al step 3; si no, dashboard
      if (!$this->is_onboard_done($uid)) {
        wp_safe_redirect($this->step_url('registro-datos'));
      } else {
        wp_safe_redirect($this->dashboard_url());
      }
      exit;
    }

    // Bloqueo del dashboard
    if ($path === self::DASHBOARD_SLUG) {
      if (!is_user_logged_in()) {
        wp_safe_redirect($this->step_url('register'));
        exit;
      }

      $uid = get_current_user_id();

      // Debe estar verificado + trial activo + onboarding done
      if (!$this->is_verified($uid)) {
        wp_safe_redirect(add_query_arg('need_verify', '1', $this->step_url('register')));
        exit;
      }

      if (!$this->current_user_has_trial_active()) {
        // Trial expirado o no asignado
        wp_safe_redirect(add_query_arg('trial', 'inactive', $this->step_url('register')));
        exit;
      }

      if (!$this->is_onboard_done($uid)) {
        wp_safe_redirect($this->step_url('registro-datos'));
        exit;
      }
    }

    // Si está logueado pero intenta ir a /registro-email/ ya no tiene sentido
    if (is_user_logged_in() && $path === 'registro-email') {
      wp_safe_redirect($this->step_url('registro-datos'));
      exit;
    }
  }

  public function login_redirect($redirect_to, $requested, $user) {
    // Siempre al dashboard después de login, pero el guard_routes decidirá si puede entrar.
    if ($user instanceof WP_User) {
      return $this->dashboard_url();
    }
    return $redirect_to;
  }

public function block_wp_admin_for_unverified() {
  if (!is_user_logged_in()) return;

  // ✅ Admins: no bloquear nunca
  if (current_user_can('manage_options')) return;

  // Permitir AJAX
  if (defined('DOING_AJAX') && DOING_AJAX) return;

  $uid = get_current_user_id();
  if (!$this->is_verified($uid)) {
    wp_safe_redirect($this->step_url('register'));
    exit;
  }
}
}

new VC_Onboarding_Wizard_PMPro();
