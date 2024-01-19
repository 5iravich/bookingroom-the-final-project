<?php 
    session_start();
    require_once './config/db.php';
    if (!isset($_SESSION['user_login'])??isset($_SESSION['admin_login'])??isset($_SESSION['coadmin_login'])) {
        $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
        header('location: index.php');
		exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<link rel="shortcut icon" type="image/svg" href="https://sv1.picz.in.th/images/2023/10/27/ddsUZnt.png"/>
		<title>Services</title>
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
					<a href="./user.php"><img src="https://sv1.picz.in.th/images/2023/10/27/ddsUZnt.png" alt="Logo"></a>
				</li>
				<li class="nav-item-a active">
					<a href="./user.php">หน้าหลัก</a>
				</li>
				<li class="nav-item-a">
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
					<li class="mobile-nav-item active">
						<a href="./user.php">หน้าหลัก</a>
					</li>
					<li class="mobile-nav-item">
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
			<div class="card news">
				<h3 class='card-name'>📰 ข่าวประชาสัมพันธ์ทั่วไป</h3>
				<div class='news-container'>
					<div class='news-column'>
					<?php
						try {
							$sql = "SELECT * FROM posts ORDER BY created_at DESC LIMIT 10";
							$stmt = $conn->query($sql);

							while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
								$formattedCreatedAt = date(' ประกาศเมื่อ d/m/Y เวลา H:i น.', strtotime($row['created_at']));
								echo "<div class='news-article'>";
								echo "	<h4 class='news-header'>✨ " . $row['header'] . "</h4>";
								echo "		<p class='news-text'>" . $row['textbody'] . "</p>";
								echo "	<span class='news-date'><i class='bx bx-time'></i>" . $formattedCreatedAt . "</span>";
								echo "</div>";
							}
						} catch (PDOException $e) {
							echo "Error: " . $e->getMessage();
						}
					?>
					</div>
				</div>
			</div>
			<div class="card">
				<a class="box" href="./user-profile.php">
					<img src="https://sv1.picz.in.th/images/2023/10/27/ddsUtaZ.png" alt="profile">
					<div class="content">	
						<h2>01</h2>
						<h3>โปรไฟล์</h3>
					</div>
				</a>
			</div>
			<div class="card">
				<a class="box" href="./user-booking.php">
					<img src="https://sv1.picz.in.th/images/2023/10/27/ddsU1zI.png" alt="booking">
					<div class="content">
						<h2>02</h2>
						<h3>จองหอพักนิสิต</h3>
					</div>
				</a>
			</div>
			<div class="card">
				<a class="box" href="./user-maintenance.php">
					<img src="https://sv1.picz.in.th/images/2023/10/27/ddsUgWe.png" alt="maintain">
					<div class="content">
						<h2>03</h2>
						<h3>แจ้งซ่อมบำรุง</h3>
					</div>
				</a>
			</div>
		</div>
		<script type="text/javascript" src="./js/script.js"></script>
	</body>
</html>