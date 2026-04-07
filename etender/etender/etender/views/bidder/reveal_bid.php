<?php
$pageTitle = 'Reveal Bid — BlockTender';
$activeNav = 'reveal';
require_once __DIR__ . '/../../views/layouts/navbar.php';
if ($role !== 'bidder') { header('Location: /etender/views/auth/login.php'); exit; }
?>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>My Committed Bids</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Bid #</th><th>Tender</th><th>Tender Status</th><th>Bid Status</th><th>Amount</th><th>Action</th></tr>
                    </thead>
                    <tbody id="my-bids">
                        <tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0"><i class="bi bi-unlock me-2 text-warning"></i>Reveal Your Bid</h6>
            </div>
            <div class="card-body">
                <div id="reveal-alert"></div>
                <div class="alert alert-warning small">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Enter the <strong>exact same amount and secret key</strong> you used when committing. The system will verify the hash matches.
                </div>
                <form id="reveal-form">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bid ID *</label>
                        <input type="number" name="bid_id" id="reveal-bid-id" class="form-control" placeholder="e.g. 1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bid amount (INR) *</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" name="amount" class="form-control" step="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Secret key *</label>
                        <input type="text" name="secret" class="form-control font-monospace" placeholder="Your 32-character secret key" required>
                    </div>
                    <button type="submit" class="btn btn-warning w-100" id="reveal-btn">
                        <span class="spinner-border spinner-border-sm d-none me-2" id="reveal-spinner"></span>
                        <i class="bi bi-unlock me-2"></i>Reveal & Verify on Blockchain
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
var SBADGE = {committed:'secondary',revealed:'info',evaluated:'warning',awarded:'success',rejected:'danger'};
var TBADGE = {open:'success',closed:'warning',awarded:'primary'};
function esc(s){ return $('<div>').text(s+'').html(); }

function loadMyBids() {
    $.getJSON('/etender/api/bid/list.php', {mine:1}, function(res) {
        var data = res.data || [];
        if (!data.length) {
            $('#my-bids').html('<tr><td colspan="6" class="text-center py-4 text-muted">No bids submitted yet. <a href="/etender/views/bidder/tender_list.php">Browse tenders</a></td></tr>');
            return;
        }
        var rows = '';
        $.each(data, function(i, b) {
            var canReveal = b.status === 'committed' && b.tender_status === 'closed';
            var action = canReveal
                ? '<button class="btn btn-sm btn-warning fill-reveal" data-id="'+b.id+'">Reveal</button>'
                : '—';
            rows += '<tr>'
                +'<td><strong>#'+b.id+'</strong></td>'
                +'<td class="small">'+esc(b.tender_title)+'</td>'
                +'<td><span class="badge bg-'+(TBADGE[b.tender_status]||'secondary')+'">'+b.tender_status+'</span></td>'
                +'<td><span class="badge bg-'+SBADGE[b.status]+'">'+b.status+'</span></td>'
                +'<td>'+(b.amount?'₹'+Number(b.amount).toLocaleString('en-IN'):'<span class="text-muted">Hidden</span>')+'</td>'
                +'<td>'+action+'</td>'
                +'</tr>';
        });
        $('#my-bids').html(rows);
    });
}

$(document).on('click', '.fill-reveal', function() {
    $('#reveal-bid-id').val($(this).data('id'));
    $('html,body').animate({scrollTop: $('#reveal-form').offset().top - 80}, 300);
    $('input[name=amount]').focus();
});

$('#reveal-form').on('submit', function(e) {
    e.preventDefault();
    $('#reveal-btn').prop('disabled', true);
    $('#reveal-spinner').removeClass('d-none');
    $('#reveal-alert').html('');

    $.ajax({
        url: '/etender/api/bid/reveal.php',
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                $('#reveal-alert').html(
                    '<div class="alert alert-success">'
                    +'<strong><i class="bi bi-check-circle me-2"></i>Bid Revealed!</strong><br>'
                    +'Blockchain Tx: <code>'+res.tx_hash+'</code>'
                    +'</div>'
                );
                $('#reveal-form')[0].reset();
                loadMyBids();
            } else {
                $('#reveal-alert').html('<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>'+res.message+'</div>');
            }
        },
        error: function() {
            $('#reveal-alert').html('<div class="alert alert-danger">Server error. Please try again.</div>');
        },
        complete: function() {
            $('#reveal-btn').prop('disabled', false);
            $('#reveal-spinner').addClass('d-none');
        }
    });
});

loadMyBids();
setInterval(loadMyBids, 15000);
</script>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
