<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Digital Suggestion Box</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <main class="home-page">

        <section class="home-container">

            <div class="home-content">

                <div class="brand">
                    <div class="brand-icon">💡</div>
                    <span>Digital Suggestion Box</span>
                </div>

                <div class="hero-text">

                    <span class="welcome-label">WELCOME</span>

                    <h1>
                        Your Voice.<br>
                        <span>Our Improvement.</span>
                    </h1>

                    <p>
                        Share your ideas, concerns and suggestions anonymously.
                        Help make our college better for everyone.
                    </p>

                </div>

                <div class="role-section">

                    <h2>How would you like to continue?</h2>

                    <div class="role-cards">

                        <a href="college-code.php" class="role-card">

                            <div class="role-icon student-icon">
                                🎓
                            </div>

                            <div class="role-info">
                                <h3>I'm a Student</h3>
                                <p>
                                    Submit anonymous feedback
                                    and suggestions.
                                </p>
                            </div>

                            <span class="role-arrow">→</span>

                        </a>


                        <a href="login.php" class="role-card">

                            <div class="role-icon admin-icon">
                                🔐
                            </div>

                            <div class="role-info">
                                <h3>I'm an Admin</h3>
                                <p>
                                    Manage and respond to
                                    student suggestions.
                                </p>
                            </div>

                            <span class="role-arrow">→</span>

                        </a>

                    </div>

                </div>

                <div class="privacy-note">
                    <span>🔒</span>
                    <p>
                        Student submissions are completely anonymous.
                        Your identity is never collected or stored.
                    </p>
                </div>

            </div>


            <div class="home-visual">

                <div class="visual-circle circle-one"></div>
                <div class="visual-circle circle-two"></div>

                <div class="suggestion-illustration">

                    <div class="illustration-glow"></div>

                    <div class="bulb">
                        <div class="bulb-glass">
                            💡
                        </div>

                        <div class="bulb-base"></div>
                    </div>

                    <div class="floating-card card-one">
                        💬
                        <span>Share your idea</span>
                    </div>

                    <div class="floating-card card-two">
                        ✓
                        <span>Make an impact</span>
                    </div>

                    <div class="floating-card card-three">
                        🔒
                        <span>100% Anonymous</span>
                    </div>

                </div>

            </div>

        </section>

    </main>

    <script src="js/script.js"></script>

</body>
</html>