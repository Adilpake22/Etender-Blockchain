<?php
$pageTitle = 'Admin Dashboard — BlockTender';
$activeNav = 'dashboard';
require_once __DIR__ . '/../../views/layouts/navbar.php';
if ($role !== 'admin') { header('Location: /etender/views/auth/login.php'); exit; }
?>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 stat-card border-primary">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-file-earmark-text text-primary fs-3"></i>
                <div><div class="fs-3 fw-bold" id="stat-total">—</div><div class="text-muted small">Total</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 stat-card border-success">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-door-open text-success fs-3"></i>
                <div><div class="fs-3 fw-bold" id="stat-open">—</div><div class="text-muted small">Open</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 stat-card border-warning">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-hourglass-split text-warning fs-3"></i>
                <div><div class="fs-3 fw-bold" id="stat-closed">—</div><div class="text-muted small">Closed</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 stat-card border-info">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-trophy text-info fs-3"></i>
                <div><div class="fs-3 fw-bold" id="stat-awarded">—</div><div class="text-muted small">Awarded</div></div>
            </div>
        </div>
    </div>
</div>

<!-- Tenders table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center py-3">
        <h5 class="mb-0"><i class="bi bi-list-ul me-2 text-primary"></i>All Tenders</h5>
        <div class="d-flex gap-2 flex-wrap">
            <input type="text" id="search" class="form-control form-control-sm" placeholder="Search..." style="width:180px">
            <select id="status-filter" class="form-select form-select-sm" style="width:130px">
                <option value="">All statuses</option>
                <option value="open">Open</option>
                <option value="closed">Closed</option>
                <option value="awarded">Awarded</option>
            </select>
            <a href="/etender/views/admin/create_tender.php" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>New Tender
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>Title</th><th>Category</th><th>Budget</th>
                        <th>Deadline</th><th>Bids</th><th>Status</th><th>Blockchain</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tbody">
                    <tr><td colspan="9" class="text-center py-5 text-muted">
                        <div class="spinner-border text-primary me-2"></div>Loading tenders...
                    </td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
var BADGES = {open:'success',closed:'warning',awarded:'primary',draft:'secondary'};

function esc(s) { return $('<div>').text(s).html(); }

function loadTenders() {
    $.ajax({
        url: '/etender/api/tender/list.php',
        data: { status: $('#status-filter').val(), search: $('#search').val() },
        dataType: 'json',
        success: function(res) {
            if (!res.success) return;
            var d = res.data, open=0, closed=0, awarded=0;
            $.each(d, function(i,t) {
                if (t.status==='open') open++;
                if (t.status==='closed') closed++;
                if (t.status==='awarded') awarded++;
            });
            $('#stat-total').text(d.length);
            $('#stat-open').text(open);
            $('#stat-closed').text(closed);
            $('#stat-awarded').text(awarded);

            if (!d.length) {
                $('#tbody').html('<tr><td colspan="9" class="text-center py-4 text-muted">No tenders found</td></tr>');
                return;
            }
            var rows = '';
            $.each(d, function(i, t) {
                var bcBadge = t.tx_hash
                    ? '<span class="badge bg-success"><i class="bi bi-link-45deg"></i> On-chain</span>'
                    : '<span class="badge bg-secondary">Pending</span>';
                var btns = '<a href="/etender/views/admin/evaluate.php?tender_id='+t.id+'" class="btn btn-outline-primary btn-sm">Evaluate</a>';
                if (t.status === 'open') {
                    btns += ' <button class="btn btn-outline-warning btn-sm ms-1 close-btn" data-id="'+t.id+'">Close</button>';
                }
                rows += '<tr>'
                    + '<td>'+t.id+'</td>'
                    + '<td><strong>'+esc(t.title)+'</strong><br><small class="text-muted">'+esc(t.category||'')+'</small></td>'
                    + '<td><span class="badge bg-light text-dark">'+esc(t.category||'—')+'</span></td>'
                    + '<td>₹'+Number(t.budget).toLocaleString('en-IN')+'</td>'
                    + '<td>'+new Date(t.deadline).toLocaleDateString('en-IN')+'</td>'
                    + '<td><span class="badge bg-secondary">'+t.bid_count+'</span></td>'
                    + '<td><span class="badge bg-'+( BADGES[t.status]||'secondary')+' text-capitalize">'+t.status+'</span></td>'
                    + '<td>'+bcBadge+'</td>'
                    + '<td>'+btns+'</td>'
                    + '</tr>';
            });
            $('#tbody').html(rows);
        },
        error: function() {
            $('#tbody').html('<tr><td colspan="9" class="text-center py-4 text-danger">Failed to load tenders. Check your database connection.</td></tr>');
        }
    });
}

$(document).on('click', '.close-btn', function() {
    var id = $(this).data('id');
    if (!confirm('Close this tender? Bidders will enter the reveal phase.')) return;
    var btn = $(this).prop('disabled', true).text('Closing...');
    $.post('/etender/api/tender/close.php', {tender_id: id}, function(res) {
        alert(res.message);
        loadTenders();
    }, 'json').fail(function() {
        alert('Error closing tender.');
        btn.prop('disabled', false).text('Close');
    });
});

var t;
$('#search').on('input', function() { clearTimeout(t); t = setTimeout(loadTenders, 400); });
$('#status-filter').on('change', loadTenders);
$(document).ready(loadTenders);
setInterval(loadTenders, 20000);
</script>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
