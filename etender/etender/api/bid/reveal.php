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
$pin    = trim($_POST['pin'] ?? '');

if (!$bidId || !$amount || !$pin) {
    echo json_encode(['success'=>false,'message'=>'Bid ID, amount and PIN required']); exit;
}

if (!preg_match('/^\d{4}$/', $pin)) {
    echo json_encode(['success'=>false,'message'=>'PIN must be exactly 4 digits']); exit;
}

$bid = Bid::find($bidId);
if (!$bid) {
    echo json_encode(['success'=>false,'message'=>'Bid not found']); exit;
}
if ($bid['bidder_id'] != Auth::id()) {
    echo json_encode(['success'=>false,'message'=>'Access denied']); exit;
}
if ($bid['status'] !== 'committed') {
    echo json_encode(['success'=>false,'message'=>'Bid already revealed']); exit;
}

if (!Blockchain::verifyBidHash($amount, $pin, $bid['bid_hash'])) {
    echo json_encode(['success'=>false,'message'=>'Incorrect PIN or amount. They must match exactly what you entered when committing.']); exit;
}

$bc = Blockchain::revealBid($bidId, $amount);
Bid::reveal($bidId, $amount, $bc['tx_hash']);

echo json_encode([
    'success' => true,
    'tx_hash' => $bc['tx_hash'],
    'message' => 'Bid revealed and verified successfully!'
]);