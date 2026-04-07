<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — BlockTender</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); min-height: 100vh; }
        .card { border-radius: 16px; }
        .brand-icon { width: 64px; height: 64px; background: #0d6efd; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center py-5">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-lg border-0 p-4">
                <div class="text-center mb-4">
                    <div class="brand-icon">
                        <i class="bi bi-shield-check text-white fs-3"></i>
                    </div>
                    <h4 class="fw-bold mb-1">BlockTender</h4>
                    <p class="text-muted small mb-0">Blockchain E-Tender System</p>
                </div>

                <div id="alert-box"></div>

                <form id="login-form">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email address</label>
                        <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2" id="login-btn">
                        <span class="spinner-border spinner-border-sm d-none me-2" id="spinner"></span>
                        Sign in
                    </button>
                </form>

                <hr class="my-3">
                <p class="text-center small mb-2 text-muted">New bidder? <a href="/etender/views/auth/register.php">Create account</a></p>

                <div class="bg-light rounded p-3 small text-muted mt-2">
                    <strong>Demo accounts (password: password)</strong><br>
                    Admin: admin@etender.gov.in<br>
                    Bidder: abc@construction.com<br>
                    Evaluator: evaluator@etender.gov.in
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
<script>
$('#login-form').on('submit', function(e) {
    e.preventDefault();
    $('#login-btn').prop('disabled', true);
    $('#spinner').removeClass('d-none');
    $('#alert-box').html('');

    $.ajax({
        url: '/etender/api/auth/login.php',
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                window.location.href = res.redirect;
            } else {
                $('#alert-box').html('<div class="alert alert-danger">' + res.message + '</div>');
                $('#login-btn').prop('disabled', false);
                $('#spinner').addClass('d-none');
            }
        },
        error: function() {
            $('#alert-box').html('<div class="alert alert-danger">Server error. Please try again.</div>');
            $('#login-btn').prop('disabled', false);
            $('#spinner').addClass('d-none');
        }
    });
});
</script>
</body>
</html>
