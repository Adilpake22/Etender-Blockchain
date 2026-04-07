<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/helpers/Auth.php';
require_once __DIR__ . '/../../app/models/Bid.php';

Auth::requireJson();

if (isset($_GET['mine'])) {
    $bids = Bid::byBidder(Auth::id());
} elseif (!empty($_GET['tender_id'])) {
    if (!in_array(Auth::role(), ['admin','evaluator'])) { echo json_encode(['success'=>false,'message'=>'Access denied']); exit; }
    $bids = Bid::forTender(intval($_GET['tender_id']));
} else {
    echo json_encode(['success'=>false,'message'=>'Parameter required']); exit;
}
echo json_encode(['success'=>true,'data'=>$bids]);
