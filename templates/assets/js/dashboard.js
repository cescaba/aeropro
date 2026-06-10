(function () {
  function initDashboardMenu() {
    var sidebar = document.querySelector('.vc-dashboard-sidebar');
    var toggle = document.querySelector('[data-vc-dashboard-menu-toggle]');
    var closeToggle = document.querySelector('[data-vc-dashboard-menu-close]');
    var searchToggle = document.querySelector('[data-vc-dashboard-search-toggle]');
    var searchInput = document.querySelector('.vc-dashboard-brand-search input, .vc-dashboard-search input');

    if (!sidebar || !toggle) {
      return;
    }

    function setSearchState(isOpen) {
      if (!searchToggle || !searchInput) {
        return;
      }

      document.body.classList.toggle('is-dashboard-search-open', isOpen);
      searchToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

      if (isOpen) {
        searchInput.focus();
        return;
      }

      if (document.activeElement === searchInput) {
        searchInput.blur();
      }
    }

    function setMenuState(isOpen) {
      sidebar.classList.toggle('is-menu-open', isOpen);
      document.body.classList.toggle('is-dashboard-menu-open', isOpen);
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      toggle.setAttribute('aria-label', isOpen ? 'Close dashboard menu' : 'Open dashboard menu');

      if (isOpen) {
        setSearchState(false);
      }
    }

    // Menu hamburguesa: abre/cierra solo la navegacion responsive de la sidebar.
    toggle.addEventListener('click', function () {
      setMenuState(!sidebar.classList.contains('is-menu-open'));
    });

    // Mobile menu close: secondary close control inside the off-canvas header.
    if (closeToggle) {
      closeToggle.addEventListener('click', function () {
        setMenuState(false);
      });
    }

    // Busqueda responsive: expande/contrae el buscador que vive dentro del brand.
    if (searchToggle && searchInput) {
      searchToggle.addEventListener('click', function (event) {
        if (!window.matchMedia('(max-width: 960px)').matches) {
          return;
        }

        event.preventDefault();
        setMenuState(false);
        setSearchState(!document.body.classList.contains('is-dashboard-search-open'));
      });
    }

    Array.prototype.forEach.call(sidebar.querySelectorAll('.vc-dashboard-nav-link, .vc-dashboard-logout'), function (link) {
      link.addEventListener('click', function () {
        if (window.matchMedia('(max-width: 960px)').matches) {
          setMenuState(false);
        }
      });
    });

    // Menu hamburguesa: cierra el off-canvas al tocar el overlay/fondo.
    document.addEventListener('click', function (event) {
      if (!window.matchMedia('(max-width: 960px)').matches || !sidebar.classList.contains('is-menu-open')) {
        return;
      }

      if (!sidebar.contains(event.target)) {
        setMenuState(false);
      }
    });

    // Menu hamburguesa: cierra el off-canvas con Escape para comportamiento tipo app.
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && sidebar.classList.contains('is-menu-open')) {
        setMenuState(false);
        return;
      }

      if (event.key === 'Escape' && document.body.classList.contains('is-dashboard-search-open')) {
        setSearchState(false);
      }
    });

    window.addEventListener('resize', function () {
      if (!window.matchMedia('(max-width: 960px)').matches) {
        setMenuState(false);
        setSearchState(false);
      }
    });
  }

  function initProfileForm() {
    var form = document.querySelector('.vc-dashboard-panel--profile .vc-profile-card');
    if (!form) {
      return;
    }

    // Profile form controls: conecta Verify, delete account y preview de avatar sin tocar otros panels.
    var emailInput = form.querySelector('input[name="email"]');
    var verifyButton = form.querySelector('.vc-profile-verify-button');
    var saveButton = form.querySelector('.vc-profile-save');
    var deleteAccountButton = form.querySelector('[data-vc-profile-delete-account]');
    var fileInput = form.querySelector('input[name="profile_photo"]');
    var profileAvatar = form.querySelector('[data-vc-profile-avatar]');
    var countrySelect = form.querySelector('[data-vc-profile-country]');
    var stateSelect = form.querySelector('[data-vc-profile-state]');
    var phonePrefixInput = form.querySelector('input[name="phone_prefix"]');
    var phoneNumberInput = form.querySelector('input[name="phone_number"]');
    var phoneMobileInput = form.querySelector('input[name="phone_mobile"]');
    var requiredFields = Array.prototype.slice.call(form.querySelectorAll('input[name="first_name"], input[name="email"], select[name="country_code"], select[name="state_code"]'));
    // Menu hamburguesa: incluye el avatar mobile del brand y el avatar desktop del topbar.
    var headerAvatars = document.querySelectorAll('[data-vc-dashboard-avatar]');

    if (!emailInput || !verifyButton) {
      return;
    }

    var currentEmail = String(emailInput.dataset.currentEmail || '').trim().toLowerCase();
    var pendingEmail = String(emailInput.dataset.pendingEmail || '').trim().toLowerCase();
    var defaultLabel = String(verifyButton.dataset.defaultLabel || 'Verify');
    var initialValues = {};
    var activeProfileAction = '';

    form.noValidate = true;

    Array.prototype.forEach.call(form.elements, function (control) {
      if (!control.name || control.type === 'file' || control.type === 'submit' || control.type === 'button' || control.type === 'hidden') {
        return;
      }

      initialValues[control.name] = control.value;
    });

    function isValidEmail(value) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    }

    function syncResponsivePhoneControls() {
      if (!phonePrefixInput || !phoneNumberInput || !phoneMobileInput) {
        return;
      }

      phonePrefixInput.disabled = false;
      phoneNumberInput.disabled = false;
      phoneMobileInput.disabled = true;
    }

    function normalizePhonePrefix() {
      if (!phonePrefixInput) {
        return;
      }

      var digits = String(phonePrefixInput.value || '').replace(/\D/g, '');
      phonePrefixInput.value = digits === '' ? '' : '+' + digits;
    }

    function getFieldMessage(input) {
      var field = input.closest('.vc-profile-field');
      var message = field ? field.querySelector('.vc-profile-field-error') : null;

      if (!field) {
        return null;
      }

      if (!message) {
        message = document.createElement('span');
        message.className = 'vc-profile-field-error';
        message.textContent = '*Complete the fields*';
        message.setAttribute('aria-live', 'polite');
        field.appendChild(message);
      }

      return message;
    }

    function setFieldError(input, hasError) {
      var message = getFieldMessage(input);
      var field = input.closest('.vc-profile-field');
      var emailRow = field ? field.closest('.vc-profile-email-row') : null;

      input.classList.toggle('is-profile-field-invalid', hasError);
      input.setAttribute('aria-invalid', hasError ? 'true' : 'false');

      if (field) {
        field.classList.toggle('has-profile-field-error', hasError);
      }

      if (emailRow) {
        emailRow.classList.toggle('has-profile-field-error', hasError);
      }

      if (message) {
        message.classList.toggle('is-visible', hasError);
      }
    }

    function validateRequiredFields() {
      var firstInvalid = null;

      requiredFields.forEach(function (input) {
        if (input.disabled || input.required === false) {
          setFieldError(input, false);
          return;
        }

        var value = String(input.value || '').trim();
        var hasError = value === '' || (input.type === 'email' && !isValidEmail(value));

        setFieldError(input, hasError);

        if (hasError && !firstInvalid) {
          firstInvalid = input;
        }
      });

      if (firstInvalid) {
        firstInvalid.focus();
        return false;
      }

      return true;
    }

    function syncStateOptions() {
      if (!countrySelect || !stateSelect) {
        return;
      }

      var countryCode = String(countrySelect.value || '').trim().toUpperCase();
      var hasVisibleStates = false;
      var selectedStateIsVisible = false;

      Array.prototype.forEach.call(stateSelect.options, function (option) {
        var optionCountry = String(option.dataset.country || '').trim().toUpperCase();
        var isPlaceholder = option.value === '';
        var isVisible = isPlaceholder || (countryCode !== '' && optionCountry === countryCode);

        option.hidden = !isVisible;
        option.disabled = !isVisible;

        if (!isPlaceholder && isVisible) {
          hasVisibleStates = true;
        }

        if (option.selected && isVisible) {
          selectedStateIsVisible = true;
        }
      });

      if (!selectedStateIsVisible) {
        stateSelect.value = '';
      }

      stateSelect.disabled = countryCode === '' || !hasVisibleStates;
      stateSelect.required = hasVisibleStates;
    }

    function restoreInitialProfileValues() {
      Object.keys(initialValues).forEach(function (name) {
        var control = form.elements[name];
        if (!control || control.type === 'file' || control.type === 'submit' || control.type === 'button' || control.type === 'hidden') {
          return;
        }

        control.value = initialValues[name];
      });

      requiredFields.forEach(function (input) {
        setFieldError(input, false);
      });

      syncStateOptions();
      syncVerifyButton();
    }

    // Profile Verify button: solo se habilita cuando el email nuevo es valido y diferente.
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
    syncResponsivePhoneControls();

    if (phonePrefixInput) {
      phonePrefixInput.addEventListener('input', normalizePhonePrefix);
      phonePrefixInput.addEventListener('blur', normalizePhonePrefix);
      normalizePhonePrefix();
    }

    requiredFields.forEach(function (input) {
      setFieldError(input, false);

      input.addEventListener('input', function () {
        if (!input.classList.contains('is-profile-field-invalid')) {
          return;
        }

        var value = String(input.value || '').trim();
        setFieldError(input, value === '' || (input.type === 'email' && !isValidEmail(value)));
      });

      input.addEventListener('change', function () {
        if (input === countrySelect) {
          syncStateOptions();
        }

        if (!input.classList.contains('is-profile-field-invalid')) {
          return;
        }

        var value = String(input.value || '').trim();
        setFieldError(input, value === '' || (input.type === 'email' && !isValidEmail(value)));
      });
    });

    if (countrySelect) {
      countrySelect.addEventListener('change', syncStateOptions);
      syncStateOptions();
    }

    if (saveButton) {
      saveButton.addEventListener('click', function (event) {
        activeProfileAction = 'save_profile';
        if (!validateRequiredFields()) {
          event.preventDefault();
        }
      });
    }

    verifyButton.addEventListener('click', function () {
      activeProfileAction = 'request_email_change';
    });

    form.addEventListener('submit', function (event) {
      syncResponsivePhoneControls();
      normalizePhonePrefix();

      var submitter = event.submitter;
      var profileAction = submitter && submitter.name === 'profile_action' ? submitter.value : activeProfileAction;

      if (profileAction !== '' && profileAction !== 'save_profile') {
        return;
      }

      if (!validateRequiredFields()) {
        event.preventDefault();
      }
    });

    Array.prototype.forEach.call(document.querySelectorAll('.vc-dashboard-nav-link'), function (link) {
      link.addEventListener('click', function () {
        if (link.classList.contains('is-active')) {
          return;
        }

        restoreInitialProfileValues();
      });
    });

    // Profile delete account: confirma la accion destructiva antes de enviar el formulario.
    if (deleteAccountButton) {
      deleteAccountButton.addEventListener('click', function (event) {
        activeProfileAction = 'delete_account';
        var message = String(deleteAccountButton.dataset.confirmMessage || 'Delete account?');
        if (!window.confirm(message)) {
          event.preventDefault();
        }
      });
    }

    // Profile avatar preview: actualiza el avatar del card y los avatares del header sin subir aun.
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
        Array.prototype.forEach.call(headerAvatars, function (headerAvatar) {
          applyAvatarPreview(headerAvatar, objectUrl, 'vc-dashboard-avatar-image');
        });
      });
    }
  }

  function initProfileNoticeCleanup() {
    if (!window.history || !window.history.replaceState) {
      return;
    }

    var currentUrl = new URL(window.location.href);
    if (currentUrl.searchParams.get('profile') !== 'saved') {
      return;
    }

    // Profile notice cleanup: muestra el success una vez y evita que reaparezca al recargar.
    currentUrl.searchParams.delete('profile');
    window.history.replaceState({}, document.title, currentUrl.pathname + currentUrl.search + currentUrl.hash);
  }

  document.addEventListener('DOMContentLoaded', initDashboardMenu);
  document.addEventListener('DOMContentLoaded', initProfileForm);
  document.addEventListener('DOMContentLoaded', initProfileNoticeCleanup);
}());
