<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Authentication extends Authentication_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /* email is okey lets check the password now */
    public function index($url_alias = '')
    {
        // an already logged in user may open this page to add one more account
        if (is_loggedin() && !$_POST && !$this->input->get('add_account')) {
            redirect(base_url('dashboard'));
        }
        $loginUrl = is_loggedin() ? base_url('authentication?add_account=1') : base_url('authentication');

        if ($_POST) {
            $rules = array(
                array(
                    'field' => 'email',
                    'label' => "Email",
                    'rules' => 'trim|required',
                ),
                array(
                    'field' => 'password',
                    'label' => "Password",
                    'rules' => 'trim|required',
                ),
            );
            $this->form_validation->set_rules($rules);
            if ($this->form_validation->run() !== false) {
                $email = $this->input->post('email');
                $password = $this->input->post('password');
                $login_role = $this->input->post('login_role');
                if (!empty($login_role)) {
                    $this->session->set_flashdata('login_role', $login_role);
                }
                // username is okey lets check the password now
                $login_credential = $this->authentication_model->login_credential($email, $password);
                if ($login_credential) {
                    // Super Admin (platform owner) and every other account use separate login tabs
                    if (!empty($login_role)) {
                        if ($login_credential->role == 1 && $login_role !== 'superadmin') {
                            set_alert('error', 'This is a Super Admin account. Please login from the Super Admin tab.');
                            redirect($loginUrl);
                            exit();
                        }
                        if ($login_credential->role != 1 && $login_role === 'superadmin') {
                            set_alert('error', 'The Super Admin tab is only for the platform Super Admin. Please use the Admin tab or your own role tab.');
                            redirect($loginUrl);
                            exit();
                        }
                    }
                    if ($login_credential->active) {
                        $getUser = $this->authentication_model->getUserNameByRoleID($login_credential->role, $login_credential->user_id);
                        $getConfig = $this->db->select('translation,session_id')->get_where('global_settings', array('id' => 1))->row();
                        $language = $getConfig->translation;
                        $sessionID = $getConfig->session_id;
                        $extraSession = array();
                        if($this->app_lib->isExistingAddon('saas')) {
                            if ($login_credential->role != 1) {
                                $schoolSettings = $this->db->select('id,translation')->where(array('id' => $getUser['branch_id'], 'status' => 1))->get('branch')->row();
                                if (empty($schoolSettings)) {
                                    set_alert('error', translate('inactive_school'));
                                    redirect($loginUrl);
                                    exit();
                                }
                            }
                            if ($login_credential->role != 1) {
                                $language = $schoolSettings->translation;
                            }
                        }
                        // login user type
                        if ($login_credential->role == 6) {
                            $userType = 'parent';
                            // check parent login status
                            $getParentLoginStatus = $this->authentication_model->getParentLoginStatus($getUser['branch_id']);
                            if ($getParentLoginStatus == 0) {
                                set_alert('error', translate('parent_login_has_been_disabled'));
                                redirect($loginUrl);
                                exit();
                            }
                        } elseif($login_credential->role == 7) {
                            $studentID = $getUser['id'];
                            $extraSession['student_id'] = $studentID;
                            // check student login status
                            $getStudentLoginStatus = $this->authentication_model->getStudentLoginStatus($getUser['branch_id']);
                            if ($getStudentLoginStatus == 0) {
                                set_alert('error', translate('student_login_has_been_disabled'));
                                redirect($loginUrl);
                                exit();
                            }
                            $studentSession = $this->application_model->getEnrollID($studentID, $sessionID);
                            if (is_array($studentSession)) {
                                $extraSession['enrollID'] = $studentSession['id'];
                                $sessionID = $studentSession['session_id'];
                            } else {
                                $extraSession['enrollID'] = $studentSession;
                            }
                            $userType = 'student';
                        } else {
                            $userType = 'staff';
                        }
                        $isRTL = $this->app_lib->getRTLStatus($language);
                        // get logger name
                        $sessionData = array(
                            'name' => $getUser['name'],
                            'logger_photo' => $getUser['photo'],
                            'loggedin_branch' => $getUser['branch_id'],
                            'loggedin_email' => $getUser['email'],
                            'loggedin_id' => $login_credential->id,
                            'loggedin_userid' => $login_credential->user_id,
                            'loggedin_role_id' => $login_credential->role,
                            'loggedin_type' => $userType,
                            'set_lang' =>  $language,
                            'is_rtl' => $isRTL,
                            'set_session_id' => $sessionID,
                            'loggedin' => true,
                        );

                        // two factor authentication
                        if($this->app_lib->isExistingAddon('two_fa') && moduleIsEnabled('two_fa')) {
                            $this->load->model('two_fa_model');
                            $manage_2FA = $this->two_fa_model->manage_2fa($sessionData, $login_credential, $getUser['email']);
                            if ($manage_2FA) {
                                if (!empty($extraSession)) {
                                    $this->session->set_userdata($extraSession);
                                }
                                $this->session->set_userdata('2FA', $sessionData);
                                redirect(base_url($this->authentication_model->getSegment(1) . 'two_fa_verification'));
                            } else {
                                $this->authentication_model->activateAccount($sessionData, $extraSession);
                            }
                        } else {
                            $this->authentication_model->activateAccount($sessionData, $extraSession);
                        }
                        
                        // is logged in
                        if ($this->session->has_userdata('redirect_url')) {
                            redirect($this->session->userdata('redirect_url'));
                        } else {
                            redirect(base_url('dashboard'));
                        }
                    } else {
                        set_alert('error', translate('inactive_account'));
                        redirect($loginUrl);
                    }
                } else {
                    set_alert('error', translate('username_password_incorrect'));
                    redirect($loginUrl);
                }
            }
        }
        $this->data['branch_id'] = $this->authentication_model->urlaliasToBranch($url_alias);
        if (empty($this->data['branch_id'])) {
            $this->data['branch_id'] = $this->authentication_model->getDefaultLoginBranchId();
        }
        $schoolDeatls = $this->authentication_model->getSchoolDeatls($url_alias, $this->data['branch_id']);
        if (!empty($schoolDeatls) && is_object($schoolDeatls)) {
            $this->data['global_config']['institute_name'] = $schoolDeatls->school_name;
            $this->data['global_config']['address'] = $schoolDeatls->address;
            if (!empty($schoolDeatls->mobileno)) {
                $this->data['global_config']['mobileno'] = $schoolDeatls->mobileno;
            }
            $this->data['global_config']['facebook_url'] = $schoolDeatls->facebook_url;
            $this->data['global_config']['twitter_url'] = $schoolDeatls->twitter_url;
            $this->data['global_config']['linkedin_url'] = $schoolDeatls->linkedin_url;
            $this->data['global_config']['youtube_url'] = $schoolDeatls->youtube_url;
        }
        $this->data['login_slider_images'] = $this->authentication_model->getLoginSliderImages($this->data['branch_id']);
        $this->data['login_sidebox_image'] = $this->authentication_model->getLoginSideboxImage($this->data['branch_id'], $this->data['login_slider_images']);
        $this->load->view('authentication/login', $this->data);
    }

    // forgot password
    public function forgot($url_alias = '')
    {
        if (is_loggedin()) {
            redirect(base_url('dashboard'), 'refresh');
        }

        if ($_POST) {
            $config = array(
                array(
                    'field' => 'username',
                    'label' => 'Email',
                    'rules' => 'trim|required',
                ),
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() !== false) {
                $username = $this->input->post('username');
                $res = $this->authentication_model->lose_password($username);
                if ($res == true) {
                    $this->session->set_flashdata('reset_res', 'true');
                    redirect(base_url('authentication/forgot'));
                } else {
                    $this->session->set_flashdata('reset_res', 'false');
                    redirect(base_url('authentication/forgot'));
                }
            }
        }
        $this->data['branch_id'] = $this->authentication_model->urlaliasToBranch($url_alias);
        if (empty($this->data['branch_id'])) {
            $this->data['branch_id'] = $this->authentication_model->getDefaultLoginBranchId();
        }
        $schoolDeatls = $this->authentication_model->getSchoolDeatls($url_alias, $this->data['branch_id']);
        if (!empty($schoolDeatls) && is_object($schoolDeatls)) {
            $this->data['global_config']['institute_name'] = $schoolDeatls->school_name;
            $this->data['global_config']['address'] = $schoolDeatls->address;
            if (!empty($schoolDeatls->mobileno)) {
                $this->data['global_config']['mobileno'] = $schoolDeatls->mobileno;
            }
            $this->data['global_config']['facebook_url'] = $schoolDeatls->facebook_url;
            $this->data['global_config']['twitter_url'] = $schoolDeatls->twitter_url;
            $this->data['global_config']['linkedin_url'] = $schoolDeatls->linkedin_url;
            $this->data['global_config']['youtube_url'] = $schoolDeatls->youtube_url;
        }
        $this->data['login_slider_images'] = $this->authentication_model->getLoginSliderImages($this->data['branch_id']);
        $this->data['login_sidebox_image'] = $this->authentication_model->getLoginSideboxImage($this->data['branch_id'], $this->data['login_slider_images']);
        $this->load->view('authentication/forgot', $this->data);
    }

    /* password reset */
    public function pwreset()
    {
        if (is_loggedin()) {
            redirect(base_url('dashboard'), 'refresh');
        }

        $key = $this->input->get('key');
        if (!empty($key)) {
            $query = $this->db->get_where('reset_password', array('key' => $key));
            if ($query->num_rows() > 0) {
                if ($this->input->post()) {
                    $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[4]|matches[c_password]');
                    $this->form_validation->set_rules('c_password', 'Confirm Password', 'trim|required|min_length[4]');
                    if ($this->form_validation->run() !== false) {
                        $password = $this->app_lib->pass_hashed($this->input->post('password'));
                        $this->db->where('id', $query->row()->login_credential_id);
                        $this->db->update('login_credential', array('password' => $password));
                        $this->db->where('login_credential_id', $query->row()->login_credential_id);
                        $this->db->delete('reset_password');
                        set_alert('success', 'Password Reset Successfully');
                        redirect(base_url('authentication'));
                    }
                }
                $this->data['branch_id'] = $this->authentication_model->getDefaultLoginBranchId();
                $schoolDeatls = $this->authentication_model->getSchoolDeatls('', $this->data['branch_id']);
                if (!empty($schoolDeatls) && is_object($schoolDeatls)) {
                    $this->data['global_config']['institute_name'] = $schoolDeatls->school_name;
                    $this->data['global_config']['address'] = $schoolDeatls->address;
                    if (!empty($schoolDeatls->mobileno)) {
                        $this->data['global_config']['mobileno'] = $schoolDeatls->mobileno;
                    }
                }
                $this->data['login_slider_images'] = $this->authentication_model->getLoginSliderImages($this->data['branch_id']);
                $this->data['login_sidebox_image'] = $this->authentication_model->getLoginSideboxImage($this->data['branch_id'], $this->data['login_slider_images']);
                $this->load->view('authentication/pwreset', $this->data);
            } else {
                set_alert('error', 'Token Has Expired');
                redirect(base_url('authentication'));
            }
        } else {
            set_alert('error', 'Token Has Expired');
            redirect(base_url('authentication'));
        }
    }

    /* restore prior session after login-as-student / parent / employee */
    public function exit_impersonate()
    {
        $backup = $this->session->userdata('impersonate_backup');
        if (empty($backup) || !is_array($backup)) {
            redirect(base_url('dashboard'));
        }
        $returnPath = 'student/view';
        if (!empty($backup['_impersonate_return'])) {
            $returnPath = $backup['_impersonate_return'];
            unset($backup['_impersonate_return']);
        }
        $this->session->unset_userdata('impersonate_backup');
        foreach (array('student_id', 'enrollID', 'myChildren_id') as $k) {
            $this->session->unset_userdata($k);
        }
        $this->session->set_userdata($backup);
        set_alert('success', translate('return_to_admin'));
        redirect(base_url($returnPath));
    }

    /* switch between the accounts that are signed in on this browser */
    public function switch_account($loginID = '')
    {
        if (!is_loggedin()) {
            redirect(base_url('authentication'));
        }
        if ($this->authentication_model->switchAccount((int) $loginID)) {
            redirect(base_url('dashboard'));
        }
        set_alert('error', translate('access_denied'));
        redirect(base_url('dashboard'));
    }

    /* session logout */
    public function logout()
    {
        if ($this->session->userdata('impersonate_backup')) {
            $backup = $this->session->userdata('impersonate_backup');
            $this->session->unset_userdata('impersonate_backup');
            foreach (array('student_id', 'enrollID', 'myChildren_id') as $k) {
                $this->session->unset_userdata($k);
            }
            unset($backup['_impersonate_return']);
            $this->session->set_userdata($backup);
        }
        // other accounts are still signed in: continue with the next one
        if (!$this->input->get('all') && $this->authentication_model->activateNextLinkedAccount()) {
            set_alert('success', translate('account_logged_out_switched_to_next'));
            redirect(base_url('dashboard'));
        }
        $webURL = base_url();
        if (!is_superadmin_loggedin()) {
            $cmsRow = $this->db->select('cms_active,url_alias')
            ->where('branch_id', get_loggedin_branch_id())
            ->get('front_cms_setting')->row_array();
            if (isset($cmsRow['cms_active']) && $cmsRow['cms_active'] == 1) {
                $webURL = base_url((isset($cmsRow['url_alias']) ? $cmsRow['url_alias'] : '') );
            }
        }

        $this->session->unset_userdata('name');
        $this->session->unset_userdata('logger_photo');
        $this->session->unset_userdata('loggedin_id');
        $this->session->unset_userdata('loggedin_userid');
        $this->session->unset_userdata('loggedin_type');
        $this->session->unset_userdata('set_lang');
        $this->session->unset_userdata('set_session_id');
        $this->session->unset_userdata('loggedin_branch');
        $this->session->unset_userdata('loggedin');
        $this->session->sess_destroy();
        redirect($webURL, 'refresh');
    }

}
