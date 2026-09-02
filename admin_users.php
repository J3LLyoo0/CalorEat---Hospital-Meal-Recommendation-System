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

$nutritionists = [];
$patients = [];

$nutritionistResult = mysqli_query($conn, "
    SELECT u.user_id, u.username, u.email, u.status, u.created_at,
           n.nutritionist_id, n.first_name, n.last_name, n.contact_number,
           n.gender, n.profile_image,
           (SELECT COUNT(*) FROM patients p WHERE p.nutritionist_id = n.nutritionist_id) AS patient_count
    FROM users u
    LEFT JOIN nutritionists n ON n.user_id = u.user_id
    WHERE u.role = 'nutritionist'
    ORDER BY u.created_at DESC, u.user_id DESC
");

if ($nutritionistResult) {
    while ($row = mysqli_fetch_assoc($nutritionistResult)) {
        $nutritionists[] = $row;
    }
}

$patientResult = mysqli_query($conn, "
    SELECT u.user_id, u.username, u.email, u.status, u.created_at,
           p.patient_id, p.first_name, p.last_name, p.contact_number,
           p.ic_number, p.date_of_birth, p.height, p.weight, p.activity_level,
           p.gender, p.medical_conditions, p.allergies, p.daily_calorie_intake,
           p.nutritionist_id,
           CONCAT_WS(' ', n.first_name, n.last_name) AS nutritionist_name
    FROM users u
    LEFT JOIN patients p ON p.user_id = u.user_id
    LEFT JOIN nutritionists n ON n.nutritionist_id = p.nutritionist_id
    WHERE u.role = 'patient'
    ORDER BY u.created_at DESC, u.user_id DESC
");

if ($patientResult) {
    while ($row = mysqli_fetch_assoc($patientResult)) {
        $patients[] = $row;
    }
}

function fullName($row) {
    $name = trim((string)(($row["first_name"] ?? "") . " " . ($row["last_name"] ?? "")));
    return $name !== "" ? $name : "Not completed";
}

$successMessage = "";
if (isset($_GET["updated"])) {
    $successMessage = "User information updated successfully.";
} elseif (isset($_GET["status_updated"])) {
    $successMessage = "Account status updated successfully.";
} elseif (isset($_GET["deleted"])) {
    $successMessage = "Patient account deleted successfully.";
}

$errorMessage = trim((string)($_GET["error"] ?? ""));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - CalorEat</title>
    <link rel="stylesheet" href="assets/css/admin.css?v=<?php echo filemtime(__DIR__ . '/assets/css/admin.css'); ?>">
</head>
<body>
<div class="admin-shell">
    <header class="admin-subheader">
        <a class="admin-back-link" href="admin_dashboard.php">← Dashboard</a>
        <div>
            <h1>User Management</h1>
            <p>Edit account information, control access, and safely remove unused patient accounts.</p>
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
            <div class="admin-panel-heading">
                <div>
                    <p class="admin-eyebrow">NUTRITIONISTS</p>
                    <h3>Nutritionist Accounts (<?php echo count($nutritionists); ?>)</h3>
                </div>
                <span class="admin-panel-note">Inactive accounts cannot log in.</span>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table admin-wide-table admin-user-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Gender</th>
                            <th>Patients</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($nutritionists) === 0): ?>
                        <tr><td colspan="8" class="admin-empty-cell">No nutritionist accounts found.</td></tr>
                    <?php else: foreach ($nutritionists as $nutritionist): ?>
                        <?php $nextStatus = $nutritionist["status"] === "active" ? "inactive" : "active"; ?>
                        <tr>
                            <td><?php echo h(fullName($nutritionist)); ?></td>
                            <td><?php echo h($nutritionist["username"] ?: "Not set"); ?></td>
                            <td><?php echo h($nutritionist["email"] ?: "Not set"); ?></td>
                            <td><?php echo h($nutritionist["contact_number"] ?: "Not set"); ?></td>
                            <td><?php echo h($nutritionist["gender"] ?: "Not set"); ?></td>
                            <td><?php echo number_format((int)$nutritionist["patient_count"]); ?></td>
                            <td><span class="admin-status-badge <?php echo h($nutritionist["status"]); ?>"><?php echo h(ucfirst($nutritionist["status"])); ?></span></td>
                            <td>
                                <div class="admin-action-group">
                                    <a class="admin-table-button neutral" href="admin_user_form.php?role=nutritionist&user_id=<?php echo (int)$nutritionist["user_id"]; ?>">Edit</a>
                                    <form action="admin_update_user_status.php" method="POST" onsubmit="return confirm('<?php echo $nextStatus === 'inactive' ? 'Deactivate this nutritionist account?' : 'Reactivate this nutritionist account?'; ?>');">
                                        <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION["admin_csrf_token"]); ?>">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$nutritionist["user_id"]; ?>">
                                        <input type="hidden" name="status" value="<?php echo h($nextStatus); ?>">
                                        <button class="admin-table-button <?php echo $nextStatus === "inactive" ? "warning" : "success"; ?>" type="submit">
                                            <?php echo $nextStatus === "inactive" ? "Deactivate" : "Activate"; ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-panel">
            <div class="admin-panel-heading">
                <div>
                    <p class="admin-eyebrow">PATIENTS</p>
                    <h3>Patient Accounts (<?php echo count($patients); ?>)</h3>
                </div>
                <span class="admin-panel-note">Use Deactivate when patient history must be preserved.</span>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table admin-wide-table admin-patient-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Nutritionist</th>
                            <th>Contact</th>
                            <th>Condition / Allergy</th>
                            <th>Daily Calories</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($patients) === 0): ?>
                        <tr><td colspan="8" class="admin-empty-cell">No patient accounts found.</td></tr>
                    <?php else: foreach ($patients as $patient): ?>
                        <?php $nextStatus = $patient["status"] === "active" ? "inactive" : "active"; ?>
                        <tr>
                            <td>
                                <strong><?php echo h(fullName($patient)); ?></strong>
                                <small><?php echo h($patient["email"] ?: "No email recorded"); ?></small>
                            </td>
                            <td><?php echo h($patient["username"] ?: "Not set"); ?></td>
                            <td><?php echo h(trim((string)$patient["nutritionist_name"]) ?: "Unassigned"); ?></td>
                            <td><?php echo h($patient["contact_number"] ?: "Not set"); ?></td>
                            <td>
                                <?php echo h($patient["medical_conditions"] ?: "None recorded"); ?>
                                <small>Allergies: <?php echo h($patient["allergies"] ?: "None recorded"); ?></small>
                            </td>
                            <td><?php echo $patient["daily_calorie_intake"] !== null ? number_format((int)$patient["daily_calorie_intake"]) . " kcal" : "Not calculated"; ?></td>
                            <td><span class="admin-status-badge <?php echo h($patient["status"]); ?>"><?php echo h(ucfirst($patient["status"])); ?></span></td>
                            <td>
                                <div class="admin-action-group">
                                    <a class="admin-table-button neutral" href="admin_user_form.php?role=patient&user_id=<?php echo (int)$patient["user_id"]; ?>">Edit</a>
                                    <form action="admin_update_user_status.php" method="POST" onsubmit="return confirm('<?php echo $nextStatus === 'inactive' ? 'Deactivate this patient account?' : 'Reactivate this patient account?'; ?>');">
                                        <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION["admin_csrf_token"]); ?>">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$patient["user_id"]; ?>">
                                        <input type="hidden" name="status" value="<?php echo h($nextStatus); ?>">
                                        <button class="admin-table-button <?php echo $nextStatus === "inactive" ? "warning" : "success"; ?>" type="submit">
                                            <?php echo $nextStatus === "inactive" ? "Deactivate" : "Activate"; ?>
                                        </button>
                                    </form>
                                    <form action="admin_delete_patient.php" method="POST" onsubmit="return confirm('Permanently delete this unused patient account? This cannot be undone.');">
                                        <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION["admin_csrf_token"]); ?>">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$patient["user_id"]; ?>">
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
            <h3>Safe patient deletion</h3>
            <p>A meal cannot be permanently deleted when it is already used in a recommendation or customized meal record. Hide the meal instead so previous record remain valid.</p>
        </section>
    </main>
</div>
</body>
</html>
