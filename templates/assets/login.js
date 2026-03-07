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
      let actionsWrap = passwordField.querySelector(".vc-login-password-actions");
      if (!actionsWrap) {
        actionsWrap = document.createElement("div");
        actionsWrap.className = "vc-login-password-actions";
        passwordField.appendChild(actionsWrap);
      }

      if (lostPasswordLink.parentNode !== actionsWrap) {
        actionsWrap.appendChild(lostPasswordLink);
      }

      const cardActions = loginWrap.querySelector(".pmpro_card_actions");
      if (cardActions && !cardActions.querySelector("a")) {
        cardActions.style.display = "none";
      }
    }
  });
});
