<!-- Meal Details Modal -->
<div class="modal-overlay" id="mealModal">
    <div
        class="modal-card meal-details-card"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modalMealName"
    >
        <button
            class="modal-close meal-details-close"
            type="button"
            data-close="mealModal"
            aria-label="Close meal information"
        >
            X
        </button>

        <h2 class="meal-details-title" id="modalMealName">
            Meal Name
        </h2>

        <div class="meal-details-layout">
            <!-- Left side: meal image -->
            <section class="meal-visual-panel">
                <div class="meal-details-image-box">
                    <img
                        id="modalMealImage"
                        src=""
                        alt="Selected meal"
                    >
                </div>

                <div class="meal-meta-row">
                    <span id="modalMealCategory">
                        Meal Category
                    </span>

                    <strong id="modalMealCalories">
                        0 kcal
                    </strong>
                </div>
            </section>

            <!-- Right side: information -->
            <section class="meal-information-panel">
                <div class="meal-information-box ingredients-information-box">
                    <h3>Core Ingredients Used</h3>

                    <ul
                        class="meal-ingredients-list"
                        id="modalMealIngredientsList"
                    >
                        <li>No ingredients stated</li>
                    </ul>
                </div>

                <div class="meal-information-box description-information-box">
                    <h3>Description</h3>

                    <p id="modalMealDescription">
                        No description available.
                    </p>
                </div>

                <?php if (($currentRole ?? "") === "nutritionist"): ?>
                    <div class="meal-patient-selection">
                        <div class="meal-selection-grid">
                            <label class="meal-selection-field">
                                <span>Select Patient</span>

                                <select
                                    id="mealPatientSelect"
                                    aria-label="Select patient"
                                >
                                    <option value="">
                                        Select a patient
                                    </option>
                                </select>
                            </label>

                            <label class="meal-selection-field">
                                <span>Meal Time</span>

                                <select
                                    id="mealTimeSelect"
                                    aria-label="Select meal time"
                                >
                                    <option value="">
                                        Select meal time
                                    </option>

                                    <option value="Breakfast">
                                        Breakfast
                                    </option>

                                    <option value="Morning Snack">
                                        Morning Snack
                                    </option>

                                    <option value="Lunch">
                                        Lunch
                                    </option>

                                    <option value="Afternoon Snack">
                                        Afternoon Snack
                                    </option>

                                    <option value="Dinner">
                                        Dinner
                                    </option>

                                    <option value="Supper">
                                        Supper
                                    </option>
                                </select>
                            </label>
                        </div>

                        <button type="button" class="check-suitability-button" id="checkMealSuitabilityBtn">
                            Check Meal Suitability
                        </button>

                        <section
                            class="meal-recommendation-result"
                            id="mealRecommendationResult"
                            aria-live="polite"
                        >
                        <h4 id="recommendationResultTitle">
                            Recommendation Result
                        </h4>

                        <p id="recommendationResultSummary">
                            Select a patient and meal time to evaluate this meal.
                        </p>

                        <div class="recommendation-calorie-grid" id="recommendationCalorieGrid" hidden>
                            <div>
                                <span>Target Calories</span>
                                <strong id="recommendationTargetCalories">
                                    —
                                </strong>
                            </div>

                            <div>
                                <span>Meal Calories</span>
                                <strong id="recommendationMealCalories">
                                    —
                                </strong>
                            </div>

                            <div>
                                <span>Suitable Range</span>
                                <strong id="recommendationCalorieRange">
                                    —
                                </strong>
                            </div>
                        </div>

                        <ul class="recommendation-reasons" id="recommendationReasons"></ul>

                        <div class="suggested-meal-box" id="suggestedMealBox" hidden>
                            <span>Suggested Alternative</span>

                            <strong id="suggestedMealName">
                                —
                            </strong>

                            <p id="suggestedMealInformation"></p>

                                <button type="button" id="viewSuggestedMealBtn">
                                    View Suggested Meal
                                </button>
                            </div>
                        </section>

                        <button type="button" class="recommend-meal-button" id="recommendMealBtn" disabled>
                            Recommend This Meal
                        </button>

                        <p
                            class="meal-assignment-message"
                            id="mealActionMessage"
                            aria-live="polite"
                        ></p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</div>