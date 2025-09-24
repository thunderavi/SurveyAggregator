<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Survey Aggregator</title>
    
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
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
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

        /* Navigation */
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

        /* Dashboard Cards */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .dashboard-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.2s ease;
            text-decoration: none;
            color: inherit;
            display: block;
            position: relative;
            overflow: hidden;
        }

        .dashboard-card::before {
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

        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
            text-decoration: none;
            color: inherit;
        }

        .dashboard-card:hover::before {
            transform: scaleX(1);
        }

        .card-header {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .card-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            flex-shrink: 0;
        }

        .card-icon.create { background: var(--success-color); }
        .card-icon.survey { background: var(--primary-color); }
        .card-icon.responses { background: var(--info-color); }
        .card-icon.analytics { background: var(--warning-color); }

        .card-content h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .card-content p {
            color: var(--text-secondary);
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .card-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-color);
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
        }

        .card-action:hover {
            color: var(--primary-dark);
        }

        /* Stats Section */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            display: block;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* Quick Actions */
        .quick-actions {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
        }

        .quick-actions h4 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .action-btn {
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 6px;
            padding: 0.5rem 1rem;
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }

        .action-btn:hover {
            background: rgba(99, 102, 241, 0.15);
            border-color: var(--primary-color);
            color: var(--primary-color);
            text-decoration: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem 0;
            }
            
            .page-title {
                font-size: 1.75rem;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .dashboard-card {
                padding: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
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
                <h1 class="page-title">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
                <p class="page-subtitle">Here's what you can do with your survey platform today.</p>
            </div>

            <!-- Main Dashboard Cards -->
            <div class="dashboard-grid">
                <a href="create_survey.php" class="dashboard-card">
                    <div class="card-header">
                        <div class="card-icon create">
                            <i class="bi bi-plus-circle"></i>
                        </div>
                        <div class="card-content">
                            <h3>Create New Survey</h3>
                            <p>Design and build engaging surveys with our intuitive builder. Add various question types and customize your survey's appearance.</p>
                            <div class="card-action">
                                Get Started <i class="bi bi-arrow-right"></i>
                            </div>
                        </div>
                    </div>
                </a>
                
                <a href="survey_host.php" class="dashboard-card">
                    <div class="card-header">
                        <div class="card-icon survey">
                            <i class="bi bi-clipboard-check"></i>
                        </div>
                        <div class="card-content">
                            <h3>Take Surveys</h3>
                            <p>Browse and participate in available surveys. Share your opinions and contribute to valuable research.</p>
                            <div class="card-action">
                                Browse Surveys <i class="bi bi-arrow-right"></i>
                            </div>
                        </div>
                    </div>
                </a>
                
                <a href="responses.php" class="dashboard-card">
                    <div class="card-header">
                        <div class="card-icon responses">
                            <i class="bi bi-list-ul"></i>
                        </div>
                        <div class="card-content">
                            <h3>Manage Responses</h3>
                            <p>View, edit, and manage individual survey responses. Search through submissions and perform bulk actions.</p>
                            <div class="card-action">
                                Manage Data <i class="bi bi-arrow-right"></i>
                            </div>
                        </div>
                    </div>
                </a>
                
                <a href="response.php" class="dashboard-card">
                    <div class="card-header">
                        <div class="card-icon analytics">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <div class="card-content">
                            <h3>Analytics & Reports</h3>
                            <p>Get insights with advanced analytics. View charts, generate reports, and discover trends in your data.</p>
                            <div class="card-action">
                                View Analytics <i class="bi bi-arrow-right"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Stats Section -->
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-number">12</span>
                    <div class="stat-label">Surveys Created</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number">847</span>
                    <div class="stat-label">Total Responses</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number">92%</span>
                    <div class="stat-label">Completion Rate</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number">4.8</span>
                    <div class="stat-label">Average Rating</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h4>Quick Actions</h4>
                <div class="action-buttons">
                    <a href="create_survey.php" class="action-btn">
                        <i class="bi bi-plus-circle"></i>
                        New Survey
                    </a>
                    <a href="survey_host.php" class="action-btn">
                        <i class="bi bi-clipboard-check"></i>
                        Take Survey
                    </a>
                    <a href="responses.php" class="action-btn">
                        <i class="bi bi-download"></i>
                        Export Data
                    </a>
                    <a href="response.php" class="action-btn">
                        <i class="bi bi-graph-up"></i>
                        View Reports
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Simple, clean animations
        document.addEventListener('DOMContentLoaded', function() {
            // Add smooth hover effects
            const cards = document.querySelectorAll('.dashboard-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>