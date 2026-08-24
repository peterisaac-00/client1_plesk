<?php

namespace App\Controllers;

use App\Core\App;
use App\Core\Controller;
use App\Models\Client;
use App\Models\Document;
use App\Models\VerificationLog;

class DashboardController extends Controller
{
    protected string $layout = 'admin';
    protected bool $requireAuth = true;

    public function index(): void
    {
        $stats = Document::stats();
        $verificationCount = VerificationLog::total();

        // إحصائيات إضافية
        $documentsWithClients = Document::count('client_id IS NOT NULL');

        $this->view('admin/dashboard', [
            'pageTitle' => 'لوحة التحكم',
            'activeMenu' => 'dashboard',
            'user' => App::user(),
            'stats' => $stats,
            'verificationCount' => $verificationCount,
            'verificationsToday' => VerificationLog::countToday(),
            'clientsCount' => Client::count(),
            'documentsWithClients' => $documentsWithClients,
            'recentDocuments' => Document::recent(5),
            'recentVerifications' => VerificationLog::recent(8),
            'weeklyVerifications' => VerificationLog::countPerDay(7),
            'loadCharts' => true,
        ]);
    }
}
