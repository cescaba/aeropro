<?php
if (!defined('ABSPATH')) exit;
?>
<div class="vc-final-step">
  <div class="vc-final-step__badge" aria-hidden="true">
    <svg viewBox="0 0 24 24" role="img" focusable="false" aria-hidden="true">
      <path d="M20 6L9 17l-5-5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
  </div>

  <p class="vc-final-step__title">Welcome to Aeropro!</p>
  <p class="subtitle">These are the tools that will help you pass your A&amp;P exam</p>

  <div class="vc-final-step__features" aria-label="Included tools">
    <article class="vc-final-step__feature">
      <div class="vc-final-step__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" role="img" focusable="false" aria-hidden="true">
          <path d="M4 6.5A2.5 2.5 0 0 1 6.5 4H11v15H6.5A2.5 2.5 0 0 0 4 21V6.5Zm16 0A2.5 2.5 0 0 0 17.5 4H13v15h4.5A2.5 2.5 0 0 1 20 21V6.5Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
        </svg>
      </div>
      <div class="vc-final-step__feature-copy">
        <p class="h2">Flashcards by category</p>
        <p>Study with over 1,000 flashcards organized by General, Airframe, and Powerplant</p>
      </div>
    </article>

    <article class="vc-final-step__feature">
      <div class="vc-final-step__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" role="img" focusable="false" aria-hidden="true">
          <circle cx="12" cy="13" r="7" fill="none" stroke="currentColor" stroke-width="2"/>
          <path d="M12 13l3-3M12 6V3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <div class="vc-final-step__feature-copy">
        <h2>FAA Mock Test</h2>
        <p>Practice with 100-question exams in 120 minutes, just like the real exam</p>
      </div>
    </article>

    <article class="vc-final-step__feature">
      <div class="vc-final-step__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" role="img" focusable="false" aria-hidden="true">
          <path d="M5 19V9M12 19V5M19 19v-7M3 19h18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <div class="vc-final-step__feature-copy">
        <h2>Progress tracking</h2>
        <p>Track your progress in each category and identify areas for improvement</p>
      </div>
    </article>
  </div>

  <a class="button button-primary vc-final-step__cta" href="<?php echo esc_url($dashboard_url); ?>">Start studying</a>
</div>
