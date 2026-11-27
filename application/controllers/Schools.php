<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Platform Super Admin: schools (tenants) and their School Admins. */
class Schools extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!is_superadmin_loggedin()) {
            access_denied();
        }
        $this->load->model('subscription_model');
    }

    public function index()
    {
        $this->data['schools'] = $this->subscription_model->getSchoolsWithSubscription();
        $this->render('schools/index', translate('school_management'));
    }

    public function edit($id = '')
    {
        $id = (int) $id;
        $school = $id ? $this->db->get_where('school', array('id' => $id))->row() : null;
        if ($id && empty($school)) {
            show_404();
        }
        if ($this->input->post('save')) {
            $this->form_validation->set_rules('name', translate('school_name'), 'trim|required|max_length[255]|callback_unique_school_name');
            $this->form_validation->set_rules('code', translate('code'), 'trim|max_length[50]|callback_unique_school_code');
            $this->form_validation->set_rules('email', translate('email'), 'trim|valid_email');
            if ($this->form_validation->run() !== false) {
                $row = array(
                    'name' => $this->input->post('name'),
                    'code' => $this->input->post('code') ?: null,
                    'email' => $this->input->post('email'),
                    'mobileno' => $this->input->post('mobileno'),
                    'status' => $this->input->post('status') ? 1 : 0,
                );
                if ($school) {
                    $this->db->where('id', $id)->update('school', $row);
                    $this->subscription_model->audit(
                        $school->status != $row['status'] ? ($row['status'] ? 'school_activated' : 'school_deactivated') : 'school_updated',
                        'school', $id, $id, null,
                        array('name' => $school->name, 'status' => (int) $school->status), $row
                    );
                } else {
                    $this->db->insert('school', $row);
                    $id = $this->db->insert_id();
                    $this->subscription_model->audit('school_created', 'school', $id, $id, null, null, $row);
                    set_alert('success', translate('information_has_been_saved_successfully'));
                    redirect(base_url('subscriptions/edit/' . $id));
                }
                set_alert('success', translate('information_has_been_updated_successfully'));
                redirect(base_url('schools'));
            }
        }
        $this->data['school'] = $school;
        $this->data['admins'] = $school ? $this->getAdmins($id) : array();
        $this->data['candidates'] = $school ? $this->getAdminCandidates($id) : array();
        $this->data['school_branches'] = $school ? $this->db->where('school_id', $id)->order_by('name')->get('branch')->result() : array();
        $this->render('schools/form', translate('school_management'));
    }

    public function add_admin()
    {
        $this->postOnly();
        $schoolID = (int) $this->input->post('school_id');
        $staffID = (int) $this->input->post('staff_id');
        $ok = $this->db->query(
            "SELECT `s`.`id` FROM `staff` `s` INNER JOIN `login_credential` `lc` ON `lc`.`user_id` = `s`.`id` AND `lc`.`role` = 2 AND `lc`.`active` = 1
             INNER JOIN `branch` `b` ON `b`.`id` = `s`.`branch_id` WHERE `s`.`id` = ? AND `b`.`school_id` = ?",
            array($staffID, $schoolID)
        )->row();
        if (empty($ok)) {
            set_alert('error', translate('access_denied'));
        } else {
            $this->db->query('INSERT IGNORE INTO `school_admin` (`school_id`, `staff_id`) VALUES (?, ?)', array($schoolID, $staffID));
            $this->subscription_model->audit('school_admin_assigned', 'school_admin', $staffID, $schoolID, null, null, array('staff_id' => $staffID));
            set_alert('success', translate('information_has_been_saved_successfully'));
        }
        redirect(base_url('schools/edit/' . $schoolID));
    }

    // create a new Admin (role 2) staff account for one branch and make it School Admin of the school
    public function create_admin()
    {
        $this->postOnly();
        $schoolID = (int) $this->input->post('school_id');
        $this->form_validation->set_rules('branch_id', translate('branch'), 'required|integer');
        $this->form_validation->set_rules('name', translate('name'), 'trim|required|max_length[255]');
        $this->form_validation->set_rules('email', translate('email'), 'trim|required|valid_email');
        $this->form_validation->set_rules('mobileno', translate('mobile_no'), 'trim');
        $this->form_validation->set_rules('username', translate('username'), 'trim|required|min_length[3]|max_length[100]|is_unique[login_credential.username]');
        $this->form_validation->set_rules('password', translate('password'), 'trim|required|min_length[6]');
        $branch = $this->db->get_where('branch', array('id' => (int) $this->input->post('branch_id'), 'school_id' => $schoolID))->row();
        if ($this->form_validation->run() === false || empty($branch)) {
            $errors = $this->form_validation->error_array();
            if (empty($branch)) {
                $errors[] = 'Select a branch of this school.';
            }
            set_alert('error', implode(' ', $errors));
            redirect(base_url('schools/edit/' . $schoolID));
        }
        $this->db->trans_start();
        $this->db->insert('staff', array(
            'staff_id' => substr(app_generate_hash(), 3, 7),
            'branch_id' => $branch->id,
            'name' => $this->input->post('name'),
            'email' => $this->input->post('email'),
            'mobileno' => (string) $this->input->post('mobileno'),
            'department' => 0, 'designation' => 0, 'qualification' => '', 'joining_date' => date('Y-m-d'),
            'birthday' => '', 'sex' => '', 'religion' => '', 'blood_group' => '', 'present_address' => '', 'permanent_address' => '',
        ));
        $staffID = $this->db->insert_id();
        $this->db->insert('login_credential', array(
            'user_id' => $staffID,
            'username' => $this->input->post('username'),
            'password' => $this->app_lib->pass_hashed($this->input->post('password')),
            'role' => 2,
            'active' => 1,
        ));
        $this->db->insert('school_admin', array('school_id' => $schoolID, 'staff_id' => $staffID));
        $this->subscription_model->audit('school_admin_created', 'school_admin', $staffID, $schoolID, $branch->id, null,
            array('name' => $this->input->post('name'), 'username' => $this->input->post('username'), 'branch_id' => $branch->id));
        $this->db->trans_complete();
        set_alert($this->db->trans_status() ? 'success' : 'error', $this->db->trans_status()
            ? 'School Admin created. Login from the Admin tab with the username and password you set.' : translate('something_went_wrong'));
        redirect(base_url('schools/edit/' . $schoolID));
    }

    public function remove_admin()
    {
        $this->postOnly();
        $schoolID = (int) $this->input->post('school_id');
        $staffID = (int) $this->input->post('staff_id');
        $this->db->delete('school_admin', array('school_id' => $schoolID, 'staff_id' => $staffID));
        $this->subscription_model->audit('school_admin_removed', 'school_admin', $staffID, $schoolID, null, array('staff_id' => $staffID), null);
        set_alert('success', translate('information_has_been_updated_successfully'));
        redirect(base_url('schools/edit/' . $schoolID));
    }

    public function unique_school_name($name)
    {
        $this->db->where('name', $name)->where('id !=', (int) $this->uri->segment(3));
        if ($this->db->count_all_results('school') > 0) {
            $this->form_validation->set_message('unique_school_name', translate('already_taken'));
            return false;
        }
        return true;
    }

    public function unique_school_code($code)
    {
        if ($code === '') {
            return true;
        }
        $this->db->where('code', $code)->where('id !=', (int) $this->uri->segment(3));
        if ($this->db->count_all_results('school') > 0) {
            $this->form_validation->set_message('unique_school_code', translate('already_taken'));
            return false;
        }
        return true;
    }

    private function getAdmins($schoolID)
    {
        return $this->db->query(
            "SELECT `s`.`id`, `s`.`name`, `s`.`email`, `b`.`name` AS `branch_name` FROM `school_admin` `sa`
             INNER JOIN `staff` `s` ON `s`.`id` = `sa`.`staff_id` LEFT JOIN `branch` `b` ON `b`.`id` = `s`.`branch_id`
             WHERE `sa`.`school_id` = ?", array($schoolID)
        )->result();
    }

    // active role-2 staff of this school's branches that are not School Admins yet
    private function getAdminCandidates($schoolID)
    {
        return $this->db->query(
            "SELECT `s`.`id`, `s`.`name`, `b`.`name` AS `branch_name` FROM `staff` `s`
             INNER JOIN `login_credential` `lc` ON `lc`.`user_id` = `s`.`id` AND `lc`.`role` = 2 AND `lc`.`active` = 1
             INNER JOIN `branch` `b` ON `b`.`id` = `s`.`branch_id` AND `b`.`school_id` = ?
             WHERE `s`.`id` NOT IN (SELECT `staff_id` FROM `school_admin` WHERE `school_id` = ?)", array($schoolID, $schoolID)
        )->result();
    }

    private function postOnly()
    {
        if (!$_POST) {
            show_404();
        }
    }

    private function render($page, $title)
    {
        $this->data['title'] = $title;
        $this->data['sub_page'] = $page;
        $this->data['main_menu'] = 'platform_schools';
        $this->load->view('layout/index', $this->data);
    }
}
