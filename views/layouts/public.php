<?php
/**
 * تخطيط عام (صفحة التحقق العامة)
 * المتغيرات المتوفرة: $content, $pageTitle, $setting, $settings, $flashes
 */
$systemName = (string)$setting('system_name', 'نظام التحقق من المستندات');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'التحقق من المستند') ?> | <?= e($systemName) ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="/assets/vendor/bootstrap/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="/assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%232b5df0'/><path d='M50 22 74 32v20c0 14-9.6 22-24 26-14.4-4-24-12-24-26V32z' fill='white'/><path d='M44 56l-8-8-5 5 13 13 25-25-5-5z' fill='%232b5df0'/></svg>">
</head>
<body class="public-body">
    <?= $content ?>

    <footer class="public-footer">
        <?= e(setting('org_name', $systemName)) ?> &copy; <?= date('Y') ?> — جميع الحقوق محفوظة
    </footer>

    <div class="toast-container position-fixed top-0 end-0 p-3" id="flashToasts">
        <?php foreach ($flashes as $flash): ?>
            <div class="toast-server d-none"
                 data-type="<?= e($flash['type'] === 'error' ? 'danger' : $flash['type']) ?>"
                 data-message="<?= e($flash['message']) ?>"></div>
        <?php endforeach; ?>
    </div>

    <script src="/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>