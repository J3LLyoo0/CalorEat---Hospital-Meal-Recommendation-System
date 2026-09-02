<!-- Patient Details Modal -->
<div class="modal-overlay" id="patientDetailsModal">
    <div
        class="modal-card patient-details-card" role="dialog" aria-modal="true" aria-labelledby="patientDetailsTitle">
        <button type="button" class="modal-close patient-details-close" data-close="patientDetailsModal" aria-label="Close patient information">X</button>

        <h2 id="patientDetailsTitle">Patient Information</h2>

        <!-- Patient heading remains visible in both modes -->
        <div class="patient-details-heading">
            <div class="patient-details-avatar" id="detailsPatientInitials">
                P
            </div>

            <div class="patient-details-heading-text">
                <h3 id="detailsPatientName">Patient Name</h3>

                <p>Patient ID:<strong id="detailsPatientId">—</strong></p>
            </div>

            <?php if (($currentRole ?? "") === "nutritionist"): ?>
                <button type="button" class="patient-edit-button" id="editPatientInformationBtn">
                    Edit Information
                </button>
            <?php endif; ?>
        </div>

        <!-- Edit Mode -->
        <form class="patient-edit-form" id="patientEditForm" hidden>
            <input type="hidden" id="editPatientId" name="patient_id">

            <section class="patient-information-section">
                <h4>Edit Personal Information</h4>

                <div class="patient-edit-grid">
                    <label class="patient-edit-field">
                        <span>First Name *</span>
                        <input type="text" id="editPatientFirstName" name="first_name" required>
                    </label>

                    <label class="patient-edit-field">
                        <span>Last Name</span>
                        <input type="text" id="editPatientLastName" name="last_name">
                    </label>

                    <label class="patient-edit-field">
                        <span>Contact Number *</span>
                        <input type="tel" id="editPatientContact" name="contact_number" required>
                    </label>

                    <label class="patient-edit-field">
                        <span>Email Address — Optional</span>
                        <input type="email" id="editPatientEmail" name="email">
                    </label>

                    <label class="patient-edit-field">
                        <span>Date of Birth *</span>
                        <input type="date" id="editPatientDob" name="date_of_birth" max="<?php echo date("Y-m-d"); ?>" required>
                    </label>

                    <label class="patient-edit-field">
                        <span>I.C. Number</span>
                        <input type="text" id="editPatientIc" disabled>
                    </label>

                    <label class="patient-edit-field">
                        <span>Gender *</span>
                        <select id="editPatientGender" name="gender" required>
                            <option value="">Select gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>

                        <small id="editPatientGenderMessage"></small>
                    </label>
                </div>
            </section>

            <section class="patient-information-section">
                <h4>Edit Calorie Information</h4>

                <div class="patient-edit-grid">
                    <label class="patient-edit-field">
                        <span>Height (cm) *</span>
                        <input type="number" id="editPatientHeight" name="height" min="50" max="250" step="0.01" required>
                    </label>

                    <label class="patient-edit-field">
                        <span>Weight (kg) *</span>
                        <input type="number" id="editPatientWeight" name="weight" min="10" max="400" step="0.01" required>
                    </label>

                    <label class="patient-edit-field">
                        <span>Activity Level *</span>
                        <select id="editPatientActivity" name="activity_level" required>
                            <option value="">Select activity level</option>
                            <option value="Sedentary">Sedentary</option>
                            <option value="Lightly Active">Lightly Active</option>
                            <option value="Moderately Active">Moderately Active</option>
                            <option value="Very Active">Very Active</option>
                            <option value="Extremely Active">Extremely Active</option>
                        </select>
                    </label>

                    <div class="patient-edit-calorie-preview">
                        <span>Updated Daily Calorie Target</span>

                        <strong id="editPatientCaloriePreview">— kcal/day</strong>
                    </div>
                </div>
            </section>

            <section class="patient-information-section">
                <h4>Edit Health Information</h4>

                <div class="patient-edit-health-grid">
                    <label class="patient-edit-field">
                        <span>Medical Conditions</span>

                        <textarea id="editPatientConditions" name="medical_conditions" placeholder="State the patient's medical conditions"></textarea>
                    </label>

                    <label class="patient-edit-field">
                        <span>Allergies / Dietary Restrictions</span>

                        <textarea id="editPatientAllergies" name="allergies" placeholder="State allergies or dietary restrictions"></textarea>
                    </label>
                </div>
            </section>

            <p class="patient-edit-status" id="patientEditStatus" aria-live="polite"></p>

            <div class="patient-edit-actions">
                <button type="button" class="patient-edit-cancel" id="cancelPatientEditBtn">Cancel</button>

                <button type="submit" class="patient-edit-save" id="savePatientInformationBtn">Save Information</button>
            </div>
        </form>

        <!-- Normal View Mode -->
        <div id="patientDetailsView">
            <section class="patient-information-section">
                <h4>Personal Information</h4>

                <div class="patient-information-grid">
                    <div class="patient-detail-item">
                        <span>Date of Birth</span>
                        <strong id="detailsPatientDob">Not provided</strong>
                    </div>

                    <div class="patient-detail-item">
                        <span>Age</span>
                        <strong id="detailsPatientAge">Not available</strong>
                    </div>

                    <div class="patient-detail-item">
                        <span>Gender</span>
                        <strong id="detailsPatientGender">Not provided</strong>
                    </div>

                    <div class="patient-detail-item">
                        <span>I.C. Number</span>
                        <strong id="detailsPatientIc">Not provided</strong>
                    </div>

                    <div class="patient-detail-item">
                        <span>Contact Number</span>
                        <strong id="detailsPatientContact">Not provided</strong>
                    </div>

                    <div class="patient-detail-item">
                        <span>Email Address</span>
                        <strong id="detailsPatientEmail">Not provided</strong>
                    </div>
                </div>
            </section>

            <section class="patient-information-section">
                <h4>Calorie Information</h4>

                <div class="patient-information-grid">
                    <div class="patient-detail-item">
                        <span>Height</span>
                        <strong id="detailsPatientHeight">Not provided</strong>
                    </div>

                    <div class="patient-detail-item">
                        <span>Weight</span>
                        <strong id="detailsPatientWeight">Not provided</strong>
                    </div>

                    <div class="patient-detail-item">
                        <span>Activity Level</span>
                        <strong id="detailsPatientActivity">Not provided</strong>
                    </div>

                    <div class="patient-detail-item patient-calorie-result">
                        <span>Estimated Daily Intake</span>

                        <strong id="detailsPatientCalories">Not calculated</strong>
                    </div>
                </div>
            </section>

            <section class="patient-information-section">
                <h4>Health Information</h4>

                <div class="patient-health-information">
                    <div class="patient-health-detail">
                        <span>Medical Conditions</span>

                        <p id="detailsPatientCondition">None recorded</p>
                    </div>

                    <div class="patient-health-detail">
                        <span>Allergies / Dietary Restrictions</span>

                        <p id="detailsPatientAllergy">None recorded</p>
                    </div>
                </div>
            </section>

            <section
                class="patient-information-section patient-recommendations-section"
            >
                <div class="patient-recommendations-heading">
                    <h4>Recommended Meals</h4>

                    <span id="patientRecommendationCount">0 recommendations</span>
                </div>

                <div
                    class="patient-recommendations-list"
                    id="patientRecommendationsList"
                >
                    <p class="patient-recommendation-placeholder">Select a patient to view recommended meals.</p>
                </div>
            </section>

            <?php if (($currentRole ?? "") === "nutritionist"): ?>
                <div class="patient-details-actions">
                    <button type="button" class="patient-customize-button" id="patientCustomizeMealBtn">Customize Meal for Patient</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>