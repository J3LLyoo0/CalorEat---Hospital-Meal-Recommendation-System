document.addEventListener("DOMContentLoaded", function () {
    const toggleButtons = document.querySelectorAll(".toggle-password");

    toggleButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const targetInputId = button.getAttribute("data-target");
            const passwordInput = document.getElementById(targetInputId);
            const icon = button.querySelector("img");

            if (!passwordInput || !icon) {
                console.log("Password toggle target is not found: ",targetInputId);
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