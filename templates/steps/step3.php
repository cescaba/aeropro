<?php
if (!defined('ABSPATH')) exit;
?>
<div class="vc-onboard">
  <?php if (!empty($stepper_html)): ?>
    <div class="vc-check-email__stepper">
      <?php echo $stepper_html; ?>
    </div>
  <?php endif; ?>

  <p class="h3">Tell us about yourself</p>
<p class="subtitle">We only need a few basic details</p>
  <form id="step-3" method="post" action="<?php echo esc_url($action_url); ?>">
    <input type="hidden" name="action" value="vc_onboard_save_profile" />
    <?php echo $nonce_html; ?>

    <p><label>First name *<br><input type="text" name="first_name" value="<?php echo esc_attr($first_name); ?>" required></label></p>
    <p><label>Last name *<br><input type="text" name="last_name" value="<?php echo esc_attr($last_name); ?>" required></label></p>

    <div class="vc-field">
      <label>Which certification are you pursuing? *</label>
      <div class="vc-choice-group" role="radiogroup" aria-label="Which certification are you pursuing?">
        <div class="vc-choice-option">
          <input class="vc-choice-input" type="radio" id="cert_track_airframe" name="cert_track" value="Airframe" <?php checked($cert_track, 'Airframe'); ?> required>
          <label class="vc-choice-chip" for="cert_track_airframe">Airframe</label>
        </div>
        <div class="vc-choice-option">
          <input class="vc-choice-input" type="radio" id="cert_track_powerplant" name="cert_track" value="Powerplant" <?php checked($cert_track, 'Powerplant'); ?> required>
          <label class="vc-choice-chip" for="cert_track_powerplant">Powerplant</label>
        </div>
        <div class="vc-choice-option">
          <input class="vc-choice-input" type="radio" id="cert_track_both" name="cert_track" value="Both (A&amp;P)" <?php checked($cert_track, 'Both (A&P)'); ?> required>
          <label class="vc-choice-chip" for="cert_track_both">Both (A&amp;P)</label>
        </div>
      </div>
    </div>

    <p>
      <label>What best describes you? *<br>
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
