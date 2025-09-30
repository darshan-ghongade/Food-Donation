<?php session_start(); ?> <!-- Start the session at the top of the page -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link rel="stylesheet" href="s.css">
    <script>
      function checkSignup() {
        // Check if the user is logged in through PHP
        <?php if (!isset($_SESSION['user_id'])): ?>
          alert("Please sign up before donating.");
          window.location.href = "auth.php"; // Redirect to signup page
        <?php else: ?>
          window.location.href = "donate.php"; // Redirect to donate page
        <?php endif; ?>
      }
    </script>
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

    <section class="hero-section">
      <h1>Welcome to Our Food Donation Platform</h1>
      <p>Your generosity can make a difference!</p>
      
      <button onclick="checkSignup();">Donate Now</button>

        <!-- Show different content if logged in -->
        <?php if (isset($_SESSION['username'])): ?>
            <p>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>! You are logged in.</p>
        <?php else: ?>
            <p>Please <a href="auth.php">sign up or log in</a> to continue.</p>
        <?php endif; ?>
    </section>
</body>
</html>
