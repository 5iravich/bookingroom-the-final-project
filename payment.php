<?php 
    session_start();
    require_once './config/db.php';
    if (!isset($_SESSION['user_login'])) {
        $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
        header('location: index.php');
        exit();
    }

    $userID = $_SESSION['user_login'];
    $sql = "SELECT * FROM bookings WHERE stdid = :stdid";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':stdid', $userID, PDO::PARAM_INT);
    $stmt->execute();

    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$booking) {
        $_SESSION['error'] = 'ไม่พบข้อมูลการจอง!';
        header('location: user-booking.php');
        exit(); 
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
        
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $imageFileType = $_FILES['image']['type'];
        if (!in_array($imageFileType, $allowedTypes)) {
            $_SESSION['error'] = 'รองรับสกุลไฟล์เฉพาะ JPEG, JPG, PNG เท่านั้น';
            header('location: payment.php');
            exit();
        }
        
        $imageData = file_get_contents($_FILES['image']['tmp_name']);

        // Update the slip_image column in the bookings table
        $updateSql = "UPDATE bookings SET slip_image = :slip_image WHERE stdid = :stdid";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bindParam(':slip_image', $imageData, PDO::PARAM_LOB);
        $updateStmt->bindParam(':stdid', $userID, PDO::PARAM_INT);
        if ($updateStmt->execute()) {
            $_SESSION['success'] = 'อัปโหลดหลักฐานการชำระเงินสำเร็จ!';
            header('location: user-booking.php');
            exit();
        } else {
            $_SESSION['error'] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล!';
            header('location: payment.php');
            exit();
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <link rel="shortcut icon" type="image/svg" href="https://sv1.picz.in.th/images/2023/10/27/ddsUZnt.png"/>
        <title>Attach Slip</title>
        <link rel="stylesheet" type="text/css" href="./css/payment.css">
        <link href="https://fonts.googleapis.com/css?family=Mitr:600&display=swap" rel="stylesheet">
        <script src="https://kit.fontawesome.com/a81368914c.js"></script>
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body class = "paymentpage">
        <div class="paymantcontainer">
            <h3 class = "paymenth3">การชำระเงิน</h3>
                <div class="payment-part">
                    <div class="payment-column">
                        <span class="span-pay">บัญชีธนาคาร: กสิกรไทย</span><br>
                        <span class="span-pay">เลขที่บัญชี: 066-1-34791-0</span>
                        <span class="attitle">หรือสแกน QR Code Payment </span>
                        <div class="attitle">
                            <img class="QR" src="https://sv1.picz.in.th/images/2023/10/27/ddsUYzb.png" alt="QR">
                        </div>
                    </div>
                    <div class="payment-column">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <span class="attitle">อัพโหลดหลักฐานการชำระเงิน</span>
                            <div class="drag-area" id="dragArea">
                                <div id="beforeLoad">
                                    <div class="iconpic">
                                        <i class="fas fa-images"></i>
                                    </div>
                                    <span class="headerpayment" id='dragText'> ลากและวางไฟล์ </span>
                                    <span class="headerpayment">หรือ <span class="buttonpayment" id="chooseFile">เลือกไฟล์</span></span> 
                                    <input class="filepayment" type="file" name="image" id="imageUpload" accept=".jpg, .jpeg, .png" required/>
                                    <span class="supportpayment">รองรับสกุลไฟล์: JPEG, JPG, PNG</span>
                                </div>
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
                    </div>
                </div>
            <div class="Btnformpay">
                <input class="btnUpPay" id="uploadBtn" type="submit" value="อัปโหลด"/>
            </div>
            <a href="user-booking.php" class="back-button"><i class="fas fa-arrow-left"></i> กลับ</a>
        </div>
        <script type="text/javascript" src="./js/draganddrop.js"></script>
    </body>
</html>
