document.addEventListener("DOMContentLoaded", function () {
    const registerForm = document.getElementById("registerForm");

    const registerUsername = document.getElementById("registerUsername");
    const registerEmail = document.getElementById("registerEmail");
    const registerPassword = document.getElementById("registerPassword");
    const registerConfirmPassword = document.getElementById("registerConfirmPassword");

    const registerSuccessModal = document.getElementById("registerSuccessModal");
    const backToLoginRegister = document.getElementById("backToLoginRegister");

    function shakeInput(input) {
        input.classList.remove("error");

        void input.offsetWidth;

        input.classList.add("error");
    }

    function clearRegisterErrors() {
        registerUsername.classList.remove("error");
        registerEmail.classList.remove("error");
        registerPassword.classList.remove("error");
        registerConfirmPassword.classList.remove("error");
    }

    if (registerForm) {
        registerForm.addEventListener("submit", function (event) {
            event.preventDefault();

            let hasError = false;

            clearRegisterErrors();

            if (registerUsername.value.trim() === "") {
                shakeInput(registerUsername);
                hasError = true;
            }

            if (registerEmail.value.trim() === "") {
                shakeInput(registerEmail);
                hasError = true;
            }

            if (registerPassword.value.trim() === "") {
                shakeInput(registerPassword);
                hasError = true;
            }

            if (registerConfirmPassword.value.trim() === "") {
                shakeInput(registerConfirmPassword);
                hasError = true;
            }

            if (hasError) {
                return;
            }

            if (registerPassword.value !== registerConfirmPassword.value) {
                shakeInput(registerPassword);
                shakeInput(registerConfirmPassword);

                registerPassword.value = "";
                registerConfirmPassword.value = "";

                registerPassword.focus();
                return;
            }

            registerForm.submit();
        });
    }

    registerUsername.addEventListener("input", function () {
        registerUsername.classList.remove("error");
    });

    registerEmail.addEventListener("input", function () {
        registerEmail.classList.remove("error");
    });

    registerPassword.addEventListener("input", function () {
        registerPassword.classList.remove("error");
    });

    registerConfirmPassword.addEventListener("input", function () {
        registerConfirmPassword.classList.remove("error");
    });

    if (backToLoginRegister) {
        backToLoginRegister.addEventListener("click", function () {
            window.location.href = "login.php";
        });
    }
});