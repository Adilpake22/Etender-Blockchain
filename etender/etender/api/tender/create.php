<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/helpers/Auth.php';
require_once __DIR__ . '/../../app/helpers/Blockchain.php';
require_once __DIR__ . '/../../app/models/Tender.php';

Auth::requireJson('admin');

$title    = trim($_POST['title'] ?? '');
$desc     = trim($_POST['description'] ?? '');
$category = trim($_POST['category'] ?? '');
$budget   = floatval($_POST['budget'] ?? 0);
$deadline = $_POST['deadline'] ?? '';
$reveal   = $_POST['reveal_deadline'] ?? '';

if (!$title || !$desc || !$budget || !$deadline || !$reveal) {
    echo json_encode(['success'=>false,'message'=>'All fields are required']); exit;
}
if (strtotime($reveal) <= strtotime($deadline)) {
    echo json_encode(['success'=>false,'message'=>'Reveal deadline must be after bid deadline']); exit;
}

$id = Tender::create(['title'=>$title,'description'=>$desc,'category'=>$category,'budget'=>$budget,'deadline'=>$deadline,'reveal_deadline'=>$reveal,'created_by'=>Auth::id()]);
$bc = Blockchain::publishTender($id, $title);
Tender::updateTxHash($id, $bc['tx_hash']);

echo json_encode(['success'=>true,'tender_id'=>$id,'tx_hash'=>$bc['tx_hash'],'message'=>'Tender published successfully!']);
