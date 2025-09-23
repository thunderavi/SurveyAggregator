<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

echo "Welcome, " . $_SESSION['username'] . "!<br>";
echo "<a href='create_survey.php'>Create Survey</a><br>";
echo "<a href='survey_host.php'>Fill Survey</a><br>";
echo "<a href='responses.php'>View Responses</a><br>";
echo "<a href='logout.php'>Logout</a>";
?>
