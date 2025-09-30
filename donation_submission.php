<?php
// donation_submission.php
include 'db_connect.php'; // Include the database connection file

// Get the form data
$donor_name = $_POST['donor_name'];
$donor_email = $_POST['donor_email'];
$donor_phone = $_POST['donor_phone'];
$donation_description = $_POST['donation_description'];
$location = $_POST['location'];

// Insert the donation into the donations table
$stmt = $conn->prepare("INSERT INTO donations (donor_name, donor_email, donor_phone, donation_description, location) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $donor_name, $donor_email, $donor_phone, $donation_description, $location);

if ($stmt->execute()) {
    // Assign a volunteer to collect the donation
    assignVolunteer($conn, $stmt->insert_id, $location);
    echo "Donation submitted successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();

// Function to assign an available volunteer to the donation
function assignVolunteer($conn, $donation_id, $location) {
    // Find an available volunteer near the donation location
    $stmt = $conn->prepare("SELECT id FROM volunteers WHERE location = ? AND availability = 'Available' LIMIT 1");
    $stmt->bind_param("s", $location);
    $stmt->execute();
    $stmt->bind_result($volunteer_id);
    $stmt->fetch();
    $stmt->close();

    if ($volunteer_id) {
        // Find an orphanage or needy location
        $stmt = $conn->prepare("SELECT id FROM orphanages_needy ORDER BY RAND() LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($orphanage_id);
        $stmt->fetch();
        $stmt->close();

        // Assign the delivery task to the volunteer
        if ($orphanage_id) {
            $stmt = $conn->prepare("INSERT INTO delivery_tasks (volunteer_id, donation_id, orphanage_id) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $volunteer_id, $donation_id, $orphanage_id);
            $stmt->execute();
            $stmt->close();

            // Mark the volunteer as busy
            $stmt = $conn->prepare("UPDATE volunteers SET availability = 'Busy' WHERE id = ?");
            $stmt->bind_param("i", $volunteer_id);
            $stmt->execute();
            $stmt->close();

            echo "Volunteer assigned to collect the donation.";
        }
    } else {
        echo "No available volunteers near the donation location.";
    }
}
?>
