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
        $hasAct = $this->db->field_exists('teaching_activity_id', 'digital_diary_role_allocation');
        $coordExpr = $hasAct
            ? "GROUP_CONCAT(DISTINCT CONCAT(st.name, IF(ra.teaching_activity_id > 0 AND act.name IS NOT NULL, CONCAT(' (', act.name, ')'), '')) ORDER BY st.name SEPARATOR ', ')"
            : "GROUP_CONCAT(DISTINCT st.name ORDER BY st.name SEPARATOR ', ')";
        $actJoin = $hasAct ? " LEFT JOIN digital_diary_teaching_activity act ON act.id = ra.teaching_activity_id" : '';
        $coordSub = "(SELECT ra.class_id, ra.section_id, ra.session_id, {$coordExpr} AS coordinator_name
            FROM digital_diary_role_allocation ra
            LEFT JOIN staff st ON st.id = ra.teacher_id
            {$actJoin}
            WHERE ra.role_type = 'coordinator'
            GROUP BY ra.class_id, ra.section_id, ra.session_id) ra_c";
        $this->db->select('ta.*,st.name as teacher_name,st.staff_id as teacher_id,c.name as class_name,c.branch_id,s.name as section_name, ra_c.coordinator_name');
        $this->db->from('teacher_allocation as ta');
        $this->db->join('staff as st', 'st.id = ta.teacher_id', 'left');
        $this->db->join('class as c', 'c.id = ta.class_id', 'left');
        $this->db->join('section as s', 's.id = ta.section_id', 'left');
        $this->db->join($coordSub, 'ra_c.class_id = ta.class_id AND ra_c.section_id = ta.section_id AND ra_c.session_id = ta.session_id', 'left', false);
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
    }
    public function get_all_classes()
    {
        $this->db->select('id, name');
        $this->db->from('class');
        $this->db->order_by('id', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Class + section staff board: class teacher, coordinators by activity, subject teachers.
     *
     * @return array<int,array>
     */
    public function getClassStaffOverview($branchID = 0, $classID = 0)
    {
        $sessionID = (int) get_session_id();
        $branchID = (int) $branchID;
        $classID = (int) $classID;
        if ($branchID < 1) {
            return array();
        }

        $this->db->select('c.id as class_id, c.name as class_name, c.branch_id, b.name as branch_name, s.id as section_id, s.name as section_name');
        $this->db->from('class as c');
        $this->db->join('branch as b', 'b.id = c.branch_id', 'left');
        $this->db->join('sections_allocation as sa', 'sa.class_id = c.id', 'inner');
        $this->db->join('section as s', 's.id = sa.section_id', 'left');
        $this->db->where('c.branch_id', $branchID);
        if ($classID > 0) {
            $this->db->where('c.id', $classID);
        }
        $this->db->order_by('c.name', 'ASC');
        $this->db->order_by('s.name', 'ASC');
        $pairs = $this->db->get()->result_array();
        if (empty($pairs)) {
            return array();
        }

        $classIds = array();
        $grouped = array();
        foreach ($pairs as $row) {
            $cid = (int) $row['class_id'];
            $sid = (int) $row['section_id'];
            if ($cid < 1 || $sid < 1) {
                continue;
            }
            $classIds[$cid] = $cid;
            if (!isset($grouped[$cid])) {
                $grouped[$cid] = array(
                    'class_id' => $cid,
                    'class_name' => $row['class_name'],
                    'branch_id' => (int) $row['branch_id'],
                    'branch_name' => $row['branch_name'],
                    'sections' => array(),
                );
            }
            $grouped[$cid]['sections'][$sid] = array(
                'section_id' => $sid,
                'section_name' => $row['section_name'],
                'class_teacher' => null,
                'coordinators' => array(),
                'subjects' => array(),
            );
        }
        if (empty($grouped)) {
            return array();
        }
        $classIdList = array_values($classIds);

        $teachers = array();
        $this->db->select('ta.id, ta.class_id, ta.section_id, ta.teacher_id, st.name as teacher_name');
        $this->db->from('teacher_allocation as ta');
        $this->db->join('staff as st', 'st.id = ta.teacher_id', 'left');
        $this->db->where('ta.session_id', $sessionID);
        $this->db->where_in('ta.class_id', $classIdList);
        foreach ($this->db->get()->result_array() as $ta) {
            $teachers[(int) $ta['class_id'] . '_' . (int) $ta['section_id']] = array(
                'allocation_id' => (int) $ta['id'],
                'teacher_id' => (int) $ta['teacher_id'],
                'teacher_name' => $ta['teacher_name'],
            );
        }

        $this->load->model('digital_diary_model');
        $this->digital_diary_model->ensureRoleAllocationTable();
        $this->digital_diary_model->ensureTeachingActivityRoleColumns();
        $activities = $this->digital_diary_model->getTeachingActivitiesForRole($branchID, 'coordinator');
        $coordByPair = array();
        if ($this->db->table_exists('digital_diary_role_allocation')) {
            $hasAct = $this->db->field_exists('teaching_activity_id', 'digital_diary_role_allocation');
            $select = 'ra.id, ra.class_id, ra.section_id, ra.teacher_id, st.name as teacher_name';
            if ($hasAct) {
                $select .= ', ra.teaching_activity_id, act.name as activity_name';
            }
            $this->db->select($select);
            $this->db->from('digital_diary_role_allocation as ra');
            $this->db->join('staff as st', 'st.id = ra.teacher_id', 'left');
            if ($hasAct) {
                $this->db->join('digital_diary_teaching_activity as act', 'act.id = ra.teaching_activity_id', 'left');
            }
            $this->db->where('ra.role_type', 'coordinator');
            $this->db->where('ra.session_id', $sessionID);
            $this->db->where('ra.branch_id', $branchID);
            $this->db->where_in('ra.class_id', $classIdList);
            foreach ($this->db->get()->result_array() as $ra) {
                $pkey = (int) $ra['class_id'] . '_' . (int) $ra['section_id'];
                $aid = $hasAct ? (int) $ra['teaching_activity_id'] : 0;
                if (!isset($coordByPair[$pkey])) {
                    $coordByPair[$pkey] = array();
                }
                $coordByPair[$pkey][$aid] = array(
                    'allocation_id' => (int) $ra['id'],
                    'activity_id' => $aid,
                    'activity_name' => !empty($ra['activity_name']) ? $ra['activity_name'] : ($aid > 0 ? 'Activity' : 'All activities'),
                    'teacher_id' => (int) $ra['teacher_id'],
                    'teacher_name' => $ra['teacher_name'],
                );
            }
        }

        $subjectsByPair = array();
        if ($this->db->table_exists('subject_assign')) {
            $this->db->select('sa.id, sa.class_id, sa.section_id, sa.subject_id, sa.teacher_id, sb.name as subject_name, st.name as teacher_name');
            $this->db->from('subject_assign as sa');
            $this->db->join('subject as sb', 'sb.id = sa.subject_id', 'left');
            $this->db->join('staff as st', 'st.id = sa.teacher_id', 'left');
            $this->db->where('sa.session_id', $sessionID);
            $this->db->where('sa.branch_id', $branchID);
            $this->db->where_in('sa.class_id', $classIdList);
            $this->db->order_by('sb.name', 'ASC');
            foreach ($this->db->get()->result_array() as $sub) {
                $pkey = (int) $sub['class_id'] . '_' . (int) $sub['section_id'];
                if (!isset($subjectsByPair[$pkey])) {
                    $subjectsByPair[$pkey] = array();
                }
                $subjectsByPair[$pkey][] = array(
                    'assign_id' => (int) $sub['id'],
                    'subject_id' => (int) $sub['subject_id'],
                    'subject_name' => $sub['subject_name'],
                    'teacher_id' => (int) $sub['teacher_id'],
                    'teacher_name' => ((int) $sub['teacher_id'] > 0) ? $sub['teacher_name'] : '',
                );
            }
        }

        foreach ($grouped as $cid => &$classRow) {
            foreach ($classRow['sections'] as $sid => &$section) {
                $pkey = $cid . '_' . $sid;
                if (isset($teachers[$pkey])) {
                    $section['class_teacher'] = $teachers[$pkey];
                }
                $coords = array();
                foreach ($activities as $aid => $aname) {
                    $coords[$aid] = array(
                        'allocation_id' => 0,
                        'activity_id' => (int) $aid,
                        'activity_name' => $aname,
                        'teacher_id' => 0,
                        'teacher_name' => '',
                    );
                }
                if (!empty($coordByPair[$pkey])) {
                    foreach ($coordByPair[$pkey] as $aid => $assigned) {
                        if (isset($coords[$aid])) {
                            $coords[$aid] = $assigned;
                        } else {
                            $coords[$aid] = $assigned;
                        }
                    }
                }
                $section['coordinators'] = array_values($coords);
                $section['subjects'] = isset($subjectsByPair[$pkey]) ? $subjectsByPair[$pkey] : array();
            }
            unset($section);
            $classRow['sections'] = array_values($classRow['sections']);
        }
        unset($classRow);

        return array_values($grouped);
    }

    public function upsertClassSectionSubjectTeacher($classID, $sectionID, $branchID, $subjectID, $staffID)
    {
        $classID = (int) $classID;
        $sectionID = (int) $sectionID;
        $branchID = (int) $branchID;
        $subjectID = (int) $subjectID;
        $staffID = (int) $staffID;
        if ($classID < 1 || $sectionID < 1 || $subjectID < 1) {
            return false;
        }
        $where = array(
            'class_id' => $classID,
            'section_id' => $sectionID,
            'subject_id' => $subjectID,
            'session_id' => (int) get_session_id(),
            'branch_id' => $branchID,
        );
        $existing = $this->db->get_where('subject_assign', $where)->row_array();
        if (!empty($existing['id'])) {
            $this->db->where('id', (int) $existing['id']);
            $this->db->update('subject_assign', array('teacher_id' => $staffID));
            return true;
        }
        if (get_permission('subject_class_assign', 'is_add') || get_permission('subject_teacher_assign', 'is_add')) {
            $where['teacher_id'] = $staffID;
            $this->db->insert('subject_assign', $where);
            return true;
        }
        return false;
    }

}