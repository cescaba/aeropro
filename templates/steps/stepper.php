<?php
if (!defined('ABSPATH')) exit;

$steps = (isset($steps) && is_array($steps) && !empty($steps))
  ? $steps
  : [
      ['label' => 'Account'],
      ['label' => 'Profile'],
      ['label' => 'Home'],
    ];

$current_step = isset($current_step) ? (int) $current_step : 0;
?>
<nav class="vc-stepper" aria-label="Wizard steps">
  <div class="vc-stepper__list">
    <?php foreach ($steps as $i => $step): ?>
      <?php
      $is_done = ($i < $current_step);
      $label = is_array($step) && isset($step['label']) ? (string) $step['label'] : (string) $step;
      ?>
      <div class="vc-stepper__item <?php echo $is_done ? 'is-done' : 'is-pending'; ?>">
        <div class="vc-stepper__circle" aria-hidden="true">
          <?php if ($is_done): ?>
            <svg class="vc-stepper__check" viewBox="0 0 24 24" role="img" focusable="false" aria-hidden="true">
              <path d="M20 6L9 17l-5-5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          <?php else: ?>
            <span class="vc-stepper__num"><?php echo (int) ($i + 1); ?></span>
          <?php endif; ?>
        </div>
        <span class="vc-stepper__label"><?php echo esc_html($label); ?></span>
      </div>
      <?php if ($i < count($steps) - 1): ?>
        <span class="vc-stepper__line is-filled" aria-hidden="true"></span>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</nav>
