<?php
require_once "admin_auth.php";
require_once "db_connect.php";

function redirectMealForm(?int $foodId, string $message): never {
    $url = "admin_meal_form.php";
    if ($foodId !== null && $foodId > 0) {
        $url .= "?food_id=" . $foodId . "&error=" . urlencode($message);
    } else {
        $url .= "?error=" . urlencode($message);
    }
    header("Location: " . $url);
    exit();
}

function nullableDecimal(string $field, ?string &$error = null): ?float {
    $raw = trim((string)($_POST[$field] ?? ""));
    if ($raw === "") {
        return null;
    }

    if (!is_numeric($raw)) {
        $error = "Nutrition values must contain valid numbers.";
        return null;
    }

    $value = (float)$raw;
    if ($value < 0 || $value > 9999.99) {
        $error = "Nutrition values must be between 0 and 9999.99.";
        return null;
    }

    return $value;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: admin_meals.php");
    exit();
}

$csrfToken = (string)($_POST["csrf_token"] ?? "");
$sessionToken = (string)($_SESSION["admin_csrf_token"] ?? "");
if ($sessionToken === "" || !hash_equals($sessionToken, $csrfToken)) {
    header("Location: admin_meals.php?error=" . urlencode("Invalid request token."));
    exit();
}

$foodIdRaw = trim((string)($_POST["food_id"] ?? ""));
$foodId = $foodIdRaw === "" ? null : filter_var($foodIdRaw, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
if ($foodIdRaw !== "" && $foodId === false) {
    header("Location: admin_meals.php?error=" . urlencode("Invalid meal record."));
    exit();
}

$foodName = trim((string)($_POST["food_name"] ?? ""));
$categoryId = filter_input(INPUT_POST, "category_id", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
$ingredients = trim((string)($_POST["ingredients"] ?? ""));
$portionSize = trim((string)($_POST["portion_size"] ?? ""));
$calories = filter_input(INPUT_POST, "calories", FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 99999]]);
$status = (string)($_POST["status"] ?? "");

if ($foodName === "" || mb_strlen($foodName) > 150) {
    redirectMealForm($foodId ?: null, "Please enter a meal name of up to 150 characters.");
}
if (!$categoryId) {
    redirectMealForm($foodId ?: null, "Please select a valid meal category.");
}
if (mb_strlen($portionSize) > 50) {
    redirectMealForm($foodId ?: null, "Portion size cannot exceed 50 characters.");
}
if ($calories === false || $calories === null) {
    redirectMealForm($foodId ?: null, "Calories must be a whole number between 0 and 99,999.");
}
if (!in_array($status, ["active", "inactive"], true)) {
    redirectMealForm($foodId ?: null, "Please select a valid visibility status.");
}

$nutritionError = null;
$protein = nullableDecimal("protein", $nutritionError);
$carbohydrates = nullableDecimal("carbohydrates", $nutritionError);
$fat = nullableDecimal("fat", $nutritionError);
$sodium = nullableDecimal("sodium", $nutritionError);
if ($nutritionError !== null) {
    redirectMealForm($foodId ?: null, $nutritionError);
}

$categoryStmt = mysqli_prepare($conn, "SELECT category_id FROM meal_categories WHERE category_id = ? LIMIT 1");
mysqli_stmt_bind_param($categoryStmt, "i", $categoryId);
mysqli_stmt_execute($categoryStmt);
if (!mysqli_fetch_assoc(mysqli_stmt_get_result($categoryStmt))) {
    redirectMealForm($foodId ?: null, "The selected meal category does not exist.");
}

$currentImage = "";
if ($foodId !== null) {
    $mealStmt = mysqli_prepare($conn, "
        SELECT fi.image
        FROM food_items fi
        LEFT JOIN custom_meals cm ON cm.food_id = fi.food_id
        WHERE fi.food_id = ? AND cm.custom_meal_id IS NULL
        LIMIT 1
    ");
    mysqli_stmt_bind_param($mealStmt, "i", $foodId);
    mysqli_stmt_execute($mealStmt);
    $mealRow = mysqli_fetch_assoc(mysqli_stmt_get_result($mealStmt));
    if (!$mealRow) {
        header("Location: admin_meals.php?error=" . urlencode("Meal not found or it is a customized meal."));
        exit();
    }
    $currentImage = trim((string)($mealRow["image"] ?? ""));
}

$newImagePath = null;
$upload = $_FILES["meal_image"] ?? null;
if ($upload && (int)$upload["error"] !== UPLOAD_ERR_NO_FILE) {
    if ((int)$upload["error"] !== UPLOAD_ERR_OK) {
        redirectMealForm($foodId ?: null, "The meal image could not be uploaded.");
    }
    if ((int)$upload["size"] > 5 * 1024 * 1024) {
        redirectMealForm($foodId ?: null, "The meal image must not exceed 5 MB.");
    }
    if (!is_uploaded_file($upload["tmp_name"])) {
        redirectMealForm($foodId ?: null, "Invalid image upload.");
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($upload["tmp_name"]);
    $allowedTypes = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];
    if (!isset($allowedTypes[$mimeType])) {
        redirectMealForm($foodId ?: null, "Only JPG, PNG, and WEBP images are allowed.");
    }

    $uploadDirectory = __DIR__ . "/images/food";
    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0775, true) && !is_dir($uploadDirectory)) {
        redirectMealForm($foodId ?: null, "Unable to create the meal image folder.");
    }

    $fileName = "admin_" . date("Ymd_His") . "_" . bin2hex(random_bytes(5)) . "." . $allowedTypes[$mimeType];
    $absolutePath = $uploadDirectory . "/" . $fileName;
    if (!move_uploaded_file($upload["tmp_name"], $absolutePath)) {
        redirectMealForm($foodId ?: null, "Unable to save the uploaded meal image.");
    }
    $newImagePath = "images/food/" . $fileName;
}

$imagePath = $newImagePath ?? ($currentImage !== "" ? $currentImage : "images/logo.png");

try {
    mysqli_begin_transaction($conn);

    if ($foodId === null) {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO food_items
                (category_id, food_name, ingredients, portion_size, calories,
                 protein, carbohydrates, fat, sodium, image, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param(
            $stmt,
            "isssiddddss",
            $categoryId,
            $foodName,
            $ingredients,
            $portionSize,
            $calories,
            $protein,
            $carbohydrates,
            $fat,
            $sodium,
            $imagePath,
            $status
        );
        mysqli_stmt_execute($stmt);
        mysqli_commit($conn);
        header("Location: admin_meals.php?added=1");
        exit();
    }

    $stmt = mysqli_prepare($conn, "
        UPDATE food_items
        SET category_id = ?, food_name = ?, ingredients = ?, portion_size = ?,
            calories = ?, protein = ?, carbohydrates = ?, fat = ?, sodium = ?,
            image = ?, status = ?
        WHERE food_id = ?
    ");
    mysqli_stmt_bind_param(
        $stmt,
        "isssiddddssi",
        $categoryId,
        $foodName,
        $ingredients,
        $portionSize,
        $calories,
        $protein,
        $carbohydrates,
        $fat,
        $sodium,
        $imagePath,
        $status,
        $foodId
    );
    mysqli_stmt_execute($stmt);
    mysqli_commit($conn);

    if ($newImagePath !== null && $currentImage !== "" && str_starts_with($currentImage, "images/food/admin_")) {
        $oldAbsolutePath = __DIR__ . "/" . $currentImage;
        if (is_file($oldAbsolutePath)) {
            @unlink($oldAbsolutePath);
        }
    }

    header("Location: admin_meals.php?updated=1");
    exit();
} catch (Throwable $exception) {
    mysqli_rollback($conn);
    if ($newImagePath !== null) {
        $newAbsolutePath = __DIR__ . "/" . $newImagePath;
        if (is_file($newAbsolutePath)) {
            @unlink($newAbsolutePath);
        }
    }
    redirectMealForm($foodId ?: null, "The meal could not be saved. Please try again.");
}
