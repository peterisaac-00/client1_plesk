<?php
/**
 * دوال مساعدة عامة
 */

use App\Core\App;

/** تهريب النصوص لمنع XSS */
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** قراءة إعداد عام من ملف الإعدادات */
function config(string $key, $default = null)
{
    return App::config($key, $default);
}

/** قراءة إعداد من جدول الإعدادات (قابل للتعديل من لوحة التحكم) */
function setting(string $key, $default = '')
{
    return App::setting($key, $default);
}

/** توليد رابط كامل */
function url(string $path = ''): string
{
    $base = App::baseUrl();
    if ($path === '') {
        return $base;
    }
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

/** إعادة توجيه */
function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

/** التحقق من أن الطلب POST */
function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/** قراءة قيمة من POST مع تنظيفها */
function input(string $key, $default = null)
{
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

/** حفظ قيمة قديمة للنماذج (بعد خطأ تحقق) */
function old(string $key, $default = '')
{
    return $_SESSION['_old'][$key] ?? $default;
}

function flash(string $type, string $message): void
{
    App::flash($type, $message);
}

/** حقل CSRF مخفي */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(App::csrfToken()) . '">';
}

/** التحقق من CSRF */
function csrf_check(): void
{
    App::validateCsrf();
}

/** أخطاء النماذج (تُقرأ وتُمسح فورًا حتى لا تبقى في صفحات أخرى) */
function form_errors(): array
{
    return App::takeSession('_form_errors', []);
}

/** حجم الملف بصيغة مقروءة */
function human_filesize(int $bytes): string
{
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' م.ب';
    }
    if ($bytes >= 1024) {
        return number_format($bytes / 1024, 1) . ' ك.ب';
    }
    return $bytes . ' بايت';
}

/** تنسيق التاريخ بالعربية */
function format_date(?string $datetime): string
{
    if (!$datetime) {
        return '—';
    }
    try {
        $dt = new DateTime($datetime, new DateTimeZone(App::config('timezone', 'UTC')));
    } catch (Exception $e) {
        return $datetime;
    }
    return $dt->format('Y-m-d h:i A');
}

/** تاريخ قصير */
function format_date_short(?string $datetime): string
{
    if (!$datetime) {
        return '—';
    }
    try {
        $dt = new DateTime($datetime, new DateTimeZone(App::config('timezone', 'UTC')));
    } catch (Exception $e) {
        return $datetime;
    }
    return $dt->format('Y-m-d');
}

/** شارة حالة المستند بالعربية */
function document_status_badge(string $status): string
{
    switch ($status) {
        case 'active':
            return '<span class="badge badge-status badge-active">مفعل</span>';
        case 'disabled':
            return '<span class="badge badge-status badge-disabled">معطل</span>';
        default:
            return '<span class="badge badge-status badge-inactive">غير مفعل</span>';
    }
}

/** نص حالة المستند */
function document_status_text(string $status): string
{
    switch ($status) {
        case 'active':
            return 'مفعل';
        case 'disabled':
            return 'معطل';
        default:
            return 'غير مفعل';
    }
}

/** امتداد الملف من الاسم */
function file_extension(string $filename): string
{
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/** شعار النظام كـ Data URI (لأن مجلد التخزين محمي) */
function logo_data_uri(): string
{
    $path = (string)App::setting('logo_path', '');
    if ($path === '') {
        return '';
    }
    $full = BASE_PATH . '/storage/logo/' . basename($path);
    if (!is_file($full) || !is_readable($full)) {
        return '';
    }
    $mimes = [
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
    ];
    $ext = file_extension($path);
    $mime = $mimes[$ext] ?? 'image/png';
    $data = @file_get_contents($full);
    if ($data === false || $data === '') {
        return '';
    }
    return 'data:' . $mime . ';base64,' . base64_encode($data);
}
