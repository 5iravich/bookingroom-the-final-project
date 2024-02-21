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

	$teamquery = "SELECT COUNT(*) as team_count FROM teams";
    $teamstmt = $conn->prepare($teamquery);
    $teamstmt->execute();
    $row = $teamstmt->fetch(PDO::FETCH_ASSOC);
    $teamCount = $row['team_count'];

	$technicianquery = "SELECT COUNT(*) as technician_count FROM teams WHERE urole = 'technician'";
	$technicianstmt = $conn->prepare($technicianquery);
	$technicianstmt->execute();
	$row = $technicianstmt->fetch(PDO::FETCH_ASSOC);
	$technicianCount = $row['technician_count'];

	$coadminquery = "SELECT COUNT(*) as coadmin_count FROM teams WHERE urole = 'coadmin'";
	$coadminstmt = $conn->prepare($coadminquery);
	$coadminstmt->execute();
	$row = $coadminstmt->fetch(PDO::FETCH_ASSOC);
	$coadminCount = $row['coadmin_count'];

	$adminquery = "SELECT COUNT(*) as admin_count FROM teams WHERE urole = 'admin'";
	$adminstmt = $conn->prepare($adminquery);
	$adminstmt->execute();
	$row = $adminstmt->fetch(PDO::FETCH_ASSOC);
	$adminCount = $row['admin_count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
		<link rel="shortcut icon" type="image/svg" href="./img/logo.png"/>
		<title>Manage a team</title>
		<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
		<link rel="stylesheet" type="text/css" href="./css/admin.css">
		<link rel="stylesheet" type="text/css" href="./css/table.css">
		<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js">
		<link href="https://fonts.googleapis.com/css?family=Mitr:600&display=swap" rel="stylesheet">
		<script src="https://kit.fontawesome.com/a81368914c.js"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
	<!-- SIDEBAR -->
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
            <li><a href="./admin-storage.php"><i class='bx bxs-cylinder' ></i><span class="text">คลังวัสดุ/อุปกรณ์</span></a></li>
			<li class="active"><a href="./admin-add-team.php"><i class='bx bxs-group' ></i><span class="text">ทีมงาน/ช่างซ่อมบำรุง</span></a></li>
			<li><a href="./admin-users.php"><i class='bx bxs-group' ></i><span class="text">จัดการสมาชิก</span></a></li>
		</ul>
		<ul class="side-menu bottom">
			<li><a href="./admin-profile.php"><i class='bx bxs-id-card' ></i><span class="text">ข้อมูลของฉัน</span></a></li>
			<li><a href="./logout.php" class="logout"><i class='bx bxs-log-out-circle' ></i><span class="text">ออกจากระบบ</span></a></li>
		</ul>
	</section>

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
					<h1>ทีมงาน/ช่างซ่อมบำรุง</h1>
					<ul class="breadcrumb">
						<li>
							<a class="active" href="./admin.php">Dashboard</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a  href="./admin-add-team.php">ทีมงาน/ช่างซ่อมบำรุง</a>
						</li>
					</ul>
				</div>
				<?php
    				if (isset($_SESSION['admin_login'])) {
				?>
    				<a href="javascript:void(0);" id="openModalBtn" class="btn-add">
        				<i class='bx bx-user-plus'></i>
        				<span class="text">เพิ่มทีมงาน/พนักงาน</span>
    				</a>
				<?php
					}
				?>
			</div>

			<ul class="box-info">
				<li>
					<i class='bx bx-wrench' ></i>
					<span class="text">
						<h3><?php echo $technicianCount; ?> คน</h3>
						<p>ช่างซ่อมบำรุง</p>
					</span>
				</li>
                <li>
					<i class='bx bx-support' ></i>
					<span class="text">
						<h3><?php echo $coadminCount; ?> คน</h3>
						<p>พนักดูแลระบบ</p>
					</span>
				</li>
				<li>
					<i class='bx bx-desktop' ></i>
					<span class="text">
						<h3><?php echo $adminCount; ?> คน</h3>
						<p>ผู้ดูแลระบบ</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-group'></i>
					<span class="text">
                        <h3><?php echo $teamCount; ?> คน</h3>
						<p>รวมทั้งหมด</p>
					</span>
				</li>
			</ul>
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

			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>👨🏼‍🔧 ช่างซ่อมบำรุง</h3>
					</div>
					<table id="technician-table" class="teams" style="width:100%">
						<thead>
							<tr>
								<th></th>
                                <th>รหัสจำตัว</th>
                                <th>เพศ</th>
                                <th>ชื่อ-นามสกุล</th>
								<th>ทีม</th>
								<th>อีเมล</th>
								<th>เบอร์ติดด่อ</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
                            <?php
                                try {
                                    $sql = "SELECT * FROM teams WHERE urole = 'technician'";
                                    $stmt = $conn->query($sql);

                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
										echo "<td >";
										if ($row['profile_img'] !== null) {
											echo '<img class="profile-img" src="data:image/jpeg;base64,' . base64_encode($row['profile_img']) . '" width="100" onclick="showImagePopup(\'' . base64_encode($row['profile_img']) . '\')" style="cursor: pointer;" />';
										  } else {
											echo 'ไม่พบรูปภาพ';
										  }
										echo "</td>";
                                        echo "<td><p>" . $row['team_id'] . "</p></td>";
                                        echo "<td>" . "<p>"; if ($row['gender'] === 'M') {
                                        echo "ชาย"; } elseif ($row['gender'] === 'F') {
                                        echo "หญิง"; }
                                        echo "</p>" . "</td>";
                                        echo "<td><p>" . $row['firstname']." ". $row['lastname'] . "</p></td>";
										echo "<td><p>" . $row['grouptech'] . "</p></td>";
										echo "<td><p>" . $row['email'] . "</p></td>";
                                        echo "<td><p>" . $row['tel'] . "</p></td>";
										if (isset($_SESSION['admin_login'])) {
											echo "<td><button class='change-role' data-id='".$row['team_id']."'data-new-role='coadmin'><i class='bx bx-transfer'></i></button></td>";
										}else{
											echo"<td><p></p></td>";
										}
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
						<h3>🧑🏼‍💻 พนักงานดูแลระบบ</h3>
					</div>
					<table id="coadmin-table" class="teams" style="width:100%">
						<thead>
							<tr>
								<th></th>
                                <th>รหัสจำตัว</th>
                                <th>เพศ</th>
                                <th>ชื่อ-นามสกุล</th>
								<th>อีเมล</th>
								<th>เบอร์ติดด่อ</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php
                                try {
                                    $sql = "SELECT * FROM teams WHERE urole = 'coadmin'";
                                    $stmt = $conn->query($sql);

                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
										echo "<td >";
										if ($row['profile_img'] !== null) {
											echo '<img class="profile-img" src="data:image/jpeg;base64,' . base64_encode($row['profile_img']) . '" width="100" onclick="showImagePopup(\'' . base64_encode($row['profile_img']) . '\')" style="cursor: pointer;" />';
										  } else {
											echo 'ไม่พบรูปภาพ';
										  }
										echo "</td>";
                                        echo "<td><p>" . $row['team_id'] . "</p></td>";
                                        echo "<td>" . "<p>"; if ($row['gender'] === 'M') {
                                        echo "ชาย"; } elseif ($row['gender'] === 'F') {
                                        echo "หญิง"; }
                                        echo "</p>" . "</td>";
                                        echo "<td><p>" . $row['firstname']." ". $row['lastname'] . "</p></td>";
										echo "<td><p>" . $row['email'] . "</p></td>";
                                        echo "<td><p>" . $row['tel'] . "</p></td>";
										if (isset($_SESSION['admin_login'])) {
											echo "<td><button class='change-role' data-id='".$row['team_id']."'data-new-role='technician'><i class='bx bx-transfer'></i></button></td>";
										}else{
											echo"<td><p></p></td>";
										}
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
			<div class="table-data">
			<div class="order">
					<div class="head">
						<h3>🖥️ ผู้ดูแลระบบ</h3>
					</div>
					<table id="admin-table" class="teams" style="width:100%">
						<thead>
							<tr>
								<th></th>
                                <th>รหัสจำตัว</th>
                                <th>เพศ</th>
                                <th>ชื่อ-นามสกุล</th>
								<th>อีเมล</th>
								<th>เบอร์ติดด่อ</th>
							</tr>
						</thead>
						<tbody>
							<?php
                                try {
                                    $sql = "SELECT * FROM teams WHERE urole = 'admin'";
                                    $stmt = $conn->query($sql);

                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
										echo "<td >";
										if ($row['profile_img'] !== null) {
											echo '<img class="profile-img" src="data:image/jpeg;base64,' . base64_encode($row['profile_img']) . '" width="100" onclick="showImagePopup(\'' . base64_encode($row['profile_img']) . '\')" style="cursor: pointer;" />';
										  } else {
											echo 'ไม่พบรูปภาพ';
										  }
										echo "</td>";
                                        echo "<td><p>" . $row['team_id'] . "</p></td>";
                                        echo "<td>" . "<p>"; if ($row['gender'] === 'M') {
                                        echo "ชาย"; } elseif ($row['gender'] === 'F') {
                                        echo "หญิง"; }
                                        echo "</p>" . "</td>";
                                        echo "<td><p>" . $row['firstname']." ". $row['lastname'] . "</p></td>";
										echo "<td><p>" . $row['email'] . "</p></td>";
                                        echo "<td><p>" . $row['tel'] . "</p></td>";
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
		<!-- modal from container -->
		<div id="myModal" class="modal">
  			<div class="modal-content">
    			<span class="close">&times;</span>
    			<form action="admin-add_db.php" method="post" enctype="multipart/form-data">
					<div class="drag-area" id="dragArea">
                        <div id="beforeLoad">
                            <div class="iconpic">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="headerprofile" id='dragText'> ลากและวางไฟล์ </span>
                            <span class="headerprofile">หรือ <span class="buttonprofile" id="chooseFile">เลือกไฟล์</span></span> 
                            <input class="fileprofile" type="file" name="image" id="imageUpload" accept=".jpg, .jpeg, .png" required/>
                        </div>
                    </div>
						<table class="form-container">
							<tr>
								<td class="form-group">
									<label for="email">อีเมล:</label>
								</td>
								<td class="form-group">
									<input type="email" name="email" id="email" pattern="[a-zA-Z0-9._%+-]+@ku\.th" required>
								</td>
							</tr>
							<tr>
							<td class="form-group">
									<label for="password">รหัสผ่าน:</label>
								</td>
								<td class="form-group">
									<input type="password" name="password" id="password" required>
								</td>
								<td class="form-group">
									<label for="c_password">ยืนยันรหัสผ่าน:</label>
								</td>
								<td class="form-group">
									<input type="password" name="c_password" id="c_password" required>
								</td>
							</tr>
							<tr>
								<td class="form-group">
									<label for="gender">เพศ:</label>
								</td>
								<td class="form-group">
									<select name="gender" id="gender" >
										<option hidden>เลือก</option>
										<option value="M">ชาย</option>
										<option value="F">หญิง</option>
									</select>
								</td>
								<td class="form-group">
									<label for="team_id">รหัสจำตัว:</label>
								</td>
								<td class="form-group">
									<input type="text" name="team_id" id="team_id" required>
								</td>
							</tr>
							<tr>
								<td class="form-group">
									<label for="firstname">ชื่อ:</label>
								</td>
								<td class="form-group">
									<input type="text" name="firstname" id="firstname" required>
								</td>
								<td class="form-group">
									<label for="lastname">นามสกุล:</label>
								</td>
								<td class="form-group">
									<input type="text" name="lastname" id="lastname" required>
								</td>
							</tr>
							<tr>
								<td class="form-group">
									<label for="position">ตำแหน่ง:</label>
								</td>
								<td class="form-group">
									<input type="text" name="position" id="position" required>
								</td>
								<td class="form-group">
									<label for="grouptech">ทีม:</label>
								</td>
								<td class="form-group">
									<input type="text" name="grouptech" id="grouptech">
								</td>
							</tr>
							<tr>
								<td class="form-group">
									<label for="tel">เบอร์ติดด่อ:</label>
								</td>
								<td class="form-group">
									<input type="tel" name="tel" id="tel" required>
								</td>
								<td class="form-group">
									<label for="urole">บทบาท:</label>
								</td>
								<td class="form-group">
									<select name="urole" id="urole" >
										<option hidden>เลือก</option>
										<option value="technician">ช่างซ่อมบำรุง</option>
										<option value="coadmin">พนักดูแลระบบ</option>
									</select>
								</td>
							</tr>
						</table>
						<div class="model-btn">
							<input class="btntech" name="add" type="submit" value="เพิ่ม"></input>
						</div>
				</form>
  			</div>
		</div>
	</section>
	
	<!-- image popup container -->
	<div id="imagePopup" class="popup">
		<div class="popup-content">
			<span class="close-popup" onclick="closePopup()">&times;</span>
			<img id="popupImage" src="" alt="Slip Image">
		</div>
	</div>
    <script type="text/javascript" src="./js/admin.js"></script>
	<script type="text/javascript" src="./js/draganddrop.js"></script>
	<!-- modal from -->
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
	<!-- image popup -->
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
	<!-- dropdown coadmin -->
	<script>
		document.addEventListener("DOMContentLoaded", function () {
			var roleDropdown = document.getElementById("urole");
			var teamInput = document.getElementById("grouptech");
			function toggleReadonly() {
				if (roleDropdown.value === "coadmin") {
					teamInput.setAttribute("readonly", "readonly");
				} else {
					teamInput.removeAttribute("readonly");
				}
			}
			roleDropdown.addEventListener("change", toggleReadonly);
			toggleReadonly();
		});
	</script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
	<!-- table -->
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
			$('#admin-table').DataTable();
			$('#coadmin-table').DataTable();
			$('#technician-table').DataTable();
		});
	</script>
	<script>
		$(document).ready(function() {
			// When a "Change Role" button is clicked
			$('.change-role').click(function() {
				var team_id = $(this).data('id');
				var newRole = $(this).data('new-role');

				$.ajax({
					type: 'POST',
					url: 'admin-change-role.php',
					data: {
						team_id: team_id,
						newRole: newRole
					},
					success: function(response) {
						location.reload();
					},
					error: function(xhr, status, error) {
						console.log('Error: ' + error);
					}
				});
			});
		});
		</script>
</body>
</html>

