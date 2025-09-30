<?php
// volunteer_registration.php
include 'db_connect.php'; // Include the database connection file

// Get the form data
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$location = $_POST['location'];
$availability = $_POST['availability'];

// Insert the volunteer data into the volunteers table
$stmt = $conn->prepare("INSERT INTO volunteers (name, email, phone, location, availability) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $email, $phone, $location, $availability);

if ($stmt->execute()) {
    echo "Volunteer registered successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
