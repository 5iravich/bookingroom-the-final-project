<?php
	session_start();
	require_once './config/db.php';

	if (!isset($_SESSION['user_login'])) {
		$_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
		header('location: index.php');
		exit();
	}

	$userID = $_SESSION['user_login'];

	$query = "SELECT r.*, t.* FROM reports r 
	LEFT JOIN teams t ON r.team_id = t.team_id
 	WHERE stdid = ?";
	$stmt = $conn->prepare($query);
	$stmt->execute([$userID]);
	$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<link rel="shortcut icon" type="image/svg" href="./img/logo.png"/>
	<title>My History</title>
	<link rel="stylesheet" type="text/css" href="./css/user.css">
	<link rel="stylesheet" type="text/css" href="./css/table.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js">
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
			<li class="nav-item-mtn">
				<a href="./user-maintenance.php" class="fas fa-clipboard"></a>
			</li>
			<li class="nav-item-mtn">
				<a href="./user-report.php" class="fas fa-wrench"></a>
			</li>
			<li class="nav-item-mtn active">
				<a href="./user-history.php" class="fas fa-list"></a>
			</li>
		</ul>

		<h1 class="card-title history">⌚ประวัติการแจ้งซ่อมบำรุง</h1>

		<div class="container-history">
        <?php if (isset($_SESSION['error'])) { ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php } ?>

        <table id="history-table" class="styled-table">
            <thead >
                <tr>
                    <th>วันที่แจ้ง</th>
                    <th>ช่วงเวลาที่สะดวก</th>
                    <th>ประเภทของงานซ่อม</th>
                    <th>สิ่งของที่ชำรุด</th>
					<th>ลักษณะ</th>
					<th>ช่างผู้รับผิดชอบ</th>
                    <th>สถานะ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $record) { ?>
                    <tr style="<?php echo getRowStyle($record['report_status']); ?>" >
                        <td><?php echo $record['report_timestamp']; ?></td>
                        <td><?php echo translateRepairTime($record['repair_time']); ?></td>
                        <td><?php echo translateRepairType($record['repair_type']); ?></td>
						<td><?php echo $record['repair_specific']; ?></td>
                        <td><?php echo $record['repair_obj']; ?></td>
						<td><?php echo $record['firstname']." ".$record['lastname'] ;  ?></td>
                        <td><?php
							if ($record['report_status'] === 'succeed') {
								echo 'เสร็จสิ้น';
							} elseif ($record['report_status'] === 'in progress') {
								echo 'กำลังดำเนินการ';
							} elseif ($record['report_status'] === 'wait') {
								echo 'รอยืนยัน';
							} else {
								echo $record['report_status'];
							}
							?>
						</td>
                    </tr>
                <?php } ?>
				<?php
						function translateRepairTime($repairTime) {
							switch ($repairTime) {
								case 'morning':
									return 'ช่วงเช้า (09:00-12:00 น.)';
								case 'afternoon':
									return 'ช่วงบ่าย (13:00-16:00 น.)';
								case 'evening':
									return 'ช่วงเย็น (18:00-19:00 น.)';
								case 'allday':
									return 'ตลอดทั้งวัน';
								default:
									return $repairTime;
							}
						}
						function translateRepairType($repairType){
							switch ($repairType) {
								case 'general':
									return 'ซ่อมแซมทั่วไป';
								case 'furniture':
									return 'เฟอร์นิเจอร์';
								case 'plumbing':
									return 'ระบบประปา';
								case 'electrical':
									return 'ระบบไฟฟ้า';
								case 'appliance':
									return 'เครื่องใช้ไฟฟ้า';
								default:
									return $repairType;
							}
						}
						function getRowStyle($reportStatus) {
							switch ($reportStatus) {
								case 'succeed':
									return 'background-color: #57CC99;';
								case 'in progress':
									return 'background-color: #FFD966;';
								case 'wait':
									return 'background-color: #EEEEE;';
								default:
									return '';
							}
						}
					?>
            </tbody>
        </table>
    </div>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
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
			$('#history-table').DataTable();
		});
	</script>
	<script type="text/javascript" src="./js/script.js"></script>
</body>
</html>