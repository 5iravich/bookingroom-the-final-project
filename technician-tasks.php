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
		$sql =	"SELECT r.*, u.*, b.*, d.*, rm.* FROM reports r
		INNER JOIN users u ON r.stdid = u.stdid
		LEFT JOIN bookings b ON r.stdid = b.stdid
		LEFT JOIN rooms rm ON b.room_id = rm.room_id
		LEFT JOIN dorms d ON rm.dorm_id = d.dorm_id
		WHERE r.team_id = :team_id AND r.report_status = 'wait'";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':team_id', $team_id, PDO::PARAM_INT);
		$stmt->execute();
	
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	} catch (PDOException $e) {
		echo "Error: " . $e->getMessage();
	}

	try {
		$sql2 =	"SELECT r.*, u.*, b.*, d.*, rm.* FROM reports r
		INNER JOIN users u ON r.stdid = u.stdid
		LEFT JOIN bookings b ON r.stdid = b.stdid
		LEFT JOIN rooms rm ON b.room_id = rm.room_id
		LEFT JOIN dorms d ON rm.dorm_id = d.dorm_id
		WHERE r.team_id = :team_id AND r.report_status = 'in progress'" ;
		$stmtprogress = $conn->prepare($sql2);
		$stmtprogress->bindParam(':team_id', $team_id, PDO::PARAM_INT);
		$stmtprogress->execute();
	
		$resultprogress = $stmtprogress->fetchAll(PDO::FETCH_ASSOC);
	
	} catch (PDOException $e) {
		echo "Error: " . $e->getMessage();
	}

	if (isset($_POST['update_status'])) {
		$newStatus = $_POST['update_status'];
		$reportId = $_POST['report_id'];
	
		try {
			$sql1 = "UPDATE reports SET report_status = 'in progress' WHERE report_id = :report_id";
			if ($newStatus === 'in progress') {
				$sql1 = "UPDATE reports SET report_status = 'in progress' WHERE report_id = :report_id";
			} elseif ($newStatus === 'archive-out') {
				$sql1 = "UPDATE reports SET team_id = NULL WHERE report_id = :report_id";
			}
			$stmtupdatestatus = $conn->prepare($sql1);
			$stmtupdatestatus->bindParam(':report_id', $reportId, PDO::PARAM_INT);
			$stmtupdatestatus->execute();

			header("Location: technician-tasks.php");
			exit();
		} catch (PDOException $e) {
			$_SESSION['error'] = 'เกิดข้อผิดพลาดในการดึงข้อมูลผู้ใช้';
			header("Location: technician-tasks.php");
			exit();
		}
	}

	if (isset($_POST['withdrawal'])) {
		$reportId = $_POST['report_id'];
		$itemID = $_POST['ItemID'];
		$withdrawalQuantity = $_POST['withdrawal_quantity'];

		$checkQuantityQuery = "SELECT ItemQuantity FROM materials WHERE ItemID = :item_id";
		$stmtCheckQuantity = $conn->prepare($checkQuantityQuery);
		$stmtCheckQuantity->bindParam(':item_id', $itemID, PDO::PARAM_INT);
		$stmtCheckQuantity->execute();
		$itemData = $stmtCheckQuantity->fetch(PDO::FETCH_ASSOC);
	
		if ($itemData['ItemQuantity'] < $withdrawalQuantity) {
			$_SESSION['warning'] = 'จำนวนไม่เพียงพอสำหรับการเบิก';
			header("Location: technician-tasks.php");
			exit();
		}
	
		$insertWithdrawalQuery = "INSERT INTO withdrawals (report_id, ItemID, withdrawal_quantity, team_id) VALUES (:report_id, :item_id, :withdrawal_quantity, :team_id)";
		$stmtInsertWithdrawal = $conn->prepare($insertWithdrawalQuery);
		$stmtInsertWithdrawal->bindParam(':report_id', $reportId, PDO::PARAM_INT);
		$stmtInsertWithdrawal->bindParam(':item_id', $itemID, PDO::PARAM_INT);
		$stmtInsertWithdrawal->bindParam(':withdrawal_quantity', $withdrawalQuantity, PDO::PARAM_INT);
		$stmtInsertWithdrawal->bindParam(':team_id', $team_id, PDO::PARAM_INT);
		$stmtInsertWithdrawal->execute();
	
		$updateMaterialsQuery = "UPDATE materials SET ItemQuantity = ItemQuantity - :quantity WHERE ItemID = :item_id";
		$stmtUpdateMaterials = $conn->prepare($updateMaterialsQuery);
		$stmtUpdateMaterials->bindParam(':quantity', $withdrawalQuantity, PDO::PARAM_INT);
		$stmtUpdateMaterials->bindParam(':item_id', $itemID, PDO::PARAM_INT);
		$stmtUpdateMaterials->execute();
		
		$_SESSION['success'] = 'ทำรายการเบิกสำเร็จเรียบร้อยแล้ว';
		header("Location: technician-tasks.php");
		exit();
	}

	if (isset($_POST['final_report_submit'])) {
		$reportType = $_POST['report_type'];
		if (isset($_FILES['final_report_image'])) {
			$reportId = $_POST['report_id'];

			if ($_FILES['final_report_image']['error'] === 0) {
				$imageData = file_get_contents($_FILES['final_report_image']['tmp_name']);
	
				try {
					$updateReportSql = "UPDATE reports SET report_img = :imageData, report_type = :reportType, report_status = 'succeed' WHERE report_id = :report_id";
					$stmtUpdateReport = $conn->prepare($updateReportSql);
					$stmtUpdateReport->bindParam(':imageData', $imageData, PDO::PARAM_LOB);
					$stmtUpdateReport->bindParam(':reportType', $reportType, PDO::PARAM_STR);
					$stmtUpdateReport->bindParam(':report_id', $reportId, PDO::PARAM_INT);
					$stmtUpdateReport->execute();
	
					$_SESSION['success'] = 'อัปโหลดรูปภาพเสร็จสิ้น';
					header("Location: technician-tasks.php");
					exit();
				} catch (PDOException $e) {
					$_SESSION['error'] = 'Error updating the final report: ' . $e->getMessage();
					header("Location: technician-tasks.php");
					exit();
				}
			} else {
				$_SESSION['error'] = 'Error uploading the image file.';
				header("Location: technician-tasks.php");
				exit();
			}
		} else {
			$_SESSION['error'] = 'ยังไม่ได้อัปโหลดไฟล์';
			header("Location: technician-tasks.php");
			exit();
		}
	}
	
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<link rel="shortcut icon" type="image/svg" href="https://sv1.picz.in.th/images/2023/10/27/ddsUZnt.png"/>
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <title>Tasks</title>
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
				<li class="nav-item-a active">
					<a href="./technician-tasks.php">งานของฉัน</a>
				</li>
				<li class="nav-item-a">
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
				<ul class="mobile-nav-list">
					<li class="mobile-nav-item">
						<a href="./technician.php">หนัาหลัก</a>
					</li>
					<li class="mobile-nav-item">
						<a href="./technician-profile.php">โปรไฟล์</a>
					</li>
					<li class="mobile-nav-item active">
						<a href="./technician-tasks.php">งานของฉัน</a>
					</li>
					<li class="mobile-nav-item">
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
		<section>
			<main>
				<ul class="box-info main">
					<div id="content" class="table-data">
						<div class="order main">
							<div class="head left">
								<h3>📋งานของฉัน</h3>
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
							<table id="mytask-table" class="display" style="width:100%">
								<thead>
									<tr>
										<th>วันที่/เวลาแจ้ง</th>
										<th>รายละเอียดผู้แจ้ง</th>
										<th>หมวดหมู่</th>
										<th>สิ่งของและลักษณะที่ชำรุด</th>
										<th>ตัวเลือก</th>
									</tr>
								</thead>
								<tbody>
									<?php
										foreach ($resultprogress as $row) {
											echo "<tr>";
											$formattedCreatedAt = date('d/m/Y', strtotime($row['report_timestamp']));
                                    	    $formattedtimeAt = date('เวลา: H:iน.', strtotime($row['report_timestamp']));
											echo "<td>" . $formattedCreatedAt . "<br>" . $formattedtimeAt . "</td>"; 
											echo "<td>" . $row['firstname'] . " " . $row['lastname'] . "<br>ตึก: " . $row['dorm_id'] . " ห้อง: " . $row['room_number'] . "<br>เบอร์ติดต่อ: <a href='tel:".$row['tel']."'>".$row['tel']."</td>";
											echo "<td><p>"; if ($row['repair_type'] === 'general') {												
											echo "ซ่อมแซมทั่วไป"; } elseif ($row['repair_type'] === 'furniture') {
											echo "เฟอร์นิเจอร์"; }elseif ($row['repair_type'] === 'plumbing') {
											echo "ระบบประปา"; }elseif ($row['repair_type'] === 'electrical') {
											echo "ระบบไฟฟ้า"; }elseif ($row['repair_type'] === 'appliance') {
											echo "เครื่องใช้ไฟฟ้า </p></td>"; }
											echo "<td>" . $row['repair_specific'] . "<br>" . $row['repair_desc'] . "<br>จำนวน: " . $row['repair_quan'] . "<br>";
											
											if ($row['repair_img'] !== null) {
												echo '<br><img src="data:image/jpeg;base64,' . base64_encode($row['repair_img']) . '" width="100" onclick="showImagePopup(\'' . base64_encode($row['repair_img']) . '\')" style="cursor: pointer;" /><br>';
											  } else {
												echo '<br>ไม่พบรูปภาพ<br>';
											  }
											echo "ช่วงเวลาที่สะดวก: <br><p>"; if ($row['repair_time'] === 'morning') {												
											echo "ช่วงเช้า (09:00-12:00น.)"; } elseif ($row['repair_time'] === 'afternoon') {
											echo "ช่วงบ่าย (13:00-16:00น.)"; }elseif ($row['repair_time'] === 'evening') {
											echo "ช่วงเย็น (18:00-19:00น.)"; }elseif ($row['repair_time'] === 'allday') {
											echo "ตลอดทั้งวัน"; }
											echo "</p></td>";
											echo "<td>";
											if ($row['report_status'] === "in progress") {
												echo "<form class='archiveform' method='POST' action=''>"; 
												echo "<input type='hidden' name='report_id' value='" . $row['report_id'] . "'>"; 
												echo "<button type='button' id='withdrawal' name='withdrawal' value='withdrawal' onclick=\"openWithdrawalModal(".$row['report_id'].")\">
													<i class='bx bxs-report'></i>ทำรายการเบิก</button>";
												echo "<button type='button' id='finalreport' name='finalreport' value='succeed' onclick=\"openFinalReportModal(".$row['report_id'].")\">
													<i class='bx bx-task'></i>รายงานผล</button>";
												echo "</form>";	
											} 
											echo "</td>";
											echo "</tr>";
										}
									?>
								</tbody>
							</table>
						</div>
					</div>
					<div id="content" class="table-data">
						<div class="order main">
							<div class="head right">
								<h3>📇งานที่ได้รับมอบหมาย</h3>
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
							<table id="assignme-table" class="display" style="width:100%">
								<thead>
									<tr>
										<th>วันที่/เวลาแจ้ง</th>
										<th>รายละเอียดผู้แจ้ง</th>
										<th>หมวดหมู่</th>
										<th>สิ่งของและลักษณะที่ชำรุด</th>
										<th>ตัวเลือก</th>
									</tr>
								</thead>
								<tbody>
									<?php
										foreach ($result as $row) {
											echo "<tr>";
											$formattedCreatedAt = date('d/m/Y', strtotime($row['report_timestamp']));
                                    	    $formattedtimeAt = date('เวลา: H:iน.', strtotime($row['report_timestamp']));
											echo "<td>" . $formattedCreatedAt . "<br>" . $formattedtimeAt . "</td>"; 
											echo "<td>" . $row['firstname'] . " " . $row['lastname'] . "<br>ตึก: " . $row['dorm_id'] . " ห้อง: " . $row['room_number'] . "<br>เบอร์ติดต่อ: <a href='tel:".$row['tel']."'>".$row['tel']."</td>";
											echo "<td><p>"; if ($row['repair_type'] === 'general') {												
											echo "ซ่อมแซมทั่วไป"; } elseif ($row['repair_type'] === 'furniture') {
											echo "เฟอร์นิเจอร์"; }elseif ($row['repair_type'] === 'plumbing') {
											echo "ระบบประปา"; }elseif ($row['repair_type'] === 'electrical') {
											echo "ระบบไฟฟ้า"; }elseif ($row['repair_type'] === 'appliance') {
											echo "เครื่องใช้ไฟฟ้า </p></td>"; }
											echo "<td>สิ่งของ: " . $row['repair_specific'] . "<br>ลักษณะ: " . $row['repair_desc'] . "<br>จำนวน: " . $row['repair_quan'] . "<br>";
											if ($row['repair_img'] !== null) {
												echo '<br><img src="data:image/jpeg;base64,' . base64_encode($row['repair_img']) . '" width="100" onclick="showImagePopup(\'' . base64_encode($row['repair_img']) . '\')" style="cursor: pointer;" /><br>';
											  } else {
												echo '<br>ไม่พบรูปภาพ<br>';
											  }
											echo "ช่วงเวลาที่สะดวก: <br><p>"; if ($row['repair_time'] === 'morning') {												
											echo "ช่วงเช้า (09:00-12:00น.)"; } elseif ($row['repair_time'] === 'afternoon') {
											echo "ช่วงบ่าย (13:00-16:00น.)"; }elseif ($row['repair_time'] === 'evening') {
											echo "ช่วงเย็น (18:00-19:00น.)"; }elseif ($row['repair_time'] === 'allday') {
											echo "ตลอดทั้งวัน"; }
											echo "</p></td>";
											echo "<td>";
											if ($row['report_status'] === "wait") {
												echo "<form class='archiveform' method='POST' action=''>"; 
												echo "<input type='hidden' name='report_id' value='" . $row['report_id'] . "'>"; 
												echo "<button type='submit' id='archive-in' name='update_status' value='in progress'><i class='bx bxs-archive-in'> </i> รับงาน</button>";
												echo "<button type='submit' id='archive-out' name='update_status' value='archive-out'><i class='bx bxs-archive-out'> </i> ปฏิเสธงาน</button>";
												echo "</form>";	
											}
											echo "</td>";
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
		<div id="imagePopup" class="popup">
			<div class="popup-content">
				<span class="close-popup" onclick="closePopup()">&times;</span>
				<img id="popupImage" src="" alt="Image">
			</div>
		</div>
		<div id="withdrawalModal" class="withdrawalmodal">
			<div class="modal-content">
				<span class="close" onclick="closeWithdrawalModal()">&times;</span>
				<h2><i class='bx bxs-report'></i> เบิกวัสดุอุปกรณ์</h2>
				<form method="post" action="" enctype="multipart/form-data">
					<table class="form-container">
						<tr>
							<td class="form-group">
								<label for="ItemID ">วัสดุอุปกรณ์:</label><br>
								<select name="ItemID" id="ItemID" onchange="fetchItemQuantity()" required>
									<option value="" hidden>เลือกวัสดุอุปกรณ์</option>
									<?php
									// Retrieve materials from the materials table
									$materialsQuery = "SELECT * FROM materials";
									$materialsResult = $conn->query($materialsQuery);

									while ($material = $materialsResult->fetch(PDO::FETCH_ASSOC)) {
										echo "<option value='" . $material['ItemID'] . "'>" . $material['ItemName'] . "</option>";
									}
									?>
								</select>
							</td>
							<td class="form-group">
								<p id="ItemQuantity"></p>
							</td>
						</tr>
						<tr>
							<td class="form-group">
								<label for="withdrawal_quantity">จำนวน:</label><br>
								<input type="number" name="withdrawal_quantity" id="withdrawal_quantity" min="1"  required>
							</td>
							<td>
								<p id="ItemQuantity"></p>
							</td>
						</tr>
					</table>
					<div class="model-btn">
						<input type="hidden" name="report_id" id="modal_withdrawal_id">
						<button class="btnw" type="submit" id="withdrawalSubmit" name="withdrawal" >ยืนยัน</button>
					</div>
				</form>
			</div>
		</div>
		<div id="finalReportModal" class="modal">
			<div class="modal-content final">
				<span class="close" onclick="closeFinalReportModal()">&times;</span>
				<h2>รายงานผลการปฎิบัติงาน</h2>
				<form method="post" action="" enctype="multipart/form-data">
				<div class="radio-group">
					<td>
						<label for="report_type">เลือกประเภทการซ่อม:</label>
						<input type="radio" name="report_type" id="report_type_repair" value="repair" required>
						<label for="report_type_repair">ซ่อมแซม</label>
						<input type="radio" name="report_type" id="report_type_replace" value="replace" required>
						<label for="report_type_replace">เปลี่ยนใหม่</label>
					</td>
				</div>	
					
					<span class="report-part">แนบรูปภาพผลการซ่อมบำรุง</span>
					<div class="drag-area" id="dragArea">
						<div id="beforeLoad">
							<div class="iconpic">
								<i class="fas fa-images"></i>
							</div>
							<span class="headerpayment" id='dragText'> ลากและวางไฟล์ </span>
							<span class="headerpayment">หรือ <span class="buttonpayment" id="chooseFile">เลือกไฟล์</span></span> 
							<input class="filepayment" type="file" name="final_report_image" id="imageUpload" accept="image/jpeg, image/jpg, image/png" required/>
							<span class="supportpayment">รองรับสกุลไฟล์: JPEG, JPG, PNG</span>
						</div>
					</div>
					<div class="model-btn">
							<input type="hidden" name="report_id" id="modal_finalreport_id">
							<button class="btnw" type="submit" id="FinalReportSubmit" name="final_report_submit">ยืนยัน</button>
					</div>
				</form>
			</div>
		</div>
		<script>
			const withdrawalModal = document.getElementById('withdrawalModal');
			withdrawalModal.addEventListener('click', function (event) {
				if (event.target === withdrawalModal) {
					closeWithdrawalModal();
				}
			});
			function openWithdrawalModal(report_id) {
				const modal = document.getElementById('withdrawalModal');
				const reportIdInput = document.getElementById('modal_withdrawal_id');
				reportIdInput.value = report_id;
				modal.style.display = 'block';
			}

			function closeWithdrawalModal() {
				const modal = document.getElementById('withdrawalModal');
				modal.style.display = 'none';
			}

			// ----------------------------------------------------------------------

			const finalReportModal = document.getElementById('finalReportModal');
			finalReportModal.addEventListener('click', function (event) {
				if (event.target === finalReportModal) {
					closeFinalReportModal();
				}
			});

			function openFinalReportModal(report_id) {
				const modal = document.getElementById('finalReportModal');
				const reportIdInput = document.getElementById('modal_finalreport_id');
				reportIdInput.value = report_id;
				modal.style.display = 'block';
			}

			function closeFinalReportModal() {
				const modal = document.getElementById('finalReportModal');
				modal.style.display = 'none';
			}
		</script>
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
		<script type="text/javascript" src="./js/draganddrop.js"></script>
		<script type="text/javascript" src="./js/materials.js"></script>
		<script type="text/javascript" src="./js/script.js"></script>
		<!-- dataTable -->
		<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
		<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
		<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
		<script	ipt src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
		<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
		<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
		<!-- ตารางไทย -->
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
				$('#mytask-table').DataTable();
				$('#assignme-table').DataTable();
			});
		</script>
	</body>
</html>