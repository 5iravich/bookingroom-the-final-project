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

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		$password = $_POST['password'];
        $c_password = $_POST['c_password'];

		if (empty($_POST['firstname']) || empty($_POST['lastname'])) {
			$_SESSION['error'] = 'กรุณากรอกชื่อหรือนามสกุล';
			header('location: admin-profile.php');
			exit();
		}else if (!empty($password) && strlen($_POST['password']) < 9) {
            $_SESSION['error'] = 'รหัสผ่านต้องมีความยาวมากกว่า 9 ตัวอักษร';
            header("location: admin-profile.php");
        }else if ($password != $c_password) {
            $_SESSION['error'] = 'รหัสผ่านไม่ตรงกัน';
            header("location: admin-profile.php");
			exit();
        }else if(empty($_POST['tel'])){
			$_SESSION['error'] = 'กรุณากรอกข้อมูล';
			header('location: admin-profile.php');
			exit();
		}else if(empty($_POST['position'])){
			$_SESSION['error'] = 'กรุณากรอกข้อมูล';
			header('location: admin-profile.php');
			exit();
		}
			$passwordHash = $team['password'];
			if (!empty($password) && $password !== $team['password']) {
				$passwordHash = password_hash($password, PASSWORD_DEFAULT);
			}
			try {
				$update_team_data = $conn->prepare("UPDATE teams SET gender = :gender, firstname = :firstname, position = :position, grouptech = :grouptech, 
                                        lastname = :lastname, tel = :tel , password = :password WHERE team_id = :team_id");
				$update_team_data->bindParam(":gender", $_POST['gender']);
				$update_team_data->bindParam(":firstname", $_POST['firstname']);
				$update_team_data->bindParam(":position", $_POST['position']);
				$update_team_data->bindParam(":grouptech", $_POST['grouptech']);
				$update_team_data->bindParam(":lastname", $_POST['lastname']);
				$update_team_data->bindParam(":tel", $_POST['tel']);
				$update_team_data->bindParam(":team_id", $team_id);
				$update_team_data->bindParam(":password", $passwordHash);
				$update_team_data->execute();
				$_SESSION['success'] = 'บันทึกข้อมูลโปรไฟล์เรียบร้อยแล้ว';
				header('location: admin-profile.php');
				exit();
			} catch (PDOException $e) {
				$_SESSION['error'] = 'เกิดข้อผิดพลาดในการอัปเดตข้อมูลโปรไฟล์';
				header('location: admin-profile.php');
				exit();
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
			<li><a href="./admin-dorms-rooms.php"><i class='bx bxs-building' ></i><span class="text">อาคารและห้องพัก</span></a></li>
            <li><a href="./admin-storage.php"><i class='bx bxs-cylinder' ></i><span class="text">คลังวัสดุ/อุปกรณ์</span></a></li>
			<li><a href="./admin-add-team.php"><i class='bx bxs-group' ></i><span class="text">ทีมงาน/ช่างซ่อมบำรุง</span></a></li>
			<li><a href="./admin-users.php"><i class='bx bxs-group' ></i><span class="text">จัดการสมาชิก</span></a></li>
		</ul>
		<ul class="side-menu bottom">
			<li class="active"><a href="./admin-profile.php"><i class='bx bxs-id-card' ></i><span class="text">ข้อมูลของฉัน</span></a></li>
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
					<h1>โปรไฟล์</h1>
					<ul class="breadcrumb">
						<li>
							<a class="active" href="./admin.php">Dashboard</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a  href="./admin-profile.php">โปรไฟล์</a>
						</li>
					</ul>
				</div>
			</div>
            <div class="container-profile">
			<form action="" method="POST">
				<div class="icon-container">
					<?php
						$profile_img_data = base64_encode($team['profile_img']);
						$profile_img_src = 'data:image/jpeg;base64,' . $profile_img_data;
						echo '<img class="profile-icon" src="' . $profile_img_src . '" alt="profile">';
					?>
				</div>
				<h1 class="card-title">โปรไฟล์ของฉัน</h1><br>
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

				<div class="column">
					<label for="gender">เพศ: </label>
					<select class="select-gender" name="gender" id="gender">
						<?php
						$teamGender = $team['gender'];
						if ($teamGender === 'M') {
							echo '<option value="M" selected>ชาย</option>';
							echo '<option value="F">หญิง</option>';
						} elseif ($teamGender === 'F') {
							echo '<option value="M">ชาย</option>';
							echo '<option value="F" selected>หญิง</option>';
						} else {
							echo '<option value="" hidden>เลือกเพศ</option>';
							echo '<option value="M">ชาย</option>';
							echo '<option value="F">หญิง</option>';
						}
					?>
					</select><br>
					<label for="firstname">ชื่อ: </label>
					<input type="text" id="firstname" name="firstname" value="<?php echo $team['firstname']; ?>"><br>
					<label for="email">อีเมล: </label>
					<input type="text" id="email" name="email" value="<?php echo $team['email']; ?>"readonly><br>
				</div>
				
				<div class="column">
					<label for="team_id">รหัสประจำตัว: </label>
					<input type="text" id="team_id" name="team_id" value="<?php echo $team['team_id']; ?>"readonly><br>
					<label for="lastname">นามสกุล: </label>
					<input type="text" id="lastname" name="lastname" value="<?php echo $team['lastname']; ?>"><br>
					<label for="password">รหัสผ่านใหม่: </label>
					<input type="password" id="password" name="password" placeholder="กรอกรหัสผ่านใหม่"><br>
				</div>

				<div class="column">
					<label for="position">ตำแหน่ง: </label>
					<input type="text" id="position" name="position" value="<?php echo $team['position']; ?>"><br>
					<label for="tel">เบอร์โทรศัพท์: </label>
					<input type="text" id="tel" name="tel" value="<?php echo $team['tel']; ?>"><br>
					<label for="c_password">ยืนยันรหัสผ่านใหม่: </label>
					<input type="password" id="c_password" name="c_password" placeholder="ยืนยันรหัสผ่านอีกครั้ง"><br>
					<label class="since">เข้าร่วมเมื่อ: <?php echo $team['created_at']; ?></label>
				</div>

				<div class="clearfix"></div>
				<div class="button-container">
					<input type="submit" value="บันทึก" class="btn btn-primary">
				</div>
			</form>
		</div>
		</main>
	</section>
    <script type="text/javascript" src="./js/admin.js"></script>
</body>
</html>

