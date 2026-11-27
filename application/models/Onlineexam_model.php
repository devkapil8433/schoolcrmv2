<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Onlineexam_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // online exam save and update function
    public function saveExam($data, $branchID)
    {
        $onlineExam = array(
            'title' => $data['title'],
            'class_id' => $data['class_id'],
            'section_id' => json_encode($this->input->post('section')),
            'subject_id' => json_encode($this->input->post('subject')),
            'limits_participation' => $data['participation_limit'],
            'exam_start' => date('Y-m-d H:i:s', strtotime($data['start_date'] . " " . $data['start_time'])),
            'exam_end' => date('Y-m-d H:i:s', strtotime($data['end_date'] . " " . $data['end_time'])),
            'duration' => date('H:i:s', strtotime($data['duration'])),
            'mark_type' => $data['mark_type'],
            'passing_mark' => $data['passing_mark'],
            'instruction' => $data['instruction'],
            'session_id' => get_session_id(),
            'publish_result' => $data['publish_result'],
            'marks_display' => 0,
            'neg_mark' => (isset($data['negative_marking']) ? 1 : 0),
            'marks_display' => (isset($data['marks_display']) ? 1 : 0),
            'question_type' => $data['question_type'],
            'fee' => ($data['exam_type'] == 1 ? $data['exam_fee'] : 0),
            'exam_type' =>  $data['exam_type'],
            'branch_id' => $branchID,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        );
        if (!isset($_POST['id'])) {
            $onlineExam['publish_status'] = 0; 
            $onlineExam['created_by'] = get_loggedin_user_id();
            $this->db->insert('online_exam', $onlineExam);
        } else {
            $this->db->where('id', $data['id']);
            $this->db->update('online_exam', $onlineExam);
        }
    }

    public function examList()
    {
        $this->db->select('online_exam.*,class.name as class_name,branch.name as branchname');
        $this->db->from('online_exam');
        $this->db->join('branch', 'branch.id = online_exam.branch_id', 'inner');
        $this->db->join('class', 'class.id = online_exam.class_id', 'left');
        if (!is_superadmin_loggedin()) {
            $this->db->where('online_exam.branch_id', get_loggedin_branch_id());
        }
        $this->db->order_by('online_exam.id', 'DESC');
        $result = $this->db->get()->result_array();
        foreach ($result as $key => $value) {
            $result[$key]['section_details'] = $this->getSectionDetails($value['section_id']);
        }
        return $result;
    }

    public function examListDT($currency_symbol = '')
    {
        $sessionID = get_session_id();
        $this->datatables->select('online_exam.*,class.name as class_name,(SELECT COUNT(id) FROM questions_manage WHERE questions_manage.onlineexam_id=online_exam.id) as questions_qty,branch.name as branchname,staff.name as created_by_name');
        $this->datatables->from('online_exam');
        $this->datatables->join('branch', 'branch.id = online_exam.branch_id', 'inner');
        $this->datatables->join('class', 'class.id = online_exam.class_id', 'left');
        $this->datatables->join('staff', 'staff.id = online_exam.created_by', 'left');
        $this->datatables->where('online_exam.session_id', $sessionID);
        if (!is_superadmin_loggedin()) {
            $this->datatables->where('online_exam.branch_id', get_loggedin_branch_id());
            if (!is_superadmin_loggedin() && !is_admin_loggedin()) {
                $this->datatables->where('online_exam.created_by', get_loggedin_user_id());
            }
            $column_order = '';
        } else {
            $column_order = 'branch.id,';
        }
        $this->datatables->search_value("online_exam.title,class.name,online_exam.exam_start,online_exam.exam_end,online_exam.fee,staff.name");
        $this->datatables->column_order("online_exam.id,".$column_order."online_exam.title,class.id,questions_qty,online_exam.exam_start,online_exam.exam_end,online_exam.duration,online_exam.fee");
        $this->datatables->order_by('online_exam.id', 'desc');
        $results = $this->datatables->generate();

        $start = $this->input->post('start');
        $count = $start + 1;
        // data processing for DataTable
        $records = array();
        $records = json_decode($results);
        $data = array();
        foreach ($records->data as $key => $record) {
            if ($record->publish_status == 0) {
                $status = '';
            } else {
                $status = 'checked';
            }
            $row = array();
            $action = "";
            if (get_permission('add_questions', 'is_add')) {
                if ($record->publish_result == 0 && $record->publish_status == 1) {
                    $action .= '<button onclick="confirmModal(' . $this->db->escape(base_url('onlineexam/make_result_publish/' . $record->id)) . ')" class="btn btn-circle btn-default icon" data-toggle="tooltip" data-original-title="' . translate('make') . " " . translate('result_publish') . '"> <i class="fas fa-square-poll-vertical"></i></button>';
                }
            }
            $action .= '<a href="' . base_url('onlineexam/question_list/' . $record->id) . '" class="btn btn-circle btn-default icon" data-toggle="tooltip" data-original-title="' . translate('view') ." ". translate('question') . '"> <i class="fas fa-list-check"></i></a>';
            if ($record->publish_status == 0) {
                $action .= '<a href="' . base_url('onlineexam/manage_question/' . $record->id) . '" class="btn btn-circle btn-default icon" data-toggle="tooltip" data-original-title="' . translate('add_questions') . '"> <i class="fas fa-question"></i></a>';
            }
            if (get_permission('online_exam', 'is_edit')) {
                $action .= '<a href="' . base_url('onlineexam/edit/' . $record->id) . '" class="btn btn-circle btn-default icon" data-toggle="tooltip" data-original-title="' . translate('edit') . '"> <i class="fas fa-pen-nib"></i></a>';
            }
            if (get_permission('online_exam', 'is_delete')) {
                $action .= btn_delete('onlineexam/delete/' . $record->id);
            }
            $row[] = $count++;
            if (is_superadmin_loggedin()) {
                $row[] = $record->branchname;
            }
            $row[] = $record->title;
            $row[] = $record->class_name . " (" . $this->getSectionDetails($record->section_id) . ")";
            $row[] = $record->questions_qty;
            $row[] = _d($record->exam_start) . "<small class='text-muted bs-block'>" . date("h:i A", strtotime($record->exam_start)) . "</small>";
            $row[] = _d($record->exam_end) . "<small class='text-muted bs-block'>" . date("h:i A", strtotime($record->exam_end)) . "</small>";
            $row[] = $record->duration;
            $row[] = $record->exam_type == 0 ? translate('free') : $currency_symbol . $record->fee;
            $row[] = '<div class="material-switch ml-xs">
                        <input class="exam-status" id="examstatus_' . $record->id . '" data-id="' . $record->id . '" name="exam_status' . $record->id . '"
                        type="checkbox" ' . $status . ' />
                        <label for="examstatus_' . $record->id . '" class="label-primary"></label>
                    </div>';
            $row[] = $record->created_by_name;
            $row[] = $action;
            $data[] = $row;
        }
        $json_data = array(
            "draw"                => intval($records->draw),
            "recordsTotal"        => intval($records->recordsTotal),
            "recordsFiltered"     => intval($records->recordsFiltered),
            "data"                => $data,
        );
        return json_encode($json_data);
    }

    public function getSelectExamList($class_id)
    {
        $arrayData = array("" => translate('select'));
        if (!is_superadmin_loggedin() && !is_admin_loggedin()) {
            $this->db->where('created_by', get_loggedin_user_id());
        }
        $this->db->where('class_id', $class_id);
        $this->db->where('session_id', get_session_id());
        $this->db->where('publish_status', 1);
        $this->db->where('publish_result', 1);
        $result = $this->db->get('online_exam')->result();
        foreach ($result as $row) {
            $arrayData[$row->id] = $row->title;
        }
        return $arrayData;
    }

    public function getSectionDetails($data)
    {
        $array = json_decode($data, true);
        $nameList = [];
        if (json_last_error() == JSON_ERROR_NONE) {
            foreach ($array as $key => $value) {
                $nameList[] = get_type_name_by_id('section', $value);
            }
        }
        $nameList = implode(', ', $nameList);
        return $nameList;
    }

    public function getSubjectDetails($data)
    {
        $array = json_decode($data, true);
        $nameList = [];
        if (json_last_error() == JSON_ERROR_NONE) {
            foreach ($array as $key => $value) {
                $nameList[] = get_type_name_by_id('subject', $value);
            }
        }
        $nameList = implode(',<br>', $nameList);
        return $nameList;
    }

    // questions save and update function
    public function saveQuestions()
    {
        $branchID = $this->application_model->get_branch_id();
        $questionType = $this->input->post('question_type');
        if ($questionType == 2) {
            $answer = json_encode($this->input->post('answer'));
        } elseif ($questionType == 5) {
            $pairCount = 4;
            for ($i = 5; $i <= 10; $i++) {
                $l = $this->tagRemove($this->input->post('match_left_' . $i, false));
                $r = $this->tagRemove($this->input->post('option' . $i, false));
                if ($l !== '' && $l !== null || $r !== '' && $r !== null) {
                    $pairCount = $i;
                }
            }
            $answerParts = array();
            for ($i = 1; $i <= $pairCount; $i++) {
                $answerParts[] = $i . '-' . $i;
            }
            $answer = implode(',', $answerParts);
        } else {
            $answer = $this->input->post('answer');
        }

        $questionID = $this->input->post('question_id');
        $classID = $this->input->post('class_id');
        $sectionID = $this->input->post('section_id');
        $subjectID = $this->input->post('subject_id');
        $exerciseID = $this->input->post('exercise_id');
        if ($exerciseID === '' || $exerciseID === null || (int) $exerciseID < 1) {
            $exerciseID = null;
        } else {
            $exerciseID = (int) $exerciseID;
        }

        $questionText = $this->input->post('question', false);
        $opt1 = $this->tagRemove($this->input->post('option1', false));
        $opt2 = $this->tagRemove($this->input->post('option2', false));
        $opt3 = $this->tagRemove($this->input->post('option3', false));
        $opt4 = $this->tagRemove($this->input->post('option4', false));
        if ($questionType == 5) {
            $leftItems = array();
            $rightItems = array($opt1, $opt2, $opt3, $opt4);
            for ($i = 1; $i <= 10; $i++) {
                $leftItems[] = $this->tagRemove($this->input->post('match_left_' . $i, false));
                if ($i >= 5) {
                    $rightItems[] = $this->tagRemove($this->input->post('option' . $i, false));
                }
            }
            $pairCount = 4;
            for ($i = 4; $i <= 9; $i++) {
                if (!empty($leftItems[$i]) || !empty($rightItems[$i])) {
                    $pairCount = $i + 1;
                }
            }
            $leftItems = array_slice($leftItems, 0, $pairCount);
            $rightItems = array_slice($rightItems, 0, $pairCount);
            $questionText = json_encode(array(
                'instruction' => $questionText,
                'left' => $leftItems,
                'right' => $rightItems,
            ));
        }

        $explanationRaw = $this->input->post('explanation', false);
        $explanationVal = null;
        if (is_string($explanationRaw) && $explanationRaw !== '') {
            $plain = trim(preg_replace('/\s+/u', ' ', strip_tags(str_replace(array('&nbsp;', "\xC2\xA0"), ' ', $explanationRaw))));
            if ($plain !== '') {
                $explanationVal = $explanationRaw;
            }
        }

        $customQuestionType = $this->input->post('custom_question_type', true);
        if (in_array((int) $questionType, array(6, 7, 8, 9, 10, 11), true) && empty($customQuestionType)) {
            $fixedTypes = $this->question_type();
            $customQuestionType = isset($fixedTypes[(string) $questionType]) ? $fixedTypes[(string) $questionType] : '';
        }
        $typeMark = $this->getBranchQuestionTypeMark((int) $branchID, (int) $questionType);
        if ($typeMark === null) {
            $typeMark = 1;
        }
        $questionsExam = array(
            'type' => $questionType,
            'custom_question_type' => $customQuestionType,
            'level' => $this->input->post('question_level'),
            'exercise_id' => $exerciseID,
            'question' => $questionText,
            'opt_1' => $questionType == 5 ? '' : $opt1,
            'opt_2' => $questionType == 5 ? '' : $opt2,
            'opt_3' => $questionType == 5 ? '' : $opt3,
            'opt_4' => $questionType == 5 ? '' : $opt4,
            'answer' => $answer,
            'mark' => $typeMark,
            'explanation' => $explanationVal,
        );

        if (!empty($classID)) {
            $questionsExam['class_id'] = $classID;
        }
        if (!empty($sectionID)) {
            $questionsExam['section_id'] = $sectionID;
        }
        if (!empty($subjectID)) {
            $questionsExam['subject_id'] = $subjectID;
        }
        if (!empty($branchID)) {
            $questionsExam['branch_id'] = $branchID;
        }
        if ($this->db->field_exists('lesson_id', 'questions')) {
            $lessonID = (int) $this->input->post('lesson_id');
            $questionsExam['lesson_id'] = $lessonID > 0 ? $lessonID : null;
        }
        $finalQuestionId = (int) $questionID;
        if (empty($questionID)) {
            $questionsExam['created_by'] = get_loggedin_user_id();
            $this->db->insert('questions', $questionsExam);
            $finalQuestionId = (int) $this->db->insert_id();
        } else {
            $this->db->where('id', $questionID);
            $this->db->update('questions', $questionsExam);
            $finalQuestionId = (int) $questionID;
        }

        if ($finalQuestionId > 0 && in_array((int) $questionType, array(6, 7), true)) {
            $this->load->model('onlineexam_subjective_model');
            $this->onlineexam_subjective_model->sync_from_post($finalQuestionId, (int) $questionType);
        }
    }

    private function tagRemove($text="")
    {
        if ($text != "") {
            $text = str_replace("<p>","",$text);
            $text = str_replace("</p>","",$text);
        }
        return $text;
    }

    public function question_level()
    {
        $arrayLevel = array(
            '' => translate("select"),
            '1' => translate("easy"),
            '2' => translate("medium"),
            '3' => translate("hard"),
        );
        return $arrayLevel;
    }

    public function exercise($branch_id = '', $class_id = '', $subject_id = '', $include_id = 0)
    {
        if (empty($branch_id)) {
            return array('' => translate('select_branch_first'));
        }
        $array = array('' => translate('select'));
        if (empty($class_id) || empty($subject_id)) {
            if ((int) $include_id > 0) {
                $row = $this->db->select('id, name')->where('id', (int) $include_id)->get('exercise')->row();
                if ($row) {
                    $array[$row->id] = $row->name;
                }
            }
            return $array;
        }
        $this->db->where('branch_id', $branch_id);
        $this->db->where('class_id', (int) $class_id);
        $this->db->where('subject_id', (int) $subject_id);
        $result = $this->db->get('exercise')->result();
        usort($result, function ($left, $right) {
            return strnatcasecmp((string) $left->name, (string) $right->name);
        });
        foreach ($result as $row) {
            $array[$row->id] = $row->name;
        }
        if ((int) $include_id > 0 && !isset($array[(int) $include_id])) {
            $row = $this->db->select('id, name')->where('id', (int) $include_id)->get('exercise')->row();
            if ($row) {
                $array[$row->id] = $row->name;
            }
        }
        return $array;
    }

    /** @deprecated use exercise() */
    public function question_group($branch_id = '', $class_id = '', $subject_id = '', $include_id = 0)
    {
        return $this->exercise($branch_id, $class_id, $subject_id, $include_id);
    }

    /**
     * Nested Class → Subject → Exercises for expand UI.
     *
     * @param int $branchID
     * @return array
     */
    public function getExerciseFlowByBranch($branchID)
    {
        $branchID = (int) $branchID;
        if ($branchID < 1) {
            return array();
        }

        $this->db->select('qg.id, qg.name, qg.branch_id, qg.class_id, qg.subject_id,
            c.name as class_name, subj.name as subject_name,
            COUNT(q.id) as question_count');
        $this->db->from('exercise qg');
        $this->db->join('class c', 'c.id = qg.class_id', 'left');
        $this->db->join('subject subj', 'subj.id = qg.subject_id', 'left');
        $this->db->join('questions q', 'q.exercise_id = qg.id', 'left');
        $this->db->where('qg.branch_id', $branchID);
        $this->db->where('qg.class_id IS NOT NULL', null, false);
        $this->db->where('qg.class_id >', 0);
        $this->db->where('qg.subject_id IS NOT NULL', null, false);
        $this->db->where('qg.subject_id >', 0);
        $this->db->group_by('qg.id');
        $this->db->order_by('c.name', 'ASC');
        $this->db->order_by('subj.name', 'ASC');
        $rows = $this->db->get()->result_array();

        $flow = array();
        foreach ($rows as $row) {
            $classId = (int) $row['class_id'];
            $subjectId = (int) $row['subject_id'];
            if (!isset($flow[$classId])) {
                $flow[$classId] = array(
                    'class_id' => $classId,
                    'class_name' => $row['class_name'],
                    'exercise_count' => 0,
                    'question_count' => 0,
                    'subjects' => array(),
                );
            }
            if (!isset($flow[$classId]['subjects'][$subjectId])) {
                $flow[$classId]['subjects'][$subjectId] = array(
                    'subject_id' => $subjectId,
                    'subject_name' => $row['subject_name'],
                    'exercise_count' => 0,
                    'question_count' => 0,
                    'exercises' => array(),
                );
            }
            $qCount = (int) $row['question_count'];
            $flow[$classId]['subjects'][$subjectId]['exercises'][] = array(
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'question_count' => $qCount,
            );
            $flow[$classId]['subjects'][$subjectId]['exercise_count']++;
            $flow[$classId]['subjects'][$subjectId]['question_count'] += $qCount;
            $flow[$classId]['exercise_count']++;
            $flow[$classId]['question_count'] += $qCount;
        }

        foreach ($flow as &$classNode) {
            foreach ($classNode['subjects'] as &$subjectNode) {
                usort($subjectNode['exercises'], function ($left, $right) {
                    return strnatcasecmp((string) $left['name'], (string) $right['name']);
                });
            }
            unset($subjectNode);
            $classNode['subjects'] = array_values($classNode['subjects']);
        }
        unset($classNode);

        return array_values($flow);
    }

    public function question_type()
    {
        $arrayType = array(
            '1' => translate("single_choice"),
            '2' => translate("multiple_choice"),
            '3' => translate("true/false"),
            '4' => translate("descriptive"), // legacy label only; not seeded/active
            '5' => translate("match"),
            '6' => 'Reading',
            '7' => 'Writing',
            '8' => 'Fill in the blanks',
            '9' => 'Very short',
            '10' => 'Short',
            '11' => 'Long',
        );
        return $arrayType;
    }

    /**
     * Display label for a question type. Types 8–11 always use canonical names
     * (ignores stale "Disc - …" values in questions.custom_question_type).
     */
    public function resolve_question_type_label($typeId, $customLabel = '')
    {
        $typeId = (int) $typeId;
        $map = $this->question_type();
        $key = (string) $typeId;
        if (in_array($typeId, array(8, 9, 10, 11), true) && isset($map[$key])) {
            return $map[$key];
        }
        $customLabel = trim((string) $customLabel);
        if ($customLabel !== '') {
            return preg_replace('/^Disc\s*-\s*/i', '', $customLabel);
        }
        return isset($map[$key]) ? $map[$key] : ('#' . $typeId);
    }

    public function getBranchQuestionTypes($branchID)
    {
        $branchID = (int) $branchID;
        if (empty($branchID) || !$this->db->table_exists('onlineexam_question_type')) {
            return array();
        }
        return $this->db
            ->select('id,question_type_id,name,description,marks')
            ->from('onlineexam_question_type')
            ->where('branch_id', $branchID)
            ->where('is_active', 1)
            ->where('question_type_id >', 0)
            ->order_by('question_type_id', 'ASC')
            ->get()
            ->result_array();
    }

    /**
     * Marks configured for a branch question type (onlineexam_question_type).
     *
     * @param int $branchId
     * @param int $questionTypeId
     * @return int|null
     */
    public function getBranchQuestionTypeMark($branchId, $questionTypeId)
    {
        $branchId = (int) $branchId;
        $questionTypeId = (int) $questionTypeId;
        if ($branchId < 1 || $questionTypeId < 1 || !$this->db->table_exists('onlineexam_question_type')) {
            return null;
        }
        $row = $this->db->select('marks')
            ->from('onlineexam_question_type')
            ->where('branch_id', $branchId)
            ->where('question_type_id', $questionTypeId)
            ->where('is_active', 1)
            ->limit(1)
            ->get()
            ->row_array();
        return !empty($row) ? (int) $row['marks'] : null;
    }

    /**
     * Insert one dummy question per type (1–11 except legacy 4) for demo/sample,
     * including Reading/Writing with subjective AI metadata and Fill blanks / VS / Short / Long.
     */
    public function addDummyQuestions($branch_id, $created_by)
    {
        $branch_id = (int) $branch_id;
        if (!$branch_id) {
            return 0;
        }
        $class = $this->db->select('id')->where('branch_id', $branch_id)->limit(1)->get('class')->row();
        $section = $this->db->select('id')->where('branch_id', $branch_id)->limit(1)->get('section')->row();
        $subject = $this->db->select('id')->where('branch_id', $branch_id)->limit(1)->get('subject')->row();
        if (!$class || !$section || !$subject) {
            return 0;
        }
        $class_id = (int) $class->id;
        $section_id = (int) $section->id;
        $subject_id = (int) $subject->id;

        $exercise = $this->db->select('id')
            ->where('branch_id', $branch_id)
            ->where('class_id', $class_id)
            ->where('subject_id', $subject_id)
            ->limit(1)
            ->get('exercise')
            ->row();
        if (!$exercise) {
            $this->db->insert('exercise', array(
                'branch_id' => $branch_id,
                'class_id' => $class_id,
                'subject_id' => $subject_id,
                'name' => 'Exercise 1',
            ));
            $exercise_id = $this->db->insert_id();
        } else {
            $exercise_id = (int) $exercise->id;
        }

        $level = 1;
        $mark = 1;
        $inserted = 0;

        $dummies = array(
            array(
                'type' => 1,
                'question' => 'What is the capital of France?',
                'opt_1' => 'London',
                'opt_2' => 'Paris',
                'opt_3' => 'Berlin',
                'opt_4' => 'Madrid',
                'answer' => '2',
            ),
            array(
                'type' => 2,
                'question' => 'Which of the following are programming languages? (Select all that apply)',
                'opt_1' => 'Python',
                'opt_2' => 'HTML',
                'opt_3' => 'Java',
                'opt_4' => 'CSS',
                'answer' => '["1","3"]',
            ),
            array(
                'type' => 3,
                'question' => 'The Earth is the third planet from the Sun.',
                'opt_1' => '',
                'opt_2' => '',
                'opt_3' => '',
                'opt_4' => '',
                'answer' => '1',
            ),
            array(
                'type' => 5,
                'question' => json_encode(array(
                    'instruction' => 'Match the country with its capital.',
                    'left' => array('France', 'Japan', 'India', 'USA'),
                    'right' => array('Paris', 'Tokyo', 'New Delhi', 'Washington D.C.'),
                )),
                'opt_1' => '',
                'opt_2' => '',
                'opt_3' => '',
                'opt_4' => '',
                'answer' => '1-1,2-2,3-3,4-4',
            ),
            array(
                'type' => 6,
                'custom_question_type' => 'Reading',
                'question' => 'What lesson does the narrator learn in this passage? Give two details from the text to support your answer.',
                'opt_1' => '',
                'opt_2' => '',
                'opt_3' => '',
                'opt_4' => '',
                'answer' => 'Sample model: The narrator learns that small acts of kindness are remembered. Detail 1: the neighbor thanked them for helping carry groceries. Detail 2: the narrator recalls feeling proud despite the task being minor.',
                'mark' => 10,
            ),
            array(
                'type' => 7,
                'custom_question_type' => 'Writing',
                'question' => 'Write a short paragraph (about 80–120 words) explaining one way students can reduce plastic waste at school.',
                'opt_1' => '',
                'opt_2' => '',
                'opt_3' => '',
                'opt_4' => '',
                'answer' => 'Sample model: Students can reduce plastic waste by carrying reusable water bottles and lunch containers. A class could run a campaign to replace single-use cutlery with metal or bamboo sets in the cafeteria. Small habits, repeated daily, add up to a large impact.',
                'mark' => 10,
            ),
            array(
                'type' => 8,
                'custom_question_type' => 'Fill in the blanks',
                'question' => 'The chemical formula for water is ____.',
                'opt_1' => '',
                'opt_2' => '',
                'opt_3' => '',
                'opt_4' => '',
                'answer' => 'H2O',
                'mark' => 1,
            ),
            array(
                'type' => 9,
                'custom_question_type' => 'Very short',
                'question' => 'Name the largest planet in our solar system.',
                'opt_1' => '',
                'opt_2' => '',
                'opt_3' => '',
                'opt_4' => '',
                'answer' => 'Jupiter',
                'mark' => 2,
            ),
            array(
                'type' => 10,
                'custom_question_type' => 'Short',
                'question' => 'Write two differences between a plant cell and an animal cell.',
                'opt_1' => '',
                'opt_2' => '',
                'opt_3' => '',
                'opt_4' => '',
                'answer' => 'Plant cells have a cell wall and chloroplasts; animal cells do not.',
                'mark' => 3,
            ),
            array(
                'type' => 11,
                'custom_question_type' => 'Long',
                'question' => 'Describe the water cycle and explain why it is important for life on Earth.',
                'opt_1' => '',
                'opt_2' => '',
                'opt_3' => '',
                'opt_4' => '',
                'answer' => 'The water cycle includes evaporation, condensation, precipitation and collection. It redistributes fresh water, supports ecosystems, and regulates climate, which makes life on Earth possible.',
                'mark' => 5,
            ),
        );

        foreach ($dummies as $q) {
            $configuredMark = $this->getBranchQuestionTypeMark($branch_id, (int) $q['type']);
            $row = array(
                'type' => $q['type'],
                'level' => $level,
                'class_id' => $class_id,
                'section_id' => $section_id,
                'subject_id' => $subject_id,
                'exercise_id' => $exercise_id,
                'question' => $q['question'],
                'opt_1' => $q['opt_1'],
                'opt_2' => $q['opt_2'],
                'opt_3' => $q['opt_3'],
                'opt_4' => $q['opt_4'],
                'answer' => $q['answer'],
                'mark' => $configuredMark !== null ? (float) $configuredMark : (isset($q['mark']) ? (float) $q['mark'] : $mark),
                'branch_id' => $branch_id,
                'created_by' => $created_by,
            );
            if ($this->db->field_exists('custom_question_type', 'questions') && !empty($q['custom_question_type'])) {
                $row['custom_question_type'] = $q['custom_question_type'];
            }
            $this->db->insert('questions', $row);
            $newQuestionId = (int) $this->db->insert_id();
            $inserted++;
            if ($newQuestionId > 0 && in_array((int) $q['type'], array(6, 7), true)) {
                $this->insertSubjectiveDummyForQuestion($newQuestionId, (int) $q['type']);
            }
        }
        return $inserted;
    }

    /**
     * Demo row in onlineexam_question_subjective for Reading/Writing dummy questions (skipped if table missing).
     */
    private function insertSubjectiveDummyForQuestion($questionId, $questionType)
    {
        if (!$this->db->table_exists('onlineexam_question_subjective')) {
            return;
        }
        $questionId = (int) $questionId;
        if ($questionId < 1) {
            return;
        }
        $now = date('Y-m-d H:i:s');
        $weights = '{"accuracy":4,"relevance":2,"completeness":2,"clarity":1,"grammar":1}';
        if ((int) $questionType === 6) {
            $this->db->insert('onlineexam_question_subjective', array(
                'question_id' => $questionId,
                'passage_context' => "Last winter, our neighbor Mrs. Okon broke her wrist. My sister and I offered to walk her dog and carry her groceries for two weeks. At first we thought it would be a chore, but we ended up chatting with her every evening. When she healed, she left a thank-you note and a tin of cookies on our porch. I realized that kindness does not have to be dramatic to matter.",
                'key_points' => "States the main lesson (kindness / helping others / small acts matter)\nReferences the neighbor or injury context\nUses at least one concrete detail from the passage\nExplains the lesson in the student's own words",
                'evaluation_criteria' => "Full marks: clear thesis, two distinct supporting details from the passage, coherent sentences. Partial: vague lesson or only one detail. Low: off-topic or copied passage without analysis.",
                'rubric_weights' => $weights,
                'difficulty' => 3,
                'ai_evaluation_enabled' => 1,
                'hybrid_keyword_boost' => 1,
                'ai_custom_instructions' => 'Penalize answers that only copy sentences from the passage without interpretation.',
                'student_show_obtained_marks' => 1,
                'student_show_feedback' => 1,
                'student_show_strengths' => 1,
                'student_show_weaknesses' => 1,
                'student_show_breakdown' => 0,
                'evaluation_type' => 'reading_writing',
                'created_at' => $now,
                'updated_at' => $now,
            ));
            return;
        }
        if ((int) $questionType === 7) {
            $this->db->insert('onlineexam_question_subjective', array(
                'question_id' => $questionId,
                'passage_context' => null,
                'key_points' => "Names at least one realistic action (reuse, refill bottles, events, awareness)\nExplains why it helps reduce plastic\nAppropriate length and on-topic\nClear beginning and end",
                'evaluation_criteria' => "Reward specific, school-relevant ideas. Penalize generic lists with no explanation or answers far under/over length without substance.",
                'rubric_weights' => $weights,
                'difficulty' => 2,
                'ai_evaluation_enabled' => 1,
                'hybrid_keyword_boost' => 0,
                'ai_custom_instructions' => 'Accept varied valid approaches; do not require the sample model answer wording.',
                'student_show_obtained_marks' => 1,
                'student_show_feedback' => 1,
                'student_show_strengths' => 1,
                'student_show_weaknesses' => 1,
                'student_show_breakdown' => 1,
                'evaluation_type' => 'reading_writing',
                'created_at' => $now,
                'updated_at' => $now,
            ));
        }
    }

    public function questionList($postData)
    {
        $response = array();

        // read value
        $draw = $postData['draw'];
        $start = $postData['start'];
        $rowperpage = $postData['length']; // Rows display per page
        $searchValue = $postData['search']['value']; // Search value

        // Search
        $branchID = $this->db->escape($postData['branch_id']);
        $examID = $this->db->escape($postData['examID']);
        $negMark = $postData['negMark'];

        $search_arr = array();
        $searchQuery = "";
        if ($searchValue != '') {
            $search_arr[] = " (`questions`.`question` like '%" . $searchValue . "%' OR `exercise`.`name` like '%" . $searchValue . "%') ";
        }

        if (!is_superadmin_loggedin()) {
            $showOwnquestion = $this->app_lib->getSchoolConfig($postData['branch_id'], 'show_own_question');
            if (!empty($showOwnquestion->show_own_question) && $showOwnquestion->show_own_question == 1) {
                $search_arr[] = " `questions`.`created_by` = " . $this->db->escape(get_loggedin_user_id());
            } 
        }

        $exerciseFilter = isset($postData['exercise_id']) ? $postData['exercise_id'] : (isset($postData['questionGroup']) ? $postData['questionGroup'] : '');
        if ($exerciseFilter != '') {
            $exerciseFilter = $this->db->escape($exerciseFilter);
            $search_arr[] = " `questions`.`exercise_id` = $exerciseFilter ";
        }

        $questionType = $postData['questionType'];
        if ($questionType != '') {
            $questionType = $this->db->escape($questionType);
            $search_arr[] = " `questions`.`type` = $questionType ";
        }

        $questionLevel = $postData['questionLevel'];
        if ($questionLevel != '') {
            $questionLevel = $this->db->escape($questionLevel);
            $search_arr[] = " `questions`.`level` = $questionLevel ";
        }

        $classID = $postData['classID'];
        if ($classID != '') {
            $classID = $this->db->escape($classID);
            $search_arr[] = " `questions`.`class_id` = $classID ";
        }
        $sectionID = $postData['sectionID'];
        if ($sectionID != '') {
            $sectionID = $this->db->escape($sectionID);
            $search_arr[] = " `questions`.`section_id` = $sectionID ";
        }
        $subjectID = $postData['subjectID'];
        if ($subjectID != '') {
            $subjectID = $this->db->escape($subjectID);
            $search_arr[] = " `questions`.`subject_id` = $subjectID ";
        }

        if (count($search_arr) > 0) {
            $searchQuery = implode("AND", $search_arr);
        }

        // Total number of records without filtering
        $userID = $this->db->escape(get_loggedin_user_id());
        $sql = "SELECT `questions`.`id` FROM `questions` WHERE `questions`.`branch_id` = $branchID";
        if (!is_superadmin_loggedin()) {
            if (!empty($showOwnquestion->show_own_question) && $showOwnquestion->show_own_question == 1) {
                $sql .= " AND `questions`.`created_by` = " . $this->db->escape(get_loggedin_user_id());
            }
        }
        $records = $this->db->query($sql)->result();
        $totalRecords = count($records);

        // Total number of record with filtering
        $sql = "SELECT `questions`.`id`,`exercise`.`name` as `exercise_name` FROM `questions` LEFT JOIN `exercise` ON `exercise`.`id` = `questions`.`exercise_id` WHERE `questions`.`branch_id` = $branchID";
        if (!empty($searchQuery)) {
            $sql .= " AND " . $searchQuery;
        }
        $records = $this->db->query($sql)->result();
        $totalRecordwithFilter = count($records);

        // Fetch records
        $sql = "SELECT `questions`.*, IFNULL(`questions_manage`.`marks`, `questions`.`mark`) as `marks`, IFNULL(`questions_manage`.`neg_marks`, 1) as `neg_marks`, `questions_manage`.`id` as `manage_id`, `branch`.`name`, `subject`.`name` as `subject_name`, `class`.`name` as `class_name`, `section`.`name` as `section_name`, `exercise`.`name` as `exercise_name` FROM `questions` INNER JOIN `branch` ON `branch`.`id` = `questions`.`branch_id` LEFT JOIN `questions_manage` ON `questions_manage`.`question_id` = `questions`.`id` and `questions_manage`.`onlineexam_id` = $examID LEFT JOIN `class` ON `class`.`id` = `questions`.`class_id` LEFT JOIN `section` ON `section`.`id` = `questions`.`section_id` LEFT JOIN `subject` ON `subject`.`id` = `questions`.`subject_id` LEFT JOIN `exercise` ON `exercise`.`id` = `questions`.`exercise_id` WHERE `questions`.`branch_id` = $branchID";
        if (!empty($searchQuery)) {
            $sql .= " AND " . $searchQuery;
        }
        $sql .= " ORDER BY `questions`.`id` ASC LIMIT $start, $rowperpage";
        $records = $this->db->query($sql)->result();

        $data = array();
        $count = $start + 1;
        $question_type = $this->onlineexam_model->question_type();
        $arrayLevel = $this->onlineexam_model->question_level();
        foreach ($records as $key => $record) {

            $checkbox_status = "";
            if (!empty($record->manage_id)) {
                $checkbox_status = "checked";
            }

            $row = array();
            $cb_row = '';
            $cb_row .= '<input type="hidden" name="question[' . $key . '][id]" value="' . $record->id . '">';
            $cb_row .= '<div class="checkbox-replace"><label class="i-checks">';
            $cb_row .= '<input type="checkbox" class="cb_question" name="question[' . $key . '][cb_id]" value="' . $record->id . '"' . $checkbox_status . '><i></i>';
            $cb_row .= '</label></div>';
            $row[] = $cb_row;
            $row[] = $count++;
            $row[] = strip_tags($record->question);
            $row[] = $record->exercise_name;
            $row[] = $record->class_name . " (" . $record->section_name . ")";
            $row[] = $record->subject_name;
            $typeLabel = isset($question_type[$record->type]) ? $question_type[$record->type] : '';
            if (in_array((int) $record->type, array(4, 8, 9, 10, 11), true) && !empty($record->custom_question_type)) {
                $typeLabel = $record->custom_question_type;
            }
            $row[] = $typeLabel;
            $row[] = $arrayLevel[$record->level];
            $row[] = '<div class="form-group"><input type="text" class="form-control" name="question[' . $key . '][marks]" value="' . $record->marks . '"><span class="error"></span></div>';
            if ($negMark == 1) {
                $row[] = '<div class="form-group"><input type="text" class="form-control" name="question[' . $key . '][negative_marks]" value="' . $record->neg_marks . '"><span class="error"></span></div>';
            }
            $data[] = $row;
        }

        // Response
        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecordwithFilter,
            "data" => $data,
        );
        return json_encode($response);
    }

    public function questionListDT($postData)
    {
        $filter_branch_id = (int) $this->input->get('branch_id');
        $show_branch_col = (is_superadmin_loggedin() && empty($filter_branch_id));
        $this->datatables->select('MIN(questions.id) as id,MIN(questions.branch_id) as branch_id,MIN(questions.exercise_id) as exercise_id,MIN(questions.class_id) as class_id,MIN(questions.section_id) as section_id,MIN(questions.subject_id) as subject_id,COUNT(questions.id) as question_count,branch.name,subject.name as subject_name,class.name as class_name,section.name as section_name,exercise.name as exercise_name');
        $this->datatables->from('questions');
        $this->datatables->join('branch', 'branch.id = questions.branch_id', 'inner');
        $this->datatables->join('class', 'class.id = questions.class_id', 'left');
        $this->datatables->join('section', 'section.id = questions.section_id', 'left');
        $this->datatables->join('subject', 'subject.id = questions.subject_id', 'left');
        $this->datatables->join('exercise', 'exercise.id = questions.exercise_id', 'left');
        if (!is_superadmin_loggedin()) {
            $showOwnquestion = $this->app_lib->getSchoolConfig('', 'show_own_question');
            $branch_id = get_loggedin_branch_id();
            $this->datatables->where('questions.branch_id', $branch_id);
            if (!empty($showOwnquestion->show_own_question) && $showOwnquestion->show_own_question == 1) {
                $this->datatables->where('questions.created_by', get_loggedin_user_id());
            } 
            $column_order = '';
        } else {
            if (!empty($filter_branch_id)) {
                $this->datatables->where('questions.branch_id', $filter_branch_id);
            }
            $column_order = $show_branch_col ? 'branch.id,' : '';
        }
        $this->datatables->group_by('questions.branch_id,questions.exercise_id,questions.class_id,questions.section_id,questions.subject_id');
        $this->datatables->search_value("branch.name,exercise.name,class.name,section.name,subject.name");
        $this->datatables->column_order("questions.id,".$column_order."exercise_name,class.id,subject.id,question_count,questions.id");
        $this->datatables->order_by('question_count', 'desc');
        $results = $this->datatables->generate();
        $start = $this->input->post('start');
        
        $records = array();
        $records = json_decode($results);

        $data = array();
        $count = $start + 1;
        foreach ($records->data as $key => $record) {
            $row = array();
            $row[] = $count++;
            if ($show_branch_col) {
                $row[] = $record->name;
            }
            $row[] = $record->exercise_name;
            $row[] = $record->class_name . " (" . $record->section_name . ")";
            $row[] = $record->subject_name;
            $row[] = (int) $record->question_count;
            $addUrl = base_url('onlineexam/question_add?branch_id=' . (int) $record->branch_id . '&exercise_id=' . (int) $record->exercise_id . '&class_id=' . (int) $record->class_id . '&section_id=' . (int) $record->section_id . '&subject_id=' . (int) $record->subject_id);
            $viewUrl = $addUrl;
            $action = '<a href="' . $viewUrl . '" class="btn btn-default btn-circle icon" data-toggle="tooltip" data-original-title="' . translate('view') . '"><i class="fas fa-eye"></i></a>';
            $action .= ' <a href="' . $addUrl . '" class="btn btn-default btn-circle icon" data-toggle="tooltip" data-original-title="Add More"><i class="fas fa-plus"></i></a>';
            $row[] = $action;
            $data[] = $row;
        }

        // Response
        $json_data = array(
            "draw"                => intval($records->draw),
            "recordsTotal"        => intval($records->recordsTotal),
            "recordsFiltered"     => intval($records->recordsFiltered),
            "data"                => $data,
        );
        return json_encode($json_data);
    }

    public function questionListForAddDT($postData)
    {
        $this->datatables->select('questions.id,questions.question,questions.type,questions.custom_question_type,questions.level,branch.name,subject.name as subject_name,class.name as class_name,section.name as section_name,exercise.name as exercise_name');
        $this->datatables->from('questions');
        $this->datatables->join('branch', 'branch.id = questions.branch_id', 'inner');
        $this->datatables->join('class', 'class.id = questions.class_id', 'left');
        $this->datatables->join('section', 'section.id = questions.section_id', 'left');
        $this->datatables->join('subject', 'subject.id = questions.subject_id', 'left');
        $this->datatables->join('exercise', 'exercise.id = questions.exercise_id', 'left');

        $filter_branch_id = (int) $this->input->get('branch_id');
        $filter_class_id = (int) $this->input->get('class_id');
        $filter_section_id = (int) $this->input->get('section_id');
        $filter_subject_id = (int) $this->input->get('subject_id');
        $filter_exercise_id = (int) $this->input->get('exercise_id');

        if (!is_superadmin_loggedin()) {
            $showOwnquestion = $this->app_lib->getSchoolConfig('', 'show_own_question');
            $branch_id = get_loggedin_branch_id();
            $this->datatables->where('questions.branch_id', $branch_id);
            if (!empty($showOwnquestion->show_own_question) && $showOwnquestion->show_own_question == 1) {
                $this->datatables->where('questions.created_by', get_loggedin_user_id());
            }
        }
        if (is_superadmin_loggedin()) {
            if (!empty($filter_branch_id)) {
                $this->datatables->where('questions.branch_id', $filter_branch_id);
            }
        }

        if (!empty($filter_class_id)) {
            $this->datatables->where('questions.class_id', $filter_class_id);
        }
        if (!empty($filter_section_id)) {
            $this->datatables->where('questions.section_id', $filter_section_id);
        }
        if (!empty($filter_subject_id)) {
            $this->datatables->where('questions.subject_id', $filter_subject_id);
        }
        if (!empty($filter_exercise_id)) {
            $this->datatables->where('questions.exercise_id', $filter_exercise_id);
        }
        $filter_question_type = (int) $this->input->get('question_type');
        if ($filter_question_type > 0) {
            $this->datatables->where('questions.type', $filter_question_type);
        }

        $this->datatables->search_value("questions.question,class.name,section.name,subject.name");
        $this->datatables->column_order("questions.id,questions.question,questions.type,questions.level,questions.id");
        $this->datatables->order_by('questions.id', 'desc');
        $results = $this->datatables->generate();
        $start = $this->input->post('start');

        $records = json_decode($results);
        $data = array();
        $count = $start + 1;
        $question_type = $this->onlineexam_model->question_type();
        $arrayLevel = $this->onlineexam_model->question_level();
        foreach ($records->data as $record) {
            $row = array();
            $action = '<a href="' . base_url('onlineexam/question_edit/' . $record->id) . '" class="btn btn-default btn-circle icon" data-toggle="tooltip" data-original-title="' . translate('edit') . '"><i class="fas fa-pen-nib"></i></a>';
            $action .= btn_delete('onlineexam/question_delete/' . $record->id);

            $row[] = $count++;
            $row[] = strip_tags($record->question);
            $typeLabel = isset($question_type[$record->type]) ? $question_type[$record->type] : '';
            if (in_array((int) $record->type, array(4, 8, 9, 10, 11), true) && !empty($record->custom_question_type)) {
                $typeLabel = $record->custom_question_type;
            }
            $row[] = $typeLabel;
            $row[] = isset($arrayLevel[$record->level]) ? $arrayLevel[$record->level] : '';
            $row[] = $action;
            $data[] = $row;
        }

        $json_data = array(
            "draw"                => intval($records->draw),
            "recordsTotal"        => intval($records->recordsTotal),
            "recordsFiltered"     => intval($records->recordsFiltered),
            "data"                => $data,
        );
        return json_encode($json_data);
    }

    /**
     * Question bank page 1: Summary by class & section (same idea as digital_diary lesson index).
     * Rows: class_id, section_id, class_name, section_name, subject_group, subjects, question_count
     */
    public function getQuestionSummaryByBranch($branchID, $filters = array())
    {
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }
        $filters = is_array($filters) ? $filters : array();
        $classId = isset($filters['class_id']) ? (int) $filters['class_id'] : 0;
        $sectionId = isset($filters['section_id']) ? (int) $filters['section_id'] : 0;
        $exerciseId = isset($filters['exercise_id']) ? (int) $filters['exercise_id'] : 0;
        $this->db->select('q.class_id, q.section_id, c.name as class_name, s.name as section_name,
            GROUP_CONCAT(DISTINCT subj.subject_type ORDER BY subj.subject_type SEPARATOR ", ") as subject_group,
            GROUP_CONCAT(DISTINCT subj.name ORDER BY subj.name SEPARATOR ", ") as subjects,
            COUNT(DISTINCT q.id) as question_count');
        $this->db->from('questions q');
        $this->db->join('class c', 'c.id = q.class_id', 'left');
        $this->db->join('section s', 's.id = q.section_id', 'left');
        $this->db->join('subject subj', 'subj.id = q.subject_id', 'left');
        $this->db->where('q.branch_id', $branchID);
        if ($classId > 0) {
            $this->db->where('q.class_id', $classId);
        }
        if ($sectionId > 0) {
            $this->db->where('q.section_id', $sectionId);
        }
        if ($exerciseId > 0) {
            $this->db->where('q.exercise_id', $exerciseId);
        }
        if (!is_superadmin_loggedin()) {
            $showOwnquestion = $this->app_lib->getSchoolConfig('', 'show_own_question');
            if (!empty($showOwnquestion->show_own_question) && $showOwnquestion->show_own_question == 1) {
                $this->db->where('q.created_by', get_loggedin_user_id());
            }
        }
        $this->db->group_by(array('q.class_id', 'q.section_id'));
        $this->db->order_by('c.name, s.name');
        return $this->db->get()->result_array();
    }

    /**
     * Nested question-bank flow for the expandable question index.
     * Class/section -> subject -> lesson -> question type -> exercise -> questions.
     */
    public function getQuestionFlowByBranch($branchID)
    {
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }

        $hasLesson = $this->db->field_exists('lesson_id', 'questions')
            && $this->db->table_exists('digital_diary_lesson');
        $hasCustomType = $this->db->field_exists('custom_question_type', 'questions');

        $select = 'q.id, q.class_id, q.section_id, q.subject_id, q.exercise_id, q.question, q.type,
            c.name as class_name, s.name as section_name, subj.name as subject_name,
            subj.subject_type as subject_group, exercise.name as exercise_name';
        if ($hasCustomType) {
            $select .= ', q.custom_question_type';
        }
        if ($hasLesson) {
            $select .= ', q.lesson_id, ddl.lesson_no, ddl.lesson_name';
        }

        $this->db->select($select);
        $this->db->from('questions q');
        $this->db->join('class c', 'c.id = q.class_id', 'left');
        $this->db->join('section s', 's.id = q.section_id', 'left');
        $this->db->join('subject subj', 'subj.id = q.subject_id', 'left');
        $this->db->join('exercise', 'exercise.id = q.exercise_id', 'left');
        if ($hasLesson) {
            $this->db->join('digital_diary_lesson ddl', 'ddl.id = q.lesson_id', 'left');
        }
        $this->db->where('q.branch_id', (int) $branchID);
        if (!is_superadmin_loggedin()) {
            $showOwnquestion = $this->app_lib->getSchoolConfig('', 'show_own_question');
            if (!empty($showOwnquestion->show_own_question) && $showOwnquestion->show_own_question == 1) {
                $this->db->where('q.created_by', get_loggedin_user_id());
            }
        }
        $this->db->order_by('c.name', 'ASC');
        $this->db->order_by('s.name', 'ASC');
        $this->db->order_by('subj.name', 'ASC');
        if ($hasLesson && $this->db->field_exists('sort_order', 'digital_diary_lesson')) {
            $this->db->order_by('ddl.sort_order', 'ASC');
        } elseif ($hasLesson) {
            $this->db->order_by('ddl.lesson_no', 'ASC');
            $this->db->order_by('ddl.lesson_name', 'ASC');
        }
        $this->db->order_by('q.type', 'ASC');
        $this->db->order_by('q.id', 'ASC');
        $rows = $this->db->get()->result_array();

        $flow = array();
        foreach ($rows as $row) {
            $classKey = (int) $row['class_id'] . '_' . (int) $row['section_id'];
            if (!isset($flow[$classKey])) {
                $flow[$classKey] = array(
                    'class_id' => (int) $row['class_id'],
                    'section_id' => (int) $row['section_id'],
                    'class_name' => $row['class_name'],
                    'section_name' => $row['section_name'],
                    'question_count' => 0,
                    'type_counts' => array(),
                    'subjects' => array(),
                );
            }

            $subjectKey = (int) $row['subject_id'];
            if (!isset($flow[$classKey]['subjects'][$subjectKey])) {
                $flow[$classKey]['subjects'][$subjectKey] = array(
                    'subject_id' => $subjectKey,
                    'subject_name' => $row['subject_name'],
                    'subject_group' => $row['subject_group'],
                    'question_count' => 0,
                    'type_counts' => array(),
                    'lessons' => array(),
                );
            }

            $lessonId = $hasLesson && !empty($row['lesson_id']) ? (int) $row['lesson_id'] : 0;
            if (!isset($flow[$classKey]['subjects'][$subjectKey]['lessons'][$lessonId])) {
                $flow[$classKey]['subjects'][$subjectKey]['lessons'][$lessonId] = array(
                    'lesson_id' => $lessonId,
                    'lesson_no' => $lessonId > 0 ? $row['lesson_no'] : '',
                    'lesson_name' => $lessonId > 0 ? $row['lesson_name'] : translate('other'),
                    'question_count' => 0,
                    'type_counts' => array(),
                    'question_types' => array(),
                );
            }

            $typeId = (int) $row['type'];
            $customQt = $hasCustomType && !empty($row['custom_question_type']) ? $row['custom_question_type'] : '';
            $typeLabel = $this->resolve_question_type_label($typeId, $customQt);
            if (!isset($flow[$classKey]['subjects'][$subjectKey]['lessons'][$lessonId]['question_types'][$typeId])) {
                $flow[$classKey]['subjects'][$subjectKey]['lessons'][$lessonId]['question_types'][$typeId] = array(
                    'type_id' => $typeId,
                    'type_name' => $typeLabel,
                    'question_count' => 0,
                    'exercises' => array(),
                );
            }
            $exerciseId = !empty($row['exercise_id']) ? (int) $row['exercise_id'] : 0;
            if (!isset($flow[$classKey]['subjects'][$subjectKey]['lessons'][$lessonId]['question_types'][$typeId]['exercises'][$exerciseId])) {
                $flow[$classKey]['subjects'][$subjectKey]['lessons'][$lessonId]['question_types'][$typeId]['exercises'][$exerciseId] = array(
                    'exercise_id' => $exerciseId,
                    'exercise_name' => $exerciseId > 0 && !empty($row['exercise_name']) ? $row['exercise_name'] : translate('other'),
                    'question_count' => 0,
                    'questions' => array(),
                );
            }

            if (!isset($flow[$classKey]['type_counts'][$typeId])) {
                $flow[$classKey]['type_counts'][$typeId] = 0;
            }
            if (!isset($flow[$classKey]['subjects'][$subjectKey]['type_counts'][$typeId])) {
                $flow[$classKey]['subjects'][$subjectKey]['type_counts'][$typeId] = 0;
            }
            if (!isset($flow[$classKey]['subjects'][$subjectKey]['lessons'][$lessonId]['type_counts'][$typeId])) {
                $flow[$classKey]['subjects'][$subjectKey]['lessons'][$lessonId]['type_counts'][$typeId] = 0;
            }
            $flow[$classKey]['type_counts'][$typeId]++;
            $flow[$classKey]['subjects'][$subjectKey]['type_counts'][$typeId]++;
            $flow[$classKey]['subjects'][$subjectKey]['lessons'][$lessonId]['type_counts'][$typeId]++;

            $flow[$classKey]['subjects'][$subjectKey]['lessons'][$lessonId]['question_types'][$typeId]['exercises'][$exerciseId]['questions'][] = $row;
            $flow[$classKey]['subjects'][$subjectKey]['lessons'][$lessonId]['question_types'][$typeId]['exercises'][$exerciseId]['question_count']++;
            $flow[$classKey]['subjects'][$subjectKey]['lessons'][$lessonId]['question_types'][$typeId]['question_count']++;
            $flow[$classKey]['subjects'][$subjectKey]['lessons'][$lessonId]['question_count']++;
            $flow[$classKey]['subjects'][$subjectKey]['question_count']++;
            $flow[$classKey]['question_count']++;
        }

        foreach ($flow as &$classSection) {
            foreach ($classSection['subjects'] as &$subject) {
                foreach ($subject['lessons'] as &$lesson) {
                    foreach ($lesson['question_types'] as &$questionType) {
                        $questionType['exercises'] = array_values($questionType['exercises']);
                    }
                    unset($questionType);
                    ksort($lesson['question_types'], SORT_NUMERIC);
                    $lesson['question_types'] = array_values($lesson['question_types']);
                }
                unset($lesson);
                $subject['lessons'] = array_values($subject['lessons']);
            }
            unset($subject);
            $classSection['subjects'] = array_values($classSection['subjects']);
        }
        unset($classSection);

        return array_values($flow);
    }

    /**
     * Question bank page 2: Per subject question counts for a class/section (like lesson_class).
     */
    public function getQuestionSummaryByClassSection($classID, $sectionID, $branchID)
    {
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }
        $classID = (int) $classID;
        $sectionID = (int) $sectionID;

        $typeSumParts = array();
        for ($t = 1; $t <= 11; $t++) {
            $typeSumParts[] = 'SUM(CASE WHEN q.type = ' . (int) $t . ' THEN 1 ELSE 0 END) AS count_type_' . $t;
        }
        $typeSumSql = implode(', ', $typeSumParts);
        $this->db->select(
            'q.class_id, q.section_id, q.subject_id, c.name as class_name, s.name as section_name, '
            . 'subj.name as subject_name, subj.subject_type as subject_group, COUNT(q.id) as total_question, '
            . $typeSumSql,
            false
        );
        $this->db->from('questions q');
        $this->db->join('class c', 'c.id = q.class_id', 'left');
        $this->db->join('section s', 's.id = q.section_id', 'left');
        $this->db->join('subject subj', 'subj.id = q.subject_id', 'left');
        $this->db->where('q.class_id', $classID);
        $this->db->where('q.section_id', $sectionID);
        $this->db->where('q.branch_id', $branchID);
        if (!is_superadmin_loggedin()) {
            $showOwnquestion = $this->app_lib->getSchoolConfig('', 'show_own_question');
            if (!empty($showOwnquestion->show_own_question) && $showOwnquestion->show_own_question == 1) {
                $this->db->where('q.created_by', get_loggedin_user_id());
            }
        }
        $this->db->group_by(array('q.class_id', 'q.section_id', 'q.subject_id'));
        $this->db->order_by('subj.name');
        return $this->db->get()->result_array();
    }

    /**
     * All questions for one class / section / subject (for question_subject view).
     */
    public function getQuestionsByClassSectionSubject($classID, $sectionID, $subjectID, $branchID, $lessonIds = array(), $chapterIds = array(), $questionIds = array(), $exerciseId = 0)
    {
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }
        $lessonIds = is_array($lessonIds) ? $lessonIds : array();
        $chapterIds = is_array($chapterIds) ? $chapterIds : array();
        $questionIds = is_array($questionIds) ? $questionIds : array();
        $exerciseId = (int) $exerciseId;
        $qSelect = 'questions.id, questions.question, questions.type, questions.level, questions.mark, exercise.name as exercise_name';
        if ($this->db->field_exists('custom_question_type', 'questions')) {
            $qSelect .= ', questions.custom_question_type';
        }
        foreach (array('opt_1', 'opt_2', 'opt_3', 'opt_4', 'answer') as $qcol) {
            if ($this->db->field_exists($qcol, 'questions')) {
                $qSelect .= ', questions.' . $qcol;
            }
        }
        $lessonJoin = $this->db->field_exists('lesson_id', 'questions') && $this->db->table_exists('digital_diary_lesson');
        if ($lessonJoin) {
            $qSelect .= ', questions.lesson_id, ddl.lesson_no as lesson_no, ddl.lesson_name as lesson_name';
        }
        $this->db->select($qSelect);
        $this->db->from('questions');
        $this->db->join('exercise', 'exercise.id = questions.exercise_id', 'left');
        if ($lessonJoin) {
            $this->db->join('digital_diary_lesson ddl', 'ddl.id = questions.lesson_id', 'left');
        }
        $this->db->where('questions.class_id', (int) $classID);
        $this->db->where('questions.section_id', (int) $sectionID);
        $this->db->where('questions.subject_id', (int) $subjectID);
        $this->db->where('questions.branch_id', $branchID);
        if ($exerciseId > 0) {
            $this->db->where('questions.exercise_id', $exerciseId);
        }
        if (!empty($chapterIds)) {
            $this->db->where_in('questions.exercise_id', $chapterIds);
        }
        if (!empty($lessonIds) && $this->db->field_exists('lesson_id', 'questions')) {
            $this->db->where_in('questions.lesson_id', $lessonIds);
        }
        if (!empty($questionIds)) {
            $this->db->where_in('questions.id', $questionIds);
        }
        if (!is_superadmin_loggedin()) {
            $showOwnquestion = $this->app_lib->getSchoolConfig('', 'show_own_question');
            if (!empty($showOwnquestion->show_own_question) && $showOwnquestion->show_own_question == 1) {
                $this->db->where('questions.created_by', get_loggedin_user_id());
            }
        }
        $this->db->order_by('questions.type', 'ASC');
        $this->db->order_by('questions.id', 'ASC');
        return $this->db->get()->result_array();
    }

    public function getStudentSubmitted($online_examID = null)
    {
        $r = $this->db->select('id')->where(array('student_id' => get_loggedin_user_id(), 'online_exam_id' => $online_examID))->get('online_exam_submitted')->row();
        return $r;
    }

    public function getExamQuestions($onlineexamID = null, $random_type = 0)
    {
        $this->db->select('questions_manage.*,questions.id as qus_id,questions.*');
        if ($this->db->table_exists('onlineexam_question_subjective')) {
            $this->db->select('sqs.passage_context as subjective_passage');
        }
        $this->db->from('questions_manage');
        $this->db->join('questions', 'questions.id = questions_manage.question_id');
        if ($this->db->table_exists('onlineexam_question_subjective')) {
            $this->db->join('onlineexam_question_subjective sqs', 'sqs.question_id = questions.id', 'left');
        }
        $this->db->where('questions_manage.onlineexam_id', $onlineexamID);
        if ($random_type == 1) {
            $this->db->order_by('rand()');
        } else {
            $this->db->order_by('questions_manage.id', 'DESC');
        }
        $query = $this->db->get();
        return $query->result();
    }

    public function getExamResults($onlineexamID = null, $studentID = 0)
    {
        $extraSelect = '';
        $extraJoin = '';
        if ($this->db->table_exists('online_exam_answer_evaluation')) {
            $extraSelect .= ", `oev`.`id` as `eval_row_id`, `oev`.`status` as `eval_status`, `oev`.`total_score` as `eval_total_score`, `oev`.`max_score` as `eval_max_score`, `oev`.`percentage` as `eval_percentage`, `oev`.`breakdown_json` as `eval_breakdown_json`, `oev`.`strengths_json` as `eval_strengths_json`, `oev`.`weaknesses_json` as `eval_weaknesses_json`, `oev`.`feedback_text` as `eval_feedback_text`, `oev`.`is_off_topic` as `eval_is_off_topic`, `oev`.`confidence` as `eval_confidence`, `oev`.`hybrid_adjustment` as `eval_hybrid_adjustment`, `oev`.`manual_override_marks` as `eval_manual_override_marks`";
            $extraJoin .= " LEFT JOIN `online_exam_answer_evaluation` `oev` ON `oev`.`online_exam_answer_id` = `online_exam_answer`.`id`";
        }
        if ($this->db->table_exists('onlineexam_question_subjective')) {
            $extraSelect .= ", `sqs`.`ai_evaluation_enabled` as `subjective_ai_enabled`, `sqs`.`student_show_obtained_marks` as `subjective_show_marks`, `sqs`.`student_show_feedback` as `subjective_show_feedback`, `sqs`.`student_show_strengths` as `subjective_show_strengths`, `sqs`.`student_show_weaknesses` as `subjective_show_weaknesses`, `sqs`.`student_show_breakdown` as `subjective_show_breakdown`, `sqs`.`passage_context` as `subjective_passage`";
        }
        $subJoin = $this->db->table_exists('onlineexam_question_subjective')
            ? " LEFT JOIN `onlineexam_question_subjective` `sqs` ON `sqs`.`question_id` = `questions`.`id`"
            : '';
        $sql = "SELECT `questions_manage`.*, `questions`.`id` as `qus_id`, `questions`.*, `online_exam_answer`.`answer` as `sb_ans`, `online_exam_answer`.`id` as `ans_id`"
            . $extraSelect
            . " FROM `questions_manage` INNER JOIN `questions` ON `questions`.`id` = `questions_manage`.`question_id` LEFT JOIN `online_exam_answer` ON `online_exam_answer`.`online_exam_id` = `questions_manage`.`onlineexam_id` and `online_exam_answer`.`question_id` = `questions`.`id` and `online_exam_answer`.`student_id` = " . $this->db->escape($studentID)
            . $extraJoin
            . $subJoin
            . " WHERE `questions_manage`.`onlineexam_id` = " . $this->db->escape($onlineexamID) . " ORDER BY `questions_manage`.`id` ASC";
        $query = $this->db->query($sql);
        return $query->result();
    }

    public function getExamDetails($onlineexamID, $status = true)
    {
        $onlineexamID = $this->db->escape($onlineexamID);
        $sessionID = $this->db->escape(get_session_id());
        $branchID = $this->db->escape(get_loggedin_branch_id());
        $sql = "SELECT `online_exam`.*, `class`.`name` as `class_name`,(SELECT COUNT(`id`) FROM `questions_manage` WHERE `questions_manage`.`onlineexam_id`=`online_exam`.`id`) as `questions_qty`, `branch`.`name` as `branchname` FROM `online_exam` INNER JOIN `branch` ON `branch`.`id` = `online_exam`.`branch_id` LEFT JOIN `class` ON `class`.`id` = `online_exam`.`class_id` WHERE `online_exam`.`session_id` = $sessionID AND `online_exam`.`id` = " . $onlineexamID;
        if ($status == true) {
            $sql .= " AND `online_exam`.`publish_status` = '1'";
        }
        if (!is_superadmin_loggedin()) {
            $sql .= " AND `online_exam`.`branch_id` = $branchID";
        }
        $records = $this->db->query($sql)->row();
        return $records;
    }

    public function getStudentAttempt($onlineexamID)
    {
        $this->db->select('IFNULL(SUM(count), 0) as att');
        $this->db->where(array('student_id' => get_loggedin_user_id(), 'online_exam_id' => $onlineexamID));
        $r = $this->db->get('online_exam_attempts')->row();
        return $r->att;
    }

    public function addStudentAttemts($onlineexamID)
    {
        $query = $this->db->where(array('student_id' => get_loggedin_user_id(), 'online_exam_id' => $onlineexamID))->get('online_exam_attempts');
        if ($query->num_rows() > 0) {
            $this->db->set('count', 'count+1', false);
            $this->db->where('id', $query->row()->id);
            $this->db->update('online_exam_attempts');
        } else {
            $this->db->insert('online_exam_attempts', ['student_id' => get_loggedin_user_id(), 'online_exam_id' => $onlineexamID, 'count' => 1]);
        }
    }

    public function resetStudentAttempts($onlineexamID)
    {
        $this->db->where(array(
            'student_id' => get_loggedin_user_id(),
            'online_exam_id' => $onlineexamID,
        ))->delete('online_exam_attempts');
    }

    public function studentHasExamAnswers($onlineexamID)
    {
        return $this->db->where(array(
            'student_id' => get_loggedin_user_id(),
            'online_exam_id' => $onlineexamID,
        ))->count_all_results('online_exam_answer') > 0;
    }

    public function getSubjectByClass($classID = '', $sectionID = '')
    {
        if (loggedin_role_id() == 3) {
            $restricted = $this->getSingle('branch', get_loggedin_branch_id(), true)->teacher_restricted;
            if ($restricted == 1) {
                $getClassTeacher = $this->getClassTeacherByClassSection($classID);
                if ($getClassTeacher == true) {
                    $query = $this->getSubjectList($classID, $sectionID);
                } else {
                    $this->db->select('timetable_class.subject_id,subject.name as subjectname');
                    $this->db->from('timetable_class');
                    $this->db->join('section', 'section.id = timetable_class.section_id', 'left');
                    $this->db->join('subject', 'subject.id = timetable_class.subject_id', 'left');
                    $this->db->where(array('timetable_class.teacher_id' => get_loggedin_user_id(), 'timetable_class.session_id' => get_session_id(), 'timetable_class.class_id' => $classID));
                    $this->db->group_by('timetable_class.subject_id');
                    $query = $this->db->get();
                }
            } else {
                $query = $this->getSubjectList($classID, $sectionID);
            }
        } else {
            $query = $this->getSubjectList($classID, $sectionID);
        }
        return $query;
    }

    public function getSubjectList($classID = '')
    {
        $this->db->select('subject_assign.subject_id, subject.name as subjectname');
        $this->db->from('subject_assign');
        $this->db->join('subject', 'subject.id = subject_assign.subject_id', 'left');
        $this->db->where('class_id', $classID);
        $this->db->where('session_id', get_session_id());
        $query = $this->db->get();
        return $query;
    }

    public function getClassTeacherByClassSection($classID = '')
    {
        $this->db->select('teacher_allocation.id');
        $this->db->from('teacher_allocation');
        $this->db->where('teacher_allocation.teacher_id', get_loggedin_user_id());
        $this->db->where('teacher_allocation.session_id', get_session_id());
        $this->db->where('teacher_allocation.class_id', $classID);
        $q = $this->db->get()->num_rows();
        if ($q > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function examReport($examID = '', $classID = '', $branchID = '',$order_by_position = 0)
    {
        $this->db->select('online_exam_submitted.student_id,online_exam_submitted.remark,online_exam_submitted.position,online_exam.id,online_exam.neg_mark,online_exam.mark_type,online_exam.passing_mark,student.first_name,student.last_name,student.register_no,student.mobileno,online_exam.title,online_exam.section_id,online_exam.subject_id,class.name as class_name');
        $this->db->from('online_exam_submitted');
        $this->db->join('online_exam', 'online_exam.id = online_exam_submitted.online_exam_id', 'inner');
        $this->db->join('class', 'class.id = online_exam.class_id', 'left');
        $this->db->join('student', 'student.id = online_exam_submitted.student_id', 'left');
        $this->db->where('online_exam_submitted.online_exam_id', $examID);
        $this->db->where('online_exam.session_id', get_session_id());
        $this->db->where('online_exam.class_id', $classID);
        $result = $this->db->get()->result_array();
        foreach ($result as $key => $value) {
            $examResult = $this->examResult($value['id'], $value['student_id']);
            $status = 0;
            $mark = 0;
            $score = 0;
            $total_neg_marks = $value['neg_mark'] == 0 ? 0 : $examResult['total_neg_marks'];
            if ($examResult['total_obtain_marks'] != 0) {
                if ($value['mark_type'] == 1) {
                    $obtain = ((($examResult['total_obtain_marks'] - $total_neg_marks) * 100) / $examResult['total_marks']);
                    if ($obtain >= $value['passing_mark']) {
                        $status = 1;
                    } else {
                        $status = 0;
                    }
                } else {
                    $obtain = ($examResult['total_obtain_marks'] - $total_neg_marks);
                    if ($obtain >= $value['passing_mark']) {
                        $status = 1;
                    } else {
                        $status = 0;
                    }
                }
                $mark = ($examResult['total_obtain_marks'] - $total_neg_marks);
            }
            $score = ($examResult['total_marks'] === 0) ? '0.00' : number_format(((($examResult['total_obtain_marks'] - $total_neg_marks) * 100) / $examResult['total_marks']), 2, '.', '');
            $result[$key]['result'] = $status;
            $result[$key]['mark'] = $mark;
            $result[$key]['totalmark'] = $examResult['total_marks'];
            $result[$key]['score'] = $score;
        }
        if ($order_by_position == 1) {
            array_multisort(array_column($result, 'position'), SORT_ASC, $result);
        } else {
            array_multisort(array_column($result, 'score'), SORT_DESC, $result);
        }
        return $result;
    }

    public function examResult($examID, $studentID)
    {
        $result = $this->getExamResults($examID, $studentID);
        $correct_ans = 0;
        $total_question = 0;
        $total_neg_marks = 0;
        $total_marks = 0;
        $total_obtain_marks = 0;
        $wrong_ans = 0;
        $total_answered = 0;
        if (!empty($result)) {
            $total_question = count($result);
            foreach ($result as $key => $value) {
                $total_marks = $total_marks + $value->marks;
                if (!empty($value->ans_id)) {
                    $total_answered++;
                    if ($value->type == 1 || $value->type == 3) {
                        if ($value->sb_ans == $value->answer) {
                            $correct_ans++;
                            $total_obtain_marks = $total_obtain_marks + $value->marks;
                        } else {
                            $total_neg_marks = $total_neg_marks + $value->neg_marks;
                            $wrong_ans++;
                        }
                    } elseif ($value->type == 2) {
                        if ($this->array_equal(json_decode($value->answer), json_decode($value->sb_ans))) {
                            $correct_ans++;
                            $total_obtain_marks = $total_obtain_marks + $value->marks;
                        } else {
                            $total_neg_marks = $total_neg_marks + $value->neg_marks;
                            $wrong_ans++;
                        }
                    } elseif (in_array((int) $value->type, array(4, 8, 9, 10, 11), true)) {
                        $correctAns = str_replace(" ", "_", $value->answer);
                        $studentAns = str_replace(" ", "_", $value->sb_ans);
                        if (strtolower($correctAns) == strtolower($studentAns)) {
                            $correct_ans++;
                            $total_obtain_marks = $total_obtain_marks + $value->marks;
                        } else {
                            $total_neg_marks = $total_neg_marks + $value->neg_marks;
                            $wrong_ans++;
                        }
                    } elseif ($value->type == 6 || $value->type == 7) {
                        $aiOn = isset($value->subjective_ai_enabled) && (int) $value->subjective_ai_enabled === 1;
                        if ($aiOn && $this->db->table_exists('online_exam_answer_evaluation')) {
                            $override = isset($value->eval_manual_override_marks) && $value->eval_manual_override_marks !== null && $value->eval_manual_override_marks !== '';
                            if ($override) {
                                $obt = min((float) $value->marks, max(0, (float) $value->eval_manual_override_marks));
                                $total_obtain_marks += $obt;
                                if ($obt >= (float) $value->marks - 0.001) {
                                    $correct_ans++;
                                } elseif ($obt <= 0.001) {
                                    $wrong_ans++;
                                }
                            } elseif (isset($value->eval_status) && $value->eval_status === 'completed') {
                                $ts = isset($value->eval_total_score) ? (float) $value->eval_total_score : 0;
                                $ms = isset($value->eval_max_score) ? (float) $value->eval_max_score : 0;
                                $hybrid = isset($value->eval_hybrid_adjustment) ? (float) $value->eval_hybrid_adjustment : 0;
                                $qm = (float) $value->marks;
                                if ($ms <= 0) {
                                    $obt = 0;
                                } else {
                                    $obt = min($qm, ($ts / $ms) * $qm + $hybrid);
                                }
                                $total_obtain_marks += $obt;
                                if ($obt >= $qm - 0.001) {
                                    $correct_ans++;
                                } elseif ($obt <= 0.001) {
                                    $wrong_ans++;
                                }
                            } elseif (isset($value->eval_status) && in_array($value->eval_status, array('pending', 'processing', 'failed', 'manual_review'), true)) {
                                // Await AI or teacher; no automatic negative marking
                            } else {
                                // Answered but no evaluation row yet
                            }
                        } else {
                            $correctAns = str_replace(" ", "_", $value->answer);
                            $studentAns = str_replace(" ", "_", $value->sb_ans);
                            if (strtolower($correctAns) == strtolower($studentAns)) {
                                $correct_ans++;
                                $total_obtain_marks = $total_obtain_marks + $value->marks;
                            } else {
                                $total_neg_marks = $total_neg_marks + $value->neg_marks;
                                $wrong_ans++;
                            }
                        }
                    } elseif ($value->type == 5) {
                        if ($this->match_answer_equal($value->answer, $value->sb_ans)) {
                            $correct_ans++;
                            $total_obtain_marks = $total_obtain_marks + $value->marks;
                        } else {
                            $total_neg_marks = $total_neg_marks + $value->neg_marks;
                            $wrong_ans++;
                        }
                    }
                }
            }
        }
        return ['total_marks' => $total_marks, 'total_obtain_marks' => $total_obtain_marks, 'correct_ans' => $correct_ans, 'total_question' => $total_question, 'total_neg_marks' => $total_neg_marks, 'wrong_ans' => $wrong_ans, 'total_answered' => $total_answered];
    }

    public function array_equal($a, $b)
    {
        return (
            is_array($a) && is_array($b) && count($a) == count($b) && array_diff($a, $b) === array_diff($b, $a)
        );
    }

    /**
     * Compare match-type answers (format: "1-1,2-2,3-3,4-4" = left-right pairs).
     */
    public function match_answer_equal($correct, $student)
    {
        if (empty($student)) {
            return false;
        }
        $correctPairs = array();
        foreach (array_map('trim', explode(',', $correct)) as $pair) {
            $parts = explode('-', $pair, 2);
            if (count($parts) == 2) {
                $correctPairs[trim($parts[0])] = trim($parts[1]);
            }
        }
        ksort($correctPairs);
        $studentPairs = array();
        foreach (array_map('trim', explode(',', $student)) as $pair) {
            $parts = explode('-', $pair, 2);
            if (count($parts) == 2) {
                $studentPairs[trim($parts[0])] = trim($parts[1]);
            }
        }
        ksort($studentPairs);
        return $correctPairs === $studentPairs;
    }

    /**
     * Question paper design templates (layout + section structure).
     *
     * @param int         $branchId
     * @param string|null $sessionId
     * @return array
     */
    public function getPaperDesignList($branchId, $sessionId = null)
    {
        $branchId = (int) $branchId;
        if ($branchId < 1 || !$this->db->table_exists('onlineexam_paper_design')) {
            return array();
        }
        if ($sessionId === null) {
            $sessionId = get_session_id();
        }
        $this->db->select('d.id, d.title, d.updated_at, d.created_at, s.name as staff_name');
        $this->db->from('onlineexam_paper_design d');
        $this->db->join('staff s', 's.id = d.created_by', 'left');
        $this->db->where('d.branch_id', $branchId);
        $this->db->where('d.session_id', (int) $sessionId);
        $this->db->order_by('d.id', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * Paper design templates with config JSON (for create-question-paper template picker).
     *
     * @param int         $branchId
     * @param string|null $sessionId
     * @return array
     */
    public function getPaperDesignListWithConfig($branchId, $sessionId = null)
    {
        $branchId = (int) $branchId;
        if ($branchId < 1 || !$this->db->table_exists('onlineexam_paper_design')) {
            return array();
        }
        if ($sessionId === null) {
            $sessionId = get_session_id();
        }
        $this->db->select('d.id, d.title, d.updated_at, d.config');
        $this->db->from('onlineexam_paper_design d');
        $this->db->where('d.branch_id', $branchId);
        $this->db->where('d.session_id', (int) $sessionId);
        $this->db->order_by('d.id', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * @param int $id
     * @param int $branchId
     * @return array|null
     */
    public function getPaperDesignById($id, $branchId)
    {
        $id = (int) $id;
        $branchId = (int) $branchId;
        if ($id < 1 || $branchId < 1 || !$this->db->table_exists('onlineexam_paper_design')) {
            return null;
        }
        $this->db->where('id', $id);
        $this->db->where('branch_id', $branchId);
        $row = $this->db->get('onlineexam_paper_design')->row_array();
        return $row ?: null;
    }

    /**
     * @param int    $branchId
     * @param int    $sessionId
     * @param int    $userId
     * @param string $title
     * @param string $configJson
     * @param int    $id 0 = insert
     * @return int|false new or updated id
     */
    public function savePaperDesign($branchId, $sessionId, $userId, $title, $configJson, $id = 0)
    {
        $branchId = (int) $branchId;
        $sessionId = (int) $sessionId;
        $userId = (int) $userId;
        $id = (int) $id;
        if ($branchId < 1 || !$this->db->table_exists('onlineexam_paper_design')) {
            return false;
        }
        $title = trim((string) $title);
        if ($title === '') {
            $title = 'Paper ' . date('Y-m-d H:i');
        }
        $now = date('Y-m-d H:i:s');
        $row = array(
            'title' => $title,
            'config' => $configJson,
            'updated_at' => $now,
        );
        if ($id > 0) {
            $this->db->where('id', $id);
            $this->db->where('branch_id', $branchId);
            $this->db->update('onlineexam_paper_design', $row);
            return $id;
        }
        $row['branch_id'] = $branchId;
        $row['session_id'] = $sessionId;
        $row['created_by'] = $userId;
        $row['created_at'] = $now;
        $this->db->insert('onlineexam_paper_design', $row);
        return (int) $this->db->insert_id();
    }

    /**
     * @param int $id
     * @param int $branchId
     * @return bool
     */
    public function deletePaperDesign($id, $branchId)
    {
        $id = (int) $id;
        $branchId = (int) $branchId;
        if ($id < 1 || $branchId < 1 || !$this->db->table_exists('onlineexam_paper_design')) {
            return false;
        }
        $this->db->where('id', $id);
        $this->db->where('branch_id', $branchId);
        $this->db->delete('onlineexam_paper_design');
        return $this->db->affected_rows() > 0;
    }

    /**
     * Save one created question paper row.
     *
     * @param array $row
     * @return int|false
     */
    public function saveQuestionPaper($row)
    {
        if (!$this->db->table_exists('onlineexam_question_paper')) {
            return false;
        }
        $this->db->insert('onlineexam_question_paper', $row);
        $id = (int) $this->db->insert_id();
        return $id > 0 ? $id : false;
    }

    /**
     * List created question papers for branch/session.
     *
     * @param int $branchId
     * @param int $sessionId
     * @return array
     */
    public function getQuestionPaperList($branchId, $sessionId)
    {
        $branchId = (int) $branchId;
        $sessionId = (int) $sessionId;
        if ($branchId < 1 || $sessionId < 1 || !$this->db->table_exists('onlineexam_question_paper')) {
            return array();
        }
        $this->db->select('qp.*, c.name as class_name, s.name as section_name, sub.name as subject_name, st.name as creator_name');
        $this->db->from('onlineexam_question_paper qp');
        $this->db->join('class c', 'c.id = qp.class_id', 'left');
        $this->db->join('section s', 's.id = qp.section_id', 'left');
        $this->db->join('subject sub', 'sub.id = qp.subject_id', 'left');
        $this->db->join('staff st', 'st.id = qp.created_by', 'left');
        $this->db->where('qp.branch_id', $branchId);
        $this->db->where('qp.session_id', $sessionId);
        if (!is_superadmin_loggedin() && !is_admin_loggedin()) {
            $this->db->where('qp.created_by', get_loggedin_user_id());
        }
        $this->db->order_by('qp.id', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * One question paper by id/branch/session.
     *
     * @param int $id
     * @param int $branchId
     * @param int $sessionId
     * @return array
     */
    public function getQuestionPaperById($id, $branchId, $sessionId)
    {
        $id = (int) $id;
        $branchId = (int) $branchId;
        $sessionId = (int) $sessionId;
        if ($id < 1 || $branchId < 1 || $sessionId < 1 || !$this->db->table_exists('onlineexam_question_paper')) {
            return array();
        }
        $this->db->select('qp.*, c.name as class_name, s.name as section_name, sub.name as subject_name, st.name as creator_name');
        $this->db->from('onlineexam_question_paper qp');
        $this->db->join('class c', 'c.id = qp.class_id', 'left');
        $this->db->join('section s', 's.id = qp.section_id', 'left');
        $this->db->join('subject sub', 'sub.id = qp.subject_id', 'left');
        $this->db->join('staff st', 'st.id = qp.created_by', 'left');
        $this->db->where('qp.id', $id);
        $this->db->where('qp.branch_id', $branchId);
        $this->db->where('qp.session_id', $sessionId);
        if (!is_superadmin_loggedin() && !is_admin_loggedin()) {
            $this->db->where('qp.created_by', get_loggedin_user_id());
        }
        $row = $this->db->get()->row_array();
        return is_array($row) ? $row : array();
    }

    /**
     * Update editable fields of question paper.
     *
     * @param int   $id
     * @param int   $branchId
     * @param int   $sessionId
     * @param array $row
     * @return bool
     */
    public function updateQuestionPaper($id, $branchId, $sessionId, $row)
    {
        $id = (int) $id;
        $branchId = (int) $branchId;
        $sessionId = (int) $sessionId;
        if ($id < 1 || $branchId < 1 || $sessionId < 1 || !$this->db->table_exists('onlineexam_question_paper')) {
            return false;
        }
        $this->db->where('id', $id);
        $this->db->where('branch_id', $branchId);
        $this->db->where('session_id', $sessionId);
        if (!is_superadmin_loggedin() && !is_admin_loggedin()) {
            $this->db->where('created_by', get_loggedin_user_id());
        }
        $this->db->update('onlineexam_question_paper', $row);
        return true;
    }

    /**
     * Get question rows from comma-separated ids.
     *
     * @param string $questionIdsCsv
     * @param int    $branchId
     * @return array
     */
    public function getQuestionsByCsvIds($questionIdsCsv, $branchId)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', explode(',', (string) $questionIdsCsv)))));
        if (empty($ids) || !$this->db->table_exists('questions')) {
            return array();
        }
        $branchId = (int) $branchId;
        $sel = 'q.id, q.question, q.type, q.mark, q.exercise_id, q.lesson_id, q.custom_question_type, g.name as exercise_name';
        foreach (array('opt_1', 'opt_2', 'opt_3', 'opt_4', 'opt_5', 'opt_6', 'opt_7', 'opt_8', 'opt_9', 'opt_10', 'answer') as $qcol) {
            if ($this->db->field_exists($qcol, 'questions')) {
                $sel .= ', q.' . $qcol;
            }
        }
        $this->db->select($sel);
        if ($this->db->table_exists('digital_diary_lesson')) {
            $this->db->select('ddl.lesson_no, ddl.lesson_name');
        }
        $this->db->from('questions q');
        $this->db->join('exercise g', 'g.id = q.exercise_id', 'left');
        if ($this->db->table_exists('digital_diary_lesson')) {
            $this->db->join('digital_diary_lesson ddl', 'ddl.id = q.lesson_id', 'left');
        }
        $this->db->where_in('q.id', $ids);
        if ($branchId > 0) {
            $this->db->where('q.branch_id', $branchId);
        }
        $rows = $this->db->get()->result_array();
        if (empty($rows)) {
            return array();
        }
        $byId = array();
        foreach ($rows as $r) {
            $byId[(int) $r['id']] = $r;
        }
        $out = array();
        foreach ($ids as $id) {
            if (isset($byId[$id])) {
                $out[] = $byId[$id];
            }
        }
        return $out;
    }

    /**
     * Digital Diary lessons for class/section/subject with question-bank counts (questions.lesson_id).
     *
     * @param int $branchId
     * @param int $classId
     * @param int $sectionId
     * @param int $subjectId
     * @return array<int,array{id:int,lesson_no:string,lesson_name:string,name:string,qcount:int}>
     */
    public function getLessonsWithQuestionCounts($branchId, $classId, $sectionId, $subjectId)
    {
        if (!$this->db->table_exists('digital_diary_lesson') || !$this->db->field_exists('lesson_id', 'questions')) {
            return array();
        }
        $branchId = (int) $branchId;
        $classId = (int) $classId;
        $sectionId = (int) $sectionId;
        $subjectId = (int) $subjectId;
        if ($branchId < 1 || $classId < 1 || $sectionId < 1 || $subjectId < 1) {
            return array();
        }
        $this->load->model('digital_diary_model');
        $lessons = $this->digital_diary_model->getLessonsBySubject($classId, $sectionId, $subjectId, $branchId);
        if (empty($lessons)) {
            return array();
        }
        $sessionId = (int) get_session_id();
        if ($sessionId > 0) {
            $bySession = array();
            foreach ($lessons as $row) {
                if (isset($row['session_id']) && (int) $row['session_id'] === $sessionId) {
                    $bySession[] = $row;
                }
            }
            if (!empty($bySession)) {
                $lessons = $bySession;
            }
        }
        $ids = array();
        foreach ($lessons as $l) {
            $ids[] = (int) $l['id'];
        }
        if (empty($ids)) {
            return array();
        }
        $map = array();
        $this->db->select('lesson_id, COUNT(id) AS cnt');
        $this->db->from('questions');
        $this->db->where('branch_id', $branchId);
        $this->db->where('class_id', $classId);
        $this->db->where('section_id', $sectionId);
        $this->db->where('subject_id', $subjectId);
        $this->db->where_in('lesson_id', $ids);
        $this->db->group_by('lesson_id');
        foreach ($this->db->get()->result_array() as $r) {
            $map[(int) $r['lesson_id']] = (int) $r['cnt'];
        }
        $out = array();
        foreach ($lessons as $l) {
            $lid = (int) $l['id'];
            $no = isset($l['lesson_no']) ? trim((string) $l['lesson_no']) : '';
            $nm = isset($l['lesson_name']) ? trim((string) $l['lesson_name']) : '';
            $label = trim($no . ($no !== '' ? '. ' : '') . $nm);
            $out[] = array(
                'id' => $lid,
                'lesson_no' => $no,
                'lesson_name' => $nm,
                'name' => $label,
                'qcount' => isset($map[$lid]) ? $map[$lid] : 0,
            );
        }
        return $out;
    }

    /**
     * Question groups ("chapters") with question counts for create question paper filters.
     *
     * @param int $branchId
     * @param int $classId
     * @param int $sectionId
     * @param int $subjectId
     * @return array
     */
    public function getExercisesWithCounts($branchId, $classId, $sectionId, $subjectId)
    {
        $branchId = (int) $branchId;
        $classId = (int) $classId;
        $sectionId = (int) $sectionId;
        $subjectId = (int) $subjectId;
        if ($branchId < 1 || $classId < 1 || $sectionId < 1 || $subjectId < 1) {
            return array();
        }
        $this->db->select('exercise.id, exercise.name, COUNT(questions.id) AS qcount');
        $this->db->from('exercise');
        $this->db->join('questions', 'questions.exercise_id = exercise.id', 'inner');
        $this->db->where('exercise.branch_id', $branchId);
        $this->db->where('questions.class_id', $classId);
        $this->db->where('questions.section_id', $sectionId);
        $this->db->where('questions.subject_id', $subjectId);
        $this->db->group_by('exercise.id');
        $rows = $this->db->get()->result_array();
        usort($rows, function ($left, $right) {
            return strnatcasecmp((string) $left['name'], (string) $right['name']);
        });
        return $rows;
    }

    /**
     * Questions for class/section/subject to support chapter-wise picker in create paper page.
     *
     * @param int $branchId
     * @param int $classId
     * @param int $sectionId
     * @param int $subjectId
     * @return array
     */
    public function getQuestionsForPaperPicker($branchId, $classId, $sectionId, $subjectId)
    {
        $branchId = (int) $branchId;
        $classId = (int) $classId;
        $sectionId = (int) $sectionId;
        $subjectId = (int) $subjectId;
        if ($branchId < 1 || $classId < 1 || $sectionId < 1 || $subjectId < 1) {
            return array();
        }
        $sel = 'questions.id, questions.question, questions.type, questions.mark, questions.exercise_id, questions.lesson_id, exercise.name as exercise_name';
        if ($this->db->field_exists('custom_question_type', 'questions')) {
            $sel .= ', questions.custom_question_type';
        }
        foreach (array('opt_1', 'opt_2', 'opt_3', 'opt_4', 'answer') as $qcol) {
            if ($this->db->field_exists($qcol, 'questions')) {
                $sel .= ', questions.' . $qcol;
            }
        }
        $this->db->select($sel);
        $this->db->from('questions');
        $this->db->join('exercise', 'exercise.id = questions.exercise_id', 'left');
        $this->db->where('questions.branch_id', $branchId);
        $this->db->where('questions.class_id', $classId);
        $this->db->where('questions.section_id', $sectionId);
        $this->db->where('questions.subject_id', $subjectId);
        if (!is_superadmin_loggedin()) {
            $showOwnquestion = $this->app_lib->getSchoolConfig('', 'show_own_question');
            if (!empty($showOwnquestion->show_own_question) && $showOwnquestion->show_own_question == 1) {
                $this->db->where('questions.created_by', get_loggedin_user_id());
            }
        }
        $rows = $this->db->get()->result_array();
        usort($rows, function ($left, $right) {
            $exerciseOrder = strnatcasecmp((string) $left['exercise_name'], (string) $right['exercise_name']);
            if ($exerciseOrder !== 0) {
                return $exerciseOrder;
            }
            return (int) $right['id'] - (int) $left['id'];
        });
        return $rows;
    }

    /**
     * Assign lesson_id to old question rows using digital_diary_lesson for the same
     * branch, class, section, and subject. Prefers current academic session; falls back
     * to the newest session that has lessons for that group.
     *
     * @param int|null $sessionId Academic session id (null = get_session_id())
     * @param string   $strategy  round_robin (default) spreads questions across lessons; first uses only the first lesson
     * @return array{updated:int,skipped_no_lesson:int,message:string}
     */
    public function backfillQuestionsLessonId($sessionId = null, $strategy = 'round_robin')
    {
        $out = array(
            'updated' => 0,
            'skipped_no_lesson' => 0,
            'message' => '',
        );
        if (!$this->db->field_exists('lesson_id', 'questions') || !$this->db->table_exists('digital_diary_lesson')) {
            $out['message'] = 'questions.lesson_id or digital_diary_lesson is missing.';
            return $out;
        }
        if ($sessionId === null || $sessionId === '') {
            $sessionId = get_session_id();
        }
        $sessionId = (int) $sessionId;

        $this->db->select('id, branch_id, class_id, section_id, subject_id');
        $this->db->from('questions');
        $this->db->group_start();
        $this->db->where('lesson_id IS NULL', null, false);
        $this->db->or_where('lesson_id', 0);
        $this->db->group_end();
        $this->db->order_by('id', 'ASC');
        $rows = $this->db->get()->result_array();
        if (empty($rows)) {
            $out['message'] = 'No questions with empty lesson_id.';
            return $out;
        }

        $lessonCache = array();
        $counter = array();
        $strategy = $strategy === 'first' ? 'first' : 'round_robin';

        foreach ($rows as $q) {
            $b = (int) $q['branch_id'];
            $c = (int) $q['class_id'];
            $s = (int) $q['section_id'];
            $sub = (int) $q['subject_id'];
            $key = $b . ':' . $c . ':' . $s . ':' . $sub;
            if (!isset($lessonCache[$key])) {
                $lessonCache[$key] = $this->fetchLessonsForBackfill($b, $c, $s, $sub, $sessionId);
            }
            $lessons = $lessonCache[$key];
            if (empty($lessons)) {
                $out['skipped_no_lesson']++;
                continue;
            }
            if ($strategy === 'first') {
                $pick = (int) $lessons[0]['id'];
            } else {
                if (!isset($counter[$key])) {
                    $counter[$key] = 0;
                }
                $i = $counter[$key] % count($lessons);
                $pick = (int) $lessons[$i]['id'];
                $counter[$key]++;
            }
            $this->db->where('id', (int) $q['id']);
            $this->db->update('questions', array('lesson_id' => $pick));
            $out['updated']++;
        }
        $out['message'] = sprintf(
            'Updated %d question(s); %d skipped (no matching lesson for class/section/subject).',
            $out['updated'],
            $out['skipped_no_lesson']
        );
        return $out;
    }

    /**
     * @return array<int,array> rows from digital_diary_lesson ordered by sort_order, id
     */
    private function fetchLessonsForBackfill($branchId, $classId, $sectionId, $subjectId, $sessionId)
    {
        $branchId = (int) $branchId;
        $classId = (int) $classId;
        $sectionId = (int) $sectionId;
        $subjectId = (int) $subjectId;

        $rows = array();
        if ($sessionId > 0) {
            $rows = $this->queryLessonsForBackfillGroup($branchId, $classId, $sectionId, $subjectId, $sessionId);
        }
        if (!empty($rows)) {
            return $rows;
        }

        $this->db->select_max('session_id', 'max_sid');
        $this->db->where('branch_id', $branchId);
        $this->db->where('class_id', $classId);
        $this->db->where('section_id', $sectionId);
        $this->db->where('subject_id', $subjectId);
        $mx = $this->db->get('digital_diary_lesson')->row();
        $maxSid = $mx && isset($mx->max_sid) && $mx->max_sid !== null ? (int) $mx->max_sid : 0;
        if ($maxSid < 1) {
            return array();
        }
        return $this->queryLessonsForBackfillGroup($branchId, $classId, $sectionId, $subjectId, $maxSid);
    }

    /**
     * @param int $sessionFilter academic session_id to match
     * @return array
     */
    private function queryLessonsForBackfillGroup($branchId, $classId, $sectionId, $subjectId, $sessionFilter)
    {
        $this->db->from('digital_diary_lesson');
        $this->db->where('branch_id', (int) $branchId);
        $this->db->where('class_id', (int) $classId);
        $this->db->where('section_id', (int) $sectionId);
        $this->db->where('subject_id', (int) $subjectId);
        $this->db->where('session_id', (int) $sessionFilter);
        if ($this->db->field_exists('sort_order', 'digital_diary_lesson')) {
            $this->db->order_by('sort_order', 'ASC');
        }
        $this->db->order_by('id', 'ASC');
        return $this->db->get()->result_array();
    }

    public function saveImageRecord($row)
    {
        if (!$this->db->table_exists('onlineexam_image_database')) {
            return false;
        }
        $this->db->insert('onlineexam_image_database', $row);
        $id = (int) $this->db->insert_id();
        return $id > 0 ? $id : false;
    }

    public function getImageById($id, $branchId = 0)
    {
        $id = (int) $id;
        $branchId = (int) $branchId;
        if ($id < 1 || !$this->db->table_exists('onlineexam_image_database')) {
            return null;
        }
        $this->db->where('id', $id);
        if ($branchId > 0) {
            $this->db->where('branch_id', $branchId);
        }
        $row = $this->db->get('onlineexam_image_database')->row_array();
        return is_array($row) ? $row : null;
    }

    public function getImageList($branchId, $search = '', $limit = 24, $offset = 0)
    {
        $branchId = (int) $branchId;
        $limit = (int) $limit;
        $offset = (int) $offset;
        if ($branchId < 1 || !$this->db->table_exists('onlineexam_image_database')) {
            return array();
        }
        if ($limit < 1) {
            $limit = 24;
        }
        if ($offset < 0) {
            $offset = 0;
        }
        $this->db->select('id,branch_id,image_name,original_name,file_name,file_path,file_url,mime_type,file_size,created_by,created_at');
        $this->db->from('onlineexam_image_database');
        $this->db->where('branch_id', $branchId);
        $search = trim((string) $search);
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('image_name', $search);
            $this->db->or_like('original_name', $search);
            $this->db->or_like('file_name', $search);
            $this->db->group_end();
        }
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result_array();
    }

    public function countImageList($branchId, $search = '')
    {
        $branchId = (int) $branchId;
        if ($branchId < 1 || !$this->db->table_exists('onlineexam_image_database')) {
            return 0;
        }
        $this->db->from('onlineexam_image_database');
        $this->db->where('branch_id', $branchId);
        $search = trim((string) $search);
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('image_name', $search);
            $this->db->or_like('original_name', $search);
            $this->db->or_like('file_name', $search);
            $this->db->group_end();
        }
        return (int) $this->db->count_all_results();
    }

    public function deleteImageById($id, $branchId = 0)
    {
        $id = (int) $id;
        $branchId = (int) $branchId;
        if ($id < 1 || !$this->db->table_exists('onlineexam_image_database')) {
            return false;
        }
        $this->db->where('id', $id);
        if ($branchId > 0) {
            $this->db->where('branch_id', $branchId);
        }
        $this->db->delete('onlineexam_image_database');
        return $this->db->affected_rows() > 0;
    }
}