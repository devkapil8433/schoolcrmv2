<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_712 extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('fee_receipt_reversals')) {
            $this->dbforge->add_field(array(
                'id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true),
                'branch_id' => array('type' => 'INT', 'constraint' => 11, 'null' => false),
                'student_id' => array('type' => 'INT', 'constraint' => 11, 'null' => false),
                'receipt_no' => array('type' => 'INT', 'constraint' => 11, 'null' => false),
                'reason' => array('type' => 'TEXT', 'null' => false),
                'reversed_by' => array('type' => 'INT', 'constraint' => 11, 'null' => false),
                'reversed_at' => array('type' => 'DATETIME', 'null' => false),
            ));
            $this->dbforge->add_key('id', true);
            $this->dbforge->add_key(array('branch_id', 'receipt_no'), false, true);
            $this->dbforge->create_table('fee_receipt_reversals', true, array('ENGINE' => 'InnoDB'));
        }
    }

    public function down()
    {
        // Reversal records are financial audit history and must not be dropped.
    }
}
