<?php
require_once "admin_auth.php";
require_once "db_connect.php";

if (!function_exists("h")) {
    function h($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
    }
}

if (empty($_SESSION["admin_csrf_token"])) {
    $_SESSION["admin_csrf_token"] = bin2hex(random_bytes(32));
}

$role = trim((string)($_GET["role"] ?? ""));
$userId = (int)($_GET["user_id"] ?? 0);

if (!in_array($role, ["nutritionist", "patient"], true) || $userId <= 0) {
    header("Location: admin_users.php?error=" . urlencode("Invalid user selection."));
    exit();
}

if ($role === "nutritionist") {
    $statement = mysqli_prepare($conn, "
        SELECT u.user_id, u.username, u.email, u.status,
               n.nutritionist_id, n.first_name, n.last_name,
               n.contact_number, n.gender
        FROM users u
        INNER JOIN nutritionists n ON n.user_id = u.user_id
        WHERE u.user_id = ? AND u.role = 'nutritionist'
        LIMIT 1
    ");
} else {
    $statement = mysqli_prepare($conn, "
        SELECT u.user_id, u.username, u.email, u.status,
               p.patient_id, p.nutritionist_id, p.first_name, p.last_name,
               p.contact_number, p.ic_number, p.date_of_birth,
               p.height, p.weight, p.activity_level, p.gender,
               p.nutritionist_gender_edit_used, p.allergies,
               p.medical_conditions, p.daily_calorie_intake
        FROM users u
        INNER JOIN patients p ON p.user_id = u.user_id
        WHERE u.user_id = ? AND u.role = 'patient'
        LIMIT 1
    ");
}

mysqli_stmt_bind_param($statement, "i", $userId);
mysqli_stmt_execute($statement);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($statement));

if (!$user) {
    header("Location: admin_users.php?error=" . urlencode("User record could not be found."));
    exit();
}

$nutritionists = [];
if ($role === "patient") {
    $result = mysqli_query($conn, "
        SELECT n.nutritionist_id, n.first_name, n.last_name, u.username, u.status
        FROM nutritionists n
        INNER JOIN users u ON u.user_id = n.user_id
        ORDER BY n.first_name ASC, n.last_name ASC, n.nutritionist_id ASC
    ");
    while ($result && $row = mysqli_fetch_assoc($result)) {
        $nutritionists[] = $row;
    }
}

$isPatient = $role === "patient";
$pageTitle = $isPatient ? "Edit Patient" : "Edit Nutritionist";
$errorMessage = trim((string)($_GET["error"] ?? ""));

$activityLevels = [
    "Sedentary",
    "Lightly Active",
    "Moderately Active",
    "Very Active",
    "Extremely Active"
];

$currentActivityLevel = (string)($user["activity_level"] ?? "");
$legacyActivityMap = [
    "Low" => "Lightly Active",
    "Medium" => "Moderately Active",
    "High" => "Very Active"
];
$activityWasConverted = isset($legacyActivityMap[$currentActivityLevel]);
if ($activityWasConverted) {
    $currentActivityLevel = $legacyActivityMap[$currentActivityLevel];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($pageTitle); ?> - CalorEat</title>
    <link rel="stylesheet" href="assets/css/admin.css?v=<?php echo filemtime(__DIR__ . '/assets/css/admin.css'); ?>">
</head>
<body>
<div class="admin-shell">
    <header class="admin-subheader">
        <a class="admin-back-link" href="admin_users.php">← User Management</a>
        <div>
            <h1><?php echo h($pageTitle); ?></h1>
            <p><?php echo $isPatient ? "Update patient account, health details, assignment, and calorie information." : "Update nutritionist account and profile information."; ?></p>
        </div>
        <a class="admin-logout-button" href="logout.php">Logout</a>
    </header>

    <main class="admin-main admin-page-main">
        <?php if ($errorMessage !== ""): ?>
            <div class="admin-alert error"><?php echo h($errorMessage); ?></div>
        <?php endif; ?>

        <section class="admin-panel admin-form-panel">
            <form class="admin-meal-form" action="admin_save_user.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION["admin_csrf_token"]); ?>">
                <input type="hidden" name="role" value="<?php echo h($role); ?>">
                <input type="hidden" name="user_id" value="<?php echo (int)$userId; ?>">

                <div class="admin-form-section">
                    <div class="admin-form-section-heading">
                        <p class="admin-eyebrow">LOGIN ACCOUNT</p>
                        <h2>Account Information</h2>
                    </div>

                    <div class="admin-form-grid two-columns">
                        <label class="admin-form-field">
                            <span>Username <em>*</em></span>
                            <input type="text" name="username" maxlength="100" required value="<?php echo h($user["username"]); ?>">
                        </label>

                        <label class="admin-form-field">
                            <span>Email <?php echo $isPatient ? "" : "<em>*</em>"; ?></span>
                            <input type="email" name="email" maxlength="255" <?php echo $isPatient ? "" : "required"; ?> value="<?php echo h($user["email"]); ?>">
                            <?php if ($isPatient): ?><small>The patient email may be left empty.</small><?php endif; ?>
                        </label>
                    </div>
                </div>

                <div class="admin-form-section">
                    <div class="admin-form-section-heading">
                        <p class="admin-eyebrow">PROFILE</p>
                        <h2>Personal Information</h2>
                    </div>

                    <div class="admin-form-grid two-columns">
                        <label class="admin-form-field">
                            <span>First Name <em>*</em></span>
                            <input type="text" name="first_name" maxlength="100" required value="<?php echo h($user["first_name"]); ?>">
                        </label>

                        <label class="admin-form-field">
                            <span>Last Name</span>
                            <input type="text" name="last_name" maxlength="100" value="<?php echo h($user["last_name"]); ?>">
                        </label>

                        <label class="admin-form-field">
                            <span>Contact Number <?php echo $isPatient ? "<em>*</em>" : ""; ?></span>
                            <input type="text" name="contact_number" maxlength="30" <?php echo $isPatient ? "required" : ""; ?> value="<?php echo h($user["contact_number"]); ?>">
                        </label>

                        <label class="admin-form-field">
                            <span>Gender <?php echo $isPatient ? "<em>*</em>" : ""; ?></span>
                            <select name="gender" <?php echo $isPatient ? "required" : ""; ?>>
                                <option value="">Select gender</option>
                                <option value="Male" <?php echo ($user["gender"] ?? "") === "Male" ? "selected" : ""; ?>>Male</option>
                                <option value="Female" <?php echo ($user["gender"] ?? "") === "Female" ? "selected" : ""; ?>>Female</option>
                                <?php if (!$isPatient): ?>
                                    <option value="Other" <?php echo ($user["gender"] ?? "") === "Other" ? "selected" : ""; ?>>Other</option>
                                <?php endif; ?>
                            </select>
                            <?php if ($isPatient): ?><small>An administrator may correct the patient's gender even after the nutritionist's one-time correction has been used.</small><?php endif; ?>
                        </label>
                    </div>
                </div>

                <?php if ($isPatient): ?>
                    <div class="admin-form-section">
                        <div class="admin-form-section-heading">
                            <p class="admin-eyebrow">PATIENT DETAILS</p>
                            <h2>Health and Assignment Information</h2>
                        </div>

                        <div class="admin-form-grid two-columns">
                            <label class="admin-form-field">
                                <span>I.C. Number <em>*</em></span>
                                <input type="text" name="ic_number" inputmode="numeric" maxlength="14" required value="<?php echo h($user["ic_number"]); ?>">
                            </label>

                            <label class="admin-form-field">
                                <span>Date of Birth <em>*</em></span>
                                <input type="date" name="date_of_birth" required value="<?php echo h($user["date_of_birth"]); ?>">
                            </label>

                            <label class="admin-form-field">
                                <span>Height (cm) <em>*</em></span>
                                <input type="number" name="height" min="50" max="250" step="0.01" required value="<?php echo h($user["height"]); ?>">
                            </label>

                            <label class="admin-form-field">
                                <span>Weight (kg) <em>*</em></span>
                                <input type="number" name="weight" min="10" max="400" step="0.01" required value="<?php echo h($user["weight"]); ?>">
                            </label>

                            <label class="admin-form-field">
                                <span>Activity Level <em>*</em></span>
                                <select name="activity_level" required>
                                    <?php foreach ($activityLevels as $level): ?>
                                        <option value="<?php echo h($level); ?>" <?php echo $currentActivityLevel === $level ? "selected" : ""; ?>><?php echo h($level); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($activityWasConverted): ?><small>The older value “<?php echo h($user["activity_level"]); ?>” has been matched to “<?php echo h($currentActivityLevel); ?>”.</small><?php endif; ?>
                            </label>

                            <label class="admin-form-field">
                                <span>Assigned Nutritionist</span>
                                <select name="nutritionist_id">
                                    <option value="0">Unassigned</option>
                                    <?php foreach ($nutritionists as $nutritionist): ?>
                                        <?php
                                            $nutritionistName = trim((string)($nutritionist["first_name"] . " " . $nutritionist["last_name"]));
                                            if ($nutritionistName === "") {
                                                $nutritionistName = $nutritionist["username"];
                                            }
                                            if ($nutritionist["status"] === "inactive") {
                                                $nutritionistName .= " (Inactive)";
                                            }
                                        ?>
                                        <option value="<?php echo (int)$nutritionist["nutritionist_id"]; ?>" <?php echo (int)($user["nutritionist_id"] ?? 0) === (int)$nutritionist["nutritionist_id"] ? "selected" : ""; ?>><?php echo h($nutritionistName); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>

                            <label class="admin-form-field admin-form-field-wide">
                                <span>Medical Conditions</span>
                                <textarea name="medical_conditions" rows="4"><?php echo h($user["medical_conditions"]); ?></textarea>
                            </label>

                            <label class="admin-form-field admin-form-field-wide">
                                <span>Allergies</span>
                                <textarea name="allergies" rows="4"><?php echo h($user["allergies"]); ?></textarea>
                            </label>

                            <div class="admin-calorie-summary admin-form-field-wide">
                                <span>Current Daily Calorie Intake</span>
                                <strong><?php echo $user["daily_calorie_intake"] !== null ? number_format((int)$user["daily_calorie_intake"]) . " kcal/day" : "Not calculated"; ?></strong>
                                <small>The value will be recalculated automatically after saving height, weight, date of birth, activity level, or gender changes.</small>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="admin-form-actions">
                    <a class="admin-secondary-button" href="admin_users.php">Cancel</a>
                    <button class="admin-primary-button" type="submit">Save Changes</button>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>
