<?php
require_once "admin_auth.php";
require_once "db_connect.php";

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

$userId = (int)($_POST["user_id"] ?? 0);
$status = trim((string)($_POST["status"] ?? ""));

if ($userId <= 0 || !in_array($status, ["active", "inactive"], true)) {
    header("Location: admin_users.php?error=" . urlencode("Invalid account status request."));
    exit();
}

$statement = mysqli_prepare($conn, "
    UPDATE users
    SET status = ?
    WHERE user_id = ? AND role IN ('nutritionist', 'patient')
");
mysqli_stmt_bind_param($statement, "si", $status, $userId);
mysqli_stmt_execute($statement);

if (mysqli_stmt_affected_rows($statement) < 1) {
    $check = mysqli_prepare($conn, "SELECT user_id FROM users WHERE user_id = ? AND role IN ('nutritionist', 'patient') LIMIT 1");
    mysqli_stmt_bind_param($check, "i", $userId);
    mysqli_stmt_execute($check);
    if (!mysqli_fetch_assoc(mysqli_stmt_get_result($check))) {
        header("Location: admin_users.php?error=" . urlencode("The selected account could not be found."));
        exit();
    }
}

header("Location: admin_users.php?status_updated=1");
exit();
?>
