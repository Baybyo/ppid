<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePermohonanTahapan extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'permohonan_id' => ['type' => 'INT', 'unsigned' => true],
            'tahap' => ['type' => 'ENUM', 'constraint' => ['Diterima', 'Verifikasi', 'Diproses', 'Selesai']],
            'urutan' => ['type' => 'TINYINT', 'unsigned' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['Menunggu', 'Dalam Proses', 'Selesai'], 'default' => 'Menunggu'],
            'sla_hari' => ['type' => 'INT', 'null' => true],
            'tanggal_mulai' => ['type' => 'DATETIME', 'null' => true],
            'tanggal_selesai' => ['type' => 'DATETIME', 'null' => true],
            'keterangan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('permohonan_id', 'permohonan', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('permohonan_tahapan', true);
    }

    public function down()
    {
        $this->forge->dropTable('permohonan_tahapan', true);
    }
}
