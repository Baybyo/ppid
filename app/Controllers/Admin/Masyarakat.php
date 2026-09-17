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
}
