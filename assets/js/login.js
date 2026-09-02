document.addEventListener("DOMContentLoaded", function () {

    /* Universal Password Toggle */
    const passwordToggleButtons = document.querySelectorAll(".toggle-password, .reset-toggle-password");

    passwordToggleButtons.forEach(function (button) {
    button.addEventListener("click", function () {
        const targetInputId = button.getAttribute("data-target");
        const passwordInput = document.getElementById(targetInputId);
        const icon = button.querySelector("img");

        if (!passwordInput || !icon) {
            return;
        }

        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            icon.src = "images/password/hide.png";
            icon.alt = "Hide Password";
        } else {
            passwordInput.type = "password";
            icon.src = "images/password/view.png";
            icon.alt = "Show Password";
        }
    });
    });
    
});