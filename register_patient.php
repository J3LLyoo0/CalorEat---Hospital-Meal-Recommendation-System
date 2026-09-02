<?php
// register_patient.php
// Processes the Register New Patient form.

require_once "session_check.php";
require_once "db_connect.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/**
 * Display a browser alert and return to the main page.
 */
function returnWithMessage($message)
{
    $safeMessage = json_encode(
        $message,
        JSON_HEX_TAG |
        JSON_HEX_APOS |
        JSON_HEX_AMP |
        JSON_HEX_QUOT
    );

    echo "
        <script>
            alert($safeMessage);
            window.location.href = 'index.php';
        </script>
    ";

    exit();
}

/**
 * Calculate age from a date of birth.
 */
function calculateAgeFromDob($dateOfBirth)
{
    $birthDate = DateTime::createFromFormat(
        "!Y-m-d",
        $dateOfBirth
    );

    $dateErrors = DateTime::getLastErrors();

    $hasDateErrors =
        $dateErrors !== false &&
        (
            $dateErrors["warning_count"] > 0 ||
            $dateErrors["error_count"] > 0
        );

    if (
        !$birthDate ||
        $hasDateErrors ||
        $birthDate->format("Y-m-d") !== $dateOfBirth
    ) {
        return null;
    }

    $today = new DateTime("today");

    if ($birthDate > $today) {
        return null;
    }

    return $birthDate->diff($today)->y;
}

/* =========================
   ACCESS VALIDATION
========================= */

if (
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "nutritionist"
) {
    returnWithMessage(
        "Only nutritionists can register patients."
    );
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

/* =========================
   GET NUTRITIONIST ID
========================= */

$nutritionistId =
    (int)($_SESSION["nutritionist_id"] ?? 0);

if ($nutritionistId <= 0) {
    $currentUserId =
        (int)($_SESSION["user_id"] ?? 0);

    $nutritionistQuery = "
        SELECT nutritionist_id
        FROM nutritionists
        WHERE user_id = ?
        LIMIT 1
    ";

    $nutritionistStatement =
        mysqli_prepare($conn, $nutritionistQuery);

    mysqli_stmt_bind_param(
        $nutritionistStatement,
        "i",
        $currentUserId
    );

    mysqli_stmt_execute($nutritionistStatement);

    $nutritionistResult =
        mysqli_stmt_get_result($nutritionistStatement);

    $nutritionist =
        mysqli_fetch_assoc($nutritionistResult);

    if (!$nutritionist) {
        returnWithMessage(
            "Nutritionist profile could not be found."
        );
    }

    $nutritionistId =
        (int)$nutritionist["nutritionist_id"];

    $_SESSION["nutritionist_id"] = $nutritionistId;
}

/* =========================
   GET FORM DATA
========================= */

$firstName =
    trim($_POST["first_name"] ?? "");

$lastName =
    trim($_POST["last_name"] ?? "");

$email =
    strtolower(trim($_POST["email"] ?? ""));

$contactNumber =
    trim($_POST["contact_number"] ?? "");

$icNumber =
    trim($_POST["ic_number"] ?? "");

$dateOfBirth =
    trim($_POST["date_of_birth"] ?? "");

$height =
    filter_var(
        $_POST["height"] ?? null,
        FILTER_VALIDATE_FLOAT
    );

$weight =
    filter_var(
        $_POST["weight"] ?? null,
        FILTER_VALIDATE_FLOAT
    );

$activityLevel =
    trim($_POST["activity_level"] ?? "");

$gender =
    trim($_POST["gender"] ?? "");

$allergies =
    trim($_POST["allergies"] ?? "");

$medicalConditions =
    trim($_POST["medical_conditions"] ?? "");

/* =========================
   REQUIRED FIELDS
========================= */

if (
    $firstName === "" ||
    $contactNumber === "" ||
    $icNumber === "" ||
    $dateOfBirth === "" ||
    $height === false ||
    $weight === false ||
    $activityLevel === "" ||
    $gender === ""
) {
    returnWithMessage(
        "Please complete all required patient information."
    );
}

/* =========================
   NAME VALIDATION
========================= */

if (strlen($firstName) > 100) {
    returnWithMessage(
        "The patient's first name is too long."
    );
}

if (strlen($lastName) > 100) {
    returnWithMessage(
        "The patient's last name is too long."
    );
}

/* =========================
   EMAIL VALIDATION
========================= */

if (
    $email !== "" &&
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    returnWithMessage(
        "Please enter a valid email address or leave it empty."
    );
}

/* =========================
   CONTACT VALIDATION
========================= */

$contactDigits =
    preg_replace("/[^0-9]/", "", $contactNumber);

if (
    strlen($contactDigits) < 9 ||
    strlen($contactDigits) > 15
) {
    returnWithMessage(
        "Please enter a valid contact number."
    );
}

$contactNumber = $contactDigits;

/* =========================
   IC NUMBER VALIDATION
========================= */

$icNumber =
    preg_replace("/[^0-9]/", "", $icNumber);

if (!preg_match("/^[0-9]{12}$/", $icNumber)) {
    returnWithMessage(
        "The patient's I.C. number must contain exactly 12 digits."
    );
}

/* =========================
   DATE OF BIRTH AND AGE
========================= */

$age = calculateAgeFromDob($dateOfBirth);

if ($age === null || $age > 120) {
    returnWithMessage(
        "Please enter a valid date of birth."
    );
}

/* =========================
   HEIGHT AND WEIGHT
========================= */

$height = (float)$height;
$weight = (float)$weight;

if ($height < 50 || $height > 250) {
    returnWithMessage(
        "Height must be between 50 cm and 250 cm."
    );
}

if ($weight < 10 || $weight > 400) {
    returnWithMessage(
        "Weight must be between 10 kg and 400 kg."
    );
}

/* =========================
   ACTIVITY LEVEL
========================= */

$activityFactors = [
    "Sedentary" => 1.2,
    "Lightly Active" => 1.375,
    "Moderately Active" => 1.55,
    "Very Active" => 1.725,
    "Extremely Active" => 1.9
];

if (!isset($activityFactors[$activityLevel])) {
    returnWithMessage(
        "Please select a valid activity level."
    );
}

/* =========================
   GENDER
========================= */

if (
    $gender !== "Male" &&
    $gender !== "Female"
) {
    returnWithMessage(
        "Please select a valid gender."
    );
}

/* =========================
   MIFFLIN-ST JEOR CALCULATION
========================= */

if ($gender === "Male") {
    $basalMetabolicRate =
        (10 * $weight) +
        (6.25 * $height) -
        (5 * $age) +
        5;
} else {
    $basalMetabolicRate =
        (10 * $weight) +
        (6.25 * $height) -
        (5 * $age) -
        161;
}

$dailyCalorieIntake = (int)round(
    $basalMetabolicRate *
    $activityFactors[$activityLevel]
);

/* =========================
   CHECK DUPLICATE EMAIL
========================= */

if ($email !== "") {
    $emailCheckQuery = "
        SELECT user_id
        FROM users
        WHERE email = ?
        LIMIT 1
    ";

    $emailCheckStatement =
        mysqli_prepare($conn, $emailCheckQuery);

    mysqli_stmt_bind_param(
        $emailCheckStatement,
        "s",
        $email
    );

    mysqli_stmt_execute($emailCheckStatement);

    $emailCheckResult =
        mysqli_stmt_get_result($emailCheckStatement);

    if (mysqli_fetch_assoc($emailCheckResult)) {
        returnWithMessage(
            "This email address is already registered."
        );
    }
}

/* =========================
   CHECK DUPLICATE IC NUMBER
========================= */

$icCheckQuery = "
    SELECT patient_id
    FROM patients
    WHERE ic_number = ?
    LIMIT 1
";

$icCheckStatement =
    mysqli_prepare($conn, $icCheckQuery);

mysqli_stmt_bind_param(
    $icCheckStatement,
    "s",
    $icNumber
);

mysqli_stmt_execute($icCheckStatement);

$icCheckResult =
    mysqli_stmt_get_result($icCheckStatement);

if (mysqli_fetch_assoc($icCheckResult)) {
    returnWithMessage(
        "A patient with this I.C. number is already registered."
    );
}

/* =========================
   GENERATE USERNAME
========================= */

$fullNameForUsername =
    $firstName . $lastName;

$patientUsername = strtolower(
    preg_replace(
        "/[^a-zA-Z0-9]/",
        "",
        $fullNameForUsername
    )
);

if ($patientUsername === "") {
    $patientUsername =
        "patient" . time();
}

$baseUsername = $patientUsername;
$usernameCounter = 1;

while (true) {
    $usernameCheckQuery = "
        SELECT user_id
        FROM users
        WHERE username = ?
        LIMIT 1
    ";

    $usernameCheckStatement =
        mysqli_prepare($conn, $usernameCheckQuery);

    mysqli_stmt_bind_param(
        $usernameCheckStatement,
        "s",
        $patientUsername
    );

    mysqli_stmt_execute($usernameCheckStatement);

    $usernameCheckResult =
        mysqli_stmt_get_result($usernameCheckStatement);

    if (
        mysqli_num_rows($usernameCheckResult) === 0
    ) {
        break;
    }

    $patientUsername =
        $baseUsername . $usernameCounter;

    $usernameCounter++;
}

/* =========================
   PASSWORD AND OPTIONAL EMAIL
========================= */

// The patient's initial password is their I.C. number.
$hashedPassword =
    password_hash(
        $icNumber,
        PASSWORD_DEFAULT
    );

// Store NULL when the nutritionist does not provide an email.
$emailForDatabase =
    $email !== "" ? $email : null;

/* =========================
   INSERT PATIENT
========================= */

mysqli_begin_transaction($conn);

try {
    $userInsertQuery = "
        INSERT INTO users
        (
            username,
            email,
            password,
            role,
            status
        )
        VALUES
        (
            ?,
            ?,
            ?,
            'patient',
            'active'
        )
    ";

    $userInsertStatement =
        mysqli_prepare($conn, $userInsertQuery);

    mysqli_stmt_bind_param(
        $userInsertStatement,
        "sss",
        $patientUsername,
        $emailForDatabase,
        $hashedPassword
    );

    mysqli_stmt_execute($userInsertStatement);

    $newUserId =
        mysqli_insert_id($conn);

    $patientInsertQuery = "
        INSERT INTO patients
        (
            user_id,
            nutritionist_id,
            first_name,
            last_name,
            contact_number,
            ic_number,
            date_of_birth,
            height,
            weight,
            activity_level,
            gender,
            allergies,
            medical_conditions,
            daily_calorie_intake
        )
        VALUES
        (
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?
        )
    ";

    $patientInsertStatement =
        mysqli_prepare(
            $conn,
            $patientInsertQuery
        );

    mysqli_stmt_bind_param(
        $patientInsertStatement,
        "iisssssddssssi",
        $newUserId,
        $nutritionistId,
        $firstName,
        $lastName,
        $contactNumber,
        $icNumber,
        $dateOfBirth,
        $height,
        $weight,
        $activityLevel,
        $gender,
        $allergies,
        $medicalConditions,
        $dailyCalorieIntake
    );

    mysqli_stmt_execute(
        $patientInsertStatement
    );

    mysqli_commit($conn);

    $successMessage =
        "Patient registered successfully!\n\n" .
        "Username: " . $patientUsername . "\n" .
        "Initial password: " . $icNumber . "\n" .
        "Daily calorie intake: " .
        $dailyCalorieIntake .
        " kcal/day";

    returnWithMessage($successMessage);

} catch (mysqli_sql_exception $error) {
    mysqli_rollback($conn);

    error_log(
        "Patient registration error: " .
        $error->getMessage()
    );

    returnWithMessage(
        "Patient registration failed.\n\n" .
        "Database message: " .
        $error->getMessage()
    );
}
?>