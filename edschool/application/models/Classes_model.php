<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Classes_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getTeacherAllocation($branch_id = '')
    {
        $this->load->model('digital_diary_model');
        $this->digital_diary_model->ensureRoleAllocationTable();
        $this->db->select('ta.*,st.name as teacher_name,st.staff_id as teacher_id,c.name as class_name,c.branch_id,s.name as section_name, st_coord.name as coordinator_name, st_mod.name as moderator_name');
        $this->db->from('teacher_allocation as ta');
        $this->db->join('staff as st', 'st.id = ta.teacher_id', 'left');
        $this->db->join('class as c', 'c.id = ta.class_id', 'left');
        $this->db->join('section as s', 's.id = ta.section_id', 'left');
        $this->db->join('digital_diary_role_allocation ra_c', "ra_c.class_id = ta.class_id AND ra_c.section_id = ta.section_id AND ra_c.session_id = ta.session_id AND ra_c.role_type = 'coordinator'", 'left');
        $this->db->join('staff as st_coord', 'st_coord.id = ra_c.teacher_id', 'left');
        $this->db->join('digital_diary_role_allocation ra_m', "ra_m.class_id = ta.class_id AND ra_m.section_id = ta.section_id AND ra_m.session_id = ta.session_id AND ra_m.role_type = 'moderator'", 'left');
        $this->db->join('staff as st_mod', 'st_mod.id = ra_m.teacher_id', 'left');
        $this->db->order_by('ta.id', 'ASC');
        $this->db->where('ta.session_id', get_session_id());
        if (!empty($branch_id)) {
            $this->db->where('c.branch_id', $branch_id);
        }
        return $this->db->get();
    }

    public function teacherAllocationSave($data)
    {
        $branchID = $this->application_model->get_branch_id();
        $arrayData = array(
            'branch_id'     => $branchID,
            'session_id'    => get_session_id(),
            'class_id'      => $data['class_id'],
            'section_id'    => $data['section_id'],
            'teacher_id'    => $data['staff_id'],
        );
        if (!isset($data['allocation_id'])) {
            if (get_permission('assign_class_teacher', 'is_add')) {
                $this->db->insert('teacher_allocation', $arrayData);
            }
            set_alert('success', translate('information_has_been_saved_successfully'));
        } else {
            if (get_permission('assign_class_teacher', 'is_edit')) {
                $this->db->where('id', $data['allocation_id']);
                $this->db->update('teacher_allocation', $arrayData);
            }
            set_alert('success', translate('information_has_been_updated_successfully'));
        }
        $this->load->model('digital_diary_model');
        $this->digital_diary_model->upsertClassSectionDiaryRole(
            'coordinator',
            $data['class_id'],
            $data['section_id'],
            $branchID,
            isset($data['coordinator_id']) ? $data['coordinator_id'] : 0
        );
        $this->digital_diary_model->upsertClassSectionDiaryRole(
            'moderator',
            $data['class_id'],
            $data['section_id'],
            $branchID,
            isset($data['moderator_id']) ? $data['moderator_id'] : 0
        );
    }
    public function get_all_classes()
    {
        $this->db->select('id, name');
        $this->db->from('class');
        $this->db->order_by('id', 'ASC');
        return $this->db->get()->result_array();
    }

}