<?php
$pageTitle = 'My Dashboard — BlockTender';
$activeNav = 'dashboard';
require_once __DIR__ . '/../../views/layouts/navbar.php';
if ($role !== 'bidder') { header('Location: /etender/views/auth/login.php'); exit; }
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 stat-card border-primary">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-file-check text-primary fs-3"></i>
                <div><div class="fs-3 fw-bold" id="stat-bids">—</div><div class="text-muted small">My Bids</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 stat-card border-warning">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-hourglass text-warning fs-3"></i>
                <div><div class="fs-3 fw-bold" id="stat-pending">—</div><div class="text-muted small">Pending Reveal</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 stat-card border-success">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-trophy text-success fs-3"></i>
                <div><div class="fs-3 fw-bold" id="stat-won">—</div><div class="text-muted small">Won</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 stat-card border-info">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-door-open text-info fs-3"></i>
                <div><div class="fs-3 fw-bold" id="stat-open">—</div><div class="text-muted small">Open Tenders</div></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>My Bid History</h6>
                <a href="/etender/views/bidder/tender_list.php" class="btn btn-primary btn-sm">Browse Tenders</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Tender</th><th>Bid Status</th><th>Amount</th><th>Blockchain</th></tr>
                    </thead>
                    <tbody id="bid-history">
                        <tr><td colspan="4" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm"></div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3"><h6 class="mb-0">Quick Actions</h6></div>
            <div class="card-body d-grid gap-2">
                <a href="/etender/views/bidder/tender_list.php" class="btn btn-outline-primary">
                    <i class="bi bi-list-ul me-2"></i>Browse & Bid on Tenders
                </a>
                <a href="/etender/views/bidder/reveal_bid.php" class="btn btn-outline-warning">
                    <i class="bi bi-unlock me-2"></i>Reveal My Committed Bid
                </a>
                <a href="/etender/views/public/audit_trail.php" class="btn btn-outline-secondary">
                    <i class="bi bi-link-45deg me-2"></i>View Blockchain Audit Trail
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
var SBADGE = {committed:'secondary',revealed:'info',evaluated:'warning',awarded:'success',rejected:'danger'};
function esc(s) { return $('<div>').text(s+'').html(); }

$.getJSON('/etender/api/bid/list.php', {mine:1}, function(res) {
    var data = res.data || [], committed=0, won=0;
    $.each(data, function(i,b) { if(b.status==='committed') committed++; if(b.status==='awarded') won++; });
    $('#stat-bids').text(data.length);
    $('#stat-pending').text(committed);
    $('#stat-won').text(won);

    if (!data.length) {
        $('#bid-history').html('<tr><td colspan="4" class="text-center py-4 text-muted">No bids yet. <a href="/etender/views/bidder/tender_list.php">Browse tenders</a></td></tr>');
        return;
    }
    var rows = '';
    $.each(data, function(i,b) {
        rows += '<tr>'
            +'<td class="small"><strong>'+esc(b.tender_title)+'</strong></td>'
            +'<td><span class="badge bg-'+SBADGE[b.status]+'">'+b.status+'</span></td>'
            +'<td>'+(b.amount?'₹'+Number(b.amount).toLocaleString('en-IN'):'<span class="text-muted">Hidden</span>')+'</td>'
            +'<td>'+(b.reveal_tx_hash?'<i class="bi bi-check-circle text-success"></i>':'<i class="bi bi-clock text-muted"></i>')+'</td>'
            +'</tr>';
    });
    $('#bid-history').html(rows);
});

$.getJSON('/etender/api/tender/list.php', {status:'open'}, function(res) {
    $('#stat-open').text(res.count||0);
});
</script>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
