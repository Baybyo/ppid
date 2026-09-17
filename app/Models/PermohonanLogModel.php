<?php

namespace App\Models;

use CodeIgniter\Model;

class PermohonanLogModel extends Model
{
    protected $table            = 'permohonan_log';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'permohonan_id', 'admin_id', 'ip_address', 'aktivitas',
        'status_dari', 'status_ke', 'created_at',
    ];

    protected $useTimestamps = false;

    public function getByPermohonan(int $permohonanId): array
    {
        return $this->where('permohonan_id', $permohonanId)
                     ->orderBy('created_at', 'ASC')
                     ->findAll();
    }

    public function logTransition(int $permohonanId, ?int $adminId, string $aktivitas, ?string $dari, ?string $ke): void
    {
        $this->insert([
            'permohonan_id' => $permohonanId,
            'admin_id'      => $adminId,
            'ip_address'    => service('request')->getIPAddress(),
            'aktivitas'     => $aktivitas,
            'status_dari'   => $dari,
            'status_ke'     => $ke,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }
}
