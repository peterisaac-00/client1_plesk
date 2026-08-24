<?php

namespace App\Controllers;

use App\Core\App;
use App\Core\Controller;
use App\Models\Document;
use App\Models\VerificationLog;
use App\Services\FileService;

/**
 * صفحة التحقق العامة - لا تتطلب تسجيل دخول
 * /verify/{token}
 */
class VerifyController extends Controller
{
    protected string $layout = 'public';
    protected bool $requireAuth = false;

    public function show(string $token): void
    {
        if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
            http_response_code(404);
            $this->view('verify/result', [
                'pageTitle' => 'المستند غير موجود',
                'document' => null,
                'error' => 'not_found',
            ]);
            return;
        }

        $document = Document::findByToken($token);

        if ($document === null) {
            http_response_code(404);
            $this->view('verify/result', [
                'pageTitle' => 'المستند غير موجود',
                'document' => null,
                'error' => 'not_found',
            ]);
            return;
        }

        // تسجيل عملية التحقق
        $ip = null;
        if ((string)config('ip_logging') === '1' && !empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $userAgent = mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
        VerificationLog::log((int)$document['id'], $document['status'], $ip, $userAgent !== '' ? $userAgent : null);

        if ($document['status'] !== Document::STATUS_ACTIVE) {
            $this->view('verify/result', [
                'pageTitle' => 'المستند غير متاح للتحقق',
                'document' => $document,
                'error' => 'unavailable',
            ]);
            return;
        }

        $this->view('verify/result', [
            'pageTitle' => 'التحقق من المستند',
            'document' => $document,
            'error' => null,
            'isImage' => str_starts_with($document['mime_type'], 'image/'),
        ]);
    }

    /** بث المستند لعرضه داخل صفحة التحقق */
    public function file(string $token): void
    {
        if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
            http_response_code(404);
            exit;
        }

        $document = Document::findByToken($token);
        if ($document === null || $document['status'] !== Document::STATUS_ACTIVE) {
            http_response_code(404);
            exit('المستند غير متاح.');
        }

        $path = FileService::path($document['stored_name']);
        FileService::stream($path, $document['original_name'], $document['mime_type'], true);
    }

    /** تحميل المستند من صفحة التحقق */
    public function download(string $token): void
    {
        if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
            http_response_code(404);
            exit;
        }

        $document = Document::findByToken($token);
        if ($document === null || $document['status'] !== Document::STATUS_ACTIVE) {
            http_response_code(404);
            exit('المستند غير متاح.');
        }

        $path = FileService::path($document['stored_name']);
        FileService::stream($path, $document['original_name'], $document['mime_type'], false);
    }
}
