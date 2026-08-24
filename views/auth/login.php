<?php /** صفحة تسجيل الدخول */ $errors = $errors ?? []; ?>
<div class="auth-card">
    <div class="auth-card-header">
        <h2>تسجيل الدخول</h2>
        <p>أدخل بياناتك للوصول إلى لوحة التحكم</p>
    </div>

    <?php if ($locked): ?>
        <div class="alert alert-danger d-flex align-items-start gap-2 mb-4">
            <i class="bi bi-shield-lock fs-5"></i>
            <div>
                <strong>تم إيقاف المحاولات مؤقتًا</strong>
                <div class="small mt-1"><?= e($errors[0] ?? 'تم إيقاف المحاولات مؤقتًا بسبب محاولات فاشلة متكررة. يرجى المحاولة بعد 15 دقيقة.') ?></div>
            </div>
        </div>
    <?php elseif ($errors): ?>
        <div class="alert alert-danger d-flex align-items-start gap-2 mb-4">
            <i class="bi bi-exclamation-circle fs-5"></i>
            <div>
                <?php foreach ($errors as $err): ?>
                    <div><?= e($err) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= url('/login') ?>" novalidate data-loading>
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label" for="email">البريد الإلكتروني</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" class="form-control" id="email" name="email"
                       placeholder="name@example.com" required autofocus autocomplete="username"
                       value="<?= e(old('email')) ?>" dir="ltr" style="text-align: right;">
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label" for="password">كلمة المرور</label>
            <div class="password-wrap">
                <input type="password" class="form-control" id="password" name="password"
                       placeholder="••••••••" required autocomplete="current-password">
                <button type="button" class="password-toggle" tabindex="-1" aria-label="إظهار كلمة المرور">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-lg w-100" id="loginBtn">
            <span class="btn-label"><i class="bi bi-box-arrow-in-left"></i> دخول</span>
        </button>
    </form>
</div>
