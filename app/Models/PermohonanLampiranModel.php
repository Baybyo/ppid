<?php

namespace App\Models;

use CodeIgniter\Model;

class PermohonanLampiranModel extends Model
{
    protected $table            = 'permohonan_lampiran';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'permohonan_id', 'tipe', 'nama_file', 'path_file',
        'mime_type', 'ukuran_kb', 'created_at',
    ];

    protected $useTimestamps = false;

    public function getByPermohonan(int $permohonanId, ?string $tipe = null): array
    {
        $builder = $this->where('permohonan_id', $permohonanId);
        if ($tipe) {
            $builder->where('tipe', $tipe);
        }
        return $builder->orderBy('created_at', 'DESC')->findAll();
    }
}
