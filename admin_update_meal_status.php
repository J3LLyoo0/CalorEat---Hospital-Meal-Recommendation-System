<?php
require_once "admin_auth.php";
require_once "db_connect.php";

function redirectStatusError(string $message): never {
    header("Location: admin_meals.php?error=" . urlencode($message));
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: admin_meals.php");
    exit();
}

$csrfToken = (string)($_POST["csrf_token"] ?? "");
$sessionToken = (string)($_SESSION["admin_csrf_token"] ?? "");
if ($sessionToken === "" || !hash_equals($sessionToken, $csrfToken)) {
    redirectStatusError("Invalid request token.");
}

$foodId = filter_input(INPUT_POST, "food_id", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
$status = (string)($_POST["status"] ?? "");
if (!$foodId || !in_array($status, ["active", "inactive"], true)) {
    redirectStatusError("Invalid meal status request.");
}

$checkStmt = mysqli_prepare($conn, "
    SELECT fi.food_id
    FROM food_items fi
    LEFT JOIN custom_meals cm ON cm.food_id = fi.food_id
    WHERE fi.food_id = ? AND cm.custom_meal_id IS NULL
    LIMIT 1
");
mysqli_stmt_bind_param($checkStmt, "i", $foodId);
mysqli_stmt_execute($checkStmt);
if (!mysqli_fetch_assoc(mysqli_stmt_get_result($checkStmt))) {
    redirectStatusError("Meal not found or it is a customized meal.");
}

$stmt = mysqli_prepare($conn, "UPDATE food_items SET status = ? WHERE food_id = ?");
mysqli_stmt_bind_param($stmt, "si", $status, $foodId);
mysqli_stmt_execute($stmt);

header("Location: admin_meals.php?status_updated=1");
exit();
