<?php 
    session_start();
    require_once './config/db.php';
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <link rel="shortcut icon" type="image/svg" href="./img/logo.png"/>
        <title>KU-KPS Dormitory</title>
        <link rel="stylesheet" type="text/css" href="./css/register.css">
        <link href="https://fonts.googleapis.com/css?family=Mitr:600&display=swap" rel="stylesheet">
        <script src="https://kit.fontawesome.com/a81368914c.js"></script>
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        <img class="wave" src="./img/wave.png">
        <div class="container">
            <div class="img">
                <img src="./img/welcome.png">
            </div>
            <div class="login-content">
                <form id="bug" action="./register_db.php" method="post">
                    <h2 class="title">ลงทะเบียนเข้าใช้งาน</h2>
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
                    <div class="input-div one">
                    <div class="i"><i class="fas fa-user"></i></div>
                    <div class="div"><h5>อีเมล</h5><input type="email" class="input" name="email" pattern="[a-zA-Z0-9._%+-]+@ku\.th" required></div>
                    </div>
                    <div class="input-div pass">
                    <div class="i"> <i class="fas fa-lock"></i></div>
                    <div class="div"><h5>รหัสผ่าน</h5><input type="password" class="input" name="password" required></div>
                    </div>
                    <div class="input-div pass">
                    <div class="i"> <i class="fas fa-lock"></i></div>
                    <div class="div"><h5>ยืนยันรหัสผ่าน</h5><input type="password" class="input" name="c_password" required></div>
                    </div>
                    <h4 class="title">ข้อมูลส่วนตัว</h4>
                    <div class="profile-2colne">
                        <div class="select-div gen">
                            <div class="i-gen"><i class="fas fa-genderless"></i></div>
                            <div class="div-gen"><h5>เพศ</h5>
                                <select class="select-gender" name="gender" required>
                                    <option value="" hidden>เลือก</option>
                                    <option value="M">ชาย</option>
                                    <option value="F">หญิง</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-div one">
                            <div class="i"><i class="fas fa-address-card"></i></div>
                            <div class="div"><h5>รหัสประจำตัวนิสิต</h5><input type="text" class="input" name="stdid" maxlength="10" required></div>
                        </div> 
                    </div>
                    <div class="profile-2col">
                    <div class="input-div one">
                            <div class="i"><i class="fas fa-address-card"></i></div>
                            <div class="div"><h5>ชื่อ</h5><input type="text" class="input" name="firstname" required></div>
                        </div>
                        <div class="input-div one">
                            <div class="i"><i class="fas fa-address-card"></i></div>
                            <div class="div"><h5>นามสกุล</h5><input type="text" class="input" name="lastname" required></div>
                        </div>
                    </div>
                    <div class="profile-2col">
                        <div class="input-div one">
                            <div class="i"><i class="fas fa-phone fa-rotate-180"></i></div>
                            <div class="div"><h5>เบอร์โทรศัพท์ที่ติดต่อได้</h5><input type="tel" class="input" name="tel" maxlength="10" required></div>
                        </div>
                        <div class="input-div one">
                            <div class="i"><i class="fas fa-envelope"></i></div>
                            <div class="div"><h5>อีเมลที่ติดต่อได้</h5><input type="email" class="input" name="coemail" required></div>
                        </div>
                    </div>
                    <div class="profile-2col">
                        <div class="input-div one">
                            <div class="i"><i class="fas fa-address-card"></i></div>
                            <div class="div"><h5>ชื่อผู้ปกครอง</h5><input type="text" class="input" name="parentsname" required></div>
                        </div>
                        <div class="input-div one">
                            <div class="i"><i class="fas fa-circle"></i></div>
                            <div class="div"><h5>ความสัมพันธ์</h5><input type="text" class="input" name="relation" required></div>
                        </div>
                    </div>
                    <div class="input-div one">
                        <div class="i"><i class="fas fa-phone fa-rotate-180"></i></div>
                        <div class="div"><h5>เบอร์โทรผู้ปกครอง</h5><input type="tel" class="input" name="parentstel" maxlength="10" required></div>
                    </div>
                    <input type="submit" name="register" class="btn" value="สมัครสมาชิก">
                    <a class="register-btn" href="./index.php">เป็นสมาชิกแล้วใช่ไหม คลิ๊กที่นี่ เพื่อเข้าสู่ระบบ</a>
                </form>
            </div>
        </div>
        <script type="text/javascript" src="./js/index.js"></script>
        <script src="./js/upload.js"></script>
        <script>
            window.onload = function() {
                document.getElementById('bug').reset();
            };
        </script>
    </body>
</html>