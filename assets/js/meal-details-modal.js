(function () {
    "use strict";

    let selectedMeal = null;
    let suggestedMeal = null;
    let latestEvaluation = null;
    let recommendationType = "manual";

    const mealTimeFactors = {
        "Breakfast": 0.20,
        "Morning Snack": 0.10,
        "Lunch": 0.30,
        "Afternoon Snack": 0.10,
        "Dinner": 0.25,
        "Supper": 0.05
    };

    document.addEventListener("DOMContentLoaded", function () {
        const patientSelect =
            document.getElementById("mealPatientSelect");

        const mealTimeSelect =
            document.getElementById("mealTimeSelect");

        const checkButton =
            document.getElementById(
                "checkMealSuitabilityBtn"
            );

        const recommendButton =
            document.getElementById("recommendMealBtn");

        const viewSuggestedButton =
            document.getElementById(
                "viewSuggestedMealBtn"
            );

        populatePatientSelect(patientSelect);

        document
            .querySelectorAll(".meal-card")
            .forEach(function (card) {
                card.addEventListener("click", function () {
                    selectedMeal =
                        readMealCardData(card);

                    recommendationType = "manual";
                    suggestedMeal = null;
                    latestEvaluation = null;

                    displayMeal(selectedMeal);
                    resetRecommendationResult();

                    if (patientSelect) {
                        patientSelect.value = "";
                    }

                    if (mealTimeSelect) {
                        mealTimeSelect.value = "";
                    }

                    openModal("mealModal");
                });
            });

        if (checkButton) {
            checkButton.addEventListener(
                "click",
                function () {
                    runSuitabilityCheck();
                }
            );
        }

        if (viewSuggestedButton) {
            viewSuggestedButton.addEventListener(
                "click",
                function () {
                    if (!suggestedMeal) {
                        return;
                    }

                    selectedMeal = suggestedMeal;
                    recommendationType = "automatic";
                    suggestedMeal = null;

                    displayMeal(selectedMeal);
                    runSuitabilityCheck();
                }
            );
        }

        if (recommendButton) {
            recommendButton.addEventListener(
                "click",
                saveRecommendation
            );
        }

        if (patientSelect) {
            patientSelect.addEventListener(
                "change",
                resetEvaluationOnly
            );
        }

        if (mealTimeSelect) {
            mealTimeSelect.addEventListener(
                "change",
                resetEvaluationOnly
            );
        }
    });

    function readMealCardData(card) {
        return {
            foodId: String(card.dataset.foodId || ""),
            name: String(
                card.dataset.name || "Unnamed Meal"
            ),
            category: String(
                card.dataset.category || "Uncategorized"
            ),
            calories: String(
                card.dataset.calories || "Not stated"
            ),
            kcalValue: Number(
                card.dataset.kcal || 0
            ),
            image: String(card.dataset.image || ""),
            ingredients: String(
                card.dataset.ingredients || ""
            ),
            description: String(
                card.dataset.description ||
                "No description available."
            )
        };
    }

    function displayMeal(meal) {
        setText("modalMealName", meal.name);
        setText("modalMealCategory", meal.category);

        setText(
            "modalMealCalories",
            meal.kcalValue > 0
                ? meal.kcalValue.toLocaleString("en-MY") +
                    " kcal"
                : meal.calories
        );

        setText(
            "modalMealDescription",
            meal.description ||
            "No description available."
        );

        displayMealImage(meal);
        displayIngredientList(meal.ingredients);
    }

    function displayMealImage(meal) {
        const image =
            document.getElementById("modalMealImage");

        if (!image) {
            return;
        }

        image.classList.remove("is-missing");
        image.src = meal.image;
        image.alt = meal.name;

        image.onerror = function () {
            image.classList.add("is-missing");
        };
    }

    function displayIngredientList(text) {
        const list =
            document.getElementById(
                "modalMealIngredientsList"
            );

        if (!list) {
            return;
        }

        list.replaceChildren();

        const ingredients = String(text)
            .split(/,|;|\n/)
            .map(function (value) {
                return value.trim();
            })
            .filter(Boolean);

        if (ingredients.length === 0) {
            const item =
                document.createElement("li");

            item.textContent =
                "No ingredients stated";

            list.appendChild(item);
            return;
        }

        ingredients.forEach(function (ingredient) {
            const item =
                document.createElement("li");

            item.textContent = ingredient;
            list.appendChild(item);
        });
    }

    function populatePatientSelect(select) {
        if (!select) {
            return;
        }

        const patients = getPatients();

        select.replaceChildren();

        const firstOption =
            document.createElement("option");

        firstOption.value = "";

        if (patients.length === 0) {
            firstOption.textContent =
                "No registered patients";

            select.appendChild(firstOption);
            select.disabled = true;
            return;
        }

        firstOption.textContent =
            "Select a patient";

        select.appendChild(firstOption);
        select.disabled = false;

        patients.forEach(function (patient) {
            const option =
                document.createElement("option");

            option.value =
                String(patient.patient_id || "");

            option.textContent =
                getPatientName(patient);

            select.appendChild(option);
        });
    }

    function runSuitabilityCheck() {
        const patientSelect =
            document.getElementById(
                "mealPatientSelect"
            );

        const mealTimeSelect =
            document.getElementById(
                "mealTimeSelect"
            );

        const message =
            document.getElementById(
                "mealActionMessage"
            );

        if (message) {
            message.textContent = "";
        }

        if (!selectedMeal) {
            showMessage("Please select a meal.");
            return;
        }

        if (
            !patientSelect ||
            patientSelect.value === ""
        ) {
            showMessage("Please select a patient.");
            patientSelect?.focus();
            return;
        }

        if (
            !mealTimeSelect ||
            mealTimeSelect.value === ""
        ) {
            showMessage("Please select a meal time.");
            mealTimeSelect?.focus();
            return;
        }

        const patient = findPatient(
            patientSelect.value
        );

        if (!patient) {
            showMessage(
                "The selected patient could not be found."
            );
            return;
        }

        latestEvaluation = evaluateMeal(
            selectedMeal,
            patient,
            mealTimeSelect.value
        );

        suggestedMeal = findSuggestedMeal(
            selectedMeal,
            patient,
            mealTimeSelect.value
        );

        displayEvaluation(
            latestEvaluation,
            suggestedMeal
        );
    }

    function evaluateMeal(
        meal,
        patient,
        mealTime
    ) {
        const dailyCalories = Number(
            patient.daily_calorie_intake || 0
        );

        const factor =
            mealTimeFactors[mealTime] || 0;

        const targetCalories = Math.round(
            dailyCalories * factor
        );

        const minimumCalories = Math.round(
            targetCalories * 0.8
        );

        const maximumCalories = Math.round(
            targetCalories * 1.2
        );

        const mealCalories =
            Number(meal.kcalValue || 0);

        const calorieSuitable =
            mealCalories >= minimumCalories &&
            mealCalories <= maximumCalories;

        const allergyConflicts =
            findAllergyConflicts(
                patient.allergies || "",
                meal.ingredients || ""
            );

        const preferredCategories =
            getConditionCategories(
                patient.medical_conditions || ""
            );

        const conditionSuitable =
            preferredCategories.length === 0 ||
            preferredCategories.includes(
                meal.category
            );

        const reasons = [];

        if (calorieSuitable) {
            reasons.push(
                "The meal calories are within the selected meal-time range."
            );
        } else if (mealCalories < minimumCalories) {
            reasons.push(
                "The meal is below the selected meal-time calorie range."
            );
        } else {
            reasons.push(
                "The meal is above the selected meal-time calorie range."
            );
        }

        if (allergyConflicts.length > 0) {
            reasons.push(
                "Possible allergy conflict: " +
                allergyConflicts.join(", ") +
                "."
            );
        } else {
            reasons.push(
                "No recorded allergy conflict was detected."
            );
        }

        if (preferredCategories.length === 0) {
            reasons.push(
                "No category-specific medical-condition rule was detected."
            );
        } else if (conditionSuitable) {
            reasons.push(
                "The meal category matches the patient's recorded condition."
            );
        } else {
            reasons.push(
                "Preferred category based on the recorded condition: " +
                preferredCategories.join(" or ") +
                "."
            );
        }

        let status = "review";

        if (allergyConflicts.length > 0) {
            status = "blocked";
        } else if (
            calorieSuitable &&
            conditionSuitable
        ) {
            status = "recommended";
        }

        return {
            status: status,
            targetCalories: targetCalories,
            minimumCalories: minimumCalories,
            maximumCalories: maximumCalories,
            mealCalories: mealCalories,
            calorieSuitable: calorieSuitable,
            allergyConflicts: allergyConflicts,
            conditionSuitable: conditionSuitable,
            preferredCategories: preferredCategories,
            reasons: reasons
        };
    }

    function displayEvaluation(
        evaluation,
        alternative
    ) {
        const result =
            document.getElementById(
                "mealRecommendationResult"
            );

        const title =
            document.getElementById(
                "recommendationResultTitle"
            );

        const summary =
            document.getElementById(
                "recommendationResultSummary"
            );

        const calorieGrid =
            document.getElementById(
                "recommendationCalorieGrid"
            );

        const reasonList =
            document.getElementById(
                "recommendationReasons"
            );

        const suggestedBox =
            document.getElementById(
                "suggestedMealBox"
            );

        const recommendButton =
            document.getElementById(
                "recommendMealBtn"
            );

        result.classList.remove(
            "is-recommended",
            "is-review",
            "is-blocked"
        );

        if (evaluation.status === "recommended") {
            result.classList.add("is-recommended");
            title.textContent = "Recommended";
            summary.textContent =
                "This meal matches the current prototype recommendation rules.";
        } else if (
            evaluation.status === "blocked"
        ) {
            result.classList.add("is-blocked");
            title.textContent = "Not Recommended";
            summary.textContent =
                "A possible allergy conflict was detected.";
        } else {
            result.classList.add("is-review");
            title.textContent =
                "Nutritionist Review Required";

            summary.textContent =
                "This meal can still be selected manually, but a closer match may be available.";
        }

        calorieGrid.hidden = false;

        setText(
            "recommendationTargetCalories",
            formatCalories(
                evaluation.targetCalories
            )
        );

        setText(
            "recommendationMealCalories",
            formatCalories(
                evaluation.mealCalories
            )
        );

        setText(
            "recommendationCalorieRange",
            evaluation.minimumCalories
                .toLocaleString("en-MY") +
                "–" +
                evaluation.maximumCalories
                    .toLocaleString("en-MY") +
                " kcal"
        );

        reasonList.replaceChildren();

        evaluation.reasons.forEach(function (reason) {
            const item =
                document.createElement("li");

            item.textContent = reason;
            reasonList.appendChild(item);
        });

        if (
            alternative &&
            evaluation.status !== "recommended"
        ) {
            suggestedBox.hidden = false;

            setText(
                "suggestedMealName",
                alternative.name
            );

            setText(
                "suggestedMealInformation",
                alternative.category +
                " · " +
                formatCalories(
                    alternative.kcalValue
                )
            );
        } else {
            suggestedBox.hidden = true;
        }

        /*
         * Allergy conflict blocks recommendation.
         * Calorie/category warnings can be overridden
         * by the nutritionist.
         */
        recommendButton.disabled =
            evaluation.status === "blocked";
    }

    function findSuggestedMeal(
        currentMeal,
        patient,
        mealTime
    ) {
        const allMeals = flattenMeals();

        const evaluatedMeals = allMeals
            .filter(function (meal) {
                return String(meal.foodId) !==
                    String(currentMeal.foodId);
            })
            .map(function (meal) {
                const evaluation =
                    evaluateMeal(
                        meal,
                        patient,
                        mealTime
                    );

                let score = Math.abs(
                    evaluation.mealCalories -
                    evaluation.targetCalories
                );

                if (
                    evaluation.allergyConflicts
                        .length > 0
                ) {
                    score += 100000;
                }

                if (
                    !evaluation.conditionSuitable
                ) {
                    score += 1000;
                }

                if (
                    !evaluation.calorieSuitable
                ) {
                    score += 300;
                }

                return {
                    meal: meal,
                    evaluation: evaluation,
                    score: score
                };
            })
            .filter(function (item) {
                return (
                    item.evaluation
                        .allergyConflicts
                        .length === 0
                );
            })
            .sort(function (first, second) {
                return first.score - second.score;
            });

        return evaluatedMeals.length > 0
            ? evaluatedMeals[0].meal
            : null;
    }

    function flattenMeals() {
        const groups =
            window.CALOREAT_MEALS || {};

        const meals = [];

        Object.entries(groups).forEach(
            function (entry) {
                const category = entry[0];
                const categoryMeals =
                    Array.isArray(entry[1])
                        ? entry[1]
                        : [];

                categoryMeals.forEach(
                    function (meal) {
                        meals.push({
                            foodId: String(
                                meal.food_id || ""
                            ),
                            name: String(
                                meal.name ||
                                "Unnamed Meal"
                            ),
                            category: category,
                            calories: String(
                                meal.calories ||
                                "Not stated"
                            ),
                            kcalValue: Number(
                                meal.kcalValue || 0
                            ),
                            image: String(
                                meal.image || ""
                            ),
                            ingredients: String(
                                meal.ingredients || ""
                            ),
                            description: String(
                                meal.description ||
                                "No description available."
                            )
                        });
                    }
                );
            }
        );

        return meals;
    }

    function findAllergyConflicts(
        allergyText,
        ingredientText
    ) {
        const allergyValue =
            normalizeText(allergyText);

        const ingredientValue =
            normalizeText(ingredientText);

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
            "peanut",
            "nut",
            "almond",
            "cashew",
            "walnut",
            "shellfish",
            "shrimp",
            "prawn",
            "crab",
            "lobster",
            "fish",
            "egg",
            "milk",
            "dairy",
            "soy",
            "soya",
            "wheat",
            "gluten",
            "sesame"
        ];

        const detected = commonAllergens.filter(
            function (allergen) {
                return (
                    allergyValue.includes(allergen) &&
                    ingredientValue.includes(allergen)
                );
            }
        );

        return [...new Set(detected)];
    }

    function getConditionCategories(text) {
        const condition = normalizeText(text);
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
            categories.push(
                "Low Sodium & Low Fat Meals"
            );
        }

        if (
            condition.includes("dysphagia") ||
            condition.includes("swallow") ||
            condition.includes("chew") ||
            condition.includes("dental") ||
            condition.includes("post surgery") ||
            condition.includes("post-surgery")
        ) {
            categories.push("Soft Meals");
            categories.push("Semi-Liquid Meals");
        }

        return [...new Set(categories)];
    }

    async function saveRecommendation() {
        const patientSelect =
            document.getElementById(
                "mealPatientSelect"
            );

        const mealTimeSelect =
            document.getElementById(
                "mealTimeSelect"
            );

        const recommendButton =
            document.getElementById(
                "recommendMealBtn"
            );

        if (
            !selectedMeal ||
            !latestEvaluation ||
            !patientSelect ||
            !mealTimeSelect
        ) {
            showMessage(
                "Please check meal suitability first."
            );
            return;
        }

        recommendButton.disabled = true;
        showMessage("Saving recommendation...");

        const payload = {
            patient_id: patientSelect.value,
            food_id: selectedMeal.foodId,
            meal_time: mealTimeSelect.value,
            recommendation_type:
                recommendationType,
            target_calories:
                latestEvaluation.targetCalories,
            evaluation_result:
                latestEvaluation.status,
            recommendation_notes:
                latestEvaluation.reasons.join(" ")
        };

        try {
            const response = await fetch(
                "save_meal_recommendation.php",
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
                result = JSON.parse(responseText);
            } catch (error) {
                throw new Error(
                    "The server returned an invalid response."
                );
            }

            if (!response.ok || !result.success) {
                throw new Error(
                    result.message ||
                    "Recommendation could not be saved."
                );
            }

            showMessage(
                selectedMeal.name +
                " was recommended successfully."
            );

            setTimeout(function() {
                closeModal("mealModal");
                clearModalRecommendationModal();
            }, 700);

        } catch (error) {
            showMessage(error.message);

            recommendButton.disabled =
                latestEvaluation.status ===
                "blocked";
        }
    }

    function resetRecommendationResult() {
        latestEvaluation = null;
        suggestedMeal = null;

        const result =
            document.getElementById(
                "mealRecommendationResult"
            );

        result?.classList.remove(
            "is-recommended",
            "is-review",
            "is-blocked"
        );

        setText(
            "recommendationResultTitle",
            "Recommendation Result"
        );

        setText(
            "recommendationResultSummary",
            "Select a patient and meal time to evaluate this meal."
        );

        const calorieGrid =
            document.getElementById(
                "recommendationCalorieGrid"
            );

        const suggestedBox =
            document.getElementById(
                "suggestedMealBox"
            );

        const reasons =
            document.getElementById(
                "recommendationReasons"
            );

        const recommendButton =
            document.getElementById(
                "recommendMealBtn"
            );

        if (calorieGrid) {
            calorieGrid.hidden = true;
        }

        if (suggestedBox) {
            suggestedBox.hidden = true;
        }

        reasons?.replaceChildren();

        if (recommendButton) {
            recommendButton.disabled = true;
        }

        showMessage("");
    }

    function resetEvaluationOnly() {
        if (latestEvaluation) {
            resetRecommendationResult();
        }
    }

    function getPatients() {
        return Array.isArray(
            window.CALOREAT_PATIENTS
        )
            ? window.CALOREAT_PATIENTS
            : [];
    }

    function findPatient(patientId) {
        return getPatients().find(
            function (patient) {
                return String(
                    patient.patient_id
                ) === String(patientId);
            }
        );
    }

    function getPatientName(patient) {
        const name = String(
            (patient.first_name || "") +
            " " +
            (patient.last_name || "")
        ).trim();

        return name || "Unnamed Patient";
    }

    function normalizeText(value) {
        return String(value || "")
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, " ")
            .replace(/\s+/g, " ")
            .trim();
    }

    function formatCalories(value) {
        const number = Number(value || 0);

        return (
            Math.round(number)
                .toLocaleString("en-MY") +
            " kcal"
        );
    }

    function setText(id, value) {
        const element =
            document.getElementById(id);

        if (element) {
            element.textContent = value;
        }
    }

    function showMessage(message) {
        setText("mealActionMessage", message);
    }

    function clearMealRecommendationModal() {
        selectedMeal = null;
        suggestedMeal = null;
        latestEvaluation = null;
        recommendationType = "manual";

        const patientSelect = document.getElementById("mealPatientSelect");

        const mealTimeSelect = document.getElementById("mealTimeselect");

        const recommendButton = document.getElementById("recommendMealBtn");

        const mealImage = document.getElementById("modalMealImage");

        const ingredientList = document.getElementById("modalMealIngredientsList");

        if (patientSelect) {
            patientSelect.value = "";
        }

        if (mealTimeSelect) {
            mealTimeSelect.value = "";
        }

        if (recommendBUtton) {
            recommendButton.value = "";
        }

        resetRecommendationResult();

        setText("modalMealName", "Meal Name");
        setText("modalMealCategory", "Meal Category");
        setText("modalMealCalories", "-- kcal");
        setText("modalMealDescription", "No description available");

        if (mealImage) {
            mealImage.removeAttribute("src");
            mealImage.alt = "Selected meal";
            mealImage.classList.remove("is-missing");
        }

        if (ingredientList) {
            ingredientList.replaceChildren();

            const emptyIngredient = document.createElement("li");

            emptyIngredient.textContent = "No ingredients stated";

            ingredientList.appendChild(emptyIngredient);
        }

        const resultBox = document.getElementsById("mealRecommendationResult");
        const ingredientBox = document.getElementById("modalMealIngredientsList");
        const descriptionBox = document.getElementById("modalMealDescription");

        if(resultBox) {
            resultBox.scrollTop = 0;
        }

        if(ingredientBox) {
            ingredientBox.scrollTop = 0;
        }

        if(descriptionBox) {
            descriptionBox.scrollTop = 0;
        }
    }
})();