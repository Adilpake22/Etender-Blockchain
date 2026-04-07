<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/helpers/Auth.php';
require_once __DIR__ . '/../../app/helpers/Blockchain.php';
require_once __DIR__ . '/../../app/models/Bid.php';

Auth::requireJson('bidder');

$bidId  = intval($_POST['bid_id'] ?? 0);
$amount = floatval($_POST['amount'] ?? 0);
$secret = trim($_POST['secret'] ?? '');

if (!$bidId || !$amount || !$secret) { echo json_encode(['success'=>false,'message'=>'Bid ID, amount and secret required']); exit; }

$bid = Bid::find($bidId);
if (!$bid) { echo json_encode(['success'=>false,'message'=>'Bid not found']); exit; }
if ($bid['bidder_id'] != Auth::id()) { echo json_encode(['success'=>false,'message'=>'Access denied']); exit; }
if ($bid['status'] !== 'committed') { echo json_encode(['success'=>false,'message'=>'Bid already revealed']); exit; }

if (!Blockchain::verifyBidHash($amount, $secret, $bid['bid_hash'])) {
    echo json_encode(['success'=>false,'message'=>'Hash verification failed. Amount or secret does not match your original commitment.']); exit;
}

$bc = Blockchain::revealBid($bidId, $amount);
Bid::reveal($bidId, $amount, $bc['tx_hash']);
echo json_encode(['success'=>true,'tx_hash'=>$bc['tx_hash'],'message'=>'Bid revealed and verified successfully!']);
