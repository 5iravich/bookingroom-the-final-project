<?php 
    session_start();
    require_once './config/db.php';

    if (!isset($_SESSION['user_login'])) {
        $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
        header('location: index.php');
		exit();
    }

	$user_id = $_SESSION['user_login'];

	try {
		$user_data = $conn->prepare("SELECT * FROM users WHERE stdid = :stdid");
		$user_data->bindParam(":stdid", $user_id);
		$user_data->execute();
		$user = $user_data->fetch(PDO::FETCH_ASSOC);
	} catch (PDOException $e) {
		$_SESSION['error'] = 'เกิดข้อผิดพลาดในการดึงข้อมูลผู้ใช้';
    	header('location: user-profile.php');
    	exit();
	}

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		$password = $_POST['password'];
        $c_password = $_POST['c_password'];

		if (empty($_POST['firstname']) || empty($_POST['lastname'])) {
			$_SESSION['error'] = 'กรุณากรอกชื่อหรือนามสกุล';
			header('location: user-profile.php');
			exit();
		}else if(empty($_POST['coemail'])){
			$_SESSION['error'] = 'กรุณากรอกอีเมลที่ติดต่อได้';
			header('location: user-profile.php');
			exit();
		}else if(empty($_POST['relation'])){
			$_SESSION['error'] = 'กรุณากรอกข้อมูล';
			header('location: user-profile.php');
			exit();
		}else if(empty($_POST['tel'])){
			$_SESSION['error'] = 'กรุณากรอกข้อมูล';
			header('location: user-profile.php');
			exit();
		}else if(empty($_POST['parentsname'])){
			$_SESSION['error'] = 'กรุณากรอกข้อมูล';
			header('location: user-profile.php');
			exit();
		}else if(empty($_POST['parentstel'])){
			$_SESSION['error'] = 'กรุณากรอกข้อมูล';
			header('location: user-profile.php');
			exit();
		}else if (!empty($password) && strlen($_POST['password']) < 9) {
            $_SESSION['error'] = 'รหัสผ่านต้องมีความยาวมากกว่า 9 ตัวอักษร';
            header("location: user-profile.php");
        }else if ($password != $c_password) {
            $_SESSION['error'] = 'รหัสผ่านไม่ตรงกัน';
            header("location: user-profile.php");
			exit();
        }
			$passwordHash = $user['password'];
			if (!empty($password) && $password !== $user['password']) {
				$passwordHash = password_hash($password, PASSWORD_DEFAULT);
			}
		try {
			// Update profile
			$update_user_data = $conn->prepare("UPDATE users SET gender = :gender, firstname = :firstname, coemail = :coemail, relation = :relation, 
											   lastname = :lastname, tel = :tel, parentsname = :parentsname, parentstel = :parentstel, password = :password WHERE stdid = :stdid");
			$update_user_data->bindParam(":gender", $_POST['gender']);
			$update_user_data->bindParam(":firstname", $_POST['firstname']);
			$update_user_data->bindParam(":coemail", $_POST['coemail']);
			$update_user_data->bindParam(":relation", $_POST['relation']);
			$update_user_data->bindParam(":lastname", $_POST['lastname']);
			$update_user_data->bindParam(":tel", $_POST['tel']);
			$update_user_data->bindParam(":parentsname", $_POST['parentsname']);
			$update_user_data->bindParam(":parentstel", $_POST['parentstel']);
			$update_user_data->bindParam(":stdid", $user_id);
			$update_user_data->bindParam(":password", $passwordHash);
	
			$update_user_data->execute();

			$_SESSION['success'] = 'บันทึกข้อมูลโปรไฟล์เรียบร้อยแล้ว';
			header('location: user-profile.php');
			exit();
		} catch (PDOException $e) {
			$_SESSION['error'] = 'เกิดข้อผิดพลาดในการอัปเดตข้อมูลโปรไฟล์';
        	header('location: user-profile.php');
        	exit();
		}
		}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<link rel="shortcut icon" type="image/svg" href="./img/logo.png"/>
	<title>Profile</title>
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" type="text/css" href="./css/user.css">
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
				<li class="nav-item-a active">
					<a href="./user-profile.php">โปรไฟล์</a>
				</li>
				<li class="nav-item-a">
					<a href="./user-booking.php">จองหอพักนิสิต</a>
				</li>
				<li class="nav-item-a">
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
					<li class="mobile-nav-item active">
						<a href="./user-profile.php">โปรไฟล์</a>
					</li>
					<li class="mobile-nav-item">
						<a href="./user-booking.php">จองหอพักนิสิต</a>
					</li>
					<li class="mobile-nav-item">
						<a href="./user-maintenance.php">แจ้งซ่อมบำรุง</a>
					</li>
					<li class="mobile-nav-item">
						<a href="./logout.php">ออกจากระบบ</a>
					</li>
				</ul>
			</div>
		</nav>
		<div class="container-profile">
			<form action="" method="POST">
				<div class="back-page">
					<a href="user.php" class="btn btn-secondary"><i class='bx bx-arrow-back' ></i> ย้อนกลับ</a>
				</div>
					<div class="icon-container">
					<img class="profile-icon"src="./img/profile.png" alt="profile">				
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
					<label for="stdid">รหัสประจำตัวนิสิต: </label>
					<input type="text" id="stdid" name="stdid" value="<?php echo $user['stdid']; ?>"readonly><br>
					<label for="gender">เพศ: </label>
						<select class="select-gender" name="gender" id="gender">
						<?php
						$userGender = $user['gender'];
						if ($userGender === 'M') {
							echo '<option value="M" selected>ชาย</option>';
							echo '<option value="F">หญิง</option>';
						} elseif ($userGender === 'F') {
							echo '<option value="M">ชาย</option>';
							echo '<option value="F" selected>หญิง</option>';
						} else {
							echo '<option value="" hidden>เลือกเพศ</option>';
							echo '<option value="M">ชาย</option>';
							echo '<option value="F">หญิง</option>';
						}
						?>
					</select><br>
					<label for="parentsname">ชื่อผู้ปกครอง: </label>
					<input type="text" id="parentsname" name="parentsname" value="<?php echo $user['parentsname']; ?>"><br>
					<label for="email">อีเมล: </label>
					<input type="text" id="email" name="email" value="<?php echo $user['email']; ?>"readonly><br>
				</div>
				
				<div class="column">
					<label for="firstname">ชื่อ: </label>
					<input type="text" id="firstname" name="firstname" value="<?php echo $user['firstname']; ?>"><br>
					<label for="tel">เบอร์โทรศัพท์: </label>
					<input type="text" id="tel" name="tel" value="<?php echo $user['tel']; ?>"><br>
					<label for="parentstel">เบอร์โทรผู้ปกครอง: </label>
					<input type="text" id="parentstel" name="parentstel" value="<?php echo $user['parentstel']; ?>"><br>
					<label for="password">รหัสผ่านใหม่: </label>
					<input type="password" id="password" name="password" placeholder="กรอกรหัสผ่านใหม่"><br>
				</div>

				<div class="column">
					<label for="lastname">นามสกุล: </label>
					<input type="text" id="lastname" name="lastname" value="<?php echo $user['lastname']; ?>"><br>
					<label for="coemail">อีเมลที่ติดต่อได้: </label>
					<input type="text" id="coemail" name="coemail" value="<?php echo $user['coemail']; ?>"><br>
					<label for="relation">ความสัมพันธ์: </label>
					<input type="text" id="relation" name="relation" value="<?php echo $user['relation']; ?>"><br>
					<label for="c_password">ยืนยันรหัสผ่านใหม่: </label>
					<input type="password" id="c_password" name="c_password" placeholder="ยืนยันรหัสผ่านอีกครั้ง"><br>
					<label class="since">เข้าร่วมเมื่อ: <?php echo $user['created_at']; ?></label>
				</div>

				<div class="clearfix"></div>
				<div class="button-container">
					<input type="submit" value="บันทึก" class="btn btn-primary">
				</div>
			</form>
		</div>
		<script type="text/javascript" src="./js/script.js"></script>
	</body>
</html>