<?php
require_once "session_check.php";
require_once "db_connect.php";

$userId = $_SESSION["user_id"];

$currentPassword = $_POST["current_password"] ?? "";
$newPassword = $_POST["new_password"] ?? "";
$confirmPassword = $_POST["confirm_password"] ?? "";

if ($currentPassword === "" || $newPassword === "" || $confirmPassword === "") {
    echo "<script>alert('Please fill in all password fields.'); window.location.href='account.php';</script>";
    exit();
}

if ($newPassword !== $confirmPassword) {
    echo "<script>alert('New password and confirm password do not match.'); window.location.href='account.php';</script>";
    exit();
}

if (strlen($newPassword) < 6) {
    echo "<script>alert('New password must be at least 6 characters.'); window.location.href='account.php';</script>";
    exit();
}

$stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user || !password_verify($currentPassword, $user["password"])) {
    echo "<script>alert('Current password is incorrect.'); window.location.href='account.php';</script>";
    exit();
}

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "si", $hashedPassword, $userId);

if (mysqli_stmt_execute($stmt)) {
    echo "<script>alert('Password updated successfully. Please log in again.'); window.location.href='logout.php';</script>";
    exit();
}

echo "<script>alert('Failed to update password.'); window.location.href='account.php';</script>";
exit();
?>