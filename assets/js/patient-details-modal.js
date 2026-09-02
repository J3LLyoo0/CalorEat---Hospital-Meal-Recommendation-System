let activePatientCard = null;
let originalPatientGender = "";
let patientGenderEditUsed = false;

document.addEventListener("DOMContentLoaded", function () {
    const editButton = document.getElementById("editPatientInformationBtn");

    const cancelButton = document.getElementById("cancelPatientEditBtn");

    const editForm = document.getElementById("patientEditForm");

    if (editButton) {
        editButton.addEventListener("click", openPatientEditMode);
    }

    if (cancelButton) {
        cancelButton.addEventListener("click", closePatientEditMode);
    }

    if (editForm) {
        editForm.addEventListener("submit", savePatientInformation);
    }

    [
        "editPatientDob",
        "editPatientHeight",
        "editPatientWeight",
        "editPatientActivity",
        "editPatientGender"
    ].forEach(function (id) {
        const input = document.getElementById(id);

        if (input) {
            input.addEventListener("input", updateEditCaloriePreview);

            input.addEventListener("change", updateEditCaloriePreview);
        }
    });

    const patientCards =
        document.querySelectorAll(".patient-card");

    const customizeButton =
        document.getElementById("patientCustomizeMealBtn");

    patientCards.forEach(function (card) {
        card.addEventListener("click", function () {
            activePatientCard = card;

            fillPatientDetails(card);
            closePatientEditMode();
            openModal("patientDetailsModal");
        });
    });

    if (customizeButton) {
        customizeButton.addEventListener("click", function () {
            const patientId =
                customizeButton.dataset.patientId || "";

            selectPatientForCustomization(patientId);

            closeModal("patientDetailsModal");
            openModal("customizeModal");
        });
    }
});

function fillPatientDetails(card) {
    const name =
        getDatasetValue(card, "name", "Unnamed Patient");

    const initials =
        createPatientInitials(name);

    setPatientDetailText(
        "detailsPatientInitials",
        initials
    );

    setPatientDetailText(
        "detailsPatientName",
        name
    );

    const patientId = getDatasetValue(card, "patientId", "");

    setPatientDetailText(
        "detailsPatientId",
        patientId || "Not available"
    );

    const dateOfBirth =
        getDatasetValue(card, "dateOfBirth", "");

    setPatientDetailText(
        "detailsPatientDob",
        formatPatientDate(dateOfBirth)
    );

    setPatientDetailText(
        "detailsPatientAge",
        calculatePatientAge(dateOfBirth)
    );

    setPatientDetailText(
        "detailsPatientGender",
        getDatasetValue(card, "gender", "Not provided")
    );

    setPatientDetailText(
        "detailsPatientIc",
        getDatasetValue(card, "icNumber", "Not provided")
    );

    setPatientDetailText(
        "detailsPatientContact",
        getDatasetValue(card, "contact", "Not provided")
    );

    setPatientDetailText(
        "detailsPatientEmail",
        getDatasetValue(card, "email", "Not provided")
    );

    setPatientDetailText(
        "detailsPatientHeight",
        formatMeasurement(
            card.dataset.height,
            "cm"
        )
    );

    setPatientDetailText(
        "detailsPatientWeight",
        formatMeasurement(
            card.dataset.weight,
            "kg"
        )
    );

    setPatientDetailText(
        "detailsPatientActivity",
        getDatasetValue(
            card,
            "activityLevel",
            "Not provided"
        )
    );

    setPatientDetailText(
        "detailsPatientCalories",
        formatDailyCalories(card.dataset.calories)
    );

    setPatientDetailText(
        "detailsPatientCondition",
        getDatasetValue(
            card,
            "condition",
            "None recorded"
        )
    );

    setPatientDetailText(
        "detailsPatientAllergy",
        getDatasetValue(
            card,
            "allergy",
            "None recorded"
        )
    );

    const customizeButton =
        document.getElementById("patientCustomizeMealBtn");

    if (customizeButton) {
        customizeButton.dataset.patientId =
            card.dataset.patientId || "";
    }

    loadPatientRecommendations(patientId);
}

function getDatasetValue(element, key, fallback) {
    const value = String(
        element.dataset[key] || ""
    ).trim();

    return value !== "" ? value : fallback;
}

function setPatientDetailText(elementId, value) {
    const element =
        document.getElementById(elementId);

    if (element) {
        element.textContent = value;
    }
}

function createPatientInitials(name) {
    const nameParts = String(name)
        .trim()
        .split(/\s+/)
        .filter(Boolean);

    if (nameParts.length === 0) {
        return "P";
    }

    if (nameParts.length === 1) {
        return nameParts[0]
            .charAt(0)
            .toUpperCase();
    }

    return (
        nameParts[0].charAt(0) +
        nameParts[nameParts.length - 1].charAt(0)
    ).toUpperCase();
}

function formatPatientDate(dateValue) {
    if (
        !dateValue ||
        dateValue === "0000-00-00"
    ) {
        return "Not provided";
    }

    const dateParts =
        dateValue.split("-").map(Number);

    if (
        dateParts.length !== 3 ||
        dateParts.some(Number.isNaN)
    ) {
        return "Not provided";
    }

    const date = new Date(
        dateParts[0],
        dateParts[1] - 1,
        dateParts[2]
    );

    return new Intl.DateTimeFormat(
        "en-MY",
        {
            day: "numeric",
            month: "long",
            year: "numeric"
        }
    ).format(date);
}

function calculatePatientAge(dateValue) {
    if (
        !dateValue ||
        dateValue === "0000-00-00"
    ) {
        return "Not available";
    }

    const dateParts =
        dateValue.split("-").map(Number);

    if (
        dateParts.length !== 3 ||
        dateParts.some(Number.isNaN)
    ) {
        return "Not available";
    }

    const birthDate = new Date(
        dateParts[0],
        dateParts[1] - 1,
        dateParts[2]
    );

    const today = new Date();

    if (birthDate > today) {
        return "Not available";
    }

    let age =
        today.getFullYear() -
        birthDate.getFullYear();

    const monthDifference =
        today.getMonth() -
        birthDate.getMonth();

    const birthdayNotReached =
        monthDifference < 0 ||
        (
            monthDifference === 0 &&
            today.getDate() <
            birthDate.getDate()
        );

    if (birthdayNotReached) {
        age--;
    }

    return age + " years old";
}

function formatMeasurement(value, unit) {
    const numberValue = Number(value);

    if (
        !Number.isFinite(numberValue) ||
        numberValue <= 0
    ) {
        return "Not provided";
    }

    return (
        numberValue.toLocaleString(
            "en-MY",
            {
                maximumFractionDigits: 2
            }
        ) +
        " " +
        unit
    );
}

function formatDailyCalories(value) {
    const calorieValue = Number(value);

    if (
        !Number.isFinite(calorieValue) ||
        calorieValue <= 0
    ) {
        return "Not calculated";
    }

    return (
        Math.round(calorieValue)
            .toLocaleString("en-MY") +
        " kcal/day"
    );
}

function selectPatientForCustomization(patientId) {
    if (!patientId) {
        return;
    }

    if (typeof window.prepareMealCustomizationPatient === "function") {
        window.prepareMealCustomizationPatient(patientId);
        return;
    }

    const patientSelect =
        document.getElementById("customPatient");

    if (patientSelect) {
        patientSelect.value = String(patientId);
        patientSelect.dispatchEvent(new Event("change"));
    }
}

async function loadPatientRecommendations(
    patientId
) {
    const recommendationList =
        document.getElementById(
            "patientRecommendationsList"
        );

    const recommendationCount =
        document.getElementById(
            "patientRecommendationCount"
        );

    if (
        !recommendationList ||
        !recommendationCount
    ) {
        return;
    }

    recommendationList.replaceChildren();

    const loadingMessage =
        document.createElement("p");

    loadingMessage.className =
        "patient-recommendation-placeholder";

    loadingMessage.textContent =
        "Loading recommended meals...";

    recommendationList.appendChild(
        loadingMessage
    );

    recommendationCount.textContent =
        "Loading...";

    if (!patientId) {
        showRecommendationError(
            "Patient ID is unavailable."
        );

        return;
    }

    try {
        const response = await fetch(
            "get_patient_recommendations.php" +
            "?patient_id=" +
            encodeURIComponent(patientId) +
            "&v=" +
            Date.now(),
            {
                method: "GET",
                headers: {
                    "Accept": "application/json"
                }
            }
        );

        const responseText =
            await response.text();

        let result;

        try {
            result =
                JSON.parse(responseText);
        } catch (error) {
            throw new Error(
                "The server returned an invalid response."
            );
        }

        if (
            !response.ok ||
            !result.success
        ) {
            throw new Error(
                result.message ||
                "Recommendations could not be loaded."
            );
        }

        renderPatientRecommendations(
            Array.isArray(
                result.recommendations
            )
                ? result.recommendations
                : []
        );

    } catch (error) {
        showRecommendationError(
            error.message
        );
    }
}

function renderPatientRecommendations(
    recommendations
) {
    const recommendationList =
        document.getElementById(
            "patientRecommendationsList"
        );

    const recommendationCount =
        document.getElementById(
            "patientRecommendationCount"
        );

    if (
        !recommendationList ||
        !recommendationCount
    ) {
        return;
    }

    recommendationList.replaceChildren();

    const count =
        recommendations.length;

    recommendationCount.textContent =
        count +
        (
            count === 1
                ? " recommendation"
                : " recommendations"
        );

    if (count === 0) {
        const emptyMessage =
            document.createElement("p");

        emptyMessage.className =
            "patient-recommendation-placeholder";

        emptyMessage.textContent =
            "No meals have been recommended for this patient.";

        recommendationList.appendChild(
            emptyMessage
        );

        return;
    }

    recommendations.forEach(
        function (recommendation) {
            recommendationList.appendChild(
                createRecommendationCard(
                    recommendation
                )
            );
        }
    );
}

function createRecommendationCard(
    recommendation
) {
    const card =
        document.createElement("article");

    card.className =
        "patient-recommendation-card";

    const imageBox =
        document.createElement("div");

    imageBox.className =
        "patient-recommendation-image";

    if (recommendation.image) {
        const image =
            document.createElement("img");

        image.src =
            recommendation.image;

        image.alt =
            recommendation.food_name ||
            "Recommended meal";

        image.onerror = function () {
            image.remove();
            imageBox.classList.add(
                "is-missing"
            );
        };

        imageBox.appendChild(image);
    } else {
        imageBox.classList.add(
            "is-missing"
        );
    }

    const content =
        document.createElement("div");

    content.className =
        "patient-recommendation-content";

    const topRow =
        document.createElement("div");

    topRow.className =
        "patient-recommendation-top";

    const mealName =
        document.createElement("h5");

    mealName.textContent =
        recommendation.food_name ||
        "Unnamed Meal";

    const mealTime =
        document.createElement("span");

    mealTime.className =
        "patient-recommendation-time";

    mealTime.textContent =
        recommendation.meal_time ||
        "Meal time not stated";

    topRow.appendChild(mealName);
    topRow.appendChild(mealTime);

    const category =
        document.createElement("p");

    category.className =
        "patient-recommendation-category";

    category.textContent =
        recommendation.category_name ||
        "Uncategorized";

    const meta =
        document.createElement("div");

    meta.className =
        "patient-recommendation-meta";

    const calories =
        document.createElement("span");

    calories.textContent =
        formatRecommendationCalories(
            recommendation.meal_calories
        );

    const type =
        document.createElement("span");

    type.className =
        "patient-recommendation-type";

    type.textContent =
        (
            recommendation
                .recommendation_type ||
            "manual"
        ) +
        " recommendation";

    const result =
        document.createElement("span");

    const resultValue =
        String(
            recommendation
                .evaluation_result ||
            "review"
        ).toLowerCase();

    result.className =
        "patient-recommendation-result " +
        resultValue;

    result.textContent =
        resultValue === "recommended"
            ? "Recommended"
            : "Nutritionist Review";

    meta.appendChild(calories);
    meta.appendChild(type);
    meta.appendChild(result);

    const date =
        document.createElement("p");

    date.className =
        "patient-recommendation-date";

    date.textContent =
        formatRecommendationDate(
            recommendation.recommended_at
        );

    content.appendChild(topRow);
    content.appendChild(category);
    content.appendChild(meta);
    content.appendChild(date);

    card.appendChild(imageBox);
    card.appendChild(content);

    return card;
}

function formatRecommendationCalories(value) {
    const calories =
        Number(value || 0);

    if (
        !Number.isFinite(calories) ||
        calories <= 0
    ) {
        return "Calories unavailable";
    }

    return (
        Math.round(calories)
            .toLocaleString("en-MY") +
        " kcal"
    );
}

function formatRecommendationDate(value) {
    if (!value) {
        return "Recommendation date unavailable";
    }

    /*
     * MySQL returns:
     * 2026-07-14 11:30:00
     *
     * Convert it into a browser-readable form.
     */
    const normalizedValue =
        String(value).replace(
            " ",
            "T"
        );

    const date =
        new Date(normalizedValue);

    if (Number.isNaN(date.getTime())) {
        return String(value);
    }

    return new Intl.DateTimeFormat(
        "en-MY",
        {
            day: "numeric",
            month: "long",
            year: "numeric",
            hour: "numeric",
            minute: "2-digit"
        }
    ).format(date);
}

function showRecommendationError(message) {
    const recommendationList =
        document.getElementById(
            "patientRecommendationsList"
        );

    const recommendationCount =
        document.getElementById(
            "patientRecommendationCount"
        );

    if (recommendationCount) {
        recommendationCount.textContent =
            "Unable to load";
    }

    if (!recommendationList) {
        return;
    }

    recommendationList.replaceChildren();

    const errorMessage =
        document.createElement("p");

    errorMessage.className =
        "patient-recommendation-placeholder";

    errorMessage.textContent =
        message ||
        "Recommended meals could not be loaded.";

    recommendationList.appendChild(
        errorMessage
    );
}

function openPatientEditMode() {
    if (!activePatientCard) {
        return;
    }

    const detailsView =
        document.getElementById(
            "patientDetailsView"
        );

    const editForm =
        document.getElementById(
            "patientEditForm"
        );

    const editButton =
        document.getElementById(
            "editPatientInformationBtn"
        );

    if (!detailsView || !editForm) {
        return;
    }

    populatePatientEditForm(
        activePatientCard
    );

    detailsView.hidden = true;
    editForm.hidden = false;

    if (editButton) {
        editButton.hidden = true;
    }

    setPatientEditStatus("");
    updateEditCaloriePreview();
}

function closePatientEditMode() {
    const detailsView =
        document.getElementById(
            "patientDetailsView"
        );

    const editForm =
        document.getElementById(
            "patientEditForm"
        );

    const editButton =
        document.getElementById(
            "editPatientInformationBtn"
        );

    if (detailsView) {
        detailsView.hidden = false;
    }

    if (editForm) {
        editForm.hidden = true;
    }

    if (editButton) {
        editButton.hidden = false;
    }

    setPatientEditStatus("");
}

function populatePatientEditForm(card) {
    const fullName =
        String(card.dataset.name || "")
            .trim();

    const nameParts =
        fullName.split(/\s+/);

    /*
     * Use the exact stored first and last names
     * where possible.
     */
    const patients =
        Array.isArray(
            window.CALOREAT_PATIENTS
        )
            ? window.CALOREAT_PATIENTS
            : [];

    const patient =
        patients.find(function (item) {
            return String(item.patient_id) ===
                String(card.dataset.patientId);
        });

    const firstName =
        patient?.first_name ||
        nameParts[0] ||
        "";

    const lastName =
        patient?.last_name ||
        nameParts.slice(1).join(" ");

    setPatientEditValue(
        "editPatientId",
        card.dataset.patientId
    );

    setPatientEditValue(
        "editPatientFirstName",
        firstName
    );

    setPatientEditValue(
        "editPatientLastName",
        lastName
    );

    setPatientEditValue(
        "editPatientContact",
        card.dataset.contact
    );

    setPatientEditValue(
        "editPatientEmail",
        card.dataset.email ===
            "Not Provided" ||
        card.dataset.email ===
            "Not provided"
            ? ""
            : card.dataset.email
    );

    setPatientEditValue(
        "editPatientDob",
        card.dataset.dateOfBirth
    );

    setPatientEditValue(
        "editPatientIc",
        card.dataset.icNumber
    );

    setPatientEditValue(
        "editPatientGender",
        card.dataset.gender
    );

    setPatientEditValue(
        "editPatientHeight",
        card.dataset.height
    );

    setPatientEditValue(
        "editPatientWeight",
        card.dataset.weight
    );

    setPatientEditValue(
        "editPatientActivity",
        card.dataset.activityLevel
    );

    setPatientEditValue(
        "editPatientConditions",
        normalizeEditableText(
            card.dataset.condition
        )
    );

    setPatientEditValue(
        "editPatientAllergies",
        normalizeEditableText(
            card.dataset.allergy
        )
    );

    originalPatientGender =
        String(card.dataset.gender || "");

    patientGenderEditUsed =
        String(
            card.dataset.genderEditUsed || "0"
        ) === "1";

    const genderSelect =
        document.getElementById(
            "editPatientGender"
        );

    const genderMessage =
        document.getElementById(
            "editPatientGenderMessage"
        );

    if (genderSelect) {
        genderSelect.disabled =
            patientGenderEditUsed;
    }

    if (genderMessage) {
        genderMessage.textContent =
            patientGenderEditUsed
                ? "Gender has already been corrected once. Further changes require an administrator."
                : "The nutritionist may correct the patient's gender once.";
    }
}

function updateEditCaloriePreview() {
    const dob =
        document.getElementById(
            "editPatientDob"
        )?.value || "";

    const height =
        Number(
            document.getElementById(
                "editPatientHeight"
            )?.value || 0
        );

    const weight =
        Number(
            document.getElementById(
                "editPatientWeight"
            )?.value || 0
        );

    const activityLevel =
        document.getElementById(
            "editPatientActivity"
        )?.value || "";

    const gender =
        document.getElementById(
            "editPatientGender"
        )?.value || "";

    const output =
        document.getElementById(
            "editPatientCaloriePreview"
        );

    if (!output) {
        return;
    }

    const age =
        calculateEditPatientAge(dob);

    const factors = {
        "Sedentary": 1.2,
        "Lightly Active": 1.375,
        "Moderately Active": 1.55,
        "Very Active": 1.725,
        "Extremely Active": 1.9
    };

    if (
        age === null ||
        height <= 0 ||
        weight <= 0 ||
        !factors[activityLevel] ||
        !["Male", "Female"].includes(gender)
    ) {
        output.textContent =
            "— kcal/day";

        return;
    }

    const bmr =
        gender === "Male"
            ? (
                10 * weight +
                6.25 * height -
                5 * age +
                5
            )
            : (
                10 * weight +
                6.25 * height -
                5 * age -
                161
            );

    const calories =
        Math.round(
            bmr * factors[activityLevel]
        );

    output.textContent =
        calories.toLocaleString("en-MY") +
        " kcal/day";
}

function calculateEditPatientAge(value) {
    if (!value) {
        return null;
    }

    const birthDate =
        new Date(value + "T00:00:00");

    if (
        Number.isNaN(
            birthDate.getTime()
        )
    ) {
        return null;
    }

    const today = new Date();

    if (birthDate > today) {
        return null;
    }

    let age =
        today.getFullYear() -
        birthDate.getFullYear();

    const monthDifference =
        today.getMonth() -
        birthDate.getMonth();

    if (
        monthDifference < 0 ||
        (
            monthDifference === 0 &&
            today.getDate() <
            birthDate.getDate()
        )
    ) {
        age--;
    }

    return age;
}

async function savePatientInformation(event) {
    event.preventDefault();

    const form = event.currentTarget;

    if (!form.reportValidity()) {
        return;
    }

    const saveButton =
        document.getElementById(
            "savePatientInformationBtn"
        );

    const genderSelect =
        document.getElementById(
            "editPatientGender"
        );

    const payload = {
        patient_id:
            document.getElementById(
                "editPatientId"
            ).value,

        first_name:
            document.getElementById(
                "editPatientFirstName"
            ).value.trim(),

        last_name:
            document.getElementById(
                "editPatientLastName"
            ).value.trim(),

        contact_number:
            document.getElementById(
                "editPatientContact"
            ).value.trim(),

        email:
            document.getElementById(
                "editPatientEmail"
            ).value.trim(),

        date_of_birth:
            document.getElementById(
                "editPatientDob"
            ).value,

        gender:
            genderSelect
                ? genderSelect.value
                : originalPatientGender,

        height:
            document.getElementById(
                "editPatientHeight"
            ).value,

        weight:
            document.getElementById(
                "editPatientWeight"
            ).value,

        activity_level:
            document.getElementById(
                "editPatientActivity"
            ).value,

        medical_conditions:
            document.getElementById(
                "editPatientConditions"
            ).value.trim(),

        allergies:
            document.getElementById(
                "editPatientAllergies"
            ).value.trim()
    };

    if (saveButton) {
        saveButton.disabled = true;
    }

    setPatientEditStatus(
        "Saving patient information..."
    );

    try {
        const response = await fetch(
            "update_patient.php",
            {
                method: "POST",
                headers: {
                    "Content-Type":
                        "application/json"
                },
                body: JSON.stringify(payload)
            }
        );

        const responseText =
            await response.text();

        let result;

        try {
            result =
                JSON.parse(responseText);
        } catch (error) {
            throw new Error(
                "The server returned an invalid response."
            );
        }

        if (
            !response.ok ||
            !result.success
        ) {
            throw new Error(
                result.message ||
                "Patient information could not be saved."
            );
        }

        setPatientEditStatus(
            "Patient information saved successfully."
        );

        setTimeout(function () {
            closeModal(
                "patientDetailsModal"
            );

            window.location.reload();
        }, 650);

    } catch (error) {
        setPatientEditStatus(
            error.message
        );

        if (saveButton) {
            saveButton.disabled = false;
        }
    }
}

function setPatientEditValue(id, value) {
    const element =
        document.getElementById(id);

    if (element) {
        element.value =
            value ?? "";
    }
}

function setPatientEditStatus(message) {
    const status =
        document.getElementById(
            "patientEditStatus"
        );

    if (status) {
        status.textContent = message;
    }
}

function normalizeEditableText(value) {
    const text =
        String(value || "").trim();

    const emptyValues = [
        "None Recorded",
        "None recorded",
        "Not Provided",
        "Not provided"
    ];

    return emptyValues.includes(text)
        ? ""
        : text;
}