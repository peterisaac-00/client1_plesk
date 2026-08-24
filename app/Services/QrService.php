<?php

namespace App\Services;

use App\Core\App;

/**
 * خدمة إنشاء رموز QR
 * تستخدم مكتبة phpqrcode (PHP QR Code)
 */
class QrService
{
    private const ERROR_CORRECTION_LEVEL = 'H'; // أعلى مستوى تصحيح أخطاء للطباعة
    private const MODULE_SCALE = 24;            // حجم كل وحدة بالبكسل (جودة عالية للطباعة)
    private const MARGIN = 4;                   // هامش أبيض حول الرمز

    /**
     * إنشاء رمز QR وحفظه كصورة PNG
     */
    public static function generatePng(string $data, string $outputPath): void
    {
        if (!extension_loaded('gd')) {
            App::renderSetupError(
                'إضافة GD غير مفعلة',
                'يحتاج النظام إلى إضافة <code>gd</code> في PHP لإنشاء رموز QR. فعّلها من إعدادات PHP في Plesk.'
            );
        }

        $qrlib = BASE_PATH . '/vendor/phpqrcode/qrlib.php';
        if (!is_file($qrlib)) {
            App::renderSetupError(
                'مكتبة QR غير موجودة',
                'تأكد من رفع مجلد <code>vendor/phpqrcode</code> مع ملفات المشروع.'
            );
        }

        require_once $qrlib;

        // تجاهل تحذيرات المكتبة القديمة أثناء الإنشاء
        set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
            error_log('[QR LIB] ' . $message);
            return true;
        }, E_WARNING | E_NOTICE | E_DEPRECATED);

        try {
            \QRcode::png($data, $outputPath, self::ERROR_CORRECTION_LEVEL, self::MODULE_SCALE, self::MARGIN);
        } finally {
            restore_error_handler();
        }

        if (!is_file($outputPath) || filesize($outputPath) === 0) {
            App::renderSetupError(
                'فشل إنشاء رمز QR',
                'تعذر إنشاء صورة الرمز. تأكد من صلاحيات الكتابة لمجلد <code>storage/qrcodes</code>.'
            );
        }
    }
}
