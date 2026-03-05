document.addEventListener("DOMContentLoaded", function () {

  document.querySelectorAll(".vc-onb-card form").forEach(function(form){

    const button = form.querySelector(".button-primary");
    if(!button) return;

    // estado inicial
    toggleButton(form, button);

    // cada cambio dentro del form
    form.addEventListener("input", function(){
      toggleButton(form, button);
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

function toggleButton(form, button){
  let isValid = false;

  switch(form.id){
    case "step-1":
      isValid = validateStep1(form);
      break;

    case "step-2":
      isValid = validateStep2(form);
      break;
  }

  button.style.opacity = isValid ? "1" : "0.4";
  button.style.pointerEvents = isValid ? "auto" : "none";
}
