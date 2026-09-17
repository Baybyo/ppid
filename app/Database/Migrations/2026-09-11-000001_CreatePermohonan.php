<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePermohonan extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'masyarakat_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'no_registrasi' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'seq_tahunan' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'nama_pemohon' => ['type' => 'VARCHAR', 'constraint' => 150],
            'no_identitas' => ['type' => 'VARCHAR', 'constraint' => 30],
            'jenis_identitas' => ['type' => 'ENUM', 'constraint' => ['KTP', 'SIM', 'Paspor']],
            'pekerjaan' => ['type' => 'VARCHAR', 'constraint' => 50],
            'alamat' => ['type' => 'TEXT'],
            'no_telp' => ['type' => 'VARCHAR', 'constraint' => 20],
            'email' => ['type' => 'VARCHAR', 'constraint' => 150],
            'rincian_informasi' => ['type' => 'TEXT'],
            'tujuan_penggunaan' => ['type' => 'TEXT'],
            'cara_memperoleh' => ['type' => 'VARCHAR', 'constraint' => 255],
            'cara_salinan' => ['type' => 'VARCHAR', 'constraint' => 255],
            'cara_salinan_lainnya' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'file_identitas' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'alasan_penolakan' => ['type' => 'TEXT', 'null' => true],
            'sla_deadline' => ['type' => 'DATE', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'submitted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('no_registrasi');
        $this->forge->addKey('masyarakat_id');
        $this->forge->addKey('status');
        $this->forge->addKey('no_identitas');
        $this->forge->createTable('permohonan', true);
    }

    public function down()
    {
        $this->forge->dropTable('permohonan', true);
    }
}
