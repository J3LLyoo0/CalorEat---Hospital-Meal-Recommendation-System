document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("registerPatientForm");

    if (!form) {
        return;
    }

    const dateOfBirthInput =
        document.getElementById("patientDateOfBirth");

    const heightInput =
        document.getElementById("patientHeight");

    const weightInput =
        document.getElementById("patientWeight");

    const activityInput =
        document.getElementById("patientActivityLevel");

    const genderInput =
        document.getElementById("patientGender");

    const ageOutput =
        document.getElementById("calculatedPatientAge");

    const calorieOutput =
        document.getElementById("dailyCalorieResult");

    const messageOutput =
        document.getElementById("calorieCalculationMessage");

    const calorieHiddenInput =
        document.getElementById("dailyCalorieInput");

    const activityFactors = {
        "Sedentary": 1.2,
        "Lightly Active": 1.375,
        "Moderately Active": 1.55,
        "Very Active": 1.725,
        "Extremely Active": 1.9
    };

    function calculateAge(dateOfBirth) {
        const birthDate = new Date(dateOfBirth + "T00:00:00");
        const today = new Date();

        if (Number.isNaN(birthDate.getTime())) {
            return null;
        }

        let age =
            today.getFullYear() - birthDate.getFullYear();

        const monthDifference =
            today.getMonth() - birthDate.getMonth();

        const birthdayHasNotOccurred =
            monthDifference < 0 ||
            (
                monthDifference === 0 &&
                today.getDate() < birthDate.getDate()
            );

        if (birthdayHasNotOccurred) {
            age--;
        }

        return age;
    }

    function calculateDailyCalories() {
        const dateOfBirth = dateOfBirthInput.value;
        const height = Number(heightInput.value);
        const weight = Number(weightInput.value);
        const activityLevel = activityInput.value;
        const gender = genderInput.value;

        const age = calculateAge(dateOfBirth);

        if (age !== null && age >= 0) {
            ageOutput.textContent = "Age: " + age;
        } else {
            ageOutput.textContent = "Age: —";
        }

        const hasCompleteInformation =
            age !== null &&
            age >= 0 &&
            height > 0 &&
            weight > 0 &&
            activityFactors[activityLevel] &&
            gender !== "";

        if (!hasCompleteInformation) {
            calorieOutput.textContent = "— kcal/day";
            calorieHiddenInput.value = "";

            messageOutput.textContent =
                "Complete the required information to calculate.";

            return;
        }

        let basalMetabolicRate;

        if (gender === "Male") {
            basalMetabolicRate =
                (10 * weight) +
                (6.25 * height) -
                (5 * age) +
                5;
        } else {
            basalMetabolicRate =
                (10 * weight) +
                (6.25 * height) -
                (5 * age) -
                161;
        }

        const dailyCalories = Math.round(
            basalMetabolicRate *
            activityFactors[activityLevel]
        );

        calorieOutput.textContent =
            dailyCalories.toLocaleString() + " kcal/day";

        calorieHiddenInput.value = dailyCalories;

        messageOutput.textContent =
            "Calculated using the Mifflin–St Jeor equation.";
    }

    [
        dateOfBirthInput,
        heightInput,
        weightInput,
        activityInput,
        genderInput
    ].forEach(function (field) {
        field.addEventListener("input", calculateDailyCalories);
        field.addEventListener("change", calculateDailyCalories);
    });
});