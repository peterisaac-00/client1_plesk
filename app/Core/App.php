<?php

namespace App\Core;

/**
 * النواة الرئيسية للنظام
 * مسؤولة عن: التهيئة، الجلسات، الإعدادات، CSRF، رسائل التنبيه
 */
class App
{
    private static array $config = [];
    private static array $settings = [];
    private static ?string $baseUrl = null;
    private static bool $booted = false;

    public static function init(): void
    {
        if (self::$booted) {
            return;
        }
        self::$booted = true;

        // تحميل الإعدادات
        $configFile = BASE_PATH . '/config/config.php';
        if (!is_file($configFile)) {
            self::renderSetupError(
                'ملف الإعدادات غير موجود',
                'يرجى نسخ ملف <code>config/config.example.php</code> إلى <code>config/config.php</code> وتعبئة بيانات قاعدة البيانات.'
            );
        }
        self::$config = require $configFile;

        // المنطقة الزمنية
        date_default_timezone_set(self::$config['timezone'] ?? 'UTC');

        // الجلسة
        self::startSession();

        // تحميل الإعدادات من قاعدة البيانات (إن توفرت)
        self::loadSettings();
    }

    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // منع استخدام معرفات الجلسات من عنوان URL أو من مدخلات غير موثوقة
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['SERVER_PORT'] ?? 80) == 443;

        session_name('docverify_sid');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();

        // حماية انتهاء الجلسة (Idle Timeout)
        $lifetime = (int)(self::$config['session_lifetime'] ?? 30) * 60;
        $lastActivity = $_SESSION['_last_activity'] ?? 0;
        if ($lastActivity && (time() - $lastActivity) > $lifetime) {
            $_SESSION = [];
            session_regenerate_id(true);
        }
        $_SESSION['_last_activity'] = time();
    }

    public static function config(string $key, $default = null)
    {
        return self::$config[$key] ?? $default;
    }

    /** تحميل الإعدادات من قاعدة البيانات إلى ذاكرة التخزين */
    public static function loadSettings(): void
    {
        try {
            $rows = Database::query('SELECT setting_key, setting_value FROM settings')->fetchAll();
            foreach ($rows as $row) {
                self::$settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (\Throwable $e) {
            self::$settings = [];
        }
    }

    public static function setting(string $key, $default = ''): string
    {
        return self::$settings[$key] ?? (string)$default;
    }

    public static function setSettings(array $settings): void
    {
        self::$settings = $settings;
    }

    public static function settings(): array
    {
        return self::$settings;
    }

    /** رابط النظام الأساسي (من الإعدادات ثم config ثم اكتشاف تلقائي) */
    public static function baseUrl(): string
    {
        if (self::$baseUrl !== null) {
            return self::$baseUrl;
        }

        // 1) الإعداد المخزن في قاعدة البيانات (قابل للتعديل من لوحة التحكم)
        $configured = trim(self::setting('base_url'));
        if ($configured !== '') {
            self::$baseUrl = rtrim($configured, '/');
            return self::$baseUrl;
        }

        // 2) الرابط الثابت في ملف الإعدادات
        $configured = trim((string)self::config('app_url', ''));
        if ($configured !== '') {
            self::$baseUrl = rtrim($configured, '/');
            return self::$baseUrl;
        }

        // 3) اكتشاف تلقائي من طلب الزائر (يستخدم في التطوير المحلي)
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['SERVER_PORT'] ?? 80) == 443
            ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        // في حالة تشغيل PHP Dev Server في مجلد فرعي
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        $scriptDir = rtrim($scriptDir, '/');

        // تجاهل ترويسة Host غير الصالحة (منع Header Poisoning في روابط QR)
        $host = preg_replace('/[^a-zA-Z0-9.\-:\[\]]/', '', $host);

        self::$baseUrl = $scheme . '://' . $host . $scriptDir;
        return self::$baseUrl;
    }

    // ---------- CSRF ----------

    public static function csrfToken(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function validateCsrf(): void
    {
        $sent = $_POST['_csrf'] ?? '';
        if (!is_string($sent) || $sent === '' || !hash_equals(self::csrfToken(), $sent)) {
            http_response_code(419);
            exit('انتهت صلاحية الجلسة أو أن الطلب غير صالح. يرجى إعادة المحاولة.');
        }
    }

    /** قراءة قيمة من الجلسة ومسحها فورًا حتى لا تتسرب رسائل قديمة إلى صفحات أخرى */
    public static function takeSession(string $key, $default = null)
    {
        $value = $_SESSION[$key] ?? $default;
        unset($_SESSION[$key]);
        return $value;
    }

    // ---------- الرسائل ----------

    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flashes'][] = ['type' => $type, 'message' => $message];
    }

    /** جلب الرسائل ومسحها */
    public static function flashes(): array
    {
        $flashes = $_SESSION['_flashes'] ?? [];
        unset($_SESSION['_flashes']);
        return $flashes;
    }

    public static function setOld(array $data): void
    {
        $_SESSION['_old'] = $data;
    }

    public static function clearOld(): void
    {
        unset($_SESSION['_old']);
    }

    // ---------- المستخدم ----------

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function setUser(array $user): void
    {
        $_SESSION['user'] = $user;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
    }

    // ---------- إنهاء ----------

    public static function shutdown(): void
    {
        session_write_close();
    }

    public static function handleFatal(\Throwable $e): void
    {
        error_log('[FATAL] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
        echo '<div dir="rtl" style="font-family: Tahoma; padding: 40px; text-align: center;">
                <h2 style="color:#c0392b;">حدث خطأ غير متوقع</h2>
                <p>نعتذر، حدثت مشكلة في النظام. تم تسجيل الخطأ، يرجى المحاولة لاحقًا أو التواصل مع المسؤول.</p>
              </div>';
        exit;
    }

    public static function renderSetupError(string $title, string $message): void
    {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
        echo '<div dir="rtl" style="font-family: Tahoma; max-width: 640px; margin: 60px auto; padding: 30px; border: 1px solid #e0e0e0; border-radius: 12px;">
                <h2 style="color:#c0392b; margin-top:0;">' . e($title) . '</h2>
                <p style="line-height:1.9; font-size:15px; color:#333;">' . $message . '</p>
              </div>';
        exit;
    }
}
