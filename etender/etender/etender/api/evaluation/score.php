<?php
// api/evaluation/score.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/helpers/Auth.php';
require_once __DIR__ . '/../../app/models/Bid.php';

Auth::requireJson('admin','evaluator');

$bidId = intval($_POST['bid_id'] ?? 0);
$tech  = intval($_POST['technical_score'] ?? 0);
$fin   = intval($_POST['financial_score'] ?? 0);
$notes = trim($_POST['notes'] ?? '');

if (!$bidId || $tech<0||$tech>100||$fin<0||$fin>100) { echo json_encode(['success'=>false,'message'=>'Valid bid ID and scores 0-100 required']); exit; }

$bid = Bid::find($bidId);
if (!$bid || $bid['status'] !== 'revealed') { echo json_encode(['success'=>false,'message'=>'Bid not found or not revealed yet']); exit; }

Bid::score($bidId, $tech, $fin, $notes);
$final = ($tech * 0.4) + ($fin * 0.6);
echo json_encode(['success'=>true,'final_score'=>$final,'message'=>'Bid scored successfully']);
