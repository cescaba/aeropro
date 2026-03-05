<?php
if (!defined('ABSPATH')) exit;
?>
<div class="vc-onboard">
  <h2>Your details</h2>
  <form method="post" action="<?php echo esc_url($action_url); ?>">
    <input type="hidden" name="action" value="vc_onboard_save_profile" />
    <?php echo $nonce_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

    <p><label>First name<br><input type="text" name="first_name" value="<?php echo esc_attr($first_name); ?>" required></label></p>
    <p><label>Last name<br><input type="text" name="last_name" value="<?php echo esc_attr($last_name); ?>" required></label></p>

    <p>
      <label>Certificate track<br>
        <select name="cert_track" required>
          <option value="">Select…</option>
          <option value="Airframe" <?php selected($cert_track, 'Airframe'); ?>>Airframe</option>
          <option value="Powerplant" <?php selected($cert_track, 'Powerplant'); ?>>Powerplant</option>
          <option value="Both (A&amp;P)" <?php selected($cert_track, 'Both (A&P)'); ?>>Both (A&amp;P)</option>
        </select>
      </label>
    </p>

    <p>
      <label>I am a…<br>
        <select name="user_stage" required>
          <option value="">Select…</option>
          <option value="Student" <?php selected($user_stage, 'Student'); ?>>Student</option>
          <option value="Graduate" <?php selected($user_stage, 'Graduate'); ?>>Graduate</option>
          <option value="Technician" <?php selected($user_stage, 'Technician'); ?>>Technician</option>
        </select>
      </label>
    </p>

    <p><button class="button button-primary" type="submit">Continue</button></p>
  </form>
</div>
