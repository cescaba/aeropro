<?php
if (!defined('ABSPATH')) exit;
?>
<p class="h3">Start your preparation</p>
<p class="subtitle">Join hundreds of students preparing for the A&amp;P exam</p>

<a class="button button-google" href="<?php echo esc_url($google_login_url); ?>">
  <img src="<?php echo esc_url($google_logo_url); ?>" alt="Aeropro">
  Continue with Google
</a>

<div class="divider"><span>o</span></div>

<form id="step-1" method="get" action="<?php echo esc_url($action_email_url); ?>">
  <p><input type="email" name="email" placeholder="Your email address" required></p>
  <p><button class="button button-primary" type="submit">Create account</button></p>
</form>

<p class="mensaje">By creating an account, you agree to our Terms of Service and Privacy Policy</p>
<p class="mensaje-bottom">Already have an account?<a href="<?php echo esc_url(home_url('/login/')); ?>">Log in</a></p>

<?php echo $notices_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
