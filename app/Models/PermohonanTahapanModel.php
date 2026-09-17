<?php

namespace App\Models;

use CodeIgniter\Model;

class PermohonanTahapanModel extends Model
{
    protected $table            = 'permohonan_tahapan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'permohonan_id', 'tahap', 'urutan', 'status',
        'sla_hari', 'tanggal_mulai', 'tanggal_selesai', 'keterangan',
    ];

    protected $useTimestamps = false;

    public function getByPermohonan(int $permohonanId): array
    {
        return $this->where('permohonan_id', $permohonanId)
                     ->orderBy('urutan', 'ASC')
                     ->findAll();
    }

    public function initTahapan(int $permohonanId): void
    {
        $tahapan = [
            ['tahap' => 'Diterima',  'urutan' => 1],
            ['tahap' => 'Verifikasi', 'urutan' => 2],
            ['tahap' => 'Diproses',  'urutan' => 3],
            ['tahap' => 'Selesai',   'urutan' => 4],
        ];

        foreach ($tahapan as $t) {
            $this->insert([
                'permohonan_id' => $permohonanId,
                'tahap'         => $t['tahap'],
                'urutan'        => $t['urutan'],
                'status'        => 'Menunggu',
                'tanggal_mulai' => null,
                'tanggal_selesai' => null,
            ]);
        }
    }
}
