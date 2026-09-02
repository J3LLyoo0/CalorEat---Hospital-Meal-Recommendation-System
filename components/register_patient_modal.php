<!-- Register Patient Modal -->
<div class="modal-overlay" id="patientModal">
    <div
        class="patient-register-card"
        role="dialog"
        aria-modal="true"
        aria-labelledby="patientModalTitle"
    >
        <button
            class="modal-close patient-modal-close"
            type="button"
            data-close="patientModal"
            aria-label="Close patient registration"
        >
            ×
        </button>

        <h2 id="patientModalTitle">Register New Patient</h2>

        <form
            id="registerPatientForm"
            action="register_patient.php"
            method="POST"
        >
            <!-- Basic Patient Information -->
            <div class="patient-basic-grid">
                <label class="patient-field">
                    <span>
                        First Name
                        <strong class="required-star">*</strong>
                    </span>

                    <input
                        type="text"
                        name="first_name"
                        placeholder="Enter first name"
                        required
                    >
                </label>

                <label class="patient-field">
                    <span>Last Name</span>

                    <input
                        type="text"
                        name="last_name"
                        placeholder="Enter last name"
                    >
                </label>

                <label class="patient-field">
                    <span>
                        Contact Number
                        <strong class="required-star">*</strong>
                    </span>

                    <input
                        type="tel"
                        name="contact_number"
                        placeholder="Example: 0123456789"
                        inputmode="tel"
                        required
                    >
                </label>

                <label class="patient-field">
                    <span>
                        I.C. Number
                        <strong class="required-star">*</strong>
                    </span>

                    <input
                        type="text"
                        name="ic_number"
                        placeholder="Example: 010203101234"
                        inputmode="numeric"
                        maxlength="12"
                        pattern="[0-9]{12}"
                        required
                    >
                </label>

                <label class="patient-field patient-email-field">
                    <span>Email Address — Optional</span>

                    <input
                        type="email"
                        name="email"
                        placeholder="Enter email address if available"
                    >
                </label>
            </div>

            <!-- Calorie Calculation Section -->
            <section class="patient-calorie-box">
                <div class="calorie-input-area">
                    <h3>Daily Calorie Calculation</h3>

                    <div class="calorie-fields-grid">
                        <label class="patient-field">
                            <span>
                                Date of Birth
                                <strong class="required-star">*</strong>
                            </span>

                            <input
                                type="date"
                                id="patientDateOfBirth"
                                name="date_of_birth"
                                max="<?php echo date('Y-m-d'); ?>"
                                required
                            >
                        </label>

                        <label class="patient-field">
                            <span>
                                Height (cm)
                                <strong class="required-star">*</strong>
                            </span>

                            <input
                                type="number"
                                id="patientHeight"
                                name="height"
                                min="50"
                                max="250"
                                step="0.01"
                                placeholder="Example: 170"
                                required
                            >
                        </label>

                        <label class="patient-field">
                            <span>
                                Weight (kg)
                                <strong class="required-star">*</strong>
                            </span>

                            <input
                                type="number"
                                id="patientWeight"
                                name="weight"
                                min="10"
                                max="400"
                                step="0.01"
                                placeholder="Example: 65"
                                required
                            >
                        </label>

                        <label class="patient-field calorie-wide-field">
                            <span>
                                Activity Level
                                <strong class="required-star">*</strong>
                            </span>

                            <select
                                id="patientActivityLevel"
                                name="activity_level"
                                required
                            >
                                <option value="">Select activity level</option>
                                <option value="Sedentary">
                                    Sedentary
                                </option>
                                <option value="Lightly Active">
                                    Lightly Active
                                </option>
                                <option value="Moderately Active">
                                    Moderately Active
                                </option>
                                <option value="Very Active">
                                    Very Active
                                </option>
                                <option value="Extremely Active">
                                    Extremely Active
                                </option>
                            </select>
                        </label>

                        <label class="patient-field">
                            <span>
                                Gender
                                <strong class="required-star">*</strong>
                            </span>

                            <select
                                id="patientGender"
                                name="gender"
                                required
                            >
                                <option value="">Select gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </label>
                    </div>
                </div>

                <aside class="calorie-result-area">
                    <h3>Estimated Daily Calorie Intake</h3>

                    <p id="calculatedPatientAge">
                        Age: —
                    </p>

                    <strong id="dailyCalorieResult">
                        — kcal/day
                    </strong>

                    <p id="calorieCalculationMessage">
                        Complete the required information to calculate.
                    </p>

                    <input
                        type="hidden"
                        id="dailyCalorieInput"
                        name="daily_calorie_intake"
                        value=""
                    >
                </aside>
            </section>

            <!-- Health Information -->
            <section class="patient-health-box">
                <label class="patient-field">
                    <span>Allergies / Dietary Restrictions</span>

                    <textarea
                        name="allergies"
                        placeholder="State the patient's allergies or dietary restrictions"
                    ></textarea>
                </label>

                <label class="patient-field">
                    <span>Medical Conditions</span>

                    <textarea
                        name="medical_conditions"
                        placeholder="State the patient's medical conditions"
                    ></textarea>
                </label>
            </section>

            <div class="patient-register-button-row">
                <button
                    class="patient-register-submit"
                    type="submit"
                >
                    Register Patient
                </button>
            </div>
        </form>
    </div>
</div>