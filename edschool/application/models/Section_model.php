<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Section_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_all_sections(){
        $this->db->select('id,name');
        $this->db->from('section');
        $this->db->order_by('name','ASC');
        return $this->db->get()->result_array();
    }
}