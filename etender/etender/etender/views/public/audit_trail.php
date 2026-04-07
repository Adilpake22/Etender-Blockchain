<?php
$pageTitle = 'Audit Trail — BlockTender';
$activeNav = 'audit';
require_once __DIR__ . '/../../views/layouts/navbar.php';
require_once __DIR__ . '/../../app/config/database.php';

$logs = getDB()->query(
    "SELECT a.*, u.name as actor_name FROM audit_log a
     LEFT JOIN users u ON a.actor_id = u.id
     ORDER BY a.logged_at DESC LIMIT 200"
)->fetchAll();

function actionBadge($a) {
    if (str_contains($a,'award'))   return 'primary';
    if (str_contains($a,'reveal'))  return 'info';
    if (str_contains($a,'commit'))  return 'secondary';
    if (str_contains($a,'publish')||str_contains($a,'create')) return 'success';
    if (str_contains($a,'close'))   return 'warning';
    return 'light';
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0"><i class="bi bi-link-45deg me-2 text-primary"></i>Blockchain Audit Trail</h5>
    <span class="badge bg-primary fs-6"><?= count($logs) ?> records</span>
</div>

<div class="alert alert-info d-flex gap-2 mb-4">
    <i class="bi bi-shield-check fs-4"></i>
    <div>Every action in this system generates a unique blockchain transaction hash. Each hash is a cryptographic proof that the action occurred and cannot be altered.</div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Time</th><th>Action</th><th>Actor</th>
                        <th>Record</th><th>Details</th><th>Blockchain Tx Hash</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted">No audit records yet. Actions will appear here as you use the system.</td></tr>
                <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td class="text-muted small"><?= date('d M Y H:i:s', strtotime($log['logged_at'])) ?></td>
                        <td><span class="badge bg-<?= actionBadge($log['action']) ?>"><?= htmlspecialchars(str_replace('_', ' ', $log['action'])) ?></span></td>
                        <td class="small"><?= htmlspecialchars($log['actor_name'] ?? 'System') ?></td>
                        <td class="small"><?= htmlspecialchars(ucfirst($log['record_type'])) ?> #<?= $log['record_id'] ?></td>
                        <td class="small text-muted"><?= htmlspecialchars($log['details'] ?? '—') ?></td>
                        <td>
                            <?php if ($log['tx_hash']): ?>
                                <code class="small"><?= htmlspecialchars(substr($log['tx_hash'], 0, 20)) ?>...</code>
                                <button class="btn btn-link btn-sm p-0 ms-1 copy-tx" data-tx="<?= htmlspecialchars($log['tx_hash']) ?>" title="Copy full hash">
                                    <i class="bi bi-clipboard small"></i>
                                </button>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).on('click', '.copy-tx', function() {
    var tx = $(this).data('tx');
    navigator.clipboard.writeText(tx).then(function() {
        alert('Transaction hash copied to clipboard!');
    });
});
</script>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
