<?php
/**
 * Plugin Name: VC Onboarding Wizard (PMPro)
 * Description: Registro multi-paso + verificación email + trial 14 días sin tarjeta (PMPro level) + bloqueo dashboard.
 * Version: 1.0.0
 * Author: VC Studio
 */

if (!defined('ABSPATH')) exit;

if (!defined('VC_OW_PLUGIN_FILE')) {
  define('VC_OW_PLUGIN_FILE', __FILE__);
}

if (!defined('VC_OW_PLUGIN_DIR')) {
  define('VC_OW_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (!defined('VC_OW_PLUGIN_URL')) {
  define('VC_OW_PLUGIN_URL', plugin_dir_url(__FILE__));
}

if (!defined('VC_OW_DB_VERSION')) {
  define('VC_OW_DB_VERSION', '1.0.7');
}

function vc_ow_locations_table_name() {
  global $wpdb;

  return $wpdb->prefix . 'vc_locations';
}

function vc_ow_create_locations_table() {
  global $wpdb;

  $table_name = vc_ow_locations_table_name();
  $charset_collate = $wpdb->get_charset_collate();

  require_once ABSPATH . 'wp-admin/includes/upgrade.php';

  $sql = "CREATE TABLE {$table_name} (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    location_type varchar(20) NOT NULL DEFAULT 'city',
    country_code varchar(2) NOT NULL DEFAULT '',
    country_name varchar(120) NOT NULL DEFAULT '',
    state_code varchar(10) NOT NULL DEFAULT '',
    state_name varchar(120) NOT NULL DEFAULT '',
    city_name varchar(120) NOT NULL DEFAULT '',
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY  (id),
    KEY location_type (location_type),
    KEY country_code (country_code),
    KEY state_code (state_code),
    KEY city_name (city_name)
  ) {$charset_collate};";

  dbDelta($sql);

  update_option('vc_ow_db_version', VC_OW_DB_VERSION);
}

function vc_ow_insert_location_if_missing(array $location) {
  global $wpdb;

  $table_name = vc_ow_locations_table_name();
  $now = current_time('mysql');
  [$type, $country_code, $country_name, $state_code, $state_name, $city_name] = $location;

  $exists = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT id FROM {$table_name}
    WHERE location_type = %s
      AND country_code = %s
      AND state_code = %s
      AND city_name = %s
    LIMIT 1",
    $type,
    $country_code,
    $state_code,
    $city_name
  ));

  if ($exists > 0) {
    return;
  }

  $wpdb->insert(
    $table_name,
    [
      'location_type' => $type,
      'country_code' => $country_code,
      'country_name' => $country_name,
      'state_code' => $state_code,
      'state_name' => $state_name,
      'city_name' => $city_name,
      'created_at' => $now,
      'updated_at' => $now,
    ],
    ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
  );
}

function vc_ow_cleanup_unsupported_locations() {
  global $wpdb;

  $table_name = vc_ow_locations_table_name();
  $wpdb->query("DELETE FROM {$table_name} WHERE location_type = 'city' OR city_name <> ''");

  delete_option('vc_ow_locations_csv_imported');
  delete_metadata('user', 0, 'vc_profile_city_name', '', true);
}

function vc_ow_seed_countries_from_csv(string $csv_path) {
  if (!file_exists($csv_path) || !is_readable($csv_path)) {
    return;
  }

  $csv_mtime = (string) filemtime($csv_path);
  if (get_option('vc_ow_countries_csv_imported') === $csv_mtime) {
    return;
  }

  $handle = fopen($csv_path, 'r');
  if (!$handle) {
    return;
  }

  $header = fgetcsv($handle);
  $columns = is_array($header) ? array_map(static function ($column) {
    return trim((string) $column, "\xEF\xBB\xBF \t\n\r\0\x0B\"'");
  }, $header) : [];
  if ($columns !== ['country_code', 'country_name']) {
    fclose($handle);
    return;
  }

  while (($row = fgetcsv($handle)) !== false) {
    $country_code = isset($row[0]) ? strtoupper(sanitize_text_field((string) $row[0])) : '';
    $country_name = isset($row[1]) ? sanitize_text_field((string) $row[1]) : '';

    if ($country_code === '' || $country_name === '') {
      continue;
    }

    vc_ow_insert_location_if_missing(['country', $country_code, $country_name, '', '', '']);
  }

  fclose($handle);
  update_option('vc_ow_countries_csv_imported', $csv_mtime);
}

function vc_ow_seed_states_from_csv(string $csv_path) {
  if (!file_exists($csv_path) || !is_readable($csv_path)) {
    return;
  }

  $csv_mtime = (string) filemtime($csv_path);
  if (get_option('vc_ow_states_csv_imported') === $csv_mtime) {
    return;
  }

  $handle = fopen($csv_path, 'r');
  if (!$handle) {
    return;
  }

  $header = fgetcsv($handle);
  $columns = is_array($header) ? array_map(static function ($column) {
    return trim((string) $column, "\xEF\xBB\xBF \t\n\r\0\x0B\"'");
  }, $header) : [];
  if ($columns !== ['country_code', 'country_name', 'state_code', 'state_name']) {
    fclose($handle);
    return;
  }

  while (($row = fgetcsv($handle)) !== false) {
    $country_code = isset($row[0]) ? strtoupper(sanitize_text_field((string) $row[0])) : '';
    $country_name = isset($row[1]) ? sanitize_text_field((string) $row[1]) : '';
    $state_code = isset($row[2]) ? strtoupper(sanitize_text_field((string) $row[2])) : '';
    $state_name = isset($row[3]) ? sanitize_text_field((string) $row[3]) : '';

    if ($country_code === '' || $country_name === '' || $state_code === '' || $state_name === '') {
      continue;
    }

    vc_ow_insert_location_if_missing(['state', $country_code, $country_name, $state_code, $state_name, '']);
  }

  fclose($handle);
  update_option('vc_ow_states_csv_imported', $csv_mtime);
}

function vc_ow_seed_default_us_states() {
  $seeded = get_option('vc_ow_us_states_seeded');

  if ($seeded === VC_OW_DB_VERSION) {
    return;
  }

  $defaults = [
    ['country', 'US', 'United States', '', '', ''],
    ['state', 'US', 'United States', 'AL', 'Alabama', ''],
    ['state', 'US', 'United States', 'AK', 'Alaska', ''],
    ['state', 'US', 'United States', 'AZ', 'Arizona', ''],
    ['state', 'US', 'United States', 'AR', 'Arkansas', ''],
    ['state', 'US', 'United States', 'CA', 'California', ''],
    ['state', 'US', 'United States', 'CO', 'Colorado', ''],
    ['state', 'US', 'United States', 'CT', 'Connecticut', ''],
    ['state', 'US', 'United States', 'DE', 'Delaware', ''],
    ['state', 'US', 'United States', 'FL', 'Florida', ''],
    ['state', 'US', 'United States', 'GA', 'Georgia', ''],
    ['state', 'US', 'United States', 'HI', 'Hawaii', ''],
    ['state', 'US', 'United States', 'ID', 'Idaho', ''],
    ['state', 'US', 'United States', 'IL', 'Illinois', ''],
    ['state', 'US', 'United States', 'IN', 'Indiana', ''],
    ['state', 'US', 'United States', 'IA', 'Iowa', ''],
    ['state', 'US', 'United States', 'KS', 'Kansas', ''],
    ['state', 'US', 'United States', 'KY', 'Kentucky', ''],
    ['state', 'US', 'United States', 'LA', 'Louisiana', ''],
    ['state', 'US', 'United States', 'ME', 'Maine', ''],
    ['state', 'US', 'United States', 'MD', 'Maryland', ''],
    ['state', 'US', 'United States', 'MA', 'Massachusetts', ''],
    ['state', 'US', 'United States', 'MI', 'Michigan', ''],
    ['state', 'US', 'United States', 'MN', 'Minnesota', ''],
    ['state', 'US', 'United States', 'MS', 'Mississippi', ''],
    ['state', 'US', 'United States', 'MO', 'Missouri', ''],
    ['state', 'US', 'United States', 'MT', 'Montana', ''],
    ['state', 'US', 'United States', 'NE', 'Nebraska', ''],
    ['state', 'US', 'United States', 'NV', 'Nevada', ''],
    ['state', 'US', 'United States', 'NH', 'New Hampshire', ''],
    ['state', 'US', 'United States', 'NJ', 'New Jersey', ''],
    ['state', 'US', 'United States', 'NM', 'New Mexico', ''],
    ['state', 'US', 'United States', 'NY', 'New York', ''],
    ['state', 'US', 'United States', 'NC', 'North Carolina', ''],
    ['state', 'US', 'United States', 'ND', 'North Dakota', ''],
    ['state', 'US', 'United States', 'OH', 'Ohio', ''],
    ['state', 'US', 'United States', 'OK', 'Oklahoma', ''],
    ['state', 'US', 'United States', 'OR', 'Oregon', ''],
    ['state', 'US', 'United States', 'PA', 'Pennsylvania', ''],
    ['state', 'US', 'United States', 'RI', 'Rhode Island', ''],
    ['state', 'US', 'United States', 'SC', 'South Carolina', ''],
    ['state', 'US', 'United States', 'SD', 'South Dakota', ''],
    ['state', 'US', 'United States', 'TN', 'Tennessee', ''],
    ['state', 'US', 'United States', 'TX', 'Texas', ''],
    ['state', 'US', 'United States', 'UT', 'Utah', ''],
    ['state', 'US', 'United States', 'VT', 'Vermont', ''],
    ['state', 'US', 'United States', 'VA', 'Virginia', ''],
    ['state', 'US', 'United States', 'WA', 'Washington', ''],
    ['state', 'US', 'United States', 'WV', 'West Virginia', ''],
    ['state', 'US', 'United States', 'WI', 'Wisconsin', ''],
    ['state', 'US', 'United States', 'WY', 'Wyoming', ''],
  ];

  foreach ($defaults as $location) {
    vc_ow_insert_location_if_missing($location);
  }

  update_option('vc_ow_us_states_seeded', VC_OW_DB_VERSION);
}

function vc_ow_install_locations_table() {
  vc_ow_create_locations_table();
  vc_ow_cleanup_unsupported_locations();
  vc_ow_seed_countries_from_csv(VC_OW_PLUGIN_DIR . 'data/countries.csv');
  vc_ow_seed_states_from_csv(VC_OW_PLUGIN_DIR . 'data/states.csv');
  vc_ow_seed_default_us_states();
}

function vc_ow_maybe_install_locations_table() {
  $countries_csv_path = VC_OW_PLUGIN_DIR . 'data/countries.csv';
  $states_csv_path = VC_OW_PLUGIN_DIR . 'data/states.csv';
  $countries_csv_mtime = file_exists($countries_csv_path) ? (string) filemtime($countries_csv_path) : '';
  $states_csv_mtime = file_exists($states_csv_path) ? (string) filemtime($states_csv_path) : '';

  if (
    get_option('vc_ow_db_version') !== VC_OW_DB_VERSION
    || get_option('vc_ow_us_states_seeded') !== VC_OW_DB_VERSION
    || ($countries_csv_mtime !== '' && get_option('vc_ow_countries_csv_imported') !== $countries_csv_mtime)
    || ($states_csv_mtime !== '' && get_option('vc_ow_states_csv_imported') !== $states_csv_mtime)
  ) {
    vc_ow_install_locations_table();
  }
}

register_activation_hook(__FILE__, 'vc_ow_install_locations_table');
add_action('plugins_loaded', 'vc_ow_maybe_install_locations_table');

require_once VC_OW_PLUGIN_DIR . 'includes/class-vc-onboarding-wizard-pmpro.php';

new VC_Onboarding_Wizard_PMPro();
