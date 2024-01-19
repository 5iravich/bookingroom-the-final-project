<?php 
    session_start();
    require_once './config/db.php';
    if (!isset($_SESSION['user_login'])) {
        $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
        header('location: index.php');
        exit();
    }

    $user_id = $_SESSION['user_login'];

	try {
		$user_data = $conn->prepare("SELECT * FROM users WHERE stdid = :stdid");
		$user_data->bindParam(":stdid", $user_id);
		$user_data->execute();
		$user = $user_data->fetch(PDO::FETCH_ASSOC);
	} catch (PDOException $e) {
		echo $e->getMessage();
	}

    if (isset($_POST['booking'])) {
        $user_id = $_SESSION['user_login'];
        $roomId = $_GET['room_id'];
    
        try {
            // Insert the data into the bookings table
            $insert_booking = $conn->prepare("INSERT INTO bookings (stdid, room_id) VALUES (:stdid, :room_id)");
            $insert_booking->bindParam(":stdid", $user_id);
            $insert_booking->bindParam(":room_id", $roomId);
            $insert_booking->execute();
    
            // Redirect the user to a success page or any other appropriate action
            header('location: user-booking.php');
            exit();
        } catch (PDOException $e) {
            echo $e->getMessage();
            // Handle the database error gracefully, e.g., display an error message
        }
    }

    try {
        $existing_booking = $conn->prepare("SELECT * FROM bookings WHERE stdid = :stdid");
        $existing_booking->bindParam(":stdid", $user_id);
        $existing_booking->execute();
        $existing_booking_data = $existing_booking->fetch(PDO::FETCH_ASSOC);
    
        if ($existing_booking_data) {
            // Redirect the user to a different page or display a message indicating they already have a booking
            $_SESSION['error'] = 'คุณมีการจองห้องพักอยู่แล้ว';
            header('location: user-booking.php');
            exit();
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
        // Handle the database error gracefully, e.g., display an error message
    }
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" type="image/svg" href="https://sv1.picz.in.th/images/2023/10/27/ddsUZnt.png"/>
    <title>Confirm booking</title>
    <link rel="stylesheet" type="text/css" href="./css/confirmBook.css">
    <link href="https://fonts.googleapis.com/css?family=Mitr:600&display=swap" rel="stylesheet">
	<script src="https://kit.fontawesome.com/a81368914c.js"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
    <div class="confirmTicket">
        <h1 class="paymentCard">ยืนยันการจองห้องพัก</h1>
        <h4 class="light">*เงื่อนไข*</h4>
        <span class="condition">1. ข้าพเจ้ามีความประสงค์ขอเข้าพักในหอพักนิสิต มหาวิทยาลัยเกษตรศาสตร์ วิทยาเขตกำแพงแสน 
                                โดยข้าพเจ้าจะปฏิบัติตามระเบียบหอพัก และระเบียบมหาวิทยาลัยฯ ทุกประการ<br>
                                2. หากข้าพเจ้าทำผิดระเบียบหอพักนิสิตหรือระเบียบมหาวิทยาลัยฯ ข้าพเจ้ายินดีที่จะให้หน่วยงานหอพักนิสิต 
                                ดำเนินการลงโทษตามระเบียบ และหากเป็นการทำผิดระเบียบหอพักบ่อยครั้ง หรือทำความผิดวินัยร้ายแรง 
                                ข้าพเจ้ายินดีที่จะให้งานหอพัก ตัดสิทธิ์การพักอาศัยในหอพักทันที และแจ้งให้ผู้ปกครองและผู้รับรองทราบ<br>
                                3. ข้าพเจ้าจะชำระค่าธรรมเนียมหอพัก และค่าไฟ้า น้ำประปา ตามอัตรา และวันที่ มหาวิทยาลัยฯ กำหนด 
                                หากข้าพเจ้าไม่ชำระค่าธรรมเนียมตามกำหนด ยินดีให้มหาวิทยาลัยฯ คิดค่าปรับตามระเบียบ และแจ้งผู้ปกครองให้ทราบ 
                                และลงโทษตามระเบียบ<br></span>
        <h6 class="docinfo">เอกสารรายละเอียด</h6>
        <a class="bluelight" href="https://itservice.kps.ku.ac.th/student/apps/kpsdorm/system/dorm-rule.pdf" target="_blank">1. ระเบียบว่าด้วยหอพักนิสิต มหาวิทยาลัยเกษตรศาสตร์ วิทยาเขตกำแพงแสน พ.ศ.2554</a><br>
        <a class="bluelight" href="https://itservice.kps.ku.ac.th/student/apps/kpsdorm/system/rate-pay-dorm.pdf" target="_blank">2. อัตราค่าธรรมเนียมหอพักนิสิต และค่าปรับต่างๆ ของหอพักนิสิต</a><br>
        
        <div class="col-payment">
                <div class="paymentInfo">
                    <label>รหัสนิสิต: <?php echo $user['stdid']; ?></label><br>
                    <?php
                    // Check if dorm_id and room_number are provided in the query string
                    if (isset($_GET['dorm_id']) && isset($_GET['room_number'])) {
                        $dormId = $_GET['dorm_id'];
                        $roomNumber = $_GET['room_number'];
                        $dormName = $_GET['dorm_name'];
                        $price = $_GET['room_price'];

                        $roomPrice = number_format($price, 0.2);
                        echo "<label>ตึก $dormId $dormName ห้องหมายเลข $roomNumber </label>";
                        echo '<div class="colpay">
                            <p>ค่าธรรมเนียมเข้าพักต่อการศึกษา ฿'.$roomPrice.'</p>
                            <p>รวมทั้งหมด ฿'.$roomPrice.'</p>
                        </div>';
                    }
                    ?>
                </div>
                <span class="confirmrule">
                    <form method="POST" action="">
                        <p>✅ ข้าพเจ้าทราบแล้วว่าหากทำการจองไปแล้วจะไม่สามารถ เปลี่ยนหรือยกเลิกการจองได้</p>
                        <p>✅ ข้าพเจ้าได้ตรวจสอบรหัสนิสิต ค่าธรรมเนียม หมายเลขตึก/ห้อง ว่าถูกต้องตามที่ข้าพเจ้าเลือก และได้อ่านเงื่อนไขครบถ้วนแล้ว</p>
                        <div class="col-btn">
                            <input type="submit" name="booking" class="bookbtngreen" value="ยอมรับและจอง">
                            <input type="button" id="cancelButton" name="cancel" class="bookbtnred" value="ยกเลิก">
                        </div>
                    </form>
                </span>
        </div>
    </div>
    <script>
        document.getElementById("cancelButton").addEventListener("click", function() {
            window.location.href = "user-booking.php";
        });
    </script>
</body>
</html>