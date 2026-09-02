(function () {
    "use strict";

    document.addEventListener("DOMContentLoaded", function () {
        const input = document.getElementById("adminMealImageInput");
        const preview = document.getElementById("adminMealImagePreview");

        if (!input || !preview) {
            return;
        }

        input.addEventListener("change", function () {
            const file = input.files && input.files[0];
            if (!file) {
                return;
            }

            if (!file.type.startsWith("image/")) {
                input.value = "";
                alert("Please select a valid image file.");
                return;
            }

            const reader = new FileReader();
            reader.addEventListener("load", function () {
                preview.src = String(reader.result || "");
            });
            reader.readAsDataURL(file);
        });
    });
})();
