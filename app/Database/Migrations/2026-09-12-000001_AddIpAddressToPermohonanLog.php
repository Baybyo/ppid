<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIpAddressToPermohonanLog extends Migration
{
    public function up()
    {
        $fields = $this->db->getFieldData('permohonan_log');
        $columnNames = array_column($fields, 'name');

        if (!in_array('ip_address', $columnNames)) {
            $this->db->query("ALTER TABLE permohonan_log ADD COLUMN ip_address VARCHAR(45) NULL AFTER admin_id");
        }
    }

    public function down()
    {
        $fields = $this->db->getFieldData('permohonan_log');
        $columnNames = array_column($fields, 'name');

        if (in_array('ip_address', $columnNames)) {
            $this->db->query("ALTER TABLE permohonan_log DROP COLUMN ip_address");
        }
    }
}
