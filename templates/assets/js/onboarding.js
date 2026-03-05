document.addEventListener("DOMContentLoaded", function () {

  document.querySelectorAll(".vc-onb-card form").forEach(function(form){

    const button = form.querySelector(".button-primary");
    if(!button) return;

    const refreshState = function(){
      toggleButton(form, button);
    };

    // estado inicial
    refreshState();

    // cada cambio dentro del form
    form.addEventListener("input", refreshState);
    form.addEventListener("change", refreshState);

    form.addEventListener("submit", function(event){
      if(!isFormValid(form)){
        event.preventDefault();
        refreshState();
      }
    });

  });

});
function validateStep1(form){
  const email = form.querySelector("input[type='email']");
  return email && email.value.trim() !== "";
}

function validateStep2(form){
  const pass1 = form.querySelector("input[name='password']");
  const pass2 = form.querySelector("input[name='password_confirm']");
  return pass1 && pass1.value.trim() !== "" && pass2 && pass2.value.trim() !== "" ;
}

function validateStep3(form){
  const firstName = form.querySelector("input[name='first_name']");
  const lastName = form.querySelector("input[name='last_name']");
  const certTrack = form.querySelector("input[name='cert_track']:checked");
  const userStage = form.querySelector("select[name='user_stage']");

  return (
    firstName && firstName.value.trim() !== "" &&
    lastName && lastName.value.trim() !== "" &&
    certTrack &&
    userStage && userStage.value.trim() !== ""
  );
}

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

function toggleButton(form, button){
  const isValid = isFormValid(form);

  button.style.opacity = isValid ? "1" : "0.4";
  button.style.pointerEvents = isValid ? "auto" : "none";
  button.disabled = !isValid;
  button.setAttribute("aria-disabled", isValid ? "false" : "true");
}
