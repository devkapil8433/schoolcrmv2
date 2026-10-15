<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Onlineexam extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('datatables');
        $this->load->model('onlineexam_model');
        $this->load->model('email_model');
        $this->load->model('sms_model');
        $this->load->model('subject_model');
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/summernote/summernote.css',
                'vendor/bootstrap-timepicker/css/bootstrap-timepicker.css',
            ),
            'js' => array(
                'vendor/summernote/summernote.js',
                'vendor/bootstrap-timepicker/bootstrap-timepicker.js',
                'js/online-exam.js',
                'js/question-hindi-input.js',
                'js/question-osk.js',
            ),
        );
        if (!moduleIsEnabled('online_exam')) {
            access_denied();
        }
    }

    /* online exam controller */
    public function index()
    {
        // check access permission
        if (!get_permission('online_exam', 'is_view')) {
            access_denied();
        }
        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['examList'] = $this->onlineexam_model->examList();
        $this->data['title'] = translate('online_exam');
        $this->data['sub_page'] = 'onlineexam/index';
        $this->data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $this->data);
    }

    /* online exam table list controller */
    public function getExamListDT()
    {
        if ($_POST) {
            $currencySymbol = $this->data['global_config']['currency_symbol'];
            echo $this->onlineexam_model->examListDT($currencySymbol);
        }
    }

    /* online exam edit controller */
    public function edit($id = '')
    {
        // check access permission
        if (!get_permission('online_exam', 'is_edit')) {
            access_denied();
        }
        if ($_POST) {
            $this->exam_validation();
            if ($this->form_validation->run() == true) {
                $post = $this->input->post();
                $branchID = $this->application_model->get_branch_id();
                $this->onlineexam_model->saveExam($post, $branchID);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('onlineexam');
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }
        $this->data['onlineexam'] = $this->app_lib->getTable('online_exam', array('t.id' => $id), true);
        $this->data['title'] = translate('online_exam');
        $this->data['sub_page'] = 'onlineexam/edit';
        $this->data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $this->data);
    }

    protected function exam_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }

        $this->form_validation->set_rules('title', translate('title'), 'trim|required');
        $this->form_validation->set_rules('class_id', translate('class'), 'trim|required');
        $this->form_validation->set_rules('section[]', translate('section'), 'trim|required');
        $this->form_validation->set_rules('subject[]', translate('subject'), 'trim|required');
        $this->form_validation->set_rules('start_date', translate('start_date'), 'trim|required');
        $this->form_validation->set_rules('end_date', translate('end_date'), 'trim|required');
        $this->form_validation->set_rules('start_time', translate('start_time'), 'trim|required');
        $this->form_validation->set_rules('end_time', translate('end_time'), 'trim|required');
        $this->form_validation->set_rules('duration', translate('duration'), 'trim|required|callback_validate_duration');
        $this->form_validation->set_rules('participation_limit', translate('limits_of_participation'), 'trim|required|numeric');
        $this->form_validation->set_rules('mark_type', translate('mark_type'), 'trim|required');
        $this->form_validation->set_rules('passing_mark', translate('passing_mark'), 'trim|required|numeric');
        $this->form_validation->set_rules('instruction', translate('instruction'), 'trim|required');
        $this->form_validation->set_rules('question_type', translate('question_type'), 'trim|required');
        $this->form_validation->set_rules('publish_result', translate('result_publish'), 'trim|required');
        $this->form_validation->set_rules('exam_type', translate('exam_type'), 'trim|required');
        $examType = $this->input->post('exam_type');
        if (!empty($examType) && $examType == 1) {
            $this->form_validation->set_rules('exam_fee', translate('exam_fee'), 'trim|required|numeric');
        }
    }

    public function validate_duration($value)
    {
        if (!empty($value)) {
            if ($value != "0:00") {
                if (!preg_match('/^(?(?=\d{2})(?:2[0-3]|[01][0-9])|[0-9]):[0-5][0-9]$/', $value)) {
                    $this->form_validation->set_message('validate_duration', 'The %s field must be H:mm');
                    return false;
                }
            } else {
                $this->form_validation->set_message('validate_duration', 'The %s field can not be 0:00.');
                return false;
            }
            return true;
        }
        return true;
    }

    /* online exam save in DB controller */
    public function exam_save()
    {
        if ($_POST) {
            $this->exam_validation();
            if ($this->form_validation->run() == true) {
                $post = $this->input->post();
                $branchID = $this->application_model->get_branch_id();
                //online exam save in DB
                $this->onlineexam_model->saveExam($post, $branchID);
                
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = array('status' => 'success');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
        }
    }

    /* online exam delete in DB controller */
    public function delete($id = '')
    {
        if (get_permission('online_exam', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('id', $id);
            $this->db->delete('online_exam');
            $done = $this->db->affected_rows();
            if ($done == true) {
                $this->db->where('onlineexam_id', $id);
                $this->db->delete('questions_manage');

                $this->db->where('online_exam_id', $id);
                $this->db->delete('online_exam_submitted');

                $this->db->where('online_exam_id', $id);
                $this->db->delete('online_exam_attempts');

                $this->db->where('online_exam_id', $id);
                $this->db->delete('online_exam_answer');

                $this->db->where('exam_id', $id);
                $this->db->delete('online_exam_payment');
            }
        }
    }

    public function question_list($id='')
    {
        if (!get_permission('online_exam', 'is_view')) {
            access_denied();
        }
        $exam = $this->onlineexam_model->getExamDetails($id, false);
        if (empty($exam)) {
            access_denied();
        }
        $this->data['exam'] = $exam;
        $this->data['title'] = translate('view') . " " . translate('question');
        $this->data['sub_page'] = 'onlineexam/question_list';
        $this->data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $this->data);
    }

    public function remove_question($id='')
    {
        if (get_permission('online_exam', 'is_edit')) {
            $this->db->select('questions_manage.id');
            $this->db->from('questions_manage');
            $this->db->join('online_exam', 'online_exam.id = questions_manage.onlineexam_id', 'inner');
            $this->db->where('questions_manage.id', $id);
            $this->db->where('online_exam.session_id', get_session_id());
            if (!is_superadmin_loggedin()) {
                $this->db->where('online_exam.branch_id', get_loggedin_branch_id());
            }
            $row = $this->db->get();
            if ($row->num_rows() > 0) {
                $this->db->where('id', $id);
                $this->db->delete('questions_manage');
            }
        }
    }

    /* Online exam question controller — list layout matches digital_diary/lesson (class, section, subject group, subjects) */
    public function question()
    {
        if (!get_permission('question_bank', 'is_view')) {
            access_denied();
        }
        if (is_superadmin_loggedin()) {
            $branchID = $this->input->get_post('branch_id');
            if (empty($branchID)) {
                $branchID = get_loggedin_branch_id();
            }
        } else {
            $branchID = get_loggedin_branch_id();
        }
        $this->data['branch_id'] = $branchID;
        $this->data['branch_name'] = !empty($branchID) ? get_type_name_by_id('branch', $branchID) : '';
        $this->data['question_flow'] = !empty($branchID)
            ? $this->onlineexam_model->getQuestionFlowByBranch($branchID)
            : array();
        $this->data['question_count'] = 0;
        foreach ($this->data['question_flow'] as $classSection) {
            $this->data['question_count'] += (int) $classSection['question_count'];
        }

        $this->data['title'] = translate('question');
        $this->data['sub_page'] = 'onlineexam/question';
        $this->data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Question bank — drill-down by class & section (like digital_diary/lesson_class).
     */
    public function question_class($class_id = '', $section_id = '')
    {
        if (!get_permission('question_bank', 'is_view')) {
            access_denied();
        }
        if (empty($class_id) || empty($section_id)) {
            redirect(base_url('onlineexam/question'));
        }
        $branchID = $this->input->get('branch_id');
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }
        if (!is_superadmin_loggedin()) {
            $branchID = get_loggedin_branch_id();
        }
        $this->data['branch_id'] = $branchID;
        $this->data['branch_name'] = !empty($branchID) ? get_type_name_by_id('branch', $branchID) : '';
        $this->data['class_id'] = $class_id;
        $this->data['section_id'] = $section_id;
        $this->data['class_name'] = get_type_name_by_id('class', $class_id);
        $this->data['section_name'] = get_type_name_by_id('section', $section_id);
        $this->data['subject_summary'] = $this->onlineexam_model->getQuestionSummaryByClassSection($class_id, $section_id, $branchID);
        $this->data['title'] = translate('question') . ' - ' . $this->data['class_name'] . ' - ' . $this->data['section_name'];
        $this->data['sub_page'] = 'onlineexam/question_class';
        $this->data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * List all questions for one subject (like digital_diary/lesson_subject).
     */
    public function question_subject($class_id = '', $section_id = '', $subject_id = '')
    {
        if (!get_permission('question_bank', 'is_view')) {
            access_denied();
        }
        if (empty($class_id) || empty($section_id) || empty($subject_id)) {
            redirect(base_url('onlineexam/question'));
        }
        $branchID = $this->input->get('branch_id');
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }
        if (!is_superadmin_loggedin()) {
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
        $lessonIdsRaw = $this->input->get('lesson_ids');
        $chapterIdsRaw = $this->input->get('chapter_ids');
        $questionIdsRaw = $this->input->get('question_ids');
        $lessonIds = array();
        $chapterIds = array();
        $questionIds = array();
        if (!empty($lessonIdsRaw)) {
            $lessonIds = array_filter(array_map('intval', explode(',', (string) $lessonIdsRaw)), function ($id) {
                return $id > 0;
            });
            $lessonIds = array_values(array_unique($lessonIds));
        }
        if (!empty($chapterIdsRaw)) {
            $chapterIds = array_filter(array_map('intval', explode(',', (string) $chapterIdsRaw)), function ($id) {
                return $id > 0;
            });
            $chapterIds = array_values(array_unique($chapterIds));
        }
        if (!empty($questionIdsRaw)) {
            $questionIds = array_filter(array_map('intval', explode(',', (string) $questionIdsRaw)), function ($id) {
                return $id > 0;
            });
            $questionIds = array_values(array_unique($questionIds));
        }
        $this->data['selected_lesson_ids'] = $lessonIds;
        $this->data['selected_chapter_ids'] = $chapterIds;
        $this->data['selected_question_ids'] = $questionIds;
        $filterExerciseId = (int) $this->input->get('exercise_id');
        $this->data['filter_exercise_id'] = $filterExerciseId;
        $this->data['question_subject_show_lesson'] = $this->db->field_exists('lesson_id', 'questions')
            && $this->db->table_exists('digital_diary_lesson');
        $this->data['questions_list'] = $this->onlineexam_model->getQuestionsByClassSectionSubject(
            $class_id,
            $section_id,
            $subject_id,
            $branchID,
            $lessonIds,
            $chapterIds,
            $questionIds,
            $filterExerciseId
        );
        $this->data['title'] = translate('question') . ' — ' . $this->data['subject_name'];
        $this->data['sub_page'] = 'onlineexam/question_subject';
        $this->data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * One-time / maintenance: set questions.lesson_id from digital_diary_lesson for the same
     * branch, class, section, and subject. Requires question bank edit permission.
     *
     * Query: strategy=round_robin (default) or first | session_id=N (optional academic session)
     * Example: onlineexam/questions_lesson_backfill?strategy=first
     */
    public function questions_lesson_backfill()
    {
        if (!get_permission('question_bank', 'is_edit')) {
            access_denied();
        }
        $this->ensureQuestionsLessonIdColumn();
        $sessionGet = $this->input->get('session_id');
        $sessionId = null;
        if ($sessionGet !== null && $sessionGet !== '') {
            $sessionId = (int) $sessionGet;
        }
        $strategy = $this->input->get('strategy');
        $strategy = ($strategy === 'first') ? 'first' : 'round_robin';
        $result = $this->onlineexam_model->backfillQuestionsLessonId($sessionId, $strategy);
        set_alert('success', $result['message']);
        redirect(base_url('onlineexam/question'));
    }

    public function getQuestionListDT()
    {
        if ($_POST) {
            $postData = $this->input->post();
            echo $this->onlineexam_model->questionListDT($postData);
        }
    }

    public function getQuestionListForAddDT()
    {
        if ($_POST) {
            $postData = $this->input->post();
            echo $this->onlineexam_model->questionListForAddDT($postData);
        }
    }

    public function question_add()
    {
        if (!get_permission('question_bank', 'is_add')) {
            access_denied();
        }
        $this->ensureQuestionTypeTable();
        $this->ensureQuestionsCustomTypeColumn();
        $this->ensureQuestionsLessonIdColumn();
        $this->data['branch_id'] = $this->application_model->get_branch_id();
        if (empty($this->data['branch_id'])) {
            $this->data['branch_id'] = get_loggedin_branch_id();
        }
        if (is_superadmin_loggedin()) {
            $filterBranch = (int) $this->input->get('branch_id');
            if ($filterBranch < 1) {
                $filterBranch = (int) $this->input->post('branch_id');
            }
        } else {
            $filterBranch = (int) get_loggedin_branch_id();
        }
        if ($filterBranch < 1) {
            $filterBranch = (int) $this->data['branch_id'];
        }
        $this->data['prefill_branch_id'] = $filterBranch;
        $this->data['prefill_class_id'] = (int) ($this->input->get('class_id') ?: $this->input->post('class_id'));
        $this->data['prefill_section_id'] = (int) ($this->input->get('section_id') ?: $this->input->post('section_id'));
        $this->data['prefill_subject_id'] = (int) ($this->input->get('subject_id') ?: $this->input->post('subject_id'));
        $this->data['prefill_exercise_id'] = (int) ($this->input->get('exercise_id') ?: $this->input->post('exercise_id'));
        $this->data['prefill_lesson_id'] = (int) ($this->input->get('lesson_id') ?: $this->input->post('lesson_id'));
        $branchID = $filterBranch > 0 ? $filterBranch : (int) $this->data['branch_id'];
        // Exercise is optional — do not auto-select the first exercise
        if (!empty($branchID)) {
            $this->seedDefaultQuestionTypes($branchID);
        }
        $this->data['digital_diary_lesson_enabled'] = $this->db->table_exists('digital_diary_lesson');
        $this->data['digital_diary_lessons'] = array();
        if ($this->data['digital_diary_lesson_enabled']) {
            $this->load->model('digital_diary_model');
            $classId = (int) $this->data['prefill_class_id'];
            $sectionId = (int) $this->data['prefill_section_id'];
            $subjectId = (int) $this->data['prefill_subject_id'];
            $lessonBranchId = $branchID;
            if ($classId > 0 && $sectionId > 0 && $subjectId > 0) {
                $this->data['digital_diary_lessons'] = $this->digital_diary_model->getLessonsBySubject($classId, $sectionId, $subjectId, $lessonBranchId);
            }
            // Prefilled lesson may belong to another subject (legacy/mis-assigned data); include it so it preselects
            $prefillLessonId = (int) $this->data['prefill_lesson_id'];
            if ($prefillLessonId > 0) {
                $found = false;
                foreach ($this->data['digital_diary_lessons'] as $lessonRow) {
                    if ((int) $lessonRow['id'] === $prefillLessonId) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $extraLesson = $this->app_lib->getTable('digital_diary_lesson', array('t.id' => $prefillLessonId), true);
                    if (!empty($extraLesson)) {
                        $this->data['digital_diary_lessons'][] = $extraLesson;
                    }
                }
            }
        }
        $this->data['branch_question_types'] = $this->onlineexam_model->getBranchQuestionTypes($branchID);
        $this->data['title'] = translate('question');
        $this->data['sub_page'] = 'onlineexam/question_add';
        $this->data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $this->data);
    }

    public function question_edit($id = '')
    {
        if (!get_permission('question_bank', 'is_edit')) {
            access_denied();
        }
        $this->ensureQuestionsLessonIdColumn();
        $this->data['branch_id'] = $this->application_model->get_branch_id();
        $this->data['questions'] = $this->app_lib->getTable('questions', array('t.id' => $id), true);
        $this->data['digital_diary_lesson_enabled'] = $this->db->table_exists('digital_diary_lesson');
        $this->data['digital_diary_lessons'] = array();
        if ($this->data['digital_diary_lesson_enabled'] && !empty($this->data['questions'])) {
            $this->load->model('digital_diary_model');
            $qrow = $this->data['questions'];
            $classId = (int) $this->input->post('class_id');
            if (!$classId) {
                $classId = (int) $qrow['class_id'];
            }
            $sectionId = (int) $this->input->post('section_id');
            if (!$sectionId) {
                $sectionId = (int) $qrow['section_id'];
            }
            $subjectId = (int) $this->input->post('subject_id');
            if (!$subjectId) {
                $subjectId = (int) $qrow['subject_id'];
            }
            $lessonBranchId = (int) $qrow['branch_id'];
            if ($classId > 0 && $sectionId > 0 && $subjectId > 0) {
                $this->data['digital_diary_lessons'] = $this->digital_diary_model->getLessonsBySubject($classId, $sectionId, $subjectId, $lessonBranchId);
            }
        }
        $this->load->model('onlineexam_subjective_model');
        $this->data['question_subjective'] = $this->onlineexam_subjective_model->get_by_question_id($id);
        $this->data['title'] = translate('question_edit');
        $this->data['sub_page'] = 'onlineexam/question_edit';
        $this->data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $this->data);
    }

    public function question_edit_save($id = '')
    {
        if (!get_permission('question_bank', 'is_edit')) {
            ajax_access_denied();
        }
        $this->ensureQuestionsCustomTypeColumn();
        $this->ensureQuestionsLessonIdColumn();
        if ($_POST) {
            $this->question_validation();
            if ($this->form_validation->run() == true) {
                $this->onlineexam_model->saveQuestions();
                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('onlineexam/question');
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }
    }

    protected function question_validation()
    {
        $questionType = $this->input->post('question_type');
        $this->form_validation->set_rules('question_level', translate('question_level'), 'trim|required');
        $this->form_validation->set_rules('exercise_id', translate('exercise'), 'trim');
        $this->form_validation->set_rules('question', translate('question'), 'trim|required');
        if ($questionType == 1) {
            $this->form_validation->set_rules('option1', translate('option') . " " . 1, 'trim|required');
            $this->form_validation->set_rules('option2', translate('option') . " " . 2, 'trim|required');
            $this->form_validation->set_rules('answer', translate('answer'), 'trim|required');
        }
        if ($questionType == 2) {
            $this->form_validation->set_rules('option1', translate('option') . " " . 1, 'trim|required');
            $this->form_validation->set_rules('option2', translate('option') . " " . 2, 'trim|required');
            $this->form_validation->set_rules('option3', translate('option') . " " . 3, 'trim|required');
            $this->form_validation->set_rules('option4', translate('option') . " " . 4, 'trim|required');
            $this->form_validation->set_rules('answer[]', translate('answer'), 'trim|required');
        }
        if ($questionType == 3 || $questionType == 4 || in_array((int) $questionType, array(8, 9, 10, 11), true)) {
            $this->form_validation->set_rules('answer', translate('answer'), 'trim|required');
        }
        // Reading / writing (6–7): reference answer optional; AI rubric is primary
        if ($questionType == 5) {
            $this->form_validation->set_rules('match_left_1', translate('option') . ' 1 (left)', 'trim|required');
            $this->form_validation->set_rules('match_left_2', translate('option') . ' 2 (left)', 'trim|required');
            $this->form_validation->set_rules('match_left_3', translate('option') . ' 3 (left)', 'trim|required');
            $this->form_validation->set_rules('match_left_4', translate('option') . ' 4 (left)', 'trim|required');
            $this->form_validation->set_rules('option1', translate('option') . ' 1 (right)', 'trim|required');
            $this->form_validation->set_rules('option2', translate('option') . ' 2 (right)', 'trim|required');
            $this->form_validation->set_rules('option3', translate('option') . ' 3 (right)', 'trim|required');
            $this->form_validation->set_rules('option4', translate('option') . ' 4 (right)', 'trim|required');
        }
    }

    public function question_save()
    {
        if (!get_permission('question_bank', 'is_add')) {
            ajax_access_denied();
        }
        $this->ensureQuestionsCustomTypeColumn();
        $this->ensureQuestionsLessonIdColumn();
        $this->ensureQuestionsExerciseIdNullable();
        $this->question_validation();
        if ($this->form_validation->run() == true) {
            $this->onlineexam_model->saveQuestions();
            $message = translate('information_has_been_saved_successfully');
            $array = array('status' => 'success', 'message' => $message);
        } else {
            $error = $this->form_validation->error_array();
            $array = array('status' => 'fail', 'error' => $error);
        }
        echo json_encode($array);
    }

    /**
     * Re-run AI evaluation for one stored answer (admin / teacher).
     */
    public function subjective_reevaluate()
    {
        if (!get_permission('question_bank', 'is_edit')) {
            ajax_access_denied();
        }
        $answerId = (int) $this->input->post('answer_id');
        if ($answerId < 1) {
            echo json_encode(array('status' => 'fail', 'error' => 'Invalid answer'));
            return;
        }
        $this->load->model('onlineexam_subjective_model');
        if (!$this->onlineexam_subjective_model->table_ready()) {
            echo json_encode(array('status' => 'fail', 'error' => 'Subjective tables not installed'));
            return;
        }
        $this->onlineexam_subjective_model->ensure_evaluation_row($answerId);
        $this->onlineexam_subjective_model->update_evaluation($answerId, array(
            'manual_override_marks' => null,
            'manual_override_by' => null,
            'manual_override_at' => null,
        ));
        $this->load->library('subjective_evaluation_service');
        if (function_exists('set_time_limit')) {
            @set_time_limit(120);
        }
        $r = $this->subjective_evaluation_service->evaluate_answer_by_id($answerId);
        echo json_encode(array(
            'status' => $r['ok'] ? 'success' : 'fail',
            'error' => isset($r['error']) ? $r['error'] : '',
        ));
    }

    /**
     * Manual marks override for a subjective evaluation row.
     */
    public function subjective_override_marks()
    {
        if (!get_permission('question_bank', 'is_edit')) {
            ajax_access_denied();
        }
        $answerId = (int) $this->input->post('answer_id');
        $marks = $this->input->post('manual_marks');
        if ($answerId < 1 || !is_numeric($marks)) {
            echo json_encode(array('status' => 'fail', 'error' => 'Invalid input'));
            return;
        }
        $this->load->model('onlineexam_subjective_model');
        if (!$this->onlineexam_subjective_model->table_ready()) {
            echo json_encode(array('status' => 'fail', 'error' => 'Subjective tables not installed'));
            return;
        }
        $this->onlineexam_subjective_model->ensure_evaluation_row($answerId);
        $this->onlineexam_subjective_model->update_evaluation($answerId, array(
            'manual_override_marks' => (float) $marks,
            'manual_override_by' => get_loggedin_user_id(),
            'manual_override_at' => date('Y-m-d H:i:s'),
            'status' => 'completed',
            'last_error' => null,
        ));
        echo json_encode(array('status' => 'success'));
    }

    public function getQuestion()
    {
        $id = $this->input->post('id');
        $this->data['questions'] = $this->onlineexam_model->get('questions', array('id' => $id), true);
        $this->load->view('onlineexam/question_view', $this->data);
    }

    public function question_delete($id = '')
    {
        if (!get_permission('question_bank', 'is_delete')) {
            return;
        }
        $id = (int) $id;
        if ($id < 1) {
            return;
        }
        $this->deleteQuestionsByIds(array($id));
    }

    /**
     * Bulk delete selected question bank rows (POST ids[]).
     */
    public function question_delete_multi()
    {
        if (!$this->input->post()) {
            echo json_encode(array('status' => 'fail', 'message' => 'Invalid request.'));
            return;
        }
        if (!get_permission('question_bank', 'is_delete')) {
            ajax_access_denied();
        }
        $ids = $this->input->post('ids');
        if (!is_array($ids)) {
            $ids = array();
        }
        $cleanIds = array();
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $cleanIds[$id] = $id;
            }
        }
        $cleanIds = array_values($cleanIds);
        if (empty($cleanIds)) {
            echo json_encode(array('status' => 'fail', 'message' => 'No questions selected.'));
            return;
        }
        $deleted = $this->deleteQuestionsByIds($cleanIds);
        echo json_encode(array(
            'status' => 'success',
            'deleted' => $deleted,
            'message' => $deleted . ' question(s) deleted.',
        ));
    }

    /**
     * Delete question rows (branch-scoped for non-superadmin) and related links.
     *
     * @param array $ids
     * @return int number deleted
     */
    protected function deleteQuestionsByIds(array $ids)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            return 0;
        }

        $this->db->select('id');
        $this->db->from('questions');
        $this->db->where_in('id', $ids);
        if (!is_superadmin_loggedin()) {
            $this->db->where('branch_id', get_loggedin_branch_id());
        }
        $allowed = $this->db->get()->result_array();
        $allowedIds = array();
        foreach ($allowed as $row) {
            $allowedIds[] = (int) $row['id'];
        }
        if (empty($allowedIds)) {
            return 0;
        }

        if ($this->db->table_exists('questions_manage')) {
            $this->db->where_in('question_id', $allowedIds);
            $this->db->delete('questions_manage');
        }
        if ($this->db->table_exists('onlineexam_question_subjective')) {
            $this->db->where_in('question_id', $allowedIds);
            $this->db->delete('onlineexam_question_subjective');
        }

        $this->db->where_in('id', $allowedIds);
        $this->db->delete('questions');
        return count($allowedIds);
    }

    public function manage_question($examid = '')
    {
        if (!get_permission('add_questions', 'is_add')) {
            access_denied();
        }
        $this->data['questionType'] = $this->input->post('question_type');
        $this->data['questionLevel'] = $this->input->post('question_level');
        $this->data['classID'] = $this->input->post('class_id');
        $this->data['sectionID'] = $this->input->post('section_id');
        $this->data['subjectID'] = $this->input->post('subject_id');
        $exam = $this->onlineexam_model->get('online_exam', array('id' => $examid), true);
        $this->data['exam'] = $exam;
        $this->data['title'] = translate('manage') . " " . translate('question');
        $this->data['sub_page'] = 'onlineexam/manage_question';
        $this->data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $this->data);
    }

    public function getQuestionDT()
    {
        if ($_POST) {
            $postData = $this->input->post();
            echo $this->onlineexam_model->questionList($postData);
        }
    }

    public function question_assign()
    {
        if (!get_permission('add_questions', 'is_add')) {
            ajax_access_denied();
        }
        if ($_POST) {
            $inputQuestions = $this->input->post('question');
            $examID = $this->input->post('exam_id');
            $negMark = $this->db->select('neg_mark')->where('id', $examID)->get('online_exam')->row()->neg_mark;
            foreach ($inputQuestions as $key => $value) {
                $this->form_validation->set_rules("question[$key][marks]", translate('marks'), 'trim|required|numeric');
                if ($negMark == 1) {
                    $this->form_validation->set_rules("question[$key][negative_marks]", translate('negative_marks'), 'trim|required|numeric');
                }
            }
            if ($this->form_validation->run() == true) {
                $questionsID = array();
                $cb_questionsID = array();
                $insertData = array();
                foreach ($inputQuestions as $key => $value) {
                    $questionsID[] = $value['id'];
                    if (isset($value['cb_id'])) {
                        $questionID = $value['cb_id'];
                        $cb_questionsID[] = $questionID;

                        $this->db->where(['question_id' => $questionID, 'onlineexam_id' => $examID]);
                        $query = $this->db->get('questions_manage');
                        $result = $query->num_rows();
                        if ($result > 0) {
                            $updateData = array(
                                'marks' => $value['marks'],
                                'neg_marks' => (empty($value['negative_marks']) ? 0 : $value['negative_marks']),
                            );
                            $this->db->where('id', $query->row()->id);
                            $this->db->update('questions_manage', $updateData);
                        } else {
                            $insertData[] = array(
                                'question_id' => $questionID,
                                'onlineexam_id' => $examID,
                                'marks' => $value['marks'],
                                'neg_marks' => (empty($value['negative_marks']) ? 0 : $value['negative_marks']),
                            );
                        }
                    }
                }
                if (!empty($insertData)) {
                    $this->db->insert_batch('questions_manage', $insertData);
                }
                $result = array_diff($questionsID, $cb_questionsID);
                if (!empty($result)) {
                    $this->db->where('onlineexam_id', $examID);
                    $this->db->where_in('question_id', $result);
                    $this->db->delete('questions_manage');
                }
                $array = array('status' => 'success', 'message' => translate('information_has_been_saved_successfully'));
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
        }
    }

    // add new exercise
    public function exercise()
    {
        if (!get_permission('exercise', 'is_view')) {
            access_denied();
        }
        if (isset($_POST['exercise'])) {
            if (!get_permission('exercise', 'is_add')) {
                access_denied();
            }
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            }
            $this->form_validation->set_rules('class_id', translate('class'), 'required');
            $this->form_validation->set_rules('subject_id', translate('subject'), 'required');
            $this->form_validation->set_rules('exercise_name', translate('exercise_name'), 'trim|required|callback_unique_exercise');
            if ($this->form_validation->run() !== false) {
                $arrayData = array(
                    'name' => $this->input->post('exercise_name'),
                    'branch_id' => $this->application_model->get_branch_id(),
                    'class_id' => (int) $this->input->post('class_id'),
                    'subject_id' => (int) $this->input->post('subject_id'),
                );
                $this->db->insert('exercise', $arrayData);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $redirect = base_url('onlineexam/exercise');
                $bid = (int) $this->application_model->get_branch_id();
                if ($bid > 0) {
                    $redirect .= '?branch_id=' . $bid;
                }
                redirect($redirect);
            }
        }

        if (isset($_POST['exercise_bulk'])) {
            if (!get_permission('exercise', 'is_add')) {
                access_denied();
            }
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            }
            $this->form_validation->set_rules('bulk_class_id', translate('class'), 'required');
            $this->form_validation->set_rules('bulk_subject_id', translate('subject'), 'required');
            if ($this->form_validation->run() !== false) {
                $branchID = (int) $this->application_model->get_branch_id();
                if (is_superadmin_loggedin()) {
                    $postedBranch = (int) $this->input->post('branch_id');
                    if ($postedBranch > 0) {
                        $branchID = $postedBranch;
                    }
                }
                $classID = (int) $this->input->post('bulk_class_id');
                $subjectID = (int) $this->input->post('bulk_subject_id');
                $names = array();

                $bulkMode = $this->input->post('bulk_mode');
                if ($bulkMode === 'generate') {
                    $prefix = trim((string) $this->input->post('bulk_prefix'));
                    if ($prefix === '') {
                        $prefix = 'Exercise';
                    }
                    $from = (int) $this->input->post('bulk_from');
                    $to = (int) $this->input->post('bulk_to');
                    if ($from < 1) {
                        $from = 1;
                    }
                    if ($to < $from) {
                        $to = $from;
                    }
                    if (($to - $from) > 199) {
                        $to = $from + 199;
                    }
                    for ($n = $from; $n <= $to; $n++) {
                        $names[] = $prefix . ' ' . $n;
                    }
                } else {
                    $raw = (string) $this->input->post('bulk_names');
                    $lines = preg_split('/\r\n|\r|\n/', $raw);
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if ($line !== '') {
                            $names[] = $line;
                        }
                    }
                }

                $names = array_values(array_unique($names));
                if (empty($names)) {
                    set_alert('error', translate('exercise_name') . ' ' . translate('is_required'));
                } else {
                    $created = 0;
                    $skipped = 0;
                    foreach ($names as $name) {
                        $exists = $this->db->where(array(
                            'name' => $name,
                            'branch_id' => $branchID,
                            'class_id' => $classID,
                            'subject_id' => $subjectID,
                        ))->get('exercise')->num_rows();
                        if ($exists > 0) {
                            $skipped++;
                            continue;
                        }
                        $this->db->insert('exercise', array(
                            'name' => $name,
                            'branch_id' => $branchID,
                            'class_id' => $classID,
                            'subject_id' => $subjectID,
                        ));
                        $created++;
                    }
                    $msg = $created . ' ' . translate('exercise') . ' created';
                    if ($skipped > 0) {
                        $msg .= ', ' . $skipped . ' skipped (already exists)';
                    }
                    set_alert($created > 0 ? 'success' : 'error', $msg);
                }
                $redirect = base_url('onlineexam/exercise');
                if ($branchID > 0) {
                    $redirect .= '?branch_id=' . $branchID;
                }
                redirect($redirect);
            }
        }

        $branchID = $this->application_model->get_branch_id();
        if (is_superadmin_loggedin()) {
            $getBranch = (int) $this->input->get('branch_id');
            if ($getBranch > 0) {
                $branchID = $getBranch;
            } elseif (!empty($this->input->post('branch_id'))) {
                $branchID = (int) $this->input->post('branch_id');
            } else {
                $branchID = 0;
            }
        }
        $this->data['branch_id'] = (int) $branchID;
        $this->data['branch_name'] = $branchID > 0 ? get_type_name_by_id('branch', $branchID) : '';
        $this->data['exercise_flow'] = $branchID > 0
            ? $this->onlineexam_model->getExerciseFlowByBranch($branchID)
            : array();
        $exerciseCount = 0;
        foreach ($this->data['exercise_flow'] as $c) {
            $exerciseCount += (int) $c['exercise_count'];
        }
        $this->data['exercise_count'] = $exerciseCount;
        $this->data['title'] = translate('exercise');
        $this->data['sub_page'] = 'onlineexam/exercise';
        $this->data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $this->data);
    }

    /** @deprecated use exercise() */
    public function question_group()
    {
        $qs = $this->input->server('QUERY_STRING');
        redirect(base_url('onlineexam/exercise' . ($qs ? '?' . $qs : '')));
    }

    /** @deprecated use exercise_edit() */
    public function group_edit()
    {
        return $this->exercise_edit();
    }

    /** @deprecated use exercise_delete() */
    public function group_delete($id = '')
    {
        return $this->exercise_delete($id);
    }

    /** @deprecated use exerciseDetails() */
    public function groupDetails()
    {
        return $this->exerciseDetails();
    }

    // update existing exercise
    public function exercise_edit()
    {
        if (!get_permission('exercise', 'is_edit')) {
            ajax_access_denied();
        }
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $this->form_validation->set_rules('class_id', translate('class'), 'required');
        $this->form_validation->set_rules('subject_id', translate('subject'), 'required');
        $this->form_validation->set_rules('exercise_name', translate('exercise_name'), 'trim|required|callback_unique_exercise');
        if ($this->form_validation->run() !== false) {
            $category_id = $this->input->post('exercise_id');
            $arrayData = array(
                'name' => $this->input->post('exercise_name'),
                'branch_id' => $this->application_model->get_branch_id(),
                'class_id' => (int) $this->input->post('class_id'),
                'subject_id' => (int) $this->input->post('subject_id'),
            );
            $this->db->where('id', $category_id);
            $this->db->update('exercise', $arrayData);
            set_alert('success', translate('information_has_been_updated_successfully'));
            $array = array('status' => 'success');
        } else {
            $error = $this->form_validation->error_array();
            $array = array('status' => 'fail', 'error' => $error);
        }
        echo json_encode($array);
    }

    // delete exercise from database
    public function exercise_delete($id)
    {
        if (get_permission('exercise', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('id', $id);
            $this->db->delete('exercise');
        }
    }

    // exercise details send by ajax
    public function exerciseDetails()
    {
        if (get_permission('exercise', 'is_edit')) {
            $id = $this->input->post('id');
            $this->db->where('id', $id);
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $query = $this->db->get('exercise');
            $result = $query->row_array();
            echo json_encode($result);
        }
    }

    /* validate unique exercise name per branch + class + subject */
    public function unique_exercise($name)
    {
        $branchID = $this->application_model->get_branch_id();
        $classID = (int) $this->input->post('class_id');
        $subjectID = (int) $this->input->post('subject_id');
        $exercise_id = $this->input->post('exercise_id');
        if (!empty($exercise_id)) {
            $this->db->where('id !=', (int) $exercise_id);
        }
        $this->db->where(array(
            'name' => $name,
            'branch_id' => $branchID,
            'class_id' => $classID,
            'subject_id' => $subjectID,
        ));
        $uniform_row = $this->db->get('exercise')->num_rows();
        if ($uniform_row == 0) {
            return true;
        } else {
            $this->form_validation->set_message("unique_exercise", translate('already_taken'));
            return false;
        }
    }

    // AJAX: exercise options for class + subject
    public function getExercisesByClassSubject()
    {
        $branchID = (int) $this->input->post('branch_id');
        $classID = (int) $this->input->post('class_id');
        $subjectID = (int) $this->input->post('subject_id');
        if ($branchID < 1) {
            $branchID = (int) $this->application_model->get_branch_id();
        }
        $selected = (int) $this->input->post('selected');
        $array = $this->onlineexam_model->exercise($branchID, $classID, $subjectID, $selected);
        $html = '';
        foreach ($array as $id => $name) {
            $sel = ((string) $id === (string) $selected) ? ' selected' : '';
            $html .= '<option value="' . html_escape($id) . '"' . $sel . '>' . html_escape($name) . '</option>';
        }
        echo $html;
    }

    // AJAX: quick-add exercise from question add/edit forms
    public function exercise_quick_add()
    {
        if (!get_permission('exercise', 'is_add')) {
            echo json_encode(array('status' => false, 'message' => translate('access_denied')));
            return;
        }
        $branchID = (int) $this->input->post('branch_id');
        if ($branchID < 1) {
            $branchID = (int) $this->application_model->get_branch_id();
        }
        $classID = (int) $this->input->post('class_id');
        $subjectID = (int) $this->input->post('subject_id');
        $name = trim((string) $this->input->post('exercise_name'));
        if ($branchID < 1 || $classID < 1 || $subjectID < 1) {
            echo json_encode(array('status' => false, 'message' => translate('select') . ' ' . translate('class') . ' / ' . translate('subject')));
            return;
        }
        if ($name === '') {
            echo json_encode(array('status' => false, 'message' => translate('exercise_name') . ' ' . translate('is_required')));
            return;
        }
        $exists = $this->db->where(array(
            'name' => $name,
            'branch_id' => $branchID,
            'class_id' => $classID,
            'subject_id' => $subjectID,
        ))->get('exercise')->num_rows();
        if ($exists > 0) {
            echo json_encode(array('status' => false, 'message' => translate('already_taken')));
            return;
        }
        $this->db->insert('exercise', array(
            'name' => $name,
            'branch_id' => $branchID,
            'class_id' => $classID,
            'subject_id' => $subjectID,
        ));
        $id = (int) $this->db->insert_id();
        echo json_encode(array(
            'status' => true,
            'id' => $id,
            'name' => $name,
            'message' => translate('information_has_been_saved_successfully'),
        ));
    }

    public function exam_status()
    {
        $id = $this->input->post('id');
        $status = $this->input->post('status');
        if ($status == 'true') {
            $arrayData['publish_status'] = 1;
        } else {
            $arrayData['publish_status'] = 0;
        }
        if (!is_superadmin_loggedin()) {
            $this->db->where('branch_id', get_loggedin_branch_id());
        }
        $this->db->where('id', $id);
        $this->db->update('online_exam', $arrayData);

        if ($status == 'true') {
            $onlineExam = $this->db->where('id', $id)->get('online_exam')->row();
            $percent = $onlineExam->mark_type == 1 ? "%" : "";
            $exam_fee = $onlineExam->exam_type == 1 ? $onlineExam->fee : "Free";
            $sectionArr = json_decode($onlineExam->section_id, true);
            //send online exam sms/email notification
            foreach ($sectionArr as $key => $value) {
                $stuList = $this->application_model->getStudentListByClassSection($onlineExam->class_id, $value, $onlineExam->branch_id);
                foreach ($stuList as $row) {
                    $row['exam_title'] = $onlineExam->title;
                    $row['start_time'] = _d($onlineExam->exam_start) . " - " . date("h:i A", strtotime($onlineExam->exam_start));
                    $row['end_time'] = _d($onlineExam->exam_end) . " - " . date("h:i A", strtotime($onlineExam->exam_end));
                    $row['time_duration'] = $onlineExam->duration;
                    $row['attempt'] = $onlineExam->limits_participation;
                    $row['passing_mark'] = $onlineExam->passing_mark . $percent;
                    $row['exam_fee'] = $exam_fee;
                    // send email/sms
                    $this->sms_model->sendOnlineExam($row);
                    $this->email_model->onlineExamPublish($row);
                }
            }
        }
        $return = array('msg' => translate('information_has_been_updated_successfully'), 'status' => true);
        echo json_encode($return);
    }

    public function make_result_publish($id = '')
    {
        if (!is_superadmin_loggedin()) {
            $this->db->where('branch_id', get_loggedin_branch_id());
        }
        $this->db->where('id', $id);
        $this->db->update('online_exam', ['publish_result' => 1]);
    }

    // get subject list based on class
    public function getByClass()
    {
        $html = '';
        $classID = $this->input->post('classID');
        if (!empty($classID)) {
            $query = $this->onlineexam_model->getSubjectByClass($classID);
            if ($query->num_rows() > 0) {
                $subjects = $query->result_array();
                foreach ($subjects as $row) {
                    $html .= '<option value="' . $row['subject_id'] . '">' . $row['subjectname'] . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select') . '</option>';
        }
        echo $html;
    }

    public function getExamByClass()
    {
        $html = '';
        $classID = $this->input->post('class_id');
        if (!empty($classID)) {
            $this->db->where('class_id', $classID);
            $this->db->where('session_id', get_session_id());
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            if (!is_superadmin_loggedin() && !is_admin_loggedin()) {
                $this->db->where('created_by', get_loggedin_user_id());
            }
            $this->db->where('publish_status', 1);
            $this->db->where('publish_result', 1);
            $query = $this->db->get('online_exam');
            if ($query->num_rows() > 0) {
                $subjects = $query->result();
                $html .= '<option value="">' . translate('select') . '</option>';
                foreach ($subjects as $row) {
                    $html .= '<option value="' . $row->id . '">' . $row->title . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select') . '</option>';
        }
        echo $html;
    }

    public function result()
    {
        // check access permission
        if (!get_permission('exam_result', 'is_view')) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();
        if (isset($_POST['search'])) {
            $classID = $this->input->post('class_id');
            $examID = $this->input->post('exam_id');
            $exam = $this->onlineexam_model->getExamDetails($examID);
            $this->data['exam'] = $exam;
            $position_order = 0;
            if ($exam->position_generated == 1) {
                $position_order = 1;
            }
            $this->data['result'] = $this->onlineexam_model->examReport($examID, $classID, $branchID, $position_order);
        }
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('online_exam') . " " . translate('result');
        $this->data['main_menu'] = 'onlineexam';
        $this->data['sub_page'] = 'onlineexam/result';
        $this->load->view('layout/index', $this->data);
    }

    public function position_generate()
    {
        // check access permission
        if (!get_permission('position_generate', 'is_view')) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();
        if (isset($_POST['search'])) {
            $classID = $this->input->post('class_id');
            $examID = $this->input->post('exam_id');
            $this->data['exam'] = $this->onlineexam_model->getExamDetails($examID);
            $this->data['result'] = $this->onlineexam_model->examReport($examID, $classID, $branchID);
        }
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('position') . " " . translate('generate');
        $this->data['main_menu'] = 'onlineexam';
        $this->data['sub_page'] = 'onlineexam/position_generate';
        $this->load->view('layout/index', $this->data);
    }

    public function save_position()
    {
        if ($_POST) {
            if (!get_permission('position_generate', 'is_add')) {
                ajax_access_denied();
            }
            $remark = $this->input->post('remark');
            foreach ($remark as $key => $value) {
                $this->form_validation->set_rules('remark[' . $key . '][position]', translate('position'), 'trim|numeric|required');
            }
            if ($this->form_validation->run() == true) {
                $examID = $this->input->post('exam_id');
                foreach ($remark as $key => $value) {
                    $array = array(); 
                    if (!empty($value['position'])) {
                        $array['position'] = $value['position'];
                    }
                    if (!empty($value['remark'])) {
                        $array['remark'] = $value['remark'];
                    } else {
                        $array['remark'] = NULL;
                    }
                    if (!empty($array) && !empty($value['student_id'])) {
                        $this->db->where('online_exam_id', $examID);
                        $this->db->where('student_id', $value['student_id']);
                        $this->db->update('online_exam_submitted', $array);
                    }
                }

                $this->db->where('id', $examID);
                $this->db->update('online_exam', ['position_generated' => 1]);
                $message = translate('information_has_been_saved_successfully');
                $array = array('status' => 'success', 'message' => $message);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
        }
    }

    public function getStudent_result()
    {
        if (get_permission('exam_result', 'is_view')) {
            if ($_POST) {
                $examID = $this->input->post('examID');
                $studentID = $this->input->post('studentID');
                $exam = $this->onlineexam_model->getExamDetails($examID);
                $data['exam'] = $exam;
                $data['studentID'] = $studentID;
                $data['questions_detail'] = array();
                if ($exam && !empty($exam->publish_result)) {
                    $data['questions_detail'] = $this->onlineexam_model->getExamResults($examID, $studentID);
                }
                echo $this->load->view('onlineexam/student_result', $data, true);
            }
        }
    }

    /* sample csv downloader */
    public function csv_Sampledownloader()
    {
        $this->load->helper('download');
        $path = FCPATH . 'uploads/import_question_sample.csv';
        if (!is_file($path)) {
            show_error('Sample file not found.', 404);
            return;
        }
        force_download('import_question_sample.csv', file_get_contents($path));
    }

    /* csv file to import question page */
    public function question_import()
    {
        // check access permission
        if (!get_permission('question_bank', 'is_add')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        $this->data['title'] = translate('question') . " " . translate('import');
        $this->data['branch_id'] = $branchID;
        $this->data['sub_page'] = 'onlineexam/question_import';
        $this->data['main_menu'] = 'onlineexam';
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/dropify/css/dropify.min.css',
            ),
            'js' => array(
                'vendor/dropify/js/dropify.min.js',
            ),
        );
        $this->load->view('layout/index', $this->data);
    }

    /* csv file to import question stored in the database here */
    public function questionCsvImport()
    {
        if ($_POST) {
            if (!get_permission('question_bank', 'is_add')) {
                ajax_access_denied();
            }
            $branchID = (int) $this->application_model->get_branch_id();
            // form validation rules
            if (is_superadmin_loggedin() == true) {
                $this->form_validation->set_rules('branch_id', 'Branch', 'trim|required');
            }
            $this->form_validation->set_rules('class_id', 'Class', 'trim|required');
            $this->form_validation->set_rules('section_id', 'Section', 'trim|required');
            $this->form_validation->set_rules('subject_id', 'Subject', 'trim|required');
            $this->form_validation->set_rules('userfile', 'CSV File', 'callback_csvfileHandleUpload[userfile]');

            if (isset($_FILES["userfile"]) && empty($_FILES['userfile']['name'])) {
                $this->form_validation->set_rules('userfile', 'CSV File', 'required');
            }
            if ($this->form_validation->run() == true) {
                if (is_superadmin_loggedin()) {
                    $postedBranch = (int) $this->input->post('branch_id');
                    if ($postedBranch > 0) {
                        $branchID = $postedBranch;
                    }
                }
                $classID = (int) $this->input->post('class_id');
                $sectionID = (int) $this->input->post('section_id');
                $subjectID = (int) $this->input->post('subject_id');
                $createdBy = (int) get_loggedin_user_id();
                $questionsExam = array();
                $skipped = 0;
                if (isset($_FILES["userfile"]) && !empty($_FILES['userfile']['name']) && $_FILES["userfile"]["size"] > 0) {
                    $fileName = $_FILES["userfile"]["tmp_name"];
                    $file = fopen($fileName, "r");
                    $isHeader = true;
                    $count = 0;
                    while (($column = fgetcsv($file, 10000, ",")) !== false) {
                        if ($isHeader) {
                            $isHeader = false;
                            continue;
                        }
                        // Skip fully empty rows
                        $joined = trim(implode('', array_map('strval', $column)));
                        if ($joined === '') {
                            continue;
                        }
                        $parsed = $this->parseImportQuestionRow($column, $branchID, $classID, $sectionID, $subjectID, $createdBy);
                        if ($parsed === null) {
                            $skipped++;
                            continue;
                        }
                        $questionsExam[] = $parsed;
                        $count++;
                    }
                    fclose($file);
                    if (!empty($questionsExam)) {
                        $this->db->insert_batch('questions', $questionsExam);
                    }
                    if ($count == 0) {
                        $url = base_url('onlineexam/question_import');
                        $msg = 'No valid questions found.';
                        if ($skipped > 0) {
                            $msg .= ' (' . $skipped . ' row(s) skipped — check type / required fields.)';
                        }
                        set_alert('error', $msg);
                    } else {
                        $url = base_url('onlineexam/question');
                        $msg = $count . ' question(s) imported successfully.';
                        if ($skipped > 0) {
                            $msg .= ' ' . $skipped . ' row(s) skipped.';
                        }
                        set_alert('success', $msg);
                    }
                } else {
                    $url = base_url('onlineexam/question_import');
                    set_alert('error', 'Question import failed.');
                }
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
        }
    }

    /**
     * Map CSV type slug → numeric type id (supports old disc_* aliases).
     */
    protected function importQuestionTypeMap()
    {
        return array(
            'single_choice' => 1,
            'multi_choice' => 2,
            'multiple_choice' => 2,
            'true_false' => 3,
            'descriptive' => 4,
            'match' => 5,
            'reading' => 6,
            'writing' => 7,
            'fill_blanks' => 8,
            'fill_in_the_blanks' => 8,
            'disc_fill_blanks' => 8,
            'very_short' => 9,
            'disc_very_short' => 9,
            'short' => 10,
            'disc_short' => 10,
            'long' => 11,
            'disc_long' => 11,
        );
    }

    /**
     * Parse one CSV data row into a questions table insert array, or null if invalid.
     *
     * Columns: type, exercise_id (optional), level, question, mark, option_1, option_2, option_3, option_4, answer
     * Match options use Left|Right in option_1..4; answer like 1-1,2-2,3-3,4-4
     * Prefer lesson_id + type when feeding bank for papers and AS/Learning work.
     */
    protected function parseImportQuestionRow(array $column, $branchID, $classID, $sectionID, $subjectID, $createdBy)
    {
        $typeSlug = strtolower(trim(isset($column[0]) ? $column[0] : ''));
        $exerciseId = (int) trim(isset($column[1]) ? $column[1] : '');
        $levelSlug = strtolower(trim(isset($column[2]) ? $column[2] : ''));
        $questionText = trim(isset($column[3]) ? $column[3] : '');
        $mark = trim(isset($column[4]) ? $column[4] : '');
        $opt1 = trim(isset($column[5]) ? $column[5] : '');
        $opt2 = trim(isset($column[6]) ? $column[6] : '');
        $opt3 = trim(isset($column[7]) ? $column[7] : '');
        $opt4 = trim(isset($column[8]) ? $column[8] : '');
        $answerRaw = trim(isset($column[9]) ? $column[9] : '');

        if ($typeSlug === '' || $questionText === '') {
            return null;
        }
        if ($exerciseId < 1) {
            $exerciseId = null;
        }

        $typeMap = $this->importQuestionTypeMap();
        if (!isset($typeMap[$typeSlug])) {
            return null;
        }
        $questionType = (int) $typeMap[$typeSlug];

        $levelMap = array('easy' => 1, 'medium' => 2, 'hard' => 3, '1' => 1, '2' => 2, '3' => 3);
        if (!isset($levelMap[$levelSlug])) {
            $levelSlug = 'easy';
        }
        $questionLevel = (int) $levelMap[$levelSlug];

        if ($mark === '' || !is_numeric($mark)) {
            $defaultMarks = array(8 => 1, 9 => 2, 10 => 3, 11 => 5, 6 => 10, 7 => 10);
            $mark = isset($defaultMarks[$questionType]) ? $defaultMarks[$questionType] : 1;
        }
        $mark = (float) $mark;

        $answer = $answerRaw;
        $customType = null;
        $typeLabels = $this->onlineexam_model->question_type();

        if ($questionType === 1) {
            // single choice: option_1 → 1
            if ($opt1 === '' || $opt2 === '') {
                return null;
            }
            $answer = str_replace('option_', '', strtolower($answer));
            if (!in_array($answer, array('1', '2', '3', '4'), true)) {
                return null;
            }
        } elseif ($questionType === 2) {
            if ($opt1 === '' || $opt2 === '') {
                return null;
            }
            $answer = str_replace('option_', '', strtolower($answer));
            // Normalize JSON like ["1","2"] or ["option_1","option_2"] already stripped
            $decoded = json_decode($answer, true);
            if (!is_array($decoded) || empty($decoded)) {
                return null;
            }
            $norm = array();
            foreach ($decoded as $a) {
                $a = str_replace('option_', '', (string) $a);
                if (in_array($a, array('1', '2', '3', '4'), true)) {
                    $norm[] = $a;
                }
            }
            if (empty($norm)) {
                return null;
            }
            $answer = json_encode($norm);
        } elseif ($questionType === 3) {
            $tf = strtolower($answer);
            if (in_array($tf, array('true', 't', 'yes', '1', 'option_1'), true)) {
                $answer = '1';
            } elseif (in_array($tf, array('false', 'f', 'no', '2', 'option_2'), true)) {
                $answer = '2';
            } else {
                return null;
            }
            $opt1 = $opt2 = $opt3 = $opt4 = '';
        } elseif ($questionType === 5) {
            // Match: option_N = Left|Right
            $left = array();
            $right = array();
            foreach (array($opt1, $opt2, $opt3, $opt4) as $pair) {
                if ($pair === '') {
                    continue;
                }
                $parts = array_map('trim', explode('|', $pair, 2));
                if (count($parts) < 2 || $parts[0] === '' || $parts[1] === '') {
                    return null;
                }
                $left[] = $parts[0];
                $right[] = $parts[1];
            }
            if (count($left) < 2) {
                return null;
            }
            $questionText = json_encode(array(
                'instruction' => $questionText,
                'left' => $left,
                'right' => $right,
            ));
            if ($answer === '') {
                $pairs = array();
                for ($i = 1; $i <= count($left); $i++) {
                    $pairs[] = $i . '-' . $i;
                }
                $answer = implode(',', $pairs);
            }
            $opt1 = $opt2 = $opt3 = $opt4 = '';
        } elseif (in_array($questionType, array(4, 6, 7, 8, 9, 10, 11), true)) {
            // Text / subjective — answer is model/reference text (optional for reading/writing)
            $opt1 = $opt2 = $opt3 = $opt4 = '';
            if (isset($typeLabels[(string) $questionType])) {
                $customType = $typeLabels[(string) $questionType];
            }
            if ($questionType === 4) {
                $customType = null;
            }
        } else {
            return null;
        }

        $row = array(
            'class_id' => (int) $classID,
            'section_id' => (int) $sectionID,
            'subject_id' => (int) $subjectID,
            'branch_id' => (int) $branchID,
            'type' => $questionType,
            'level' => $questionLevel,
            'exercise_id' => $exerciseId > 0 ? $exerciseId : null,
            'question' => $questionText,
            'mark' => $mark,
            'opt_1' => $opt1,
            'opt_2' => $opt2,
            'opt_3' => $opt3,
            'opt_4' => $opt4,
            'answer' => $answer,
            'created_by' => (int) $createdBy,
        );
        // Same keys on every row (required for insert_batch)
        if ($this->db->field_exists('custom_question_type', 'questions')) {
            $row['custom_question_type'] = $customType !== null ? $customType : null;
        }
        return $row;
    }

    /* Question Type - list/manage question types */
    public function question_type()
    {
        if (!get_permission('question_bank', 'is_view')) {
            access_denied();
        }
        $branchID = $this->input->get('branch_id');
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }
        $this->ensureQuestionTypeTable();
        $this->seedDefaultQuestionTypes((int) $branchID);
        $this->data['branch_id'] = $branchID;
        $this->data['branch_name'] = !empty($branchID) ? get_type_name_by_id('branch', $branchID) : '';
        $this->data['title'] = 'Question Type';
        $this->data['sub_page'] = 'onlineexam/question_type';
        $this->data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $this->data);
    }

    public function save_question_type()
    {
        if (!get_permission('question_bank', 'is_edit')) {
            echo json_encode(array('status' => 'access_denied'));
            exit();
        }
        if ($this->input->method() !== 'post') {
            echo json_encode(array('status' => 'fail', 'error' => array('form_error' => 'Invalid request.')));
            exit();
        }
        $this->ensureQuestionTypeTable();
        $id = (int) $this->input->post('qt_id');
        if ($id < 1) {
            echo json_encode(array('status' => 'access_denied'));
            exit();
        }

        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required|integer');
            $branchID = (int) $this->input->post('branch_id');
        } else {
            $branchID = (int) get_loggedin_branch_id();
        }
        if (empty($branchID)) {
            $branchID = (int) $this->application_model->get_branch_id();
        }

        $this->form_validation->set_rules('description', translate('description'), 'trim|max_length[1000]');
        $this->form_validation->set_rules('marks', 'Question Marks', 'trim|required|integer|greater_than_equal_to[0]');
        if ($this->form_validation->run() !== false) {
            $save = array(
                'description' => $this->input->post('description', true),
                'marks' => (int) $this->input->post('marks'),
                'is_active' => 1,
                'updated_at' => date('Y-m-d H:i:s'),
            );
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('branch_id', $branchID);
            $this->db->where('id', $id);
            $exists = $this->db->get('onlineexam_question_type')->row();
            if (!$exists) {
                echo json_encode(array('status' => 'fail', 'error' => array('form_error' => 'Question type not found.')));
                exit();
            }
            $this->db->where('id', $id);
            $this->db->update('onlineexam_question_type', $save);
            $message = translate('information_has_been_updated_successfully');
            set_alert('success', $message);
            $url = base_url('onlineexam/question_type') . (!empty($branchID) ? '?branch_id=' . urlencode($branchID) : '');
            echo json_encode(array('status' => 'success', 'url' => $url, 'message' => $message));
        } else {
            echo json_encode(array('status' => 'fail', 'error' => $this->form_validation->error_array()));
        }
        exit();
    }

    public function delete_question_type($id = '')
    {
        access_denied();
    }

    /**
     * DataTables server-side for Question Type list (fixed system types).
     */
    public function getQuestionTypeDT()
    {
        if (!get_permission('question_bank', 'is_view')) {
            echo json_encode(array('draw' => 0, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => array()));
            return;
        }
        if (!$this->input->post()) {
            echo json_encode(array('draw' => 0, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => array()));
            return;
        }
        $draw = (int) $this->input->post('draw');
        $start = (int) $this->input->post('start');
        $length = (int) $this->input->post('length');
        if ($length < 0) {
            $length = 100;
        }
        $search = '';
        $searchArr = $this->input->post('search');
        if (is_array($searchArr) && isset($searchArr['value'])) {
            $search = strtolower(trim($searchArr['value']));
        }

        $this->ensureQuestionTypeTable();
        $branchID = $this->resolveQuestionTypeBranchID();
        $this->seedDefaultQuestionTypes($branchID);

        $this->db->select('id,question_type_id,name,description,marks');
        $this->db->from('onlineexam_question_type');
        $this->db->where('branch_id', $branchID);
        $this->db->where('question_type_id >', 0);
        $this->db->where('is_active', 1);
        $this->db->order_by('question_type_id', 'ASC');
        $rows = $this->db->get()->result_array();

        $filtered = array();
        foreach ($rows as $row) {
            $name = (string) $row['name'];
            $description = (string) $row['description'];
            $marks = (string) $row['marks'];
            if ($search !== '') {
                $hay = strtolower($name . ' ' . $description . ' ' . $marks);
                if (strpos($hay, $search) === false) {
                    continue;
                }
            }
            $filtered[] = $row;
        }

        $recordsTotal = count($rows);
        $recordsFiltered = count($filtered);
        $pageRows = array_slice($filtered, $start, $length);

        $data = array();
        foreach ($pageRows as $row) {
            $actionBtn = '';
            if (get_permission('question_bank', 'is_edit')) {
                $actionBtn .= '<button type="button" class="btn btn-circle icon btn-default qt-edit" data-id="' . (int) $row['id'] . '" data-name="' . html_escape($row['name']) . '" data-description="' . html_escape($row['description']) . '" data-marks="' . (int) $row['marks'] . '" data-toggle="tooltip" data-original-title="' . translate('edit') . '"><i class="fas fa-pen-nib"></i></button> ';
            }
            $data[] = array(
                html_escape($row['name']),
                html_escape($row['description']),
                (int) $row['marks'],
                $actionBtn,
            );
        }

        echo json_encode(array(
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ));
    }

    protected function resolveQuestionTypeBranchID()
    {
        if (is_superadmin_loggedin()) {
            $branchID = (int) $this->input->post('branch_id');
            if (empty($branchID)) {
                $branchID = (int) $this->application_model->get_branch_id();
            }
        } else {
            $branchID = (int) get_loggedin_branch_id();
        }
        if (empty($branchID)) {
            $branchID = (int) $this->application_model->get_branch_id();
        }
        return (int) $branchID;
    }

    protected function ensureQuestionTypeTable()
    {
        if ($this->db->table_exists('onlineexam_question_type')) {
            if (!$this->db->field_exists('question_type_id', 'onlineexam_question_type')) {
                $this->db->query("ALTER TABLE `onlineexam_question_type` ADD COLUMN `question_type_id` int(11) NOT NULL DEFAULT 0 AFTER `branch_id`");
            }
            return;
        }
        $this->db->query("CREATE TABLE IF NOT EXISTS `onlineexam_question_type` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `branch_id` int(11) NOT NULL,
            `question_type_id` int(11) NOT NULL DEFAULT 0,
            `name` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `marks` int(11) NOT NULL DEFAULT 1,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` datetime DEFAULT current_timestamp(),
            `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `onlineexam_question_type_branch` (`branch_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
    }

    protected function seedDefaultQuestionTypes($branchID)
    {
        $branchID = (int) $branchID;
        if (empty($branchID)) {
            return;
        }
        $defaults = $this->onlineexam_model->question_type();
        // Descriptive (4) is replaced by Fill in the blanks / Very short / Short / Long.
        unset($defaults['4']);
        $descriptions = array(
            1 => 'Single correct answer from multiple options.',
            2 => 'Multiple correct answers from multiple options.',
            3 => 'True or False response.',
            5 => 'Match items from left column to right column.',
            6 => 'Reading comprehension style answer.',
            7 => 'Writing/long-form answer.',
            8 => 'Fill in the blank(s) with a short text answer.',
            9 => 'Very short descriptive answer.',
            10 => 'Short descriptive answer.',
            11 => 'Long descriptive answer.',
        );
        $defaultMarks = array(
            1 => 1,
            2 => 1,
            3 => 1,
            5 => 1,
            6 => 1,
            7 => 1,
            8 => 1,
            9 => 2,
            10 => 3,
            11 => 5,
        );
        $existing = $this->db->select('id,question_type_id,marks,description')
            ->from('onlineexam_question_type')
            ->where('branch_id', $branchID)
            ->get()
            ->result_array();
        $this->db->where('branch_id', $branchID);
        $this->db->where('question_type_id', 0);
        $this->db->update('onlineexam_question_type', array('is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')));
        // Deactivate legacy Descriptive type (4).
        $this->db->where('branch_id', $branchID);
        $this->db->where('question_type_id', 4);
        $this->db->update('onlineexam_question_type', array('is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')));
        $byType = array();
        foreach ($existing as $row) {
            $qid = (int) $row['question_type_id'];
            if ($qid < 1 || $qid === 4) {
                continue;
            }
            if (!isset($byType[$qid])) {
                $byType[$qid] = $row;
            }
        }
        foreach ($defaults as $typeId => $typeName) {
            $typeId = (int) $typeId;
            if ($typeId === 4) {
                continue;
            }
            if (isset($byType[$typeId])) {
                // Keep existing DB name (do not overwrite admin/SQL renames like removing "Disc -").
                $this->db->where('id', (int) $byType[$typeId]['id']);
                $this->db->update('onlineexam_question_type', array(
                    'is_active' => 1,
                    'updated_at' => date('Y-m-d H:i:s'),
                ));
                continue;
            }
            $this->db->insert('onlineexam_question_type', array(
                'branch_id' => $branchID,
                'question_type_id' => $typeId,
                'name' => $typeName,
                'description' => isset($descriptions[$typeId]) ? $descriptions[$typeId] : '',
                'marks' => isset($defaultMarks[$typeId]) ? (int) $defaultMarks[$typeId] : 1,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));
        }
    }

    protected function ensureQuestionsCustomTypeColumn()
    {
        if ($this->db->field_exists('custom_question_type', 'questions')) {
            return;
        }
        $this->db->query("ALTER TABLE `questions` ADD COLUMN `custom_question_type` varchar(255) DEFAULT NULL AFTER `type`");
    }

    /**
     * Link questions to Digital Diary lessons (digital_diary_lesson.id).
     */
    protected function ensureQuestionsLessonIdColumn()
    {
        if ($this->db->field_exists('lesson_id', 'questions')) {
            return;
        }
        $this->db->query("ALTER TABLE `questions` ADD COLUMN `lesson_id` int(11) DEFAULT NULL AFTER `subject_id`");
    }

    /**
     * Allow questions without an exercise (Lesson + Type is primary).
     */
    protected function ensureQuestionsExerciseIdNullable()
    {
        if (!$this->db->table_exists('questions') || !$this->db->field_exists('exercise_id', 'questions')) {
            return;
        }
        @$this->db->query("ALTER TABLE `questions` MODIFY COLUMN `exercise_id` int(11) DEFAULT NULL");
    }

    /* Add one dummy question per type (1–11 except legacy descriptive), including Reading/Writing + subjective AI metadata */
    public function add_dummy_questions()
    {
        if (!get_permission('question_bank', 'is_add')) {
            access_denied();
        }
        $branch_id = $this->application_model->get_branch_id();
        $created_by = get_loggedin_user_id();
        $count = $this->onlineexam_model->addDummyQuestions($branch_id, $created_by);
        if ($count > 0) {
            set_alert('success', $count . ' sample question(s) added to the question bank.');
        } else {
            set_alert('error', 'Could not add sample questions. Ensure branch has at least one class, section and subject.');
        }
        redirect(base_url('onlineexam/question'));
    }

    /**
     * Branch for question-bank screens: superadmin must use ?branch_id= or session; GET is not in post().
     */
    protected function resolveDesignBranchId()
    {
        $branchID = $this->input->get('branch_id');
        if (empty($branchID)) {
            $branchID = $this->input->post('branch_id');
        }
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }
        return (int) $branchID;
    }

    /* Question paper template (saved layout: sections by branch question type + counts) */
    public function design_question_paper()
    {
        if (!get_permission('question_bank', 'is_view')) {
            access_denied();
        }
        $this->ensurePaperDesignTable();
        $this->ensureQuestionTypeTable();
        $bid = $this->resolveDesignBranchId();
        $getBranch = (int) $this->input->get('branch_id');
        // Canonical URL always includes branch_id once resolved (e.g. branch staff).
        if ($bid > 0 && $getBranch < 1) {
            $qs = array('branch_id' => $bid);
            $loadIdRedirect = (int) $this->input->get('id');
            if ($loadIdRedirect > 0) {
                $qs['id'] = $loadIdRedirect;
            }
            if ((int) $this->input->get('new') === 1) {
                $qs['new'] = 1;
            }
            redirect('onlineexam/design_question_paper?' . http_build_query($qs));
            return;
        }
        if ($bid > 0) {
            $this->seedDefaultQuestionTypes((int) $bid);
        }
        $this->data['branch_id'] = $bid;
        $this->data['branch_question_types'] = $bid > 0 ? $this->onlineexam_model->getBranchQuestionTypes($bid) : array();
        if ($bid > 0) {
            $bdRow = $this->db->get_where('branch', array('id' => $bid))->row_array();
            $this->data['branch_details'] = !empty($bdRow) ? $bdRow : array();
        } else {
            $this->data['branch_details'] = array();
        }
        $this->data['paper_logo_url'] = $this->getDesignPaperLogoUrl((int) $bid);
        $this->data['paper_designs'] = $bid > 0 ? $this->onlineexam_model->getPaperDesignList($bid) : array();
        $this->data['editing_design_id'] = 0;
        $this->data['editing_title'] = '';
        $this->data['saved_config'] = null;
        $loadId = (int) $this->input->get('id');
        $wantNew = ((int) $this->input->get('new') === 1);
        if ($loadId > 0 && $bid > 0) {
            $row = $this->onlineexam_model->getPaperDesignById($loadId, $bid);
            if (!empty($row)) {
                $this->data['editing_design_id'] = $loadId;
                $this->data['editing_title'] = $row['title'];
                $decoded = json_decode($row['config'], true);
                $this->data['saved_config'] = is_array($decoded) ? $decoded : null;
            }
        }
        // Designer form only when adding or editing a template (not on branch list / templates index).
        $this->data['show_designer'] = ($bid > 0 && ($wantNew || (int) $this->data['editing_design_id'] > 0));
        $this->data['title'] = 'Question Paper Template';
        $this->data['sub_page'] = 'onlineexam/design_question_paper';
        $this->data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Resolve print or main branch logo for question paper preview.
     *
     * @param int $branchId
     * @return string
     */
    private function getDesignPaperLogoUrl($branchId)
    {
        if ($branchId <= 0) {
            return '';
        }
        foreach (array('printing-logo-', 'logo-') as $prefix) {
            foreach (array('jpg', 'jpeg', 'png', 'gif', 'webp') as $ext) {
                $rel = 'uploads/app_image/' . $prefix . $branchId . '.' . $ext;
                if (file_exists(FCPATH . $rel)) {
                    return base_url($rel);
                }
            }
        }
        return '';
    }

    protected function ensurePaperDesignTable()
    {
        if ($this->db->table_exists('onlineexam_paper_design')) {
            return;
        }
        $this->db->query("CREATE TABLE IF NOT EXISTS `onlineexam_paper_design` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `branch_id` int(11) NOT NULL,
            `session_id` int(11) NOT NULL,
            `title` varchar(255) NOT NULL DEFAULT '',
            `config` longtext NOT NULL,
            `created_by` int(11) NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `oe_paper_design_br` (`branch_id`,`session_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
    }

    protected function ensureQuestionPaperTable()
    {
        if (!$this->db->table_exists('onlineexam_question_paper')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `onlineexam_question_paper` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `branch_id` int(11) NOT NULL,
            `session_id` int(11) NOT NULL,
            `created_by` int(11) NOT NULL DEFAULT 0,
            `title` varchar(255) NOT NULL DEFAULT '',
            `class_id` int(11) NOT NULL,
            `section_id` int(11) NOT NULL,
            `subject_id` int(11) NOT NULL,
            `exam_name` varchar(191) DEFAULT NULL,
            `exam_mode` varchar(32) DEFAULT NULL,
            `exam_type` varchar(64) DEFAULT NULL,
            `exam_assessment` varchar(64) DEFAULT NULL,
            `paper_time` varchar(16) DEFAULT NULL,
            `max_marks` varchar(16) DEFAULT NULL,
            `publish_date` date DEFAULT NULL,
            `publish_time` varchar(16) DEFAULT NULL,
            `lesson_ids` text DEFAULT NULL,
            `chapter_ids` text DEFAULT NULL,
            `question_ids` longtext DEFAULT NULL,
            `paper_design_id` int(11) NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `oe_qpaper_branch_session` (`branch_id`,`session_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
            return;
        }
        if (!$this->db->field_exists('paper_design_id', 'onlineexam_question_paper')) {
            $this->db->query("ALTER TABLE `onlineexam_question_paper` ADD COLUMN `paper_design_id` int(11) NOT NULL DEFAULT 0 AFTER `question_ids`");
        }
    }

    protected function ensureImageDatabaseTable()
    {
        if ($this->db->table_exists('onlineexam_image_database')) {
            if (!$this->db->field_exists('image_name', 'onlineexam_image_database')) {
                $this->db->query("ALTER TABLE `onlineexam_image_database` ADD COLUMN `image_name` varchar(191) DEFAULT NULL AFTER `session_id`");
            }
            return;
        }
        $this->db->query("CREATE TABLE IF NOT EXISTS `onlineexam_image_database` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `branch_id` int(11) NOT NULL,
            `session_id` int(11) NOT NULL,
            `image_name` varchar(191) DEFAULT NULL,
            `original_name` varchar(255) NOT NULL,
            `file_name` varchar(255) NOT NULL,
            `file_path` varchar(500) NOT NULL,
            `file_url` varchar(500) NOT NULL,
            `mime_type` varchar(100) NOT NULL,
            `file_size` int(11) NOT NULL DEFAULT 0,
            `created_by` int(11) NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `oe_img_branch_session` (`branch_id`,`session_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
    }

    /**
     * AJAX: save question paper design template (JSON config).
     */
    public function paper_design_save()
    {
        if ($this->input->method() !== 'post') {
            show_error('Invalid request', 400);
        }
        $this->ensurePaperDesignTable();
        $id = (int) $this->input->post('id');
        if ($id > 0) {
            if (!get_permission('question_bank', 'is_edit')) {
                echo json_encode(array('status' => 'access_denied'));
                exit();
            }
        } else {
            if (!get_permission('question_bank', 'is_add')) {
                echo json_encode(array('status' => 'access_denied'));
                exit();
            }
        }
        $branchId = $this->resolveDesignBranchId();
        if ($branchId < 1) {
            echo json_encode(array('status' => 'fail', 'error' => array('form_error' => 'No branch selected.')));
            exit();
        }
        $title = $this->input->post('title', true);
        $configRaw = $this->input->post('config');
        if (!is_string($configRaw) || $configRaw === '') {
            echo json_encode(array('status' => 'fail', 'error' => array('form_error' => 'Missing configuration.')));
            exit();
        }
        json_decode($configRaw);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(array('status' => 'fail', 'error' => array('form_error' => 'Invalid JSON configuration.')));
            exit();
        }
        if ($id > 0) {
            $existing = $this->onlineexam_model->getPaperDesignById($id, $branchId);
            if (empty($existing)) {
                echo json_encode(array('status' => 'fail', 'error' => array('form_error' => 'Template not found.')));
                exit();
            }
        }
        $uid = (int) get_loggedin_user_id();
        $newId = $this->onlineexam_model->savePaperDesign(
            $branchId,
            get_session_id(),
            $uid,
            $title,
            $configRaw,
            $id
        );
        if ($newId === false) {
            echo json_encode(array('status' => 'fail', 'error' => array('form_error' => 'Unable to save template.')));
            exit();
        }
        $msg = translate('information_has_been_saved_successfully');
        // After save, return to branch templates list.
        $redirectUrl = base_url('onlineexam/design_question_paper?branch_id=' . (int) $branchId);
        echo json_encode(array(
            'status' => 'success',
            'id' => (int) $newId,
            'message' => $msg,
            'url' => $redirectUrl,
        ));
        exit();
    }

    /**
     * AJAX: delete a saved paper design template.
     */
    public function paper_design_delete()
    {
        if ($this->input->method() !== 'post') {
            show_error('Invalid request', 400);
        }
        if (!get_permission('question_bank', 'is_delete')) {
            echo json_encode(array('status' => 'access_denied'));
            exit();
        }
        $this->ensurePaperDesignTable();
        $id = (int) $this->input->post('id');
        $branchId = $this->resolveDesignBranchId();
        if ($id < 1 || $branchId < 1) {
            echo json_encode(array('status' => 'fail', 'error' => 'Invalid id.'));
            exit();
        }
        if ($this->onlineexam_model->deletePaperDesign($id, $branchId)) {
            echo json_encode(array('status' => 'success', 'message' => translate('information_deleted')));
        } else {
            echo json_encode(array('status' => 'fail', 'error' => 'Unable to delete template.'));
        }
        exit();
    }

    /* Create Question Paper */
    public function create_question_paper()
    {
        if (!get_permission('question_bank', 'is_view')) {
            access_denied();
        }
        $this->ensureQuestionPaperTable();
        $this->ensureQuestionTypeTable();
        $bid = $this->resolveDesignBranchId();
        if ($bid > 0) {
            $this->seedDefaultQuestionTypes((int) $bid);
        }
        $this->data['branch_id'] = $bid;
        $this->data['paper_templates'] = $bid > 0 ? $this->onlineexam_model->getPaperDesignListWithConfig($bid) : array();
        $this->data['cqp_type_meta'] = $bid > 0 ? $this->onlineexam_model->getBranchQuestionTypes($bid) : array();
        $this->data['cqp_preselect_template'] = (int) $this->input->get('template_id');
        if ($bid > 0) {
            $bdRow = $this->db->get_where('branch', array('id' => $bid))->row_array();
            $this->data['branch_details'] = !empty($bdRow) ? $bdRow : array();
            $this->data['paper_logo_url'] = $this->getDesignPaperLogoUrl((int) $bid);
        } else {
            $this->data['branch_details'] = array();
            $this->data['paper_logo_url'] = '';
        }
        $this->data['title'] = 'Create Question Paper';
        $this->data['sub_page'] = 'onlineexam/create_question_paper';
        $this->data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * AJAX: exercises with counts for paper checkboxes (create question paper).
     */
    public function ajax_paper_chapters()
    {
        if (!get_permission('question_bank', 'is_view')) {
            echo json_encode(array('ok' => false));
            exit();
        }
        $branchId = $this->resolveDesignBranchId();
        $classId = (int) $this->input->post('class_id');
        $sectionId = (int) $this->input->post('section_id');
        $subjectId = (int) $this->input->post('subject_id');
        $chapters = array();
        $lessons = array();
        if ($branchId > 0 && $classId > 0 && $sectionId > 0 && $subjectId > 0) {
            $chapters = $this->onlineexam_model->getExercisesWithCounts($branchId, $classId, $sectionId, $subjectId);
            $lessons = $this->onlineexam_model->getLessonsWithQuestionCounts($branchId, $classId, $sectionId, $subjectId);
            $questions = $this->onlineexam_model->getQuestionsForPaperPicker($branchId, $classId, $sectionId, $subjectId);
        } else {
            $questions = array();
        }
        $books = ($classId > 0 && $sectionId > 0 && $subjectId > 0) ? 1 : 0;
        echo json_encode(array('ok' => true, 'lessons' => $lessons, 'chapters' => $chapters, 'questions' => $questions, 'books' => $books));
        exit();
    }

    /**
     * Save settings and open question bank for the selected class / section / subject.
     */
    public function create_question_paper_proceed()
    {
        if (!get_permission('question_bank', 'is_view')) {
            access_denied();
        }
        if ($this->input->method() !== 'post') {
            redirect(base_url('onlineexam/create_question_paper'));
        }
        $branchId = $this->resolveDesignBranchId();
        $this->ensureQuestionPaperTable();
        $paperDesignId = (int) $this->input->post('paper_design_id');
        if ($paperDesignId < 1) {
            set_alert('error', 'Please select a question paper template.');
            redirect(base_url('onlineexam/create_question_paper' . ($branchId > 0 ? '?branch_id=' . $branchId : '')));
        }
        if ($branchId < 1) {
            set_alert('error', translate('select') . ' ' . translate('branch') . '.');
            redirect(base_url('onlineexam/create_question_paper'));
        }
        if (empty($this->onlineexam_model->getPaperDesignById($paperDesignId, $branchId))) {
            set_alert('error', 'Invalid or unavailable question paper template.');
            redirect(base_url('onlineexam/create_question_paper?branch_id=' . $branchId));
        }
        $classId = (int) $this->input->post('class_id');
        $sectionId = (int) $this->input->post('section_id');
        $subjectId = (int) $this->input->post('subject_id');
        $lessonIdsPost = $this->input->post('lesson_ids');
        $chapterIdsPost = $this->input->post('chapter_ids');
        $questionIdsPost = $this->input->post('question_ids');
        if ($classId < 1 || $sectionId < 1 || $subjectId < 1) {
            set_alert('error', translate('select') . ' ' . translate('class') . ', ' . translate('section') . ', ' . translate('subject') . '.');
            redirect(base_url('onlineexam/create_question_paper' . ($branchId > 0 ? '?branch_id=' . $branchId : '')));
        }
        $lessonIds = array();
        $chapterIds = array();
        $questionIds = array();
        if (is_array($lessonIdsPost)) {
            $lessonIds = array_filter(array_map('intval', $lessonIdsPost), function ($id) {
                return $id > 0;
            });
            $lessonIds = array_values(array_unique($lessonIds));
        }
        if (is_array($chapterIdsPost)) {
            $chapterIds = array_filter(array_map('intval', $chapterIdsPost), function ($id) {
                return $id > 0;
            });
            $chapterIds = array_values(array_unique($chapterIds));
        }
        if (is_array($questionIdsPost)) {
            $questionIds = array_filter(array_map('intval', $questionIdsPost), function ($id) {
                return $id > 0;
            });
            $questionIds = array_values(array_unique($questionIds));
        }
        $className = get_type_name_by_id('class', $classId);
        $sectionName = get_type_name_by_id('section', $sectionId);
        $subjectName = get_type_name_by_id('subject', $subjectId);
        $examName = trim((string) $this->input->post('exam_name'));
        $paperTitle = trim($examName . ' - ' . $subjectName . ' (' . $className . '-' . $sectionName . ')');
        if ($paperTitle === '' || $paperTitle === ' -  (-)') {
            $paperTitle = 'Question Paper ' . date('Y-m-d H:i');
        }
        $row = array(
            'branch_id' => (int) $branchId,
            'session_id' => (int) get_session_id(),
            'created_by' => (int) get_loggedin_user_id(),
            'title' => $paperTitle,
            'class_id' => (int) $classId,
            'section_id' => (int) $sectionId,
            'subject_id' => (int) $subjectId,
            'exam_name' => $examName,
            'exam_mode' => trim((string) $this->input->post('exam_mode')),
            'exam_type' => trim((string) $this->input->post('exam_type')),
            'exam_assessment' => trim((string) $this->input->post('exam_assessment')),
            'paper_time' => trim((string) $this->input->post('paper_time')),
            'max_marks' => trim((string) $this->input->post('max_marks')),
            'publish_date' => trim((string) $this->input->post('publish_date')),
            'publish_time' => trim((string) $this->input->post('publish_time')),
            'lesson_ids' => !empty($lessonIds) ? implode(',', $lessonIds) : null,
            'chapter_ids' => !empty($chapterIds) ? implode(',', $chapterIds) : null,
            'question_ids' => !empty($questionIds) ? implode(',', $questionIds) : null,
            'paper_design_id' => $paperDesignId,
            'created_at' => date('Y-m-d H:i:s'),
        );
        $newId = $this->onlineexam_model->saveQuestionPaper($row);
        if ($newId === false) {
            set_alert('error', 'Unable to create question paper.');
            redirect(base_url('onlineexam/create_question_paper' . ($branchId > 0 ? '?branch_id=' . $branchId : '')));
        }
        set_alert('success', 'Question paper created successfully.');
        $go = base_url('onlineexam/my_question_paper');
        if ($branchId > 0) {
            $go .= '?branch_id=' . $branchId;
        }
        redirect($go);
    }

    /* Image Database */
    public function image_database()
    {
        if (!get_permission('question_bank', 'is_view')) {
            access_denied();
        }
        $this->ensureImageDatabaseTable();
        $resolvedBranchId = $this->resolveImageBranchId();
        if ($resolvedBranchId < 1) {
            $resolvedBranchId = $this->getFallbackBranchId();
        }
        $this->data['image_branch_id'] = (int) $resolvedBranchId;
        $this->data['title'] = 'Image Database';
        $this->data['sub_page'] = 'onlineexam/image_database';
        $this->data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $this->data);
    }

    public function image_upload()
    {
        if (!get_permission('question_bank', 'is_view')) {
            ajax_access_denied();
        }
        if ($this->input->method() !== 'post') {
            show_error('Invalid request', 400);
        }
        $this->ensureImageDatabaseTable();
        $branchId = $this->resolveImageBranchId();
        if ($branchId < 1) {
            $branchId = $this->getFallbackBranchId();
            if ($branchId < 1) {
                echo json_encode(array('status' => 'fail', 'error' => 'Invalid branch context.'));
                exit();
            }
        }
        if (!isset($_FILES['image']) || empty($_FILES['image']['name'])) {
            echo json_encode(array('status' => 'fail', 'error' => 'Please select an image.'));
            exit();
        }
        $imageName = trim((string) $this->input->post('image_name', true));
        if ($imageName === '') {
            echo json_encode(array('status' => 'fail', 'error' => 'Please enter image name.'));
            exit();
        }
        if (strlen($imageName) > 191) {
            echo json_encode(array('status' => 'fail', 'error' => 'Image name is too long.'));
            exit();
        }
        $imageName = $this->buildUniqueImageName($branchId, $imageName);
        $maxSize = 5 * 1024 * 1024;
        $tmpPath = $_FILES['image']['tmp_name'];
        if (!is_uploaded_file($tmpPath)) {
            echo json_encode(array('status' => 'fail', 'error' => 'Invalid upload.'));
            exit();
        }
        $size = (int) $_FILES['image']['size'];
        if ($size < 1 || $size > $maxSize) {
            echo json_encode(array('status' => 'fail', 'error' => 'Image size must be up to 5 MB.'));
            exit();
        }
        $originalName = $_FILES['image']['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExt = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        if (!in_array($extension, $allowedExt, true)) {
            echo json_encode(array('status' => 'fail', 'error' => 'Unsupported image type.'));
            exit();
        }
        $finfo = @finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = $finfo ? @finfo_file($finfo, $tmpPath) : '';
        if ($finfo) {
            @finfo_close($finfo);
        }
        $allowedMime = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
        if (!in_array((string) $mimeType, $allowedMime, true)) {
            echo json_encode(array('status' => 'fail', 'error' => 'Invalid image MIME type.'));
            exit();
        }

        $relativeDir = 'uploads/onlineexam_images/' . date('Y/m');
        $absoluteDir = FCPATH . $relativeDir;
        if (!is_dir($absoluteDir) && !@mkdir($absoluteDir, 0755, true) && !is_dir($absoluteDir)) {
            echo json_encode(array('status' => 'fail', 'error' => 'Unable to create upload directory.'));
            exit();
        }

        $safeBaseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
        $safeBaseName = trim((string) $safeBaseName, '_');
        if ($safeBaseName === '') {
            $safeBaseName = 'img';
        }
        $storedName = $safeBaseName . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;
        $absolutePath = $absoluteDir . '/' . $storedName;
        if (!@move_uploaded_file($tmpPath, $absolutePath)) {
            echo json_encode(array('status' => 'fail', 'error' => 'Failed to store image.'));
            exit();
        }
        @chmod($absolutePath, 0644);

        $relativePath = $relativeDir . '/' . $storedName;
        $fileUrl = base_url($relativePath);
        $row = array(
            'branch_id' => $branchId,
            'session_id' => (int) get_session_id(),
            'image_name' => $imageName,
            'original_name' => $originalName,
            'file_name' => $storedName,
            'file_path' => $relativePath,
            'file_url' => $fileUrl,
            'mime_type' => (string) $mimeType,
            'file_size' => $size,
            'created_by' => (int) get_loggedin_user_id(),
            'created_at' => date('Y-m-d H:i:s'),
        );
        $id = $this->onlineexam_model->saveImageRecord($row);
        if ($id === false) {
            @unlink($absolutePath);
            echo json_encode(array('status' => 'fail', 'error' => 'Unable to save image metadata.'));
            exit();
        }
        $row['id'] = (int) $id;
        echo json_encode(array('status' => 'success', 'image' => $row));
        exit();
    }

    protected function buildUniqueImageName($branchId, $baseName)
    {
        $branchId = (int) $branchId;
        $baseName = trim((string) $baseName);
        if ($branchId < 1 || $baseName === '' || !$this->db->table_exists('onlineexam_image_database')) {
            return $baseName;
        }
        $candidate = $baseName;
        $counter = 1;
        while (true) {
            $this->db->select('id');
            $this->db->from('onlineexam_image_database');
            $this->db->where('branch_id', $branchId);
            $this->db->where('image_name', $candidate);
            $exists = $this->db->get()->row_array();
            if (empty($exists)) {
                return $candidate;
            }
            $counter++;
            $candidate = $baseName . ' (' . $counter . ')';
            if (strlen($candidate) > 191) {
                $trimmedBase = substr($baseName, 0, max(1, 180));
                $candidate = $trimmedBase . ' (' . $counter . ')';
            }
        }
    }

    public function image_list()
    {
        if (!get_permission('question_bank', 'is_view')) {
            ajax_access_denied();
        }
        $this->ensureImageDatabaseTable();
        $branchId = $this->resolveImageBranchId();
        if ($branchId < 1) {
            $branchId = $this->getFallbackBranchId();
        }
        $search = trim((string) $this->input->get('search'));
        $page = (int) $this->input->get('page');
        $perPage = (int) $this->input->get('per_page');
        if ($page < 1) {
            $page = 1;
        }
        if ($perPage < 1 || $perPage > 60) {
            $perPage = 24;
        }
        $offset = ($page - 1) * $perPage;
        $total = $this->onlineexam_model->countImageList($branchId, $search);
        $images = $this->onlineexam_model->getImageList($branchId, $search, $perPage, $offset);
        echo json_encode(array(
            'status' => 'success',
            'images' => $images,
            'pagination' => array(
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $perPage > 0 ? (int) ceil($total / $perPage) : 1,
            ),
        ));
        exit();
    }

    public function image_delete($id = 0)
    {
        if (!get_permission('question_bank', 'is_delete')) {
            ajax_access_denied();
        }
        if ($this->input->method() !== 'post') {
            show_error('Invalid request', 400);
        }
        $this->ensureImageDatabaseTable();
        $id = (int) $id;
        $branchId = $this->resolveImageBranchId();
        if ($branchId < 1) {
            $branchId = $this->getFallbackBranchId();
        }
        $row = $this->onlineexam_model->getImageById($id, $branchId);
        if (empty($row)) {
            echo json_encode(array('status' => 'fail', 'error' => 'Image not found.'));
            exit();
        }
        $deleted = $this->onlineexam_model->deleteImageById($id, $branchId);
        if (!$deleted) {
            echo json_encode(array('status' => 'fail', 'error' => 'Unable to delete image record.'));
            exit();
        }
        $abs = FCPATH . ltrim((string) $row['file_path'], '/');
        if (is_file($abs)) {
            @unlink($abs);
        }
        echo json_encode(array('status' => 'success'));
        exit();
    }

    public function image_seed_defaults()
    {
        if (!get_permission('question_bank', 'is_view')) {
            ajax_access_denied();
        }
        if ($this->input->method() !== 'post') {
            show_error('Invalid request', 400);
        }
        $this->ensureImageDatabaseTable();
        $branchId = $this->resolveImageBranchId();
        if ($branchId < 1) {
            $branchId = $this->getFallbackBranchId();
        }
        if ($branchId < 1) {
            echo json_encode(array('status' => 'fail', 'error' => 'Invalid branch context.'));
            exit();
        }
        $library = $this->getDefaultStarterSvgLibrary();
        if (empty($library)) {
            echo json_encode(array('status' => 'fail', 'error' => 'No default library found.'));
            exit();
        }
        $relativeDir = 'uploads/onlineexam_images/defaults/branch_' . (int) $branchId;
        $absoluteDir = FCPATH . $relativeDir;
        if (!is_dir($absoluteDir) && !@mkdir($absoluteDir, 0755, true) && !is_dir($absoluteDir)) {
            echo json_encode(array('status' => 'fail', 'error' => 'Unable to create default image directory.'));
            exit();
        }
        $created = 0;
        foreach ($library as $item) {
            $imageName = isset($item['image_name']) ? trim((string) $item['image_name']) : '';
            $svg = isset($item['svg']) ? (string) $item['svg'] : '';
            if ($imageName === '' || $svg === '') {
                continue;
            }
            $exists = $this->db->select('id')
                ->from('onlineexam_image_database')
                ->where('branch_id', $branchId)
                ->where('image_name', $imageName)
                ->get()
                ->row_array();
            if (!empty($exists)) {
                continue;
            }
            $slug = $this->slugifyImageName($imageName);
            $fileName = $slug . '.svg';
            $relativePath = $relativeDir . '/' . $fileName;
            $absolutePath = $absoluteDir . '/' . $fileName;
            if (is_file($absolutePath)) {
                $fileName = $slug . '_' . time() . '_' . mt_rand(100, 999) . '.svg';
                $relativePath = $relativeDir . '/' . $fileName;
                $absolutePath = $absoluteDir . '/' . $fileName;
            }
            if (@file_put_contents($absolutePath, $svg) === false) {
                continue;
            }
            @chmod($absolutePath, 0644);
            $row = array(
                'branch_id' => $branchId,
                'session_id' => (int) get_session_id(),
                'image_name' => $imageName,
                'original_name' => $fileName,
                'file_name' => $fileName,
                'file_path' => $relativePath,
                'file_url' => base_url($relativePath),
                'mime_type' => 'image/svg+xml',
                'file_size' => (int) filesize($absolutePath),
                'created_by' => (int) get_loggedin_user_id(),
                'created_at' => date('Y-m-d H:i:s'),
            );
            $id = $this->onlineexam_model->saveImageRecord($row);
            if ($id !== false) {
                $created++;
            }
        }
        echo json_encode(array(
            'status' => 'success',
            'created' => $created,
            'message' => $created > 0 ? ($created . ' default images added to searchable gallery.') : 'Defaults already available.',
        ));
        exit();
    }

    protected function resolveImageBranchId()
    {
        $postedBranchId = (int) $this->input->post('branch_id');
        if ($postedBranchId < 1) {
            $postedBranchId = (int) $this->input->get('branch_id');
        }
        $sessionBranchId = (int) get_loggedin_branch_id();
        if ($sessionBranchId < 1) {
            $sessionBranchId = (int) $this->application_model->get_branch_id();
        }
        if (!is_superadmin_loggedin()) {
            if ($sessionBranchId > 0) {
                return $sessionBranchId;
            }
            return $postedBranchId > 0 ? $postedBranchId : 0;
        }
        if ($postedBranchId > 0) {
            return $postedBranchId;
        }
        if ($sessionBranchId > 0) {
            return $sessionBranchId;
        }
        return 0;
    }

    protected function getFallbackBranchId()
    {
        $row = $this->db->select('id')->from('branch')->order_by('id', 'ASC')->limit(1)->get()->row_array();
        if (!empty($row) && isset($row['id'])) {
            return (int) $row['id'];
        }
        return 0;
    }

    protected function slugifyImageName($name)
    {
        $slug = strtolower(trim((string) $name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim((string) $slug, '-');
        if ($slug === '') {
            $slug = 'img';
        }
        return substr($slug, 0, 80);
    }

    protected function getDefaultStarterSvgLibrary()
    {
        $mk = function ($title, $subtitle = '', $icon = '●') {
            $titleEsc = html_escape($title);
            $subEsc = html_escape($subtitle);
            $iconEsc = html_escape($icon);
            return '<svg xmlns="http://www.w3.org/2000/svg" width="220" height="160">'
                . '<rect width="100%" height="100%" fill="white"/>'
                . '<rect x="8" y="8" width="204" height="144" rx="8" ry="8" fill="#fff" stroke="#222" stroke-width="2"/>'
                . '<text x="24" y="62" font-size="30" font-family="Arial">' . $iconEsc . '</text>'
                . '<text x="24" y="96" font-size="18" font-family="Arial" font-weight="bold">' . $titleEsc . '</text>'
                . '<text x="24" y="122" font-size="13" font-family="Arial">' . $subEsc . '</text>'
                . '</svg>';
        };
        return array(
            array('image_name' => 'Alphabet A Apple', 'svg' => $mk('A - Apple', 'Nursery phonics', 'A')),
            array('image_name' => 'Alphabet B Ball', 'svg' => $mk('B - Ball', 'Nursery phonics', 'B')),
            array('image_name' => 'Alphabet C Cat', 'svg' => $mk('C - Cat', 'Nursery phonics', 'C')),
            array('image_name' => 'Alphabet D Dog', 'svg' => $mk('D - Dog', 'Nursery phonics', 'D')),
            array('image_name' => 'Alphabet E Elephant', 'svg' => $mk('E - Elephant', 'Nursery phonics', 'E')),
            array('image_name' => 'Alphabet F Fish', 'svg' => $mk('F - Fish', 'Nursery phonics', 'F')),
            array('image_name' => 'Numbers 1 to 10', 'svg' => $mk('1 2 3 4 5', '6 7 8 9 10', '#')),
            array('image_name' => 'Numbers 11 to 20', 'svg' => $mk('11 12 13 14 15', '16 17 18 19 20', '#')),
            array('image_name' => 'Before After Numbers', 'svg' => $mk('Before / After', 'Number sense', '↔')),
            array('image_name' => 'Colors Red Blue Green', 'svg' => $mk('Red Blue Green', 'Basic colors', '●')),
            array('image_name' => 'Colors Yellow Orange Purple', 'svg' => $mk('Yellow Orange', 'Purple Brown Pink', '●')),
            array('image_name' => 'Animal Cat Dog Bird', 'svg' => $mk('Cat Dog Bird', 'Animal words', '🐾')),
            array('image_name' => 'Animal Farm Set', 'svg' => $mk('Cow Goat Hen', 'Farm animals', '🐄')),
            array('image_name' => 'Fruit Apple Mango Banana', 'svg' => $mk('Apple Mango', 'Banana Orange', '🍎')),
            array('image_name' => 'Vegetable Set', 'svg' => $mk('Potato Tomato', 'Carrot Onion', '🥕')),

            array('image_name' => 'Counting Dots 1 to 20', 'svg' => $mk('Counting Dots', '1 to 20 practice', '•')),
            array('image_name' => 'Number Line 0 to 20', 'svg' => $mk('Number Line', '0 to 20', '↔')),
            array('image_name' => 'Place Value Tens Ones', 'svg' => $mk('Place Value', 'Tens / Ones', '10')),
            array('image_name' => 'Place Value Hundreds Tens Ones', 'svg' => $mk('Place Value', 'Hundreds/Tens/Ones', '100')),
            array('image_name' => 'Addition Facts', 'svg' => $mk('Addition Facts', 'Single digit sums', '+')),
            array('image_name' => 'Subtraction Facts', 'svg' => $mk('Subtraction Facts', 'Single digit minus', '−')),
            array('image_name' => 'Multiplication Basics', 'svg' => $mk('Multiplication', 'Repeated addition', '×')),
            array('image_name' => 'Division Basics', 'svg' => $mk('Division', 'Equal sharing', '÷')),
            array('image_name' => 'Fraction Half Quarter', 'svg' => $mk('Fractions', '1/2 and 1/4', '½')),
            array('image_name' => 'Fraction Thirds', 'svg' => $mk('Fractions', '1/3 and 2/3', '⅓')),
            array('image_name' => 'Equivalent Fractions', 'svg' => $mk('Equivalent', '1/2 = 2/4', '≡')),
            array('image_name' => 'Clock Face Practice', 'svg' => $mk('Clock Face', 'Tell the time', '🕒')),
            array('image_name' => 'Time Quarter Past', 'svg' => $mk('Time', 'Quarter past/to', '⏰')),
            array('image_name' => 'Multiplication Grid', 'svg' => $mk('Multiplication', 'Basic table grid', '×')),
            array('image_name' => 'Roman Numerals', 'svg' => $mk('Roman Numerals', 'I V X L C', 'Ⅹ')),

            array('image_name' => 'Phonics Short Vowels', 'svg' => $mk('Short Vowels', 'a e i o u', 'aeiou')),
            array('image_name' => 'Phonics Long Vowels', 'svg' => $mk('Long Vowels', 'ai ee ie oa ue', 'AEIOU')),
            array('image_name' => 'Consonant Blends', 'svg' => $mk('Blends', 'bl cl fl st tr', 'BL')),
            array('image_name' => 'Punctuation Marks', 'svg' => $mk('Punctuation', '. , ? ! ; :', '?')),
            array('image_name' => 'Sentence Starter Cards', 'svg' => $mk('Sentence Starters', 'I can / We can', '✎')),
            array('image_name' => 'Noun Verb Adjective', 'svg' => $mk('Grammar Basics', 'Noun Verb Adjective', 'ABC')),
            array('image_name' => 'Pronoun Set', 'svg' => $mk('Pronouns', 'I you he she they', '👤')),
            array('image_name' => 'Tenses Overview', 'svg' => $mk('Tenses', 'Past Present Future', '⏳')),
            array('image_name' => 'Opposites Word Card', 'svg' => $mk('Opposites', 'Hot/Cold Big/Small', '⇅')),

            array('image_name' => 'Plant Parts Diagram', 'svg' => $mk('Plant Parts', 'Root Stem Leaf', '🌱')),
            array('image_name' => 'Human Body Parts', 'svg' => $mk('Body Parts', 'Head Arm Leg', '🧍')),
            array('image_name' => 'Weather Symbols', 'svg' => $mk('Weather', 'Sun Rain Cloud', '☀')),
            array('image_name' => 'States of Matter', 'svg' => $mk('Matter', 'Solid Liquid Gas', '◈')),
            array('image_name' => 'Life Cycle Butterfly', 'svg' => $mk('Life Cycle', 'Egg Larva Pupa Adult', '🦋')),
            array('image_name' => 'Solar System Basic', 'svg' => $mk('Solar System', 'Sun and planets', '☉')),
            array('image_name' => 'Simple Machines', 'svg' => $mk('Machines', 'Lever Pulley Wheel', '⚙')),

            array('image_name' => 'Map Directions NSEW', 'svg' => $mk('Directions', 'North South East West', '🧭')),
            array('image_name' => 'Community Helpers', 'svg' => $mk('Helpers', 'Doctor Teacher Police', '👥')),
            array('image_name' => 'Basic Civics Symbols', 'svg' => $mk('Civics', 'Flag School Rule', '⚑')),
            array('image_name' => 'India Physical Map Label', 'svg' => $mk('India Map', 'Mountains Plains Plateau', '🗺')),
            array('image_name' => 'Continents and Oceans', 'svg' => $mk('World Basics', '7 continents 5 oceans', '🌍')),

            array('image_name' => '2D Shapes Set', 'svg' => $mk('2D Shapes', 'Circle Triangle Square', '△')),
            array('image_name' => '3D Shapes Set', 'svg' => $mk('3D Shapes', 'Cube Cone Cylinder', '◻')),
            array('image_name' => 'Angle Types', 'svg' => $mk('Angles', 'Acute Right Obtuse', '∠')),
            array('image_name' => 'Symmetry Practice', 'svg' => $mk('Symmetry', 'Line of symmetry', '⟂')),
            array('image_name' => 'Coordinate Grid', 'svg' => $mk('Coordinate Grid', 'x-y plane', '+')),
            array('image_name' => 'Graph Paper', 'svg' => $mk('Graph Paper', 'Math plotting', '#')),
            array('image_name' => 'Perimeter Area Formula', 'svg' => $mk('Perimeter & Area', 'Square Rectangle', '▦')),
            array('image_name' => 'Circle Radius Diameter', 'svg' => $mk('Circle Parts', 'Radius Diameter Chord', '◯')),
            array('image_name' => 'Types of Triangles', 'svg' => $mk('Triangles', 'Scalene Isosceles Equilateral', '△')),
            array('image_name' => 'Bar Graph Template', 'svg' => $mk('Bar Graph', 'Plot values', '▮')),
            array('image_name' => 'Pie Chart Template', 'svg' => $mk('Pie Chart', 'Data sectors', '◔')),

            array('image_name' => 'Worksheet Match Template', 'svg' => $mk('Match the following', 'Template', '⇄')),
            array('image_name' => 'Worksheet Circle Option', 'svg' => $mk('Circle the answer', 'Template', '○')),
            array('image_name' => 'Worksheet Fill in Blanks', 'svg' => $mk('Fill in blanks', 'Template', '_')),
            array('image_name' => 'Worksheet True False', 'svg' => $mk('True / False', 'Template', '✓')),
            array('image_name' => 'Worksheet Tick Cross Icons', 'svg' => $mk('Tick / Cross', 'Assessment icons', '✓✗')),
            array('image_name' => 'Worksheet Multiple Choice', 'svg' => $mk('MCQ Template', 'A B C D options', 'Ⓐ')),
            array('image_name' => 'Worksheet Short Answer', 'svg' => $mk('Short Answer', 'Write in 2-3 lines', '✍')),
            array('image_name' => 'Worksheet Word Problem', 'svg' => $mk('Word Problem', 'Read and solve', '📘')),
        );
    }

    /* My Question Paper */
    public function my_question_paper()
    {
        if (!get_permission('question_bank', 'is_view')) {
            access_denied();
        }
        $this->ensureQuestionPaperTable();
        $branchId = $this->resolveDesignBranchId();
        $this->data['branch_id'] = $branchId;
        $this->data['question_papers'] = $this->onlineexam_model->getQuestionPaperList($branchId, (int) get_session_id());
        $this->data['title'] = 'My Question Paper';
        $this->data['sub_page'] = 'onlineexam/my_question_paper';
        $this->data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $this->data);
    }

    public function question_paper_view($id = 0)
    {
        if (!get_permission('question_bank', 'is_view')) {
            access_denied();
        }
        $data = $this->loadQuestionPaperPayload($id);
        if ($data === null) {
            return;
        }
        $this->data = array_merge($this->data, $data);
        $this->data['title'] = 'View Question Paper';
        $this->data['sub_page'] = 'onlineexam/question_paper_view';
        $this->data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $this->data);
    }

    /**
     * Download student question paper as PDF (mPDF).
     */
    public function question_paper_pdf($id = 0)
    {
        if (!get_permission('question_bank', 'is_view')) {
            access_denied();
        }
        $data = $this->loadQuestionPaperPayload($id);
        if ($data === null) {
            return;
        }
        $this->data = array_merge($this->data, $data);
        $html = $this->load->view('onlineexam/question_paper_pdf', $this->data, true);

        $this->load->library('html2pdf');
        $mpdf = $this->html2pdf->mpdf;
        $mpdf->SetMargins(12, 12, 12);
        $mpdf->SetAutoPageBreak(true, 12);
        $mpdf->SetTitle(isset($data['paper']['title']) ? $data['paper']['title'] : 'Question Paper');
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->autoScriptToLang = true;
        $mpdf->baseScript = 1;
        $mpdf->autoLangToFont = true;
        $mpdf->WriteHTML($html);

        $safeTitle = preg_replace('/[^A-Za-z0-9_\-]+/', '_', isset($data['paper']['title']) ? $data['paper']['title'] : 'question_paper');
        $safeTitle = trim($safeTitle, '_');
        if ($safeTitle === '') {
            $safeTitle = 'question_paper';
        }
        $filename = $safeTitle . '_' . (int) $id . '.pdf';
        $mpdf->Output($filename, 'D');
        exit();
    }

    /**
     * Shared payload for question paper view / PDF.
     *
     * @param int $id
     * @return array|null
     */
    private function loadQuestionPaperPayload($id)
    {
        $this->ensureQuestionPaperTable();
        $id = (int) $id;
        $branchId = $this->resolveDesignBranchId();
        $paper = $this->onlineexam_model->getQuestionPaperById($id, $branchId, (int) get_session_id());
        if (empty($paper)) {
            set_alert('error', 'Question paper not found.');
            redirect(base_url('onlineexam/my_question_paper' . ($branchId > 0 ? '?branch_id=' . $branchId : '')));
            return null;
        }
        $bdRow = array();
        if ($branchId > 0) {
            $bdRow = $this->db->where('id', $branchId)->get('branch')->row_array();
        }
        $questions = $this->onlineexam_model->getQuestionsByCsvIds(
            isset($paper['question_ids']) ? $paper['question_ids'] : '',
            $branchId
        );
        return array(
            'branch_id' => $branchId,
            'paper' => $paper,
            'paper_questions' => $questions,
            'paper_sections' => $this->buildQuestionPaperSectionsForDisplay($paper, $questions, $branchId),
            'branch_details' => !empty($bdRow) ? $bdRow : array(),
            'paper_logo_url' => $this->getDesignPaperLogoUrl((int) $branchId),
            'paper_logo_path' => $this->getDesignPaperLogoPath((int) $branchId),
        );
    }

    /**
     * Build section blocks for student paper (questions on paper may exceed max attempt).
     *
     * @param array $paper
     * @param array $questions
     * @param int   $branchId
     * @return array
     */
    private function buildQuestionPaperSectionsForDisplay($paper, $questions, $branchId)
    {
        $questions = is_array($questions) ? $questions : array();
        $typeMap = $this->onlineexam_model->question_type();
        $roman = array(1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E', 6 => 'F', 7 => 'G', 8 => 'H', 9 => 'I', 10 => 'J', 11 => 'K');
        $designId = isset($paper['paper_design_id']) ? (int) $paper['paper_design_id'] : 0;
        $out = array();

        if ($designId > 0 && $branchId > 0) {
            $design = $this->onlineexam_model->getPaperDesignById($designId, $branchId);
            $cfg = (!empty($design['config'])) ? json_decode($design['config'], true) : null;
            if (is_array($cfg) && !empty($cfg['sections']) && is_array($cfg['sections'])) {
                $metaById = array();
                foreach ($this->onlineexam_model->getBranchQuestionTypes($branchId) as $bt) {
                    $metaById[(string) $bt['id']] = $bt;
                }
                $used = array();
                $secIdx = 0;
                foreach ($cfg['sections'] as $sec) {
                    if (!is_array($sec)) {
                        continue;
                    }
                    $tid = !empty($sec['qtype_id']) ? (string) $sec['qtype_id'] : '';
                    $qnum = isset($sec['qnum']) ? (int) $sec['qnum'] : 0;
                    if ($tid === '' || $qnum < 1) {
                        continue;
                    }
                    $m = isset($metaById[$tid]) ? $metaById[$tid] : null;
                    $baseType = $m ? (int) $m['question_type_id'] : 0;
                    $name = $m && !empty($m['name']) ? $m['name'] : (isset($typeMap[(string) $baseType]) ? $typeMap[(string) $baseType] : ('Type ' . $baseType));
                    $attempt = isset($sec['attempt']) ? (int) $sec['attempt'] : 0;
                    if ($attempt < 1) {
                        $attempt = $qnum;
                    }
                    if ($attempt > $qnum) {
                        $attempt = $qnum;
                    }
                    $secQs = array();
                    foreach ($questions as $q) {
                        if (count($secQs) >= $qnum) {
                            break;
                        }
                        $qid = (int) (isset($q['id']) ? $q['id'] : 0);
                        if ($qid < 1 || isset($used[$qid])) {
                            continue;
                        }
                        if ((int) (isset($q['type']) ? $q['type'] : 0) !== $baseType) {
                            continue;
                        }
                        $secQs[] = $q;
                        $used[$qid] = true;
                    }
                    $eachMk = null;
                    if ($m && isset($m['marks']) && $m['marks'] !== '' && $m['marks'] !== null) {
                        $eachMk = (float) $m['marks'];
                    } elseif (!empty($secQs[0]['mark'])) {
                        $eachMk = (float) $secQs[0]['mark'];
                    }
                    $secIdx++;
                    $out[] = array(
                        'letter' => isset($roman[$secIdx]) ? $roman[$secIdx] : (string) $secIdx,
                        'name' => $name,
                        'qnum' => $qnum,
                        'attempt' => $attempt,
                        'marks' => $eachMk !== null ? ($attempt * $eachMk) : 0,
                        'attempt_label' => ($attempt < $qnum)
                            ? ('Attempt any ' . $attempt . ' of ' . $qnum)
                            : ('Attempt all ' . $qnum),
                        'questions' => $secQs,
                    );
                }
                $leftover = array();
                foreach ($questions as $q) {
                    $qid = (int) (isset($q['id']) ? $q['id'] : 0);
                    if ($qid > 0 && !isset($used[$qid])) {
                        $leftover[] = $q;
                    }
                }
                if (!empty($leftover)) {
                    $secIdx++;
                    $out[] = array(
                        'letter' => isset($roman[$secIdx]) ? $roman[$secIdx] : (string) $secIdx,
                        'name' => 'Additional',
                        'qnum' => count($leftover),
                        'attempt' => count($leftover),
                        'marks' => 0,
                        'attempt_label' => 'Attempt all ' . count($leftover),
                        'questions' => $leftover,
                    );
                }
                return $out;
            }
        }

        $grouped = array();
        foreach ($questions as $q) {
            $qt = (int) (isset($q['type']) ? $q['type'] : 0);
            if (!isset($grouped[$qt])) {
                $grouped[$qt] = array();
            }
            $grouped[$qt][] = $q;
        }
        $secIdx = 0;
        foreach ($grouped as $qt => $qlist) {
            $secIdx++;
            $n = count($qlist);
            $secMarks = 0;
            foreach ($qlist as $qq) {
                $secMarks += isset($qq['mark']) ? (float) $qq['mark'] : 0;
            }
            $out[] = array(
                'letter' => isset($roman[$secIdx]) ? $roman[$secIdx] : (string) $secIdx,
                'name' => isset($typeMap[(string) $qt]) ? $typeMap[(string) $qt] : ('Type ' . $qt),
                'qnum' => $n,
                'attempt' => $n,
                'marks' => $secMarks,
                'attempt_label' => 'Attempt all ' . $n,
                'questions' => $qlist,
            );
        }
        return $out;
    }

    /**
     * Local filesystem path for branch logo (mPDF).
     *
     * @param int $branchId
     * @return string
     */
    private function getDesignPaperLogoPath($branchId)
    {
        if ($branchId <= 0) {
            return '';
        }
        foreach (array('printing-logo-', 'logo-') as $prefix) {
            foreach (array('jpg', 'jpeg', 'png', 'gif', 'webp') as $ext) {
                $rel = 'uploads/app_image/' . $prefix . $branchId . '.' . $ext;
                $full = FCPATH . $rel;
                if (file_exists($full)) {
                    return $full;
                }
            }
        }
        return '';
    }

    public function question_paper_edit($id = 0)
    {
        if (!get_permission('question_bank', 'is_edit')) {
            access_denied();
        }
        $this->ensureQuestionPaperTable();
        $id = (int) $id;
        $branchId = $this->resolveDesignBranchId();
        $paper = $this->onlineexam_model->getQuestionPaperById($id, $branchId, (int) get_session_id());
        if (empty($paper)) {
            set_alert('error', 'Question paper not found.');
            redirect(base_url('onlineexam/my_question_paper' . ($branchId > 0 ? '?branch_id=' . $branchId : '')));
        }
        $this->data['branch_id'] = $branchId;
        $this->data['paper'] = $paper;
        $this->data['paper_questions'] = $this->onlineexam_model->getQuestionsByCsvIds(
            isset($paper['question_ids']) ? $paper['question_ids'] : '',
            $branchId
        );
        $allQuestions = $this->onlineexam_model->getQuestionsByClassSectionSubject(
            (int) $paper['class_id'],
            (int) $paper['section_id'],
            (int) $paper['subject_id'],
            $branchId
        );
        $this->data['paper_all_questions'] = is_array($allQuestions) ? $allQuestions : array();
        $this->data['title'] = 'Edit Question Paper';
        $this->data['sub_page'] = 'onlineexam/question_paper_edit';
        $this->data['main_menu'] = 'onlineexam';
        $this->load->view('layout/index', $this->data);
    }

    public function question_paper_update($id = 0)
    {
        if (!get_permission('question_bank', 'is_edit')) {
            access_denied();
        }
        if ($this->input->method() !== 'post') {
            redirect(base_url('onlineexam/my_question_paper'));
        }
        $this->ensureQuestionPaperTable();
        $id = (int) $id;
        $branchId = $this->resolveDesignBranchId();
        $paper = $this->onlineexam_model->getQuestionPaperById($id, $branchId, (int) get_session_id());
        if (empty($paper)) {
            set_alert('error', 'Question paper not found.');
            redirect(base_url('onlineexam/my_question_paper' . ($branchId > 0 ? '?branch_id=' . $branchId : '')));
        }
        $save = array(
            'title' => trim((string) $this->input->post('title')),
            'exam_name' => trim((string) $this->input->post('exam_name')),
            'exam_mode' => trim((string) $this->input->post('exam_mode')),
            'exam_type' => trim((string) $this->input->post('exam_type')),
            'exam_assessment' => trim((string) $this->input->post('exam_assessment')),
            'paper_time' => trim((string) $this->input->post('paper_time')),
            'max_marks' => trim((string) $this->input->post('max_marks')),
            'publish_date' => trim((string) $this->input->post('publish_date')),
            'publish_time' => trim((string) $this->input->post('publish_time')),
            'updated_at' => date('Y-m-d H:i:s'),
        );
        $questionIdsPost = $this->input->post('question_ids');
        $questionIds = array();
        if (is_array($questionIdsPost)) {
            $questionIds = array_values(array_unique(array_filter(array_map('intval', $questionIdsPost), function ($id) {
                return $id > 0;
            })));
        }
        $save['question_ids'] = !empty($questionIds) ? implode(',', $questionIds) : null;
        $this->onlineexam_model->updateQuestionPaper($id, $branchId, (int) get_session_id(), $save);
        set_alert('success', translate('information_has_been_updated_successfully'));
        redirect(base_url('onlineexam/my_question_paper' . ($branchId > 0 ? '?branch_id=' . $branchId : '')));
    }

    public function csvfileHandleUpload($str, $fields)
    {
        $allowedExts = array_map('trim', array_map('strtolower', explode(',', 'csv')));
        if (isset($_FILES["$fields"]) && !empty($_FILES["$fields"]['name'])) {
            $file_size = $_FILES["$fields"]["size"];
            $file_name = $_FILES["$fields"]["name"];
            $extension = pathinfo($file_name, PATHINFO_EXTENSION);
            if ($files = filesize($_FILES["$fields"]['tmp_name'])) {
                if (!in_array(strtolower($extension), $allowedExts)) {
                    $this->form_validation->set_message('csvfileHandleUpload', translate('this_file_type_is_not_allowed'));
                    return false;
                }
            } else {
                $this->form_validation->set_message('csvfileHandleUpload', translate('error_reading_the_file'));
                return false;
            }
            return true;
        }
        $this->form_validation->set_message('csvfileHandleUpload', translate('this_field_is_required'));
        return false;
    }
}
