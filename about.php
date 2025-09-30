<?php session_start(); ?> <!-- Start the session -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <link rel="stylesheet" href="s.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="get_involved.php">Get Involved</a></li>
                <li>
                  <a href="donate.php" onclick="checkSignup(); return false;">Donate</a>
                </li>
                      <!-- Display logout button if the user is logged in -->
                      <?php if (isset($_SESSION['user_id'])): ?>
                          <li><a href="logout.php">Logout</a></li>
                      <?php else: ?>
                          <!-- Show login/signup button if not logged in -->
                          <li><a href="auth.php">Sign Up / Login</a></li>
                      <?php endif; ?>
            </ul>
        </nav>
    </header>
    <section class="about-section">
        <h1>About Our Food Donation Platform</h1>
        <p>Our mission is to connect food donors with those in need. We believe that no one should go hungry, and through our platform, we aim to reduce food waste while helping our community.</p>
        <h2>Our Vision</h2>
        <p>We envision a world where surplus food is redirected to those who need it most, fostering a sense of community and support.</p>
        <h2>Our Goals</h2>
        <ul>
            <li>To raise awareness about food waste and its impact.</li>
            <li>To provide a seamless platform for food donation.</li>
            <li>To engage volunteers in our mission to help those in need.</li>
        </ul>
        <h2>Get Involved</h2>
        <p>If you are interested in helping us achieve our goals, consider volunteering or donating food. Every little bit helps!</p>
    </section>
</body>
</html>
