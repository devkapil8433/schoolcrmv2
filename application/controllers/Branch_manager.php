<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Branch management for the Super Admin (all schools) and School Admins (own school's branches only).
 * Deactivating a branch only flips branch.status; no student/teacher/fee/attendance data is touched.
 */
class Branch_manager extends Admin_Controller
{
    private $schoolIDs; // null = every school (super admin)

    public function __construct()
    {
        parent::__construct();
        $this->load->model('subscription_model');
        $this->schoolIDs = $this->subscription_model->manageableSchoolIDs();
        if ($this->schoolIDs !== null && empty($this->schoolIDs)) {
            access_denied();
        }
    }

    public function index()
    {
        $this->db->select('b.*, s.name AS school_label, lc.username AS changed_by_name');
        $this->db->from('branch b');
        $this->db->join('school s', 's.id = b.school_id', 'left');
        $this->db->join('login_credential lc', 'lc.id = b.status_changed_by', 'left');
        $this->scopeToManageable('b.school_id');
        $schoolID = (int) $this->input->get('school_id');
        if ($schoolID) {
            $this->db->where('b.school_id', $schoolID);
        }
        $status = $this->input->get('status');
        if ($status === '0' || $status === '1') {
            $this->db->where('b.status', (int) $status);
        }
        $q = trim((string) $this->input->get('q'));
        if ($q !== '') {
            $this->db->group_start()->like('b.name', $q)->or_like('b.city', $q)->or_like('b.school_name', $q)->group_end();
        }
        $this->db->order_by('s.name')->order_by('b.name');
        $this->data['branches'] = $this->db->get()->result();
        $this->data['schools'] = $this->manageableSchools();
        $this->data['filters'] = array('school_id' => $schoolID, 'status' => $status, 'q' => $q);
        $this->render('branch_manager/index');
    }

    public function form($id = '')
    {
        $id = (int) $id;
        $branch = $id ? $this->getManageableBranch($id) : null;
        $schools = $this->manageableSchools();
        if ($this->input->post('save')) {
            $this->form_validation->set_rules('branch_name', translate('branch_name'), 'trim|required|max_length[255]|callback_unique_branch_name');
            $this->form_validation->set_rules('school_id', translate('school'), 'trim|required|integer');
            $this->form_validation->set_rules('email', translate('email'), 'trim|required|valid_email');
            $this->form_validation->set_rules('mobileno', translate('mobile_no'), 'trim|required');
            $this->form_validation->set_rules('currency', translate('currency'), 'trim|required');
            $this->form_validation->set_rules('currency_symbol', translate('currency_symbol'), 'trim|required');
            if ($this->form_validation->run() !== false) {
                $school = $this->db->get_where('school', array('id' => (int) $this->input->post('school_id')))->row();
                // School Admins may only use their own schools; only the Super Admin may move a branch to another school
                if (empty($school) || !$this->subscription_model->canManageSchool($school->id)
                    || ($branch && !is_superadmin_loggedin() && $school->id != $branch->school_id)) {
                    access_denied();
                }
                $row = array(
                    'name' => $this->input->post('branch_name'),
                    'school_id' => $school->id,
                    'school_name' => $school->name,
                    'email' => $this->input->post('email'),
                    'mobileno' => $this->input->post('mobileno'),
                    'currency' => $this->input->post('currency'),
                    'symbol' => $this->input->post('currency_symbol'),
                    'city' => (string) $this->input->post('city'),
                    'state' => (string) $this->input->post('state'),
                    'address' => (string) $this->input->post('address'),
                );
                if ($branch) {
                    $this->db->where('id', $branch->id)->update('branch', $row);
                    $this->subscription_model->audit('branch_updated', 'branch', $branch->id, $school->id, $branch->id,
                        array('name' => $branch->name, 'school_id' => $branch->school_id), $row);
                    set_alert('success', translate('information_has_been_updated_successfully'));
                } else {
                    $global = $this->db->select('translation,timezone')->get_where('global_settings', array('id' => 1))->row();
                    $row += array(
                        'status' => 1,
                        'stu_username_prefix' => '', 'stu_default_password' => '', 'grd_username_prefix' => '', 'grd_default_password' => '',
                        'translation' => $global->translation ?: 'english', 'timezone' => $global->timezone ?: 'Asia/Kolkata',
                        'reg_prefix_digit' => 0,
                    );
                    $this->db->insert('branch', $row);
                    $newID = $this->db->insert_id();
                    $this->subscription_model->audit('branch_created', 'branch', $newID, $school->id, $newID, null, $row);
                    set_alert('success', translate('information_has_been_saved_successfully'));
                }
                redirect(base_url('branch_manager'));
            }
        }
        $this->data['branch'] = $branch;
        $this->data['schools'] = $schools;
        $this->render('branch_manager/form');
    }

    public function set_status()
    {
        if (!$_POST) {
            show_404();
        }
        $branch = $this->getManageableBranch((int) $this->input->post('id'));
        $status = $this->input->post('status') ? 1 : 0;
        if (!$status && !is_superadmin_loggedin() && $branch->id == get_loggedin_branch_id()) {
            set_alert('error', 'Switch to another branch before deactivating the branch you are working in.');
        } elseif ((int) $branch->status !== $status) {
            $this->subscription_model->setBranchStatus($branch, $status);
            set_alert('success', $status ? 'Branch activated. Its users have access again.' : 'Branch deactivated. Its users can no longer access it (data is kept).');
        }
        redirect(base_url('branch_manager'));
    }

    // School Admin: work inside another branch of the own school (the app scopes data by the session branch)
    public function switch_branch()
    {
        if (!$_POST || !is_admin_loggedin()) {
            show_404();
        }
        $branch = $this->getManageableBranch((int) $this->input->post('id'));
        $access = $this->subscription_model->getBranchAccess($branch->id);
        if (!$access['allowed']) {
            set_alert('error', $access['message']);
            redirect(base_url('branch_manager'));
        }
        $this->subscription_model->audit('branch_switched', 'branch', $branch->id, $branch->school_id, $branch->id,
            array('branch_id' => get_loggedin_branch_id()), array('branch_id' => $branch->id));
        $this->session->set_userdata('loggedin_branch', $branch->id);
        redirect(base_url('dashboard'));
    }

    public function unique_branch_name($name)
    {
        $this->db->where('name', $name)->where('id !=', (int) $this->uri->segment(3));
        if ($this->db->count_all_results('branch') > 0) {
            $this->form_validation->set_message('unique_branch_name', translate('already_taken'));
            return false;
        }
        return true;
    }

    private function scopeToManageable($column)
    {
        if ($this->schoolIDs !== null) {
            $this->db->where_in($column, $this->schoolIDs);
        }
    }

    private function manageableSchools()
    {
        $this->scopeToManageable('id');
        return $this->db->order_by('name')->get('school')->result();
    }

    private function getManageableBranch($id)
    {
        $branch = $this->db->get_where('branch', array('id' => (int) $id))->row();
        if (empty($branch)) {
            show_404();
        }
        if (!$this->subscription_model->canManageSchool($branch->school_id)) {
            access_denied();
        }
        return $branch;
    }

    private function render($page)
    {
        $this->data['title'] = translate('branch_management');
        $this->data['sub_page'] = $page;
        $this->data['main_menu'] = 'platform_branches';
        $this->load->view('layout/index', $this->data);
    }
}
