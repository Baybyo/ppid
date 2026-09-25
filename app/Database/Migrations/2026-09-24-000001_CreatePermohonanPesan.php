<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePermohonanPesan extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'permohonan_id' => ['type' => 'INT', 'unsigned' => true],
            'admin_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'tipe' => ['type' => 'ENUM', 'constraint' => ['penolakan', 'umum'], 'default' => 'umum'],
            'judul' => ['type' => 'VARCHAR', 'constraint' => 150],
            'isi' => ['type' => 'TEXT'],
            'is_read' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'email_sent' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('permohonan_id');
        $this->forge->addForeignKey('permohonan_id', 'permohonan', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('permohonan_pesan', true);
    }

    public function down()
    {
        $this->forge->dropTable('permohonan_pesan', true);
    }
}
