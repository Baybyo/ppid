<?php

namespace App\Models;

use CodeIgniter\Model;

class PermohonanModel extends Model
{
    protected $table            = 'permohonan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'masyarakat_id', 'no_registrasi', 'seq_tahunan', 'nama_pemohon',
        'no_identitas', 'jenis_identitas', 'pekerjaan', 'alamat', 'no_telp',
        'email', 'rincian_informasi', 'tujuan_penggunaan', 'cara_memperoleh',
        'cara_salinan', 'cara_salinan_lainnya', 'file_identitas', 'status',
        'alasan_penolakan', 'sla_deadline', 'created_at', 'updated_at', 'submitted_at',
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'nama_pemohon'     => 'required|min_length[3]|max_length[100]',
        'no_identitas'     => 'required|min_length[8]|max_length[20]',
        'jenis_identitas'  => 'required|in_list[KTP,SIM,Paspor]',
        'pekerjaan'        => 'required|min_length[2]|max_length[50]',
        'alamat'           => 'required|min_length[10]|max_length[500]',
        'no_telp'          => 'required|min_length[10]|max_length[15]',
        'email'            => 'required|valid_email|max_length[100]',
        'rincian_informasi'=> 'required|min_length[20]|max_length[2000]',
        'tujuan_penggunaan'=> 'required|min_length[10]|max_length[1000]',
    ];

    public function findByNoRegistrasi(string $no): ?array
    {
        return $this->where('no_registrasi', $no)->first();
    }

    public function forMasyarakat(int $masyarakatId): array
    {
        return $this->where('masyarakat_id', $masyarakatId)
                     ->orderBy('created_at', 'DESC')
                     ->findAll();
    }

    public function listForAdmin(array $filters = []): array
    {
        $builder = $this->builder();
        $builder->where('status !=', 0);

        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }
        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $builder->groupStart()
                ->like('nama_pemohon', $q)
                ->orLike('no_registrasi', $q)
                ->orLike('no_identitas', $q)
                ->groupEnd();
        }
        if (!empty($filters['dari'])) {
            $builder->where('submitted_at >=', $filters['dari'] . ' 00:00:00');
        }
        if (!empty($filters['sampai'])) {
            $builder->where('submitted_at <=', $filters['sampai'] . ' 23:59:59');
        }

        return $builder->orderBy('submitted_at', 'DESC')->get()->getResultArray();
    }

    public function detailForAdmin(int $id): ?array
    {
        return $this->find($id);
    }

    public function recentForAdmin(int $limit = 10): array
    {
        return $this->where('status !=', 0)
                     ->orderBy('submitted_at', 'DESC')
                     ->limit($limit)
                     ->findAll();
    }

    public function statusCounts(): array
    {
        $db = $this->db;
        return [
            'menunggu' => (int) $db->table('permohonan')->where('status', 1)->countAllResults(),
            'diproses' => (int) $db->table('permohonan')->where('status', 2)->countAllResults(),
            'selesai'  => (int) $db->table('permohonan')->where('status', 3)->countAllResults(),
            'ditolak'  => (int) $db->table('permohonan')->where('status', 4)->countAllResults(),
            'total'    => (int) $db->table('permohonan')->where('status !=', 0)->countAllResults(),
        ];
    }

    public function generateNoRegistrasi(): array
    {
        $tahun = date('Y');
        $count = $this->where('status !=', 0)
                       ->where('YEAR(submitted_at)', $tahun)
                       ->countAllResults();
        $seq = $count + 1;
        $noReg = sprintf('PPID-%03d,%s,%s,%s', $seq, date('d'), date('m'), $tahun);
        return ['no_registrasi' => $noReg, 'seq' => $seq];
    }

    public function calculateSlaDeadline(string $submittedAt, int $hariKerja = 10, int $perpanjangan = 7): array
    {
        $date = new \DateTime($submittedAt);
        $added = 0;
        while ($added < $hariKerja) {
            $date->modify('+1 day');
            if ((int) $date->format('N') < 6) $added++;
        }
        $deadline = $date->format('Y-m-d');

        $dateExt = clone $date;
        $added = 0;
        while ($added < $perpanjangan) {
            $dateExt->modify('+1 day');
            if ((int) $dateExt->format('N') < 6) $added++;
        }
        $deadlineExt = $dateExt->format('Y-m-d');

        return ['deadline' => $deadline, 'deadline_ext' => $deadlineExt];
    }

    /**
     * Hitung sisa hari KERJA (Senin–Jumat) antara hari ini menuju deadline.
     * Sabtu & Minggu otomatis tidak dihitung.
     * Nilai negatif = sudah lewat deadline.
     */
    public function sisaHariKerja(?string $deadline): int
    {
        if (empty($deadline)) return 0;

        $today = new \DateTime('today');
        $end   = new \DateTime($deadline);

        $sign = $end < $today ? -1 : 1;
        $from = $sign > 0 ? clone $today : clone $end;
        $to   = $sign > 0 ? $end : clone $today;

        $count = 0;
        $cursor = clone $from;
        while ($cursor <= $to) {
            if ((int) $cursor->format('N') < 6) $count++;
            $cursor->modify('+1 day');
        }

        // Deadline hari ini = masih ada hari ini (1 hari kerja tersisa)
        return $sign * max($count - ($sign > 0 ? 1 : 0), 0);
    }

    /**
     * Total hari kerja yang dibutuhkan sejak kirim sampai deadline.
     */
    public function totalHariKerja(?string $submittedAt, ?string $deadline): ?int
    {
        if (empty($submittedAt) || empty($deadline)) return null;

        $from = new \DateTime($submittedAt);
        $to   = new \DateTime($deadline);

        $count = 0;
        $cursor = clone $from;
        while ($cursor->format('Y-m-d') <= $to->format('Y-m-d')) {
            if ((int) $cursor->format('N') < 6) $count++;
            $cursor->modify('+1 day');
        }
        return max($count - 1, 0);
    }
}
