<?php session_start();
require_once './config/db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $stdid = $_POST['stdid'];
        $newStatus = $_POST['statuspay'];

        if ($newStatus === 'cancelled') {
            // Delete the row from the database for the specified stdid
            $deleteSql = "DELETE FROM bookings WHERE stdid = :stdid";
            $stmt = $conn->prepare($deleteSql);
            $stmt->bindParam(':stdid', $stdid);
            $stmt->execute();
        } else {
            // Update the statuspay field in the database for the specified stdid
            $updateSql = "UPDATE bookings SET statuspay = :statuspay WHERE stdid = :stdid";
            $stmt = $conn->prepare($updateSql);
            $stmt->bindParam(':stdid', $stdid);
            $stmt->bindParam(':statuspay', $newStatus);
            $stmt->execute();
        }

        // Redirect back to the original page after updating
        header("Location: admin-bookings.php"); // Change 'original_page.php' to your actual page
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
