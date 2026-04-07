<?php
$pageTitle = 'New Tender — BlockTender';
$activeNav = 'new-tender';
require_once __DIR__ . '/../../views/layouts/navbar.php';
if ($role !== 'admin') { header('Location: /etender/views/auth/login.php'); exit; }
?>

<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="bi bi-plus-circle me-2 text-primary"></i>Publish New Tender</h5>
    </div>
    <div class="card-body p-4">
        <div id="alert-box"></div>
        <form id="tender-form">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Tender title *</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Road Construction Project NH-48" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Description *</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Detailed scope of work..." required></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Category</label>
                    <select name="category" class="form-select">
                        <option value="">Select category</option>
                        <option>Infrastructure</option>
                        <option>Technology</option>
                        <option>Healthcare</option>
                        <option>Education</option>
                        <option>Transportation</option>
                        <option>Energy</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Budget (INR) *</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" name="budget" class="form-control" placeholder="5000000" min="1" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Bid deadline *</label>
                    <input type="datetime-local" name="deadline" class="form-control" required>
                    <div class="form-text">Last date to submit bids</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Reveal deadline *</label>
                    <input type="datetime-local" name="reveal_deadline" class="form-control" required>
                    <div class="form-text">Must be after bid deadline</div>
                </div>
                <div class="col-12">
                    <div class="alert alert-info d-flex gap-2">
                        <i class="bi bi-shield-check fs-5 mt-1"></i>
                        <div>Publishing this tender will generate a blockchain transaction hash — creating an immutable record that cannot be altered by anyone.</div>
                    </div>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4" id="submit-btn">
                        <span class="spinner-border spinner-border-sm d-none me-2" id="spinner"></span>
                        <i class="bi bi-shield-check me-2"></i>Publish to Blockchain
                    </button>
                    <a href="/etender/views/admin/dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$('#tender-form').on('submit', function(e) {
    e.preventDefault();
    var deadline = new Date($('input[name=deadline]').val());
    var reveal   = new Date($('input[name=reveal_deadline]').val());
    if (reveal <= deadline) {
        $('#alert-box').html('<div class="alert alert-danger">Reveal deadline must be after bid deadline.</div>');
        return;
    }
    $('#submit-btn').prop('disabled', true);
    $('#spinner').removeClass('d-none');
    $('#alert-box').html('');

    $.ajax({
        url: '/etender/api/tender/create.php',
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                $('#alert-box').html(
                    '<div class="alert alert-success">' +
                    '<strong><i class="bi bi-check-circle me-2"></i>Tender Published!</strong><br>' +
                    'Tender ID: <strong>#' + res.tender_id + '</strong><br>' +
                    'Blockchain Tx: <code>' + res.tx_hash + '</code>' +
                    '</div>'
                );
                $('#tender-form')[0].reset();
            } else {
                $('#alert-box').html('<div class="alert alert-danger">' + res.message + '</div>');
            }
        },
        error: function() {
            $('#alert-box').html('<div class="alert alert-danger">Server error. Please try again.</div>');
        },
        complete: function() {
            $('#submit-btn').prop('disabled', false);
            $('#spinner').addClass('d-none');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
