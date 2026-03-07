document.addEventListener("DOMContentLoaded", function () {
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
});
