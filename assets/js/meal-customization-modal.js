(function () {
    "use strict";

    let selectedBaseMeal = null;
    let latestEvaluation = null;

    const mealTimeFactors = {
        "Breakfast": 0.20,
        "Morning Snack": 0.10,
        "Lunch": 0.30,
        "Afternoon Snack": 0.10,
        "Dinner": 0.25,
        "Supper": 0.05
    };

    document.addEventListener("DOMContentLoaded", function () {
        const openButton = document.getElementById("customizeOpenBtn");
        const patientSelect = document.getElementById("customPatient");
        const mealTimeSelect = document.getElementById("customMealTime");
        const categorySelect = document.getElementById("customCategory");
        const baseMealSelect = document.getElementById("customBaseMeal");
        const form = document.getElementById("mealCustomizationForm");
        const checkButton = document.getElementById("checkCustomMealBtn");

        populatePatients();
        populateCategories();

        if (openButton) {
            openButton.addEventListener("click", function () {
                resetCustomizationModal();
                openModal("customizeModal");
            });
        }

        if (patientSelect) {
            patientSelect.addEventListener("change", function () {
                updatePatientSummary();
                invalidateEvaluation();
            });
        }

        if (mealTimeSelect) {
            mealTimeSelect.addEventListener("change", function () {
                updatePatientSummary();
                invalidateEvaluation();
            });
        }

        if (categorySelect) {
            categorySelect.addEventListener("change", function () {
                populateBaseMeals(categorySelect.value);
                invalidateEvaluation();
            });
        }

        if (baseMealSelect) {
            baseMealSelect.addEventListener("change", function () {
                selectedBaseMeal = findMealById(baseMealSelect.value);
                displayBaseMeal(selectedBaseMeal);
                prefillFromBaseMeal(selectedBaseMeal);
                invalidateEvaluation();
            });
        }

        [
            "customMealName",
            "customRemoveIngredients",
            "customAddIngredients",
            "customPortionSize",
            "customFinalCalories",
            "customPreparationNotes"
        ].forEach(function (id) {
            const field = document.getElementById(id);

            if (field) {
                field.addEventListener("input", invalidateEvaluation);
                field.addEventListener("change", invalidateEvaluation);
            }
        });

        if (checkButton) {
            checkButton.addEventListener("click", runCustomSuitabilityCheck);
        }

        if (form) {
            form.addEventListener("submit", saveCustomizedMeal);
        }
    });

    window.prepareMealCustomizationPatient = function (patientId) {
        populatePatients();

        const patientSelect = document.getElementById("customPatient");

        if (!patientSelect) {
            return;
        }

        patientSelect.value = String(patientId || "");
        updatePatientSummary();
        invalidateEvaluation();
    };

    function getPatients() {
        return Array.isArray(window.CALOREAT_PATIENTS)
            ? window.CALOREAT_PATIENTS
            : [];
    }

    function getMealGroups() {
        return window.CALOREAT_MEALS && typeof window.CALOREAT_MEALS === "object"
            ? window.CALOREAT_MEALS
            : {};
    }

    function flattenMeals() {
        const meals = [];

        Object.entries(getMealGroups()).forEach(function (entry) {
            const category = entry[0];
            const categoryMeals = Array.isArray(entry[1]) ? entry[1] : [];

            categoryMeals.forEach(function (meal) {
                meals.push({
                    foodId: String(meal.food_id || ""),
                    name: String(meal.name || "Unnamed Meal"),
                    category: category,
                    calories: Number(meal.kcalValue || 0),
                    image: String(meal.image || ""),
                    ingredients: String(meal.ingredients || ""),
                    description: String(meal.description || "")
                });
            });
        });

        return meals;
    }

    function populatePatients() {
        const select = document.getElementById("customPatient");

        if (!select) {
            return;
        }

        const currentValue = select.value;
        const patients = getPatients();

        select.replaceChildren();

        const defaultOption = document.createElement("option");
        defaultOption.value = "";
        defaultOption.textContent = patients.length > 0
            ? "Select a patient"
            : "No registered patients";

        select.appendChild(defaultOption);
        select.disabled = patients.length === 0;

        patients.forEach(function (patient) {
            const option = document.createElement("option");
            option.value = String(patient.patient_id || "");
            option.textContent = getPatientName(patient);
            select.appendChild(option);
        });

        if (currentValue && patients.some(function (patient) {
            return String(patient.patient_id) === String(currentValue);
        })) {
            select.value = currentValue;
        }
    }

    function populateCategories() {
        const select = document.getElementById("customCategory");

        if (!select) {
            return;
        }

        select.replaceChildren();

        const defaultOption = document.createElement("option");
        defaultOption.value = "";
        defaultOption.textContent = "Select category";
        select.appendChild(defaultOption);

        Object.keys(getMealGroups()).forEach(function (category) {
            const option = document.createElement("option");
            option.value = category;
            option.textContent = category;
            select.appendChild(option);
        });
    }

    function populateBaseMeals(category) {
        const select = document.getElementById("customBaseMeal");

        if (!select) {
            return;
        }

        select.replaceChildren();

        const defaultOption = document.createElement("option");
        defaultOption.value = "";
        defaultOption.textContent = category ? "Select base meal" : "Select category first";
        select.appendChild(defaultOption);

        const meals = Array.isArray(getMealGroups()[category])
            ? getMealGroups()[category]
            : [];

        meals.forEach(function (meal) {
            const option = document.createElement("option");
            option.value = String(meal.food_id || "");
            option.textContent = String(meal.name || "Unnamed Meal");
            select.appendChild(option);
        });

        select.disabled = meals.length === 0;
        selectedBaseMeal = null;
        displayBaseMeal(null);
    }

    function findPatient(patientId) {
        return getPatients().find(function (patient) {
            return String(patient.patient_id) === String(patientId);
        }) || null;
    }

    function findMealById(foodId) {
        return flattenMeals().find(function (meal) {
            return String(meal.foodId) === String(foodId);
        }) || null;
    }

    function getPatientName(patient) {
        const name = String(
            (patient.first_name || "") + " " + (patient.last_name || "")
        ).trim();

        return name || "Unnamed Patient";
    }

    function updatePatientSummary() {
        const patientId = document.getElementById("customPatient")?.value || "";
        const mealTime = document.getElementById("customMealTime")?.value || "";
        const patient = findPatient(patientId);

        if (!patient) {
            setText("customDailyCalories", "— kcal/day");
            setText("customMealTarget", "— kcal");
            setText("customPatientCondition", "Not selected");
            setText("customPatientAllergy", "Not selected");
            return;
        }

        const dailyCalories = Number(patient.daily_calorie_intake || 0);
        const factor = mealTimeFactors[mealTime] || 0;
        const target = Math.round(dailyCalories * factor);

        setText(
            "customDailyCalories",
            dailyCalories > 0
                ? dailyCalories.toLocaleString("en-MY") + " kcal/day"
                : "Not calculated"
        );

        setText(
            "customMealTarget",
            target > 0 ? target.toLocaleString("en-MY") + " kcal" : "Select meal time"
        );

        setText(
            "customPatientCondition",
            normalizeEmptyDisplay(patient.medical_conditions, "None recorded")
        );

        setText(
            "customPatientAllergy",
            normalizeEmptyDisplay(patient.allergies, "None recorded")
        );
    }

    function displayBaseMeal(meal) {
        const image = document.getElementById("customBaseImage");
        const list = document.getElementById("customBaseIngredients");

        if (!meal) {
            setText("customBaseName", "Select a base meal");
            setText("customBaseCalories", "— kcal");

            if (image) {
                image.classList.remove("has-image");
                image.removeAttribute("src");
            }

            if (list) {
                list.replaceChildren();
                const item = document.createElement("li");
                item.textContent = "No base meal selected";
                list.appendChild(item);
            }

            return;
        }

        setText("customBaseName", meal.name);
        setText(
            "customBaseCalories",
            meal.calories > 0
                ? meal.calories.toLocaleString("en-MY") + " kcal"
                : "Calories unavailable"
        );

        if (image) {
            image.classList.remove("has-image");

            if (meal.image) {
                image.src = meal.image;
                image.alt = meal.name;
                image.onload = function () {
                    image.classList.add("has-image");
                };
                image.onerror = function () {
                    image.classList.remove("has-image");
                };
            }
        }

        renderIngredientList(list, splitIngredientText(meal.ingredients));
    }

    function prefillFromBaseMeal(meal) {
        if (!meal) {
            return;
        }

        setValue("customMealName", "Customized " + meal.name);
        setValue("customFinalCalories", meal.calories > 0 ? meal.calories : "");

        const portionMatch = meal.description.match(/Portion:\s*([^\.]+)/i);
        setValue("customPortionSize", portionMatch ? portionMatch[1].trim() : "1 serving");
    }

    function runCustomSuitabilityCheck() {
        const form = document.getElementById("mealCustomizationForm");

        if (!form || !form.reportValidity()) {
            return;
        }

        const patient = findPatient(document.getElementById("customPatient")?.value || "");
        const mealTime = document.getElementById("customMealTime")?.value || "";

        if (!patient || !selectedBaseMeal) {
            setSaveMessage("Please select a patient and base meal.");
            return;
        }

        latestEvaluation = evaluateCustomizedMeal(patient, mealTime);
        displayEvaluation(latestEvaluation);
    }

    function evaluateCustomizedMeal(patient, mealTime) {
        const finalCalories = Number(document.getElementById("customFinalCalories")?.value || 0);
        const finalIngredients = buildFinalIngredients();
        const dailyCalories = Number(patient.daily_calorie_intake || 0);
        const target = Math.round(dailyCalories * (mealTimeFactors[mealTime] || 0));
        const minimum = Math.round(target * 0.8);
        const maximum = Math.round(target * 1.2);
        const calorieSuitable = finalCalories >= minimum && finalCalories <= maximum;
        const allergyConflicts = findAllergyConflicts(patient.allergies || "", finalIngredients.join(", "));
        const preferredCategories = getConditionCategories(patient.medical_conditions || "");
        const conditionSuitable = preferredCategories.length === 0 || preferredCategories.includes(selectedBaseMeal.category);
        const reasons = [];

        if (calorieSuitable) {
            reasons.push("The customized calories are within the selected meal-time range (" + minimum + "–" + maximum + " kcal).");
        } else {
            reasons.push("The customized calories are outside the selected meal-time range (" + minimum + "–" + maximum + " kcal).");
        }

        if (allergyConflicts.length > 0) {
            reasons.push("Possible allergy conflict: " + allergyConflicts.join(", ") + ".");
        } else {
            reasons.push("No recorded allergy conflict was detected in the final ingredient list.");
        }

        if (preferredCategories.length === 0) {
            reasons.push("No category-specific medical-condition rule was detected.");
        } else if (conditionSuitable) {
            reasons.push("The selected category matches the patient's recorded condition.");
        } else {
            reasons.push("Preferred category based on the recorded condition: " + preferredCategories.join(" or ") + ".");
        }

        let status = "review";

        if (allergyConflicts.length > 0) {
            status = "blocked";
        } else if (calorieSuitable && conditionSuitable) {
            status = "recommended";
        }

        return {
            status: status,
            targetCalories: target,
            finalCalories: finalCalories,
            finalIngredients: finalIngredients,
            allergyConflicts: allergyConflicts,
            reasons: reasons
        };
    }

    function displayEvaluation(evaluation) {
        const result = document.getElementById("customSuitabilityResult");
        const reasons = document.getElementById("customSuitabilityReasons");
        const saveButton = document.getElementById("saveCustomMealBtn");

        if (!result || !reasons || !saveButton) {
            return;
        }

        result.classList.remove("is-recommended", "is-review", "is-blocked");
        result.classList.add("is-" + evaluation.status);
        reasons.replaceChildren();

        evaluation.reasons.forEach(function (reason) {
            const item = document.createElement("li");
            item.textContent = reason;
            reasons.appendChild(item);
        });

        if (evaluation.status === "recommended") {
            setText("customSuitabilityTitle", "Recommended");
            setText("customSuitabilitySummary", "This customized meal matches the current prototype recommendation rules.");
        } else if (evaluation.status === "blocked") {
            setText("customSuitabilityTitle", "Not Recommended");
            setText("customSuitabilitySummary", "Remove the possible allergen before saving this customized meal.");
        } else {
            setText("customSuitabilityTitle", "Nutritionist Review Required");
            setText("customSuitabilitySummary", "The customized meal can be saved, but the nutritionist should review the warnings.");
        }

        saveButton.disabled = evaluation.status === "blocked";
        setSaveMessage("");
    }

    async function saveCustomizedMeal(event) {
        event.preventDefault();

        const form = event.currentTarget;

        if (!form.reportValidity()) {
            return;
        }

        if (!latestEvaluation) {
            setSaveMessage("Please check custom meal suitability first.");
            return;
        }

        if (latestEvaluation.status === "blocked") {
            setSaveMessage("This custom meal cannot be saved while an allergy conflict is detected.");
            return;
        }

        const saveButton = document.getElementById("saveCustomMealBtn");
        const payload = {
            patient_id: document.getElementById("customPatient").value,
            base_food_id: selectedBaseMeal.foodId,
            meal_time: document.getElementById("customMealTime").value,
            custom_name: document.getElementById("customMealName").value.trim(),
            removed_ingredients: document.getElementById("customRemoveIngredients").value.trim(),
            added_ingredients: document.getElementById("customAddIngredients").value.trim(),
            portion_size: document.getElementById("customPortionSize").value.trim(),
            final_calories: Number(document.getElementById("customFinalCalories").value || 0),
            preparation_notes: document.getElementById("customPreparationNotes").value.trim()
        };

        saveButton.disabled = true;
        setSaveMessage("Saving customized meal...");

        try {
            const response = await fetch("save_custom_meal.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify(payload)
            });

            const responseText = await response.text();
            let result;

            try {
                result = JSON.parse(responseText);
            } catch (error) {
                throw new Error("The server returned an invalid response.");
            }

            if (!response.ok || !result.success) {
                throw new Error(result.message || "Customized meal could not be saved.");
            }

            setSaveMessage("Customized meal saved and recommended successfully.");

            setTimeout(function () {
                closeModal("customizeModal");
                resetCustomizationModal();
            }, 700);
        } catch (error) {
            setSaveMessage(error.message);
            saveButton.disabled = latestEvaluation.status === "blocked";
        }
    }

    function buildFinalIngredients() {
        const baseIngredients = selectedBaseMeal
            ? splitIngredientText(selectedBaseMeal.ingredients)
            : [];
        const removeItems = splitIngredientText(document.getElementById("customRemoveIngredients")?.value || "");
        const addItems = splitIngredientText(document.getElementById("customAddIngredients")?.value || "");
        const removeNormalized = removeItems.map(normalizeText);

        const remaining = baseIngredients.filter(function (ingredient) {
            const normalizedIngredient = normalizeText(ingredient);

            return !removeNormalized.some(function (removeItem) {
                return removeItem && (
                    normalizedIngredient.includes(removeItem) ||
                    removeItem.includes(normalizedIngredient)
                );
            });
        });

        const combined = remaining.concat(addItems);
        const unique = [];
        const seen = new Set();

        combined.forEach(function (ingredient) {
            const key = normalizeText(ingredient);

            if (key && !seen.has(key)) {
                seen.add(key);
                unique.push(ingredient.trim());
            }
        });

        return unique;
    }

    function splitIngredientText(value) {
        return String(value || "")
            .split(/,|;|\n/)
            .map(function (item) {
                return item.trim();
            })
            .filter(Boolean);
    }

    function findAllergyConflicts(allergyText, ingredientText) {
        const allergyValue = normalizeText(allergyText);
        const ingredientValue = normalizeText(ingredientText);
        const emptyValues = [
            "",
            "none",
            "none recorded",
            "no",
            "nil",
            "n a",
            "not applicable",
            "no known allergies"
        ];

        if (emptyValues.includes(allergyValue)) {
            return [];
        }

        const commonAllergens = [
            "peanut", "nut", "almond", "cashew", "walnut",
            "shellfish", "shrimp", "prawn", "crab", "lobster",
            "fish", "egg", "milk", "dairy", "soy", "soya",
            "wheat", "gluten", "sesame"
        ];

        return [...new Set(commonAllergens.filter(function (allergen) {
            return allergyValue.includes(allergen) && ingredientValue.includes(allergen);
        }))];
    }

    function getConditionCategories(value) {
        const condition = normalizeText(value);
        const categories = [];

        if (
            condition.includes("diabetes") ||
            condition.includes("diabetic") ||
            condition.includes("high blood sugar") ||
            condition.includes("hypergly")
        ) {
            categories.push("Low Sugar Meals");
        }

        if (
            condition.includes("hypertension") ||
            condition.includes("high blood pressure") ||
            condition.includes("heart") ||
            condition.includes("cardiac") ||
            condition.includes("cardiovascular") ||
            condition.includes("high cholesterol")
        ) {
            categories.push("Low Sodium & Low Fat Meals");
        }

        if (
            condition.includes("dysphagia") ||
            condition.includes("swallow") ||
            condition.includes("chew") ||
            condition.includes("dental") ||
            condition.includes("post surgery") ||
            condition.includes("post-surgery")
        ) {
            categories.push("Soft Meals", "Semi-Liquid Meals");
        }

        return [...new Set(categories)];
    }

    function invalidateEvaluation() {
        latestEvaluation = null;

        const result = document.getElementById("customSuitabilityResult");
        const reasons = document.getElementById("customSuitabilityReasons");
        const saveButton = document.getElementById("saveCustomMealBtn");

        if (result) {
            result.classList.remove("is-recommended", "is-review", "is-blocked");
        }

        if (reasons) {
            reasons.replaceChildren();
        }

        if (saveButton) {
            saveButton.disabled = true;
        }

        setText("customSuitabilityTitle", "Suitability Result");
        setText(
            "customSuitabilitySummary",
            "Complete the required information and run the suitability check."
        );
        setSaveMessage("");
    }

    function resetCustomizationModal() {
        const form = document.getElementById("mealCustomizationForm");

        if (form) {
            form.reset();
        }

        selectedBaseMeal = null;
        latestEvaluation = null;
        populatePatients();
        populateCategories();
        populateBaseMeals("");
        updatePatientSummary();
        displayBaseMeal(null);
        invalidateEvaluation();
    }

    function normalizeEmptyDisplay(value, fallback) {
        const text = String(value || "").trim();
        const emptyValues = ["", "none", "none recorded", "no", "nil"];

        return emptyValues.includes(text.toLowerCase()) ? fallback : text;
    }

    function normalizeText(value) {
        return String(value || "")
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, " ")
            .replace(/\s+/g, " ")
            .trim();
    }

    function renderIngredientList(list, ingredients) {
        if (!list) {
            return;
        }

        list.replaceChildren();

        if (ingredients.length === 0) {
            const item = document.createElement("li");
            item.textContent = "No ingredients stated";
            list.appendChild(item);
            return;
        }

        ingredients.forEach(function (ingredient) {
            const item = document.createElement("li");
            item.textContent = ingredient;
            list.appendChild(item);
        });
    }

    function setText(id, value) {
        const element = document.getElementById(id);

        if (element) {
            element.textContent = value;
        }
    }

    function setValue(id, value) {
        const element = document.getElementById(id);

        if (element) {
            element.value = value ?? "";
        }
    }

    function setSaveMessage(message) {
        setText("customSaveMessage", message);
    }
})();
