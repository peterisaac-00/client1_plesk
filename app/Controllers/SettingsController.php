<?php

namespace App\Controllers;

use App\Core\App;
use App\Core\Controller;
use App\Models\Setting;
use App\Models\User;

class SettingsController extends Controller
{
    protected string $layout = 'admin';
    protected bool $requireAuth = true;

    public function index(): void
    {
        // مسح القيم القديمة إذا لم يكن هناك أخطاء معلقة
        if (!empty($_SESSION['_form_errors']) || !empty($_SESSION['_account_errors'])) {
            $errors = form_errors();
            $accountErrors = App::takeSession('_account_errors', []);
        } else {
            App::clearOld();
            $errors = [];
            $accountErrors = [];
        }

        $this->view('admin/settings/index', [
            'pageTitle' => 'إعدادات النظام',
            'activeMenu' => 'settings',
            'user' => App::user(),
            'errors' => $errors,
            'accountErrors' => $accountErrors,
        ]);
    }

    public function update(): void
    {
        $errors = [];

        $systemName = trim((string)($_POST['system_name'] ?? ''));
        if ($systemName === '') {
            $errors[] = 'اسم النظام مطلوب.';
        }

        if ($errors) {
            $_SESSION['_form_errors'] = $errors;
            App::setOld($_POST);
            redirect('/admin/settings');
        }

        Setting::saveMany([
            'system_name' => $systemName,
        ]);

        App::flash('success', 'تم حفظ الإعدادات بنجاح.');
        redirect('/admin/settings');
    }

    /** تحديث بيانات حساب المدير: البريد الإلكتروني وكلمة المرور */
    public function updateAccount(): void
    {
        $user = App::user();
        $dbUser = User::find((int)$user['id']);
        if ($dbUser === null) {
            redirect('/logout');
        }

        $email = trim((string)($_POST['email'] ?? ''));
        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        $errors = [];

        // كلمة المرور الحالية مطلوبة دائمًا لتأكيد الهوية
        if ($currentPassword === '') {
            $errors[] = 'يرجى إدخال كلمة المرور الحالية.';
        } elseif (!User::verifyPassword($currentPassword, $dbUser['password'])) {
            $errors[] = 'كلمة المرور الحالية غير صحيحة.';
        }

        // البريد الإلكتروني
        if ($email === '') {
            $errors[] = 'البريد الإلكتروني مطلوب.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 191) {
            $errors[] = 'البريد الإلكتروني غير صحيح.';
        } else {
            $existing = User::findByEmail($email);
            if ($existing !== null && (int)$existing['id'] !== (int)$dbUser['id']) {
                $errors[] = 'البريد الإلكتروني مستخدم بالفعل لحساب آخر.';
            }
        }

        // كلمة المرور الجديدة (اختيارية)
        $changingPassword = $newPassword !== '';
        if ($changingPassword || $confirmPassword !== '') {
            if (strlen($newPassword) < 8) {
                $errors[] = 'كلمة المرور الجديدة يجب ألا تقل عن 8 أحرف.';
            } elseif (!preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
                $errors[] = 'كلمة المرور الجديدة يجب أن تحتوي على حروف وأرقام.';
            } elseif ($newPassword !== $confirmPassword) {
                $errors[] = 'تأكيد كلمة المرور غير مطابق.';
            } elseif ($currentPassword !== '' && $newPassword === $currentPassword) {
                $errors[] = 'كلمة المرور الجديدة يجب أن تختلف عن الحالية.';
            }
        }

        // لا يوجد أي تغيير فعلي
        if (!$errors && !$changingPassword && strtolower($email) === strtolower(trim($dbUser['email']))) {
            $_SESSION['_account_errors'][] = 'لا توجد تغييرات لحفظها. عدّل البريد الإلكتروني أو أدخل كلمة مرور جديدة.';
            App::setOld(['email' => $email]);
            redirect('/admin/settings');
        }

        if ($errors) {
            $_SESSION['_account_errors'] = $errors;
            App::setOld(['email' => $email]);
            redirect('/admin/settings');
        }

        User::updateEmail((int)$dbUser['id'], $email);
        if ($changingPassword) {
            User::updatePassword((int)$dbUser['id'], $newPassword);
        }

        App::setUser([
            'id' => (int)$dbUser['id'],
            'name' => $dbUser['name'],
            'email' => strtolower(trim($email)),
            'must_change_password' => 0,
        ]);

        App::flash('success', 'تم تحديث بيانات الحساب بنجاح.');
        redirect('/admin/settings');
    }
}
