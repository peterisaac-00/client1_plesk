<?php
/**
 * إعدادات التشغيل المحلي - انسخ config.example.php وعدّل القيم للإنتاج
 */

return [

    'db_host' => '127.0.0.1',
    'db_port' => 3306,
    'db_name' => 'document_verify',
    'db_user' => 'root',
    'db_pass' => 'root123',

    // النطاق الرئيسي للموقع ولوحة الإدارة.
    'app_url' => 'https://ourum-explor.com',

    // النطاق العام الذي تُشفّر روابطه داخل رموز QR.
    'qr_url' => 'https://portal.ourum-explor.com',

    'timezone' => 'Asia/Riyadh',

    'app_key' => 'dev-key-9f8a7b6c5d4e3f2a1b0c9d8e7f6a5b4c3d2e1f0a9b8c7d6e5f4a3b2c1d0e9f8a7b6c5d',

    'session_lifetime' => 30,

    'max_upload_size' => 20971520,

    'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
    'allowed_mimes' => ['application/pdf', 'image/jpeg', 'image/png'],

    'ip_logging' => true,

    'pagination_per_page' => 15,
];
