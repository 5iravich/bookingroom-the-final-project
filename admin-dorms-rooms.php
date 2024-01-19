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
        $cdquery = "SELECT COUNT(*) as dorm_count FROM dorms";
        $cdstmt = $conn->prepare($cdquery);
        $cdstmt->execute();
        $row = $cdstmt->fetch(PDO::FETCH_ASSOC);
        $dormCount = $row['dorm_count'];

		$crquery = "SELECT COUNT(*) as room_count FROM rooms";
        $crstmt = $conn->prepare($crquery);
        $crstmt->execute();
        $row = $crstmt->fetch(PDO::FETCH_ASSOC);
        $roomCount = $row['room_count'];

		$crfquery = "SELECT COUNT(*) as roomF_count FROM rooms WHERE max_capacity = 
		( SELECT COUNT(*) AS booked_persons FROM bookings WHERE bookings.room_id = rooms.room_id );";
        $crfstmt = $conn->prepare($crfquery);
        $crfstmt->execute();
        $row = $crfstmt->fetch(PDO::FETCH_ASSOC);
        $roomFCount = $row['roomF_count'];

		$craquery = "SELECT COUNT(*) AS roomA_count FROM rooms r LEFT JOIN
		( SELECT room_id, COUNT(*) AS booked_persons FROM bookings GROUP BY room_id ) b ON r.room_id = b.room_id
		WHERE b.booked_persons IS NULL OR b.booked_persons > r.max_capacity;";
        $crastmt = $conn->prepare($craquery);
        $crastmt->execute();
        $row = $crastmt->fetch(PDO::FETCH_ASSOC);
        $roomACount = $row['roomA_count'];

    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
	<link rel="shortcut icon" type="image/svg" href="https://sv1.picz.in.th/images/2023/10/27/ddsUZnt.png"/>
	<title>Buildings and rooms</title>
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
            <img src="https://sv1.picz.in.th/images/2023/10/27/ddsUZnt.png" width="25px" alt="Logo">
			<span class="text">DORMITORY</span>
		</a>
		<ul class="side-menu top">
			<li><a href="./admin.php"><i class='bx bxs-dashboard' ></i><span class="text">Dashboard</span></a></li>
            <li><a href="./admin-bookings.php"><i class='bx bx-list-ul' ></i><span class="text">การจองห้องพัก</span></a></li>
            <li><a href="./admin-reports.php"><i class='bx bx-list-ul' ></i><span class="text">การแจ้งซ่อมบำรุง</span></a></li>
			<li class="active"><a href="./admin-dorms-rooms.php"><i class='bx bxs-building' ></i><span class="text">อาคารและห้องพัก</span></a></li>
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
					<h1>อาคารและห้องพัก</h1>
					<ul class="breadcrumb">
						<li>
							<a class="active" href="./admin.php">Dashboard</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a  href="./admin-add-team.php">อาคารและห้องพัก</a>
						</li>
					</ul>
				</div>
			</div>

			<ul class="box-info">
				<li class="one">
					<i class='bx bxs-bed' ></i>
					<span class="text">
						<h3><?php echo $roomFCount; ?> ห้อง</h3>
						<p>ห้องพักที่เต็มแล้ว</p>
					</span>
				</li>
                <li class="one">
					<i class='bx bxs-bed' ></i>
					<span class="text">
						<h3><?php echo $roomACount; ?> ห้อง</h3>
						<p>ห้องพักที่ว่าง</p>
					</span>
				</li>
				<li class="one">
					<i class='bx bxs-bed' ></i>
					<span class="text">
						<h3><?php echo $roomCount; ?> ห้อง</h3>
						<p>ห้องพักทั้งหมด</p>
					</span>
				</li>
				<li class="one">
					<i class='bx bxs-buildings' ></i>
					<span class="text">
						<h3><?php echo $dormCount; ?> ตึก</h3>
						<p>จำนวนหอพักทั้งหมด</p>
					</span>
				</li>
			</ul>


			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>🍃 หอพักชายธรรมดา</h3>
					</div>
					<table id="ms-table" class="display" style="width:100%">
						<thead>
							<tr>
                                <th>หอพักที่</th>
                                <th>ชื่อหอพัก</th>
                                <th>จำนวนห้องทั้งหมด</th>
							</tr>
						</thead>
						<tbody>
                            <?php
                                try {
									$sql = "SELECT d.*, COUNT(r.room_id) AS room_count
									FROM dorms d
									LEFT JOIN rooms r ON d.dorm_id = r.dorm_id
									WHERE d.dorm_gen = 'ชาย' AND d.dorm_type = 'Standard'
									GROUP BY d.dorm_id";
                                    $stmt = $conn->query($sql);
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
                                        echo "<td><p>" . $row['dorm_id'] . "</p></td>";
                                        echo "<td><p>" . $row['dorm_name']."</p></td>";
                                        echo "<td><p>" . $row['room_count'] . "</p></td>";
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
						<h3>🍃 หอพักหญิงธรรมดา</h3>
					</div>
					<table id="fs-table" class="display" style="width:100%">
						<thead>
							<tr>
                                <th>หอพักที่</th>
                                <th>ชื่อหอพัก</th>
                                <th>จำนวนห้องทั้งหมด</th>
							</tr>
						</thead>
						<tbody>
                            <?php
                                try {
									$sql = "SELECT d.*, COUNT(r.room_id) AS room_count
									FROM dorms d
									LEFT JOIN rooms r ON d.dorm_id = r.dorm_id
									WHERE d.dorm_gen = 'หญิง' AND d.dorm_type = 'Standard'
									GROUP BY d.dorm_id";
                                    $stmt = $conn->query($sql);
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
                                        echo "<td><p>" . $row['dorm_id'] . "</p></td>";
                                        echo "<td><p>" . $row['dorm_name']."</p></td>";
                                        echo "<td><p>" . $row['room_count'] . "</p></td>";
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
						<h3>❄️ หอพักชายปรับอากาศ</h3>
					</div>
					<table id="ma-table" class="display" style="width:100%">
						<thead>
							<tr>
                                <th>หอพักที่</th>
                                <th>ชื่อหอพัก</th>
                                <th>จำนวนห้องทั้งหมด</th>
							</tr>
						</thead>
						<tbody>
                            <?php
                                try {
									$sql = "SELECT d.*, COUNT(r.room_id) AS room_count
									FROM dorms d
									LEFT JOIN rooms r ON d.dorm_id = r.dorm_id
									WHERE d.dorm_gen = 'ชาย' AND d.dorm_type = 'Air'
									GROUP BY d.dorm_id";
                                    $stmt = $conn->query($sql);

                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
                                        echo "<td><p>" . $row['dorm_id'] . "</p></td>";
                                        echo "<td><p>" . $row['dorm_name']."</p></td>";
                                        echo "<td><p>" . $row['room_count'] . "</p></td>";
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
						<h3>❄️ หอพักหญิงปรับอากาศ</h3>
					</div>
					<table id="fa-table" class="display" style="width:100%">
						<thead>
							<tr>
                                <th>หอพักที่</th>
                                <th>ชื่อหอพัก</th>
                                <th>จำนวนห้องทั้งหมด</th>
							</tr>
						</thead>
						<tbody>
                            <?php
                                try {
									$sql = "SELECT d.*, COUNT(r.room_id) AS room_count
									FROM dorms d
									LEFT JOIN rooms r ON d.dorm_id = r.dorm_id
									WHERE d.dorm_gen = 'หญิง' AND d.dorm_type = 'Air'
									GROUP BY d.dorm_id";
                                    $stmt = $conn->query($sql);

                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
                                        echo "<td><p>" . $row['dorm_id'] . "</p></td>";
                                        echo "<td><p>" . $row['dorm_name']."</p></td>";
                                        echo "<td><p>" . $row['room_count'] . "</p></td>";
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
    <script type="text/javascript" src="./js/admin.js"></script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
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
			$('#ms-table').DataTable();
			$('#ma-table').DataTable();
			$('#fs-table').DataTable();
			$('#fa-table').DataTable();
		});
	</script>
</body>
</html>

