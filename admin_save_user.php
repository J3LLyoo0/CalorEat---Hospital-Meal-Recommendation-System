<?php
require_once "admin_auth.php";
require_once "db_connect.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function redirectToForm($role, $userId, $message)
{
    header(
        "Location: admin_user_form.php?role=" . urlencode($role) .
        "&user_id=" . (int)$userId .
        "&error=" . urlencode($message)
    );
    exit();
}

function validDateOfBirth($dateOfBirth)
{
    $birthDate = DateTime::createFromFormat("!Y-m-d", $dateOfBirth);
    $errors = DateTime::getLastErrors();
    $hasErrors = $errors !== false && ($errors["warning_count"] > 0 || $errors["error_count"] > 0);

    if (!$birthDate || $hasErrors || $birthDate->format("Y-m-d") !== $dateOfBirth) {
        return null;
    }

    $today = new DateTime("today");
    if ($birthDate > $today) {
        return null;
    }

    return $birthDate->diff($today)->y;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: admin_users.php");
    exit();
}

$csrfToken = (string)($_POST["csrf_token"] ?? "");
if (
    empty($_SESSION["admin_csrf_token"]) ||
    $csrfToken === "" ||
    !hash_equals($_SESSION["admin_csrf_token"], $csrfToken)
) {
    header("Location: admin_users.php?error=" . urlencode("Your session token expired. Please try again."));
    exit();
}

$role = trim((string)($_POST["role"] ?? ""));
$userId = (int)($_POST["user_id"] ?? 0);

if (!in_array($role, ["nutritionist", "patient"], true) || $userId <= 0) {
    header("Location: admin_users.php?error=" . urlencode("Invalid user selection."));
    exit();
}

$username = trim((string)($_POST["username"] ?? ""));
$email = strtolower(trim((string)($_POST["email"] ?? "")));
$firstName = trim((string)($_POST["first_name"] ?? ""));
$lastName = trim((string)($_POST["last_name"] ?? ""));
$contactNumber = trim((string)($_POST["contact_number"] ?? ""));
$gender = trim((string)($_POST["gender"] ?? ""));

if ($username === "" || strlen($username) > 100) {
    redirectToForm($role, $userId, "Please enter a valid username.");
}

if ($firstName === "" || strlen($firstName) > 100 || strlen($lastName) > 100) {
    redirectToForm($role, $userId, "Please enter a valid first and last name.");
}

if ($role === "nutritionist" && $email === "") {
    redirectToForm($role, $userId, "Nutritionist email is required.");
}

if ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectToForm($role, $userId, "Please enter a valid email address.");
}

if ($contactNumber !== "") {
    $contactNumber = preg_replace("/[^0-9]/", "", $contactNumber);
    if (strlen($contactNumber) < 9 || strlen($contactNumber) > 15) {
        redirectToForm($role, $userId, "Contact number must contain between 9 and 15 digits.");
    }
} elseif ($role === "patient") {
    redirectToForm($role, $userId, "Patient contact number is required.");
}

if ($role === "nutritionist" && !in_array($gender, ["", "Male", "Female", "Other"], true)) {
    redirectToForm($role, $userId, "Please select a valid gender.");
}

try {
    $recordStatement = mysqli_prepare($conn, "
        SELECT u.user_id, u.role,
               n.nutritionist_id,
               p.patient_id, p.gender AS patient_gender,
               p.nutritionist_gender_edit_used
        FROM users u
        LEFT JOIN nutritionists n ON n.user_id = u.user_id
        LEFT JOIN patients p ON p.user_id = u.user_id
        WHERE u.user_id = ? AND u.role = ?
        LIMIT 1
    ");
    mysqli_stmt_bind_param($recordStatement, "is", $userId, $role);
    mysqli_stmt_execute($recordStatement);
    $record = mysqli_fetch_assoc(mysqli_stmt_get_result($recordStatement));

    if (!$record) {
        redirectToForm($role, $userId, "The selected user record could not be found.");
    }

    $usernameCheck = mysqli_prepare($conn, "SELECT user_id FROM users WHERE username = ? AND user_id <> ? LIMIT 1");
    mysqli_stmt_bind_param($usernameCheck, "si", $username, $userId);
    mysqli_stmt_execute($usernameCheck);
    if (mysqli_fetch_assoc(mysqli_stmt_get_result($usernameCheck))) {
        redirectToForm($role, $userId, "This username is already used by another account.");
    }

    if ($email !== "") {
        $emailCheck = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ? AND user_id <> ? LIMIT 1");
        mysqli_stmt_bind_param($emailCheck, "si", $email, $userId);
        mysqli_stmt_execute($emailCheck);
        if (mysqli_fetch_assoc(mysqli_stmt_get_result($emailCheck))) {
            redirectToForm($role, $userId, "This email address is already used by another account.");
        }
    }

    $databaseEmail = $email !== "" ? $email : null;

    if ($role === "nutritionist") {
        $nutritionistId = (int)($record["nutritionist_id"] ?? 0);
        if ($nutritionistId <= 0) {
            redirectToForm($role, $userId, "The nutritionist profile could not be found.");
        }

        $databaseGender = $gender !== "" ? $gender : null;

        mysqli_begin_transaction($conn);

        $userUpdate = mysqli_prepare($conn, "UPDATE users SET username = ?, email = ? WHERE user_id = ? AND role = 'nutritionist'");
        mysqli_stmt_bind_param($userUpdate, "ssi", $username, $databaseEmail, $userId);
        mysqli_stmt_execute($userUpdate);

        $profileUpdate = mysqli_prepare($conn, "
            UPDATE nutritionists
            SET first_name = ?, last_name = ?, contact_number = ?, gender = ?
            WHERE nutritionist_id = ?
        ");
        mysqli_stmt_bind_param($profileUpdate, "ssssi", $firstName, $lastName, $contactNumber, $databaseGender, $nutritionistId);
        mysqli_stmt_execute($profileUpdate);

        mysqli_commit($conn);
    } else {
        $patientId = (int)($record["patient_id"] ?? 0);
        if ($patientId <= 0) {
            redirectToForm($role, $userId, "The patient profile could not be found.");
        }

        $icNumber = preg_replace("/[^0-9]/", "", trim((string)($_POST["ic_number"] ?? "")));
        $dateOfBirth = trim((string)($_POST["date_of_birth"] ?? ""));
        $height = filter_var($_POST["height"] ?? null, FILTER_VALIDATE_FLOAT);
        $weight = filter_var($_POST["weight"] ?? null, FILTER_VALIDATE_FLOAT);
        $activityLevel = trim((string)($_POST["activity_level"] ?? ""));
        $nutritionistId = (int)($_POST["nutritionist_id"] ?? 0);
        $allergies = trim((string)($_POST["allergies"] ?? ""));
        $medicalConditions = trim((string)($_POST["medical_conditions"] ?? ""));

        if (!preg_match("/^[0-9]{12}$/", $icNumber)) {
            redirectToForm($role, $userId, "The patient's I.C. number must contain exactly 12 digits.");
        }

        $age = validDateOfBirth($dateOfBirth);
        if ($age === null || $age > 120) {
            redirectToForm($role, $userId, "Please enter a valid date of birth.");
        }

        if ($height === false || (float)$height < 50 || (float)$height > 250) {
            redirectToForm($role, $userId, "Height must be between 50 cm and 250 cm.");
        }

        if ($weight === false || (float)$weight < 10 || (float)$weight > 400) {
            redirectToForm($role, $userId, "Weight must be between 10 kg and 400 kg.");
        }

        $activityFactors = [
            "Sedentary" => 1.2,
            "Lightly Active" => 1.375,
            "Moderately Active" => 1.55,
            "Very Active" => 1.725,
            "Extremely Active" => 1.9
        ];

        if (!isset($activityFactors[$activityLevel])) {
            redirectToForm($role, $userId, "Please select a valid activity level.");
        }

        if (!in_array($gender, ["Male", "Female"], true)) {
            redirectToForm($role, $userId, "Please select Male or Female so the daily calorie intake can be calculated.");
        }

        if ($nutritionistId > 0) {
            $nutritionistCheck = mysqli_prepare($conn, "SELECT nutritionist_id FROM nutritionists WHERE nutritionist_id = ? LIMIT 1");
            mysqli_stmt_bind_param($nutritionistCheck, "i", $nutritionistId);
            mysqli_stmt_execute($nutritionistCheck);
            if (!mysqli_fetch_assoc(mysqli_stmt_get_result($nutritionistCheck))) {
                redirectToForm($role, $userId, "The selected nutritionist could not be found.");
            }
        }

        $icCheck = mysqli_prepare($conn, "SELECT patient_id FROM patients WHERE ic_number = ? AND patient_id <> ? LIMIT 1");
        mysqli_stmt_bind_param($icCheck, "si", $icNumber, $patientId);
        mysqli_stmt_execute($icCheck);
        if (mysqli_fetch_assoc(mysqli_stmt_get_result($icCheck))) {
            redirectToForm($role, $userId, "This I.C. number is already used by another patient.");
        }

        $height = (float)$height;
        $weight = (float)$weight;
        if ($gender === "Male") {
            $bmr = (10 * $weight) + (6.25 * $height) - (5 * $age) + 5;
        } else {
            $bmr = (10 * $weight) + (6.25 * $height) - (5 * $age) - 161;
        }
        $dailyCalorieIntake = (int)round($bmr * $activityFactors[$activityLevel]);

        $genderChanged = $gender !== (string)($record["patient_gender"] ?? "");
        $genderEditUsed = (int)($record["nutritionist_gender_edit_used"] ?? 0);
        if ($genderChanged) {
            $genderEditUsed = 1;
        }

        $nutritionistIdForDatabase = $nutritionistId > 0 ? $nutritionistId : null;

        mysqli_begin_transaction($conn);

        $userUpdate = mysqli_prepare($conn, "UPDATE users SET username = ?, email = ? WHERE user_id = ? AND role = 'patient'");
        mysqli_stmt_bind_param($userUpdate, "ssi", $username, $databaseEmail, $userId);
        mysqli_stmt_execute($userUpdate);

        $patientUpdate = mysqli_prepare($conn, "
            UPDATE patients
            SET nutritionist_id = ?, first_name = ?, last_name = ?, contact_number = ?,
                ic_number = ?, date_of_birth = ?, height = ?, weight = ?,
                activity_level = ?, gender = ?, nutritionist_gender_edit_used = ?,
                allergies = ?, medical_conditions = ?, daily_calorie_intake = ?
            WHERE patient_id = ?
        ");
        mysqli_stmt_bind_param(
            $patientUpdate,
            "isssssddssissii",
            $nutritionistIdForDatabase,
            $firstName,
            $lastName,
            $contactNumber,
            $icNumber,
            $dateOfBirth,
            $height,
            $weight,
            $activityLevel,
            $gender,
            $genderEditUsed,
            $allergies,
            $medicalConditions,
            $dailyCalorieIntake,
            $patientId
        );
        mysqli_stmt_execute($patientUpdate);

        mysqli_commit($conn);
    }

    header("Location: admin_users.php?updated=1");
    exit();
} catch (mysqli_sql_exception $error) {
    if (isset($conn) && mysqli_thread_id($conn)) {
        try {
            mysqli_rollback($conn);
        } catch (Throwable $ignored) {
        }
    }

    error_log("Admin user update error: " . $error->getMessage());
    redirectToForm($role, $userId, "The user information could not be saved.");
}
?>
