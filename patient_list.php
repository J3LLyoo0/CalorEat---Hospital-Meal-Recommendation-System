<?php
require_once "session_check.php";
require_once "db_connect.php";

if ($_SESSION["role"] !== "nutritionist") {
    echo "<script>alert('Only nutritionists can view this page.'); window.location.href='index.php';</script>";
    exit();
}

$nutritionist_id = (int)$_SESSION["nutritionist_id"];

$sql = "SELECT p.patient_id, p.first_name, p.last_name, u.email, p.contact_number, p.gender,
               p.height, p.weight, p.activity_level, p.medical_conditions, p.allergies,
               p.daily_calorie_intake
        FROM patients p
        INNER JOIN users u ON p.user_id = u.user_id
        WHERE p.nutritionist_id = ?
        ORDER BY p.patient_id DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $nutritionist_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient List - CalorEat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7f6;
            margin: 0;
            padding: 30px;
            color: #000066;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .btn {
            background: #8bae79;
            color: white;
            padding: 10px 16px;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #e5e5e5;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #8bae79;
            color: white;
        }
        tr:hover {
            background: #f0faf7;
        }
    </style>
</head>
<body>
    <div class="page-header">
        <h1>Registered Patients</h1>
        <a class="btn" href="index.php">Back to Main Page</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Contact</th>
                <th>Gender</th>
                <th>Height / Weight</th>
                <th>Activity</th>
                <th>Medical Conditions</th>
                <th>Allergies</th>
                <th>Daily Calories</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) === 0): ?>
                <tr>
                    <td colspan="9">No patients registered yet.</td>
                </tr>
            <?php endif; ?>

            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= htmlspecialchars(trim($row["first_name"] . " " . $row["last_name"])) ?></td>
                    <td><?= htmlspecialchars($row["email"]) ?></td>
                    <td><?= htmlspecialchars($row["contact_number"] ?? "") ?></td>
                    <td><?= htmlspecialchars($row["gender"] ?? "") ?></td>
                    <td><?= htmlspecialchars($row["height"] ?? "") ?> cm / <?= htmlspecialchars($row["weight"] ?? "") ?> kg</td>
                    <td><?= htmlspecialchars($row["activity_level"] ?? "") ?></td>
                    <td><?= nl2br(htmlspecialchars($row["medical_conditions"] ?? "")) ?></td>
                    <td><?= nl2br(htmlspecialchars($row["allergies"] ?? "")) ?></td>
                    <td><?= htmlspecialchars($row["daily_calorie_intake"] ?? "") ?> kcal</td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
