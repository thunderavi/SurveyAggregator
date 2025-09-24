<?php
include 'db.php';
include 'auth_check.php';

echo "<h2>Survey Responses</h2>";

// Get all surveys
$survey_result = $conn->query("SELECT * FROM surveys");
while ($survey = $survey_result->fetch_assoc()) {
    echo "<h3>Survey: " . $survey['title'] . "</h3>";

    // Get questions
    $questions_result = $conn->query("SELECT * FROM questions WHERE survey_id=" . $survey['survey_id']);
    $questions = [];
    while ($q = $questions_result->fetch_assoc()) {
        $questions[$q['question_id']] = $q['question_text'];
    }

    // Get responses
    $responses_result = $conn->query("SELECT * FROM responses WHERE survey_id=" . $survey['survey_id']);
    if ($responses_result->num_rows > 0) {
        while ($r = $responses_result->fetch_assoc()) {
            echo "Responder: " . $r['responder_name'] . "<br>";
            $answers = json_decode($r['answers'], true);
            foreach ($answers as $qid => $ans) {
                echo $questions[$qid] . " : " . $ans . "<br>";
            }
            echo "<hr>";
        }
    } else {
        echo "No responses yet.<hr>";
    }
}
?>
