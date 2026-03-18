(function () {
  function initProfileForm() {
    var form = document.querySelector('.vc-profile-card');
    if (!form) {
      return;
    }

    var emailInput = form.querySelector('input[name="email"]');
    var verifyButton = form.querySelector('.vc-profile-verify-button');
    var fileInput = form.querySelector('input[name="profile_photo"]');
    var profileAvatar = form.querySelector('[data-vc-profile-avatar]');
    var headerAvatar = document.querySelector('[data-vc-dashboard-avatar]');

    if (!emailInput || !verifyButton) {
      return;
    }

    var currentEmail = String(emailInput.dataset.currentEmail || '').trim().toLowerCase();
    var pendingEmail = String(emailInput.dataset.pendingEmail || '').trim().toLowerCase();
    var defaultLabel = String(verifyButton.dataset.defaultLabel || 'Verify');

    function isValidEmail(value) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    }

    function syncVerifyButton() {
      var nextValue = String(emailInput.value || '').trim().toLowerCase();

      if (pendingEmail !== '' && nextValue === pendingEmail) {
        verifyButton.disabled = true;
        verifyButton.textContent = 'Pending';
        return;
      }

      if (nextValue !== '' && nextValue !== currentEmail && isValidEmail(nextValue)) {
        verifyButton.disabled = false;
        verifyButton.textContent = 'Verify';
        return;
      }

      verifyButton.disabled = true;
      verifyButton.textContent = defaultLabel;
    }

    emailInput.addEventListener('input', syncVerifyButton);
    emailInput.addEventListener('change', syncVerifyButton);
    syncVerifyButton();

    function applyAvatarPreview(target, source, fallbackClassName) {
      if (!target) {
        return;
      }

      target.innerHTML = '';

      var image = document.createElement('img');
      image.src = source;
      image.alt = 'Profile photo';
      image.className = fallbackClassName;
      target.appendChild(image);
    }

    if (fileInput) {
      fileInput.addEventListener('change', function () {
        var file = fileInput.files && fileInput.files[0];
        if (!file) {
          return;
        }

        if (!file.type || file.type.indexOf('image/') !== 0) {
          return;
        }

        var objectUrl = window.URL.createObjectURL(file);
        applyAvatarPreview(profileAvatar, objectUrl, 'vc-profile-avatar-image');
        applyAvatarPreview(headerAvatar, objectUrl, 'vc-dashboard-avatar-image');
      });
    }
  }

  document.addEventListener('DOMContentLoaded', initProfileForm);
}());
