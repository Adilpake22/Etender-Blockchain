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
    <div class="col-12 text-center py-5 text-muted">
        <div class="spinner-border text-primary"></div>
        <p class="mt-2">Loading tenders...</p>
    </div>
</div>

<!-- Bid Modal -->
<div class="modal fade" id="bidModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-tender-title">
                    <i class="bi bi-shield-lock me-2"></i>Submit Sealed Bid
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="bid-alert"></div>
                <div class="alert alert-info small">
                    <i class="bi bi-info-circle me-2"></i>
                    Your bid amount is protected by a <strong>4-digit PIN</strong>. Only the hash is stored. You need your PIN to reveal your bid after the deadline.
                </div>
                <form id="bid-form">
                    <input type="hidden" name="tender_id" id="bid-tender-id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bid amount (INR) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">&#8377;</span>
                            <input type="number" name="amount" id="bid-amount" class="form-control" min="1" step="0.01" placeholder="e.g. 5000000" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">4-digit PIN <span class="text-danger">*</span>
                            <span class="text-muted small fw-normal">— needed to reveal your bid later</span>
                        </label>
                        <input type="password" name="pin" id="pin-field" class="form-control form-control-lg text-center"
                            placeholder="Enter 4 digits" maxlength="4" inputmode="numeric" autocomplete="off" required>
                        <div class="form-text text-warning">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Remember this PIN — you MUST enter it again to reveal your bid.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Confirm PIN <span class="text-danger">*</span></label>
                        <input type="password" name="pin_confirm" id="pin-confirm-field" class="form-control form-control-lg text-center"
                            placeholder="Re-enter 4 digits" maxlength="4" inputmode="numeric" autocomplete="off" required>
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

function esc(s) { return $('<div>').text(s + '').html(); }

function loadTenders() {
    $.ajax({
        url: '/etender/api/tender/list.php',
        method: 'GET',
        data: { status: 'open', search: $('#search').val(), category: $('#cat-filter').val() },
        dataType: 'json',
        success: function(res) {
            if (!res.success || !res.data || res.data.length === 0) {
                $('#tenders-grid').html('<div class="col-12 text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No open tenders found</div>');
                return;
            }
            var cards = '';
            $.each(res.data, function(i, t) {
                var daysLeft = Math.ceil((new Date(t.deadline) - new Date()) / 86400000);
                var urgency  = daysLeft <= 3 ? 'danger' : daysLeft <= 7 ? 'warning' : 'success';
                cards += '<div class="col-md-6 col-lg-4">'
                    + '<div class="card border-0 shadow-sm h-100">'
                    + '<div class="card-body">'
                    + '<div class="d-flex justify-content-between mb-2">'
                    + '<span class="badge bg-light text-dark">' + esc(t.category || 'General') + '</span>'
                    + '<span class="badge bg-' + urgency + '">' + (daysLeft > 0 ? daysLeft + ' days left' : 'Expired') + '</span>'
                    + '</div>'
                    + '<h6 class="fw-bold">' + esc(t.title) + '</h6>'
                    + '<p class="text-muted small">' + esc(t.description).substring(0, 100) + '...</p>'
                    + '<div class="row text-center border-top pt-2 mt-2 g-0">'
                    + '<div class="col-6"><div class="text-muted small">Budget</div><div class="fw-bold small">&#8377;' + Number(t.budget).toLocaleString('en-IN') + '</div></div>'
                    + '<div class="col-6"><div class="text-muted small">Bids</div><div class="fw-bold">' + t.bid_count + '</div></div>'
                    + '</div>'
                    + (t.tx_hash ? '<div class="text-center mt-2"><span class="badge bg-success"><i class="bi bi-link-45deg"></i> Blockchain verified</span></div>' : '')
                    + '</div>'
                    + '<div class="card-footer bg-white border-0 pb-3">'
                    + '<button class="btn btn-primary w-100 btn-sm bid-btn" data-id="' + t.id + '" data-title="' + esc(t.title) + '">'
                    + '<i class="bi bi-shield-lock me-2"></i>Submit Sealed Bid'
                    + '</button>'
                    + '</div>'
                    + '</div></div>';
            });
            $('#tenders-grid').html(cards);
        },
        error: function(xhr) {
            console.error('Load error:', xhr.status, xhr.responseText);
            $('#tenders-grid').html('<div class="col-12 text-center py-5 text-danger">Failed to load tenders. Error: ' + xhr.status + '</div>');
        }
    });
}

// Open modal
$(document).on('click', '.bid-btn', function() {
    $('#bid-tender-id').val($(this).data('id'));
    $('#modal-tender-title').html('<i class="bi bi-shield-lock me-2"></i>' + $(this).data('title'));
    $('#bid-form')[0].reset();
    $('#bid-alert').html('');
    bidModal.show();
});

// Only allow digits in PIN fields
$(document).on('input', '#pin-field, #pin-confirm-field', function() {
    $(this).val($(this).val().replace(/\D/g, ''));
});

// Submit bid
$('#submit-bid-btn').on('click', function() {
    var pin        = $('#pin-field').val().trim();
    var pinConfirm = $('#pin-confirm-field').val().trim();
    var amount     = $('#bid-amount').val().trim();
    var tenderId   = $('#bid-tender-id').val();

    $('#bid-alert').html('');

    if (!amount || parseFloat(amount) <= 0) {
        $('#bid-alert').html('<div class="alert alert-warning">Please enter a valid bid amount.</div>');
        return;
    }
    if (pin.length !== 4 || !/^\d{4}$/.test(pin)) {
        $('#bid-alert').html('<div class="alert alert-warning">PIN must be exactly 4 digits.</div>');
        return;
    }
    if (pin !== pinConfirm) {
        $('#bid-alert').html('<div class="alert alert-danger">PINs do not match. Please re-enter.</div>');
        return;
    }
    if (!confirm('Your PIN is: ' + pin + '\n\nYou MUST remember this to reveal your bid.\n\nContinue?')) return;

    $('#submit-bid-btn').prop('disabled', true);
    $('#bid-spinner').removeClass('d-none');

    $.ajax({
        url: '/etender/api/bid/commit.php',
        method: 'POST',
        data: { tender_id: tenderId, amount: amount, pin: pin },
        dataType: 'json',
        success: function(res) {
            console.log('Bid result:', res);
            if (res.success) {
                $('#bid-alert').html(
                    '<div class="alert alert-success">'
                    + '<strong><i class="bi bi-check-circle me-2"></i>Bid Committed!</strong><br>'
                    + 'Bid ID: <strong>#' + res.bid_id + '</strong><br>'
                    + 'Blockchain Tx: <code>' + res.tx_hash + '</code><br>'
                    + '<span class="text-danger fw-bold">Your PIN: ' + pin + ' — write it down!</span>'
                    + '</div>'
                );
                loadTenders();
            } else {
                $('#bid-alert').html('<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>' + res.message + '</div>');
            }
        },
        error: function(xhr) {
            console.error('Commit error:', xhr.status, xhr.responseText);
            $('#bid-alert').html('<div class="alert alert-danger">Error ' + xhr.status + ': ' + xhr.responseText + '</div>');
        },
        complete: function() {
            $('#submit-bid-btn').prop('disabled', false);
            $('#bid-spinner').addClass('d-none');
        }
    });
});

var searchTimer;
$('#search').on('input', function() { clearTimeout(searchTimer); searchTimer = setTimeout(loadTenders, 400); });
$('#cat-filter').on('change', loadTenders);
$(document).ready(function() { loadTenders(); });
</script>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>