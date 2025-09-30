<?php 
session_start(); // Start the session

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    // If the user is logged in, display a message and the logout button
    echo "<h1>Welcome, " . htmlspecialchars($_SESSION['username']) . "!</h1>";
    echo "<p>You are already logged in.</p>";
    echo '<p><a href="logout.php">Logout</a></p>';
    exit();
}

include 'db_connect.php'; // Include database connection

// Handling signup and login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if signup form is submitted
    if ($_POST['action'] == 'signup') {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Insert the new user into the database
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);

        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['username'] = $username;
            header("Location: index.php"); // Redirect to homepage after signup
            exit();
        } else {
            echo "Signup failed. Please try again.";
        }
        $stmt->close();
    }

    // Check if login form is submitted
    if ($_POST['action'] == 'login') {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Fetch user details from the database
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($user_id, $username, $hashed_password);
        $stmt->fetch();

        if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            header("Location: index.php"); // Redirect to homepage after login
            exit();
        } else {
            echo "Invalid email or password.";
        }
        $stmt->close();
    }
}

$conn->close(); // Close database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup/Login</title>
    <link rel="stylesheet" href="s.css">
    <style>
        .form-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .toggle-btn {
            margin: 10px;
            cursor: pointer;
            font-size: 16px;
        }
        .toggle-btn.active {
            font-weight: bold;
        }
        form {
            display: none;
        }
        form.active {
            display: block;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="getinvolved.php">Get Involved</a></li>
                <li><a href="donate.php">Donate</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a></li>
                <?php else: ?>
                    <li><a href="auth.php">Signup/Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <div class="form-container">
        <button class="toggle-btn active" id="signup-btn">Signup</button>
        <button class="toggle-btn" id="login-btn">Login</button>

        <!-- Signup Form -->
        <form id="signup-form" class="active" method="POST" action="auth.php">
            <input type="hidden" name="action" value="signup">
            <label for="username">Username:</label>
            <input type="text" name="username" required />
            <label for="email">Email:</label>
            <input type="email" name="email" required />
            <label for="password">Password:</label>
            <input type="password" name="password" required minlength="8" />
            <button type="submit">Signup</button>
        </form>

        <!-- Login Form -->
        <form id="login-form" method="POST" action="auth.php">
            <input type="hidden" name="action" value="login">
            <label for="email">Email:</label>
            <input type="email" name="email" required />
            <label for="password">Password:</label>
            <input type="password" name="password" required minlength="8" />
            <button type="submit">Login</button>
        </form>
    </div>
    <footer>
        <p>&copy; 2024 Food Donation Platform. All rights reserved.</p>
    </footer>
    <script>
        const signupForm = document.getElementById('signup-form');
        const loginForm = document.getElementById('login-form');
        const signupBtn = document.getElementById('signup-btn');
        const loginBtn = document.getElementById('login-btn');

        signupBtn.addEventListener('click', () => {
            signupForm.classList.add('active');
            loginForm.classList.remove('active');
            signupBtn.classList.add('active');
            loginBtn.classList.remove('active');
        });

        loginBtn.addEventListener('click', () => {
            loginForm.classList.add('active');
            signupForm.classList.remove('active');
            loginBtn.classList.add('active');
            signupBtn.classList.remove('active');
        });
    </script>
</body>
</html>
