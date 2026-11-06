<?php
if (!defined('ABSPATH')) exit;

/* Profile email status class: solo muestra estado cuando existe verificacion o una solicitud pendiente. */
$email_status_class = 'vc-profile-email-status';
if ($pending_email !== '') {
  $email_status_class .= ' is-pending';
} elseif (!empty($email_verified)) {
  $email_status_class .= ' is-verified';
}

$phone_prefix = '';
$phone_number = trim((string) $phone);
if ($phone_number !== '' && preg_match('/^\s*(\+\d{1,4})\s*(.*)$/', $phone_number, $phone_matches)) {
  $phone_prefix = $phone_matches[1];
  $phone_number = trim($phone_matches[2]);
}
?>
<section class="vc-profile-shell">
  <?php /* Profile notices: muestra mensajes generados por el handler sin duplicar logica en el template. */ ?>
  <?php if (!empty($notice_html)): ?>
    <div class="vc-profile-notice-wrap">
      <?php echo $notice_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </div>
  <?php endif; ?>

  <?php /* Profile form: unifica guardado, verificacion de email, foto y delete account mediante profile_action. */ ?>
  <form class="vc-profile-card" method="post" action="<?php echo esc_url($action_url); ?>" enctype="multipart/form-data">
    <input type="hidden" name="action" value="vc_onboard_save_account_profile">
    <?php echo $nonce_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

    <?php /* Profile hero: avatar editable, nombre visible, membresia y controles de foto. */ ?>
    <div class="vc-profile-hero">
      <?php /* Profile mobile photo edit: el avatar tambien abre el selector de foto. */ ?>
      <label class="vc-profile-avatar-wrap" for="vc_profile_photo" data-vc-profile-avatar aria-label="<?php esc_attr_e('Edit profile photo', 'aeropro'); ?>">
        <?php if (!empty($avatar_url)): ?>
          <img class="vc-profile-avatar-image" src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($member_name); ?>">
        <?php else: ?>
          <span class="vc-profile-avatar-fallback"><?php echo esc_html($avatar_initials); ?></span>
        <?php endif; ?>
      </label>

      <div class="vc-profile-hero-copy">
        <h2 class="h2-profile"><?php echo esc_html($member_name); ?></h2>
        <?php /* Profile membership meta: separa la fecha para poder bajarla de linea solo en 480px. */ ?>
        <p>
          <?php echo esc_html($membership_label); ?><?php if (!empty($member_since)): ?>
            <span class="vc-profile-member-since-label">&middot; <?php esc_html_e('Member since', 'aeropro'); ?></span>
            <span class="vc-profile-member-since-date"><?php echo esc_html($member_since); ?></span>
          <?php endif; ?>
        </p>

        <?php /* Profile photo actions: desktop mantiene boton textual; mobile usa el avatar con icono. */ ?>
        <div class="vc-profile-photo-actions">
          <label class="vc-profile-photo-button" for="vc_profile_photo"><?php esc_html_e('Edit photo', 'aeropro'); ?></label>
          <?php if (!empty($show_delete_photo)): ?>
            <button type="submit" class="vc-profile-photo-delete" name="remove_avatar" value="1"><?php esc_html_e('Delete', 'aeropro'); ?></button>
          <?php endif; ?>
        </div>

        <input id="vc_profile_photo" type="file" name="profile_photo" accept="image/*" hidden>
      </div>
    </div>

    <div class="vc-profile-divider"></div>

    <div class="vc-profile-grid vc-profile-grid--two">
      <label class="vc-profile-field">
        <?php /* Profile required field: marca visible para la validacion custom de Profile. */ ?>
        <span class="vc-profile-field-label"><?php esc_html_e('Name', 'aeropro'); ?> <span class="vc-profile-required" aria-hidden="true">*</span></span>
        <input type="text" name="first_name" value="<?php echo esc_attr($first_name); ?>" placeholder="<?php esc_attr_e('First name', 'aeropro'); ?>" required aria-required="true">
      </label>

      <label class="vc-profile-field">
        <span class="vc-profile-field-label"><?php esc_html_e('Last name', 'aeropro'); ?></span>
        <input type="text" name="last_name" value="<?php echo esc_attr($last_name); ?>" placeholder="<?php esc_attr_e('Last name', 'aeropro'); ?>">
      </label>
    </div>

    <?php /* Profile email: Verify usa el mismo form, pero evita la validacion required con formnovalidate. */ ?>
    <div class="vc-profile-email-row">
      <label class="vc-profile-field vc-profile-field--email">
        <?php /* Profile required field: email obligatorio al guardar sin cambiar el flujo Verify. */ ?>
        <span class="vc-profile-field-label"><?php esc_html_e('E-mail', 'aeropro'); ?> <span class="vc-profile-required" aria-hidden="true">*</span></span>
        <input
          type="email"
          name="email"
          value="<?php echo esc_attr($email); ?>"
          data-current-email="<?php echo esc_attr($current_email); ?>"
          data-pending-email="<?php echo esc_attr($pending_email); ?>"
          required
          aria-required="true"
        >
      </label>

      <button
        type="submit"
        class="vc-profile-verify-button"
        name="profile_action"
        value="request_email_change"
        formnovalidate
        data-default-label="<?php echo esc_attr($pending_email !== '' ? __('Pending', 'aeropro') : (!empty($email_verified) ? __('Verified', 'aeropro') : __('Verify', 'aeropro'))); ?>"
        <?php echo ($pending_email === '' && strcasecmp($email, $current_email) === 0) ? 'disabled' : ''; ?>
      >
        <?php echo $pending_email !== '' ? esc_html__('Pending', 'aeropro') : (!empty($email_verified) ? esc_html__('Verified', 'aeropro') : esc_html__('Verify', 'aeropro')); ?>
      </button>
    </div>

    <?php /* Profile email status: refleja email actual, verificado o pendiente desde PHP. */ ?>
    <p class="<?php echo esc_attr($email_status_class); ?>">
      <span class="vc-profile-email-status-dot"></span>
      <?php
      if ($pending_email !== '') {
        esc_html_e('Email verification pending', 'aeropro');
      } elseif (!empty($email_verified)) {
        esc_html_e('Email verified', 'aeropro');
      } else {
        esc_html_e('Email verification pending', 'aeropro');
      }
      ?>
    </p>

    <?php /* Profile contact fields: phone opcional y ubicacion estructurada para Save Changes. */ ?>
    <div class="vc-profile-grid vc-profile-grid--two">
      <label class="vc-profile-field">
        <span class="vc-profile-field-label"><?php esc_html_e('Country', 'aeropro'); ?> <span class="vc-profile-required" aria-hidden="true">*</span></span>
        <select name="country_code" required aria-required="true" data-vc-profile-country>
          <option value=""><?php esc_html_e('Select country', 'aeropro'); ?></option>
          <?php foreach ((array) $countries as $country): ?>
            <?php
            $option_country_code = strtoupper((string) ($country['country_code'] ?? ''));
            $option_country_name = (string) ($country['country_name'] ?? '');
            if ($option_country_code === '' || $option_country_name === '') {
              continue;
            }
            ?>
            <option value="<?php echo esc_attr($option_country_code); ?>" <?php selected($country_code, $option_country_code); ?>>
              <?php echo esc_html($option_country_name); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label class="vc-profile-field">
        <span class="vc-profile-field-label"><?php esc_html_e('State', 'aeropro'); ?> <span class="vc-profile-required" aria-hidden="true">*</span></span>
        <select name="state_code" required aria-required="true" data-vc-profile-state>
          <option value=""><?php esc_html_e('Select state', 'aeropro'); ?></option>
          <?php foreach ((array) $states as $state): ?>
            <?php
            $option_state_country = strtoupper((string) ($state['country_code'] ?? ''));
            $option_state_code = strtoupper((string) ($state['state_code'] ?? ''));
            $option_state_name = (string) ($state['state_name'] ?? '');
            if ($option_state_country === '' || $option_state_code === '' || $option_state_name === '') {
              continue;
            }
            ?>
            <option
              value="<?php echo esc_attr($option_state_code); ?>"
              data-country="<?php echo esc_attr($option_state_country); ?>"
              <?php selected($state_code, $option_state_code); ?>
            >
              <?php echo esc_html($option_state_name); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>

    <div class="vc-profile-grid vc-profile-grid--two">
      <div class="vc-profile-field vc-profile-field--phone">
        <span class="vc-profile-field-label"><?php esc_html_e('Phone', 'aeropro'); ?></span>
        <div class="vc-profile-phone-inputs">
          <label class="vc-profile-phone-control">
            <span class="screen-reader-text"><?php esc_html_e('Phone prefix', 'aeropro'); ?></span>
            <input type="text" name="phone_prefix" value="<?php echo esc_attr($phone_prefix); ?>" placeholder="<?php esc_attr_e('+1', 'aeropro'); ?>" inputmode="tel" autocomplete="tel-country-code">
          </label>
          <label class="vc-profile-phone-control">
            <span class="screen-reader-text"><?php esc_html_e('Phone number', 'aeropro'); ?></span>
            <input type="text" name="phone_number" value="<?php echo esc_attr($phone_number); ?>" placeholder="<?php esc_attr_e('555 123 4567', 'aeropro'); ?>" inputmode="tel" autocomplete="tel-national">
          </label>
        </div>
        <input class="vc-profile-phone-mobile" type="text" name="phone_mobile" value="<?php echo esc_attr($phone); ?>" placeholder="<?php esc_attr_e('+1 555 123 4567', 'aeropro'); ?>" inputmode="tel" autocomplete="tel" disabled>
      </div>
    </div>

    <label class="vc-profile-field vc-profile-field--textarea">
      <span class="vc-profile-field-label"><?php esc_html_e('Bio', 'aeropro'); ?></span>
      <textarea name="bio" rows="7" maxlength="200" placeholder="<?php esc_attr_e('Tell us a bit about yourself', 'aeropro'); ?>"><?php echo esc_textarea($bio); ?></textarea>
      <small><?php esc_html_e('Maximum 200 characters', 'aeropro'); ?></small>
    </label>

    <?php /* Profile actions: desktop separa Delete account a la izquierda; mobile reordena desde CSS. */ ?>
    <div class="vc-profile-actions">
      <?php /* Profile delete account: accion destructiva al extremo izquierdo del footer. */ ?>
      <button
        type="submit"
        class="vc-profile-delete-account"
        name="profile_action"
        value="delete_account"
        formnovalidate
        data-vc-profile-delete-account
        data-confirm-message="<?php esc_attr_e('Are you sure you want to delete your account? This action cannot be undone.', 'aeropro'); ?>"
      >
        <?php esc_html_e('Delete account', 'aeropro'); ?>
      </button>

      <?php /* Profile actions main: acciones no destructivas agrupadas para responsive. */ ?>
      <div class="vc-profile-actions-main">
        <a class="vc-profile-cancel" href="<?php echo esc_url($cancel_url); ?>"><?php esc_html_e('Cancel', 'aeropro'); ?></a>
        <button type="submit" class="vc-profile-save" name="profile_action" value="save_profile"><?php esc_html_e('Save Changes', 'aeropro'); ?></button>
      </div>
      <div class="vc-profile-divider-action"></div>
    </div>
  </form>
</section>
