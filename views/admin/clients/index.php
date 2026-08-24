<?php /** قائمة العملاء */ ?>
<div class="page-head">
    <div>
        <h1 class="page-head-title">إدارة العملاء</h1>
        <p class="page-head-sub">تنظيم المستندات حسب العميل — لا يحتاج العميل لإدخال بيانات المستندات</p>
    </div>
    <div class="page-head-actions">
        <a href="<?= url('/admin/clients/create') ?>" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> إضافة عميل
        </a>
    </div>
</div>

<div class="card card-table">
    <div class="card-body pb-0 pt-3 px-3 px-md-4">
        <form method="get" action="<?= url('/admin/clients') ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-6 col-lg-5">
                <label class="form-label small text-muted" for="q">بحث</label>
                <div class="input-group">
                    <input type="text" name="q" id="q" value="<?= e($q) ?>" class="form-control"
                           placeholder="اسم العميل، الهاتف، البريد...">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </div>
            <div class="col-12 col-md-2">
                <?php if ($q !== ''): ?>
                    <a href="<?= url('/admin/clients') ?>" class="btn btn-light w-100 mt-md-4">مسح البحث</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-mobile-card">
            <thead>
            <tr>
                <th>العميل</th>
                <th>رقم الهاتف</th>
                <th>البريد الإلكتروني</th>
                <th>عدد المستندات</th>
                <th>تاريخ الإضافة</th>
                <th class="text-end">الإجراءات</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$clients): ?>
                <tr>
                    <td colspan="6">
                        <div class="empty-state py-5">
                            <div class="empty-icon"><i class="bi bi-people"></i></div>
                            <div class="empty-title"><?= $q !== '' ? 'لا يوجد عملاء مطابقون للبحث' : 'لا يوجد عملاء بعد' ?></div>
                            <div class="empty-hint"><?= $q !== '' ? 'جرّب كلمة بحث مختلفة' : 'أضف أول عميل لتنظيم مستنداته' ?></div>
                            <?php if ($q === ''): ?>
                                <a href="<?= url('/admin/clients/create') ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-person-plus"></i> إضافة أول عميل
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
            <?php foreach ($clients as $client): ?>
                <tr>
                    <td data-label="العميل">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar avatar-sm bg-primary-soft text-primary" style="background: var(--primary-soft); color: var(--primary);"><?= e(mb_substr($client['name'], 0, 1)) ?></div>
                            <strong><?= e($client['name']) ?></strong>
                        </div>
                    </td>
                    <td data-label="الهاتف" dir="ltr" class="text-end small"><?= e($client['phone'] ?: '—') ?></td>
                    <td data-label="البريد" dir="ltr" class="text-end small"><?= e($client['email'] ?: '—') ?></td>
                    <td data-label="المستندات">
                        <span class="badge-count"><?= (int)$client['documents_count'] ?> مستند</span>
                    </td>
                    <td data-label="تاريخ الإضافة" class="text-muted small"><?= e(format_date_short($client['created_at'])) ?></td>
                    <td class="text-end table-actions-cell">
                        <div class="table-actions justify-content-end">
                            <a href="<?= url('/admin/clients/' . $client['id'] . '/documents') ?>" class="btn-icon" title="عرض المستندات">
                                <i class="bi bi-folder2-open"></i>
                            </a>
                            <a href="<?= url('/admin/clients/' . $client['id'] . '/edit') ?>" class="btn-icon" title="تعديل">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="post" action="<?= url('/admin/clients/' . $client['id'] . '/delete') ?>"
                                  data-confirm="حذف العميل"
                                  data-confirm-variant="danger"
                                  data-message="هل أنت متأكد من حذف العميل «<?= e($client['name']) ?>»؟ لا يمكن حذف عميل مرتبط بمستندات.">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn-icon btn-danger-hover" title="حذف">
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
</div>
