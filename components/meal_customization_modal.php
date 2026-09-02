<!-- Meal Customization Modal -->
<div class="modal-overlay" id="customizeModal">
    <div
        class="modal-card meal-customization-card"
        role="dialog"
        aria-modal="true"
        aria-labelledby="mealCustomizationTitle"
    >
        <button
            class="modal-close meal-customization-close"
            type="button"
            data-close="customizeModal"
            aria-label="Close meal customization"
        >
            X
        </button>

        <h2 id="mealCustomizationTitle">Patient Meal Customization</h2>

        <form id="mealCustomizationForm">
            <div class="meal-customization-layout">
                <section class="meal-customization-panel">
                    <h3>Patient and Base Meal</h3>

                    <div class="meal-customization-grid two-columns">
                        <label class="meal-customization-field">
                            <span>Select Patient *</span>
                            <select id="customPatient" required>
                                <option value="">Select a patient</option>
                            </select>
                        </label>

                        <label class="meal-customization-field">
                            <span>Meal Time *</span>
                            <select id="customMealTime" required>
                                <option value="">Select meal time</option>
                                <option value="Breakfast">Breakfast</option>
                                <option value="Morning Snack">Morning Snack</option>
                                <option value="Lunch">Lunch</option>
                                <option value="Afternoon Snack">Afternoon Snack</option>
                                <option value="Dinner">Dinner</option>
                                <option value="Supper">Supper</option>
                            </select>
                        </label>
                    </div>

                    <div class="custom-patient-summary" id="customPatientSummary">
                        <div>
                            <span>Daily Calorie Target</span>
                            <strong id="customDailyCalories">— kcal/day</strong>
                        </div>
                        <div>
                            <span>Selected Meal Target</span>
                            <strong id="customMealTarget">— kcal</strong>
                        </div>
                        <div>
                            <span>Medical Conditions</span>
                            <strong id="customPatientCondition">Not selected</strong>
                        </div>
                        <div>
                            <span>Allergies / Restrictions</span>
                            <strong id="customPatientAllergy">Not selected</strong>
                        </div>
                    </div>

                    <div class="meal-customization-grid two-columns">
                        <label class="meal-customization-field">
                            <span>Meal Category *</span>
                            <select id="customCategory" required>
                                <option value="">Select category</option>
                            </select>
                        </label>

                        <label class="meal-customization-field">
                            <span>Base Meal *</span>
                            <select id="customBaseMeal" required disabled>
                                <option value="">Select base meal</option>
                            </select>
                        </label>
                    </div>

                    <div class="custom-base-meal-preview" id="customBaseMealPreview">
                        <div class="custom-base-image" id="customBaseImageBox">
                            <img id="customBaseImage" alt="Base meal">
                        </div>

                        <div class="custom-base-information">
                            <h4 id="customBaseName">Select a base meal</h4>
                            <p id="customBaseCalories">— kcal</p>

                            <span>Base Ingredients</span>
                            <ul id="customBaseIngredients">
                                <li>No base meal selected</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="meal-customization-panel">
                    <h3>Customize Meal</h3>

                    <label class="meal-customization-field">
                        <span>Custom Meal Name *</span>
                        <input
                            id="customMealName"
                            type="text"
                            maxlength="150"
                            placeholder="Example: Low-Sodium Chicken Pasta"
                            required
                        >
                    </label>

                    <div class="meal-customization-grid two-columns">
                        <label class="meal-customization-field">
                            <span>Ingredients to Remove</span>
                            <textarea
                                id="customRemoveIngredients"
                                placeholder="Separate ingredients using commas or new lines"
                            ></textarea>
                        </label>

                        <label class="meal-customization-field">
                            <span>Ingredients to Add</span>
                            <textarea
                                id="customAddIngredients"
                                placeholder="Separate ingredients using commas or new lines"
                            ></textarea>
                        </label>
                    </div>

                    <div class="meal-customization-grid two-columns">
                        <label class="meal-customization-field">
                            <span>Portion Size *</span>
                            <input
                                id="customPortionSize"
                                type="text"
                                maxlength="100"
                                placeholder="Example: 1 serving"
                                required
                            >
                        </label>

                        <label class="meal-customization-field">
                            <span>Final Estimated Calories *</span>
                            <input
                                id="customFinalCalories"
                                type="number"
                                min="1"
                                max="5000"
                                step="1"
                                placeholder="Example: 540"
                                required
                            >
                        </label>
                    </div>

                    <label class="meal-customization-field">
                        <span>Preparation Notes</span>
                        <textarea
                            id="customPreparationNotes"
                            placeholder="Example: Use less salt, blend until soft, avoid spicy seasoning"
                        ></textarea>
                    </label>

                    <button
                        class="custom-check-button"
                        id="checkCustomMealBtn"
                        type="button"
                    >
                        Check Custom Meal Suitability
                    </button>

                    <section
                        class="custom-suitability-result"
                        id="customSuitabilityResult"
                        aria-live="polite"
                    >
                        <h4 id="customSuitabilityTitle">Suitability Result</h4>
                        <p id="customSuitabilitySummary">
                            Complete the required information and run the suitability check.
                        </p>
                        <ul id="customSuitabilityReasons"></ul>
                    </section>

                    <button
                        class="custom-save-button"
                        id="saveCustomMealBtn"
                        type="submit"
                        disabled
                    >
                        Save and Recommend Customized Meal
                    </button>

                    <p
                        class="custom-save-message"
                        id="customSaveMessage"
                        aria-live="polite"
                    ></p>
                </section>
            </div>
        </form>
    </div>
</div>
