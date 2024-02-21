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
        $cbquery = "SELECT COUNT(*) as booking_count FROM bookings";
        $cbstmt = $conn->prepare($cbquery);
        $cbstmt->execute();
        $row = $cbstmt->fetch(PDO::FETCH_ASSOC);
        $bookingCount = $row['booking_count'];

		$pendingUsersQuery = "SELECT COUNT(*) as pending_user_count FROM bookings WHERE statuspay = 'pending'";
    	$pendingUsersStmt = $conn->prepare($pendingUsersQuery);
		$pendingUsersStmt->execute();
		$pendingUserRow = $pendingUsersStmt->fetch(PDO::FETCH_ASSOC);
		$pendingUserCount = $pendingUserRow['pending_user_count'];

		$confirmedUsersQuery = "SELECT COUNT(*) as confirmed_user_count FROM bookings WHERE statuspay = 'confirmed'";
    	$confirmedUsersStmt = $conn->prepare($confirmedUsersQuery);
		$confirmedUsersStmt->execute();
		$confirmedUserRow = $confirmedUsersStmt->fetch(PDO::FETCH_ASSOC);
		$confirmedUserCount = $confirmedUserRow['confirmed_user_count'];

    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
	<link rel="shortcut icon" type="image/svg" href="./img/logo.png"/>
	<title>Reservation</title>
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
            <li class="active"><a href="./admin-bookings.php"><i class='bx bx-list-ul' ></i><span class="text">การจองห้องพัก</span></a></li>
            <li><a href="./admin-reports.php"><i class='bx bx-list-ul' ></i><span class="text">การแจ้งซ่อมบำรุง</span></a></li>
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
					<h1>การจองห้องพัก</h1>
					<ul class="breadcrumb">
						<li>
							<a class="active" href="./admin.php">Dashboard</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a  href="./admin-add-team.php">การจองห้องพัก</a>
						</li>
					</ul>
				</div>
			</div>

			<ul class="box-info">
                <li>
					<i class='bx bxs-notepad' ></i>
					<span class="text">
						<h3><?php echo $pendingUserCount; ?> รายการ</h3>
						<p>ที่รอยืนยัน</p>
					</span>
				</li>
				<li>
					<i class='bx bx-check'></i>
					<span class="text">
						<h3><?php echo $confirmedUserCount; ?> รายการ</h3>
						<p>ที่ยืนยันแล้ว</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-bed' ></i>
					<span class="text">
                        <h3><?php echo $bookingCount; ?> รายการ</h3>
						<p>การจองห้องพักทั้งหมด</p>
					</span>
				</li>
			</ul>


			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>🔑 รายการจองห้องพัก</h3>
					</div>
					<table id="bk-table" class="display" style="width:100%">
						<thead>
							<tr>
								<th>เวลาที่จอง</th>
								<th>วันที่จอง</th>
                                <th>รหัสประจำตัวนิสิต</th>
                                <th>เพศ</th>
                                <th>ชื่อ-นามสกุล</th>
								<th>อาคาร</th>
								<th>ห้อง</th>
								<th>ประเภท</th>
								<th>ราคา</th>
                                <th>เบอร์ติดด่อ</th>
                                <th>หลังฐานการชำระเงิน</th>
								<th>สถานะการจอง</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
                            <?php
                                try {
                                    $sql = "SELECT u.*, b.*,rm.*,d.*
                                    FROM users u
                                    JOIN bookings b ON u.stdid = b.stdid
									JOIN rooms rm ON b.room_id = rm.room_id
                                    JOIN dorms d ON rm.dorm_id = d.dorm_id";
                                    $stmt = $conn->query($sql);

                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
										$formattedTimeAt = date('H:iน.', strtotime($row['booking_timestamp']));
                                        echo "<td>" . $formattedTimeAt . "</td>"; 
										$formattedDateAt = date('d/m/Y', strtotime($row['booking_timestamp']));
                                        echo "<td>" . $formattedDateAt . "</td>"; 
                                        echo "<td>"."<p>" . $row['stdid'] . "</p>" . "</td>";
                                        echo "<td>" . "<p>"; if ($row['gender'] === 'M') {
                                        echo "ชาย"; } elseif ($row['gender'] === 'F') {
                                        echo "หญิง"; }
                                        echo "</p>" . "</td>";
                                        echo "<td>";
                                        echo "<p>" . $row['firstname']." ". $row['lastname'] . "</p>";
                                        echo "</td>";
										echo "<td>";
                                        echo "<p>" . $row['dorm_id'] . "</p>";
                                        echo "</td>";
										echo "<td>";
                                        echo "<p>" . $row['room_number'] . "</p>";
                                        echo "</td>";
                                        echo "<td>" . "<p>"; if ($row['dorm_type'] === 'Standard') {
                                        echo "ธรรมดา"; } elseif ($row['dorm_type'] === 'Air') {
                                        echo "ปรับอากาศ"; }
                                        echo "</p>" . "</td>";
										echo "<td>";
                                        echo "<p>" . $row['price'] . " บาท</p>";
                                        echo "</td>";
                                        echo "<td>";
                                        echo "<p>" . $row['tel'] . "</p>";
                                        echo "</td>";
										echo "<td >";
										if ($row['slip_image'] !== null) {
											echo '<img src="data:image/jpeg;base64,' . base64_encode($row['slip_image']) . '" width="100" onclick="showImagePopup(\'' . base64_encode($row['slip_image']) . '\')" style="cursor: pointer;" />';
										  } else {
											echo 'ไม่พบรูปภาพ';
										  }
										echo "</td>";
                                        echo "<td>";
                                            if ($row['statuspay'] === 'confirmed') {
                                                echo "<span class='status completed'>เสร็จสิ้น</span>";
                                            } elseif ($row['statuspay'] === 'pending') {
                                                echo "<span class='status pending'>รอยืนยัน</span>";
                                            } elseif ($row['statuspay'] === 'cancelled') {
                                                echo "<span class='status process'>ยกเลิก</span>";
                                            } else {
                                                echo "<span class='status unknown'>Unknown</span>";
                                            }
                                        echo "</td>";
										echo "<td>";
										if ($row['statuspay'] !== 'confirmed') {
											echo "<form method='POST' action='admin-update-status.php'>"; 
											echo "<input type='hidden' name='stdid' value='" . $row['stdid'] . "'>"; 
											echo "<button type='submit' id='confirmed' name='statuspay' value='confirmed'><i class='bx bxs-check-square'></i></button>";
											echo "<button type='submit' id='cancelled' name='statuspay' value='cancelled'><i class='bx bxs-x-square'></i></button>";
											echo "</form>";	
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
			$('#bk-table').DataTable({
				dom: 'Blfrtip',
				buttons: [
            		'print'
        		]
			});
		});
	</script>
</body>
</html>

