<?php 
    session_start();
    require_once './config/db.php';

    if (isset($_POST['login'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        if (empty($email)) {
            $_SESSION['error'] = 'กรุณากรอกอีเมล';
            header("location: ./index.php");
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'รูปแบบอีเมลไม่ถูกต้อง';
            header("location: ./index.php");
        } else if (empty($password)) {
            $_SESSION['error'] = 'กรุณากรอกรหัสผ่าน';
            header("location: ./index.php");
        } else if (strlen($_POST['password']) > 20 || strlen($_POST['password']) < 5) {
            $_SESSION['error'] = 'รหัสผ่านไม่ถูกต้อง';
            header("location: ./index.php");
        } else {
            try {
                $check_data = $conn->prepare("SELECT * FROM teams WHERE email = :email");
                $check_data->bindParam(":email", $email);
                $check_data->execute();
                $row = $check_data->fetch(PDO::FETCH_ASSOC);

                if ($check_data->rowCount() > 0) {

                    if ($email == $row['email']) {
                        if (password_verify($password, $row['password'])) {

                            $reset_attempts = $conn->prepare("UPDATE teams SET login_attempts = 0 WHERE email = :email");
                            $reset_attempts->bindParam(":email", $email);
                            $reset_attempts->execute();

                            if ($row['urole'] == 'admin') {
                                $_SESSION['admin_login'] = $row['team_id'];
                                header("location: ./admin.php");
                            }
                            elseif ($row['urole'] == 'coadmin') {
                                $_SESSION['coadmin_login'] = $row['team_id'];
                                header("location: ./admin.php");
                            }
                            elseif ($row['urole'] == 'technician') {
                                $_SESSION['technician_login'] = $row['team_id'];
                                header("location: ./technician.php");
                            } else {
                                $_SESSION['error'] = "ไม่พบข้อมูลผู้ใช้ในระบบ";
                                header("location: ./index.php");
                            }
                        } else {
                            $update_attempts = $conn->prepare("UPDATE users SET login_attempts = login_attempts + 1 WHERE email = :email");
                            $update_attempts->bindParam(":email", $email);
                            $update_attempts->execute();

                            $_SESSION['error'] = 'รหัสผ่านผิด';
                            header("location: ./index.php");
                        }
                    } else {
                        $_SESSION['error'] = 'อีเมลผิด';
                        header("location: ./index.php");
                    }
                } else {
                    $check_user = $conn->prepare("SELECT * FROM users WHERE email = :email");
                    $check_user->bindParam(":email", $email);
                    $check_user->execute();
                    $user_row = $check_user->fetch(PDO::FETCH_ASSOC);

                    if ($check_user->rowCount() > 0) {
                        if (password_verify($password, $user_row['password'])) {
                            $reset_attempts = $conn->prepare("UPDATE users SET login_attempts = 0 WHERE email = :email");
                            $reset_attempts->bindParam(":email", $email);
                            $reset_attempts->execute();
                            $_SESSION['user_login'] = $user_row['stdid'];
                            header("location: ./user.php");
                        } else {
                            $_SESSION['error'] = 'รหัสผ่านผิด';
                            header("location: ./index.php");
                        }
                    } else {
                        $_SESSION['error'] = "ไม่พบข้อมูลผู้ใช้ในระบบ";
                        header("location: ./index.php");
                    }
                }
            } catch(PDOException $e) {
                echo $e->getMessage();
            }
        }
    }
?>