<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit();
}

include 'db.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "Username already exists. Please choose a different one.";
            $stmt->close();
        } else {
            $stmt->close();
            
            // Check if email already exists
            $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error_message = "Email already registered. Please use a different email.";
                $stmt->close();
            } else {
                $stmt->close();
                
                // Hash password and insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $email, $hashed_password);

                if ($stmt->execute()) {
                    $success_message = "Registration successful! You can now log in.";
                } else {
                    $error_message = "Registration failed. Please try again.";
                }
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Survey Aggregator</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --dark-gradient: linear-gradient(135deg, #0c0c0c 0%, #1a1a2e 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --text-light: rgba(255, 255, 255, 0.9);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--dark-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .register-container {
            max-width: 450px;
            width: 100%;
            padding: 1rem;
        }

        .register-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25);
            position: relative;
            overflow: hidden;
        }

        .register-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--secondary-gradient);
            border-radius: 24px 24px 0 0;
        }

        .brand-logo {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .brand-logo h1 {
            color: white;
            font-weight: 800;
            font-size: 1.9rem;
            margin-bottom: 0.5rem;
            background: var(--secondary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand-logo p {
            color: var(--text-light);
            font-size: 0.95rem;
            margin: 0;
            font-weight: 400;
        }

        .form-floating {
            margin-bottom: 1.8rem;
            position: relative;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: white;
            padding: 1.2rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            height: auto;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(245, 87, 108, 0.6);
            box-shadow: 0 0 0 3px rgba(245, 87, 108, 0.15);
            color: white;
            transform: translateY(-1px);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-floating label {
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
            padding-left: 1rem;
        }

        .password-requirements {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-left: 3px solid rgba(245, 87, 108, 0.6);
        }

        .password-requirements h6 {
            color: var(--text-light);
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .password-requirements ul {
            margin: 0;
            padding-left: 1rem;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .password-requirements li {
            margin-bottom: 0.2rem;
        }

        .btn-register {
            background: var(--secondary-gradient);
            border: none;
            border-radius: 12px;
            padding: 1.2rem 2rem;
            font-weight: 600;
            color: white;
            width: 100%;
            margin-bottom: 1.8rem;
            transition: all 0.3s ease;
            font-size: 1.05rem;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(245, 87, 108, 0.4);
            color: white;
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .alert {
            border-radius: 12px;
            backdrop-filter: blur(10px);
            border: 1px solid;
            margin-bottom: 1.5rem;
            padding: 1rem 1.2rem;
            font-weight: 500;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.15);
            border-color: rgba(220, 53, 69, 0.3);
            color: #ff7a8a;
        }

        .alert-success {
            background: rgba(25, 135, 84, 0.15);
            border-color: rgba(25, 135, 84, 0.3);
            color: #75d896;
        }

        .text-center a {
            color: var(--text-light);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .text-center a:hover {
            color: white;
            text-shadow: 0 0 10px rgba(245, 87, 108, 0.5);
            transform: translateY(-1px);
            display: inline-block;
        }

        .back-to-home {
            position: fixed;
            top: 2rem;
            left: 2rem;
            color: var(--text-light);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            font-weight: 500;
            z-index: 1000;
        }

        .back-to-home:hover {
            color: white;
            transform: translateX(-5px);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.9rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }

        .divider span {
            padding: 0 1rem;
        }

        @media (max-width: 576px) {
            .register-container {
                padding: 0.5rem;
            }
            
            .register-card {
                padding: 2rem 1.5rem;
            }
            
            .back-to-home {
                top: 1rem;
                left: 1rem;
            }
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-to-home">
        <i class="bi bi-arrow-left"></i>
        Back to Home
    </a>

    <div class="register-container">
        <div class="register-card">
            <div class="brand-logo">
                <h1><i class="bi bi-clipboard-data me-2"></i>Survey Aggregator</h1>
                <p>Create your account and start aggregating surveys today!</p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($success_message)): ?>
            <form method="POST" novalidate>
                <div class="form-floating">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                    <label for="username"><i class="bi bi-person me-2"></i>Username</label>
                </div>

                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email address"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    <label for="email"><i class="bi bi-envelope me-2"></i>Email Address</label>
                </div>
                
                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password"><i class="bi bi-lock me-2"></i>Password</label>
                </div>

                <div class="form-floating">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                    <label for="confirm_password"><i class="bi bi-lock-fill me-2"></i>Confirm Password</label>
                </div>

                <div class="password-requirements">
                    <h6><i class="bi bi-info-circle me-2"></i>Password Requirements</h6>
                    <ul>
                        <li>At least 8 characters long</li>
                        <li>Use a unique password you don't use elsewhere</li>
                        <li>Consider using a mix of letters, numbers, and symbols</li>
                    </ul>
                </div>
                
                <button type="submit" class="btn btn-register">
                    <i class="bi bi-person-plus me-2"></i>
                    Create Account
                </button>
            </form>
            <?php endif; ?>

            <div class="divider">
                <span>Already have an account?</span>
            </div>

            <div class="text-center">
                <?php if (!empty($success_message)): ?>
                    <a href="login.php" class="fw-bold">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign in now
                    </a>
                <?php else: ?>
                    <a href="login.php" class="fw-bold">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign in here
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Real-time password confirmation validation
        document.addEventListener('DOMContentLoaded', function() {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            function validatePasswords() {
                if (confirmPassword.value && password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match');
                    confirmPassword.classList.add('is-invalid');
                } else {
                    confirmPassword.setCustomValidity('');
                    confirmPassword.classList.remove('is-invalid');
                }
            }
            
            password.addEventListener('input', validatePasswords);
            confirmPassword.addEventListener('input', validatePasswords);
        });
    </script>
</body>
</html>