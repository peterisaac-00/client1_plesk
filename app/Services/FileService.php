<?php

namespace App\Services;

use App\Core\App;

/**
 * خدمة إدارة الملفات: رفع آمن، بث، حذف
 */
class FileService
{
    public const STORAGE_DOCUMENTS = 'documents';
    public const STORAGE_QR = 'qrcodes';
    public const STORAGE_LOGO = 'logo';

    /** المسار الكامل لمجلد تخزين معين */
    public static function storagePath(string $folder): string
    {
        // يسمح للدومين الرئيسي والـ portal باستخدام نفس مخزن الملفات.
        // المسار يُضبط من config.php، ويظل التخزين المحلي هو fallback للتطوير.
        $configuredRoot = trim((string)App::config('storage_path', ''));
        if ($configuredRoot === '') {
            $configuredRoot = BASE_PATH . '/storage';
        } elseif ($configuredRoot[0] !== '/') {
            $configuredRoot = BASE_PATH . '/' . ltrim($configuredRoot, '/');
        }

        $path = rtrim($configuredRoot, '/') . '/' . trim($folder, '/');
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        return $path;
    }

    /** اسم عشوائي آمن لملف مخزن */
    public static function randomStoredName(string $extension): string
    {
        return bin2hex(random_bytes(16)) . '.' . strtolower($extension);
    }

    /** تطهير اسم الملف الأصلي للعرض الآمن */
    public static function sanitizeOriginalName(string $filename): string
    {
        // إزالة أي مسارات لتفادي Path Traversal
        $name = basename(str_replace('\\', '/', $filename));
        // إزالة الأحرف الضارة
        $name = preg_replace('/[^\w\.\-\s\(\)ا-يأ-ي]/u', '', $name);
        // إزالة النقاط الزائدة في البداية
        $name = ltrim($name, '.');
        $name = preg_replace('/\.{2,}/', '.', $name);
        $name = trim($name);
        if ($name === '' || $name === '.') {
            $name = 'document';
        }
        return mb_substr($name, 0, 200);
    }

    /**
     * التحقق من صحة ملف مرفوع
     * @return array|null معلومات الملف الصالحة أو null
     */
    public static function validateUpload(array $file): ?array
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            return null;
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                App::flash('error', 'حجم الملف أكبر من الحد المسموح (' . human_filesize((int)config('max_upload_size', 20971520)) . ').');
                return null;
            case UPLOAD_ERR_NO_FILE:
                App::flash('error', 'يرجى اختيار ملف للرفع.');
                return null;
            default:
                App::flash('error', 'حدث خطأ أثناء رفع الملف. يرجى المحاولة مرة أخرى.');
                return null;
        }

        // حجم الملف
        $maxSize = (int)config('max_upload_size', 20971520);
        if ($file['size'] <= 0 || $file['size'] > $maxSize) {
            App::flash('error', 'حجم الملف غير مسموح (الحد الأقصى: ' . human_filesize($maxSize) . ').');
            return null;
        }

        // الامتداد
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExt = config('allowed_extensions', ['pdf', 'jpg', 'jpeg', 'png']);
        if (!in_array($extension, $allowedExt, true)) {
            App::flash('error', 'نوع الملف غير مسموح. الأنواع المقبولة: PDF, JPG, JPEG, PNG.');
            return null;
        }

        // التحقق من المحتوى الفعلي (MIME) لمنع الملفات الخطرة
        if (!is_uploaded_file($file['tmp_name'])) {
            App::flash('error', 'ملف غير صالح.');
            return null;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowedMimes = config('allowed_mimes', ['application/pdf', 'image/jpeg', 'image/png']);
        if (!in_array($mime, $allowedMimes, true)) {
            App::flash('error', 'محتوى الملف غير مسموح. تأكد من أن الملف PDF أو صورة حقيقية.');
            return null;
        }

        // مطابقة الامتداد مع المحتوى
        $expectedMimes = [
            'pdf' => ['application/pdf'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
        ];
        if (!in_array($mime, $expectedMimes[$extension] ?? [], true)) {
            App::flash('error', 'الامتداد لا يطابق محتوى الملف الفعلي.');
            return null;
        }

        return [
            'original_name' => self::sanitizeOriginalName($file['name']),
            'extension' => $extension,
            'mime' => $mime,
            'size' => (int)$file['size'],
            'tmp' => $file['tmp_name'],
        ];
    }

    /** حفظ الملف بآمان في التخزين */
    public static function store(array $validated, string $folder = self::STORAGE_DOCUMENTS): string
    {
        $storedName = self::randomStoredName($validated['extension']);
        $destination = self::storagePath($folder) . '/' . $storedName;
        if (!move_uploaded_file($validated['tmp'], $destination)) {
            App::flash('error', 'تعذر حفظ الملف على الخادم.');
            return '';
        }
        @chmod($destination, 0644);
        return $storedName;
    }

    /** مسار ملف مخزن */
    public static function path(string $storedName, string $folder = self::STORAGE_DOCUMENTS): string
    {
        return self::storagePath($folder) . '/' . basename($storedName);
    }

    /**
     * بث ملف إلى المتصفح بأمان
     */
    public static function stream(string $absolutePath, string $displayName, string $mime, bool $inline = true): void
    {
        if (!is_file($absolutePath) || !is_readable($absolutePath)) {
            http_response_code(404);
            exit('الملف غير موجود.');
        }

        // منع التنفيذ: ترويسات حماية إضافية
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($absolutePath));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store, max-age=0');
        header('Pragma: no-cache');

        $filename = self::sanitizeOriginalName($displayName);
        $disposition = $inline ? 'inline' : 'attachment';
        header(
            "Content-Disposition: $disposition; filename*=UTF-8''" . rawurlencode($filename)
            . '; filename="' . addslashes($filename) . '"'
        );

        // إغلاق الجلسة قبل بث الملف الكبير
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $handle = fopen($absolutePath, 'rb');
        if ($handle === false) {
            http_response_code(500);
            exit('تعذر قراءة الملف.');
        }
        while (!feof($handle)) {
            $chunk = fread($handle, 8192);
            if ($chunk === false) {
                break;
            }
            echo $chunk;
            flush();
        }
        fclose($handle);
        exit;
    }

    /** حذف ملف من التخزين */
    public static function delete(string $absolutePath): void
    {
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    /** حذف ملف برقمه المخزن */
    public static function deleteStored(string $storedName, string $folder = self::STORAGE_DOCUMENTS): void
    {
        if ($storedName !== '') {
            self::delete(self::path($storedName, $folder));
        }
    }
}
