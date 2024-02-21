<?php 
    session_start();
    require_once './config/db.php';
    if (!isset($_SESSION['user_login'])) {
        $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
        header('location: index.php');
		exit();
    }

	$userID = $_SESSION['user_login'];

	$query = "SELECT statuspay FROM bookings WHERE stdid = ? LIMIT 1";
	$stmt = $conn->prepare($query);
	$stmt->execute([$userID]);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	if (!$row || $row['statuspay'] !== 'confirmed') {
		$_SESSION['error'] = 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้ เนื่องจากไม่พบข้อมูลการจองในระบบหรือยังไม่ได้รับการยืนยันการชำระเงิน';
		header('location: user.php');
		exit();
	}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<link rel="shortcut icon" type="image/svg" href="./img/logo.png"/>
		<title>Maintenance Service</title>
		<link rel="stylesheet" type="text/css" href="./css/user.css">
		<link rel="stylesheet" type="text/css" href="./css/report.css">
		<link href="https://fonts.googleapis.com/css?family=Mitr:600&display=swap" rel="stylesheet">
		<script src="https://kit.fontawesome.com/a81368914c.js"></script>
		<meta name="viewport" content="width=device-width, initial-scale=1">
	</head>
	<body>
		<nav>
			<ul class="nav-list">
				<li class="nav-item">
					<a href="./user.php"><img src="./img/logo.png" alt="Logo"></a>
				</li>
				<li class="nav-item-a">
					<a href="./user.php">หน้าหลัก</a>
				</li>
				<li class="nav-item-a">
					<a href="./user-profile.php">โปรไฟล์</a>
				</li>
				<li class="nav-item-a">
					<a href="./user-booking.php">จองหอพักนิสิต</a>
				</li>
				<li class="nav-item-a active">
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
					<li class="mobile-nav-item">
						<a href="./user-booking.php">จองหอพักนิสิต</a>
					</li>
					<li class="mobile-nav-item active">
						<a href="./user-maintenance.php">แจ้งซ่อมบำรุง</a>
					</li>
					<li class="mobile-nav-item">
						<a href="./logout.php">ออกจากระบบ</a>
					</li>
				</ul>
			</div>
		</nav>
		<ul class="nav-maintenance">
			<li class="nav-item-mtn active">
				<a href="./user-maintenance.php" class="fas fa-clipboard"></a>
			</li>
			<li class="nav-item-mtn">
				<a href="./user-report.php" class="fas fa-wrench"></a>
			</li>
			<li class="nav-item-mtn">
				<a href="./user-history.php" class="fas fa-list"></a>
			</li>
		</ul><div class='container ma'>
		<h1 class='card-title'>📋รายการปัจจุบัน</h1></div>
		<div class="container-report-m">	
		
				<?php
				// Fetch and display the list of reports with "in progress" status for the user
				$query = "SELECT r.*,t.* FROM reports r 
				LEFT JOIN teams t ON r.team_id = t.team_id WHERE stdid = ? AND report_status = 'in progress' ORDER BY report_time DESC";
				$stmt = $conn->prepare($query);
				$stmt->execute([$userID]);
				if ($stmt->rowCount() > 0) {
					while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
						echo "<div class='container m'>";
						echo "<table class='now'>";
						echo "<tbody>";
						echo "<tr><th><b>รายละเอียด</b></th>";
						echo "<th><b>ผู้รับผิดชอบ</b></th>";
						echo "<th></th></tr>";
						echo "<tr><td>";
						echo "<li>รหัสการแจ้งซ่อมบำรุง: " . $row['report_id'] . "</li>";
						echo "<li>หมวดหมู่: "; if ($row['repair_type'] === 'general') {												
						echo "ซ่อมแซมทั่วไป"; } elseif ($row['repair_type'] === 'furniture') {
						echo "เฟอร์นิเจอร์"; }elseif ($row['repair_type'] === 'plumbing') {
						echo "ระบบประปา"; }elseif ($row['repair_type'] === 'electrical') {
						echo "ระบบไฟฟ้า"; }elseif ($row['repair_type'] === 'appliance') {
						echo "เครื่องใช้ไฟฟ้า </li>"; }
						echo "<li>สิ่งของที่ชำรุด: " . $row['repair_specific'] . "</li>";
						echo "<li>ลักษณะการชำรุด: " . $row['repair_obj'] . "</li>";
						echo "<li>อธิบายการชำรุด: " . $row['repair_desc'] . "</li>";
						echo "<li>ช่วงเวลาที่สะดวก: "; if ($row['repair_time'] === 'morning') {												
						echo "ช่วงเช้า (09:00-12:00น.)"; } elseif ($row['repair_time'] === 'afternoon') {
						echo "ช่วงบ่าย (13:00-16:00น.)"; }elseif ($row['repair_time'] === 'evening') {
						echo "ช่วงเย็น (18:00-19:00น.)"; }elseif ($row['repair_time'] === 'allday') {
						echo "ตลอดทั้งวัน</li>"; }
						$formattedCreatedAt = date('d/m/Y', strtotime($row['report_timestamp']));
						$formattedtimeAt = date('เวลา: H:i น.', strtotime($row['report_timestamp']));
						echo '<li>วันที่แจ้ง: '. $formattedCreatedAt . " เวลา: " . $formattedtimeAt .'</li>';
						echo "</td>";
						echo "<td>";
						echo "<li>รหัสช่างซ่อมบำรุง: " . $row['team_id'] . "</li>";
						echo "<li>เพศ: "; if ($row['gender'] === 'M') {												
						echo "ชาย"; } elseif ($row['gender'] === 'F') {
						echo "หญิง </li>"; }
						echo "<li>ช่างซ่อมบำรุง: " . $row['firstname'] . " " . $row['lastname'] . "</li>";
						echo "<li>ตำแหน่ง: " . $row['position'] . " ทีม: " . $row['grouptech'] . "</li>";
						echo "<li>อีเมล: " . $row['email'] . "</li>";
						echo "<li>เบอร์โทรติดต่อ: <a href='tel:".$row['tel']."'>" . $row['tel'] . "</a></li>";
						$formattedCreatedReport = date('d/m/Y', strtotime($row['report_time']));
						$formattedtimeReport = date('เวลา: H:i น.', strtotime($row['report_time']));
						echo '<li>ปรับปรุง: '. $formattedCreatedReport . " " . $formattedtimeReport .'</li>';
						echo "</td>";
						echo "<td>";
						if ($row['repair_img'] !== null) {
							echo '<img src="data:image/jpeg;base64,' . base64_encode($row['profile_img']) . '" />';
						} else {
							echo 'ไม่พบรูปภาพ';
						}
						echo "</td></tr>";
						echo "</tbody>";
						echo "</table>";
						echo "</div>";
					}
				}else {
					echo '<div class="reportnotfound"><b>( ยังไม่มีการดำเนินการใด ๆ สำหรับรายการของคุณ )</b></div>';
				}
				?>
	
	</div>
	<script type="text/javascript" src="./js/script.js"></script>
</body>
</html>