<?php
if (!defined('ABSPATH')) exit;
?>
<section class="vc-profile-step" aria-labelledby="vc-profile-step-title">
  <header class="vc-profile-step__header">
    <p id="vc-profile-step-title" class="h3 vc-profile-step__title">Tell us about yourself</p>
    <p class="subtitle vc-profile-step__subtitle">We only need a few basic details</p>
  </header>

  <form id="step-3" class="vc-profile-step__form" method="post" action="<?php echo esc_url($action_url); ?>">
    <input type="hidden" name="action" value="vc_onboard_save_profile" />
    <?php echo $nonce_html; ?>

    <p class="vc-profile-field vc-profile-field--first-name">
      <label class="vc-profile-field__label" for="vc_first_name">First name *</label>
      <input class="vc-profile-field__control" type="text" id="vc_first_name" name="first_name" value="<?php echo esc_attr($first_name); ?>" placeholder="Your first name" required>
    </p>

    <p class="vc-profile-field vc-profile-field--last-name">
      <label class="vc-profile-field__label" for="vc_last_name">Last name *</label>
      <input class="vc-profile-field__control" type="text" id="vc_last_name" name="last_name" value="<?php echo esc_attr($last_name); ?>" placeholder="Your Last name" required>
    </p>

    <div class="vc-profile-field vc-profile-field--certification">
      <p class="vc-profile-field__label">Which certification are you pursuing? *</p>
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

    <div class="vc-profile-field vc-profile-field--stage">
      <p class="vc-profile-field__label">What best describes you? *</p>
      <div class="vc-choice-group" role="radiogroup" aria-label="What best describes you?">
        <div class="vc-choice-option">
          <input class="vc-choice-input" type="radio" id="user_stage_student" name="user_stage" value="Student" <?php checked($user_stage, 'Student'); ?> required>
          <label class="vc-choice-chip" for="user_stage_student">Student</label>
        </div>
        <div class="vc-choice-option">
          <input class="vc-choice-input" type="radio" id="user_stage_graduate" name="user_stage" value="Graduate" <?php checked($user_stage, 'Graduate'); ?> required>
          <label class="vc-choice-chip" for="user_stage_graduate">Graduate</label>
        </div>
        <div class="vc-choice-option">
          <input class="vc-choice-input" type="radio" id="user_stage_technician" name="user_stage" value="Technician" <?php checked($user_stage, 'Technician'); ?> required>
          <label class="vc-choice-chip" for="user_stage_technician">Technician</label>
        </div>
      </div>
    </div>

    <p class="vc-profile-step__actions">
      <button class="button button-primary" type="submit">Continue</button>
    </p>

    <p class="vc-profile-step__back-wrap">
      <a class="vc-profile-step__back" href="<?php echo esc_url(home_url('/register/')); ?>">Back</a>
    </p>
  </form>
</section>
