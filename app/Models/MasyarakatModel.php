<?php

namespace App\Models;

use CodeIgniter\Model;

class MasyarakatModel extends Model
{
    protected $table            = 'masyarakat';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'nama', 'nisn', 'email', 'password', 'no_hp',
        'is_active', 'created_at', 'updated_at',
    ];

    protected $useTimestamps = true;

    public function findByNoHp(string $noHp): ?array
    {
        return $this->where('no_hp', $noHp)->first();
    }

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }
}
