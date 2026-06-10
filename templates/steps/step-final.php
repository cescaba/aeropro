<?php
if (!defined('ABSPATH')) exit;

$features = [
  [
    'icon' => 'flashcards',
    'title' => 'Flashcards by category',
    'description' => 'Study with over 1,000 flashcards organized by General, Airframe, and Powerplant',
  ],
  [
    'icon' => 'mock-test',
    'title' => 'A&P Mock Test',
    'description' => 'Practice with 100-question exams in 120 minutes, just like the real exam',
  ],
  [
    'icon' => 'progress',
    'title' => 'Progress tracking',
    'description' => 'Track your progress in each category and identify areas for improvement',
  ],
];

$icons = [
  'flashcards' => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" focusable="false" aria-hidden="true"><path d="M12 7V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 18C2.73478 18 2.48043 17.8946 2.29289 17.7071C2.10536 17.5196 2 17.2652 2 17V4C2 3.73478 2.10536 3.48043 2.29289 3.29289C2.48043 3.10536 2.73478 3 3 3H8C9.06087 3 10.0783 3.42143 10.8284 4.17157C11.5786 4.92172 12 5.93913 12 7C12 5.93913 12.4214 4.92172 13.1716 4.17157C13.9217 3.42143 14.9391 3 16 3H21C21.2652 3 21.5196 3.10536 21.7071 3.29289C21.8946 3.48043 22 3.73478 22 4V17C22 17.2652 21.8946 17.5196 21.7071 17.7071C21.5196 17.8946 21.2652 18 21 18H15C14.2044 18 13.4413 18.3161 12.8787 18.8787C12.3161 19.4413 12 20.2044 12 21C12 20.2044 11.6839 19.4413 11.1213 18.8787C10.5587 18.3161 9.79565 18 9 18H3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  'mock-test' => '<svg viewBox="0 0 24 24" role="img" focusable="false" aria-hidden="true"><circle cx="12" cy="13" r="7" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 13l3-3M12 6V3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  'progress' => '<svg viewBox="0 0 24 24" role="img" focusable="false" aria-hidden="true"><path d="M5 19V9M12 19V5M19 19v-7M3 19h18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
];
?>
<section class="vc-final-step" aria-labelledby="vc-final-step-title">
  <header class="vc-final-step__header">
    <div class="vc-final-step__badge" aria-hidden="true">
      <svg viewBox="0 0 24 24" role="img" focusable="false" aria-hidden="true">
        <path d="M20 6L9 17l-5-5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>

    <p id="vc-final-step-title" class="h3 vc-final-step__title">Welcome to Aeropro!</p>
    <p class="vc-final-step__subtitle">These are the tools that will help you pass your A&amp;P exam</p>
  </header>

  <div class="vc-final-step__body">
    <ul class="vc-final-step__features" aria-label="Included tools">
      <?php foreach ($features as $feature): ?>
        <?php
        $icon_key = isset($feature['icon']) ? (string) $feature['icon'] : '';
        $icon_html = $icons[$icon_key] ?? '';
        ?>
        <li class="vc-final-step__feature">
          <span class="vc-final-step__feature-icon" aria-hidden="true">
            <?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
          </span>
          <span class="vc-final-step__feature-content">
            <span class="vc-final-step__feature-title"><?php echo esc_html(wp_strip_all_tags($feature['title'])); ?></span>
            <span class="vc-final-step__feature-description"><?php echo esc_html($feature['description']); ?></span>
          </span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <div class="vc-final-step__actions">
    <a class="button button-primary vc-final-step__cta" href="<?php echo esc_url($dashboard_url); ?>">Start studying</a>
  </div>
</section>
