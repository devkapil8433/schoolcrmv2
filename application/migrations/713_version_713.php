<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_713 extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('fee_receipt_corrections')) {
            $this->dbforge->add_field(array(
                'id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true),
                'branch_id' => array('type' => 'INT', 'constraint' => 11, 'null' => false),
                'student_id' => array('type' => 'INT', 'constraint' => 11, 'null' => false),
                'original_receipt_no' => array('type' => 'INT', 'constraint' => 11, 'null' => false),
                'replacement_receipt_no' => array('type' => 'INT', 'constraint' => 11, 'null' => false),
                'reason' => array('type' => 'TEXT', 'null' => false),
                'original_items' => array('type' => 'LONGTEXT', 'null' => false),
                'corrected_by' => array('type' => 'INT', 'constraint' => 11, 'null' => false),
                'corrected_at' => array('type' => 'DATETIME', 'null' => false),
            ));
            $this->dbforge->add_key('id', true);
            $this->dbforge->add_key(array('branch_id', 'original_receipt_no'), false, true);
            $this->dbforge->create_table('fee_receipt_corrections', true, array('ENGINE' => 'InnoDB'));
        }
    }

    public function down()
    {
        // Receipt corrections are financial audit history and must not be dropped.
    }
}
