<?php
if (!defined('ABSPATH')) exit;
?>
<p class="h3">Set up your password</p>
<p class="subtitle">Create a secure password for your account</p>

<form id="step-2" method="post" action="<?php echo esc_url($action_url); ?>">
  <input type="hidden" name="action" value="vc_onboard_email_start" />
  <?php echo $nonce_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

  <input type="hidden" name="email" value="<?php echo esc_attr($prefill_email); ?>" />

  <p style="text-align: left;"><label>Password*</label><br><input type="password" placeholder="Your password" name="password" minlength="8" required></p>
  <p style="text-align: left;" class="last"><label>Confirm password<br></label><input type="password" placeholder="Confirm password" name="password_confirm" minlength="8" required></p>

  <p><button class="button button-primary" type="submit">Continue</button></p>
</form>

<?php echo $error_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
