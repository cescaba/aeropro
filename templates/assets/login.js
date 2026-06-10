/*
 * Login screen enhancement layer.
 * Normalizes PMPro markup into the Aeropro UI without changing the server-side
 * login, reset or password flows.
 */
document.addEventListener("DOMContentLoaded", function () {

  // Forgot-password form: relabel PMPro fields and keep CTA readiness visible.
  document.querySelectorAll(".vc-login-form-wrap--reset #lostpasswordform").forEach(function (resetForm) {
    const loginInput = resetForm.querySelector("input[name='user_login']");
    const resetWrap = resetForm.closest(".vc-login-form-wrap--reset");
    const submitButton = resetForm.querySelector("input[type='submit'], .pmpro_btn-submit");
    const loginField = loginInput ? loginInput.closest(".pmpro_form_field") : null;
    const loginLabel = loginField ? loginField.querySelector(".pmpro_form_label, label") : null;

    if (loginLabel) {
      loginLabel.textContent = "Email address";
    }

    if (loginInput) {
      loginInput.setAttribute("placeholder", "name@example.com");
    }

    if (submitButton) {
      submitButton.value = "Send recovery link";
      submitButton.textContent = "Send recovery link";
    }

    if (loginField && loginInput && !loginField.querySelector(".vc-login-reset__field-help")) {
      const fieldHelp = document.createElement("p");
      fieldHelp.className = "vc-login-reset__field-help";
      fieldHelp.textContent = "Enter the email associated with your account";
      loginInput.insertAdjacentElement("afterend", fieldHelp);
    }

    // Updates reset form visual state while keeping the submit button clickable.
    const refreshState = function () {
      if (!loginInput || !resetWrap) {
        return;
      }

      const isReady = loginInput.value.trim() !== "";
      resetWrap.classList.toggle("is-ready", isReady);

      if (submitButton) {
        submitButton.style.opacity = isReady ? "1" : "0.45";
        submitButton.setAttribute("aria-disabled", isReady ? "false" : "true");

        if (submitButton.disabled) {
          submitButton.disabled = false;
        }
      }
    };

    if (loginInput) {
      loginInput.addEventListener("input", refreshState);
      refreshState();
    }

    // Submission guard stores a temporary hint for the reset confirmation screen.
    resetForm.addEventListener("submit", function (event) {
      if (!loginInput) {
        return;
      }

      if (submitButton && submitButton.disabled) {
        event.preventDefault();
        refreshState();
        return;
      }

      if (loginInput.value.trim() === "") {
        event.preventDefault();
        refreshState();
        loginInput.focus();
        return;
      }

      const value = loginInput.value.trim();
      document.cookie = "vc_reset_login_hint=" + encodeURIComponent(value) + "; path=/; max-age=3600; SameSite=Lax";
    });
  });

  // Set-password form: move PMPro password toggle into the custom input shell.
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

  // Set-password readiness: show active CTA styling once both fields have values.
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

  // Login form: reshape PMPro markup into the Aeropro email/password layout.
  document.querySelectorAll(".vc-login-form-wrap .pmpro_login_wrap").forEach(function (loginWrap) {
    const usernameInput = loginWrap.querySelector(".login-username input, input[name='log'], input[name='user_login']");
    const passwordField = loginWrap.querySelector(".login-password");
    const passwordInput = passwordField ? passwordField.querySelector("input[type='password'], input[type='text']") : null;

    if (usernameInput) {
      usernameInput.setAttribute("placeholder", "Your email address");
    }

    if (passwordInput) {
      passwordInput.setAttribute("placeholder", "Your password");
    }

    if (!passwordField) {
      return;
    }

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

  /* Login submit guard: validates without blocking Safari/iOS autofill taps. */
  document.querySelectorAll(".vc-login-form-wrap #loginform").forEach(function (loginForm) {
    const usernameInput = loginForm.querySelector("input[name='log']");
    const passwordInput = loginForm.querySelector("input[name='pwd']");
    const submitButton = loginForm.querySelector("#wp-submit, input[type='submit'], .button-primary");

    if (!usernameInput || !passwordInput || !submitButton) {
      return;
    }

    const isLoginReady = function () {
      return usernameInput.value.trim() !== "" && passwordInput.value.trim() !== "";
    };

    // Updates CTA opacity and ARIA state, but never disables pointer interaction.
    const refreshLoginState = function () {
      const isReady = isLoginReady();
      submitButton.style.opacity = isReady ? "1" : "0.4";
      submitButton.setAttribute("aria-disabled", isReady ? "false" : "true");

      if (submitButton.disabled) {
        submitButton.disabled = false;
      }
    };

    usernameInput.addEventListener("input", refreshLoginState);
    passwordInput.addEventListener("input", refreshLoginState);
    usernameInput.addEventListener("change", refreshLoginState);
    passwordInput.addEventListener("change", refreshLoginState);
    usernameInput.addEventListener("blur", refreshLoginState);
    passwordInput.addEventListener("blur", refreshLoginState);
    usernameInput.addEventListener("focus", refreshLoginState);
    passwordInput.addEventListener("focus", refreshLoginState);

    /* Revalidates on submit in case the user presses Enter or uses autofill. */
    loginForm.addEventListener("submit", function (event) {
      refreshLoginState();

      if (!isLoginReady()) {
        event.preventDefault();
        (usernameInput.value.trim() === "" ? usernameInput : passwordInput).focus();
      }
    });

    refreshLoginState();
    window.setTimeout(refreshLoginState, 250);
    window.setTimeout(refreshLoginState, 1000);

    window.addEventListener("pageshow", refreshLoginState);
  });
});
