<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = [];

// Remove the browser's PHP session cookie as well as server-side data.
if (ini_get("session.use_cookies")) {
    $cookieParams = session_get_cookie_params();
    setcookie(
        session_name(),
        "",
        time() - 42000,
        $cookieParams["path"],
        $cookieParams["domain"],
        $cookieParams["secure"],
        $cookieParams["httponly"]
    );
}

session_destroy();
header("Location: login.php");
exit();
?>
