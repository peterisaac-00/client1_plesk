<?php
/** نموذج إضافة / تعديل عميل */
$isEdit = $client !== null;
$errors = $errors ?? [];
$old = [
    'name' => old('name', $isEdit ? $client['name'] : ''),
];
?>
<div class="page-head">
    <div>
        <a href="<?= url('/admin/clients') ?>" class="btn btn-soft-primary btn-sm mb-2">
            <i class="bi bi-arrow-right"></i> العودة للعملاء
        </a>
        <h1 class="page-head-title"><?= $isEdit ? 'تعديل بيانات العميل' : 'إضافة عميل جديد' ?></h1>
        <p class="page-head-sub">تُستخدم بيانات العميل لتنظيم المستندات فقط</p>
    </div>
</div>

<div class="card card-page mx-auto" style="max-width: 680px;">
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

        <form method="post" action="<?= url($isEdit ? '/admin/clients/' . $client['id'] . '/update' : '/admin/clients/store') ?>" data-loading>
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label" for="name">اسم العميل <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg" id="name" name="name"
                           value="<?= e($old['name']) ?>" required>
                </div>
                <div class="col-12 d-flex gap-2 pt-3">
                    <button type="submit" class="btn btn-primary btn-lg px-4">
                        <span class="btn-label"><i class="bi bi-check-lg"></i> <?= $isEdit ? 'حفظ التعديلات' : 'إضافة العميل' ?></span>
                    </button>
                    <a href="<?= url('/admin/clients') ?>" class="btn btn-light btn-lg">إلغاء</a>
                </div>
            </div>
        </form>
    </div>
</div>
