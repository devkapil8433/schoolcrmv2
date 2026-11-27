<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('dashboard_model');
    }

    public function index()
    {
        if (is_teacher_loggedin()) {
            $staffID = (int) get_loggedin_user_id();
            $schoolID = (int) get_loggedin_branch_id();
            $this->data['title'] = translate('dashboard');
            $this->data['school_id'] = $schoolID;
            $this->data['teacher_name'] = get_type_name_by_id('staff', $staffID);
            $this->data['teacher_dashboard'] = $this->dashboard_model->getTeacherDashboardData($staffID, $schoolID);
            $this->data['complaint_module_enabled'] = moduleIsEnabled('reception') && $this->db->table_exists('complaint');
            $this->data['complaint_types'] = array();
            $this->data['complaint_recipients'] = array();
            if ($this->data['complaint_module_enabled'] && $schoolID > 0) {
                $this->data['complaint_types'] = $this->app_lib->getSelectByBranch('complaint_type', $schoolID);
                $this->data['complaint_recipients'] = $this->app_lib->getStaffList($schoolID);
                unset($this->data['complaint_recipients'][$staffID]);
            }
            $this->data['sub_page'] = 'dashboard/teacher';
        } elseif (is_student_loggedin() || is_parent_loggedin()) {
            $studentID = 0;
            if (is_student_loggedin()) {
                $this->data['title'] = translate('welcome_to') . " " . $this->session->userdata('name');
                $studentID = get_loggedin_user_id();
            }elseif (is_parent_loggedin()) {
                $studentID = $this->session->userdata('myChildren_id');
                if (!empty($studentID)) {
                    $this->data['title'] = get_type_name_by_id('student', $studentID, 'first_name') . " - " . translate('dashboard');
                } else {
                    $this->data['title'] = translate('welcome_to') . " " . $this->session->userdata('name');
                }
            }
            $this->data['student_id'] = $studentID;
            $schoolID = get_loggedin_branch_id();
            $this->data['school_id'] = $schoolID;
            $this->data['sub_page'] = 'userrole/dashboard';
        } else {
            if (is_superadmin_loggedin()) {
                if ($this->input->get('school_id')) {
                    $schoolID = $this->input->get('school_id');
                    $this->data['title'] = get_type_name_by_id('branch', $schoolID) . " " . translate('branch_dashboard');
                } else {
                    $this->data['title'] = translate('all_branch_dashboard');
                    $schoolID = "";
                }
            } else {
                $schoolID = get_loggedin_branch_id();
                $this->data['title'] = get_type_name_by_id('branch', $schoolID) . " " . translate('branch_dashboard');
            }
            $getSQLMode = $this->application_model->getSQLMode();
            $this->data['school_id'] = $schoolID;
            $this->data['sqlMode'] = $getSQLMode;
            if ($getSQLMode == false) {
                $this->data['fees_summary'] = $this->dashboard_model->annualFeessummaryCharts($schoolID);
            } else {
                $this->data['fees_summary'] = array(
                    'total_fee' => 0,
                    'total_paid' => 0,
                    'total_due' => 0,
                );
            }
            $this->data['student_by_class'] = $this->dashboard_model->getStudentByClass($schoolID);
            $this->data['income_vs_expense'] = $this->dashboard_model->getIncomeVsExpense($schoolID);
            $this->data['weekend_attendance'] = $this->dashboard_model->getWeekendAttendance($schoolID);
            $this->data['get_monthly_admission'] = $this->dashboard_model->getMonthlyAdmission($schoolID);
            $this->data['get_voucher'] = $this->dashboard_model->getVoucher($schoolID);
            $this->data['get_transport_route'] = $this->dashboard_model->get_transport_route($schoolID);
            $this->data['get_total_student'] = $this->dashboard_model->get_total_student($schoolID);
            $this->data['sub_page'] = 'dashboard/index';
        }
        $language = 'en';
        $jsArray = array(
            'vendor/chartjs/chart.min.js',
            'vendor/echarts/echarts.common.min.js',
            'vendor/moment/moment.js',
            'vendor/fullcalendar/fullcalendar.js',
        ); 
        if ($this->session->userdata('set_lang') != 'english') {
            $language = $this->dashboard_model->languageShortCodes($this->session->userdata('set_lang'));
            $jsArray[] = "vendor/fullcalendar/locale/$language.js";
        }
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/fullcalendar/fullcalendar.css',
            ),
            'js' => $jsArray
        );
        $this->data['language'] = $language;
        $this->data['main_menu'] = 'dashboard';
        $this->load->view('layout/index', $this->data);
    }

    public function submit_teacher_complaint()
    {
        if (!is_teacher_loggedin() || !moduleIsEnabled('reception') || !$this->db->table_exists('complaint')) {
            access_denied();
        }

        $staffID = (int) get_loggedin_user_id();
        $branchID = (int) get_loggedin_branch_id();
        $this->form_validation->set_rules('type_id', translate('complaint') . ' ' . translate('type'), 'trim|required|integer');
        $this->form_validation->set_rules('staff_id', translate('assign_to'), 'trim|required|integer');
        $this->form_validation->set_rules('subject', 'Subject', 'trim|required|max_length[120]');
        $this->form_validation->set_rules('note', translate('note'), 'trim|required|max_length[1000]');

        if ($branchID < 1 || $this->form_validation->run() === false) {
            set_alert('error', 'Please select a complaint type and recipient, and enter a subject and description.');
            redirect(base_url('dashboard'));
        }

        $typeID = (int) $this->input->post('type_id');
        $recipientID = (int) $this->input->post('staff_id');
        $category = $this->db->get_where('complaint_type', array(
            'id' => $typeID,
            'branch_id' => $branchID,
        ), 1)->row_array();
        $recipient = $this->db->select('s.id')
            ->from('staff as s')
            ->join('login_credential as lc', 'lc.user_id = s.id AND lc.role NOT IN (6, 7)', 'inner')
            ->where('s.id', $recipientID)
            ->where('s.branch_id', $branchID)
            ->where('s.id !=', $staffID)
            ->limit(1)
            ->get()
            ->row_array();

        if (empty($category) || empty($recipient)) {
            set_alert('error', 'The selected complaint type or recipient is not available for your school.');
            redirect(base_url('dashboard'));
        }

        $subject = trim((string) $this->input->post('subject'));
        $description = trim((string) $this->input->post('note'));
        $this->load->model('reception_model');
        $this->reception_model->complaintSave(array(
            'complainant_name' => get_type_name_by_id('staff', $staffID),
            'phone_number' => '',
            'type_id' => $typeID,
            'staff_id' => $recipientID,
            'date' => date('Y-m-d'),
            'note' => 'Subject: ' . $subject . "\n\n" . $description,
        ));

        set_alert('success', 'Your complaint has been submitted successfully.');
        redirect(base_url('dashboard'));
    }
}
