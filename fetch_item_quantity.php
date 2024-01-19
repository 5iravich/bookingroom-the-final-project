<?php
    require_once './config/db.php';

    if (isset($_GET['ItemID'])) {
        $ItemID = $_GET['ItemID'];
        $sql = "SELECT ItemQuantity FROM materials WHERE ItemID = :ItemID";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':ItemID', $ItemID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            echo $result['ItemQuantity'];
        } else {
            echo 'ไม่พบข้อมูล';
        }
    }
?>
