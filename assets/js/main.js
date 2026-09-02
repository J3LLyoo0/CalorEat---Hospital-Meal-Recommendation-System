document.addEventListener("DOMContentLoaded", function () {
    setupPatientButtons();
    setupModalCloseButtons();
});

function getPatients() {
    return Array.isArray(window.CALOREAT_PATIENTS)
        ? window.CALOREAT_PATIENTS
        : [];
}

function getPatientName(patient) {
    return String(
        ((patient.first_name || "") + " " + (patient.last_name || "")).trim() ||
        patient.email ||
        "Patient"
    );
}

function setupPatientButtons() {
    document.querySelectorAll(".js-register-btn").forEach(function (button) {
        button.addEventListener("click", function () {
            openModal("patientModal");
        });
    });
}

function openModal(id) {
    const modal = document.getElementById(id);

    if (modal) {
        modal.classList.add("show");
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);

    if (modal) {
        modal.classList.remove("show");
    }
}

function setupModalCloseButtons() {
    document.querySelectorAll("[data-close]").forEach(function (button) {
        button.addEventListener("click", function () {
            closeModal(button.dataset.close);
        });
    });

    document.querySelectorAll(".modal-overlay").forEach(function (overlay) {
        overlay.addEventListener("click", function (event) {
            if (event.target === overlay) {
                overlay.classList.remove("show");
            }
        });
    });
}

function escapeHtml(text) {
    return String(text)
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}
