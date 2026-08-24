<?php

namespace App\Controllers;

use App\Core\App;
use App\Core\Controller;
use App\Core\Paginator;
use App\Models\Client;
use App\Models\VerificationLog;

class VerificationsController extends Controller
{
    protected string $layout = 'admin';
    protected bool $requireAuth = true;

    public function index(): void
    {
        $filters = [
            'q' => trim((string)($_GET['q'] ?? '')),
            'client_id' => (int)($_GET['client_id'] ?? 0),
            'date_from' => trim((string)($_GET['date_from'] ?? '')),
            'date_to' => trim((string)($_GET['date_to'] ?? '')),
        ];

        $perPage = (int)config('pagination_per_page', 15);
        $page = max(1, (int)($_GET['page'] ?? 1));

        $result = VerificationLog::filter($filters, $perPage, $page);
        $paginator = new Paginator($result['items'], $result['total'], $page, $perPage);

        $this->view('admin/verifications/index', [
            'pageTitle' => 'سجل عمليات التحقق',
            'activeMenu' => 'verifications',
            'user' => App::user(),
            'logs' => $paginator->items(),
            'paginator' => $paginator,
            'filters' => $filters,
            'clients' => Client::all(),
            'ipEnabled' => (bool)config('ip_logging'),
        ]);
    }
}
