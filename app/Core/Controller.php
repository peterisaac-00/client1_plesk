<?php

namespace App\Core;

/**
 * الوحدة الأساسية لوحدات التحكم
 * توفر: التحقق من CSRF، عرض الصفحات، الحماية، وإعادة التوجيه
 */
class Controller
{
    protected string $layout = 'public';
    protected bool $requireAuth = false;

    /** تنفيذ إجراء داخل وحدة التحكم مع التحقق المسبق */
    public function run(string $action, array $params = []): void
    {
        if (!method_exists($this, $action)) {
            http_response_code(404);
            exit;
        }

        // حماية الصفحات الإدارية
        if ($this->requireAuth) {
            $this->requireAuth();
        }

        // التحقق من CSRF في كل طلبات POST
        if (is_post()) {
            csrf_check();
        }

        // إرسال ترويسات الأمان
        $this->securityHeaders();

        $this->$action(...$params);
    }

    /** عرض صفحة مع تخطيط */
    protected function view(string $path, array $data = []): void
    {
        $data['settings'] = App::settings();
        $data['setting'] = function (string $key, $default = '') {
            return App::setting($key, $default);
        };
        $data['flashes'] = App::flashes();
        $data['layout'] = $this->layout;
        $data['activeMenu'] = $data['activeMenu'] ?? '';

        extract($data, EXTR_SKIP);
        ob_start();
        require BASE_PATH . '/views/' . $path . '.php';
        $content = ob_get_clean();

        require BASE_PATH . '/views/layouts/' . $layout . '.php';
    }

    /** إعادة توجيه مع رسالة */
    protected function redirect(string $path, ?string $flashType = null, ?string $flashMessage = null): void
    {
        if ($flashType && $flashMessage) {
            App::flash($flashType, $flashMessage);
        }
        redirect($path);
    }

    /** منع الوصول غير المصرح به */
    protected function requireAuth(): void
    {
        if (!App::user()) {
            // حفظ الصفحة المطلوبة لإعادة التوجيه إليها بعد الدخول
            $_SESSION['_redirect_after_login'] = App::baseUrl() . $_SERVER['REQUEST_URI'];
            redirect('/login');
        }

        // إجبار تغيير كلمة المرور عند أول دخول
        $mustChange = (bool)($_SESSION['user']['must_change_password'] ?? 0);
        $currentPath = strtok($_SERVER['REQUEST_URI'], '?');
        $isSettingsPage = (bool)str_contains($currentPath, '/admin/settings');

        if ($mustChange && !$isSettingsPage) {
            App::flash('warning', 'يجب عليك تغيير كلمة المرور الافتراضية قبل متابعة استخدام النظام.');
            redirect('/admin/settings');
        }
    }

    /** ترويسات الأمان */
    protected function securityHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('X-XSS-Protection: 1; mode=block');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; font-src 'self' data:; frame-src 'self'; frame-ancestors 'self'; object-src 'none'; base-uri 'self'; form-action 'self'; connect-src 'self'");
    }
}
