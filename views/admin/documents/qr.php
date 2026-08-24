<?php /** صفحة عرض رمز QR */
$verifyLink = $document['verify_url'] ?: url('/verify/' . $document['token']);
?>
<div class="page-head">
    <div>
        <a href="<?= url('/admin/documents') ?>" class="btn btn-soft-primary btn-sm mb-2">
            <i class="bi bi-arrow-right"></i> العودة للمستندات
        </a>
        <h1 class="page-head-title">رمز QR للمستند</h1>
        <p class="page-head-sub"><span class="doc-number"><?= e($document['doc_number']) ?></span></p>
    </div>
    <div class="page-head-actions">
        <?= document_status_badge($document['status']) ?>
    </div>
</div>

<div class="row g-4 justify-content-center">
    <div class="col-12 col-lg-7 col-xl-6">
        <div class="card qr-card">
            <div class="card-body p-4 p-md-5 text-center">
                <div class="qr-frame" id="printArea">
                    <?php if ($document['qr_path']): ?>
                        <img src="<?= url('/admin/documents/' . $document['id'] . '/qr/image') ?>"
                             alt="رمز QR" class="qr-image" id="qrImage">
                        <div class="qr-brand">
                            <i class="bi bi-shield-check text-primary"></i>
                            <?= e($setting('system_name', 'نظام التحقق من المستندات')) ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state py-5" style="min-width: 260px;">
                            <div class="empty-icon"><i class="bi bi-qr-code"></i></div>
                            <div class="empty-title">لم يتم إنشاء رمز QR بعد</div>
                            <div class="empty-hint">أنشئ الرمز ليتمكن الجميع من التحقق من المستند</div>
                            <form method="post" action="<?= url('/admin/documents/' . $document['id'] . '/qr/generate') ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-magic"></i> إنشاء رمز QR الآن
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($document['qr_path']): ?>
                    <div class="verify-link-box mb-4">
                        <small class="small text-muted mb-1">رابط التحقق</small>
                        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                            <span class="fw-bold text-truncate" style="max-width: 320px;" id="verifyLink"><?= e($verifyLink) ?></span>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-light" data-copy data-copy-source="verifyLink" title="نسخ الرابط">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                                <a href="<?= e($verifyLink) ?>" target="_blank" class="btn btn-sm btn-soft-primary">
                                    <i class="bi bi-box-arrow-up-left"></i> فتح الرابط
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <a href="<?= url('/admin/documents/' . $document['id'] . '/qr/download') ?>" class="btn btn-primary">
                            <i class="bi bi-download"></i> تحميل QR
                        </a>

                        <?php if ($document['status'] === 'inactive'): ?>
                            <form method="post" action="<?= url('/admin/documents/' . $document['id'] . '/activate') ?>"
                                  data-confirm="تفعيل رمز QR"
                                  data-confirm-variant="success"
                                  data-confirm-submit="تفعيل"
                                  data-message="هل تريد تفعيل رمز QR لهذا المستند؟ سيصبح رابط التحقق متاحًا للجميع.">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check2-circle"></i> تفعيل QR
                                </button>
                            </form>
                        <?php elseif ($document['status'] === 'active'): ?>
                            <form method="post" action="<?= url('/admin/documents/' . $document['id'] . '/disable') ?>"
                                  data-confirm="تعطيل رمز QR"
                                  data-confirm-variant="warning"
                                  data-confirm-submit="تعطيل"
                                  data-message="هل أنت متأكد من تعطيل رمز QR؟ سيصبح رابط التحقق غير متاح حتى إعادة التفعيل.">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="bi bi-slash-circle"></i> تعطيل QR
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5 col-xl-4">
        <div class="card">
            <div class="card-header-custom">
                <div class="card-header-icon"><i class="bi bi-file-earmark-text"></i></div>
                <div>
                    <h5 class="mb-0">تفاصيل المستند</h5>
                    <small class="text-muted">معلومات الملف والرمز</small>
                </div>
            </div>
            <div class="card-body p-4">
                <dl class="detail-list mb-0">
                    <div class="detail-item">
                        <dt>رقم المستند</dt>
                        <dd class="text-truncate" style="direction: ltr;"><?= e($document['doc_number']) ?></dd>
                    </div>
                    <div class="detail-item">
                        <dt>اسم الملف</dt>
                        <dd class="text-truncate" title="<?= e($document['original_name']) ?>"><?= e($document['original_name']) ?></dd>
                    </div>
                    <div class="detail-item">
                        <dt>النوع</dt>
                        <dd><span class="badge-type"><?= e(strtoupper(file_extension($document['original_name']))) ?></span></dd>
                    </div>
                    <div class="detail-item">
                        <dt>الحجم</dt>
                        <dd><?= e(human_filesize((int)$document['file_size'])) ?></dd>
                    </div>
                    <div class="detail-item">
                        <dt>تاريخ الرفع</dt>
                        <dd><?= e(format_date($document['created_at'])) ?></dd>
                    </div>
                    <div class="detail-item">
                        <dt>تاريخ إصدار QR</dt>
                        <dd><?= e(format_date($document['qr_generated_at'])) ?></dd>
                    </div>
                    <div class="detail-item">
                        <dt>حالة QR</dt>
                        <dd><?= document_status_badge($document['status']) ?></dd>
                    </div>
                </dl>

                <div class="d-flex gap-2 mt-4">
                    <a href="<?= url('/admin/documents/' . $document['id'] . '/viewer') ?>" class="btn btn-light flex-fill">
                        <i class="bi bi-eye"></i> عرض المستند
                    </a>
                    <a href="<?= url('/admin/documents/' . $document['id'] . '/download') ?>" class="btn btn-light flex-fill">
                        <i class="bi bi-download"></i> تحميل
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
