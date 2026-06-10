<?php
if (!defined('ABSPATH')) exit;

$steps = (isset($steps) && is_array($steps) && !empty($steps))
  ? $steps
  : [
      ['label' => 'Account'],
      ['label' => 'Profile'],
    ];

$current_step = isset($current_step) ? (int) $current_step : 0;
?>
<nav class="vc-stepper" aria-label="Wizard steps">
  <div class="vc-stepper__list">
    <?php foreach ($steps as $i => $step): ?>
      <?php
      $is_done = ($i < $current_step);
      $is_current = ($i === $current_step);
      $label = is_array($step) && isset($step['label']) ? (string) $step['label'] : (string) $step;
      ?>
      <div class="vc-stepper__item <?php echo $is_done ? 'is-done' : ($is_current ? 'is-current' : 'is-pending'); ?>">
        <div class="vc-stepper__circle" aria-hidden="true">
          <span class="vc-stepper__num"><?php echo (int) ($i + 1); ?></span>
        </div>
        <span class="vc-stepper__label"><?php echo esc_html($label); ?></span>
      </div>
      <?php if ($i < count($steps) - 1): ?>
        <span class="vc-stepper__line <?php echo $i < $current_step ? 'is-filled' : 'is-empty'; ?>" aria-hidden="true"></span>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</nav>
