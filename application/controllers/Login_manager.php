<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Admin tool: list login credentials (admin / teacher / student / parent / other staff),
 * edit username & password and open any account next to the current one.
 */
class Login_manager extends Admin_Controller
{
    private $groups = array(
        'admin' => 'admin',
        'teacher' => 'teacher',
        'student' => 'student',
        'parent' => 'parent',
        'staff' => 'other_staff',
    );

    public function __construct()
    {
        parent::__construct();
        if (!is_superadmin_loggedin() && !is_admin_loggedin()) {
            access_denied();
        }
        $this->load->model('authentication_model');
    }

    public function index()
    {
        $type = $this->input->get('type');
        if (!isset($this->groups[$type])) {
            $type = 'admin';
        }
        $this->data['type'] = $type;
        $this->data['groups'] = $this->groups;
        $this->data['accounts'] = $this->getAccounts($type);
        $this->data['title'] = translate('login_manager');
        $this->data['sub_page'] = 'login_manager/index';
        $this->data['main_menu'] = 'login_manager';
        $this->load->view('layout/index', $this->data);
    }

    // update username and/or password of one login credential
    public function update()
    {
        if (!$_POST) {
            exit;
        }
        $credential = $this->getManageableCredential($this->input->post('id'));
        if (empty($credential)) {
            echo json_encode(array('status' => 'access_denied'));
            return;
        }
        $this->form_validation->set_rules('username', translate('username'), 'trim|required|min_length[3]|callback_unique_username');
        $this->form_validation->set_rules('new_password', translate('password'), 'trim|min_length[4]');
        if ($this->form_validation->run() == false) {
            echo json_encode(array('status' => 'fail', 'error' => $this->form_validation->error_array()));
            return;
        }
        $data = array('username' => $this->input->post('username'));
        $newPassword = $this->input->post('new_password');
        if ($newPassword !== '' && $newPassword !== null) {
            $data['password'] = $this->app_lib->pass_hashed($newPassword);
        }
        $this->db->where('id', $credential->id);
        $this->db->update('login_credential', $data);
        set_alert('success', translate('information_has_been_updated_successfully'));
        $type = isset($this->groups[$this->input->post('type')]) ? $this->input->post('type') : 'admin';
        echo json_encode(array('status' => 'success', 'url' => base_url('login_manager?type=' . $type)));
    }

    // sign in to the selected account while staying signed in as the current one
    public function open()
    {
        if (!$_POST) {
            show_404();
        }
        $credential = $this->getManageableCredential($this->input->post('id'));
        $type = isset($this->groups[$this->input->post('type')]) ? $this->input->post('type') : 'admin';
        $back = base_url('login_manager?type=' . $type);
        if (empty($credential) || $credential->id == get_loggedin_id()) {
            set_alert('error', translate('access_denied'));
            redirect($back);
        }
        if (!$credential->active) {
            set_alert('error', translate('inactive_account'));
            redirect($back);
        }
        $built = $this->buildAccountSession($credential);
        if (empty($built)) {
            set_alert('error', translate('inactive_school'));
            redirect($back);
        }
        $this->authentication_model->activateAccount($built[0], $built[1]);
        redirect(base_url('dashboard'));
    }

    public function unique_username($username)
    {
        $this->db->where('id !=', (int) $this->input->post('id'));
        $this->db->where('username', $username);
        if ($this->db->get('login_credential')->num_rows() > 0) {
            $this->form_validation->set_message('unique_username', translate('username_has_already_been_used'));
            return false;
        }
        return true;
    }

    private function getAccounts($type)
    {
        $branchFilter = !is_superadmin_loggedin() ? (int) get_loggedin_branch_id() : 0;
        $this->db->from('login_credential as lc');
        if ($type == 'student') {
            $this->db->select("lc.id, lc.username, lc.role, lc.active, lc.last_login, CONCAT_WS(' ', st.first_name, st.last_name) as name, st.register_no as code, c.name as class_name, se.name as section_name", false);
            $this->db->join('student as st', 'st.id = lc.user_id', 'inner');
            $this->db->join('enroll as e', 'e.student_id = st.id AND e.session_id = ' . $this->db->escape(get_session_id()), 'inner');
            $this->db->join('class as c', 'c.id = e.class_id', 'left');
            $this->db->join('section as se', 'se.id = e.section_id', 'left');
            $this->db->where('lc.role', 7);
            if ($branchFilter) {
                $this->db->where('e.branch_id', $branchFilter);
            }
            $this->db->group_by('lc.id');
            $this->db->order_by('st.register_no', 'asc');
        } elseif ($type == 'parent') {
            $this->db->select("lc.id, lc.username, lc.role, lc.active, lc.last_login, p.name as name, '' as code", false);
            $this->db->join('parent as p', 'p.id = lc.user_id', 'inner');
            $this->db->where('lc.role', 6);
            if ($branchFilter) {
                $this->db->where('p.branch_id', $branchFilter);
            }
            $this->db->order_by('p.name', 'asc');
        } else {
            $this->db->select('lc.id, lc.username, lc.role, lc.active, lc.last_login, s.name as name, s.staff_id as code, r.name as role_name');
            $this->db->join('staff as s', 's.id = lc.user_id', 'inner');
            $this->db->join('roles as r', 'r.id = lc.role', 'left');
            if ($type == 'admin') {
                $this->db->where_in('lc.role', array(1, 2));
            } elseif ($type == 'teacher') {
                $this->db->where('lc.role', 3);
            } else {
                $this->db->where_not_in('lc.role', array(1, 2, 3, 6, 7));
            }
            if ($branchFilter) {
                $this->db->where('s.branch_id', $branchFilter);
                $this->db->where('lc.role !=', 1);
            }
            $this->db->order_by('s.name', 'asc');
        }
        return $this->db->get()->result_array();
    }

    // credential the logged-in admin is allowed to manage, otherwise null
    private function getManageableCredential($id)
    {
        $credential = $this->db->get_where('login_credential', array('id' => (int) $id))->row();
        if (empty($credential)) {
            return null;
        }
        if (is_superadmin_loggedin()) {
            return $credential;
        }
        if ($credential->role == 1) {
            return null;
        }
        if ($credential->role == 6) {
            $row = $this->db->select('branch_id')->get_where('parent', array('id' => $credential->user_id))->row();
        } elseif ($credential->role == 7) {
            $row = $this->db->select('branch_id')->where('student_id', $credential->user_id)->order_by('id', 'desc')->limit(1)->get('enroll')->row();
        } else {
            $row = $this->db->select('branch_id')->get_where('staff', array('id' => $credential->user_id))->row();
        }
        if (empty($row) || $row->branch_id != get_loggedin_branch_id()) {
            return null;
        }
        return $credential;
    }

    // same session data a normal login creates; returns array(sessionData, extraSession) or null
    private function buildAccountSession($credential)
    {
        $getUser = $this->authentication_model->getUserNameByRoleID($credential->role, $credential->user_id);
        if (empty($getUser)) {
            return null;
        }
        $getConfig = $this->db->select('translation,session_id')->get_where('global_settings', array('id' => 1))->row();
        $language = $getConfig->translation;
        $sessionID = $getConfig->session_id;
        $extra = array();
        if ($this->app_lib->isExistingAddon('saas') && $credential->role != 1) {
            $schoolSettings = $this->db->select('id,translation')->where(array('id' => $getUser['branch_id'], 'status' => 1))->get('branch')->row();
            if (empty($schoolSettings)) {
                return null;
            }
            $language = $schoolSettings->translation;
        }
        if ($credential->role == 6) {
            $userType = 'parent';
            $this->db->select('e.id as enroll_id, e.student_id');
            $this->db->from('enroll as e');
            $this->db->join('student as s', 's.id = e.student_id', 'inner');
            $this->db->join('login_credential as l', 'l.user_id = s.id AND l.role = 7', 'inner');
            $this->db->where('s.parent_id', $credential->user_id);
            $this->db->where('e.session_id', get_session_id());
            $this->db->where('l.active', 1);
            $this->db->order_by('e.id', 'asc');
            $firstEnroll = $this->db->get()->row();
            if (!empty($firstEnroll)) {
                $extra['myChildren_id'] = $firstEnroll->student_id;
                $extra['enrollID'] = $firstEnroll->enroll_id;
            }
        } elseif ($credential->role == 7) {
            $userType = 'student';
            $extra['student_id'] = $getUser['id'];
            $studentSession = $this->application_model->getEnrollID($getUser['id'], $sessionID);
            if (is_array($studentSession)) {
                $extra['enrollID'] = $studentSession['id'];
                $sessionID = $studentSession['session_id'];
            } else {
                $extra['enrollID'] = $studentSession;
            }
        } else {
            $userType = 'staff';
        }
        $sessionData = array(
            'name' => $getUser['name'],
            'logger_photo' => $getUser['photo'],
            'loggedin_branch' => $getUser['branch_id'],
            'loggedin_email' => $getUser['email'],
            'loggedin_id' => $credential->id,
            'loggedin_userid' => $credential->user_id,
            'loggedin_role_id' => $credential->role,
            'loggedin_type' => $userType,
            'set_lang' => $language,
            'is_rtl' => $this->app_lib->getRTLStatus($language),
            'set_session_id' => $sessionID,
            'loggedin' => true,
        );
        return array($sessionData, $extra);
    }
}
