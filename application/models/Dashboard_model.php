<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Dashboard_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getTeacherDashboardData($staffID, $branchID)
    {
        $staffID = (int) $staffID;
        $branchID = (int) $branchID;
        $sessionID = (int) get_session_id();
        $today = date('Y-m-d');
        $assignments = array();
        $classTeacherPairs = array();

        $classSections = $this->app_lib->get_class_teacher_sections($branchID, $sessionID);
        foreach ($classSections as $classID => $sectionIDs) {
            foreach ($sectionIDs as $sectionID) {
                $key = (int) $classID . ':' . (int) $sectionID;
                $classTeacherPairs[$key] = array(
                    'class_id' => (int) $classID,
                    'section_id' => (int) $sectionID,
                );
                $assignments[$key] = array(
                    'class_id' => (int) $classID,
                    'section_id' => (int) $sectionID,
                    'subject_id' => 0,
                );
            }
        }

        $this->db->select('class_id, section_id, subject_id');
        $this->db->from('subject_assign');
        $this->db->where('teacher_id', $staffID);
        $this->db->where('branch_id', $branchID);
        $this->db->where('session_id', $sessionID);
        $subjectAssignments = $this->db->get()->result_array();
        foreach ($subjectAssignments as $assignment) {
            $key = (int) $assignment['class_id'] . ':' . (int) $assignment['section_id'];
            $subjectKey = $key . ':' . (int) $assignment['subject_id'];
            $assignments[$subjectKey] = array(
                'class_id' => (int) $assignment['class_id'],
                'section_id' => (int) $assignment['section_id'],
                'subject_id' => (int) $assignment['subject_id'],
            );
        }

        $this->db->select('t.id as timetable_id, t.class_id, t.section_id, t.subject_id, t.day, t.time_start, t.time_end, t.class_room, t.break, c.name as class_name, s.name as section_name, sb.name as subject_name');
        $this->db->from('timetable_class as t');
        $this->db->join('class as c', 'c.id = t.class_id', 'left');
        $this->db->join('section as s', 's.id = t.section_id', 'left');
        $this->db->join('subject as sb', 'sb.id = t.subject_id', 'left');
        $this->db->where('t.teacher_id', $staffID);
        $this->db->where('t.branch_id', $branchID);
        $this->db->where('t.session_id', $sessionID);
        $this->db->order_by('t.time_start', 'ASC');
        $timetable = $this->db->get()->result_array();
        foreach ($timetable as $period) {
            if ((int) $period['break'] === 1) {
                continue;
            }
            $key = (int) $period['class_id'] . ':' . (int) $period['section_id'] . ':' . (int) $period['subject_id'];
            $assignments[$key] = array(
                'class_id' => (int) $period['class_id'],
                'section_id' => (int) $period['section_id'],
                'subject_id' => (int) $period['subject_id'],
            );
        }

        $pairs = array();
        $examScopes = array();
        foreach ($assignments as $assignment) {
            $pairKey = $assignment['class_id'] . ':' . $assignment['section_id'];
            $pairs[$pairKey] = array(
                'class_id' => $assignment['class_id'],
                'section_id' => $assignment['section_id'],
            );
            $examKey = $pairKey . ':' . $assignment['subject_id'];
            $examScopes[$examKey] = $assignment;
        }

        $studentCount = 0;
        if (!empty($pairs)) {
            $this->db->select('COUNT(DISTINCT e.student_id) as total', false);
            $this->db->from('enroll as e');
            $this->db->join('student as st', 'st.id = e.student_id', 'inner');
            $this->db->where('e.branch_id', $branchID);
            $this->db->where('e.session_id', $sessionID);
            $this->db->where('st.active', 1);
            $this->db->group_start();
            foreach ($pairs as $pair) {
                $this->db->or_group_start();
                $this->db->where('e.class_id', $pair['class_id']);
                $this->db->where('e.section_id', $pair['section_id']);
                $this->db->group_end();
            }
            $this->db->group_end();
            $countRow = $this->db->get()->row_array();
            $studentCount = !empty($countRow['total']) ? (int) $countRow['total'] : 0;
        }

        $allocatedStudents = array();
        if (!empty($pairs)) {
            $this->db->distinct();
            $this->db->select('st.id, st.first_name, st.last_name, st.register_no, e.roll, c.name as class_name, s.name as section_name');
            $this->db->from('enroll as e');
            $this->db->join('student as st', 'st.id = e.student_id', 'inner');
            $this->db->join('class as c', 'c.id = e.class_id', 'left');
            $this->db->join('section as s', 's.id = e.section_id', 'left');
            $this->db->where('e.branch_id', $branchID);
            $this->db->where('e.session_id', $sessionID);
            $this->db->where('st.active', 1);
            $this->db->group_start();
            foreach ($pairs as $pair) {
                $this->db->or_group_start();
                $this->db->where('e.class_id', $pair['class_id']);
                $this->db->where('e.section_id', $pair['section_id']);
                $this->db->group_end();
            }
            $this->db->group_end();
            $this->db->order_by('c.name', 'ASC');
            $this->db->order_by('s.name', 'ASC');
            $this->db->order_by('e.roll', 'ASC');
            $allocatedStudents = $this->db->get()->result_array();
        }

        $todayPeriods = array();
        foreach ($timetable as $period) {
            if ((int) $period['break'] !== 0 || strtolower((string) $period['day']) !== strtolower(date('l'))) {
                continue;
            }
            $period['student_count'] = 0;
            $period['work_recorded'] = 0;
            $period['work_incomplete'] = 0;
            $period['incomplete_students'] = array();
            $period['work_report_available'] = $this->db->table_exists('digital_diary_student_work_report');
            $pairKey = (int) $period['class_id'] . ':' . (int) $period['section_id'];
            if (!isset($pairs[$pairKey])) {
                continue;
            }
            if (!isset($todayPeriods[$period['timetable_id']])) {
                $this->db->from('enroll as e');
                $this->db->join('student as st', 'st.id = e.student_id', 'inner');
                $this->db->where('e.branch_id', $branchID);
                $this->db->where('e.session_id', $sessionID);
                $this->db->where('e.class_id', (int) $period['class_id']);
                $this->db->where('e.section_id', (int) $period['section_id']);
                $this->db->where('st.active', 1);
                $period['student_count'] = (int) $this->db->count_all_results();

                if ($period['work_report_available']) {
                    $this->db->select('COUNT(*) as recorded, SUM(CASE WHEN swr.fair_copy = 0 OR swr.writing_work = 0 OR swr.learning_work = 0 THEN 1 ELSE 0 END) as incomplete', false);
                    $this->db->from('digital_diary_student_work_report as swr');
                    $this->db->join('student as st', 'st.id = swr.student_id', 'inner');
                    $this->db->join('enroll as e', 'e.student_id = swr.student_id AND e.class_id = swr.class_id AND e.section_id = swr.section_id AND e.session_id = swr.session_id AND e.branch_id = swr.branch_id', 'inner');
                    $this->db->where('swr.branch_id', $branchID);
                    $this->db->where('swr.session_id', $sessionID);
                    $this->db->where('swr.work_date', $today);
                    $this->db->where('swr.class_id', (int) $period['class_id']);
                    $this->db->where('swr.section_id', (int) $period['section_id']);
                    $this->db->where('swr.subject_id', (int) $period['subject_id']);
                    $this->db->where('st.active', 1);
                    $workRow = $this->db->get()->row_array();
                    $period['work_recorded'] = !empty($workRow['recorded']) ? (int) $workRow['recorded'] : 0;
                    $period['work_incomplete'] = !empty($workRow['incomplete']) ? (int) $workRow['incomplete'] : 0;

                    $this->db->select('st.id, st.first_name, st.last_name, st.register_no, e.roll', false);
                    $this->db->from('digital_diary_student_work_report as swr');
                    $this->db->join('student as st', 'st.id = swr.student_id', 'inner');
                    $this->db->join('enroll as e', 'e.student_id = swr.student_id AND e.class_id = swr.class_id AND e.section_id = swr.section_id AND e.session_id = swr.session_id AND e.branch_id = swr.branch_id', 'inner');
                    $this->db->where('swr.branch_id', $branchID);
                    $this->db->where('swr.session_id', $sessionID);
                    $this->db->where('swr.work_date', $today);
                    $this->db->where('swr.class_id', (int) $period['class_id']);
                    $this->db->where('swr.section_id', (int) $period['section_id']);
                    $this->db->where('swr.subject_id', (int) $period['subject_id']);
                    $this->db->where('st.active', 1);
                    $this->db->where('(swr.fair_copy = 0 OR swr.writing_work = 0 OR swr.learning_work = 0)', null, false);
                    $this->db->order_by('e.roll', 'ASC');
                    $period['incomplete_students'] = $this->db->get()->result_array();
                    $period['work_incomplete'] = count($period['incomplete_students']);
                }
                $todayPeriods[$period['timetable_id']] = $period;
            }
        }
        $todayPeriods = array_values($todayPeriods);

        $nonPerformerTracker = array();
        $nonPerformerStudentIDs = array();
        $nonPerformerRangeStart = date('Y-m-d', strtotime($today . ' -29 days'));
        $subjectTrackerScopes = array();
        foreach ($assignments as $assignment) {
            if ((int) $assignment['subject_id'] > 0) {
                $scopeKey = (int) $assignment['class_id'] . ':' . (int) $assignment['section_id'] . ':' . (int) $assignment['subject_id'];
                $subjectTrackerScopes[$scopeKey] = $assignment;
            }
        }
        if ($this->db->table_exists('digital_diary_student_work_report')
            && (!empty($classTeacherPairs) || !empty($subjectTrackerScopes))) {
            $this->db->select(
                'swr.student_id, st.first_name, st.last_name, st.register_no, e.roll, ' .
                'swr.class_id, c.name as class_name, swr.section_id, sec.name as section_name, ' .
                'swr.subject_id, sb.name as subject_name, COUNT(DISTINCT swr.work_date) as incomplete_days, ' .
                'SUM(CASE WHEN swr.fair_copy = 0 THEN 1 ELSE 0 END) as fair_copy_missed, ' .
                'SUM(CASE WHEN swr.writing_work = 0 THEN 1 ELSE 0 END) as writing_work_missed, ' .
                'SUM(CASE WHEN swr.learning_work = 0 THEN 1 ELSE 0 END) as learning_work_missed, ' .
                'MAX(swr.work_date) as last_incomplete_date',
                false
            );
            $this->db->from('digital_diary_student_work_report as swr');
            $this->db->join('student as st', 'st.id = swr.student_id', 'inner');
            $this->db->join(
                'enroll as e',
                'e.student_id = swr.student_id AND e.class_id = swr.class_id AND e.section_id = swr.section_id AND e.session_id = swr.session_id AND e.branch_id = swr.branch_id',
                'inner'
            );
            $this->db->join('class as c', 'c.id = swr.class_id', 'left');
            $this->db->join('section as sec', 'sec.id = swr.section_id', 'left');
            $this->db->join('subject as sb', 'sb.id = swr.subject_id', 'left');
            $this->db->where('swr.branch_id', $branchID);
            $this->db->where('swr.session_id', $sessionID);
            $this->db->where('swr.work_date >=', $nonPerformerRangeStart);
            $this->db->where('swr.work_date <=', $today);
            $this->db->where('st.active', 1);
            $this->db->group_start();
            foreach ($classTeacherPairs as $pair) {
                $this->db->or_group_start();
                $this->db->where('swr.class_id', $pair['class_id']);
                $this->db->where('swr.section_id', $pair['section_id']);
                $this->db->group_end();
            }
            foreach ($subjectTrackerScopes as $scope) {
                $this->db->or_group_start();
                $this->db->where('swr.class_id', $scope['class_id']);
                $this->db->where('swr.section_id', $scope['section_id']);
                $this->db->where('swr.subject_id', $scope['subject_id']);
                $this->db->group_end();
            }
            $this->db->group_end();
            $this->db->where('(swr.fair_copy = 0 OR swr.writing_work = 0 OR swr.learning_work = 0)', null, false);
            $this->db->group_by(array(
                'swr.student_id',
                'st.first_name',
                'st.last_name',
                'st.register_no',
                'e.roll',
                'swr.class_id',
                'c.name',
                'swr.section_id',
                'sec.name',
                'swr.subject_id',
                'sb.name',
            ));
            $this->db->order_by('incomplete_days', 'DESC');
            $this->db->order_by('last_incomplete_date', 'DESC');
            $this->db->order_by('e.roll', 'ASC');
            $nonPerformerTracker = $this->db->get()->result_array();
            foreach ($nonPerformerTracker as $row) {
                $nonPerformerStudentIDs[(int) $row['student_id']] = true;
            }
        }

        $upcomingClasses = array();
        $currentTime = date('H:i:s');
        foreach ($todayPeriods as $period) {
            if ($period['time_end'] >= $currentTime) {
                $upcomingClasses[] = $period;
            }
        }
        $upcomingClasses = array_slice($upcomingClasses, 0, 5);

        $upcomingExams = array();
        if (!empty($examScopes)) {
            $this->db->select('te.exam_date, te.time_start, te.time_end, te.class_id, te.section_id, te.subject_id, e.name as exam_name, c.name as class_name, s.name as section_name, sb.name as subject_name');
            $this->db->from('timetable_exam as te');
            $this->db->join('exam as e', 'e.id = te.exam_id', 'inner');
            $this->db->join('class as c', 'c.id = te.class_id', 'left');
            $this->db->join('section as s', 's.id = te.section_id', 'left');
            $this->db->join('subject as sb', 'sb.id = te.subject_id', 'left');
            $this->db->where('te.branch_id', $branchID);
            $this->db->where('te.session_id', $sessionID);
            $this->db->where('te.exam_date >=', $today);
            $this->db->group_start();
            foreach ($examScopes as $scope) {
                $this->db->or_group_start();
                $this->db->where('te.class_id', $scope['class_id']);
                $this->db->where('te.section_id', $scope['section_id']);
                if ($scope['subject_id'] > 0) {
                    $this->db->where('te.subject_id', $scope['subject_id']);
                }
                $this->db->group_end();
            }
            $this->db->group_end();
            $this->db->order_by('te.exam_date', 'ASC');
            $this->db->order_by('te.time_start', 'ASC');
            $this->db->limit(5);
            $upcomingExams = $this->db->get()->result_array();
        }

        $this->db->from('leave_application');
        $this->db->where('user_id', $staffID);
        $this->db->where('role_id', 3);
        $this->db->where('session_id', $sessionID);
        $this->db->where('status', 1);
        $pendingLeaves = (int) $this->db->count_all_results();

        $this->db->select('net_salary');
        $this->db->from('payslip');
        $this->db->where('staff_id', $staffID);
        $this->db->where('month', date('m'));
        $this->db->where('year', date('Y'));
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $salary = $this->db->get()->row_array();

        $complaintStats = array(
            'assigned_total' => 0,
            'assigned_pending' => 0,
            'assigned_resolved' => 0,
            'assigned_recent' => array(),
            'submitted_total' => 0,
            'submitted_pending' => 0,
            'submitted_resolved' => 0,
            'submitted_recent' => array(),
        );
        if (moduleIsEnabled('reception') && $this->db->table_exists('complaint')) {
            $this->db->select("COUNT(*) as total, SUM(CASE WHEN date_of_solution IS NULL OR date_of_solution = '' OR date_of_solution = '0000-00-00' THEN 1 ELSE 0 END) as pending", false);
            $this->db->from('complaint');
            $this->db->where('assigned_id', $staffID);
            $this->db->where('branch_id', $branchID);
            $assignedCounts = $this->db->get()->row_array();
            $complaintStats['assigned_total'] = !empty($assignedCounts['total']) ? (int) $assignedCounts['total'] : 0;
            $complaintStats['assigned_pending'] = !empty($assignedCounts['pending']) ? (int) $assignedCounts['pending'] : 0;
            $complaintStats['assigned_resolved'] = $complaintStats['assigned_total'] - $complaintStats['assigned_pending'];

            $this->db->select('c.id, c.name, c.note, c.date, c.date_of_solution, ct.name as type_name');
            $this->db->from('complaint as c');
            $this->db->join('complaint_type as ct', 'ct.id = c.type_id', 'left');
            $this->db->where('c.assigned_id', $staffID);
            $this->db->where('c.branch_id', $branchID);
            $this->db->order_by('c.id', 'DESC');
            $this->db->limit(5);
            $complaintStats['assigned_recent'] = $this->db->get()->result_array();

            $this->db->select("COUNT(*) as total, SUM(CASE WHEN date_of_solution IS NULL OR date_of_solution = '' OR date_of_solution = '0000-00-00' THEN 1 ELSE 0 END) as pending", false);
            $this->db->from('complaint');
            $this->db->where('created_by', $staffID);
            $this->db->where('branch_id', $branchID);
            $submittedCounts = $this->db->get()->row_array();
            $complaintStats['submitted_total'] = !empty($submittedCounts['total']) ? (int) $submittedCounts['total'] : 0;
            $complaintStats['submitted_pending'] = !empty($submittedCounts['pending']) ? (int) $submittedCounts['pending'] : 0;
            $complaintStats['submitted_resolved'] = $complaintStats['submitted_total'] - $complaintStats['submitted_pending'];

            $this->db->select('c.id, c.name, c.note, c.date, c.date_of_solution, ct.name as type_name');
            $this->db->from('complaint as c');
            $this->db->join('complaint_type as ct', 'ct.id = c.type_id', 'left');
            $this->db->where('c.created_by', $staffID);
            $this->db->where('c.branch_id', $branchID);
            $this->db->order_by('c.id', 'DESC');
            $this->db->limit(5);
            $complaintStats['submitted_recent'] = $this->db->get()->result_array();
        }

        $this->load->model('digital_diary_model');
        $coordinatorScopes = $this->digital_diary_model->getStaffDiaryRoleScopes($staffID, 'coordinator', $branchID);
        $coordinatorTasks = empty($coordinatorScopes)
            ? 0
            : $this->digital_diary_model->countUnreadCoordinatorAlerts($branchID, $coordinatorScopes);

        $teacherRank = null;
        if ($this->db->table_exists('digital_diary_student_work_report')) {
            $this->db->select('swr.staff_id, COUNT(*) as report_count, SUM(swr.fair_copy + swr.writing_work + swr.learning_work) as completed_checks', false);
            $this->db->from('digital_diary_student_work_report as swr');
            $this->db->join('login_credential as lc', 'lc.user_id = swr.staff_id AND lc.role = 3', 'inner');
            $this->db->where('swr.branch_id', $branchID);
            $this->db->where('swr.session_id', $sessionID);
            $this->db->where('swr.work_date', $today);
            $this->db->group_by('swr.staff_id');
            $rankRows = $this->db->get()->result_array();
            foreach ($rankRows as &$rankRow) {
                $checks = (int) $rankRow['report_count'] * 3;
                $rankRow['score'] = $checks > 0
                    ? (int) round(((int) $rankRow['completed_checks'] / $checks) * 100)
                    : 0;
            }
            unset($rankRow);
            usort($rankRows, function ($a, $b) {
                if ($a['score'] === $b['score']) {
                    return (int) $a['staff_id'] - (int) $b['staff_id'];
                }
                return $b['score'] - $a['score'];
            });
            foreach ($rankRows as $index => $rankRow) {
                if ((int) $rankRow['staff_id'] === $staffID) {
                    $teacherRank = array(
                        'rank' => $index + 1,
                        'total' => count($rankRows),
                        'score' => (int) $rankRow['score'],
                    );
                    break;
                }
            }
        }

        $this->load->model('subject_model');
        $syllabusSubjects = array();
        $teacherSubjects = $this->subject_model->getAssignmentsByTeacher($staffID, $branchID);
        if ($this->db->table_exists('digital_diary_lesson')) {
            foreach ($teacherSubjects as $assignment) {
                $lessons = $this->digital_diary_model->getLessonsBySubject(
                    $assignment['class_id'],
                    $assignment['section_id'],
                    $assignment['subject_id'],
                    $branchID
                );
                $completedIDs = $this->digital_diary_model->getCompletedLessonIdsForSubject(
                    $assignment['class_id'],
                    $assignment['section_id'],
                    $assignment['subject_id'],
                    $branchID,
                    $today
                );
                $completedSet = array_flip($completedIDs);
                $completedCount = 0;
                foreach ($lessons as &$lesson) {
                    $lesson['is_completed'] = isset($completedSet[(int) $lesson['id']]);
                    if ($lesson['is_completed']) {
                        $completedCount++;
                    }
                }
                unset($lesson);
                $totalLessons = count($lessons);
                $syllabusSubjects[] = array(
                    'class_name' => $assignment['class_name'],
                    'section_name' => $assignment['section_name'],
                    'subject_name' => $assignment['subject_name'],
                    'class_id' => $assignment['class_id'],
                    'section_id' => $assignment['section_id'],
                    'subject_id' => $assignment['subject_id'],
                    'lessons' => $lessons,
                    'completed_count' => $completedCount,
                    'total_count' => $totalLessons,
                    'percentage' => $totalLessons > 0 ? (int) round(($completedCount / $totalLessons) * 100) : null,
                );
            }
        }

        $schoolCalendar = array();
        $schoolYear = '';
        $yearRow = $this->db->select('school_year')->get_where('schoolyear', array('id' => $sessionID), 1)->row_array();
        if (!empty($yearRow['school_year'])) {
            $schoolYear = $yearRow['school_year'];
        }
        if ($this->db->table_exists('event')) {
            $this->db->select('id, title, type, start_date, end_date, audition, selected_list');
            $this->db->from('event');
            $this->db->where('branch_id', $branchID);
            $this->db->where('session_id', $sessionID);
            $this->db->where('status', 1);
            $this->db->order_by('start_date', 'ASC');
            foreach ($this->db->get()->result_array() as $event) {
                $audience = (int) $event['audition'];
                if ($audience === 2 || $audience === 3) {
                    $selected = json_decode((string) $event['selected_list'], true);
                    if (!is_array($selected)) {
                        continue;
                    }
                    $visible = false;
                    foreach ($selected as $selectedAudience) {
                        if ($audience === 2) {
                            foreach ($pairs as $pair) {
                                if ((int) $selectedAudience === (int) $pair['class_id']) {
                                    $visible = true;
                                    break 2;
                                }
                            }
                        } else {
                            $selectedParts = explode('-', (string) $selectedAudience);
                            if (count($selectedParts) === 2) {
                                $pairKey = (int) $selectedParts[0] . ':' . (int) $selectedParts[1];
                                if (isset($pairs[$pairKey])) {
                                    $visible = true;
                                    break;
                                }
                            }
                        }
                    }
                    if (!$visible) {
                        continue;
                    }
                }
                $schoolCalendar[] = array(
                    'id' => (int) $event['id'],
                    'title' => (string) $event['title'],
                    'start' => $event['start_date'],
                    'end' => date('Y-m-d', strtotime($event['end_date'] . ' +1 day')),
                    'color' => $event['type'] === 'holiday' ? '#d76570' : '#167d87',
                    'allDay' => true,
                );
            }
        }

        return array(
            'student_count' => $studentCount,
            'allocated_students' => $allocatedStudents,
            'pending_leaves' => $pendingLeaves,
            'salary' => isset($salary['net_salary']) ? (float) $salary['net_salary'] : null,
            'coordinator_tasks' => (int) $coordinatorTasks,
            'coordinator_assigned' => !empty($coordinatorScopes),
            'complaint_stats' => $complaintStats,
            'today_periods' => $todayPeriods,
            'non_performer_tracker' => $nonPerformerTracker,
            'non_performer_student_count' => count($nonPerformerStudentIDs),
            'non_performer_range_start' => $nonPerformerRangeStart,
            'upcoming_classes' => $upcomingClasses,
            'upcoming_exams' => $upcomingExams,
            'syllabus_subjects' => $syllabusSubjects,
            'syllabus_available' => $this->db->table_exists('digital_diary_lesson'),
            'school_calendar' => $schoolCalendar,
            'school_year' => $schoolYear,
            'teacher_rank' => $teacherRank,
            'work_report_available' => $this->db->table_exists('digital_diary_student_work_report'),
        );
    }

    public function getMonthlyBookIssued($id = '')
    {
        $this->db->select('id');
        $this->db->from('book_issues');
        $this->db->where("date_of_issue BETWEEN DATE_SUB(CURDATE() ,INTERVAL 1 MONTH) AND CURDATE() AND role_id = '7' AND user_id = " . $this->db->escape($id));
        return $this->db->get()->num_rows();
    }

    public function getStaffCounter($role = '', $branchID = '')
    {
        $this->db->select('COUNT(staff.id) as snumber');
        $this->db->from('staff');
        $this->db->join('login_credential', 'login_credential.user_id = staff.id', 'inner');
        $this->db->where_not_in('login_credential.role', 1);
        if (!empty($role)) {
            $this->db->where('login_credential.role', $role);
        } else {
            $this->db->where_not_in('login_credential.role', array(1, 3, 6, 7));
        }
        if (!empty($branchID)) {
            $this->db->where('staff.branch_id', $branchID);
        }
        return $this->db->get()->row_array();
    }

    public function getMonthlyPayment($id = '')
    {
        $this->db->select('IFNULL(sum(h.amount),0) as amount');
        $this->db->from('fee_allocation as fa');
        $this->db->join('fee_payment_history as h', 'h.allocation_id = fa.id', 'left');
        $this->db->where("h.date BETWEEN DATE_SUB(CURDATE(),INTERVAL 1 MONTH) AND CURDATE() AND fa.student_id = " . $this->db->escape($id) . " AND fa.session_id = " . $this->db->escape(get_session_id()));
        return $this->db->get()->row()->amount;
    }

    /* annual academic fees summary charts */
    public function annualFeessummaryCharts($branchID = '', $studentID = '')
    {
        $total_fee = array();
        $total_paid = array();
        $total_due = array();
        $year = date("Y");
        for ($month = 1; $month <= 12; $month++) {
            $sql = "SELECT `fa`.`id` as `allocation_id`,`gd`.`fee_type_id`,`gd`.`amount` FROM `fee_allocation` as `fa` INNER JOIN `fee_groups_details` as `gd` ON `gd`.`fee_groups_id` = `fa`.`group_id` WHERE MONTH(`gd`.`due_date`) = " . $this->db->escape($month) . " AND YEAR(`gd`.`due_date`) = '$year' AND `fa`.`session_id` = " . $this->db->escape(get_session_id());
            if (!empty($branchID)) {
                $sql .= " AND `fa`.`branch_id` = " . $this->db->escape($branchID);
            }
            if (!empty($studentID)) {
                $sql .= " AND `fa`.`student_id` = " . $this->db->escape($studentID);
            }
            $total_amount = 0;
            $totalpaid = 0;
            $total_discount = 0;
            $result = $this->db->query($sql)->result();
            foreach ($result as $row) {
                $total_amount += $row->amount;
                $sql = "SELECT SUM(`h`.`amount`) AS `total_paid`, SUM(`h`.`discount`) AS `total_discount` FROM `fee_payment_history` as `h` WHERE `h`.`allocation_id` = " . $this->db->escape($row->allocation_id) . " AND `h`.`type_id` = " . $this->db->escape($row->fee_type_id) . " AND MONTH(`h`.`date`) = " . $this->db->escape($month) . " AND YEAR(`h`.`date`) = '$year'";
                $r = $this->db->query($sql)->row();
                $totalpaid += $r->total_paid;
                $total_discount += $r->total_discount;
            }
            $total_fee[] = floatval($total_amount);
            $total_paid[] = floatval($totalpaid);
            $total_due[] = floatval($total_amount - ($totalpaid + $total_discount));
        };
        return array(
            'total_fee' => $total_fee,
            'total_paid' => $total_paid,
            'total_due' => $total_due,
        );
    }

    /* student annual attendance charts */
    public function getStudentAttendance($studentID = '')
    {
        $total_present = array();
        $total_absent = array();
        $total_late = array();
        $enrollID = $this->db->select('id')->where(['student_id' => $studentID, 'session_id' => get_session_id()])->get('enroll')->row()->id;
        for ($month = 1; $month <= 12; $month++):
            $total_present[] = $this->db->query("SELECT id FROM student_attendance WHERE MONTH(date) = " . $this->db->escape($month) . " AND YEAR(date) = YEAR(CURDATE()) AND status = 'P' AND enroll_id = " . $this->db->escape($enrollID))->num_rows();
            $total_absent[] = $this->db->query("SELECT id FROM student_attendance WHERE MONTH(date) = " . $this->db->escape($month) . " AND YEAR(date) = YEAR(CURDATE()) AND status = 'A' AND enroll_id = " . $this->db->escape($enrollID))->num_rows();
            $total_late[] = $this->db->query("SELECT id FROM student_attendance WHERE MONTH(date) = " . $this->db->escape($month) . " AND YEAR(date) = YEAR(CURDATE()) AND status = 'L' AND enroll_id = " . $this->db->escape($enrollID))->num_rows();
        endfor;
        return array(
            'total_present' => $total_present,
            'total_absent' => $total_absent,
            'total_late' => $total_late,
        );
    }

    public function get_monthly_attachments($id = '')
    {
        $branchID = get_loggedin_branch_id();
        $classID = $this->db->select('class_id')->where('student_id', $id)->get('enroll')->row()->class_id;
        $this->db->select('id');
        $this->db->from('attachments');
        $this->db->where("date BETWEEN DATE_SUB(CURDATE() ,INTERVAL 1 MONTH) AND CURDATE() AND (class_id = " . $this->db->escape($classID) . " OR class_id = 'unfiltered') AND branch_id = " . $this->db->escape($branchID));
        return $this->db->get()->num_rows();
    }

    /* annual academic fees summary charts */
    public function getWeekendAttendance($branchID = '')
    {
        $days = array();
        $employee_att = array();
        $student_att = array();
        $now = new DateTime("6 days ago");
        $interval = new DateInterval('P1D'); // 1 Day interval
        $period = new DatePeriod($now, $interval, 6); // 7 Days
        foreach ($period as $day) {
            $days[] = $day->format("d-M");
            $this->db->select('id');
            if (!empty($branchID)) {
                $this->db->where('branch_id', $branchID);
            }

            $this->db->where('date = "' . $day->format('Y-m-d') . '" AND (status = "P" OR status = "L")');
            $student_att[]['y'] = $this->db->get('student_attendance')->num_rows();

            $this->db->select('id');
            if (!empty($branchID)) {
                $this->db->where('branch_id', $branchID);
            }

            $this->db->where('date = "' . $day->format('Y-m-d') . '" AND (status = "P" OR status = "L")');
            $employee_att[]['y'] = $this->db->get('staff_attendance')->num_rows();
        }
        return array(
            'days' => $days,
            'employee_att' => $employee_att,
            'student_att' => $student_att,
        );
    }

    /* monthly academic cash book transaction charts */
    public function getIncomeVsExpense($branchID = '')
    {
        $query = "SELECT IFNULL(SUM(dr),0) as dr, IFNULL(SUM(cr),0) as cr FROM transactions WHERE month(DATE) = MONTH(now()) AND year(DATE) = YEAR(now())";
        if (!empty($branchID)) {
            $query .= " AND branch_id = " . $this->db->escape($branchID);
        }
        $r = $this->db->query($query)->row_array();
        return array(['name' => translate("expense"), 'value' => $r['dr']], ['name' => translate("income"), 'value' => $r['cr']]);
    }

    /* total academic students strength classes divided into charts */
    public function getStudentByClass($branchID = '')
    {
        $this->db->select('IFNULL(COUNT(e.student_id), 0) as total_student, c.name as class_name');
        $this->db->from('enroll as e');
        $this->db->join('class as c', 'c.id = e.class_id', 'inner');
        $this->db->group_by('e.class_id');
        if (!empty($branchID)) {
            $this->db->where('e.branch_id', $branchID);
        }

        $query = $this->db->get();
        $data = array();
        if ($query->num_rows() > 0) {
            $students = $query->result();
            foreach ($students as $row) {
                $data[] = ['value' => floatval($row->total_student), 'name' => $row->class_name];
            }
        } else {
            $data[] = ['value' => 0, 'name' => translate('not_found_anything')];
        }
        return $data;
    }

    public function get_total_student($branchID = '')
    {
        $sessionID = get_session_id();
        $this->db->select('IFNULL(COUNT(enroll.id), 0) as total_student');
        $this->db->from('enroll');
        $this->db->join('student', 'student.id = enroll.student_id', 'inner');
        $this->db->where('enroll.session_id', $sessionID);
        if (!empty($branchID)) {
            $this->db->where('enroll.branch_id', $branchID);
        }
        return $this->db->get()->row()->total_student;
    }

    public function getMonthlyAdmission($branchID = '')
    {
        $this->db->select('s.id');
        $this->db->from('student as s');
        $this->db->join('enroll as e', 'e.student_id = s.id', 'inner');
        $this->db->where('s.admission_date BETWEEN DATE_SUB(CURDATE() ,INTERVAL 1 MONTH) AND CURDATE()');
        if (!empty($branchID)) {
            $this->db->where('e.branch_id', $branchID);
        }
        return $this->db->get()->num_rows();
    }

    public function getVoucher($branchID = '')
    {
        $this->db->select('id');
        if (!empty($branchID)) {
            $this->db->where('branch_id', $branchID);
        }
        $this->db->where('date BETWEEN DATE_SUB(CURDATE() ,INTERVAL 1 MONTH) AND CURDATE()');
        return $this->db->get('transactions')->num_rows();
    }

    public function get_transport_route($branchID = '')
    {
        if (!empty($branchID)) {
            $this->db->where('branch_id', $branchID);
        }
        return $this->db->get('transport_route')->num_rows();
    }


    public function languageShortCodes($lang='')
    {
        $codes = array (
          'english' => 'en',
          'bengali' => 'bn',
          'arabic' => 'ar',
          'french' => 'fr',
          'hindi' => 'hi',
          'indonesian' => 'id',
          'italian' => 'it',
          'japanese' => 'ja',
          'korean' => 'ko',
          'portuguese' => 'pt',
          'thai' => 'th',
          'turkish' => 'tr',
          'urdu' => 'ur',
          'chinese' => 'zh',
          'afrikaans' => 'af',
          'german' => 'de',
          'nepali' => 'ne',
          'russian' => 'ru',
          'danish' => 'da',
          'armenian' => 'hy',
          'georgian' => 'ka',
          'marathi' => 'mr',
          'malay' => 'ms',
          'tamil' => 'ta',
          'telugu' => 'te',
          'swedish' => 'sv',
          'dutch' => 'nl',
          'greek' => 'el',
          'spanish' => 'es',
          'punjabi' => 'pa',
        );
        return empty($codes[$lang]) ? '' : $codes[$lang];
    }
}
