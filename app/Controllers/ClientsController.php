<?php

namespace App\Controllers;

use App\Core\App;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Client;

class ClientsController extends Controller
{
    protected string $layout = 'admin';
    protected bool $requireAuth = true;

    /** قائمة العملاء */
    public function index(): void
    {
        $q = trim((string)($_GET['q'] ?? ''));

        $clients = $q !== ''
            ? Client::search($q)
            : Client::allWithCounts();

        $this->view('admin/clients/index', [
            'pageTitle' => 'إدارة العملاء',
            'activeMenu' => 'clients',
            'user' => App::user(),
            'clients' => $clients,
            'q' => $q,
        ]);
    }

    /** صفحة إضافة عميل */
    public function create(): void
    {
        $this->view('admin/clients/form', [
            'pageTitle' => 'إضافة عميل جديد',
            'activeMenu' => 'clients',
            'user' => App::user(),
            'client' => null,
            'errors' => form_errors(),
        ]);
    }

    /** حفظ عميل جديد */
    public function store(): void
    {
        $errors = $this->validate($_POST);
        if ($errors) {
            $_SESSION['_form_errors'] = $errors;
            App::setOld($_POST);
            redirect('/admin/clients/create');
        }

        $id = Client::create($_POST);
        App::flash('success', 'تمت إضافة العميل بنجاح.');
        redirect('/admin/clients');
    }

    /** صفحة تعديل عميل */
    public function edit(int $id): void
    {
        $client = Client::find($id);
        if ($client === null) {
            App::flash('error', 'العميل غير موجود.');
            redirect('/admin/clients');
        }

        $this->view('admin/clients/form', [
            'pageTitle' => 'تعديل بيانات العميل',
            'activeMenu' => 'clients',
            'user' => App::user(),
            'client' => $client,
            'errors' => form_errors(),
        ]);
    }

    /** حفظ تعديل عميل */
    public function update(int $id): void
    {
        $client = Client::find($id);
        if ($client === null) {
            App::flash('error', 'العميل غير موجود.');
            redirect('/admin/clients');
        }

        $errors = $this->validate($_POST);
        if ($errors) {
            $_SESSION['_form_errors'] = $errors;
            App::setOld($_POST);
            redirect('/admin/clients/' . $id . '/edit');
        }

        Client::updateById($id, $_POST);
        App::flash('success', 'تم تحديث بيانات العميل بنجاح.');
        redirect('/admin/clients');
    }

    /** حذف عميل (فقط إذا لم تكن له مستندات) */
    public function destroy(int $id): void
    {
        $client = Client::find($id);
        if ($client === null) {
            App::flash('error', 'العميل غير موجود.');
            redirect('/admin/clients');
        }

        if (Client::documentsCount($id) > 0) {
            App::flash('error', 'لا يمكن حذف العميل لأنه مرتبط بمستندات. احذف مستنداته أولًا أو انقلها لعميل آخر.');
            redirect('/admin/clients');
        }

        Client::deleteById($id);
        App::flash('success', 'تم حذف العميل بنجاح.');
        redirect('/admin/clients');
    }

    /** عرض مستندات عميل معين */
    public function documents(int $id): void
    {
        $client = Client::find($id);
        if ($client === null) {
            App::flash('error', 'العميل غير موجود.');
            redirect('/admin/clients');
        }

        $documents = Database::fetchAll(
            'SELECT d.*,
                    (SELECT COUNT(*) FROM verification_logs v WHERE v.document_id = d.id) AS verification_count
             FROM documents d
             WHERE d.client_id = ?
             ORDER BY d.created_at DESC',
            [$id]
        );

        $this->view('admin/clients/documents', [
            'pageTitle' => 'مستندات العميل: ' . $client['name'],
            'activeMenu' => 'clients',
            'user' => App::user(),
            'client' => $client,
            'documents' => $documents,
        ]);
    }

    private function validate(array $data): array
    {
        $errors = [];
        $name = trim((string)($data['name'] ?? ''));
        if ($name === '') {
            $errors[] = 'اسم العميل مطلوب.';
        } elseif (mb_strlen($name) > 150) {
            $errors[] = 'اسم العميل طويل جدًا.';
        }

        return $errors;
    }
}
