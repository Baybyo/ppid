<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PermohonanModel;
use App\Models\PermohonanLampiranModel;
use App\Models\PermohonanTahapanModel;
use App\Models\PermohonanLogModel;
use App\Models\PermohonanPesanModel;

class Permohonan extends BaseController
{
    protected PermohonanModel $permohonanModel;
    protected PermohonanLampiranModel $lampiranModel;
    protected PermohonanTahapanModel $tahapanModel;
    protected PermohonanLogModel $logModel;
    protected PermohonanPesanModel $pesanModel;

    public function __construct()
    {
        $this->permohonanModel = new PermohonanModel();
        $this->lampiranModel   = new PermohonanLampiranModel();
        $this->tahapanModel    = new PermohonanTahapanModel();
        $this->logModel        = new PermohonanLogModel();
        $this->pesanModel      = new PermohonanPesanModel();
    }

    public function index()
    {
        $filters = [];
        $q = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));
        $dari = trim((string) $this->request->getGet('dari'));
        $sampai = trim((string) $this->request->getGet('sampai'));

        if ($q !== '') $filters['q'] = $q;
        if ($status !== '') $filters['status'] = (int) $status;
        if ($dari !== '') $filters['dari'] = $dari;
        if ($sampai !== '') $filters['sampai'] = $sampai;

        $list = $this->permohonanModel->listForAdmin($filters);

        return view('admin/permohonan/index', [
            'title'       => 'Daftar Permohonan',
            'active'      => 'permohonan',
            'pageTitle'   => 'Daftar Permohonan',
            'pageSubtitle'=> 'Kelola permohonan informasi publik',
            'list'        => $list,
            'q'           => $q,
            'status'      => $status,
            'dari'        => $dari,
            'sampai'      => $sampai,
        ]);
    }

    public function detail(int $id)
    {
        $row = $this->permohonanModel->detailForAdmin($id);
        if (!$row) {
            return redirect()->to(site_url('admin/permohonan'))->with('error', 'Data tidak ditemukan.');
        }

        $logs      = $this->logModel->getByPermohonan($id);
        $lampiran  = $this->lampiranModel->getByPermohonan($id);
        $tahapan   = $this->tahapanModel->getByPermohonan($id);

        // Nama admin pelaku (untuk riwayat log lebih informatif)
        $adminModel = new \App\Models\AdminModel();
        $adminNames = [];
        foreach ($logs as $l) {
            $aid = (int) ($l['admin_id'] ?? 0);
            if ($aid > 0 && !isset($adminNames[$aid])) {
                $a = $adminModel->find($aid);
                $adminNames[$aid] = $a['nama'] ?? ('Admin #' . $aid);
            }
        }

        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');

        return view('admin/permohonan/detail', [
            'title'       => 'Detail Permohonan',
            'active'      => 'permohonan',
            'pageTitle'   => 'Detail Permohonan',
            'pageSubtitle'=> $row['no_registrasi'] ?? 'Draft #' . $row['id'],
            'row'         => $row,
            'logs'        => $logs,
            'lampiran'    => $lampiran,
            'tahapan'     => $tahapan,
            'pesan'       => $this->pesanModel->getByPermohonan($id),
            'adminNames'  => $adminNames,
            'sisaKerja'   => $this->permohonanModel->sisaHariKerja($row['sla_deadline'] ?? null),
            'totalKerja'  => $this->permohonanModel->totalHariKerja($row['submitted_at'] ?? null, $row['sla_deadline'] ?? null),
            'usedKerja'   => max((int) ($this->permohonanModel->totalHariKerja($row['submitted_at'] ?? null, $row['sla_deadline'] ?? null) ?? 0)
                                 - $this->permohonanModel->sisaHariKerja($row['sla_deadline'] ?? null), 0),
        ]);
    }

    public function updateStatus(int $id)
    {
        $row = $this->permohonanModel->find($id);
        if (!$row) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data tidak ditemukan'])->setStatusCode(404);
        }

        $to     = (int) $this->request->getPost('status');
        $alasan = trim((string) $this->request->getPost('alasan_penolakan'));
        $keterangan = trim((string) $this->request->getPost('keterangan'));

        // State machine guard
        $transitions = [
            1 => [2, 4],       // Menunggu -> Diproses / Ditolak
            2 => [3, 4],       // Diproses -> Selesai / Ditolak
            3 => [],           // Selesai = final
            4 => [],           // Ditolak = final
        ];

        $from = (int) $row['status'];
        if (!in_array($to, $transitions[$from] ?? [], true)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => "Transisi dari status {$from} ke {$to} tidak diizinkan.",
            ])->setStatusCode(409);
        }

        if ($to === 4 && empty($alasan)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Alasan penolakan wajib diisi.',
            ])->setStatusCode(422);
        }

        $statusNames = [1 => 'Menunggu Verifikasi', 2 => 'Diproses', 3 => 'Selesai', 4 => 'Ditolak'];

        $update = ['status' => $to, 'updated_at' => date('Y-m-d H:i:s')];
        if ($to === 4) {
            $update['alasan_penolakan'] = $alasan;
        }
        if ($to === 3) {
            $update['alasan_penolakan'] = null;
        }

        $this->permohonanModel->update($id, $update);

        // Update tahapan
        $this->updateTahapan($id, $to);

        // Log
        $this->logModel->logTransition(
            $id,
            session()->get('adminId'),
            'Status diubah: ' . ($statusNames[$from] ?? $from) . ' → ' . ($statusNames[$to] ?? $to) . ($keterangan ? " ({$keterangan})" : ''),
            (string) $from,
            (string) $to
        );

        // Saat menolak: kirim catatan alasan penolakan ke pemohon
        if ($to === 4 && $alasan !== '') {
            $noReg = $row['no_registrasi'] ?? ('Draft #' . $id);
            $this->pesanModel->kirim(
                $id,
                session()->get('adminId') ? (int) session()->get('adminId') : null,
                'penolakan',
                'Permohonan Ditolak - ' . $noReg,
                "Yth. " . ($row['nama_pemohon'] ?? 'Pemohon') . ",\n\n"
                . "Permohonan Anda dengan nomor registrasi " . $noReg . " telah DITOLAK.\n\n"
                . "Alasan penolakan:\n" . $alasan . "\n\n"
                . "Demikian pemberitahuan ini. Terima kasih."
            );

            $this->logModel->logTransition(
                $id,
                session()->get('adminId'),
                'Catatan alasan penolakan dikirim ke pemohon',
                null,
                null
            );
        }

        return $this->response->setJSON([
            'status'  => true,
            'message' => $to === 4
                ? 'Status diperbarui. Alasan penolakan telah dikirim ke pemohon.'
                : 'Status berhasil diperbarui.',
        ]);
    }

    /** Kirim pesan manual dari admin ke pemohon (mis. klarifikasi / alasan penolakan). */
    public function kirimPesan(int $id)
    {
        $row = $this->permohonanModel->find($id);
        if (!$row) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data tidak ditemukan'])->setStatusCode(404);
        }

        $judul = trim((string) $this->request->getPost('judul'));
        $isi   = trim((string) $this->request->getPost('isi'));
        $tipe  = $this->request->getPost('tipe') === 'penolakan' ? 'penolakan' : 'umum';

        if ($judul === '' || $isi === '') {
            return $this->response->setJSON(['status' => false, 'message' => 'Judul dan isi pesan wajib diisi.'])->setStatusCode(422);
        }
        if (mb_strlen($judul) > 150) {
            return $this->response->setJSON(['status' => false, 'message' => 'Judul pesan maksimal 150 karakter.'])->setStatusCode(422);
        }

        $this->pesanModel->kirim(
            $id,
            session()->get('adminId') ? (int) session()->get('adminId') : null,
            $tipe,
            $judul,
            $isi
        );

        $this->logModel->logTransition(
            $id,
            session()->get('adminId'),
            'Pesan dikirim ke pemohon: ' . $judul,
            null,
            null
        );

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Pesan berhasil dikirim ke pemohon.',
        ]);
    }

    private function updateTahapan(int $permohonanId, int $status): void
    {
        $tahapanMap = [
            1 => ['Diterima', 'Verifikasi'],
            2 => ['Diproses'],
            3 => ['Selesai'],
        ];

        $activeTahaps = $tahapanMap[$status] ?? [];

        // Set semua ke Menunggu dulu
        $tahapanAll = $this->tahapanModel->getByPermohonan($permohonanId);
        foreach ($tahapanAll as $t) {
            if (in_array($t['tahap'], $activeTahaps)) {
                $this->tahapanModel->update($t['id'], [
                    'status'       => $status === 3 ? 'Selesai' : 'Dalam Proses',
                    'tanggal_mulai' => $t['tanggal_mulai'] ?? date('Y-m-d H:i:s'),
                    'tanggal_selesai' => $status === 3 ? date('Y-m-d H:i:s') : null,
                ]);
            } elseif ($t['status'] !== 'Selesai') {
                $this->tahapanModel->update($t['id'], ['status' => 'Menunggu']);
            }
        }
    }

    public function uploadJawaban(int $id)
    {
        $row = $this->permohonanModel->find($id);
        if (!$row) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data tidak ditemukan'])->setStatusCode(404);
        }

        if ((int)$row['status'] === 4) {
            return $this->response->setJSON(['status' => false, 'message' => 'Permohonan ditolak. Tidak dapat mengunggah file jawaban.'])->setStatusCode(403);
        }

        $file = $this->request->getFile('file_jawaban');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['status' => false, 'message' => 'File jawaban wajib.'])->setStatusCode(422);
        }

        $extOk = ['pdf', 'jpg', 'jpeg', 'png'];
        $ext   = strtolower($file->getExtension());
        $mime  = $file->getMimeType();
        $validMimes = ['image/jpeg', 'image/png', 'application/pdf'];

        if (!in_array($ext, $extOk, true) || !in_array($mime, $validMimes, true) || $file->getSizeByUnit('mb') > 10) {
            return $this->response->setJSON(['status' => false, 'message' => 'File harus JPG/JPEG/PNG/PDF, max 10MB.'])->setStatusCode(422);
        }

        $newName = 'jawaban_' . $id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $file->move(FCPATH . 'uploads/permohonan', $newName);

        $this->lampiranModel->insert([
            'permohonan_id' => $id,
            'tipe'          => 'jawaban',
            'nama_file'     => $file->getClientName(),
            'path_file'     => 'uploads/permohonan/' . $newName,
            'mime_type'     => $mime,
            'ukuran_kb'     => (int) $file->getSizeByUnit('kb'),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        $this->logModel->logTransition(
            $id,
            session()->get('adminId'),
            'Upload file jawaban: ' . $file->getClientName(),
            null,
            null
        );

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Jawaban berhasil diunggah.',
        ]);
    }

    public function delete(int $id)
    {
        $row = $this->permohonanModel->find($id);
        if (!$row) {
            return redirect()->to(site_url('admin/permohonan'))->with('error', 'Data tidak ditemukan.');
        }

        if ((int) $row['status'] !== 0) {
            return redirect()->to(site_url('admin/permohonan'))->with('error', 'Hanya draft yang bisa dihapus.');
        }

        $lampiranList = $this->lampiranModel->getByPermohonan($id);
        foreach ($lampiranList as $lamp) {
            $path = FCPATH . $lamp['path_file'];
            if (!empty($lamp['path_file']) && file_exists($path)) {
                unlink($path);
            }
        }
        if (!empty($row['file_identitas'])) {
            $path = FCPATH . $row['file_identitas'];
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $this->permohonanModel->delete($id);
        return redirect()->to(site_url('admin/permohonan'))->with('success', 'Permohonan dihapus.');
    }

    public function export()
    {
        $filters = [];
        $q = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));
        $dari = trim((string) $this->request->getGet('dari'));
        $sampai = trim((string) $this->request->getGet('sampai'));

        if ($q !== '') $filters['q'] = $q;
        if ($status !== '') $filters['status'] = (int) $status;
        if ($dari !== '') $filters['dari'] = $dari;
        if ($sampai !== '') $filters['sampai'] = $sampai;

        $list = $this->permohonanModel->listForAdmin($filters);

        $statusMap = [
            0 => 'Draft',
            1 => 'Menunggu Verifikasi',
            2 => 'Diproses',
            3 => 'Selesai',
            4 => 'Ditolak',
        ];

        $filename = 'rekap_permohonan_' . date('Y-m-d_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [
            'No. Registrasi',
            'Nama Pemohon',
            'No. Identitas',
            'Jenis Identitas',
            'Pekerjaan',
            'No. Telp',
            'Email',
            'Tanggal Kirim',
            'Deadline SLA',
            'Status',
            'Alasan Penolakan',
        ], ';');

        foreach ($list as $row) {
            fputcsv($output, [
                $row['no_registrasi'] ?? '-',
                $row['nama_pemohon'] ?? '-',
                $row['no_identitas'] ?? '-',
                $row['jenis_identitas'] ?? '-',
                $row['pekerjaan'] ?? '-',
                $row['no_telp'] ?? '-',
                $row['email'] ?? '-',
                $row['submitted_at'] ?? '-',
                $row['sla_deadline'] ?? '-',
                $statusMap[(int)($row['status'] ?? 0)] ?? '-',
                $row['alasan_penolakan'] ?? '-',
            ], ';');
        }

        fclose($output);
        exit;
    }
}
