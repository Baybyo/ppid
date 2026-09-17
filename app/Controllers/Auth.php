<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MasyarakatModel;
use App\Filters\LoginRateLimiter;

class Auth extends BaseController
{
    protected MasyarakatModel $masyarakatModel;
    protected LoginRateLimiter $rateLimiter;

    public function __construct()
    {
        $this->masyarakatModel = new MasyarakatModel();
        $this->rateLimiter = new LoginRateLimiter();
    }

    /**
     * GET /register
     */
    public function register()
    {
        if (session()->get('isMasyarakatLoggedIn')) {
            return redirect()->to(site_url('/'));
        }

        return view('auth/register', ['title' => 'Daftar Akun']);
    }

    /**
     * POST /register
     *
     * Fields: nama, nisn, no_hp, email (opsional), password.
     * no_hp wajib & unik karena dipakai untuk login.
     */
    public function doRegister()
    {
        $rules = [
            'nama'                 => 'required|min_length[3]',
            'no_hp'                => 'required|is_unique[masyarakat.no_hp]',
            'email'                => 'permit_empty|valid_email|is_unique[masyarakat.email]',
            'password'             => 'required|min_length[6]',
            'konfirmasi_password'  => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return view('auth/register', [
                'title'      => 'Daftar Akun',
                'validation' => $this->validator,
            ]);
        }

        $this->masyarakatModel->insert([
            'nama'      => $this->request->getPost('nama'),
            'email'     => $this->request->getPost('email') ?: null,
            'no_hp'     => $this->request->getPost('no_hp'),
            'password'  => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'is_active' => 1,
        ]);

        return redirect()->to(site_url('login'))->with('success', 'Akun berhasil dibuat. Silakan masuk.');
    }

    /**
     * GET /login
     */
    public function login()
    {
        if (session()->get('isMasyarakatLoggedIn')) {
            return redirect()->to(site_url('/'));
        }

        return view('auth/login', ['title' => 'Masuk']);
    }

    /**
     * POST /login — login menggunakan NOMOR TELEPON + password.
     */
    public function doLogin()
    {
        $rules = [
            'no_hp'    => 'required',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return view('auth/login', ['title' => 'Masuk', 'validation' => $this->validator]);
        }

        $noHp     = trim($this->request->getPost('no_hp'));
        $password = $this->request->getPost('password');
        $ip       = $this->request->getIPAddress();

        $user = $this->masyarakatModel->findByNoHp($noHp);

        if (! $user || ! password_verify($password, $user['password'])) {
            $this->rateLimiter->recordFailedAttempt($ip);
            return redirect()->back()->withInput()->with('error', 'Nomor telepon atau password salah.');
        }

        if ((int) $user['is_active'] !== 1) {
            return redirect()->back()->withInput()->with('error', 'Akun Anda dinonaktifkan. Hubungi admin.');
        }

        $this->rateLimiter->clearAttempts($ip);

        session()->regenerate();
        session()->set([
            'isMasyarakatLoggedIn' => true,
            'masyarakatId'         => $user['id'],
            'masyarakatNama'       => $user['nama'],
            'masyarakatEmail'      => $user['email'],
            'masyarakatNoHp'       => $user['no_hp'],
        ]);

        $redirectTo = $this->request->getPost('redirect_to');
        $target = ($redirectTo && strpos($redirectTo, site_url()) === 0) ? $redirectTo : site_url('/');

        return redirect()->to($target)->with('success', 'Berhasil masuk. Selamat datang, ' . $user['nama'] . '.');
    }

    /**
     * GET /logout
     */
    public function logout()
    {
        session()->remove(['isMasyarakatLoggedIn', 'masyarakatId', 'masyarakatNama', 'masyarakatEmail', 'masyarakatNoHp']);

        return redirect()->to(site_url('/'))->with('success', 'Anda telah keluar.');
    }
}
