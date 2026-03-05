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

require_once VC_OW_PLUGIN_DIR . 'includes/class-vc-onboarding-wizard-pmpro.php';

new VC_Onboarding_Wizard_PMPro();
