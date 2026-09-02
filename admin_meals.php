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

$meals = [];
$mealResult = mysqli_query($conn, "
    SELECT fi.food_id, fi.food_name, fi.ingredients, fi.portion_size, fi.calories,
           fi.image, fi.status, fi.created_at, mc.category_name
    FROM food_items fi
    INNER JOIN meal_categories mc ON mc.category_id = fi.category_id
    LEFT JOIN custom_meals cm ON cm.food_id = fi.food_id
    WHERE cm.custom_meal_id IS NULL
    ORDER BY mc.category_id ASC, fi.food_name ASC
");

if ($mealResult) {
    while ($row = mysqli_fetch_assoc($mealResult)) {
        $meals[] = $row;
    }
}

$successMessage = "";
if (isset($_GET["added"])) {
    $successMessage = "New meal added successfully.";
} elseif (isset($_GET["updated"])) {
    $successMessage = "Meal information updated successfully.";
} elseif (isset($_GET["status_updated"])) {
    $successMessage = "Meal visibility updated successfully.";
} elseif (isset($_GET["deleted"])) {
    $successMessage = "Meal deleted successfully.";
}

$errorMessage = trim((string)($_GET["error"] ?? ""));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Meals - CalorEat</title>
    <link rel="stylesheet" href="assets/css/admin.css?v=<?php echo filemtime(__DIR__ . '/assets/css/admin.css'); ?>">
</head>
<body>
<div class="admin-shell">
    <header class="admin-subheader">
        <a class="admin-back-link" href="admin_dashboard.php">← Dashboard</a>
        <div>
            <h1>Meal Management</h1>
            <p>Add, edit, show, hide, and safely delete general meals.</p>
        </div>
        <a class="admin-logout-button" href="logout.php">Logout</a>
    </header>

    <main class="admin-main admin-page-main">
        <?php if ($successMessage !== ""): ?>
            <div class="admin-alert success"><?php echo h($successMessage); ?></div>
        <?php endif; ?>

        <?php if ($errorMessage !== ""): ?>
            <div class="admin-alert error"><?php echo h($errorMessage); ?></div>
        <?php endif; ?>

        <section class="admin-panel">
            <div class="admin-panel-heading admin-meal-heading">
                <div>
                    <p class="admin-eyebrow">GENERAL MEALS</p>
                    <h3>Meal Records (<?php echo count($meals); ?>)</h3>
                    <span class="admin-panel-note admin-panel-note-left">Customized meals are not changed from this page.</span>
                </div>

                <a class="admin-primary-button" href="admin_meal_form.php">+ Add New Meal</a>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table admin-wide-table admin-meal-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Meal</th>
                            <th>Category</th>
                            <th>Portion</th>
                            <th>Calories</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($meals) === 0): ?>
                        <tr><td colspan="7" class="admin-empty-cell">No general meals found.</td></tr>
                    <?php else: foreach ($meals as $meal): ?>
                        <?php
                            $imagePath = trim((string)($meal["image"] ?? ""));
                            if ($imagePath === "" || !is_file(__DIR__ . "/" . $imagePath)) {
                                $imagePath = "images/logo.png";
                            }
                            $nextStatus = $meal["status"] === "active" ? "inactive" : "active";
                        ?>
                        <tr>
                            <td><img class="admin-meal-thumb" src="<?php echo h($imagePath); ?>" alt="<?php echo h($meal["food_name"]); ?>"></td>
                            <td>
                                <strong><?php echo h($meal["food_name"]); ?></strong>
                                <small><?php echo h($meal["ingredients"] ?: "Ingredients not recorded"); ?></small>
                            </td>
                            <td><?php echo h($meal["category_name"]); ?></td>
                            <td><?php echo h($meal["portion_size"] ?: "Not set"); ?></td>
                            <td><?php echo number_format((int)$meal["calories"]); ?> kcal</td>
                            <td><span class="admin-status-badge <?php echo h($meal["status"]); ?>"><?php echo h(ucfirst($meal["status"])); ?></span></td>
                            <td>
                                <div class="admin-action-group">
                                    <a class="admin-table-button neutral" href="admin_meal_form.php?food_id=<?php echo (int)$meal["food_id"]; ?>">Edit</a>

                                    <form action="admin_update_meal_status.php" method="POST" onsubmit="return confirm('Change this meal to <?php echo h($nextStatus); ?>?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION["admin_csrf_token"]); ?>">
                                        <input type="hidden" name="food_id" value="<?php echo (int)$meal["food_id"]; ?>">
                                        <input type="hidden" name="status" value="<?php echo h($nextStatus); ?>">
                                        <button class="admin-table-button <?php echo $nextStatus === "inactive" ? "warning" : "success"; ?>" type="submit">
                                            <?php echo $nextStatus === "inactive" ? "Hide" : "Show"; ?>
                                        </button>
                                    </form>

                                    <form action="admin_delete_meal.php" method="POST" onsubmit="return confirm('Permanently delete this meal? This cannot be undone.');">
                                        <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION["admin_csrf_token"]); ?>">
                                        <input type="hidden" name="food_id" value="<?php echo (int)$meal["food_id"]; ?>">
                                        <button class="admin-table-button danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-info-panel">
            <h3>How safe deletion works</h3>
            <p>A meal cannot be permanently deleted when it is already used in a recommendation, customized meal, or personalized meal record. Hide the meal instead so previous records remain valid.</p>
        </section>
    </main>
</div>
</body>
</html>
