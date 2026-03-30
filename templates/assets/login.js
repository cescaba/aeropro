  document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".vc-login-form-wrap--reset #lostpasswordform").forEach(function (resetForm) {
    const loginInput = resetForm.querySelector("input[name='user_login']");
    const resetWrap = resetForm.closest(".vc-login-form-wrap--reset");
    const submitButton = resetForm.querySelector("input[type='submit'], .pmpro_btn-submit");

    const refreshState = function () {
      if (!loginInput || !resetWrap) {
        return;
      }

      const isReady = loginInput.value.trim() !== "";
      resetWrap.classList.toggle("is-ready", isReady);

      if (submitButton) {
        submitButton.style.opacity = isReady ? "1" : "0.45";
        submitButton.style.pointerEvents = isReady ? "auto" : "none";
        submitButton.disabled = !isReady;
        submitButton.setAttribute("aria-disabled", isReady ? "false" : "true");
      }
    };

    if (loginInput) {
      loginInput.addEventListener("input", refreshState);
      refreshState();
    }

    resetForm.addEventListener("submit", function (event) {
      if (!loginInput) {
        return;
      }

      if (submitButton && submitButton.disabled) {
        event.preventDefault();
        refreshState();
        return;
      }

      const value = loginInput.value.trim();
      document.cookie = "vc_reset_login_hint=" + encodeURIComponent(value) + "; path=/; max-age=3600; SameSite=Lax";
    });
  });

  document.querySelectorAll(".vc-login-form-wrap--set-password .pmpro_form_field").forEach(function (field) {
    const passwordInput = field.querySelector("input[type='password'], input[type='text']");
    const toggleWrapper = field.querySelector(".pmpro_form_field-password-toggle");

    if (!passwordInput || !toggleWrapper) {
      return;
    }

    const passwordLabel = toggleWrapper.querySelector("label");
    if (passwordLabel && passwordLabel.parentNode === toggleWrapper) {
      field.insertBefore(passwordLabel, passwordInput);
    }

    let inputWrap = field.querySelector(".vc-login-password-wrap");
    if (!inputWrap) {
      inputWrap = document.createElement("div");
      inputWrap.className = "vc-login-password-wrap";
      passwordInput.parentNode.insertBefore(inputWrap, passwordInput);
      inputWrap.appendChild(passwordInput);
    }

    if (toggleWrapper.parentNode !== inputWrap) {
      inputWrap.appendChild(toggleWrapper);
    }
  });

  document.querySelectorAll(".vc-login-form-wrap--set-password").forEach(function (setPasswordWrap) {
    const pass1 = setPasswordWrap.querySelector("input[name='pass1']");
    const pass2 = setPasswordWrap.querySelector("input[name='pass2']");

    if (!pass1 || !pass2) {
      return;
    }

    const refreshState = function () {
      const isReady = pass1.value.trim() !== "" && pass2.value.trim() !== "";
      setPasswordWrap.classList.toggle("is-ready", isReady);
    };

    pass1.addEventListener("input", refreshState);
    pass2.addEventListener("input", refreshState);
    refreshState();
  });

  document.querySelectorAll(".vc-login-form-wrap .pmpro_login_wrap").forEach(function (loginWrap) {
    const passwordField = loginWrap.querySelector(".login-password");
    if (!passwordField) {
      return;
    }

    const passwordInput = passwordField.querySelector("input[type='password'], input[type='text']");
    const toggleWrapper = passwordField.querySelector(".pmpro_form_field-password-toggle");
    const actionsNav = loginWrap.querySelector(".pmpro_actions_nav");
    const lostPasswordLink = actionsNav ? actionsNav.querySelector("a") : null;

    if (!passwordInput || !toggleWrapper) {
      return;
    }

    let inputWrap = passwordField.querySelector(".vc-login-password-wrap");
    if (!inputWrap) {
      inputWrap = document.createElement("div");
      inputWrap.className = "vc-login-password-wrap";
      passwordInput.parentNode.insertBefore(inputWrap, passwordInput);
      inputWrap.appendChild(passwordInput);
    }

    if (toggleWrapper.parentNode !== inputWrap) {
      inputWrap.appendChild(toggleWrapper);
    }

    if (lostPasswordLink) {
      const passwordLabel = passwordField.querySelector("label");
      let metaWrap = passwordField.querySelector(".vc-login-password-meta");

      if (!metaWrap) {
        metaWrap = document.createElement("div");
        metaWrap.className = "vc-login-password-meta";

        if (passwordLabel) {
          passwordField.insertBefore(metaWrap, passwordLabel);
          metaWrap.appendChild(passwordLabel);
        } else {
          passwordField.insertBefore(metaWrap, inputWrap);
        }
      }

      if (lostPasswordLink.parentNode !== metaWrap) {
        lostPasswordLink.classList.add("vc-login-password-link");
        metaWrap.appendChild(lostPasswordLink);
      }

      const cardActions = loginWrap.querySelector(".pmpro_card_actions");
      if (cardActions && !cardActions.querySelector("a")) {
        cardActions.style.display = "none";
      }
    }

    const rememberField = loginWrap.querySelector(".login-remember");
    if (rememberField) {
      rememberField.style.display = "none";
    }
  });

  /* Mantiene el CTA de login deshabilitado hasta que usuario y password tengan contenido. */
  document.querySelectorAll(".vc-login-form-wrap #loginform").forEach(function (loginForm) {
    const usernameInput = loginForm.querySelector("input[name='log']");
    const passwordInput = loginForm.querySelector("input[name='pwd']");
    const submitButton = loginForm.querySelector("#wp-submit, input[type='submit'], .button-primary");

    if (!usernameInput || !passwordInput || !submitButton) {
      return;
    }

    /* Replica el patron visual del onboarding para evitar submits vacios en el login. */
    const refreshLoginState = function () {
      const isReady = usernameInput.value.trim() !== "" && passwordInput.value.trim() !== "";

      submitButton.style.opacity = isReady ? "1" : "0.4";
      submitButton.style.pointerEvents = isReady ? "auto" : "none";
      submitButton.disabled = !isReady;
      submitButton.setAttribute("aria-disabled", isReady ? "false" : "true");
    };

    usernameInput.addEventListener("input", refreshLoginState);
    passwordInput.addEventListener("input", refreshLoginState);
    usernameInput.addEventListener("change", refreshLoginState);
    passwordInput.addEventListener("change", refreshLoginState);

    /* Revalida el estado al enviar por si el submit llega desde Enter o autofill. */
    loginForm.addEventListener("submit", function (event) {
      if (submitButton.disabled) {
        event.preventDefault();
        refreshLoginState();
      }
    });

    refreshLoginState();
  });
});
