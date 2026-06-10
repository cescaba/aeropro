/*
 * Onboarding validation controller.
 * Keeps each step CTA visually in sync with the required fields while leaving
 * the button clickable for mobile browsers and password managers.
 */
document.addEventListener("DOMContentLoaded", function () {

  // Bind every onboarding form rendered inside the shared onboarding card.
  document.querySelectorAll(".vc-onb-card form").forEach(function(form){

    // Each step uses one primary CTA; forms without it do not need JS handling.
    const button = form.querySelector(".button-primary");
    if(!button) return;

    // Recalculate visual readiness without disabling pointer interaction.
    const refreshState = function(){
      toggleButton(form, button);
    };

    // Initial state covers prefilled fields, browser restore and password managers.
    refreshState();

    // Input/change catches typing, radio selection and browser autofill changes.
    form.addEventListener("input", refreshState);
    form.addEventListener("change", refreshState);

    // Final guard: block invalid submissions and move focus to the first problem.
    form.addEventListener("submit", function(event){
      if(!isFormValid(form)){
        event.preventDefault();
        refreshState();
        focusFirstInvalidControl(form);
      }
    });

  });

});

// Step 1 requires only a non-empty email field; browser validation handles format.
function validateStep1(form){
  const email = form.querySelector("input[type='email']");
  return email && email.value.trim() !== "";
}

// Step 2 requires both password fields before the request can continue.
function validateStep2(form){
  const pass1 = form.querySelector("input[name='password']");
  const pass2 = form.querySelector("input[name='password_confirm']");
  return pass1 && pass1.value.trim() !== "" && pass2 && pass2.value.trim() !== "" ;
}

// Step 3 requires profile identity plus both choice groups.
function validateStep3(form){
  const firstName = form.querySelector("input[name='first_name']");
  const lastName = form.querySelector("input[name='last_name']");
  const certTrack = form.querySelector("input[name='cert_track']:checked");
  const userStage = form.querySelector("input[name='user_stage']:checked");

  return (
    firstName && firstName.value.trim() !== "" &&
    lastName && lastName.value.trim() !== "" &&
    certTrack &&
    userStage
  );
}

// Route validation by form id so templates can keep simple, readable markup.
function isFormValid(form){
  switch(form.id){
    case "step-1":
      return validateStep1(form);
    case "step-2":
      return validateStep2(form);
    case "step-3":
      return validateStep3(form);
    default:
      return true;
  }
}

// Put keyboard focus on the field or group that prevents submission.
function focusFirstInvalidControl(form){
  const firstInvalid = getFirstInvalidControl(form);

  if(firstInvalid){
    firstInvalid.focus();
  }
}

// Returns the first control that should receive focus for each step.
function getFirstInvalidControl(form){
  switch(form.id){
    case "step-1":
      return form.querySelector("input[type='email']");
    case "step-2": {
      const pass1 = form.querySelector("input[name='password']");
      const pass2 = form.querySelector("input[name='password_confirm']");

      if(pass1 && pass1.value.trim() === ""){
        return pass1;
      }

      return pass2;
    }
    case "step-3": {
      const firstName = form.querySelector("input[name='first_name']");
      const lastName = form.querySelector("input[name='last_name']");

      if(firstName && firstName.value.trim() === ""){
        return firstName;
      }

      if(lastName && lastName.value.trim() === ""){
        return lastName;
      }

      return form.querySelector("input[name='cert_track'], input[name='user_stage']");
    }
    default:
      return form.querySelector("input:not([type='hidden']):not(:disabled), select:not(:disabled), textarea:not(:disabled)");
  }
}

// Visual disabled state only; the button stays enabled to avoid iOS/Safari dead taps.
function toggleButton(form, button){
  const isValid = isFormValid(form);

  button.style.opacity = isValid ? "1" : "0.4";
  button.setAttribute("aria-disabled", isValid ? "false" : "true");

  if(button.disabled){
    button.disabled = false;
  }
}
