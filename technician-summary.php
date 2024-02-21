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

		$waitCount = "SELECT COUNT(*) as wait_count FROM reports WHERE team_id = :team_id AND report_status = 'wait'";
		$waitCountstmt = $conn->prepare($waitCount);
		$waitCountstmt->bindParam(':team_id', $team_id);
		$waitCountstmt->execute();
		$result = $waitCountstmt->fetch(PDO::FETCH_ASSOC);
		$wait_count = $result['wait_count'];

		$inprogressCount = "SELECT COUNT(*) as inprogress_count FROM reports WHERE team_id = :team_id AND report_status = 'in progress'";
		$inprogressCountstmt = $conn->prepare($inprogressCount);
		$inprogressCountstmt->bindParam(':team_id', $team_id);
		$inprogressCountstmt->execute();
		$result = $inprogressCountstmt->fetch(PDO::FETCH_ASSOC);
		$inprogress_count = $result['inprogress_count'];

		$succeedCount = "SELECT COUNT(*) as succeed_count FROM reports WHERE team_id = :team_id AND report_status = 'succeed'";
		$succeedCountstmt = $conn->prepare($succeedCount);
		$succeedCountstmt->bindParam(':team_id', $team_id);
		$succeedCountstmt->execute();
		$result = $succeedCountstmt->fetch(PDO::FETCH_ASSOC);
		$succeed_count = $result['succeed_count'];

		$withdrawalData = "SELECT w.*,m.* FROM withdrawals w JOIN Materials m ON w.ItemID = m.ItemID WHERE team_id = :team_id";
		$withdrawalDataStmt = $conn->prepare($withdrawalData);
		$withdrawalDataStmt->bindParam(':team_id', $team_id);
		$withdrawalDataStmt->execute();
		$withdrawalData = $withdrawalDataStmt->fetchAll(PDO::FETCH_ASSOC);

		$withdrawalQuantities = array_column($withdrawalData, 'withdrawal_quantity');
    	$itemNames = array_column($withdrawalData, 'ItemName');

		$inProgressPerDay = "SELECT DATE(report_time) AS report_date, COUNT(*) AS in_progress_count
							FROM reports
							WHERE team_id = :team_id AND report_status = 'in progress'
							GROUP BY DATE(report_time)
							ORDER BY DATE(report_time) ASC";
		$inProgressPerDayStmt = $conn->prepare($inProgressPerDay);
		$inProgressPerDayStmt->bindParam(':team_id', $team_id);
		$inProgressPerDayStmt->execute();
		$inProgressDataPerDay = $inProgressPerDayStmt->fetchAll(PDO::FETCH_ASSOC);

		// Create arrays for years and in-progress counts
		$dates = array();
		$inProgressCountsPerDay = array();

		foreach ($inProgressDataPerDay as $data) {
			$years[] = $data['report_date'];
			$inProgressCounts[] = $data['in_progress_count'];
		}

		$roomDormData = "SELECT r.*,b.*,rm.*, COUNT(*) as count
                FROM reports r
                JOIN users u ON r.stdid = u.stdid
				JOIN bookings b ON u.stdid = b.stdid
				JOIN rooms rm ON b.room_id = rm.room_id
				JOIN dorms d ON rm.dorm_id = d.dorm_id
                WHERE r.team_id = :team_id
                GROUP BY r.stdid";
		$roomDormDataStmt = $conn->prepare($roomDormData);
		$roomDormDataStmt->bindParam(':team_id', $team_id);
		$roomDormDataStmt->execute();
		$roomDormData = $roomDormDataStmt->fetchAll(PDO::FETCH_ASSOC);

		$roomNumbers = array_column($roomDormData, 'room_number');
		$dormIDs = array_column($roomDormData, 'dorm_id');

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
    <title>Summary</title>
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
				<li class="nav-item-a">
					<a href="./technician-list-withdrawals.php">ประวัติการเบิกวัสดุอุปกรณ์</a>
				</li>
				<li class="nav-item-a active">
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
					<li class="mobile-nav-item">
						<a href="./technician-tasks.php">งานของฉัน</a>
					</li>
					<li class="mobile-nav-item">
						<a href="./technician-history.php">ประวัติงานซ่อมบำรุง</a>
					</li>
					<li class="mobile-nav-item">
						<a href="./technician-list-withdrawals.php">ประวัติการเบิกวัสดุอุปกรณ์</a>
					</li>
					<li class="mobile-nav-item active">
						<a href="./technician-summary.php">สรุปภาพรวม</a>
					</li>
					<li class="mobile-nav-item">
						<a href="./logout.php">ออกจากระบบ</a>
					</li>
				</ul>
			</div>
		</nav>
		<section>
		<main class="chart">
			<div class="head-title">
				<div class="right">
					<a href="./technician-pdf.php" target="_blank" class="btn-download">
						<i class='bx bxs-cloud-download' ></i>
						<span class="text">Download PDF</span>
					</a>
				</div>
			</div>
			<ul class="box-info">
				<li class="one">
					<i class='bx bx-paperclip' ></i>
					<span class="text">
						<h3><?php echo $wait_count; ?> รายการ</h3>
						<p>ได้รับมอบหมาย</p>
					</span>
				</li>
                <li class="one">
                    <i class='bx bx-clipboard' ></i>
					<span class="text">
						<h3><?php echo $inprogress_count; ?> รายการ</h3>
						<p>ดำเนินการ</p>
					</span>
				</li>
				<li class="one">
					<i class='bx bx-check'></i>
					<span class="text">
						<h3><?php echo $succeed_count; ?> รายการ</h3>
						<p>เสร็จสิ้น</p>
					</span>
				</li>
			</ul>
			<ul class="box-info">
				<div class="table-data">
					<div class="order">
						<div class="chart-content main" >
							<canvas id="reportChart"></canvas>
						</div>
					</div>
					<div class="order">
						<div class="chart-content">
							<canvas id="inProgressLineChart"></canvas>
						</div>
					</div>
					<div class="order">
						<div class="chart-content">
							<canvas id="withdrawalChart"></canvas>
						</div>
					</div>
					<div class="order">
						<div class="chart-content">
							<canvas id="roomDormChart"></canvas>
						</div>
					</div>
				</div>
			</ul>
			</section>
			<script type="text/javascript" src="./js/script.js"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
			<script>
				var ctx = document.getElementById('reportChart').getContext('2d');

				var data = {
					labels: ['รอการยืนยัน', 'ดำเนินการ', 'สำเร็จ'],
					datasets: [
						{
							label: 'สัดส่วนรายการงานซ่อมบำรุงของฉัน',
							data: [<?php echo $wait_count; ?>, <?php echo $inprogress_count; ?>, <?php echo $succeed_count; ?>],
							backgroundColor:  ['#FFCD4BC4','#82CD47C4','#5272F2C4',],
							borderColor: ['#FFCD4B','#82CD47','#5272F2',],
							borderWidth: 1,
							borderRadius: 15,
							pointRadius: 50,
							pointBackgroundColor: ['#FFCD4B','#82CD47','#5272F2',],
						},
					],
				};

				var options = {
					responsive: true,
					plugins: {
					title: {
						display: true,
						text: 'สัดส่วนรายการงานซ่อมบำรุงของฉัน',
						font: { size: 16, family: 'Mitr' }
					},
					legend: {
						display: false,
					},
				},
					scales: {
						y: {
							beginAtZero: true,
							suggestedMin: 0,
						},
					},
				};

				var reportChart = new Chart(ctx, {
					type: 'bar',
					data: data,
					options: options,
				});
			</script>
			<script>
				var ctxWithdrawal = document.getElementById('withdrawalChart').getContext('2d');

				var withdrawalData = {
					labels: <?php echo json_encode($itemNames); ?>,
					datasets: [
						{
							label: 'จำนวนการเบิก',
							data: <?php echo json_encode($withdrawalQuantities); ?>,
							backgroundColor: ['#FFCD4B', '#82CD47', '#5272F2',],
						},
					],
				};

				var withdrawalOptions = {
					responsive: true,
					plugins: {
						title: {
							display: true,
							text: 'สัดส่วนการเบิกของฉัน',
							font: { size: 16, family: 'Mitr' },
						},
					},
				};

				var withdrawalChart = new Chart(ctxWithdrawal, {
					type: 'pie',
					data: withdrawalData,
					options: withdrawalOptions,
				});
			</script>
			<script>
				var ctxInProgress = document.getElementById('inProgressLineChart').getContext('2d');

				var inProgressData = {
					labels: <?php echo json_encode($years); ?>,
					datasets: [
						{
							label: 'งานซ่อมบำรุงที่ได้รับมอบหมาย',
							data: <?php echo json_encode($inProgressCounts); ?>,
							fill: false,
							borderColor: 'rgba(75, 192, 192, 1)', // Line color
							borderWidth: 2,
							pointBackgroundColor: 'rgba(75, 192, 192, 1)', // Point color
						},
					],
				};

				var inProgressOptions = {
					responsive: true,
					plugins: {
						title: {
							display: true,
							text: 'งานซ่อมบำรุงที่ได้รับมอบหมายต่อปี',
							font: { size: 16, family: 'Mitr' },
						},
					},
					scales: {
						x: {
							display: true,
							beginAtZero: true,
							title: {
							display: true
							}
						},
						y: {
							display: true,
							title: {
								display: true,
								text: 'จำนวนรายการ'
							},
							beginAtZero: true,
						},
					},elements: {
							line: {
								tension: 0.4,
							}
						}
				};

				var inProgressLineChart = new Chart(ctxInProgress, {
					type: 'line',
					data: inProgressData,
					options: inProgressOptions,
				});
			</script>
			<script>
				var ctxRoomDorm = document.getElementById('roomDormChart').getContext('2d');

				var roomDormData = {
					labels: <?php echo json_encode($roomNumbers); ?>,
					datasets: [
						{
							data: <?php echo json_encode($dormIDs); ?>,
							backgroundColor: [
								'rgba(255, 99, 132, 0.2)',
								'rgba(54, 162, 235, 0.2)',
								'rgba(255, 206, 86, 0.2)',
								'rgba(75, 192, 192, 0.2)',
								'rgba(153, 102, 255, 0.2)',
							],
							borderColor: [
								'rgba(255, 99, 132, 1)',
								'rgba(54, 162, 235, 1)',
								'rgba(255, 206, 86, 1)',
								'rgba(75, 192, 192, 1)',
								'rgba(153, 102, 255, 1)',
							],
							borderWidth: 1,
						},
					],
				};

				var roomDormOptions = {
					responsive: true,
					plugins: {
						title: {
							display: true,
							text: 'สัดส่วนห้องพักที่แจ้งซ่อมบำรุง',
							font: { size: 16, family: 'Mitr' },
						},
					},
				};

				var roomDormChart = new Chart(ctxRoomDorm, {
					type: 'doughnut',
					data: roomDormData,
					options: roomDormOptions,
				});
			</script>
			</main>
		</body>
</html>