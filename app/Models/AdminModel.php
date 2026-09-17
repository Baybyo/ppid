<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminModel extends Model
{
    protected $table            = 'admin';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'username', 'password', 'nama', 'created_at',
    ];

    protected $useTimestamps = false;

    public function findByUsername(string $username): ?array
    {
        return $this->where('username', $username)->first();
    }
}
