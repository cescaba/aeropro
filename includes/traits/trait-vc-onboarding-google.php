<?php

if (!defined('ABSPATH')) exit;

/**
 * Login con Google sobre Nextend Social Login (NSL).
 *
 * Las credenciales se administran en Ajustes > Google Login y se inyectan en NSL
 * via filtros, asi no hace falta tocar la pantalla de NSL.
 */
trait VC_Onboarding_Wizard_Google {
  private function register_google_hooks() {
    // Estos filtros deben existir antes de que NSL lea sus ajustes (plugins_loaded).
    add_filter('nsl_finalize_settings_nextend_social_login', [$this, 'google_filter_nsl_settings']);
    add_filter('nsl_finalize_settings_nsl_google', [$this, 'google_filter_provider_settings']);

    add_filter('google_login_redirect_url', [$this, 'google_post_auth_redirect'], 10, 1);
    add_filter('google_register_redirect_url', [$this, 'google_post_auth_redirect'], 10, 1);

    add_action('nsl_google_register_new_user', [$this, 'google_on_register'], 10, 1);
    add_action('nsl_google_login', [$this, 'google_on_login'], 10, 1);

    add_action('admin_menu', [$this, 'google_register_settings_page']);
    add_action('admin_init', [$this, 'google_register_settings']);
  }

  private function google_options(): array {
    $stored = get_option(self::OPTION_GOOGLE, []);
    if (!is_array($stored)) $stored = [];

    return array_merge([
      'enabled' => 0,
      'client_id' => '',
      'client_secret' => '',
      'select_account' => 1,
    ], $stored);
  }

  private function google_has_credentials(): bool {
    $opts = $this->google_options();
    return $opts['client_id'] !== '' && $opts['client_secret'] !== '';
  }

  private function google_is_configured(): bool {
    $opts = $this->google_options();
    return !empty($opts['enabled']) && $this->google_has_credentials();
  }

  /** Google queda listo solo si esta configurado aqui y NSL lo cargo como provider activo. */
  private function google_is_ready(): bool {
    return $this->google_is_configured()
      && class_exists('NextendSocialLogin')
      && NextendSocialLogin::isProviderEnabled('google');
  }

  /** URL que arranca el OAuth con Google; '' si no esta disponible (el template oculta el boton). */
  private function google_login_url(string $redirect): string {
    if (!$this->google_is_ready()) return '';

    $provider = NextendSocialLogin::getProviderByProviderID('google');
    if (!$provider) return '';

    return add_query_arg('redirect', rawurlencode($redirect), $provider->getLoginUrl());
  }

  /** Destino tras autenticar: onboarding pendiente => registro-datos, si no dashboard. */
  private function post_auth_url(int $user_id): string {
    if (!$this->is_onboard_done($user_id)) {
      return $this->step_url('registro-datos');
    }

    return $this->dashboard_url();
  }

  public function google_filter_nsl_settings($settings) {
    $enabled = isset($settings['enabled']) && is_array($settings['enabled']) ? $settings['enabled'] : [];
    $enabled = array_values(array_diff($enabled, ['google']));

    if ($this->google_is_configured()) {
      $enabled[] = 'google';
      // El wizard crea usuarios por su cuenta; NSL no debe depender de users_can_register.
      $settings['allow_register'] = 1;

      // El wizard ya pinta su propio boton; evita el duplicado que NSL inyecta en los formularios.
      $settings['show_login_form'] = 'hide';
      $settings['show_registration_form'] = 'hide';
      $settings['show_embedded_login_form'] = 'hide';
    }

    $settings['enabled'] = $enabled;
    return $settings;
  }

  public function google_filter_provider_settings($settings) {
    if (!$this->google_is_configured()) return $settings;

    $opts = $this->google_options();
    $settings['client_id'] = $opts['client_id'];
    $settings['client_secret'] = $opts['client_secret'];
    $settings['select_account'] = !empty($opts['select_account']) ? 1 : 0;
    $settings['tested'] = 1;

    return $settings;
  }

  public function google_post_auth_redirect($fixed_redirect) {
    $user_id = get_current_user_id();
    if (!$user_id) return $fixed_redirect;

    return $this->post_auth_url($user_id);
  }

  /** Usuario nuevo via Google: el correo ya viene verificado, solo falta darle el trial. */
  public function google_on_register($user_id) {
    $user_id = (int) $user_id;
    if (!$user_id) return;

    $this->google_mark_verified($user_id);
    $this->assign_trial_level($user_id);
  }

  /** Cuenta existente pendiente de verificar que entra con Google: Google ya confirmo el correo. */
  public function google_on_login($user_id) {
    $user_id = (int) $user_id;
    if (!$user_id || $this->is_verified($user_id)) return;

    $this->google_mark_verified($user_id);
    $this->assign_trial_level($user_id);
  }

  private function google_mark_verified(int $user_id) {
    update_user_meta($user_id, self::META_VERIFIED, 1);
    delete_user_meta($user_id, self::META_TOKEN);
    delete_user_meta($user_id, self::META_TOKEN_EXPIRES);
  }

  /* ---------- Backoffice ---------- */

  public function google_register_settings_page() {
    add_options_page(
      'Google Login',
      'Google Login',
      'manage_options',
      self::GOOGLE_SETTINGS_SLUG,
      [$this, 'google_render_settings_page']
    );
  }

  public function google_register_settings() {
    register_setting(self::GOOGLE_SETTINGS_GROUP, self::OPTION_GOOGLE, [
      'type' => 'array',
      'sanitize_callback' => [$this, 'google_sanitize_options'],
      'default' => [],
    ]);
  }

  public function google_sanitize_options($input) {
    $input = is_array($input) ? $input : [];
    $current = $this->google_options();

    $secret = isset($input['client_secret']) ? trim(sanitize_text_field(wp_unslash($input['client_secret']))) : '';

    return [
      'enabled' => !empty($input['enabled']) ? 1 : 0,
      'client_id' => isset($input['client_id']) ? trim(sanitize_text_field(wp_unslash($input['client_id']))) : '',
      // Campo vacio = conservar el secret guardado (nunca se vuelve a mostrar).
      'client_secret' => $secret !== '' ? $secret : $current['client_secret'],
      'select_account' => !empty($input['select_account']) ? 1 : 0,
    ];
  }

  public function google_render_settings_page() {
    if (!current_user_can('manage_options')) return;

    $opts = $this->google_options();
    $name = esc_attr(self::OPTION_GOOGLE);
    $nsl_active = class_exists('NextendSocialLogin');

    $redirect_uri = add_query_arg('loginSocial', 'google', site_url('wp-login.php'));
    if ($nsl_active && isset(NextendSocialLogin::$providers['google'])) {
      $redirect_uri = NextendSocialLogin::$providers['google']->getRedirectUriForAuthFlow();
    }
    $parts = wp_parse_url(home_url());
    $origin = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? '') . (isset($parts['port']) ? ':' . $parts['port'] : '');

    $checks = [
      'Nextend Social Login activo' => $nsl_active,
      'Login con Google habilitado' => !empty($opts['enabled']),
      'Client ID y Client Secret cargados' => $this->google_has_credentials(),
      'Provider Google activo en Nextend' => $this->google_is_ready(),
    ];
    ?>
    <div class="wrap">
      <h1>Google Login</h1>
      <p>Credenciales del OAuth Client de Google Cloud. Se aplican al boton <em>Continue with Google</em> del registro y del login.</p>

      <form method="post" action="options.php">
        <?php settings_fields(self::GOOGLE_SETTINGS_GROUP); ?>
        <table class="form-table" role="presentation">
          <tr>
            <th scope="row">Estado</th>
            <td>
              <label>
                <input type="checkbox" name="<?php echo $name; ?>[enabled]" value="1" <?php checked(!empty($opts['enabled'])); ?>>
                Habilitar login con Google
              </label>
              <p class="description">Si esta apagado, el boton se oculta en registro y login.</p>
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="vc-google-client-id">Client ID</label></th>
            <td>
              <input id="vc-google-client-id" type="text" class="large-text code" autocomplete="off"
                name="<?php echo $name; ?>[client_id]" value="<?php echo esc_attr($opts['client_id']); ?>"
                placeholder="1234567890-abc123.apps.googleusercontent.com">
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="vc-google-client-secret">Client Secret</label></th>
            <td>
              <input id="vc-google-client-secret" type="password" class="regular-text code" autocomplete="new-password"
                name="<?php echo $name; ?>[client_secret]" value=""
                placeholder="<?php echo $opts['client_secret'] !== '' ? esc_attr('Guardado (deja vacio para conservarlo)') : ''; ?>">
            </td>
          </tr>
          <tr>
            <th scope="row">Selector de cuenta</th>
            <td>
              <label>
                <input type="checkbox" name="<?php echo $name; ?>[select_account]" value="1" <?php checked(!empty($opts['select_account'])); ?>>
                Pedir siempre elegir la cuenta de Google
              </label>
            </td>
          </tr>
        </table>
        <?php submit_button('Guardar cambios'); ?>
      </form>

      <h2>Datos para Google Cloud Console</h2>
      <p>En <em>APIs y servicios &gt; Credenciales &gt; ID de cliente OAuth (Aplicacion web)</em> registra:</p>
      <table class="form-table" role="presentation">
        <tr>
          <th scope="row">URI de redireccionamiento autorizado</th>
          <td><input type="text" class="large-text code" readonly onclick="this.select()" value="<?php echo esc_attr($redirect_uri); ?>"></td>
        </tr>
        <tr>
          <th scope="row">Origen de JavaScript autorizado</th>
          <td><input type="text" class="large-text code" readonly onclick="this.select()" value="<?php echo esc_attr($origin); ?>"></td>
        </tr>
      </table>
      <p class="description">Cada entorno (local, staging, produccion) necesita su propia URI registrada en Google.</p>

      <h2>Diagnostico</h2>
      <ul>
        <?php foreach ($checks as $label => $ok): ?>
          <li><?php echo $ok ? '&#9989;' : '&#10060;'; ?> <?php echo esc_html($label); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php
  }
}
