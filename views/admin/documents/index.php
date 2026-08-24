<?php /** إدارة المستندات */ ?>
<div class="page-head">
    <div>
        <h1 class="page-head-title">إدارة المستندات</h1>
        <p class="page-head-sub">إدارة المستندات وإنشاء رموز QR والتحكم في حالتها</p>
    </div>
    <div class="page-head-actions">
        <a href="<?= url('/admin/documents/upload') ?>" class="btn btn-primary">
            <i class="bi bi-cloud-arrow-up"></i> رفع مستند جديد
        </a>
    </div>
</div>

<!-- ===== الفلاتر ===== -->
<div class="card mb-4">
    <div class="card-body py-3 px-3 px-md-4">
        <form method="get" action="<?= url('/admin/documents') ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small text-muted" for="f-q">بحث</label>
                <input type="text" name="q" id="f-q" value="<?= e($filters['q']) ?>" class="form-control"
                       placeholder="اسم الملف أو رقم المستند...">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted" for="f-client">العميل</label>
                <select name="client_id" id="f-client" class="form-select">
                    <option value="">الكل</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= (int)$client['id'] ?>" <?= (int)$filters['client_id'] === (int)$client['id'] ? 'selected' : '' ?>>
                            <?= e($client['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted" for="f-status">حالة QR</label>
                <select name="status" id="f-status" class="form-select">
                    <option value="">الكل</option>
                    <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>مفعل</option>
                    <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>غير مفعل</option>
                    <option value="disabled" <?= $filters['status'] === 'disabled' ? 'selected' : '' ?>>معطل</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted" for="f-type">نوع الملف</label>
                <select name="type" id="f-type" class="form-select">
                    <option value="">الكل</option>
                    <option value="pdf" <?= $filters['type'] === 'pdf' ? 'selected' : '' ?>>PDF</option>
                    <option value="jpg" <?= $filters['type'] === 'jpg' ? 'selected' : '' ?>>JPG</option>
                    <option value="jpeg" <?= $filters['type'] === 'jpeg' ? 'selected' : '' ?>>JPEG</option>
                    <option value="png" <?= $filters['type'] === 'png' ? 'selected' : '' ?>>PNG</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted" for="f-from">من تاريخ</label>
                <input type="date" name="date_from" id="f-from" value="<?= e($filters['date_from']) ?>" class="form-control">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted" for="f-to">إلى تاريخ</label>
                <input type="date" name="date_to" id="f-to" value="<?= e($filters['date_to']) ?>" class="form-control">
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-fill" type="submit">
                    <i class="bi bi-funnel"></i> تطبيق الفلاتر
                </button>
                <a href="<?= url('/admin/documents') ?>" class="btn btn-light flex-fill" title="مسح الفلاتر">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ===== الجدول ===== -->
<div class="card card-table">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-mobile-card">
            <thead>
            <tr>
                <th>رقم المستند</th>
                <th>اسم الملف</th>
                <th>العميل</th>
                <th>النوع</th>
                <th>تاريخ الرفع</th>
                <th>حالة QR</th>
                <th>مرات التحقق</th>
                <th class="text-end">الإجراءات</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$documents): ?>
                <tr>
                    <td colspan="8">
                        <div class="empty-state py-5">
                            <div class="empty-icon"><i class="bi bi-files"></i></div>
                            <div class="empty-title">لا توجد مستندات مطابقة</div>
                            <div class="empty-hint">جرّب تعديل الفلاتر أو ارفع مستندًا جديدًا</div>
                            <a href="<?= url('/admin/documents/upload') ?>" class="btn btn-primary btn-sm">
                                <i class="bi bi-cloud-arrow-up"></i> رفع مستند جديد
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
                            <span class="text-truncate d-inline-block" style="max-width: 180px;" title="<?= e($doc['original_name']) ?>">
                                <?= e($doc['original_name']) ?>
                            </span>
                        </div>
                    </td>
                    <td data-label="العميل" class="text-muted"><?= e($doc['client_name'] ?? '—') ?></td>
                    <td data-label="النوع"><span class="badge-type"><?= e(strtoupper(file_extension($doc['original_name']))) ?></span></td>
                    <td data-label="تاريخ الرفع" class="text-muted small"><?= e(format_date($doc['created_at'])) ?></td>
                    <td data-label="حالة QR">
                        <?= document_status_badge($doc['status']) ?>
                        <?php if ($doc['qr_path']): ?>
                            <i class="bi bi-check-circle-fill text-success small ms-1" title="تم إنشاء QR"></i>
                        <?php endif; ?>
                    </td>
                    <td data-label="التحقق"><span class="badge-count"><?= (int)$doc['verification_count'] ?> مرة</span></td>
                    <td class="text-end table-actions-cell">
                        <div class="table-actions justify-content-end">
                            <a href="<?= url('/admin/documents/' . $doc['id'] . '/viewer') ?>" class="btn-icon" title="عرض المستند">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="<?= url('/admin/documents/' . $doc['id'] . '/download') ?>" class="btn-icon" title="تحميل المستند">
                                <i class="bi bi-download"></i>
                            </a>

                            <form method="post" action="<?= url('/admin/documents/' . $doc['id'] . '/replace') ?>"
                                  enctype="multipart/form-data" class="d-inline" data-replace-form>
                                <?= csrf_field() ?>
                                <label class="btn-icon" title="استبدال الملف (نفس رقم المستند ورمز QR)" style="cursor: pointer;">
                                    <i class="bi bi-file-earmark-arrow-up"></i>
                                    <input type="file" name="replacement" accept=".pdf,.jpg,.jpeg,.png" class="d-none">
                                </label>
                            </form>

                            <?php if (!$doc['qr_path']): ?>
                                <form method="post" action="<?= url('/admin/documents/' . $doc['id'] . '/qr/generate') ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn-icon" title="إنشاء QR">
                                        <i class="bi bi-qr-code"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="<?= url('/admin/documents/' . $doc['id'] . '/qr') ?>" class="btn-icon" title="عرض QR">
                                    <i class="bi bi-qr-code-scan"></i>
                                </a>
                            <?php endif; ?>

                            <?php if ($doc['status'] !== 'active'): ?>
                                <form method="post" action="<?= url('/admin/documents/' . $doc['id'] . '/activate') ?>"
                                      data-confirm="تفعيل رمز QR"
                                      data-confirm-variant="success"
                                      data-confirm-submit="تفعيل"
                                      data-message="هل تريد تفعيل رمز QR لهذا المستند؟ سيصبح رابط التحقق متاحًا للجميع.">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn-icon" title="تفعيل QR" style="color: var(--success);">
                                        <i class="bi bi-check2-circle"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="post" action="<?= url('/admin/documents/' . $doc['id'] . '/disable') ?>"
                                      data-confirm="تعطيل رمز QR"
                                      data-confirm-variant="warning"
                                      data-confirm-submit="تعطيل"
                                      data-message="هل أنت متأكد من تعطيل رمز QR؟ سيؤدي التعطيل إلى إظهار «المستند غير متاح» عند مسحه.">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn-icon" title="تعطيل QR" style="color: var(--danger);">
                                        <i class="bi bi-slash-circle"></i>
                                    </button>
                                </form>
                            <?php endif; ?>

                            <form method="post" action="<?= url('/admin/documents/' . $doc['id'] . '/delete') ?>"
                                  data-confirm="حذف المستند"
                                  data-confirm-variant="danger"
                                  data-message="سيتم حذف المستند وملفه ورمز QR وجميع سجلات التحقق نهائيًا. هل أنت متأكد؟">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn-icon btn-danger-hover" title="حذف المستند">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($paginator->hasPages()): ?>
        <div class="card-footer-toolbar">
            <small class="text-muted">إجمالي <?= number_format($paginator->total()) ?> مستند</small>
            <?= $paginator->links() ?>
        </div>
    <?php endif; ?>
</div>
