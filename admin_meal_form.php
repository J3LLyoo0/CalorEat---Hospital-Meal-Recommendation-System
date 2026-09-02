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

$foodId = filter_input(INPUT_GET, "food_id", FILTER_VALIDATE_INT);
$isEditing = $foodId !== false && $foodId !== null && $foodId > 0;

$meal = [
    "food_id" => "",
    "category_id" => "",
    "food_name" => "",
    "ingredients" => "",
    "portion_size" => "1 serving",
    "calories" => "",
    "protein" => "",
    "carbohydrates" => "",
    "fat" => "",
    "sodium" => "",
    "image" => "",
    "status" => "active"
];

if ($isEditing) {
    $stmt = mysqli_prepare($conn, "
        SELECT fi.*
        FROM food_items fi
        LEFT JOIN custom_meals cm ON cm.food_id = fi.food_id
        WHERE fi.food_id = ? AND cm.custom_meal_id IS NULL
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, "i", $foodId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $storedMeal = mysqli_fetch_assoc($result);

    if (!$storedMeal) {
        header("Location: admin_meals.php?error=" . urlencode("Meal not found or it is a customized meal."));
        exit();
    }

    $meal = array_merge($meal, $storedMeal);
}

$categories = [];
$categoryResult = mysqli_query($conn, "
    SELECT category_id, category_name, status
    FROM meal_categories
    ORDER BY category_id ASC
");

if ($categoryResult) {
    while ($row = mysqli_fetch_assoc($categoryResult)) {
        $categories[] = $row;
    }
}

$currentImage = trim((string)$meal["image"]);
$currentImageExists = $currentImage !== "" && is_file(__DIR__ . "/" . $currentImage);
$errorMessage = trim((string)($_GET["error"] ?? ""));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEditing ? "Edit Meal" : "Add Meal"; ?> - CalorEat</title>
    <link rel="stylesheet" href="assets/css/admin.css?v=<?php echo filemtime(__DIR__ . '/assets/css/admin.css'); ?>">
</head>
<body>
<div class="admin-shell">
    <header class="admin-subheader">
        <a class="admin-back-link" href="admin_meals.php">← Meal Management</a>
        <div>
            <h1><?php echo $isEditing ? "Edit Meal" : "Add New Meal"; ?></h1>
            <p><?php echo $isEditing ? "Update the selected general meal." : "Create a new general meal for CalorEat."; ?></p>
        </div>
        <a class="admin-logout-button" href="logout.php">Logout</a>
    </header>

    <main class="admin-main admin-page-main">
        <?php if ($errorMessage !== ""): ?>
            <div class="admin-alert error"><?php echo h($errorMessage); ?></div>
        <?php endif; ?>

        <section class="admin-panel admin-form-panel">
            <form class="admin-meal-form" action="admin_save_meal.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION["admin_csrf_token"]); ?>">
                <input type="hidden" name="food_id" value="<?php echo $isEditing ? (int)$meal["food_id"] : ""; ?>">

                <div class="admin-form-section">
                    <div class="admin-form-section-heading">
                        <p class="admin-eyebrow">BASIC INFORMATION</p>
                        <h2>Meal details</h2>
                    </div>

                    <div class="admin-form-grid two-columns">
                        <label class="admin-form-field admin-form-field-wide">
                            <span>Meal Name <em>*</em></span>
                            <input type="text" name="food_name" maxlength="150" required value="<?php echo h($meal["food_name"]); ?>" placeholder="Example: Steamed Chicken with Vegetables">
                        </label>

                        <label class="admin-form-field">
                            <span>Category <em>*</em></span>
                            <select name="category_id" required>
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo (int)$category["category_id"]; ?>" <?php echo (int)$meal["category_id"] === (int)$category["category_id"] ? "selected" : ""; ?>>
                                        <?php echo h($category["category_name"]); ?><?php echo $category["status"] === "inactive" ? " (Inactive category)" : ""; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label class="admin-form-field">
                            <span>Portion Size</span>
                            <input type="text" name="portion_size" maxlength="50" value="<?php echo h($meal["portion_size"]); ?>" placeholder="Example: 1 serving">
                        </label>

                        <label class="admin-form-field admin-form-field-wide">
                            <span>Core Ingredients</span>
                            <textarea name="ingredients" rows="5" placeholder="Separate ingredients using commas"><?php echo h($meal["ingredients"]); ?></textarea>
                        </label>
                    </div>
                </div>

                <div class="admin-form-section">
                    <div class="admin-form-section-heading">
                        <p class="admin-eyebrow">NUTRITION</p>
                        <h2>Calories and nutrients</h2>
                    </div>

                    <div class="admin-form-grid nutrition-grid">
                        <label class="admin-form-field">
                            <span>Calories (kcal) <em>*</em></span>
                            <input type="number" name="calories" min="0" max="99999" step="1" required value="<?php echo h($meal["calories"]); ?>">
                        </label>

                        <label class="admin-form-field">
                            <span>Protein (g)</span>
                            <input type="number" name="protein" min="0" max="9999.99" step="0.01" value="<?php echo h($meal["protein"]); ?>">
                        </label>

                        <label class="admin-form-field">
                            <span>Carbohydrates (g)</span>
                            <input type="number" name="carbohydrates" min="0" max="9999.99" step="0.01" value="<?php echo h($meal["carbohydrates"]); ?>">
                        </label>

                        <label class="admin-form-field">
                            <span>Fat (g)</span>
                            <input type="number" name="fat" min="0" max="9999.99" step="0.01" value="<?php echo h($meal["fat"]); ?>">
                        </label>

                        <label class="admin-form-field">
                            <span>Sodium (mg)</span>
                            <input type="number" name="sodium" min="0" max="9999.99" step="0.01" value="<?php echo h($meal["sodium"]); ?>">
                        </label>

                        <label class="admin-form-field">
                            <span>Visibility <em>*</em></span>
                            <select name="status" required>
                                <option value="active" <?php echo $meal["status"] === "active" ? "selected" : ""; ?>>Active — visible to users</option>
                                <option value="inactive" <?php echo $meal["status"] === "inactive" ? "selected" : ""; ?>>Inactive — hidden from users</option>
                            </select>
                        </label>
                    </div>
                </div>

                <div class="admin-form-section">
                    <div class="admin-form-section-heading">
                        <p class="admin-eyebrow">MEAL IMAGE</p>
                        <h2>Upload image</h2>
                    </div>

                    <div class="admin-image-upload-layout">
                        <div class="admin-image-preview-box">
                            <img id="adminMealImagePreview" src="<?php echo h($currentImageExists ? $currentImage : 'images/logo.png'); ?>" alt="Meal image preview">
                        </div>

                        <label class="admin-form-field admin-file-field">
                            <span><?php echo $isEditing ? "Replace Image" : "Meal Image"; ?></span>
                            <input id="adminMealImageInput" type="file" name="meal_image" accept="image/jpeg,image/png,image/webp">
                            <small>JPG, PNG, or WEBP. Maximum file size: 5 MB. <?php echo $isEditing ? "Leave empty to keep the current image." : "A logo placeholder is used when no image is selected."; ?></small>
                        </label>
                    </div>
                </div>

                <div class="admin-form-actions">
                    <a class="admin-secondary-button" href="admin_meals.php">Cancel</a>
                    <button class="admin-primary-button" type="submit"><?php echo $isEditing ? "Save Changes" : "Add Meal"; ?></button>
                </div>
            </form>
        </section>
    </main>
</div>
<script src="assets/js/admin-meal-form.js?v=<?php echo filemtime(__DIR__ . '/assets/js/admin-meal-form.js'); ?>"></script>
</body>
</html>
