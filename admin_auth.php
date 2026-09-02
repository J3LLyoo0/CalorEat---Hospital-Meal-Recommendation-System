<?php
require_once "session_check.php";

if (($_SESSION["role"] ?? "") !== "admin") {
    header("Location: index.php");
    exit();
}
?>
