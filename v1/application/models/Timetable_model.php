<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Timetable_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Insert or update a single timetable_class period.
     *
     * @return array{status:bool,id:int,message:string}
     */
    public function save_class_period($data)
    {
        $id = (int) (isset($data['id']) ? $data['id'] : 0);
        $branchID = (int) (isset($data['branch_id']) ? $data['branch_id'] : 0);
        $classID = (int) (isset($data['class_id']) ? $data['class_id'] : 0);
        $sectionID = (int) (isset($data['section_id']) ? $data['section_id'] : 0);
        $day = strtolower(trim((string) (isset($data['day']) ? $data['day'] : '')));
        $sessionID = (int) get_session_id();
        $isBreak = !empty($data['break']);
        $validDays = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');

        if ($branchID < 1 || $classID < 1 || $sectionID < 1 || !in_array($day, $validDays, true)) {
            return array('status' => false, 'id' => 0, 'message' => 'Invalid class / day details.');
        }

        if (empty($data['time_start']) || empty($data['time_end'])) {
            return array('status' => false, 'id' => 0, 'message' => 'Starting and ending time are required.');
        }
        $timeStart = date('H:i:s', strtotime($data['time_start']));
        $timeEnd = date('H:i:s', strtotime($data['time_end']));

        $row = array(
            'class_id' => $classID,
            'section_id' => $sectionID,
            'subject_id' => $isBreak ? 0 : (int) (isset($data['subject_id']) ? $data['subject_id'] : 0),
            'teacher_id' => $isBreak ? 0 : (int) (isset($data['teacher_id']) ? $data['teacher_id'] : 0),
            'time_start' => $timeStart,
            'time_end' => $timeEnd,
            'class_room' => isset($data['class_room']) ? (string) $data['class_room'] : '',
            'session_id' => $sessionID,
            'branch_id' => $branchID,
            'break' => $isBreak ? 1 : 0,
            'day' => $day,
        );

        if (!$isBreak && ($row['subject_id'] < 1 || $row['teacher_id'] < 1)) {
            return array('status' => false, 'id' => 0, 'message' => 'Subject and teacher are required.');
        }

        if ($id > 0) {
            $existing = $this->db->get_where('timetable_class', array(
                'id' => $id,
                'class_id' => $classID,
                'section_id' => $sectionID,
                'branch_id' => $branchID,
                'session_id' => $sessionID,
            ))->row();
            if (empty($existing)) {
                return array('status' => false, 'id' => 0, 'message' => 'Period not found.');
            }
            $this->db->where('id', $id);
            $this->db->update('timetable_class', $row);
            return array('status' => true, 'id' => $id, 'message' => translate('information_has_been_updated_successfully'));
        }

        $this->db->insert('timetable_class', $row);
        return array(
            'status' => true,
            'id' => (int) $this->db->insert_id(),
            'message' => translate('information_has_been_saved_successfully'),
        );
    }

    /**
     * Delete a single timetable_class period.
     *
     * @return array{status:bool,message:string}
     */
    public function delete_class_period($id, $classID, $sectionID, $branchID)
    {
        $id = (int) $id;
        $classID = (int) $classID;
        $sectionID = (int) $sectionID;
        $branchID = (int) $branchID;
        if ($id < 1) {
            return array('status' => false, 'message' => 'Invalid period.');
        }
        $this->db->where('id', $id);
        $this->db->where('class_id', $classID);
        $this->db->where('section_id', $sectionID);
        $this->db->where('branch_id', $branchID);
        $this->db->where('session_id', (int) get_session_id());
        $this->db->delete('timetable_class');
        if ($this->db->affected_rows() > 0) {
            return array('status' => true, 'message' => translate('information_has_been_deleted_successfully'));
        }
        return array('status' => false, 'message' => 'Period not found.');
    }

    // class wise information save
    public function classwise_save($data)
    {
        $branchID   = $this->application_model->get_branch_id();
        $sectionID  = $data['section_id'];
        $classID    = $data['class_id'];
        $sessionID  = get_session_id();
        $day        = $data['day'];
        $arrayItems = $this->input->post('timetable');
        if (!empty($arrayItems)) {
            foreach ($arrayItems as $key => $value) {
                if (!isset($value['break'])) {
                    $subjectID  = $value['subject'];
                    $teacherID  = $value['teacher'];
                    $break      = false;
                } else {
                    $subjectID  = 0;
                    $teacherID  = 0;
                    $break      = true;
                }
                $timeStart = date("H:i:s", strtotime($value['time_start']));
                $timeEnd = date("H:i:s", strtotime($value['time_end']));
                $roomNumber = $value['class_room'];
                if (!empty($timeStart) && !empty($timeEnd)) {
                    $arrayRoutine = array(
                        'class_id'      => $classID,
                        'section_id'    => $sectionID,
                        'subject_id'    => $subjectID,
                        'teacher_id'    => $teacherID,
                        'time_start'    => $timeStart,
                        'time_end'      => $timeEnd,
                        'class_room'    => $roomNumber,
                        'session_id'    => $sessionID,
                        'branch_id'     => $branchID,
                        'break'         => $break,
                        'day'           => $day,
                    );
                    if ($data['old_id'][$key] == 0) {
                        $this->db->insert('timetable_class', $arrayRoutine);
                    } else {
                        $this->db->where('id', $data['old_id'][$key]);
                        $this->db->update('timetable_class', $arrayRoutine);
                    }
                }
            }
        }
    
        $arrayI = (isset($data['i'])) ? $data['i'] : array();
        $preserve_array = (isset($data['old_id'])) ? $data['old_id'] : array();
        $deleteArray = array_diff($arrayI, $preserve_array);
        if (!empty($deleteArray)) {
            $this->db->where_in('id', $deleteArray);
            $this->db->delete('timetable_class');
        }
        
    }

    public function getExamTimetableList($classID, $sectionID, $branchID)
    {
        $sessionID = get_session_id();
        $this->db->select('t.*,b.name as branch_name');
        $this->db->from('timetable_exam as t');
        $this->db->join('branch as b', 'b.id = t.branch_id', 'left');
        $this->db->where('t.branch_id', $branchID);
        $this->db->where('t.class_id', $classID);
        $this->db->where('t.section_id', $sectionID);
        $this->db->where('t.session_id', $sessionID);
        $this->db->order_by('t.id', 'asc');
        $this->db->group_by('t.exam_id');
        return $this->db->get()->result_array();
    }

    public function getSubjectExam($classID, $sectionID, $examID, $branchID)
    {
        $sessionID  = get_session_id();
        $sql = "SELECT sa.*, s.name as subject_name, te.time_start, te.time_end, te.hall_id, te.exam_date, te.mark_distribution FROM subject_assign as sa
        LEFT JOIN subject as s ON s.id = sa.subject_id LEFT JOIN timetable_exam as te ON te.class_id = sa.class_id and te.section_id = sa.section_id and
        te.subject_id = sa.subject_id and te.session_id = sa.session_id and te.exam_id = " . $this->db->escape($examID) . " WHERE sa.class_id = " .
        $this->db->escape($classID) . " AND sa.section_id = " . $this->db->escape($sectionID) . " AND sa.branch_id = " .
        $this->db->escape($branchID) . " AND sa.session_id = " . $this->db->escape($sessionID);
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function getExamTimetableByModal($examID, $classID, $sectionID, $branchID = '')
    {
        $sessionID = get_session_id();
        $this->db->select('t.*,s.name as subject_name,eh.hall_no');
        $this->db->from('timetable_exam as t');
        $this->db->join('subject as s', 's.id = t.subject_id', 'left');
        $this->db->join('exam_hall as eh', 'eh.id = t.hall_id', 'left');
        if (!empty($branchID)) {
            $this->db->where('t.branch_id', $branchID);
        } else {
            if (!is_superadmin_loggedin()) {
                $this->db->where('t.branch_id', get_loggedin_branch_id());
            }
        }
        $this->db->where('t.exam_id', $examID);
        $this->db->where('t.class_id', $classID);
        $this->db->where('t.section_id', $sectionID);
        $this->db->where('t.session_id', $sessionID);
        return $this->db->get();
    }

    public function getTeacherTimetable($teacherID = '', $branchID = '')
    {
        $sessionID = get_session_id();
        $this->db->select('t.*,s.name as subject_name,s.name as subject_name,s.subject_code,c.name as class_name,sc.name as section_name');
        $this->db->from('timetable_class as t');
        $this->db->join('subject as s', 's.id = t.subject_id', 'left');
        $this->db->join('class as c', 'c.id = t.class_id', 'left');
        $this->db->join('section as sc', 'sc.id = t.section_id', 'left');
        $this->db->where('t.branch_id', $branchID);
        $this->db->where('t.teacher_id', $teacherID);
        $this->db->where('t.session_id', get_session_id());
        $this->db->order_by('t.time_start', 'asc');
        return $this->db->get()->result();
    }

    /**
     * Copy one teacher's timetable_class rows.
     * - day: from weekday → to weekday (same session)
     * - year: all days from one academic session → another
     *
     * @return array{copied:int,message:string}
     */
    public function copy_teacher_schedule($teacherID, $branchID, $mode, $options = array())
    {
        $teacherID = (int) $teacherID;
        $branchID = (int) $branchID;
        $replace = !empty($options['replace']);
        $validDays = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');

        if ($teacherID < 1 || $branchID < 1) {
            return array('copied' => 0, 'message' => 'Invalid teacher or branch.');
        }

        if ($mode === 'year') {
            $fromSession = (int) (isset($options['from_session_id']) ? $options['from_session_id'] : 0);
            $toSession = (int) (isset($options['to_session_id']) ? $options['to_session_id'] : 0);
            if ($fromSession < 1 || $toSession < 1) {
                return array('copied' => 0, 'message' => 'Select source and target academic year.');
            }
            if ($fromSession === $toSession) {
                return array('copied' => 0, 'message' => 'Source and target academic year must be different.');
            }

            $this->db->where('teacher_id', $teacherID);
            $this->db->where('branch_id', $branchID);
            $this->db->where('session_id', $fromSession);
            $rows = $this->db->get('timetable_class')->result_array();
            if (empty($rows)) {
                return array('copied' => 0, 'message' => 'No schedule found in the source academic year.');
            }

            if ($replace) {
                $this->db->where('teacher_id', $teacherID);
                $this->db->where('branch_id', $branchID);
                $this->db->where('session_id', $toSession);
                $this->db->delete('timetable_class');
            }

            $copied = 0;
            foreach ($rows as $row) {
                unset($row['id']);
                $row['session_id'] = $toSession;
                $row['branch_id'] = $branchID;
                $row['teacher_id'] = $teacherID;
                $this->db->insert('timetable_class', $row);
                $copied++;
            }
            return array('copied' => $copied, 'message' => $copied . ' period(s) copied to the selected academic year.');
        }

        // day / date modes → weekday copy
        $fromDay = strtolower(trim((string) (isset($options['from_day']) ? $options['from_day'] : '')));
        $toDay = strtolower(trim((string) (isset($options['to_day']) ? $options['to_day'] : '')));
        if (!in_array($fromDay, $validDays, true) || !in_array($toDay, $validDays, true)) {
            return array('copied' => 0, 'message' => 'Select a valid source and target day.');
        }
        if ($fromDay === $toDay) {
            return array('copied' => 0, 'message' => 'Source and target day must be different.');
        }

        $sessionID = (int) (isset($options['session_id']) ? $options['session_id'] : get_session_id());
        $this->db->where('teacher_id', $teacherID);
        $this->db->where('branch_id', $branchID);
        $this->db->where('session_id', $sessionID);
        $this->db->where('day', $fromDay);
        $rows = $this->db->get('timetable_class')->result_array();
        if (empty($rows)) {
            return array('copied' => 0, 'message' => 'No schedule found on the source day for this teacher.');
        }

        if ($replace) {
            $classSections = array();
            foreach ($rows as $row) {
                $classSections[(int) $row['class_id'] . '-' . (int) $row['section_id']] = array(
                    (int) $row['class_id'],
                    (int) $row['section_id'],
                );
            }
            foreach ($classSections as $pair) {
                $this->db->where('teacher_id', $teacherID);
                $this->db->where('branch_id', $branchID);
                $this->db->where('session_id', $sessionID);
                $this->db->where('day', $toDay);
                $this->db->where('class_id', $pair[0]);
                $this->db->where('section_id', $pair[1]);
                $this->db->delete('timetable_class');
            }
        }

        $copied = 0;
        foreach ($rows as $row) {
            unset($row['id']);
            $row['day'] = $toDay;
            $row['session_id'] = $sessionID;
            $row['branch_id'] = $branchID;
            $row['teacher_id'] = $teacherID;
            $this->db->insert('timetable_class', $row);
            $copied++;
        }
        return array('copied' => $copied, 'message' => $copied . ' period(s) copied from ' . ucfirst($fromDay) . ' to ' . ucfirst($toDay) . '.');
    }

    /**
     * Copy a class/section timetable_class rows.
     * - day: from weekday → to weekday (same session)
     * - year: all days from one academic session → another
     *
     * @return array{copied:int,message:string}
     */
    public function copy_class_schedule($classID, $sectionID, $branchID, $mode, $options = array())
    {
        $classID = (int) $classID;
        $sectionID = (int) $sectionID;
        $branchID = (int) $branchID;
        $replace = !empty($options['replace']);
        $validDays = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');

        if ($classID < 1 || $sectionID < 1 || $branchID < 1) {
            return array('copied' => 0, 'message' => 'Invalid class, section or branch.');
        }

        if ($mode === 'year') {
            $fromSession = (int) (isset($options['from_session_id']) ? $options['from_session_id'] : 0);
            $toSession = (int) (isset($options['to_session_id']) ? $options['to_session_id'] : 0);
            if ($fromSession < 1 || $toSession < 1) {
                return array('copied' => 0, 'message' => 'Select source and target academic year.');
            }
            if ($fromSession === $toSession) {
                return array('copied' => 0, 'message' => 'Source and target academic year must be different.');
            }

            $this->db->where('class_id', $classID);
            $this->db->where('section_id', $sectionID);
            $this->db->where('branch_id', $branchID);
            $this->db->where('session_id', $fromSession);
            $rows = $this->db->get('timetable_class')->result_array();
            if (empty($rows)) {
                return array('copied' => 0, 'message' => 'No schedule found in the source academic year.');
            }

            if ($replace) {
                $this->db->where('class_id', $classID);
                $this->db->where('section_id', $sectionID);
                $this->db->where('branch_id', $branchID);
                $this->db->where('session_id', $toSession);
                $this->db->delete('timetable_class');
            }

            $copied = 0;
            foreach ($rows as $row) {
                unset($row['id']);
                $row['session_id'] = $toSession;
                $row['class_id'] = $classID;
                $row['section_id'] = $sectionID;
                $row['branch_id'] = $branchID;
                $this->db->insert('timetable_class', $row);
                $copied++;
            }
            return array('copied' => $copied, 'message' => $copied . ' period(s) copied to the selected academic year.');
        }

        $fromDay = strtolower(trim((string) (isset($options['from_day']) ? $options['from_day'] : '')));
        $toDay = strtolower(trim((string) (isset($options['to_day']) ? $options['to_day'] : '')));
        if (!in_array($fromDay, $validDays, true) || !in_array($toDay, $validDays, true)) {
            return array('copied' => 0, 'message' => 'Select a valid source and target day.');
        }
        if ($fromDay === $toDay) {
            return array('copied' => 0, 'message' => 'Source and target day must be different.');
        }

        $sessionID = (int) (isset($options['session_id']) ? $options['session_id'] : get_session_id());
        $this->db->where('class_id', $classID);
        $this->db->where('section_id', $sectionID);
        $this->db->where('branch_id', $branchID);
        $this->db->where('session_id', $sessionID);
        $this->db->where('day', $fromDay);
        $rows = $this->db->get('timetable_class')->result_array();
        if (empty($rows)) {
            return array('copied' => 0, 'message' => 'No schedule found on the source day for this class.');
        }

        if ($replace) {
            $this->db->where('class_id', $classID);
            $this->db->where('section_id', $sectionID);
            $this->db->where('branch_id', $branchID);
            $this->db->where('session_id', $sessionID);
            $this->db->where('day', $toDay);
            $this->db->delete('timetable_class');
        }

        $copied = 0;
        foreach ($rows as $row) {
            unset($row['id']);
            $row['day'] = $toDay;
            $row['session_id'] = $sessionID;
            $row['class_id'] = $classID;
            $row['section_id'] = $sectionID;
            $row['branch_id'] = $branchID;
            $this->db->insert('timetable_class', $row);
            $copied++;
        }
        return array('copied' => $copied, 'message' => $copied . ' period(s) copied from ' . ucfirst($fromDay) . ' to ' . ucfirst($toDay) . '.');
    }
}
