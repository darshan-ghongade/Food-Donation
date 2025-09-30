<?php
session_start(); // Start session

// If user is not logged in, redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php"); // Redirect to auth page if not logged in
    exit();
}

include 'db_connect.php'; // Include database connection

// If form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $volunteer_name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $availability = $_POST['availability'];
    $location = $_POST['location'];
    $latitude = $_POST['latitude'];  // Live location (latitude)
    $longitude = $_POST['longitude']; // Live location (longitude)

    // Check if a volunteer with the same name, email, and phone already exists
    $stmt = $conn->prepare("SELECT id, availability FROM volunteers WHERE name = ? AND email = ? AND phone = ?");
    $stmt->bind_param("sss", $volunteer_name, $email, $phone);
    $stmt->execute();
    $stmt->store_result();
    
    // If the volunteer exists, update the availability
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($volunteer_id, $current_availability);
        $stmt->fetch();
        
        // Check if the availability needs to be updated
        if ($current_availability !== $availability) {
            $update_stmt = $conn->prepare("UPDATE volunteers SET availability = ?, latitude = ?, longitude = ?, location = ? WHERE id = ?");
            $update_stmt->bind_param("sssii", $availability, $latitude, $longitude, $location, $volunteer_id);
            if ($update_stmt->execute()) {
                echo "Your availability has been updated, $volunteer_name.";
            } else {
                echo "There was an error updating your availability. Please try again.";
            }
            $update_stmt->close();
        } else {
            echo "Your availability is already set as $availability, no changes were made.";
        }
    } else {
        // If the volunteer doesn't exist, insert a new record
        $insert_stmt = $conn->prepare("INSERT INTO volunteers (name, email, phone, availability, location, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("sssssss", $volunteer_name, $email, $phone, $availability, $location, $latitude, $longitude);
        
        if ($insert_stmt->execute()) {
            echo "Thank you, $volunteer_name! You are now registered as a volunteer.";
        } else {
            echo "There was an error submitting your form. Please try again.";
        }
        $insert_stmt->close();
    }

    $stmt->close();
    $conn->close(); // Close database connection
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Involved</title>
    <link rel="stylesheet" href="s.css">
    <script>
        // Function to get user's live location
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition);
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }

        // Set latitude and longitude in the form fields
        function showPosition(position) {
            document.getElementById('latitude').value = position.coords.latitude;
            document.getElementById('longitude').value = position.coords.longitude;
        }
        
        // Call getLocation on page load
        window.onload = function() {
            getLocation();
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
                <li><a href="donate.php">Donate</a></li>
                <!-- Show Logout if logged in -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="auth.php">Sign Up / Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <section>
        <h1>Get Involved</h1>
        <p>Thank you for your interest in getting involved with our platform. Please fill out the form below to register as a volunteer.</p>

        <!-- Volunteer Registration Form -->
        <form action="get_involved.php" method="POST">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="phone">Phone:</label>
            <input type="tel" id="phone" name="phone" required>

            <label for="location">Location:</label>
            <input type="text" id="location" name="location" required>

            <label for="availability">Availability:</label>
            <select id="availability" name="availability" required>
                <option value="Available">Available</option>
                <option value="Busy">Busy</option>
            </select>

            <!-- Hidden fields for live location -->
            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">

            <button type="submit">Submit</button>
        </form>
    </section>
</body>
</html>
