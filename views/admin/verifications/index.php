<?php /** سجل عمليات التحقق */ ?>
<div class="page-head mb-4">
    <h1 class="page-head-title">سجل عمليات التحقق</h1>
    <p class="text-muted mb-0">جميع عمليات فتح روابط التحقق عبر QR Code
        <?php if (!$ipEnabled): ?>
            <span class="badge badge-inactive ms-1">تسجيل IP معطل</span>
        <?php endif; ?>
    </p>
</div>

<!-- ===== الفلاتر ===== -->
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="get" action="<?= url('/admin/verifications') ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label small text-muted">بحث</label>
                <input type="text" name="q" value="<?= e($filters['q']) ?>" class="form-control"
                       placeholder="رقم المستند، اسم الملف، اسم العميل...">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted">العميل</label>
                <select name="client_id" class="form-select">
                    <option value="">الكل</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= (int)$client['id'] ?>" <?= (int)$filters['client_id'] === (int)$client['id'] ? 'selected' : '' ?>>
                            <?= e($client['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted">من تاريخ</label>
                <input type="date" name="date_from" value="<?= e($filters['date_from']) ?>" class="form-control">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted">إلى تاريخ</label>
                <input type="date" name="date_to" value="<?= e($filters['date_to']) ?>" class="form-control">
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button class="btn btn-primary flex-fill" type="submit"><i class="bi bi-funnel"></i></button>
                <a href="<?= url('/admin/verifications') ?>" class="btn btn-light flex-fill" title="مسح"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- ===== الجدول ===== -->
<div class="card card-table">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr>
                <th>#</th>
                <th>رقم المستند</th>
                <th>اسم الملف</th>
                <th>العميل</th>
                <th>وقت التحقق</th>
                <th>الحالة عند التحقق</th>
                <th>IP</th>
                <th>متصفح / جهاز</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$logs): ?>
                <tr>
                    <td colspan="8">
                        <div class="empty-state py-5">
                            <div class="empty-icon"><i class="bi bi-clipboard2-x"></i></div>
                            <p class="text-muted mb-0">لا توجد عمليات تحقق مطابقة</p>
                            <small class="text-muted">عند مسح أي QR Code سيظهر التحقق هنا تلقائيًا</small>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td class="text-muted"><?= (int)$log['id'] ?></td>
                    <td><span class="doc-number"><?= e($log['doc_number']) ?></span></td>
                    <td>
                        <span class="text-truncate d-inline-block" style="max-width: 200px;" title="<?= e($log['original_name']) ?>">
                            <?= e($log['original_name']) ?>
                        </span>
                    </td>
                    <td><?= e($log['client_name'] ?? '—') ?></td>
                    <td class="text-muted small"><?= e(format_date($log['created_at'])) ?></td>
                    <td><?= document_status_badge($log['doc_status']) ?></td>
                    <td dir="ltr" class="small text-end"><?= $ipEnabled ? e($log['ip_address'] ?? '—') : '—' ?></td>
                    <td>
                        <span class="text-truncate d-inline-block text-muted small" style="max-width: 160px;"
                              title="<?= e($log['user_agent'] ?? '') ?>">
                            <?= e(mb_substr((string)$log['user_agent'], 0, 60)) ?><?= mb_strlen((string)$log['user_agent']) > 60 ? '…' : '' ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($paginator->hasPages()): ?>
        <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <small class="text-muted">إجمالي <?= number_format($paginator->total()) ?> عملية تحقق</small>
            <?= $paginator->links() ?>
        </div>
    <?php endif; ?>
</div>
