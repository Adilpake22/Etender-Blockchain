<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['user_role'];
    $map  = ['admin'=>'/etender/views/admin/dashboard.php','bidder'=>'/etender/views/bidder/dashboard.php','evaluator'=>'/etender/views/admin/evaluate.php'];
    header('Location: ' . ($map[$role] ?? '/etender/views/auth/login.php'));
} else {
    header('Location: /etender/views/auth/login.php');
}
exit;
