<?php
if (!defined('ABSPATH')) exit;
?>
<nav class="vc-stepper" aria-label="Wizard steps">
  <ol class="vc-stepper__list">
    <?php foreach ($steps as $i => $step): ?>
      <?php
      $state = 'is-upcoming';
      if ($i < $current_step) $state = 'is-done';
      if ($i === $current_step) $state = 'is-current';
      ?>
      <li class="vc-stepper__item <?php echo esc_attr($state); ?>">
        <div class="vc-stepper__circle" aria-hidden="true">
          <?php if ($state === 'is-done'): ?>
            <svg class="vc-stepper__check" viewBox="0 0 24 24" role="img" focusable="false" aria-hidden="true">
              <path d="M20 6L9 17l-5-5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          <?php else: ?>
            <span class="vc-stepper__num"><?php echo (int) ($i + 1); ?></span>
          <?php endif; ?>
        </div>

        <?php if ($i < count($steps) - 1): ?>
          <?php $connector_state = ($i < $current_step) ? 'is-filled' : 'is-empty'; ?>
          <span class="vc-stepper__line <?php echo esc_attr($connector_state); ?>" aria-hidden="true"></span>
        <?php endif; ?>

        <span class="vc-stepper__label"<?php echo ($state === 'is-current') ? ' aria-current="step"' : ''; ?>>
          <?php echo esc_html($step['label']); ?>
        </span>
      </li>
    <?php endforeach; ?>
  </ol>
</nav>
