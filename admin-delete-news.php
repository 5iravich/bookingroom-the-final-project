<?php session_start();
require_once './config/db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $postid = $_POST['postid'];

            // Delete the row from the database for the specified stdid
            $deleteSql = "DELETE FROM posts WHERE postid = :postid";
            $stmt = $conn->prepare($deleteSql);
            $stmt->bindParam(':postid', $postid);
            $stmt->execute();

        // Redirect back to the original page after updating
        header("Location: admin.php"); // Change 'original_page.php' to your actual page
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
