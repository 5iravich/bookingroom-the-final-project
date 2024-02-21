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
        $sql = "SELECT w.*,m.*,r.*,d.*,rm.* FROM withdrawals w
        JOIN materials m ON w.ItemID = m.ItemID
        JOIN teams t ON w.team_id = t.team_id
        JOIN reports r ON w.report_id = r.report_id
        JOIN bookings b ON r.stdid = b.stdid
        JOIN rooms rm ON b.room_id = rm.room_id
        JOIN dorms d ON rm.dorm_id = d.dorm_id
        WHERE t.team_id = :team_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':team_id', $team_id);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<link rel="shortcut icon" type="image/svg" href="./img/logo.png"/>
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <title>Withdrawals</title>
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
					<a href="./technician.php"><img src="./img/logo.png"></img></a>
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
				<li class="nav-item-a">
					<a href="./technician-history.php">ประวัติงานซ่อมบำรุง</a>
				</li>
				<li class="nav-item-a active">
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
					<li class="mobile-nav-item">
						<a href="./technician-history.php">ประวัติงานซ่อมบำรุง</a>
					</li>
					<li class="mobile-nav-item active">
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
        <section>
			<main>
				<ul class="box-info main">
                    <div id="history" class="table-data">
						<div class="order main">
							<div class="head">
								<h3>📇 ประวัติการเบิกวัสดุอุปกรณ์</h3>
							</div>
							<table id="withdrawals-table" class="display" style="width:100%">
								<thead class="history">
									<tr>
										<th>วันที่/เวลาที่เบิก</th>
										<th>รายการที่เบิก</th>
										<th>จำนวน</th>
										<th>สถานที่</th>
									</tr>
								</thead>
								<tbody>
									<?php
										foreach ($result as $row) {
											echo "<tr>";
                                            $formattedCreatedAt = date('d/m/Y เวลา H:i น.', strtotime($row['withdrawal_timestamp']));
                                            echo "<td>" . $formattedCreatedAt . "</td>";
                                            echo "<td>"."<p>" . $row['ItemName'] . "</p>" . "</td>";
										    echo "<td>"."<p>" . $row['withdrawal_quantity'] . " " . $row['ItemUnit'] . "</p>" . "</td>";
											echo "<td><p>ตึก: " . $row['dorm_id'] . " ห้อง: " . $row['room_number'] . "</p></td>";
											echo "</tr>";
										}
									?>
								</tbody>
							</table>
						</div>
					</div>
				</ul>
			</main>
		</section>
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
				$('#withdrawals-table').DataTable();
			});
		</script>
	</body>
</html>