<?php
/**
 * صفحة التحقق العامة
 * الحالات: موثق (مفعل) / غير متاح (غير مفعل أو معطل) / غير موجود
 */
$logo = logo_data_uri();
$systemName = setting('system_name', 'نظام التحقق من المستندات');
$orgName = setting('org_name', '');
$isValid = $error === null && !empty($document);
?>
<div class="public-wrap">

    <!-- ===== حالة: مستند موثق ===== -->
    <?php if ($isValid): ?>

        <div class="verify-card">

            <div class="verify-header">
                <?php if ($logo): ?>
                    <img src="<?= $logo ?>" alt="شعار النظام" class="verify-logo">
                <?php else: ?>
                    <div class="verify-logo verify-logo-default"><i class="bi bi-shield-check"></i></div>
                <?php endif; ?>
                <div class="verify-title">
                    <h1>التحقق من المستند</h1>
                    <p><?= e($systemName) ?></p>
                </div>
            </div>

            <div class="verify-badge-wrap">
                <div class="verify-badge verify-badge-success">
                    <span class="verify-badge-icon">
                        <i class="bi bi-patch-check-fill"></i>
                    </span>

                    <div>
                        <strong>مستند موثق — QR مفعل</strong>
                        <small>تم التحقق من صحة هذا المستند إلكترونيًا</small>
                    </div>
                </div>
            </div>

            <div class="doc-info">

                <div class="doc-info-item">
                    <span class="doc-info-label">رقم المستند</span>
                    <span class="doc-info-value doc-number">
                        <?= e($document['doc_number']) ?>
                    </span>
                </div>

                <div class="doc-info-item">
                    <span class="doc-info-label">اسم الملف</span>
                    <span class="doc-info-value">
                        <?= e($document['original_name']) ?>
                    </span>
                </div>

                <div class="doc-info-item">
                    <span class="doc-info-label">تاريخ إصدار QR</span>
                    <span class="doc-info-value">
                        <?= e(format_date($document['qr_generated_at'])) ?>
                    </span>
                </div>

                <div class="doc-info-item">
                    <span class="doc-info-label">وقت التحقق</span>
                    <span class="doc-info-value">
                        <?= e(format_date(date('Y-m-d H:i:s'))) ?>
                    </span>
                </div>

            </div>

            <?php if ($isImage): ?>

                <div class="doc-viewer doc-viewer-image">
                    <img
                        src="<?= '/verify/' . $document['token'] . '/file' ?>"
                        alt="المستند"
                    >
                </div>

            <?php else: ?>

                <div class="doc-viewer">

                    <iframe
                        src="<?= '/verify/' . $document['token'] . '/file' ?>"
                        class="doc-iframe"
                        loading="lazy"
                        title="عرض المستند"
                    ></iframe>

                    <div class="doc-viewer-loading" id="viewerLoading">
                        <div class="spinner-border spinner-border-sm text-primary ms-2"></div>
                        جاري تحميل المستند...
                    </div>

                </div>

            <?php endif; ?>

            <div class="verify-actions">

                <a
                    href="<?= '/verify/' . $document['token'] . '/file' ?>"
                    target="_blank"
                    class="btn btn-primary btn-lg"
                >
                    <i class="bi bi-eye ms-1"></i>
                    عرض المستند بحجم كامل
                </a>

                <a
                    href="<?= '/verify/' . $document['token'] . '/download' ?>"
                    class="btn btn-light btn-lg"
                >
                    <i class="bi bi-download ms-1"></i>
                    تحميل المستند
                </a>

            </div>

            <div class="verify-footnote">
                <i class="bi bi-shield-check text-success"></i>

                <span>
                    تم التحقق من هذا المستند عبر نظام <?= e($systemName) ?>.
                </span>
            </div>

        </div>

    <!-- ===== حالة: غير متاح ===== -->
    <?php elseif ($error === 'unavailable'): ?>

        <div class="verify-card verify-card-state">

            <div class="state-icon state-icon-warning">
                <i class="bi bi-hourglass-split"></i>
            </div>

            <h2>المستند غير متاح للتحقق حاليًا</h2>

            <p class="text-muted">
                هذا المستند غير متاح للتحقق حاليًا.
            </p>

            <div class="alert alert-warning d-inline-flex align-items-center gap-2 mx-auto">

                <i class="bi bi-info-circle"></i>

                <span>
                    يرجى التواصل مع الجهة المصدرة للمستند لمزيد من المعلومات.
                </span>

            </div>

        </div>

    <!-- ===== حالة: غير موجود ===== -->
    <?php else: ?>

        <div class="verify-card verify-card-state">

            <div class="state-icon state-icon-danger">
                <i class="bi bi-x-circle"></i>
            </div>

            <h2>المستند غير موجود</h2>

            <p class="text-muted">
                المستند غير موجود.
            </p>

            <div class="alert alert-danger d-inline-flex align-items-center gap-2 mx-auto">

                <i class="bi bi-shield-exclamation"></i>

                <span>
                    قد يكون الرابط غير صحيح أو أن المستند تم حذفه من النظام.
                </span>

            </div>

        </div>

    <?php endif; ?>

</div>