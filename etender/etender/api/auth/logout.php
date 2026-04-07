<?php
require_once __DIR__ . '/../../app/helpers/Auth.php';
Auth::logout();
header('Location: /etender/views/auth/login.php');
exit;
