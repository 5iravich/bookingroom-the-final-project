<?php 
    session_start();
    require_once './config/db.php';
    if (!isset($_SESSION['user_login'])) {
        $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
        header('location: index.php');
        exit();
    }

    //list dorm gender
    $sql = "SELECT * FROM dorms";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stdid = $_SESSION['user_login'];
    $sql_user = "SELECT gender FROM users WHERE stdid = :stdid";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bindParam(':stdid', $stdid, PDO::PARAM_STR);
    $stmt_user->execute();
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    $sql_check_booking = "SELECT COUNT(*) as count FROM bookings WHERE stdid = :stdid";
    $stmt_check_booking = $conn->prepare($sql_check_booking);
    $stmt_check_booking->bindParam(':stdid', $stdid, PDO::PARAM_STR);
    $stmt_check_booking->execute();
    $booking_count = $stmt_check_booking->fetch(PDO::FETCH_ASSOC);

    // $sql_booking_info = "SELECT * FROM bookings WHERE stdid = :stdid";
    $sql_booking_info = "SELECT *, slip_image FROM bookings
                     JOIN rooms ON bookings.room_id = rooms.room_id
                     WHERE bookings.stdid = :stdid";
    $stmt_booking_info = $conn->prepare($sql_booking_info);
    $stmt_booking_info->bindParam(':stdid', $stdid, PDO::PARAM_STR);
    $stmt_booking_info->execute();
    $booking_info = $stmt_booking_info->fetch(PDO::FETCH_ASSOC);

    // $slipImage = $booking_info['slip_image'];

    $gender = $user['gender'];

    $sevenDaysAgo = date('Y-m-d H:i:s', strtotime('-7 days'));

    $sql = "DELETE FROM bookings WHERE slip_image IS NULL AND booking_timestamp < :sevenDaysAgo";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':sevenDaysAgo', $sevenDaysAgo, PDO::PARAM_STR);
    $stmt->execute();
    // เช็คการลบ 7 วัน
    // $deletedCount = $stmt->rowCount();
    // if ($deletedCount > 0) {
    //     $_SESSION['success'] = "$deletedCount records deleted successfully.";
    // } else {
    //     $_SESSION['warning'] = "No records to delete.";
    // }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <link rel="shortcut icon" type="image/svg" href="https://sv1.picz.in.th/images/2023/10/27/ddsUZnt.png"/>
        <title>Room Booking</title>
        <link rel="stylesheet" type="text/css" href="./css/user.css">
        <link rel="stylesheet" type="text/css" href="./css/booking.css">
        <link href="https://fonts.googleapis.com/css?family=Mitr:600&display=swap" rel="stylesheet">
        <script src="https://kit.fontawesome.com/a81368914c.js"></script>
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        <nav>
			<ul class="nav-list">
				<li class="nav-item">
					<a href="./user.php"><img src="https://sv1.picz.in.th/images/2023/10/27/ddsUZnt.png" alt="Logo"></a>
				</li>
                <li class="nav-item-a">
					<a href="./user.php">หน้าหลัก</a>
				</li>
				<li class="nav-item-a">
					<a href="./user-profile.php">โปรไฟล์</a>
				</li>
				<li class="nav-item-a active">
					<a href="./user-booking.php">จองหอพักนิสิต</a>
				</li>
				<li class="nav-item-a">
					<a href="./user-maintenance.php">แจ้งซ่อมบำรุง</a>
				</li>
				<li class="nav-item-b">
					<a class="btn-primary" href="./logout.php" >ออกจากระบบ</a>
				</li>
			</ul>
			<div class="mobile-menu">
				<div class="menu-toggle">
					<div class="bar bar1"></div>
					<div class="bar bar2"></div>
					<div class="bar bar3"></div>
				</div>
				<ul class="mobile-nav-list">
					<li class="mobile-nav-item">
						<a href="./user.php">หน้าหลัก</a>
					</li>
					<li class="mobile-nav-item">
						<a href="./user-profile.php">โปรไฟล์</a>
					</li>
					<li class="mobile-nav-item active">
						<a href="./user-booking.php">จองหอพักนิสิต</a>
					</li>
					<li class="mobile-nav-item">
						<a href="./user-maintenance.php">แจ้งซ่อมบำรุง</a>
					</li>
					<li class="mobile-nav-item">
						<a href="./logout.php">ออกจากระบบ</a>
					</li>
				</ul>
			</div>
		</nav>
    <div class="picture-cover">
        <img src="https://sv1.picz.in.th/images/2023/10/27/ddsUxev.png" class="start" alt="cover"/>
        <div class="card">
            <div class="card-img" style="background-image: url('https://sv1.picz.in.th/images/2023/10/27/ddsUKxP.md.jpeg');">
                <div class="bottomleft-container">
                    <p class="text-white">
                        <span class="jumbo"><b>แอร์</b></span><br>
                        <span class="large"><b>*ราคาค่าธรรมต่อเดือน</b></span><br>
                        <span class="tag-red">หอพักชายคนละ ฿2,250</span><br>
                        <span class="tag-red">หอพักหญิงคนละ ฿1,500</span><br>
                    </p>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-img" style="background-image: url('https://sv1.picz.in.th/images/2023/10/27/ddsUiKl.jpeg');">
                <div class="bottomleft-container">
                     <p class="text-white">
                        <span class="jumbo"><b>ธรรมดา</b></span><br>
                        <span class="tag-red">คนละ ฿2,000/เทอม</span><br>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php if(isset($_SESSION['error'])) { ?>
        <div class="alert alert-danger" role="alert">
            <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
            ?>
        </div>
    <?php } ?>
    <?php if(isset($_SESSION['success'])) { ?>
        <div class="alert alert-success" role="alert">
            <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
            ?>
        </div>
    <?php } ?>
    <?php if(isset($_SESSION['warning'])) { ?>
        <div class="alert alert-warning" role="alert">
            <?php 
                echo $_SESSION['warning'];
                unset($_SESSION['warning']);
            ?>
        </div>
    <?php } ?>

    <div class="bookInfo">
        <div class="bookDescription">
            <h5>🙏🏼 ยินดีต้อนรับสู่บริการจองหอพักออนไลน์</h5>
        </div>
        <div class="bookStatus">
            <?php if ($booking_info) { ?>
                <p>🔑 <?php echo htmlspecialchars($booking_info['stdid']); ?> คุณมีการจองหอพักแล้ว</p>
                <p class="statusrooms"></p>ตึกที่: <?php echo htmlspecialchars($booking_info['dorm_id']); ?> ห้อง: <?php echo htmlspecialchars($booking_info['room_number']); ?></p>
                <?php
                    $booking_status = $booking_info['statuspay'];
                    $payment_status = '';

                    if ($booking_status === 'pending') {
                        $payment_status = 'รอดำเนินการ';
                        $status_color = 'yellow';
                    } elseif ($booking_status === 'confirmed') {
                        $payment_status = 'ชำระเงินแล้ว';
                        $status_color = 'green';
                    } else {
                        $payment_status = 'ไม่ได้ชำระเงิน';
                        $status_color = 'red';
                    }
                    
                    echo '<div class="statusshow">';
                    echo '<p>สถานะการชำระเงิน: </p>';
                    echo '<p class="'. $status_color .'"> ' . $payment_status . '</p>';
                    echo '</div>';

                    if (!empty($booking_info['slip_image'])) {
                        echo '<div class="statusbtn"><a class="btnpayment" href ="./user-proof-payment.php" target="_blank">พิมพ์หลักฐานการชำระเงิน</a></div>';
                    } else {
                        echo '<a class="clickdownload" target="_blank" href="https://physics.flas.kps.ku.ac.th/images/News/หอพักนิสิต/แบบคำรับรองและยินยอมให้อาศัยในหอพัก.pdf">
                                <i class="fas fa-download"></i> แบบฟอร์มรับรองและยินยอมของบิดามารดาหรือผู้ปกครอง</a>';
                        echo '<div class="statusbtn"><button class="btnpayment" onclick="window.location.href = \'payment.php\';">ชำระเงิน</button></div>';
                    }
                ?>
            <?php } else { ?>
                <p>คุณยังไม่มีการจองหอพัก</p>
            <?php } ?>
        </div>
    </div>

    <?php 
        if ($booking_count['count'] > 0) {
            // found in bookings - hide 
            echo '<style>.building-container { display: none; }</style>';
        }
    ?>
    <div class="building-container">
        <div class="title-bar">
            <img src="https://sv1.picz.in.th/images/2023/10/27/ddsURPk.png" alt="Air Conditioner">
            <h2 class="blue">Air Conditioner</h2>
        </div>
        <div class="air-box">
            <?php foreach ($data as $row){
                if ($row['dorm_type'] == 'Air'){
                    if ($gender === 'M' && $row['dorm_gen'] === 'ชาย'||$gender === 'F' && $row['dorm_gen'] === 'หญิง') {
                    echo'<div class="dorm-card">
                            <div class="dormitory-bg-air">
                                <h5 class="dormitory-no">
                                    ตึก<br> '. htmlspecialchars($row['dorm_id']) .'
                                </h5>
                            </div>
                            <div class="dorm-info">
                                <p class="dormname-title">
                                    '. htmlspecialchars($row['dorm_name']) .'
                                    <br><input type="submit" value="เลือก ➥" class="btn-dorm" data-dorm-id="' . htmlspecialchars($row['dorm_id']) . '"data-dorm-name="' . htmlspecialchars($row['dorm_name']) . '">
                                </p>
                            </div> 
                        </div>';}
                    }
                } 
            ?>    
        </div>
        
    </div>
    <div class="building-container">
        <div class="title-bar">
            <img src="https://sv1.picz.in.th/images/2023/10/27/ddsU5aV.png" alt="Stardard">
            <h2 class="gray">Standard</h2>
        </div>
        <div class="standard-box">
            <?php foreach ($data as $row){
                    if ($row['dorm_type'] == 'Standard'){
                        if ($gender === 'M' && $row['dorm_gen'] === 'ชาย'||$gender === 'F' && $row['dorm_gen'] === 'หญิง'){
                        echo'<div class="dorm-card">
                            <div class="dormitory-bg-standard">
                                <h5 class="dormitory-no">
                                    ตึกที่<br> '. htmlspecialchars($row['dorm_id']) .'
                                </h5>
                            </div>
                            <div class="dorm-info">
                                <p class="dormname-title">
                                    '. htmlspecialchars($row['dorm_name']) .'
                                    <br><input type="submit" value="เลือก ➥" class="btn-dorm" data-dorm-id="' . htmlspecialchars($row['dorm_id']) . ' "data-dorm-name="' . htmlspecialchars($row['dorm_name']) . '" >
                                </p>
                            </div>
                        </div>';}
                    }
                } 
            ?> 
        </div>
    </div>

    <div id="roomListModal" class="modal">
        <div class="modal-content">
            <p class="DormTitle">เลขตึก</p><span class="close">&times;</span>
            <h2 id="selectedDormNo"></h2>
            <p id="selectedDormName"></p>
            <div class="roomListTab" id="roomList"></div>
        </div>
    </div>

    <script type="text/javascript" src="./js/booking.js"></script>
    <script type="text/javascript" src="./js/script.js"></script>
    </body>
</html>
