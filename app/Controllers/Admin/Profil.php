<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdminModel;

class Profil extends BaseController
{
    protected AdminModel $adminModel;

    public function __construct()
    {
        $this->adminModel = new AdminModel();
    }

    public function index()
    {
        if (!session()->get('isAdminLoggedIn')) {
            return redirect()->to(site_url('admin/login'));
        }

        $admin = $this->adminModel->find(session()->get('adminId'));

        return view('admin/profil/index', [
            'title' => 'Profil Admin',
            'active' => 'profil',
            'pageTitle' => 'Profil Saya',
            'pageSubtitle' => 'Kelola data akun admin',
            'admin' => $admin,
        ]);
    }

    public function update()
    {
        if (!session()->get('isAdminLoggedIn')) {
            return redirect()->to(site_url('admin/login'));
        }

        $adminId = session()->get('adminId');
        $rules = [
            'nama' => 'required|min_length[3]',
            'username' => "required|is_unique[admin.username,id,{$adminId}]",
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Data tidak valid.');
        }

        $this->adminModel->update($adminId, [
            'nama' => $this->request->getPost('nama'),
            'username' => $this->request->getPost('username'),
        ]);

        session()->set('adminNama', $this->request->getPost('nama'));
        session()->set('adminUsername', $this->request->getPost('username'));

        return redirect()->to(site_url('admin/profil'))->with('success', 'Profil berhasil diperbarui.');
    }

    public function changePassword()
    {
        if (!session()->get('isAdminLoggedIn')) {
            return redirect()->to(site_url('admin/login'));
        }

        $adminId = session()->get('adminId');
        $rules = [
            'password_lama' => 'required',
            'password_baru' => 'required|min_length[6]',
            'konfirmasi_password' => 'required|matches[password_baru]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Gagal mengganti password.');
        }

        $admin = $this->adminModel->find($adminId);

        if (! password_verify($this->request->getPost('password_lama'), $admin['password'])) {
            return redirect()->back()->withInput()->with('error', 'Password lama tidak sesuai.');
        }

        $this->adminModel->update($adminId, [
            'password' => password_hash($this->request->getPost('password_baru'), PASSWORD_DEFAULT),
        ]);

        return redirect()->to(site_url('admin/profil'))->with('success', 'Password berhasil diganti.');
    }
}
