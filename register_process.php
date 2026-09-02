<?php
// register_process.php
// This creates a nutritionist account from register.php.

require_once "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.php");
    exit();
}

$username = trim($_POST["username"] ?? "");
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";
$confirm_password = $_POST["confirm_password"] ?? "";

if ($username === "" || $email === "" || $password === "" || $confirm_password === "") {
    echo "<script>alert('Please fill in all required fields.'); window.location.href='register.php';</script>";
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('Please enter a valid email address.'); window.location.href='register.php';</script>";
    exit();
}

if ($password !== $confirm_password) {
    echo "<script>alert('Passwords do not match.'); window.location.href='register.php';</script>";
    exit();
}

$email_check_sql = "
SELECT user_id
FROM users
WHERE email = ?
LIMIT 1";

$email_check_stmt = mysqli_prepare($conn, $email_check_sql);
mysqli_stmt_bind_param($email_check_stmt, "s", $email);
mysqli_stmt_execute($email_check_stmt);

$email_check_result = mysqli_stmt_get_result($email_check_stmt);

if (mysqli_fetch_assoc($email_check_result)) {
    echo "<script>alert('This email is already registered.')
        window.location.href='index.php';
    </script>";
    exit();
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

mysqli_begin_transaction($conn);

try {
    $user_sql = "INSERT INTO users (username, email, password, role, status)
                 VALUES (?, ?, ?, 'nutritionist', 'active')";
    $user_stmt = mysqli_prepare($conn, $user_sql);
    mysqli_stmt_bind_param($user_stmt, "sss", $username, $email, $hashed_password);
    mysqli_stmt_execute($user_stmt);

    $user_id = mysqli_insert_id($conn);

    // For now, username is saved as the nutritionist's first name.
    //The user can update first name, last name, and contact number later in account.php.
    $nutritionist_sql = "INSERT INTO nutritionists (user_id, first_name, last_name, contact_number)
                         VALUES (?, ?, '', '')";
    $nutritionist_stmt = mysqli_prepare($conn, $nutritionist_sql);
    mysqli_stmt_bind_param($nutritionist_stmt, "is", $user_id, $username);
    mysqli_stmt_execute($nutritionist_stmt);

    mysqli_commit($conn);

    echo "<script>alert('Account registered successfully. Please log in.'); window.location.href='login.php';</script>";
    exit();
} catch (mysqli_sql_exception $e) {
    mysqli_rollback($conn);

    if (stripos($e->getMessage(), 'Duplicate') !== false) {
        echo "<script>alert('This username or email is already registered.'); window.location.href='register.php';</script>";
        exit();
    }

    die("Database error: " . $e->getMessage());
}
?>
