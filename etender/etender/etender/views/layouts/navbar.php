<?php
// views/layouts/navbar.php
// Include this at the top of every page
// $activeNav = 'dashboard' | 'tenders' | 'evaluate' | 'audit' | 'new-tender'
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /etender/views/auth/login.php'); exit;
}
$role     = $_SESSION['user_role'];
$userName = $_SESSION['user_name'];
$activeNav = $activeNav ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'BlockTender' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6fb; }
        .navbar-brand { font-weight: 700; letter-spacing: -0.5px; }
        .table th { font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em; color: #6c757d; font-weight: 600; }
        .table td { vertical-align: middle; }
        .card { border-radius: 12px; }
        .card-header { border-radius: 12px 12px 0 0 !important; }
        .badge { font-weight: 500; }
        .btn { border-radius: 8px; }
        code { background: #f1f3f5; padding: 2px 6px; border-radius: 4px; font-size: 0.8em; color: #d63384; word-break: break-all; }
        .tx-hash { font-size: 0.75rem; color: #198754; }
        .stat-card { border-left: 4px solid; border-radius: 12px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="#">
            <i class="bi bi-shield-check me-2"></i>BlockTender
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                <?php if ($role === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link <?= $activeNav==='dashboard'?'active':'' ?>" href="/etender/views/admin/dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link <?= $activeNav==='new-tender'?'active':'' ?>" href="/etender/views/admin/create_tender.php"><i class="bi bi-plus-circle me-1"></i>New Tender</a></li>
                    <li class="nav-item"><a class="nav-link <?= $activeNav==='evaluate'?'active':'' ?>" href="/etender/views/admin/evaluate.php"><i class="bi bi-clipboard-check me-1"></i>Evaluate</a></li>
                <?php elseif ($role === 'bidder'): ?>
                    <li class="nav-item"><a class="nav-link <?= $activeNav==='dashboard'?'active':'' ?>" href="/etender/views/bidder/dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link <?= $activeNav==='tenders'?'active':'' ?>" href="/etender/views/bidder/tender_list.php"><i class="bi bi-list-ul me-1"></i>Browse Tenders</a></li>
                    <li class="nav-item"><a class="nav-link <?= $activeNav==='reveal'?'active':'' ?>" href="/etender/views/bidder/reveal_bid.php"><i class="bi bi-unlock me-1"></i>Reveal Bid</a></li>
                <?php elseif ($role === 'evaluator'): ?>
                    <li class="nav-item"><a class="nav-link" href="/etender/views/admin/evaluate.php"><i class="bi bi-clipboard-check me-1"></i>Evaluate Bids</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link <?= $activeNav==='audit'?'active':'' ?>" href="/etender/views/public/audit_trail.php"><i class="bi bi-link-45deg me-1"></i>Audit Trail</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-white text-primary"><?= ucfirst($role) ?></span>
                <span class="text-white-50 small d-none d-md-inline"><?= htmlspecialchars($userName) ?></span>
                <a href="/etender/api/auth/logout.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid px-4">
