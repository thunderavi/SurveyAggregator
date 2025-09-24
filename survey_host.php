<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if tables and columns exist
$has_description = false;
$has_options_table = false;
$has_is_required = false;

try {
    $result = $conn->query("SHOW COLUMNS FROM surveys LIKE 'description'");
    $has_description = $result->num_rows > 0;
    
    $result = $conn->query("SHOW TABLES LIKE 'question_options'");
    $has_options_table = $result->num_rows > 0;
    
    $result = $conn->query("SHOW COLUMNS FROM questions LIKE 'is_required'");
    $has_is_required = $result->num_rows > 0;
} catch (Exception $e) {
    // Continue with basic functionality
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Debug: Let's see what POST data we're getting
    // error_log("POST data: " . print_r($_POST, true));
    
    // Step 1: Submit survey response
    if (isset($_POST['submit_answers'])) {
        $survey_id = $_POST['survey_id'];
        $responder_name = $_POST['name'];
        $user_id = $_SESSION['user_id'];
        $answers = $_POST['answers'] ?? [];
        
        // Convert answers to JSON
        $answers_json = json_encode($answers);

        try {
            $stmt = $conn->prepare("INSERT INTO responses (survey_id, responder_name, answers) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $survey_id, $responder_name, $answers_json);
            $stmt->execute();
            $stmt->close();

            $success_message = "Thank you! Your response has been recorded successfully.";
        } catch (Exception $e) {
            $error_message = "Error submitting response: " . $e->getMessage();
        }
    }
    // Step 2: Show survey questions
    elseif (isset($_POST['select_survey'])) {
        $survey_id = intval($_POST['survey_id']);
        $show_survey = true;
        
        try {
            // Get survey details
            $survey_query = "SELECT * FROM surveys WHERE survey_id = ?";
            $stmt = $conn->prepare($survey_query);
            $stmt->bind_param("i", $survey_id);
            $stmt->execute();
            $survey_result = $stmt->get_result();
            $survey = $survey_result->fetch_assoc();
            $stmt->close();
            
            if (!$survey) {
                $error_message = "Survey not found.";
                $show_survey = false;
            } else {
                // Get questions - check which column name is used for questions
                $question_id_column = 'question_id';
                $result = $conn->query("SHOW COLUMNS FROM questions");
                $columns = $result->fetch_all(MYSQLI_ASSOC);
                
                foreach ($columns as $column) {
                    if ($column['Key'] == 'PRI') {
                        $question_id_column = $column['Field'];
                        break;
                    }
                }
                
                $survey_id_field = $survey['survey_id'];
                $stmt = $conn->prepare("SELECT * FROM questions WHERE survey_id = ? ORDER BY $question_id_column");
                $stmt->bind_param("i", $survey_id_field);
                $stmt->execute();
                $questions_result = $stmt->get_result();
                $questions = $questions_result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
                
                // Get options for each question if options table exists
                if ($has_options_table) {
                    foreach ($questions as &$question) {
                        $q_id = $question[$question_id_column];
                        $stmt = $conn->prepare("SELECT * FROM question_options WHERE question_id = ? ORDER BY option_order, id");
                        $stmt->bind_param("i", $q_id);
                        $stmt->execute();
                        $options_result = $stmt->get_result();
                        $question['options'] = $options_result->fetch_all(MYSQLI_ASSOC);
                        $stmt->close();
                    }
                }
                
                // Debug: Check if we have questions
                if (empty($questions)) {
                    $error_message = "No questions found for this survey.";
                    $show_survey = false;
                }
            }
        } catch (Exception $e) {
            $error_message = "Error loading survey: " . $e->getMessage();
            $show_survey = false;
        }
    }
}

// Default: Show survey selection
if (!isset($show_survey)) {
    // Check which primary key column exists
    $result = $conn->query("SHOW COLUMNS FROM surveys");
    $columns = $result->fetch_all(MYSQLI_ASSOC);
    $primary_key = 'survey_id'; // default
    
    foreach ($columns as $column) {
        if ($column['Key'] == 'PRI') {
            $primary_key = $column['Field'];
            break;
        }
    }
    
    $surveys_query = "SELECT * FROM surveys ORDER BY $primary_key DESC";
    $result = $conn->query($surveys_query);
    $surveys = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Survey - Survey Aggregator</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --dark-bg: #0f172a;
            --card-bg: #1e293b;
            --border-color: #334155;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--dark-bg);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Navigation - Same as dashboard */
        .navbar {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--text-primary) !important;
        }

        .nav-link {
            color: var(--text-secondary) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .nav-link:hover {
            color: var(--text-primary) !important;
            background: rgba(99, 102, 241, 0.1);
        }

        .nav-link.active {
            color: var(--text-primary) !important;
            background: rgba(99, 102, 241, 0.15);
        }

        .user-badge {
            background: var(--primary-color);
            border-radius: 6px;
            padding: 0.5rem 1rem;
            color: white !important;
        }

        /* Main Content */
        .main-content {
            padding: 2rem 0;
            min-height: calc(100vh - 76px);
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
        }

        /* Survey Cards */
        .survey-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.2s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .survey-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary-color);
            transform: scaleX(0);
            transition: transform 0.2s ease;
        }

        .survey-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
        }

        .survey-card:hover::before {
            transform: scaleX(1);
        }

        .survey-header {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .survey-icon {
            width: 48px;
            height: 48px;
            background: var(--primary-color);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            flex-shrink: 0;
        }

        .survey-info h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .survey-info p {
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin: 0;
        }

        /* Form Styling */
        .survey-form {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control, .form-select {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.75rem;
            color: var(--text-primary);
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            color: var(--text-primary);
        }

        /* Question Styling */
        .question-item {
            background: rgba(15, 23, 42, 0.3);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .question-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .question-number {
            background: var(--primary-color);
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 600;
            flex-shrink: 0;
        }

        .question-text {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--text-primary);
            flex: 1;
        }

        .required-badge {
            background: var(--danger-color);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        /* Form Options */
        .form-check {
            margin-bottom: 0.75rem;
        }

        .form-check-input {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid var(--border-color);
            margin-top: 0.25rem;
        }

        .form-check-input:checked {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .form-check-label {
            color: var(--text-secondary);
            margin-left: 0.5rem;
        }

        /* Buttons */
        .btn-primary {
            background: var(--primary-color);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: var(--primary-color);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            background: rgba(99, 102, 241, 0.15);
            border-color: var(--primary-color);
        }

        /* Alert Messages */
        .alert {
            border: none;
            border-radius: 8px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--border-color);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem 0;
            }
            
            .survey-form {
                padding: 1.5rem;
            }
            
            .question-item {
                padding: 1.25rem;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.3s ease;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-clipboard-data me-2"></i>
                Survey Aggregator
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list text-white fs-4"></i>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="create_survey.php">
                            <i class="bi bi-plus-circle me-1"></i>Create Survey
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="survey_host.php">
                            <i class="bi bi-clipboard-check me-1"></i>Take Survey
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="response.php">
                            <i class="bi bi-graph-up me-1"></i>Analytics
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <span class="user-badge">
                                <i class="bi bi-person-circle me-2"></i>
                                <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li><a class="dropdown-item" href="dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a></li>
                            <li><a class="dropdown-item" href="create_survey.php">
                                <i class="bi bi-plus-circle me-2"></i>Create Survey
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content" style="margin-top: 76px;">
        <div class="container">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success fade-in">
                    <i class="bi bi-check-circle me-2"></i>
                    <?php echo $success_message; ?>
                    <div class="mt-2">
                        <a href="survey_host.php" class="btn-secondary">Take Another Survey</a>
                        <a href="dashboard.php" class="btn-secondary ms-2">Back to Dashboard</a>
                    </div>
                </div>
            <?php elseif (isset($error_message)): ?>
                <div class="alert alert-danger fade-in">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($show_survey) && $show_survey && !empty($questions)): ?>
                <!-- Show Survey Questions -->
                <div class="page-header">
                    <h1 class="page-title"><?php echo htmlspecialchars($survey['title']); ?></h1>
                    <?php if ($has_description && !empty($survey['description'])): ?>
                        <p class="page-subtitle"><?php echo htmlspecialchars($survey['description']); ?></p>
                    <?php endif; ?>
                </div>

                <form method="POST" class="survey-form fade-in">
                    <input type="hidden" name="survey_id" value="<?php echo $survey['survey_id']; ?>">
                    
                    <div class="form-group">
                        <label class="form-label" for="name">Your Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required 
                               placeholder="Enter your full name">
                    </div>

                    <?php foreach ($questions as $index => $question): ?>
                        <div class="question-item">
                            <div class="question-header">
                                <div class="question-number"><?php echo $index + 1; ?></div>
                                <div class="question-text"><?php echo htmlspecialchars($question['question_text']); ?></div>
                                <?php if ($has_is_required && !empty($question['is_required'])): ?>
                                    <div class="required-badge">Required</div>
                                <?php endif; ?>
                            </div>

                            <?php 
                            // Determine the correct question ID column
                            $question_id_column = 'question_id';
                            if (!isset($question['question_id']) && isset($question['id'])) {
                                $question_id_column = 'id';
                            }
                            $question_id = $question[$question_id_column];
                            $question_type = $question['type'];
                            $is_required = ($has_is_required && !empty($question['is_required'])) ? 'required' : '';
                            ?>

                            <?php if (in_array($question_type, ['radio', 'yesno'])): ?>
                                <?php if ($has_options_table && !empty($question['options'])): ?>
                                    <?php foreach ($question['options'] as $option): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" 
                                                   name="answers[<?php echo $question_id; ?>]" 
                                                   value="<?php echo htmlspecialchars($option['option_text']); ?>" 
                                                   id="q<?php echo $question_id; ?>_<?php echo $option['id']; ?>" <?php echo $is_required; ?>>
                                            <label class="form-check-label" for="q<?php echo $question_id; ?>_<?php echo $option['id']; ?>">
                                                <?php echo htmlspecialchars($option['option_text']); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <!-- Fallback for Yes/No -->
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" 
                                               name="answers[<?php echo $question_id; ?>]" value="Yes" 
                                               id="q<?php echo $question_id; ?>_yes" <?php echo $is_required; ?>>
                                        <label class="form-check-label" for="q<?php echo $question_id; ?>_yes">Yes</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" 
                                               name="answers[<?php echo $question_id; ?>]" value="No" 
                                               id="q<?php echo $question_id; ?>_no">
                                        <label class="form-check-label" for="q<?php echo $question_id; ?>_no">No</label>
                                    </div>
                                <?php endif; ?>

                            <?php elseif ($question_type == 'checkbox'): ?>
                                <?php if ($has_options_table && !empty($question['options'])): ?>
                                    <?php foreach ($question['options'] as $option): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="answers[<?php echo $question_id; ?>][]" 
                                                   value="<?php echo htmlspecialchars($option['option_text']); ?>" 
                                                   id="q<?php echo $question_id; ?>_<?php echo $option['id']; ?>">
                                            <label class="form-check-label" for="q<?php echo $question_id; ?>_<?php echo $option['id']; ?>">
                                                <?php echo htmlspecialchars($option['option_text']); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                            <?php elseif ($question_type == 'dropdown'): ?>
                                <select class="form-select" name="answers[<?php echo $question_id; ?>]" <?php echo $is_required; ?>>
                                    <option value="">Choose an option...</option>
                                    <?php if ($has_options_table && !empty($question['options'])): ?>
                                        <?php foreach ($question['options'] as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option['option_text']); ?>">
                                                <?php echo htmlspecialchars($option['option_text']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>

                            <?php elseif ($question_type == 'textarea'): ?>
                                <textarea class="form-control" name="answers[<?php echo $question_id; ?>]" 
                                          rows="4" placeholder="Enter your answer here..." <?php echo $is_required; ?>></textarea>

                            <?php elseif ($question_type == 'number'): ?>
                                <input type="number" class="form-control" name="answers[<?php echo $question_id; ?>]" 
                                       placeholder="Enter a number" <?php echo $is_required; ?>>

                            <?php elseif ($question_type == 'email'): ?>
                                <input type="email" class="form-control" name="answers[<?php echo $question_id; ?>]" 
                                       placeholder="Enter your email address" <?php echo $is_required; ?>>

                            <?php elseif ($question_type == 'date'): ?>
                                <input type="date" class="form-control" name="answers[<?php echo $question_id; ?>]" <?php echo $is_required; ?>>

                            <?php elseif ($question_type == 'rating'): ?>
                                <?php if ($has_options_table && !empty($question['options'])): ?>
                                    <?php foreach ($question['options'] as $option): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" 
                                                   name="answers[<?php echo $question_id; ?>]" 
                                                   value="<?php echo htmlspecialchars($option['option_text']); ?>" 
                                                   id="q<?php echo $question_id; ?>_<?php echo $option['id']; ?>" <?php echo $is_required; ?>>
                                            <label class="form-check-label" for="q<?php echo $question_id; ?>_<?php echo $option['id']; ?>">
                                                <?php echo htmlspecialchars($option['option_text']); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                            <?php else: ?>
                                <!-- Default: text input -->
                                <input type="text" class="form-control" name="answers[<?php echo $question_id; ?>]" 
                                       placeholder="Enter your answer" <?php echo $is_required; ?>>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <div class="d-flex gap-3 justify-content-end">
                        <a href="survey_host.php" class="btn-secondary">
                            <i class="bi bi-arrow-left"></i>Back to Surveys
                        </a>
                        <button type="submit" name="submit_answers" class="btn-primary">
                            <i class="bi bi-check-circle"></i>Submit Survey
                        </button>
                    </div>
                </form>

            <?php elseif (!isset($success_message) && !isset($error_message)): ?>
                <!-- Show Survey Selection -->
                <div class="page-header">
                    <h1 class="page-title">Available Surveys</h1>
                    <p class="page-subtitle">Choose a survey to participate in and share your valuable feedback.</p>
                </div>

                <?php if (!empty($surveys)): ?>
                    <div class="row">
                        <?php foreach ($surveys as $survey): ?>
                            <div class="col-lg-6 col-md-6 mb-4">
                                <div class="survey-card" onclick="selectSurvey(<?php echo $survey['survey_id']; ?>)">
                                    <div class="survey-header">
                                        <div class="survey-icon">
                                            <i class="bi bi-clipboard-check"></i>
                                        </div>
                                        <div class="survey-info">
                                            <h3><?php echo htmlspecialchars($survey['title']); ?></h3>
                                            <?php if ($has_description && !empty($survey['description'])): ?>
                                                <p><?php echo htmlspecialchars(substr($survey['description'], 0, 100)) . (strlen($survey['description']) > 100 ? '...' : ''); ?></p>
                                            <?php else: ?>
                                                <p>Click to participate in this survey</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <form method="POST" style="display: none;" id="form_<?php echo $survey['survey_id']; ?>">
                                        <input type="hidden" name="survey_id" value="<?php echo $survey['survey_id']; ?>">
                                        <button type="submit" name="select_survey">Select</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-clipboard-x"></i>
                        <h3>No Surveys Available</h3>
                        <p>There are currently no surveys available to take.</p>
                        <a href="create_survey.php" class="btn-primary mt-3">
                            <i class="bi bi-plus-circle"></i>Create Your First Survey
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function selectSurvey(surveyId) {
            // Add loading state to the clicked card
            const card = event.currentTarget;
            const originalContent = card.innerHTML;
            
            card.innerHTML = `
                <div class="survey-header">
                    <div class="survey-icon">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="survey-info">
                        <h3>Loading Survey...</h3>
                        <p>Please wait while we load the survey questions.</p>
                    </div>
                </div>
            `;
            
            // Submit the form
            const form = document.getElementById('form_' + surveyId);
            if (form) {
                form.submit();
            } else {
                // Fallback: create and submit form
                const newForm = document.createElement('form');
                newForm.method = 'POST';
                newForm.innerHTML = `
                    <input type="hidden" name="survey_id" value="${surveyId}">
                    <input type="hidden" name="select_survey" value="1">
                `;
                document.body.appendChild(newForm);
                newForm.submit();
            }
        }

        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const surveyForm = document.querySelector('form[method="POST"]:not([style*="display: none"])');
            
            if (surveyForm && surveyForm.querySelector('button[name="submit_answers"]')) {
                surveyForm.addEventListener('submit', function(e) {
                    const name = document.getElementById('name');
                    if (!name || !name.value.trim()) {
                        alert('Please enter your name.');
                        e.preventDefault();
                        return false;
                    }

                    // Check required questions
                    const requiredQuestions = document.querySelectorAll('.required-badge');
                    for (let badge of requiredQuestions) {
                        const questionItem = badge.closest('.question-item');
                        const inputs = questionItem.querySelectorAll('input[required], select[required], textarea[required]');
                        let hasAnswer = false;
                        
                        for (let input of inputs) {
                            if (input.type === 'radio' || input.type === 'checkbox') {
                                if (input.checked) {
                                    hasAnswer = true;
                                    break;
                                }
                            } else if (input.value && input.value.trim()) {
                                hasAnswer = true;
                                break;
                            }
                        }
                        
                        if (!hasAnswer) {
                            const questionText = questionItem.querySelector('.question-text').textContent;
                            alert('Please answer the required question: ' + questionText);
                            questionItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            e.preventDefault();
                            return false;
                        }
                    }
                    
                    // Check if at least one question is answered
                    const allQuestions = document.querySelectorAll('.question-item');
                    let totalAnswered = 0;
                    
                    allQuestions.forEach(question => {
                        const inputs = question.querySelectorAll('input, textarea, select');
                        let hasAnswer = false;
                        
                        for (let input of inputs) {
                            if (input.type === 'radio' || input.type === 'checkbox') {
                                if (input.checked) {
                                    hasAnswer = true;
                                    break;
                                }
                            } else if (input.value && input.value.trim()) {
                                hasAnswer = true;
                                break;
                            }
                        }
                        
                        if (hasAnswer) totalAnswered++;
                    });
                    
                    if (totalAnswered === 0) {
                        alert('Please answer at least one question before submitting.');
                        e.preventDefault();
                        return false;
                    }
                    
                    // Show loading state
                    const submitBtn = this.querySelector('button[name="submit_answers"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Submitting...';
                    submitBtn.disabled = true;
                    
                    // Re-enable if form submission fails (fallback)
                    setTimeout(() => {
                        if (submitBtn.disabled) {
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }
                    }, 10000);
                    
                    return true;
                });
            }

            // Add smooth hover effects to survey cards
            const surveyCards = document.querySelectorAll('.survey-card');
            surveyCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    if (!this.classList.contains('loading')) {
                        this.style.transform = 'translateY(-4px)';
                    }
                });
                
                card.addEventListener('mouseleave', function() {
                    if (!this.classList.contains('loading')) {
                        this.style.transform = 'translateY(0)';
                    }
                });
            });

            // Auto-focus first input
            const firstInput = document.querySelector('#name, input[type="text"]:not([type="hidden"]), textarea, select');
            if (firstInput) {
                setTimeout(() => {
                    firstInput.focus();
                }, 100);
            }

            // Add character counter for textarea
            const textareas = document.querySelectorAll('textarea');
            textareas.forEach(textarea => {
                const maxLength = 500; // You can adjust this
                textarea.setAttribute('maxlength', maxLength);
                
                const counter = document.createElement('small');
                counter.className = 'character-counter';
                counter.style.color = 'var(--text-secondary)';
                counter.style.fontSize = '0.75rem';
                counter.style.display = 'block';
                counter.style.textAlign = 'right';
                counter.style.marginTop = '0.25rem';
                
                textarea.parentNode.appendChild(counter);
                
                function updateCounter() {
                    const remaining = maxLength - textarea.value.length;
                    counter.textContent = `${textarea.value.length}/${maxLength} characters`;
                    counter.style.color = remaining < 50 ? 'var(--warning-color)' : 'var(--text-secondary)';
                }
                
                textarea.addEventListener('input', updateCounter);
                updateCounter();
            });

            // Add confirmation before leaving page if form is partially filled
            let formModified = false;
            const formInputs = document.querySelectorAll('input:not([type="hidden"]), textarea, select');
            
            formInputs.forEach(input => {
                input.addEventListener('change', () => {
                    formModified = true;
                });
                input.addEventListener('input', () => {
                    formModified = true;
                });
            });

            window.addEventListener('beforeunload', function(e) {
                if (formModified && !document.querySelector('.alert-success')) {
                    e.preventDefault();
                    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                    return 'You have unsaved changes. Are you sure you want to leave?';
                }
            });

            // Remove confirmation after successful submission
            if (document.querySelector('.alert-success')) {
                formModified = false;
            }

            // Add progress indicator for long surveys
            const questions = document.querySelectorAll('.question-item');
            if (questions.length > 3) {
                addProgressIndicator(questions);
            }

            // Add smooth scrolling to next question on answer
            questions.forEach((question, index) => {
                const inputs = question.querySelectorAll('input[type="radio"], input[type="checkbox"]');
                inputs.forEach(input => {
                    input.addEventListener('change', () => {
                        // Small delay then scroll to next question
                        setTimeout(() => {
                            const nextQuestion = questions[index + 1];
                            if (nextQuestion) {
                                const rect = nextQuestion.getBoundingClientRect();
                                const isVisible = rect.top >= 0 && rect.bottom <= window.innerHeight;
                                if (!isVisible) {
                                    nextQuestion.scrollIntoView({ 
                                        behavior: 'smooth', 
                                        block: 'center' 
                                    });
                                }
                            }
                        }, 300);
                    });
                });
            });
        });

        function addProgressIndicator(questions) {
            const progressContainer = document.createElement('div');
            progressContainer.className = 'progress-indicator';
            progressContainer.style.cssText = `
                position: sticky;
                top: 80px;
                background: var(--card-bg);
                border: 1px solid var(--border-color);
                border-radius: 8px;
                padding: 1rem;
                margin-bottom: 1.5rem;
                z-index: 100;
            `;
            
            const progressLabel = document.createElement('div');
            progressLabel.style.cssText = `
                color: var(--text-secondary);
                font-size: 0.875rem;
                margin-bottom: 0.5rem;
            `;
            progressLabel.textContent = 'Survey Progress';
            
            const progressBar = document.createElement('div');
            progressBar.style.cssText = `
                background: rgba(15, 23, 42, 0.5);
                border-radius: 4px;
                height: 6px;
                position: relative;
                overflow: hidden;
            `;
            
            const progressFill = document.createElement('div');
            progressFill.style.cssText = `
                background: var(--primary-color);
                height: 100%;
                width: 0%;
                transition: width 0.3s ease;
                border-radius: 4px;
            `;
            
            const progressText = document.createElement('div');
            progressText.style.cssText = `
                color: var(--text-primary);
                font-size: 0.75rem;
                text-align: center;
                margin-top: 0.5rem;
            `;
            
            progressBar.appendChild(progressFill);
            progressContainer.appendChild(progressLabel);
            progressContainer.appendChild(progressBar);
            progressContainer.appendChild(progressText);
            
            const surveyForm = document.querySelector('.survey-form');
            if (surveyForm) {
                surveyForm.insertBefore(progressContainer, surveyForm.firstChild);
                
                // Update progress based on answered questions
                function updateProgress() {
                    let answered = 0;
                    questions.forEach(question => {
                        const inputs = question.querySelectorAll('input, textarea, select');
                        let hasAnswer = false;
                        
                        for (let input of inputs) {
                            if (input.type === 'radio' || input.type === 'checkbox') {
                                if (input.checked) {
                                    hasAnswer = true;
                                    break;
                                }
                            } else if (input.value.trim()) {
                                hasAnswer = true;
                                break;
                            }
                        }
                        
                        if (hasAnswer) answered++;
                    });
                    
                    const percentage = Math.round((answered / questions.length) * 100);
                    progressFill.style.width = percentage + '%';
                    progressText.textContent = `${answered} of ${questions.length} questions answered (${percentage}%)`;
                }
                
                // Listen for changes
                questions.forEach(question => {
                    const inputs = question.querySelectorAll('input, textarea, select');
                    inputs.forEach(input => {
                        input.addEventListener('change', updateProgress);
                        input.addEventListener('input', updateProgress);
                    });
                });
                
                // Initial update
                updateProgress();
            }
        }

        // Add smooth scrolling to questions
        function scrollToNextQuestion(currentQuestion) {
            const questions = Array.from(document.querySelectorAll('.question-item'));
            const currentIndex = questions.indexOf(currentQuestion);
            const nextQuestion = questions[currentIndex + 1];
            
            if (nextQuestion) {
                nextQuestion.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
            }
        }

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + Enter to submit form
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                const submitBtn = document.querySelector('button[name="submit_answers"]');
                if (submitBtn) {
                    submitBtn.click();
                }
            }
        });
    </script>
</body>
</html>