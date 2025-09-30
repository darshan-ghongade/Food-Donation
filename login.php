<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user from the database
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $username, $hashed_password);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
        // If login successful, set session variables
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;

        // Redirect to homepage after successful login
        header("Location: index.php");
        exit();
    } else {
        // Invalid login credentials
        echo "Invalid email or password.";
    }

    $stmt->close();
    $conn->close();
}
?>
