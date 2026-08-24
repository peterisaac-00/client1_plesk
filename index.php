<?php
/**
 * ملف الدخول الرئيسي للنظام
 * جميع الطلبات تمر عبر هذا الملف (Front Controller)
 */

declare(strict_types=1);

use App\Core\App;
use App\Core\Router;

define('BASE_PATH', __DIR__);

// ---------------------------------------------------------------
// خدمة الملفات الثابتة (Assets) مباشرة قبل أي توجيه
// - يعمل مع PHP Built-in Server (Router Script يخدم الملف بإرجاع false)
// - آمن: يسمح فقط بامتدادات ثابتة معروفة داخل نطاق المشروع
// - في Apache/Plesk نفس السلوك افتراضيًا (الملفات الموجودة تُخدم قبل index.php)
// ---------------------------------------------------------------
$requestPath = urldecode((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$baseRoot = realpath(BASE_PATH);
$staticFile = realpath(BASE_PATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $requestPath));
// منع Path Traversal: يجب أن يبقى الملف داخل نطاق المشروع، وغير مخفي، وامتداده من القائمة الآمنة
$insideBase = $staticFile !== false
    && $baseRoot !== false
    && strncmp($staticFile, $baseRoot . DIRECTORY_SEPARATOR, strlen($baseRoot . DIRECTORY_SEPARATOR)) === 0;
if ($insideBase && is_file($staticFile) && strpos(basename($staticFile), '.') !== 0) {
    $extension = strtolower(pathinfo($staticFile, PATHINFO_EXTENSION));
    $staticExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'ico', 'woff', 'woff2', 'ttf', 'eot', 'map', 'txt', 'webmanifest'];
    if (in_array($extension, $staticExtensions, true)) {
        return false;
    }
}
unset($requestPath, $baseRoot, $staticFile, $insideBase, $extension, $staticExtensions);

// إعدادات الأمان والإبلاغ عن الأخطاء
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/storage/logs/php-error.log');
date_default_timezone_set('UTC');

// تحميل النواة
require BASE_PATH . '/app/Core/helpers.php';
require BASE_PATH . '/app/Core/App.php';
require BASE_PATH . '/app/Core/Database.php';
require BASE_PATH . '/app/Core/Paginator.php';
require BASE_PATH . '/app/Core/Controller.php';
require BASE_PATH . '/app/Core/Router.php';

// تسجيل محمل الفئات (Autoloader)
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

try {
    App::init();
    error_log('[REQ] sid=' . session_id() . ' user=' . (isset($_SESSION['user']) ? 'yes' : 'no') . ' ' . ($_SERVER['REQUEST_METHOD'] ?? '?') . ' ' . ($_SERVER['REQUEST_URI'] ?? '?') . ' cookie=' . (isset($_COOKIE[session_name()]) ? 'yes' : 'no'));

    // تعريف المسارات
    require BASE_PATH . '/app/routes.php';

    // تشغيل النظام
    Router::dispatch();

    App::shutdown();
} catch (Throwable $e) {
    App::handleFatal($e);
}