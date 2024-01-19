<?php
session_start();
require_once './config/db.php';

if (isset($_POST['team_id']) && isset($_POST['newRole'])) {
    $team_id = $_POST['team_id'];
    $newRole = $_POST['newRole'];

    try {
        $updateQuery = "UPDATE teams SET urole = :newRole WHERE team_id = :team_id";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(":team_id", $team_id);
        $updateStmt->bindParam(":newRole", $newRole);
        $updateStmt->execute();
        // Handle success or error accordingly
        echo 'Role updated successfully';
    } catch (PDOException $e) {
        // Handle error
        echo 'Error updating role: ' . $e->getMessage();
    }
} else {
    // Handle invalid input
    echo 'Invalid request';
}
?>
