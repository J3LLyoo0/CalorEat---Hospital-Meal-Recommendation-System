<?php
require_once "session_check.php";
require_once "db_connect.php";

if (!function_exists("h")) {
    function h($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
    }
}

$userId = $_SESSION["user_id"];
$role = $_SESSION["role"];
$homePage = $role === "admin" ? "admin_dashboard.php" : "index.php";

$user = null;
$profile = null;

$stmt = mysqli_prepare($conn, "SELECT user_id, username, email, profile_image, role, status FROM users WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$userResult = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($userResult);

if (!$user) {
    echo "<script>alert('User account not found.'); window.location.href='logout.php';</script>";
    exit();
}

if ($role === "nutritionist") {
    $sql = "SELECT * FROM nutritionists WHERE user_id = ? LIMIT 1";
} elseif ($role === "patient") {
    $sql = "SELECT * FROM patients WHERE user_id = ? LIMIT 1";
} elseif ($role === "admin") {
    $sql = "SELECT * FROM admins WHERE user_id = ? LIMIT 1";
} else {
    echo "<script>alert('Invalid role.'); window.location.href='index.php';</script>";
    exit();
}

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$profileResult = mysqli_stmt_get_result($stmt);
$profile = mysqli_fetch_assoc($profileResult);

$savedGender = trim((string)($profile["gender"] ?? ""));
$genderIsLocked = $savedGender !== "";

$defaultProfileImage = "images/user.png";

$profileImage = !empty($user["profile_image"])
? $user["profile_image"] : $defaultProfileImage;

function readonlyGenderField($gender) {
    if ($gender === null || $gender === "") {
        return '
            <select name="gender">
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        ';
    }

    return '<input type="text" value="' . h($gender) . '" readonly>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account - CalorEat</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/account.css">
    <link rel="stylesheet" href="assets/css/account-reset-password.css">
</head>

<body class="account-page-body">

<div class="account-page-wrapper">

    <header class="account-topbar">
        <div class="brand">
            <div class="logo-box">
                <img src="images/logo.png" alt="CalorEat Logo">
            </div>
            <h1>CalorEat</h1>
        </div>
    </header>

    <a href="<?php echo h($homePage); ?>" class="account-back-link">← Back to Home Page</a>

    <main class="account-main">

        <form action="update_account.php" method="POST" enctype="multipart/form-data">

            <div class="profile-image-area">
                <div class="profile-image-circle">
                    <img src="<?php echo h($profileImage); ?>" alt="Profile Image" id="profileImagePreview"
                         onerror="this.style.display='none'; this.parentElement.classList.add('profile-placeholder');">
                </div>

                <?php if ($role === "nutritionist"): ?>
                    <label class="profile-edit-btn" title="Change profile image">
                        ✎
                        <input type="file" name="profile_image" id="profileImageInput" accept=".jpg,.jpeg,.png,.webp">
                    </label>
                <?php endif; ?>
            </div>

            <div class="account-edit-guide">
                <?php if ($role === "nutritionist"): ?>
                    <p><strong>Editable:</strong> Profile picture, first name, last name, contact number, and email address.</p>
                    <p><strong>Not editable:</strong> Username and role.</p>
                    <p><strong>Gender:</strong> Can be selected once. After saving, it cannot be changed.</p>
                <?php elseif ($role === "admin"): ?>
                    <p><strong>Editable:</strong> First name, last name, contact number, and email address.</p>
                    <p><strong>Not editable:</strong> Username and role.</p>
                <?php else: ?>
                    <p><strong>Editable:</strong> Personal and health information shown below.</p>
                    <p><strong>Not editable:</strong> Username, role, and confirmed gender.</p>
                <?php endif; ?>
            </div>

            <section class="account-details-box">
                <h2>
                    <?php
                    if ($role === "nutritionist") {
                        echo "Nutritionist Account Details";
                    } elseif ($role === "patient") {
                        echo "Patient Account Details";
                    } else {
                        echo "Admin Account Details";
                    }
                    ?>
                </h2>

                <div class="account-form-grid">

                    <label class="account-field locked-field">
                        Username:
                        <span class="field-status">Cannot be changed</span>
                        <input type="text" value="<?php echo h($user["username"]); ?>" readonly>
                    </label>

                    <label>
                        Email Address:
                        <span class="field-status">Editable</span>
                        <input type="email" name="email" value="<?php echo h($user["email"]); ?>" required>
                    </label>

                    <?php if ($role === "nutritionist"): ?>

                        <label class="account-field editable-field">
                            First Name:
                            <span class="field-status">Editable</span>
                            <input type="text" name="first_name" value="<?php echo h($profile["first_name"] ?? ""); ?>" required>
                        </label>

                        <label>
                            Last Name:
                            <span class="field-status">Editable</span>
                            <input type="text" name="last_name" value="<?php echo h($profile["last_name"] ?? ""); ?>">
                        </label>

                        <label>
                            Contact Number:
                            <span class="field-status">Editable</span>
                            <input type="text" name="contact_number" value="<?php echo h($profile["contact_number"] ?? ""); ?>">
                        </label>

                        <label class="account-field <?php echo $genderIsLocked ? "locked-field" : "editable-field"; ?>">
                            Gender:
                            <span class="field-status">
                                <?php echo $genderIsLocked ? "Cannot be changed" : "Can only be set once"; ?>
                            </span>
                            <select name="gender" id="gender" <?php echo $genderIsLocked ? "disabled" : ""; ?>>
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo $savedGender === "Male" ? "selected" : ""; ?>>Male</option>
                                <option value="Female" <?php echo $savedGender === "Female" ? "selected" : ""; ?>>Female</option>
                            </select>
                        </label>

                    <?php elseif ($role === "patient"): ?>

                        <label>
                            First Name:<input type="text" name="first_name" value="<?php echo h($profile["first_name"] ?? ""); ?>" required>
                        </label>

                        <label>
                            Last Name:<input type="text" name="last_name" value="<?php echo h($profile["last_name"] ?? ""); ?>">
                        </label>

                        <label>
                            Contact Number:<input type="text" name="contact_number" value="<?php echo h($profile["contact_number"] ?? ""); ?>">
                        </label>

                        <label>
                            IC Number:<input type="text" value="<?php echo h($profile["ic_number"] ?? ""); ?>" readonly>
                        </label>

                        <label>
                            Date of Birth:<input type="date" name="date_of_birth" value="<?php echo h($profile["date_of_birth"] ?? ""); ?>">
                        </label>

                        <label>
                            Gender:<?php echo readonlyGenderField($profile["gender"] ?? ""); ?>
                        </label>

                        <label>
                            Height cm:<input type="number" step="0.01" name="height" value="<?php echo h($profile["height"] ?? ""); ?>">
                        </label>

                        <label>
                            Weight kg:<input type="number" step="0.01" name="weight" value="<?php echo h($profile["weight"] ?? ""); ?>">
                        </label>

                        <label>
                            Activity Level:
                            <select name="activity_level">
                                <option value="">Select Activity Level</option>
                                <option value="Low" <?php echo (($profile["activity_level"] ?? "") === "Low") ? "selected" : ""; ?>>Low</option>
                                <option value="Moderate" <?php echo (($profile["activity_level"] ?? "") === "Moderate") ? "selected" : ""; ?>>Moderate</option>
                                <option value="High" <?php echo (($profile["activity_level"] ?? "") === "High") ? "selected" : ""; ?>>High</option>
                            </select>
                        </label>

                        <label>
                            Daily Calorie Intake:
                            <input type="number" name="daily_calorie_intake" value="<?php echo h($profile["daily_calorie_intake"] ?? ""); ?>">
                        </label>

                        <label class="account-full-field">
                            Medical Conditions:
                            <textarea name="medical_conditions"><?php echo h($profile["medical_conditions"] ?? ""); ?></textarea>
                        </label>

                        <label class="account-full-field">
                            Allergies / Restrictions:
                            <textarea name="allergies"><?php echo h($profile["allergies"] ?? ""); ?></textarea>
                        </label>

                    <?php elseif ($role === "admin"): ?>

                        <label class="account-field editable-field">
                            First Name:
                            <span class="field-status">Editable</span>
                            <input type="text" name="first_name" value="<?php echo h($profile["first_name"] ?? ""); ?>" required>
                        </label>

                        <label class="account-field editable-field">
                            Last Name:
                            <span class="field-status">Editable</span>
                            <input type="text" name="last_name" value="<?php echo h($profile["last_name"] ?? ""); ?>">
                        </label>

                        <label class="account-field editable-field account-full-field">
                            Contact Number:
                            <span class="field-status">Editable</span>
                            <input type="text" name="contact_number" value="<?php echo h($profile["contact_number"] ?? ""); ?>">
                        </label>

                    <?php endif; ?>

                </div>

                <div class="account-center-actions">
                    <button type="button" class="account-soft-btn" id="openPasswordModal">
                        Reset Password
                    </button>
                </div>

                <div class="account-save-area">
                    <button type="submit" class="account-save-btn">Save Changes</button>
                </div>
            </section>
        </form>
    </main>

    <a href="logout.php" class="account-logout-btn">Log Out</a>

</div>

<!-- Reset Password OTP Modal -->
<div class="otp-modal-overlay" id="passwordModal">
    <div class="otp-modal-card">
        <button class="otp-modal-close" type="button" id="closePasswordModal">X</button>

        <!--Step 1:OTP-->
        <div class="reset-step reset-step-active" id="otpStep">
            <h2>Reset Your Password</h2>

            <p class="otp-instruction">Check Your Email Inbox for OTP code</p>

            <form id="otpForm">
                <div class="otp-input-row" id="otpInputRow">
                    <input type="text" maxlength="1" class="otp-box" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-box" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-box" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-box" inputmode="numeric">
                </div>

                <button type="submit" class="otp-confirm-btn">Confirm</button>
            </form>

            <p class="otp-status-text" id="otpStatusText"></p>
        </div>

        <!--Step 2: New Password-->
        <div class="reset-step" id="newPasswordStep">
            <h2>Create New Password</h2>

            <p class="otp-instruction">Enter and confirm your new password</p>

            <form id="newPasswordForm">
                <div class="reset-password-field">
                    <input type="password" id="newPassword" placeholder="New Password" autocomplete="new-password">

                    <button type="button" class="reset-password-toggle" data-password-target="newPassword" aria-label="Show Password">
                        <img src="images/password/view.png" alt="" class="password-toggle-image">
                    </button>
                </div>

                <div class="reset-password-field">
                    <input type="password" id="confirmNewPassword" placeholder="Confirm New Password" autocomplete="new-password">

                    <button type="button" class="reset-password-toggle" data-password-target="confirmNewPassword" aria-label="Show Password">
                        <img src="images/password/view.png" alt="" class="password-toggle-image">
                    </button>
                </div>

                <button type="submit" class="otp-confirm-btn">Reset Password</button>
            </form>

            <p class="otp-status-text" id="passwordStatusText"></p>
        </div>

        <!--Step 3: Success-->
        <div class="reset-step" id="successStep">
            <img src="images/accept.png" alt="Password reset successful" class="reset-success-image">

            <h2>Password Reset Complete</h2>

            <p class="otp-instruction">Your password has been updated successfully.</p>

            <button type="button" class="otp-confirm-btn" id="closeSuccessModal">Done</button>
        </div>
    </div>
</div>

<script src="assets/js/account.js"></script>
</body>
</html>