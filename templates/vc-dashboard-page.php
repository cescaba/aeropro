<?php
if (!defined('ABSPATH')) exit;

$assets_url = plugin_dir_url(__FILE__) . 'assets/';
$assets_dir = plugin_dir_path(__FILE__) . 'assets/';
$dashboard_css = $assets_url . 'dashboard.css';
$dashboard_css_ver = file_exists($assets_dir . 'dashboard.css') ? (string) filemtime($assets_dir . 'dashboard.css') : null;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
  <link rel="stylesheet" href="<?php echo esc_url(add_query_arg('ver', $dashboard_css_ver, $dashboard_css)); ?>">
</head>
<body <?php body_class('vc-dashboard-page'); ?>>
  <?php wp_body_open(); ?>
  <?php echo do_shortcode('[vc_member_dashboard]'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
  <?php wp_footer(); ?>
</body>
</html>
