<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Subject_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }
    
    // get subjects assign list
    public function getAssignList()
    {
        $this->db->select('sa.class_id,sa.section_id,sa.branch_id,b.name as branch_name,c.name as class_name,s.name as section_name');
        $this->db->from('subject_assign as sa');
        $this->db->join('branch as b', 'b.id = sa.branch_id', 'left');
        $this->db->join('class as c', 'c.id = sa.class_id', 'left');
        $this->db->join('section as s', 's.id = sa.section_id', 'left');
        $this->db->group_by(array('sa.class_id', 'sa.section_id', 'sa.branch_id'));
        $this->db->where('sa.session_id', get_session_id());
        if (!is_superadmin_loggedin()) {
            $this->db->where('sa.branch_id', get_loggedin_branch_id());
        }
        $result = $this->db->get()->result_array();
        return $result;
    }

    // get subject list by class id and section id
    public function get_subject_list($class_id = '', $section_id = '')
    {
        $this->db->select('sa.subject_id,s.name');
        $this->db->from('subject_assign as sa');
        $this->db->join('subject as s', 's.id = sa.subject_id', 'left');
        $this->db->where('sa.class_id', $class_id);
        $this->db->where('sa.section_id', $section_id);
        $this->db->where('sa.session_id', get_session_id());
        $subjects = $this->db->get()->result();
        $name_list = '';
        foreach ($subjects as $row) {
            $name_list .= '- ' . $row->name . '<br>';
        }
        return $name_list;
    }

    // get teacher assign list
    public function getTeacherAssignList()
    {
        $sql = "SELECT sa.*, c.name as class_name, s.name as section_name, sb.name as subject_name, t.name as teacher_name, t.department, sd.name as department_name FROM subject_assign as sa LEFT JOIN class as c ON c.id = sa.class_id LEFT JOIN section as s ON s.id = sa.section_id LEFT JOIN subject as sb ON sb.id = sa.subject_id LEFT JOIN staff as t ON t.id = sa.teacher_id LEFT JOIN staff_department as sd ON sd.id = t.department WHERE sa.teacher_id != 0";
        if (!is_superadmin_loggedin()) {
            $sql .= " AND sa.branch_id = " . $this->db->escape(get_loggedin_branch_id());
        }
        $sql .= " ORDER BY sa.id ASC";
        $result = $this->db->query($sql)->result();
        return $result;
    }

    /**
     * All class/section/subject assignments for a specific teacher in current session.
     *
     * @param int      $teacherID
     * @param int|null $branchID
     * @return array<int,array{class_id:int,section_id:int,subject_id:int,class_name:string,section_name:string,subject_name:string}>
     */
    public function getAssignmentsByTeacher($teacherID, $branchID = null)
    {
        $teacherID = (int) $teacherID;
        if ($teacherID <= 0) {
            return array();
        }
        // Primary source: subject_assign with explicit teacher_id.
        $this->db->select('sa.class_id, sa.section_id, sa.subject_id, c.name as class_name, s.name as section_name, sb.name as subject_name');
        $this->db->from('subject_assign as sa');
        $this->db->join('class as c', 'c.id = sa.class_id', 'left');
        $this->db->join('section as s', 's.id = sa.section_id', 'left');
        $this->db->join('subject as sb', 'sb.id = sa.subject_id', 'left');
        $this->db->where('sa.teacher_id', $teacherID);
        $this->db->where('sa.session_id', get_session_id());
        if ($branchID !== null && (int) $branchID > 0) {
            $this->db->where('sa.branch_id', (int) $branchID);
        }
        $assignments = $this->db->get()->result_array();

        // Fallback: if no explicit subject_assign rows, use timetable_class subjects taught by this teacher.
        if (empty($assignments)) {
            $this->db->select('tc.class_id, tc.section_id, tc.subject_id, c.name as class_name, s.name as section_name, sb.name as subject_name');
            $this->db->from('timetable_class as tc');
            $this->db->join('class as c', 'c.id = tc.class_id', 'left');
            $this->db->join('section as s', 's.id = tc.section_id', 'left');
            $this->db->join('subject as sb', 'sb.id = tc.subject_id', 'left');
            $this->db->where('tc.teacher_id', $teacherID);
            $this->db->where('tc.session_id', get_session_id());
            if ($branchID !== null && (int) $branchID > 0) {
                $this->db->where('tc.branch_id', (int) $branchID);
            }
            $this->db->group_by(array('tc.class_id', 'tc.section_id', 'tc.subject_id'));
            $assignments = $this->db->get()->result_array();
        }

        if (empty($assignments)) {
            return array();
        }

        // Normalize and sort.
        $out = array();
        foreach ($assignments as $a) {
            $out[] = array(
                'class_id' => (int) $a['class_id'],
                'section_id' => (int) $a['section_id'],
                'subject_id' => (int) $a['subject_id'],
                'class_name' => isset($a['class_name']) ? $a['class_name'] : '',
                'section_name' => isset($a['section_name']) ? $a['section_name'] : '',
                'subject_name' => isset($a['subject_name']) ? $a['subject_name'] : '',
            );
        }
        usort($out, function ($a, $b) {
            $ac = strcasecmp($a['class_name'], $b['class_name']);
            if ($ac !== 0) return $ac;
            $as = strcasecmp($a['section_name'], $b['section_name']);
            if ($as !== 0) return $as;
            return strcasecmp($a['subject_name'], $b['subject_name']);
        });
        return $out;
    }

    public function getSubjectByClassSection($classID = '', $sectionID = '')
    {
        if (loggedin_role_id() == 3) {
            // Teacher should see subjects assigned through subject_assign.
            $this->db->select('subject_assign.subject_id, subject.name as subjectname, subject.subject_code');
            $this->db->from('subject_assign');
            $this->db->join('subject', 'subject.id = subject_assign.subject_id', 'left');
            $this->db->where('subject_assign.class_id', $classID);
            $this->db->where('subject_assign.section_id', $sectionID);
            $this->db->where('subject_assign.session_id', get_session_id());
            $this->db->where('subject_assign.teacher_id', get_loggedin_user_id());
            if (!is_superadmin_loggedin()) {
                $this->db->where('subject_assign.branch_id', get_loggedin_branch_id());
            }
            $query = $this->db->get();
            // Fallback: if no explicit subject_assign rows, use timetable subjects assigned to this teacher.
            if ($query->num_rows() == 0) {
                $this->db->select('timetable_class.subject_id, subject.name as subjectname, subject.subject_code');
                $this->db->from('timetable_class');
                $this->db->join('subject', 'subject.id = timetable_class.subject_id', 'left');
                $this->db->where('timetable_class.teacher_id', get_loggedin_user_id());
                $this->db->where('timetable_class.session_id', get_session_id());
                $this->db->where('timetable_class.class_id', $classID);
                $this->db->where('timetable_class.section_id', $sectionID);
                $this->db->group_by('timetable_class.subject_id');
                $query = $this->db->get();
            }
        } else {
            $query = $this->getSubjectList($classID, $sectionID);
        }
        return $query;
    }


    public function getSubjectList($classID = '', $sectionID = '')
    {
        $this->db->select('subject_assign.subject_id, subject.name as subjectname,subject.subject_code');
        $this->db->from('subject_assign');
        $this->db->join('subject', 'subject.id = subject_assign.subject_id', 'left');
        $this->db->where('class_id', $classID);
        $this->db->where('section_id', $sectionID);
        $this->db->where('session_id', get_session_id());
        $query = $this->db->get();
        return $query;
    }

    /**
     * All subjects for class/section (for grids that list every subject, e.g. Student Work Report).
     *
     * @param int $branchID optional branch filter (non–super-admin)
     * @return CI_DB_result
     */
    public function getAllSubjectsListForClassSection($classID, $sectionID, $branchID = null)
    {
        $this->db->select('subject_assign.subject_id, subject.name as subjectname');
        $this->db->from('subject_assign');
        $this->db->join('subject', 'subject.id = subject_assign.subject_id', 'left');
        $this->db->where('subject_assign.class_id', $classID);
        $this->db->where('subject_assign.section_id', $sectionID);
        $this->db->where('subject_assign.session_id', get_session_id());
        if ($branchID !== null && $branchID !== '' && (int) $branchID > 0) {
            $this->db->where('subject_assign.branch_id', (int) $branchID);
        }
        $this->db->group_by(array('subject_assign.subject_id', 'subject.name'));
        $this->db->order_by('subject.name', 'ASC');
        return $this->db->get();
    }

    public function getClassTeacherByClassSection($classID = '', $sectionID = '')
    {
        $this->db->select('teacher_allocation.id');
        $this->db->from('teacher_allocation');
        $this->db->where('teacher_allocation.teacher_id', get_loggedin_user_id());
        $this->db->where('teacher_allocation.session_id', get_session_id());
        $this->db->where('teacher_allocation.class_id', $classID);
        $this->db->where('teacher_allocation.section_id', $sectionID);
        $q = $this->db->get()->num_rows();
        if ($q > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Class teacher name(s) for a class+section in current session.
     *
     * @return string
     */
    public function getClassTeacherNamesByClassSection($classID = '', $sectionID = '')
    {
        $this->db->select('staff.name');
        $this->db->from('teacher_allocation');
        $this->db->join('staff', 'staff.id = teacher_allocation.teacher_id', 'left');
        $this->db->where('teacher_allocation.class_id', $classID);
        $this->db->where('teacher_allocation.section_id', $sectionID);
        $this->db->where('teacher_allocation.session_id', get_session_id());
        $rows = $this->db->get()->result_array();
        if (empty($rows)) {
            return '';
        }
        $names = array();
        foreach ($rows as $r) {
            if (!empty($r['name'])) {
                $names[] = $r['name'];
            }
        }
        return implode(', ', array_unique($names));
    }
}
