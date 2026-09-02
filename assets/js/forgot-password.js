document.addEventListener("DOMContentLoaded", function () {
    const openForgotModal = document.getElementById("openForgotModal");
    const closeForgotModal = document.getElementById("closeForgotModal");
    const forgotModal = document.getElementById("forgotModal");

    const emailStep = document.getElementById("emailStep");
    const otpStep = document.getElementById("otpStep");
    const resetStep = document.getElementById("resetStep");
    const resetSuccessStep = document.getElementById("resetSuccessStep");

    const forgotEmail = document.getElementById("forgotEmail");
    const forgotSubmit = document.getElementById("forgotSubmit");
    const emailMessage = document.getElementById("emailMessage");

    const otpInputs = document.querySelectorAll(".otp-input");
    const resendOtp = document.getElementById("resendOtp");
    const verifyOtp = document.getElementById("verifyOtp");
    const otpMessage = document.getElementById("otpMessage");

    const newPassword = document.getElementById("newPassword");
    const confirmNewPassword = document.getElementById("confirmNewPassword");
    const resetPasswordBtn = document.getElementById("resetPasswordBtn");
    const passwordMessage = document.getElementById("passwordMessage");

    const backToLoginBtn = document.getElementById("backToLoginBtn");

    function showStep(stepToShow) {
        [emailStep, otpStep, resetStep, resetSuccessStep].forEach(function (step) {
            if (step) {
                step.style.display = "none";
            }
        });

        if (stepToShow) {
            stepToShow.style.display = "block";
        }
    }

    function setMessage(element, message, isError) {
        if (!element) {
            return;
        }

        element.textContent = message;
        element.classList.toggle("error", Boolean(isError));
    }

    function shakeInput(input) {
        if (!input) {
            return;
        }

        input.classList.remove("error");
        void input.offsetWidth;
        input.classList.add("error");
    }

    function setButtonLoading(button, isLoading, normalText, loadingText) {
        if (!button) {
            return;
        }

        button.disabled = isLoading;
        button.textContent = isLoading ? loadingText : normalText;
    }

    async function postJson(url, payload) {
        const response = await fetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(payload)
        });

        const rawText = await response.text();

        try {
            return JSON.parse(rawText);
        } catch (error) {
            console.error("Invalid JSON response from", url, rawText);
            throw new Error("The server returned an invalid response.");
        }
    }

    function clearOtpInputs(focusFirst) {
        otpInputs.forEach(function (input) {
            input.value = "";
            input.classList.remove("error");
        });

        if (focusFirst && otpInputs.length > 0) {
            otpInputs[0].focus();
        }
    }

    function showOtpError(message) {
        otpInputs.forEach(function (input) {
            input.classList.remove("error");
            void input.offsetWidth;
            input.classList.add("error");
        });

        setMessage(otpMessage, message, true);

        setTimeout(function () {
            clearOtpInputs(true);
        }, 350);
    }

    async function sendForgotPasswordOtp(isResend) {
        const email = forgotEmail ? forgotEmail.value.trim() : "";

        setMessage(emailMessage, "", false);
        setMessage(otpMessage, "", false);

        if (email === "") {
            shakeInput(forgotEmail);
            setMessage(emailMessage, "Please enter your email address.", true);
            forgotEmail.focus();
            return;
        }

        if (!forgotEmail.checkValidity()) {
            shakeInput(forgotEmail);
            setMessage(emailMessage, "Please enter a valid email address.", true);
            forgotEmail.focus();
            return;
        }

        if (isResend) {
            setButtonLoading(verifyOtp, true, "Verify", "Sending...");
        } else {
            setButtonLoading(forgotSubmit, true, "Submit", "Sending...");
        }

        try {
            const result = await postJson("send_forgot_password_otp.php", {
                email: email
            });

            if (!result.success) {
                if (isResend) {
                    setMessage(otpMessage, result.message || "Unable to resend OTP.", true);
                } else {
                    shakeInput(forgotEmail);
                    setMessage(emailMessage, result.message || "Unable to send OTP.", true);
                }
                return;
            }

            clearOtpInputs(false);
            showStep(otpStep);
            setMessage(otpMessage, result.message || "OTP sent successfully.", false);

            if (otpInputs.length > 0) {
                otpInputs[0].focus();
            }
        } catch (error) {
            const message = error.message || "Unable to connect to the server.";

            if (isResend) {
                setMessage(otpMessage, message, true);
            } else {
                setMessage(emailMessage, message, true);
            }
        } finally {
            setButtonLoading(forgotSubmit, false, "Submit", "Sending...");
            setButtonLoading(verifyOtp, false, "Verify", "Sending...");
        }
    }

    if (openForgotModal && forgotModal) {
        openForgotModal.addEventListener("click", function (event) {
            event.preventDefault();
            forgotModal.classList.add("active");
        });
    }

    if (closeForgotModal && forgotModal) {
        closeForgotModal.addEventListener("click", function () {
            forgotModal.classList.remove("active");
        });
    }

    if (forgotModal) {
        forgotModal.addEventListener("click", function (event) {
            if (event.target === forgotModal) {
                forgotModal.classList.remove("active");
            }
        });
    }

    if (forgotSubmit) {
        forgotSubmit.addEventListener("click", function () {
            sendForgotPasswordOtp(false);
        });
    }

    if (forgotEmail) {
        forgotEmail.addEventListener("input", function () {
            forgotEmail.classList.remove("error");
            setMessage(emailMessage, "", false);
        });

        forgotEmail.addEventListener("keydown", function (event) {
            if (event.key === "Enter") {
                event.preventDefault();
                sendForgotPasswordOtp(false);
            }
        });
    }

    otpInputs.forEach(function (input, index) {
        input.addEventListener("input", function () {
            input.value = input.value.replace(/[^0-9]/g, "");
            input.classList.remove("error");
            setMessage(otpMessage, "", false);

            if (input.value !== "" && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
        });

        input.addEventListener("keydown", function (event) {
            if (event.key === "Backspace" && input.value === "" && index > 0) {
                otpInputs[index - 1].focus();
            }

            if (event.key === "Enter") {
                event.preventDefault();
                verifyOtp.click();
            }
        });

        input.addEventListener("paste", function (event) {
            const pastedText = (event.clipboardData || window.clipboardData)
                .getData("text")
                .replace(/[^0-9]/g, "")
                .slice(0, otpInputs.length);

            if (pastedText.length === 0) {
                return;
            }

            event.preventDefault();

            otpInputs.forEach(function (otpInput, otpIndex) {
                otpInput.value = pastedText[otpIndex] || "";
            });

            const focusIndex = Math.min(pastedText.length, otpInputs.length) - 1;
            otpInputs[focusIndex].focus();
        });
    });

    if (resendOtp) {
        resendOtp.addEventListener("click", function (event) {
            event.preventDefault();
            clearOtpInputs(false);
            sendForgotPasswordOtp(true);
        });
    }

    if (verifyOtp) {
        verifyOtp.addEventListener("click", async function () {
            let enteredOtp = "";

            otpInputs.forEach(function (input) {
                enteredOtp += input.value;
            });

            if (enteredOtp.length !== otpInputs.length) {
                showOtpError("Please enter the complete OTP code.");
                return;
            }

            setButtonLoading(verifyOtp, true, "Verify", "Checking...");

            try {
                const result = await postJson("verify_forgot_password_otp.php", {
                    otp: enteredOtp
                });

                if (!result.success) {
                    showOtpError(result.message || "Invalid OTP.");
                    return;
                }

                setMessage(otpMessage, result.message || "OTP verified successfully.", false);

                setTimeout(function () {
                    showStep(resetStep);
                    newPassword.focus();
                }, 400);
            } catch (error) {
                showOtpError(error.message || "Unable to verify OTP.");
            } finally {
                setButtonLoading(verifyOtp, false, "Verify", "Checking...");
            }
        });
    }

    if (resetPasswordBtn && newPassword && confirmNewPassword) {
        resetPasswordBtn.addEventListener("click", async function () {
            let hasError = false;

            newPassword.classList.remove("error");
            confirmNewPassword.classList.remove("error");
            setMessage(passwordMessage, "", false);

            if (newPassword.value === "") {
                shakeInput(newPassword);
                hasError = true;
            }

            if (confirmNewPassword.value === "") {
                shakeInput(confirmNewPassword);
                hasError = true;
            }

            if (hasError) {
                setMessage(passwordMessage, "Please fill in both password fields.", true);
                newPassword.focus();
                return;
            }

            if (newPassword.value.length < 6) {
                shakeInput(newPassword);
                setMessage(passwordMessage, "Password must be at least 6 characters.", true);
                newPassword.focus();
                return;
            }

            if (newPassword.value !== confirmNewPassword.value) {
                shakeInput(newPassword);
                shakeInput(confirmNewPassword);
                setMessage(passwordMessage, "Passwords do not match.", true);
                confirmNewPassword.focus();
                return;
            }

            setButtonLoading(resetPasswordBtn, true, "Reset", "Updating...");

            try {
                const result = await postJson("reset_forgot_password.php", {
                    new_password: newPassword.value,
                    confirm_password: confirmNewPassword.value
                });

                if (!result.success) {
                    shakeInput(newPassword);
                    shakeInput(confirmNewPassword);
                    setMessage(passwordMessage, result.message || "Unable to reset password.", true);
                    return;
                }

                showStep(resetSuccessStep);
            } catch (error) {
                setMessage(passwordMessage, error.message || "Unable to reset password.", true);
            } finally {
                setButtonLoading(resetPasswordBtn, false, "Reset", "Updating...");
            }
        });

        newPassword.addEventListener("input", function () {
            newPassword.classList.remove("error");
            setMessage(passwordMessage, "", false);
        });

        confirmNewPassword.addEventListener("input", function () {
            confirmNewPassword.classList.remove("error");
            setMessage(passwordMessage, "", false);
        });
    }

    if (backToLoginBtn && forgotModal) {
        backToLoginBtn.addEventListener("click", function () {
            forgotModal.classList.remove("active");

            if (window.resetForgotModal) {
                window.resetForgotModal();
            }
        });
    }
});
