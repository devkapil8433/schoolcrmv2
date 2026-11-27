<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Central server-side gate for every protected request.
 *
 * Called from MY_Controller, so it covers all web pages and AJAX calls (and any API controller
 * that extends MY_Controller). Order of checks:
 *   1. authenticated user (public pages are not touched)
 *   2. super admin  -> always allowed
 *   3. branch active, school active, subscription state (Active / Grace / Expired / Suspended)
 *   4. permissions are then checked by the existing get_permission() calls in each controller
 *
 * API controllers that do not extend MY_Controller should call
 *   $this->access_guard->api_check($branchID);
 * which answers with the standard JSON contract below (HTTP 402 / 403).
 */
class Access_guard
{
    protected $CI;

    // controllers that must stay reachable when a school is blocked
    protected $always_allowed = array('authentication', 'subscription_notice', 'errors', 'install');

    public function __construct()
    {
        $this->CI = &get_instance();
    }

    public function run()
    {
        if (!$this->CI->session->userdata('loggedin')) {
            return;
        }
        if (is_superadmin_loggedin()) {
            return;
        }
        // subscription tables not migrated yet -> feature not installed
        if (!$this->CI->db->table_exists('school_subscription')) {
            return;
        }
        $class = strtolower($this->CI->router->class);
        if (in_array($class, $this->always_allowed, true)) {
            return;
        }
        $this->CI->load->model('subscription_model');
        $access = $this->CI->subscription_model->getBranchAccess(get_loggedin_branch_id());
        if ($access['allowed']) {
            // only School/Branch admins see renewal warnings
            if (!empty($access['warning']) && is_admin_loggedin()) {
                $this->CI->data['subscription_notice'] = $access['warning'];
            }
            return;
        }
        // an inactive branch must not lock a School Admin out of branch management (to switch branches)
        if ($access['code'] === 'BRANCH_INACTIVE' && $class === 'branch_manager' && is_admin_loggedin()
            && !empty($this->CI->subscription_model->getSchoolIDsForAdmin(get_loggedin_user_id()))) {
            return;
        }
        $this->deny($access);
    }

    public function deny($access)
    {
        $status = ($access['code'] === 'BRANCH_INACTIVE' || $access['code'] === 'SCHOOL_INACTIVE') ? 403 : 402;
        $this->CI->output->set_status_header($status);
        if ($this->wants_json()) {
            $this->CI->output->set_content_type('application/json')->set_output(json_encode($this->payload($access)));
        } else {
            $data = array('access' => $access, 'global_config' => $this->CI->data['global_config']);
            $this->CI->load->view('subscription/blocked', $data);
        }
        $this->CI->output->_display();
        exit;
    }

    // standard response for mobile apps / APIs
    public function payload($access)
    {
        return array(
            'status' => false,
            'code' => $access['code'],
            'subscription_state' => $access['state'],
            'message' => $access['message'],
            'expiry_date' => $access['expiry_date'],
            'grace_end_date' => $access['grace_end'],
            'contact' => $access['support_message'],
        );
    }

    // for API controllers: returns true when allowed, otherwise sends the JSON error and exits
    public function api_check($branchID)
    {
        $this->CI->load->model('subscription_model');
        $access = $this->CI->subscription_model->getBranchAccess($branchID);
        if ($access['allowed']) {
            return true;
        }
        $status = ($access['code'] === 'BRANCH_INACTIVE' || $access['code'] === 'SCHOOL_INACTIVE') ? 403 : 402;
        $this->CI->output->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode($this->payload($access)));
        $this->CI->output->_display();
        exit;
    }

    protected function wants_json()
    {
        if ($this->CI->input->is_ajax_request()) {
            return true;
        }
        $accept = (string) $this->CI->input->server('HTTP_ACCEPT');
        if (stripos($accept, 'application/json') !== false && stripos($accept, 'text/html') === false) {
            return true;
        }
        return strtolower((string) $this->CI->uri->segment(1)) === 'api';
    }
}
