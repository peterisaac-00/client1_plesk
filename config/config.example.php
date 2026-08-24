<?php
/**
 * إعدادات النظام
 * انسخ هذا الملف إلى config.php وعدّل القيم بما يناسب بيئتك.
 */

return [

    // ---------- قاعدة البيانات (MySQL) ----------
    'db_host' => '127.0.0.1',
    'db_port' => 3306,
    'db_name' => 'document_verify',
    'db_user' => 'docadmin',
    'db_pass' => 'change_me',

    // ---------- النظام ----------
    // النطاق الرئيسي للموقع ولوحة الإدارة.
    'app_url' => 'https://ourum-explor.com',

    // النطاق العام الذي تُشفّر روابطه داخل رموز QR.
    'qr_url' => 'https://portal.ourum-explor.com',

    // جذر التخزين. في نسخة portal يمكن استخدام ../ourum-explor.com/storage
    // حتى يقرأ الموقعان نفس الملفات بدل إنشاء مخزنين منفصلين.
    'storage_path' => 'storage',

    // المنطقة الزمنية
    'timezone' => 'Asia/Riyadh',

    // ---------- الأمان ----------
    // مفتاح يستخدم لتشفير البيانات الحساسة - غيّره لقيمة عشوائية طويلة
    'app_key' => 'CHANGE_THIS_TO_A_RANDOM_LONG_STRING_64_CHARS',

    // مدة انتهاء الجلسة بالدقائق
    'session_lifetime' => 30,

    // ---------- الملفات ----------
    // الحد الأقصى لحجم الملف المرفوع بالبايت (20 ميجابايت)
    'max_upload_size' => 20971520,

    // الملفات المسموح رفعها
    'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
    'allowed_mimes' => ['application/pdf', 'image/jpeg', 'image/png'],

    // هل يُسجّل عنوان IP في سجل التحقق (راجع القوانين المحلية قبل تفعيله)
    'ip_logging' => true,

    // ---------- الصفحات ----------
    // عدد الصفوف في الجداول
    'pagination_per_page' => 15,
];
