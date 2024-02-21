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
        $cmquery = "SELECT COUNT(*) as material_count FROM materials";
        $cmstmt = $conn->prepare($cmquery);
        $cmstmt->execute();
        $row = $cmstmt->fetch(PDO::FETCH_ASSOC);
        $materialCount = $row['material_count'];

		$tmquery = "SELECT SUM(ItemQuantity) as total_quantity FROM materials";
        $tmstmt = $conn->prepare($tmquery);
        $tmstmt->execute();
        $row = $tmstmt->fetch(PDO::FETCH_ASSOC);
        $materialTotal = $row['total_quantity'];

    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }

	try {
        $cwquery = "SELECT COUNT(*) as withdrawals_count FROM withdrawals";
        $cwstmt = $conn->prepare($cwquery);
        $cwstmt->execute();
        $row = $cwstmt->fetch(PDO::FETCH_ASSOC);
        $withdrawalsCounts = $row['withdrawals_count'];

    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }

	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		try {
			$ItemID = $_POST['ItemID'];
			$ItemQuantity = $_POST['ItemQuantity'];

			$selectSql = "SELECT ItemQuantity FROM materials WHERE ItemID = :ItemID";
			$stmt = $conn->prepare($selectSql);
			$stmt->bindParam(':ItemID', $ItemID);
			$stmt->execute();

			$currentItemQuantity = $stmt->fetchColumn();

			if (isset($_POST['additem'])) {
				$newQuantity = $currentItemQuantity + $ItemQuantity;
				if ($newQuantity < 0) {
					$_SESSION['error'] = 'จำนวนไม่สามารถเป็นค่าลบ';
					header("Location: admin-storage.php");
					exit();
				}
			} elseif (isset($_POST['delitem'])) {
				$newQuantity = $currentItemQuantity - $ItemQuantity;
				if ($newQuantity < 0) {
					$_SESSION['error'] = 'จำนวนไม่สามารถเป็นค่าลบ';
					header("Location: admin-storage.php");
					exit();
				}
			}

			$updateSql = "UPDATE materials SET ItemQuantity = :newQuantity  WHERE ItemID = :ItemID";
			$newstmt = $conn->prepare($updateSql);
			$newstmt->bindParam(':ItemID', $ItemID);
			$newstmt->bindParam(':newQuantity', $newQuantity);
			$newstmt->execute();
			header("Location: admin-storage.php");
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
		<title>Storage</title>
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
            <li><a href="./admin-reports.php"><i class='bx bx-list-ul' ></i><span class="text">การแจ้งซ่อมบำรุง</span></a></li>
			<li><a href="./admin-dorms-rooms.php"><i class='bx bxs-building' ></i><span class="text">อาคารและห้องพัก</span></a></li>
            <li class="active"><a href="./admin-storage.php"><i class='bx bxs-cylinder' ></i><span class="text">คลังวัสดุ/อุปกรณ์</span></a></li>
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
				$profile_img_src = 'data:image/jpeg;base64,' . $profile_img_data;
				echo '<img src="' . $profile_img_src . '" alt="profile">';
				?>
			</a>
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>คลังวัสดุ/อุปกรณ์</h1>
					<ul class="breadcrumb">
						<li>
							<a class="active" href="./admin.php">Dashboard</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a  href="./admin-add-team.php">คลังวัสดุ/อุปกรณ์</a>
						</li>
					</ul>
				</div>
				<?php
    				if (isset($_SESSION['admin_login'])) {
				?>
				<a href="javascript:void(0);" id="openModalBtn" class="btn-storage">
					<i class='bx bxs-plus-square' ></i>
					<span class="text">เพิ่มวัสดุใหม่ในคลัง</span>
				</a>
				<?php
					}
				?>
			</div>

			<ul class="box-info">
                <li>
					<i class='bx bxs-calendar-check' ></i>
					<span class="text">
						<h3><?php echo $withdrawalsCounts; ?> รายการ</h3>
						<p>การเบิกวัสดุอุปกรณ์</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-group' ></i>
					<span class="text">
						<h3><?php echo $materialTotal; ?> (จำนวน)</h3>
						<p>จำนวนวัสดุทั้งหมด</p>
					</span>
				</li>
                <li>
					<i class='bx bxs-group' ></i>
					<span class="text">
						<h3><?php echo $materialCount; ?> รายการ</h3>
						<p>รายการวัสดุทั้งหมด</p>
					</span>
				</li>
			</ul>


			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>📇 รายการการเบิกวัสดุ</h3>
					</div>
					<table id="pick-table" class="display" style="width:100%">
						<thead>
							<tr>
								<th>วันที่/เวลา</th>
                                <th>รายการการเบิกวัสดุ</th>
                                <th>จำนวน</th>
								<th>สถานที่</th>
                                <th>โดย</th>
							</tr>
						</thead>
						<tbody>
                            <?php
                                try {
                                    $sql = "SELECT w.*,m.*,t.*,d.*,rm.* FROM withdrawals w
									JOIN materials m ON w.ItemID = m.ItemID
            						JOIN teams t ON w.team_id = t.team_id
									JOIN reports r ON w.report_id = r.report_id
									JOIN bookings b ON r.stdid = b.stdid
     							    JOIN rooms rm ON b.room_id = rm.room_id
									JOIN dorms d ON rm.dorm_id = d.dorm_id";
                                    $wstmt = $conn->query($sql);

                                    while ($row = $wstmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
										$formattedCreatedAt = date('d/m/Y เวลา H:i น.', strtotime($row['withdrawal_timestamp']));
                                        echo "<td>" . $formattedCreatedAt . "</td>";
                                        echo "<td>"."<p>" . $row['ItemName'] . "</p>" . "</td>";
										echo "<td>"."<p>" . $row['withdrawal_quantity'] . " " . $row['ItemUnit'] . "</p>" . "</td>";
										echo "<td><p>ตึก: " . $row['dorm_id'] . " ห้อง: " . $row['room_number'] . "</p></td>";
                                        echo "<td>";
                                        echo "<p>" . $row['firstname']." ". $row['lastname'] . "</p>";
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
						<h3>🗄️รายการวัสดุในคลัง</h3>
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
					<table id="store-table" class="display" style="width:100%">
						<thead>
							<tr>
                                <th>ชื่อรายการ</th>
                                <th>หมวดหมู่</th>
                                <th>จำนวน</th>
								<th>เพิ่มล่าสุด</th>
								<th>เพิ่มจำนวน</th>
							</tr>
						</thead>
						<tbody>
                            <?php
                                try {
                                    $sql = "SELECT * FROM materials";
                                    $stmt = $conn->query($sql);

                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
                                        echo "<td><p>" . $row['ItemName'] . "</p></td>";
										echo "<td><p>" . $row['ItemCategory'] . "</p></td>";
                                        echo "<td><p>" . $row['ItemQuantity']." ". $row['ItemUnit'] . "</p></td>";
										$formattedItemUpdated = date('d/m/Y', strtotime($row['ItemUpdated']));
                                        echo "<td><p>" . $formattedItemUpdated . "</p></td>";
										echo "<td>";
										if (isset($_SESSION['admin_login'])) {
											echo "<form class='Qua' action='' method='POST'>";
											echo "<input type='number' name='ItemID' value='" . $row['ItemID'] . "' style='display:none;'>";
											echo "<input type='number' name='ItemQuantity' id='ItemQuantity' min='1' required>";
											echo "<button type='submit' id='additem' name='additem' class='add-item-button'>";
											echo "<i class='bx bx-plus' ></i>";
											echo "</button>";
											echo "<button type='submit' id='delitem' name='delitem' class='add-item-button'>";
											echo "<i class='bx bx-minus' ></i>";
											echo "</button></form>";
										}else{
											echo"<i id='add' class='bx bx-x-circle'></i>";
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
		<div id="myModal" class="modal">
  			<div class="modal-content mat">
    			<span class="close">&times;</span>
				<h2><i class='bx bxs-plus-square'></i>เพิ่มวัสดุใหม่ในคลัง</h2>
    			<form action="admin-storage_db.php" method="post" enctype="multipart/form-data">
						<table class="form-container">
							<tr>
								<td class="form-group">
									<label for="ItemName">วัสดุ/อุปกรณ์:</label>
								</td>
								<td class="form-group">
									<input type="text" name="ItemName" id="ItemName" required>
								</td>
								<td class="form-group">
									<label for="ItemCategory">หมวดหมู่:</label>
								</td>
								<td class="form-group">
									<select name="ItemCategory" id="ItemCategory" >
										<option hidden>เลือกหมวดหมู่</option>
										<option value="ทั่วไป">งานซ่อมแซมทั่วไป</option>
										<option value="เฟอร์นิเจอร์">งานเฟอร์นิเจอร์</option>
										<option value="ประปา">งานประปา</option>
										<option value="ไฟฟ้า">งานระบบไฟฟ้า</option>
										<option value="เครื่องใช้ไฟฟ้า">งานเครื่องใช้ไฟฟ้า</option>
										<option value="เครื่องมือ">เครื่องมือ</option>
									</select>
								</td>

							</tr>
							<tr>
								<td class="form-group">
									<label for="ItemQuantity">จำนวน:</label>
								</td>
								<td class="form-group">
									<input type="number" name="ItemQuantity" id="ItemQuantity" min="1" required>
								</td>
								<td class="form-group">
									<label for="ItemUnit">หน่วยนับ:</label>
								</td>
								<td class="form-group">
									<select name="ItemUnit" id="ItemUnit" >
										<option hidden>เลือกหน่วยนับ</option>
										<option value="ลูก">ลูก</option>
										<option value="ดอก">ดอก</option>
										<option value="หลอด">หลอด</option>
										<option value="ชุด">ชุด</option>
										<option value="ตัว">ตัว</option>
										<option value="คู่">คู่</option>
										<option value="ขา">ขา</option>
										<option value="อัน">อัน</option>
										<option value="ม้วน">ม้วน</option>
										<option value="ถุง">ถุง</option>
										<option value="เส้น">เส้น</option>
										<option value="กล่อง">กล่อง</option>
										<option value="แผ่น">แผ่น</option>
										<option value="ชิ้น">ชิ้น</option>
										<option value="เล่ม">เล่ม</option>
										<option value="บาน">บาน</option>
										<option value="ใบ">ใบ</option>
									</select>
								</td>
							</tr>
						</table>
						<div class="model-btn">
							<input class="btntech" name="addmat" type="submit" value="เพิ่มวัสดุอุปกรณ์"></input>
						</div>
				</form>
  			</div>
		</div>

	</section>
    <script type="text/javascript" src="./js/admin.js"></script>
	<script>
		var modal = document.getElementById("myModal");
		var openModalBtn = document.getElementById("openModalBtn");
		var closeButton = document.getElementsByClassName("close")[0];

		openModalBtn.onclick = function() {
		modal.style.display = "block";
		};

		closeButton.onclick = function() {
		modal.style.display = "none";
		};

		window.onclick = function(event) {
		if (event.target == modal) {
			modal.style.display = "none";
		}
		};
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
			$('#store-table').DataTable();
			$('#pick-table').DataTable({
				dom: 'Blfrtip',
				buttons: [
            		'pdf'
        		]
			});
		});
	</script>
</body>
</html>

