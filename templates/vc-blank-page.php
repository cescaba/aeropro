<?php
if (!defined('ABSPATH')) exit;

$assets_url = plugin_dir_url(__FILE__) . 'assets/';

$left_bg = $assets_url . 'onboarding-left.png';
$logo    = $assets_url . 'aeropro-logo.svg';
$logo2	 = $assets_url . 'aeropro-logo2.svg';
$left_title = 'A better way to prepare for the A&amp;P written exam.';
$left_subtitle = '';

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="<?php echo esc_url($assets_url . 'onboarding.css'); ?>" />
  <?php wp_head(); ?>
</head>

<body <?php body_class('vc-onboarding'); ?>>
  <div class="vc-onb-wrap">

    <aside class="vc-onb-left" style="background-image:url('<?php echo esc_url($left_bg); ?>')">
      <div class="vc-onb-left-content">
        <div>
          <h1 class="vc-onb-left-title"><?php echo $left_title; ?></h1>
          <?php if (!empty($left_subtitle)): ?>
            <p class="vc-onb-left-sub"><?php echo esc_html($left_subtitle); ?></p>
          <?php endif; ?>
        </div>
      </div>

      <div class="vc-onb-brand">
        <?php if (!empty($logo)): ?>
          <img src="<?php echo esc_url($logo); ?>" alt="Aeropro">
        <?php endif; ?>
      </div>
    </aside>

    <main class="vc-onb-right">
      <div class="vc-onb-card">
      	<?php if (!empty($logo2)): ?>
          <img class="logo2" src="<?php echo esc_url($logo2); ?>" alt="Aeropro">
        <?php endif; ?>
        <?php
          while (have_posts()) : the_post();
            the_content();
          endwhile;
        ?>
      </div>
    </main>

  </div>

  <?php wp_footer(); ?>
</body>
</html>
