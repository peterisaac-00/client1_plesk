<?php

/**
 * تعريف مسارات النظام (Routes)
 */

use App\Core\App;
use App\Core\Router;

// ---------- الصفحة الرئيسية ----------
Router::get('/', 'AuthController', 'redirectHome');

// ---------- المصادقة ----------
Router::get('/login', 'AuthController', 'showLogin');
Router::post('/login', 'AuthController', 'login');
Router::post('/logout', 'AuthController', 'logout');

// ---------- لوحة التحكم ----------
Router::get('/admin', 'DashboardController', 'index');
Router::get('/admin/dashboard', 'DashboardController', 'index');

// ---------- العملاء ----------
Router::get('/admin/clients', 'ClientsController', 'index');
Router::get('/admin/clients/create', 'ClientsController', 'create');
Router::post('/admin/clients/store', 'ClientsController', 'store');
Router::get('/admin/clients/{id}/edit', 'ClientsController', 'edit');
Router::post('/admin/clients/{id}/update', 'ClientsController', 'update');
Router::post('/admin/clients/{id}/delete', 'ClientsController', 'destroy');
Router::get('/admin/clients/{id}/documents', 'ClientsController', 'documents');

// ---------- المستندات ----------
Router::get('/admin/documents', 'DocumentsController', 'index');
Router::get('/admin/documents/upload', 'DocumentsController', 'showUpload');
Router::post('/admin/documents/store', 'DocumentsController', 'store');
Router::get('/admin/documents/{id}/qr', 'DocumentsController', 'showQr');
Router::get('/admin/documents/{id}/qr/image', 'DocumentsController', 'qrImage');
Router::get('/admin/documents/{id}/qr/download', 'DocumentsController', 'downloadQr');
Router::post('/admin/documents/{id}/qr/generate', 'DocumentsController', 'generateQr');
Router::post('/admin/documents/{id}/activate', 'DocumentsController', 'activate');
Router::post('/admin/documents/{id}/disable', 'DocumentsController', 'disable');
Router::post('/admin/documents/{id}/replace', 'DocumentsController', 'replaceFile');
Router::get('/admin/documents/{id}/view', 'DocumentsController', 'viewFile');
Router::get('/admin/documents/{id}/viewer', 'DocumentsController', 'showViewer');
Router::get('/admin/documents/{id}/download', 'DocumentsController', 'downloadFile');
Router::post('/admin/documents/{id}/delete', 'DocumentsController', 'destroy');

// ---------- سجل التحقق ----------
Router::get('/admin/verifications', 'VerificationsController', 'index');

// ---------- الإعدادات ----------
Router::get('/admin/settings', 'SettingsController', 'index');
Router::post('/admin/settings', 'SettingsController', 'update');
Router::post('/admin/settings/account', 'SettingsController', 'updateAccount');

// ---------- صفحة التحقق العامة ----------
Router::get('/verify/{token}', 'VerifyController', 'show');
Router::get('/verify/{token}/file', 'VerifyController', 'file');
Router::get('/verify/{token}/download', 'VerifyController', 'download');
