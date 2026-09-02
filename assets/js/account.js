document.addEventListener("DOMContentLoaded", function () {
    const openPasswordModal = document.getElementById("openPasswordModal");
    const closePasswordModal = document.getElementById("closePasswordModal");
    const closeSuccessModal = document.getElementById("closeSuccessModal");

    const passwordModal = document.getElementById("passwordModal");

    const otpStep = document.getElementById("otpStep");
    const newPasswordStep = document.getElementById("newPasswordStep");
    const successStep = document.getElementById("successStep");

    const otpForm = document.getElementById("otpForm");
    const otpBoxes = document.querySelectorAll(".otp-box");
    const otpInputRow = document.getElementById("otpInputRow");
    const otpStatusText = document.getElementById("otpStatusText");

    const newPasswordForm = document.getElementById("newPasswordForm");
    const newPassword = document.getElementById("newPassword");
    const confirmNewPassword = document.getElementById("confirmNewPassword");
    const passwordResetFields = document.getElementById("passwordResetFields");
    const passwordStatusText = document.getElementById("passwordStatusText");

    const profileImageInput = document.getElementById("profileImageInput");
    const profileImagePreview = document.getElementById("profileImagePreview");

    const passwordToggleButtons = document.querySelectorAll(".reset-password-toggle");

    function showStep(stepName) {
        otpStep.classList.remove("reset-step-active");
        newPasswordStep.classList.remove("reset-step-active");
        successStep.classList.remove("reset-step-active");

        if (stepName === "otp") {
            otpStep.classList.add("reset-step-active");
        }

        if (stepName === "password") {
            newPasswordStep.classList.add("reset-step-active");
        }

        if (stepName === "success") {
            successStep.classList.add("reset-step-active");
        }
    }

    function clearOtpBoxes() {
        otpBoxes.forEach(function (box) {
            box.value = "";
            box.classList.remove("input-error");
        });
    }

    function shakeElement(element) {
        if(!element) {
            console.error("Shake animation element is not detected.");
            return;
        }
        
        element.classList.remove("shake-error");

        //Force the browser to restart the animation
        void element.offsetWidth;

        element.classList.add("shake-error");

        setTimeout(function () {
            element.classList.remove("shake-error");
        }, 300);
    }

    function markOtpError() {
        otpBoxes.forEach(function (box) {
            box.classList.add("input-error");
        });

        shakeElement(otpInputRow);
    }

    function clearOtpError() {
        otpBoxes.forEach(function (box) {
            box.classList.remove("input-error");
        });
    }

    function markPasswordError() {
        newPassword.classList.add("input-error");
        confirmNewPassword.classList.add("input-error");
        shakeElement(passwordResetFields);
    }

    function clearPasswordError() {
        newPassword.classList.remove("input-error");
        confirmNewPassword.classList.remove("input-error");
    }

    function openModal() {
        passwordModal.classList.add("active");
        showStep("otp");
        clearOtpBoxes();

        otpStatusText.style.color = "#000066";
        otpStatusText.textContent = "Sending OTP...";

        fetch("send_account_otp.php", {
            method: "POST"
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            if (data.success) {
                otpStatusText.style.color = "#2f8f6f";

                if (data.debug_otp) {
                    otpStatusText.textContent = "Localhost test OTP: " + data.debug_otp;
                } else {
                    otpStatusText.textContent = "OTP has been sent to your email.";
                }

                setTimeout(function () {
                    otpBoxes[0].focus();
                }, 100);
            } else {
                otpStatusText.style.color = "#d2042d";
                otpStatusText.textContent = data.message || "Failed to send OTP.";
            }
        })
        .catch(function () {
            otpStatusText.style.color = "#d2042d";
            otpStatusText.textContent = "Failed to send OTP. Please try again.";
        });
    }

    function closeModal() {
        passwordModal.classList.remove("active");
    }

    if (openPasswordModal) {
        openPasswordModal.addEventListener("click", openModal);
    }

    if (closePasswordModal) {
        closePasswordModal.addEventListener("click", closeModal);
    }

    if (closeSuccessModal) {
        closeSuccessModal.addEventListener("click", closeModal);
    }

    passwordModal.addEventListener("click", function (event) {
        if (event.target === passwordModal) {
            closeModal();
        }
    });

    otpBoxes.forEach(function (box, index) {
        box.addEventListener("input", function () {
            box.value = box.value.replace(/[^0-9]/g, "");

            clearOtpError();

            if (box.value !== "" && index < otpBoxes.length - 1) {
                otpBoxes[index + 1].focus();
            }
        });

        box.addEventListener("keydown", function (event) {
            if (event.key === "Backspace" && box.value === "" && index > 0) {
                otpBoxes[index - 1].focus();
            }
        });
    });

    otpForm.addEventListener("submit", function (event) {
        event.preventDefault();

        let otpCode = "";

        otpBoxes.forEach(function (box) {
            otpCode += box.value;
        });

        if (otpCode.length !== 4) {
            otpStatusText.style.color = "#d2042d";
            otpStatusText.textContent = "Please enter the 4-digit OTP code.";
            markOtpError();
            return;
        }

        fetch("verify_account_otp.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                otp: otpCode
            })
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            if (data.success) {
                otpStatusText.textContent = "";
                clearOtpError();
                showStep("password");

                setTimeout(function () {
                    newPassword.focus();
                }, 100);
            } else {
                otpStatusText.style.color = "#d2042d";
                otpStatusText.textContent = data.message || "Invalid OTP code.";
                markOtpError();
            }
        })
        .catch(function () {
            otpStatusText.style.color = "#d2042d";
            otpStatusText.textContent = "Failed to verify OTP.";
            markOtpError();
        });
    });

    newPasswordForm.addEventListener("submit", function (event) {
        event.preventDefault();

        clearPasswordError();

        const newPasswordValue = newPassword.value.trim();
        const confirmPasswordValue = confirmNewPassword.value.trim();

        if (newPasswordValue === "" || confirmPasswordValue === "") {
            passwordStatusText.style.color = "#d2042d";
            passwordStatusText.textContent = "Please fill in both password fields.";
            markPasswordError();
            return;
        }

        if (newPasswordValue.length < 6) {
            passwordStatusText.style.color = "#d2042d";
            passwordStatusText.textContent = "Password must be at least 6 characters.";
            markPasswordError();
            return;
        }

        if (newPasswordValue !== confirmPasswordValue) {
            passwordStatusText.style.color = "#d2042d";
            passwordStatusText.textContent = "Passwords do not match.";
            markPasswordError();
            return;
        }

        fetch("reset_account_password.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                new_password: newPasswordValue,
                confirm_password: confirmPasswordValue
            })
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            if (data.success) {
                newPassword.value = "";
                confirmNewPassword.value = "";
                passwordStatusText.textContent = "";
                showStep("success");
            } else {
                passwordStatusText.style.color = "#d2042d";
                passwordStatusText.textContent = data.message || "Failed to reset password.";
                markPasswordError();
            }
        })
        .catch(function () {
            passwordStatusText.style.color = "#d2042d";
            passwordStatusText.textContent = "Failed to reset password.";
            markPasswordError();
        });
    });

    if (profileImageInput && profileImagePreview) {
        const originalImageSource = profileImagePreview.src;

        let temporaryImageUrl = null;

        profileImageInput.addEventListener("change", function() {
            const selectedFile = this.files[0];

            //no file selected
            if (!selectedFile) {
                profileImagePreview.src = originalImageSource;
                return;
            }

            //validate extension
            const allowedExtensions = [
                "jpg", "jpeg", "png", "webp"
            ];

            const fileExtension = selectedFile.name.split(".").pop().toLowerCase();

            if (!allowedExtensions.includes(fileExtension)) {
                alert("Only JPG, JPEG, PNG, and WEBP images are allowed.");

                this.value = "";
                profileImagePreview.src = originalImageSource;

                return;
            }

            //validate file size
            const maximumFileSize = 3 * 1024 * 1024;
            if (selectedFile.size > maximumFileSize) {
                alert("Image size must be below 3MB.");

                this.value = "";
                profileImagePreview.src = originalImageSource;

                return;
            }

            //remove previous temporary preview
            if (temporaryImageUrl) {
                URL.revokeObjectURL(temporaryImageUrl);
            }

            //display selected image before submitting
            temporaryImageUrl = URL.createObjectURL(selectedFile);

            profileImagePreview.src = temporaryImageUrl;
        });

        //clear temporary browser memory when leaving the page
        window.addEventListener("beforeunload", function() {
            if (temporaryImageUrl) {
                URL.revokeObjectURL(temporaryImageUrl);
            }
        });
    }

    passwordToggleButtons.forEach(function (toggleButton) {
        toggleButton.addEventListener("click", function() {
            const targetId = toggleButton.dataset.passwordTarget;
            const passwordInput = document.getElementById(targetId);
            const toggleImage = toggleButton.querySelector(".password-toggle-image");

            if (!passwordInput || !toggleImage) {
                return;
            }

            const passwordIsHidden = passwordInput.type === "password";

            passwordInput.type = passwordIsHidden ? "text" : "password";

            toggleImage.src = passwordIsHidden ? "images/password/hide.png" : "images/password/view.png";

            toggleButton.setAttribute("aria-label", passwordIsHidden ? "Hide password" : "Show password");
        });
    });
});
