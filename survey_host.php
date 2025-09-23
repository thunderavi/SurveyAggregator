<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Step 1: Submit survey response
    if (isset($_POST['submit_answers'])) {
        $survey_id = $_POST['survey_id'];
        $responder_name = $_POST['name'];
        $answers = json_encode($_POST['answers']); // store as JSON

        $stmt = $conn->prepare("INSERT INTO responses (survey_id, responder_name, answers) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $survey_id, $responder_name, $answers);
        $stmt->execute();
        $stmt->close();

        echo "Thank you, your response has been recorded.";
        exit;
    }
    // Step 2: Show survey questions
    elseif (isset($_POST['select_survey'])) {
        $survey_id = $_POST['survey_id'];
        $survey_result = $conn->query("SELECT * FROM surveys WHERE survey_id=$survey_id");
        $survey = $survey_result->fetch_assoc();

        $questions_result = $conn->query("SELECT * FROM questions WHERE survey_id=$survey_id");

        echo "<h2>Survey: " . $survey['title'] . "</h2>";
        echo "<form method='POST'>";
        echo "<input type='hidden' name='survey_id' value='$survey_id'>";
        echo "Your Name: <input type='text' name='name' required><br><br>";

        while ($q = $questions_result->fetch_assoc()) {
            echo $q['question_text'] . "<br>";
            echo "<input type='radio' name='answers[" . $q['question_id'] . "]' value='Yes' required> Yes ";
            echo "<input type='radio' name='answers[" . $q['question_id'] . "]' value='No'> No <br><br>";
        }

        echo "<button type='submit' name='submit_answers'>Submit Answers</button>";
        echo "</form>";
        exit;
    }
}

// Default: Show survey selection
$result = $conn->query("SELECT * FROM surveys");
echo "<h2>Fill Survey</h2>";
if ($result->num_rows > 0) {
    echo "<form method='POST'>";
    echo "Your Name: <input type='text' name='name' required><br>";
    echo "Select Survey: <select name='survey_id'>";
    while ($row = $result->fetch_assoc()) {
        echo "<option value='{$row['survey_id']}'>{$row['title']}</option>";
    }
    echo "</select><br><br>";
    echo "<button type='submit' name='select_survey'>Next</button>";
    echo "</form>";
} else {
    echo "No surveys available.";
}
?>
