<?php
// api/tender/list.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/models/Tender.php';

$tenders = Tender::all([
    'status'   => $_GET['status']   ?? '',
    'category' => $_GET['category'] ?? '',
    'search'   => $_GET['search']   ?? '',
]);
echo json_encode(['success'=>true,'data'=>$tenders,'count'=>count($tenders)]);
