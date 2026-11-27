<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Always reachable (even when the subscription has expired):
 *   subscription_notice          human readable status page
 *   subscription_notice/status   JSON status for the logged in user (used by mobile apps)
 */
class Subscription_notice extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('subscription_model');
    }

    public function index()
    {
        if (!is_loggedin()) {
            redirect(base_url('authentication'));
        }
        if (is_superadmin_loggedin()) {
            redirect(base_url('subscriptions'));
        }
        $access = $this->subscription_model->getBranchAccess(get_loggedin_branch_id());
        if ($access['allowed']) {
            redirect(base_url('dashboard'));
        }
        $this->load->view('subscription/blocked', array('access' => $access, 'global_config' => $this->data['global_config']));
    }

    public function status()
    {
        $this->output->enable_profiler(false);
        if (!is_loggedin()) {
            $this->output->set_status_header(401)->set_content_type('application/json')
                ->set_output(json_encode(array('status' => false, 'code' => 'UNAUTHENTICATED', 'message' => 'Login required.')));
            return;
        }
        if (is_superadmin_loggedin()) {
            $payload = array('status' => true, 'code' => 'OK', 'subscription_state' => 'platform_admin');
        } else {
            $access = $this->subscription_model->getBranchAccess(get_loggedin_branch_id());
            $payload = $access['allowed']
                ? array('status' => true, 'code' => 'OK', 'subscription_state' => $access['state'], 'expiry_date' => $access['expiry_date'],
                    'grace_end_date' => $access['grace_end'], 'days_left' => $access['days_left'], 'warning' => $access['warning'])
                : $this->access_guard->payload($access);
            if (!$access['allowed']) {
                $this->output->set_status_header(($access['code'] === 'BRANCH_INACTIVE' || $access['code'] === 'SCHOOL_INACTIVE') ? 403 : 402);
            }
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }
}
