<?php
require_once "admin_auth.php";
require_once "db_connect.php";

if (!function_exists("h")) {
    function h($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
    }
}

function countRows($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_row($result);
    return (int)($row[0] ?? 0);
}

$userId = (int)($_SESSION["user_id"] ?? 0);
$adminName = trim((string)(($_SESSION["first_name"] ?? "") . " " . ($_SESSION["last_name"] ?? "")));
if ($adminName === "") {
    $adminName = $_SESSION["username"] ?? "Admin";
}

$nutritionistCount = countRows($conn, "SELECT COUNT(*) FROM users WHERE role = 'nutritionist'");
$patientCount = countRows($conn, "SELECT COUNT(*) FROM users WHERE role = 'patient'");
$activeMealCount = countRows($conn, "
    SELECT COUNT(*)
    FROM food_items fi
    LEFT JOIN custom_meals cm ON cm.food_id = fi.food_id
    WHERE cm.custom_meal_id IS NULL AND fi.status = 'active'
");
$inactiveMealCount = countRows($conn, "
    SELECT COUNT(*)
    FROM food_items fi
    LEFT JOIN custom_meals cm ON cm.food_id = fi.food_id
    WHERE cm.custom_meal_id IS NULL AND fi.status = 'inactive'
");

$profileImage = "images/user.png";
$stmt = mysqli_prepare($conn, "SELECT profile_image FROM users WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$imageRow = mysqli_fetch_assoc($result);
$storedImage = trim((string)($imageRow["profile_image"] ?? ""));
if ($storedImage !== "" && is_file(__DIR__ . "/" . $storedImage)) {
    $profileImage = $storedImage . "?v=" . filemtime(__DIR__ . "/" . $storedImage);
}

$recentUsers = [];
$recentResult = mysqli_query($conn, "
    SELECT username, email, role, status, created_at
    FROM users
    WHERE role IN ('nutritionist', 'patient')
    ORDER BY created_at DESC, user_id DESC
    LIMIT 6
");

if ($recentResult) {
    while ($row = mysqli_fetch_assoc($recentResult)) {
        $recentUsers[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CalorEat</title>
    <link rel="stylesheet" href="assets/css/admin.css?v=1">
</head>
<body>
<div class="admin-shell">
    <header class="admin-header">
        <div class="admin-brand">
            <img src="images/logo.png" alt="CalorEat Logo">
            <div>
                <h1>CalorEat</h1>
                <p>Administration Dashboard</p>
            </div>
        </div>

        <div class="admin-header-actions">
            <div class="admin-welcome">
                <span>Welcome,</span>
                <strong><?php echo h($adminName); ?></strong>
            </div>
            <a class="admin-profile-link" href="account.php" title="Admin account">
                <img src="<?php echo h($profileImage); ?>" alt="Admin Profile">
            </a>
            <a class="admin-logout-button" href="logout.php">Logout</a>
        </div>
    </header>

    <main class="admin-main">
        <section class="admin-intro">
            <div>
                <p class="admin-eyebrow">SYSTEM OVERVIEW</p>
                <h2>Admin Dashboard</h2>
                <p>Manage CalorEat users, meal records, and meal visibility from one place.</p>
            </div>
        </section>

        <section class="admin-stat-grid" aria-label="System statistics">
            <article class="admin-stat-card">
                <span class="admin-stat-label">Nutritionists</span>
                <strong><?php echo $nutritionistCount; ?></strong>
                <p>Registered nutritionist accounts</p>
            </article>

            <article class="admin-stat-card">
                <span class="admin-stat-label">Patients</span>
                <strong><?php echo $patientCount; ?></strong>
                <p>Registered patient accounts</p>
            </article>

            <article class="admin-stat-card">
                <span class="admin-stat-label">Active Meals</span>
                <strong><?php echo $activeMealCount; ?></strong>
                <p>Meals visible to system users</p>
            </article>

            <article class="admin-stat-card">
                <span class="admin-stat-label">Inactive Meals</span>
                <strong><?php echo $inactiveMealCount; ?></strong>
                <p>Meals currently hidden from users</p>
            </article>
        </section>

        <section class="admin-action-grid">
            <a class="admin-action-card" href="admin_users.php">
                <div class="admin-action-icon">U</div>
                <div>
                    <h3>Manage Users</h3>
                    <p>View nutritionist and patient account information.</p>
                </div>
                <span class="admin-action-arrow">→</span>
            </a>

            <a class="admin-action-card" href="admin_meals.php">
                <div class="admin-action-icon">M</div>
                <div>
                    <h3>Manage Meals</h3>
                    <p>Add, edit, show, hide, or safely delete general meals.</p>
                </div>
                <span class="admin-action-arrow">→</span>
            </a>

            <a class="admin-action-card" href="account.php">
                <div class="admin-action-icon">A</div>
                <div>
                    <h3>Admin Account</h3>
                    <p>Update your name, contact number, email, or password.</p>
                </div>
                <span class="admin-action-arrow">→</span>
            </a>
        </section>

        <section class="admin-panel">
            <div class="admin-panel-heading">
                <div>
                    <p class="admin-eyebrow">LATEST RECORDS</p>
                    <h3>Recently Registered Users</h3>
                </div>
                <a href="admin_users.php">View all users</a>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($recentUsers) === 0): ?>
                        <tr><td colspan="5" class="admin-empty-cell">No user records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentUsers as $recentUser): ?>
                            <tr>
                                <td><?php echo h($recentUser["username"] ?: "Not set"); ?></td>
                                <td><?php echo h($recentUser["email"] ?: "Not set"); ?></td>
                                <td><span class="admin-role-badge"><?php echo h(ucfirst($recentUser["role"])); ?></span></td>
                                <td><span class="admin-status-badge <?php echo h($recentUser["status"]); ?>"><?php echo h(ucfirst($recentUser["status"])); ?></span></td>
                                <td><?php echo h(date("d M Y", strtotime($recentUser["created_at"]))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
</body>
</html>
