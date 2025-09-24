<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // Not logged in → redirect
    header("Location: login.php");
    exit();
}
?>
