<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Platform Super Admin: school subscriptions, manual payments, expiry monitoring, policy and audit log.
 * School/Branch admins can never reach this controller.
 */
class Subscriptions extends Admin_Controller
{
    private $methods_list = array('Cash', 'Bank Transfer', 'UPI', 'Cheque', 'Card', 'Other');

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
        $q = trim((string) $this->input->get('q'));
        $state = (string) $this->input->get('state');
        $rows = array();
        foreach ($this->subscription_model->getSchoolsWithSubscription() as $r) {
            if ($q !== '' && stripos($r['name'] . ' ' . $r['code'], $q) === false) {
                continue;
            }
            if ($state !== '' && $r['st']['state'] !== $state) {
                continue;
            }
            $rows[] = $r;
        }
        $this->data['rows'] = $rows;
        $this->data['q'] = $q;
        $this->data['state'] = $state;
        $this->render('subscriptions/index', translate('subscription_management'));
    }

    public function view($schoolID = 0)
    {
        $school = $this->getSchool($schoolID);
        $sub = $this->subscription_model->getSubscriptionBySchool($school->id);
        $this->data['school'] = $school;
        $this->data['sub'] = $sub;
        $this->data['st'] = $this->subscription_model->stateFor($sub);
        $this->data['methods'] = $this->methods_list;
        $this->data['payments'] = $this->db->query(
            "SELECT `p`.*, `lc`.`username` AS `recorded_by_name` FROM `subscription_payment` `p`
             LEFT JOIN `login_credential` `lc` ON `lc`.`id` = `p`.`recorded_by` WHERE `p`.`school_id` = ? ORDER BY `p`.`id` DESC", array($school->id)
        )->result();
        $this->data['history'] = $this->db->where('school_id', $school->id)->order_by('id', 'desc')->limit(100)->get('platform_audit_log')->result();
        $this->render('subscriptions/view', translate('subscription_management'));
    }

    // create / update the subscription record of a school
    public function edit($schoolID = 0)
    {
        $school = $this->getSchool($schoolID);
        $sub = $this->subscription_model->getSubscriptionBySchool($school->id);
        if ($this->input->post('save')) {
            $this->form_validation->set_rules('plan_name', translate('plan'), 'trim|max_length[100]');
            $this->form_validation->set_rules('amount', translate('amount'), 'trim|required|numeric|greater_than_equal_to[0]');
            $this->form_validation->set_rules('start_date', translate('start_date'), 'trim|required|callback_valid_date');
            $this->form_validation->set_rules('expiry_date', translate('expiry_date'), 'trim|required|callback_valid_date|callback_after_start');
            $this->form_validation->set_rules('grace_days', translate('grace_period'), 'trim|integer|greater_than_equal_to[0]|less_than_equal_to[90]');
            if ($this->form_validation->run() !== false) {
                $id = $this->subscription_model->saveSubscription($school->id, $this->input->post());
                set_alert($id ? 'success' : 'error', translate($id ? 'information_has_been_saved_successfully' : 'something_went_wrong'));
                redirect(base_url('subscriptions/view/' . $school->id));
            }
        }
        $this->data['school'] = $school;
        $this->data['sub'] = $sub;
        $this->data['settings'] = $this->subscription_model->getSettings();
        $this->render('subscriptions/form', translate('subscription_management'));
    }

    public function set_status()
    {
        $sub = $this->postedSubscription();
        $status = $this->input->post('status') === 'suspended' ? 'suspended' : 'active';
        if ($sub->admin_status !== $status) {
            $this->subscription_model->setAdminStatus($sub, $status);
        }
        set_alert('success', translate('information_has_been_updated_successfully'));
        redirect(base_url('subscriptions/view/' . $sub->school_id));
    }

    public function set_payment_status()
    {
        $sub = $this->postedSubscription();
        $status = $this->input->post('payment_status') === 'paid' ? 'paid' : 'unpaid';
        if ($sub->payment_status !== $status) {
            $this->subscription_model->setPaymentStatus($sub, $status);
        }
        set_alert('success', translate('information_has_been_updated_successfully'));
        redirect(base_url('subscriptions/view/' . $sub->school_id));
    }

    public function extend()
    {
        $sub = $this->postedSubscription();
        $days = (int) $this->input->post('days');
        $newExpiry = $days > 0
            ? date('Y-m-d', strtotime('+' . $days . ' days', strtotime($sub->expiry_date)))
            : (string) $this->input->post('new_expiry');
        if (!$this->isDate($newExpiry) || $newExpiry <= $sub->expiry_date) {
            set_alert('error', 'The new expiry date must be after the current expiry date (' . $sub->expiry_date . ').');
        } else {
            $this->subscription_model->extendExpiry($sub, $newExpiry, (string) $this->input->post('note'));
            set_alert('success', 'Expiry extended to ' . date('d M Y', strtotime($newExpiry)) . '.');
        }
        redirect(base_url('subscriptions/view/' . $sub->school_id));
    }

    public function add_payment()
    {
        $sub = $this->postedSubscription();
        $amount = $this->input->post('amount');
        $date = (string) $this->input->post('payment_date');
        $method = trim((string) $this->input->post('method'));
        $extendTo = trim((string) $this->input->post('extend_to'));
        if (!is_numeric($amount) || $amount <= 0 || !$this->isDate($date) || !in_array($method, $this->methods_list, true)
            || ($extendTo !== '' && !$this->isDate($extendTo))) {
            set_alert('error', 'Enter a valid amount, payment date and method.');
        } else {
            $ok = $this->subscription_model->addPayment($sub, array(
                'amount' => $amount,
                'payment_date' => $date,
                'method' => $method,
                'reference_no' => substr(trim((string) $this->input->post('reference_no')), 0, 100),
                'note' => substr(trim((string) $this->input->post('note')), 0, 255),
            ), $extendTo, (bool) $this->input->post('mark_paid'));
            set_alert($ok ? 'success' : 'error', $ok ? translate('information_has_been_saved_successfully') : translate('something_went_wrong'));
        }
        redirect(base_url('subscriptions/view/' . $sub->school_id));
    }

    public function void_payment()
    {
        if (!$_POST) {
            show_404();
        }
        $payment = $this->db->get_where('subscription_payment', array('id' => (int) $this->input->post('payment_id'), 'status' => 'recorded'))->row();
        $reason = trim((string) $this->input->post('reason'));
        if (empty($payment) || $reason === '') {
            set_alert('error', 'A reason is required to void a payment.');
            redirect(base_url('subscriptions'));
        }
        $this->subscription_model->voidPayment($payment, substr($reason, 0, 255));
        set_alert('success', translate('information_has_been_updated_successfully'));
        redirect(base_url('subscriptions/view/' . $payment->school_id));
    }

    public function payments()
    {
        $this->db->select('p.*, s.name AS school_name, lc.username AS recorded_by_name');
        $this->db->from('subscription_payment p');
        $this->db->join('school s', 's.id = p.school_id', 'inner');
        $this->db->join('login_credential lc', 'lc.id = p.recorded_by', 'left');
        $schoolID = (int) $this->input->get('school_id');
        if ($schoolID) {
            $this->db->where('p.school_id', $schoolID);
        }
        $from = (string) $this->input->get('from');
        $to = (string) $this->input->get('to');
        if ($this->isDate($from)) {
            $this->db->where('p.payment_date >=', $from);
        }
        if ($this->isDate($to)) {
            $this->db->where('p.payment_date <=', $to);
        }
        $this->db->order_by('p.payment_date', 'desc')->order_by('p.id', 'desc');
        $this->data['payments'] = $this->db->get()->result();
        $this->data['schools'] = $this->db->order_by('name')->get('school')->result();
        $this->data['filters'] = array('school_id' => $schoolID, 'from' => $from, 'to' => $to);
        $this->render('subscriptions/payments', translate('payment_history'));
    }

    // about to expire (within N days), in grace period or expired
    public function expiring()
    {
        $days = (int) $this->input->get('days');
        $days = $days > 0 ? min($days, 365) : (int) $this->subscription_model->getSettings()->warning_days;
        $expiring = $grace = $expired = $suspended = array();
        foreach ($this->subscription_model->getSchoolsWithSubscription() as $r) {
            $st = $r['st']['state'];
            if ($st === 'active' && $r['st']['days_left'] <= $days) {
                $expiring[] = $r;
            } elseif ($st === 'grace') {
                $grace[] = $r;
            } elseif ($st === 'expired') {
                $expired[] = $r;
            } elseif ($st === 'suspended') {
                $suspended[] = $r;
            }
        }
        $this->data += compact('days', 'expiring', 'grace', 'expired', 'suspended');
        $this->render('subscriptions/expiring', translate('expiring_and_expired_subscriptions'));
    }

    public function settings()
    {
        if ($this->input->post('save')) {
            $this->form_validation->set_rules('default_grace_days', translate('grace_period'), 'trim|required|integer|greater_than_equal_to[0]|less_than_equal_to[90]');
            $this->form_validation->set_rules('warning_days', translate('warning_days'), 'trim|required|integer|greater_than_equal_to[0]|less_than_equal_to[90]');
            $this->form_validation->set_rules('support_message', translate('message'), 'trim|required|max_length[500]');
            if ($this->form_validation->run() !== false) {
                $this->subscription_model->saveSettings($this->input->post());
                set_alert('success', translate('information_has_been_updated_successfully'));
                redirect(base_url('subscriptions/settings'));
            }
        }
        $this->data['settings'] = $this->subscription_model->getSettings();
        $this->render('subscriptions/settings', translate('subscription_settings'));
    }

    public function audit()
    {
        $this->db->select('a.*, s.name AS school_name')->from('platform_audit_log a')->join('school s', 's.id = a.school_id', 'left');
        $schoolID = (int) $this->input->get('school_id');
        if ($schoolID) {
            $this->db->where('a.school_id', $schoolID);
        }
        $entity = (string) $this->input->get('entity');
        if ($entity !== '') {
            $this->db->where('a.entity_type', $entity);
        }
        $this->data['logs'] = $this->db->order_by('a.id', 'desc')->limit(500)->get()->result();
        $this->data['schools'] = $this->db->order_by('name')->get('school')->result();
        $this->data['filters'] = array('school_id' => $schoolID, 'entity' => $entity);
        $this->render('subscriptions/audit', translate('audit_log'));
    }

    // ------------------------------------------------------------------ validation callbacks
    public function valid_date($d)
    {
        if (!$this->isDate($d)) {
            $this->form_validation->set_message('valid_date', 'The {field} must be a date (YYYY-MM-DD).');
            return false;
        }
        return true;
    }

    public function after_start($expiry)
    {
        if ($this->isDate($expiry) && $this->isDate($this->input->post('start_date')) && $expiry < $this->input->post('start_date')) {
            $this->form_validation->set_message('after_start', 'The {field} cannot be before the start date.');
            return false;
        }
        return true;
    }

    // ------------------------------------------------------------------ helpers
    private function isDate($d)
    {
        $dt = DateTime::createFromFormat('Y-m-d', (string) $d);
        return $dt && $dt->format('Y-m-d') === $d;
    }

    private function getSchool($id)
    {
        $school = $this->db->get_where('school', array('id' => (int) $id))->row();
        if (empty($school)) {
            show_404();
        }
        return $school;
    }

    private function postedSubscription()
    {
        if (!$_POST) {
            show_404();
        }
        $sub = $this->db->get_where('school_subscription', array('id' => (int) $this->input->post('subscription_id')))->row();
        if (empty($sub)) {
            show_404();
        }
        return $sub;
    }

    private function render($page, $title)
    {
        $this->data['title'] = $title;
        $this->data['sub_page'] = $page;
        $this->data['main_menu'] = 'platform_subscriptions';
        $this->load->view('layout/index', $this->data);
    }
}
