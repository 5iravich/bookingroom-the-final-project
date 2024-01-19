<?php 
    session_start();
    unset($_SESSION['user_login']);
    unset($_SESSION['admin_login']);
    unset($_SESSION['coadmin_login']);
    unset($_SESSION['technician_login']);
    header('location: index.php');
    exit();
?>