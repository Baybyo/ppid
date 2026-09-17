<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MasyarakatSeeder extends Seeder
{
    public function run()
    {
        if ($this->db->table('masyarakat')->countAllResults() > 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        // Demo login: no_hp di bawah ini + password "masyarakat123"
        $data = [
            ['nama' => 'Budi Santoso', 'nisn' => '0051234567', 'email' => 'budi.santoso@example.com', 'no_hp' => '081234567890'],
            ['nama' => 'Siti Rahma', 'nisn' => '0051234568', 'email' => 'siti.rahma@example.com', 'no_hp' => '081298765432'],
            ['nama' => 'Andi Wijaya', 'nisn' => '0051234569', 'email' => null, 'no_hp' => '081211122233', 'is_active' => 0],
        ];

        foreach ($data as &$row) {
            $row['password'] = password_hash('masyarakat123', PASSWORD_DEFAULT);
            $row['is_active'] = $row['is_active'] ?? 1;
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        $this->db->table('masyarakat')->insertBatch($data);
    }
}
