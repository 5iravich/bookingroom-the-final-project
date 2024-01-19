<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<link rel="shortcut icon" type="image/svg" href="https://sv1.picz.in.th/images/2023/10/27/ddsUZnt.png"/>
	<title>KU-KPS Dormitory</title>
	<link rel="stylesheet" type="text/css" href="./css/index.css">
	<link href="https://fonts.googleapis.com/css?family=Mitr:600&display=swap" rel="stylesheet">
	<script src="https://kit.fontawesome.com/a81368914c.js"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
	<img class="wave" src="https://sv1.picz.in.th/images/2023/10/27/ddsU8rf.png">
	<div class="container">
		<div class="img">
			<img src="https://sv1.picz.in.th/images/2023/10/27/ddsUqbN.png">
		</div>
		<div class="login-content">
			<form id="bug" action="./login_db.php" method="post">
				<img src="https://sv1.picz.in.th/images/2023/10/27/ddsUZnt.png">
				<h2 class="title">ลงชื่อเข้าใช้งาน</h2>
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
           		<div class="input-div one">
           		   <div class="i">
           		   		<i class="fas fa-user"></i>
           		   </div>
           		   <div class="div">
           		   		<h5>อีเมล</h5>
           		   		<input type="email" class="input" name="email">
           		   </div>
           		</div>
           		<div class="input-div pass">
           		   <div class="i"> 
           		    	<i class="fas fa-lock"></i>
           		   </div>
           		   <div class="div">
           		    	<h5>รหัสผ่าน</h5>
           		    	<input type="password" class="input" name="password">
            	   </div>
            	</div>
            	<!-- <a class="forgotpass-btn" href="./forgot-password.php">ลืมรหัสผ่าน?</a> -->
            	<input type="submit" class="btn" name="login" value="เข้าสู่ระบบ">
                <a class="register-btn" href="./register.php">สมาชิกใหม่? ลงทะเบียนที่นี่</a>
            </form>
        </div>
    </div>
    <script type="text/javascript" src="./js/index.js"></script>
	<script>
		window.onload = function() {
    		document.getElementById('bug').reset();
		};
	</script>
</body>
</html>