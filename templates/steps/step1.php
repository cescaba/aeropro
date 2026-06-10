<?php
if (!defined('ABSPATH')) exit;
?>
<section class="vc-start-step" aria-labelledby="vc-start-step-title">
  <header class="vc-start-step__header">
    <p id="vc-start-step-title" class="h3 vc-start-step__title">Start your preparation</p>
    <p class="subtitle vc-start-step__subtitle">Join hundreds of students preparing for the A&amp;P exam</p>
  </header>

  <div class="vc-start-step__social">
    <a class="button button-google vc-start-step__google" href="<?php echo esc_url($google_login_url); ?>">
      <img class="vc-start-step__google-icon" src="<?php echo esc_url($google_logo_url); ?>" alt="">
      <span class="vc-start-step__google-label">Continue with Google</span>
    </a>
  </div>

  <div class="divider vc-start-step__divider" aria-hidden="true">
    <span>or</span>
  </div>

  <form id="step-1" class="vc-start-step__form" method="get" action="<?php echo esc_url($action_email_url); ?>">
    <p class="vc-start-step__field">
      <input class="vc-start-step__input" type="email" name="email" placeholder="Your email address" required>
    </p>

    <p class="vc-start-step__actions">
      <button class="button button-primary vc-start-step__submit" type="submit">Create account</button>
    </p>
  </form>

  <footer class="vc-start-step__footer">
    <p class="mensaje vc-start-step__legal">
      By creating an account, you agree to our <a href="<?php echo esc_url(home_url('/terms-of-service/')); ?>">Terms of Service</a><br class="mensaje-break"> and <a href="<?php echo esc_url(get_privacy_policy_url() ?: home_url('/privacy-policy/')); ?>">Privacy Policy</a>
    </p>
    <p class="mensaje-bottom vc-start-step__login">Already have an account?<a href="<?php echo esc_url(home_url('/login/')); ?>">Log in</a></p>
  </footer>

  <?php if (!empty($notices_html)): ?>
    <div class="vc-start-step__notices">
      <?php echo $notices_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </div>
  <?php endif; ?>
</section>
