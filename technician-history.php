<?php 
    session_start();
    require_once './config/db.php';
    if (!isset($_SESSION['technician_login'])) {
        $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
        header('location: index.php');
		exit();
    }

	$team_id = $_SESSION['technician_login'];

	try {
		$sql = "SELECT r.*, b.*, d.*,rm.*
		FROM reports r
		JOIN bookings b ON r.stdid = b.stdid
		JOIN rooms rm ON b.room_id = rm.room_id
		JOIN dorms d ON rm.dorm_id = d.dorm_id
		WHERE r.team_id = :team_id AND r.report_status = 'succeed'";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':team_id', $team_id, PDO::PARAM_STR);
		$stmt->execute();
	} catch (PDOException $e) {
		die("Database connection failed: " . $e->getMessage());
	}
	
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<link rel="shortcut icon" type="image/svg" href="https://sv1.picz.in.th/images/2023/10/27/ddsUZnt.png"/>
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <title>History</title>
	<link rel="stylesheet" type="text/css" href="./css/technician.css">
	<link rel="stylesheet" type="text/css" href="./css/table.css">
	<link href="https://fonts.googleapis.com/css?family=Mitr:600&display=swap" rel="stylesheet">
	<script src="https://kit.fontawesome.com/a81368914c.js"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
	<body>
		<nav>
			<ul class="nav-list">
				<li class="nav-item">
					<a href="./technician.php"><img src="https://sv1.picz.in.th/images/2023/10/27/ddsUZnt.png"></img></a>
				</li>
				<li class="nav-item-a">
					<a href="./technician.php">หนัาหลัก</a>
				</li>
				<li class="nav-item-a">
					<a href="./technician-profile.php">โปรไฟล์</a>
				</li>
				<li class="nav-item-a">
					<a href="./technician-tasks.php">งานของฉัน</a>
				</li>
				<li class="nav-item-a active">
					<a href="./technician-history.php">ประวัติงานซ่อมบำรุง</a>
				</li>
				<li class="nav-item-a">
					<a href="./technician-list-withdrawals.php">ประวัติการเบิกวัสดุอุปกรณ์</a>
				</li>
				<li class="nav-item-a">
					<a href="./technician-summary.php">สรุปภาพรวม</a>
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
				<!-- mobile -->
				<ul class="mobile-nav-list">
					<li class="mobile-nav-item">
						<a href="./technician.php">หนัาหลัก</a>
					</li>
					<li class="mobile-nav-item">
						<a href="./technician-profile.php">โปรไฟล์</a>
					</li>
					<li class="mobile-nav-item">
						<a href="./technician-tasks.php">งานของฉัน</a>
					</li>
					<li class="mobile-nav-item active">
						<a href="./technician-history.php">ประวัติงานซ่อมบำรุง</a>
					</li>
					<li class="mobile-nav-item">
						<a href="./technician-list-withdrawals.php">ประวัติการเบิกวัสดุอุปกรณ์</a>
					</li>
					<li class="mobile-nav-item">
						<a href="./technician-summary.php">สรุปภาพรวม</a>
					</li>
					<li class="mobile-nav-item">
						<a href="./logout.php">ออกจากระบบ</a>
					</li>
				</ul>
			</div>
		</nav>
	<main>
		<ul class="box-info main">
			<div id="history" class="table-data">
				<div class="order main">
					<div class="head">
						<h2>✅ประวัติงานซ่อมบำรุง</h2>
					</div>
					<table id="finish-table" class="display" style="width:100%">
						<thead class="history">
							<tr>
								<th>รหัสงาน</th>
								<th>หมวดหมู่</th>
								<th>สิ่งของที่ชำรุด</th>
								<th>ประเภทการซ่อม</th>
								<th>สถานที่</th>
								<th>เสร็จสิ้นเมื่อ</th>
							</tr>
						</thead>
						<tbody>
							<?php
							while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
								echo "<tr>";
								echo "<td>" . $row['report_id'] . "</td>";
								echo "<td>"; if ($row['repair_type'] === 'general') {
								echo "ซ่อมแซมทั่วไป"; } elseif ($row['repair_type'] === 'furniture') {
								echo "เฟอร์นิเจอร์"; }elseif ($row['repair_type'] === 'plumbing') {
								echo "ระบบประปา"; }elseif ($row['repair_type'] === 'electrical') {
								echo "ระบบไฟฟ้า"; }elseif ($row['repair_type'] === 'appliance') {
								echo "เครื่องใช้ไฟฟ้า</td>"; }
								echo "<td>" . $row['repair_specific'] . "</td>";
								echo "<td>"; if ($row['report_type'] === 'repair') {
								echo "ซ่อมแซม"; }elseif ($row['report_type'] === 'replace') {
								echo "เปลี่ยนใหม่</td>"; }
								echo "<td>ตึก: ".$row['dorm_id']." ห้อง: ".$row['room_number']."</td>";
								$formattedCreatedAt = date('d/m/Y เวลา H:i น.', strtotime($row['report_time']));
								echo "<td>" . $formattedCreatedAt . "</td>";
								echo "</tr>";
							}
							?>
						</tbody>
					</table>
    			</div>
			</div>
		</ul>
	</main>
		<script type="text/javascript" src="./js/script.js"></script>
		<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
		<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
		<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
		<script	ipt src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
		<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
		<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
		<script>
			$.extend(true, $.fn.dataTable.defaults, {
				"language": {
						"sProcessing": "กำลังดำเนินการ...",
						"sLengthMenu": "แสดง _MENU_ แถว",
						"sZeroRecords": "ไม่พบข้อมูล",
						"sInfo": "แสดง _START_ ถึง _END_ จาก _TOTAL_ แถว",
						"sInfoEmpty": "แสดง 0 ถึง 0 จาก 0 แถว",
						"sInfoFiltered": "(กรองข้อมูล _MAX_ ทุกแถว)",
						"sInfoPostFix": "",
						"sSearch": "ค้นหา:",
						"sUrl": "",
						"oPaginate": {
										"sFirst": "เิริ่มต้น",
										"sPrevious": "ก่อนหน้า",
										"sNext": "ถัดไป",
										"sLast": "สุดท้าย"
						}
				}
			});
			$(document).ready(function(){
				$('#finish-table').DataTable();
			});
		</script>
</body>
</html>
