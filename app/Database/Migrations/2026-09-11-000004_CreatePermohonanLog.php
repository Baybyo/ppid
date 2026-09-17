<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePermohonanLog extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'permohonan_id' => ['type' => 'INT', 'unsigned' => true],
            'admin_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'aktivitas' => ['type' => 'VARCHAR', 'constraint' => 255],
            'status_dari' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'status_ke' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('permohonan_id', 'permohonan', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('permohonan_log', true);
    }

    public function down()
    {
        $this->forge->dropTable('permohonan_log', true);
    }
}
