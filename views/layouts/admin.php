<?php
/**
 * تخطيط لوحة التحكم — Professional SaaS
 * المتغيرات المتوفرة: $content, $pageTitle, $setting, $settings, $flashes, $activeMenu, $user, $recentVerifications, $loadCharts
 */
$logo = logo_data_uri();
$nav = [
    'dashboard' => ['/admin', 'bi-grid-1x2', 'الرئيسية'],
    'clients' => ['/admin/clients', 'bi-people', 'العملاء'],
    'documents' => ['/admin/documents', 'bi-file-earmark-text', 'المستندات'],
    'verifications' => ['/admin/verifications', 'bi-clipboard2-check', 'سجل التحقق'],
    'settings' => ['/admin/settings', 'bi-sliders', 'الإعدادات'],
];
$userName = $user['name'] ?? 'المدير';
$userInitial = mb_substr($userName, 0, 1);
$systemName = (string)$setting('system_name', 'نظام التحقق من المستندات');
$pageTitle = $pageTitle ?? 'لوحة التحكم';
$breadcrumbName = $nav[$activeMenu][2] ?? null;
$verifCount = isset($recentVerifications) ? count($recentVerifications) : 0;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | <?= e($systemName) ?></title>
    <link rel="stylesheet" href="<?= url('/assets/vendor/bootstrap/bootstrap.rtl.min.css') ?>">
    <link rel="stylesheet" href="<?= url('/assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= url('/assets/css/app.css') ?>">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%232b5df0'/><path d='M50 22 74 32v20c0 14-9.6 22-24 26-14.4-4-24-12-24-26V32z' fill='white'/><path d='M44 56l-8-8-5 5 13 13 25-25-5-5z' fill='%232b5df0'/></svg>">
</head>
<body class="admin-body">
<div class="admin-shell">

    <!-- ===== الشريط الجانبي ===== -->
    <aside class="sidebar" id="adminSidebar" aria-label="القائمة الجانبية">
        <div class="sidebar-brand">
            <?php if ($logo): ?>
                <img src="<?= $logo ?>" alt="الشعار" class="sidebar-logo">
            <?php else: ?>
                <div class="sidebar-logo-icon"><i class="bi bi-shield-check"></i></div>
            <?php endif; ?>
            <div class="sidebar-brand-text">
                <strong><?= e($systemName) ?></strong>
                <span>لوحة التحكم</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <?php foreach ($nav as $key => $item): ?>
                <a href="<?= url($item[0]) ?>"
                   class="sidebar-link <?= $activeMenu === $key ? 'active' : '' ?>">
                    <span><?= $item[2] ?></span>
                </a>
            <?php endforeach; ?>

            <div class="sidebar-section-title">الحساب</div>
            <form method="post" action="<?= url('/logout') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="sidebar-link sidebar-link-danger sidebar-link-btn">
                    <span>تسجيل الخروج</span>
                </button>
            </form>
        </nav>

        <div class="sidebar-user">
            <div class="avatar"><?= e($userInitial) ?></div>
            <div class="sidebar-user-text">
                <strong><?= e($userName) ?></strong>
                <span><?= e($user['email'] ?? '') ?></span>
            </div>
        </div>
    </aside>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- ===== المحتوى الرئيسي ===== -->
    <main class="main">
        <header class="topbar">
            <div class="d-flex align-items-center gap-3 min-w-0">
                <button class="btn-icon d-lg-none" type="button" id="sidebarToggle" aria-label="فتح القائمة">
                    <i class="bi bi-list"></i>
                </button>
                <div class="min-w-0">
                    <h2 class="topbar-title text-truncate"><?= e($pageTitle) ?></h2>
                    <nav class="breadcrumb-top" aria-label="breadcrumb">
                        <a href="<?= url('/admin') ?>">لوحة التحكم</a>
                        <?php if ($breadcrumbName && $activeMenu !== 'dashboard'): ?>
                            <i class="bi bi-chevron-left"></i>
                            <span><?= e($breadcrumbName) ?></span>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>

            <div class="topbar-actions">
                <!-- الإشعارات -->
                <div class="dropdown">
                    <button class="btn-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                            aria-label="الإشعارات">
                        <i class="bi bi-bell"></i>
                        <?php if ($verifCount > 0): ?>
                            <span class="icon-badge"><?= $verifCount ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width: 300px;">
                        <div class="dropdown-header d-flex justify-content-between align-items-center">
                            <span>آخر عمليات التحقق</span>
                            <a href="<?= url('/admin/verifications') ?>" class="small">عرض الكل</a>
                        </div>
                        <?php if ($verifCount === 0): ?>
                            <div class="px-3 py-3 text-center">
                                <i class="bi bi-bell-slash d-block mb-2" style="font-size: 22px; color: var(--faint);"></i>
                                <small class="text-muted">لا توجد إشعارات جديدة</small>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recentVerifications as $v): ?>
                                <a class="dropdown-item" href="<?= url('/admin/verifications') ?>">
                                    <span class="bg-success-soft text-success d-inline-grid" style="width: 30px; height: 30px; border-radius: 8px; place-items: center;">
                                        <i class="bi bi-qr-code-scan" style="font-size: 14px;"></i>
                                    </span>
                                    <span class="min-w-0 flex-grow-1">
                                        <span class="d-block text-truncate fw-bold"><?= e($v['doc_number']) ?></span>
                                        <small class="text-muted"><?= e($v['original_name']) ?></small>
                                    </span>
                                    <small class="text-muted flex-shrink-0"><?= e(format_date_short($v['created_at'])) ?></small>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- قائمة المستخدم -->
                <div class="dropdown">
                    <button class="topbar-user" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar avatar-sm"><?= e($userInitial) ?></div>
                        <span><?= e($userName) ?></span>
                        <i class="bi bi-chevron-down small text-muted"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li class="px-3 py-2">
                            <strong class="d-block"><?= e($userName) ?></strong>
                            <small class="text-muted d-block text-truncate" style="max-width: 220px;"><?= e($user['email'] ?? '') ?></small>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?= url('/admin/settings') ?>">
                                <i class="bi bi-gear text-muted"></i>الإعدادات
                            </a>
                        </li>
                        <li>
                            <form method="post" action="<?= url('/logout') ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right"></i>تسجيل الخروج
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <div class="content">
            <?= $content ?>
        </div>

        <footer class="main-footer">
            <?= e($systemName) ?> — جميع الحقوق محفوظة &copy; <?= date('Y') ?>
        </footer>
    </main>
</div>

<!-- ===== حاوية الإشعارات ===== -->
<div class="toast-container position-fixed top-0 end-0 p-3" id="flashToasts" style="z-index: 1090;">
    <?php foreach ($flashes as $flash): ?>
        <div class="toast-server d-none"
             data-type="<?= e($flash['type'] === 'error' ? 'danger' : $flash['type']) ?>"
             data-message="<?= e($flash['message']) ?>"></div>
    <?php endforeach; ?>
</div>

<!-- ===== نافذة تأكيد العمليات ===== -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true" aria-labelledby="confirmTitle">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 430px;">
        <div class="modal-content">
            <div class="modal-body text-center p-4 p-md-5">
                <div class="confirm-icon confirm-icon-danger"><i class="bi bi-trash"></i></div>
                <h4 class="confirm-title mb-2" id="confirmTitle">تأكيد العملية</h4>
                <p class="confirm-message mb-4" id="confirmMessage">هل أنت متأكد؟</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4" data-confirm-cancel>إلغاء</button>
                    <button type="button" class="btn btn-danger px-4" data-confirm-submit>
                        <span class="btn-label">نعم، متأكد</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= url('/assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<?php if (!empty($loadCharts)): ?>
    <script src="<?= url('/assets/vendor/chartjs/chart.umd.min.js') ?>"></script>
<?php endif; ?>
<script src="<?= url('/assets/js/app.js') ?>"></script>
</body>
</html>
