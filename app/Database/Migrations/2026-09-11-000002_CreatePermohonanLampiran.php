<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePermohonanLampiran extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'permohonan_id' => ['type' => 'INT', 'unsigned' => true],
            'tipe' => ['type' => 'ENUM', 'constraint' => ['identitas', 'jawaban']],
            'nama_file' => ['type' => 'VARCHAR', 'constraint' => 255],
            'path_file' => ['type' => 'VARCHAR', 'constraint' => 255],
            'mime_type' => ['type' => 'VARCHAR', 'constraint' => 100],
            'ukuran_kb' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('permohonan_id', 'permohonan', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('permohonan_lampiran', true);
    }

    public function down()
    {
        $this->forge->dropTable('permohonan_lampiran', true);
    }
}
