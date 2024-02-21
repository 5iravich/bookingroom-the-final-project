<?php
session_start();
require_once './config/db.php';

if (isset($_POST['add'])) {
    $email = $_POST["email"];
        $password = $_POST["password"];
        $c_password = $_POST["c_password"];
        $urole = $_POST["urole"];
        $gender = $_POST["gender"];
        $team_id = $_POST["team_id"];
        $firstname = $_POST["firstname"];
        $lastname = $_POST["lastname"];
        $position = $_POST["position"];
        $grouptech = $_POST["grouptech"];
        $tel = $_POST["tel"];

        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $imageFileType = $_FILES['image']['type'];

        if (!in_array($imageFileType, $allowedTypes)) {
            $_SESSION['error'] = 'รองรับสกุลไฟล์เฉพาะ JPEG, JPG, PNG เท่านั้น';
            header('location: user-report.php');
            exit();
        }
		$imageData = file_get_contents($_FILES['image']['tmp_name']);

        if (empty($firstname)) {
            $_SESSION['error'] = 'กรุณากรอกชื่อ';
            header("location: ./admin-add-team.php");
        } else if (empty($lastname)) {
            $_SESSION['error'] = 'กรุณากรอกนามสกุล';
            header("location: ./admin-add-team.php");
        } else if (empty($email)) {
            $_SESSION['error'] = 'กรุณากรอกอีเมล';
            header("location: ./admin-add-team.php");
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'รูปแบบอีเมลไม่ถูกต้อง';
            header("location: ./admin-add-team.php");
        } else if (!endsWith($email, "@ku.th")) {
            $_SESSION['error'] = 'อีเมลต้องเป็นโดเมน @ku.th';
            header("location: ./admin-add-team.php");
        } else if (empty($password)) {
            $_SESSION['error'] = 'กรุณากรอกรหัสผ่าน';
            header("location: ./admin-add-team.php");
        } else if (strlen($_POST['password']) < 9) {
            $_SESSION['error'] = 'รหัสผ่านต้องมีความยาวมากกว่า 9 ตัวอักษร';
            header("location: ./admin-add-team.php");
        } else if (empty($c_password)) {
            $_SESSION['error'] = 'กรุณายืนยันรหัสผ่าน';
            header("location: ./admin-add-team.php");
        } else if ($password != $c_password) {
            $_SESSION['error'] = 'รหัสผ่านไม่ตรงกัน';
            header("location: ./admin-add-team.php");
        } else {
            try {
                $check_email = $conn->prepare("SELECT email FROM teams WHERE email = :email");
                $check_email->bindParam(":email", $email);
                $check_email->execute();
                $row = $check_email->fetch(PDO::FETCH_ASSOC);
                
                if ($row['email'] == $email) {
                    $_SESSION['warning'] = "มีอีเมลนี้อยู่ในระบบแล้ว";
                    header("location: ./admin-add-team.php");
                } else if (!isset($_SESSION['error'])) {
                    $vpassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO teams (email, password, urole, gender, team_id, firstname, lastname, position, grouptech, tel, profile_img) 
                                VALUES (:email, :password, :urole, :gender, :team_id, :firstname, :lastname, :position, :grouptech, :tel, :profile_img)");
                    $stmt->bindParam(":email", $email);
                    $stmt->bindParam(":password", $vpassword);
                    $stmt->bindParam(":urole", $urole);
                    $stmt->bindParam(":gender", $gender);
                    $stmt->bindParam(":team_id", $team_id);
                    $stmt->bindParam(":firstname", $firstname);
                    $stmt->bindParam(":lastname", $lastname);
                    $stmt->bindParam(":position", $position);
                    $stmt->bindParam(":grouptech", $grouptech);
                    $stmt->bindParam(":tel", $tel);
                    $stmt->bindParam(":profile_img", $imageData, PDO::PARAM_LOB);
                    $stmt->execute();
                    $_SESSION['success'] = "เพิ่มข้อมูลเรียบร้อยแล้ว!";
                    header("location: ./admin-add-team.php");
                } else {
                    $_SESSION['error'] = "มีบางอย่างผิดพลาด";
                    header("location: ./admin-add-team.php");
                }
            } catch(PDOException $e) {
                echo $e->getMessage();
            }
        }
    }

function endsWith($string, $ending) {
    $len = strlen($ending);
    if ($len == 0) {
        return true;
    }
    return (substr($string, -$len) === $ending);
}

