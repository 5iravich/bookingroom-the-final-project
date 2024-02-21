<?php 
    session_start();
    require_once './config/db.php';
    if (!isset($_SESSION['technician_login'])) {
        $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
        header('location: index.php');
		exit();
    }	
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<link rel="shortcut icon" type="image/svg" href="./img/logo.png"/>
	<title>Menus</title>
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" type="text/css" href="./css/technician.css">
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
				<li class="nav-item-a active">
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
				<li class="nav-item-a">
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
					<li class="mobile-nav-item active">
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
					<li class="mobile-nav-item">
						<a href="./technician-summary.php">สรุปภาพรวม</a>
					</li>
					<li class="mobile-nav-item">
						<a href="./logout.php">ออกจากระบบ</a>
					</li>
				</ul>
			</div>
		</nav>
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
            	<?php 
                    echo $_SESSION['warning'];
                    unset($_SESSION['warning']);
            	?>
        	</div>
        <?php } ?>
		<div class="container">
			<div class="card">
				<a class="box" href="./technician-profile.php">
					<img src="./img/profile.png" alt="profile">
					<div class="content">	
						<h2>01</h2>
						<h3>โปรไฟล์</h3>
					</div>
				</a>
			</div>
			<div class="card">
				<a class="box" href="./technician-tasks.php">
					<img src="./img/work.png" alt="tasks">
					<div class="content">	
						<h2>02</h2>
						<h3>งานของฉัน</h3>
					</div>
				</a>
			</div>

			<div class="card">
				<a class="box" href="./technician-history.php">
					<img src="./img/10.png" alt="history">
					<div class="content">
						<h2>03</h2>
						<h3>ประวัติงานซ่อมบำรุง</h3>
					</div>
				</a>
			</div>

			<div class="card">
				<a class="box" href="./technician-list-withdrawals.php">
					<img src="./img/bar.png" alt="withdrawals">
					<div class="content">
						<h2>04</h2>
						<h3>ประวัติการเบิกวัสดุอุปกรณ์</h3>
					</div>
				</a>
			</div>

			<div class="card">
				<a class="box" href="./technician-summary.php">
					<img src="./img/Chart.png" alt="summary">
					<div class="content">
						<h2>05</h2>
						<h3>สรุปภาพรวม</h3>
					</div>
				</a>
			</div>
		</div>
		<script type="text/javascript" src="./js/script.js"></script>
	</body>
</html>