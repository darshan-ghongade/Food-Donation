r<?php
session_start(); // Start session

// If the user is not logged in, redirect them to the login/signup page
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php"); // Redirect to auth page if not logged in
    exit();
}

include 'db_connect.php'; // Include database connection

// Include PHPMailer for sending email
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Use Composer's autoloader

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Donor details
    $donor_name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $donation_description = $_POST['donation_description'];
    // Get donor's live location from the form
    $donor_latitude = $_POST['latitude'];
    $donor_longitude = $_POST['longitude'];
    $location = $_POST['location'];

    // Insert the donation details into the database without volunteer_id
    $stmt = $conn->prepare("INSERT INTO donations (donor_name, email, phone, donation_description, location) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $donor_name, $email, $phone, $donation_description, $location);

    if ($stmt->execute()) {
        // Get the last inserted donation ID
        $donation_id = $stmt->insert_id;

        // Find the closest available volunteer
        $sql = "SELECT id, name, email, latitude, longitude 
                FROM volunteers 
                WHERE availability = 'available'";
        $result = $conn->query($sql);

        $closest_volunteer = null;
        $shortest_distance = PHP_INT_MAX;

        while ($volunteer = $result->fetch_assoc()) {
            // Haversine formula to calculate distance between donor and volunteer
            $volunteer_latitude = $volunteer['latitude'];
            $volunteer_longitude = $volunteer['longitude'];

            // Calculate the distance
            $distance = haversineDistance($donor_latitude, $donor_longitude, $volunteer_latitude, $volunteer_longitude);

            if ($distance < $shortest_distance) {
                $shortest_distance = $distance;
                $closest_volunteer = $volunteer;
            }
        }

        if ($closest_volunteer) {
            // Assign the closest volunteer and mark them as busy
            $volunteer_id = $closest_volunteer['id'];
            $volunteer_email = $closest_volunteer['email'];
            $volunteer_name = $closest_volunteer['name'];

            // Update volunteer availability
            $update_stmt = $conn->prepare("UPDATE volunteers SET availability = 'busy' WHERE id = ?");
            $update_stmt->bind_param("i", $volunteer_id);
            $update_stmt->execute();

            // Find the nearest active nonprofit organization to the volunteer
            $sql_nonprofit = "SELECT id, name, email, latitude, longitude 
                              FROM nonprofit_organisation 
                              WHERE status = 'active'"; // Correct table name
            $result_nonprofit = $conn->query($sql_nonprofit);

            $closest_nonprofit = null;
            $shortest_distance_nonprofit = PHP_INT_MAX;

            while ($nonprofit = $result_nonprofit->fetch_assoc()) {
                $nonprofit_latitude = $nonprofit['latitude'];
                $nonprofit_longitude = $nonprofit['longitude'];

                // Calculate distance between volunteer and nonprofit
                $distance_nonprofit = haversineDistance($volunteer_latitude, $volunteer_longitude, $nonprofit_latitude, $nonprofit_longitude);

                if ($distance_nonprofit < $shortest_distance_nonprofit) {
                    $shortest_distance_nonprofit = $distance_nonprofit;
                    $closest_nonprofit = $nonprofit;
                }
            }

            if ($closest_nonprofit) {
                $nonprofit_id = $closest_nonprofit['id'];
                $nonprofit_email = $closest_nonprofit['email'];
                $nonprofit_name = $closest_nonprofit['name'];
                $nonprofit_latitude = $closest_nonprofit['latitude'];
                $nonprofit_longitude = $closest_nonprofit['longitude'];

                // Insert into delivery_tasks table
                $task_stmt = $conn->prepare("INSERT INTO delivery_tasks (volunteer_id, donation_id, nonprofit_id) VALUES (?, ?, ?)");
                $task_stmt->bind_param("iii", $volunteer_id, $donation_id, $nonprofit_id);
                $task_stmt->execute();
                $task_stmt->close();

                // Send email to the volunteer
                $mail = new PHPMailer(true);

                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';         // Set the SMTP server to send through
                    $mail->SMTPAuth   = true;                     // Enable SMTP authentication
                    $mail->Username   = 'Enter Your Gmail';    // Gmail username
                    $mail->Password   = 'Enter password';      // Gmail app password (not regular password)
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
                    $mail->Port       = 587;                      // TCP port to connect to

                    // Recipients
                    $mail->setFrom('Your Gmail', 'FoodForGood');
                    $mail->addAddress($volunteer_email); // Add volunteer's email

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Donation Pickup Assignment';
                    $mail->Body    = "
                    Hello $volunteer_name,<br><br>
                    You have been assigned to collect the following donation:<br><br>
                    Donor Name: $donor_name<br>
                    Email: $email<br>
                    Phone: $phone<br>
                    Donation Description: $donation_description<br>
                    Location: $location<br><br>
                    Donor's Live Location: <a href='https://www.google.com/maps/search/?api=1&query=$donor_latitude,$donor_longitude'>Click here to view on Google Maps</a><br><br>
                    After collecting the donation, please deliver it to the following nonprofit organization:<br><br>
                    Nonprofit Organization: $nonprofit_name<br>
                    Nonprofit Location: Latitude: $nonprofit_latitude, Longitude: $nonprofit_longitude<br>
                    Nonprofit Location on Map: <a href='https://www.google.com/maps/search/?api=1&query=$nonprofit_latitude,$nonprofit_longitude'>Click here to view on Google Maps</a><br><br>
                    Thank you for your help!<br>Regards,<br>Your Team";

                    $mail->send();
                    echo 'Message has been sent to the volunteer';
                } catch (Exception $e) {
                    echo "Failed to send email to the volunteer. Error: {$mail->ErrorInfo}";
                }
            } else {
                echo "No active nonprofit organizations available.";
            }
        } else {
            echo "No available volunteers at this time. Please try again later.";
        }
        $stmt->close();
    } else {
        echo "There was an error submitting your donation. Please try again.";
    }

    $conn->close(); // Close the database connection
}

// Haversine formula to calculate distance between two lat/long points
function haversineDistance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371; // Earth's radius in kilometers

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    $distance = $earth_radius * $c; // Distance in kilometers

    return $distance;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate</title>
    <link rel="stylesheet" href="s.css">
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
        <h1>Donate</h1>
        <p>Your donation can make a difference. Please fill out the form below to submit your donation.</p>

        <!-- Donation Form -->
        <form action="donate.php" method="POST" id="donationForm">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="phone">Phone:</label>
            <input type="tel" id="phone" name="phone" required>

            <label for="donation_description">Donation Description:</label>
            <textarea id="donation_description" name="donation_description" rows="4" required></textarea>
            
            <label for="location">Location:</label>
            <input type="text" id="location" name="location" required>

            <!-- Hidden fields to store latitude and longitude -->
            <input type="hidden" id="latitude" name="latitude" required>
            <input type="hidden" id="longitude" name="longitude" required>

            <button type="submit">Submit Donation</button>
        </form>
    </section>

    <script>
        // Get the user's location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                // Set the latitude and longitude fields in the form
                document.getElementById('latitude').value = position.coords.latitude;
                document.getElementById('longitude').value = position.coords.longitude;
            }, function() {
                alert('Unable to retrieve your location. Please enter it manually.');
            });
        } else {
            alert('Geolocation is not supported by this browser.');
        }
    </script>
</body>
</html>
