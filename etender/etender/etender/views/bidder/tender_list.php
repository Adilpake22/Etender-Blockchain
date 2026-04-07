<?php
$pageTitle = 'Browse Tenders — BlockTender';
$activeNav = 'tenders';
require_once __DIR__ . '/../../views/layouts/navbar.php';
if ($role !== 'bidder') { header('Location: /etender/views/auth/login.php'); exit; }
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0"><i class="bi bi-list-ul me-2 text-primary"></i>Open Tenders</h5>
    <div class="d-flex gap-2">
        <input type="text" id="search" class="form-control form-control-sm" placeholder="Search..." style="width:200px">
        <select id="cat-filter" class="form-select form-select-sm" style="width:160px">
            <option value="">All categories</option>
            <option>Infrastructure</option><option>Technology</option>
            <option>Healthcare</option><option>Education</option>
            <option>Transportation</option><option>Energy</option>
        </select>
    </div>
</div>

<div id="tenders-grid" class="row g-4">
    <div class="col-12 text-center py-5 text-muted"><div class="spinner-border text-primary"></div></div>
</div>

<!-- Bid Modal -->
<div class="modal fade" id="bidModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-shield-lock me-2"></i>Submit Sealed Bid</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="bid-alert"></div>
                <div class="alert alert-info small">
                    <i class="bi bi-info-circle me-2"></i>
                    Your actual bid amount is <strong>never stored in plain text</strong>. Only a cryptographic hash (SHA-256) is recorded. You need your secret key to reveal your bid after the deadline.
                </div>
                <form id="bid-form">
                    <input type="hidden" name="tender_id" id="bid-tender-id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bid amount (INR) *</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" name="amount" class="form-control" min="1" step="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Secret key *
                            <span class="text-danger small"> — Save this! You need it to reveal your bid later.</span>
                        </label>
                        <div class="input-group">
                            <input type="text" name="secret" id="secret-field" class="form-control font-monospace" readonly required>
                            <button type="button" class="btn btn-outline-secondary" id="gen-secret">
                                <i class="bi bi-shuffle me-1"></i>Generate
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="copy-secret" title="Copy to clipboard">
                                <i class="bi bi-clipboard" id="copy-icon"></i>
                            </button>
                        </div>
                        <div class="form-text text-danger">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Store this key safely. If you lose it, you cannot reveal your bid.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submit-bid-btn">
                    <span class="spinner-border spinner-border-sm d-none me-2" id="bid-spinner"></span>
                    <i class="bi bi-shield-check me-2"></i>Commit Bid to Blockchain
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
var bidModal = new bootstrap.Modal(document.getElementById('bidModal'));
function esc(s) { return $('<div>').text(s+'').html(); }

function loadTenders() {
    $.ajax({
        url: '/etender/api/tender/list.php',
        data: { status:'open', search:$('#search').val(), category:$('#cat-filter').val() },
        dataType: 'json',
        success: function(res) {
            if (!res.data.length) {
                $('#tenders-grid').html('<div class="col-12 text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No open tenders found</div>');
                return;
            }
            var cards = '';
            $.each(res.data, function(i, t) {
                var daysLeft = Math.ceil((new Date(t.deadline) - new Date()) / 86400000);
                var urgency  = daysLeft <= 3 ? 'danger' : daysLeft <= 7 ? 'warning' : 'success';
                cards += '<div class="col-md-6 col-lg-4">'
                    +'<div class="card border-0 shadow-sm h-100">'
                    +'<div class="card-body">'
                    +'<div class="d-flex justify-content-between mb-2">'
                    +'<span class="badge bg-light text-dark">'+esc(t.category||'General')+'</span>'
                    +'<span class="badge bg-'+urgency+'">'+daysLeft+' days left</span>'
                    +'</div>'
                    +'<h6 class="fw-bold">'+esc(t.title)+'</h6>'
                    +'<p class="text-muted small">'+esc(t.description).substring(0,100)+'...</p>'
                    +'<div class="row text-center border-top pt-2 mt-2 g-0">'
                    +'<div class="col-6"><div class="text-muted small">Budget</div><div class="fw-bold small">₹'+Number(t.budget).toLocaleString('en-IN')+'</div></div>'
                    +'<div class="col-6"><div class="text-muted small">Bids</div><div class="fw-bold">'+t.bid_count+'</div></div>'
                    +'</div>'
                    +(t.tx_hash?'<div class="text-center mt-2"><span class="badge bg-success"><i class="bi bi-link-45deg"></i> Blockchain verified</span></div>':'')
                    +'</div>'
                    +'<div class="card-footer bg-white border-0 pb-3">'
                    +'<button class="btn btn-primary w-100 btn-sm bid-btn" data-id="'+t.id+'" data-title="'+esc(t.title)+'">'
                    +'<i class="bi bi-shield-lock me-2"></i>Submit Sealed Bid'
                    +'</button>'
                    +'</div>'
                    +'</div></div>';
            });
            $('#tenders-grid').html(cards);
        },
        error: function() {
            $('#tenders-grid').html('<div class="col-12 text-center py-5 text-danger">Failed to load tenders.</div>');
        }
    });
}

$(document).on('click', '.bid-btn', function() {
    $('#bid-tender-id').val($(this).data('id'));
    $('.modal-title').html('<i class="bi bi-shield-lock me-2"></i>Bid: '+$(this).data('title'));
    $('#bid-form')[0].reset();
    $('#bid-alert').html('');
    $('#secret-field').val('');
    bidModal.show();
});

$('#gen-secret').on('click', function() {
    var arr = new Uint8Array(16);
    crypto.getRandomValues(arr);
    var secret = Array.from(arr).map(function(b){ return b.toString(16).padStart(2,'0'); }).join('');
    $('#secret-field').val(secret);
});

$('#copy-secret').on('click', function() {
    var s = $('#secret-field').val();
    if (!s) { alert('Generate a secret key first!'); return; }
    navigator.clipboard.writeText(s).then(function() {
        $('#copy-icon').removeClass('bi-clipboard').addClass('bi-clipboard-check');
        setTimeout(function(){ $('#copy-icon').removeClass('bi-clipboard-check').addClass('bi-clipboard'); }, 2000);
    });
});

$('#submit-bid-btn').on('click', function() {
    var secret = $('#secret-field').val();
    var amount = $('input[name=amount]').val();
    if (!secret) { $('#bid-alert').html('<div class="alert alert-warning">Please generate a secret key first.</div>'); return; }
    if (!amount) { $('#bid-alert').html('<div class="alert alert-warning">Please enter your bid amount.</div>'); return; }
    if (!confirm('IMPORTANT: Have you saved your secret key?\n\n'+secret+'\n\nYou MUST keep this to reveal your bid later. Continue?')) return;

    $(this).prop('disabled', true);
    $('#bid-spinner').removeClass('d-none');
    $('#bid-alert').html('');

    $.ajax({
        url: '/etender/api/bid/commit.php',
        method: 'POST',
        data: $('#bid-form').serialize(),
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                $('#bid-alert').html(
                    '<div class="alert alert-success">'
                    +'<strong><i class="bi bi-check-circle me-2"></i>Bid Committed!</strong><br>'
                    +'Bid ID: <strong>#'+res.bid_id+'</strong><br>'
                    +'Hash: <code>'+res.bid_hash+'</code><br>'
                    +'Blockchain Tx: <code>'+res.tx_hash+'</code>'
                    +'</div>'
                );
                loadTenders();
            } else {
                $('#bid-alert').html('<div class="alert alert-danger">'+res.message+'</div>');
            }
        },
        error: function() {
            $('#bid-alert').html('<div class="alert alert-danger">Server error. Please try again.</div>');
        },
        complete: function() {
            $('#submit-bid-btn').prop('disabled', false);
            $('#bid-spinner').addClass('d-none');
        }
    });
});

var t;
$('#search').on('input', function(){ clearTimeout(t); t=setTimeout(loadTenders,400); });
$('#cat-filter').on('change', loadTenders);
$(document).ready(loadTenders);
</script>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
