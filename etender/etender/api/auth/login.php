<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/models/User.php';
require_once __DIR__ . '/../../app/helpers/Auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false,'message'=>'Invalid request']); exit; }

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) { echo json_encode(['success'=>false,'message'=>'Email and password required']); exit; }

$user = User::verify($email, $password);
if (!$user) { echo json_encode(['success'=>false,'message'=>'Invalid email or password']); exit; }

Auth::login($user);

$redirects = ['admin'=>'/etender/views/admin/dashboard.php','bidder'=>'/etender/views/bidder/dashboard.php','evaluator'=>'/etender/views/admin/evaluate.php'];
echo json_encode(['success'=>true,'role'=>$user['role'],'name'=>$user['name'],'redirect'=>$redirects[$user['role']]]);
