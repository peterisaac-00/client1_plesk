<?php
/**
 * تخطيط عارض المستند (صفحة مستقلة)
 * المتغيرات المتوفرة: $content, $pageTitle, $setting, $settings
 */
$systemName = (string)$setting('system_name', 'نظام التحقق من المستندات');
$logo = logo_data_uri();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'عرض المستند') ?> | <?= e($systemName) ?></title>
    <link rel="stylesheet" href="<?= url('/assets/vendor/bootstrap/bootstrap.rtl.min.css') ?>">
    <link rel="stylesheet" href="<?= url('/assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= url('/assets/css/app.css') ?>">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%232b5df0'/><path d='M50 22 74 32v20c0 14-9.6 22-24 26-14.4-4-24-12-24-26V32z' fill='white'/><path d='M44 56l-8-8-5 5 13 13 25-25-5-5z' fill='%232b5df0'/></svg>">
</head>
<body class="doc-shell">
    <?= $content ?>
</body>
</html>
