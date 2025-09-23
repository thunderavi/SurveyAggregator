<?php
session_start();
?>

<!-- Navbar -->
<nav>
    <a href="dashboard.php">Dashboard</a> | 
    <a href="create_survey.php">Create Survey</a> | 
    <a href="survey_host.php">Fill Survey</a> | 
    <a href="response.php">View Responses</a> | 
    <?php if(isset($_SESSION['user_id'])): ?>
        <a href="logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a>
    <?php else: ?>
        <a href="login.php">Login</a> | 
        <a href="register.php">Register</a>
    <?php endif; ?>
</nav>
<hr>

<!-- Landing Content -->
<h2>Welcome to Survey Aggregator</h2>
<p>This is a simple PHP & MySQL based survey system.</p>
<p>Use the navbar above to navigate.</p>
