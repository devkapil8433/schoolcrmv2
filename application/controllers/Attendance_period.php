<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Attendance_period extends Admin_Controller
{
    protected $teacherClassSections;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('subject_model');
        $this->load->model('attendance_model');
        $this->load->model('attendance_period_model');
        $this->load->model('sms_model');
        if (!moduleIsEnabled('attendance')) {
            access_denied();
        }
        $getAttendanceType = $this->app_lib->getAttendanceType();
        if ($getAttendanceType != 2 && $getAttendanceType != 1) {
            access_denied();
        }
    }

    // student submitted attendance all data are prepared and stored in the database here
    public function index()
    {
        if (!get_permission('student_attendance', 'is_add')) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();
        if (isset($_POST['search'])) {
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            }
            $this->form_validation->set_rules('class_id', translate('class'), 'required');
            $this->form_validation->set_rules('section_id', translate('section'), 'required');
            $this->form_validation->set_rules('subject_timetable_id', translate('subject'), 'required');
            $this->form_validation->set_rules('date', translate('date'), 'trim|required|callback_check_weekendday|callback_check_holiday|callback_get_valid_date');
            if ($this->form_validation->run() == true) {
                $classID = $this->input->post('class_id');
                $sectionID = $this->input->post('section_id');
                if (!$this->teacher_can_access_class_section($classID, $sectionID)) {
                    access_denied();
                }
                $subject_timetableID = $this->input->post('subject_timetable_id');
                $date = $this->input->post('date');
                $this->data['date'] = $date;
                $this->data['attendencelist'] = $this->attendance_period_model->getStudentAttendence($classID, $sectionID, $date, $subject_timetableID, $branchID);
            }
        }
        $this->data['getWeekends'] = $this->application_model->getWeekends($branchID);
        $this->data['getHolidays'] = $this->attendance_model->getHolidays($branchID);
        if (isset($_POST['save'])) {
            $attendance = (array) $this->input->post('attendance');
            $date = $this->input->post('date');
            $subject_timetable_id = $this->input->post('subject_timetable_id');
            if (is_teacher_loggedin()) {
                $period = $this->db->get_where('timetable_class', array(
                    'id' => (int) $subject_timetable_id,
                    'branch_id' => (int) $branchID,
                    'session_id' => (int) get_session_id(),
                ))->row();
                if (empty($period) || !$this->teacher_can_access_class_section($period->class_id, $period->section_id)) {
                    access_denied();
                }
                foreach ($attendance as $value) {
                    $enrollID = isset($value['enroll_id']) ? (int) $value['enroll_id'] : 0;
                    $enroll = $this->db->get_where('enroll', array(
                        'id' => $enrollID,
                        'branch_id' => (int) $branchID,
                        'session_id' => (int) get_session_id(),
                        'class_id' => (int) $period->class_id,
                        'section_id' => (int) $period->section_id,
                        'student_id' => (int) (isset($value['student_id']) ? $value['student_id'] : 0),
                    ))->row();
                    if (empty($enroll)) {
                        access_denied();
                    }
                    if (!empty($value['attendance_id'])) {
                        $existingAttendance = $this->db->get_where('student_subject_attendance', array(
                            'id' => (int) $value['attendance_id'],
                            'enroll_id' => $enrollID,
                            'subject_timetable_id' => (int) $subject_timetable_id,
                            'date' => $date,
                            'branch_id' => (int) $branchID,
                        ))->row();
                        if (empty($existingAttendance)) {
                            access_denied();
                        }
                    }
                }
            }
            foreach ($attendance as $key => $value) {
                $attStatus = (isset($value['status']) ? $value['status'] : "");
                $studentID = $value['student_id'];
                $arrayAttendance = array(
                    'enroll_id' => $value['enroll_id'],
                    'subject_timetable_id' => $subject_timetable_id,
                    'status' => $attStatus,
                    'remark' => $value['remark'],
                    'date' => $date,
                    'branch_id' => $branchID,
                );
                if (empty($value['attendance_id'])) {
                    $this->db->insert('student_subject_attendance', $arrayAttendance);
                } else {
                    $this->db->where('id', $value['attendance_id']);
                    $this->db->where('enroll_id', $value['enroll_id']);
                    $this->db->where('subject_timetable_id', $subject_timetable_id);
                    $this->db->where('date', $date);
                    $this->db->where('branch_id', $branchID);
                    $this->db->update('student_subject_attendance', array('status' => $attStatus, 'remark' => $value['remark']));
                }
                // send student absent then sms
                if ($attStatus == 'A') {
                    $arrayAttendance['student_id'] = $studentID;
                    $this->sms_model->send_sms($arrayAttendance, 3);
                }
            }
            set_alert('success', translate('information_has_been_updated_successfully'));
            redirect(current_url());
        }
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student_attendance');
        $this->data['sub_page'] = 'attendance_period/index';
        $this->data['main_menu'] = 'attendance';
        $this->load->view('layout/index', $this->data);
    }

    public function reportsbydate()
    {
        if (!get_permission('student_attendance_report', 'is_view')) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();
        $this->data['getWeekends'] = $this->application_model->getWeekends($branchID);
        $this->data['getHolidays'] = $this->attendance_model->getHolidays($branchID);
        if ($_POST) {
            $this->data['class_id'] = $this->input->post('class_id');
            $this->data['section_id'] = $this->input->post('section_id');
            if (!$this->teacher_can_access_class_section($this->data['class_id'], $this->data['section_id'])) {
                access_denied();
            }
            $this->data['date'] = $this->input->post('date');
            $date = date('l', strtotime($this->data['date']));
            $date = strtolower($date);
            $this->data['subjectByClassSection'] = $this->attendance_period_model->getSubjectByClassSection($this->data['class_id'], $this->data['section_id'], $date);
            $this->data['studentlist'] = $this->attendance_model->getStudentList($branchID, $this->data['class_id'], $this->data['section_id']);
        }
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student_attendance');
        $this->data['sub_page'] = 'attendance_period/reportsbydate';
        $this->data['main_menu'] = 'attendance_report';
        $this->load->view('layout/index', $this->data);
    }

    public function reportbymonth()
    {
        if (!get_permission('student_attendance_report', 'is_view')) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();
        if ($_POST) {
            $this->data['class_id'] = $this->input->post('class_id');
            $this->data['section_id'] = $this->input->post('section_id');
            if (!$this->teacher_can_access_class_section($this->data['class_id'], $this->data['section_id'])) {
                access_denied();
            }
            $this->data['subject_id'] = $this->input->post('subject_id');
            $this->data['month'] = date('m', strtotime($this->input->post('timestamp')));
            $this->data['year'] = date('Y', strtotime($this->input->post('timestamp')));
            $this->data['days'] = date('t', strtotime($this->data['year'] . "-" . $this->data['month']));
            $this->data['studentlist'] = $this->attendance_model->getStudentList($branchID, $this->data['class_id'], $this->data['section_id']);
        }
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student_attendance');
        $this->data['sub_page'] = 'attendance_period/reportbymonth';
        $this->data['main_menu'] = 'attendance_report';
        $this->load->view('layout/index', $this->data);
    }

    public function get_valid_date($date)
    {
        $present_date = date('Y-m-d');
        $date = date("Y-m-d", strtotime($date));
        if ($date > $present_date) {
            $this->form_validation->set_message("get_valid_date", "Please Enter Correct Date");
            return false;
        } else {
            return true;
        }
    }

    public function check_holiday($date)
    {
        $branchID = $this->application_model->get_branch_id();
        $getHolidays = $this->attendance_model->getHolidays($branchID);
        $getHolidaysArray = explode('","', $getHolidays);

        if (!empty($getHolidaysArray)) {
            if (in_array($date, $getHolidaysArray)) {
                $this->form_validation->set_message('check_holiday', 'You have selected a holiday.');
                return false;
            } else {
                return true;
            }
        }
    }

    public function check_weekendday($date)
    {
        $branchID = $this->application_model->get_branch_id();
        $getWeekendDays = $this->attendance_model->getWeekendDaysSession($branchID);
        if (!empty($getWeekendDays)) {
            if (in_array($date, $getWeekendDays)) {
                $this->form_validation->set_message('check_weekendday', "You have selected a weekend date.");
                return false;
            } else {
                return true;
            }
        }
        return true;
    }

    public function reports()
    {
        if (!get_permission('student_attendance_report', 'is_view')) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();
        if (isset($_POST['search'])) {
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            }
            $this->form_validation->set_rules('class_id', translate('class'), 'required');
            $this->form_validation->set_rules('section_id', translate('section'), 'required');
            $this->form_validation->set_rules('subject_timetable_id', translate('subject'), 'required');
            $this->form_validation->set_rules('date', translate('date'), 'trim|required|callback_check_weekendday|callback_check_holiday|callback_get_valid_date');
            if ($this->form_validation->run() == true) {
                $classID = $this->input->post('class_id');
                $sectionID = $this->input->post('section_id');
                if (!$this->teacher_can_access_class_section($classID, $sectionID)) {
                    access_denied();
                }
                $subject_timetableID = $this->input->post('subject_timetable_id');
                $date = $this->input->post('date');
                $this->data['class_id'] = $classID;
                $this->data['section_id'] = $sectionID;
                $this->data['date'] = $date;
                $this->data['attendencelist'] = $this->attendance_period_model->getSubjectAttendanceReport($classID, $sectionID, $date, $subject_timetableID, $branchID);
            }
        }
        $this->data['getWeekends'] = $this->application_model->getWeekends($branchID);
        $this->data['getHolidays'] = $this->attendance_model->getHolidays($branchID);
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student_attendance');
        $this->data['sub_page'] = 'attendance_period/reports';
        $this->data['main_menu'] = 'attendance_report';
        $this->load->view('layout/index', $this->data);
    }

    protected function teacher_can_access_class_section($classID, $sectionID)
    {
        if (!is_teacher_loggedin()) {
            return true;
        }
        if ($this->teacherClassSections === null) {
            $this->teacherClassSections = $this->app_lib->get_class_teacher_sections();
        }
        return isset($this->teacherClassSections[(int) $classID])
            && in_array((int) $sectionID, $this->teacherClassSections[(int) $classID], true);
    }

    // get subject list based on class section
    public function getByClassSection()
    {
        $html = '';
        $classID = $this->input->post('classID');
        $sectionID = $this->input->post('sectionID');
        $selectPOST = $this->input->post('selectPOST');
        $date = date('l', strtotime($this->input->post('date')));
        $date = strtolower($date);

        if (!empty($classID) && $this->teacher_can_access_class_section($classID, $sectionID)) {
            $query = $this->attendance_period_model->getSubjectByClassSection($classID, $sectionID, $date);
            if ($query->num_rows() > 0) {
                $html .= '<option value="">' . translate('select') . '</option>';
                $subjects = $query->result_array();
                foreach ($subjects as $row) {
                    $select = "";
                    if ($selectPOST == $row['id']) {
                        $select = "selected=selected";
                    }
                    $html .= '<option ' . $select . ' value="' . $row['id'] . '">' . $row['subjectname'] . " (" . date("g:i A", strtotime($row['time_start'])) . " - " . date("g:i A", strtotime($row['time_end'])) . ')</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select') . '</option>';
        }
        echo $html;
    }
}
