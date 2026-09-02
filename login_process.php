<?php
// login_process.php
// This checks the login form from login.php.

session_start();
require_once "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit();
}

$login_identifier = trim($_POST["username"] ?? ""); // username or email
$password = $_POST["password"] ?? "";

if ($login_identifier === "" || $password === "") {
    echo "<script>alert('Please enter username/email and password.'); window.location.href='login.php';</script>";
    exit();
}

$sql = "SELECT user_id, username, email, password, role, status
        FROM users
        WHERE username = ? OR email = ?
        LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $login_identifier, $login_identifier);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user || !password_verify($password, $user["password"])) {
    echo "<script>alert('Invalid username/email or password.'); window.location.href='login.php';</script>";
    exit();
}

if ($user["status"] !== "active") {
    echo "<script>alert('This account is inactive.'); window.location.href='login.php';</script>";
    exit();
}

// Start this login with a clean authenticated session.
// This prevents IDs from a previously logged-in role remaining in the session.
session_regenerate_id(true);
unset(
    $_SESSION["user_id"],
    $_SESSION["username"],
    $_SESSION["email"],
    $_SESSION["role"],
    $_SESSION["first_name"],
    $_SESSION["last_name"],
    $_SESSION["nutritionist_id"],
    $_SESSION["patient_id"],
    $_SESSION["admin_id"]
);

$_SESSION["user_id"] = (int)$user["user_id"];
$_SESSION["username"] = $user["username"];
$_SESSION["email"] = $user["email"];
$_SESSION["role"] = $user["role"];

if ($user["role"] === "nutritionist") {
    $profile_sql = "SELECT nutritionist_id, first_name, last_name FROM nutritionists WHERE user_id = ? LIMIT 1";
} elseif ($user["role"] === "patient") {
    $profile_sql = "SELECT patient_id, first_name, last_name FROM patients WHERE user_id = ? LIMIT 1";
} elseif ($user["role"] === "admin") {
    $profile_sql = "SELECT admin_id, first_name, last_name FROM admins WHERE user_id = ? LIMIT 1";
} else {
    session_unset();
    session_destroy();
    echo "<script>alert('Invalid account role.'); window.location.href='login.php';</script>";
    exit();
}

$profile_stmt = mysqli_prepare($conn, $profile_sql);
mysqli_stmt_bind_param($profile_stmt, "i", $user["user_id"]);
mysqli_stmt_execute($profile_stmt);
$profile_result = mysqli_stmt_get_result($profile_stmt);
$profile = mysqli_fetch_assoc($profile_result);

if ($profile) {
    $_SESSION["first_name"] = $profile["first_name"];
    $_SESSION["last_name"] = $profile["last_name"];

    if ($user["role"] === "nutritionist") {
        $_SESSION["nutritionist_id"] = (int)$profile["nutritionist_id"];
    } elseif ($user["role"] === "patient") {
        $_SESSION["patient_id"] = (int)$profile["patient_id"];
    } else {
        $_SESSION["admin_id"] = (int)$profile["admin_id"];
    }
}

if ($user["role"] === "admin") {
    header("Location: admin_dashboard.php");
} else {
    header("Location: index.php");
}
exit();
?>
