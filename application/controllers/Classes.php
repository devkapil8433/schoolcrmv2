<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Classes extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('classes_model');
    }

    /* class form validation rules */
    protected function class_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $this->form_validation->set_rules('name', translate('name'), 'trim|required');
        $this->form_validation->set_rules('name_numeric', translate('name_numeric'), 'trim|numeric');
        $this->form_validation->set_rules('sections[]', translate('section'), 'trim|required');
    }

    public function index()
    {
        if (!get_permission('classes', 'is_view')) {
            access_denied();
        }
        if ($_POST) {
            if (get_permission('classes', 'is_add')) {
                $this->class_validation();
                if ($this->form_validation->run() !== false) {
                    $arrayClass = array(
                        'name' => $this->input->post('name'),
                        'name_numeric' => $this->input->post('name_numeric'),
                        'branch_id' => $this->application_model->get_branch_id(),
                    );
                    $this->db->insert('class', $arrayClass);
                    $class_id = $this->db->insert_id();
                    $sections = $this->input->post('sections');
                    foreach ($sections as $section) {
                        $arrayData = array(
                            'class_id' => $class_id,
                            'section_id' => $section,
                        );
                        $query = $this->db->get_where("sections_allocation", $arrayData);
                        if ($query->num_rows() == 0) {
                            $this->db->insert('sections_allocation', $arrayData);
                        }
                    }
                    set_alert('success', translate('information_has_been_saved_successfully'));
                    $url = base_url('classes');
                    $array = array('status' => 'success', 'url' => $url, 'error' => '');
                } else {
                    $error = $this->form_validation->error_array();
                    $array = array('status' => 'fail', 'url' => '', 'error' => $error);
                }
                echo json_encode($array);
                exit();
            }
        }
        $this->data['classlist'] = $this->app_lib->getTable('class');
        $this->data['query_classes'] = $this->db->get('class');
        $this->data['title'] = translate('control_classes');
        $this->data['sub_page'] = 'classes/index';
        $this->data['main_menu'] = 'classes';
        $this->load->view('layout/index', $this->data);

    }

    public function edit($id = '')
    {
        if (!get_permission('classes', 'is_edit')) {
            access_denied();
        }
        if ($_POST) {
            $this->class_validation();
            if ($this->form_validation->run() !== false) {
                $id = $this->input->post('class_id');
                $arrayClass = array(
                    'name' => $this->input->post('name'),
                    'name_numeric' => $this->input->post('name_numeric'),
                    'branch_id' => $this->application_model->get_branch_id(),
                );
                $this->db->where('id', $id);
                $this->db->update('class', $arrayClass);
                $sections = $this->input->post('sections');
                foreach ($sections as $section) {
                    $query = $this->db->get_where("sections_allocation", array('class_id' => $id, 'section_id' => $section));
                    if ($query->num_rows() == 0) {
                        $this->db->insert('sections_allocation', array('class_id' => $id, 'section_id' => $section));
                    }
                }
                $this->db->where_not_in('section_id', $sections);
                $this->db->where('class_id', $id);
                $this->db->delete('sections_allocation');
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('classes');
                $array = array('status' => 'success', 'url' => $url, 'error' => '');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'url' => '', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }
        $this->data['class'] = $this->app_lib->getTable('class', array('t.id' => $id), true);
        $this->data['title'] = translate('control_classes');
        $this->data['sub_page'] = 'classes/edit';
        $this->data['main_menu'] = 'classes';
        $this->load->view('layout/index', $this->data);
    }

    public function delete($id = '')
    {
        if (get_permission('classes', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('id', $id);
            $this->db->delete('class');
            if ($this->db->affected_rows() > 0) {
                $this->db->where('class_id', $id);
                $this->db->delete('sections_allocation');
            }
        }
    }

    // class teacher allocation
    public function teacher_allocation()
    {
        if (!get_permission('assign_class_teacher', 'is_view')) {
            access_denied();
        }
        $branch_id = $this->application_model->get_branch_id();
        $this->data['branch_id'] = $branch_id;
        $this->data['query'] = $this->classes_model->getTeacherAllocation($branch_id);
        $this->data['title'] = translate('assign_class_teacher');
        $this->data['sub_page'] = 'classes/teacher_allocation';
        $this->data['main_menu'] = 'classes';
        $this->load->view('layout/index', $this->data);
    }

    public function getAllocationTeacher()
    {
        if (get_permission('assign_class_teacher', 'is_edit')) {
            $allocation_id = $this->input->post('id');
            $row = $this->app_lib->get_table('teacher_allocation', $allocation_id, true);
            if (!empty($row)) {
                $this->load->model('digital_diary_model');
                $roles = $this->digital_diary_model->getClassSectionDiaryStaffIds(
                    $row['class_id'],
                    $row['section_id'],
                    isset($row['branch_id']) ? $row['branch_id'] : 0
                );
                $row['coordinator_id'] = $roles['coordinator'];
                $row['moderator_id'] = $roles['moderator'];
            }
            $this->data['data'] = $row;
            $this->load->view('classes/tallocation_modalEdit', $this->data);
        }
    }

    public function teacher_allocation_save()
    {
        if ($_POST) {
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            }
            $this->form_validation->set_rules('class_id', translate('class'), 'required');
            $this->form_validation->set_rules('section_id', translate('section'), 'required|callback_unique_sectionID');
            $this->form_validation->set_rules('staff_id', translate('teacher'), 'required|callback_unique_teacherID');
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                $this->classes_model->teacherAllocationSave($post);
                $url = base_url('classes/teacher_allocation');
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
        }
    }

    public function teacher_allocation_delete($id = '')
    {
        if (get_permission('assign_class_teacher', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('id', $id);
            $this->db->delete('teacher_allocation');
        }
    }

    protected function can_view_class_overview()
    {
        return is_superadmin_loggedin()
            || is_admin_loggedin()
            || get_permission('classes', 'is_view')
            || get_permission('assign_class_teacher', 'is_view')
            || get_permission('subject_teacher_assign', 'is_view')
            || (is_teacher_loggedin() && get_permission('teacher_timetable', 'is_view'));
    }

    protected function can_assign_coordinator()
    {
        return is_superadmin_loggedin()
            || is_admin_loggedin()
            || get_permission('assign_class_teacher', 'is_add')
            || get_permission('assign_class_teacher', 'is_edit');
    }

    protected function overview_redirect_url($branchID, $filterClassID, $classID = 0, $sectionID = 0)
    {
        $qs = array();
        if (is_superadmin_loggedin() && (int) $branchID > 0) {
            $qs['branch_id'] = (int) $branchID;
        }
        if ((int) $filterClassID > 0) {
            $qs['class_id'] = (int) $filterClassID;
        }
        $url = base_url('classes/overview');
        if (!empty($qs)) {
            $url .= '?' . http_build_query($qs);
        }
        if ((int) $sectionID > 0 && (int) $classID > 0) {
            $url .= '#cs-' . (int) $classID . '-' . (int) $sectionID;
        }
        return $url;
    }

    public function overview()
    {
        if (!$this->can_view_class_overview()) {
            access_denied();
        }
        $branch_id = $this->application_model->get_branch_id();
        if (is_superadmin_loggedin()) {
            $posted = $this->input->get('branch_id');
            if ($posted !== null && $posted !== '') {
                $branch_id = (int) $posted;
            }
        }
        $class_id = (int) $this->input->get('class_id');
        $this->data['branch_id'] = $branch_id;
        $this->data['filter_class_id'] = $class_id;

        // Teachers only see their own schedule — not the full class/staff assignment board.
        $this->data['my_timetables'] = array();
        $this->data['my_staff_id'] = 0;
        $this->data['show_my_schedule'] = false;
        $this->data['overview'] = array();
        $this->data['teacher_overview_only'] = false;
        if (is_teacher_loggedin()) {
            $this->data['teacher_overview_only'] = true;
            $this->load->model('timetable_model');
            $staffID = (int) get_loggedin_user_id();
            $branchForTeacher = (int) ($branch_id > 0 ? $branch_id : get_loggedin_branch_id());
            if ($staffID > 0 && $branchForTeacher > 0) {
                $this->data['show_my_schedule'] = true;
                $this->data['my_staff_id'] = $staffID;
                $this->data['branch_id'] = $branchForTeacher;
                $this->data['my_timetables'] = $this->timetable_model->getTeacherTimetable($staffID, $branchForTeacher);
                $this->data['weekend_raw'] = $this->application_model->getWeekends($branchForTeacher);
            }
        } else {
            $this->data['overview'] = $this->classes_model->getClassStaffOverview($branch_id, $class_id);
        }

        $this->data['title'] = 'Class Overview';
        $this->data['sub_page'] = 'classes/overview';
        $this->data['main_menu'] = 'classes';
        $this->load->view('layout/index', $this->data);
    }

    public function overview_assign_form()
    {
        if (!$this->can_view_class_overview()) {
            access_denied();
        }
        $type = $this->input->post('type');
        $branchID = is_superadmin_loggedin() ? (int) $this->input->post('branch_id') : (int) $this->application_model->get_branch_id();
        $classID = (int) $this->input->post('class_id');
        $sectionID = (int) $this->input->post('section_id');
        $this->data['type'] = $type;
        $this->data['branch_id'] = $branchID;
        $this->data['class_id'] = $classID;
        $this->data['section_id'] = $sectionID;
        $this->data['filter_class_id'] = (int) $this->input->post('filter_class_id');
        $this->data['class_name'] = $classID > 0 ? get_type_name_by_id('class', $classID) : '';
        $this->data['section_name'] = $sectionID > 0 ? get_type_name_by_id('section', $sectionID) : '';
        $this->data['teachers'] = $this->app_lib->getStaffList($branchID, 3);
        $this->data['staff_id'] = (int) $this->input->post('staff_id');
        $this->data['allocation_id'] = (int) $this->input->post('allocation_id');
        $this->data['is_edit'] = $this->data['staff_id'] > 0;
        $this->data['activity_id'] = 0;
        $this->data['activity_name'] = '';
        $this->data['activities'] = array();
        $this->data['subject_id'] = 0;
        $this->data['subject_name'] = '';
        if ($type === 'coordinator') {
            $this->load->model('digital_diary_model');
            $this->data['activity_id'] = (int) $this->input->post('activity_id');
            if ($this->data['activity_id'] > 0) {
                $this->data['activity_name'] = get_type_name_by_id('digital_diary_teaching_activity', $this->data['activity_id']);
            }
            $this->data['activities'] = $this->digital_diary_model->getTeachingActivitiesForRole($branchID, 'coordinator');
        } elseif ($type === 'subject_teacher') {
            $this->data['subject_id'] = (int) $this->input->post('subject_id');
            if ($this->data['subject_id'] > 0) {
                $this->data['subject_name'] = get_type_name_by_id('subject', $this->data['subject_id']);
            }
        }
        $this->load->view('classes/overview_modal', $this->data);
    }

    public function overview_save_class_teacher()
    {
        if (!$_POST) {
            show_404();
        }
        $allocationID = (int) $this->input->post('allocation_id');
        if ($allocationID > 0) {
            if (!get_permission('assign_class_teacher', 'is_edit')) {
                echo json_encode(array('status' => 'fail', 'error' => array('form_error' => translate('access_denied'))));
                return;
            }
        } elseif (!get_permission('assign_class_teacher', 'is_add')) {
            echo json_encode(array('status' => 'fail', 'error' => array('form_error' => translate('access_denied'))));
            return;
        }
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $this->form_validation->set_rules('class_id', translate('class'), 'required');
        $this->form_validation->set_rules('section_id', translate('section'), 'required|callback_unique_sectionID');
        $this->form_validation->set_rules('staff_id', translate('teacher'), 'required|callback_unique_teacherID');
        if ($this->form_validation->run() === false) {
            echo json_encode(array('status' => 'fail', 'error' => $this->form_validation->error_array()));
            return;
        }
        $post = $this->input->post();
        if ($allocationID < 1) {
            unset($post['allocation_id']);
        }
        $this->classes_model->teacherAllocationSave($post);
        echo json_encode(array(
            'status' => 'success',
            'url' => $this->overview_redirect_url(
                $this->application_model->get_branch_id(),
                $this->input->post('filter_class_id'),
                $this->input->post('class_id'),
                $this->input->post('section_id')
            ),
        ));
    }

    public function overview_save_coordinator()
    {
        if (!$_POST) {
            show_404();
        }
        if (!$this->can_assign_coordinator()) {
            echo json_encode(array('status' => 'fail', 'error' => array('form_error' => translate('access_denied'))));
            return;
        }
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $this->form_validation->set_rules('class_id', translate('class'), 'required');
        $this->form_validation->set_rules('section_id', translate('section'), 'required');
        $this->form_validation->set_rules('teaching_activity_id', 'Activity type', 'required|numeric|greater_than[0]');
        $this->form_validation->set_rules('staff_id', translate('teacher'), 'required');
        if ($this->form_validation->run() === false) {
            echo json_encode(array('status' => 'fail', 'error' => $this->form_validation->error_array()));
            return;
        }
        $this->load->model('digital_diary_model');
        $ok = $this->digital_diary_model->upsertClassSectionActivityCoordinator(
            $this->input->post('class_id'),
            $this->input->post('section_id'),
            $this->application_model->get_branch_id(),
            $this->input->post('teaching_activity_id'),
            $this->input->post('staff_id')
        );
        if (!$ok) {
            echo json_encode(array('status' => 'fail', 'error' => array('staff_id' => 'Unable to save coordinator.')));
            return;
        }
        set_alert('success', translate('information_has_been_saved_successfully'));
        echo json_encode(array(
            'status' => 'success',
            'url' => $this->overview_redirect_url(
                $this->application_model->get_branch_id(),
                $this->input->post('filter_class_id'),
                $this->input->post('class_id'),
                $this->input->post('section_id')
            ),
        ));
    }

    public function overview_save_subject_teacher()
    {
        if (!$_POST) {
            show_404();
        }
        if (!get_permission('subject_teacher_assign', 'is_add') && !get_permission('subject_teacher_assign', 'is_edit')) {
            echo json_encode(array('status' => 'fail', 'error' => array('form_error' => translate('access_denied'))));
            return;
        }
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $this->form_validation->set_rules('class_id', translate('class'), 'required');
        $this->form_validation->set_rules('section_id', translate('section'), 'required');
        $this->form_validation->set_rules('subject_id', translate('subject'), 'required');
        $this->form_validation->set_rules('staff_id', translate('teacher'), 'required');
        if ($this->form_validation->run() === false) {
            echo json_encode(array('status' => 'fail', 'error' => $this->form_validation->error_array()));
            return;
        }
        $ok = $this->classes_model->upsertClassSectionSubjectTeacher(
            $this->input->post('class_id'),
            $this->input->post('section_id'),
            $this->application_model->get_branch_id(),
            $this->input->post('subject_id'),
            $this->input->post('staff_id')
        );
        if (!$ok) {
            echo json_encode(array('status' => 'fail', 'error' => array('subject_id' => 'Assign this subject to the class first.')));
            return;
        }
        set_alert('success', translate('information_has_been_saved_successfully'));
        echo json_encode(array(
            'status' => 'success',
            'url' => $this->overview_redirect_url(
                $this->application_model->get_branch_id(),
                $this->input->post('filter_class_id'),
                $this->input->post('class_id'),
                $this->input->post('section_id')
            ),
        ));
    }

    // validate here, if the check teacher allocated for this class
    public function unique_teacherID($teacher_id)
    {
        if (!empty($teacher_id)) {
            $classID = $this->input->post('class_id');
            $sectionID = $this->input->post('section_id');
            $allocationID = $this->input->post('allocation_id');
            if (!empty($allocationID)) {
                $this->db->where_not_in('id', $allocationID);
            }
            $this->db->where('teacher_id', $teacher_id);
            $this->db->where('class_id', $classID);
            $this->db->where('section_id', $sectionID);
            $this->db->where('session_id', get_session_id());
            $query = $this->db->get('teacher_allocation');
            if ($query->num_rows() > 0) {
                $this->form_validation->set_message("unique_teacherID", translate('class_teachers_are_already_allocated_for_this_class'));
                return false;
            } else {
                return true;
            }
        }
    }

    // validate here, if the check teacher allocated for this class
    public function unique_sectionID($sectionID)
    {
        if (!empty($sectionID)) {
            $classID = $this->input->post('class_id');
            $allocationID = $this->input->post('allocation_id');
            if (!empty($allocationID)) {
                $this->db->where_not_in('id', $allocationID);
            }
            $this->db->where('class_id', $classID);
            $this->db->where('section_id', $sectionID);
            $this->db->where('session_id', get_session_id());
            $query = $this->db->get('teacher_allocation');
            if ($query->num_rows() > 0) {
                $this->form_validation->set_message("unique_sectionID", translate('this_class_teacher_already_assigned'));
                return false;
            } else {
                return true;
            }
        }
    }
}
