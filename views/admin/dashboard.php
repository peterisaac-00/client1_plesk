<?php
/** لوحة التحكم */
$weekLabels = json_encode($weeklyVerifications['labels'] ?? [], JSON_UNESCAPED_UNICODE);
$weekValues = json_encode($weeklyVerifications['values'] ?? [], JSON_UNESCAPED_UNICODE);
$statusData = json_encode([
    (int)($stats['active'] ?? 0),
    (int)($stats['inactive'] ?? 0),
    (int)($stats['disabled'] ?? 0),
], JSON_UNESCAPED_UNICODE);
?>
<div class="page-head">
    <div>
        <h1 class="page-head-title">لوحة التحكم</h1>
        <p class="page-head-sub">نظرة عامة على المستندات وعمليات التحقق</p>
    </div>
    <div class="page-head-actions">
        <a href="<?= url('/admin/documents') ?>" class="btn btn-light">
            <i class="bi bi-files"></i> المستندات
        </a>
        <a href="<?= url('/admin/documents/upload') ?>" class="btn btn-primary">
            <i class="bi bi-cloud-arrow-up"></i> رفع مستند جديد
        </a>
    </div>
</div>

<!-- ===== بطاقات الإحصائيات ===== -->
<div class="section-grid mb-4">
    <div class="card stat-card" style="--stat-bg: var(--primary-soft); --stat-color: var(--primary); --stat-glow: var(--primary-soft);">
        <div class="stat-icon"><i class="bi bi-files"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?= number_format((int)$stats['total']) ?></span>
            <span class="stat-label">إجمالي المستندات</span>
        </div>
    </div>
    <div class="card stat-card" style="--stat-bg: var(--success-soft); --stat-color: var(--success); --stat-glow: var(--success-soft);">
        <div class="stat-icon"><i class="bi bi-patch-check"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?= number_format((int)$stats['active']) ?></span>
            <span class="stat-label">المستندات المفعلة</span>
        </div>
    </div>
    <div class="card stat-card" style="--stat-bg: #eef1f7; --stat-color: #5b6b89; --stat-glow: #eef1f7;">
        <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?= number_format((int)$stats['inactive']) ?></span>
            <span class="stat-label">المستندات غير المفعلة</span>
        </div>
    </div>
    <div class="card stat-card" style="--stat-bg: var(--danger-soft); --stat-color: var(--danger); --stat-glow: var(--danger-soft);">
        <div class="stat-icon"><i class="bi bi-slash-circle"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?= number_format((int)$stats['disabled']) ?></span>
            <span class="stat-label">المستندات المعطلة</span>
        </div>
    </div>
    <div class="card stat-card" style="--stat-bg: var(--info-soft); --stat-color: var(--info); --stat-glow: var(--info-soft);">
        <div class="stat-icon"><i class="bi bi-qr-code-scan"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?= number_format((int)$verificationCount) ?></span>
            <span class="stat-label">إجمالي عمليات التحقق</span>
        </div>
    </div>
    <div class="card stat-card" style="--stat-bg: var(--warning-soft); --stat-color: var(--warning); --stat-glow: var(--warning-soft);">
        <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?= number_format((int)$verificationsToday) ?></span>
            <span class="stat-label">تحققات اليوم</span>
        </div>
    </div>
    <div class="card stat-card" style="--stat-bg: var(--primary-soft); --stat-color: var(--primary); --stat-glow: var(--primary-soft);">
        <div class="stat-icon"><i class="bi bi-people"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?= number_format((int)$clientsCount) ?></span>
            <span class="stat-label">العملاء</span>
        </div>
    </div>
    <div class="card stat-card" style="--stat-bg: var(--success-soft); --stat-color: var(--success-dark); --stat-glow: var(--success-soft);">
        <div class="stat-icon"><i class="bi bi-diagram-3"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?= number_format((int)$documentsWithClients) ?></span>
            <span class="stat-label">مستندات مرتبطة بعميل</span>
        </div>
    </div>
</div>

<!-- ===== الرسوم البيانية ===== -->
<div class="row g-4 mb-4">
    <div class="col-12 col-xl-8">
        <div class="card chart-card h-100">
            <div class="card-header-custom justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="card-header-icon"><i class="bi bi-bar-chart-line"></i></div>
                    <div>
                        <h5 class="mb-0">عمليات التحقق — آخر 7 أيام</h5>
                        <small class="text-muted">عدد مرات مسح QR حسب اليوم</small>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="chart-skeleton" style="height: 260px; position: absolute; inset: 0; z-index: 2;">
                    <div class="skeleton h-100 w-100"></div>
                </div>
                <div class="chart-box">
                    <canvas id="weekChart"
                            data-labels="<?= e($weekLabels) ?>"
                            data-values="<?= e($weekValues) ?>"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card chart-card h-100">
            <div class="card-header-custom justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="card-header-icon"><i class="bi bi-pie-chart"></i></div>
                    <div>
                        <h5 class="mb-0">توزيع حالات المستندات</h5>
                        <small class="text-muted">حالة رموز QR</small>
                    </div>
                </div>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-center">
                <div class="chart-skeleton" style="height: 220px; position: absolute; inset: 0; z-index: 2;">
                    <div class="skeleton h-100 w-100"></div>
                </div>
                <div class="chart-box-sm mb-3">
                    <canvas id="statusChart" data-data="<?= e($statusData) ?>"></canvas>
                </div>
                <div class="chart-legend">
                    <span><i style="background: var(--success);"></i> مفعل (<?= number_format((int)$stats['active']) ?>)</span>
                    <span><i style="background: var(--muted);"></i> غير مفعل (<?= number_format((int)$stats['inactive']) ?>)</span>
                    <span><i style="background: var(--danger);"></i> معطل (<?= number_format((int)$stats['disabled']) ?>)</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== آخر المستندات + آخر عمليات التحقق ===== -->
<div class="row g-4">
    <div class="col-12 col-xl-7">
        <div class="card card-table h-100">
            <div class="card-header-custom justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="card-header-icon"><i class="bi bi-clock-history"></i></div>
                    <div>
                        <h5 class="mb-0">أحدث المستندات</h5>
                        <small class="text-muted">آخر الملفات المرفوعة</small>
                    </div>
                </div>
                <a href="<?= url('/admin/documents') ?>" class="btn btn-soft-primary btn-sm">
                    عرض الكل <i class="bi bi-arrow-left"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                    <tr>
                        <th>رقم المستند</th>
                        <th>اسم الملف</th>
                        <th>العميل</th>
                        <th>حالة QR</th>
                        <th>التحقق</th>
                        <th>تاريخ الرفع</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!$recentDocuments): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-icon"><i class="bi bi-files"></i></div>
                                    <div class="empty-title">لا توجد مستندات بعد</div>
                                    <div class="empty-hint">ارفع أول مستند ليظهر هنا</div>
                                    <a href="<?= url('/admin/documents/upload') ?>" class="btn btn-soft-primary btn-sm">
                                        <i class="bi bi-cloud-arrow-up"></i> رفع مستند
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($recentDocuments as $doc): ?>
                        <tr>
                            <td><span class="doc-number"><?= e($doc['doc_number']) ?></span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="file-icon <?= str_starts_with($doc['mime_type'], 'image/') ? 'file-icon-img' : '' ?>">
                                        <i class="bi bi-<?= str_starts_with($doc['mime_type'], 'image/') ? 'image' : 'filetype-pdf' ?>"></i>
                                    </span>
                                    <span class="text-truncate d-inline-block" style="max-width: 170px;" title="<?= e($doc['original_name']) ?>">
                                        <?= e($doc['original_name']) ?>
                                    </span>
                                </div>
                            </td>
                            <td class="text-muted"><?= e($doc['client_name'] ?? '—') ?></td>
                            <td><?= document_status_badge($doc['status']) ?></td>
                            <td><span class="badge-count"><?= (int)($doc['verification_count'] ?? 0) ?> مرة</span></td>
                            <td class="text-muted small"><?= e(format_date($doc['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-5">
        <div class="card card-table h-100">
            <div class="card-header-custom justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="card-header-icon"><i class="bi bi-qr-code-scan"></i></div>
                    <div>
                        <h5 class="mb-0">آخر عمليات التحقق</h5>
                        <small class="text-muted">أحدث عمليات مسح QR</small>
                    </div>
                </div>
                <a href="<?= url('/admin/verifications') ?>" class="btn btn-soft-primary btn-sm">
                    عرض الكل <i class="bi bi-arrow-left"></i>
                </a>
            </div>
            <?php if (!$recentVerifications): ?>
                <div class="empty-state py-5">
                    <div class="empty-icon"><i class="bi bi-qr-code-scan"></i></div>
                    <div class="empty-title">لا توجد عمليات تحقق بعد</div>
                    <div class="empty-hint">عند مسح أي QR Code سيظهر التحقق هنا</div>
                </div>
            <?php else: ?>
                <ul class="activity-list">
                    <?php foreach ($recentVerifications as $v): ?>
                        <li>
                            <div class="activity-icon bg-success-soft text-success"><i class="bi bi-check2-circle"></i></div>
                            <div class="activity-body">
                                <strong><?= e($v['doc_number']) ?></strong>
                                <span class="text-truncate"><?= e($v['original_name']) ?></span>
                            </div>
                            <div class="activity-meta">
                                <span><?= e(format_date($v['created_at'])) ?></span>
                                <span class="text-muted"><?= e($v['client_name'] ?? '—') ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
