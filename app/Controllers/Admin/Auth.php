<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdminModel;
use App\Filters\LoginRateLimiter;

class Auth extends BaseController
{
    protected AdminModel $adminModel;
    protected LoginRateLimiter $rateLimiter;

    public function __construct()
    {
        $this->adminModel = new AdminModel();
        $this->rateLimiter = new LoginRateLimiter();
    }

    public function login()
    {
        if (session()->get('isAdminLoggedIn')) {
            return redirect()->to(site_url('admin'));
        }

        return view('admin/login', ['title' => 'Admin Login']);
    }

    public function attempt()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $ip       = $this->request->getIPAddress();

        $admin = $this->adminModel->findByUsername($username);

        if (!$admin || !password_verify($password, $admin['password'])) {
            $this->rateLimiter->recordFailedAttempt($ip);
            return redirect()->back()->withInput()->with('error', 'Username atau password salah.');
        }

        $this->rateLimiter->clearAttempts($ip);

        session()->regenerate();
        session()->set([
            'isAdminLoggedIn' => true,
            'adminId'         => $admin['id'],
            'adminUsername'   => $admin['username'],
            'adminNama'       => $admin['nama'],
        ]);

        return redirect()->to(site_url('admin'))->with('success', 'Selamat datang, ' . $admin['nama']);
    }

    public function logout()
    {
        $session = session();
        $session->destroy();
        setcookie(session_name(), '', 0, '/');

        return redirect()->to(site_url('admin/login'))->with('success', 'Anda telah keluar.');
    }
}
