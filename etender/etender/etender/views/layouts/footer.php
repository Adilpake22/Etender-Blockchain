
</div><!-- end container -->

<footer class="text-center py-4 mt-5 text-muted small border-top">
    <i class="bi bi-shield-check text-primary me-1"></i>
    BlockTender — Blockchain E-Tender System &copy; <?= date('Y') ?>
</footer>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($pageScript)): ?>
<script src="/etender/public/js/<?= $pageScript ?>"></script>
<?php endif; ?>
<?php if (!empty($inlineScript)): ?>
<script><?= $inlineScript ?></script>
<?php endif; ?>
</body>
</html>
