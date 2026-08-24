<?php

namespace App\Controllers;

use App\Core\App;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Paginator;
use App\Models\Client;
use App\Models\Document;
use App\Services\FileService;
use App\Services\QrService;

class DocumentsController extends Controller
{
    protected string $layout = 'admin';
    protected bool $requireAuth = true;

    /** قائمة المستندات مع البحث والفلاتر */
    public function index(): void
    {
        $filters = [
            'q' => trim((string)($_GET['q'] ?? '')),
            'client_id' => (int)($_GET['client_id'] ?? 0),
            'status' => (string)($_GET['status'] ?? ''),
            'type' => trim((string)($_GET['type'] ?? '')),
            'date_from' => trim((string)($_GET['date_from'] ?? '')),
            'date_to' => trim((string)($_GET['date_to'] ?? '')),
        ];

        $perPage = (int)config('pagination_per_page', 15);
        $page = max(1, (int)($_GET['page'] ?? 1));

        $result = Document::filter($filters, $perPage, $page);
        $paginator = new Paginator($result['items'], $result['total'], $page, $perPage);

        $this->view('admin/documents/index', [
            'pageTitle' => 'إدارة المستندات',
            'activeMenu' => 'documents',
            'user' => App::user(),
            'documents' => $paginator->items(),
            'paginator' => $paginator,
            'filters' => $filters,
            'clients' => Client::all(),
        ]);
    }

    /** صفحة رفع مستند جديد */
    public function showUpload(): void
    {
        $this->view('admin/documents/upload', [
            'pageTitle' => 'رفع مستند جديد',
            'activeMenu' => 'documents',
            'user' => App::user(),
            'clients' => Client::all(),
            'errors' => form_errors(),
        ]);
    }

    /** معالجة رفع مستند */
    public function store(): void
    {
        $clientId = (int)($_POST['client_id'] ?? 0);

        // التحقق من أن العميل موجود فعلًا
        if ($clientId > 0 && Client::find($clientId) === null) {
            App::flash('error', 'العميل المحدد غير موجود.');
            redirect('/admin/documents/upload');
        }

        $file = $_FILES['document'] ?? null;
        if ($file === null || !isset($file['name']) || $file['name'] === '') {
            App::flash('error', 'يرجى اختيار ملف للرفع.');
            redirect('/admin/documents/upload');
        }

        $validated = FileService::validateUpload($file);
        if ($validated === null) {
            redirect('/admin/documents/upload');
        }

        // حفظ الملف باسم عشوائي آمن
        $storedName = FileService::store($validated);
        if ($storedName === '') {
            redirect('/admin/documents/upload');
        }

        // إنشاء سجل المستند
        $token = Document::generateToken();
        // الرمز يشير مباشرة لملف المستند حتى يفتح فورًا عند المسح
        $verifyUrl = url('/verify/' . $token . '/file');

        $docId = Document::create([
            'doc_number' => '',
            'client_id' => $clientId > 0 ? $clientId : null,
            'original_name' => $validated['original_name'],
            'stored_name' => $storedName,
            'mime_type' => $validated['mime'],
            'file_size' => $validated['size'],
            'token' => $token,
            'verify_url' => $verifyUrl,
        ]);

        // إنشاء رقم المستند الداخلي
        $docNumber = Document::generateDocNumber($docId);
        \App\Core\Database::update(
            'UPDATE documents SET doc_number = ?, updated_at = NOW() WHERE id = ?',
            [$docNumber, $docId]
        );

        // إنشاء رمز QR تلقائيًا
        $qrPath = 'qrcodes/' . $token . '.png';
        $qrFullPath = FileService::storagePath(FileService::STORAGE_QR) . '/' . $token . '.png';
        try {
            QrService::generatePng($verifyUrl, $qrFullPath);
            Document::setQrPath($docId, $qrPath);
            $qrReady = true;
        } catch (\Throwable $e) {
            $qrReady = false;
        }

        App::clearOld();
        App::flash('success', 'تم رفع المستند بنجاح' . ($qrReady ? ' وتم إنشاء رمز QR الخاص به.' : '.'));
        redirect('/admin/documents');
    }

    /** توليد رمز QR لمستند */
    public function generateQr(int $id): void
    {
        $document = Document::find($id);
        if ($document === null) {
            App::flash('error', 'المستند غير موجود.');
            redirect('/admin/documents');
        }

        // إعادة بناء الرابط دائمًا ليشير للملف مباشرة (يحدّث الرموز القديمة أيضًا)
        $verifyUrl = url('/verify/' . $document['token'] . '/file');

        $qrPath = 'qrcodes/' . $document['token'] . '.png';
        $qrFullPath = FileService::storagePath(FileService::STORAGE_QR) . '/' . $document['token'] . '.png';
        QrService::generatePng($verifyUrl, $qrFullPath);
        Document::setQrPath($id, $qrPath);
        \App\Core\Database::update(
            'UPDATE documents SET verify_url = ?, updated_at = NOW() WHERE id = ?',
            [$verifyUrl, $id]
        );

        App::flash('success', 'تم إنشاء رمز QR للمستند بنجاح.');
        redirect('/admin/documents/' . $id . '/qr');
    }

    /** صفحة عرض رمز QR */
    public function showQr(int $id): void
    {
        $document = Document::find($id);
        if ($document === null) {
            App::flash('error', 'المستند غير موجود.');
            redirect('/admin/documents');
        }

        $this->view('admin/documents/qr', [
            'pageTitle' => 'رمز QR للمستند',
            'activeMenu' => 'documents',
            'user' => App::user(),
            'document' => $document,
        ]);
    }

    /** بث صورة رمز QR */
    public function qrImage(int $id): void
    {
        $document = Document::find($id);
        if ($document === null || $document['qr_path'] === '') {
            http_response_code(404);
            exit;
        }

        $path = FileService::path($document['qr_path'], 'qrcodes');
        FileService::stream($path, 'qr-' . $document['token'] . '.png', 'image/png', true);
    }

    /** تحميل صورة رمز QR */
    public function downloadQr(int $id): void
    {
        $document = Document::find($id);
        if ($document === null || $document['qr_path'] === '') {
            App::flash('error', 'رمز QR غير موجود لهذا المستند.');
            redirect('/admin/documents');
        }

        $path = FileService::path($document['qr_path'], 'qrcodes');
        FileService::stream($path, 'qr-' . $document['doc_number'] . '.png', 'image/png', false);
    }

    /** تفعيل QR */
    public function activate(int $id): void
    {
        $document = Document::find($id);
        if ($document === null) {
            App::flash('error', 'المستند غير موجود.');
            redirect('/admin/documents');
        }

        Document::updateStatus($id, Document::STATUS_ACTIVE);
        App::flash('success', 'تم تفعيل رمز QR بنجاح. أصبح الرابط صالحًا للتحقق الآن.');
        redirect('/admin/documents');
    }

    /** تعطيل QR */
    public function disable(int $id): void
    {
        $document = Document::find($id);
        if ($document === null) {
            App::flash('error', 'المستند غير موجود.');
            redirect('/admin/documents');
        }

        Document::updateStatus($id, Document::STATUS_DISABLED);
        App::flash('success', 'تم تعطيل رمز QR. لن يعمل رابط التحقق حتى يتم التفعيل مجددًا.');
        redirect('/admin/documents');
    }

    /** استبدال ملف مستند مع الاحتفاظ بنفس الرمز و QR والحالة */
    public function replaceFile(int $id): void
    {
        $document = Document::find($id);
        if ($document === null) {
            App::flash('error', 'المستند غير موجود.');
            redirect('/admin/documents');
        }

        $file = $_FILES['replacement'] ?? null;
        if ($file === null || !isset($file['name']) || $file['name'] === '') {
            App::flash('error', 'يرجى اختيار الملف الجديد للاستبدال.');
            redirect('/admin/documents');
        }

        $validated = FileService::validateUpload($file);
        if ($validated === null) {
            redirect('/admin/documents');
        }

        // منع الاستبدال بنفس المحتوى: غالبًا تم اختيار النسخة غير المعدلة
        $newHash = hash_file('sha256', $validated['tmp']);
        $oldHash = (is_file(FileService::path($document['stored_name'])))
            ? hash_file('sha256', FileService::path($document['stored_name']))
            : '';
        if ($newHash !== '' && $newHash === $oldHash) {
            App::flash(
                'error',
                'الملف الذي اخترته مطابق تمامًا للملف الحالي (نفس المحتوى بالبايت). '
                . 'تأكد أنك اخترت النسخة التي تحتوي على رمز QR المضمّن، وأن المحرر حفظ التغييرات فعلًا.'
            );
            redirect('/admin/documents');
        }

        // حفظ الملف الجديد ثم حذف القديم (نفس الرمز ونفس QR)
        $storedName = FileService::store($validated);
        if ($storedName === '') {
            redirect('/admin/documents');
        }
        FileService::deleteStored($document['stored_name']);

        Database::update(
            'UPDATE documents SET original_name = ?, stored_name = ?, mime_type = ?, file_size = ?, updated_at = NOW() WHERE id = ?',
            [$validated['original_name'], $storedName, $validated['mime'], $validated['size'], $id]
        );

        App::flash(
            'success',
            'تم استبدال الملف بنجاح — الملف الحالي الآن: «' . $validated['original_name'] . '» (' . number_format($validated['size'] / 1024, 0) . ' ك.ب). '
            . 'رقم المستند ورابط التحقق ورمز QR بقيت كما هي.'
        );
        redirect('/admin/documents');
    }

    /** عرض المستند داخل الصفحة (محمي) */
    public function viewFile(int $id): void
    {
        $document = Document::find($id);
        if ($document === null) {
            http_response_code(404);
            exit;
        }
        $path = FileService::path($document['stored_name']);
        FileService::stream($path, $document['original_name'], $document['mime_type'], true);
    }

    /** عرض المستند في عارض احترافي (صفحة مستقلة) */
    public function showViewer(int $id): void
    {
        $document = Document::find($id);
        if ($document === null) {
            http_response_code(404);
            exit;
        }
        $client = $document['client_id'] ? Client::find((int)$document['client_id']) : null;
        $this->layout = 'viewer';
        $this->view('admin/documents/viewer', [
            'pageTitle' => 'عرض المستند',
            'user' => App::user(),
            'document' => $document,
            'clientName' => $client['name'] ?? null,
        ]);
    }

    /** تحميل المستند (محمي) */
    public function downloadFile(int $id): void
    {
        $document = Document::find($id);
        if ($document === null) {
            App::flash('error', 'المستند غير موجود.');
            redirect('/admin/documents');
        }
        $path = FileService::path($document['stored_name']);
        FileService::stream($path, $document['original_name'], $document['mime_type'], false);
    }

    /** حذف المستند */
    public function destroy(int $id): void
    {
        $document = Document::find($id);
        if ($document === null) {
            App::flash('error', 'المستند غير موجود.');
            redirect('/admin/documents');
        }

        // حذف الملفات من القرص
        FileService::deleteStored($document['stored_name']);
        if ($document['qr_path'] !== '') {
            FileService::deleteStored(basename($document['qr_path']), 'qrcodes');
        }

        Document::deleteById($id);
        App::flash('success', 'تم حذف المستند وجميع بياناته بنجاح.');
        redirect('/admin/documents');
    }
}
