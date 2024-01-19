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
		if (!$team) {
			$_SESSION['error'] = 'ไม่พบข้อมูลทีม';
			header('location: admin-profile.php');
			exit();
		}
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

        $csquery = "SELECT COUNT(*) as material_count FROM materials";
        $csstmt = $conn->prepare($csquery);
        $csstmt->execute();
        $row = $csstmt->fetch(PDO::FETCH_ASSOC);
        $materialCount = $row['material_count'];

        $cuquery = "SELECT COUNT(*) as user_count FROM users";
        $custmt = $conn->prepare($cuquery);
        $custmt->execute();
        $row = $custmt->fetch(PDO::FETCH_ASSOC);
        $userCount = $row['user_count'];

		$teamCountQuery = "SELECT COUNT(*) as team_count FROM teams";
		$teamStmt = $conn->prepare($teamCountQuery);
		$teamStmt->execute();
		$teamRow = $teamStmt->fetch(PDO::FETCH_ASSOC);
		$teamCount = $teamRow['team_count'];

		$totalCount = $userCount + $teamCount;

        $maleQuery = "SELECT COUNT(*) as male_count FROM users WHERE gender = 'M'";
        $femaleQuery = "SELECT COUNT(*) as female_count FROM users WHERE gender = 'F'";

        $maleStmt = $conn->prepare($maleQuery);
        $maleStmt->execute();
        $maleRow = $maleStmt->fetch(PDO::FETCH_ASSOC);
        $maleCount = $maleRow['male_count'];

        $femaleStmt = $conn->prepare($femaleQuery);
        $femaleStmt->execute();
        $femaleRow = $femaleStmt->fetch(PDO::FETCH_ASSOC);
        $femaleCount = $femaleRow['female_count'];

    } catch (PDOException $e) {
        $_SESSION['error'] = 'เกิดข้อผิดพลาดในการดึงข้อมูลผู้ใช้';
    	header('location: admin.php');
    	exit();
    }

	$yearsAgo = 5; // years to go back
	$startDate = date('Y-m-d', strtotime("-$yearsAgo years"));
	$endDate = date('Y-m-d', strtotime('-1 year'));

	$reportPerYearQuery = "SELECT YEAR(report_timestamp) AS report_year, COUNT(*) as report_count FROM reports WHERE report_timestamp BETWEEN :startDate AND :endDate GROUP BY report_year";
	$reportPerYearStmt = $conn->prepare($reportPerYearQuery);
	$reportPerYearStmt->bindParam(":startDate", $startDate);
	$reportPerYearStmt->bindParam(":endDate", $endDate);
	$reportPerYearStmt->execute();
	$reportCountsperYear = $reportPerYearStmt->fetchAll(PDO::FETCH_ASSOC);

	$dailyReportCountsQuery = "SELECT DATE(report_timestamp) as report_date, COUNT(*) as report_count FROM reports GROUP BY report_date";
	$dailyReportCountsStmt = $conn->prepare($dailyReportCountsQuery);
	$dailyReportCountsStmt->execute();
	$dailyReportCounts = $dailyReportCountsStmt->fetchAll(PDO::FETCH_ASSOC);

	$dates = [];
	$reportCounts = [];
	
	foreach ($dailyReportCounts as $row) {
		$dates[] = $row['report_date'];
		$reportCounts[] = $row['report_count'];
	}
	
	if(isset($_POST['btn_save'])){
		try{
			$header = $_POST['txt_header'];
			$textbody = $_POST['txt_summernote'];

			if(!empty($header)&&!empty($textbody)){
				$addpost = $conn->prepare("INSERT INTO posts (header, textbody) VALUES(:header, :textbody)");
				$addpost->bindParam(':header', $header);
				$addpost->bindParam(':textbody', $textbody);
				$addpost->execute();
				$_SESSION['success'] = "ประกาศข่าวสารเรียบร้อยแล้ว!";
                header("location: ./admin.php");
				exit();
			}else{
				$_SESSION['error'] = "กรุณาใส่ข้อมูลก่อนกดเพิ่มข้อมูล";
				header("location: ./admin.php");
				exit();
			}
		}catch(PDOException $e) {
            echo $e->getMessage();
		}
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
	<link rel="shortcut icon" type="image/svg" href="https://sv1.picz.in.th/images/2023/10/27/ddsUZnt.png"/>
	<title>Dashboard</title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" type="text/css" href="./css/admin.css">
	<link rel="stylesheet" type="text/css" href="./css/table.css">
	<link href="https://fonts.googleapis.com/css?family=Mitr:600&display=swap" rel="stylesheet">
	<script src="https://kit.fontawesome.com/a81368914c.js"></script>
	<!-- summernote -->
	<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
	<section class="sidebar" id="sidebar">
		<a href="./admin.php" class="brand">
            <img src="https://sv1.picz.in.th/images/2023/10/27/ddsUZnt.png" width="25px" alt="Logo">
			<span class="text">DORMITORY</span>
		</a>
		<ul class="side-menu top">
			<li class="active"><a href="./admin.php"><i class='bx bxs-dashboard' ></i><span class="text">Dashboard</span></a></li>
            <li><a href="./admin-bookings.php"><i class='bx bx-list-ul' ></i><span class="text">การจองห้องพัก</span></a></li>
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
					<h1>Dashboard</h1>
					<ul class="breadcrumb">
						<li><a href="./admin.php">Dashboard</a></li>
					</ul>
				</div>
				<div class="right">
					<a href="#" class="btn-news">
						<i class='bx bx-news'></i>
						<span class="text">ประกาศข่าวสาร</span>
					</a>
					<a href="./admin-pdf.php" target="_blank" class="btn-download">
						<i class='bx bxs-cloud-download' ></i>
						<span class="text">Download PDF</span>
					</a>
				</div>
			</div>

			<ul class="box-info">
				<li class="one">
                    <i class='bx bxs-bed' ></i>
					<span class="text">
						<h3><?php echo $bookingCount; ?> รายการ</h3>
						<p>จองห้องพัก</p>
					</span>
				</li>
                <li class="one">
                    <i class='bx bx-clipboard' ></i>
					<span class="text">
						<h3><?php echo $reportCount; ?> รายการ</h3>
						<p>การแจ้งซ่อม</p>
					</span>
				</li>
				<li class="one">
                    <i class='bx bx-package'></i>
					<span class="text">
						<h3><?php echo $materialCount; ?> รายการ</h3>
						<p>วัสดุและอุปกรณ์ทั้งหมด</p>
					</span>
				</li>
				<li class="one">
					<i class='bx bxs-group' ></i>
					<span class="text">
						<h3><?php echo $totalCount; ?> คน</h3>
						<p>จำนวนผู้ใช้งานทั้งหมด</p>
					</span>
				</li>
			</ul>
			
			<ul class="box-info main">
			<div class="table-data">
				<div class="order main">
					<div class="chart-content" >
						<canvas id="reportLineChart"></canvas>
					</div>
				</div>
				<div class="order main">
					<div class="chart-content" >
						<canvas id="reportChart" ></canvas>
					</div>
				</div>
				<div class="order main">
					<div class="chart-content" >
						<canvas id="genderChart"></canvas>
					</div>
				</div>
				
				<div class="order main">
					<div class="head">
						<h3>📰 รายการข่าวสาร</h3>
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
					<table id="news-table" class="display" style="width:100%">
						<thead>
							<tr>
								<th>ประกาศเมื่อ</th>
								<th>เวลา</th>
					            <th>หัวข้อ</th>
                                <th>เนื้อหาย่อ</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
                            <?php
                                try {
                                    $sql = "SELECT * FROM posts";
                                    $stmt = $conn->query($sql);

                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
										$formattedCreatedAt = date('d/m/Y', strtotime($row['created_at']));
                                        echo "<td>" . $formattedCreatedAt . "</td>"; 
										$formattedCreatedAt = date('H:iน.', strtotime($row['created_at']));
                                        echo "<td>" . $formattedCreatedAt . "</td>"; 
                                        echo "<td>"."<p>" . $row['header'] . "</p>" . "</td>";
										$textbody = $row['textbody'];
										if (str_word_count($textbody) > 10) {
											$words = explode(' ', $textbody);
											$shortText = implode(' ', array_slice($words, 0, 4));
											echo "<td>" . "<p>" . $shortText . "..." . "</p>" . "</td>";
										} else {
											echo "<td>" . "<p>" . $textbody . "</p>" . "</td>";
										}
										echo "<td>";
										echo "<form method='POST' action='admin-delete-news.php'>"; 
										echo "<input type='hidden' name='postid' value='" . $row['postid'] . "'>"; 
										echo "<button type='submit' id='delete' value='delete'><i class='bx bx-trash'></i></button>";
										echo "</form>";	
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
			</ul>
		</main>
	</section>
	<div id="myModal" class="modal">
		<div class="modal-content news">
			<span class="close">&times;</span>
			<h2><i class='bx bx-news'></i> ประกาศข่าวสาร</h2><br>
			<form action="" method="POST" clas="container">
				<div class="form-group">
					<label class="news">หัวข้อ:</label>
					<input class="input_txt" type="text" name="txt_header" placeholder="กรอกหัวข้อ" required>
				</div>
				<div class="form-group">
					<label class="news">เนื้อหา:</label>
					<textarea name="txt_summernote" id="summernote" required>

					</textarea>
				</div>
				<div class="form-button">
					<button name="btn_save" class="btn btn-save">บันทึก</button>
				</div>
			</form>
		</div>
	</div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
	<!-- DoughnutChart -->
    <script> 
        var ctx = document.getElementById('genderChart').getContext('2d');
        var genderChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['ชาย', 'หญิง'],
                datasets: [{
                    data: [<?php echo $maleCount; ?>, <?php echo $femaleCount; ?>],
                    backgroundColor: ['#8FBDD3', '#FDCEDF']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'สัดส่วนของผู้ใช้งานตามเพศ',
                        font: { size: 16, family: 'Mitr' }
                    }
                }
            }
        });
    </script>
	<!-- BarChart -->
	<script>

		var reportCountsperYear = <?php echo json_encode($reportCountsperYear); ?>;

		var years = reportCountsperYear.map(function(item) {
			return item.report_year;
		});

		var counts = reportCountsperYear.map(function(item) {
			return item.report_count;
		});
		var ctx = document.getElementById('reportChart').getContext('2d');
		var reportChart = new Chart(ctx, {
			type: 'bar',
			data: {
				labels: years,
				datasets: [{
					label: 'จำนวนการแจ้งซ่อมบำรุง',
					data: counts,
					backgroundColor: '#D8003299',
					borderColor: '#D83F31',
					borderWidth: 1,
				}]
			},
			options: {
				responsive: true,
				plugins: {
						title: {
							display: true,
							text: 'จำนวนการแจ้งซ่อมบำรุงในแต่ละปี',
							font: { size: 16, family: 'Mitr' }
						}
					},
				scales: {
					y: {
						beginAtZero: true
					}
				}
			}
		});
	</script>
	<!-- LineChart -->
	<script>
		var ctx = document.getElementById('reportLineChart').getContext('2d');
		var reportChart = new Chart(ctx, {
			type: 'line',
			data: {
				labels: <?php echo json_encode($dates); ?>,
				datasets: [{
					label: 'การแจ้งซ่อมบำรุงทั้งหมด',
					data: <?php echo json_encode($reportCounts); ?>,
					borderColor: '#00A9FF',
					backgroundColor: '#00A9FF59',
					fill: true,
				}]
			},
			options: {
				responsive: true,
				plugins: {
					title: {
						display: true,
						text: 'จำนวนการแจ้งซ่อมบำรุงรายวัน',
						font: { size: 16, family: 'Mitr' }
					}
				},
				scales: {
					x: {
						display: true,
						title: {
							display: true
						}
					},
					y: {
						display: true,
						title: {
							display: true,
							text: 'ครั้ง'
						},
						suggestedMin: 0
					},},
				elements: {
					line: {
						tension: 0.4,
					}
        		}
			}
		});
	</script>
    <script type="text/javascript" src="./js/admin.js"></script>
	<!-- modal -->
	<script>
		var modal = document.getElementById("myModal");
		var btnNews = document.querySelector(".btn-news");
		var closeBtn = document.querySelector(".close");

		btnNews.addEventListener("click", function() {
			modal.style.display = "block";
		});

		closeBtn.addEventListener("click", function() {
			modal.style.display = "none";
		});

		window.addEventListener("click", function(event) {
			if (event.target == modal) {
				modal.style.display = "none";
			}
		});
	</script>
	<!-- summernote -->
	<script>
      $('#summernote').summernote({
        placeholder: 'Hello stand alone ui',
        tabsize: 2,
        height: 150,
        toolbar: [
          ['style', ['style']],
          ['font', ['bold', 'underline', 'clear']],
          ['color', ['color']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['table', ['table']],
          ['insert', ['link']],
          ['view', ['help']]
        ]
      });
    </script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
	<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
	<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
	<!-- dataTable -->
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
			$('#news-table').DataTable();
		});
	</script>
</body>
</html>

