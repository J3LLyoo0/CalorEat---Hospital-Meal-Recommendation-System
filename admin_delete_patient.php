<?php
require_once "admin_auth.php";
require_once "db_connect.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function returnUserError($message)
{
    header("Location: admin_users.php?error=" . urlencode($message));
    exit();
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
    returnUserError("Your session token expired. Please try again.");
}

$userId = (int)($_POST["user_id"] ?? 0);
if ($userId <= 0) {
    returnUserError("Invalid patient selection.");
}

try {
    $patientStatement = mysqli_prepare($conn, "
        SELECT p.patient_id, u.profile_image
        FROM users u
        INNER JOIN patients p ON p.user_id = u.user_id
        WHERE u.user_id = ? AND u.role = 'patient'
        LIMIT 1
    ");
    mysqli_stmt_bind_param($patientStatement, "i", $userId);
    mysqli_stmt_execute($patientStatement);
    $patient = mysqli_fetch_assoc(mysqli_stmt_get_result($patientStatement));

    if (!$patient) {
        returnUserError("The selected patient account could not be found.");
    }

    $patientId = (int)$patient["patient_id"];
    $dependencyQueries = [
        "meal recommendations" => "SELECT COUNT(*) AS total FROM meal_recommendations WHERE patient_id = ?",
        "customized meals" => "SELECT COUNT(*) AS total FROM custom_meals WHERE patient_id = ?"
    ];

    $dependencies = [];
    foreach ($dependencyQueries as $label => $query) {
        $countStatement = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($countStatement, "i", $patientId);
        mysqli_stmt_execute($countStatement);
        $countRow = mysqli_fetch_assoc(mysqli_stmt_get_result($countStatement));
        $count = (int)($countRow["total"] ?? 0);
        if ($count > 0) {
            $dependencies[] = $count . " " . $label;
        }
    }

    if (count($dependencies) > 0) {
        returnUserError(
            "This patient cannot be deleted because the account has " .
            implode(", ", $dependencies) .
            ". Deactivate the account instead to preserve its records."
        );
    }

    mysqli_begin_transaction($conn);
    $deleteStatement = mysqli_prepare($conn, "DELETE FROM users WHERE user_id = ? AND role = 'patient'");
    mysqli_stmt_bind_param($deleteStatement, "i", $userId);
    mysqli_stmt_execute($deleteStatement);

    if (mysqli_stmt_affected_rows($deleteStatement) !== 1) {
        throw new mysqli_sql_exception("Patient user row was not deleted.");
    }

    mysqli_commit($conn);

    $profileImage = trim((string)($patient["profile_image"] ?? ""));
    if (
        $profileImage !== "" &&
        str_starts_with(str_replace("\\", "/", $profileImage), "images/profile/")
    ) {
        $absolutePath = __DIR__ . "/" . str_replace("\\", "/", $profileImage);
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    header("Location: admin_users.php?deleted=1");
    exit();
} catch (mysqli_sql_exception $error) {
    try {
        mysqli_rollback($conn);
    } catch (Throwable $ignored) {
    }

    error_log("Admin patient delete error: " . $error->getMessage());
    returnUserError("The patient account could not be deleted. Deactivate it instead if it has linked records.");
}
?>
