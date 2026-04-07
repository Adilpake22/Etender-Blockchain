<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — BlockTender</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); min-height: 100vh; }
        .card { border-radius: 16px; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center py-5">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 p-4">
                <div class="text-center mb-4">
                    <i class="bi bi-shield-check text-primary fs-1"></i>
                    <h4 class="fw-bold mt-2 mb-1">Create Bidder Account</h4>
                    <p class="text-muted small">Register to participate in tenders</p>
                </div>

                <div id="alert-box"></div>

                <form id="register-form">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Full name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Company name</label>
                            <input type="text" name="company_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="tel" name="phone" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Password *</label>
                            <input type="password" name="password" class="form-control" minlength="6" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100 py-2" id="reg-btn">
                                <span class="spinner-border spinner-border-sm d-none me-2" id="spinner"></span>
                                Create Account
                            </button>
                        </div>
                    </div>
                </form>
                <p class="text-center small mt-3 mb-0">Already have an account? <a href="/etender/views/auth/login.php">Sign in</a></p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
<script>
$('#register-form').on('submit', function(e) {
    e.preventDefault();
    $('#reg-btn').prop('disabled', true);
    $('#spinner').removeClass('d-none');
    $.ajax({
        url: '/etender/api/auth/register.php',
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                $('#alert-box').html('<div class="alert alert-success">' + res.message + '</div>');
                setTimeout(function() { window.location.href = '/etender/views/auth/login.php'; }, 1500);
            } else {
                $('#alert-box').html('<div class="alert alert-danger">' + res.message + '</div>');
                $('#reg-btn').prop('disabled', false);
                $('#spinner').addClass('d-none');
            }
        },
        error: function() {
            $('#alert-box').html('<div class="alert alert-danger">Server error. Please try again.</div>');
            $('#reg-btn').prop('disabled', false);
            $('#spinner').addClass('d-none');
        }
    });
});
</script>
</body>
</html>
