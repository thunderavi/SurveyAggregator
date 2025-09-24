<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'] ?? '';
    $user_id = $_SESSION['user_id'];

    try {
        // Check if description column exists
        $result = $conn->query("SHOW COLUMNS FROM surveys LIKE 'description'");
        $has_description = $result->num_rows > 0;
        
        // Insert survey with appropriate query
        if ($has_description) {
            $stmt = $conn->prepare("INSERT INTO surveys (owner_id, title, description) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $title, $description);
        } else {
            $stmt = $conn->prepare("INSERT INTO surveys (owner_id, title) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $title);
        }
        
        $stmt->execute();
        $survey_id = $stmt->insert_id;
        $stmt->close();

        // Check if is_required column exists
        $result = $conn->query("SHOW COLUMNS FROM questions LIKE 'is_required'");
        $has_is_required = $result->num_rows > 0;

        // Check if question_options table exists
        $result = $conn->query("SHOW TABLES LIKE 'question_options'");
        $has_options_table = $result->num_rows > 0;

        // Insert questions
        if (isset($_POST['questions']) && is_array($_POST['questions'])) {
            foreach ($_POST['questions'] as $index => $question) {
                if (empty(trim($question['text']))) {
                    continue; // Skip empty questions
                }
                
                $question_text = trim($question['text']);
                $question_type = $question['type'];
                $is_required = isset($question['required']) ? 1 : 0;
                
                // Insert question with appropriate query
                if ($has_is_required) {
                    $stmt = $conn->prepare("INSERT INTO questions (survey_id, question_text, type, is_required) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("issi", $survey_id, $question_text, $question_type, $is_required);
                } else {
                    $stmt = $conn->prepare("INSERT INTO questions (survey_id, question_text, type) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $survey_id, $question_text, $question_type);
                }
                
                $stmt->execute();
                $question_id = $stmt->insert_id;
                $stmt->close();

                // Insert options for questions that need them
                if (in_array($question_type, ['radio', 'checkbox', 'dropdown', 'yesno', 'rating']) && isset($question['options'])) {
                    foreach ($question['options'] as $order => $option_text) {
                        $option_text = trim($option_text);
                        if (!empty($option_text)) {
                            if ($has_options_table) {
                                // Use proper question_options table
                                $stmt = $conn->prepare("INSERT INTO question_options (question_id, option_text, option_order) VALUES (?, ?, ?)");
                                $stmt->bind_param("isi", $question_id, $option_text, $order);
                                $stmt->execute();
                                $stmt->close();
                            } else {
                                // Fallback: store options as additional questions with special type
                                $option_label = "OPTION_" . $question_id . "_" . $order . ": " . $option_text;
                                $stmt = $conn->prepare("INSERT INTO questions (survey_id, question_text, type) VALUES (?, ?, 'option')");
                                $stmt->bind_param("is", $survey_id, $option_label);
                                $stmt->execute();
                                $stmt->close();
                            }
                        }
                    }
                }
            }
        }

        echo "<script>
            alert('Survey created successfully! " . ($has_description ? "" : "Note: Run the database upgrade script to unlock all features.") . "'); 
            window.location.href='dashboard.php';
        </script>";
        
    } catch (Exception $e) {
        echo "<script>
            alert('Error creating survey: " . addslashes($e->getMessage()) . "'); 
            window.history.back();
        </script>";
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Survey - Survey Aggregator</title>
    
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
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
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

        /* Form Styling */
        .survey-form {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
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

        .form-control::placeholder {
            color: var(--text-secondary);
        }

        /* Question Builder */
        .questions-container {
            margin-top: 2rem;
        }

        .question-item {
            background: rgba(15, 23, 42, 0.3);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            position: relative;
            transition: all 0.2s ease;
        }

        .question-item:hover {
            border-color: var(--primary-color);
            background: rgba(15, 23, 42, 0.5);
        }

        .question-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
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

        .question-controls {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .btn-remove {
            background: var(--danger-color);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-remove:hover {
            background: #dc2626;
            transform: scale(1.05);
        }

        .question-type-select {
            min-width: 150px;
        }

        /* Options Container */
        .options-container {
            margin-top: 1rem;
            padding: 1rem;
            background: rgba(15, 23, 42, 0.3);
            border-radius: 8px;
            border: 1px dashed var(--border-color);
        }

        .option-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .option-input {
            flex: 1;
        }

        .btn-remove-option {
            background: transparent;
            border: none;
            color: var(--danger-color);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .btn-remove-option:hover {
            background: rgba(239, 68, 68, 0.1);
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
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-secondary:hover {
            background: rgba(99, 102, 241, 0.15);
            border-color: var(--primary-color);
        }

        .btn-success {
            background: var(--success-color);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-success:hover {
            background: #059669;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
        }

        /* Required indicator */
        .required-indicator {
            color: var(--danger-color);
            font-size: 0.875rem;
            margin-left: 0.25rem;
        }

        /* Form check styling */
        .form-check {
            margin-top: 0.5rem;
        }

        .form-check-input {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid var(--border-color);
        }

        .form-check-input:checked {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .form-check-label {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem 0;
            }
            
            .survey-form {
                padding: 1.5rem;
            }
            
            .question-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 1rem;
            }
            
            .form-actions > div {
                width: 100%;
                display: flex;
                gap: 1rem;
            }
        }

        /* Animation for new questions */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .question-item.new {
            animation: slideIn 0.3s ease;
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
                        <a class="nav-link active" href="create_survey.php">
                            <i class="bi bi-plus-circle me-1"></i>Create Survey
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="survey_host.php">
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
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Create New Survey</h1>
                <p class="page-subtitle">Design engaging surveys with multiple question types and customizable options.</p>
            </div>

            <!-- Survey Form -->
            <form method="POST" id="surveyForm">
                <div class="survey-form">
                    <!-- Basic Information -->
                    <div class="form-group">
                        <label class="form-label" for="title">
                            Survey Title <span class="required-indicator">*</span>
                        </label>
                        <input type="text" class="form-control" id="title" name="title" 
                               placeholder="Enter a compelling title for your survey" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="description">Survey Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                                placeholder="Provide a brief description of your survey's purpose and goals"></textarea>
                    </div>
                </div>

                <!-- Questions Container -->
                <div class="questions-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 style="color: var(--text-primary); margin: 0;">Survey Questions</h4>
                        <button type="button" class="btn-secondary" onclick="addQuestion()">
                            <i class="bi bi-plus-circle"></i>Add Question
                        </button>
                    </div>
                    
                    <div id="questionsContainer">
                        <!-- Initial question -->
                        <div class="question-item" data-question="1">
                            <div class="question-header">
                                <div class="question-number">1</div>
                                <div class="question-controls">
                                    <select class="form-select question-type-select" name="questions[0][type]" onchange="toggleOptions(this)">
                                        <option value="radio">Multiple Choice (Single)</option>
                                        <option value="checkbox">Multiple Choice (Multiple)</option>
                                        <option value="text">Short Text</option>
                                        <option value="textarea">Long Text</option>
                                        <option value="number">Number</option>
                                        <option value="email">Email</option>
                                        <option value="date">Date</option>
                                        <option value="dropdown">Dropdown</option>
                                        <option value="yesno">Yes/No</option>
                                        <option value="rating">Rating Scale</option>
                                    </select>
                                    <button type="button" class="btn-remove" onclick="removeQuestion(this)" style="display: none;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <input type="text" class="form-control" name="questions[0][text]" 
                                       placeholder="Enter your question here..." required>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="questions[0][required]" id="required_0">
                                <label class="form-check-label" for="required_0">
                                    Required question
                                </label>
                            </div>
                            
                            <div class="options-container" id="options_0">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small style="color: var(--text-secondary);">Answer Options:</small>
                                    <button type="button" class="btn-success btn-sm" onclick="addOption(0)">
                                        <i class="bi bi-plus"></i> Add Option
                                    </button>
                                </div>
                                <div class="options-list">
                                    <div class="option-item">
                                        <input type="text" class="form-control option-input" 
                                               name="questions[0][options][]" placeholder="Option 1">
                                        <button type="button" class="btn-remove-option" onclick="removeOption(this)" style="display: none;">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                    <div class="option-item">
                                        <input type="text" class="form-control option-input" 
                                               name="questions[0][options][]" placeholder="Option 2">
                                        <button type="button" class="btn-remove-option" onclick="removeOption(this)" style="display: none;">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <div>
                        <button type="button" class="btn-secondary" onclick="addQuestion()">
                            <i class="bi bi-plus-circle"></i>Add Another Question
                        </button>
                    </div>
                    <div>
                        <a href="dashboard.php" class="btn-secondary me-3">
                            <i class="bi bi-arrow-left"></i>Cancel
                        </a>
                        <button type="submit" class="btn-primary">
                            <i class="bi bi-check-circle"></i>Create Survey
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let questionCount = 1;

        function addQuestion() {
            const container = document.getElementById('questionsContainer');
            const questionDiv = document.createElement('div');
            questionDiv.className = 'question-item new';
            questionDiv.setAttribute('data-question', questionCount + 1);
            
            questionDiv.innerHTML = `
                <div class="question-header">
                    <div class="question-number">${questionCount + 1}</div>
                    <div class="question-controls">
                        <select class="form-select question-type-select" name="questions[${questionCount}][type]" onchange="toggleOptions(this)">
                            <option value="radio">Multiple Choice (Single)</option>
                            <option value="checkbox">Multiple Choice (Multiple)</option>
                            <option value="text">Short Text</option>
                            <option value="textarea">Long Text</option>
                            <option value="number">Number</option>
                            <option value="email">Email</option>
                            <option value="date">Date</option>
                            <option value="dropdown">Dropdown</option>
                            <option value="yesno">Yes/No</option>
                            <option value="rating">Rating Scale</option>
                        </select>
                        <button type="button" class="btn-remove" onclick="removeQuestion(this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <input type="text" class="form-control" name="questions[${questionCount}][text]" 
                           placeholder="Enter your question here..." required>
                </div>
                
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="questions[${questionCount}][required]" id="required_${questionCount}">
                    <label class="form-check-label" for="required_${questionCount}">
                        Required question
                    </label>
                </div>
                
                <div class="options-container" id="options_${questionCount}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small style="color: var(--text-secondary);">Answer Options:</small>
                        <button type="button" class="btn-success btn-sm" onclick="addOption(${questionCount})">
                            <i class="bi bi-plus"></i> Add Option
                        </button>
                    </div>
                    <div class="options-list">
                        <div class="option-item">
                            <input type="text" class="form-control option-input" 
                                   name="questions[${questionCount}][options][]" placeholder="Option 1">
                            <button type="button" class="btn-remove-option" onclick="removeOption(this)" style="display: none;">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                        <div class="option-item">
                            <input type="text" class="form-control option-input" 
                                   name="questions[${questionCount}][options][]" placeholder="Option 2">
                            <button type="button" class="btn-remove-option" onclick="removeOption(this)" style="display: none;">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            container.appendChild(questionDiv);
            questionCount++;
            updateQuestionNumbers();
            updateRemoveButtons();
        }

        function removeQuestion(btn) {
            const questionItem = btn.closest('.question-item');
            questionItem.remove();
            questionCount--;
            updateQuestionNumbers();
            updateRemoveButtons();
        }

        function updateQuestionNumbers() {
            const questions = document.querySelectorAll('.question-item');
            questions.forEach((question, index) => {
                const numberEl = question.querySelector('.question-number');
                numberEl.textContent = index + 1;
                question.setAttribute('data-question', index + 1);
            });
        }

        function updateRemoveButtons() {
            const removeButtons = document.querySelectorAll('.btn-remove');
            removeButtons.forEach((btn, index) => {
                btn.style.display = removeButtons.length > 1 ? 'flex' : 'none';
            });
        }

        function toggleOptions(select) {
            const questionItem = select.closest('.question-item');
            const optionsContainer = questionItem.querySelector('.options-container');
            const questionType = select.value;
            
            const needsOptions = ['radio', 'checkbox', 'dropdown'].includes(questionType);
            optionsContainer.style.display = needsOptions ? 'block' : 'none';
            
            // Handle special cases
            if (questionType === 'yesno') {
                // Set predefined yes/no options
                const optionsList = optionsContainer.querySelector('.options-list');
                optionsList.innerHTML = `
                    <div class="option-item">
                        <input type="text" class="form-control option-input" 
                               name="${select.name.replace('[type]', '[options][]')}" value="Yes" readonly>
                    </div>
                    <div class="option-item">
                        <input type="text" class="form-control option-input" 
                               name="${select.name.replace('[type]', '[options][]')}" value="No" readonly>
                    </div>
                `;
                optionsContainer.style.display = 'block';
            } else if (questionType === 'rating') {
                // Set rating scale options
                const optionsList = optionsContainer.querySelector('.options-list');
                optionsList.innerHTML = `
                    <div class="option-item">
                        <input type="text" class="form-control option-input" 
                               name="${select.name.replace('[type]', '[options][]')}" value="1 - Poor" readonly>
                    </div>
                    <div class="option-item">
                        <input type="text" class="form-control option-input" 
                               name="${select.name.replace('[type]', '[options][]')}" value="2 - Fair" readonly>
                    </div>
                    <div class="option-item">
                        <input type="text" class="form-control option-input" 
                               name="${select.name.replace('[type]', '[options][]')}" value="3 - Good" readonly>
                    </div>
                    <div class="option-item">
                        <input type="text" class="form-control option-input" 
                               name="${select.name.replace('[type]', '[options][]')}" value="4 - Very Good" readonly>
                    </div>
                    <div class="option-item">
                        <input type="text" class="form-control option-input" 
                               name="${select.name.replace('[type]', '[options][]')}" value="5 - Excellent" readonly>
                    </div>
                `;
                optionsContainer.style.display = 'block';
            }
        }

        function addOption(questionIndex) {
            const optionsContainer = document.getElementById(`options_${questionIndex}`);
            const optionsList = optionsContainer.querySelector('.options-list');
            const optionCount = optionsList.children.length + 1;
            
            const optionDiv = document.createElement('div');
            optionDiv.className = 'option-item';
            optionDiv.innerHTML = `
                <input type="text" class="form-control option-input" 
                       name="questions[${questionIndex}][options][]" placeholder="Option ${optionCount}">
                <button type="button" class="btn-remove-option" onclick="removeOption(this)">
                    <i class="bi bi-x"></i>
                </button>
            `;
            
            optionsList.appendChild(optionDiv);
            updateOptionRemoveButtons(optionsList);
        }

        function removeOption(btn) {
            const optionItem = btn.closest('.option-item');
            const optionsList = optionItem.parentNode;
            optionItem.remove();
            updateOptionRemoveButtons(optionsList);
        }

        function updateOptionRemoveButtons(optionsList) {
            const removeButtons = optionsList.querySelectorAll('.btn-remove-option');
            removeButtons.forEach((btn, index) => {
                btn.style.display = removeButtons.length > 2 ? 'inline-block' : 'none';
            });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize options visibility for first question
            toggleOptions(document.querySelector('select[name="questions[0][type]"]'));
            updateRemoveButtons();
            
            // Update option remove buttons for initial question
            const initialOptionsList = document.querySelector('#options_0 .options-list');
            updateOptionRemoveButtons(initialOptionsList);
            
            // Form validation
            document.getElementById('surveyForm').addEventListener('submit', function(e) {
                const title = document.getElementById('title').value.trim();
                const questions = document.querySelectorAll('input[name*="[text]"]');
                
                if (!title) {
                    alert('Please enter a survey title.');
                    e.preventDefault();
                    return;
                }
                
                let hasValidQuestion = false;
                questions.forEach(question => {
                    if (question.value.trim()) {
                        hasValidQuestion = true;
                    }
                });
                
                if (!hasValidQuestion) {
                    alert('Please add at least one question with text.');
                    e.preventDefault();
                    return;
                }
                
                // Validate that multiple choice questions have at least 2 options
                const multipleChoiceQuestions = document.querySelectorAll('select[name*="[type]"]');
                for (let select of multipleChoiceQuestions) {
                    const questionType = select.value;
                    if (['radio', 'checkbox', 'dropdown'].includes(questionType)) {
                        const questionItem = select.closest('.question-item');
                        const optionInputs = questionItem.querySelectorAll('input[name*="[options]"]');
                        let validOptions = 0;
                        
                        optionInputs.forEach(input => {
                            if (input.value.trim()) validOptions++;
                        });
                        
                        if (validOptions < 2) {
                            alert('Multiple choice questions must have at least 2 options.');
                            e.preventDefault();
                            return;
                        }
                    }
                }
            });
        });

        // Add smooth animations
        function animateNewElement(element) {
            element.style.opacity = '0';
            element.style.transform = 'translateY(-10px)';
            
            setTimeout(() => {
                element.style.transition = 'all 0.3s ease';
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, 10);
        }
    </script>
</body>
</html>