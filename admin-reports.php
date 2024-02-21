<?php 
    session_start();
    require_once './config/db.php';
    if (!isset($_SESSION['admin_login'])&&!isset($_SESSION['coadmin_login'])&&isset($_SESSION['user_login'])) {
        $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
        header('location: index.php');
        exit();
    }

	$team_id = $_SESSION['admin_login']?? $_SESSION['coadmin_login'];

	try {
		$team_data = $conn->prepare("SELECT * FROM teams WHERE team_id = :team_id");
		$team_data->bindParam(":team_id", $team_id);
		$team_data->execute();
		$team = $team_data->fetch(PDO::FETCH_ASSOC);
	} catch (PDOException $e) {
		$_SESSION['error'] = 'เกิดข้อผิดพลาดในการดึงข้อมูลผู้ใช้';
    	header('location: admin-profile.php');
    	exit();
	}

    try {
        $crquery = "SELECT COUNT(*) as report_count FROM reports";
        $crstmt = $conn->prepare($crquery);
        $crstmt->execute();
        $row = $crstmt->fetch(PDO::FETCH_ASSOC);
        $reportCount = $row['report_count'];

		$waitReportsQuery = "SELECT COUNT(*) as wait_report_count FROM reports WHERE report_status = 'wait'";
    	$waitReportsStmt = $conn->prepare($waitReportsQuery);
		$waitReportsStmt->execute();
		$waitReportRow = $waitReportsStmt->fetch(PDO::FETCH_ASSOC);
		$waitReportCount = $waitReportRow['wait_report_count'];

		$progressReportsQuery = "SELECT COUNT(*) as progress_report_count FROM reports WHERE report_status = 'in progress'";
    	$progressReportsStmt = $conn->prepare($progressReportsQuery);
		$progressReportsStmt->execute();
		$progressReportRow = $progressReportsStmt->fetch(PDO::FETCH_ASSOC);
		$progressReportCount = $progressReportRow['progress_report_count'];

		$succeedReportsQuery = "SELECT COUNT(*) as succeed_report_count FROM reports WHERE report_status = 'succeed'";
    	$succeedReportsStmt = $conn->prepare($succeedReportsQuery);
		$succeedReportsStmt->execute();
		$succeedReportRow = $succeedReportsStmt->fetch(PDO::FETCH_ASSOC);
		$succeedReportCount = $succeedReportRow['succeed_report_count'];

    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }

	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assigned'])) {
		$reportId = $_POST['report_id'];
		$assignedTechnician = $_POST['technician'];
		try {
			$updateQuery = "UPDATE reports SET team_id = :team_id WHERE report_id = :report_id";
			$updateStatement = $conn->prepare($updateQuery);
			$updateStatement->bindParam(':team_id', $assignedTechnician);
			$updateStatement->bindParam(':report_id', $reportId);
			$updateStatement->execute();
			header("Location: admin-reports.php");
			exit();
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
		}
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
	<link rel="shortcut icon" type="image/svg" href="./img/logo.png"/>
	<title>Maintenance</title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" type="text/css" href="./css/admin.css">
	<link rel="stylesheet" type="text/css" href="./css/table.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js">
	<link href="https://fonts.googleapis.com/css?family=Mitr:600&display=swap" rel="stylesheet">
	<script src="https://kit.fontawesome.com/a81368914c.js"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
	<section class="sidebar" id="sidebar">
		<a href="./admin.php" class="brand">
            <img src="./img/logo.png" width="25px" alt="Logo">
			<span class="text">DORMITORY</span>
		</a>
		<ul class="side-menu top">
			<li><a href="./admin.php"><i class='bx bxs-dashboard' ></i><span class="text">Dashboard</span></a></li>
            <li><a href="./admin-bookings.php"><i class='bx bx-list-ul' ></i><span class="text">การจองห้องพัก</span></a></li>
            <li class="active"><a href="./admin-reports.php"><i class='bx bx-list-ul' ></i><span class="text">การแจ้งซ่อมบำรุง</span></a></li>
			<li><a href="./admin-dorms-rooms.php"><i class='bx bxs-building' ></i><span class="text">อาคารและห้องพัก</span></a></li>
            <li><a href="./admin-storage.php"><i class='bx bxs-cylinder' ></i><span class="text">คลังวัสดุ/อุปกรณ์</span></a></li>
			<li><a href="./admin-add-team.php"><i class='bx bxs-group' ></i><span class="text">ทีมงาน/ช่างซ่อมบำรุง</span></a></li>
			<li><a href="./admin-users.php"><i class='bx bxs-group' ></i><span class="text">จัดการสมาชิก</span></a></li>
		</ul>
		<ul class="side-menu bottom">
			<li><a href="./admin-profile.php"><i class='bx bxs-id-card' ></i><span class="text">ข้อมูลของฉัน</span></a></li>
			<li><a href="./logout.php" class="logout"><i class='bx bxs-log-out-circle' ></i><span class="text">ออกจากระบบ</span></a></li>
		</ul>
	</section>
	<!-- SIDEBAR -->


	<!-- CONTENT -->
	<section id="content">
		<!-- NAVBAR -->
		<nav>
			<i class='bx bx-menu' ></i>
			<a href="./admin-profile.php" class="profile">
				<?php
				$profile_img_data = base64_encode($team['profile_img']);
				$profile_img_src = 'data:image/jpeg;base64,' . $profile_img_data; // Change the MIME type accordingly if your images are of a different type
				echo '<img src="' . $profile_img_src . '" alt="profile">';
				?>
			</a>
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>การแจ้งซ่อมบำรุง</h1>
					<ul class="breadcrumb">
						<li>
							<a class="active" href="./admin.php">Dashboard</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a  href="./admin-reports.php">การแจ้งซ่อมบำรุง</a>
						</li>
					</ul>
				</div>
			</div>

			<ul class="box-info">
                <li class="report">
					<i class='bx bx-list-ul' ></i>
					<span class="text">
						<h3><?php echo $waitReportCount; ?> รายการ</h3>
						<p>รอการยืนยัน</p>
					</span>
				</li>
				<li class="report">
					<i class='bx bx-list-check'></i>
					<span class="text">
						<h3><?php echo $progressReportCount; ?> รายการ</h3>
						<p>ยืนยันแล้ว</p>
					</span>
				</li>
				<li class="report">
					<i class='bx bx-check'></i>
					<span class="text">
						<h3><?php echo $succeedReportCount; ?> รายการ</h3>
						<p>เสร็จสิ้นแล้ว</p>
					</span>
				</li>
				<li class="report">
                    <i class='bx bx-wrench'></i>
					<span class="text">
                        <h3><?php echo $reportCount; ?> รายการ</h3>
						<p>การแจ้งซ่อมบำรุงท้งหมด</p>
					</span>
				</li>
			</ul>


			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>📋 การมอบหมายงาน</h3>
					</div>
					<table id="as-table" class="display" style="width:100%">
						<thead>
							<tr>
								<th>วันที่<br>(เวลา)</th>
                                <th>รายละเอียดผู้แจ้ง</th>
								<th>รายละเอียดงาน</th>
								<th>มอบหมายงาน</th>
							</tr>
						</thead>
						<tbody>
                            <?php
                                try {
                                    $sql = "SELECT u.*, r.*, b.*,rm.*,d.*
                                    FROM users u
                                    JOIN reports r ON u.stdid = r.stdid
                                    JOIN bookings b ON u.stdid = b.stdid
                                    JOIN rooms rm ON b.room_id = rm.room_id
                                    JOIN dorms d ON rm.dorm_id = d.dorm_id";
                                    $stmt = $conn->query($sql);

                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
                                        $formattedReportDate = date('d/m/Y', strtotime($row['report_timestamp']));
										$formattedReportTime = date('H:iน.', strtotime($row['report_timestamp']));
                                        echo "<td>" . $formattedReportDate . "<br>(" . $formattedReportTime . ")</td>"; 
                                        echo "<td><p>".$row['firstname']." ".$row['lastname']."<br>ตึกที่ ".$row['dorm_id']." ห้อง ".$row['room_number']."<br>เบอร์โทร ".$row['tel']."</p></td>";
										echo "<td><p>"; if ($row['repair_type'] === 'general') {
										echo "ซ่อมแซมทั่วไป"; } elseif ($row['repair_type'] === 'furniture') {
										echo "เฟอร์นิเจอร์"; }elseif ($row['repair_type'] === 'plumbing') {
										echo "ระบบประปา"; }elseif ($row['repair_type'] === 'electrical') {
										echo "ระบบไฟฟ้า"; }elseif ($row['repair_type'] === 'appliance') {
										echo "เครื่องใช้ไฟฟ้า"; }
										echo "<br>" . $row['repair_specific'] . "<br>" . $row['repair_desc'] . "<br>จำนวน " . $row['repair_quan'] . "</p>";
										if ($row['repair_img'] !== null) {
											echo '<img src="data:image/jpeg;base64,' . base64_encode($row['repair_img']) . '" width="100" onclick="showImagePopup(\'' . base64_encode($row['repair_img']) . '\')" style="cursor: pointer;" />';
										  } else {
											echo 'ไม่พบรูปภาพ';
										  }
										echo "</td>";
                                        echo "<td>";
										echo "<form class='assignedform' action='' method='POST'>";
										echo "<input type='hidden' name='report_id' value='" . $row['report_id'] . "'>";
										echo "<select name='technician' id='technicianDropdown'>";
										echo "<option value='' hidden> เลือกช่าง </option>";
										try {
											$tech_query = "SELECT * FROM teams WHERE urole = 'technician'";
											$tech_stmt = $conn->query($tech_query);
											while ($tech_row = $tech_stmt->fetch(PDO::FETCH_ASSOC)) {
												$selected = ($tech_row['team_id'] === $row['team_id']) ? 'selected' : '';
												echo "<option value='" . $tech_row['team_id'] . "' $selected>" . $tech_row['firstname'] . " ".$tech_row['lastname']."</option>";
											}
										} catch (PDOException $e) {
											echo "Error: " . $e->getMessage();
										}
										echo "</select>";
										echo "<button type='submit' id='assigned' name='assigned' class='assigned-button'>";
										echo "<i class='bx bx-task'></i>";
										echo "</button></form>";
                                        echo "</td>";
										echo "</tr>";
										}
                                } catch (PDOException $e) {
                                    echo "Error: " . $e->getMessage();
                                }
                            ?>
						</tbody>
					</table>
				</div>
				<div class="order">
					<div class="head">
						<h3>🗃️ รายการแจ้งซ่อมบำรุง</h3>
					</div>
					<table id="re-table" class="display" style="width:100%">
						<thead>
							<tr>
                                <th>วันที่แจ้งซ่อม<br>(เวลา)</th>
                                <th>รายละเอียดผู้แจ้ง</th>
								<th>รายละเอียดงาน</th>
								<th>ผู้รับผิดชอบ</th>
								<th>เสร็จสิ้นเมื่อ</th>
								<th>แนบรูป</th>
								<th>สถานะของงาน</th>
							</tr>
						</thead>
						<tbody>
                            <?php
                                try {
                                    $sql = "SELECT u.*, r.*, b.*,rm.*,d.*, 
									t.firstname AS team_firstname, t.lastname AS team_lastname
                                    FROM users u
                                    JOIN reports r ON u.stdid = r.stdid
                                    JOIN bookings b ON u.stdid = b.stdid
                                    JOIN rooms rm ON b.room_id = rm.room_id
                                    JOIN dorms d ON rm.dorm_id = d.dorm_id
									LEFT JOIN teams t ON r.team_id = t.team_id";
                                    $stmt = $conn->query($sql);

                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
                                        $formattedReportDate = date('d/m/Y', strtotime($row['report_timestamp']));
										$formattedReportTime = date('H:iน.', strtotime($row['report_timestamp']));
                                        echo "<td>" . $formattedReportDate . "<br>(" . $formattedReportTime . ")</td>"; 
                                        echo "<td><p>".$row['firstname']." ".$row['lastname']."<br>ตึกที่ ".$row['dorm_id']." ห้อง ".$row['room_number']."<br>เบอร์โทร ".$row['tel']."</p></td>";
										echo "<td><p>"; if ($row['repair_type'] === 'general') {
										echo "ซ่อมแซมทั่วไป"; } elseif ($row['repair_type'] === 'furniture') {
										echo "เฟอร์นิเจอร์"; }elseif ($row['repair_type'] === 'plumbing') {
										echo "ระบบประปา"; }elseif ($row['repair_type'] === 'electrical') {
										echo "ระบบไฟฟ้า"; }elseif ($row['repair_type'] === 'appliance') {
										echo "เครื่องใช้ไฟฟ้า"; }
										echo "<br>" . $row['repair_specific'] . "<br>" . $row['repair_desc'] . "<br>จำนวน " . $row['repair_quan'] . "</p>";
										if ($row['repair_img'] !== null) {
											echo '<img src="data:image/jpeg;base64,' . base64_encode($row['repair_img']) . '" width="100" onclick="showImagePopup(\'' . base64_encode($row['repair_img']) . '\')" style="cursor: pointer;" />';
										  } else {
											echo 'ไม่พบรูปภาพ';
										  }
										echo "</td>";
										echo "<td><p>".$row['team_firstname']." ".$row['team_lastname']."</p></td>";
										$formattedfinalReportDate = date('d/m/Y', strtotime($row['report_time']));
										$formattedfinalReportTime = date('H:iน.', strtotime($row['report_time']));
                                        echo "<td>" . $formattedfinalReportDate . "<br>(" . $formattedfinalReportTime . ")</td>"; 
										echo "<td >";
										if ($row['report_img'] !== null) {
											if ($row['report_type'] === 'repair') {
											echo "ซ่อมแซม"; } elseif ($row['report_type'] === 'replace') {
											echo "เปลี่ยนใหม่"; }
											echo '<img src="data:image/jpeg;base64,' . base64_encode($row['report_img']) . '" width="100" onclick="showImagePopup(\'' . base64_encode($row['report_img']) . '\')" style="cursor: pointer;" />';
										  } else {
											echo 'ไม่พบรูปภาพ';
										  }
										echo "</td>";
										
                                        echo "<td>";
                                            if ($row['report_status'] === 'succeed') {
                                                echo "<span class='status succeed'>เสร็จสิ้น</span>";
                                            } elseif ($row['report_status'] === 'in progress') {
                                                echo "<span class='status in-progress'>ดำเนินการ</span>";
                                            } elseif ($row['report_status'] === 'wait') {
                                                echo "<span class='status wait'>รอยืนยัน</span>";
                                            } else {
                                                echo "<span class='status unknown'>Unknown</span>";
                                            }
                                        echo "</td>";
										echo "</tr>";
										}
                                } catch (PDOException $e) {
                                    echo "Error: " . $e->getMessage();
                                }
                            ?>
						</tbody>
					</table>
				</div>
			</div>
		</main>
	</section>
	<div id="imagePopup" class="popup">
		<div class="popup-content">
			<span class="close-popup" onclick="closePopup()">&times;</span>
			<img id="popupImage" src="" alt="Slip Image">
		</div>
	</div>
    <script type="text/javascript" src="./js/admin.js"></script>
	<script>
		const imagePopup = document.getElementById('imagePopup');
		imagePopup.addEventListener('click', function (event) {
		
			if (event.target === imagePopup) {
				closePopup();
			}
		});
		function closePopup() {
			imagePopup.style.display = 'none';
		}
	</script>
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
			$('#as-table').DataTable();
			$('#re-table').DataTable();
		});
	</script>
</body>
</html>

