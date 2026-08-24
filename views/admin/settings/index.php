<?php /** إعدادات النظام */
$errors = $errors ?? [];
$accountErrors = $accountErrors ?? [];
$old = [
    'system_name' => old('system_name', setting('system_name', 'نظام التحقق من المستندات')),
];
?>
<div class="row g-3 justify-content-center">
    <div class="col-12 col-xl-8">
        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle ms-1"></i>
                <?php foreach ($errors as $err): ?>
                    <div><?= e($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- ===== الهوية ===== -->
        <form method="post" action="<?= url('/admin/settings') ?>">
            <?= csrf_field() ?>
            <div class="card card-page mb-3">
                <div class="card-header-custom">
                    <div class="card-header-icon"><i class="bi bi-building"></i></div>
                    <div>
                        <h5 class="mb-0">هوية النظام والجهة</h5>
                        <small class="text-muted">تظهر هذه البيانات في لوحة التحكم وصفحات التحقق</small>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="system_name">اسم النظام <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="system_name" name="system_name"
                                   value="<?= e($old['system_name']) ?>" required>
                        </div>
                        <div class="col-12 pt-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg px-4">
                                <i class="bi bi-check-lg ms-1"></i> حفظ الإعدادات
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- ===== حساب المدير ===== -->
        <div class="card card-page mb-3" id="account">
            <div class="card-header-custom">
                <div class="card-header-icon"><i class="bi bi-person-gear"></i></div>
                <div>
                    <h5 class="mb-0">حساب المدير</h5>
                    <small class="text-muted">البريد الإلكتروني وكلمة المرور الخاصان بتسجيل الدخول</small>
                </div>
            </div>
            <div class="card-body p-4">
                <?php if ((int)($user['must_change_password'] ?? 0) === 1): ?>
                    <div class="alert alert-warning d-flex align-items-start gap-2">
                        <i class="bi bi-shield-exclamation fs-5"></i>
                        <div>
                            <strong>كلمة المرور الافتراضية</strong>
                            <div class="small mt-1">يجب عليك تغيير كلمة المرور الافتراضية قبل متابعة استخدام النظام.</div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($accountErrors): ?>
                    <div class="alert alert-danger d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-circle fs-5"></i>
                        <div>
                            <?php foreach ($accountErrors as $err): ?>
                                <div><?= e($err) ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= url('/admin/settings/account') ?>" data-loading>
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="email">البريد الإلكتروني (لتسجيل الدخول) <span class="text-danger">*</span></label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email"
                                   value="<?= e(old('email', $user['email'] ?? '')) ?>" required dir="ltr">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="current_password">كلمة المرور الحالية <span class="text-danger">*</span></label>
                            <div class="password-wrap">
                                <input type="password" class="form-control form-control-lg" id="current_password"
                                       name="current_password" required autocomplete="current-password">
                                <button type="button" class="password-toggle" tabindex="-1" aria-label="إظهار كلمة المرور">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="new_password">كلمة المرور الجديدة</label>
                            <div class="password-wrap">
                                <input type="password" class="form-control form-control-lg" id="new_password"
                                       name="new_password" minlength="8" autocomplete="new-password">
                                <button type="button" class="password-toggle" tabindex="-1" aria-label="إظهار كلمة المرور">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="confirm_password">تأكيد كلمة المرور الجديدة</label>
                            <div class="password-wrap">
                                <input type="password" class="form-control form-control-lg" id="confirm_password"
                                       name="confirm_password" minlength="8" autocomplete="new-password">
                                <button type="button" class="password-toggle" tabindex="-1" aria-label="إظهار كلمة المرور">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">اترك كلمة المرور الجديدة فارغة إذا لا تريد تغييرها</div>
                        </div>
                        <div class="col-12 pt-2">
                            <button type="submit" class="btn btn-primary btn-lg px-4">
                                <span class="btn-label"><i class="bi bi-check-lg"></i> حفظ بيانات الحساب</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
