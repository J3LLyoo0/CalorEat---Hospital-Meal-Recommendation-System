<?php
require_once "admin_auth.php";
require_once "db_connect.php";

function redirectWithDeleteError(string $message): never {
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
    redirectWithDeleteError("Invalid request token.");
}

$foodId = filter_input(INPUT_POST, "food_id", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
if (!$foodId) {
    redirectWithDeleteError("Invalid meal record.");
}

$stmt = mysqli_prepare($conn, "
    SELECT fi.food_name, fi.image
    FROM food_items fi
    LEFT JOIN custom_meals cm ON cm.food_id = fi.food_id
    WHERE fi.food_id = ? AND cm.custom_meal_id IS NULL
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $foodId);
mysqli_stmt_execute($stmt);
$meal = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$meal) {
    redirectWithDeleteError("Meal not found or it is a customized meal.");
}

$referenceChecks = [
    "recommendation" => "SELECT COUNT(*) AS total FROM meal_recommendations WHERE food_id = ?",
    "customized meal" => "SELECT COUNT(*) AS total FROM custom_meals WHERE base_food_id = ? OR food_id = ?"
];

$referenceSummary = [];
foreach ($referenceChecks as $label => $sql) {
    $checkStmt = mysqli_prepare($conn, $sql);
    if ($label === "customized meal") {
        mysqli_stmt_bind_param($checkStmt, "ii", $foodId, $foodId);
    } else {
        mysqli_stmt_bind_param($checkStmt, "i", $foodId);
    }
    mysqli_stmt_execute($checkStmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($checkStmt));
    $count = (int)($row["total"] ?? 0);
    if ($count > 0) {
        $referenceSummary[] = $count . " " . $label . ($count === 1 ? " record" : " records");
    }
}

if ($referenceSummary !== []) {
    redirectWithDeleteError(
        "This meal cannot be deleted because it is used in " . implode(", ", $referenceSummary) . ". Hide the meal instead to preserve existing records."
    );
}

$imagePath = trim((string)($meal["image"] ?? ""));

try {
    mysqli_begin_transaction($conn);
    $deleteStmt = mysqli_prepare($conn, "DELETE FROM food_items WHERE food_id = ? LIMIT 1");
    mysqli_stmt_bind_param($deleteStmt, "i", $foodId);
    mysqli_stmt_execute($deleteStmt);

    if (mysqli_stmt_affected_rows($deleteStmt) !== 1) {
        throw new RuntimeException("Meal was not deleted.");
    }

    mysqli_commit($conn);

    if ($imagePath !== "" && str_starts_with($imagePath, "images/food/admin_")) {
        $absoluteImagePath = __DIR__ . "/" . $imagePath;
        if (is_file($absoluteImagePath)) {
            @unlink($absoluteImagePath);
        }
    }

    header("Location: admin_meals.php?deleted=1");
    exit();
} catch (Throwable $exception) {
    mysqli_rollback($conn);
    redirectWithDeleteError("The meal could not be deleted. Hide it instead or check its related records.");
}
