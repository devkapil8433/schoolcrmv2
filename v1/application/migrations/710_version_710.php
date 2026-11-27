<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_710 extends CI_Migration
{
    public function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        if (!$this->db->field_exists('explanation', 'questions')) {
            $this->dbforge->add_column('questions', array(
                'explanation' => array(
                    'type' => 'LONGTEXT',
                    'null' => true,
                ),
            ));
        }
    }
}
