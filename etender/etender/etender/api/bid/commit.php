<?php
// api/bid/commit.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/helpers/Auth.php';
require_once __DIR__ . '/../../app/helpers/Blockchain.php';
require_once __DIR__ . '/../../app/models/Tender.php';
require_once __DIR__ . '/../../app/models/Bid.php';

Auth::requireJson('bidder');

$tenderId = intval($_POST['tender_id'] ?? 0);
$amount   = floatval($_POST['amount'] ?? 0);
$secret   = trim($_POST['secret'] ?? '');

if (!$tenderId || !$amount || !$secret) { echo json_encode(['success'=>false,'message'=>'All fields required']); exit; }

$tender = Tender::find($tenderId);
if (!$tender || $tender['status'] !== 'open') { echo json_encode(['success'=>false,'message'=>'Tender is not open for bidding']); exit; }
if (strtotime($tender['deadline']) < time()) { echo json_encode(['success'=>false,'message'=>'Bidding deadline has passed']); exit; }
if (Bid::existsForTender($tenderId, Auth::id())) { echo json_encode(['success'=>false,'message'=>'You have already submitted a bid for this tender']); exit; }

$bidHash  = Blockchain::createBidHash($amount, $secret);
$bc       = Blockchain::commitBid(0, $bidHash);
$bidId    = Bid::create(['tender_id'=>$tenderId,'bidder_id'=>Auth::id(),'bid_hash'=>$bidHash,'commit_tx_hash'=>$bc['tx_hash']]);

echo json_encode(['success'=>true,'bid_id'=>$bidId,'bid_hash'=>$bidHash,'tx_hash'=>$bc['tx_hash'],'message'=>'Bid committed successfully! Save your secret key.']);
