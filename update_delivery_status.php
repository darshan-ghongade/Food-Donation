<?php
// update_delivery_status.php
include 'db_connect.php'; // Include the database connection file

// Get the task ID from the form
$task_id = $_POST['task_id'];

// Update the task status to 'Completed'
$stmt = $conn->prepare("UPDATE delivery_tasks SET status = 'Completed', completion_date = NOW() WHERE id = ?");
$stmt->bind_param("i", $task_id);

if ($stmt->execute()) {
    // Make the volunteer available again
    $stmt = $conn->prepare("UPDATE volunteers SET availability = 'Available' WHERE id = (SELECT volunteer_id FROM delivery_tasks WHERE id = ?)");
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $stmt->close();

    echo "Delivery task completed and volunteer is now available.";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
