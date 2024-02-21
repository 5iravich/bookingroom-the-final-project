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

    $cuquery = "SELECT COUNT(*) as user_count FROM users";
    $custmt = $conn->prepare($cuquery);
    $custmt->execute();
    $row = $custmt->fetch(PDO::FETCH_ASSOC);
    $userCount = $row['user_count'];

    $fesql = "SELECT COUNT(*) as female_count FROM users WHERE gender = 'F'";
    $festmt = $conn->query($fesql);
    $row = $festmt->fetch(PDO::FETCH_ASSOC);
    $femaleCount = $row['female_count'];

    $msql = "SELECT COUNT(*) as male_count FROM users WHERE gender = 'M'";
    $mstmt = $conn->query($msql);
    $row = $mstmt->fetch(PDO::FETCH_ASSOC);
    $maleCount = $row['male_count'];
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<link rel="shortcut icon" type="image/svg" href="./img/logo.png"/>
		<title>Manage members</title>
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
            <li><a href="./admin-storage.php"><i class='bx bxs-cylinder' ></i><span class="text">คลังวัสดุ/อุปกรณ์</span></a></li>
			<li><a href="./admin-add-team.php"><i class='bx bxs-group' ></i><span class="text">ทีมงาน/ช่างซ่อมบำรุง</span></a></li>
			<li class="active"><a href="./admin-users.php"><i class='bx bxs-group' ></i><span class="text">จัดการสมาชิก</span></a></li>
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
					<h1>จัดการสมาชิก</h1>
					<ul class="breadcrumb">
						<li>
							<a class="active" href="./admin.php">Dashboard</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a  href="./admin-add-team.php">จัดการสมาชิก</a>
						</li>
					</ul>
				</div>
			</div>

			<ul class="box-info">
                <li class="users">
                    <i class='bx bx-female' ></i>
					<span class="text">
						<h3><?php echo $femaleCount; ?> คน</h3>
						<p>เพศหญิง</p>
					</span>
				</li>
				<li class="users">
                    <i class='bx bx-male'></i>
					<span class="text">
						<h3><?php echo $maleCount; ?> คน</h3>
						<p>เพศชาย</p>
					</span>
				</li>
				<li class="users">
                    <i class='bx bxs-user' ></i>
					<span class="text">
						<h3><?php echo $userCount; ?> คน</h3>
						<p>จำนวนสมาชิกทั้งหมด</p>
					</span>
				</li>
			</ul>
			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>🗂️ สมาชิก</h3>
					</div>
					<table id="users-table" class="display" style="width:100%">
						<thead>
							<tr>
                                <th>รหัสนิสิต</th>
                                <th>เพศ</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>เบอร์โทรศัพท์</th>
                                <th>อีเมล</th>
                                <th>อีเมลสำรอง</th>
								<th>ผู้ปกครอง</th>
                                <th>เบอร์โทรผู้ปกครอง</th>
								<th>เข้าร่วมเมื่อ</th>
							</tr>
						</thead>
						<tbody>
                            <?php
                                try {
                                    $sql = "SELECT * FROM users";
                                    $stmt = $conn->query($sql);

                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
                                        echo "<td>"."<p>" . $row['stdid'] . "</p>" . "</td>";
                                        echo "<td>" . "<p>"; if ($row['gender'] === 'M') {
                                        echo "ชาย"; } elseif ($row['gender'] === 'F') {
                                        echo "หญิง"; }
                                        echo "</p>" . "</td>";
                                        echo "<td><p>" . $row['firstname']." ". $row['lastname'] . "</p></td>";
                                        echo "<td><p>" . $row['tel'] . "</p></td>";
                                        echo "<td><p>" . $row['email'] . "</p></td>";
                                        echo "<td><p>" . $row['coemail'] . "</p></td>";
                                        echo "<td><p>" . $row['parentsname'] . " (". $row['relation'] .")</p></td>";
                                        echo "<td><p>" . $row['parentstel'] . "</p></td>";
                                        $formattedCreatedAt = date('d/m/Y', strtotime($row['created_at']));
                                        echo "<td>" . $formattedCreatedAt . "</td>"; 
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
			$('#users-table').DataTable({
				dom: 'Blfrtip',
				buttons: [ 
            		 'print'
        		]
			});
		});
	</script>
</body>
</html>

