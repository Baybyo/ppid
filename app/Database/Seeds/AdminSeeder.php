<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        if ($this->db->table('admin')->countAllResults() > 0) {
            return;
        }

        $this->db->table('admin')->insert([
            'username'   => 'admin',
            'password'   => password_hash('admin123', PASSWORD_DEFAULT),
            'nama'       => 'Super Admin',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
