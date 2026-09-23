<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MasyarakatModel;

class Masyarakat extends BaseController
{
    protected MasyarakatModel $masyarakatModel;

    public function __construct()
    {
        $this->masyarakatModel = new MasyarakatModel();
    }

    public function index()
    {
        $list = $this->masyarakatModel->orderBy('created_at', 'DESC')->findAll();

        return view('admin/masyarakat/index', [
            'title'       => 'Kelola Masyarakat',
            'active'      => 'masyarakat',
            'pageTitle'   => 'Kelola Masyarakat',
            'pageSubtitle'=> 'Daftar seluruh akun masyarakat terdaftar',
            'list'        => $list,
        ]);
    }

    public function toggleStatus(int $id)
    {
        $user = $this->masyarakatModel->find($id);
        if (!$user) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data tidak ditemukan'])->setStatusCode(404);
        }

        $newStatus = (int) $user['is_active'] === 1 ? 0 : 1;
        $this->masyarakatModel->update($id, ['is_active' => $newStatus]);

        return $this->response->setJSON([
            'status'  => true,
            'message' => $newStatus === 1 ? 'Akun diaktifkan.' : 'Akun dinonaktifkan.',
            'is_active' => $newStatus,
        ]);
    }

    public function detail(int $id)
    {
        $user = $this->masyarakatModel->find($id);
        if (!$user) {
            return redirect()->to(site_url('admin/masyarakat'))->with('error', 'Data tidak ditemukan.');
        }

        return view('admin/masyarakat/detail', [
            'title'       => 'Detail Masyarakat',
            'active'      => 'masyarakat',
            'pageTitle'   => 'Detail Masyarakat',
            'pageSubtitle'=> esc($user['nama']),
            'user'        => $user,
        ]);
    }

    public function update(int $id)
    {
        $user = $this->masyarakatModel->find($id);
        if (!$user) {
            return redirect()->to(site_url('admin/masyarakat'))->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'nama'  => 'required|min_length[3]',
            'no_hp' => 'required|is_unique[masyarakat.no_hp,id,' . $id . ']',
            'email' => 'permit_empty|valid_email|is_unique[masyarakat.email,id,' . $id . ']',
        ];

        $passBaru = $this->request->getPost('password_baru');
        if (!empty($passBaru)) {
            $rules['password_baru'] = 'min_length[8]|regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/]';
            $rules['konfirmasi_password'] = 'required|matches[password_baru]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $data = [
            'nama'  => trim($this->request->getPost('nama')),
            'no_hp' => trim($this->request->getPost('no_hp')),
            'email' => trim($this->request->getPost('email')) ?: null,
            'nisn'  => trim($this->request->getPost('nisn')) ?: null,
            'is_active' => (int) $this->request->getPost('is_active'),
        ];

        if (!empty($passBaru)) {
            $data['password'] = password_hash($passBaru, PASSWORD_DEFAULT);
        }

        $this->masyarakatModel->update($id, $data);

        return redirect()->to(site_url('admin/masyarakat/detail/' . $id))->with('success', 'Data akun berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $user = $this->masyarakatModel->find($id);
        if (!$user) {
            return redirect()->to(site_url('admin/masyarakat'))->with('error', 'Data tidak ditemukan.');
        }

        // Nullify masyarakat_id in permohonan table to maintain archive records without foreign key crash
        $db = \Config\Database::connect();
        $db->table('permohonan')->where('masyarakat_id', $id)->update(['masyarakat_id' => null]);

        $this->masyarakatModel->delete($id);

        return redirect()->to(site_url('admin/masyarakat'))->with('success', 'Akun masyarakat berhasil dihapus.');
    }
}
