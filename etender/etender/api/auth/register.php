<?php
// api/auth/register.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/models/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false,'message'=>'Invalid request']); exit; }

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$pass    = $_POST['password'] ?? '';
$company = trim($_POST['company_name'] ?? '');
$phone   = trim($_POST['phone'] ?? '');

if (!$name || !$email || !$pass) { echo json_encode(['success'=>false,'message'=>'Name, email and password required']); exit; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo json_encode(['success'=>false,'message'=>'Invalid email']); exit; }
if (strlen($pass) < 6) { echo json_encode(['success'=>false,'message'=>'Password must be at least 6 characters']); exit; }
if (User::emailExists($email)) { echo json_encode(['success'=>false,'message'=>'Email already registered']); exit; }

User::create(['name'=>$name,'email'=>$email,'password'=>$pass,'company_name'=>$company,'phone'=>$phone]);
echo json_encode(['success'=>true,'message'=>'Account created! Please login.']);


if (!$name || !$email || !$pass) { echo json_encode(['success'=>false,'message'=>'Name, email and password required']); exit; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo json_encode(['success'=>false,'message'=>'Invalid email']); exit; }
if (strlen($pass) < 6) { echo json_encode(['success'=>false,'message'=>'Password must be at least 6 characters']); exit; }
if (User::emailExists($email)) { echo json_encode(['success'=>false,'message'=>'Email already registered']); exit; }

User::create(['name'=>$name,'email'=>$email,'password'=>$pass,'company_name'=>$company,'phone'=>$phone]);
echo json_encode(['success'=>true,'message'=>'Account created! Please login.']);
