document.addEventListener("DOMContentLoaded", function () {
    window.resetForgotModal = function () {
        const emailStep = document.getElementById("emailStep");
        const otpStep = document.getElementById("otpStep");
        const resetStep = document.getElementById("resetStep");
        const resetSuccessStep = document.getElementById("resetSuccessStep");

        const forgotEmail = document.getElementById("forgotEmail");
        const emailMessage = document.getElementById("emailMessage");
        const otpMessage = document.getElementById("otpMessage");
        const passwordMessage = document.getElementById("passwordMessage");
        const otpInputs = document.querySelectorAll(".otp-input");

        const newPassword = document.getElementById("newPassword");
        const confirmNewPassword = document.getElementById("confirmNewPassword");

        if (emailStep) {
            emailStep.style.display = "block";
        }

        if (otpStep) {
            otpStep.style.display = "none";
        }

        if (resetStep) {
            resetStep.style.display = "none";
        }

        if (resetSuccessStep) {
            resetSuccessStep.style.display = "none";
        }

        if (forgotEmail) {
            forgotEmail.value = "";
            forgotEmail.classList.remove("error");
        }

        otpInputs.forEach(function (input) {
            input.value = "";
            input.classList.remove("error");
        });

        [emailMessage, otpMessage, passwordMessage].forEach(function (messageElement) {
            if (messageElement) {
                messageElement.textContent = "";
                messageElement.classList.remove("error");
            }
        });

        if (newPassword) {
            newPassword.value = "";
            newPassword.type = "password";
            newPassword.classList.remove("error");
        }

        if (confirmNewPassword) {
            confirmNewPassword.value = "";
            confirmNewPassword.type = "password";
            confirmNewPassword.classList.remove("error");
        }

        const resetPasswordIcons = document.querySelectorAll(".reset-toggle-password img");

        resetPasswordIcons.forEach(function (icon) {
            icon.src = "images/password/view.png";
            icon.alt = "Show Password";
        });
    };
});
