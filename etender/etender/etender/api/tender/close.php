<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/helpers/Auth.php';
require_once __DIR__ . '/../../app/helpers/Blockchain.php';
require_once __DIR__ . '/../../app/models/Tender.php';

Auth::requireJson('admin');
$id = intval($_POST['tender_id'] ?? 0);
if (!$id) { echo json_encode(['success'=>false,'message'=>'Tender ID required']); exit; }

$tender = Tender::find($id);
if (!$tender || $tender['status'] !== 'open') { echo json_encode(['success'=>false,'message'=>'Tender not found or not open']); exit; }

$bc = Blockchain::closeTender($id);
Tender::updateStatus($id, 'closed');
echo json_encode(['success'=>true,'tx_hash'=>$bc['tx_hash'],'message'=>'Tender closed successfully']);
