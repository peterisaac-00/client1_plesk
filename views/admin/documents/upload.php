<?php /** صفحة رفع مستند جديد */
$errors = $errors ?? [];
$preselectedClient = (int)($_GET['client_id'] ?? 0);
$maxSize = (int)config('max_upload_size', 20971520);
?>
<div class="page-head">
    <div>
        <a href="<?= url('/admin/documents') ?>" class="btn btn-soft-primary btn-sm mb-2">
            <i class="bi bi-arrow-right"></i> العودة للمستندات
        </a>
        <h1 class="page-head-title">رفع مستند جديد</h1>
        <p class="page-head-sub">سيتم إنشاء رمز QR فريد للمستند تلقائيًا بعد الرفع</p>
    </div>
</div>

<div class="card card-page mx-auto" style="max-width: 760px;">
    <div class="card-body p-4 p-md-5">
        <?php if ($errors): ?>
            <div class="alert alert-danger d-flex align-items-start gap-2 mb-4">
                <i class="bi bi-exclamation-circle fs-5"></i>
                <div>
                    <?php foreach ($errors as $err): ?>
                        <div><?= e($err) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= url('/admin/documents/store') ?>" enctype="multipart/form-data" id="uploadForm">
            <?= csrf_field() ?>

            <div class="mb-4">
                <label class="form-label" for="document">الملف <span class="text-danger">*</span></label>
                <label class="upload-dropzone" for="document" id="dropzone">
                    <input type="file" name="document" id="document" class="d-none"
                           accept=".pdf,.jpg,.jpeg,.png" required
                           data-max-size="<?= $maxSize ?>">
                    <div class="upload-dropzone-inner" id="dropzoneInner">
                        <div class="upload-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                        <p class="mb-1 fw-bold fs-5" style="color: var(--ink);">اسحب المستند هنا أو اضغط لاختيار ملف</p>
                        <small class="upload-hint d-block mb-2">PDF · JPG · JPEG · PNG — بحد أقصى <?= human_filesize($maxSize) ?></small>
                        <span class="btn btn-soft-primary btn-sm mt-2"><i class="bi bi-folder2-open"></i> اختيار ملف</span>
                    </div>
                    <div class="upload-preview d-none" id="uploadPreview">
                        <span class="file-icon"><i class="bi bi-file-earmark-check"></i></span>
                        <div class="upload-preview-meta">
                            <strong id="fileName"></strong>
                            <small id="fileSize"></small>
                        </div>
                        <button type="button" class="upload-preview-remove" id="uploadRemove" title="حذف الملف">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </label>
                <div class="form-text mt-2">المستند نفسه هو مصدر البيانات — سيتم إنشاء رمز QR فريد له تلقائيًا</div>
            </div>

            <div class="mb-4">
                <label class="form-label" for="client_id">العميل المرتبط (اختياري)</label>
                <select name="client_id" id="client_id" class="form-select form-select-lg">
                    <option value="">— بدون عميل —</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= (int)$client['id'] ?>" <?= $preselectedClient === (int)$client['id'] ? 'selected' : '' ?>>
                            <?= e($client['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">يمكنك اختيار العميل لاحقًا من صفحة إدارة العملاء.</div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-lg px-4" id="uploadBtn">
                    <span class="btn-label"><i class="bi bi-cloud-arrow-up"></i> رفع المستند</span>
                </button>
                <a href="<?= url('/admin/documents') ?>" class="btn btn-light btn-lg">إلغاء</a>
            </div>
        </form>
    </div>
</div>
