<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Digital_diary_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * DataTables list for digital diary entries
     */
    public function getDiaryDT_list()
    {
        $classID = $this->input->post('class_id');
        $sectionID = $this->input->post('section_id');
        $dateFilter = $this->input->post('date');
        $branchID = $this->application_model->get_branch_id();

        $this->datatables->select('digital_diary.*, class.name as class_name, section.name as section_name, staff.name as creator_name, CONCAT_WS(" ", student.first_name, student.last_name) as student_name');
        $this->datatables->from('digital_diary');
        $this->datatables->join('class', 'class.id = digital_diary.class_id', 'left');
        $this->datatables->join('section', 'section.id = digital_diary.section_id', 'left');
        $this->datatables->join('staff', 'staff.id = digital_diary.created_by', 'left');
        $this->datatables->join('student', 'student.id = digital_diary.student_id', 'left');

        if (!empty($classID)) {
            $this->datatables->where('digital_diary.class_id', $classID);
        }
        if (!empty($sectionID)) {
            $this->datatables->where('digital_diary.section_id', $sectionID);
        }
        if (!empty($dateFilter)) {
            $this->datatables->where('digital_diary.entry_date', $dateFilter);
        }
        $this->datatables->where('digital_diary.branch_id', $branchID);
        $this->datatables->where('digital_diary.session_id', get_session_id());

        $this->datatables->search_value('digital_diary.title, digital_diary.description, class.name, section.name, student.first_name, student.last_name, staff.name');
        $this->datatables->column_order('digital_diary.id, digital_diary.entry_date, class.name, section.name, student_name, digital_diary.title, digital_diary.status, creator_name');
        $this->datatables->order_by('digital_diary.id', 'desc');

        return $this->datatables->generate();
    }

    /**
     * Save or update diary entry
     */
    public function save($data)
    {
        $branchID = !empty($data['branch_id']) ? $data['branch_id'] : $this->application_model->get_branch_id();
        $sessionId = get_session_id();
        if (empty($sessionId)) {
            $row = $this->db->select('id')->order_by('id', 'desc')->limit(1)->get('session')->row();
            $sessionId = $row ? $row->id : 0;
        }
        $entryDate = isset($data['entry_date']) ? $data['entry_date'] : date('Y-m-d');
        $array = array(
            'branch_id' => $branchID,
            'class_id' => (int) $data['class_id'],
            'section_id' => (int) $data['section_id'],
            'student_id' => !empty($data['student_id']) ? (int) $data['student_id'] : null,
            'session_id' => (int) $sessionId,
            'entry_date' => date('Y-m-d', strtotime($entryDate)),
            'title' => isset($data['entry_title']) ? $data['entry_title'] : '',
            'description' => isset($data['description']) ? $data['description'] : '',
            'status' => isset($data['status']) ? (int) $data['status'] : 1,
            'created_by' => get_loggedin_user_id(),
        );

        if (isset($data['diary_id']) && !empty($data['diary_id'])) {
            $this->db->where('id', $data['diary_id']);
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->update('digital_diary', $array);
        } else {
            $this->db->insert('digital_diary', $array);
        }
    }

    /**
     * DataTables list for lessons
     */
    public function getLessonDT_list()
    {
        $classID = $this->input->post('class_id');
        $sectionID = $this->input->post('section_id');
        $subjectID = $this->input->post('subject_id');
        $branchID = $this->application_model->get_branch_id();
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }

        $this->datatables->select('digital_diary_lesson.*, class.name as class_name, section.name as section_name, subject.name as subject_name');
        $this->datatables->from('digital_diary_lesson');
        $this->datatables->join('class', 'class.id = digital_diary_lesson.class_id', 'left');
        $this->datatables->join('section', 'section.id = digital_diary_lesson.section_id', 'left');
        $this->datatables->join('subject', 'subject.id = digital_diary_lesson.subject_id', 'left');

        if (!empty($classID)) {
            $this->datatables->where('digital_diary_lesson.class_id', $classID);
        }
        if (!empty($sectionID)) {
            $this->datatables->where('digital_diary_lesson.section_id', $sectionID);
        }
        if (!empty($subjectID)) {
            $this->datatables->where('digital_diary_lesson.subject_id', $subjectID);
        }
        // Only filter by branch when we have one (otherwise show all lessons)
        if (!empty($branchID)) {
            $this->datatables->where('digital_diary_lesson.branch_id', $branchID);
        }

        $this->datatables->search_value('digital_diary_lesson.lesson_no, digital_diary_lesson.lesson_name, class.name, section.name, subject.name');
        $this->datatables->column_order('digital_diary_lesson.id, class.name, section.name, subject.name, digital_diary_lesson.lesson_no, digital_diary_lesson.lesson_name');
        $this->datatables->order_by('digital_diary_lesson.id', 'desc');

        return $this->datatables->generate();
    }

    /**
     * Save or update lesson
     */
    public function save_lesson($data)
    {
        $branchID = !empty($data['branch_id']) ? $data['branch_id'] : $this->application_model->get_branch_id();
        $sessionId = get_session_id();
        if (empty($sessionId)) {
            $row = $this->db->select('id')->order_by('id', 'desc')->limit(1)->get('session')->row();
            $sessionId = $row ? $row->id : 0;
        }
        $array = array(
            'branch_id' => $branchID,
            'session_id' => (int) $sessionId,
            'class_id' => (int) $data['class_id'],
            'section_id' => (int) $data['section_id'],
            'subject_id' => (int) $data['subject_id'],
            'lesson_no' => $data['lesson_no'],
            'lesson_name' => $data['lesson_name'],
            'created_by' => get_loggedin_user_id(),
        );

        if (isset($data['lesson_id']) && !empty($data['lesson_id'])) {
            $this->db->where('id', $data['lesson_id']);
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->update('digital_diary_lesson', $array);
        } else {
            if ($this->db->field_exists('sort_order', 'digital_diary_lesson')) {
                $array['sort_order'] = $this->getNextSortOrder($data['class_id'], $data['section_id'], $data['subject_id'], $branchID);
            }
            $this->db->insert('digital_diary_lesson', $array);
        }
    }

    /**
     * Check whether a lesson field value (lesson_no / lesson_name) already exists
     * for the same branch + class + section + subject.
     *
     * @return bool
     */
    public function lessonFieldExists($field, $value, $classId, $sectionId, $subjectId, $branchId, $excludeId = 0)
    {
        if (!in_array($field, array('lesson_no', 'lesson_name'), true)) {
            return false;
        }
        $this->db->where('class_id', (int) $classId);
        $this->db->where('section_id', (int) $sectionId);
        $this->db->where('subject_id', (int) $subjectId);
        if (!empty($branchId)) {
            $this->db->where('branch_id', (int) $branchId);
        }
        $this->db->where('TRIM(' . $field . ') =', trim((string) $value));
        if ((int) $excludeId > 0) {
            $this->db->where('id !=', (int) $excludeId);
        }
        return $this->db->count_all_results('digital_diary_lesson') > 0;
    }

    /**
     * Page 1: Summary by class & section - for lesson index
     * Returns rows: class_id, section_id, class_name, section_name, subject_group (subject_type), subjects (names), subject_count
     */
    public function getLessonSummaryByBranch($branchID, $classID = 0, $sectionID = 0)
    {
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }
        $classID = (int) $classID;
        $sectionID = (int) $sectionID;
        $this->db->select('l.class_id, l.section_id, c.name as class_name, s.name as section_name,
            GROUP_CONCAT(DISTINCT subj.subject_type ORDER BY subj.subject_type SEPARATOR ", ") as subject_group,
            GROUP_CONCAT(DISTINCT subj.name ORDER BY subj.name SEPARATOR ", ") as subjects,
            COUNT(DISTINCT l.subject_id) as subject_count');
        $this->db->from('digital_diary_lesson l');
        $this->db->join('class c', 'c.id = l.class_id', 'left');
        $this->db->join('section s', 's.id = l.section_id', 'left');
        $this->db->join('subject subj', 'subj.id = l.subject_id', 'left');
        $this->db->where('l.branch_id', $branchID);
        if ($classID > 0) {
            $this->db->where('l.class_id', $classID);
        }
        if ($sectionID > 0) {
            $this->db->where('l.section_id', $sectionID);
        }
        $this->db->group_by(array('l.class_id', 'l.section_id'));
        $this->db->order_by('c.name, s.name');
        return $this->db->get()->result_array();
    }

    /**
     * Nested lesson flow for expandable UI: class/section → subjects → lessons
     *
     * @return array[] each: class_id, section_id, class_name, section_name, subjects[]
     */
    public function getLessonFlowByBranch($branchID)
    {
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }
        $this->db->select('l.id, l.class_id, l.section_id, l.subject_id, l.lesson_no, l.lesson_name,
            c.name as class_name, s.name as section_name, subj.name as subject_name, subj.subject_type as subject_group');
        $this->db->from('digital_diary_lesson l');
        $this->db->join('class c', 'c.id = l.class_id', 'left');
        $this->db->join('section s', 's.id = l.section_id', 'left');
        $this->db->join('subject subj', 'subj.id = l.subject_id', 'left');
        $this->db->where('l.branch_id', $branchID);
        $this->db->order_by('c.name', 'ASC');
        $this->db->order_by('s.name', 'ASC');
        $this->db->order_by('subj.name', 'ASC');
        if ($this->db->field_exists('sort_order', 'digital_diary_lesson')) {
            $this->db->order_by('l.sort_order', 'ASC');
        }
        $this->db->order_by('l.id', 'ASC');
        $rows = $this->db->get()->result_array();

        $flow = array();
        foreach ($rows as $row) {
            $csKey = $row['class_id'] . '_' . $row['section_id'];
            if (!isset($flow[$csKey])) {
                $flow[$csKey] = array(
                    'class_id' => (int) $row['class_id'],
                    'section_id' => (int) $row['section_id'],
                    'class_name' => $row['class_name'],
                    'section_name' => $row['section_name'],
                    'subjects' => array(),
                );
            }
            $subjKey = (int) $row['subject_id'];
            if (!isset($flow[$csKey]['subjects'][$subjKey])) {
                $flow[$csKey]['subjects'][$subjKey] = array(
                    'subject_id' => $subjKey,
                    'subject_name' => $row['subject_name'],
                    'subject_group' => $row['subject_group'],
                    'lessons' => array(),
                );
            }
            $flow[$csKey]['subjects'][$subjKey]['lessons'][] = array(
                'id' => (int) $row['id'],
                'lesson_no' => $row['lesson_no'],
                'lesson_name' => $row['lesson_name'],
                'subject_name' => $row['subject_name'],
            );
        }

        // Re-index subjects as lists for the view
        foreach ($flow as &$cs) {
            $cs['subjects'] = array_values($cs['subjects']);
        }
        unset($cs);
        return array_values($flow);
    }

    /**
     * All completed lesson IDs for a branch in the current session.
     *
     * @return int[]
     */
    public function getCompletedLessonIdsByBranch($branchID)
    {
        if (!$this->db->table_exists('digital_diary_lesson_completion') || empty($branchID)) {
            return array();
        }
        $this->db->select('lesson_id');
        $this->db->from('digital_diary_lesson_completion');
        $this->db->where('branch_id', (int) $branchID);
        $this->db->where('session_id', (int) get_session_id());
        $rows = $this->db->get()->result_array();
        $out = array();
        foreach ($rows as $r) {
            $out[] = (int) $r['lesson_id'];
        }
        return $out;
    }

    /**
     * Page 2: For a class+section, list each subject (from subject_assign) with total lesson count
     * Shows all subjects assigned to this class/section, with 0 if no lessons yet
     * Uses class/section/branch; session not required so data shows even if session differs in subject_assign
     */
    public function getLessonSummaryByClassSection($classID, $sectionID, $branchID)
    {
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }
        $classID = (int) $classID;
        $sectionID = (int) $sectionID;

        $subquery = "SELECT subject_id, COUNT(id) as cnt FROM digital_diary_lesson 
            WHERE class_id = " . $this->db->escape($classID) . " 
            AND section_id = " . $this->db->escape($sectionID);
        if (!empty($branchID)) {
            $subquery .= " AND branch_id = " . $this->db->escape($branchID);
        }
        $subquery .= " GROUP BY subject_id";

        $this->db->select('sa.class_id, sa.section_id, sa.subject_id, c.name as class_name, s.name as section_name,
            subj.name as subject_name, subj.subject_type as subject_group, COALESCE(lc.cnt, 0) as total_lesson');
        $this->db->from('subject_assign sa');
        $this->db->join('class c', 'c.id = sa.class_id', 'left');
        $this->db->join('section s', 's.id = sa.section_id', 'left');
        $this->db->join('subject subj', 'subj.id = sa.subject_id', 'left');
        $this->db->join('(' . $subquery . ') lc', 'lc.subject_id = sa.subject_id', 'left');
        $this->db->where('sa.class_id', $classID);
        $this->db->where('sa.section_id', $sectionID);
        if (!empty($branchID)) {
            $this->db->where('sa.branch_id', $branchID);
        }
        $this->db->group_by(array('sa.class_id', 'sa.section_id', 'sa.subject_id'));
        $this->db->order_by('subj.name');
        return $this->db->get()->result_array();
    }

    /**
     * Page 3: All lessons for a class, section, subject
     * Only filters by branch when branchID is set (so data shows for superadmin or when branch is empty)
     */
    public function getLessonsBySubject($classID, $sectionID, $subjectID, $branchID)
    {
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }
        $this->db->select('l.*, subj.name as subject_name');
        $this->db->from('digital_diary_lesson l');
        $this->db->join('subject subj', 'subj.id = l.subject_id', 'left');
        $this->db->where('l.class_id', (int) $classID);
        $this->db->where('l.section_id', (int) $sectionID);
        $this->db->where('l.subject_id', (int) $subjectID);
        if (!empty($branchID)) {
            $this->db->where('l.branch_id', $branchID);
        }
        if ($this->db->field_exists('sort_order', 'digital_diary_lesson')) {
            $this->db->order_by('l.sort_order', 'ASC');
        }
        $this->db->order_by('l.id', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Get next sort_order for a new lesson in this class/section/subject
     */
    public function getNextSortOrder($classID, $sectionID, $subjectID, $branchID)
    {
        $this->db->select_max('sort_order');
        $this->db->where('class_id', (int) $classID);
        $this->db->where('section_id', (int) $sectionID);
        $this->db->where('subject_id', (int) $subjectID);
        if (!empty($branchID)) {
            $this->db->where('branch_id', $branchID);
        }
        $row = $this->db->get('digital_diary_lesson')->row();
        return $row && $row->sort_order !== null ? (int) $row->sort_order + 1 : 0;
    }

    /**
     * Move lesson up or down in sequence (swap sort_order with neighbour)
     */
    public function lesson_move($lessonID, $direction)
    {
        if (!$this->db->field_exists('sort_order', 'digital_diary_lesson')) {
            return false;
        }
        $lesson = $this->app_lib->getTable('digital_diary_lesson', array('t.id' => $lessonID), true);
        if (empty($lesson)) {
            return false;
        }
        $classID = (int) $lesson['class_id'];
        $sectionID = (int) $lesson['section_id'];
        $subjectID = (int) $lesson['subject_id'];
        $branchID = !empty($lesson['branch_id']) ? $lesson['branch_id'] : null;

        $this->db->select('id, sort_order');
        $this->db->from('digital_diary_lesson');
        $this->db->where('class_id', $classID);
        $this->db->where('section_id', $sectionID);
        $this->db->where('subject_id', $subjectID);
        if (!empty($branchID)) {
            $this->db->where('branch_id', $branchID);
        }
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('id', 'ASC');
        $rows = $this->db->get()->result_array();
        $index = -1;
        foreach ($rows as $i => $r) {
            if ((int) $r['id'] === (int) $lessonID) {
                $index = $i;
                break;
            }
        }
        if ($index < 0) {
            return false;
        }
        $swapIdx = -1;
        if ($direction === 'up' && $index > 0) {
            $swapIdx = $index - 1;
        } elseif ($direction === 'down' && $index < count($rows) - 1) {
            $swapIdx = $index + 1;
        }
        if ($swapIdx < 0) {
            return false;
        }

        // Swap positions in the array, then renumber all (0,1,2,...) so order is always correct
        $tmp = $rows[$index];
        $rows[$index] = $rows[$swapIdx];
        $rows[$swapIdx] = $tmp;

        foreach ($rows as $i => $r) {
            $this->db->where('id', $r['id'])->update('digital_diary_lesson', array('sort_order' => $i));
        }
        return true;
    }

    /**
     * DataTables list for Note Book Type (digital_diary_notebook_type)
     */
    public function getNotebookTypeDT_list()
    {
        $branchID = $this->input->post('branch_id');
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }

        $this->datatables->select('t.id, t.branch_id, t.name, t.code, t.description, t.sort_order, t.is_active, t.created_at');
        $this->datatables->from('digital_diary_notebook_type t');
        if (!empty($branchID)) {
            $this->datatables->where('t.branch_id', $branchID);
        }
        $this->datatables->search_value('t.name, t.code, t.description');
        $this->datatables->column_order('t.id, t.name, t.code, t.sort_order, t.is_active');
        $this->datatables->order_by('t.sort_order', 'ASC');
        $this->datatables->order_by('t.id', 'ASC');
        return $this->datatables->generate();
    }

    /**
     * Save or update Note Book Type
     */
    public function save_notebook_type($data)
    {
        $branchID = !empty($data['branch_id']) ? $data['branch_id'] : $this->application_model->get_branch_id();
        $array = array(
            'branch_id' => (int) $branchID,
            'name' => isset($data['name']) ? $data['name'] : '',
            'code' => isset($data['code']) ? $data['code'] : null,
            'description' => isset($data['description']) ? $data['description'] : null,
            'sort_order' => isset($data['sort_order']) ? (int) $data['sort_order'] : 0,
            'is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
        );
        if (isset($data['notebook_type_id']) && !empty($data['notebook_type_id'])) {
            $this->db->where('id', $data['notebook_type_id']);
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->update('digital_diary_notebook_type', $array);
        } else {
            $this->db->insert('digital_diary_notebook_type', $array);
        }
    }

    /**
     * DataTables list for Teaching Activity (digital_diary_teaching_activity)
     */
    public function getTeachingActivityDT_list()
    {
        $branchID = $this->input->post('branch_id');
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }

        $select = 't.id, t.branch_id, t.name, t.code, t.description, t.sort_order, t.is_active, t.created_at';
        if ($this->db->field_exists('notebook_type_ids', 'digital_diary_teaching_activity')) {
            $select .= ', t.notebook_type_ids';
        }
        if ($this->db->field_exists('notebook_type_id', 'digital_diary_teaching_activity')) {
            $select .= ', nb.name as notebook_type_name';
        }
        $this->datatables->select($select);
        $this->datatables->from('digital_diary_teaching_activity t');
        if ($this->db->field_exists('notebook_type_id', 'digital_diary_teaching_activity')) {
            $this->datatables->join('digital_diary_notebook_type nb', 'nb.id = t.notebook_type_id', 'left');
        }
        if (!empty($branchID)) {
            $this->datatables->where('t.branch_id', $branchID);
        }
        $searchCols = 't.name, t.code, t.description';
        $orderCols = 't.id, t.name, t.code';
        if ($this->db->field_exists('notebook_type_id', 'digital_diary_teaching_activity')) {
            $searchCols .= ', nb.name';
            $orderCols .= ', nb.name';
        }
        $orderCols .= ', t.description, t.sort_order, t.is_active';
        $this->datatables->search_value($searchCols);
        $this->datatables->column_order($orderCols);
        $this->datatables->order_by('t.sort_order', 'ASC');
        $this->datatables->order_by('t.id', 'ASC');
        return $this->datatables->generate();
    }

    /**
     * Get notebook types for dropdown by branch (for Teaching Activity form)
     */
    public function getNotebookTypesByBranch($branchID)
    {
        if (empty($branchID) || !$this->db->table_exists('digital_diary_notebook_type')) {
            return array();
        }
        $this->db->select('id, name');
        $this->db->from('digital_diary_notebook_type');
        $this->db->where('branch_id', (int) $branchID);
        $this->db->where('is_active', 1);
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('name', 'ASC');
        $rows = $this->db->get()->result_array();
        $out = array('' => translate('select'));
        foreach ($rows as $r) {
            $out[$r['id']] = $r['name'];
        }
        return $out;
    }

    /**
     * Notebook types for multiselect (no placeholder row)
     */
    public function getNotebookTypesByBranchMultiselect($branchID)
    {
        if (empty($branchID) || !$this->db->table_exists('digital_diary_notebook_type')) {
            return array();
        }
        $this->db->select('id, name');
        $this->db->from('digital_diary_notebook_type');
        $this->db->where('branch_id', (int) $branchID);
        $this->db->where('is_active', 1);
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('name', 'ASC');
        $rows = $this->db->get()->result_array();
        $out = array();
        foreach ($rows as $r) {
            $out[$r['id']] = $r['name'];
        }
        return $out;
    }

    /**
     * Resolve notebook type names in submission order (for display)
     *
     * @param int[] $ids
     * @return string[]
     */
    public function getNotebookTypeNamesByIdsOrdered($ids)
    {
        if (empty($ids) || !$this->db->table_exists('digital_diary_notebook_type')) {
            return array();
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            return array();
        }
        $this->db->select('id, name');
        $this->db->from('digital_diary_notebook_type');
        $this->db->where_in('id', $ids);
        $rows = $this->db->get()->result_array();
        $byId = array();
        foreach ($rows as $r) {
            $byId[(int) $r['id']] = $r['name'];
        }
        $ordered = array();
        foreach ($ids as $id) {
            if (isset($byId[$id])) {
                $ordered[] = $byId[$id];
            }
        }
        return $ordered;
    }

    /**
     * Filter notebook type IDs to those that belong to the branch (preserves order)
     *
     * @param int   $branchID
     * @param mixed $raw      array or scalar from POST
     * @return int[]
     */
    public function filterNotebookTypeIdsForBranch($branchID, $raw)
    {
        if (empty($branchID) || !$this->db->table_exists('digital_diary_notebook_type')) {
            return array();
        }
        if (!is_array($raw)) {
            $raw = ($raw === null || $raw === '') ? array() : array($raw);
        }
        $order = array();
        foreach ($raw as $v) {
            $id = (int) $v;
            if ($id > 0 && !in_array($id, $order, true)) {
                $order[] = $id;
            }
        }
        if (empty($order)) {
            return array();
        }
        $this->db->select('id');
        $this->db->from('digital_diary_notebook_type');
        $this->db->where('branch_id', (int) $branchID);
        $this->db->where_in('id', $order);
        $validRows = $this->db->get()->result_array();
        $valid = array();
        foreach ($validRows as $r) {
            $valid[(int) $r['id']] = true;
        }
        $out = array();
        foreach ($order as $id) {
            if (isset($valid[$id])) {
                $out[] = $id;
            }
        }
        return $out;
    }

    /**
     * Save or update Teaching Activity
     */
    public function save_teaching_activity($data)
    {
        $branchID = !empty($data['branch_id']) ? $data['branch_id'] : $this->application_model->get_branch_id();
        $array = array(
            'branch_id' => (int) $branchID,
            'name' => isset($data['name']) ? $data['name'] : '',
            'code' => isset($data['code']) ? $data['code'] : null,
            'description' => isset($data['description']) ? $data['description'] : null,
        );
        if ($this->db->field_exists('notebook_type_ids', 'digital_diary_teaching_activity')) {
            $rawNb = isset($data['notebook_type_ids']) ? $data['notebook_type_ids'] : null;
            $filtered = $this->filterNotebookTypeIdsForBranch((int) $branchID, $rawNb);
            $array['notebook_type_ids'] = !empty($filtered) ? implode(',', $filtered) : null;
            if ($this->db->field_exists('notebook_type_id', 'digital_diary_teaching_activity')) {
                $array['notebook_type_id'] = !empty($filtered) ? (int) $filtered[0] : null;
            }
        } elseif ($this->db->field_exists('notebook_type_id', 'digital_diary_teaching_activity')) {
            $array['notebook_type_id'] = null;
            if (isset($data['notebook_type_ids'])) {
                $filtered = $this->filterNotebookTypeIdsForBranch((int) $branchID, $data['notebook_type_ids']);
                if (!empty($filtered)) {
                    $array['notebook_type_id'] = (int) $filtered[0];
                }
            } elseif (!empty($data['notebook_type_id'])) {
                $array['notebook_type_id'] = (int) $data['notebook_type_id'];
            }
        }
        $isUpdate = isset($data['teaching_activity_id']) && !empty($data['teaching_activity_id']);
        if (!$isUpdate) {
            $array['sort_order'] = 0;
            $array['is_active'] = 1;
        }
        if ($isUpdate) {
            $this->db->where('id', $data['teaching_activity_id']);
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->update('digital_diary_teaching_activity', $array);
        } else {
            $this->db->insert('digital_diary_teaching_activity', $array);
        }
    }

    /**
     * Master teaching activities for branch (dropdown)
     */
    public function getActiveTeachingActivitiesForBranch($branchID)
    {
        if (empty($branchID) || !$this->db->table_exists('digital_diary_teaching_activity')) {
            return array();
        }
        $this->db->select('id, name, code');
        $this->db->from('digital_diary_teaching_activity');
        $this->db->where('branch_id', (int) $branchID);
        $this->db->where('is_active', 1);
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('name', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Get Teaching Activity logs for a specific date
     * Used by admin `digital_diary/today_work` page.
     *
     * @param string $activityDate Y-m-d
     * @param mixed  $branchID     branch id
     * @return array
     */
    public function getTeachingActivityLogByDate($activityDate, $branchID = '')
    {
        if (empty($activityDate)) {
            $activityDate = date('Y-m-d');
        }

        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }

        if (
            !$this->db->table_exists('digital_diary_teacher_activity_log') ||
            !$this->db->table_exists('digital_diary_teaching_activity')
        ) {
            return array();
        }

        $sessionID = get_session_id();

        $this->db->select('l.id, l.activity_date, l.remarks, l.staff_id, l.class_id, l.section_id, l.subject_id');
        $this->db->select('ta.name as activity_name');
        $this->db->select('st.name as staff_name');
        $this->db->select('c.name as class_name, se.name as section_name, sub.name as subject_name');
        $this->db->from('digital_diary_teacher_activity_log l');
        $this->db->join('digital_diary_teaching_activity ta', 'ta.id = l.teaching_activity_id', 'inner');
        $this->db->join('staff st', 'st.id = l.staff_id', 'left');
        $this->db->join('class c', 'c.id = l.class_id', 'left');
        $this->db->join('section se', 'se.id = l.section_id', 'left');
        $this->db->join('subject sub', 'sub.id = l.subject_id', 'left');
        $this->db->where('l.branch_id', (int) $branchID);
        $this->db->where('l.session_id', (int) $sessionID);
        $this->db->where('l.activity_date', $activityDate);
        $this->db->order_by('l.id', 'DESC');

        return $this->db->get()->result_array();
    }

    /**
     * Today's work report map by subject for one class/section/date.
     * Returns keyed array: subject_id => [activity_name, remarks, activity_count]
     *
     * @param string $activityDate Y-m-d
     * @param mixed  $branchID
     * @param int    $classID
     * @param int    $sectionID
     * @param int    $subjectID
     * @return array
     */
    public function getTodayTeachingActivityMapBySubject($activityDate, $branchID = '', $classID = 0, $sectionID = 0, $subjectID = 0)
    {
        if (empty($activityDate)) {
            $activityDate = date('Y-m-d');
        }
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }
        if (empty($classID) || empty($sectionID)) {
            return array();
        }
        if (
            !$this->db->table_exists('digital_diary_teacher_activity_log') ||
            !$this->db->table_exists('digital_diary_teaching_activity')
        ) {
            return array();
        }

        $sessionID = get_session_id();

        $this->db->select('l.subject_id, MAX(l.id) as latest_log_id, COUNT(l.id) as activity_count');
        $this->db->from('digital_diary_teacher_activity_log l');
        $this->db->where('l.branch_id', (int) $branchID);
        $this->db->where('l.session_id', (int) $sessionID);
        $this->db->where('l.activity_date', $activityDate);
        $this->db->where('l.class_id', (int) $classID);
        $this->db->where('l.section_id', (int) $sectionID);
        $this->db->where('l.subject_id IS NOT NULL', null, false);
        if (!empty($subjectID)) {
            $this->db->where('l.subject_id', (int) $subjectID);
        }
        $this->db->group_by('l.subject_id');
        $summaryRows = $this->db->get()->result_array();
        if (empty($summaryRows)) {
            return array();
        }

        $logIDs = array();
        $countMap = array();
        foreach ($summaryRows as $sr) {
            $sid = (int) $sr['subject_id'];
            if (!empty($sr['latest_log_id'])) {
                $logIDs[] = (int) $sr['latest_log_id'];
            }
            $countMap[$sid] = (int) $sr['activity_count'];
        }
        if (empty($logIDs)) {
            return array();
        }

        $this->db->select('l.id, l.subject_id, l.remarks, ta.name as activity_name');
        $this->db->from('digital_diary_teacher_activity_log l');
        $this->db->join('digital_diary_teaching_activity ta', 'ta.id = l.teaching_activity_id', 'inner');
        $this->db->where_in('l.id', $logIDs);
        $detailRows = $this->db->get()->result_array();

        $out = array();
        foreach ($detailRows as $dr) {
            $sid = (int) $dr['subject_id'];
            $out[$sid] = array(
                'activity_name' => isset($dr['activity_name']) ? $dr['activity_name'] : '',
                'remarks' => isset($dr['remarks']) ? $dr['remarks'] : '',
                'activity_count' => isset($countMap[$sid]) ? (int) $countMap[$sid] : 0,
            );
        }
        return $out;
    }

    /**
     * Distinct subject_type values for branch (Subject Group filter on Today's Work).
     *
     * @param mixed $branchID
     * @return array<string,string> value => label
     */
    public function getSubjectGroupOptionsByBranch($branchID)
    {
        $opts = array('' => translate('all'));
        if (empty($branchID) || !$this->db->table_exists('subject')) {
            return $opts;
        }
        $this->db->distinct();
        $this->db->select('subject_type');
        $this->db->from('subject');
        $this->db->where('branch_id', (int) $branchID);
        $this->db->where('subject_type IS NOT NULL', null, false);
        $this->db->where('subject_type !=', '');
        $this->db->order_by('subject_type', 'ASC');
        $rows = $this->db->get()->result_array();
        foreach ($rows as $r) {
            $t = isset($r['subject_type']) ? trim((string) $r['subject_type']) : '';
            if ($t !== '') {
                $opts[$t] = $t;
            }
        }
        return $opts;
    }

    /**
     * Legacy labels when branch has no rows in digital_diary_teaching_activity (Today's Work).
     *
     * @return array
     */
    public function getTodayWorkTypeLabels()
    {
        return array(
            'reading' => 'Reading',
            'explanation' => 'Explanation',
            'word_meaning' => 'Word Meaning',
            'fair_copy' => 'Fair Copy',
            'rough_copy' => 'Rough Copy',
            'chart_paper' => 'Chart Paper',
        );
    }

    /**
     * @param int $branchID
     * @return bool
     */
    public function branchHasActiveTeachingActivities($branchID)
    {
        return !empty($this->getActiveTeachingActivitiesForBranch($branchID));
    }

    /**
     * Dropdown options: teaching activity master (id => name), or legacy slug keys if none.
     *
     * @param int $branchID
     * @return array<string,string>
     */
    public function getTodayWorkActivityDropdownOptions($branchID)
    {
        $opts = array('' => translate('select'));
        if (!empty($branchID) && $this->branchHasActiveTeachingActivities($branchID)) {
            foreach ($this->getActiveTeachingActivitiesForBranch($branchID) as $a) {
                $opts[(string) $a['id']] = $a['name'];
            }
            return $opts;
        }
        foreach ($this->getTodayWorkTypeLabels() as $k => $lab) {
            $opts[$k] = $lab;
        }
        return $opts;
    }

    /**
     * @param string $csv comma-separated teaching_activity ids or legacy keys
     * @param int|null $branchID for resolving numeric ids to activity names
     * @return string
     */
    public function formatTodayWorkItemsDisplay($csv, $branchID = null)
    {
        if ($csv === null || $csv === '') {
            return '—';
        }
        $labels = $this->getTodayWorkTypeLabels();
        $keys = array_filter(array_map('trim', explode(',', (string) $csv)));
        $parts = array();
        $resolveIds = !empty($branchID) && $this->db->table_exists('digital_diary_teaching_activity');
        foreach ($keys as $k) {
            if ($resolveIds && ctype_digit((string) $k)) {
                $this->db->select('name');
                $this->db->where('id', (int) $k);
                $this->db->where('branch_id', (int) $branchID);
                $row = $this->db->get('digital_diary_teaching_activity', 1)->row();
                if ($row && !empty($row->name)) {
                    $parts[] = $row->name;
                    continue;
                }
            }
            if (isset($labels[$k])) {
                $parts[] = $labels[$k];
            }
        }
        return empty($parts) ? '—' : implode(', ', $parts);
    }

    /**
     * Saved today's work rows keyed by subject_id.
     *
     * @param string $workDate Y-m-d
     * @return array<int,array>
     */
    public function getTodayWorkMapByClassSectionDate($workDate, $branchID, $classID, $sectionID)
    {
        if (
            !$this->db->table_exists('digital_diary_today_work') ||
            empty($workDate) ||
            empty($classID) ||
            empty($sectionID)
        ) {
            return array();
        }
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }
        $sessionID = (int) get_session_id();

        $this->db->select('tw.*, l.lesson_no, l.lesson_name');
        $this->db->from('digital_diary_today_work tw');
        $this->db->join('digital_diary_lesson l', 'l.id = tw.lesson_id', 'left');
        $this->db->where('tw.branch_id', (int) $branchID);
        $this->db->where('tw.session_id', $sessionID);
        $this->db->where('tw.work_date', $workDate);
        $this->db->where('tw.class_id', (int) $classID);
        $this->db->where('tw.section_id', (int) $sectionID);
        $rows = $this->db->get()->result_array();
        $map = array();
        foreach ($rows as $r) {
            $map[(int) $r['subject_id']] = $r;
        }
        return $map;
    }

    /**
     * Syllabus / work percentage from lesson position in ordered syllabus list.
     *
     * @param int $lessonID
     * @return array{syllabus_percentage:string,work_percentage:string}
     */
    public function getLessonSyllabusWorkPercent($lessonID, $classID, $sectionID, $subjectID, $branchID)
    {
        $dash = '—';
        if (empty($lessonID)) {
            return array('syllabus_percentage' => $dash, 'work_percentage' => $dash);
        }
        $lessons = $this->getLessonsBySubject($classID, $sectionID, $subjectID, $branchID);
        $total = count($lessons);
        if ($total === 0) {
            return array('syllabus_percentage' => $dash, 'work_percentage' => $dash);
        }
        $pos = 0;
        foreach ($lessons as $i => $l) {
            if ((int) $l['id'] === (int) $lessonID) {
                $pos = $i + 1;
                break;
            }
        }
        if ($pos === 0) {
            return array('syllabus_percentage' => $dash, 'work_percentage' => $dash);
        }
        $pct = (int) round(($pos / $total) * 100);
        $s = $pct . ' %';
        return array('syllabus_percentage' => $s, 'work_percentage' => $s);
    }

    /**
     * Furthest lesson in syllabus order among selected ids (for progress %).
     *
     * @param string $lessonIdsCsv comma-separated ids
     * @return int|null
     */
    public function pickPrimaryLessonIdFromLessonIds($lessonIdsCsv, $classID, $sectionID, $subjectID, $branchID)
    {
        $ids = array_filter(array_map('intval', explode(',', (string) $lessonIdsCsv)));
        if (empty($ids)) {
            return null;
        }
        $lessons = $this->getLessonsBySubject($classID, $sectionID, $subjectID, $branchID);
        $bestId = null;
        $bestPos = -1;
        foreach ($lessons as $i => $l) {
            $lid = (int) $l['id'];
            if (in_array($lid, $ids, true) && $i > $bestPos) {
                $bestPos = $i;
                $bestId = $lid;
            }
        }
        return $bestId !== null ? $bestId : (int) $ids[0];
    }

    /**
     * Display string for multiple lessons (Now going on).
     *
     * @param string $lessonIdsCsv
     * @return string
     */
    public function formatMultiLessonLabels($lessonIdsCsv, $classID, $sectionID, $subjectID, $branchID)
    {
        $ids = array_filter(array_map('intval', explode(',', (string) $lessonIdsCsv)));
        if (empty($ids)) {
            return '';
        }
        $lessons = $this->getLessonsBySubject($classID, $sectionID, $subjectID, $branchID);
        $byId = array();
        foreach ($lessons as $l) {
            $byId[(int) $l['id']] = $l;
        }
        $parts = array();
        foreach ($ids as $id) {
            if (isset($byId[$id])) {
                $l = $byId[$id];
                $parts[] = 'Lesson - ' . $l['lesson_no'] . ', ' . $l['lesson_name'];
            }
        }
        return empty($parts) ? '' : implode(' · ', $parts);
    }

    /**
     * Lesson IDs marked completed for this class/section/subject in current session.
     *
     * @return int[]
     */
    public function getCompletedLessonIdsForSubject($classID, $sectionID, $subjectID, $branchID, $reportDate = null)
    {
        if (
            !$this->db->table_exists('digital_diary_lesson_completion') ||
            empty($branchID) ||
            empty($classID) ||
            empty($sectionID) ||
            empty($subjectID)
        ) {
            return array();
        }
        $sessionID = (int) get_session_id();
        $this->db->select('lesson_id');
        $this->db->from('digital_diary_lesson_completion');
        $this->db->where('branch_id', (int) $branchID);
        $this->db->where('session_id', $sessionID);
        $this->db->where('class_id', (int) $classID);
        $this->db->where('section_id', (int) $sectionID);
        $this->db->where('subject_id', (int) $subjectID);
        if (!empty($reportDate)) {
            $this->db->where('work_date <=', date('Y-m-d', strtotime($reportDate)));
        }
        $rows = $this->db->get()->result_array();
        $out = array();
        foreach ($rows as $r) {
            $out[] = (int) $r['lesson_id'];
        }
        return $out;
    }

    /**
     * Store completion for lessons (teacher answered Yes). REPLACE on unique key.
     *
     * @param int[] $lessonIds
     * @return bool
     */
    public function saveLessonCompletions($branchID, $classID, $sectionID, $subjectID, $lessonIds, $staffId, $workDate)
    {
        if (!$this->db->table_exists('digital_diary_lesson_completion')) {
            return false;
        }
        $branchID = (int) $branchID;
        $sessionID = (int) get_session_id();
        $staffId = (int) $staffId;
        $workDate = date('Y-m-d', strtotime($workDate));
        $classID = (int) $classID;
        $sectionID = (int) $sectionID;
        $subjectID = (int) $subjectID;
        foreach ($lessonIds as $lid) {
            $lid = (int) $lid;
            if ($lid <= 0) {
                continue;
            }
            $this->db->where('id', $lid);
            $this->db->where('class_id', $classID);
            $this->db->where('section_id', $sectionID);
            $this->db->where('subject_id', $subjectID);
            $this->db->where('branch_id', $branchID);
            if ($this->db->get('digital_diary_lesson')->num_rows() === 0) {
                continue;
            }
            $row = array(
                'branch_id' => $branchID,
                'session_id' => $sessionID,
                'class_id' => $classID,
                'section_id' => $sectionID,
                'subject_id' => $subjectID,
                'lesson_id' => $lid,
                'staff_id' => $staffId,
                'work_date' => $workDate,
                'updated_at' => date('Y-m-d H:i:s'),
            );
            $this->db->replace('digital_diary_lesson_completion', $row);
        }
        return true;
    }

    /**
     * Log teaching activity done for completed lessons (from Today's Work confirmation).
     * Upserts one row per lesson for the same date / class / section / subject when lesson_id column exists.
     *
     * @param array<int,int> $lessonActivityMap lesson_id => teaching_activity_id
     * @return bool
     */
    public function saveLessonTeachingActivities(
        $branchID,
        $classID,
        $sectionID,
        $subjectID,
        $lessonActivityMap,
        $staffId,
        $workDate,
        $remarks = ''
    ) {
        if (
            !$this->db->table_exists('digital_diary_teacher_activity_log') ||
            !$this->db->table_exists('digital_diary_teaching_activity') ||
            empty($lessonActivityMap) ||
            !is_array($lessonActivityMap)
        ) {
            return false;
        }
        $branchID = (int) $branchID;
        $sessionID = (int) get_session_id();
        $staffId = (int) $staffId;
        $workDate = date('Y-m-d', strtotime($workDate));
        $classID = (int) $classID;
        $sectionID = (int) $sectionID;
        $subjectID = (int) $subjectID;
        $remarks = trim((string) $remarks);
        $hasLessonCol = $this->db->field_exists('lesson_id', 'digital_diary_teacher_activity_log');

        foreach ($lessonActivityMap as $lessonId => $activityId) {
            $lessonId = (int) $lessonId;
            $activityId = (int) $activityId;
            if ($lessonId <= 0 || $activityId <= 0) {
                continue;
            }
            $this->db->where('id', $lessonId);
            $this->db->where('class_id', $classID);
            $this->db->where('section_id', $sectionID);
            $this->db->where('subject_id', $subjectID);
            $this->db->where('branch_id', $branchID);
            if ($this->db->get('digital_diary_lesson')->num_rows() === 0) {
                continue;
            }
            $this->db->where('id', $activityId);
            $this->db->where('branch_id', $branchID);
            $this->db->where('is_active', 1);
            if ($this->db->get('digital_diary_teaching_activity', 1)->num_rows() === 0) {
                continue;
            }

            $row = array(
                'branch_id' => $branchID,
                'session_id' => $sessionID,
                'staff_id' => $staffId,
                'teaching_activity_id' => $activityId,
                'activity_date' => $workDate,
                'class_id' => $classID,
                'section_id' => $sectionID,
                'subject_id' => $subjectID,
                'remarks' => $remarks !== '' ? $remarks : null,
            );
            if ($hasLessonCol) {
                $row['lesson_id'] = $lessonId;
                $this->db->where('branch_id', $branchID);
                $this->db->where('session_id', $sessionID);
                $this->db->where('activity_date', $workDate);
                $this->db->where('class_id', $classID);
                $this->db->where('section_id', $sectionID);
                $this->db->where('subject_id', $subjectID);
                $this->db->where('lesson_id', $lessonId);
                $existing = $this->db->get('digital_diary_teacher_activity_log', 1)->row();
                if ($existing) {
                    $this->db->where('id', (int) $existing->id);
                    $this->db->update('digital_diary_teacher_activity_log', $row);
                    continue;
                }
            }
            $this->db->insert('digital_diary_teacher_activity_log', $row);
        }
        return true;
    }

    /**
     * Syllabus / work percentage from completed lesson count vs total lessons in subject order.
     *
     * @return array{syllabus_percentage:string,work_percentage:string}
     */
    public function getLessonSyllabusCompletionPercent($classID, $sectionID, $subjectID, $branchID, $reportDate = null)
    {
        $dash = '—';
        $lessons = $this->getLessonsBySubject($classID, $sectionID, $subjectID, $branchID);
        $total = count($lessons);
        if ($total === 0) {
            return array('syllabus_percentage' => $dash, 'work_percentage' => $dash);
        }
        if (!$this->db->table_exists('digital_diary_lesson_completion')) {
            return array('syllabus_percentage' => $dash, 'work_percentage' => $dash);
        }
        $completedIds = $this->getCompletedLessonIdsForSubject($classID, $sectionID, $subjectID, $branchID, $reportDate);
        if (empty($completedIds)) {
            $pct = 0;
        } else {
            $set = array_flip($completedIds);
            $n = 0;
            foreach ($lessons as $l) {
                if (isset($set[(int) $l['id']])) {
                    $n++;
                }
            }
            $pct = (int) round(($n / $total) * 100);
        }
        $s = $pct . ' %';
        return array('syllabus_percentage' => $s, 'work_percentage' => $s);
    }

    /**
     * Insert or update one subject row for today's work.
     *
     * @return bool
     */
    public function saveTodayWorkEntry($data)
    {
        if (!$this->db->table_exists('digital_diary_today_work')) {
            return false;
        }
        $branchID = (int) $data['branch_id'];
        $sessionID = (int) $data['session_id'];
        $workDate = $data['work_date'];
        $classID = (int) $data['class_id'];
        $sectionID = (int) $data['section_id'];
        $subjectID = (int) $data['subject_id'];
        $staffID = (int) $data['staff_id'];
        $lessonID = !empty($data['lesson_id']) ? (int) $data['lesson_id'] : null;
        $lessonIdsCsv = isset($data['lesson_ids']) ? $data['lesson_ids'] : null;

        $array = array(
            'branch_id' => $branchID,
            'session_id' => $sessionID,
            'staff_id' => $staffID,
            'work_date' => $workDate,
            'class_id' => $classID,
            'section_id' => $sectionID,
            'subject_id' => $subjectID,
            'lesson_id' => $lessonID,
            'class_work_items' => isset($data['class_work_items']) ? $data['class_work_items'] : null,
            'home_work_items' => isset($data['home_work_items']) ? $data['home_work_items'] : null,
            'home_work_note' => isset($data['home_work_note']) ? $data['home_work_note'] : null,
        );
        if ($this->db->field_exists('cw_detail', 'digital_diary_today_work')) {
            $cwDetail = isset($data['cw_detail']) ? trim((string) $data['cw_detail']) : '';
            $array['cw_detail'] = $cwDetail !== '' ? mb_substr($cwDetail, 0, 500) : null;
        }
        if ($this->db->field_exists('hw_detail', 'digital_diary_today_work')) {
            $hwDetail = isset($data['hw_detail']) ? trim((string) $data['hw_detail']) : '';
            $array['hw_detail'] = $hwDetail !== '' ? mb_substr($hwDetail, 0, 500) : null;
        }
        if ($this->db->field_exists('learning_qtype', 'digital_diary_today_work')) {
            $array['learning_qtype'] = !empty($data['learning_qtype']) ? (int) $data['learning_qtype'] : null;
        }
        if ($this->db->field_exists('learning_qcount', 'digital_diary_today_work')) {
            $array['learning_qcount'] = !empty($data['learning_qcount']) ? (int) $data['learning_qcount'] : null;
        }
        if ($this->db->field_exists('cw_attachment', 'digital_diary_today_work') && array_key_exists('cw_attachment', $data)) {
            $array['cw_attachment'] = !empty($data['cw_attachment']) ? $data['cw_attachment'] : null;
        }
        if ($this->db->field_exists('hw_attachment', 'digital_diary_today_work') && array_key_exists('hw_attachment', $data)) {
            $array['hw_attachment'] = !empty($data['hw_attachment']) ? $data['hw_attachment'] : null;
        }
        if ($this->db->field_exists('lesson_ids', 'digital_diary_today_work')) {
            $array['lesson_ids'] = $lessonIdsCsv;
        }
        if ($this->db->field_exists('cw_notebook_type_ids', 'digital_diary_today_work')) {
            $rawCw = isset($data['cw_notebook_type_ids']) ? $data['cw_notebook_type_ids'] : null;
            $cwFiltered = $this->filterNotebookTypeIdsForBranch((int) $branchID, $rawCw);
            $array['cw_notebook_type_ids'] = !empty($cwFiltered) ? implode(',', $cwFiltered) : null;
            if ($this->db->field_exists('cw_notebook_type_id', 'digital_diary_today_work')) {
                $array['cw_notebook_type_id'] = !empty($cwFiltered) ? (int) $cwFiltered[0] : null;
            }
        } elseif ($this->db->field_exists('cw_notebook_type_id', 'digital_diary_today_work')) {
            $array['cw_notebook_type_id'] = null;
            if (isset($data['cw_notebook_type_ids'])) {
                $cwFiltered = $this->filterNotebookTypeIdsForBranch((int) $branchID, $data['cw_notebook_type_ids']);
                if (!empty($cwFiltered)) {
                    $array['cw_notebook_type_id'] = (int) $cwFiltered[0];
                }
            } elseif (!empty($data['cw_notebook_type_id'])) {
                $array['cw_notebook_type_id'] = (int) $data['cw_notebook_type_id'];
            }
        }
        if ($this->db->field_exists('hw_notebook_type_ids', 'digital_diary_today_work')) {
            $rawHw = isset($data['hw_notebook_type_ids']) ? $data['hw_notebook_type_ids'] : null;
            $hwFiltered = $this->filterNotebookTypeIdsForBranch((int) $branchID, $rawHw);
            $array['hw_notebook_type_ids'] = !empty($hwFiltered) ? implode(',', $hwFiltered) : null;
            if ($this->db->field_exists('hw_notebook_type_id', 'digital_diary_today_work')) {
                $array['hw_notebook_type_id'] = !empty($hwFiltered) ? (int) $hwFiltered[0] : null;
            }
        } elseif ($this->db->field_exists('hw_notebook_type_id', 'digital_diary_today_work')) {
            $array['hw_notebook_type_id'] = null;
            if (isset($data['hw_notebook_type_ids'])) {
                $hwFiltered = $this->filterNotebookTypeIdsForBranch((int) $branchID, $data['hw_notebook_type_ids']);
                if (!empty($hwFiltered)) {
                    $array['hw_notebook_type_id'] = (int) $hwFiltered[0];
                }
            } elseif (!empty($data['hw_notebook_type_id'])) {
                $array['hw_notebook_type_id'] = (int) $data['hw_notebook_type_id'];
            }
        }

        $this->db->where('branch_id', $branchID);
        $this->db->where('session_id', $sessionID);
        $this->db->where('work_date', $workDate);
        $this->db->where('class_id', $classID);
        $this->db->where('section_id', $sectionID);
        $this->db->where('subject_id', $subjectID);
        $q = $this->db->get('digital_diary_today_work');
        if ($q->num_rows() > 0) {
            $row = $q->row();
            $update = array(
                'staff_id' => $staffID,
                'lesson_id' => $lessonID,
                'class_work_items' => $array['class_work_items'],
                'home_work_items' => $array['home_work_items'],
                'home_work_note' => $array['home_work_note'],
            );
            if ($this->db->field_exists('lesson_ids', 'digital_diary_today_work')) {
                $update['lesson_ids'] = $lessonIdsCsv;
            }
            if ($this->db->field_exists('cw_detail', 'digital_diary_today_work')) {
                $update['cw_detail'] = $array['cw_detail'];
            }
            if ($this->db->field_exists('hw_detail', 'digital_diary_today_work')) {
                $update['hw_detail'] = $array['hw_detail'];
            }
            if ($this->db->field_exists('learning_qtype', 'digital_diary_today_work') && array_key_exists('learning_qtype', $array)) {
                $update['learning_qtype'] = $array['learning_qtype'];
            }
            if ($this->db->field_exists('learning_qcount', 'digital_diary_today_work') && array_key_exists('learning_qcount', $array)) {
                $update['learning_qcount'] = $array['learning_qcount'];
            }
            if ($this->db->field_exists('cw_attachment', 'digital_diary_today_work') && array_key_exists('cw_attachment', $array)) {
                $update['cw_attachment'] = $array['cw_attachment'];
            }
            if ($this->db->field_exists('hw_attachment', 'digital_diary_today_work') && array_key_exists('hw_attachment', $array)) {
                $update['hw_attachment'] = $array['hw_attachment'];
            }
            if ($this->db->field_exists('cw_notebook_type_ids', 'digital_diary_today_work')) {
                $update['cw_notebook_type_ids'] = $array['cw_notebook_type_ids'];
            }
            if ($this->db->field_exists('cw_notebook_type_id', 'digital_diary_today_work')) {
                $update['cw_notebook_type_id'] = $array['cw_notebook_type_id'];
            }
            if ($this->db->field_exists('hw_notebook_type_ids', 'digital_diary_today_work')) {
                $update['hw_notebook_type_ids'] = $array['hw_notebook_type_ids'];
            }
            if ($this->db->field_exists('hw_notebook_type_id', 'digital_diary_today_work')) {
                $update['hw_notebook_type_id'] = $array['hw_notebook_type_id'];
            }
            $this->db->where('id', (int) $row->id);
            $this->db->update('digital_diary_today_work', $update);
            $todayWorkId = (int) $row->id;
        } else {
            $this->db->insert('digital_diary_today_work', $array);
            $todayWorkId = (int) $this->db->insert_id();
        }

        if (!empty($data['learning_question_ids']) || array_key_exists('learning_question_ids', $data)) {
            $isTest = !empty($data['learning_is_test']) ? 1 : 0;
            $qIds = isset($data['learning_question_ids']) ? $data['learning_question_ids'] : array();
            $this->replaceTodayWorkAssignments($todayWorkId, $qIds, $isTest);
        }

        return $todayWorkId > 0 ? $todayWorkId : true;
    }

    /**
     * Ensure easy-entry columns on digital_diary_today_work (safe to call repeatedly).
     */
    public function ensureTodayWorkEasyEntryColumns()
    {
        if (!$this->db->table_exists('digital_diary_today_work')) {
            return;
        }
        $cols = array(
            'cw_detail' => "ALTER TABLE `digital_diary_today_work` ADD COLUMN `cw_detail` varchar(500) DEFAULT NULL",
            'hw_detail' => "ALTER TABLE `digital_diary_today_work` ADD COLUMN `hw_detail` varchar(500) DEFAULT NULL",
            'learning_qtype' => "ALTER TABLE `digital_diary_today_work` ADD COLUMN `learning_qtype` int(11) DEFAULT NULL",
            'learning_qcount' => "ALTER TABLE `digital_diary_today_work` ADD COLUMN `learning_qcount` int(11) DEFAULT NULL",
            'cw_attachment' => "ALTER TABLE `digital_diary_today_work` ADD COLUMN `cw_attachment` varchar(255) DEFAULT NULL",
            'hw_attachment' => "ALTER TABLE `digital_diary_today_work` ADD COLUMN `hw_attachment` varchar(255) DEFAULT NULL",
            'coordinator_reviewed' => "ALTER TABLE `digital_diary_today_work` ADD COLUMN `coordinator_reviewed` tinyint(1) NOT NULL DEFAULT 0",
            'coordinator_reviewed_at' => "ALTER TABLE `digital_diary_today_work` ADD COLUMN `coordinator_reviewed_at` datetime DEFAULT NULL",
            'coordinator_reviewed_by' => "ALTER TABLE `digital_diary_today_work` ADD COLUMN `coordinator_reviewed_by` int(11) DEFAULT NULL",
        );
        foreach ($cols as $field => $sql) {
            if (!$this->db->field_exists($field, 'digital_diary_today_work')) {
                @$this->db->query($sql);
            }
        }
    }

    /**
     * Append free-text detail to activity display (e.g. "Reading — Ex 3 / Page 42").
     *
     * @param string $activityDisplay
     * @param string|null $detail
     * @return string
     */
    public function formatTodayWorkActivityWithDetail($activityDisplay, $detail = null)
    {
        $detail = trim((string) $detail);
        $base = ($activityDisplay === null || $activityDisplay === '') ? '—' : $activityDisplay;
        if ($detail === '') {
            return $base;
        }
        if ($base === '—') {
            return $detail;
        }
        return $base . ' — ' . $detail;
    }

    /**
     * Auto-pick N questions from Question Bank by lesson(s) + type.
     *
     * @param int $branchID
     * @param int $classID
     * @param int $sectionID
     * @param int $subjectID
     * @param array $lessonIds
     * @param int $typeId
     * @param int $count
     * @return array{questions:array,requested:int,found:int,warning:string}
     */
    public function autoPickQuestionsForLearning($branchID, $classID, $sectionID, $subjectID, $lessonIds, $typeId, $count)
    {
        $count = max(1, min(50, (int) $count));
        $typeId = (int) $typeId;
        $out = array(
            'questions' => array(),
            'requested' => $count,
            'found' => 0,
            'warning' => '',
        );
        if ($typeId < 1 || !$this->db->table_exists('questions')) {
            $out['warning'] = 'Select a question type.';
            return $out;
        }
        $this->load->model('onlineexam_model');
        $typeMap = $this->onlineexam_model->question_type();
        $hasLesson = $this->db->field_exists('lesson_id', 'questions') && $this->db->table_exists('digital_diary_lesson');
        $select = 'q.id, q.question, q.type, q.custom_question_type, q.lesson_id, q.mark';
        if ($hasLesson) {
            $select .= ', ddl.lesson_no, ddl.lesson_name';
        }
        $this->db->select($select);
        $this->db->from('questions q');
        if ($hasLesson) {
            $this->db->join('digital_diary_lesson ddl', 'ddl.id = q.lesson_id', 'left');
        }
        $this->db->where('q.branch_id', (int) $branchID);
        $this->db->where('q.class_id', (int) $classID);
        $this->db->where('q.section_id', (int) $sectionID);
        $this->db->where('q.subject_id', (int) $subjectID);
        $this->db->where('q.type', $typeId);
        $lessonIds = array_values(array_filter(array_map('intval', (array) $lessonIds)));
        if (!empty($lessonIds) && $this->db->field_exists('lesson_id', 'questions')) {
            $this->db->where_in('q.lesson_id', $lessonIds);
        }
        $this->db->order_by('q.id', 'ASC');
        $this->db->limit($count);
        $rows = $this->db->get()->result_array();
        $questions = array();
        foreach ($rows as $r) {
            $t = (int) $r['type'];
            $typeLabel = isset($typeMap[(string) $t]) ? $typeMap[(string) $t] : ('Type ' . $t);
            if (!empty($r['custom_question_type'])) {
                $typeLabel = $r['custom_question_type'];
            }
            $stem = $this->formatQuestionPreviewForPicker(isset($r['question']) ? $r['question'] : '', $t);
            $lessonPart = '';
            if (!empty($r['lesson_id'])) {
                $lno = isset($r['lesson_no']) ? trim((string) $r['lesson_no']) : '';
                $lname = isset($r['lesson_name']) ? trim((string) $r['lesson_name']) : '';
                $lessonPart = trim($lno . ($lno !== '' && $lname !== '' ? '. ' : '') . $lname);
            }
            $label = '[' . $typeLabel . ']';
            if ($lessonPart !== '') {
                $label .= ' [' . $lessonPart . ']';
            }
            $label .= ' ' . $stem;
            $questions[] = array(
                'id' => (int) $r['id'],
                'type' => $t,
                'type_label' => $typeLabel,
                'mark' => isset($r['mark']) ? $r['mark'] : '',
                'lesson_id' => isset($r['lesson_id']) ? (int) $r['lesson_id'] : 0,
                'label' => $label,
            );
        }
        $out['questions'] = $questions;
        $out['found'] = count($questions);
        if ($out['found'] < $count) {
            $out['warning'] = $out['found'] === 0
                ? 'No questions found for this lesson and type.'
                : ('Only ' . $out['found'] . ' of ' . $count . ' questions available; assigning all found.');
        }
        return $out;
    }

    /**
     * Ensure learning-flow tables exist (safe to call repeatedly).
     */
    public function ensureTodayWorkLearningFlowTables()
    {
        $this->ensureTodayWorkEasyEntryColumns();
        $this->ensureTodayWorkDayReviewDeadlineColumn();
        if (!$this->db->table_exists('digital_diary_today_work_assignment')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `digital_diary_today_work_assignment` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `today_work_id` int(11) NOT NULL,
              `question_id` int(11) NOT NULL,
              `is_test` tinyint(1) NOT NULL DEFAULT 0,
              `sort_order` int(11) NOT NULL DEFAULT 0,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`),
              UNIQUE KEY `dd_twa_unique` (`today_work_id`,`question_id`),
              KEY `dd_twa_tw` (`today_work_id`),
              KEY `dd_twa_q` (`question_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        }
        if (!$this->db->table_exists('digital_diary_student_submission')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `digital_diary_student_submission` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `today_work_id` int(11) NOT NULL,
              `student_id` int(11) NOT NULL,
              `submit_type` varchar(20) NOT NULL,
              `photo` varchar(255) DEFAULT NULL,
              `message` text DEFAULT NULL,
              `status` varchar(20) NOT NULL DEFAULT 'submitted',
              `marks` decimal(8,2) DEFAULT NULL,
              `verified_by` int(11) DEFAULT NULL,
              `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
              `verified_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `dd_ss_unique` (`today_work_id`,`student_id`,`submit_type`),
              KEY `dd_ss_tw` (`today_work_id`),
              KEY `dd_ss_student` (`student_id`),
              KEY `dd_ss_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        }
        if (!$this->db->table_exists('digital_diary_coordinator_alert')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `digital_diary_coordinator_alert` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `branch_id` int(11) NOT NULL,
              `work_date` date NOT NULL,
              `class_id` int(11) NOT NULL DEFAULT 0,
              `section_id` int(11) NOT NULL DEFAULT 0,
              `event_type` varchar(50) NOT NULL,
              `message` varchar(500) NOT NULL,
              `ref_id` int(11) DEFAULT NULL,
              `is_read` tinyint(1) NOT NULL DEFAULT 0,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`),
              KEY `dd_ca_branch` (`branch_id`),
              KEY `dd_ca_date` (`work_date`),
              KEY `dd_ca_read` (`is_read`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        }
        $this->ensureRoleAllocationTable();
    }

    /**
     * Assign existing teachers as Digital Diary coordinator / moderator (per class+section).
     */
    public function ensureRoleAllocationTable()
    {
        if ($this->db->table_exists('digital_diary_role_allocation')) {
            return;
        }
        $this->db->query("CREATE TABLE IF NOT EXISTS `digital_diary_role_allocation` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `role_type` varchar(20) NOT NULL COMMENT 'coordinator|moderator',
          `teacher_id` int(11) NOT NULL,
          `class_id` int(11) NOT NULL,
          `section_id` int(11) NOT NULL,
          `session_id` int(11) NOT NULL,
          `branch_id` int(11) DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `dd_ra_unique` (`role_type`,`class_id`,`section_id`,`session_id`,`branch_id`),
          KEY `dd_ra_teacher` (`teacher_id`),
          KEY `dd_ra_role` (`role_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
    }

    /**
     * @param string $roleType coordinator|moderator|'' (all)
     * @return CI_DB_result
     */
    public function getRoleAllocations($branchID = '', $roleType = '')
    {
        $this->ensureRoleAllocationTable();
        $this->db->select('ra.*, st.name as teacher_name, st.staff_id as teacher_code, c.name as class_name, s.name as section_name');
        $this->db->from('digital_diary_role_allocation ra');
        $this->db->join('staff st', 'st.id = ra.teacher_id', 'left');
        $this->db->join('class c', 'c.id = ra.class_id', 'left');
        $this->db->join('section s', 's.id = ra.section_id', 'left');
        $this->db->where('ra.session_id', (int) get_session_id());
        if ($branchID !== '' && $branchID !== null && (int) $branchID > 0) {
            $this->db->where('ra.branch_id', (int) $branchID);
        }
        if ($roleType === 'coordinator' || $roleType === 'moderator') {
            $this->db->where('ra.role_type', $roleType);
        }
        $this->db->order_by('ra.role_type ASC, c.name ASC, s.name ASC, ra.id ASC');
        return $this->db->get();
    }

    /**
     * @param array $data role_type, class_id, section_id, staff_id, allocation_id?, branch_id?
     */
    public function roleAllocationSave(array $data)
    {
        $this->ensureRoleAllocationTable();
        $roleType = isset($data['role_type']) ? trim((string) $data['role_type']) : '';
        if ($roleType !== 'coordinator' && $roleType !== 'moderator') {
            return false;
        }
        $branchID = isset($data['branch_id']) ? (int) $data['branch_id'] : (int) $this->application_model->get_branch_id();
        if ($branchID < 1) {
            $branchID = (int) get_loggedin_branch_id();
        }
        $row = array(
            'role_type' => $roleType,
            'teacher_id' => (int) $data['staff_id'],
            'class_id' => (int) $data['class_id'],
            'section_id' => (int) $data['section_id'],
            'session_id' => (int) get_session_id(),
            'branch_id' => $branchID,
        );
        if (!empty($data['allocation_id'])) {
            $this->db->where('id', (int) $data['allocation_id']);
            $this->db->update('digital_diary_role_allocation', $row);
            return true;
        }
        $this->db->insert('digital_diary_role_allocation', $row);
        return true;
    }

    public function roleAllocationDelete($id, $branchID = 0)
    {
        $this->ensureRoleAllocationTable();
        if ((int) $branchID > 0) {
            $this->db->where('branch_id', (int) $branchID);
        }
        $this->db->where('id', (int) $id);
        return $this->db->delete('digital_diary_role_allocation');
    }

    /**
     * Coordinator/moderator staff IDs for one class+section (current session).
     *
     * @return array{coordinator:int,moderator:int}
     */
    public function getClassSectionDiaryStaffIds($classID, $sectionID, $branchID = 0)
    {
        $this->ensureRoleAllocationTable();
        $out = array('coordinator' => 0, 'moderator' => 0);
        $this->db->select('role_type, teacher_id');
        $this->db->from('digital_diary_role_allocation');
        $this->db->where('class_id', (int) $classID);
        $this->db->where('section_id', (int) $sectionID);
        $this->db->where('session_id', (int) get_session_id());
        if ((int) $branchID > 0) {
            $this->db->where('branch_id', (int) $branchID);
        }
        foreach ($this->db->get()->result_array() as $r) {
            if ($r['role_type'] === 'coordinator' || $r['role_type'] === 'moderator') {
                $out[$r['role_type']] = (int) $r['teacher_id'];
            }
        }
        return $out;
    }

    /**
     * Set or clear coordinator/moderator for a class+section.
     * Empty staff ID removes that role.
     */
    public function upsertClassSectionDiaryRole($roleType, $classID, $sectionID, $branchID, $staffID)
    {
        $this->ensureRoleAllocationTable();
        if ($roleType !== 'coordinator' && $roleType !== 'moderator') {
            return false;
        }
        $classID = (int) $classID;
        $sectionID = (int) $sectionID;
        $branchID = (int) $branchID;
        $staffID = (int) $staffID;
        $sessionID = (int) get_session_id();
        $where = array(
            'role_type' => $roleType,
            'class_id' => $classID,
            'section_id' => $sectionID,
            'session_id' => $sessionID,
        );
        if ($branchID > 0) {
            $where['branch_id'] = $branchID;
        }
        if ($staffID < 1) {
            $this->db->where($where);
            $this->db->delete('digital_diary_role_allocation');
            return true;
        }
        $existing = $this->db->get_where('digital_diary_role_allocation', $where)->row_array();
        $row = array(
            'role_type' => $roleType,
            'teacher_id' => $staffID,
            'class_id' => $classID,
            'section_id' => $sectionID,
            'session_id' => $sessionID,
            'branch_id' => $branchID > 0 ? $branchID : null,
        );
        if (!empty($existing['id'])) {
            $this->db->where('id', (int) $existing['id']);
            $this->db->update('digital_diary_role_allocation', $row);
        } else {
            $this->db->insert('digital_diary_role_allocation', $row);
        }
        return true;
    }

    /**
     * Whether staff is allocated as coordinator/moderator for current session.
     *
     * @param string $roleType coordinator|moderator
     */
    public function staffHasDiaryRole($staffID, $roleType, $branchID = 0)
    {
        $this->ensureRoleAllocationTable();
        if ((int) $staffID < 1 || ($roleType !== 'coordinator' && $roleType !== 'moderator')) {
            return false;
        }
        $this->db->from('digital_diary_role_allocation');
        $this->db->where('teacher_id', (int) $staffID);
        $this->db->where('role_type', $roleType);
        $this->db->where('session_id', (int) get_session_id());
        if ((int) $branchID > 0) {
            $this->db->where('branch_id', (int) $branchID);
        }
        $this->db->limit(1);
        return $this->db->get()->num_rows() > 0;
    }

    /**
     * Class/section pairs for a diary role (for filtering coordinator inbox).
     *
     * @return array<int,array{class_id:int,section_id:int}>
     */
    public function getStaffDiaryRoleScopes($staffID, $roleType = '', $branchID = 0)
    {
        $this->ensureRoleAllocationTable();
        $this->db->select('class_id, section_id, role_type');
        $this->db->from('digital_diary_role_allocation');
        $this->db->where('teacher_id', (int) $staffID);
        $this->db->where('session_id', (int) get_session_id());
        if ($roleType === 'coordinator' || $roleType === 'moderator') {
            $this->db->where('role_type', $roleType);
        }
        if ((int) $branchID > 0) {
            $this->db->where('branch_id', (int) $branchID);
        }
        $out = array();
        foreach ($this->db->get()->result_array() as $r) {
            $out[] = array(
                'class_id' => (int) $r['class_id'],
                'section_id' => (int) $r['section_id'],
                'role_type' => $r['role_type'],
            );
        }
        return $out;
    }

    /**
     * Replace Question Bank assignments for a today_work row.
     *
     * @param int $todayWorkId
     * @param array|string $questionIds
     * @param int $isTest
     */
    public function replaceTodayWorkAssignments($todayWorkId, $questionIds, $isTest = 0)
    {
        $this->ensureTodayWorkLearningFlowTables();
        $todayWorkId = (int) $todayWorkId;
        if ($todayWorkId <= 0) {
            return;
        }
        if (!$this->db->table_exists('digital_diary_today_work_assignment')) {
            return;
        }
        $ids = array();
        if (is_array($questionIds)) {
            foreach ($questionIds as $qid) {
                $qid = (int) $qid;
                if ($qid > 0) {
                    $ids[] = $qid;
                }
            }
        } elseif (is_string($questionIds) && $questionIds !== '') {
            foreach (explode(',', $questionIds) as $qid) {
                $qid = (int) trim($qid);
                if ($qid > 0) {
                    $ids[] = $qid;
                }
            }
        }
        $ids = array_values(array_unique($ids));
        $this->db->where('today_work_id', $todayWorkId)->delete('digital_diary_today_work_assignment');
        $sort = 0;
        $isTest = $isTest ? 1 : 0;
        foreach ($ids as $qid) {
            $this->db->insert('digital_diary_today_work_assignment', array(
                'today_work_id' => $todayWorkId,
                'question_id' => $qid,
                'is_test' => $isTest,
                'sort_order' => $sort++,
            ));
        }
    }

    /**
     * Questions for today's work learning picker (by class/section/subject + optional lessons).
     *
     * @return array
     */
    public function getQuestionsForTodayWorkPicker($branchID, $classID, $sectionID, $subjectID, $lessonIds = array(), $typeId = 0)
    {
        if (!$this->db->table_exists('questions')) {
            return array();
        }
        $this->load->model('onlineexam_model');
        $typeMap = $this->onlineexam_model->question_type();
        $hasLesson = $this->db->field_exists('lesson_id', 'questions') && $this->db->table_exists('digital_diary_lesson');
        $select = 'q.id, q.question, q.type, q.custom_question_type, q.lesson_id, q.mark';
        if ($hasLesson) {
            $select .= ', ddl.lesson_no, ddl.lesson_name';
        }
        $this->db->select($select);
        $this->db->from('questions q');
        if ($hasLesson) {
            $this->db->join('digital_diary_lesson ddl', 'ddl.id = q.lesson_id', 'left');
        }
        $this->db->where('q.branch_id', (int) $branchID);
        $this->db->where('q.class_id', (int) $classID);
        $this->db->where('q.section_id', (int) $sectionID);
        $this->db->where('q.subject_id', (int) $subjectID);
        $typeId = (int) $typeId;
        if ($typeId > 0) {
            $this->db->where('q.type', $typeId);
        }
        $lessonIds = array_values(array_filter(array_map('intval', (array) $lessonIds)));
        if (!empty($lessonIds) && $this->db->field_exists('lesson_id', 'questions')) {
            $this->db->group_start();
            $this->db->where_in('q.lesson_id', $lessonIds);
            $this->db->or_where('q.lesson_id IS NULL', null, false);
            $this->db->or_where('q.lesson_id', 0);
            $this->db->group_end();
        }
        if ($hasLesson) {
            if ($this->db->field_exists('sort_order', 'digital_diary_lesson')) {
                $this->db->order_by('ddl.sort_order ASC, q.type ASC, q.id ASC');
            } else {
                $this->db->order_by('ddl.lesson_no ASC, q.type ASC, q.id ASC');
            }
        } else {
            $this->db->order_by('q.type ASC, q.id ASC');
        }
        $this->db->limit(300);
        $rows = $this->db->get()->result_array();
        $out = array();
        $seen = array();
        foreach ($rows as $r) {
            $qid = (int) $r['id'];
            if (isset($seen[$qid])) {
                continue;
            }
            $seen[$qid] = true;
            $t = (int) $r['type'];
            $typeLabel = isset($typeMap[(string) $t]) ? $typeMap[(string) $t] : ('Type ' . $t);
            if (!empty($r['custom_question_type'])) {
                $typeLabel = $r['custom_question_type'];
            }
            $stem = $this->formatQuestionPreviewForPicker(isset($r['question']) ? $r['question'] : '', $t);
            $lessonPart = '';
            if (!empty($r['lesson_id'])) {
                $lno = isset($r['lesson_no']) ? trim((string) $r['lesson_no']) : '';
                $lname = isset($r['lesson_name']) ? trim((string) $r['lesson_name']) : '';
                $lessonPart = trim($lno . ($lno !== '' && $lname !== '' ? '. ' : '') . $lname);
            }
            $label = '[' . $typeLabel . ']';
            if ($lessonPart !== '') {
                $label .= ' [' . $lessonPart . ']';
            }
            $label .= ' ' . $stem;
            $out[] = array(
                'id' => $qid,
                'type' => $t,
                'type_label' => $typeLabel,
                'mark' => isset($r['mark']) ? $r['mark'] : '',
                'lesson_id' => isset($r['lesson_id']) ? (int) $r['lesson_id'] : 0,
                'label' => $label,
            );
        }
        return $out;
    }

    /**
     * Clean short preview for Question Bank picker / assignment lists.
     *
     * @param string $raw
     * @param int $type
     * @return string
     */
    public function formatQuestionPreviewForPicker($raw, $type = 0)
    {
        $raw = html_entity_decode((string) $raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $raw = preg_replace('#<(script|style)[^>]*>.*?</\1>#is', '', $raw);
        // Match-the-following stored as JSON
        if ((int) $type === 5) {
            $dec = json_decode($raw, true);
            if (is_array($dec) && !empty($dec['instruction'])) {
                $raw = $dec['instruction'];
            } elseif (is_array($dec)) {
                $raw = 'Match the following';
            }
        }
        $stem = trim(preg_replace('/\s+/u', ' ', strip_tags($raw)));
        $stem = str_replace(array("\xc2\xa0", '&nbsp;'), ' ', $stem);
        $stem = trim(preg_replace('/\s+/u', ' ', $stem));
        if ($stem === '') {
            $stem = 'Question #' . (int) $type;
        }
        if (function_exists('mb_substr')) {
            $short = mb_substr($stem, 0, 90);
            if (mb_strlen($stem) > 90) {
                $short .= '…';
            }
        } else {
            $short = substr($stem, 0, 90);
            if (strlen($stem) > 90) {
                $short .= '…';
            }
        }
        return $short;
    }

    /**
     * @param int $todayWorkId
     * @return array
     */
    public function getTodayWorkAssignments($todayWorkId)
    {
        $this->ensureTodayWorkLearningFlowTables();
        if (!$this->db->table_exists('digital_diary_today_work_assignment') || (int) $todayWorkId <= 0) {
            return array();
        }
        $this->load->model('onlineexam_model');
        $typeMap = $this->onlineexam_model->question_type();
        $this->db->select('a.*, q.question, q.type, q.custom_question_type, q.mark');
        $this->db->from('digital_diary_today_work_assignment a');
        $this->db->join('questions q', 'q.id = a.question_id', 'left');
        $this->db->where('a.today_work_id', (int) $todayWorkId);
        $this->db->order_by('a.sort_order ASC, a.id ASC');
        $rows = $this->db->get()->result_array();
        foreach ($rows as &$r) {
            $t = (int) (isset($r['type']) ? $r['type'] : 0);
            $typeLabel = isset($typeMap[(string) $t]) ? $typeMap[(string) $t] : ('Type ' . $t);
            if (!empty($r['custom_question_type'])) {
                $typeLabel = $r['custom_question_type'];
            }
            $r['type_label'] = $typeLabel;
            $r['question_preview'] = $this->formatQuestionPreviewForPicker(isset($r['question']) ? $r['question'] : '', $t);
        }
        unset($r);
        return $rows;
    }

    /**
     * Assignment summary labels keyed by today_work_id or subject map enrichment.
     *
     * @param array $todayWorkIds
     * @return array<int,array{count:int,labels:string,has_test:int}>
     */
    public function getAssignmentSummaryByTodayWorkIds($todayWorkIds)
    {
        $this->ensureTodayWorkLearningFlowTables();
        $out = array();
        $ids = array_values(array_filter(array_map('intval', (array) $todayWorkIds)));
        if (empty($ids) || !$this->db->table_exists('digital_diary_today_work_assignment')) {
            return $out;
        }
        $this->load->model('onlineexam_model');
        $typeMap = $this->onlineexam_model->question_type();
        $this->db->select('a.today_work_id, a.is_test, q.type, q.custom_question_type');
        $this->db->from('digital_diary_today_work_assignment a');
        $this->db->join('questions q', 'q.id = a.question_id', 'left');
        $this->db->where_in('a.today_work_id', $ids);
        $rows = $this->db->get()->result_array();
        foreach ($rows as $r) {
            $twid = (int) $r['today_work_id'];
            if (!isset($out[$twid])) {
                $out[$twid] = array('count' => 0, 'labels' => array(), 'has_test' => 0);
            }
            $out[$twid]['count']++;
            if (!empty($r['is_test'])) {
                $out[$twid]['has_test'] = 1;
            }
            $t = (int) (isset($r['type']) ? $r['type'] : 0);
            $lab = isset($typeMap[(string) $t]) ? $typeMap[(string) $t] : ('Type ' . $t);
            if (!empty($r['custom_question_type'])) {
                $lab = $r['custom_question_type'];
            }
            $out[$twid]['labels'][$lab] = $lab;
        }
        foreach ($out as $twid => $info) {
            $out[$twid]['labels'] = implode(', ', array_values($info['labels']));
        }
        return $out;
    }

    /**
     * @param array $data branch_id, work_date, class_id, section_id, event_type, message, ref_id
     */
    public function addCoordinatorAlert($data)
    {
        $this->ensureTodayWorkLearningFlowTables();
        if (!$this->db->table_exists('digital_diary_coordinator_alert')) {
            return false;
        }
        $row = array(
            'branch_id' => (int) $data['branch_id'],
            'work_date' => $data['work_date'],
            'class_id' => isset($data['class_id']) ? (int) $data['class_id'] : 0,
            'section_id' => isset($data['section_id']) ? (int) $data['section_id'] : 0,
            'event_type' => substr((string) $data['event_type'], 0, 50),
            'message' => substr((string) $data['message'], 0, 500),
            'ref_id' => isset($data['ref_id']) ? (int) $data['ref_id'] : null,
            'is_read' => 0,
        );
        return $this->db->insert('digital_diary_coordinator_alert', $row);
    }

    /**
     * @return array
     */
    /**
     * @param array<int,array{class_id:int,section_id:int}>|null $scopes null = no class/section filter
     * @return array
     */
    public function getCoordinatorAlerts($branchID, $limit = 100, $unreadOnly = false, $scopes = null)
    {
        $this->ensureTodayWorkLearningFlowTables();
        if (!$this->db->table_exists('digital_diary_coordinator_alert')) {
            return array();
        }
        $this->db->select('a.*, c.name as class_name, s.name as section_name');
        $this->db->from('digital_diary_coordinator_alert a');
        $this->db->join('class c', 'c.id = a.class_id', 'left');
        $this->db->join('section s', 's.id = a.section_id', 'left');
        if ((int) $branchID > 0) {
            $this->db->where('a.branch_id', (int) $branchID);
        }
        if ($unreadOnly) {
            $this->db->where('a.is_read', 0);
        }
        if (is_array($scopes)) {
            if (empty($scopes)) {
                return array();
            }
            $parts = array();
            foreach ($scopes as $sc) {
                $parts[] = '(a.class_id=' . (int) $sc['class_id'] . ' AND a.section_id=' . (int) $sc['section_id'] . ')';
            }
            $this->db->where('(' . implode(' OR ', $parts) . ')', null, false);
        }
        $this->db->order_by('a.id', 'DESC');
        $this->db->limit((int) $limit);
        return $this->db->get()->result_array();
    }

    /**
     * @param array<int,array{class_id:int,section_id:int}>|null $scopes
     * @return int
     */
    public function countUnreadCoordinatorAlerts($branchID, $scopes = null)
    {
        $this->ensureTodayWorkLearningFlowTables();
        if (!$this->db->table_exists('digital_diary_coordinator_alert')) {
            return 0;
        }
        $this->db->from('digital_diary_coordinator_alert');
        $this->db->where('is_read', 0);
        if ((int) $branchID > 0) {
            $this->db->where('branch_id', (int) $branchID);
        }
        if (is_array($scopes)) {
            if (empty($scopes)) {
                return 0;
            }
            $parts = array();
            foreach ($scopes as $sc) {
                $parts[] = '(class_id=' . (int) $sc['class_id'] . ' AND section_id=' . (int) $sc['section_id'] . ')';
            }
            $this->db->where('(' . implode(' OR ', $parts) . ')', null, false);
        }
        return (int) $this->db->count_all_results();
    }

    /**
     * Map of work_date|class_id|section_id => reviewed status for coordinator inbox.
     *
     * @param int $branchID
     * @return array<string,array{reviewed:int,total:int,reviewed_count:int}>
     */
    public function getCoordinatorReviewStatusMap($branchID)
    {
        $out = array();
        if (!$this->db->table_exists('digital_diary_today_work')) {
            return $out;
        }
        $hasReview = $this->db->field_exists('coordinator_reviewed', 'digital_diary_today_work');
        $sel = 'work_date, class_id, section_id, COUNT(*) AS total';
        if ($hasReview) {
            $sel .= ', SUM(CASE WHEN coordinator_reviewed = 1 THEN 1 ELSE 0 END) AS reviewed_count';
        }
        $this->db->select($sel, false);
        $this->db->from('digital_diary_today_work');
        if ((int) $branchID > 0) {
            $this->db->where('branch_id', (int) $branchID);
        }
        $this->db->where('session_id', (int) get_session_id());
        $this->db->group_by(array('work_date', 'class_id', 'section_id'));
        $rows = $this->db->get()->result_array();
        foreach ($rows as $r) {
            $key = $r['work_date'] . '|' . (int) $r['class_id'] . '|' . (int) $r['section_id'];
            $total = (int) $r['total'];
            $revCount = $hasReview ? (int) $r['reviewed_count'] : 0;
            $out[$key] = array(
                'reviewed' => ($total > 0 && $revCount >= $total) ? 1 : 0,
                'total' => $total,
                'reviewed_count' => $revCount,
            );
        }
        return $out;
    }

    public function markCoordinatorAlertRead($id, $branchID = 0)
    {
        if (!$this->db->table_exists('digital_diary_coordinator_alert')) {
            return false;
        }
        $this->db->where('id', (int) $id);
        if ((int) $branchID > 0) {
            $this->db->where('branch_id', (int) $branchID);
        }
        return $this->db->update('digital_diary_coordinator_alert', array('is_read' => 1));
    }

    public function markAllCoordinatorAlertsRead($branchID)
    {
        if (!$this->db->table_exists('digital_diary_coordinator_alert')) {
            return false;
        }
        if ((int) $branchID > 0) {
            $this->db->where('branch_id', (int) $branchID);
        }
        return $this->db->update('digital_diary_coordinator_alert', array('is_read' => 1));
    }

    /**
     * Mark all today's work rows for class/section/date as coordinator-reviewed.
     */
    public function markTodayWorkCoordinatorReviewed($workDate, $branchID, $classID, $sectionID, $staffID)
    {
        if (!$this->db->table_exists('digital_diary_today_work')) {
            return false;
        }
        if (!$this->db->field_exists('coordinator_reviewed', 'digital_diary_today_work')) {
            $this->ensureTodayWorkEasyEntryColumns();
        }
        if (!$this->db->field_exists('coordinator_reviewed', 'digital_diary_today_work')) {
            return false;
        }
        $sessionID = (int) get_session_id();
        $this->db->where('branch_id', (int) $branchID);
        $this->db->where('session_id', $sessionID);
        $this->db->where('work_date', $workDate);
        $this->db->where('class_id', (int) $classID);
        $this->db->where('section_id', (int) $sectionID);
        return $this->db->update('digital_diary_today_work', array(
            'coordinator_reviewed' => 1,
            'coordinator_reviewed_at' => date('Y-m-d H:i:s'),
            'coordinator_reviewed_by' => (int) $staffID,
        ));
    }

    /**
     * Upsert student submission for homework/learning.
     *
     * @param array $data
     * @return int submission id
     */
    public function saveStudentSubmission($data)
    {
        $this->ensureTodayWorkLearningFlowTables();
        if (!$this->db->table_exists('digital_diary_student_submission')) {
            return 0;
        }
        $twId = (int) $data['today_work_id'];
        $studentId = (int) $data['student_id'];
        $type = isset($data['submit_type']) ? $data['submit_type'] : 'homework';
        if (!in_array($type, array('homework', 'learning'), true)) {
            $type = 'homework';
        }
        $row = array(
            'today_work_id' => $twId,
            'student_id' => $studentId,
            'submit_type' => $type,
            'message' => isset($data['message']) ? $data['message'] : null,
            'status' => isset($data['status']) ? $data['status'] : 'submitted',
            'submitted_at' => date('Y-m-d H:i:s'),
        );
        if (array_key_exists('photo', $data)) {
            $row['photo'] = $data['photo'];
        }
        $this->db->where('today_work_id', $twId);
        $this->db->where('student_id', $studentId);
        $this->db->where('submit_type', $type);
        $existing = $this->db->get('digital_diary_student_submission')->row();
        if ($existing) {
            if (!empty($existing->status) && $existing->status === 'verified' && empty($data['force'])) {
                return (int) $existing->id;
            }
            $this->db->where('id', (int) $existing->id);
            $this->db->update('digital_diary_student_submission', $row);
            return (int) $existing->id;
        }
        $this->db->insert('digital_diary_student_submission', $row);
        return (int) $this->db->insert_id();
    }

    /**
     * Submissions map: [student_id][submit_type] => row for given today_work ids.
     *
     * @return array
     */
    public function getSubmissionsMapByTodayWorkIds($todayWorkIds)
    {
        $this->ensureTodayWorkLearningFlowTables();
        $map = array();
        $ids = array_values(array_filter(array_map('intval', (array) $todayWorkIds)));
        if (empty($ids) || !$this->db->table_exists('digital_diary_student_submission')) {
            return $map;
        }
        $this->db->where_in('today_work_id', $ids);
        $rows = $this->db->get('digital_diary_student_submission')->result_array();
        foreach ($rows as $r) {
            $sid = (int) $r['student_id'];
            $tw = (int) $r['today_work_id'];
            if (!isset($map[$tw])) {
                $map[$tw] = array();
            }
            if (!isset($map[$tw][$sid])) {
                $map[$tw][$sid] = array();
            }
            $map[$tw][$sid][$r['submit_type']] = $r;
        }
        return $map;
    }

    /**
     * Whether any assignment for subject/day is marked as test.
     *
     * @return array<int,bool> subject_id => has_test
     */
    public function getSubjectHasTestMapFromTodayWork($workDate, $branchID, $classID, $sectionID)
    {
        $map = array();
        $twMap = $this->getTodayWorkMapByClassSectionDate($workDate, $branchID, $classID, $sectionID);
        if (empty($twMap)) {
            return $map;
        }
        $twIds = array();
        $subjByTw = array();
        foreach ($twMap as $subjId => $row) {
            $twid = (int) $row['id'];
            $twIds[] = $twid;
            $subjByTw[$twid] = (int) $subjId;
            $map[(int) $subjId] = false;
        }
        $sum = $this->getAssignmentSummaryByTodayWorkIds($twIds);
        foreach ($sum as $twid => $info) {
            if (!empty($info['has_test']) && isset($subjByTw[$twid])) {
                $map[$subjByTw[$twid]] = true;
            }
        }
        return $map;
    }

    /**
     * Verify submissions and optionally set marks for learning.
     *
     * @param array $marksByStudent [student_id => marks] for learning type
     */
    public function verifySubmissionsForTodayWork($todayWorkId, $staffId, $marksByStudent = array())
    {
        $this->ensureTodayWorkLearningFlowTables();
        if (!$this->db->table_exists('digital_diary_student_submission')) {
            return;
        }
        $todayWorkId = (int) $todayWorkId;
        $this->db->where('today_work_id', $todayWorkId);
        $this->db->where_in('status', array('submitted', 'verified'));
        $rows = $this->db->get('digital_diary_student_submission')->result_array();
        foreach ($rows as $r) {
            $upd = array(
                'status' => 'verified',
                'verified_by' => (int) $staffId,
                'verified_at' => date('Y-m-d H:i:s'),
            );
            $sid = (int) $r['student_id'];
            if ($r['submit_type'] === 'learning' && array_key_exists($sid, $marksByStudent)) {
                $m = $marksByStudent[$sid];
                $upd['marks'] = ($m === '' || $m === null) ? null : (float) $m;
            }
            $this->db->where('id', (int) $r['id']);
            $this->db->update('digital_diary_student_submission', $upd);
        }
    }

    /**
     * Today's work rows for a student's class/section on a date (for portal).
     *
     * @return array
     */
    public function getTodayWorkListForStudent($workDate, $branchID, $classID, $sectionID)
    {
        if (!$this->db->table_exists('digital_diary_today_work')) {
            return array();
        }
        $sessionID = (int) get_session_id();
        $this->db->select('tw.*, subj.name as subject_name');
        $this->db->from('digital_diary_today_work tw');
        $this->db->join('subject subj', 'subj.id = tw.subject_id', 'left');
        $this->db->where('tw.branch_id', (int) $branchID);
        $this->db->where('tw.session_id', $sessionID);
        $this->db->where('tw.work_date', $workDate);
        $this->db->where('tw.class_id', (int) $classID);
        $this->db->where('tw.section_id', (int) $sectionID);
        $this->db->order_by('subj.name ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Ensure review_deadline column exists and backfill existing final days.
     */
    public function ensureTodayWorkDayReviewDeadlineColumn()
    {
        if (!$this->db->table_exists('digital_diary_today_work_day')) {
            return;
        }
        if (!$this->db->field_exists('review_deadline', 'digital_diary_today_work_day')) {
            $this->db->query(
                "ALTER TABLE `digital_diary_today_work_day`
                 ADD COLUMN `review_deadline` date DEFAULT NULL
                 COMMENT 'Coordinator review due (7 working days after final submit)'
                 AFTER `finalized_at`"
            );
        }
        if (!$this->db->field_exists('review_deadline', 'digital_diary_today_work_day')) {
            return;
        }
        // Backfill exact Mon–Fri deadlines for existing final days.
        $this->db->select('branch_id, session_id, work_date, class_id, section_id, finalized_at');
        $this->db->from('digital_diary_today_work_day');
        $this->db->where('is_final', 1);
        $this->db->group_start();
        $this->db->where('review_deadline IS NULL', null, false);
        $this->db->or_where('review_deadline', '0000-00-00');
        $this->db->group_end();
        $this->db->where('finalized_at IS NOT NULL', null, false);
        $rows = $this->db->get()->result_array();
        foreach ($rows as $row) {
            $start = date('Y-m-d', strtotime($row['finalized_at']));
            $deadline = $this->addWorkingDays($start, 7);
            $this->db->where(array(
                'branch_id' => (int) $row['branch_id'],
                'session_id' => (int) $row['session_id'],
                'work_date' => $row['work_date'],
                'class_id' => (int) $row['class_id'],
                'section_id' => (int) $row['section_id'],
            ));
            $this->db->update('digital_diary_today_work_day', array('review_deadline' => $deadline));
        }
    }

    /**
     * Add N working days (Mon–Fri) after $startDate.
     *
     * @param string $startDate Y-m-d
     * @param int $days
     * @return string Y-m-d
     */
    public function addWorkingDays($startDate, $days)
    {
        $days = max(0, (int) $days);
        $ts = strtotime($startDate);
        if ($ts === false) {
            $ts = time();
        }
        $added = 0;
        while ($added < $days) {
            $ts = strtotime('+1 day', $ts);
            $w = (int) date('w', $ts);
            if ($w !== 0 && $w !== 6) {
                $added++;
            }
        }
        return date('Y-m-d', $ts);
    }

    /**
     * Count Mon–Fri days from $fromDate to $toDate inclusive (0 if to < from).
     *
     * @param string $fromDate Y-m-d
     * @param string $toDate Y-m-d
     * @return int
     */
    public function countWorkingDaysInclusive($fromDate, $toDate)
    {
        $from = strtotime($fromDate);
        $to = strtotime($toDate);
        if ($from === false || $to === false || $to < $from) {
            return 0;
        }
        $n = 0;
        for ($ts = $from; $ts <= $to; $ts = strtotime('+1 day', $ts)) {
            $w = (int) date('w', $ts);
            if ($w !== 0 && $w !== 6) {
                $n++;
            }
        }
        return $n;
    }

    /**
     * Day row for date/class/section (current session).
     *
     * @return array|null
     */
    public function getTodayWorkDayRow($workDate, $branchID, $classID, $sectionID)
    {
        if (!$this->db->table_exists('digital_diary_today_work_day')) {
            return null;
        }
        $this->ensureTodayWorkDayReviewDeadlineColumn();
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }
        $this->db->where(array(
            'branch_id' => (int) $branchID,
            'session_id' => (int) get_session_id(),
            'work_date' => $workDate,
            'class_id' => (int) $classID,
            'section_id' => (int) $sectionID,
        ));
        $row = $this->db->get('digital_diary_today_work_day')->row_array();
        return $row ? $row : null;
    }

    /**
     * Whether all Today’s Work rows for the day are coordinator-reviewed.
     */
    public function isCoordinatorDayReviewed($workDate, $branchID, $classID, $sectionID)
    {
        if (!$this->db->table_exists('digital_diary_today_work')) {
            return false;
        }
        if (!$this->db->field_exists('coordinator_reviewed', 'digital_diary_today_work')) {
            $this->ensureTodayWorkEasyEntryColumns();
        }
        if (!$this->db->field_exists('coordinator_reviewed', 'digital_diary_today_work')) {
            return false;
        }
        $this->db->from('digital_diary_today_work');
        $this->db->where('branch_id', (int) $branchID);
        $this->db->where('session_id', (int) get_session_id());
        $this->db->where('work_date', $workDate);
        $this->db->where('class_id', (int) $classID);
        $this->db->where('section_id', (int) $sectionID);
        $total = (int) $this->db->count_all_results();
        if ($total < 1) {
            return false;
        }
        $this->db->from('digital_diary_today_work');
        $this->db->where('branch_id', (int) $branchID);
        $this->db->where('session_id', (int) get_session_id());
        $this->db->where('work_date', $workDate);
        $this->db->where('class_id', (int) $classID);
        $this->db->where('section_id', (int) $sectionID);
        $this->db->where('coordinator_reviewed', 1);
        $reviewed = (int) $this->db->count_all_results();
        return $reviewed >= $total;
    }

    /**
     * Coordinator review window status for a class/section/date.
     *
     * @return array{status:string,deadline:?string,days_left:?int,is_final:bool,is_reviewed:bool,label:string}
     */
    public function getCoordinatorReviewWindowStatus($workDate, $branchID, $classID, $sectionID)
    {
        $out = array(
            'status' => 'not_final',
            'deadline' => null,
            'days_left' => null,
            'is_final' => false,
            'is_reviewed' => false,
            'label' => 'Not final submitted',
        );
        $day = $this->getTodayWorkDayRow($workDate, $branchID, $classID, $sectionID);
        $isFinal = !empty($day['is_final']);
        $out['is_final'] = $isFinal;
        $isReviewed = $this->isCoordinatorDayReviewed($workDate, $branchID, $classID, $sectionID);
        $out['is_reviewed'] = $isReviewed;
        if (!$isFinal) {
            return $out;
        }
        $deadline = !empty($day['review_deadline']) ? $day['review_deadline'] : null;
        if ($deadline === null && !empty($day['finalized_at'])) {
            $deadline = $this->addWorkingDays(date('Y-m-d', strtotime($day['finalized_at'])), 7);
        }
        $out['deadline'] = $deadline;
        if ($isReviewed) {
            $out['status'] = 'closed_reviewed';
            $out['label'] = 'Reviewed';
            return $out;
        }
        $today = date('Y-m-d');
        if ($deadline === null) {
            $out['status'] = 'open';
            $out['label'] = 'Open for review';
            return $out;
        }
        if ($today > $deadline) {
            $out['status'] = 'overdue';
            $out['days_left'] = 0;
            $out['label'] = 'Overdue (was due ' . date('d-M-Y', strtotime($deadline)) . ')';
            return $out;
        }
        $out['status'] = 'open';
        $out['days_left'] = $this->countWorkingDaysInclusive($today, $deadline);
        $out['label'] = 'Review by ' . date('d-M-Y', strtotime($deadline))
            . ' (' . (int) $out['days_left'] . ' working day' . ((int) $out['days_left'] === 1 ? '' : 's') . ' left)';
        return $out;
    }

    /**
     * @return bool
     */
    public function isTodayWorkDayFinal($workDate, $branchID, $classID, $sectionID)
    {
        if (!$this->db->table_exists('digital_diary_today_work_day')) {
            return false;
        }
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }
        $sessionID = (int) get_session_id();
        $this->db->where(array(
            'branch_id' => (int) $branchID,
            'session_id' => $sessionID,
            'work_date' => $workDate,
            'class_id' => (int) $classID,
            'section_id' => (int) $sectionID,
            'is_final' => 1,
        ));
        return $this->db->count_all_results('digital_diary_today_work_day') > 0;
    }

    /**
     * @return bool
     */
    public function setTodayWorkDayFinal($workDate, $branchID, $classID, $sectionID, $staffID)
    {
        if (!$this->db->table_exists('digital_diary_today_work_day')) {
            return false;
        }
        $this->ensureTodayWorkDayReviewDeadlineColumn();
        $sessionID = (int) get_session_id();
        $finalizedAt = date('Y-m-d H:i:s');
        $deadline = $this->addWorkingDays(date('Y-m-d', strtotime($finalizedAt)), 7);
        $row = array(
            'branch_id' => (int) $branchID,
            'session_id' => $sessionID,
            'work_date' => $workDate,
            'class_id' => (int) $classID,
            'section_id' => (int) $sectionID,
            'is_final' => 1,
            'staff_id' => (int) $staffID,
            'finalized_at' => $finalizedAt,
            'review_deadline' => $deadline,
        );
        $this->db->replace('digital_diary_today_work_day', $row);
        return true;
    }

    /**
     * Remove final-submit lock for this date / class / section (allow editing again).
     *
     * @return bool
     */
    public function clearTodayWorkDayFinal($workDate, $branchID, $classID, $sectionID)
    {
        if (!$this->db->table_exists('digital_diary_today_work_day')) {
            return false;
        }
        if (empty($branchID)) {
            $branchID = get_loggedin_branch_id();
        }
        $sessionID = (int) get_session_id();
        $this->db->where(array(
            'branch_id' => (int) $branchID,
            'session_id' => $sessionID,
            'work_date' => $workDate,
            'class_id' => (int) $classID,
            'section_id' => (int) $sectionID,
        ));
        $this->db->delete('digital_diary_today_work_day');
        return true;
    }

    /**
     * Active students in class/section for current session & branch.
     *
     * @return array<int,array{student_id:int,fullname:string,roll:mixed}>
     */
    public function getStudentsForStudentWorkReport($branchID, $classID, $sectionID)
    {
        if (empty($branchID) || empty($classID) || empty($sectionID)) {
            return array();
        }
        $sessionID = (int) get_session_id();
        $this->db->select('s.id as student_id, s.first_name, s.last_name, e.roll');
        $this->db->from('enroll e');
        $this->db->join('student s', 's.id = e.student_id', 'inner');
        $this->db->where('e.branch_id', (int) $branchID);
        $this->db->where('e.session_id', $sessionID);
        $this->db->where('e.class_id', (int) $classID);
        $this->db->where('e.section_id', (int) $sectionID);
        $this->db->where('s.active', 1);
        $this->db->order_by('e.roll', 'ASC');
        $this->db->order_by('s.first_name', 'ASC');
        $this->db->order_by('s.last_name', 'ASC');
        $rows = $this->db->get()->result_array();
        $out = array();
        foreach ($rows as $r) {
            $fn = isset($r['first_name']) ? trim((string) $r['first_name']) : '';
            $ln = isset($r['last_name']) ? trim((string) $r['last_name']) : '';
            $out[] = array(
                'student_id' => (int) $r['student_id'],
                'fullname' => trim($fn . ' ' . $ln),
                'roll' => isset($r['roll']) ? $r['roll'] : '',
            );
        }
        return $out;
    }

    /**
     * Attendance map keyed by student_id for a date/class/section.
     *
     * @param string $workDate Y-m-d
     * @return array<int,array{status:string,remark:string,discipline_dress:int,discipline_conduct:int}>
     */
    public function getStudentAttendanceMapForDate($workDate, $branchID, $classID, $sectionID)
    {
        if (empty($workDate) || empty($branchID) || empty($classID) || empty($sectionID)) {
            return array();
        }
        $sessionID = (int) get_session_id();
        $sel = 'e.student_id, sa.status, sa.remark';
        if ($this->db->field_exists('discipline_dress', 'student_attendance')) {
            $sel .= ', sa.discipline_dress';
        }
        if ($this->db->field_exists('discipline_conduct', 'student_attendance')) {
            $sel .= ', sa.discipline_conduct';
        }
        $this->db->select($sel);
        $this->db->from('student_attendance sa');
        $this->db->join('enroll e', 'e.id = sa.enroll_id', 'inner');
        $this->db->where('sa.branch_id', (int) $branchID);
        $this->db->where('sa.date', $workDate);
        $this->db->where('e.branch_id', (int) $branchID);
        $this->db->where('e.session_id', $sessionID);
        $this->db->where('e.class_id', (int) $classID);
        $this->db->where('e.section_id', (int) $sectionID);
        $rows = $this->db->get()->result_array();
        $map = array();
        foreach ($rows as $row) {
            $status = isset($row['status']) ? strtoupper(trim((string) $row['status'])) : '';
            $defaultOk = ($status === '' || $status === 'P' || $status === 'L' || $status === 'HD') ? 1 : 0;
            $map[(int) $row['student_id']] = array(
                'status' => isset($row['status']) ? (string) $row['status'] : '',
                'remark' => isset($row['remark']) ? (string) $row['remark'] : '',
                'discipline_dress' => array_key_exists('discipline_dress', $row) && $row['discipline_dress'] !== null
                    ? (!empty($row['discipline_dress']) ? 1 : 0)
                    : $defaultOk,
                'discipline_conduct' => array_key_exists('discipline_conduct', $row) && $row['discipline_conduct'] !== null
                    ? (!empty($row['discipline_conduct']) ? 1 : 0)
                    : $defaultOk,
            );
        }
        return $map;
    }

    /**
     * Update or create attendance discipline flags for one student on a date.
     */
    public function updateStudentAttendanceDiscipline($workDate, $branchID, $classID, $sectionID, $studentID, $dress, $conduct)
    {
        if (
            empty($workDate) ||
            empty($branchID) ||
            !$this->db->table_exists('student_attendance') ||
            !$this->db->field_exists('discipline_dress', 'student_attendance')
        ) {
            return false;
        }
        $sessionID = (int) get_session_id();
        $this->db->select('e.id as enroll_id, sa.id as attendance_id');
        $this->db->from('enroll e');
        $this->db->join(
            'student_attendance sa',
            'sa.enroll_id = e.id AND sa.date = ' . $this->db->escape($workDate) . ' AND sa.branch_id = ' . (int) $branchID,
            'left'
        );
        $this->db->where('e.student_id', (int) $studentID);
        $this->db->where('e.branch_id', (int) $branchID);
        $this->db->where('e.session_id', $sessionID);
        $this->db->where('e.class_id', (int) $classID);
        $this->db->where('e.section_id', (int) $sectionID);
        $row = $this->db->get()->row_array();
        if (empty($row['enroll_id'])) {
            return false;
        }
        $data = array(
            'discipline_dress' => !empty($dress) ? 1 : 0,
        );
        if ($this->db->field_exists('discipline_conduct', 'student_attendance')) {
            $data['discipline_conduct'] = !empty($conduct) ? 1 : 0;
        }
        if (!empty($row['attendance_id'])) {
            $this->db->where('id', (int) $row['attendance_id']);
            $this->db->update('student_attendance', $data);
            return true;
        }
        $insert = array(
            'enroll_id' => (int) $row['enroll_id'],
            'branch_id' => (int) $branchID,
            'date' => $workDate,
            'status' => 'P',
        );
        $insert = array_merge($insert, $data);
        $this->db->insert('student_attendance', $insert);
        return true;
    }

    /**
     * Whether Student Work Report has been submitted for this date/class/section.
     *
     * @param string $workDate Y-m-d
     */
    public function hasStudentWorkReportForDay($workDate, $branchID, $classID, $sectionID)
    {
        if (
            !$this->db->table_exists('digital_diary_student_work_report') ||
            empty($workDate) ||
            (int) $branchID < 1 ||
            (int) $classID < 1 ||
            (int) $sectionID < 1
        ) {
            return false;
        }
        $this->db->select('id');
        $this->db->from('digital_diary_student_work_report');
        $this->db->where('branch_id', (int) $branchID);
        $this->db->where('session_id', (int) get_session_id());
        $this->db->where('work_date', $workDate);
        $this->db->where('class_id', (int) $classID);
        $this->db->where('section_id', (int) $sectionID);
        $this->db->limit(1);
        $q = $this->db->get();
        return ($q && $q->num_rows() > 0);
    }

    /**
     * @param string $workDate Y-m-d
     * @return array<int,array> keyed by student_id
     */
    public function getStudentWorkReportSavedMap($workDate, $branchID, $classID, $sectionID, $subjectID)
    {
        if (
            !$this->db->table_exists('digital_diary_student_work_report') ||
            empty($workDate) ||
            empty($branchID) ||
            empty($classID) ||
            empty($sectionID) ||
            empty($subjectID)
        ) {
            return array();
        }
        $sessionID = (int) get_session_id();
        $this->db->where('branch_id', (int) $branchID);
        $this->db->where('session_id', $sessionID);
        $this->db->where('work_date', $workDate);
        $this->db->where('class_id', (int) $classID);
        $this->db->where('section_id', (int) $sectionID);
        $this->db->where('subject_id', (int) $subjectID);
        $q = $this->db->get('digital_diary_student_work_report');
        $map = array();
        foreach ($q->result_array() as $row) {
            $map[(int) $row['student_id']] = $row;
        }
        return $map;
    }

    /**
     * Overview stats for Fair / Writing / Learning work — only students with a saved row (submitted).
     *
     * @param array<int,array> $savedMap student_id => row from getStudentWorkReportSavedMap values only
     * @return array{total_submitted:int,rows:array<int,array{label:string,p_student:int,complete:int,uncomplete:int,com_pct:int,uncom_pct:int}>}|null
     */
    public function summarizeStudentWorkReportOverview(array $savedMap)
    {
        if (empty($savedMap)) {
            return null;
        }
        $defs = array(
            array('label' => 'Fair Work', 'field' => 'fair_copy'),
            array('label' => 'Writing Work', 'field' => 'writing_work'),
            array('label' => 'Learning Work', 'field' => 'learning_work'),
        );
        $n = count($savedMap);
        $rows = array();
        foreach ($defs as $def) {
            $complete = 0;
            foreach ($savedMap as $row) {
                if (!empty($row[$def['field']])) {
                    $complete++;
                }
            }
            $uncomplete = $n - $complete;
            $rows[] = array(
                'label' => $def['label'],
                'p_student' => $n,
                'complete' => $complete,
                'uncomplete' => $uncomplete,
                'com_pct' => $n > 0 ? (int) round($complete * 100 / $n) : 0,
                'uncom_pct' => $n > 0 ? (int) round($uncomplete * 100 / $n) : 0,
            );
        }
        return array(
            'total_submitted' => $n,
            'rows' => $rows,
        );
    }

    /**
     * Work % for Today's Work table: average of Fair / Writing / Learning "Com %" (same as Student Work Report overview).
     *
     * @param array<int,array> $savedMap from getStudentWorkReportSavedMap
     * @return string e.g. "67 %" or "—"
     */
    public function getStudentWorkReportWorkPercentageFromSavedMap(array $savedMap)
    {
        if (!$this->db->table_exists('digital_diary_student_work_report')) {
            return '—';
        }
        $ov = $this->summarizeStudentWorkReportOverview($savedMap);
        if ($ov === null || empty($ov['rows'])) {
            return '—';
        }
        $sum = 0;
        $n = 0;
        foreach ($ov['rows'] as $r) {
            $sum += (int) $r['com_pct'];
            $n++;
        }
        if ($n === 0) {
            return '—';
        }
        return (int) round($sum / $n) . ' %';
    }

    /**
     * Replace all rows for this date/class/section/subject with posted data.
     *
     * @param array<int,array> $swrByStudent keyed by student_id (dress/conduct when not using preserve map)
     * @param array<int>       $validStudentIds
     * @param array<int,array>|null $preserveDisciplineFromMap prior rows keyed by student_id (subject-teacher saves; keeps dress/conduct)
     * @return bool
     */
    public function replaceStudentWorkReportBatch(
        $workDate,
        $branchID,
        $classID,
        $sectionID,
        $subjectID,
        $staffID,
        $swrByStudent,
        $validStudentIds,
        $preserveDisciplineFromMap = null
    ) {
        if (!$this->db->table_exists('digital_diary_student_work_report')) {
            return false;
        }
        $sessionID = (int) get_session_id();
        $this->db->where('branch_id', (int) $branchID);
        $this->db->where('session_id', $sessionID);
        $this->db->where('work_date', $workDate);
        $this->db->where('class_id', (int) $classID);
        $this->db->where('section_id', (int) $sectionID);
        $this->db->where('subject_id', (int) $subjectID);
        $this->db->delete('digital_diary_student_work_report');

        foreach ($validStudentIds as $studentID) {
            $studentID = (int) $studentID;
            $r = isset($swrByStudent[$studentID]) ? $swrByStudent[$studentID] : array();
            if ($preserveDisciplineFromMap !== null) {
                if (isset($preserveDisciplineFromMap[$studentID])) {
                    $pe = $preserveDisciplineFromMap[$studentID];
                    $dress = !empty($pe['discipline_dress']);
                    $conduct = !empty($pe['discipline_conduct']);
                } else {
                    $dress = true;
                    $conduct = true;
                }
            } else {
                $dress = !empty($r['dress']);
                $conduct = !empty($r['conduct']);
            }
            $row = array(
                'branch_id' => (int) $branchID,
                'session_id' => $sessionID,
                'staff_id' => (int) $staffID,
                'work_date' => $workDate,
                'class_id' => (int) $classID,
                'section_id' => (int) $sectionID,
                'subject_id' => (int) $subjectID,
                'student_id' => $studentID,
                'discipline_dress' => $dress ? 1 : 0,
                'discipline_conduct' => $conduct ? 1 : 0,
                'fair_copy' => !empty($r['fair_copy']) ? 1 : 0,
                'writing_work' => !empty($r['writing_work']) ? 1 : 0,
                'learning_work' => !empty($r['learning_work']) ? 1 : 0,
                'remarks' => isset($r['remarks']) ? substr(trim((string) $r['remarks']), 0, 500) : null,
            );
            $this->db->insert('digital_diary_student_work_report', $row);
        }
        return true;
    }

}
