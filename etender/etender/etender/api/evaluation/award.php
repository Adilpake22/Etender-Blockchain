<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/helpers/Auth.php';
require_once __DIR__ . '/../../app/helpers/Blockchain.php';
require_once __DIR__ . '/../../app/models/Tender.php';
require_once __DIR__ . '/../../app/models/Bid.php';

Auth::requireJson('admin');

$tenderId = intval($_POST['tender_id'] ?? 0);
$bidId    = intval($_POST['bid_id'] ?? 0);
if (!$tenderId || !$bidId) { echo json_encode(['success'=>false,'message'=>'Tender ID and Bid ID required']); exit; }

$bid = Bid::find($bidId);
if (!$bid) { echo json_encode(['success'=>false,'message'=>'Bid not found']); exit; }

$bc = Blockchain::awardTender($tenderId, $bid['bidder_name']);
Bid::award($bidId);
Bid::rejectOthers($tenderId, $bidId);
Tender::setAwarded($tenderId);

echo json_encode(['success'=>true,'tx_hash'=>$bc['tx_hash'],'winner'=>$bid['bidder_name'],'message'=>'Tender awarded successfully!']);
