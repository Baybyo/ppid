<?php

namespace App\Controllers;

use App\Models\PermohonanModel;
use App\Models\PermohonanLampiranModel;
use App\Models\PermohonanTahapanModel;
use App\Models\PermohonanLogModel;
use App\Models\MasyarakatModel;

class Permohonan extends BaseController
{
    protected PermohonanModel $permohonanModel;
    protected PermohonanLampiranModel $lampiranModel;
    protected PermohonanTahapanModel $tahapanModel;
    protected PermohonanLogModel $logModel;

    public function __construct()
    {
        $this->permohonanModel = new PermohonanModel();
        $this->lampiranModel   = new PermohonanLampiranModel();
        $this->tahapanModel    = new PermohonanTahapanModel();
        $this->logModel        = new PermohonanLogModel();
    }

    private function requireLogin(): ?\CodeIgniter\HTTP\RedirectResponse
    {
        if (!session()->get('isMasyarakatLoggedIn')) {
            return redirect()->to(site_url('login') . '?redirect=' . urlencode(site_url('permohonan/create')))
                ->with('error', 'Silakan masuk terlebih dahulu.');
        }
        return null;
    }

    private function handleUpload($file): ?array
    {
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $ext  = strtolower($file->getExtension());
        $mime = $file->getMimeType();
        $validExt  = ['pdf', 'jpg', 'jpeg', 'png'];
        $validMime = ['image/jpeg', 'image/png', 'application/pdf'];

        if (!in_array($ext, $validExt, true) || !in_array($mime, $validMime, true)) {
            return null;
        }
        if ($file->getSizeByUnit('mb') > 10) {
            return null;
        }

        $size = $file->getSizeByUnit('kb');
        $orig = $file->getClientName();
        $newName = 'permohonan_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $file->move(FCPATH . 'uploads/permohonan', $newName);

        return [
            'path'     => 'uploads/permohonan/' . $newName,
            'original' => $orig,
            'mime'     => $mime,
            'size'     => (int) $size,
        ];
    }

    private function arrayToJSON($val): string
    {
        return is_array($val) ? json_encode($val) : (string) $val;
    }

    // ─── FORM ────────────────────────────────────────────

    public function create()
    {
        if ($guard = $this->requireLogin()) return $guard;

        $existing = null;
        $id = (int) $this->request->getGet('id');
        if ($id > 0) {
            $row = $this->permohonanModel->find($id);
            if ($row && (int) $row['masyarakat_id'] === (int) session()->get('masyarakatId') && (int) $row['status'] === 0) {
                $existing = $row;
            }
        }

        $masyarakatModel = new MasyarakatModel();
        $user = $masyarakatModel->find((int) session()->get('masyarakatId'));

        return view('permohonan/form', [
            'title'    => 'Formulir Permohonan Informasi Publik',
            'existing' => $existing,
            'user'     => $user,
        ]);
    }


    public function storeDraft()
    {
        if ($guard = $this->requireLogin()) return $guard;

        $id       = (int) $this->request->getPost('id');
        $existing = $id ? $this->permohonanModel->find($id) : null;

        if ($existing && (int) $existing['masyarakat_id'] !== (int) session()->get('masyarakatId')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak'])->setStatusCode(403);
        }

        // Guard: tidak bisa edit submitted/processed/final
        if ($existing && (int) $existing['status'] >= 1) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data sudah dikirim dan tidak bisa diedit.'])->setStatusCode(403);
        }

        $now = date('Y-m-d H:i:s');
        $data = [
            'masyarakat_id'        => session()->get('masyarakatId'),
            'nama_pemohon'         => $this->request->getPost('nama_pemohon') ?: ($existing['nama_pemohon'] ?? ''),
            'no_identitas'         => $this->request->getPost('no_identitas') ?: ($existing['no_identitas'] ?? ''),
            'jenis_identitas'      => $this->request->getPost('jenis_identitas') ?: ($existing['jenis_identitas'] ?? 'KTP'),
            'pekerjaan'            => $this->request->getPost('pekerjaan') ?: ($existing['pekerjaan'] ?? ''),
            'alamat'               => $this->request->getPost('alamat') ?: ($existing['alamat'] ?? ''),
            'no_telp'              => $this->request->getPost('no_telp') ?: ($existing['no_telp'] ?? ''),
            'email'                => $this->request->getPost('email') ?: ($existing['email'] ?? ''),
            'rincian_informasi'    => $this->request->getPost('rincian_informasi') ?: ($existing['rincian_informasi'] ?? ''),
            'tujuan_penggunaan'    => $this->request->getPost('tujuan_penggunaan') ?: ($existing['tujuan_penggunaan'] ?? ''),
            'cara_memperoleh'      => $this->arrayToJSON($this->request->getPost('cara_memperoleh')) ?: ($existing['cara_memperoleh'] ?? '[]'),
            'cara_salinan'         => $this->arrayToJSON($this->request->getPost('cara_salinan')) ?: ($existing['cara_salinan'] ?? '[]'),
            'cara_salinan_lainnya' => $this->request->getPost('cara_salinan_lainnya') ?: ($existing['cara_salinan_lainnya'] ?? null),
            'status'               => 0,
            'updated_at'           => $now,
            'created_at'           => $existing['created_at'] ?? $now,
        ];

        $file = $this->request->getFile('file_identitas');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $ext  = strtolower($file->getExtension());
            $mime = $file->getMimeType();
            $validExt  = ['pdf', 'jpg', 'jpeg', 'png'];
            $validMime = ['image/jpeg', 'image/png', 'application/pdf'];

            if (in_array($ext, $validExt, true) && in_array($mime, $validMime, true) && $file->getSizeByUnit('mb') <= 10) {
                $up = $this->handleUpload($file);
                if ($up) {
                    $data['file_identitas'] = $up['path'];
                }
            }
        }

        if ($existing) {
            $this->permohonanModel->update($id, $data);
        } else {
            $id = $this->permohonanModel->insert($data, true);
        }

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Draft tersimpan',
            'draft_id'=> $id,
        ]);
    }



    public function submit()
    {
        if ($guard = $this->requireLogin()) return $guard;

        $id       = (int) $this->request->getPost('id');
        $existing = $id ? $this->permohonanModel->find($id) : null;

        if ($existing && (int) $existing['masyarakat_id'] !== (int) session()->get('masyarakatId')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak'])->setStatusCode(403);
        }

        // Guard: tidak bisa re-submit yang sudah dikirim
        if ($existing && (int) $existing['status'] >= 1) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data sudah dikirim sebelumnya.'])->setStatusCode(403);
        }

        // Validation ketat sesuai Sesi 06 & aturan KTP 16 digit
        $jenisIdentitas = $this->request->getPost('jenis_identitas');
        $identitasRule = ($jenisIdentitas === 'KTP') ? 'required|exact_length[16]|numeric' : 'required|min_length[8]|max_length[20]|alpha_numeric';

        $rules = [
            'nama_pemohon'      => 'required|min_length[3]|max_length[100]|regex_match[/^[a-zA-Z\s.\'-]+$/]',
            'no_identitas'      => $identitasRule,
            'jenis_identitas'   => 'required|in_list[KTP,SIM,Paspor]',
            'pekerjaan'         => 'required|min_length[2]|max_length[50]',
            'alamat'            => 'required|min_length[10]|max_length[500]',
            'no_telp'           => 'required|min_length[10]|max_length[15]|regex_match[/^08[0-9]+$/]',
            'email'             => 'required|valid_email|max_length[100]',
            'rincian_informasi' => 'required|min_length[20]|max_length[2000]',
            'tujuan_penggunaan' => 'required|min_length[10]|max_length[1000]',
            'persetujuan'       => 'required',
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => false, 'errors' => $errors])->setStatusCode(422);
            }
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $caraMemperoleh = $this->request->getPost('cara_memperoleh');
        $caraSalinan    = $this->request->getPost('cara_salinan');

        if (empty($caraMemperoleh) || !is_array($caraMemperoleh)) {
            $msg = 'Pilih minimal 1 cara memperoleh informasi.';
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => false, 'errors' => ['cara_memperoleh' => $msg]])->setStatusCode(422);
            }
            return redirect()->back()->withInput()->with('error', $msg);
        }

        if (empty($caraSalinan) || !is_array($caraSalinan)) {
            $msg = 'Pilih minimal 1 cara mendapatkan salinan.';
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => false, 'errors' => ['cara_salinan' => $msg]])->setStatusCode(422);
            }
            return redirect()->back()->withInput()->with('error', $msg);
        }

        if (in_array('Yang Lain', $caraSalinan, true)) {
            $lainnya = trim((string) $this->request->getPost('cara_salinan_lainnya'));
            if (empty($lainnya) || strlen($lainnya) < 3 || strlen($lainnya) > 100) {
                $msg = 'Detail "Lainnya" wajib diisi 3-100 karakter.';
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['status' => false, 'errors' => ['cara_salinan_lainnya' => $msg]])->setStatusCode(422);
                }
                return redirect()->back()->withInput()->with('error', $msg);
            }
        }

        $file = $this->request->getFile('file_identitas');
        $hasExistingFile = $existing && !empty($existing['file_identitas']);

        if (!$hasExistingFile && (!$file || !$file->isValid())) {
            $msg = 'File identitas wajib diunggah (JPG/PNG/PDF, max 10MB).';
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => false, 'errors' => ['file_identitas' => $msg]])->setStatusCode(422);
            }
            return redirect()->back()->withInput()->with('error', $msg);
        }

        if ($file && $file->isValid()) {
            $extOk = ['pdf', 'jpg', 'jpeg', 'png'];
            $ext   = strtolower($file->getExtension());
            $mime  = $file->getMimeType();
            $validMimes = ['image/jpeg', 'image/png', 'application/pdf'];

            if (!in_array($ext, $extOk, true) || !in_array($mime, $validMimes, true) || $file->getSizeByUnit('mb') > 10) {
                $msg = 'File harus JPG/JPEG/PNG/PDF, max 10MB.';
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['status' => false, 'errors' => ['file_identitas' => $msg]])->setStatusCode(422);
                }
                return redirect()->back()->withInput()->with('error', $msg);
            }
        }
        $noRegistrasiData = $this->permohonanModel->generateNoRegistrasi();

        $submittedAt = date('Y-m-d H:i:s');
        $sla         = $this->permohonanModel->calculateSlaDeadline($submittedAt, 10, 7);

        $data = [
            'masyarakat_id'        => session()->get('masyarakatId'),
            'no_registrasi'        => $noRegistrasiData['no_registrasi'],
            'seq_tahunan'          => $noRegistrasiData['seq'],
            'nama_pemohon'         => $this->request->getPost('nama_pemohon'),
            'no_identitas'         => $this->request->getPost('no_identitas'),
            'jenis_identitas'      => $this->request->getPost('jenis_identitas'),
            'pekerjaan'            => $this->request->getPost('pekerjaan'),
            'alamat'               => $this->request->getPost('alamat'),
            'no_telp'              => $this->request->getPost('no_telp'),
            'email'                => $this->request->getPost('email'),
            'rincian_informasi'    => $this->request->getPost('rincian_informasi'),
            'tujuan_penggunaan'    => $this->request->getPost('tujuan_penggunaan'),
            'cara_memperoleh'      => json_encode($caraMemperoleh),
            'cara_salinan'         => json_encode($caraSalinan),
            'cara_salinan_lainnya' => $this->request->getPost('cara_salinan_lainnya') ?: null,
            'status'               => 1,
            'sla_deadline'         => $sla['deadline'],
            'submitted_at'         => $submittedAt,
            'updated_at'           => $submittedAt,
            'created_at'           => $existing['created_at'] ?? $submittedAt,
        ];

        $fileOrigName = null;
        $fileMime     = null;
        $fileSize     = 0;

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fileOrigName = $file->getClientName();
            $fileMime     = $file->getMimeType();
            $fileSize     = (int) $file->getSizeByUnit('kb');
            $up = $this->handleUpload($file);
            if ($up) {
                $data['file_identitas'] = $up['path'];
            }
        }

        if ($existing) {
            $this->permohonanModel->update($id, $data);
            $permId = $id;
        } else {
            $permId = $this->permohonanModel->insert($data, true);
        }

        if (!empty($data['file_identitas'])) {
            $existingIdentitas = $this->lampiranModel->where('permohonan_id', $permId)->where('tipe', 'identitas')->first();
            if (!$existingIdentitas) {
                $origName = $fileOrigName ?? basename($data['file_identitas']);
                $this->lampiranModel->insert([
                    'permohonan_id' => $permId,
                    'tipe'          => 'identitas',
                    'nama_file'     => $origName,
                    'path_file'     => $data['file_identitas'],
                    'mime_type'     => $fileMime ?: 'application/octet-stream',
                    'ukuran_kb'     => $fileSize,
                    'created_at'    => date('Y-m-d H:i:s'),
                ]);
            }
        } elseif ($existing && !empty($existing['file_identitas'])) {
            $existingIdentitas = $this->lampiranModel->where('permohonan_id', $permId)->where('tipe', 'identitas')->first();
            if (!$existingIdentitas) {
                $this->lampiranModel->insert([
                    'permohonan_id' => $permId,
                    'tipe'          => 'identitas',
                    'nama_file'     => basename($existing['file_identitas']),
                    'path_file'     => $existing['file_identitas'],
                    'mime_type'     => 'application/octet-stream',
                    'ukuran_kb'     => 0,
                    'created_at'    => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // Init tahapan + log
        $this->tahapanModel->initTahapan($permId);
        $this->logModel->logTransition(
            $permId,
            null,
            'Permohonan dikirim oleh pemohon',
            $existing ? ($existing['status'] ?? '0') : '0',
            '1'
        );

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'       => true,
                'message'      => 'Permohonan berhasil dikirim',
                'no_registrasi'=> $noRegistrasiData['no_registrasi'],
                'redirect'     => site_url('permohonan/success/' . $noRegistrasiData['no_registrasi']),
            ]);
        }

        return redirect()->to(site_url('permohonan/success/' . $noRegistrasiData['no_registrasi']));
    }


    private function calculateWorkingDays(string $startDate, int $days): string
    {
        $date = new \DateTime($startDate);
        $added = 0;
        while ($added < $days) {
            $date->modify('+1 day');
            $dayOfWeek = (int) $date->format('N');
            if ($dayOfWeek < 6) {
                $added++;
            }
        }
        return $date->format('Y-m-d');
    }


    public function success(string $noRegistrasi)
    {
        $row = $this->permohonanModel->findByNoRegistrasi($noRegistrasi);
        if (!$row) {
            return redirect()->to(site_url('permohonan/create'))->with('error', 'Data tidak ditemukan.');
        }

        $isOwner = session()->get('isMasyarakatLoggedIn') && (int) $row['masyarakat_id'] === (int) session()->get('masyarakatId');
        $isAdmin = session()->get('isAdminLoggedIn');
        if (!$isOwner && !$isAdmin) {
            return redirect()->to(site_url('/'))->with('error', 'Akses ditolak.');
        }

        return view('permohonan/success', [
            'title' => 'Permohonan Terkirim',
            'row'   => $row,
        ]);
    }


    public function riwayat()
    {
        if ($guard = $this->requireLogin()) return $guard;

        $list = $this->permohonanModel->forMasyarakat(session()->get('masyarakatId'));

        return view('permohonan/riwayat', [
            'title' => 'Riwayat Permohonan',
            'list'  => $list,
        ]);
    }


    public function detail(int $id)
    {
        if ($guard = $this->requireLogin()) return $guard;

        $row = $this->permohonanModel->find($id);
        if (!$row || (int) $row['masyarakat_id'] !== (int) session()->get('masyarakatId')) {
            return redirect()->to(site_url('permohonan/riwayat'))->with('error', 'Data tidak ditemukan.');
        }

        $logs     = $this->logModel->getByPermohonan($id);
        $lampiran = $this->lampiranModel->getByPermohonan($id);

        return view('permohonan/detail', [
            'title'    => 'Detail Permohonan',
            'row'      => $row,
            'logs'     => $logs,
            'lampiran' => $lampiran,
        ]);
    }
    // ─── CETAK BUKTI ────────────────────────────────────

    public function cetakBukti(int $id)
    {
        $row = $this->permohonanModel->find($id);
        if (!$row) {
            return redirect()->to(site_url('/'))->with('error', 'Data tidak ditemukan.');
        }

        $isOwner = session()->get('isMasyarakatLoggedIn') && (int) $row['masyarakat_id'] === (int) session()->get('masyarakatId');
        $isAdmin = session()->get('isAdminLoggedIn');
        if (!$isOwner && !$isAdmin) {
            return redirect()->to(site_url('/'))->with('error', 'Akses ditolak.');
        }

        return view('permohonan/bukti', [
            'title' => 'Bukti Permohonan',
            'row'   => $row,
        ]);
    }

    public function downloadBukti(int $id)
    {
        $row = $this->permohonanModel->find($id);
        if (!$row) {
            return redirect()->to(site_url('/'))->with('error', 'Data tidak ditemukan.');
        }

        $isOwner = session()->get('isMasyarakatLoggedIn') && (int) $row['masyarakat_id'] === (int) session()->get('masyarakatId');
        $isAdmin = session()->get('isAdminLoggedIn');
        if (!$isOwner && !$isAdmin) {
            return redirect()->to(site_url('/'))->with('error', 'Akses ditolak.');
        }

        $nik = $row['no_identitas'] ?? '';
        $maskedNik = strlen($nik) > 4 ? str_repeat('*', strlen($nik) - 4) . substr($nik, -4) : '****';

        $statusMap = [0 => 'Draft', 1 => 'Menunggu Verifikasi', 2 => 'Diproses', 3 => 'Selesai', 4 => 'Ditolak'];

        $html = '<!DOCTYPE html><html><head><meta charset="utf-8">
<style>
body { font-family: sans-serif; font-size: 12px; color: #1f2a44; }
table { width: 100%; border-collapse: collapse; margin: 10px 0; }
td, th { padding: 6px 10px; border: 1px solid #e6e9f0; text-align: left; vertical-align: top; }
th { background: #f4f6fb; width: 180px; font-weight: 600; }
.header { text-align: center; margin-bottom: 20px; }
.header h2 { margin: 0; font-size: 16px; text-transform: uppercase; }
.header p { margin: 4px 0 0; font-size: 11px; color: #64748b; }
.footer { margin-top: 30px; text-align: right; font-size: 11px; color: #64748b; }
</style></head><body>
<div class="header">
<h2>Bukti Permohonan Informasi Publik</h2>
<p>Dinas Tenaga Kerja Provinsi Jawa Timur</p>
</div>
<table>
<tr><th>No. Registrasi</th><td><strong>' . esc($row['no_registrasi']) . '</strong></td></tr>
<tr><th>Nama Pemohon</th><td>' . esc($row['nama_pemohon']) . '</td></tr>
<tr><th>No. Identitas</th><td>' . esc($maskedNik) . '</td></tr>
<tr><th>Jenis Identitas</th><td>' . esc($row['jenis_identitas']) . '</td></tr>
<tr><th>Pekerjaan</th><td>' . esc($row['pekerjaan']) . '</td></tr>
<tr><th>Alamat</th><td>' . esc($row['alamat']) . '</td></tr>
<tr><th>No. Telp</th><td>' . esc($row['no_telp']) . '</td></tr>
<tr><th>Email</th><td>' . esc($row['email']) . '</td></tr>
<tr><th>Rincian Informasi</th><td>' . esc($row['rincian_informasi']) . '</td></tr>
<tr><th>Tujuan Penggunaan</th><td>' . esc($row['tujuan_penggunaan']) . '</td></tr>
<tr><th>Tanggal Pengajuan</th><td>' . esc($row['submitted_at'] ?? $row['created_at']) . '</td></tr>
<tr><th>Status</th><td>' . ($statusMap[(int)($row['status'] ?? 0)] ?? '-') . '</td></tr>
</table>
<div class="footer">Dicetak pada: ' . date('d M Y H:i') . '</div>
</body></html>';

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'bukti_' . $row['no_registrasi'] . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    // ─── DELETE DRAFT ──────────────────────────────────

    public function deleteDraft(int $id)
    {
        if ($guard = $this->requireLogin()) return $guard;

        $row = $this->permohonanModel->find($id);
        if (!$row || (int) $row['masyarakat_id'] !== (int) session()->get('masyarakatId')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data tidak ditemukan.'])->setStatusCode(404);
        }

        if ((int) $row['status'] !== 0) {
            return $this->response->setJSON(['status' => false, 'message' => 'Hanya draft yang bisa dihapus.'])->setStatusCode(403);
        }

        $this->deleteAssociatedFiles($id);
        $this->permohonanModel->delete($id);
        return $this->response->setJSON(['status' => true, 'message' => 'Draft berhasil dihapus.']);
    }

    private function deleteAssociatedFiles(int $permohonanId): void
    {
        $lampiranList = $this->lampiranModel->getByPermohonan($permohonanId);
        foreach ($lampiranList as $lamp) {
            $path = FCPATH . $lamp['path_file'];
            if (!empty($lamp['path_file']) && file_exists($path)) {
                unlink($path);
            }
        }

        $row = $this->permohonanModel->find($permohonanId);
        if ($row && !empty($row['file_identitas'])) {
            $path = FCPATH . $row['file_identitas'];
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    // ─── TRACKING (PUBLIK) ──────────────────────────────

    public function tracking()
    {
        return view('permohonan/tracking', [
            'title' => 'Lacak Status Permohonan',
        ]);
    }

    public function checkStatus()
    {
        $no = trim((string) $this->request->getPost('no_registrasi'));
        if ($no === '') {
            return $this->response->setJSON(['found' => false, 'message' => 'Masukkan nomor registrasi']);
        }

        $row = $this->permohonanModel->findByNoRegistrasi($no);
        if (!$row) {
            return $this->response->setJSON(['found' => false, 'message' => 'Nomor tidak ditemukan']);
        }

        $logs = $this->logModel->getByPermohonan($row['id']);

        $maskedNik = strlen($row['no_identitas']) > 4
            ? str_repeat('*', strlen($row['no_identitas']) - 4) . substr($row['no_identitas'], -4)
            : '****';

        $maskedTelp = strlen($row['no_telp']) > 4
            ? str_repeat('*', strlen($row['no_telp']) - 4) . substr($row['no_telp'], -4)
            : '****';

        return $this->response->setJSON([
            'found' => true,
            'row'   => [
                'no_registrasi'      => $row['no_registrasi'],
                'nama_pemohon'       => $row['nama_pemohon'],
                'status'             => $row['status'],
                'submitted_at'       => $row['submitted_at'],
                'nik_masked'         => $maskedNik,
                'telp_masked'        => $maskedTelp,
                'rincian_informasi'  => mb_strimwidth($row['rincian_informasi'], 0, 50, '...'),
                'alasan_penolakan'   => $row['alasan_penolakan'],
            ],
            'logs' => $logs,
        ]);
    }
}
