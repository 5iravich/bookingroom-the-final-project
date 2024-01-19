<?php
session_start();
require_once './config/db.php';

if (isset($_POST['addmat'])) {
    $ItemName = $_POST["ItemName"];
        $ItemCategory = $_POST["ItemCategory"];
        $ItemQuantity = $_POST["ItemQuantity"];
        $ItemUnit = $_POST["ItemUnit"];

        if (empty($ItemName)) {
            $_SESSION['error'] = 'กรุณากรอกชื่อวัสดุ';
            header("location: ./admin-storage.php");
        } else {
            try {
                $check_ItemName = $conn->prepare("SELECT ItemName FROM materials WHERE ItemName = :ItemName");
                $check_ItemName->bindParam(":ItemName", $ItemName);
                $check_ItemName->execute();
                $row = $check_ItemName->fetch(PDO::FETCH_ASSOC);
                
                if ($row["ItemName"] == $ItemName) {
                    $_SESSION['warning'] = "มีวัสดุนี้อยู่ในระบบแล้ว";
                    header("location: ./admin-storage.php");
                } else if (!isset($_SESSION['error'])) {
                    $stmt = $conn->prepare("INSERT INTO materials (ItemName, ItemCategory, ItemQuantity, ItemUnit) 
                                VALUES (:ItemName, :ItemCategory, :ItemQuantity, :ItemUnit)");
                    $stmt->bindParam(":ItemName", $ItemName);
                    $stmt->bindParam(":ItemCategory", $ItemCategory);
                    $stmt->bindParam(":ItemQuantity", $ItemQuantity);
                    $stmt->bindParam(":ItemUnit", $ItemUnit);
                    $stmt->execute();
                    $_SESSION['success'] = "เพิ่มข้อมูลเรียบร้อยแล้ว!";
                    header("location: ./admin-storage.php");
                } else {
                    $_SESSION['error'] = "มีบางอย่างผิดพลาด";
                    header("location: ./admin-storage.php");
                }
            } catch(PDOException $e) {
                echo $e->getMessage();
            }
        }
    }
?>
