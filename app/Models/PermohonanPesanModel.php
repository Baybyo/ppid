<?php

namespace App\Models;

use CodeIgniter\Model;

class PermohonanPesanModel extends Model
{
    protected $table            = 'permohonan_pesan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'permohonan_id', 'admin_id', 'tipe', 'judul', 'isi',
        'is_read', 'email_sent', 'created_at',
    ];

    protected $useTimestamps = false;

    /** Semua pesan untuk 1 permohonan (terbaru di atas). */
    public function getByPermohonan(int $permohonanId): array
    {
        return $this->where('permohonan_id', $permohonanId)
                     ->orderBy('created_at', 'DESC')
                     ->orderBy('id', 'DESC')
                     ->findAll();
    }

    /** Jumlah pesan belum dibaca untuk seluruh pemilik akun ini. */
    public function countUnreadForMasyarakat(int $masyarakatId): int
    {
        return (int) $this->join('permohonan', 'permohonan.id = permohonan_pesan.permohonan_id')
                          ->where('permohonan.masyarakat_id', $masyarakatId)
                          ->where('permohonan_pesan.is_read', 0)
                          ->countAllResults();
    }

    /** [permohonan_id => jumlah] pesan belum dibaca, untuk badge di daftar riwayat. */
    public function unreadPerPermohonan(int $masyarakatId): array
    {
        $rows = $this->select('permohonan_pesan.permohonan_id, COUNT(permohonan_pesan.id) AS jml')
                     ->join('permohonan', 'permohonan.id = permohonan_pesan.permohonan_id')
                     ->where('permohonan.masyarakat_id', $masyarakatId)
                     ->where('permohonan_pesan.is_read', 0)
                     ->groupBy('permohonan_pesan.permohonan_id')
                     ->findAll();

        $out = [];
        foreach ($rows as $r) {
            $out[(int) $r['permohonan_id']] = (int) $r['jml'];
        }
        return $out;
    }

    /** Simpan pesan admin → pemohon. */
    public function kirim(int $permohonanId, ?int $adminId, string $tipe, string $judul, string $isi, bool $emailSent = false): int
    {
        return (int) $this->insert([
            'permohonan_id' => $permohonanId,
            'admin_id'      => $adminId,
            'tipe'          => $tipe === 'penolakan' ? 'penolakan' : 'umum',
            'judul'         => $judul,
            'isi'           => $isi,
            'is_read'       => 0,
            'email_sent'    => $emailSent ? 1 : 0,
            'created_at'    => date('Y-m-d H:i:s'),
        ], true);
    }

    /** Tandai semua pesan pada 1 permohonan sudah dibaca pemohon. */
    public function tandaiDibaca(int $permohonanId): void
    {
        $this->db->table($this->table)
                 ->where('permohonan_id', $permohonanId)
                 ->where('is_read', 0)
                 ->update(['is_read' => 1]);
    }
}
