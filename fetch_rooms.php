<?php
    session_start();
    require_once './config/db.php';

    $dormId = $_GET['dorm_id'];

    $sql = "SELECT rooms.*, COUNT(booking_id) AS booking_count
            FROM rooms
            LEFT JOIN bookings ON rooms.room_id = bookings.room_id
            WHERE rooms.dorm_id = :dormId
            GROUP BY rooms.room_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':dormId', $dormId, PDO::PARAM_INT);
    $stmt->execute();

    $roomListData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($roomListData);
?>
