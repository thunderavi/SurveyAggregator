<?php
session_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Aggregator - Next Generation Survey Platform</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/style.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --accent-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --dark-gradient: linear-gradient(135deg, #0c0c0c 0%, #1a1a2e 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --text-light: rgba(255, 255, 255, 0.9);
            --shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.15);
            --shadow-xl: 0 25px 80px rgba(0, 0, 0, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--dark-gradient);
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: var(--dark-gradient);
        }

        .bg-animation::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 25% 25%, rgba(102, 126, 234, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 75% 75%, rgba(245, 87, 108, 0.1) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        /* Glass Navigation */
        .navbar {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            padding: 1rem 0;
        }

        .navbar.scrolled {
            background: rgba(0, 0, 0, 0.95);
            box-shadow: var(--shadow-lg);
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            color: white !important;
            text-shadow: 0 0 20px rgba(102, 126, 234, 0.5);
        }

        .nav-link {
            color: var(--text-light) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: 25px;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: white !important;
            background: var(--glass-bg);
            transform: translateY(-2px);
        }

        .user-badge {
            background: var(--primary-gradient);
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--glass-border);
        }

        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 4.5rem;
            font-weight: 800;
            color: white;
            margin-bottom: 1.5rem;
            line-height: 1.1;
            background: linear-gradient(135deg, #ffffff 0%, #667eea 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 1.4rem;
            color: var(--text-light);
            margin-bottom: 3rem;
            font-weight: 400;
        }

        .search-container {
            position: relative;
            max-width: 650px;
            margin: 0 auto 3rem;
        }

        .search-box {
            width: 100%;
            padding: 1.5rem 2.5rem;
            font-size: 1.1rem;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 60px;
            color: white;
            box-shadow: var(--shadow-xl);
            transition: all 0.3s ease;
        }

        .search-box:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.5);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2), var(--shadow-xl);
            transform: scale(1.02);
        }

        .search-box::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .cta-section {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .cta-btn {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            position: relative;
            overflow: hidden;
            border: none;
            cursor: pointer;
        }

        .cta-primary {
            background: var(--primary-gradient);
            color: white;
            box-shadow: var(--shadow-lg);
        }

        .cta-primary:hover {
            color: white;
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 30px 80px rgba(102, 126, 234, 0.4);
        }

        .cta-secondary {
            background: transparent;
            color: white;
            border: 2px solid var(--glass-border);
            backdrop-filter: blur(20px);
        }

        .cta-secondary:hover {
            color: white;
            background: var(--glass-bg);
            transform: translateY(-5px) scale(1.05);
            box-shadow: var(--shadow-lg);
        }

        /* Features Section */
        .features-section {
            padding: 120px 0;
            background: linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.5) 100%);
        }

        .section-title {
            font-size: 3rem;
            font-weight: 800;
            color: white;
            text-align: center;
            margin-bottom: 1rem;
        }

        .section-subtitle {
            font-size: 1.2rem;
            color: var(--text-light);
            text-align: center;
            margin-bottom: 4rem;
        }

        .feature-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 25px;
            padding: 2.5rem;
            height: 100%;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--accent-gradient);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-card:hover {
            transform: translateY(-15px);
            box-shadow: var(--shadow-xl);
            border-color: rgba(102, 126, 234, 0.3);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-gradient);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            font-size: 2rem;
            color: white;
            box-shadow: var(--shadow-lg);
        }

        .feature-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
        }

        .feature-description {
            color: var(--text-light);
            line-height: 1.7;
        }

        /* Stats Section */
        .stats-section {
            padding: 80px 0;
            background: rgba(0, 0, 0, 0.3);
        }

        .stat-card {
            text-align: center;
            padding: 2rem;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: white;
            display: block;
            margin-bottom: 0.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 1.1rem;
            font-weight: 500;
        }

        /* Footer */
        .footer {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(20px);
            border-top: 1px solid var(--glass-border);
            padding: 60px 0 30px;
            color: var(--text-light);
        }

        /* Floating Elements */
        .floating-element {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(245, 87, 108, 0.1));
            backdrop-filter: blur(10px);
            animation: float-random 15s infinite ease-in-out;
        }

        .floating-1 {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-2 {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 15%;
            animation-delay: 5s;
        }

        .floating-3 {
            width: 60px;
            height: 60px;
            bottom: 30%;
            left: 20%;
            animation-delay: 10s;
        }

        @keyframes float-random {
            0%, 100% { transform: translateY(0px) translateX(0px) rotate(0deg); }
            25% { transform: translateY(-30px) translateX(20px) rotate(90deg); }
            50% { transform: translateY(-10px) translateX(-20px) rotate(180deg); }
            75% { transform: translateY(-40px) translateX(10px) rotate(270deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 3rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .cta-btn {
                width: 100%;
                margin: 0.5rem 0;
            }
            
            .section-title {
                font-size: 2.5rem;
            }
            
            .feature-card {
                margin-bottom: 2rem;
            }
        }

        /* Cursor Trail Effect */
        .cursor-trail {
            position: fixed;
            width: 20px;
            height: 20px;
            background: radial-gradient(circle, rgba(102, 126, 234, 0.8), transparent);
            border-radius: 50%;
            pointer-events: none;
            z-index: 9999;
            transition: all 0.1s ease;
        }

        /* Scroll Indicator */
        .scroll-indicator {
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 3px;
            background: var(--primary-gradient);
            z-index: 1000;
            transition: width 0.1s ease;
        }
    </style>
</head>
<body>
    <!-- Scroll Indicator -->
    <div class="scroll-indicator"></div>
    
    <!-- Cursor Trail -->
    <div class="cursor-trail"></div>
    
    <!-- Background Animation -->
    <div class="bg-animation"></div>
    
    <!-- Floating Elements -->
    <div class="floating-element floating-1"></div>
    <div class="floating-element floating-2"></div>
    <div class="floating-element floating-3"></div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top" id="navbar">
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
                    <?php if(isset($_SESSION['user_id'])): ?>
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
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link user-badge ms-2" href="register.php">
                                <i class="bi bi-rocket-takeoff me-1"></i>Get Started
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content text-center">
                <h1 class="hero-title">The Future of<br>Survey Intelligence</h1>
                <p class="hero-subtitle">Create stunning surveys, gather meaningful insights, and make data-driven decisions with our next-generation platform</p>
                
                <div class="search-container">
                    <input type="text" class="search-box" placeholder="What would you like to discover today?" readonly onclick="<?php echo isset($_SESSION['user_id']) ? 'window.location.href=\'dashboard.php\'' : 'window.location.href=\'login.php\''; ?>">
                </div>
                
                <div class="cta-section">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="create_survey.php" class="cta-btn cta-primary">
                            <i class="bi bi-plus-circle me-2"></i>Create Survey
                        </a>
                        <a href="dashboard.php" class="cta-btn cta-secondary">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard
                        </a>
                    <?php else: ?>
                        <a href="register.php" class="cta-btn cta-primary">
                            <i class="bi bi-rocket-takeoff me-2"></i>Start Creating
                        </a>
                        <a href="survey_host.php" class="cta-btn cta-secondary">
                            <i class="bi bi-play-circle me-2"></i>Try Demo
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 class="section-title">Powerful Features</h2>
            <p class="section-subtitle">Everything you need to create, distribute, and analyze surveys like a pro</p>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-magic"></i>
                        </div>
                        <h3 class="feature-title">AI-Powered Creation</h3>
                        <p class="feature-description">Generate intelligent survey questions and get suggestions based on your goals with our advanced AI assistant.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-lightning-charge"></i>
                        </div>
                        <h3 class="feature-title">Real-time Analytics</h3>
                        <p class="feature-description">Watch responses flow in real-time with interactive dashboards and instant insights that update as data comes in.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-globe"></i>
                        </div>
                        <h3 class="feature-title">Global Distribution</h3>
                        <p class="feature-description">Share surveys across multiple channels and reach your audience wherever they are with smart distribution tools.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3 class="feature-title">Enterprise Security</h3>
                        <p class="feature-description">Your data is protected with military-grade encryption and compliance with international privacy standards.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-palette"></i>
                        </div>
                        <h3 class="feature-title">Custom Branding</h3>
                        <p class="feature-description">Create surveys that perfectly match your brand with advanced customization and white-label options.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <h3 class="feature-title">Advanced Insights</h3>
                        <p class="feature-description">Uncover hidden patterns in your data with machine learning-powered analytics and predictive insights.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <span class="stat-number counter" data-target="50000">0</span>
                        <div class="stat-label">Surveys Created</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <span class="stat-number counter" data-target="2500000">0</span>
                        <div class="stat-label">Responses Collected</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <span class="stat-number counter" data-target="15000">0</span>
                        <div class="stat-label">Happy Customers</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <span class="stat-number">99.9%</span>
                        <div class="stat-label">Uptime Guarantee</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <div class="mb-3">
                        <h5 class="text-white fw-bold">Survey Aggregator</h5>
                        <p class="mb-0">The future of survey intelligence</p>
                    </div>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div class="social-links mb-3">
                        <a href="#" class="text-decoration-none me-3">
                            <i class="bi bi-twitter fs-5"></i>
                        </a>
                        <a href="#" class="text-decoration-none me-3">
                            <i class="bi bi-linkedin fs-5"></i>
                        </a>
                        <a href="#" class="text-decoration-none">
                            <i class="bi bi-github fs-5"></i>
                        </a>
                    </div>
                </div>
            </div>
            <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">&copy; 2025 Survey Aggregator. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="text-decoration-none me-3" style="color: rgba(255,255,255,0.6)">Privacy</a>
                    <a href="#" class="text-decoration-none me-3" style="color: rgba(255,255,255,0.6)">Terms</a>
                    <a href="#" class="text-decoration-none" style="color: rgba(255,255,255,0.6)">Contact</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    
    <script>
        // Initialize GSAP
        gsap.registerPlugin(ScrollTrigger);
        
        // Page Load Animation
        window.addEventListener('load', function() {
            const tl = gsap.timeline();
            
            tl.from('.navbar', {
                y: -100,
                opacity: 0,
                duration: 1,
                ease: "power3.out"
            })
            .from('.hero-title', {
                y: 100,
                opacity: 0,
                duration: 1.2,
                ease: "power3.out"
            }, "-=0.5")
            .from('.hero-subtitle', {
                y: 50,
                opacity: 0,
                duration: 1,
                ease: "power3.out"
            }, "-=0.7")
            .from('.search-container', {
                scale: 0.8,
                opacity: 0,
                duration: 1,
                ease: "back.out(1.7)"
            }, "-=0.5")
            .from('.cta-btn', {
                y: 30,
                opacity: 0,
                duration: 0.8,
                stagger: 0.2,
                ease: "power3.out"
            }, "-=0.3");
        });
        
        // Scroll Animations
        gsap.utils.toArray('.feature-card').forEach((card, i) => {
            gsap.from(card, {
                y: 100,
                opacity: 0,
                duration: 0.8,
                delay: i * 0.1,
                scrollTrigger: {
                    trigger: card,
                    start: "top 80%",
                    end: "bottom 20%",
                    toggleActions: "play none none reverse"
                }
            });
        });
        
        // Counter Animation
        gsap.utils.toArray('.counter').forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target'));
            
            gsap.to(counter, {
                textContent: target,
                duration: 2,
                ease: "power2.out",
                snap: { textContent: 1 },
                scrollTrigger: {
                    trigger: counter,
                    start: "top 80%",
                    toggleActions: "play none none none"
                },
                onUpdate: function() {
                    counter.textContent = Math.ceil(counter.textContent).toLocaleString();
                }
            });
        });
        
        // Parallax Effect
        gsap.utils.toArray('.floating-element').forEach(element => {
            gsap.to(element, {
                y: -100,
                rotation: 360,
                duration: 10,
                repeat: -1,
                ease: "none",
                yoyo: true
            });
        });
        
        // Navbar Scroll Effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
            
            // Update scroll indicator
            const scrolled = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
            document.querySelector('.scroll-indicator').style.width = scrolled + '%';
        });
        
        // Cursor Trail Effect
        let mouseX = 0, mouseY = 0;
        let trailX = 0, trailY = 0;
        const trail = document.querySelector('.cursor-trail');
        
        document.addEventListener('mousemove', function(e) {
            mouseX = e.clientX;
            mouseY = e.clientY;
        });
        
        function animateTrail() {
            trailX += (mouseX - trailX) * 0.1;
            trailY += (mouseY - trailY) * 0.1;
            
            trail.style.left = trailX - 10 + 'px';
            trail.style.top = trailY - 10 + 'px';
            
            requestAnimationFrame(animateTrail);
        }
        animateTrail();
        
        // Magnetic Effect on Buttons
        gsap.utils.toArray('.cta-btn').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                gsap.to(btn, {
                    scale: 1.05,
                    duration: 0.3,
                    ease: "power2.out"
                });
            });
            
            btn.addEventListener('mouseleave', function() {
                gsap.to(btn, {
                    scale: 1,
                    duration: 0.3,
                    ease: "power2.out"
                });
            });
        });
        
        // Feature Cards Tilt Effect
        gsap.utils.toArray('.feature-card').forEach(card => {
            card.addEventListener('mousemove', function(e) {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                const rotateX = (y - centerY) / 10;
                const rotateY = (centerX - x) / 10;
                
                gsap.to(card, {
                    rotationX: rotateX,
                    rotationY: rotateY,
                    duration: 0.3,
                    transformPerspective: 1000,
                    transformOrigin: "center"
                });
            });
            
            card.addEventListener('mouseleave', function() {
                gsap.to(card, {
                    rotationX: 0,
                    rotationY: 0,
                    duration: 0.5,
                    ease: "power2.out"
                });
            });
        });
        
        // Search Box Focus Animation
        const searchBox = document.querySelector('.search-box');
        searchBox.addEventListener('focus', function() {
            gsap.to(searchBox, {
                scale: 1.02,
                duration: 0.3,
                ease: "power2.out"
            });
        });
        
        searchBox.addEventListener('blur', function() {
            gsap.to(searchBox, {
                scale: 1,
                duration: 0.3,
                ease: "power2.out"
            });
        });
        
        // Text Reveal Animation
        gsap.utils.toArray('.section-title').forEach(title => {
            gsap.from(title, {
                opacity: 0,
                y: 50,
                duration: 1,
                scrollTrigger: {
                    trigger: title,
                    start: "top 80%",
                    end: "bottom 20%",
                    toggleActions: "play none none reverse"
                }
            });
        });
        
        gsap.utils.toArray('.section-subtitle').forEach(subtitle => {
            gsap.from(subtitle, {
                opacity: 0,
                y: 30,
                duration: 0.8,
                delay: 0.2,
                scrollTrigger: {
                    trigger: subtitle,
                    start: "top 80%",
                    end: "bottom 20%",
                    toggleActions: "play none none reverse"
                }
            });
        });
        
        // Smooth Scrolling for Anchor Links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    gsap.to(window, {
                        duration: 1.5,
                        scrollTo: {
                            y: target,
                            offsetY: 100
                        },
                        ease: "power2.inOut"
                    });
                }
            });
        });
        
        // Stagger Animation for Stats Cards
        gsap.from('.stat-card', {
            y: 50,
            opacity: 0,
            duration: 0.8,
            stagger: 0.2,
            scrollTrigger: {
                trigger: '.stats-section',
                start: "top 80%",
                end: "bottom 20%",
                toggleActions: "play none none reverse"
            }
        });
        
        // Background Gradient Animation
        gsap.to('.bg-animation::before', {
            rotation: 360,
            duration: 20,
            repeat: -1,
            ease: "none"
        });
        
        // Mobile Menu Animation
        const navbarToggler = document.querySelector('.navbar-toggler');
        const navbarCollapse = document.querySelector('.navbar-collapse');
        
        navbarToggler.addEventListener('click', function() {
            if (navbarCollapse.classList.contains('show')) {
                gsap.to(navbarCollapse, {
                    height: 0,
                    opacity: 0,
                    duration: 0.3,
                    ease: "power2.inOut"
                });
            } else {
                gsap.to(navbarCollapse, {
                    height: 'auto',
                    opacity: 1,
                    duration: 0.3,
                    ease: "power2.inOut"
                });
            }
        });
        
        // Intersection Observer for Advanced Animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate');
                }
            });
        }, observerOptions);
        
        // Observe all feature cards
        document.querySelectorAll('.feature-card').forEach(card => {
            observer.observe(card);
        });
        
        // Loading Screen Animation (if you want to add one)
        function createLoadingAnimation() {
            const loadingOverlay = document.createElement('div');
            loadingOverlay.className = 'loading-overlay';
            loadingOverlay.innerHTML = `
                <div class="loading-spinner">
                    <div class="spinner-ring"></div>
                    <div class="loading-text">Loading Experience...</div>
                </div>
            `;
            
            const loadingCSS = `
                .loading-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: var(--dark-gradient);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 10000;
                }
                
                .loading-spinner {
                    text-align: center;
                }
                
                .spinner-ring {
                    width: 60px;
                    height: 60px;
                    border: 3px solid rgba(102, 126, 234, 0.3);
                    border-top: 3px solid #667eea;
                    border-radius: 50%;
                    margin: 0 auto 20px;
                    animation: spin 1s linear infinite;
                }
                
                .loading-text {
                    color: white;
                    font-size: 1.1rem;
                    font-weight: 500;
                }
                
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            
            const style = document.createElement('style');
            style.textContent = loadingCSS;
            document.head.appendChild(style);
            document.body.appendChild(loadingOverlay);
            
            // Remove loading screen after page loads
            window.addEventListener('load', function() {
                gsap.to(loadingOverlay, {
                    opacity: 0,
                    duration: 0.5,
                    ease: "power2.inOut",
                    onComplete: function() {
                        loadingOverlay.remove();
                        style.remove();
                    }
                });
            });
        }
        
        // Uncomment the line below if you want the loading animation
        // createLoadingAnimation();
        
        // Performance optimization: Debounce scroll events
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
        
        // Apply debouncing to scroll events
        const debouncedScrollHandler = debounce(function() {
            // Additional scroll-based animations can go here
        }, 10);
        
        window.addEventListener('scroll', debouncedScrollHandler);
    </script>
    
    <!-- Custom JS -->
    <script src="assets/script.js"></script>
</body>
</html>