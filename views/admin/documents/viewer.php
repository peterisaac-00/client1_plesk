<?php /** عارض المستند الاحترافي */
$doc = $document;
$isImage = str_starts_with($doc['mime_type'], 'image/');
?>
<div class="doc-shell-topbar">
    <div class="d-flex align-items-center gap-3 min-w-0">
        <a href="<?= url('/admin/documents') ?>" class="btn-icon" title="العودة للمستندات">
            <i class="bi bi-arrow-right"></i>
        </a>
        <div class="min-w-0">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="doc-number"><?= e($doc['doc_number']) ?></span>
                <h2 class="topbar-title text-truncate mb-0"><?= e($doc['original_name']) ?></h2>
            </div>
            <div class="breadcrumb-top">
                <span><?= e($clientName ?? 'بدون عميل') ?></span>
                <i class="bi bi-dot"></i>
                <span><?= e(format_date($doc['created_at'])) ?></span>
                <i class="bi bi-dot"></i>
                <?= document_status_badge($doc['status']) ?>
            </div>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="<?= url('/admin/documents/' . $doc['id'] . '/download') ?>" class="btn btn-primary">
            <i class="bi bi-download"></i> تحميل
        </a>
        <a href="<?= url('/admin/documents/' . $doc['id'] . '/qr') ?>" class="btn btn-light">
            <i class="bi bi-qr-code-scan"></i> QR
        </a>
    </div>
</div>

<div class="doc-shell-body">
    <div class="doc-shell-frame">
        <?php if ($isImage): ?>
            <img src="<?= url('/admin/documents/' . $doc['id'] . '/view') ?>" alt="<?= e($doc['original_name']) ?>">
        <?php else: ?>
            <iframe src="<?= url('/admin/documents/' . $doc['id'] . '/view') ?>"
                    title="عرض المستند" loading="lazy"></iframe>
        <?php endif; ?>
    </div>
</div>
