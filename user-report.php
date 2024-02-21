<?php 
    session_start();
    require_once './config/db.php';
    if (!isset($_SESSION['user_login'])) {
        $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
        header('location: index.php');
		exit();
    }

	$userID = $_SESSION['user_login'];

	$query = "SELECT statuspay FROM bookings WHERE stdid = ? LIMIT 1";
	$stmt = $conn->prepare($query);
	$stmt->execute([$userID]);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	if (!$row || $row['statuspay'] !== 'confirmed') {
		$_SESSION['error'] = 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้ เนื่องจากไม่พบข้อมูลการจองในระบบหรือยังไม่ได้รับการยืนยันการชำระเงิน';
		header('location: user.php');
		exit();
	}
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$repairTime = $_POST['repair-time'];
		$repairType = $_POST['repair-type'];
		$repairSpecific = $_POST['repair-specific'];
		$repairObj = $_POST['repair-obj'];
		$repairQuan = $_POST['repair-quan'];
		$repairDesc = $_POST['repair-desc'];

		$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $imageFileType = $_FILES['image']['type'];

        if (!in_array($imageFileType, $allowedTypes)) {
            $_SESSION['error'] = 'รองรับสกุลไฟล์เฉพาะ JPEG, JPG, PNG เท่านั้น';
            header('location: user-report.php');
            exit();
        }

		$imageData = file_get_contents($_FILES['image']['tmp_name']);

		$query = "INSERT INTO reports (stdid, repair_time, repair_type, repair_specific, repair_obj, repair_quan, repair_desc, repair_img)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
		$stmt = $conn->prepare($query);

		if ($stmt->execute([$userID, $repairTime, $repairType, $repairSpecific, $repairObj, $repairQuan, $repairDesc, $imageData])) {
			$_SESSION['success'] = 'บันทึกข้อมูลการแจ้งซ่อมบำรุงเรียบร้อยแล้ว';
			header('location: user-report.php');
			exit();
		} else {
			$_SESSION['error'] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
			header('location: user-report.php');
			exit();
		}
	}
	
	$progressquery = "SELECT * FROM reports WHERE report_status = 'in progress' LIMIT 7";
	$progressstmt = $conn->prepare($progressquery);
	$progressstmt->execute();
	$inProgressReports = $progressstmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<link rel="shortcut icon" type="image/svg" href="./img/logo.png"/>
		<title>Maintenance Service</title>
		<link rel="stylesheet" type="text/css" href="./css/user.css">
		<link rel="stylesheet" type="text/css" href="./css/report.css">
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
			<li class="nav-item-mtn active">
				<a href="./user-report.php" class="fas fa-wrench"></a>
			</li>
			<li class="nav-item-mtn">
				<a href="./user-history.php" class="fas fa-list"></a>
			</li>
		</ul>
	<section class="report-section">
	<div class="container-report">
		<form action="" method="POST" enctype="multipart/form-data">
			<h1 class="card-title"><b>🛠️แบบฟอร์มแจ้งซ่อมบำรุง</b></h1>

			<?php if(isset($_SESSION['error'])) { ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $_SESSION['error'];
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php } ?>
            <?php if(isset($_SESSION['success'])) { ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $_SESSION['success'];
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php } ?>
            <?php if(isset($_SESSION['warning'])) { ?>
                <div class="alert alert-warning" role="alert">
                    <?php echo $_SESSION['warning'];
                        unset($_SESSION['warning']);
                    ?>
                </div>
            <?php } ?>

			<div class="report-column center">
				<label for="repair-time">ช่วงเวลาที่สะดวก: </label>
				<select class="repair-type" name="repair-time" id="repair-time" required>
					<option value="" hidden>เลือกช่วงเวลา</option>
					<option value="morning">ช่วงเช้า (09:00-12:00 น.)</option>
					<option value="afternoon">ช่วงบ่าย (13:00-16:00 น.)</option>
					<option value="evening">ช่วงเย็น (18:00-19:00 น.)</option>
					<option value="allday">ตลอดทั้งวัน</option>
				</select><br>
				<label for="repair-type">ประเภทของงานซ่อม: </label>
				<select class="repair-type" name="repair-type" id="repair-type" required >
					<option value="" hidden>เลือกประเภท</option>
					<option value="general">ซ่อมแซมทั่วไป</option>
					<option value="furniture">เฟอร์นิเจอร์</option>
					<option value="plumbing">ระบบประปา</option>
					<option value="electrical">ระบบไฟฟ้า</option>
					<option value="appliance">เครื่องใช้ไฟฟ้า</option>
				</select><br>
				<label for="repair-specific">ประเภทของปัญหา: </label>
				<select class="repair-type" name="repair-specific" id="repair-specific" required>
				</select><br>
					<label for="repair-obj">สิ่งของที่ชำรุด: </label>
					<input type="text" id="repair-obj" name="repair-obj" value="" required><br>
					<label for="repair-quan">จำนวนที่ชำรุด: </label>
					<input type="number" id="repair-quan" name="repair-quan" min="1" value="" required><br>
					<label for="repair-desc">รายละเอียด/ลักษณะของการชำรุด:</label><br>
        			<textarea id="repair-desc" name="repair-desc" rows="5" cols="35" required></textarea><br>
				</div>
				
				<div class="report-column">
				<span class="report-part">แนบรูปภาพประกอบ</span>
					<div class="drag-area" id="dragArea">
						<div id="beforeLoad">
							<div class="iconpic">
								<i class="fas fa-images"></i>
							</div>
							<span class="headerpayment" id='dragText'> ลากและวางไฟล์ </span>
							<span class="headerpayment">หรือ <span class="buttonpayment" id="chooseFile">เลือกไฟล์</span></span> 
							<input class="filepayment" type="file" name="image" id="imageUpload" accept="image/jpeg, image/jpg, image/png" required/>
							<span class="supportpayment">รองรับสกุลไฟล์: JPEG, JPG, PNG</span>
						</div>
					</div>
				</div>
				
				<div class="clearfix"></div>
				<div class="button-container">
					<input type="submit" value="ส่ง" class="btn btn-report">
				</div>
			</form>
		</div>
		<div class="container-report side">
		<h1 class="card-title"><b>🟢ตารางงานของช่างในขณะนี้</b></h1>
			<?php 
			function sortByDateDesc($a, $b) {
				return strtotime($b['report_time']) - strtotime($a['report_time']);
			}
			usort($inProgressReports, 'sortByDateDesc');
			foreach ($inProgressReports as $report) :
				$formattedReportDate = date('d/m/Y เวลา H:i น.', strtotime($report['report_time']));
				echo "<div class='tab'>". $formattedReportDate . " ช่างกำลังปฏิบัติงานซ่อมบำรุง</div>";
            endforeach; ?>
		</div>
		</section>
		<script>
        const repairTypeSelect = document.getElementById('repair-type');
        const specificRepairTypeSelect = document.getElementById('repair-specific');

        const repairOptions = {
			general: ['ประตูห้องพัก','ประตูห้องน้ำ','หน้าต่าง','ราวตากผ้า','กระเบี้องพื้นในห้อง','กระเบี้องพื้นในห้องน้ำ','เพดานห้อง','ผนังห้อง','อื่นๆ'],
			furniture: ['เตียงนอน','เบาะที่นอน','ตู้เสื้อผ้า','ชั้นวางของ','โต๊ะอ่านหนังสือ','เก้าอี้','กระจกเงา','อื่นๆ'],
            plumbing: ['ท่อ','ก๊อกน้ำ', 'ท่อระบายน้ำ', 'ฝักบัว','ระบบชักโครก','อ่างล้างหน้า','สายฉีดชำระ','อื่นๆ'],
            electrical: ['ไฟกลางห้อง 36 w.', 'ไฟห้องน้ำ 18 w.', 'ไฟระเบียง', 'สวิทซ์ไฟ','ปลั๊กไฟ','อื่นๆ'],
            appliance: ['เครื่องปรับอากาศ', 'รีโมทเครื่องปรับอากาศ', 'พัดลม', 'เครื่องทำน้ำอุ่น', 'อื่นๆ']
        };

        function updateSpecificRepairOptions() {
            const selectedRepairType = repairTypeSelect.value;
            const specificOptions = repairOptions[selectedRepairType] || [];

            specificRepairTypeSelect.innerHTML = '';

			const hiddenOption = document.createElement('option');
            hiddenOption.value = '';
            hiddenOption.textContent = 'เลือกปัญหา';
            hiddenOption.hidden = true;
            specificRepairTypeSelect.appendChild(hiddenOption);

            specificOptions.forEach(optionText => {
                const option = document.createElement('option');
                option.value = optionText;
                option.textContent = optionText;
                specificRepairTypeSelect.appendChild(option);
            });
        }
        repairTypeSelect.addEventListener('change', updateSpecificRepairOptions);

        updateSpecificRepairOptions();
		</script>
		<script type="text/javascript" src="./js/draganddrop.js"></script>
		<script type="text/javascript" src="./js/script.js"></script>
	</body>
</html>