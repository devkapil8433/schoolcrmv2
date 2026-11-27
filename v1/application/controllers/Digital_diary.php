<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Digital Diary Module
 * @package : Ramom school management system
 * @filename : Digital_diary.php
 */

class Digital_diary extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('datatables');
        $this->load->model('digital_diary_model');
        $this->load->model('subject_model');
    }

    /**
     * Teachers only use teaching activity log; block other diary admin/report URLs.
     */
    protected function digital_diary_forbid_teacher()
    {
        if (is_teacher_loggedin()) {
            access_denied();
        }
    }

    /**
     * Empty DataTables payload when a teacher must not see the data (avoid redirect on AJAX).
     */
    protected function digital_diary_teacher_empty_datatable()
    {
        echo json_encode(array(
            'draw' => (int) $this->input->post('draw'),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
        ));
    }

    protected function diary_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required');
        }
        $this->form_validation->set_rules('class_id', translate('class'), 'trim|required');
        $this->form_validation->set_rules('section_id', translate('section'), 'trim|required');
        $this->form_validation->set_rules('entry_date', translate('date'), 'trim|required');
        $this->form_validation->set_rules('entry_title', translate('title'), 'trim|required');
    }

    /**
     * Digital Diary - Main listing page
     */
    public function index()
    {
        if (is_teacher_loggedin()) {
            if ($_POST) {
                echo json_encode(array('status' => 'fail', 'error' => array('form_error' => translate('access_denied'))));
                exit();
            }
            access_denied();
        }
        if ($_POST) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'trim');
            $this->form_validation->set_rules('class_id', translate('class'), 'trim');
            $this->form_validation->set_rules('section_id', translate('section'), 'trim');
            if ($this->form_validation->run() == true) {
                $branchID = $this->application_model->get_branch_id();
                $export_title = get_type_name_by_id('branch', $branchID) . ' - ' . (isset($this->lang->language['digital_diary']) ? translate('digital_diary') : 'Digital Diary') . ' ' . translate('list');
                $array = array('status' => 'success', 'export_title' => $export_title, 'error' => '');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }

        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['title'] = isset($this->lang->language['digital_diary']) ? translate('digital_diary') : 'Digital Diary';
        $this->data['sub_page'] = 'digital_diary/index';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * DataTables server-side for diary list
     */
    public function getDiaryListDT()
    {
        if ($_POST) {
            if (is_teacher_loggedin()) {
                $this->digital_diary_teacher_empty_datatable();
                return;
            }
            $submit_btn = $this->input->post('submit_btn');
            if (empty($submit_btn)) {
                $json_data = array(
                    "draw" => intval(0),
                    "recordsTotal" => intval(0),
                    "recordsFiltered" => intval(0),
                    "data" => [],
                );
                echo json_encode($json_data);
            } else {
                $results = $this->digital_diary_model->getDiaryDT_list();
                $startDT = $this->input->post('start');
                $records = json_decode($results);
                $data = array();
                $count = $startDT + 1;
                foreach ($records->data as $key => $record) {
                    $actions = '<a href="' . base_url('digital_diary/edit/' . $record->id) . '" class="btn btn-circle btn-default icon"><i class="fas fa-pen-nib"></i></a>';
                    $actions .= btn_delete('digital_diary/delete/' . $record->id);

                    if (isset($record->status) && $record->status == 1) {
                        $status = translate('published');
                        $labelmode = 'label-success-custom';
                    } else {
                        $status = isset($this->lang->language['draft']) ? translate('draft') : 'Draft';
                        $labelmode = 'label-info-custom';
                    }

                    $student_name = !empty($record->student_name) ? $record->student_name : '—';

                    $row = array();
                    $row[] = $count++;
                    $row[] = _d($record->entry_date);
                    $row[] = $record->class_name;
                    $row[] = $record->section_name;
                    $row[] = $student_name;
                    $row[] = $record->title;
                    $row[] = "<span class='value label " . $labelmode . "'>" . $status . "</span>";
                    $row[] = $actions;
                    $data[] = $row;
                }
                $json_data = array(
                    "draw" => intval($records->draw),
                    "recordsTotal" => intval($records->recordsTotal),
                    "recordsFiltered" => intval($records->recordsFiltered),
                    "data" => $data,
                );
                echo json_encode($json_data);
            }
        }
    }

    /**
     * Diary entries list (shows same list as index)
     */
    public function entries()
    {
        if (is_teacher_loggedin()) {
            if ($_POST) {
                echo json_encode(array('status' => 'fail', 'error' => array('form_error' => translate('access_denied'))));
                exit();
            }
            access_denied();
        }
        if ($_POST) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'trim');
            $this->form_validation->set_rules('class_id', translate('class'), 'trim');
            $this->form_validation->set_rules('section_id', translate('section'), 'trim');
            if ($this->form_validation->run() == true) {
                $branchID = $this->application_model->get_branch_id();
                $export_title = get_type_name_by_id('branch', $branchID) . ' - ' . (isset($this->lang->language['diary_entries']) ? translate('diary_entries') : 'Diary Entries');
                $array = array('status' => 'success', 'export_title' => $export_title, 'error' => '');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }
        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['title'] = isset($this->lang->language['diary_entries']) ? translate('diary_entries') : 'Diary Entries';
        $this->data['sub_page'] = 'digital_diary/index';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Add diary entry
     */
    public function add_entry()
    {
        if (is_teacher_loggedin()) {
            if ($this->input->method() === 'post' && $this->input->post('entry_title') !== null) {
                echo json_encode(array('status' => 'fail', 'error' => array('form_error' => translate('access_denied'))));
                exit();
            }
            access_denied();
        }
        if ($this->input->method() === 'post' && $this->input->post('entry_title') !== null) {
            $this->diary_validation();
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                if (is_superadmin_loggedin() && empty($post['branch_id'])) {
                    $post['branch_id'] = $this->application_model->get_branch_id();
                }
                try {
                    $this->digital_diary_model->save($post);
                    set_alert('success', translate('information_has_been_saved_successfully'));
                    $url = base_url('digital_diary');
                    $array = array('status' => 'success', 'url' => $url, 'message' => translate('information_has_been_saved_successfully'));
                } catch (Exception $e) {
                    $array = array('status' => 'fail', 'error' => array('form_error' => 'Database error. Please ensure the digital_diary table exists.'));
                }
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }

        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['title'] = translate('add') . ' ' . (isset($this->lang->language['diary_entry']) ? translate('diary_entry') : 'Diary Entry');
        $this->data['sub_page'] = 'digital_diary/add_entry';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Edit diary entry
     */
    public function edit($id = '')
    {
        if (is_teacher_loggedin()) {
            if ($_POST) {
                echo json_encode(array('status' => 'fail', 'error' => array('form_error' => translate('access_denied'))));
                exit();
            }
            access_denied();
        }
        if (empty($id)) {
            redirect(base_url('digital_diary'));
        }

        $diary = $this->app_lib->getTable('digital_diary', array('t.id' => $id), true);
        if (empty($diary)) {
            show_404();
        }

        if ($_POST) {
            $this->diary_validation();
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                $post['diary_id'] = $id;
                $this->digital_diary_model->save($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('digital_diary');
                $array = array('status' => 'success', 'url' => $url, 'message' => translate('information_has_been_updated_successfully'));
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }

        $branchID = $this->input->get('branch_id');
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        if (empty($branchID)) {
            $branchID = !empty($diary['branch_id']) ? $diary['branch_id'] : get_loggedin_branch_id();
        }
        $this->data['diary'] = $diary;
        $this->data['branch_id'] = $branchID;
        $this->data['branch_name'] = !empty($branchID) ? get_type_name_by_id('branch', $branchID) : '';
        $this->data['title'] = translate('edit') . ' ' . (isset($this->lang->language['diary_entry']) ? translate('diary_entry') : 'Diary Entry');
        $this->data['sub_page'] = 'digital_diary/edit';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Delete diary entry
     */
    public function delete($id = '')
    {
        if (is_teacher_loggedin()) {
            return;
        }
        if (!empty($id)) {
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('id', $id);
            $this->db->delete('digital_diary');
        }
    }

    /**
     * Diary report - filter and list diary entries
     */
    public function report()
    {
        if (is_teacher_loggedin()) {
            if ($this->input->method() === 'post') {
                echo json_encode(array('status' => 'fail', 'error' => array('form_error' => translate('access_denied'))));
                exit();
            }
            access_denied();
        }
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'trim');
            $this->form_validation->set_rules('class_id', translate('class'), 'trim');
            $this->form_validation->set_rules('section_id', translate('section'), 'trim');
            if ($this->form_validation->run() == true) {
                $branchID = $this->application_model->get_branch_id();
                $export_title = get_type_name_by_id('branch', $branchID) . ' - ' . (isset($this->lang->language['diary_report']) ? translate('diary_report') : 'Diary Report');
                $array = array('status' => 'success', 'export_title' => $export_title, 'error' => '');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }
        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['title'] = isset($this->lang->language['diary_report']) ? translate('diary_report') : 'Diary Report';
        $this->data['sub_page'] = 'digital_diary/report';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Lesson - expandable flow: Class/Section → Subject → Lessons
     */
    public function lesson()
    {
        $this->digital_diary_forbid_teacher();
        $branchID = $this->input->get_post('branch_id');
        if (empty($branchID) && is_superadmin_loggedin()) {
            $branchID = get_loggedin_branch_id();
        }
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }
        $this->data['branch_id'] = $branchID;
        $this->data['branch_name'] = !empty($branchID) ? get_type_name_by_id('branch', $branchID) : '';
        $this->data['lesson_flow'] = $this->digital_diary_model->getLessonFlowByBranch($branchID);
        $this->data['completed_lesson_ids'] = $this->db->table_exists('digital_diary_lesson_completion')
            ? $this->digital_diary_model->getCompletedLessonIdsByBranch($branchID)
            : array();
        $this->data['title'] = 'Lesson';
        $this->data['sub_page'] = 'digital_diary/lesson';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Lesson - Page 2: When click on class. Table: S.No, Class, Section, Subject Group, Subject, Total Lesson, Action
     * Click on subject -> lesson_subject (Page 3)
     * Branch from GET (passed from lesson page) or session.
     */
    public function lesson_class($class_id = '', $section_id = '')
    {
        $this->digital_diary_forbid_teacher();
        if (empty($class_id) || empty($section_id)) {
            redirect(base_url('digital_diary/lesson'));
        }
        $branchID = $this->input->get('branch_id');
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }
        $this->data['branch_id'] = $branchID;
        $this->data['branch_name'] = !empty($branchID) ? get_type_name_by_id('branch', $branchID) : '';
        $this->data['class_id'] = $class_id;
        $this->data['section_id'] = $section_id;
        $this->data['class_name'] = get_type_name_by_id('class', $class_id);
        $this->data['section_name'] = get_type_name_by_id('section', $section_id);
        $this->data['subject_summary'] = $this->digital_diary_model->getLessonSummaryByClassSection($class_id, $section_id, $branchID);
        $this->data['title'] = 'Lesson - ' . $this->data['class_name'] . ' - ' . $this->data['section_name'];
        $this->data['sub_page'] = 'digital_diary/lesson_class';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Lesson - Page 3: All lessons for a subject. Table: Subject, Lesson No, Lesson Name, Action
     * Branch from GET (passed from lesson_class) or session.
     */
    public function lesson_subject($class_id = '', $section_id = '', $subject_id = '')
    {
        $this->digital_diary_forbid_teacher();
        if (empty($class_id) || empty($section_id) || empty($subject_id)) {
            redirect(base_url('digital_diary/lesson'));
        }
        $branchID = $this->input->get('branch_id');
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }
        $this->data['branch_id'] = $branchID;
        $this->data['branch_name'] = !empty($branchID) ? get_type_name_by_id('branch', $branchID) : '';
        $this->data['class_id'] = $class_id;
        $this->data['section_id'] = $section_id;
        $this->data['subject_id'] = $subject_id;
        $this->data['class_name'] = get_type_name_by_id('class', $class_id);
        $this->data['section_name'] = get_type_name_by_id('section', $section_id);
        $this->data['subject_name'] = get_type_name_by_id('subject', $subject_id);
        $this->data['lessons'] = $this->digital_diary_model->getLessonsBySubject($class_id, $section_id, $subject_id, $branchID);
        $this->data['completed_lesson_ids'] = $this->db->table_exists('digital_diary_lesson_completion')
            ? $this->digital_diary_model->getCompletedLessonIdsForSubject($class_id, $section_id, $subject_id, $branchID)
            : array();
        $this->data['title'] = 'Lesson - ' . $this->data['subject_name'];
        $this->data['sub_page'] = 'digital_diary/lesson_subject';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Add Lesson - form page (like Homework Add)
     */
    public function lesson_add()
    {
        $this->digital_diary_forbid_teacher();
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('class_id', translate('class'), 'trim|required');
            $this->form_validation->set_rules('section_id', translate('section'), 'trim|required');
            $this->form_validation->set_rules('subject_id', translate('subject'), 'trim|required');
            $this->form_validation->set_rules('lesson_no', translate('lesson_no'), 'trim|required');
            $this->form_validation->set_rules('lesson_name', translate('lesson_name'), 'trim|required');
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                $dupErrors = $this->lessonUniqueErrors($post);
                if (!empty($dupErrors)) {
                    $array = array('status' => 'fail', 'error' => $dupErrors);
                } else {
                    $this->digital_diary_model->save_lesson($post);
                    set_alert('success', translate('information_has_been_saved_successfully'));
                    $array = array(
                        'status' => 'success',
                        'url' => $this->lessonReturnUrl($post),
                        'message' => translate('information_has_been_saved_successfully'),
                    );
                }
            } else {
                $array = array('status' => 'fail', 'error' => $this->form_validation->error_array());
            }
            echo json_encode($array);
            exit();
        }
        $branchID = $this->application_model->get_branch_id();
        if (is_superadmin_loggedin() && (int) $this->input->get('branch_id') > 0) {
            $branchID = (int) $this->input->get('branch_id');
        }
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }
        $this->data['branch_id'] = $branchID;
        $this->data['preset_class_id'] = (int) $this->input->get('class_id');
        $this->data['preset_section_id'] = (int) $this->input->get('section_id');
        $this->data['preset_subject_id'] = (int) $this->input->get('subject_id');
        $this->data['existing_lessons'] = array();
        if ($this->data['preset_class_id'] > 0 && $this->data['preset_section_id'] > 0 && $this->data['preset_subject_id'] > 0) {
            $this->data['existing_lessons'] = $this->digital_diary_model->getLessonsBySubject(
                $this->data['preset_class_id'],
                $this->data['preset_section_id'],
                $this->data['preset_subject_id'],
                $branchID
            );
        }
        $this->data['title'] = translate('add_lesson');
        $this->data['sub_page'] = 'digital_diary/lesson_add';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    public function getLessonListDT()
    {
        if (!$this->input->post()) {
            echo json_encode(array('draw' => 0, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []));
            return;
        }
        if (is_teacher_loggedin()) {
            $this->digital_diary_teacher_empty_datatable();
            return;
        }
        // By default show all added lessons; optional filters (class/section/subject) narrow results
        try {
            $results = $this->digital_diary_model->getLessonDT_list();
            $startDT = (int) $this->input->post('start');
            $records = json_decode($results);
            if (!$records || !isset($records->data) || !is_array($records->data)) {
                echo json_encode(array(
                    'draw' => (int) $this->input->post('draw'),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ));
                return;
            }
            $data = array();
            $count = $startDT + 1;
            foreach ($records->data as $record) {
                $rec = is_array($record) ? (object) $record : $record;
                $actions = '<a href="' . base_url('digital_diary/lesson_edit/' . $rec->id) . '" class="btn btn-circle btn-default icon" title="Edit"><i class="fas fa-pen-nib"></i></a>';
                $actions .= btn_delete('digital_diary/lesson_delete/' . $rec->id);
                $row = array(
                    $count++,
                    isset($rec->class_name) ? $rec->class_name : '',
                    isset($rec->section_name) ? $rec->section_name : '',
                    isset($rec->subject_name) ? $rec->subject_name : '',
                    isset($rec->lesson_no) ? $rec->lesson_no : '',
                    isset($rec->lesson_name) ? $rec->lesson_name : '',
                    $actions,
                );
                $data[] = $row;
            }
            echo json_encode(array(
                'draw' => (int) $records->draw,
                'recordsTotal' => (int) $records->recordsTotal,
                'recordsFiltered' => (int) $records->recordsFiltered,
                'data' => $data,
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'draw' => (int) $this->input->post('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ));
        }
    }

    public function lesson_edit($id = '')
    {
        $this->digital_diary_forbid_teacher();
        if (empty($id)) {
            redirect(base_url('digital_diary/lesson'));
        }
        $lesson = $this->app_lib->getTable('digital_diary_lesson', array('t.id' => $id), true);
        if (empty($lesson)) {
            show_404();
        }
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('class_id', translate('class'), 'trim|required');
            $this->form_validation->set_rules('section_id', translate('section'), 'trim|required');
            $this->form_validation->set_rules('subject_id', translate('subject'), 'trim|required');
            $this->form_validation->set_rules('lesson_no', translate('lesson_no'), 'trim|required');
            $this->form_validation->set_rules('lesson_name', translate('lesson_name'), 'trim|required');
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                $post['lesson_id'] = $id;
                $dupErrors = $this->lessonUniqueErrors($post, (int) $id);
                if (!empty($dupErrors)) {
                    $array = array('status' => 'fail', 'error' => $dupErrors);
                } else {
                    $this->digital_diary_model->save_lesson($post);
                    set_alert('success', translate('information_has_been_updated_successfully'));
                    $array = array(
                        'status' => 'success',
                        'url' => $this->lessonReturnUrl($post),
                        'message' => translate('information_has_been_updated_successfully'),
                    );
                }
            } else {
                $array = array('status' => 'fail', 'error' => $this->form_validation->error_array());
            }
            echo json_encode($array);
            exit();
        }
        $branchID = $this->input->get('branch_id');
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        if (empty($branchID)) {
            $branchID = !empty($lesson['branch_id']) ? $lesson['branch_id'] : get_loggedin_branch_id();
        }
        $this->data['lesson'] = $lesson;
        $this->data['branch_id'] = $branchID;
        $this->data['branch_name'] = !empty($branchID) ? get_type_name_by_id('branch', $branchID) : '';
        $this->data['title'] = translate('edit_lesson');
        $this->data['sub_page'] = 'digital_diary/lesson_edit';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Duplicate checks for lesson_no / lesson_name within branch+class+section+subject.
     *
     * @param array $post
     * @param int $excludeId lesson id to skip (when editing)
     * @return array field => error message
     */
    private function lessonUniqueErrors($post, $excludeId = 0)
    {
        $branchId = !empty($post['branch_id']) ? (int) $post['branch_id'] : (int) $this->application_model->get_branch_id();
        if (empty($branchId)) {
            $branchId = (int) get_loggedin_branch_id();
        }
        $classId = isset($post['class_id']) ? (int) $post['class_id'] : 0;
        $sectionId = isset($post['section_id']) ? (int) $post['section_id'] : 0;
        $subjectId = isset($post['subject_id']) ? (int) $post['subject_id'] : 0;
        $errors = array();
        if ($this->digital_diary_model->lessonFieldExists('lesson_no', $post['lesson_no'], $classId, $sectionId, $subjectId, $branchId, $excludeId)) {
            $errors['lesson_no'] = translate('lesson_no') . ' already exists for this subject';
        }
        if ($this->digital_diary_model->lessonFieldExists('lesson_name', $post['lesson_name'], $classId, $sectionId, $subjectId, $branchId, $excludeId)) {
            $errors['lesson_name'] = translate('lesson_name') . ' already exists for this subject';
        }
        return $errors;
    }

    /**
     * Return URL after lesson add/edit: subject lesson list when class/section/subject known.
     *
     * @param array $post
     * @return string
     */
    private function lessonReturnUrl($post = array())
    {
        $classId = isset($post['class_id']) ? (int) $post['class_id'] : 0;
        $sectionId = isset($post['section_id']) ? (int) $post['section_id'] : 0;
        $subjectId = isset($post['subject_id']) ? (int) $post['subject_id'] : 0;
        $branchId = !empty($post['branch_id'])
            ? (int) $post['branch_id']
            : (int) $this->application_model->get_branch_id();
        $branchQuery = $branchId > 0 ? ('?branch_id=' . $branchId) : '';

        if ($classId > 0 && $sectionId > 0 && $subjectId > 0) {
            return base_url('digital_diary/lesson_subject/' . $classId . '/' . $sectionId . '/' . $subjectId) . $branchQuery;
        }
        if ($classId > 0 && $sectionId > 0) {
            return base_url('digital_diary/lesson_class/' . $classId . '/' . $sectionId) . $branchQuery;
        }
        return base_url('digital_diary/lesson') . $branchQuery;
    }

    /**
     * Move lesson up/down in sequence (AJAX). POST: id, direction (up|down)
     */
    public function lesson_move()
    {
        if (is_teacher_loggedin()) {
            echo json_encode(array('status' => 'fail', 'message' => translate('access_denied')));
            exit();
        }
        $id = $this->input->post('id');
        $direction = $this->input->post('direction');
        if (empty($id) || !in_array($direction, array('up', 'down'), true)) {
            echo json_encode(array('status' => 'fail', 'message' => 'Invalid request'));
            exit();
        }
        if (!$this->db->field_exists('sort_order', 'digital_diary_lesson')) {
            echo json_encode(array('status' => 'fail', 'message' => 'Sort order not available. Run the migration: add sort_order column to digital_diary_lesson table.'));
            exit();
        }
        $ok = $this->digital_diary_model->lesson_move($id, $direction);
        echo json_encode($ok ? array('status' => 'success') : array('status' => 'fail', 'message' => 'Could not move'));
        exit();
    }

    public function lesson_delete($id = '')
    {
        $this->digital_diary_forbid_teacher();
        if (!empty($id)) {
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('id', $id);
            $this->db->delete('digital_diary_lesson');
        }
    }

    public function lesson_export_pdf()
    {
        $this->digital_diary_forbid_teacher();
        // Placeholder: generate PDF report of lessons
        redirect(base_url('digital_diary/lesson'));
    }

    public function lesson_export_excel()
    {
        $this->digital_diary_forbid_teacher();
        // Placeholder: generate Excel export of lessons
        redirect(base_url('digital_diary/lesson'));
    }

    /**
     * Note Book Type - list (UA Digital Diary page 4)
     */
    public function notebook_teaching_activity()
    {
        $this->digital_diary_forbid_teacher();
        $branchID = $this->input->get('branch_id');
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }
        $this->data['branch_id'] = $branchID;
        $this->data['branch_name'] = !empty($branchID) ? get_type_name_by_id('branch', $branchID) : '';
        $this->data['title'] = 'Note Book Type';
        $this->data['sub_page'] = 'digital_diary/notebook_teaching_activity';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * DataTables server-side for Note Book Type list
     */
    public function getNotebookTypeDT()
    {
        if (!$this->input->post()) {
            echo json_encode(array('draw' => 0, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []));
            return;
        }
        if (is_teacher_loggedin()) {
            $this->digital_diary_teacher_empty_datatable();
            return;
        }
        if (!$this->db->table_exists('digital_diary_notebook_type')) {
            echo json_encode(array(
                'draw' => (int) $this->input->post('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ));
            return;
        }
        try {
            $results = $this->digital_diary_model->getNotebookTypeDT_list();
            $startDT = (int) $this->input->post('start');
            $records = json_decode($results);
            if (!$records || !isset($records->data) || !is_array($records->data)) {
                echo json_encode(array(
                    'draw' => (int) $this->input->post('draw'),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ));
                return;
            }
            $data = array();
            $count = $startDT + 1;
            foreach ($records->data as $record) {
                $rec = is_array($record) ? (object) $record : $record;
                $actions = '<a href="' . base_url('digital_diary/notebook_type_edit/' . $rec->id) . '" class="btn btn-circle btn-default icon" title="Edit"><i class="fas fa-pen-nib"></i></a>';
                $actions .= btn_delete('digital_diary/notebook_type_delete/' . $rec->id);
                $status = !empty($rec->is_active) ? translate('active') : (isset($this->lang->language['inactive']) ? translate('inactive') : 'Inactive');
                $row = array(
                    $count++,
                    isset($rec->name) ? $rec->name : '',
                    isset($rec->code) ? $rec->code : '—',
                    isset($rec->description) ? $rec->description : '—',
                    $status,
                    $actions,
                );
                $data[] = $row;
            }
            echo json_encode(array(
                'draw' => (int) $records->draw,
                'recordsTotal' => (int) $records->recordsTotal,
                'recordsFiltered' => (int) $records->recordsFiltered,
                'data' => $data,
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'draw' => (int) $this->input->post('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ));
        }
    }

    /**
     * Add Note Book Type
     */
    public function notebook_type_add()
    {
        if (is_teacher_loggedin()) {
            if ($this->input->method() === 'post') {
                echo json_encode(array('status' => 'fail', 'error' => array('form_error' => translate('access_denied'))));
                exit();
            }
            access_denied();
        }
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('name', 'Name', 'trim|required');
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                if (!$this->db->table_exists('digital_diary_notebook_type')) {
                    $array = array('status' => 'fail', 'error' => array('form_error' => 'Table digital_diary_notebook_type not found. Please run application/sql/digital_diary_notebook_type.sql'));
                } else {
                    $this->digital_diary_model->save_notebook_type($post);
                    set_alert('success', translate('information_has_been_saved_successfully'));
                    $array = array('status' => 'success', 'url' => base_url('digital_diary/notebook_teaching_activity'), 'message' => translate('information_has_been_saved_successfully'));
                }
            } else {
                $array = array('status' => 'fail', 'error' => $this->form_validation->error_array());
            }
            echo json_encode($array);
            exit();
        }
        $branchID = $this->input->get('branch_id');
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = 'Add Note Book Type';
        $this->data['sub_page'] = 'digital_diary/notebook_type_add';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Edit Note Book Type
     */
    public function notebook_type_edit($id = '')
    {
        if (is_teacher_loggedin()) {
            if ($this->input->method() === 'post') {
                echo json_encode(array('status' => 'fail', 'error' => array('form_error' => translate('access_denied'))));
                exit();
            }
            access_denied();
        }
        if (empty($id)) {
            redirect(base_url('digital_diary/notebook_teaching_activity'));
        }
        if (!$this->db->table_exists('digital_diary_notebook_type')) {
            redirect(base_url('digital_diary/notebook_teaching_activity'));
        }
        $row = $this->app_lib->getTable('digital_diary_notebook_type', array('t.id' => $id), true);
        if (empty($row)) {
            show_404();
        }
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('name', 'Name', 'trim|required');
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                $post['notebook_type_id'] = $id;
                $this->digital_diary_model->save_notebook_type($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $array = array('status' => 'success', 'url' => base_url('digital_diary/notebook_teaching_activity'), 'message' => translate('information_has_been_updated_successfully'));
            } else {
                $array = array('status' => 'fail', 'error' => $this->form_validation->error_array());
            }
            echo json_encode($array);
            exit();
        }
        $this->data['notebook_type'] = $row;
        $this->data['branch_id'] = !empty($row['branch_id']) ? $row['branch_id'] : $this->application_model->get_branch_id();
        $this->data['title'] = 'Edit Note Book Type';
        $this->data['sub_page'] = 'digital_diary/notebook_type_edit';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Delete Note Book Type
     */
    public function notebook_type_delete($id = '')
    {
        if (is_teacher_loggedin()) {
            access_denied();
        }
        if (!empty($id)) {
            if (!$this->db->table_exists('digital_diary_notebook_type')) {
                redirect(base_url('digital_diary/notebook_teaching_activity'));
            }
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('id', $id);
            $this->db->delete('digital_diary_notebook_type');
        }
        redirect(base_url('digital_diary/notebook_teaching_activity'));
    }

    /**
     * Teaching Activity list: define types (admin).
     */
    public function teaching_activity_types()
    {
        if (is_teacher_loggedin()) {
            access_denied();
        }
        $branchID = $this->input->get('branch_id');
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }
        $this->data['branch_id'] = $branchID;
        $this->data['branch_name'] = !empty($branchID) ? get_type_name_by_id('branch', $branchID) : '';
        $this->data['notebook_types'] = $this->digital_diary_model->getNotebookTypesByBranch($branchID);
        $this->digital_diary_model->ensureTeachingActivityRoleColumns();
        $this->data['title'] = 'Teaching Activity';
        $this->data['sub_page'] = 'digital_diary/teaching_activity_types';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    protected function digital_diary_teacher_may_use_class_section($branchID, $classID, $sectionID)
    {
        if (is_superadmin_loggedin() || (int) loggedin_role_id() !== 3) {
            return true;
        }
        if (empty($classID) || empty($sectionID)) {
            return false;
        }
        $assigned = $this->app_lib->get_ownClassSection();
        if ($assigned === false) {
            return true;
        }
        if (empty($assigned)) {
            return false;
        }
        $classID = (int) $classID;
        $sectionID = (int) $sectionID;
        foreach ($assigned as $ckey => $secs) {
            if ((int) $ckey !== $classID) {
                continue;
            }
            foreach ($secs as $sid) {
                if ((int) $sid === $sectionID) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Final Submit to Principal is for class teacher (or admin), not individual subject teachers.
     */
    protected function digital_diary_teacher_may_final_submit($classID, $sectionID)
    {
        if (!is_teacher_loggedin()) {
            return true;
        }
        $this->load->model('subject_model');
        return (bool) $this->subject_model->getClassTeacherByClassSection((int) $classID, (int) $sectionID);
    }

    /**
     * Who can assign coordinator/moderator teachers.
     */
    protected function digital_diary_can_assign_roles()
    {
        // Teachers must not assign coordinators — admin / superadmin only (or staff with class-teacher assign, excluding teachers).
        if (is_teacher_loggedin()) {
            return false;
        }
        return is_superadmin_loggedin()
            || is_admin_loggedin()
            || get_permission('assign_class_teacher', 'is_view');
    }

    /**
     * Admin or assigned diary coordinator/moderator teachers.
     */
    protected function digital_diary_user_may_coordinate()
    {
        if (is_superadmin_loggedin() || is_admin_loggedin()) {
            return true;
        }
        if (!is_teacher_loggedin()) {
            return false;
        }
        $this->digital_diary_model->ensureRoleAllocationTable();
        $staffID = (int) get_loggedin_user_id();
        $branchID = (int) get_loggedin_branch_id();
        return $this->digital_diary_model->staffHasDiaryRole($staffID, 'coordinator', $branchID);
    }

    /**
     * Class/section scopes for coordinator inbox filtering (null = all for admin).
     *
     * @return array<int,array{class_id:int,section_id:int}>|null
     */
    protected function digital_diary_user_coordinate_scopes($branchID = 0)
    {
        if (is_superadmin_loggedin() || is_admin_loggedin()) {
            return null;
        }
        $staffID = (int) get_loggedin_user_id();
        $scopes = $this->digital_diary_model->getStaffDiaryRoleScopes($staffID, 'coordinator', (int) $branchID);
        $out = array();
        $seen = array();
        foreach ($scopes as $sc) {
            $key = (int) $sc['class_id'] . '-' . (int) $sc['section_id'];
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = array('class_id' => (int) $sc['class_id'], 'section_id' => (int) $sc['section_id']);
        }
        return $out;
    }

    /**
     * Full coordinator/moderator scopes including teaching_activity_id (null = admin, all).
     *
     * @return array<int,array{class_id:int,section_id:int,role_type:string,teaching_activity_id:int}>|null
     */
    protected function digital_diary_user_coordinate_full_scopes($branchID = 0)
    {
        if (is_superadmin_loggedin() || is_admin_loggedin()) {
            return null;
        }
        return $this->digital_diary_model->getStaffDiaryRoleScopes((int) get_loggedin_user_id(), 'coordinator', (int) $branchID);
    }

    protected function digital_diary_user_may_coordinate_class_section($classID, $sectionID)
    {
        if (is_superadmin_loggedin() || is_admin_loggedin()) {
            return true;
        }
        $scopes = $this->digital_diary_user_coordinate_scopes((int) get_loggedin_branch_id());
        if (!is_array($scopes)) {
            return true;
        }
        foreach ($scopes as $sc) {
            if ((int) $sc['class_id'] === (int) $classID && (int) $sc['section_id'] === (int) $sectionID) {
                return true;
            }
        }
        return false;
    }

    /**
     * Class list visible to the current user (teachers: assigned classes only when restricted).
     *
     * @return array<int,string>
     */
    protected function digital_diary_teacher_allowed_class_map($branchID)
    {
        $classes = $this->app_lib->getClass($branchID);
        unset($classes['']);
        if (!is_teacher_loggedin()) {
            return $classes;
        }
        $assigned = $this->app_lib->get_ownClassSection();
        if ($assigned === false) {
            return $classes;
        }
        $out = array();
        if (is_array($assigned)) {
            foreach ($assigned as $cid => $secs) {
                $cid = (int) $cid;
                if ($cid > 0) {
                    $out[$cid] = get_type_name_by_id('class', $cid);
                }
            }
        }
        return $out;
    }

    protected function digital_diary_populate_class_section_filters($branchID, $classID = 0)
    {
        if (is_teacher_loggedin()) {
            $assigned = $this->app_lib->get_ownClassSection();
            $classOptions = array('' => translate('select'));
            if (is_array($assigned) && !empty($assigned)) {
                foreach ($assigned as $classKey => $sections) {
                    $classOptions[$classKey] = get_type_name_by_id('class', $classKey);
                }
            }
            if (count($classOptions) <= 1 && !empty($branchID)) {
                $classOptions = $this->app_lib->getClass($branchID);
            }
            $this->data['class_options'] = $classOptions;
            $restricted = is_array($assigned) && !empty($assigned);
            $section_options = array('' => translate('select'));
            if ($restricted && !empty($classID) && isset($assigned[$classID])) {
                foreach ($assigned[$classID] as $sid) {
                    $section_options[$sid] = get_type_name_by_id('section', $sid);
                }
            } elseif (!empty($classID)) {
                $section_options = $this->app_lib->getSections($classID, false);
            } else {
                $section_options = array('' => translate('select_class_first'));
            }
            $this->data['section_options'] = $section_options;
        } else {
            $this->data['class_options'] = $this->app_lib->getClass($branchID);
            $this->data['section_options'] = !empty($classID) ? $this->app_lib->getSections($classID, false) : array('' => translate('select_class_first'));
        }
    }

    /**
     * Teacher landing boxes for a date: timetable periods first (time_start order, like timetable/viewclass),
     * then any assigned class–sections that have no period that day.
     *
     * @return array<int,array{class_id:int,section_id:int,class_name:string,section_name:string,is_class_teacher:bool,subject_id:int,subject_name:string,time_start:string,time_end:string,time_label:string,is_period:bool}>
     */
    protected function digital_diary_teacher_assigned_class_boxes($branchID = null, $reportDate = null)
    {
        if (!is_teacher_loggedin()) {
            return array();
        }
        $teacherId = (int) get_loggedin_user_id();
        $sessionId = (int) get_session_id();
        if (empty($reportDate)) {
            $reportDate = date('Y-m-d');
        } else {
            $reportDate = date('Y-m-d', strtotime($reportDate));
        }
        $day = strtolower(date('l', strtotime($reportDate)));

        $boxes = array();
        $seenClassSection = array();

        // Period-wise for selected weekday (same source/order as timetable/viewclass).
        $this->db->select('t.class_id, t.section_id, t.subject_id, t.time_start, t.time_end, t.break, t.class_room, s.name as subject_name, c.name as class_name, sc.name as section_name');
        $this->db->from('timetable_class as t');
        $this->db->join('subject as s', 's.id = t.subject_id', 'left');
        $this->db->join('class as c', 'c.id = t.class_id', 'left');
        $this->db->join('section as sc', 'sc.id = t.section_id', 'left');
        $this->db->where('t.teacher_id', $teacherId);
        $this->db->where('t.session_id', $sessionId);
        $this->db->where('t.day', $day);
        if (!empty($branchID)) {
            $this->db->where('t.branch_id', (int) $branchID);
        }
        $this->db->order_by('t.time_start', 'asc');
        foreach ($this->db->get()->result() as $row) {
            if ($this->digital_diary_is_timetable_break(isset($row->break) ? $row->break : 0)) {
                continue;
            }
            $cid = (int) $row->class_id;
            $sid = (int) $row->section_id;
            if ($cid <= 0 || $sid <= 0) {
                continue;
            }
            $seenClassSection[$cid . '-' . $sid] = true;
            $tStart = !empty($row->time_start) ? date('g:i A', strtotime($row->time_start)) : '';
            $tEnd = !empty($row->time_end) ? date('g:i A', strtotime($row->time_end)) : '';
            $timeLabel = ($tStart !== '' && $tEnd !== '') ? ($tStart . ' - ' . $tEnd) : $tStart;
            static $dayPeriodMaps = array();
            $mapKey = $day . '|' . $cid . '|' . $sid . '|' . (int) $branchID;
            if (!isset($dayPeriodMaps[$mapKey])) {
                $dayPeriodMaps[$mapKey] = $this->digital_diary_class_period_numbers($cid, $sid, $day, $branchID);
            }
            $slotKey = (string) $row->time_start . '|' . (int) $row->subject_id;
            $periodNo = isset($dayPeriodMaps[$mapKey][$slotKey]) ? (int) $dayPeriodMaps[$mapKey][$slotKey] : (count($boxes) + 1);
            $boxes[] = array(
                'class_id' => $cid,
                'section_id' => $sid,
                'class_name' => (string) ($row->class_name !== null && $row->class_name !== '' ? $row->class_name : get_type_name_by_id('class', $cid)),
                'section_name' => (string) ($row->section_name !== null && $row->section_name !== '' ? $row->section_name : get_type_name_by_id('section', $sid)),
                'is_class_teacher' => (bool) $this->subject_model->getClassTeacherByClassSection($cid, $sid),
                'subject_id' => (int) $row->subject_id,
                'subject_name' => (string) ($row->subject_name !== null ? $row->subject_name : get_type_name_by_id('subject', (int) $row->subject_id)),
                'time_start' => (string) $row->time_start,
                'time_end' => (string) $row->time_end,
                'time_label' => $timeLabel,
                'period_no' => $periodNo,
                'class_room' => (string) (isset($row->class_room) ? $row->class_room : ''),
                'is_period' => true,
            );
        }

        // Fallback / extras: assigned class–sections with no period that day.
        $pairs = array();
        $addPair = function ($classId, $sectionId) use (&$pairs) {
            $classId = (int) $classId;
            $sectionId = (int) $sectionId;
            if ($classId <= 0 || $sectionId <= 0) {
                return;
            }
            $pairs[$classId . '-' . $sectionId] = array($classId, $sectionId);
        };

        $assigned = $this->app_lib->get_ownClassSection();
        if (is_array($assigned)) {
            foreach ($assigned as $cid => $secs) {
                foreach ((array) $secs as $sid) {
                    $addPair($cid, $sid);
                }
            }
        }

        $this->db->select('class_id, section_id');
        $this->db->from('subject_assign');
        $this->db->where('teacher_id', $teacherId);
        $this->db->where('session_id', $sessionId);
        if (!empty($branchID)) {
            $this->db->where('branch_id', (int) $branchID);
        }
        $this->db->group_by(array('class_id', 'section_id'));
        foreach ($this->db->get()->result() as $row) {
            $addPair($row->class_id, $row->section_id);
        }

        $this->db->select('class_id, section_id');
        $this->db->from('timetable_class');
        $this->db->where('teacher_id', $teacherId);
        $this->db->where('session_id', $sessionId);
        $this->db->group_by(array('class_id', 'section_id'));
        foreach ($this->db->get()->result() as $row) {
            $addPair($row->class_id, $row->section_id);
        }

        $this->db->select('class_id, section_id');
        $this->db->from('teacher_allocation');
        $this->db->where('teacher_id', $teacherId);
        $this->db->where('session_id', $sessionId);
        foreach ($this->db->get()->result() as $row) {
            $addPair($row->class_id, $row->section_id);
        }

        $extras = array();
        foreach ($pairs as $key => $pair) {
            if (!empty($seenClassSection[$key])) {
                continue;
            }
            $cid = (int) $pair[0];
            $sid = (int) $pair[1];
            $extras[] = array(
                'class_id' => $cid,
                'section_id' => $sid,
                'class_name' => (string) get_type_name_by_id('class', $cid),
                'section_name' => (string) get_type_name_by_id('section', $sid),
                'is_class_teacher' => (bool) $this->subject_model->getClassTeacherByClassSection($cid, $sid),
                'subject_id' => 0,
                'subject_name' => '',
                'time_start' => '',
                'time_end' => '',
                'time_label' => '',
                'period_no' => 0,
                'class_room' => '',
                'is_period' => false,
            );
        }
        usort($extras, function ($a, $b) {
            $c = strcasecmp($a['class_name'], $b['class_name']);
            if ($c !== 0) {
                return $c;
            }
            return strcasecmp($a['section_name'], $b['section_name']);
        });

        return array_merge($boxes, $extras);
    }

    /**
     * timetable_class.break is varchar/bool-ish ('false', 0, 'true', 1).
     */
    protected function digital_diary_is_timetable_break($break)
    {
        if ($break === true || $break === 1 || $break === '1') {
            return true;
        }
        $b = strtolower(trim((string) $break));
        return ($b === 'true' || $b === 'yes');
    }

    /**
     * Map time_start|subject_id => period number (1-based) for a class/section/day.
     *
     * @return array<string,int>
     */
    protected function digital_diary_class_period_numbers($classID, $sectionID, $day, $branchID = null)
    {
        $map = array();
        $this->db->select('time_start, subject_id, break');
        $this->db->from('timetable_class');
        $this->db->where('class_id', (int) $classID);
        $this->db->where('section_id', (int) $sectionID);
        $this->db->where('session_id', (int) get_session_id());
        $this->db->where('day', $day);
        if (!empty($branchID)) {
            $this->db->where('branch_id', (int) $branchID);
        }
        $this->db->order_by('time_start', 'asc');
        $n = 0;
        foreach ($this->db->get()->result() as $row) {
            if ($this->digital_diary_is_timetable_break(isset($row->break) ? $row->break : 0)) {
                continue;
            }
            $n++;
            $map[(string) $row->time_start . '|' . (int) $row->subject_id] = $n;
        }
        return $map;
    }

    /**
     * Full class/section day slots (all teachers), padded to 8 periods.
     * Marks which slots belong to the logged-in teacher.
     *
     * @return array<int,array>
     */
    protected function digital_diary_class_day_period_slots($classID, $sectionID, $day, $branchID, $workDate, $teacherId, $slotCount = 8)
    {
        $classID = (int) $classID;
        $sectionID = (int) $sectionID;
        $teacherId = (int) $teacherId;
        $slotCount = max(1, (int) $slotCount);
        $isClassTeacher = (bool) $this->subject_model->getClassTeacherByClassSection($classID, $sectionID);
        $className = (string) get_type_name_by_id('class', $classID);
        $sectionName = (string) get_type_name_by_id('section', $sectionID);

        $this->db->select('t.subject_id, t.teacher_id, t.time_start, t.time_end, t.break, t.class_room, s.name as subject_name, st.name as teacher_name');
        $this->db->from('timetable_class as t');
        $this->db->join('subject as s', 's.id = t.subject_id', 'left');
        $this->db->join('staff as st', 'st.id = t.teacher_id', 'left');
        $this->db->where('t.class_id', $classID);
        $this->db->where('t.section_id', $sectionID);
        $this->db->where('t.session_id', (int) get_session_id());
        $this->db->where('t.day', $day);
        if (!empty($branchID)) {
            $this->db->where('t.branch_id', (int) $branchID);
        }
        $this->db->order_by('t.time_start', 'asc');

        $filled = array();
        $periodNo = 0;
        foreach ($this->db->get()->result() as $row) {
            if ($this->digital_diary_is_timetable_break(isset($row->break) ? $row->break : 0)) {
                continue;
            }
            $periodNo++;
            if ($periodNo > $slotCount) {
                break;
            }
            // Only the logged-in teacher's assigned courses (not other teachers').
            if ((int) $row->teacher_id !== $teacherId) {
                continue;
            }
            $tStart = !empty($row->time_start) ? date('g:i A', strtotime($row->time_start)) : '';
            $tEnd = !empty($row->time_end) ? date('g:i A', strtotime($row->time_end)) : '';
            $filled[$periodNo] = array(
                'class_id' => $classID,
                'section_id' => $sectionID,
                'class_name' => $className,
                'section_name' => $sectionName,
                'is_class_teacher' => $isClassTeacher,
                'subject_id' => (int) $row->subject_id,
                'subject_name' => (string) ($row->subject_name !== null ? $row->subject_name : get_type_name_by_id('subject', (int) $row->subject_id)),
                'teacher_id' => (int) $row->teacher_id,
                'teacher_name' => (string) ($row->teacher_name !== null ? $row->teacher_name : ''),
                'time_start' => (string) $row->time_start,
                'time_end' => (string) $row->time_end,
                'time_label' => ($tStart !== '' && $tEnd !== '') ? ($tStart . ' - ' . $tEnd) : $tStart,
                'period_no' => $periodNo,
                'class_room' => (string) (isset($row->class_room) ? $row->class_room : ''),
                'is_period' => true,
                'is_assigned' => true,
                'is_empty' => false,
                'can_open' => true,
                'day' => $day,
                'work_date' => $workDate,
            );
        }

        $slots = array();
        for ($n = 1; $n <= $slotCount; $n++) {
            if (isset($filled[$n])) {
                $slots[] = $filled[$n];
                continue;
            }
            // Empty slot (no course of theirs in this period) — not another teacher's subject.
            $slots[] = array(
                'class_id' => $classID,
                'section_id' => $sectionID,
                'class_name' => $className,
                'section_name' => $sectionName,
                'is_class_teacher' => $isClassTeacher,
                'subject_id' => 0,
                'subject_name' => '',
                'teacher_id' => 0,
                'teacher_name' => '',
                'time_start' => '',
                'time_end' => '',
                'time_label' => '',
                'period_no' => $n,
                'class_room' => '',
                'is_period' => true,
                'is_assigned' => false,
                'is_empty' => true,
                'can_open' => false,
                'day' => $day,
                'work_date' => $workDate,
            );
        }
        return $slots;
    }

    /**
     * Teacher schedule for SWR landing — selected/current date only.
     * Each related class/section shows 8 period slots (assigned filled, others empty).
     *
     * @return array{boxes:array,days:array}
     */
    protected function digital_diary_teacher_week_period_boxes($branchID = null, $reportDate = null)
    {
        $empty = array('boxes' => array(), 'days' => array());
        if (!is_teacher_loggedin()) {
            return $empty;
        }
        if (empty($reportDate)) {
            $reportDate = date('Y-m-d');
        } else {
            $reportDate = date('Y-m-d', strtotime($reportDate));
        }

        $teacherId = (int) get_loggedin_user_id();
        $sessionId = (int) get_session_id();
        $slotCount = 8;

        // Class–sections this teacher is linked to.
        $classSections = array();
        $addCs = function ($cid, $sid) use (&$classSections) {
            $cid = (int) $cid;
            $sid = (int) $sid;
            if ($cid > 0 && $sid > 0) {
                $classSections[$cid . '-' . $sid] = array($cid, $sid);
            }
        };
        $assigned = $this->app_lib->get_ownClassSection();
        if (is_array($assigned)) {
            foreach ($assigned as $cid => $secs) {
                foreach ((array) $secs as $sid) {
                    $addCs($cid, $sid);
                }
            }
        }
        foreach (array('subject_assign', 'timetable_class', 'teacher_allocation') as $tbl) {
            $this->db->select('class_id, section_id');
            $this->db->from($tbl);
            $this->db->where('teacher_id', $teacherId);
            $this->db->where('session_id', $sessionId);
            if ($tbl === 'subject_assign' && !empty($branchID)) {
                $this->db->where('branch_id', (int) $branchID);
            }
            $this->db->group_by(array('class_id', 'section_id'));
            foreach ($this->db->get()->result() as $row) {
                $addCs($row->class_id, $row->section_id);
            }
        }

        // Days the teacher personally teaches (class-section keys).
        $teacherDayCs = array();
        $this->db->select('day, class_id, section_id, break');
        $this->db->from('timetable_class');
        $this->db->where('teacher_id', $teacherId);
        $this->db->where('session_id', $sessionId);
        if (!empty($branchID)) {
            $this->db->where('branch_id', (int) $branchID);
        }
        foreach ($this->db->get()->result() as $row) {
            if ($this->digital_diary_is_timetable_break(isset($row->break) ? $row->break : 0)) {
                continue;
            }
            $dayKey = strtolower(trim((string) $row->day));
            $csKey = (int) $row->class_id . '-' . (int) $row->section_id;
            if (!isset($teacherDayCs[$dayKey])) {
                $teacherDayCs[$dayKey] = array();
            }
            $teacherDayCs[$dayKey][$csKey] = true;
            $addCs($row->class_id, $row->section_id);
        }

        $allBoxes = array();
        $orderedDays = array();

        // Selected / current date only (not the full week).
        $workDate = $reportDate;
        $dayKey = strtolower(date('l', strtotime($workDate)));
        $isOpenDay = $this->digital_diary_is_school_open_day($workDate, $branchID);
        $isBranchHoliday = in_array($workDate, $this->digital_diary_get_holiday_dates($branchID), true);
        $dowNum = (string) date('w', strtotime($workDate)); // 0=Sun … 6=Sat

        if (!$isOpenDay) {
            if ($isBranchHoliday) {
                $holidayNote = 'School holiday. No class on this day.';
            } elseif ($dowNum === '0') {
                $holidayNote = 'Sunday is a holiday. No class.';
            } else {
                $holidayNote = ucfirst($dayKey) . ' is a weekly off. No class on this day.';
            }
            $orderedDays[] = array(
                'day' => $dayKey,
                'day_label' => strtoupper($dayKey),
                'date' => $workDate,
                'periods' => array(),
                'class_rows' => array(),
                'is_holiday' => true,
                'holiday_label' => 'Holiday — No class',
                'holiday_note' => $holidayNote,
            );
            return array('boxes' => $allBoxes, 'days' => $orderedDays);
        }

        $classRows = array();
        $flatPeriods = array();
        $csForDay = array();
        if (!empty($teacherDayCs[$dayKey])) {
            foreach ($teacherDayCs[$dayKey] as $csKey => $true) {
                if (isset($classSections[$csKey])) {
                    $csForDay[$csKey] = $classSections[$csKey];
                }
            }
        }
        foreach ($classSections as $csKey => $pair) {
            if (isset($csForDay[$csKey])) {
                continue;
            }
            if ($this->subject_model->getClassTeacherByClassSection($pair[0], $pair[1])) {
                $csForDay[$csKey] = $pair;
            }
        }
        // Nothing on this weekday — still show linked classes with all empty period boxes.
        if (empty($csForDay) && !empty($classSections)) {
            $csForDay = $classSections;
        }

        foreach ($csForDay as $csKey => $pair) {
            $slots = $this->digital_diary_class_day_period_slots(
                $pair[0],
                $pair[1],
                $dayKey,
                $branchID,
                $workDate,
                $teacherId,
                $slotCount
            );
            // Always keep the row (all 8 boxes), even when every slot is empty / unassigned.
            $classRows[] = array(
                'class_id' => (int) $pair[0],
                'section_id' => (int) $pair[1],
                'class_name' => (string) get_type_name_by_id('class', (int) $pair[0]),
                'section_name' => (string) get_type_name_by_id('section', (int) $pair[1]),
                'periods' => $slots,
            );
            foreach ($slots as $sl) {
                $flatPeriods[] = $sl;
                $allBoxes[] = $sl;
            }
        }

        $orderedDays[] = array(
            'day' => $dayKey,
            'day_label' => strtoupper($dayKey),
            'date' => $workDate,
            'periods' => $flatPeriods,
            'class_rows' => $classRows,
            'is_holiday' => false,
            'is_empty_day' => empty($classRows),
            'holiday_label' => '',
            'holiday_note' => empty($classRows) ? 'No assigned periods for you on this date.' : '',
        );

        return array('boxes' => $allBoxes, 'days' => $orderedDays);
    }

    /**
     * Reorder subject grid by timetable periods for the date's weekday; attach time_label.
     * Subjects with no slot that day stay at the end in original order.
     *
     * @param array<int,array{subject_id:int,subject_name:string}> $subjects
     * @return array<int,array{subject_id:int,subject_name:string,time_label:string,time_start:string}>
     */
    protected function digital_diary_order_subjects_by_period($subjects, $classID, $sectionID, $reportDate, $branchID = null)
    {
        if (empty($subjects) || empty($classID) || empty($sectionID)) {
            return $subjects;
        }
        $day = strtolower(date('l', strtotime($reportDate)));
        $byId = array();
        foreach ($subjects as $sg) {
            $sid = (int) $sg['subject_id'];
            if ($sid <= 0) {
                continue;
            }
            if (!isset($byId[$sid])) {
                $sg['time_label'] = isset($sg['time_label']) ? $sg['time_label'] : '';
                $sg['time_start'] = isset($sg['time_start']) ? $sg['time_start'] : '';
                $byId[$sid] = $sg;
            }
        }
        if (empty($byId)) {
            return $subjects;
        }

        $this->db->select('subject_id, time_start, time_end, break');
        $this->db->from('timetable_class');
        $this->db->where('class_id', (int) $classID);
        $this->db->where('section_id', (int) $sectionID);
        $this->db->where('session_id', (int) get_session_id());
        $this->db->where('day', $day);
        if (!empty($branchID)) {
            $this->db->where('branch_id', (int) $branchID);
        }
        $this->db->order_by('time_start', 'asc');
        $ordered = array();
        $seen = array();
        foreach ($this->db->get()->result() as $row) {
            if ($this->digital_diary_is_timetable_break(isset($row->break) ? $row->break : 0)) {
                continue;
            }
            $sid = (int) $row->subject_id;
            if ($sid <= 0 || !isset($byId[$sid]) || isset($seen[$sid])) {
                continue;
            }
            $seen[$sid] = true;
            $item = $byId[$sid];
            $tStart = !empty($row->time_start) ? date('g:i A', strtotime($row->time_start)) : '';
            $tEnd = !empty($row->time_end) ? date('g:i A', strtotime($row->time_end)) : '';
            $item['time_start'] = (string) $row->time_start;
            $item['time_label'] = ($tStart !== '' && $tEnd !== '') ? ($tStart . ' - ' . $tEnd) : $tStart;
            $item['period_no'] = count($ordered) + 1;
            $ordered[] = $item;
        }
        foreach ($byId as $sid => $item) {
            if (!isset($seen[$sid])) {
                if (!isset($item['period_no'])) {
                    $item['period_no'] = 0;
                }
                $ordered[] = $item;
            }
        }
        return $ordered;
    }

    protected function digital_diary_teacher_may_view_subject($classID, $sectionID, $subjectID)
    {
        if (!is_teacher_loggedin()) {
            return true;
        }
        if ($this->subject_model->getClassTeacherByClassSection($classID, $sectionID)) {
            return true;
        }
        $tq = $this->subject_model->getSubjectByClassSection($classID, $sectionID);
        foreach ($tq->result() as $trow) {
            if ((int) $trow->subject_id === (int) $subjectID) {
                return true;
            }
        }
        $this->db->where(array(
            'class_id' => (int) $classID,
            'section_id' => (int) $sectionID,
            'subject_id' => (int) $subjectID,
            'teacher_id' => (int) get_loggedin_user_id(),
            'session_id' => (int) get_session_id(),
        ));
        return $this->db->get('timetable_class')->num_rows() > 0;
    }

    /**
     * Subject IDs a teacher may see for non-performer / branch-wide reports.
     *
     * @return array<int,array{id:int,name:string}>
     */
    protected function digital_diary_teacher_report_subjects($branchID)
    {
        $this->db->select('id, name');
        $this->db->from('subject');
        if (!empty($branchID)) {
            $this->db->where('branch_id', (int) $branchID);
        }
        $this->db->order_by('name');
        $all = array();
        foreach ($this->db->get()->result_array() as $s) {
            $all[] = array('id' => (int) $s['id'], 'name' => $s['name']);
        }
        if (!is_teacher_loggedin()) {
            return $all;
        }
        $allowedIds = array();
        $classes = $this->digital_diary_teacher_allowed_class_map($branchID);
        $assigned = $this->app_lib->get_ownClassSection();
        foreach ($classes as $cid => $cname) {
            $sectionIds = array();
            if ($assigned === false) {
                $sections = $this->app_lib->getSections($cid, false);
                unset($sections['']);
                $sectionIds = array_keys($sections);
            } elseif (isset($assigned[$cid])) {
                $sectionIds = $assigned[$cid];
            }
            foreach ($sectionIds as $sid) {
                if ($this->subject_model->getClassTeacherByClassSection($cid, $sid)) {
                    $allSubQ = $this->subject_model->getAllSubjectsListForClassSection($cid, $sid, $branchID);
                    foreach ($allSubQ->result() as $srow) {
                        if (!empty($srow->subject_id)) {
                            $allowedIds[(int) $srow->subject_id] = true;
                        }
                    }
                } else {
                    $tq = $this->subject_model->getSubjectByClassSection($cid, $sid);
                    foreach ($tq->result() as $trow) {
                        if (!empty($trow->subject_id)) {
                            $allowedIds[(int) $trow->subject_id] = true;
                        }
                    }
                }
            }
        }
        $out = array();
        foreach ($all as $s) {
            if (isset($allowedIds[$s['id']])) {
                $out[] = $s;
            }
        }
        return $out;
    }

    protected function digital_diary_teacher_may_access_np_cell($classID, $subjectID)
    {
        if (!is_teacher_loggedin()) {
            return true;
        }
        $classes = $this->digital_diary_teacher_allowed_class_map($this->application_model->get_branch_id());
        if (!isset($classes[(int) $classID])) {
            return false;
        }
        $assigned = $this->app_lib->get_ownClassSection();
        $sectionIds = array();
        if ($assigned === false) {
            $sections = $this->app_lib->getSections($classID, false);
            unset($sections['']);
            $sectionIds = array_keys($sections);
        } elseif (isset($assigned[(int) $classID])) {
            $sectionIds = $assigned[(int) $classID];
        }
        foreach ($sectionIds as $sid) {
            if ($this->digital_diary_teacher_may_view_subject($classID, $sid, $subjectID)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Branch holiday dates (Y-m-d) for current session.
     *
     * @return array<int,string>
     */
    protected function digital_diary_get_holiday_dates($branchID)
    {
        static $cache = array();
        $key = (int) $branchID;
        if (!isset($cache[$key])) {
            $dates = array();
            if ($this->db->table_exists('event')) {
                $this->db->select('start_date, end_date');
                $this->db->from('event');
                $this->db->where('branch_id', $key);
                $this->db->where('session_id', (int) get_session_id());
                $this->db->where('status', 1);
                $this->db->where('type', 'holiday');
                foreach ($this->db->get()->result_array() as $row) {
                    $from = strtotime($row['start_date']);
                    $to = strtotime($row['end_date']);
                    if ($from === false || $to === false) {
                        continue;
                    }
                    for ($t = $from; $t <= $to; $t += 86400) {
                        $dates[] = date('Y-m-d', $t);
                    }
                }
            }
            $cache[$key] = array_values(array_unique($dates));
        }
        return $cache[$key];
    }

    /**
     * Working school day: not a configured weekly off and not a branch holiday.
     */
    protected function digital_diary_is_school_open_day($date, $branchID)
    {
        $date = date('Y-m-d', strtotime($date));
        $weekendDay = $this->application_model->getWeekends($branchID);
        if ($weekendDay !== '' && $weekendDay !== null) {
            $dow = (string) date('w', strtotime($date));
            foreach (explode(',', $weekendDay) as $weekendValue) {
                $weekendValue = trim($weekendValue);
                if ($weekendValue !== '' && $weekendValue === $dow) {
                    return false;
                }
            }
        }
        return !in_array($date, $this->digital_diary_get_holiday_dates($branchID), true);
    }

    /**
     * Work summary scores: completed checks scaled to school open days (excl. holidays & weekly off).
     *
     * @return array{subject_scores:array,grade:string}
     */
    protected function digital_diary_build_subject_work_scores($subjectsForGrid, $subjTotals, $schoolDays)
    {
        $subject_scores = array();
        $openDays = max(1, (int) $schoolDays);
        $maxPts = max(1, $openDays * 3);
        $avg = 0;
        $cnt = 0;
        foreach ($subjectsForGrid as $sg) {
            $sid = (int) $sg['subject_id'];
            $checks = isset($subjTotals[$sid]) ? (int) $subjTotals[$sid]['checks'] : 0;
            $outOfDays = (int) round($checks / $maxPts * $openDays);
            if ($outOfDays > $openDays) {
                $outOfDays = $openDays;
            }
            $nm = strtolower($sg['subject_name']);
            $fair = isset($subjTotals[$sid]['fair']) ? (int) $subjTotals[$sid]['fair'] : 0;
            $writing = isset($subjTotals[$sid]['writing']) ? (int) $subjTotals[$sid]['writing'] : 0;
            $learning = isset($subjTotals[$sid]['learning']) ? (int) $subjTotals[$sid]['learning'] : 0;
            $subject_scores[] = array(
                'label' => $sg['subject_name'],
                'text' => $outOfDays . '/' . $openDays,
                'score' => $outOfDays,
                'out_of' => $openDays,
                'checks' => $checks,
                'max_checks' => $maxPts,
                'percent' => $maxPts > 0 ? (int) round($checks / $maxPts * 100) : 0,
                'fair' => $fair,
                'writing' => $writing,
                'learning' => $learning,
                'is_hindi' => (strpos($nm, 'hindi') !== false),
            );
            $avg += $outOfDays;
            $cnt++;
        }
        $avg = $cnt ? ($avg / $cnt) : 0;
        // Grade thresholds mirror previous /50 bands as % of school open days.
        $pct = $openDays > 0 ? ($avg / $openDays) * 100 : 0;
        if ($cnt === 0) {
            $grade = '—';
        } elseif ($pct >= 90) {
            $grade = 'A';
        } elseif ($pct >= 80) {
            $grade = 'B';
        } elseif ($pct >= 66) {
            $grade = 'C';
        } else {
            $grade = 'D';
        }
        return array(
            'subject_scores' => $subject_scores,
            'grade' => $grade,
        );
    }

    /**
     * DataTables server-side for Teaching Activity list
     */
    public function getTeachingActivityDT()
    {
        if (!$this->input->post()) {
            echo json_encode(array('draw' => 0, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []));
            return;
        }
        if (is_teacher_loggedin()) {
            echo json_encode(array(
                'draw' => (int) $this->input->post('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ));
            return;
        }
        if (!$this->db->table_exists('digital_diary_teaching_activity')) {
            echo json_encode(array(
                'draw' => (int) $this->input->post('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ));
            return;
        }
        $this->digital_diary_model->ensureTeachingActivityRoleColumns();
        try {
            $results = $this->digital_diary_model->getTeachingActivityDT_list();
            $records = json_decode($results);
            if (!$records || !isset($records->data) || !is_array($records->data)) {
                echo json_encode(array(
                    'draw' => (int) $this->input->post('draw'),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ));
                return;
            }
            $data = array();
            foreach ($records->data as $record) {
                $rec = is_array($record) ? (object) $record : $record;
                $actions = '<a href="' . base_url('digital_diary/role_allocation') . '?activity_id=' . (int) $rec->id . (is_superadmin_loggedin() && !empty($rec->branch_id) ? '&branch_id=' . (int) $rec->branch_id : '') . '" class="btn btn-circle btn-default icon" title="Assign Coordinator"><i class="fas fa-user-plus"></i></a>';
                $actions .= '<a href="' . base_url('digital_diary/teaching_activity_edit/' . $rec->id) . '" class="btn btn-circle btn-default icon" title="Edit"><i class="fas fa-pen-nib"></i></a>';
                $actions .= btn_delete('digital_diary/teaching_activity_delete/' . $rec->id);
                $notebookTypeName = '—';
                if (isset($rec->notebook_type_ids) && trim((string) $rec->notebook_type_ids) !== '') {
                    $ids = array_filter(array_map('intval', explode(',', $rec->notebook_type_ids)));
                    $names = $this->digital_diary_model->getNotebookTypeNamesByIdsOrdered($ids);
                    if (!empty($names)) {
                        $notebookTypeName = implode(', ', $names);
                    }
                } elseif (isset($rec->notebook_type_name) && $rec->notebook_type_name) {
                    $notebookTypeName = $rec->notebook_type_name;
                }
                $row = array(
                    isset($rec->name) ? html_escape($rec->name) : '',
                    isset($rec->code) && $rec->code !== '' && $rec->code !== null ? html_escape($rec->code) : '—',
                    $notebookTypeName !== '—' ? html_escape($notebookTypeName) : '—',
                    isset($rec->description) && $rec->description !== '' ? html_escape($rec->description) : '—',
                    $this->digital_diary_teaching_activity_role_switch_html($rec->id, 'coordinator', isset($rec->enable_coordinator) ? $rec->enable_coordinator : 1),
                    $actions,
                );
                $data[] = $row;
            }
            echo json_encode(array(
                'draw' => (int) $records->draw,
                'recordsTotal' => (int) $records->recordsTotal,
                'recordsFiltered' => (int) $records->recordsFiltered,
                'data' => $data,
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'draw' => (int) $this->input->post('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ));
        }
    }

    /**
     * Add Teaching Activity
     */
    public function teaching_activity_add()
    {
        if (is_teacher_loggedin()) {
            if ($this->input->method() === 'post') {
                echo json_encode(array('status' => 'fail', 'error' => array('form_error' => translate('access_denied'))));
                exit();
            }
            access_denied();
        }
        if ($this->input->method() === 'post') {
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required');
            }
            $this->form_validation->set_rules('name', 'Activity', 'trim|required');
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                if (!$this->db->table_exists('digital_diary_teaching_activity')) {
                    $array = array('status' => 'fail', 'error' => array('form_error' => 'Table digital_diary_teaching_activity not found. Please run application/sql/digital_diary_teaching_activity.sql'));
                } else {
                    $this->digital_diary_model->save_teaching_activity($post);
                    set_alert('success', translate('information_has_been_saved_successfully'));
                    $redirect = base_url('digital_diary/teaching_activity_types');
                    if (is_superadmin_loggedin() && !empty($post['branch_id'])) {
                        $redirect .= '?branch_id=' . urlencode($post['branch_id']);
                    }
                    $array = array('status' => 'success', 'url' => $redirect, 'message' => translate('information_has_been_saved_successfully'));
                }
            } else {
                $array = array('status' => 'fail', 'error' => $this->form_validation->error_array());
            }
            echo json_encode($array);
            exit();
        }
        $branchID = $this->input->get('branch_id');
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        $this->data['branch_id'] = $branchID;
        $this->data['notebook_types'] = $this->digital_diary_model->getNotebookTypesByBranch($branchID);
        $this->data['notebook_types_multiselect'] = $this->digital_diary_model->getNotebookTypesByBranchMultiselect($branchID);
        $this->digital_diary_model->ensureTeachingActivityRoleColumns();
        $this->data['title'] = 'Add Teaching Activity';
        $this->data['sub_page'] = 'digital_diary/teaching_activity_add';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Edit Teaching Activity
     */
    public function teaching_activity_edit($id = '')
    {
        if (is_teacher_loggedin()) {
            if ($this->input->method() === 'post') {
                echo json_encode(array('status' => 'fail', 'error' => array('form_error' => translate('access_denied'))));
                exit();
            }
            access_denied();
        }
        if (empty($id)) {
            redirect(base_url('digital_diary/teaching_activity_types'));
        }
        if (!$this->db->table_exists('digital_diary_teaching_activity')) {
            redirect(base_url('digital_diary/teaching_activity_types'));
        }
        $this->digital_diary_model->ensureTeachingActivityRoleColumns();
        $row = $this->app_lib->getTable('digital_diary_teaching_activity', array('t.id' => $id), true);
        if (empty($row)) {
            show_404();
        }
        if ($this->input->method() === 'post') {
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required');
            }
            $this->form_validation->set_rules('name', 'Activity', 'trim|required');
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                $post['teaching_activity_id'] = $id;
                $this->digital_diary_model->save_teaching_activity($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $redirect = base_url('digital_diary/teaching_activity_types');
                if (is_superadmin_loggedin() && !empty($post['branch_id'])) {
                    $redirect .= '?branch_id=' . urlencode($post['branch_id']);
                }
                $array = array('status' => 'success', 'url' => $redirect, 'message' => translate('information_has_been_updated_successfully'));
            } else {
                $array = array('status' => 'fail', 'error' => $this->form_validation->error_array());
            }
            echo json_encode($array);
            exit();
        }
        $this->data['teaching_activity'] = $row;
        $this->data['branch_id'] = !empty($row['branch_id']) ? $row['branch_id'] : $this->application_model->get_branch_id();
        $this->data['notebook_types'] = $this->digital_diary_model->getNotebookTypesByBranch($this->data['branch_id']);
        $this->data['notebook_types_multiselect'] = $this->digital_diary_model->getNotebookTypesByBranchMultiselect($this->data['branch_id']);
        $this->data['title'] = 'Edit Teaching Activity';
        $this->data['sub_page'] = 'digital_diary/teaching_activity_edit';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Delete Teaching Activity
     */
    public function teaching_activity_delete($id = '')
    {
        if (is_teacher_loggedin()) {
            access_denied();
        }
        if (!empty($id)) {
            if (!$this->db->table_exists('digital_diary_teaching_activity')) {
                redirect(base_url('digital_diary/teaching_activity_types'));
            }
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('id', $id);
            $this->db->delete('digital_diary_teaching_activity');
        }
        redirect(base_url('digital_diary/teaching_activity_types'));
    }

    /**
     * Material switch HTML for Coordinator / Moderator on Teaching Activity list.
     *
     * @param int    $id
     * @param string $role coordinator|moderator
     * @param mixed  $enabled
     * @return string
     */
    protected function digital_diary_teaching_activity_role_switch_html($id, $role, $enabled)
    {
        $id = (int) $id;
        $role = ($role === 'moderator') ? 'moderator' : 'coordinator';
        $domId = 'ta_' . $role . '_' . $id;
        $checked = !empty($enabled) ? ' checked' : '';
        return '<div class="material-switch ml-xs">'
            . '<input class="ta-role-switch" id="' . $domId . '" data-id="' . $id . '" data-role="' . $role . '" type="checkbox"' . $checked . '>'
            . '<label for="' . $domId . '" class="label-primary"></label>'
            . '</div>';
    }

    /**
     * AJAX: enable/disable Coordinator or Moderator for one activity type.
     */
    public function teaching_activity_role_toggle()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        if (is_teacher_loggedin()) {
            echo json_encode(array('status' => false, 'msg' => translate('access_denied')));
            return;
        }
        $this->digital_diary_model->ensureTeachingActivityRoleColumns();
        $id = (int) $this->input->post('id');
        $role = trim((string) $this->input->post('role'));
        $status = $this->input->post('status');
        $field = '';
        if ($role === 'coordinator') {
            $field = 'enable_coordinator';
        }
        if ($id < 1 || $field === '' || !$this->db->table_exists('digital_diary_teaching_activity')
            || !$this->db->field_exists($field, 'digital_diary_teaching_activity')) {
            echo json_encode(array('status' => false, 'msg' => translate('invalid')));
            return;
        }
        $val = ($status === 'true' || $status === true || $status === '1' || $status === 1) ? 1 : 0;
        if (!is_superadmin_loggedin()) {
            $this->db->where('branch_id', get_loggedin_branch_id());
        }
        $this->db->where('id', $id);
        $this->db->update('digital_diary_teaching_activity', array($field => $val));
        echo json_encode(array(
            'status' => true,
            'msg' => translate('information_has_been_updated_successfully'),
        ));
    }

    /**
     * Notebook type <option> HTML for Teaching Activity add/edit when branch changes (AJAX)
     */
    public function ajax_notebook_types_options()
    {
        if (is_teacher_loggedin()) {
            echo '<option value="">' . html_escape(translate('access_denied')) . '</option>';
            return;
        }
        if (is_superadmin_loggedin()) {
            $branchID = $this->input->post('branch_id');
        } else {
            $branchID = get_loggedin_branch_id();
        }
        if (empty($branchID)) {
            echo '<option value="">' . html_escape(translate('select_branch_first')) . '</option>';
            return;
        }
        if (!$this->db->table_exists('digital_diary_notebook_type')) {
            echo '<option value="">' . html_escape(translate('select')) . '</option>';
            return;
        }
        $multi = $this->input->post('multi');
        if ($multi) {
            $types = $this->digital_diary_model->getNotebookTypesByBranchMultiselect($branchID);
            $html = '';
            foreach ($types as $tid => $name) {
                $html .= '<option value="' . (int) $tid . '">' . html_escape($name) . '</option>';
            }
        } else {
            $types = $this->digital_diary_model->getNotebookTypesByBranch($branchID);
            $html = '<option value="">' . html_escape(translate('select')) . '</option>';
            foreach ($types as $tid => $name) {
                if ($tid === '' || $tid === null) {
                    continue;
                }
                $html .= '<option value="' . (int) $tid . '">' . html_escape($name) . '</option>';
            }
        }
        echo $html;
    }

    /**
     * Assign existing teachers as Digital Diary Coordinator / Moderator (like class teacher allocation).
     */
    public function role_allocation()
    {
        if (!$this->digital_diary_can_assign_roles()) {
            access_denied();
        }
        $this->digital_diary_model->ensureRoleAllocationTable();
        $this->digital_diary_model->ensureTeachingActivityRoleColumns();
        $branch_id = $this->application_model->get_branch_id();
        $presetActivity = (int) $this->input->get('activity_id');
        $this->data['branch_id'] = $branch_id;
        $this->data['preset_activity_id'] = $presetActivity;
        $this->data['query_groups'] = $this->digital_diary_model->getRoleAllocationGroups($branch_id, 'coordinator');
        $this->data['activity_options'] = $this->digital_diary_model->getTeachingActivitiesForRole($branch_id, 'coordinator');
        $this->data['class_section_options'] = $this->digital_diary_model->getClassSectionPairOptions($branch_id);
        $this->data['title'] = 'Assign Coordinator';
        $this->data['sub_page'] = 'digital_diary/role_allocation';
        $this->data['main_menu'] = 'classes';
        $this->load->view('layout/index', $this->data);
    }

    public function get_role_allocation()
    {
        if (!$this->digital_diary_can_assign_roles()) {
            access_denied();
        }
        $allocation_id = (int) $this->input->post('id');
        $this->digital_diary_model->ensureRoleAllocationTable();
        $row = $this->digital_diary_model->getRoleAllocationGroupById($allocation_id);
        if (empty($row)) {
            echo '';
            return;
        }
        $this->data['data'] = $row;
        $this->data['activity_options'] = $this->digital_diary_model->getTeachingActivitiesForRole(
            isset($row['branch_id']) ? $row['branch_id'] : $this->application_model->get_branch_id(),
            'coordinator'
        );
        $this->data['class_section_options'] = $this->digital_diary_model->getClassSectionPairOptions(
            isset($row['branch_id']) ? $row['branch_id'] : 0
        );
        $this->load->view('digital_diary/role_allocation_modal', $this->data);
    }

    public function role_allocation_save()
    {
        if (!$this->input->post()) {
            show_404();
        }
        if (!$this->digital_diary_can_assign_roles()) {
            echo json_encode(array('status' => 'fail', 'error' => array('form_error' => translate('access_denied'))));
            return;
        }
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $_POST['role_type'] = 'coordinator';
        $this->form_validation->set_rules('role_type', 'Role', 'required|in_list[coordinator]');
        $this->form_validation->set_rules('teaching_activity_id', 'Activity type', 'required|numeric|greater_than[0]');
        $this->form_validation->set_rules('staff_id', translate('teacher'), 'required');
        $this->form_validation->set_rules('class_section[]', translate('class'), 'callback_unique_diary_role_classes');
        $pairsOk = !empty($this->digital_diary_model->parseClassSectionPairs($this->input->post('class_section')));
        if ($this->form_validation->run() === false || !$pairsOk) {
            $errors = $this->form_validation->error_array();
            if (!$pairsOk && empty($errors['class_section']) && empty($errors['class_section[]'])) {
                $errors['class_section'] = 'Select at least one class / section.';
                $errors['class_section[]'] = 'Select at least one class / section.';
            }
            echo json_encode(array('status' => 'fail', 'error' => $errors));
            return;
        }
        $post = $this->input->post();
        $post['role_type'] = 'coordinator';
        if (!is_superadmin_loggedin()) {
            $post['branch_id'] = (int) get_loggedin_branch_id();
        }
        $this->digital_diary_model->roleAllocationSave($post);
        $redirect = base_url('digital_diary/role_allocation');
        if (is_superadmin_loggedin() && !empty($post['branch_id'])) {
            $redirect .= '?branch_id=' . urlencode($post['branch_id']);
        }
        set_alert('success', empty($post['allocation_id'])
            ? translate('information_has_been_saved_successfully')
            : translate('information_has_been_updated_successfully'));
        echo json_encode(array('status' => 'success', 'url' => $redirect));
    }

    public function role_allocation_delete($id = '')
    {
        if (!$this->digital_diary_can_assign_roles()) {
            access_denied();
        }
        $branchID = is_superadmin_loggedin() ? 0 : (int) get_loggedin_branch_id();
        $this->digital_diary_model->roleAllocationDelete($id, $branchID);
        redirect(base_url('digital_diary/role_allocation'));
    }

    public function unique_diary_role_classes($unused)
    {
        $pairs = $this->digital_diary_model->parseClassSectionPairs($this->input->post('class_section'));
        if (empty($pairs)) {
            $this->form_validation->set_message('unique_diary_role_classes', 'Select at least one class / section.');
            return false;
        }
        $roleType = 'coordinator';
        $activityId = (int) $this->input->post('teaching_activity_id');
        $teacherID = (int) $this->input->post('staff_id');
        $allocationID = (int) $this->input->post('allocation_id');
        $branchID = is_superadmin_loggedin() ? (int) $this->input->post('branch_id') : (int) get_loggedin_branch_id();
        $sessionID = (int) get_session_id();
        $oldGroup = $allocationID > 0 ? $this->digital_diary_model->getRoleAllocationGroupById($allocationID) : null;
        foreach ($pairs as $pair) {
            $this->db->from('digital_diary_role_allocation');
            $this->db->where('role_type', $roleType);
            $this->db->where('class_id', $pair['class_id']);
            $this->db->where('section_id', $pair['section_id']);
            $this->db->where('session_id', $sessionID);
            if ($this->db->field_exists('teaching_activity_id', 'digital_diary_role_allocation')) {
                $this->db->where('teaching_activity_id', $activityId);
            }
            if ($branchID > 0) {
                $this->db->where('branch_id', $branchID);
            }
            $existing = $this->db->get()->row_array();
            if (empty($existing)) {
                continue;
            }
            $sameTeacher = (int) $existing['teacher_id'] === $teacherID;
            $sameOldGroup = false;
            if (!empty($oldGroup)) {
                $sameOldGroup = $existing['role_type'] === $oldGroup['role_type']
                    && (int) $existing['teacher_id'] === (int) $oldGroup['teacher_id']
                    && (int) $existing['branch_id'] === (int) $oldGroup['branch_id']
                    && (int) (isset($existing['teaching_activity_id']) ? $existing['teaching_activity_id'] : 0) === (int) $oldGroup['teaching_activity_id'];
            }
            if (!$sameTeacher && !$sameOldGroup) {
                $this->form_validation->set_message(
                    'unique_diary_role_classes',
                    'Another teacher is already ' . $roleType . ' for this activity on ' . get_type_name_by_id('class', $pair['class_id']) . ' - ' . get_type_name_by_id('section', $pair['section_id']) . '.'
                );
                return false;
            }
        }
        return true;
    }

    /**
     * AJAX: class-section <option> HTML for coordinator assignment.
     */
    public function ajax_class_section_pairs()
    {
        if (!$this->digital_diary_can_assign_roles()) {
            echo '';
            return;
        }
        $branchID = is_superadmin_loggedin() ? (int) $this->input->post('branch_id') : (int) get_loggedin_branch_id();
        $selected = $this->input->post('selected');
        if (!is_array($selected)) {
            $selected = array();
        }
        $opts = $this->digital_diary_model->getClassSectionPairOptions($branchID);
        $html = '';
        foreach ($opts as $val => $lab) {
            $sel = in_array((string) $val, array_map('strval', $selected), true) ? ' selected' : '';
            $html .= '<option value="' . html_escape($val) . '"' . $sel . '>' . html_escape($lab) . '</option>';
        }
        echo $html;
    }

    /**
     * AJAX: teaching activity options filtered by coordinator/moderator flag.
     */
    public function ajax_teaching_activities_for_role()
    {
        if (!$this->digital_diary_can_assign_roles()) {
            echo '<option value="">' . html_escape(translate('access_denied')) . '</option>';
            return;
        }
        $branchID = is_superadmin_loggedin() ? (int) $this->input->post('branch_id') : (int) get_loggedin_branch_id();
        if ($branchID < 1) {
            $branchID = (int) $this->application_model->get_branch_id();
        }
        $selected = (int) $this->input->post('selected');
        $opts = $this->digital_diary_model->getTeachingActivitiesForRole($branchID, 'coordinator');
        $html = '<option value="">' . html_escape(translate('select')) . '</option>';
        foreach ($opts as $id => $name) {
            $sel = ((int) $id === $selected) ? ' selected' : '';
            $html .= '<option value="' . (int) $id . '"' . $sel . '>' . html_escape($name) . '</option>';
        }
        echo $html;
    }

    /**
     * Coordinator task alerts (Admin / Super Admin / assigned coordinator or moderator teachers).
     */
    public function coordinator_tasks()
    {
        if (!$this->digital_diary_user_may_coordinate()) {
            access_denied();
        }
        $this->digital_diary_model->ensureTodayWorkLearningFlowTables();

        $branchID = 0;
        if (is_superadmin_loggedin()) {
            $branchGet = $this->input->get('branch_id');
            if ($branchGet !== null && $branchGet !== '' && (int) $branchGet > 0) {
                $branchID = (int) $branchGet;
            }
        } else {
            $branchID = (int) get_loggedin_branch_id();
            if ($branchID < 1) {
                $branchID = (int) $this->application_model->get_branch_id();
            }
        }

        $fullScopes = $this->digital_diary_user_coordinate_full_scopes($branchID);

        if ($this->input->get('mark_all') === '1') {
            $this->digital_diary_model->markAllCoordinatorAlertsRead((int) $branchID);
            set_alert('success', 'All alerts marked as read.');
            redirect(base_url('digital_diary/coordinator_tasks' . ($branchID ? ('?branch_id=' . $branchID) : '')));
        }
        $markId = (int) $this->input->get('read');
        if ($markId > 0) {
            $this->digital_diary_model->markCoordinatorAlertRead($markId, is_superadmin_loggedin() ? 0 : (int) $branchID);
            redirect(base_url('digital_diary/coordinator_tasks' . ($branchID ? ('?branch_id=' . $branchID) : '')));
        }

        $alerts = $this->digital_diary_model->getCoordinatorAlerts((int) $branchID, 300, false, $fullScopes);
        if (is_array($fullScopes)) {
            $filteredAlerts = array();
            foreach ($alerts as $a) {
                $aid = isset($a['teaching_activity_id']) ? (int) $a['teaching_activity_id'] : 0;
                if ($aid < 0) {
                    continue;
                }
                $actIds = $aid > 0 ? array($aid) : $this->digital_diary_model->resolveAlertActivityIds($a);
                if ($this->digital_diary_model->scopesMatchClassActivity(
                    $fullScopes,
                    (int) $a['class_id'],
                    (int) $a['section_id'],
                    $actIds
                )) {
                    $filteredAlerts[] = $a;
                }
            }
            $alerts = $filteredAlerts;
        }
        $pending = array();
        $completed = array();
        $unread = 0;
        $windowCache = array();
        $fullScopesTask = $this->digital_diary_user_coordinate_full_scopes($branchID);
        foreach ($alerts as &$a) {
            $actId = isset($a['teaching_activity_id']) ? (int) $a['teaching_activity_id'] : 0;
            $key = $a['work_date'] . '|' . (int) $a['class_id'] . '|' . (int) $a['section_id'] . '|' . $actId;
            $isUnread = empty($a['is_read']);
            if ($isUnread) {
                $unread++;
            }
            $event = isset($a['event_type']) ? (string) $a['event_type'] : '';
            $alertBranch = !empty($a['branch_id']) ? (int) $a['branch_id'] : (int) $branchID;
            if (!isset($windowCache[$key])) {
                $twMapDay = $this->digital_diary_model->getTodayWorkMapByClassSectionDate(
                    $a['work_date'],
                    $alertBranch,
                    (int) $a['class_id'],
                    (int) $a['section_id']
                );
                $matchIds = array();
                foreach ($twMapDay as $twRow) {
                    $rowActs = $this->digital_diary_model->todayWorkRowActivityIds($twRow);
                    if ($actId > 0 && !in_array($actId, $rowActs, true)) {
                        continue;
                    }
                    if (is_array($fullScopesTask) && !$this->digital_diary_model->scopesMatchClassActivity(
                        $fullScopesTask,
                        (int) $a['class_id'],
                        (int) $a['section_id'],
                        $actId > 0 ? array($actId) : $rowActs
                    )) {
                        continue;
                    }
                    $matchIds[] = (int) $twRow['id'];
                }
                $windowCache[$key] = $this->digital_diary_model->getCoordinatorReviewWindowStatus(
                    $a['work_date'],
                    $alertBranch,
                    (int) $a['class_id'],
                    (int) $a['section_id'],
                    $matchIds
                );
            }
            $win = $windowCache[$key];
            $dayReviewed = !empty($win['is_reviewed']);
            $isReviewedEvent = $dayReviewed;
            $a['review_deadline'] = isset($win['deadline']) ? $win['deadline'] : null;
            $a['review_window_status'] = isset($win['status']) ? $win['status'] : 'not_final';
            $a['review_days_left'] = isset($win['days_left']) ? $win['days_left'] : null;
            $a['review_window_label'] = isset($win['label']) ? $win['label'] : '';

            // Reviewed tab: reviewed days / coordinator_reviewed events
            // Pending tab: still needs coordinator attention
            if ($isReviewedEvent) {
                $a['task_status'] = 'reviewed';
                $a['status_label'] = 'Reviewed';
                $completed[] = $a;
            } else {
                $a['task_status'] = 'pending';
                if ($a['review_window_status'] === 'overdue') {
                    $a['status_label'] = 'Overdue';
                } elseif ($a['review_window_status'] === 'open' && $a['review_days_left'] !== null) {
                    $a['status_label'] = (int) $a['review_days_left'] . ' day' . ((int) $a['review_days_left'] === 1 ? '' : 's') . ' left';
                } else {
                    $a['status_label'] = $isUnread ? 'Pending' : 'Open';
                }
                $pending[] = $a;
            }
            $a['day_reviewed'] = $dayReviewed ? 1 : 0;
            $a['event_label'] = $this->digital_diary_coordinator_event_label($event);
            if (empty($a['activity_name']) && $actId > 0) {
                $a['activity_name'] = $this->digital_diary_model->getTeachingActivityName($actId);
            }
        }
        unset($a);

        // Pending: Overdue first, then soonest deadline, then newest alerts
        usort($pending, function ($x, $y) {
            $xo = (isset($x['review_window_status']) && $x['review_window_status'] === 'overdue') ? 0 : 1;
            $yo = (isset($y['review_window_status']) && $y['review_window_status'] === 'overdue') ? 0 : 1;
            if ($xo !== $yo) {
                return $xo - $yo;
            }
            $xd = !empty($x['review_deadline']) ? $x['review_deadline'] : '9999-12-31';
            $yd = !empty($y['review_deadline']) ? $y['review_deadline'] : '9999-12-31';
            if ($xd !== $yd) {
                return strcmp($xd, $yd);
            }
            $xc = isset($x['created_at']) ? $x['created_at'] : '';
            $yc = isset($y['created_at']) ? $y['created_at'] : '';
            return strcmp($yc, $xc);
        });

        $this->data['branch_id'] = $branchID;
        $this->data['branch_name'] = !empty($branchID) ? get_type_name_by_id('branch', $branchID) : '';
        $this->data['alerts'] = $alerts;
        $this->data['alerts_pending'] = $pending;
        $this->data['alerts_reviewed'] = $completed;
        $this->data['alerts_completed'] = $completed; // legacy alias
        $this->data['task_groups_pending'] = $this->digital_diary_group_coordinator_tasks($pending, (int) $branchID);
        $this->data['task_groups_reviewed'] = $this->digital_diary_group_coordinator_tasks($completed, (int) $branchID);
        $this->data['task_groups_all'] = $this->digital_diary_group_coordinator_tasks($alerts, (int) $branchID);

        $filterWorkDate = trim((string) $this->input->get('work_date'));
        if ($filterWorkDate !== '') {
            $ts = strtotime($filterWorkDate);
            $filterWorkDate = $ts ? date('Y-m-d', $ts) : '';
        }
        if ($filterWorkDate !== '') {
            $keepDate = function ($rows) use ($filterWorkDate) {
                $out = array();
                foreach ($rows as $row) {
                    if (!empty($row['work_date']) && $row['work_date'] === $filterWorkDate) {
                        $out[] = $row;
                    }
                }
                return $out;
            };
            $this->data['task_groups_pending'] = $keepDate($this->data['task_groups_pending']);
            $this->data['task_groups_reviewed'] = $keepDate($this->data['task_groups_reviewed']);
            $this->data['task_groups_all'] = $keepDate($this->data['task_groups_all']);
        }
        $this->data['filter_work_date'] = $filterWorkDate;
        $overdueCount = 0;
        foreach ($this->data['task_groups_pending'] as $pg) {
            if (isset($pg['review_window_status']) && $pg['review_window_status'] === 'overdue') {
                $overdueCount++;
            }
        }
        $this->data['overdue_count'] = $overdueCount;
        $this->data['unread_count'] = $unread;
        $this->data['pending_count'] = count($this->data['task_groups_pending']);
        $this->data['reviewed_count'] = count($this->data['task_groups_reviewed']);
        $this->data['completed_count'] = $this->data['reviewed_count'];
        $this->data['all_count'] = count($this->data['task_groups_all']);
        $this->data['active_tab'] = $this->input->get('tab');
        if ($this->data['active_tab'] === 'completed') {
            $this->data['active_tab'] = 'reviewed';
        }
        if (!in_array($this->data['active_tab'], array('pending', 'reviewed', 'all'), true)) {
            $this->data['active_tab'] = ($this->data['pending_count'] > 0) ? 'pending' : 'all';
        }
        $this->data['title'] = 'Coordinator Tasks';
        $this->data['sub_page'] = 'digital_diary/coordinator_tasks';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Human label for coordinator alert event types.
     *
     * @param string $event
     * @return string
     */
    protected function digital_diary_coordinator_event_label($event)
    {
        $map = array(
            'today_work_saved' => "Today's Work saved",
            'student_submitted' => 'Student submitted',
            'teacher_verified' => 'Teacher verified SWR',
            'final_submit' => 'Final submit',
            'principal_report' => 'Principal report',
            'coordinator_reviewed' => 'Coordinator reviewed',
        );
        return isset($map[$event]) ? $map[$event] : ($event !== '' ? $event : 'Alert');
    }

    /**
     * Coordinator review page URL (keeps activity + alert context).
     *
     * @param int $studentId optional student to focus
     * @return string
     */
    protected function digital_diary_coordinator_review_url($workDate, $classID, $sectionID, $branchID, $alertId = 0, $focusActivity = 0, $studentId = 0)
    {
        $q = array(
            'date' => $workDate,
            'class_id' => (int) $classID,
            'section_id' => (int) $sectionID,
        );
        if ((int) $branchID > 0) {
            $q['branch_id'] = (int) $branchID;
        }
        if ((int) $alertId > 0) {
            $q['alert_id'] = (int) $alertId;
        }
        if ((int) $focusActivity > 0) {
            $q['activity_id'] = (int) $focusActivity;
        }
        if ((int) $studentId > 0) {
            $q['student_id'] = (int) $studentId;
        }
        return base_url('digital_diary/coordinator_review') . '?' . http_build_query($q);
    }

    /**
     * Student IDs that already have a score for every listed activity.
     *
     * @param array $students
     * @param array $skillRatings
     * @param array<int,string> $activityOptions
     * @return array<int,int> student_id => 1
     */
    protected function digital_diary_rated_student_map($students, $skillRatings, $activityOptions)
    {
        $out = array();
        $aids = array_map('intval', array_keys($activityOptions));
        if (empty($aids)) {
            return $out;
        }
        foreach ($students as $st) {
            $sid = (int) $st['student_id'];
            $ok = true;
            foreach ($aids as $aid) {
                $score = isset($skillRatings[$sid][$aid]['score']) ? (int) $skillRatings[$sid][$aid]['score'] : 0;
                if ($score < 1) {
                    $ok = false;
                    break;
                }
            }
            if ($ok) {
                $out[$sid] = 1;
            }
        }
        return $out;
    }

    /**
     * Collapse event alerts into one task per class + date + teaching activity.
     *
     * @param array $alerts
     * @param int $branchID
     * @return array
     */
    protected function digital_diary_group_coordinator_tasks($alerts, $branchID)
    {
        $groups = array();
        foreach ((array) $alerts as $a) {
            $actId = isset($a['teaching_activity_id']) ? (int) $a['teaching_activity_id'] : 0;
            $key = (isset($a['work_date']) ? $a['work_date'] : '') . '|' . (int) $a['class_id'] . '|' . (int) $a['section_id'] . '|' . $actId;
            $created = isset($a['created_at']) ? (string) $a['created_at'] : '';
            if (!isset($groups[$key])) {
                $groups[$key] = $a;
                $groups[$key]['event_count'] = 0;
                $groups[$key]['group_unread'] = 0;
                $groups[$key]['latest_message'] = isset($a['message']) ? (string) $a['message'] : '';
                $groups[$key]['latest_event'] = isset($a['event_label']) ? (string) $a['event_label'] : '';
                $groups[$key]['latest_at'] = $created;
            }
            $groups[$key]['event_count']++;
            if (empty($a['is_read'])) {
                $groups[$key]['group_unread']++;
            }
            if ($created !== '' && $created >= (string) $groups[$key]['latest_at']) {
                $groups[$key]['latest_at'] = $created;
                $groups[$key]['latest_message'] = isset($a['message']) ? (string) $a['message'] : '';
                $groups[$key]['latest_event'] = isset($a['event_label']) ? (string) $a['event_label'] : '';
                if (!empty($a['id'])) {
                    $groups[$key]['id'] = (int) $a['id'];
                }
            }
        }
        $studentCache = array();
        $ratingCache = array();
        foreach ($groups as &$g) {
            $classID = (int) $g['class_id'];
            $sectionID = (int) $g['section_id'];
            $workDate = isset($g['work_date']) ? $g['work_date'] : '';
            $b = !empty($g['branch_id']) ? (int) $g['branch_id'] : (int) $branchID;
            $ck = $b . '|' . $workDate . '|' . $classID . '|' . $sectionID;
            if (!isset($studentCache[$ck])) {
                $studentCache[$ck] = $this->digital_diary_model->getStudentsForStudentWorkReport($b, $classID, $sectionID);
                $ratingCache[$ck] = $this->digital_diary_model->getCoordinatorActivityRatingsMap($workDate, $b, $classID, $sectionID);
            }
            $actId = isset($g['teaching_activity_id']) ? (int) $g['teaching_activity_id'] : 0;
            $rated = 0;
            $students = $studentCache[$ck];
            $ratings = $ratingCache[$ck];
            foreach ($students as $st) {
                $sid = (int) $st['student_id'];
                $score = 0;
                if ($actId > 0) {
                    $score = isset($ratings[$sid][$actId]['score']) ? (int) $ratings[$sid][$actId]['score'] : 0;
                } elseif (!empty($ratings[$sid]) && is_array($ratings[$sid])) {
                    foreach ($ratings[$sid] as $cell) {
                        if (!empty($cell['score'])) {
                            $score = (int) $cell['score'];
                            break;
                        }
                    }
                }
                if ($score > 0) {
                    $rated++;
                }
            }
            $g['rated_count'] = $rated;
            $g['student_count'] = count($students);
        }
        unset($g);
        usort($groups, function ($x, $y) {
            $xd = isset($x['work_date']) ? $x['work_date'] : '';
            $yd = isset($y['work_date']) ? $y['work_date'] : '';
            if ($xd !== $yd) {
                return strcmp($yd, $xd);
            }
            $xc = trim((isset($x['class_name']) ? $x['class_name'] : '') . (isset($x['section_name']) ? $x['section_name'] : ''));
            $yc = trim((isset($y['class_name']) ? $y['class_name'] : '') . (isset($y['section_name']) ? $y['section_name'] : ''));
            if ($xc !== $yc) {
                return strcasecmp($xc, $yc);
            }
            $xa = isset($x['activity_name']) ? $x['activity_name'] : '';
            $ya = isset($y['activity_name']) ? $y['activity_name'] : '';
            return strcasecmp($xa, $ya);
        });
        return array_values($groups);
    }

    /**
     * Coordinator review screen: students + work status for a class/section/date; Submit marks reviewed.
     */
    public function coordinator_review()
    {
        if (!$this->digital_diary_user_may_coordinate()) {
            access_denied();
        }
        $this->digital_diary_model->ensureTodayWorkLearningFlowTables();

        $workDate = $this->input->get('date');
        if (empty($workDate)) {
            $workDate = date('Y-m-d');
        } else {
            $workDate = date('Y-m-d', strtotime($workDate));
        }
        $classID = (int) $this->input->get('class_id');
        $sectionID = (int) $this->input->get('section_id');
        $alertId = (int) $this->input->get('alert_id');
        $subjectID = (int) $this->input->get('subject_id');
        $focusActivity = (int) $this->input->get('activity_id');
        $focusStudentId = (int) $this->input->get('student_id');
        if ($classID > 0 && $sectionID > 0 && !$this->digital_diary_user_may_coordinate_class_section($classID, $sectionID)) {
            access_denied();
        }

        // Resolve branch: GET → alert → logged-in → today_work row for this date/class/section
        $branchID = 0;
        $branchGet = $this->input->get('branch_id');
        if ($branchGet !== null && $branchGet !== '' && (int) $branchGet > 0) {
            $branchID = (int) $branchGet;
        }
        if ($branchID < 1 && $alertId > 0 && $this->db->table_exists('digital_diary_coordinator_alert')) {
            $alertCols = 'branch_id, work_date, class_id, section_id';
            if ($this->db->field_exists('teaching_activity_id', 'digital_diary_coordinator_alert')) {
                $alertCols .= ', teaching_activity_id';
            }
            $alertRow = $this->db->select($alertCols)
                ->where('id', $alertId)
                ->get('digital_diary_coordinator_alert')
                ->row_array();
            if (!empty($alertRow)) {
                $branchID = (int) $alertRow['branch_id'];
                if ($classID < 1) {
                    $classID = (int) $alertRow['class_id'];
                }
                if ($sectionID < 1) {
                    $sectionID = (int) $alertRow['section_id'];
                }
                if (!empty($alertRow['work_date'])) {
                    $workDate = date('Y-m-d', strtotime($alertRow['work_date']));
                }
                if ($focusActivity < 1 && !empty($alertRow['teaching_activity_id'])) {
                    $focusActivity = (int) $alertRow['teaching_activity_id'];
                }
            }
        }
        if ($branchID < 1 && !is_superadmin_loggedin()) {
            $branchID = (int) get_loggedin_branch_id();
        }
        if ($branchID < 1 && $classID > 0 && $sectionID > 0 && $this->db->table_exists('digital_diary_today_work')) {
            $guess = $this->db->select('branch_id')
                ->from('digital_diary_today_work')
                ->where('work_date', $workDate)
                ->where('class_id', $classID)
                ->where('section_id', $sectionID)
                ->where('session_id', (int) get_session_id())
                ->order_by('id', 'DESC')
                ->limit(1)
                ->get()
                ->row();
            if ($guess && !empty($guess->branch_id)) {
                $branchID = (int) $guess->branch_id;
            }
        }
        if ($branchID < 1) {
            $branchID = (int) $this->application_model->get_branch_id();
        }

        if ($this->input->method() === 'post') {
            $workDate = date('Y-m-d', strtotime($this->input->post('work_date')));
            $classID = (int) $this->input->post('class_id');
            $sectionID = (int) $this->input->post('section_id');
            $postBranch = (int) $this->input->post('branch_id');
            if ($postBranch > 0) {
                $branchID = $postBranch;
            } elseif ($branchID < 1 && !is_superadmin_loggedin()) {
                $branchID = (int) get_loggedin_branch_id();
            }
            $alertId = (int) $this->input->post('alert_id');
            $staffID = (int) get_loggedin_user_id();
            $postFocus = (int) $this->input->post('focus_activity_id');
            if ($postFocus > 0) {
                $focusActivity = $postFocus;
            }

            if ($classID < 1 || $sectionID < 1 || $branchID < 1) {
                set_alert('error', 'Class, section and branch are required.');
                redirect(base_url('digital_diary/coordinator_tasks' . ($branchID ? ('?branch_id=' . $branchID) : '')));
                return;
            }
            if (!$this->digital_diary_user_may_coordinate_class_section($classID, $sectionID)) {
                access_denied();
            }

            $fullScopes = $this->digital_diary_user_coordinate_full_scopes($branchID);
            $twMap = $this->digital_diary_model->getTodayWorkMapByClassSectionDate($workDate, $branchID, $classID, $sectionID);
            $reviewTwIds = array();
            foreach ($twMap as $twRowPre) {
                $rowActs = $this->digital_diary_model->todayWorkRowActivityIds($twRowPre);
                if ($focusActivity > 0 && !in_array($focusActivity, $rowActs, true)) {
                    continue;
                }
                if (is_array($fullScopes) && !$this->digital_diary_model->scopesMatchClassActivity(
                    $fullScopes,
                    $classID,
                    $sectionID,
                    $focusActivity > 0 ? array($focusActivity) : $rowActs
                )) {
                    continue;
                }
                $reviewTwIds[] = (int) $twRowPre['id'];
            }

            $window = $this->digital_diary_model->getCoordinatorReviewWindowStatus(
                $workDate,
                $branchID,
                $classID,
                $sectionID,
                $reviewTwIds
            );
            if ($window['status'] === 'not_final') {
                set_alert('error', 'Review opens after the class teacher Final Submit.');
                $failQ = array(
                    'date' => $workDate,
                    'class_id' => $classID,
                    'section_id' => $sectionID,
                    'branch_id' => $branchID,
                    'alert_id' => $alertId,
                );
                if ($focusActivity > 0) {
                    $failQ['activity_id'] = $focusActivity;
                }
                redirect(base_url('digital_diary/coordinator_review') . '?' . http_build_query($failQ));
                return;
            }
            if ($window['status'] === 'closed_reviewed') {
                set_alert('error', 'This day is already coordinator-reviewed and locked.');
                redirect(base_url('digital_diary/coordinator_tasks' . ($branchID ? ('?branch_id=' . $branchID) : '')));
                return;
            }
            if (!in_array($window['status'], array('open', 'overdue'), true)) {
                set_alert('error', 'Coordinator review is not available for this day.');
                redirect(base_url('digital_diary/coordinator_tasks' . ($branchID ? ('?branch_id=' . $branchID) : '')));
                return;
            }

            $activityOptions = $this->digital_diary_model->getReviewActivityOptions($branchID, $classID, $sectionID, $fullScopes);
            if ($focusActivity > 0 && isset($activityOptions[$focusActivity])) {
                $activityOptions = array($focusActivity => $activityOptions[$focusActivity]);
            }
            $students = $this->digital_diary_model->getStudentsForStudentWorkReport($branchID, $classID, $sectionID);
            $validIds = array();
            foreach ($students as $st) {
                $validIds[] = (int) $st['student_id'];
            }
            $submitMode = $this->input->post('submit_mode') === 'student' ? 'student' : 'all';
            $oneStudentId = (int) $this->input->post('student_id');
            $isAjax = ($this->input->post('ajax') === '1') || $this->input->is_ajax_request();
            if ($submitMode === 'student') {
                if ($oneStudentId < 1 || !in_array($oneStudentId, $validIds, true)) {
                    if ($isAjax) {
                        $this->output->set_content_type('application/json');
                        echo json_encode(array('status' => 'fail', 'message' => 'Select a student to save.'));
                        return;
                    }
                    set_alert('error', 'Select a student to save.');
                    redirect($this->digital_diary_coordinator_review_url($workDate, $classID, $sectionID, $branchID, $alertId, $focusActivity));
                    return;
                }
                $validIds = array($oneStudentId);
            }
            $skillPost = $this->input->post('skill');
            if (!is_array($skillPost)) {
                $skillPost = array();
            }
            $this->digital_diary_model->saveCoordinatorActivityRatings(
                $workDate,
                $branchID,
                $classID,
                $sectionID,
                $staffID,
                $skillPost,
                $activityOptions,
                $validIds
            );

            if ($submitMode === 'student') {
                $savedName = '';
                foreach ($students as $st) {
                    if ((int) $st['student_id'] === $oneStudentId) {
                        $savedName = isset($st['fullname']) ? $st['fullname'] : '';
                        break;
                    }
                }
                $ratingsNow = $this->digital_diary_model->getCoordinatorActivityRatingsMap($workDate, $branchID, $classID, $sectionID);
                $ratedNow = $this->digital_diary_rated_student_map($students, $ratingsNow, $activityOptions);
                $nextId = 0;
                $foundCur = false;
                foreach ($students as $st) {
                    $sid = (int) $st['student_id'];
                    if ($sid === $oneStudentId) {
                        $foundCur = true;
                        continue;
                    }
                    if ($foundCur && $nextId < 1 && empty($ratedNow[$sid])) {
                        $nextId = $sid;
                    }
                }
                if ($nextId < 1) {
                    foreach ($students as $st) {
                        $sid = (int) $st['student_id'];
                        if ($sid !== $oneStudentId && empty($ratedNow[$sid])) {
                            $nextId = $sid;
                            break;
                        }
                    }
                }
                $msg = ($savedName !== '' ? $savedName : 'Student') . ' saved. Continue with the next student or finalize when the class is done.';
                if ($isAjax) {
                    $this->output->set_content_type('application/json');
                    echo json_encode(array(
                        'status' => 'success',
                        'message' => $msg,
                        'student_id' => $oneStudentId,
                        'next_student_id' => $nextId,
                    ));
                    return;
                }
                set_alert('success', $msg);
                redirect($this->digital_diary_coordinator_review_url(
                    $workDate,
                    $classID,
                    $sectionID,
                    $branchID,
                    $alertId,
                    $focusActivity,
                    $nextId > 0 ? $nextId : $oneStudentId
                ));
                return;
            }

            $this->digital_diary_model->markTodayWorkCoordinatorReviewed(
                $workDate,
                $branchID,
                $classID,
                $sectionID,
                $staffID,
                $reviewTwIds
            );
            if ($alertId > 0) {
                $this->digital_diary_model->markCoordinatorAlertRead($alertId, is_superadmin_loggedin() ? 0 : (int) $branchID);
            }
            $actLabel = !empty($activityOptions) ? implode(', ', array_values($activityOptions)) : 'assigned activities';
            $this->digital_diary_model->addCoordinatorAlertsForActivities(array(
                'branch_id' => $branchID,
                'work_date' => $workDate,
                'class_id' => $classID,
                'section_id' => $sectionID,
                'event_type' => 'coordinator_reviewed',
                'message' => 'Coordinator reviewed ' . $actLabel . ' for ' . get_type_name_by_id('class', $classID) . '-' . get_type_name_by_id('section', $sectionID) . ' on ' . $workDate,
                'ref_id' => 0,
            ), array_keys($activityOptions));
            set_alert('success', 'Coordinator review submitted for: ' . $actLabel);
            $redir = base_url('digital_diary/coordinator_tasks');
            if ($branchID > 0) {
                $redir .= '?branch_id=' . $branchID;
            }
            redirect($redir);
            return;
        }

        $twMap = array();
        $students = array();
        $saved_maps = array();
        $submissions = array();
        $subjectRows = array();
        $reviewActivities = array();
        $skillRatings = array();
        $fullScopesView = $this->digital_diary_user_coordinate_full_scopes($branchID);
        $twIds = array();
        if ($branchID > 0 && $classID > 0 && $sectionID > 0) {
            $reviewActivities = $this->digital_diary_model->getReviewActivityOptions($branchID, $classID, $sectionID, $fullScopesView);
            if ($focusActivity > 0 && isset($reviewActivities[$focusActivity])) {
                $reviewActivities = array($focusActivity => $reviewActivities[$focusActivity]);
            }
            $allowedActIds = array_map('intval', array_keys($reviewActivities));
            $twMap = $this->digital_diary_model->getTodayWorkMapByClassSectionDate($workDate, $branchID, $classID, $sectionID);
            $students = $this->digital_diary_model->getStudentsForStudentWorkReport($branchID, $classID, $sectionID);
            $skillRatings = $this->digital_diary_model->getCoordinatorActivityRatingsMap($workDate, $branchID, $classID, $sectionID);
            foreach ($twMap as $sid => $row) {
                if ($subjectID > 0 && (int) $sid !== $subjectID) {
                    continue;
                }
                $rowActs = $this->digital_diary_model->todayWorkRowActivityIds($row);
                if ($focusActivity > 0 && !in_array($focusActivity, $rowActs, true)) {
                    continue;
                }
                if (is_array($fullScopesView) && !$this->digital_diary_model->scopesMatchClassActivity(
                    $fullScopesView,
                    $classID,
                    $sectionID,
                    $focusActivity > 0 ? array($focusActivity) : $rowActs
                )) {
                    continue;
                }
                $twIds[] = (int) $row['id'];
                $cw = $this->digital_diary_model->formatTodayWorkActivityWithDetail(
                    $this->digital_diary_model->formatTodayWorkItemsDisplay(
                        isset($row['class_work_items']) ? $row['class_work_items'] : '',
                        $branchID,
                        $allowedActIds
                    ),
                    isset($row['cw_detail']) ? $row['cw_detail'] : ''
                );
                $hw = $this->digital_diary_model->formatTodayWorkActivityWithDetail(
                    $this->digital_diary_model->formatTodayWorkItemsDisplay(
                        isset($row['home_work_items']) ? $row['home_work_items'] : '',
                        $branchID,
                        $allowedActIds
                    ),
                    isset($row['hw_detail']) ? $row['hw_detail'] : ''
                );
                $subjectRows[] = array(
                    'subject_id' => (int) $sid,
                    'subject_name' => get_type_name_by_id('subject', (int) $sid),
                    'class_work' => $cw,
                    'home_work' => $hw,
                    'cw_attachment' => isset($row['cw_attachment']) ? $row['cw_attachment'] : '',
                    'hw_attachment' => isset($row['hw_attachment']) ? $row['hw_attachment'] : '',
                    'reviewed' => !empty($row['coordinator_reviewed']),
                    'today_work_id' => (int) $row['id'],
                );
                $saved_maps[(int) $sid] = $this->digital_diary_model->getStudentWorkReportSavedMap(
                    $workDate,
                    $branchID,
                    $classID,
                    $sectionID,
                    (int) $sid
                );
            }
            $submissions = $this->digital_diary_model->getSubmissionsMapByTodayWorkIds($twIds);
        }

        $studentWork = array();
        foreach ($subjectRows as $srWork) {
            $twidWork = isset($srWork['today_work_id']) ? (int) $srWork['today_work_id'] : 0;
            if ($twidWork < 1 || empty($submissions[$twidWork]) || !is_array($submissions[$twidWork])) {
                continue;
            }
            foreach ($submissions[$twidWork] as $stuWorkId => $typesWork) {
                $stuWorkId = (int) $stuWorkId;
                if (!isset($studentWork[$stuWorkId])) {
                    $studentWork[$stuWorkId] = array();
                }
                foreach ((array) $typesWork as $typeWork => $subWork) {
                    $studentWork[$stuWorkId][] = array(
                        'subject_name' => isset($srWork['subject_name']) ? $srWork['subject_name'] : '',
                        'type' => (string) $typeWork,
                        'photo' => isset($subWork['photo']) ? $subWork['photo'] : '',
                        'message' => isset($subWork['message']) ? $subWork['message'] : '',
                        'status' => isset($subWork['status']) ? $subWork['status'] : '',
                    );
                }
            }
        }

        $reviewWindow = array(
            'status' => 'not_final',
            'deadline' => null,
            'days_left' => null,
            'is_final' => false,
            'is_reviewed' => false,
            'label' => 'Not final submitted',
        );
        if ($branchID > 0 && $classID > 0 && $sectionID > 0) {
            $reviewWindow = $this->digital_diary_model->getCoordinatorReviewWindowStatus(
                $workDate,
                $branchID,
                $classID,
                $sectionID,
                $twIds
            );
        }
        $canEditReview = in_array($reviewWindow['status'], array('open', 'overdue'), true);
        $ratedMap = $this->digital_diary_rated_student_map($students, $skillRatings, $reviewActivities);

        $this->data['branch_id'] = $branchID;
        $this->data['work_date'] = $workDate;
        $this->data['filter_class_id'] = $classID;
        $this->data['filter_section_id'] = $sectionID;
        $this->data['alert_id'] = $alertId;
        $this->data['focus_activity_id'] = $focusActivity;
        $this->data['focus_student_id'] = $focusStudentId;
        $this->data['subject_rows'] = $subjectRows;
        $this->data['students'] = $students;
        $this->data['saved_maps'] = $saved_maps;
        $this->data['submissions'] = $submissions;
        $this->data['student_work'] = $studentWork;
        $this->data['tw_map'] = $twMap;
        $this->data['review_window'] = $reviewWindow;
        $this->data['can_edit_review'] = $canEditReview;
        $this->data['review_activities'] = $reviewActivities;
        $this->data['skill_ratings'] = $skillRatings;
        $this->data['rated_map'] = $ratedMap;
        $this->data['title'] = 'Coordinator Review';
        $this->data['sub_page'] = 'digital_diary/coordinator_review';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    public function today_work()
    {
        $branchGet = $this->input->get('branch_id');
        $branchID = ($branchGet !== null && $branchGet !== '' && $branchGet !== false) ? (int) $branchGet : '';
        if ($branchID === 0) {
            $branchID = '';
        }
        if ($branchID === '') {
            $branchID = get_loggedin_branch_id();
        }
        if (($branchID === '' || $branchID === null) && !is_superadmin_loggedin()) {
            $branchID = $this->application_model->get_branch_id();
        }
        if ($branchID !== '' && $branchID !== null) {
            $branchID = (int) $branchID;
        }

        $this->data['branch_id'] = $branchID;
        $this->data['branch_name'] = !empty($branchID) ? get_type_name_by_id('branch', $branchID) : '';

        if (is_teacher_loggedin()) {
            $assigned = $this->app_lib->get_ownClassSection();
            $classOptions = array('' => translate('select'));
            if (is_array($assigned) && !empty($assigned)) {
                foreach ($assigned as $classKey => $sections) {
                    $classOptions[$classKey] = get_type_name_by_id('class', $classKey);
                }
            }
            if (count($classOptions) <= 1 && !empty($branchID)) {
                $classOptions = $this->app_lib->getClass($branchID);
            }
            $this->data['class_options'] = $classOptions;
        } else {
            $this->data['class_options'] = $this->app_lib->getClass($branchID);
        }

        $reportDate = $this->input->get('date');
        if (empty($reportDate)) {
            $reportDate = date('Y-m-d');
        } else {
            $reportDate = date('Y-m-d', strtotime($reportDate));
        }
        $classID = (int) $this->input->get('class_id');
        $sectionID = (int) $this->input->get('section_id');
        $subjectID = (int) $this->input->get('subject_id');
        $subjectGroup = trim((string) $this->input->get('subject_group'));

        $this->data['today_date'] = $reportDate;
        $this->data['filter_class_id'] = $classID;
        $this->data['filter_section_id'] = $sectionID;
        $this->data['filter_subject_id'] = $subjectID;
        $this->data['filter_subject_group'] = $subjectGroup;

        // If SWR is not submitted for this day/class/section, open SWR first.
        // Example: digital_diary/student_work_report_entry?date=2026-08-05&class_id=1&section_id=1&branch_id=1
        if ((int) $branchID > 0 && (int) $classID > 0 && (int) $sectionID > 0
            && $this->db->table_exists('digital_diary_student_work_report')
            && !$this->digital_diary_model->hasStudentWorkReportForDay($reportDate, $branchID, $classID, $sectionID)
        ) {
            $swrQ = array(
                'date' => $reportDate,
                'class_id' => (int) $classID,
                'section_id' => (int) $sectionID,
                'branch_id' => (int) $branchID,
            );
            redirect(base_url('digital_diary/student_work_report_entry') . '?' . http_build_query($swrQ));
            return;
        }

        if (is_teacher_loggedin()) {
            $assigned = $this->app_lib->get_ownClassSection();
            $restricted = is_array($assigned) && !empty($assigned);
            if ($restricted && !empty($classID)) {
                $section_options = array('' => translate('select'));
                if (isset($assigned[$classID])) {
                    foreach ($assigned[$classID] as $sid) {
                        $section_options[$sid] = get_type_name_by_id('section', $sid);
                    }
                }
                $this->data['section_options'] = $section_options;
            } else {
                $this->data['section_options'] = !empty($classID) ? $this->app_lib->getSections($classID, false) : array('' => translate('select_class_first'));
            }
        } else {
            $this->data['section_options'] = !empty($classID) ? $this->app_lib->getSections($classID, false) : array('' => translate('select_class_first'));
        }

        $this->data['subject_group_options'] = $this->digital_diary_model->getSubjectGroupOptionsByBranch($branchID);
        $this->data['report_rows'] = array();
        $this->data['today_work_saved_json'] = '{}';
        $this->data['is_today_work_day_final'] = false;

        if (!empty($classID) && !empty($sectionID)) {
            $subjectSummary = $this->digital_diary_model->getLessonSummaryByClassSection($classID, $sectionID, $branchID);
            $activityMap = $this->digital_diary_model->getTodayTeachingActivityMapBySubject($reportDate, $branchID, $classID, $sectionID, $subjectID);
            $savedMap = $this->digital_diary_model->getTodayWorkMapByClassSectionDate($reportDate, $branchID, $classID, $sectionID);
            $this->digital_diary_model->ensureTodayWorkLearningFlowTables();
            $twIdsForAssign = array();
            foreach ($savedMap as $sv) {
                if (!empty($sv['id'])) {
                    $twIdsForAssign[] = (int) $sv['id'];
                }
            }
            $assignSummary = $this->digital_diary_model->getAssignmentSummaryByTodayWorkIds($twIdsForAssign);
            if ($this->db->table_exists('digital_diary_today_work_day')) {
                $this->data['is_today_work_day_final'] = $this->digital_diary_model->isTodayWorkDayFinal($reportDate, $branchID, $classID, $sectionID);
            }
            $rows = array();
            $subjectGroupOptions = $this->data['subject_group_options'];
            $savedForJs = array();
            foreach ($subjectSummary as $subjRow) {
                $sid = (int) $subjRow['subject_id'];
                if (is_teacher_loggedin() && !$this->digital_diary_teacher_may_view_subject($classID, $sectionID, $sid)) {
                    continue;
                }
                $groupName = isset($subjRow['subject_group']) ? trim((string) $subjRow['subject_group']) : '';
                if ($groupName !== '' && !isset($subjectGroupOptions[$groupName])) {
                    $subjectGroupOptions[$groupName] = $groupName;
                }
                if (!empty($subjectID) && $sid !== (int) $subjectID) {
                    continue;
                }
                if ($subjectGroup !== '' && strcasecmp($groupName, $subjectGroup) !== 0) {
                    continue;
                }
                $todayItem = isset($activityMap[$sid]) ? $activityMap[$sid] : array();
                $saved = isset($savedMap[$sid]) ? $savedMap[$sid] : null;

                $totalLesson = isset($subjRow['total_lesson']) ? (int) $subjRow['total_lesson'] : 0;
                $nowGoing = '';
                $classWorkStr = '';
                $homeWorkStr = '';
                $sylPct = '—';
                $workPct = '—';

                if (!empty($saved)) {
                    $savedForJs[(string) $sid] = array(
                        'lesson_id' => !empty($saved['lesson_id']) ? (int) $saved['lesson_id'] : '',
                        'lesson_ids' => (!empty($saved['lesson_ids']) && $this->db->field_exists('lesson_ids', 'digital_diary_today_work')) ? $saved['lesson_ids'] : '',
                        'class_work_items' => isset($saved['class_work_items']) ? $saved['class_work_items'] : '',
                        'home_work_items' => isset($saved['home_work_items']) ? $saved['home_work_items'] : '',
                        'home_work_note' => isset($saved['home_work_note']) ? $saved['home_work_note'] : '',
                        'cw_detail' => isset($saved['cw_detail']) ? $saved['cw_detail'] : '',
                        'hw_detail' => isset($saved['hw_detail']) ? $saved['hw_detail'] : '',
                        'cw_attachment' => isset($saved['cw_attachment']) ? $saved['cw_attachment'] : '',
                        'hw_attachment' => isset($saved['hw_attachment']) ? $saved['hw_attachment'] : '',
                        'learning_qtype' => !empty($saved['learning_qtype']) ? (int) $saved['learning_qtype'] : '',
                        'learning_qcount' => !empty($saved['learning_qcount']) ? (int) $saved['learning_qcount'] : '',
                        'cw_notebook_type_id' => (!empty($saved['cw_notebook_type_id']) && $this->db->field_exists('cw_notebook_type_id', 'digital_diary_today_work')) ? (int) $saved['cw_notebook_type_id'] : '',
                        'hw_notebook_type_id' => (!empty($saved['hw_notebook_type_id']) && $this->db->field_exists('hw_notebook_type_id', 'digital_diary_today_work')) ? (int) $saved['hw_notebook_type_id'] : '',
                        'cw_notebook_type_ids' => ($this->db->field_exists('cw_notebook_type_ids', 'digital_diary_today_work') && !empty($saved['cw_notebook_type_ids'])) ? $saved['cw_notebook_type_ids'] : '',
                        'hw_notebook_type_ids' => ($this->db->field_exists('hw_notebook_type_ids', 'digital_diary_today_work') && !empty($saved['hw_notebook_type_ids'])) ? $saved['hw_notebook_type_ids'] : '',
                        'learning_question_ids' => '',
                        'learning_is_test' => 0,
                    );
                    $twid = (int) $saved['id'];
                    if ($twid > 0 && isset($assignSummary[$twid])) {
                        $as = $assignSummary[$twid];
                        $qIds = array();
                        foreach ($this->digital_diary_model->getTodayWorkAssignments($twid) as $ar) {
                            $qIds[] = (int) $ar['question_id'];
                        }
                        $savedForJs[(string) $sid]['learning_question_ids'] = implode(',', $qIds);
                        $savedForJs[(string) $sid]['learning_is_test'] = !empty($as['has_test']) ? 1 : 0;
                    }
                    $hasMultiLessons = !empty($saved['lesson_ids']) && $this->db->field_exists('lesson_ids', 'digital_diary_today_work');
                    if ($hasMultiLessons) {
                        $nowGoing = $this->digital_diary_model->formatMultiLessonLabels($saved['lesson_ids'], $classID, $sectionID, $sid, $branchID);
                    } elseif (!empty($saved['lesson_id']) && !empty($saved['lesson_no'])) {
                        $nowGoing = 'Lesson - ' . $saved['lesson_no'] . ', ' . $saved['lesson_name'];
                    } elseif (!empty($saved['lesson_id'])) {
                        $les = $this->app_lib->getTable('digital_diary_lesson', array('t.id' => $saved['lesson_id']), true);
                        if (!empty($les)) {
                            $nowGoing = 'Lesson - ' . $les['lesson_no'] . ', ' . $les['lesson_name'];
                        }
                    }
                    $classWorkStr = $this->digital_diary_model->formatTodayWorkActivityWithDetail(
                        $this->digital_diary_model->formatTodayWorkItemsDisplay(
                            isset($saved['class_work_items']) ? $saved['class_work_items'] : '',
                            $branchID
                        ),
                        isset($saved['cw_detail']) ? $saved['cw_detail'] : ''
                    );
                    if ($this->db->field_exists('cw_notebook_type_ids', 'digital_diary_today_work') && !empty(trim((string) (isset($saved['cw_notebook_type_ids']) ? $saved['cw_notebook_type_ids'] : '')))) {
                        $cwNbIds = array_filter(array_map('intval', explode(',', $saved['cw_notebook_type_ids'])));
                        $cwNbNames = $this->digital_diary_model->getNotebookTypeNamesByIdsOrdered($cwNbIds);
                        if (!empty($cwNbNames)) {
                            $nbTxt = 'NB: ' . implode(', ', $cwNbNames);
                            $classWorkStr = ($classWorkStr === '—') ? $nbTxt : $classWorkStr . ' · ' . $nbTxt;
                        }
                    } elseif (!empty($saved['cw_notebook_type_id']) && $this->db->table_exists('digital_diary_notebook_type')) {
                        $nbRow = $this->db->select('name')->where('id', (int) $saved['cw_notebook_type_id'])->get('digital_diary_notebook_type')->row();
                        if ($nbRow && !empty($nbRow->name)) {
                            $nbTxt = 'NB: ' . $nbRow->name;
                            $classWorkStr = ($classWorkStr === '—') ? $nbTxt : $classWorkStr . ' · ' . $nbTxt;
                        }
                    }
                    $hwItems = $this->digital_diary_model->formatTodayWorkActivityWithDetail(
                        $this->digital_diary_model->formatTodayWorkItemsDisplay(
                            isset($saved['home_work_items']) ? $saved['home_work_items'] : '',
                            $branchID
                        ),
                        isset($saved['hw_detail']) ? $saved['hw_detail'] : ''
                    );
                    $note = isset($saved['home_work_note']) ? trim((string) $saved['home_work_note']) : '';
                    if ($hwItems !== '—' && $note !== '') {
                        $homeWorkStr = $hwItems . ' — ' . $note;
                    } elseif ($note !== '') {
                        $homeWorkStr = $note;
                    } else {
                        $homeWorkStr = $hwItems;
                    }
                    if ($this->db->field_exists('hw_notebook_type_ids', 'digital_diary_today_work') && !empty(trim((string) (isset($saved['hw_notebook_type_ids']) ? $saved['hw_notebook_type_ids'] : '')))) {
                        $hwNbIds = array_filter(array_map('intval', explode(',', $saved['hw_notebook_type_ids'])));
                        $hwNbNames = $this->digital_diary_model->getNotebookTypeNamesByIdsOrdered($hwNbIds);
                        if (!empty($hwNbNames)) {
                            $nbTxt = 'NB: ' . implode(', ', $hwNbNames);
                            $homeWorkStr = ($homeWorkStr === '—') ? $nbTxt : $homeWorkStr . ' · ' . $nbTxt;
                        }
                    } elseif (!empty($saved['hw_notebook_type_id']) && $this->db->table_exists('digital_diary_notebook_type')) {
                        $nbRow = $this->db->select('name')->where('id', (int) $saved['hw_notebook_type_id'])->get('digital_diary_notebook_type')->row();
                        if ($nbRow && !empty($nbRow->name)) {
                            $nbTxt = 'NB: ' . $nbRow->name;
                            $homeWorkStr = ($homeWorkStr === '—') ? $nbTxt : $homeWorkStr . ' · ' . $nbTxt;
                        }
                    }
                    if ($this->db->table_exists('digital_diary_lesson_completion') && $totalLesson > 0) {
                        $pct = $this->digital_diary_model->getLessonSyllabusCompletionPercent(
                            $classID,
                            $sectionID,
                            $sid,
                            $branchID,
                            $reportDate
                        );
                        $sylPct = $pct['syllabus_percentage'];
                    } elseif (!empty($saved['lesson_id'])) {
                        $pct = $this->digital_diary_model->getLessonSyllabusWorkPercent(
                            (int) $saved['lesson_id'],
                            $classID,
                            $sectionID,
                            $sid,
                            $branchID
                        );
                        $sylPct = $pct['syllabus_percentage'];
                    }
                } else {
                    $nowGoing = !empty($todayItem['activity_name']) ? $todayItem['activity_name'] : '—';
                    $classWorkStr = !empty($todayItem['remarks']) ? $todayItem['remarks'] : '—';
                    $homeWorkStr = '—';
                    if ($this->db->table_exists('digital_diary_lesson_completion') && $totalLesson > 0) {
                        $pct = $this->digital_diary_model->getLessonSyllabusCompletionPercent(
                            $classID,
                            $sectionID,
                            $sid,
                            $branchID,
                            $reportDate
                        );
                        $sylPct = $pct['syllabus_percentage'];
                    }
                }

                if ($this->db->table_exists('digital_diary_student_work_report')) {
                    $swrMapForSubject = $this->digital_diary_model->getStudentWorkReportSavedMap(
                        $reportDate,
                        $branchID,
                        $classID,
                        $sectionID,
                        $sid
                    );
                    $workPct = $this->digital_diary_model->getStudentWorkReportWorkPercentageFromSavedMap($swrMapForSubject);
                }

                $learningWorkStr = '—';
                if (!empty($saved) && !empty($saved['id']) && isset($assignSummary[(int) $saved['id']])) {
                    $as = $assignSummary[(int) $saved['id']];
                    $learningWorkStr = ((int) $as['count']) . ' item(s)';
                    if (!empty($as['labels'])) {
                        $learningWorkStr .= ' — ' . $as['labels'];
                    }
                    if (!empty($as['has_test'])) {
                        $learningWorkStr .= ' [Test]';
                    }
                }

                $rows[] = array(
                    'subject_id' => $sid,
                    'subject_name' => isset($subjRow['subject_name']) ? $subjRow['subject_name'] : '',
                    'subject_group' => $groupName,
                    'total_lesson' => $totalLesson,
                    'now_going_on' => $nowGoing !== '' ? $nowGoing : '—',
                    'class_work' => $classWorkStr,
                    'home_work' => $homeWorkStr,
                    'learning_work' => $learningWorkStr,
                    'syllabus_percentage' => $sylPct,
                    'student_work_percentage' => $workPct,
                    'has_work' => !empty($saved),
                );
            }
            $this->data['subject_group_options'] = $subjectGroupOptions;
            $this->data['report_rows'] = $rows;
            $this->data['today_work_saved_json'] = json_encode($savedForJs);
            // Final Submit readiness: ALL class subjects must have Today's Work (class teacher submits to Principal).
            $subjectsNeeding = 0;
            $subjectsFilled = 0;
            $mySubjectsNeeding = 0;
            $mySubjectsFilled = 0;
            foreach ($subjectSummary as $subjRowAll) {
                $sidAll = (int) $subjRowAll['subject_id'];
                $subjectsNeeding++;
                if (isset($savedMap[$sidAll])) {
                    $subjectsFilled++;
                }
                if (!is_teacher_loggedin() || $this->digital_diary_teacher_may_view_subject($classID, $sectionID, $sidAll)) {
                    $mySubjectsNeeding++;
                    if (isset($savedMap[$sidAll])) {
                        $mySubjectsFilled++;
                    }
                }
            }
            $this->data['all_subjects_filled'] = ($subjectsNeeding > 0 && $subjectsFilled >= $subjectsNeeding);
            $this->data['subjects_filled_count'] = $subjectsFilled;
            $this->data['subjects_total_count'] = $subjectsNeeding;
            $this->data['my_subjects_filled_count'] = $mySubjectsFilled;
            $this->data['my_subjects_total_count'] = $mySubjectsNeeding;
        } else {
            $this->data['all_subjects_filled'] = false;
            $this->data['subjects_filled_count'] = 0;
            $this->data['subjects_total_count'] = 0;
            $this->data['my_subjects_filled_count'] = 0;
            $this->data['my_subjects_total_count'] = 0;
        }

        $this->data['is_class_teacher_for_section'] = false;
        $this->data['can_final_submit'] = false;
        $this->data['can_unlock_final_day'] = is_superadmin_loggedin() || is_admin_loggedin();
        if (!empty($classID) && !empty($sectionID)) {
            $this->load->model('subject_model');
            $this->data['is_class_teacher_for_section'] = !is_teacher_loggedin()
                || (bool) $this->subject_model->getClassTeacherByClassSection($classID, $sectionID);
            $this->data['can_final_submit'] = $this->digital_diary_teacher_may_final_submit($classID, $sectionID);
        }

        $this->data['keep_subject_id'] = (int) $this->input->get('keep_subject');

        $this->data['notebook_type_options'] = array('' => translate('select'));
        $this->data['notebook_types_multiselect'] = array();
        if ($this->db->table_exists('digital_diary_notebook_type') && !empty($branchID)) {
            $this->data['notebook_type_options'] = $this->digital_diary_model->getNotebookTypesByBranch($branchID);
            $this->data['notebook_types_multiselect'] = $this->digital_diary_model->getNotebookTypesByBranchMultiselect($branchID);
        }
        $this->data['activity_type_options'] = $this->digital_diary_model->getTodayWorkActivityDropdownOptions($branchID);
        $this->load->model('onlineexam_model');
        $qTypeOpts = array('' => translate('select'));
        foreach ($this->onlineexam_model->question_type() as $tid => $tlab) {
            if ((int) $tid === 4) {
                continue; // legacy descriptive deactivated
            }
            $qTypeOpts[(string) $tid] = $tlab;
        }
        $this->data['learning_question_type_options'] = $qTypeOpts;
        $this->data['title'] = "Today's Work";
        $this->data['sub_page'] = 'digital_diary/today_work';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * AJAX: lessons for class/section/subject (Today's Work dropdown).
     */
    public function ajax_today_work_lessons()
    {
        if (!$this->input->post()) {
            echo json_encode(array('status' => 'fail', 'lessons' => array()));
            return;
        }
        $classID = (int) $this->input->post('class_id');
        $sectionID = (int) $this->input->post('section_id');
        $subjectID = (int) $this->input->post('subject_id');
        if (is_superadmin_loggedin()) {
            $branchID = (int) $this->input->post('branch_id');
        } else {
            $branchID = (int) get_loggedin_branch_id();
        }
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        if (empty($classID) || empty($sectionID) || empty($subjectID)) {
            echo json_encode(array('status' => 'fail', 'lessons' => array()));
            return;
        }
        if (is_teacher_loggedin() && !$this->digital_diary_teacher_may_use_class_section($branchID, $classID, $sectionID)) {
            echo json_encode(array('status' => 'fail', 'lessons' => array()));
            return;
        }
        if (!$this->db->table_exists('digital_diary_lesson')) {
            echo json_encode(array('status' => 'fail', 'lessons' => array()));
            return;
        }
        $lessons = $this->digital_diary_model->getLessonsBySubject($classID, $sectionID, $subjectID, $branchID);
        $completedIds = array();
        if ($this->db->table_exists('digital_diary_lesson_completion')) {
            $completedIds = $this->digital_diary_model->getCompletedLessonIdsForSubject($classID, $sectionID, $subjectID, $branchID);
        }
        $doneSet = array_flip($completedIds);
        $out = array();
        foreach ($lessons as $l) {
            $lid = (int) $l['id'];
            $no = trim((string) $l['lesson_no']);
            $name = trim((string) $l['lesson_name']);
            $label = ($no !== '' ? ($no . '. ') : '') . $name;
            if ($label === '') {
                $label = 'Lesson #' . $lid;
            }
            $out[] = array(
                'id' => $lid,
                'lesson_no' => $l['lesson_no'],
                'lesson_name' => $l['lesson_name'],
                'label' => $label,
                'completed' => isset($doneSet[$lid]),
            );
        }
        echo json_encode(array('status' => 'success', 'lessons' => $out));
    }

    /**
     * AJAX: Question Bank items for Learning work on Today's Work.
     */
    public function ajax_today_work_questions()
    {
        if (!$this->input->post()) {
            echo json_encode(array('status' => 'fail', 'questions' => array()));
            return;
        }
        $this->digital_diary_model->ensureTodayWorkLearningFlowTables();
        $classID = (int) $this->input->post('class_id');
        $sectionID = (int) $this->input->post('section_id');
        $subjectID = (int) $this->input->post('subject_id');
        $branchID = is_superadmin_loggedin() ? (int) $this->input->post('branch_id') : (int) get_loggedin_branch_id();
        if (empty($branchID)) {
            $branchID = (int) $this->application_model->get_branch_id();
        }
        $lessonIds = array();
        $lessonPost = $this->input->post('lesson_ids');
        if (is_array($lessonPost)) {
            foreach ($lessonPost as $lid) {
                $lid = (int) $lid;
                if ($lid > 0) {
                    $lessonIds[] = $lid;
                }
            }
        } elseif (is_string($lessonPost) && $lessonPost !== '') {
            foreach (explode(',', $lessonPost) as $lid) {
                $lid = (int) trim($lid);
                if ($lid > 0) {
                    $lessonIds[] = $lid;
                }
            }
        }
        if (empty($classID) || empty($sectionID) || empty($subjectID)) {
            echo json_encode(array('status' => 'fail', 'questions' => array()));
            return;
        }
        if (is_teacher_loggedin() && !$this->digital_diary_teacher_may_use_class_section($branchID, $classID, $sectionID)) {
            echo json_encode(array('status' => 'fail', 'questions' => array()));
            return;
        }
        $typeId = (int) $this->input->post('question_type');
        $questions = $this->digital_diary_model->getQuestionsForTodayWorkPicker($branchID, $classID, $sectionID, $subjectID, $lessonIds, $typeId);
        echo json_encode(array('status' => 'success', 'questions' => $questions));
    }

    /**
     * AJAX: auto-pick Learning questions by lesson + type + count.
     */
    public function ajax_today_work_auto_questions()
    {
        if (!$this->input->post()) {
            echo json_encode(array('status' => 'fail', 'questions' => array(), 'warning' => 'Invalid request'));
            return;
        }
        $this->digital_diary_model->ensureTodayWorkLearningFlowTables();
        $classID = (int) $this->input->post('class_id');
        $sectionID = (int) $this->input->post('section_id');
        $subjectID = (int) $this->input->post('subject_id');
        $typeId = (int) $this->input->post('question_type');
        $count = (int) $this->input->post('count');
        if ($count < 1) {
            $count = 5;
        }
        $branchID = is_superadmin_loggedin() ? (int) $this->input->post('branch_id') : (int) get_loggedin_branch_id();
        if (empty($branchID)) {
            $branchID = (int) $this->application_model->get_branch_id();
        }
        $lessonIds = array();
        $lessonPost = $this->input->post('lesson_ids');
        if (is_array($lessonPost)) {
            foreach ($lessonPost as $lid) {
                $lid = (int) $lid;
                if ($lid > 0) {
                    $lessonIds[] = $lid;
                }
            }
        } elseif (is_string($lessonPost) && $lessonPost !== '') {
            foreach (explode(',', $lessonPost) as $lid) {
                $lid = (int) trim($lid);
                if ($lid > 0) {
                    $lessonIds[] = $lid;
                }
            }
        }
        if (empty($classID) || empty($sectionID) || empty($subjectID) || $typeId < 1) {
            echo json_encode(array('status' => 'fail', 'questions' => array(), 'warning' => 'Select subject and question type.'));
            return;
        }
        $picked = $this->digital_diary_model->autoPickQuestionsForLearning(
            $branchID,
            $classID,
            $sectionID,
            $subjectID,
            $lessonIds,
            $typeId,
            $count
        );
        echo json_encode(array(
            'status' => 'success',
            'questions' => $picked['questions'],
            'requested' => $picked['requested'],
            'found' => $picked['found'],
            'warning' => $picked['warning'],
        ));
    }

    /**
     * AJAX: save lesson completion after Today's Work save (teacher confirms Yes).
     */
    public function lesson_completion_save()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        if (!$this->db->table_exists('digital_diary_lesson_completion')) {
            echo json_encode(array('status' => 'fail', 'message' => 'Run application/sql/digital_diary_lesson_completion.sql'));
            return;
        }
        $branchID = is_superadmin_loggedin() ? (int) $this->input->post('branch_id') : (int) get_loggedin_branch_id();
        $classID = (int) $this->input->post('class_id');
        $sectionID = (int) $this->input->post('section_id');
        $subjectID = (int) $this->input->post('subject_id');
        $workDate = $this->input->post('work_date');
        if (is_teacher_loggedin() && !$this->digital_diary_teacher_may_use_class_section($branchID, $classID, $sectionID)) {
            echo json_encode(array('status' => 'fail', 'message' => translate('access_denied')));
            return;
        }
        if (empty($workDate) || empty($classID) || empty($sectionID) || empty($subjectID)) {
            echo json_encode(array('status' => 'fail', 'message' => translate('invalid')));
            return;
        }
        if ($this->digital_diary_model->isTodayWorkDayFinal(
            date('Y-m-d', strtotime($workDate)),
            $branchID,
            $classID,
            $sectionID
        )) {
            echo json_encode(array('status' => 'fail', 'message' => 'This day is already final submitted. Teachers cannot edit.'));
            return;
        }
        $lessonIds = array();
        $lessonPost = $this->input->post('lesson_ids');
        if (is_array($lessonPost)) {
            foreach ($lessonPost as $lid) {
                $lid = (int) $lid;
                if ($lid > 0) {
                    $lessonIds[] = $lid;
                }
            }
        } elseif ($lessonPost !== null && $lessonPost !== '') {
            foreach (explode(',', (string) $lessonPost) as $lid) {
                $lid = (int) trim($lid);
                if ($lid > 0) {
                    $lessonIds[] = $lid;
                }
            }
        }
        $lessonIds = array_values(array_unique($lessonIds));
        $this->digital_diary_model->saveLessonCompletions(
            $branchID,
            $classID,
            $sectionID,
            $subjectID,
            $lessonIds,
            (int) get_loggedin_user_id(),
            $workDate
        );

        // Teaching activity per lesson (independent of lesson completion / syllabus %).
        $lessonActivityMap = array();
        $actPost = $this->input->post('lesson_activities');
        if (is_array($actPost)) {
            foreach ($actPost as $lid => $aid) {
                $lid = (int) $lid;
                $aid = (int) $aid;
                if ($lid > 0 && $aid > 0) {
                    $lessonActivityMap[$lid] = $aid;
                }
            }
        }
        if (!empty($lessonActivityMap)) {
            $cwDetail = trim((string) $this->input->post('cw_detail'));
            $this->digital_diary_model->saveLessonTeachingActivities(
                $branchID,
                $classID,
                $sectionID,
                $subjectID,
                $lessonActivityMap,
                (int) get_loggedin_user_id(),
                $workDate,
                $cwDetail
            );
        }

        echo json_encode(array('status' => 'success'));
    }

    /**
     * Save one subject row for Today's Work (AJAX, frm-submit-data).
     */
    public function today_work_save()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        if (!$this->db->table_exists('digital_diary_today_work')) {
            echo json_encode(array('status' => 'fail', 'error' => array('form_error' => 'Run application/sql/digital_diary_today_work.sql')));
            return;
        }
        $this->form_validation->set_rules('work_date', translate('date'), 'trim|required');
        $this->form_validation->set_rules('class_id', translate('class'), 'trim|required|numeric');
        $this->form_validation->set_rules('section_id', translate('section'), 'trim|required|numeric');
        $this->form_validation->set_rules('subject_id', translate('subject'), 'trim|required|numeric');
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required|numeric');
        }
        if ($this->form_validation->run() === false) {
            echo json_encode(array('status' => 'fail', 'error' => $this->form_validation->error_array()));
            return;
        }
        $branchID = is_superadmin_loggedin() ? (int) $this->input->post('branch_id') : (int) get_loggedin_branch_id();
        $classID = (int) $this->input->post('class_id');
        $sectionID = (int) $this->input->post('section_id');
        $subjectID = (int) $this->input->post('subject_id');
        if (is_teacher_loggedin() && !$this->digital_diary_teacher_may_use_class_section($branchID, $classID, $sectionID)) {
            echo json_encode(array('status' => 'fail', 'error' => array('class_id' => translate('access_denied'))));
            return;
        }
        if (is_teacher_loggedin() && !$this->digital_diary_teacher_may_view_subject($classID, $sectionID, $subjectID)) {
            echo json_encode(array('status' => 'fail', 'error' => array('subject_id' => 'You can only save Today\'s Work for your own subject(s).')));
            return;
        }
        if ($this->digital_diary_model->isTodayWorkDayFinal(
            date('Y-m-d', strtotime($this->input->post('work_date'))),
            $branchID,
            $classID,
            $sectionID
        )) {
            echo json_encode(array(
                'status' => 'fail',
                'error' => array('form_error' => 'Final submitted. Teachers and class teachers cannot edit. Contact admin to unlock.'),
            ));
            return;
        }
        if (!$this->digital_diary_today_work_validate_subject($branchID, $classID, $sectionID, $subjectID)) {
            echo json_encode(array('status' => 'fail', 'error' => array('subject_id' => translate('invalid') . ' ' . translate('subject'))));
            return;
        }

        $labels = $this->digital_diary_model->getTodayWorkTypeLabels();
        $useMasterActivities = $this->digital_diary_model->branchHasActiveTeachingActivities($branchID);
        $cwKeys = array();
        $cw = $this->input->post('cw');
        if (is_array($cw)) {
            $cw = reset($cw);
        }
        $cw = trim((string) $cw);
        if ($cw !== '') {
            if ($useMasterActivities) {
                $cwId = (int) $cw;
                if ($cwId > 0) {
                    $this->db->where('id', $cwId);
                    $this->db->where('branch_id', (int) $branchID);
                    $this->db->where('is_active', 1);
                    if ($this->db->get('digital_diary_teaching_activity', 1)->num_rows() === 0) {
                        echo json_encode(array('status' => 'fail', 'error' => array('cw' => translate('invalid') . ' activity type')));
                        return;
                    }
                    $cwKeys[] = (string) $cwId;
                }
            } elseif (isset($labels[$cw])) {
                $cwKeys[] = $cw;
            }
        }
        $hwKeys = array();
        $hw = $this->input->post('hw');
        if (is_array($hw)) {
            $hw = reset($hw);
        }
        $hw = trim((string) $hw);
        if ($hw !== '') {
            if ($useMasterActivities) {
                $hwId = (int) $hw;
                if ($hwId > 0) {
                    $this->db->where('id', $hwId);
                    $this->db->where('branch_id', (int) $branchID);
                    $this->db->where('is_active', 1);
                    if ($this->db->get('digital_diary_teaching_activity', 1)->num_rows() === 0) {
                        echo json_encode(array('status' => 'fail', 'error' => array('hw' => translate('invalid') . ' activity type')));
                        return;
                    }
                    $hwKeys[] = (string) $hwId;
                }
            } elseif (isset($labels[$hw])) {
                $hwKeys[] = $hw;
            }
        }

        $lessonPost = $this->input->post('lesson_id');
        $lessonIds = array();
        if (is_array($lessonPost)) {
            foreach ($lessonPost as $lid) {
                $lid = (int) $lid;
                if ($lid > 0) {
                    $lessonIds[] = $lid;
                }
            }
        } elseif (!empty($lessonPost)) {
            $lessonIds[] = (int) $lessonPost;
        }
        $lessonIds = array_values(array_unique($lessonIds));
        $lessonIdsCsv = empty($lessonIds) ? null : implode(',', $lessonIds);
        $primaryLessonId = null;
        if (!empty($lessonIds)) {
            $this->db->where_in('id', $lessonIds);
            $this->db->where('class_id', $classID);
            $this->db->where('section_id', $sectionID);
            $this->db->where('subject_id', $subjectID);
            if (!empty($branchID)) {
                $this->db->where('branch_id', $branchID);
            }
            $lessonOk = $this->db->get('digital_diary_lesson')->num_rows();
            if ($lessonOk !== count($lessonIds)) {
                echo json_encode(array('status' => 'fail', 'error' => array('lesson_id' => translate('invalid') . ' lesson')));
                return;
            }
            if ($this->db->field_exists('lesson_ids', 'digital_diary_today_work')) {
                $primaryLessonId = $this->digital_diary_model->pickPrimaryLessonIdFromLessonIds($lessonIdsCsv, $classID, $sectionID, $subjectID, $branchID);
            } else {
                $primaryLessonId = (int) $lessonIds[count($lessonIds) - 1];
            }
        }

        $post = array(
            'branch_id' => $branchID,
            'session_id' => (int) get_session_id(),
            'staff_id' => (int) get_loggedin_user_id(),
            'work_date' => date('Y-m-d', strtotime($this->input->post('work_date'))),
            'class_id' => $classID,
            'section_id' => $sectionID,
            'subject_id' => $subjectID,
            'lesson_id' => $primaryLessonId,
            'lesson_ids' => $lessonIdsCsv,
            'class_work_items' => empty($cwKeys) ? null : implode(',', $cwKeys),
            'home_work_items' => empty($hwKeys) ? null : implode(',', $hwKeys),
            'home_work_note' => $this->input->post('home_work_note'),
            'cw_detail' => $this->input->post('cw_detail'),
            'hw_detail' => $this->input->post('hw_detail'),
            'cw_notebook_type_ids' => $this->input->post('cw_notebook_type_ids'),
            'hw_notebook_type_ids' => $this->input->post('hw_notebook_type_ids'),
            'learning_question_ids' => $this->input->post('learning_question_ids'),
            'learning_is_test' => (int) $this->input->post('learning_is_test'),
            'learning_qtype' => (int) $this->input->post('learning_qtype'),
            'learning_qcount' => (int) $this->input->post('learning_qcount'),
        );
        $uploadDir = FCPATH . 'uploads/attachments/digital_diary_today_work/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }
        foreach (array('cw_attachment' => 'cw_attachment', 'hw_attachment' => 'hw_attachment') as $field => $fileKey) {
            if (!empty($_FILES[$fileKey]['name'])) {
                $ext = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
                $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf');
                if (in_array($ext, $allowed, true) && is_uploaded_file($_FILES[$fileKey]['tmp_name'])) {
                    $fname = $field . '_' . date('YmdHis') . '_' . mt_rand(1000, 9999) . '.' . $ext;
                    if (@move_uploaded_file($_FILES[$fileKey]['tmp_name'], $uploadDir . $fname)) {
                        $post[$field] = $fname;
                    }
                }
            }
        }
        $this->digital_diary_model->ensureTodayWorkLearningFlowTables();
        $todayWorkId = $this->digital_diary_model->saveTodayWorkEntry($post);
        $subjName = get_type_name_by_id('subject', $subjectID);
        $roleNeeds = $this->digital_diary_model->todayWorkDiaryRoleNeeds(
            empty($cwKeys) ? null : implode(',', $cwKeys),
            empty($hwKeys) ? null : implode(',', $hwKeys)
        );
        if (!empty($roleNeeds['any'])) {
            $actAlertIds = array();
            foreach (array_merge($cwKeys, $hwKeys) as $k) {
                if (ctype_digit((string) $k) && (int) $k > 0) {
                    $actAlertIds[] = (int) $k;
                }
            }
            $this->digital_diary_model->addCoordinatorAlertsForActivities(array(
                'branch_id' => $branchID,
                'work_date' => $post['work_date'],
                'class_id' => $classID,
                'section_id' => $sectionID,
                'event_type' => 'today_work_saved',
                'message' => 'Today\'s Work saved for ' . $subjName . ' on ' . $post['work_date'],
                'ref_id' => is_numeric($todayWorkId) ? (int) $todayWorkId : 0,
            ), $actAlertIds);
        }
        set_alert('success', translate('information_has_been_saved_successfully'));
        $q = array(
            'date' => $post['work_date'],
            'class_id' => $classID,
            'section_id' => $sectionID,
        );
        if (is_superadmin_loggedin() && !empty($branchID)) {
            $q['branch_id'] = $branchID;
        }
        $filterSubj = (int) $this->input->post('filter_subject_id');
        if ($filterSubj > 0) {
            $q['subject_id'] = $filterSubj;
        }
        $sg = trim((string) $this->input->post('filter_subject_group'));
        if ($sg !== '') {
            $q['subject_group'] = $sg;
        }
        $lessonCompletionOptions = array();
        if (!empty($lessonIds) && $this->db->table_exists('digital_diary_lesson')) {
            foreach ($lessonIds as $lid) {
                $lid = (int) $lid;
                $this->db->select('lesson_no, lesson_name');
                $this->db->from('digital_diary_lesson');
                $this->db->where('id', $lid);
                $lr = $this->db->get()->row();
                if ($lr) {
                    $lessonCompletionOptions[] = array(
                        'id' => $lid,
                        'label' => 'Lesson - ' . $lr->lesson_no . ', ' . $lr->lesson_name,
                    );
                }
            }
        }
        // Completion prompt is for class work / home work lessons (not learning assignments alone).
        $hasClassOrHomeWork = !empty($cwKeys) || !empty($hwKeys)
            || trim((string) $this->input->post('cw_detail')) !== ''
            || trim((string) $this->input->post('hw_detail')) !== ''
            || trim((string) $this->input->post('home_work_note')) !== '';
        $cwLabelParts = array();
        $hwLabelParts = array();
        if (!empty($cwKeys)) {
            $cwLabelParts[] = $this->digital_diary_model->formatTodayWorkItemsDisplay(implode(',', $cwKeys), $branchID);
        }
        $cwDetail = trim((string) $this->input->post('cw_detail'));
        if ($cwDetail !== '') {
            $cwLabelParts[] = $cwDetail;
        }
        if (!empty($hwKeys)) {
            $hwLabelParts[] = $this->digital_diary_model->formatTodayWorkItemsDisplay(implode(',', $hwKeys), $branchID);
        }
        $hwDetail = trim((string) $this->input->post('hw_detail'));
        if ($hwDetail !== '') {
            $hwLabelParts[] = $hwDetail;
        }
        $hwNote = trim((string) $this->input->post('home_work_note'));
        if ($hwNote !== '') {
            $hwLabelParts[] = $hwNote;
        }
        $savedJsEntry = array(
            'lesson_id' => $primaryLessonId ? (int) $primaryLessonId : '',
            'lesson_ids' => $lessonIdsCsv ? $lessonIdsCsv : '',
            'class_work_items' => isset($post['class_work_items']) ? $post['class_work_items'] : '',
            'home_work_items' => isset($post['home_work_items']) ? $post['home_work_items'] : '',
            'home_work_note' => isset($post['home_work_note']) ? $post['home_work_note'] : '',
            'cw_detail' => isset($post['cw_detail']) ? $post['cw_detail'] : '',
            'hw_detail' => isset($post['hw_detail']) ? $post['hw_detail'] : '',
            'cw_attachment' => isset($post['cw_attachment']) ? $post['cw_attachment'] : '',
            'hw_attachment' => isset($post['hw_attachment']) ? $post['hw_attachment'] : '',
            'learning_qtype' => !empty($post['learning_qtype']) ? (int) $post['learning_qtype'] : '',
            'learning_qcount' => !empty($post['learning_qcount']) ? (int) $post['learning_qcount'] : '',
            'cw_notebook_type_ids' => is_array($this->input->post('cw_notebook_type_ids')) ? implode(',', array_map('intval', $this->input->post('cw_notebook_type_ids'))) : '',
            'hw_notebook_type_ids' => is_array($this->input->post('hw_notebook_type_ids')) ? implode(',', array_map('intval', $this->input->post('hw_notebook_type_ids'))) : '',
            'learning_question_ids' => is_array($this->input->post('learning_question_ids')) ? implode(',', array_map('intval', $this->input->post('learning_question_ids'))) : '',
            'learning_is_test' => (int) $this->input->post('learning_is_test'),
        );
        $cwActivityId = (!empty($cwKeys) && $useMasterActivities && ctype_digit((string) $cwKeys[0]))
            ? (int) $cwKeys[0]
            : 0;
        $teachingActivityOptions = array();
        if ($useMasterActivities) {
            foreach ($this->digital_diary_model->getActiveTeachingActivitiesForBranch($branchID) as $a) {
                $teachingActivityOptions[] = array(
                    'id' => (int) $a['id'],
                    'name' => isset($a['name']) ? $a['name'] : '',
                    'enable_coordinator' => isset($a['enable_coordinator']) ? (int) $a['enable_coordinator'] : 1,
                );
            }
        }
        echo json_encode(array(
            'status' => 'success',
            'url' => base_url('digital_diary/today_work') . '?' . http_build_query($q),
            'message' => translate('information_has_been_saved_successfully'),
            'prompt_lesson_completion' => !empty($lessonIds) && $hasClassOrHomeWork,
            'lesson_ids' => $lessonIds,
            'lesson_completion_options' => $lessonCompletionOptions,
            'class_work_label' => implode(' — ', array_filter($cwLabelParts)),
            'home_work_label' => implode(' — ', array_filter($hwLabelParts)),
            'cw_activity_id' => $cwActivityId,
            'ask_teaching_activity' => $useMasterActivities && !empty($teachingActivityOptions),
            'teaching_activity_options' => $teachingActivityOptions,
            'keep_form' => true,
            'subject_id' => $subjectID,
            'saved_entry' => $savedJsEntry,
        ));
    }

    /**
     * Final submit for the selected date / class / section.
     */
    public function today_work_final_submit()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        if (!$this->db->table_exists('digital_diary_today_work_day')) {
            echo json_encode(array('status' => 'fail', 'error' => array('form_error' => 'Run application/sql/digital_diary_today_work.sql')));
            return;
        }
        $this->form_validation->set_rules('work_date', translate('date'), 'trim|required');
        $this->form_validation->set_rules('class_id', translate('class'), 'trim|required|numeric');
        $this->form_validation->set_rules('section_id', translate('section'), 'trim|required|numeric');
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required|numeric');
        }
        if ($this->form_validation->run() === false) {
            echo json_encode(array('status' => 'fail', 'error' => $this->form_validation->error_array()));
            return;
        }
        $branchID = is_superadmin_loggedin() ? (int) $this->input->post('branch_id') : (int) get_loggedin_branch_id();
        $classID = (int) $this->input->post('class_id');
        $sectionID = (int) $this->input->post('section_id');
        $workDate = date('Y-m-d', strtotime($this->input->post('work_date')));
        if (is_teacher_loggedin() && !$this->digital_diary_teacher_may_use_class_section($branchID, $classID, $sectionID)) {
            echo json_encode(array('status' => 'fail', 'error' => array('class_id' => translate('access_denied'))));
            return;
        }
        if (!$this->digital_diary_teacher_may_final_submit($classID, $sectionID)) {
            echo json_encode(array(
                'status' => 'fail',
                'error' => array('form_error' => 'Only the class teacher can Final Submit to Principal. Subject teachers submit their own subject work first.'),
            ));
            return;
        }
        $subjectSummary = $this->digital_diary_model->getLessonSummaryByClassSection($classID, $sectionID, $branchID);
        $savedMap = $this->digital_diary_model->getTodayWorkMapByClassSectionDate($workDate, $branchID, $classID, $sectionID);
        $need = 0;
        $have = 0;
        foreach ($subjectSummary as $subjRowAll) {
            $sidAll = (int) $subjRowAll['subject_id'];
            $need++;
            if (isset($savedMap[$sidAll])) {
                $have++;
            }
        }
        if ($need === 0 || $have < $need) {
            echo json_encode(array(
                'status' => 'fail',
                'error' => array('form_error' => 'All subject teachers must submit Today\'s Work first (' . $have . '/' . $need . '). Then class teacher can Final Submit to Principal.'),
            ));
            return;
        }
        $this->digital_diary_model->setTodayWorkDayFinal($workDate, $branchID, $classID, $sectionID, (int) get_loggedin_user_id());
        $this->digital_diary_model->ensureTodayWorkLearningFlowTables();
        $className = get_type_name_by_id('class', $classID);
        $sectionName = get_type_name_by_id('section', $sectionID);
        $dayActIds = $this->digital_diary_model->getTodayWorkActivityIdsForClassDate($workDate, $branchID, $classID, $sectionID);
        $this->digital_diary_model->addCoordinatorAlertsForActivities(array(
            'branch_id' => $branchID,
            'work_date' => $workDate,
            'class_id' => $classID,
            'section_id' => $sectionID,
            'event_type' => 'final_submit',
            'message' => 'Today\'s Work final submitted for ' . $className . '-' . $sectionName . ' on ' . $workDate,
            'ref_id' => 0,
        ), $dayActIds);
        $this->digital_diary_model->addCoordinatorAlertsForActivities(array(
            'branch_id' => $branchID,
            'work_date' => $workDate,
            'class_id' => $classID,
            'section_id' => $sectionID,
            'event_type' => 'principal_report',
            'message' => 'Today\'s Work / Student report sent to Principal for ' . $className . '-' . $sectionName . ' on ' . $workDate,
            'ref_id' => 0,
        ), $dayActIds);
        set_alert('success', 'Final Submit done. Report has been sent to Principal.');
        $q = array('date' => $workDate, 'class_id' => $classID, 'section_id' => $sectionID);
        if (is_superadmin_loggedin() && !empty($branchID)) {
            $q['branch_id'] = $branchID;
        }
        echo json_encode(array(
            'status' => 'success',
            'url' => base_url('digital_diary/today_work') . '?' . http_build_query($q),
            'message' => translate('information_has_been_saved_successfully'),
        ));
    }

    /**
     * Undo final submit for the selected date / class / section (unlock Today's Work & Student Work Report for that day).
     * Uses normal form POST + redirect (not AJAX) so CSRF and flash messages work reliably.
     */
    public function today_work_final_revert()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $backUrl = $this->student_work_report_entry_url_from_post();
        if (!$this->db->table_exists('digital_diary_today_work_day')) {
            set_alert('error', 'Run application/sql/digital_diary_today_work.sql');
            redirect($backUrl);
            return;
        }
        $this->form_validation->set_rules('work_date', translate('date'), 'trim|required');
        $this->form_validation->set_rules('class_id', translate('class'), 'trim|required|numeric');
        $this->form_validation->set_rules('section_id', translate('section'), 'trim|required|numeric');
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required|numeric');
        }
        if ($this->form_validation->run() === false) {
            set_alert('error', strip_tags(validation_errors()));
            redirect($backUrl);
            return;
        }
        $branchID = is_superadmin_loggedin() ? (int) $this->input->post('branch_id') : (int) get_loggedin_branch_id();
        $classID = (int) $this->input->post('class_id');
        $sectionID = (int) $this->input->post('section_id');
        $workDate = date('Y-m-d', strtotime($this->input->post('work_date')));
        // After Final Submit, teachers / class teachers cannot unlock or edit — admin only.
        if (is_teacher_loggedin() || (!is_superadmin_loggedin() && !is_admin_loggedin())) {
            set_alert('error', 'Final submitted. Teachers and class teachers cannot unlock or edit. Contact admin.');
            redirect($backUrl);
            return;
        }
        $this->digital_diary_model->clearTodayWorkDayFinal($workDate, $branchID, $classID, $sectionID);
        set_alert('success', 'This day has been unlocked for editing.');
        $q = array('date' => $workDate, 'class_id' => $classID, 'section_id' => $sectionID);
        if (is_superadmin_loggedin() && !empty($branchID)) {
            $q['branch_id'] = $branchID;
        }
        redirect(base_url('digital_diary/student_work_report_entry') . '?' . http_build_query($q));
    }

    /**
     * Build student_work_report_entry URL from POST fields (for redirects after unlock errors).
     *
     * @return string
     */
    protected function student_work_report_entry_url_from_post()
    {
        $q = array();
        $wd = $this->input->post('work_date');
        if ($wd !== null && $wd !== '') {
            $q['date'] = date('Y-m-d', strtotime($wd));
        }
        $cid = (int) $this->input->post('class_id');
        $sid = (int) $this->input->post('section_id');
        if ($cid > 0) {
            $q['class_id'] = $cid;
        }
        if ($sid > 0) {
            $q['section_id'] = $sid;
        }
        if (is_superadmin_loggedin()) {
            $bid = (int) $this->input->post('branch_id');
            if ($bid > 0) {
                $q['branch_id'] = $bid;
            }
        }
        $url = base_url('digital_diary/student_work_report_entry');
        return $q ? ($url . '?' . http_build_query($q)) : $url;
    }

    /**
     * Subject must be valid for class/section (and teacher assign / timetable when teacher).
     *
     * @return bool
     */
    protected function digital_diary_today_work_validate_subject($branchID, $classID, $sectionID, $subjectID)
    {
        if (empty($subjectID)) {
            return false;
        }
        $subjectValid = false;
        $this->db->where(array('class_id' => (int) $classID, 'section_id' => (int) $sectionID, 'subject_id' => (int) $subjectID));
        if (!empty($branchID)) {
            $this->db->where('branch_id', (int) $branchID);
        }
        if ($this->db->get('subject_assign')->num_rows()) {
            $subjectValid = true;
        }
        if (!$subjectValid && is_teacher_loggedin()) {
            $this->db->where(array(
                'class_id' => (int) $classID,
                'section_id' => (int) $sectionID,
                'subject_id' => (int) $subjectID,
                'teacher_id' => (int) get_loggedin_user_id(),
                'session_id' => (int) get_session_id(),
            ));
            if ($this->db->get('timetable_class')->num_rows()) {
                $subjectValid = true;
            }
        }
        return $subjectValid;
    }

    /**
     * Today's Work Report - content as per PDF spec
     */
    public function todays_work_report()
    {
        $branchID = $this->input->get('branch_id');
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }

        $this->data['branch_id'] = $branchID;
        $this->data['branch_name'] = !empty($branchID) ? get_type_name_by_id('branch', $branchID) : '';

        $reportDate = $this->input->get('date');
        if (empty($reportDate)) {
            $reportDate = date('Y-m-d');
        } else {
            $reportDate = date('Y-m-d', strtotime($reportDate));
        }
        $classID = (int) $this->input->get('class_id');
        $sectionID = (int) $this->input->get('section_id');
        $subjectID = (int) $this->input->get('subject_id');

        $this->data['today_date'] = $reportDate;
        $this->data['filter_class_id'] = $classID;
        $this->data['filter_section_id'] = $sectionID;
        $this->data['filter_subject_id'] = $subjectID;
        $this->digital_diary_populate_class_section_filters($branchID, $classID);
        $this->data['report_rows'] = array();

        if (!empty($classID) && !empty($sectionID)) {
            if (!is_teacher_loggedin() || $this->digital_diary_teacher_may_use_class_section($branchID, $classID, $sectionID)) {
                $this->data['report_rows'] = $this->digital_diary_build_todays_work_report_rows($branchID, $reportDate, $classID, $sectionID, $subjectID);
            }
        }

        $this->data['title'] = "Today's Work Report";
        $this->data['sub_page'] = 'digital_diary/todays_work_report';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Syllabus Report - content as per PDF spec
     */
    public function syllabus_report()
    {
        $branchID = $this->input->get('branch_id');
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }

        $this->data['branch_id'] = $branchID;
        $this->data['branch_name'] = !empty($branchID) ? get_type_name_by_id('branch', $branchID) : '';

        $todayDate = $this->input->get('date');
        if (empty($todayDate)) {
            $todayDate = date('Y-m-d');
        } else {
            $todayDate = date('Y-m-d', strtotime($todayDate));
        }
        $this->data['today_date'] = $todayDate;

        $mode = $this->input->get('mode');
        if (!in_array($mode, array('class', 'teacher', 'subject'), true)) {
            $mode = is_teacher_loggedin() ? 'teacher' : 'class';
        }
        $this->data['syllabus_mode'] = $mode;
        $teacherFilter = trim((string) $this->input->get('teacher'));
        if (is_teacher_loggedin()) {
            $teacherFilter = get_type_name_by_id('staff', get_loggedin_user_id());
        }
        $this->data['teacher_filter'] = $teacherFilter;

        $this->load->model('subject_model');
        $classes = $this->digital_diary_teacher_allowed_class_map($branchID);
        $classRows = array();
        $winnerClass = '';
        $winnerClassOverall = null;

        foreach ($classes as $cid => $cname) {
            $cid = (int) $cid;
            if ($cid <= 0) {
                continue;
            }
            $sections = $this->app_lib->getSections($cid, false);
            unset($sections['']);
            foreach ($sections as $sid => $sname) {
                $sid = (int) $sid;
                if ($sid <= 0) {
                    continue;
                }
                if (is_teacher_loggedin() && !$this->digital_diary_teacher_may_use_class_section($branchID, $cid, $sid)) {
                    continue;
                }

                $assignPctAgg = array();
                $workPctAgg = array();
                $sylPctAgg = array();

                $subSummary = $this->digital_diary_model->getLessonSummaryByClassSection($cid, $sid, $branchID);
                foreach ($subSummary as $subjRow) {
                    $sidSub = (int) $subjRow['subject_id'];
                    $lpct = $this->digital_diary_model->getLessonSyllabusCompletionPercent($cid, $sid, $sidSub, $branchID, $todayDate);
                    if ($lpct['syllabus_percentage'] !== '—') {
                        $sylPctAgg[] = (int) $lpct['syllabus_percentage'];
                    }
                }

                // Work % from Today's Work Report data (already SWR-based)
                $subjectSummary = $this->digital_diary_model->getLessonSummaryByClassSection($cid, $sid, $branchID);
                $activityMap = $this->digital_diary_model->getTodayTeachingActivityMapBySubject($todayDate, $branchID, $cid, $sid, 0);
                $savedMap = $this->db->table_exists('digital_diary_today_work')
                    ? $this->digital_diary_model->getTodayWorkMapByClassSectionDate($todayDate, $branchID, $cid, $sid)
                    : array();
                foreach ($subjectSummary as $subjRow) {
                    $subId = (int) $subjRow['subject_id'];
                    $todayItem = isset($activityMap[$subId]) ? $activityMap[$subId] : array();
                    $saved = isset($savedMap[$subId]) ? $savedMap[$subId] : null;
                    $workPct = '—';
                    if ($this->db->table_exists('digital_diary_student_work_report')) {
                        $swrMapForSubject = $this->digital_diary_model->getStudentWorkReportSavedMap(
                            $todayDate,
                            $branchID,
                            $cid,
                            $sid,
                            $subId
                        );
                        $workPct = $this->digital_diary_model->getStudentWorkReportWorkPercentageFromSavedMap($swrMapForSubject);
                    }
                    if ($workPct !== '—') {
                        $workPctAgg[] = (int) $workPct;
                    }
                    if (!empty($saved)) {
                        $assignPctAgg[] = 100;
                    } elseif (!empty($todayItem)) {
                        $assignPctAgg[] = 50;
                    }
                }

                $assignAvg = !empty($assignPctAgg) ? (int) round(array_sum($assignPctAgg) / count($assignPctAgg)) : 0;
                $workAvg = !empty($workPctAgg) ? (int) round(array_sum($workPctAgg) / count($workPctAgg)) : 0;
                $sylAvg = !empty($sylPctAgg) ? (int) round(array_sum($sylPctAgg) / count($sylPctAgg)) : 0;
                $overall = (int) round(($assignAvg + $workAvg + $sylAvg) / 3);

                $teacherName = $this->subject_model->getClassTeacherNamesByClassSection($cid, $sid);
                $classRows[] = array(
                    'class_id' => $cid,
                    'section_id' => $sid,
                    'class_name' => $cname . ' (' . $sname . ')',
                    'class_teacher' => $teacherName,
                    'assign_percent' => $assignAvg,
                    'work_percent' => $workAvg,
                    'syllabus_percent' => $sylAvg,
                    'overall_percent' => $overall,
                );

                if ($winnerClassOverall === null || $overall > $winnerClassOverall) {
                    $winnerClassOverall = $overall;
                    $winnerClass = $cname . ' (' . $sname . ')';
                }
            }
        }

        // Rank by overall percent desc
        usort($classRows, function ($a, $b) {
            return $b['overall_percent'] <=> $a['overall_percent'];
        });
        $rank = 1;
        foreach ($classRows as &$row) {
            $row['rank'] = $rank++;
        }
        unset($row);

        $this->data['class_rows'] = $classRows;
        $this->data['today_winner_class'] = $winnerClass;

        // Teacher wise: aggregate class rows by class_teacher when available.
        $teacherRows = array();
        foreach ($classRows as $row) {
            $teacherName = trim((string) $row['class_teacher']);
            if ($teacherName === '') {
                continue;
            }
            if (!isset($teacherRows[$teacherName])) {
                $teacherRows[$teacherName] = array(
                    'teacher_id' => 0,
                    'teacher' => $teacherName,
                    'classes' => array(),
                    'assign_sum' => 0,
                    'assign_n' => 0,
                    'work_sum' => 0,
                    'work_n' => 0,
                    'syl_sum' => 0,
                    'syl_n' => 0,
                );
            }
            $teacherRows[$teacherName]['classes'][] = $row['class_name'];
            $teacherRows[$teacherName]['assign_sum'] += (int) $row['assign_percent'];
            $teacherRows[$teacherName]['assign_n']++;
            $teacherRows[$teacherName]['work_sum'] += (int) $row['work_percent'];
            $teacherRows[$teacherName]['work_n']++;
            $teacherRows[$teacherName]['syl_sum'] += (int) $row['syllabus_percent'];
            $teacherRows[$teacherName]['syl_n']++;
        }

        // Map teacher names to staff IDs for this branch (to preselect in detail view).
        $teacherIdMap = array();
        if (!empty($branchID)) {
            $this->db->select('id, name');
            $this->db->from('staff');
            $this->db->where('branch_id', (int) $branchID);
            $staffRows = $this->db->get()->result_array();
            foreach ($staffRows as $sr) {
                if (!empty($sr['name'])) {
                    $teacherIdMap[trim((string) $sr['name'])] = (int) $sr['id'];
                }
            }
        }
        foreach ($teacherRows as $name => &$t) {
            if (empty($t['teacher_id']) && isset($teacherIdMap[$name])) {
                $t['teacher_id'] = $teacherIdMap[$name];
            }
        }
        unset($t);
        $teacherList = array();
        $winnerTeacher = '';
        $winnerTeacherOverall = null;
        foreach ($teacherRows as $t) {
            $assignAvg = $t['assign_n'] ? (int) round($t['assign_sum'] / $t['assign_n']) : 0;
            $workAvg = $t['work_n'] ? (int) round($t['work_sum'] / $t['work_n']) : 0;
            $sylAvg = $t['syl_n'] ? (int) round($t['syl_sum'] / $t['syl_n']) : 0;
            $overall = (int) round(($assignAvg + $workAvg + $sylAvg) / 3);
            $teacherList[] = array(
                'teacher_id' => isset($t['teacher_id']) ? (int) $t['teacher_id'] : 0,
                'teacher' => $t['teacher'],
                'classes' => implode(', ', $t['classes']),
                'assign_percent' => $assignAvg,
                'work_percent' => $workAvg,
                'syllabus_percent' => $sylAvg,
                'overall_percent' => $overall,
            );
            if ($winnerTeacherOverall === null || $overall > $winnerTeacherOverall) {
                $winnerTeacherOverall = $overall;
                $winnerTeacher = $t['teacher'];
            }
        }
        usort($teacherList, function ($a, $b) {
            return $b['overall_percent'] <=> $a['overall_percent'];
        });
        $trank = 1;
        foreach ($teacherList as &$trow) {
            $trow['rank'] = $trank++;
        }
        unset($trow);
        if ($teacherFilter !== '') {
            $filtered = array();
            foreach ($teacherList as $trow) {
                if (strcasecmp($trow['teacher'], $teacherFilter) === 0) {
                    $filtered[] = $trow;
                }
            }
            if (!empty($filtered)) {
                $teacherList = $filtered;
            }
        }
        $this->data['teacher_rows'] = $teacherList;

        // Subject wise: per class/section/subject rows.
        $subjectRows = array();
        $winnerSubject = '';
        $winnerSubjectOverall = null;
        foreach ($classes as $cid => $cname) {
            $cid = (int) $cid;
            $sections = $this->app_lib->getSections($cid, false);
            unset($sections['']);
            foreach ($sections as $sid => $sname) {
                $sid = (int) $sid;
                if (is_teacher_loggedin() && !$this->digital_diary_teacher_may_use_class_section($branchID, $cid, $sid)) {
                    continue;
                }
                $subSummary = $this->digital_diary_model->getLessonSummaryByClassSection($cid, $sid, $branchID);
                foreach ($subSummary as $subjRow) {
                    $subId = (int) $subjRow['subject_id'];
                    if (is_teacher_loggedin() && !$this->digital_diary_teacher_may_view_subject($cid, $sid, $subId)) {
                        continue;
                    }
                    $lpct = $this->digital_diary_model->getLessonSyllabusCompletionPercent($cid, $sid, $subId, $branchID, $todayDate);
                    $syl = $lpct['syllabus_percentage'] !== '—' ? (int) $lpct['syllabus_percentage'] : 0;
                    $workPct = 0;
                    if ($this->db->table_exists('digital_diary_student_work_report')) {
                        $swrMapForSubject = $this->digital_diary_model->getStudentWorkReportSavedMap(
                            $todayDate,
                            $branchID,
                            $cid,
                            $sid,
                            $subId
                        );
                        $wp = $this->digital_diary_model->getStudentWorkReportWorkPercentageFromSavedMap($swrMapForSubject);
                        if ($wp !== '—') {
                            $workPct = (int) $wp;
                        }
                    }
                    $assignPct = 0;
                    $todayMap = $this->digital_diary_model->getTodayTeachingActivityMapBySubject($todayDate, $branchID, $cid, $sid, $subId);
                    $savedMap = $this->db->table_exists('digital_diary_today_work')
                        ? $this->digital_diary_model->getTodayWorkMapByClassSectionDate($todayDate, $branchID, $cid, $sid)
                        : array();
                    $saved = isset($savedMap[$subId]) ? $savedMap[$subId] : null;
                    $todayItem = isset($todayMap[$subId]) ? $todayMap[$subId] : array();
                    if (!empty($saved)) {
                        $assignPct = 100;
                    } elseif (!empty($todayItem)) {
                        $assignPct = 50;
                    }
                    $overall = (int) round(($assignPct + $workPct + $syl) / 3);
                    $subjectRows[] = array(
                        'class_name' => $cname . ' (' . $sname . ')',
                        'class_id' => $cid,
                        'section_id' => $sid,
                        'subject_name' => isset($subjRow['subject_name']) ? $subjRow['subject_name'] : '',
                        'assign_percent' => $assignPct,
                        'work_percent' => $workPct,
                        'syllabus_percent' => $syl,
                        'overall_percent' => $overall,
                    );
                    if ($winnerSubjectOverall === null || $overall > $winnerSubjectOverall) {
                        $winnerSubjectOverall = $overall;
                        $winnerSubject = (isset($subjRow['subject_name']) ? $subjRow['subject_name'] : '') . ' - ' . $cname;
                    }
                }
            }
        }
        usort($subjectRows, function ($a, $b) {
            return $b['overall_percent'] <=> $a['overall_percent'];
        });
        $srank = 1;
        foreach ($subjectRows as &$srow) {
            $srow['rank'] = $srank++;
        }
        unset($srow);
        $this->data['subject_rows'] = $subjectRows;
        $this->data['winner_class_name'] = $winnerClass;
        $this->data['winner_teacher_name'] = $winnerTeacher;
        $this->data['winner_subject_name'] = $winnerSubject;

        if ($mode === 'teacher' && $teacherFilter !== '') {
            $this->data['title'] = 'Syllabus Report of ' . $teacherFilter;
        } else {
            $this->data['title'] = 'Syllabus Report';
        }
        $this->data['sub_page'] = 'digital_diary/syllabus_report';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Detailed Syllabus Report for a single class/section over a date range.
     */
    public function syllabus_report_class_detail()
    {
        $branchID = $this->input->get('branch_id');
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }

        $from = $this->input->get('from');
        $to = $this->input->get('to');
        if (empty($from)) {
            $from = date('Y-m-d');
        } else {
            $from = date('Y-m-d', strtotime($from));
        }
        if (empty($to)) {
            $to = $from;
        } else {
            $to = date('Y-m-d', strtotime($to));
        }

        $classID = (int) $this->input->get('class_id');
        $sectionID = (int) $this->input->get('section_id');

        $this->data['branch_id'] = $branchID;
        $this->data['from_date'] = $from;
        $this->data['to_date'] = $to;
        $this->data['class_id'] = $classID;
        $this->data['section_id'] = $sectionID;
        $this->digital_diary_populate_class_section_filters($branchID, $classID);

        $subjects = array();
        $rowsByDate = array();
        $todayPerformance = '—';

        if (!empty($classID) && !empty($sectionID)) {
            if (is_teacher_loggedin() && !$this->digital_diary_teacher_may_use_class_section($branchID, $classID, $sectionID)) {
                access_denied();
            }
            $subjectSummary = $this->digital_diary_model->getLessonSummaryByClassSection($classID, $sectionID, $branchID);
            foreach ($subjectSummary as $subjRow) {
                $sid = (int) $subjRow['subject_id'];
                if (is_teacher_loggedin() && !$this->digital_diary_teacher_may_view_subject($classID, $sectionID, $sid)) {
                    continue;
                }
                $lpct = $this->digital_diary_model->getLessonSyllabusCompletionPercent($classID, $sectionID, $sid, $branchID, $to);
                $subjects[] = array(
                    'subject_id' => $sid,
                    'subject_name' => isset($subjRow['subject_name']) ? $subjRow['subject_name'] : '',
                    'syllabus_percent' => $lpct['syllabus_percentage'],
                );
            }

            // Build dates between from and to (inclusive).
            $dates = array();
            $startTs = strtotime($from);
            $endTs = strtotime($to);
            for ($ts = $startTs; $ts <= $endTs; $ts += 86400) {
                $dates[] = date('Y-m-d', $ts);
            }

            foreach ($dates as $d) {
                $activityMap = $this->digital_diary_model->getTodayTeachingActivityMapBySubject($d, $branchID, $classID, $sectionID, 0);
                $savedMap = $this->digital_diary_model->getTodayWorkMapByClassSectionDate($d, $branchID, $classID, $sectionID);
                $cells = array();
                foreach ($subjects as $subj) {
                    $sid = (int) $subj['subject_id'];
                    $todayItem = isset($activityMap[$sid]) ? $activityMap[$sid] : array();
                    $saved = isset($savedMap[$sid]) ? $savedMap[$sid] : null;
                    $parts = array();
                    if (!empty($saved)) {
                        if (!empty($saved['lesson_no']) && !empty($saved['lesson_name'])) {
                            $parts[] = 'Lesson ' . $saved['lesson_no'];
                            $parts[] = $saved['lesson_name'];
                        }
                        if (!empty($saved['class_work_items']) || !empty($saved['cw_detail'])) {
                            $parts[] = $this->digital_diary_model->formatTodayWorkActivityWithDetail(
                                $this->digital_diary_model->formatTodayWorkItemsDisplay(
                                    isset($saved['class_work_items']) ? $saved['class_work_items'] : '',
                                    $branchID
                                ),
                                isset($saved['cw_detail']) ? $saved['cw_detail'] : ''
                            );
                        }
                        if (!empty($saved['home_work_items']) || !empty($saved['home_work_note']) || !empty($saved['hw_detail'])) {
                            $hwText = $this->digital_diary_model->formatTodayWorkActivityWithDetail(
                                $this->digital_diary_model->formatTodayWorkItemsDisplay(
                                    isset($saved['home_work_items']) ? $saved['home_work_items'] : '',
                                    $branchID
                                ),
                                isset($saved['hw_detail']) ? $saved['hw_detail'] : ''
                            );
                            if (!empty($saved['home_work_note'])) {
                                $hwText = trim($hwText . ' ' . $saved['home_work_note']);
                            }
                            $parts[] = $hwText;
                        }
                    } elseif (!empty($todayItem)) {
                        if (!empty($todayItem['activity_name'])) {
                            $parts[] = $todayItem['activity_name'];
                        }
                        if (!empty($todayItem['remarks'])) {
                            $parts[] = $todayItem['remarks'];
                        }
                    }
                    $cells[$sid] = !empty($parts) ? implode("\n", $parts) : '';
                }
                $rowsByDate[] = array(
                    'date' => $d,
                    'cells' => $cells,
                );
            }

            // Today class performance: average of syllabus % for this class on "to" date.
            $syllVals = array();
            foreach ($subjects as $subj) {
                if ($subj['syllabus_percent'] !== '—') {
                    $syllVals[] = (int) $subj['syllabus_percent'];
                }
            }
            if (!empty($syllVals)) {
                $todayPerformance = (int) round(array_sum($syllVals) / count($syllVals)) . ' %';
            }
        }

        $this->data['detail_subjects'] = $subjects;
        $this->data['detail_rows'] = $rowsByDate;
        $this->data['today_class_performance'] = $todayPerformance;
        $this->data['title'] = 'Syllabus Report of Class';
        $this->data['sub_page'] = 'digital_diary/syllabus_report_class_detail';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Detailed Syllabus Report for a single teacher over a date range.
     */
    public function syllabus_report_teacher_detail()
    {
        $this->load->model('subject_model');

        $branchID = $this->input->get('branch_id');
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }

        $from = $this->input->get('from');
        $to = $this->input->get('to');
        if (empty($from)) {
            $from = date('Y-m-d');
        } else {
            $from = date('Y-m-d', strtotime($from));
        }
        if (empty($to)) {
            $to = $from;
        } else {
            $to = date('Y-m-d', strtotime($to));
        }

        $teacherID = (int) $this->input->get('teacher_id');
        $teacherNameParam = trim((string) $this->input->get('teacher_name'));
        if (is_teacher_loggedin()) {
            $teacherID = (int) get_loggedin_user_id();
            $teacherNameParam = get_type_name_by_id('staff', $teacherID);
        }

        $this->data['branch_id'] = $branchID;
        $this->data['from_date'] = $from;
        $this->data['to_date'] = $to;
        $this->data['teacher_id'] = $teacherID;

        // Teacher dropdown
        $this->db->select('staff.id, staff.name');
        $this->db->from('staff');
        $this->db->join('teacher_allocation ta', 'ta.teacher_id = staff.id', 'inner');
        $this->db->where('ta.session_id', get_session_id());
        if (!empty($branchID)) {
            $this->db->where('staff.branch_id', $branchID);
        }
        $this->db->group_by('staff.id');
        $this->db->order_by('staff.name');
        $teachers = $this->db->get()->result_array();
        $teacherOptions = array('' => translate('select'));
        foreach ($teachers as $t) {
            $teacherOptions[(int) $t['id']] = $t['name'];
        }
        if (is_teacher_loggedin()) {
            $teacherOptions = array((int) get_loggedin_user_id() => get_type_name_by_id('staff', get_loggedin_user_id()));
        }
        $this->data['teacher_options'] = $teacherOptions;

        $assignments = array();
        $columns = array();
        $rowsByDate = array();
        $todayMarks = '—';
        $teacherName = '';

        if ($teacherID <= 0 && $teacherNameParam !== '') {
            // Resolve teacher ID from name if only name was passed.
            $this->db->select('id');
            $this->db->from('staff');
            $this->db->where('name', $teacherNameParam);
            if (!empty($branchID)) {
                $this->db->where('branch_id', (int) $branchID);
            }
            $row = $this->db->get()->row_array();
            if (!empty($row['id'])) {
                $teacherID = (int) $row['id'];
                $this->data['teacher_id'] = $teacherID;
            }
        }

        if ($teacherID > 0) {
            $teacherName = isset($teacherOptions[$teacherID]) ? $teacherOptions[$teacherID] : $teacherNameParam;
            $assignments = $this->subject_model->getAssignmentsByTeacher($teacherID, $branchID);

            // Build columns: one per (class, section, subject)
            foreach ($assignments as $a) {
                $cid = (int) $a['class_id'];
                $sid = (int) $a['section_id'];
                $subId = (int) $a['subject_id'];
                $key = $cid . '_' . $sid . '_' . $subId;
                if (isset($columns[$key])) {
                    continue;
                }
                $lpct = $this->digital_diary_model->getLessonSyllabusCompletionPercent($cid, $sid, $subId, $branchID, $to);
                $columns[$key] = array(
                    'class_id' => $cid,
                    'section_id' => $sid,
                    'subject_id' => $subId,
                    'subject_name' => $a['subject_name'],
                    'class_name' => $a['class_name'],
                    'section_name' => $a['section_name'],
                    'syllabus_percent' => $lpct['syllabus_percentage'],
                );
            }

            // Dates range
            $dates = array();
            $startTs = strtotime($from);
            $endTs = strtotime($to);
            for ($ts = $startTs; $ts <= $endTs; $ts += 86400) {
                $dates[] = date('Y-m-d', $ts);
            }

            foreach ($dates as $d) {
                $cells = array();
                foreach ($columns as $key => $col) {
                    $cid = $col['class_id'];
                    $sid = $col['section_id'];
                    $subId = $col['subject_id'];
                    $activityMap = $this->digital_diary_model->getTodayTeachingActivityMapBySubject($d, $branchID, $cid, $sid, $subId);
                    $savedMap = $this->db->table_exists('digital_diary_today_work')
                        ? $this->digital_diary_model->getTodayWorkMapByClassSectionDate($d, $branchID, $cid, $sid)
                        : array();
                    $todayItem = isset($activityMap[$subId]) ? $activityMap[$subId] : array();
                    $saved = isset($savedMap[$subId]) ? $savedMap[$subId] : null;
                    $parts = array();
                    if (!empty($saved)) {
                        if (!empty($saved['lesson_no']) && !empty($saved['lesson_name'])) {
                            $parts[] = 'Lesson ' . $saved['lesson_no'];
                            $parts[] = $saved['lesson_name'];
                        }
                        if (!empty($saved['class_work_items']) || !empty($saved['cw_detail'])) {
                            $parts[] = $this->digital_diary_model->formatTodayWorkActivityWithDetail(
                                $this->digital_diary_model->formatTodayWorkItemsDisplay(
                                    isset($saved['class_work_items']) ? $saved['class_work_items'] : '',
                                    $branchID
                                ),
                                isset($saved['cw_detail']) ? $saved['cw_detail'] : ''
                            );
                        }
                        if (!empty($saved['home_work_items']) || !empty($saved['home_work_note']) || !empty($saved['hw_detail'])) {
                            $hwText = $this->digital_diary_model->formatTodayWorkActivityWithDetail(
                                $this->digital_diary_model->formatTodayWorkItemsDisplay(
                                    isset($saved['home_work_items']) ? $saved['home_work_items'] : '',
                                    $branchID
                                ),
                                isset($saved['hw_detail']) ? $saved['hw_detail'] : ''
                            );
                            if (!empty($saved['home_work_note'])) {
                                $hwText = trim($hwText . ' ' . $saved['home_work_note']);
                            }
                            $parts[] = $hwText;
                        }
                    } elseif (!empty($todayItem)) {
                        if (!empty($todayItem['activity_name'])) {
                            $parts[] = $todayItem['activity_name'];
                        }
                        if (!empty($todayItem['remarks'])) {
                            $parts[] = $todayItem['remarks'];
                        }
                    }
                    $cells[$key] = !empty($parts) ? implode("\n", $parts) : '';
                }
                $rowsByDate[] = array(
                    'date' => $d,
                    'cells' => $cells,
                );
            }

            // Today Marks: avg syllabus % across this teacher's columns at "to" date.
            $vals = array();
            foreach ($columns as $col) {
                if ($col['syllabus_percent'] !== '—') {
                    $vals[] = (int) $col['syllabus_percent'];
                }
            }
            if (!empty($vals)) {
                $todayMarks = (int) round(array_sum($vals) / count($vals)) . ' %';
            }
        }

        $this->data['teacher_name'] = $teacherName;
        $this->data['teacher_columns'] = $columns;
        $this->data['teacher_rows'] = $rowsByDate;
        $this->data['teacher_today_marks'] = $todayMarks;
        $this->data['title'] = $teacherName ? ('Syllabus Report of ' . $teacherName) : 'Syllabus Report - Teacher wise';
        $this->data['sub_page'] = 'digital_diary/syllabus_report_teacher_detail';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Non Performer - content as per PDF spec
     */
    public function non_performer_report()
    {
        $this->load->model('subject_model');
        $branchID = $this->input->get('branch_id');
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }

        $from = $this->input->get('from');
        $to = $this->input->get('to');
        if (empty($from)) {
            $from = date('Y-m-d');
        } else {
            $from = date('Y-m-d', strtotime($from));
        }
        if (empty($to)) {
            $to = $from;
        } else {
            $to = date('Y-m-d', strtotime($to));
        }

        $this->data['branch_id'] = $branchID;
        $this->data['branch_name'] = !empty($branchID) ? get_type_name_by_id('branch', $branchID) : '';
        $this->data['from_date'] = $from;
        $this->data['to_date'] = $to;

        $classes = $this->digital_diary_teacher_allowed_class_map($branchID);

        // Subject headers (teacher: only subjects they teach).
        $subjects = $this->digital_diary_teacher_report_subjects($branchID);
        $this->data['np_subjects'] = $subjects;

        $rows = array();
        $totalsPerSubject = array();
        $totalStudentsAll = 0;

        if ($this->db->table_exists('digital_diary_student_work_report') && !empty($subjects)) {
            foreach ($classes as $cid => $cname) {
                $cid = (int) $cid;
                if ($cid <= 0) {
                    continue;
                }
                // Total students in this class (all sections).
                $this->db->from('enroll');
                $this->db->where('class_id', $cid);
                if (!empty($branchID)) {
                    $this->db->where('branch_id', (int) $branchID);
                }
                $this->db->where('session_id', get_session_id());
                $totalStudents = (int) $this->db->count_all_results();
                $totalStudentsAll += $totalStudents;

                $subjectCounts = array();
                foreach ($subjects as $sub) {
                    $sid = (int) $sub['id'];
                    // Count distinct students with any incomplete work in date range.
                    $sql = "SELECT COUNT(DISTINCT swr.student_id) AS cnt
                            FROM digital_diary_student_work_report swr
                            JOIN enroll e ON e.student_id = swr.student_id
                            WHERE swr.class_id = ? AND swr.subject_id = ? AND swr.work_date >= ? AND swr.work_date <= ?
                              AND e.class_id = swr.class_id";
                    $params = array($cid, $sid, $from, $to);
                    if (!empty($branchID)) {
                        $sql .= " AND swr.branch_id = ? AND e.branch_id = ?";
                        $params[] = (int) $branchID;
                        $params[] = (int) $branchID;
                    }
                    $sql .= " AND (swr.fair_copy = 0 OR swr.writing_work = 0 OR swr.learning_work = 0)";
                    $row = $this->db->query($sql, $params)->row_array();
                    $cnt = !empty($row['cnt']) ? (int) $row['cnt'] : 0;
                    $subjectCounts[$sid] = $cnt;
                    if (!isset($totalsPerSubject[$sid])) {
                        $totalsPerSubject[$sid] = 0;
                    }
                    $totalsPerSubject[$sid] += $cnt;
                }
                $rows[] = array(
                    'class_id' => $cid,
                    'class_name' => $cname,
                    'total_students' => $totalStudents,
                    'subjects' => $subjectCounts,
                );
            }
        }

        $this->data['np_rows'] = $rows;
        $this->data['np_totals_per_subject'] = $totalsPerSubject;
        $this->data['np_total_students_all'] = $totalStudentsAll;

        $this->data['title'] = 'Non Performer Report';
        $this->data['sub_page'] = 'digital_diary/non_performer_report';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Non Performer — student list for one class + subject in date range (detail view).
     */
    public function non_performer_report_detail()
    {
        $branchID = $this->input->get('branch_id');
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }
        $branchID = (int) $branchID;

        $from = $this->input->get('from');
        $to = $this->input->get('to');
        if (empty($from)) {
            $from = date('Y-m-d');
        } else {
            $from = date('Y-m-d', strtotime($from));
        }
        if (empty($to)) {
            $to = $from;
        } else {
            $to = date('Y-m-d', strtotime($to));
        }

        $classID = (int) $this->input->get('class_id');
        $subjectID = (int) $this->input->get('subject_id');

        if ($classID <= 0 || $subjectID <= 0) {
            show_404();
            return;
        }
        if (is_teacher_loggedin() && !$this->digital_diary_teacher_may_access_np_cell($classID, $subjectID)) {
            access_denied();
        }

        $this->data['branch_id'] = $branchID;
        $this->data['branch_name'] = get_type_name_by_id('branch', $branchID);
        $this->data['from_date'] = $from;
        $this->data['to_date'] = $to;
        $this->data['class_id'] = $classID;
        $this->data['subject_id'] = $subjectID;
        $this->data['class_name'] = get_type_name_by_id('class', $classID);
        $this->data['subject_name'] = get_type_name_by_id('subject', $subjectID);

        $detailRows = array();
        if ($this->db->table_exists('digital_diary_student_work_report')) {
            $sessionID = (int) get_session_id();
            $this->db->from('digital_diary_student_work_report swr');
            $this->db->where('swr.branch_id', $branchID);
            $this->db->where('swr.session_id', $sessionID);
            $this->db->where('swr.class_id', $classID);
            $this->db->where('swr.subject_id', $subjectID);
            $this->db->where('swr.work_date >=', $from);
            $this->db->where('swr.work_date <=', $to);
            $this->db->where('(swr.fair_copy = 0 OR swr.writing_work = 0 OR swr.learning_work = 0)');
            $swrRows = $this->db->get()->result_array();

            $byStudent = array();
            foreach ($swrRows as $row) {
                $stid = (int) $row['student_id'];
                if (!isset($byStudent[$stid])) {
                    $byStudent[$stid] = array();
                }
                $byStudent[$stid][] = $row;
            }

            $agg = array();
            foreach ($byStudent as $stid => $srows) {
                $fair = 'Completed';
                $writ = 'Completed';
                $learn = 'Completed';
                $remarks = '';
                $latestDate = '';
                foreach ($srows as $r) {
                    if (empty($r['fair_copy'])) {
                        $fair = 'Uncompleted';
                    }
                    if (empty($r['writing_work'])) {
                        $writ = 'Uncompleted';
                    }
                    if (empty($r['learning_work'])) {
                        $learn = 'Uncompleted';
                    }
                    $wd = isset($r['work_date']) ? (string) $r['work_date'] : '';
                    if ($wd >= $latestDate) {
                        $latestDate = $wd;
                        $remarks = isset($r['remarks']) ? (string) $r['remarks'] : '';
                    }
                }
                if ($fair === 'Uncompleted' || $writ === 'Uncompleted' || $learn === 'Uncompleted') {
                    $agg[$stid] = array(
                        'fair' => $fair,
                        'writing' => $writ,
                        'learning' => $learn,
                        'remarks' => $remarks,
                    );
                }
            }

            if (!empty($agg)) {
                $ids = array_keys($agg);
                $this->db->select('s.id as student_id, s.register_no, e.roll, CONCAT_WS(" ", s.first_name, s.last_name) as fullname');
                $this->db->from('enroll e');
                $this->db->join('student s', 's.id = e.student_id', 'inner');
                $this->db->where('e.class_id', $classID);
                $this->db->where('e.branch_id', $branchID);
                $this->db->where('e.session_id', $sessionID);
                $this->db->where_in('s.id', $ids);
                $this->db->order_by('e.roll', 'ASC');
                $this->db->order_by('s.first_name', 'ASC');
                $stRows = $this->db->get()->result_array();

                $seen = array();
                foreach ($stRows as $sr) {
                    $sid = (int) $sr['student_id'];
                    if (isset($seen[$sid])) {
                        continue;
                    }
                    $seen[$sid] = true;
                    if (!isset($agg[$sid])) {
                        continue;
                    }
                    $a = $agg[$sid];
                    $detailRows[] = array(
                        'student_id' => $sid,
                        'register_no' => isset($sr['register_no']) ? (string) $sr['register_no'] : '',
                        'roll' => isset($sr['roll']) ? $sr['roll'] : '',
                        'fullname' => isset($sr['fullname']) ? (string) $sr['fullname'] : '',
                        'fair_notebook' => $a['fair'],
                        'written_work' => $a['writing'],
                        'learning_work' => $a['learning'],
                        'remarks' => $a['remarks'],
                    );
                }
            }
        }

        $this->data['np_detail_rows'] = $detailRows;
        $this->data['title'] = 'Non Performer — ' . $this->data['class_name'] . ' / ' . $this->data['subject_name'];
        $this->data['sub_page'] = 'digital_diary/non_performer_report_detail';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Student Work Evaluation Report — per-student date range (layout per school format).
     */
    public function student_work_report()
    {
        $this->digital_diary_prepare_student_work_eval();
        $this->data['title'] = 'Student Work Evaluation Report';
        $this->data['sub_page'] = 'digital_diary/student_work_report';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Shared data for Student Work Evaluation Report and Parent Report Format.
     */
    protected function digital_diary_prepare_student_work_eval()
    {
        $this->load->model('subject_model');
        $branchGet = $this->input->get('branch_id');
        $branchID = ($branchGet !== null && $branchGet !== '' && $branchGet !== false) ? (int) $branchGet : '';
        if ($branchID === 0) {
            $branchID = '';
        }
        if ($branchID === '') {
            $branchID = get_loggedin_branch_id();
        }
        if (($branchID === '' || $branchID === null) && !is_superadmin_loggedin()) {
            $branchID = $this->application_model->get_branch_id();
        }
        if ($branchID !== '' && $branchID !== null) {
            $branchID = (int) $branchID;
        }

        $this->data['branch_id'] = $branchID;
        $this->data['branch_name'] = !empty($branchID) ? get_type_name_by_id('branch', $branchID) : '';

        $searchQ = trim((string) $this->input->get('search'));
        $dateFromIn = $this->input->get('date_from');
        $dateToIn = $this->input->get('date_to');
        $singleDate = trim((string) $this->input->get('date'));
        if (empty($dateToIn) && $singleDate !== '' && strtotime($singleDate)) {
            $dateTo = date('Y-m-d', strtotime($singleDate));
        } elseif (empty($dateToIn)) {
            $dateTo = date('Y-m-d');
        } else {
            $dateTo = date('Y-m-d', strtotime($dateToIn));
        }
        if (empty($dateFromIn)) {
            $dateFrom = date('Y-m-d', strtotime($dateTo . ' -8 days'));
        } else {
            $dateFrom = date('Y-m-d', strtotime($dateFromIn));
        }
        if (strtotime($dateFrom) > strtotime($dateTo)) {
            $tmp = $dateFrom;
            $dateFrom = $dateTo;
            $dateTo = $tmp;
        }

        $classID = (int) $this->input->get('class_id');
        $sectionID = (int) $this->input->get('section_id');
        $studentID = (int) $this->input->get('student_id');

        if (is_teacher_loggedin()) {
            $assigned = $this->app_lib->get_ownClassSection();
            $classOptions = array('' => translate('select'));
            if (is_array($assigned) && !empty($assigned)) {
                foreach ($assigned as $classKey => $sections) {
                    $classOptions[$classKey] = get_type_name_by_id('class', $classKey);
                }
            }
            if (count($classOptions) <= 1 && !empty($branchID)) {
                $classOptions = $this->app_lib->getClass($branchID);
            }
            $this->data['class_options'] = $classOptions;
            $restricted = is_array($assigned) && !empty($assigned);
            $section_options = array('' => translate('select'));
            if ($restricted && !empty($classID) && isset($assigned[$classID])) {
                foreach ($assigned[$classID] as $sid) {
                    $section_options[$sid] = get_type_name_by_id('section', $sid);
                }
            } elseif (!empty($classID)) {
                $section_options = $this->app_lib->getSections($classID, false);
            } else {
                $section_options = array('' => translate('select_class_first'));
            }
            $this->data['section_options'] = $section_options;
        } else {
            $this->data['class_options'] = !empty($branchID) ? $this->app_lib->getClass($branchID) : array('' => translate('select'));
            $this->data['section_options'] = !empty($classID) ? $this->app_lib->getSections($classID, false) : array('' => translate('select_class_first'));
        }

        $subjects_for_grid = array();
        if (!empty($branchID) && !empty($classID) && !empty($sectionID)) {
            $allSubQ = $this->subject_model->getAllSubjectsListForClassSection($classID, $sectionID, $branchID);
            foreach ($allSubQ->result() as $srow) {
                if (!empty($srow->subject_id)) {
                    $subId = (int) $srow->subject_id;
                    if (is_teacher_loggedin() && !$this->digital_diary_teacher_may_view_subject($classID, $sectionID, $subId)) {
                        continue;
                    }
                    $subjects_for_grid[] = array(
                        'subject_id' => $subId,
                        'subject_name' => $srow->subjectname,
                    );
                }
            }
        }

        $students = array();
        if (empty($classID)) {
            $student_options = array('' => translate('select_class_first'));
        } elseif (empty($sectionID)) {
            $student_options = array('' => translate('select') . ' ' . translate('section'));
        } else {
            $student_options = array('' => translate('select'));
        }
        $report_rows = array();
        $summary = array(
            'school_open_days' => 0,
            'attend_days' => 0,
            'absent_days' => 0,
            'attendance_pct' => 0,
            'without_dress' => 0,
            'bad_conduct' => 0,
            'subject_scores' => array(),
            'grade' => '—',
            'avg_score' => 0,
        );
        $student_info = null;
        $period_info = null;
        $grid_totals = null;

        $table_ok = $this->db->table_exists('digital_diary_student_work_report');
        $student_valid = false;

        if ($table_ok && !empty($branchID) && !empty($classID) && !empty($sectionID)) {
            if (!is_teacher_loggedin() || $this->digital_diary_teacher_may_use_class_section($branchID, $classID, $sectionID)) {
                $students = $this->digital_diary_model->getStudentsForStudentWorkReport($branchID, $classID, $sectionID);
                foreach ($students as $st) {
                    $rollStr = isset($st['roll']) ? (string) $st['roll'] : '';
                    if ($searchQ === '' || stripos($st['fullname'], $searchQ) !== false || stripos($rollStr, $searchQ) !== false) {
                        $student_options[$st['student_id']] = $st['fullname'];
                    }
                }
                if ($studentID > 0) {
                    foreach ($students as $st) {
                        if ((int) $st['student_id'] === $studentID) {
                            $student_valid = true;
                            $student_info = array(
                                'name' => $st['fullname'],
                                'roll' => isset($st['roll']) ? $st['roll'] : '',
                                'class_name' => get_type_name_by_id('class', $classID),
                                'section_name' => get_type_name_by_id('section', $sectionID),
                            );
                            break;
                        }
                    }
                }
            }
        }

        if (!empty($branchID) && strtotime($dateFrom) <= strtotime($dateTo)) {
            $calendarDays = 0;
            $excludedDays = 0;
            $openDays = 0;
            for ($t = strtotime($dateFrom); $t <= strtotime($dateTo); $t += 86400) {
                $calendarDays++;
                $d = date('Y-m-d', $t);
                if ($this->digital_diary_is_school_open_day($d, $branchID)) {
                    $openDays++;
                } else {
                    $excludedDays++;
                }
            }
            $period_info = array(
                'date_from_disp' => date('d M Y', strtotime($dateFrom)),
                'date_to_disp' => date('d M Y', strtotime($dateTo)),
                'calendar_days' => $calendarDays,
                'excluded_days' => $excludedDays,
                'school_open_days' => $openDays,
            );
        }

        if ($table_ok && $student_valid && !empty($branchID) && !empty($classID) && !empty($sectionID)) {
            $t0 = strtotime($dateFrom);
            $t1 = strtotime($dateTo);
            $schoolDays = 0;
            $attendDays = 0;
            $absentDays = 0;
            $withoutDress = 0;
            $badConduct = 0;
            $subjTotals = array();
            foreach ($subjects_for_grid as $sg) {
                $subjTotals[(int) $sg['subject_id']] = array(
                    'checks' => 0,
                    'fair' => 0,
                    'writing' => 0,
                    'learning' => 0,
                );
            }

            $sr = 1;
            for ($t = $t0; $t <= $t1; $t += 86400) {
                if ($sr > 400) {
                    break;
                }
                $d = date('Y-m-d', $t);
                if (!$this->digital_diary_is_school_open_day($d, $branchID)) {
                    continue;
                }
                $schoolDays++;
                $attMap = $this->digital_diary_model->getStudentAttendanceMapForDate($d, $branchID, $classID, $sectionID);
                $rec = isset($attMap[$studentID]) ? $attMap[$studentID] : null;
                $status = $rec ? strtoupper(trim((string) $rec['status'])) : '';
                $isAbsent = ($status === 'A');
                $showP = !$isAbsent && ($status === 'P' || $status === 'L' || $status === 'HD');
                if ($showP) {
                    $attendDays++;
                }
                if ($isAbsent) {
                    $absentDays++;
                }

                if ($rec) {
                    $dressOk = !empty($rec['discipline_dress']);
                    $conductOk = !empty($rec['discipline_conduct']);
                    if (!$dressOk) {
                        $withoutDress++;
                    }
                    if (!$conductOk) {
                        $badConduct++;
                    }
                } else {
                    $dressOk = null;
                    $conductOk = null;
                }

                $subject_cells = array();
                foreach ($subjects_for_grid as $sg) {
                    $sid = (int) $sg['subject_id'];
                    $subject_cells[$sid] = array('fair' => '', 'writing' => '', 'learn' => '');
                    $saved = $this->digital_diary_model->getStudentWorkReportSavedMap($d, $branchID, $classID, $sectionID, $sid);
                    if (isset($saved[$studentID])) {
                        $r = $saved[$studentID];
                        if (!empty($r['fair_copy'])) {
                            $subject_cells[$sid]['fair'] = '✓';
                            $subjTotals[$sid]['fair']++;
                            $subjTotals[$sid]['checks']++;
                        }
                        if (!empty($r['writing_work'])) {
                            $subject_cells[$sid]['writing'] = '✓';
                            $subjTotals[$sid]['writing']++;
                            $subjTotals[$sid]['checks']++;
                        }
                        if (!empty($r['learning_work'])) {
                            $subject_cells[$sid]['learn'] = '✓';
                            $subjTotals[$sid]['learning']++;
                            $subjTotals[$sid]['checks']++;
                        }
                    }
                }

                $attendance = '';
                if ($isAbsent) {
                    $attendance = 'A';
                } elseif ($showP) {
                    $attendance = $status;
                }

                $report_rows[] = array(
                    'sr' => $sr,
                    'date_disp' => date('d-m-Y', $t),
                    't_days' => (string) $sr,
                    'attendance' => $attendance,
                    'dress' => ($rec && $dressOk) ? '✓' : '',
                    'conduct' => ($rec && $conductOk) ? '✓' : '',
                    'subjects' => $subject_cells,
                );
                $sr++;
            }

            $summary['school_open_days'] = $schoolDays;
            $summary['attend_days'] = $attendDays;
            $summary['absent_days'] = $absentDays;
            $summary['attendance_pct'] = $schoolDays > 0 ? (int) round($attendDays / $schoolDays * 100) : 0;
            $summary['without_dress'] = $withoutDress;
            $summary['bad_conduct'] = $badConduct;

            $scoreBundle = $this->digital_diary_build_subject_work_scores($subjects_for_grid, $subjTotals, $schoolDays);
            $summary['subject_scores'] = $scoreBundle['subject_scores'];
            $summary['grade'] = $scoreBundle['grade'];
            $avgScore = 0;
            foreach ($scoreBundle['subject_scores'] as $sc) {
                $avgScore += isset($sc['score']) ? (int) $sc['score'] : 0;
            }
            $summary['avg_score'] = count($scoreBundle['subject_scores']) ? (int) round($avgScore / count($scoreBundle['subject_scores'])) : 0;

            $grid_totals = array(
                'present' => $attendDays,
                'absent' => $absentDays,
                'without_dress' => $withoutDress,
                'bad_conduct' => $badConduct,
                'subjects' => $subjTotals,
            );
        } elseif (!empty($classID) && !empty($sectionID) && strtotime($dateFrom) <= strtotime($dateTo)) {
            $empty_subjects = array();
            foreach ($subjects_for_grid as $sg) {
                $empty_subjects[(int) $sg['subject_id']] = array('fair' => '', 'writing' => '', 'learn' => '');
            }
            $sr = 1;
            for ($t = strtotime($dateFrom); $t <= strtotime($dateTo); $t += 86400) {
                if ($sr > 400) {
                    break;
                }
                $d = date('Y-m-d', $t);
                if (!$this->digital_diary_is_school_open_day($d, $branchID)) {
                    continue;
                }
                $report_rows[] = array(
                    'sr' => $sr,
                    'date_disp' => date('d-m-Y', $t),
                    't_days' => (string) $sr,
                    'attendance' => '',
                    'dress' => '',
                    'conduct' => '',
                    'subjects' => $empty_subjects,
                );
                $sr++;
            }
        }

        $this->data['search_q'] = $searchQ;
        $this->data['date_from'] = $dateFrom;
        $this->data['date_to'] = $dateTo;
        $this->data['filter_class_id'] = $classID;
        $this->data['filter_section_id'] = $sectionID;
        $this->data['filter_student_id'] = $studentID;
        $this->data['student_options'] = $student_options;
        $this->data['subjects_for_grid'] = $subjects_for_grid;
        $this->data['report_rows'] = $report_rows;
        $this->data['summary'] = $summary;
        $this->data['student_info'] = $student_info;
        $this->data['period_info'] = $period_info;
        $this->data['grid_totals'] = $grid_totals;
        $this->data['swr_table_ok'] = $table_ok;
        $this->data['student_valid'] = $student_valid;
        $skill_summary = array();
        if ($student_valid && $studentID > 0 && !empty($branchID) && $classID > 0 && $sectionID > 0) {
            $skill_summary = $this->digital_diary_model->getCoordinatorActivityRatingSummary(
                $studentID,
                $dateFrom,
                $dateTo,
                $branchID,
                $classID,
                $sectionID
            );
        }
        $this->data['skill_summary'] = $skill_summary;
    }

    /**
     * Student Work Report — class/section/subject grid with discipline + work checkboxes (teachers allowed).
     */
    public function student_work_report_entry()
    {
        $this->load->model('subject_model');
        $branchGet = $this->input->get('branch_id');
        $branchID = ($branchGet !== null && $branchGet !== '' && $branchGet !== false) ? (int) $branchGet : '';
        if ($branchID === 0) {
            $branchID = '';
        }
        if ($branchID === '') {
            $branchID = get_loggedin_branch_id();
        }
        if (($branchID === '' || $branchID === null) && !is_superadmin_loggedin()) {
            $branchID = $this->application_model->get_branch_id();
        }
        if ($branchID !== '' && $branchID !== null) {
            $branchID = (int) $branchID;
        }

        $this->data['branch_id'] = $branchID;
        $this->data['branch_name'] = !empty($branchID) ? get_type_name_by_id('branch', $branchID) : '';

        $reportDate = $this->input->get('date');
        if (empty($reportDate)) {
            $reportDate = date('Y-m-d');
        } else {
            $reportDate = date('Y-m-d', strtotime($reportDate));
        }
        $classID = (int) $this->input->get('class_id');
        $sectionID = (int) $this->input->get('section_id');

        $teacher_class_boxes = array();
        $teacher_week_days = array();
        if (is_teacher_loggedin()) {
            // Teacher landing: week schedule with period timings (same source as timetable/viewclass).
            if (empty($classID) || empty($sectionID)) {
                $weekPack = $this->digital_diary_teacher_week_period_boxes($branchID, $reportDate);
                $teacher_class_boxes = $weekPack['boxes'];
                $teacher_week_days = $weekPack['days'];
            } else {
                $teacher_class_boxes = $this->digital_diary_teacher_assigned_class_boxes($branchID, $reportDate);
            }
            $assigned = $this->app_lib->get_ownClassSection();
            $classOptions = array('' => translate('select'));
            if (is_array($assigned) && !empty($assigned)) {
                foreach ($assigned as $classKey => $sections) {
                    $classOptions[$classKey] = get_type_name_by_id('class', $classKey);
                }
            }
            if (count($classOptions) <= 1 && !empty($teacher_class_boxes)) {
                foreach ($teacher_class_boxes as $box) {
                    $classOptions[(int) $box['class_id']] = $box['class_name'];
                }
            }
            if (count($classOptions) <= 1 && !empty($branchID)) {
                $classOptions = $this->app_lib->getClass($branchID);
            }
            $this->data['class_options'] = $classOptions;
            $restricted = is_array($assigned) && !empty($assigned);
            $section_options = array('' => translate('select'));
            if ($restricted && !empty($classID) && isset($assigned[$classID])) {
                foreach ($assigned[$classID] as $sid) {
                    $section_options[$sid] = get_type_name_by_id('section', $sid);
                }
            } elseif (!empty($classID) && !empty($teacher_class_boxes)) {
                foreach ($teacher_class_boxes as $box) {
                    if ((int) $box['class_id'] === (int) $classID) {
                        $section_options[(int) $box['section_id']] = $box['section_name'];
                    }
                }
                if (count($section_options) <= 1) {
                    $section_options = $this->app_lib->getSections($classID, false);
                }
            } elseif (!empty($classID)) {
                $section_options = $this->app_lib->getSections($classID, false);
            } else {
                $section_options = array('' => translate('select_class_first'));
            }
            $this->data['section_options'] = $section_options;
        } else {
            $this->data['class_options'] = !empty($branchID) ? $this->app_lib->getClass($branchID) : array('' => translate('select'));
            $this->data['section_options'] = !empty($classID) ? $this->app_lib->getSections($classID, false) : array('' => translate('select_class_first'));
        }
        $this->data['teacher_class_boxes'] = $teacher_class_boxes;
        $this->data['teacher_week_days'] = $teacher_week_days;

        $subjects_for_grid = array();
        if (!empty($branchID) && !empty($classID) && !empty($sectionID)) {
            $allSubQ = $this->subject_model->getAllSubjectsListForClassSection($classID, $sectionID, $branchID);
            foreach ($allSubQ->result() as $srow) {
                if (!empty($srow->subject_id)) {
                    $subjects_for_grid[] = array(
                        'subject_id' => (int) $srow->subject_id,
                        'subject_name' => $srow->subjectname,
                    );
                }
            }
        }

        $teacher_subject_ids = array();
        if (is_teacher_loggedin()) {
            $tq = $this->subject_model->getSubjectByClassSection($classID, $sectionID);
            foreach ($tq->result() as $trow) {
                if (!empty($trow->subject_id)) {
                    $teacher_subject_ids[] = (int) $trow->subject_id;
                }
            }
            $teacher_subject_ids = array_values(array_unique($teacher_subject_ids));
        }
        $is_class_teacher = is_teacher_loggedin()
            && $this->subject_model->getClassTeacherByClassSection($classID, $sectionID);
        // Subject teachers: only their courses. Class teacher / admin: all class courses.
        if (is_teacher_loggedin() && !$is_class_teacher) {
            $filtered_subjects = array();
            foreach ($subjects_for_grid as $sg) {
                $sid = (int) $sg['subject_id'];
                if ($this->digital_diary_teacher_may_view_subject($classID, $sectionID, $sid)) {
                    $filtered_subjects[] = $sg;
                }
            }
            $subjects_for_grid = $filtered_subjects;
        }
        // Order subjects by that day's timetable periods (time_start), attach timings.
        if (!empty($subjects_for_grid) && !empty($classID) && !empty($sectionID)) {
            $subjects_for_grid = $this->digital_diary_order_subjects_by_period(
                $subjects_for_grid,
                $classID,
                $sectionID,
                $reportDate,
                $branchID
            );
        }
        $swr_subject_can_edit = array();
        foreach ($subjects_for_grid as $sg) {
            $sid = (int) $sg['subject_id'];
            if (!is_teacher_loggedin()) {
                $swr_subject_can_edit[$sid] = true;
            } elseif ($is_class_teacher) {
                $swr_subject_can_edit[$sid] = true;
            } else {
                $swr_subject_can_edit[$sid] = in_array($sid, $teacher_subject_ids, true);
            }
        }
        // Discipline columns: class teacher only among teachers; admin/non-teacher can manage.
        $show_discipline = !is_teacher_loggedin() || !empty($is_class_teacher);
        $can_edit_discipline = $show_discipline;

        $students = array();
        $saved_maps_by_subject = array();
        $discipline_display_map = array();
        $table_ok = $this->db->table_exists('digital_diary_student_work_report');
        if ($table_ok && !empty($branchID) && !empty($classID) && !empty($sectionID) && !empty($subjects_for_grid)) {
            if (!is_teacher_loggedin() || $this->digital_diary_teacher_may_use_class_section($branchID, $classID, $sectionID)) {
                $students = $this->digital_diary_model->getStudentsForStudentWorkReport($branchID, $classID, $sectionID);
                foreach ($subjects_for_grid as $sg) {
                    $subId = (int) $sg['subject_id'];
                    $saved_maps_by_subject[$subId] = $this->digital_diary_model->getStudentWorkReportSavedMap(
                        $reportDate,
                        $branchID,
                        $classID,
                        $sectionID,
                        $subId
                    );
                }
                $attendanceMap = $this->digital_diary_model->getStudentAttendanceMapForDate(
                    $reportDate,
                    $branchID,
                    $classID,
                    $sectionID
                );
                foreach ($students as $st) {
                    $stuId = (int) $st['student_id'];
                    $dress = isset($attendanceMap[$stuId]['discipline_dress']) ? (int) $attendanceMap[$stuId]['discipline_dress'] : 1;
                    $conduct = isset($attendanceMap[$stuId]['discipline_conduct']) ? (int) $attendanceMap[$stuId]['discipline_conduct'] : 1;
                    $discipline_display_map[$stuId] = array(
                        'discipline_dress' => $dress,
                        'discipline_conduct' => $conduct,
                    );
                }
            }
        }

        $swr_has_saved_data = false;
        foreach ($saved_maps_by_subject as $swrMap) {
            if (!empty($swrMap)) {
                $swr_has_saved_data = true;
                break;
            }
        }

        $is_day_final = false;
        if ($this->db->table_exists('digital_diary_today_work_day') && !empty($branchID) && !empty($classID) && !empty($sectionID)) {
            $is_day_final = $this->digital_diary_model->isTodayWorkDayFinal($reportDate, $branchID, $classID, $sectionID);
        }

        $this->digital_diary_model->ensureTodayWorkLearningFlowTables();
        $twMap = $this->digital_diary_model->getTodayWorkMapByClassSectionDate($reportDate, $branchID, $classID, $sectionID);
        $twIds = array();
        $twIdBySubject = array();
        foreach ($twMap as $subjId => $twRow) {
            $twid = (int) $twRow['id'];
            $twIds[] = $twid;
            $twIdBySubject[(int) $subjId] = $twid;
        }
        $submissionsByTw = $this->digital_diary_model->getSubmissionsMapByTodayWorkIds($twIds);
        $subjectHasTest = $this->digital_diary_model->getSubjectHasTestMapFromTodayWork($reportDate, $branchID, $classID, $sectionID);
        // student_id => subject_id => [writing/learning/photo/marks/status]
        $submission_prefills = array();
        foreach ($twIdBySubject as $subjId => $twid) {
            if (empty($submissionsByTw[$twid])) {
                continue;
            }
            foreach ($submissionsByTw[$twid] as $stuId => $types) {
                if (!isset($submission_prefills[$stuId])) {
                    $submission_prefills[$stuId] = array();
                }
                $submission_prefills[$stuId][$subjId] = array(
                    'writing_work' => !empty($types['homework']),
                    'learning_work' => !empty($types['learning']),
                    'hw_photo' => !empty($types['homework']['photo']) ? $types['homework']['photo'] : '',
                    'hw_status' => !empty($types['homework']['status']) ? $types['homework']['status'] : '',
                    'learning_status' => !empty($types['learning']['status']) ? $types['learning']['status'] : '',
                    'marks' => isset($types['learning']['marks']) ? $types['learning']['marks'] : '',
                    'today_work_id' => $twid,
                );
            }
        }

        $this->data['report_date'] = $reportDate;
        $this->data['filter_class_id'] = $classID;
        $this->data['filter_section_id'] = $sectionID;
        $this->data['subjects_for_grid'] = $subjects_for_grid;
        $this->data['students'] = $students;
        $this->data['saved_maps_by_subject'] = $saved_maps_by_subject;
        $this->data['discipline_display_map'] = $discipline_display_map;
        $this->data['swr_subject_can_edit'] = $swr_subject_can_edit;
        $this->data['show_discipline'] = $show_discipline;
        $this->data['can_edit_discipline'] = $can_edit_discipline;
        $this->data['is_class_teacher'] = $is_class_teacher;
        $this->data['can_final_submit'] = $this->digital_diary_teacher_may_final_submit($classID, $sectionID);
        $this->data['can_unlock_final_day'] = is_superadmin_loggedin() || is_admin_loggedin();
        $this->data['swr_table_ok'] = $table_ok;
        $this->data['is_day_final'] = $is_day_final;
        $this->data['swr_has_saved_data'] = $swr_has_saved_data;
        $this->data['submission_prefills'] = $submission_prefills;
        $this->data['subject_has_test'] = $subjectHasTest;
        $this->data['tw_id_by_subject'] = $twIdBySubject;

        // Yesterday's work strip (previous calendar day with saved Today’s Work)
        $yesterday_work_rows = array();
        $prevDate = date('Y-m-d', strtotime($reportDate . ' -1 day'));
        for ($i = 0; $i < 7; $i++) {
            $tryDate = date('Y-m-d', strtotime($reportDate . ' -' . ($i + 1) . ' day'));
            $prevMap = $this->digital_diary_model->getTodayWorkMapByClassSectionDate($tryDate, $branchID, $classID, $sectionID);
            if (!empty($prevMap)) {
                $prevDate = $tryDate;
                foreach ($prevMap as $psid => $psaved) {
                    if (is_teacher_loggedin() && !$this->digital_diary_teacher_may_view_subject($classID, $sectionID, (int) $psid)) {
                        continue;
                    }
                    $cw = $this->digital_diary_model->formatTodayWorkActivityWithDetail(
                        $this->digital_diary_model->formatTodayWorkItemsDisplay(isset($psaved['class_work_items']) ? $psaved['class_work_items'] : '', $branchID),
                        isset($psaved['cw_detail']) ? $psaved['cw_detail'] : ''
                    );
                    $hw = $this->digital_diary_model->formatTodayWorkActivityWithDetail(
                        $this->digital_diary_model->formatTodayWorkItemsDisplay(isset($psaved['home_work_items']) ? $psaved['home_work_items'] : '', $branchID),
                        isset($psaved['hw_detail']) ? $psaved['hw_detail'] : ''
                    );
                    $as = $this->digital_diary_model->getAssignmentSummaryByTodayWorkIds(array((int) $psaved['id']));
                    $learn = '—';
                    if (isset($as[(int) $psaved['id']])) {
                        $learn = $as[(int) $psaved['id']]['count'] . ' item(s)';
                    }
                    $yesterday_work_rows[] = array(
                        'subject_name' => get_type_name_by_id('subject', (int) $psid),
                        'class_work' => $cw,
                        'home_work' => $hw,
                        'learning' => $learn,
                    );
                }
                break;
            }
        }
        $this->data['yesterday_work_date'] = $prevDate;
        $this->data['yesterday_work_rows'] = $yesterday_work_rows;

        // Prior-day homework (writing_work) % per subject for comparison chart
        $prior_pct_by_subject = array();
        $studentCount = count($students);
        if ($studentCount > 0 && !empty($subjects_for_grid) && !empty($prevDate)) {
            foreach ($subjects_for_grid as $sg) {
                $psid = (int) $sg['subject_id'];
                $prevSaved = $this->digital_diary_model->getStudentWorkReportSavedMap(
                    $prevDate,
                    $branchID,
                    $classID,
                    $sectionID,
                    $psid
                );
                $done = 0;
                foreach ($students as $st) {
                    $stuId = (int) $st['student_id'];
                    $row = isset($prevSaved[$stuId]) ? $prevSaved[$stuId] : array();
                    if (!empty($row['writing_work'])) {
                        $done++;
                    }
                }
                $prior_pct_by_subject[$psid] = round($done * 100 / $studentCount, 2);
            }
        }
        $this->data['prior_pct_by_subject'] = $prior_pct_by_subject;
        $this->data['prior_compare_date'] = $prevDate;

        $this->data['title'] = 'Student Work Report — Daily grid';
        $this->data['sub_page'] = 'digital_diary/student_work_report_entry';
        $this->data['main_menu'] = 'digital_diary';
        $this->data['headerelements'] = array(
            'js' => array('vendor/chartjs/chart.min.js'),
        );
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Save Student Work Report (AJAX POST, frm-submit-data).
     */
    public function student_work_report_save()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        if (!$this->db->table_exists('digital_diary_student_work_report')) {
            echo json_encode(array('status' => 'fail', 'error' => array('form_error' => 'Run application/sql/digital_diary_student_work_report.sql')));
            return;
        }
        $this->load->model('subject_model');
        $this->form_validation->set_rules('work_date', translate('date'), 'trim|required');
        $this->form_validation->set_rules('class_id', translate('class'), 'trim|required|numeric');
        $this->form_validation->set_rules('section_id', translate('section'), 'trim|required|numeric');
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required|numeric');
        }
        if ($this->form_validation->run() === false) {
            echo json_encode(array('status' => 'fail', 'error' => $this->form_validation->error_array()));
            return;
        }
        $branchID = is_superadmin_loggedin() ? (int) $this->input->post('branch_id') : (int) get_loggedin_branch_id();
        $classID = (int) $this->input->post('class_id');
        $sectionID = (int) $this->input->post('section_id');
        $workDate = date('Y-m-d', strtotime($this->input->post('work_date')));

        $isCoordinatorForClass = $this->digital_diary_user_may_coordinate()
            && $this->digital_diary_user_may_coordinate_class_section($classID, $sectionID);
        if (is_teacher_loggedin() && !$isCoordinatorForClass
            && !$this->digital_diary_teacher_may_use_class_section($branchID, $classID, $sectionID)) {
            echo json_encode(array('status' => 'fail', 'error' => array('class_id' => translate('access_denied'))));
            return;
        }
        if ($this->digital_diary_model->isTodayWorkDayFinal($workDate, $branchID, $classID, $sectionID)) {
            // Coordinators may edit SWR only during open/overdue review window (before review submit).
            $coordWindowOk = false;
            if ($isCoordinatorForClass) {
                $win = $this->digital_diary_model->getCoordinatorReviewWindowStatus(
                    $workDate,
                    $branchID,
                    $classID,
                    $sectionID
                );
                $coordWindowOk = in_array($win['status'], array('open', 'overdue'), true);
            }
            if (!$coordWindowOk) {
                echo json_encode(array('status' => 'fail', 'error' => array('form_error' => 'This day is already final submitted.')));
                return;
            }
        }

        $students = $this->digital_diary_model->getStudentsForStudentWorkReport($branchID, $classID, $sectionID);
        $validIds = array();
        foreach ($students as $st) {
            $validIds[] = (int) $st['student_id'];
        }
        $swr = $this->input->post('swr');
        if (!is_array($swr)) {
            $swr = array();
        }
        $attendanceMap = $this->digital_diary_model->getStudentAttendanceMapForDate(
            $workDate,
            $branchID,
            $classID,
            $sectionID
        );

        $isTeacher = is_teacher_loggedin();
        $isClassTeacher = $isTeacher && $this->subject_model->getClassTeacherByClassSection($classID, $sectionID);
        $teacherSubjectIds = array();
        if ($isTeacher) {
            $tq = $this->subject_model->getSubjectByClassSection($classID, $sectionID);
            foreach ($tq->result() as $trow) {
                if (!empty($trow->subject_id)) {
                    $teacherSubjectIds[] = (int) $trow->subject_id;
                }
            }
            $teacherSubjectIds = array_values(array_unique($teacherSubjectIds));
        }

        $staffID = (int) get_loggedin_user_id();
        // Discipline updates: class teacher only among teachers; admin/non-teacher allowed.
        $canEditDiscipline = !$isTeacher || $isClassTeacher;
        $discPost = $this->input->post('disc');
        if (!is_array($discPost)) {
            $discPost = array();
        }

        if ($canEditDiscipline) {
            foreach ($validIds as $stuId) {
                $d = isset($discPost[$stuId]) ? $discPost[$stuId] : (isset($discPost[(string) $stuId]) ? $discPost[(string) $stuId] : array());
                $dress = !empty($d['dress']) ? 1 : 0;
                $conduct = !empty($d['conduct']) ? 1 : 0;
                if (!isset($attendanceMap[$stuId])) {
                    $attendanceMap[$stuId] = array();
                }
                $attendanceMap[$stuId]['discipline_dress'] = $dress;
                $attendanceMap[$stuId]['discipline_conduct'] = $conduct;
                $this->digital_diary_model->updateStudentAttendanceDiscipline(
                    $workDate,
                    $branchID,
                    $classID,
                    $sectionID,
                    $stuId,
                    $dress,
                    $conduct
                );
            }
        }

        foreach ($swr as $subjectID => $swrByStudent) {
            $subjectID = (int) $subjectID;
            if ($subjectID <= 0 || !is_array($swrByStudent)) {
                continue;
            }
            if (!$this->digital_diary_today_work_validate_subject($branchID, $classID, $sectionID, $subjectID)) {
                echo json_encode(array('status' => 'fail', 'error' => array('form_error' => translate('invalid') . ' ' . translate('subject'))));
                return;
            }
            if ($isTeacher && !$isClassTeacher && !$isCoordinatorForClass && !in_array($subjectID, $teacherSubjectIds, true)) {
                echo json_encode(array('status' => 'fail', 'error' => array('form_error' => translate('access_denied'))));
                return;
            }

            $normalized = array();
            foreach ($validIds as $stuId) {
                $r = array();
                if (isset($swrByStudent[$stuId]) && is_array($swrByStudent[$stuId])) {
                    $r = $swrByStudent[$stuId];
                } elseif (isset($swrByStudent[(string) $stuId]) && is_array($swrByStudent[(string) $stuId])) {
                    $r = $swrByStudent[(string) $stuId];
                }
                $r['dress'] = !empty($attendanceMap[$stuId]['discipline_dress']);
                $r['conduct'] = !empty($attendanceMap[$stuId]['discipline_conduct']);
                $normalized[$stuId] = $r;
            }

            $preserveDiscipline = null;

            $this->digital_diary_model->replaceStudentWorkReportBatch(
                $workDate,
                $branchID,
                $classID,
                $sectionID,
                $subjectID,
                $staffID,
                $normalized,
                $validIds,
                $preserveDiscipline
            );

            // Verify portal submissions + optional test marks
            $twMap = $this->digital_diary_model->getTodayWorkMapByClassSectionDate($workDate, $branchID, $classID, $sectionID);
            if (!empty($twMap[$subjectID]['id'])) {
                $twid = (int) $twMap[$subjectID]['id'];
                $marksPost = $this->input->post('marks');
                $marksByStudent = array();
                if (is_array($marksPost) && isset($marksPost[$subjectID]) && is_array($marksPost[$subjectID])) {
                    foreach ($marksPost[$subjectID] as $msid => $mval) {
                        $marksByStudent[(int) $msid] = $mval;
                    }
                }
                $this->digital_diary_model->verifySubmissionsForTodayWork($twid, $staffID, $marksByStudent);
            }
        }

        $verifyActIds = array();
        if (!empty($twMap[$subjectID])) {
            $verifyActIds = $this->digital_diary_model->todayWorkRowActivityIds($twMap[$subjectID]);
        }
        if (empty($verifyActIds)) {
            $verifyActIds = $this->digital_diary_model->getTodayWorkActivityIdsForClassDate($workDate, $branchID, $classID, $sectionID);
        }
        $this->digital_diary_model->addCoordinatorAlertsForActivities(array(
            'branch_id' => $branchID,
            'work_date' => $workDate,
            'class_id' => $classID,
            'section_id' => $sectionID,
            'event_type' => 'teacher_verified',
            'message' => 'Student Work Report verified/submitted for ' . get_type_name_by_id('class', $classID) . '-' . get_type_name_by_id('section', $sectionID) . ' on ' . $workDate,
            'ref_id' => !empty($twMap[$subjectID]['id']) ? (int) $twMap[$subjectID]['id'] : 0,
        ), $verifyActIds);

        set_alert('success', translate('information_has_been_saved_successfully'));
        $q = array(
            'date' => $workDate,
            'class_id' => (int) $classID,
            'section_id' => (int) $sectionID,
            'branch_id' => (int) $branchID,
        );
        echo json_encode(array(
            'status' => 'success',
            'url' => base_url('digital_diary/today_work') . '?' . http_build_query($q),
            'message' => 'Step 1 saved. Continuing to Step 2: Add Today\'s Work.',
        ));
    }

    /**
     * Rows for "Today's Work" / Parent Report syllabus table (subject × lesson / homework / syllabus %).
     *
     * @return array<int,array<string,mixed>>
     */
    protected function digital_diary_build_todays_work_report_rows($branchID, $reportDate, $classID, $sectionID, $subjectID = 0)
    {
        $subjectSummary = $this->digital_diary_model->getLessonSummaryByClassSection($classID, $sectionID, $branchID);
        $todayMap = $this->digital_diary_model->getTodayTeachingActivityMapBySubject($reportDate, $branchID, $classID, $sectionID, $subjectID);
        $savedMap = $this->db->table_exists('digital_diary_today_work')
            ? $this->digital_diary_model->getTodayWorkMapByClassSectionDate($reportDate, $branchID, $classID, $sectionID)
            : array();
        $assignSummary = array();
        if (!empty($savedMap)) {
            $this->digital_diary_model->ensureTodayWorkLearningFlowTables();
            $twIdsForAssign = array();
            foreach ($savedMap as $sv) {
                if (!empty($sv['id'])) {
                    $twIdsForAssign[] = (int) $sv['id'];
                }
            }
            if (!empty($twIdsForAssign)) {
                $assignSummary = $this->digital_diary_model->getAssignmentSummaryByTodayWorkIds($twIdsForAssign);
            }
        }
        $rows = array();
        foreach ($subjectSummary as $subjRow) {
            $sid = (int) $subjRow['subject_id'];
            if (is_teacher_loggedin() && !$this->digital_diary_teacher_may_view_subject($classID, $sectionID, $sid)) {
                continue;
            }
            if (!empty($subjectID) && $sid !== (int) $subjectID) {
                continue;
            }
            $todayItem = isset($todayMap[$sid]) ? $todayMap[$sid] : array();
            $saved = isset($savedMap[$sid]) ? $savedMap[$sid] : null;

            $totalLesson = isset($subjRow['total_lesson']) ? (int) $subjRow['total_lesson'] : 0;
            $nowGoing = '';
            $classWorkStr = '';
            $homeWorkStr = '';
            $sylPct = '—';
            $workPct = '—';

            if (!empty($saved)) {
                $hasMultiLessons = !empty($saved['lesson_ids']) && $this->db->field_exists('lesson_ids', 'digital_diary_today_work');
                if ($hasMultiLessons) {
                    $nowGoing = $this->digital_diary_model->formatMultiLessonLabels($saved['lesson_ids'], $classID, $sectionID, $sid, $branchID);
                } elseif (!empty($saved['lesson_id']) && !empty($saved['lesson_no'])) {
                    $nowGoing = 'Lesson - ' . $saved['lesson_no'] . ', ' . $saved['lesson_name'];
                } elseif (!empty($saved['lesson_id'])) {
                    $les = $this->app_lib->getTable('digital_diary_lesson', array('t.id' => $saved['lesson_id']), true);
                    if (!empty($les)) {
                        $nowGoing = 'Lesson - ' . $les['lesson_no'] . ', ' . $les['lesson_name'];
                    }
                }
                $classWorkStr = $this->digital_diary_model->formatTodayWorkActivityWithDetail(
                    $this->digital_diary_model->formatTodayWorkItemsDisplay(
                        isset($saved['class_work_items']) ? $saved['class_work_items'] : '',
                        $branchID
                    ),
                    isset($saved['cw_detail']) ? $saved['cw_detail'] : ''
                );
                if ($this->db->field_exists('cw_notebook_type_ids', 'digital_diary_today_work') && !empty(trim((string) (isset($saved['cw_notebook_type_ids']) ? $saved['cw_notebook_type_ids'] : '')))) {
                    $cwNbIds = array_filter(array_map('intval', explode(',', $saved['cw_notebook_type_ids'])));
                    $cwNbNames = $this->digital_diary_model->getNotebookTypeNamesByIdsOrdered($cwNbIds);
                    if (!empty($cwNbNames)) {
                        $nbTxt = 'NB: ' . implode(', ', $cwNbNames);
                        $classWorkStr = ($classWorkStr === '—') ? $nbTxt : $classWorkStr . ' · ' . $nbTxt;
                    }
                }
                $hwItems = $this->digital_diary_model->formatTodayWorkActivityWithDetail(
                    $this->digital_diary_model->formatTodayWorkItemsDisplay(
                        isset($saved['home_work_items']) ? $saved['home_work_items'] : '',
                        $branchID
                    ),
                    isset($saved['hw_detail']) ? $saved['hw_detail'] : ''
                );
                $note = isset($saved['home_work_note']) ? trim((string) $saved['home_work_note']) : '';
                if ($hwItems !== '—' && $note !== '') {
                    $homeWorkStr = $hwItems . ' — ' . $note;
                } elseif ($note !== '') {
                    $homeWorkStr = $note;
                } else {
                    $homeWorkStr = $hwItems;
                }
                if ($this->db->field_exists('hw_notebook_type_ids', 'digital_diary_today_work') && !empty(trim((string) (isset($saved['hw_notebook_type_ids']) ? $saved['hw_notebook_type_ids'] : '')))) {
                    $hwNbIds = array_filter(array_map('intval', explode(',', $saved['hw_notebook_type_ids'])));
                    $hwNbNames = $this->digital_diary_model->getNotebookTypeNamesByIdsOrdered($hwNbIds);
                    if (!empty($hwNbNames)) {
                        $nbTxt = 'NB: ' . implode(', ', $hwNbNames);
                        $homeWorkStr = ($homeWorkStr === '—') ? $nbTxt : $homeWorkStr . ' · ' . $nbTxt;
                    }
                }
                if ($this->db->table_exists('digital_diary_lesson_completion') && $totalLesson > 0) {
                    $pct = $this->digital_diary_model->getLessonSyllabusCompletionPercent(
                        $classID,
                        $sectionID,
                        $sid,
                        $branchID
                    );
                    $sylPct = $pct['syllabus_percentage'];
                } elseif (!empty($saved['lesson_id'])) {
                    $pct = $this->digital_diary_model->getLessonSyllabusWorkPercent(
                        (int) $saved['lesson_id'],
                        $classID,
                        $sectionID,
                        $sid,
                        $branchID
                    );
                    $sylPct = $pct['syllabus_percentage'];
                }
            } else {
                $nowGoing = !empty($todayItem['activity_name']) ? $todayItem['activity_name'] : '—';
                $classWorkStr = !empty($todayItem['remarks']) ? $todayItem['remarks'] : '—';
                $homeWorkStr = '—';
                if ($this->db->table_exists('digital_diary_lesson_completion') && $totalLesson > 0) {
                    $pct = $this->digital_diary_model->getLessonSyllabusCompletionPercent(
                        $classID,
                        $sectionID,
                        $sid,
                        $branchID
                    );
                    $sylPct = $pct['syllabus_percentage'];
                }
            }

            if ($this->db->table_exists('digital_diary_student_work_report')) {
                $swrMapForSubject = $this->digital_diary_model->getStudentWorkReportSavedMap(
                    $reportDate,
                    $branchID,
                    $classID,
                    $sectionID,
                    $sid
                );
                $workPct = $this->digital_diary_model->getStudentWorkReportWorkPercentageFromSavedMap($swrMapForSubject);
            }

            $learningWorkStr = '—';
            if (!empty($saved) && !empty($saved['id']) && isset($assignSummary[(int) $saved['id']])) {
                $as = $assignSummary[(int) $saved['id']];
                $learningWorkStr = ((int) $as['count']) . ' item(s)';
                if (!empty($as['labels'])) {
                    $learningWorkStr .= ' — ' . $as['labels'];
                }
                if (!empty($as['has_test'])) {
                    $learningWorkStr .= ' [Test]';
                }
            }

            $rows[] = array(
                'subject_id' => $sid,
                'subject_name' => isset($subjRow['subject_name']) ? $subjRow['subject_name'] : '',
                'subject_group' => isset($subjRow['subject_group']) ? $subjRow['subject_group'] : '',
                'total_lesson' => $totalLesson,
                'now_going_on' => $nowGoing !== '' ? $nowGoing : '—',
                'class_work' => $classWorkStr !== '' ? $classWorkStr : '—',
                'home_work' => $homeWorkStr !== '' ? $homeWorkStr : '—',
                'learning_work' => $learningWorkStr,
                'syllabus_percentage' => $sylPct,
                'student_work_percentage' => $workPct,
            );
        }
        return $rows;
    }

    /**
     * Parent Report Format — daily homework/syllabus + work summary (SWR visual layout).
     */
    public function parent_report_format()
    {
        $this->load->model('subject_model');
        $reportDate = $this->input->get('date');
        if (empty($reportDate)) {
            $reportDate = $this->input->get('date_to');
        }
        if (empty($reportDate) || !strtotime($reportDate)) {
            $reportDate = date('Y-m-d');
        } else {
            $reportDate = date('Y-m-d', strtotime($reportDate));
        }
        $_GET['date'] = $reportDate;
        $_GET['date_from'] = $reportDate;
        $_GET['date_to'] = $reportDate;

        $this->digital_diary_prepare_student_work_eval();
        $this->data['school_name'] = isset($this->data['branch_name']) ? $this->data['branch_name'] : '';
        $this->data['swr_grid_rows'] = isset($this->data['report_rows']) ? $this->data['report_rows'] : array();
        $this->data['skill_summary'] = array();
        $this->data['parent_skill_summary'] = array('date_from' => '', 'date_to' => '', 'window_days' => 15, 'skills' => array());
        $this->data['date_from'] = $reportDate;
        $this->data['date_to'] = $reportDate;
        $this->data['period_info'] = null;

        $branchID = isset($this->data['branch_id']) ? $this->data['branch_id'] : '';
        $reportDate = !empty($this->data['date_to']) ? $this->data['date_to'] : date('Y-m-d');
        $classID = isset($this->data['filter_class_id']) ? (int) $this->data['filter_class_id'] : 0;
        $sectionID = isset($this->data['filter_section_id']) ? (int) $this->data['filter_section_id'] : 0;
        $studentID = isset($this->data['filter_student_id']) ? (int) $this->data['filter_student_id'] : 0;
        $searchQ = isset($this->data['search_q']) ? $this->data['search_q'] : '';

        $this->data['today_date'] = $reportDate;
        $this->data['report_rows'] = array();
        $this->data['student_block'] = null;
        $this->data['attendance_summary'] = '—';
        $this->data['discipline_dress'] = '—';
        $this->data['discipline_conduct'] = '—';
        $this->data['work_summary_subjects'] = array();
        $this->data['grade_display'] = '';

        if (!empty($classID) && !empty($sectionID)) {
            if (is_teacher_loggedin() && !$this->digital_diary_teacher_may_use_class_section($branchID, $classID, $sectionID)) {
                access_denied();
            }
            $this->data['report_rows'] = $this->digital_diary_build_todays_work_report_rows($branchID, $reportDate, $classID, $sectionID, 0);

            $students = $this->digital_diary_model->getStudentsForStudentWorkReport($branchID, $classID, $sectionID);
            foreach ($students as $st) {
                $rollStr = isset($st['roll']) ? (string) $st['roll'] : '';
                if ($searchQ === '' || stripos($st['fullname'], $searchQ) !== false || stripos($rollStr, $searchQ) !== false) {
                    $this->data['student_options'][$st['student_id']] = $st['fullname'];
                }
            }

            $studentValid = false;
            if ($studentID > 0) {
                foreach ($students as $st) {
                    if ((int) $st['student_id'] === $studentID) {
                        $studentValid = true;
                        break;
                    }
                }
            }

            if ($studentValid) {
                $sessionID = (int) get_session_id();
                $this->db->select('s.id as student_id, s.register_no, s.birthday, s.current_address, s.photo, s.mobileno as student_mobile, s.email as student_email, e.roll, c.name as class_name, se.name as section_name, p.father_name, p.mother_name, p.address as parent_address, p.mobileno as parent_mobile, p.email as parent_email, CONCAT_WS(" ", s.first_name, s.last_name) as fullname');
                $this->db->from('enroll e');
                $this->db->join('student s', 's.id = e.student_id', 'inner');
                $this->db->join('class c', 'c.id = e.class_id', 'left');
                $this->db->join('section se', 'se.id = e.section_id', 'left');
                $this->db->join('parent p', 'p.id = s.parent_id', 'left');
                $this->db->where('e.branch_id', (int) $branchID);
                $this->db->where('e.session_id', $sessionID);
                $this->db->where('e.class_id', $classID);
                $this->db->where('e.section_id', $sectionID);
                $this->db->where('s.id', $studentID);
                $this->db->where('s.active', 1);
                $sb = $this->db->get()->row_array();

                if (!empty($sb)) {
                    $addr = '';
                    if (!empty($sb['current_address'])) {
                        $addr = trim((string) $sb['current_address']);
                    } elseif (!empty($sb['parent_address'])) {
                        $addr = trim((string) $sb['parent_address']);
                    }
                    $dob = '';
                    if (!empty($sb['birthday']) && $sb['birthday'] !== '0000-00-00') {
                        $dob = date('d-m-Y', strtotime($sb['birthday']));
                    }
                    $classDisp = isset($sb['class_name']) ? trim((string) $sb['class_name']) : '';
                    if (!empty($sb['section_name'])) {
                        $classDisp = $classDisp !== '' ? $classDisp . ' - ' . trim((string) $sb['section_name']) : trim((string) $sb['section_name']);
                    }
                    $parentMobile = !empty($sb['parent_mobile']) ? trim((string) $sb['parent_mobile']) : '';
                    $studentMobile = !empty($sb['student_mobile']) ? trim((string) $sb['student_mobile']) : '';
                    $parentEmail = !empty($sb['parent_email']) ? trim((string) $sb['parent_email']) : '';
                    $studentEmail = !empty($sb['student_email']) ? trim((string) $sb['student_email']) : '';
                    $this->data['student_block'] = array(
                        'admission_no' => isset($sb['register_no']) ? (string) $sb['register_no'] : '—',
                        'class_name' => $classDisp !== '' ? $classDisp : '—',
                        'section_name' => isset($sb['section_name']) ? (string) $sb['section_name'] : '',
                        'roll' => isset($sb['roll']) ? $sb['roll'] : '—',
                        'student_name' => isset($sb['fullname']) ? (string) $sb['fullname'] : '—',
                        'father_name' => !empty($sb['father_name']) ? (string) $sb['father_name'] : '—',
                        'mother_name' => !empty($sb['mother_name']) ? (string) $sb['mother_name'] : '—',
                        'address' => $addr !== '' ? $addr : '—',
                        'dob' => $dob !== '' ? $dob : '—',
                        'photo' => isset($sb['photo']) ? (string) $sb['photo'] : '',
                        'mobile' => $parentMobile !== '' ? $parentMobile : $studentMobile,
                        'email' => $parentEmail !== '' ? $parentEmail : $studentEmail,
                    );

                    $this->data['parent_skill_summary'] = $this->digital_diary_model->getParentReportSkillSummary(
                        $studentID,
                        $reportDate,
                        $branchID,
                        $classID,
                        $sectionID,
                        15
                    );

                    $attMap = $this->digital_diary_model->getStudentAttendanceMapForDate($reportDate, $branchID, $classID, $sectionID);
                    if (isset($attMap[$studentID])) {
                        $am = $attMap[$studentID];
                        $st = isset($am['status']) ? strtoupper(trim((string) $am['status'])) : '';
                        if ($st === 'P' || $st === 'L' || $st === 'HD' || $st === '') {
                            $this->data['attendance_summary'] = 'Present Today';
                        } elseif ($st === 'A') {
                            $this->data['attendance_summary'] = 'Absent';
                        } else {
                            $this->data['attendance_summary'] = $st !== '' ? $st : '—';
                        }
                        $dressOk = !empty($am['discipline_dress']);
                        $conductOk = !empty($am['discipline_conduct']);
                        $this->data['discipline_dress'] = $dressOk ? 'Completed' : 'Not Completed';
                        $this->data['discipline_conduct'] = $conductOk ? 'Good' : 'Found Guilty';
                    }

                    $syllabusBySubjectId = array();
                    foreach ($this->data['report_rows'] as $rr) {
                        $rid = (int) $rr['subject_id'];
                        if (!isset($syllabusBySubjectId[$rid]) && isset($rr['syllabus_percentage'])) {
                            $syllabusBySubjectId[$rid] = $rr['syllabus_percentage'];
                        }
                    }

                    $assignedSubjects = array();
                    $subQ = $this->subject_model->getAllSubjectsListForClassSection($classID, $sectionID, $branchID);
                    foreach ($subQ->result() as $srow) {
                        if (!empty($srow->subject_id)) {
                            $assignedSubjects[] = array(
                                'subject_id' => (int) $srow->subject_id,
                                'subject_name' => $srow->subjectname,
                            );
                        }
                    }

                    $workSubs = array();
                    $syllabusNums = array();
                    $swrTableOk = $this->db->table_exists('digital_diary_student_work_report');
                    // Only subjects with Today's Work for this date count toward Work Summary.
                    $todayWorkSubjectIds = array();
                    if ($this->db->table_exists('digital_diary_today_work')) {
                        $todayWorkMap = $this->digital_diary_model->getTodayWorkMapByClassSectionDate(
                            $reportDate,
                            $branchID,
                            $classID,
                            $sectionID
                        );
                        foreach ($todayWorkMap as $twSid => $_twRow) {
                            $todayWorkSubjectIds[(int) $twSid] = true;
                        }
                    }

                    $buildWorkSummaryTier = function ($row, $hasTodayWork) {
                        if (!$hasTodayWork || $row === null) {
                            return array(
                                'tier' => 'empty',
                                'text' => '',
                                'fair' => false,
                                'writing' => false,
                                'learn' => false,
                            );
                        }
                        $fair = !empty($row['fair_copy']);
                        $writ = !empty($row['writing_work']);
                        $learn = !empty($row['learning_work']);
                        $done = ($fair ? 1 : 0) + ($writ ? 1 : 0) + ($learn ? 1 : 0);
                        if ($done >= 3) {
                            $tier = 'green';
                            $text = 'Completed';
                        } elseif ($done === 2) {
                            $tier = 'yellow';
                            $text = 'Not Completed';
                        } elseif ($done === 1) {
                            $tier = 'orange';
                            $text = 'Needs Attention';
                        } else {
                            $tier = 'red';
                            $text = 'Not Completed';
                        }
                        return array(
                            'tier' => $tier,
                            'text' => $text,
                            'fair' => $fair,
                            'writing' => $writ,
                            'learn' => $learn,
                        );
                    };

                    foreach ($assignedSubjects as $as) {
                        $subId = (int) $as['subject_id'];
                        $label = isset($as['subject_name']) ? (string) $as['subject_name'] : '';
                        $hasTodayWork = isset($todayWorkSubjectIds[$subId]);
                        $savedMapOne = ($swrTableOk && $hasTodayWork)
                            ? $this->digital_diary_model->getStudentWorkReportSavedMap($reportDate, $branchID, $classID, $sectionID, $subId)
                            : array();
                        $row = isset($savedMapOne[$studentID]) ? $savedMapOne[$studentID] : null;
                        $tiers = $buildWorkSummaryTier($row, $hasTodayWork);
                        $workSubs[] = array(
                            'subject_id' => $subId,
                            'subject_name' => $label,
                            'tier' => $tiers['tier'],
                            'text' => $tiers['text'],
                            'fair' => $tiers['fair'],
                            'writing' => $tiers['writing'],
                            'learn' => $tiers['learn'],
                        );
                        if ($hasTodayWork) {
                            $sp = isset($syllabusBySubjectId[$subId]) ? $syllabusBySubjectId[$subId] : '';
                            if (is_string($sp) && preg_match('/(\d+)/', $sp, $m)) {
                                $syllabusNums[] = (int) $m[1];
                            }
                        }
                    }

                    if (empty($workSubs) && !empty($this->data['report_rows'])) {
                        foreach ($this->data['report_rows'] as $rr) {
                            $subId = (int) $rr['subject_id'];
                            $hasTodayWork = isset($todayWorkSubjectIds[$subId]);
                            $savedMapOne = ($swrTableOk && $hasTodayWork)
                                ? $this->digital_diary_model->getStudentWorkReportSavedMap($reportDate, $branchID, $classID, $sectionID, $subId)
                                : array();
                            $row = isset($savedMapOne[$studentID]) ? $savedMapOne[$studentID] : null;
                            $label = isset($rr['subject_name']) ? $rr['subject_name'] : '';
                            $tiers = $buildWorkSummaryTier($row, $hasTodayWork);
                            $workSubs[] = array(
                                'subject_id' => $subId,
                                'subject_name' => $label,
                                'tier' => $tiers['tier'],
                                'text' => $tiers['text'],
                                'fair' => $tiers['fair'],
                                'writing' => $tiers['writing'],
                                'learn' => $tiers['learn'],
                            );
                            if ($hasTodayWork) {
                                $sp = isset($rr['syllabus_percentage']) ? $rr['syllabus_percentage'] : '';
                                if (is_string($sp) && preg_match('/(\d+)/', $sp, $m)) {
                                    $syllabusNums[] = (int) $m[1];
                                }
                            }
                        }
                    }

                    $this->data['work_summary_subjects'] = $workSubs;

                    $swrById = array();
                    $swrByName = array();
                    foreach ($workSubs as $ws) {
                        if (!empty($ws['subject_id'])) {
                            $swrById[(int) $ws['subject_id']] = $ws;
                        }
                        if (!empty($ws['subject_name'])) {
                            $swrByName[strtolower(trim((string) $ws['subject_name']))] = $ws;
                        }
                    }
                    if (!empty($this->data['swr_grid_rows'][0]['subjects']) && is_array($this->data['swr_grid_rows'][0]['subjects'])) {
                        foreach ($this->data['swr_grid_rows'][0]['subjects'] as $gid => $cell) {
                            $gid = (int) $gid;
                            if ($gid && !isset($swrById[$gid])) {
                                $swrById[$gid] = array(
                                    'fair' => !empty($cell['fair']),
                                    'writing' => !empty($cell['writing']),
                                    'learn' => !empty($cell['learn']),
                                );
                            }
                        }
                    }
                    foreach ($this->data['report_rows'] as $ri => $rr) {
                        $sid = isset($rr['subject_id']) ? (int) $rr['subject_id'] : 0;
                        $nm = isset($rr['subject_name']) ? strtolower(trim((string) $rr['subject_name'])) : '';
                        $ws = null;
                        if ($sid && isset($swrById[$sid])) {
                            $ws = $swrById[$sid];
                        } elseif ($nm !== '' && isset($swrByName[$nm])) {
                            $ws = $swrByName[$nm];
                        }
                        $this->data['report_rows'][$ri]['swr_fair'] = $ws ? !empty($ws['fair']) : false;
                        $this->data['report_rows'][$ri]['swr_writing'] = $ws ? !empty($ws['writing']) : false;
                        $this->data['report_rows'][$ri]['swr_learn'] = $ws ? !empty($ws['learn']) : false;
                        $this->data['report_rows'][$ri]['swr_has'] = $ws !== null;
                    }

                    if (!empty($syllabusNums)) {
                        $avg = (int) round(array_sum($syllabusNums) / count($syllabusNums));
                        if ($avg >= 75) {
                            $this->data['grade_display'] = 'A';
                        } elseif ($avg >= 60) {
                            $this->data['grade_display'] = 'B';
                        } elseif ($avg >= 45) {
                            $this->data['grade_display'] = 'C';
                        } elseif ($avg >= 33) {
                            $this->data['grade_display'] = 'D';
                        } else {
                            $this->data['grade_display'] = 'E';
                        }
                    }
                }
            }
        }

        $this->data['title'] = 'Parent Report Format';
        $this->data['sub_page'] = 'digital_diary/parent_report_format';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * PTM Report Format - content as per PDF spec
     */
    public function ptm_report_format()
    {
        $this->load->model('subject_model');
        $branchID = $this->input->get('branch_id');
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }
        $classID = (int) $this->input->get('class_id');
        $sectionID = (int) $this->input->get('section_id');
        $studentID = (int) $this->input->get('student_id');
        $month = (int) $this->input->get('month');
        $year = (int) $this->input->get('year');
        if ($month < 1 || $month > 12) {
            $month = (int) date('n');
        }
        if ($year < 2000 || $year > 2100) {
            $year = (int) date('Y');
        }
        $daysInMonth = (int) cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $dateStart = sprintf('%04d-%02d-01', $year, $month);
        $dateEnd = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

        $this->data['branch_id'] = $branchID;
        $this->data['school_name'] = !empty($branchID) ? get_type_name_by_id('branch', $branchID) : '';
        $this->data['filter_class_id'] = $classID;
        $this->data['filter_section_id'] = $sectionID;
        $this->data['filter_student_id'] = $studentID;
        $this->data['month'] = $month;
        $this->data['year'] = $year;
        $this->digital_diary_populate_class_section_filters($branchID, $classID);
        $this->data['student_options'] = array('' => translate('select'));
        $this->data['student_block'] = null;
        $this->data['student_valid'] = false;
        $this->data['table_subjects'] = array(
            array('subject_id' => 0, 'subject_name' => 'Hindi'),
            array('subject_id' => 0, 'subject_name' => 'English'),
        );
        $this->data['report_rows'] = array();
        $this->data['summary'] = array(
            'school_open_days' => 0,
            'attend_days' => 0,
            'absent_days' => 0,
            'without_dress' => 0,
            'bad_conduct' => 0,
            'subject_scores' => array(),
            'grade' => '—',
            'attendance_status' => '—',
            'discipline_status' => '—',
            'work_status' => '—',
            'parent_note' => '',
            'is_low' => false,
        );
        $this->data['attendance_summary'] = '—';
        $this->data['discipline_dress'] = '—';
        $this->data['discipline_conduct'] = '—';
        $this->data['work_summary_subjects'] = array();
        $this->data['grade_display'] = '';
        $this->data['parent_skill_summary'] = array('date_from' => '', 'date_to' => '', 'window_days' => 15, 'skills' => array());
        $this->data['month_label'] = date('F Y', strtotime($dateStart));

        if (!empty($classID) && !empty($sectionID)) {
            if (is_teacher_loggedin() && !$this->digital_diary_teacher_may_use_class_section($branchID, $classID, $sectionID)) {
                access_denied();
            }
            $subjectsForGrid = array();
            $allSubQ = $this->subject_model->getAllSubjectsListForClassSection($classID, $sectionID, $branchID);
            foreach ($allSubQ->result() as $srow) {
                if (!empty($srow->subject_id)) {
                    $subId = (int) $srow->subject_id;
                    if (is_teacher_loggedin() && !$this->digital_diary_teacher_may_view_subject($classID, $sectionID, $subId)) {
                        continue;
                    }
                    $subjectsForGrid[] = array(
                        'subject_id' => $subId,
                        'subject_name' => (string) $srow->subjectname,
                    );
                }
            }

            if (!empty($subjectsForGrid)) {
                $this->data['table_subjects'] = $subjectsForGrid;
            }

            $students = $this->digital_diary_model->getStudentsForStudentWorkReport($branchID, $classID, $sectionID);
            foreach ($students as $st) {
                $this->data['student_options'][$st['student_id']] = $st['fullname'];
            }

            $studentValid = false;
            if ($studentID > 0) {
                foreach ($students as $st) {
                    if ((int) $st['student_id'] === $studentID) {
                        $studentValid = true;
                        $this->data['student_valid'] = true;
                        break;
                    }
                }
            }

            if ($studentValid) {
                $sessionID = (int) get_session_id();
                $this->db->select('s.id as student_id, s.register_no, s.birthday, s.current_address, e.roll, c.name as class_name, se.name as section_name, p.father_name, p.mother_name, p.address as parent_address, CONCAT_WS(" ", s.first_name, s.last_name) as fullname');
                $this->db->from('enroll e');
                $this->db->join('student s', 's.id = e.student_id', 'inner');
                $this->db->join('class c', 'c.id = e.class_id', 'left');
                $this->db->join('section se', 'se.id = e.section_id', 'left');
                $this->db->join('parent p', 'p.id = s.parent_id', 'left');
                $this->db->where('e.branch_id', (int) $branchID);
                $this->db->where('e.session_id', $sessionID);
                $this->db->where('e.class_id', $classID);
                $this->db->where('e.section_id', $sectionID);
                $this->db->where('s.id', $studentID);
                $this->db->where('s.active', 1);
                $sb = $this->db->get()->row_array();

                if (!empty($sb)) {
                    $addr = '';
                    if (!empty($sb['current_address'])) {
                        $addr = trim((string) $sb['current_address']);
                    } elseif (!empty($sb['parent_address'])) {
                        $addr = trim((string) $sb['parent_address']);
                    }
                    $dob = '';
                    if (!empty($sb['birthday']) && $sb['birthday'] !== '0000-00-00') {
                        $dob = date('d-m-Y', strtotime($sb['birthday']));
                    }
                    $classDisp = isset($sb['class_name']) ? trim((string) $sb['class_name']) : '';
                    if (!empty($sb['section_name'])) {
                        $classDisp = $classDisp !== '' ? $classDisp . ' - ' . trim((string) $sb['section_name']) : trim((string) $sb['section_name']);
                    }
                    $pendingFee = 0.0;
                    if ($this->db->table_exists('student_fee_allocation')) {
                        $this->db->select('IFNULL(SUM(balance), 0) as total_balance', false);
                        $this->db->from('student_fee_allocation');
                        $this->db->where('student_id', $studentID);
                        $this->db->where('class_id', $classID);
                        $balRow = $this->db->get()->row_array();
                        $pendingFee += (float) (isset($balRow['total_balance']) ? $balRow['total_balance'] : 0);
                    }
                    if ($this->db->table_exists('student_last_balance')) {
                        $lastBal = $this->db->select('last_balance')
                            ->where(array('student_id' => $studentID, 'session_id' => $sessionID))
                            ->get('student_last_balance')
                            ->row('last_balance');
                        if ($lastBal !== null) {
                            $pendingFee += (float) $lastBal;
                        }
                    }
                    if ($pendingFee < 0) {
                        $pendingFee = 0.0;
                    }

                    $this->data['student_block'] = array(
                        'admission_no' => isset($sb['register_no']) ? (string) $sb['register_no'] : '—',
                        'class_name' => $classDisp !== '' ? $classDisp : '—',
                        'roll' => isset($sb['roll']) ? $sb['roll'] : '—',
                        'student_name' => isset($sb['fullname']) ? (string) $sb['fullname'] : '—',
                        'father_name' => !empty($sb['father_name']) ? (string) $sb['father_name'] : '—',
                        'mother_name' => !empty($sb['mother_name']) ? (string) $sb['mother_name'] : '—',
                        'address' => $addr !== '' ? $addr : '—',
                        'dob' => $dob !== '' ? $dob : '—',
                        'pending_fee' => $pendingFee,
                    );
                }

                $schoolDays = 0;
                $attendDays = 0;
                $absentDays = 0;
                $withoutDress = 0;
                $badConduct = 0;
                $subjectTotals = array();
                foreach ($subjectsForGrid as $sg) {
                    $subjectTotals[(int) $sg['subject_id']] = array(
                        'checks' => 0,
                        'fair' => 0,
                        'writing' => 0,
                        'learning' => 0,
                    );
                }

                $tableSubjects = $this->data['table_subjects'];
                $weekendDay = (string) $this->application_model->getWeekends($branchID);
                $weekendSet = array();
                if ($weekendDay !== '') {
                    foreach (explode(',', $weekendDay) as $weekendValue) {
                        $weekendValue = trim($weekendValue);
                        if ($weekendValue !== '') {
                            $weekendSet[$weekendValue] = true;
                        }
                    }
                }
                // Default to Sunday if no weekend config.
                if (empty($weekendSet)) {
                    $weekendSet['0'] = true;
                }
                $holidayDates = $this->digital_diary_get_holiday_dates($branchID);
                $holidaySet = array_fill_keys($holidayDates, true);

                $sr = 0;
                $openDayNo = 0;
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $dow = (string) date('w', strtotime($date));
                    $isWeeklyOff = isset($weekendSet[$dow]);
                    $isBranchHoliday = isset($holidaySet[$date]);
                    $isOpen = $this->digital_diary_is_school_open_day($date, $branchID);

                    if (!$isOpen) {
                        // Show Sundays / weekly offs and configured holidays, marked as Holiday.
                        if ($isWeeklyOff || $isBranchHoliday) {
                            $sr++;
                            $emptySubjects = array();
                            foreach ($tableSubjects as $ts) {
                                $tsid = isset($ts['subject_id']) ? (int) $ts['subject_id'] : 0;
                                $emptySubjects[$tsid] = array('fair' => '', 'writing' => '', 'learn' => '');
                            }
                            $this->data['report_rows'][] = array(
                                'sr' => $sr,
                                'date_disp' => date('d-m-Y', strtotime($date)),
                                't_days' => '',
                                'attendance' => 'Holiday',
                                'dress' => '',
                                'conduct' => '',
                                'subjects' => $emptySubjects,
                                'is_holiday' => true,
                            );
                        }
                        continue;
                    }

                    $sr++;
                    $openDayNo++;
                    $schoolDays++;
                    $attMap = $this->digital_diary_model->getStudentAttendanceMapForDate($date, $branchID, $classID, $sectionID);
                    $rec = isset($attMap[$studentID]) ? $attMap[$studentID] : null;
                    $status = $rec ? strtoupper(trim((string) $rec['status'])) : '';
                    // No attendance record on a school-open day => treat as absent.
                    if ($status === '') {
                        $status = 'A';
                    }
                    $isAbsent = ($status === 'A');
                    $isPresent = !$isAbsent && ($status === 'P' || $status === 'L' || $status === 'HD');
                    if ($isPresent) {
                        $attendDays++;
                    } else {
                        $absentDays++;
                    }

                    $dressOk = null;
                    $conductOk = null;
                    // Absent = discipline negative for that day.
                    if ($isAbsent) {
                        $withoutDress++;
                        $badConduct++;
                    } elseif ($rec && $isPresent) {
                        $dressOk = !empty($rec['discipline_dress']);
                        $conductOk = !empty($rec['discipline_conduct']);
                        if (!$dressOk) {
                            $withoutDress++;
                        }
                        if (!$conductOk) {
                            $badConduct++;
                        }
                    }

                    $subjectsRow = array();
                    foreach ($tableSubjects as $ts) {
                        $tsid = isset($ts['subject_id']) ? (int) $ts['subject_id'] : 0;
                        // Absent day: mark discipline/subject cells as absent (negative).
                        if ($isAbsent) {
                            $subjectsRow[$tsid] = array('fair' => 'A', 'writing' => 'A', 'learn' => 'A');
                            continue;
                        }
                        $subjectsRow[$tsid] = array('fair' => '', 'writing' => '', 'learn' => '');
                        if ($tsid <= 0) {
                            continue;
                        }
                        $saved = $this->digital_diary_model->getStudentWorkReportSavedMap($date, $branchID, $classID, $sectionID, $tsid);
                        if (isset($saved[$studentID])) {
                            $r = $saved[$studentID];
                            if (!empty($r['fair_copy'])) {
                                $subjectsRow[$tsid]['fair'] = 'Y';
                            }
                            if (!empty($r['writing_work'])) {
                                $subjectsRow[$tsid]['writing'] = 'Y';
                            }
                            if (!empty($r['learning_work'])) {
                                $subjectsRow[$tsid]['learn'] = 'Y';
                            }
                        }
                    }

                    foreach ($subjectsForGrid as $sg) {
                        if ($isAbsent) {
                            continue;
                        }
                        $sid = (int) $sg['subject_id'];
                        $savedMap = $this->digital_diary_model->getStudentWorkReportSavedMap($date, $branchID, $classID, $sectionID, $sid);
                        if (isset($savedMap[$studentID])) {
                            $r = $savedMap[$studentID];
                            if (!empty($r['fair_copy'])) {
                                $subjectTotals[$sid]['fair']++;
                                $subjectTotals[$sid]['checks']++;
                            }
                            if (!empty($r['writing_work'])) {
                                $subjectTotals[$sid]['writing']++;
                                $subjectTotals[$sid]['checks']++;
                            }
                            if (!empty($r['learning_work'])) {
                                $subjectTotals[$sid]['learning']++;
                                $subjectTotals[$sid]['checks']++;
                            }
                        }
                    }

                    $attendance = $isAbsent ? 'A' : ($isPresent ? $status : 'A');

                    $this->data['report_rows'][] = array(
                        'sr' => $sr,
                        'date_disp' => date('d-m-Y', strtotime($date)),
                        't_days' => (string) $openDayNo,
                        'attendance' => $attendance,
                        'dress' => $isAbsent ? 'A' : (($isPresent && $dressOk) ? 'Y' : ''),
                        'conduct' => $isAbsent ? 'A' : (($isPresent && $conductOk) ? 'Y' : ''),
                        'subjects' => $subjectsRow,
                        'is_holiday' => false,
                    );
                }

                $scoreBundle = $this->digital_diary_build_subject_work_scores($subjectsForGrid, $subjectTotals, $schoolDays);
                $attendancePct = $schoolDays > 0 ? (int) round(($attendDays / $schoolDays) * 100) : 0;
                $workPct = 0;
                $workCnt = 0;
                $workSummarySubjects = array();
                foreach ($scoreBundle['subject_scores'] as $scRow) {
                    $pct = isset($scRow['percent']) ? (int) $scRow['percent'] : 0;
                    $workPct += $pct;
                    $workCnt++;
                    if ($pct >= 80) {
                        $tier = 'green';
                        $text = 'Completed';
                    } elseif ($pct >= 66) {
                        $tier = 'yellow';
                        $text = 'Not Completed';
                    } elseif ($pct >= 40) {
                        $tier = 'orange';
                        $text = 'Needs Attention';
                    } else {
                        $tier = 'red';
                        $text = 'Not Completed';
                    }
                    $workSummarySubjects[] = array(
                        'subject_id' => isset($scRow['label']) ? 0 : 0,
                        'subject_name' => isset($scRow['label']) ? (string) $scRow['label'] : '',
                        'tier' => $tier,
                        'text' => $text,
                        'score_text' => isset($scRow['text']) ? (string) $scRow['text'] : '',
                        'percent' => $pct,
                    );
                }
                $workPct = $workCnt > 0 ? (int) round($workPct / $workCnt) : 0;

                if ($schoolDays <= 0) {
                    $attendanceSummary = '—';
                } elseif ($attendDays >= $schoolDays) {
                    $attendanceSummary = 'Present';
                } elseif ($attendDays <= 0) {
                    $attendanceSummary = 'Absent';
                } else {
                    $attendanceSummary = $attendDays . '/' . $schoolDays . ' Present';
                }

                $disciplineDress = ($withoutDress <= 0) ? 'Completed' : 'Not Completed';
                $disciplineConduct = ($badConduct <= 0) ? 'Good' : 'Found Guilty';

                $attendanceLow = ($attendancePct < 75);
                $disciplineLow = ($withoutDress > 0 || $badConduct > 0);
                $workLow = ($workPct < 66 || strtoupper((string) $scoreBundle['grade']) === 'D');
                $isLow = ($attendanceLow || $disciplineLow || $workLow);

                $statusParts = array();
                if ($attendanceLow) {
                    $statusParts[] = 'attendance status';
                }
                if ($workLow) {
                    $statusParts[] = 'work status';
                }
                if ($disciplineLow) {
                    $statusParts[] = 'discipline status';
                }
                if ($isLow) {
                    $partsCopy = $statusParts;
                    $last = array_pop($partsCopy);
                    $joined = !empty($partsCopy) ? implode(', ', $partsCopy) . ' and ' . $last : $last;
                    $parentNote = 'Dear parents, Your child ' . $joined . ' is low.<br>Be careful & come to school and meet to principal sir';
                } else {
                    $parentNote = 'Dear parents, Your child attendance, work and discipline status is satisfactory.<br>Please continue supporting your child.';
                }

                $skillDateTo = $dateEnd;
                if (strtotime($skillDateTo) > strtotime(date('Y-m-d'))) {
                    $skillDateTo = date('Y-m-d');
                }
                $this->data['parent_skill_summary'] = $this->digital_diary_model->getParentReportSkillSummary(
                    $studentID,
                    $skillDateTo,
                    $branchID,
                    $classID,
                    $sectionID,
                    15
                );

                $this->data['attendance_summary'] = $attendanceSummary;
                $this->data['discipline_dress'] = $disciplineDress;
                $this->data['discipline_conduct'] = $disciplineConduct;
                $this->data['work_summary_subjects'] = $workSummarySubjects;
                $this->data['grade_display'] = $scoreBundle['grade'];

                $this->data['summary'] = array(
                    'school_open_days' => $schoolDays,
                    'attend_days' => $attendDays,
                    'absent_days' => $absentDays,
                    'attendance_pct' => $attendancePct,
                    'without_dress' => $withoutDress,
                    'bad_conduct' => $badConduct,
                    'subject_scores' => $scoreBundle['subject_scores'],
                    'grade' => $scoreBundle['grade'],
                    'work_pct' => $workPct,
                    'attendance_status' => $attendanceLow ? 'Low' : 'Good',
                    'discipline_status' => $disciplineLow ? 'Low' : 'Good',
                    'work_status' => $workLow ? 'Low' : 'Good',
                    'parent_note' => $parentNote,
                    'is_low' => $isLow,
                );
            }
        }

        $this->data['title'] = 'PTM Report Format';
        $this->data['sub_page'] = 'digital_diary/ptm_report_format';
        $this->data['main_menu'] = 'digital_diary';
        $this->load->view('layout/index', $this->data);
    }
}
