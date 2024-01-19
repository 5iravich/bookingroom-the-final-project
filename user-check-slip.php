<?php
session_start();
require_once './config/db.php';

// Check if the user is logged in or has appropriate permissions to run this script
if (!isset($_SESSION['user_login']) /* add your permission check here */) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

// Calculate the date 7 days ago
$sevenDaysAgo = date('Y-m-d H:i:s', strtotime('-7 days'));

// Define a SQL query to select and delete old records without slip_image
$sql = "DELETE FROM bookings WHERE slip_image IS NULL AND created_at < :sevenDaysAgo";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':sevenDaysAgo', $sevenDaysAgo, PDO::PARAM_STR);
$stmt->execute();

// Optionally, log the number of records deleted
$deletedCount = $stmt->rowCount();
if ($deletedCount > 0) {
    // Log or display a success message
    $_SESSION['success'] = "$deletedCount records deleted successfully.";
} else {
    // Log or display a message indicating no records were deleted.
    $_SESSION['warning'] = "No records to delete.";
}

// Redirect to the appropriate page
header('Location: user-booking.php');
exit();
