<?php /** مستندات عميل معين */ ?>
<div class="page-head">
    <div>
        <a href="<?= url('/admin/clients') ?>" class="btn btn-soft-primary btn-sm mb-2">
            <i class="bi bi-arrow-right"></i> العودة للعملاء
        </a>
        <h1 class="page-head-title">مستندات العميل: <?= e($client['name']) ?></h1>
        <p class="page-head-sub">
            <?= e($client['phone'] ?: '') ?> <?= $client['phone'] && $client['email'] ? '·' : '' ?> <?= e($client['email'] ?: '') ?>
        </p>
    </div>
    <div class="page-head-actions">
        <a href="<?= url('/admin/documents/upload?client_id=' . $client['id']) ?>" class="btn btn-primary">
            <i class="bi bi-cloud-arrow-up"></i> رفع مستند لهذا العميل
        </a>
    </div>
</div>

<div class="card card-table">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-mobile-card">
            <thead>
            <tr>
                <th>رقم المستند</th>
                <th>اسم الملف</th>
                <th>النوع</th>
                <th>حالة QR</th>
                <th>مرات التحقق</th>
                <th>تاريخ الرفع</th>
                <th class="text-end">إجراءات</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$documents): ?>
                <tr>
                    <td colspan="7">
                        <div class="empty-state py-5">
                            <div class="empty-icon"><i class="bi bi-folder-x"></i></div>
                            <div class="empty-title">لا توجد مستندات لهذا العميل بعد</div>
                            <div class="empty-hint">ارفع أول مستند ليظهر هنا</div>
                            <a href="<?= url('/admin/documents/upload?client_id=' . $client['id']) ?>" class="btn btn-primary btn-sm">
                                <i class="bi bi-cloud-arrow-up"></i> رفع أول مستند
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
            <?php foreach ($documents as $doc): ?>
                <tr>
                    <td data-label="رقم المستند"><span class="doc-number"><?= e($doc['doc_number']) ?></span></td>
                    <td data-label="اسم الملف">
                        <div class="d-flex align-items-center gap-2">
                            <span class="file-icon <?= str_starts_with($doc['mime_type'], 'image/') ? 'file-icon-img' : '' ?>">
                                <i class="bi bi-<?= str_starts_with($doc['mime_type'], 'image/') ? 'image' : 'filetype-pdf' ?>"></i>
                            </span>
                            <span class="text-truncate d-inline-block" style="max-width: 220px;" title="<?= e($doc['original_name']) ?>">
                                <?= e($doc['original_name']) ?>
                            </span>
                        </div>
                    </td>
                    <td data-label="النوع"><span class="badge-type"><?= e(strtoupper(file_extension($doc['original_name']))) ?></span></td>
                    <td data-label="حالة QR"><?= document_status_badge($doc['status']) ?></td>
                    <td data-label="التحقق"><span class="badge-count"><?= (int)$doc['verification_count'] ?> مرة</span></td>
                    <td data-label="تاريخ الرفع" class="text-muted small"><?= e(format_date($doc['created_at'])) ?></td>
                    <td class="text-end table-actions-cell">
                        <div class="table-actions justify-content-end">
                            <a href="<?= url('/admin/documents/' . $doc['id'] . '/qr') ?>" class="btn-icon" title="عرض QR">
                                <i class="bi bi-qr-code-scan"></i>
                            </a>
                            <a href="<?= url('/admin/documents/' . $doc['id'] . '/view') ?>" target="_blank" class="btn-icon" title="عرض المستند">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="<?= url('/admin/documents/' . $doc['id'] . '/download') ?>" class="btn-icon" title="تحميل">
                                <i class="bi bi-download"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
