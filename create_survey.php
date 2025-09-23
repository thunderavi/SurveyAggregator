<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $user_id = $_SESSION['user_id'];

    // Insert survey
    $stmt = $conn->prepare("INSERT INTO surveys (owner_id, title) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $title);
    $stmt->execute();
    $survey_id = $stmt->insert_id;
    $stmt->close();

    // Insert questions
    $questions = $_POST['questions']; // array of question_text
    foreach ($questions as $q) {
        $stmt = $conn->prepare("INSERT INTO questions (survey_id, question_text, type) VALUES (?, ?, 'radio')");
        $stmt->bind_param("is", $survey_id, $q);
        $stmt->execute();
        $stmt->close();
    }

    echo "Survey created successfully. <a href='dashboard.php'>Go back</a>";
}
?>

<h2>Create Survey</h2>
<form method="POST">
    Survey Title: <input type="text" name="title" required><br>
    Questions:<br>
    <input type="text" name="questions[]" required><br>
    <input type="text" name="questions[]" required><br>
    <input type="text" name="questions[]" required><br>
    <button type="submit">Create Survey</button>
</form>
