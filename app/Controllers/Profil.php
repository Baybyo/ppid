<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MasyarakatModel;

class Profil extends BaseController
{
    protected MasyarakatModel $masyarakatModel;

    public function __construct()
    {
        $this->masyarakatModel = new MasyarakatModel();
    }

    /**
     * GET /profil — Tampilan profil saya
     */
    public function index()
    {
        if (! session()->get('isMasyarakatLoggedIn')) {
            return redirect()->to(site_url('login'));
        }

        $user = $this->masyarakatModel->find(session()->get('masyarakatId'));

        return view('profil/index', [
            'title' => 'Profil Saya',
            'user'  => $user,
        ]);
    }

    /**
     * POST /profil/update — Update data profil
     */
    public function update()
    {
        if (! session()->get('isMasyarakatLoggedIn')) {
            return redirect()->to(site_url('login'));
        }

        $userId = session()->get('masyarakatId');
        $rules = [
            'nama'   => 'required|min_length[3]',
            'email'  => "permit_empty|valid_email|is_unique[masyarakat.email,id,{$userId}]",
            'no_hp'  => "required|is_unique[masyarakat.no_hp,id,{$userId}]",
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Data tidak valid. Silakan periksa kembali.');
        }

        $data = [
            'nama'  => $this->request->getPost('nama'),
            'email' => $this->request->getPost('email') ?: null,
            'no_hp' => $this->request->getPost('no_hp'),
        ];

        $this->masyarakatModel->update($userId, $data);

        session()->set([
            'masyarakatNama'  => $data['nama'],
            'masyarakatEmail' => $data['email'],
            'masyarakatNoHp'  => $data['no_hp'],
        ]);

        return redirect()->to(site_url('profil'))->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * POST /profil/change-password — Ganti password
     */
    public function changePassword()
    {
        if (! session()->get('isMasyarakatLoggedIn')) {
            return redirect()->to(site_url('login'));
        }

        $userId = session()->get('masyarakatId');
        $rules = [
            'password_lama'     => 'required',
            'password_baru'     => 'required|min_length[6]',
            'konfirmasi_password' => 'required|matches[password_baru]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Gagal mengganti password. Silakan periksa kembali.');
        }

        $user = $this->masyarakatModel->find($userId);

        if (! password_verify($this->request->getPost('password_lama'), $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Password lama tidak sesuai.');
        }

        $this->masyarakatModel->update($userId, [
            'password' => password_hash($this->request->getPost('password_baru'), PASSWORD_DEFAULT),
        ]);

        return redirect()->to(site_url('profil'))->with('success', 'Password berhasil diganti.');
    }
}
