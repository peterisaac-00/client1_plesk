<?php

namespace App\Controllers;

use App\Core\App;
use App\Core\Controller;
use App\Models\User;

class AuthController extends Controller
{
    protected string $layout = 'auth';
    protected bool $requireAuth = false;

    /** توجيه الصفحة الرئيسية */
    public function redirectHome(): void
    {
        redirect(App::user() ? '/admin' : '/login');
    }

    /** صفحة تسجيل الدخول */
    public function showLogin(): void
    {
        if (App::user()) {
            redirect('/admin');
        }
        $this->view('auth/login', [
            'pageTitle' => 'تسجيل الدخول',
            'errors' => App::takeSession('_login_errors', []),
            'locked' => (bool)App::takeSession('_login_locked', false),
        ]);
    }

    /** تنفيذ تسجيل الدخول */
    public function login(): void
    {
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        unset($_SESSION['_login_errors'], $_SESSION['_login_locked']);

        // التحقق من الحقول
        if ($email === '' || $password === '') {
            $_SESSION['_login_errors'][] = 'يرجى إدخال البريد الإلكتروني وكلمة المرور.';
            redirect('/login');
        }

        // حماية من تخمين كلمات المرور
        if (User::isLocked($email, $ip)) {
            $_SESSION['_login_locked'] = true;
            $_SESSION['_login_errors'][] = 'تم إيقاف المحاولات مؤقتًا بسبب محاولات فاشلة متكررة. يرجى المحاولة بعد 15 دقيقة.';
            redirect('/login');
        }

        $user = User::findByEmail($email);

        if ($user === null || !User::verifyPassword($password, $user['password'])) {
            User::registerFailedAttempt($email, $ip);
            $_SESSION['_login_errors'][] = 'بيانات الدخول غير صحيحة. يرجى المحاولة مرة أخرى.';
            redirect('/login');
        }

        // نجاح الدخول
        User::clearFailedAttempts($email, $ip);
        session_regenerate_id(true);

        App::setUser([
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'must_change_password' => (int)$user['must_change_password'],
        ]);

        // إعادة التوجيه للصفحة المطلوبة سابقًا
        $redirectTo = $_SESSION['_redirect_after_login'] ?? url('/admin');
        unset($_SESSION['_redirect_after_login']);
        App::flash('success', 'مرحبًا بعودتك، ' . $user['name'] . '!');
        header('Location: ' . $redirectTo);
        exit;
    }

    /** تسجيل الخروج */
    public function logout(): void
    {
        App::logout();
        App::flash('success', 'تم تسجيل الخروج بنجاح.');
        redirect('/login');
    }
}
