<?php
$pageTitle = 'Evaluate Bids — BlockTender';
$activeNav = 'evaluate';
require_once __DIR__ . '/../../views/layouts/navbar.php';
if (!in_array($role, ['admin','evaluator'])) { header('Location: /etender/views/auth/login.php'); exit; }
$tenderId = intval($_GET['tender_id'] ?? 0);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0"><i class="bi bi-clipboard-check me-2 text-primary"></i>Bid Evaluation</h5>
    <div class="d-flex align-items-center gap-2">
        <label class="text-muted small">Select tender:</label>
        <select id="tender-select" class="form-select form-select-sm" style="width:300px">
            <option value="">— Loading tenders —</option>
        </select>
    </div>
</div>

<div id="alert-box"></div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Bidder</th><th>Company</th><th>Amount</th><th>Status</th>
                        <th>Tech (40%)</th><th>Fin (60%)</th><th>Final</th><th>Blockchain</th><th>Action</th>
                    </tr>
                </thead>
                <tbody id="bids-tbody">
                    <tr><td colspan="9" class="text-center py-5 text-muted">Select a tender to view bids</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Score Modal -->
<div class="modal fade" id="scoreModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-star me-2"></i>Score Bid</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="score-form">
                    <input type="hidden" id="score-bid-id" name="bid_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Technical score (0–100) <span class="text-muted small">— 40% weight</span></label>
                        <input type="number" name="technical_score" class="form-control" min="0" max="100" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Financial score (0–100) <span class="text-muted small">— 60% weight</span></label>
                        <input type="number" name="financial_score" class="form-control" min="0" max="100" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="save-score">Save Score</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
var scoreModal = new bootstrap.Modal(document.getElementById('scoreModal'));
var currentTenderId = <?= $tenderId ?: 'null' ?>;
var SBADGE = {committed:'secondary',revealed:'info',evaluated:'warning',awarded:'success',rejected:'danger'};

function esc(s) { return $('<div>').text(s+'').html(); }

// Load closed tenders into select
$.getJSON('/etender/api/tender/list.php', {status:'closed'}, function(res) {
    var opts = '<option value="">— Select closed tender —</option>';
    $.each(res.data, function(i,t) {
        opts += '<option value="'+t.id+'" '+(t.id==currentTenderId?'selected':'')+'>'+esc(t.title)+' (#'+t.id+')</option>';
    });
    // also add open tenders for preview
    $.getJSON('/etender/api/tender/list.php', {status:'awarded'}, function(r2) {
        $.each(r2.data, function(i,t) {
            opts += '<option value="'+t.id+'" '+(t.id==currentTenderId?'selected':'')+'>'+esc(t.title)+' — [Awarded]</option>';
        });
        $('#tender-select').html(opts);
        if (currentTenderId) loadBids(currentTenderId);
    });
});

$('#tender-select').on('change', function() {
    currentTenderId = $(this).val();
    if (currentTenderId) loadBids(currentTenderId);
    else $('#bids-tbody').html('<tr><td colspan="9" class="text-center py-5 text-muted">Select a tender</td></tr>');
});

function loadBids(tid) {
    $('#bids-tbody').html('<tr><td colspan="9" class="text-center py-3"><div class="spinner-border spinner-border-sm"></div> Loading...</td></tr>');
    $.getJSON('/etender/api/bid/list.php', {tender_id: tid}, function(res) {
        if (!res.data || !res.data.length) {
            $('#bids-tbody').html('<tr><td colspan="9" class="text-center py-5 text-muted">No bids found for this tender</td></tr>');
            return;
        }
        var rows = '';
        $.each(res.data, function(i, b) {
            var amount = b.amount ? '₹'+Number(b.amount).toLocaleString('en-IN') : '<span class="text-muted">Not revealed</span>';
            var tx = b.reveal_tx_hash ? '<span class="badge bg-success"><i class="bi bi-link-45deg"></i> Verified</span>' : '<span class="badge bg-secondary">Pending</span>';
            var action = '';
            if (b.status === 'revealed') {
                action = '<button class="btn btn-sm btn-outline-primary score-btn" data-id="'+b.id+'">Score</button>';
            } else if (b.status === 'evaluated') {
                action = '<button class="btn btn-sm btn-success award-btn" data-id="'+b.id+'" data-tender="'+tid+'" data-name="'+esc(b.bidder_name)+'">Award</button>';
            } else if (b.status === 'awarded') {
                action = '<span class="badge bg-success fs-6">Winner</span>';
            }
            rows += '<tr>'
                +'<td><strong>'+esc(b.bidder_name)+'</strong></td>'
                +'<td>'+esc(b.company_name||'—')+'</td>'
                +'<td>'+amount+'</td>'
                +'<td><span class="badge bg-'+SBADGE[b.status]+'">'+b.status+'</span></td>'
                +'<td>'+(b.technical_score!==null?b.technical_score:'—')+'</td>'
                +'<td>'+(b.financial_score!==null?b.financial_score:'—')+'</td>'
                +'<td>'+(b.final_score!==null?'<strong>'+b.final_score+'</strong>':'—')+'</td>'
                +'<td>'+tx+'</td>'
                +'<td>'+action+'</td>'
                +'</tr>';
        });
        $('#bids-tbody').html(rows);
    });
}

$(document).on('click', '.score-btn', function() {
    $('#score-bid-id').val($(this).data('id'));
    $('#score-form')[0].reset();
    $('#score-bid-id').val($(this).data('id'));
    scoreModal.show();
});

$('#save-score').on('click', function() {
    var btn = $(this).prop('disabled', true).text('Saving...');
    $.post('/etender/api/evaluation/score.php', $('#score-form').serialize(), function(res) {
        scoreModal.hide();
        var cls = res.success ? 'success' : 'danger';
        $('#alert-box').html('<div class="alert alert-'+cls+'">'+res.message+'</div>');
        if (res.success) loadBids(currentTenderId);
    }, 'json').always(function() { btn.prop('disabled', false).text('Save Score'); });
});

$(document).on('click', '.award-btn', function() {
    var bidId = $(this).data('id'), tid = $(this).data('tender'), name = $(this).data('name');
    if (!confirm('Award this tender to '+name+'? This is permanent and will be recorded on the blockchain.')) return;
    var btn = $(this).prop('disabled', true).text('Awarding...');
    $.post('/etender/api/evaluation/award.php', {tender_id: tid, bid_id: bidId}, function(res) {
        var cls = res.success ? 'success' : 'danger';
        $('#alert-box').html('<div class="alert alert-'+cls+'">'+res.message+(res.tx_hash?'<br>Tx: <code>'+res.tx_hash+'</code>':'')+'</div>');
        if (res.success) loadBids(tid);
    }, 'json').fail(function() { btn.prop('disabled', false).text('Award'); });
});
</script>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
