<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Schools (tenants), branch access, subscriptions, manual payments and the platform audit log.
 *
 * Effective subscription state is computed, never stored:
 *   suspended  admin_status = suspended
 *   active     today <= expiry_date
 *   grace      expiry_date < today <= expiry_date + grace_days
 *   expired    after the grace period
 */
class Subscription_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getSettings()
    {
        $row = $this->db->get_where('subscription_settings', array('id' => 1))->row();
        if (empty($row)) {
            $row = (object) array(
                'default_grace_days' => 7,
                'warning_days' => 15,
                'support_message' => 'Please contact the platform administrator to renew the subscription.',
            );
        }
        return $row;
    }

    // computed state for a subscription row (object/array with expiry_date, grace_days, admin_status)
    public function stateFor($sub, $settings = null)
    {
        if (empty($sub)) {
            return array('state' => 'none', 'days_left' => null, 'grace_days' => 0, 'grace_end' => null, 'expiry_date' => null);
        }
        $sub = (object) $sub;
        $settings = $settings ?: $this->getSettings();
        $today = strtotime(date('Y-m-d'));
        $expiry = strtotime($sub->expiry_date);
        $grace = ($sub->grace_days !== null && $sub->grace_days !== '') ? (int) $sub->grace_days : (int) $settings->default_grace_days;
        $graceEnd = strtotime('+' . $grace . ' days', $expiry);
        if ($sub->admin_status === 'suspended') {
            $state = 'suspended';
        } elseif ($today <= $expiry) {
            $state = 'active';
        } elseif ($today <= $graceEnd) {
            $state = 'grace';
        } else {
            $state = 'expired';
        }
        return array(
            'state' => $state,
            'days_left' => (int) round(($expiry - $today) / 86400),
            'grace_days' => $grace,
            'grace_end' => date('Y-m-d', $graceEnd),
            'expiry_date' => $sub->expiry_date,
        );
    }

    /**
     * Single place that decides whether a branch (and therefore its users) may use protected modules.
     * Returns array(allowed, code, message, state, days_left, expiry_date, grace_end, school_id, branch_id, warning)
     */
    public function getBranchAccess($branchID)
    {
        $branchID = (int) $branchID;
        $sql = "SELECT `b`.`id`, `b`.`status` AS `branch_status`, `b`.`school_id`, `s`.`status` AS `school_status`,
                `sub`.`expiry_date`, `sub`.`grace_days`, `sub`.`admin_status`, `sub`.`id` AS `sub_id`
                FROM `branch` AS `b`
                LEFT JOIN `school` AS `s` ON `s`.`id` = `b`.`school_id`
                LEFT JOIN `school_subscription` AS `sub` ON `sub`.`school_id` = `s`.`id`
                WHERE `b`.`id` = " . $this->db->escape($branchID);
        $row = $this->db->query($sql)->row();
        $settings = $this->getSettings();
        $out = array(
            'allowed' => false, 'code' => '', 'message' => '', 'state' => 'none', 'days_left' => null,
            'expiry_date' => null, 'grace_end' => null, 'school_id' => null, 'branch_id' => $branchID, 'warning' => '',
            'support_message' => $settings->support_message,
        );
        if (empty($row)) {
            $out['code'] = 'BRANCH_NOT_FOUND';
            $out['message'] = 'This branch could not be found. Please contact the platform administrator.';
            return $out;
        }
        $out['school_id'] = $row->school_id;
        if ($row->branch_status != 1) {
            $out['code'] = 'BRANCH_INACTIVE';
            $out['message'] = 'This branch has been deactivated. Please contact your School Admin or the platform administrator.';
            return $out;
        }
        if (empty($row->school_id) || $row->school_status === null) {
            $out['code'] = 'SCHOOL_NOT_ASSIGNED';
            $out['message'] = 'This branch is not linked to a school. Please contact the platform administrator.';
            return $out;
        }
        if ($row->school_status != 1) {
            $out['code'] = 'SCHOOL_INACTIVE';
            $out['message'] = 'This school has been deactivated. The School Admin should contact the platform administrator.';
            return $out;
        }
        if (empty($row->sub_id)) {
            $out['code'] = 'SUBSCRIPTION_MISSING';
            $out['message'] = "This school has no active subscription. The School Admin should contact the platform administrator.";
            return $out;
        }
        $st = $this->stateFor($row, $settings);
        $out['state'] = $st['state'];
        $out['days_left'] = $st['days_left'];
        $out['expiry_date'] = $st['expiry_date'];
        $out['grace_end'] = $st['grace_end'];
        if ($st['state'] === 'suspended') {
            $out['code'] = 'SUBSCRIPTION_SUSPENDED';
            $out['message'] = "This school's subscription has been suspended. The School Admin should contact the platform administrator.";
            return $out;
        }
        if ($st['state'] === 'expired') {
            $out['code'] = 'SUBSCRIPTION_EXPIRED';
            $out['message'] = "This school's subscription has expired. The School Admin should contact the platform administrator to renew it.";
            return $out;
        }
        $out['allowed'] = true;
        $out['code'] = 'OK';
        if ($st['state'] === 'grace') {
            $out['warning'] = "The school's subscription expired on " . date('d M Y', strtotime($st['expiry_date'])) . '. Access will be restricted after ' . date('d M Y', strtotime($st['grace_end'])) . '. Please renew now.';
        } elseif ($st['days_left'] <= (int) $settings->warning_days) {
            $out['warning'] = "The school's subscription expires on " . date('d M Y', strtotime($st['expiry_date'])) . ' (' . $st['days_left'] . ' day(s) left). Please renew in time.';
        }
        return $out;
    }

    // ---------------------------------------------------------------- audit
    public function audit($action, $entityType, $entityID = null, $schoolID = null, $branchID = null, $old = null, $new = null)
    {
        $this->db->insert('platform_audit_log', array(
            'actor_login_id' => get_loggedin_id(),
            'actor_name' => $this->session->userdata('name'),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityID,
            'school_id' => $schoolID,
            'branch_id' => $branchID,
            'old_value' => $old === null ? null : json_encode($old),
            'new_value' => $new === null ? null : json_encode($new),
            'ip_address' => $this->input->ip_address(),
        ));
    }

    // ---------------------------------------------------------------- schools
    public function getSchoolIDsForAdmin($staffID)
    {
        $rows = $this->db->select('school_id')->get_where('school_admin', array('staff_id' => (int) $staffID))->result();
        return array_map(function ($r) { return (int) $r->school_id; }, $rows);
    }

    // school ids the logged in user may manage; null = all (super admin)
    public function manageableSchoolIDs()
    {
        if (is_superadmin_loggedin()) {
            return null;
        }
        if (is_admin_loggedin()) {
            return $this->getSchoolIDsForAdmin(get_loggedin_user_id());
        }
        return array();
    }

    public function canManageSchool($schoolID)
    {
        $ids = $this->manageableSchoolIDs();
        return $ids === null || in_array((int) $schoolID, $ids, true);
    }

    public function findOrCreateSchoolByName($name)
    {
        $row = $this->db->get_where('school', array('name' => $name))->row();
        if ($row) {
            return $row->id;
        }
        $this->db->insert('school', array(
            'name' => $name,
            'code' => 'SCH' . str_pad((string) (1 + (int) $this->db->select_max('id')->get('school')->row()->id), 4, '0', STR_PAD_LEFT),
        ));
        $id = $this->db->insert_id();
        $this->audit('school_created', 'school', $id, $id, null, null, array('name' => $name, 'source' => 'branch_form'));
        return $id;
    }

    public function getSchoolsWithSubscription()
    {
        $sql = "SELECT `s`.*, `sub`.`id` AS `sub_id`, `sub`.`plan_name`, `sub`.`amount`, `sub`.`start_date`, `sub`.`expiry_date`,
                `sub`.`grace_days`, `sub`.`admin_status`, `sub`.`payment_status`,
                (SELECT COUNT(*) FROM `branch` WHERE `branch`.`school_id` = `s`.`id`) AS `branch_count`,
                (SELECT COUNT(*) FROM `branch` WHERE `branch`.`school_id` = `s`.`id` AND `branch`.`status` = 1) AS `active_branch_count`
                FROM `school` AS `s` LEFT JOIN `school_subscription` AS `sub` ON `sub`.`school_id` = `s`.`id`
                ORDER BY `s`.`name` ASC";
        $rows = $this->db->query($sql)->result_array();
        $settings = $this->getSettings();
        foreach ($rows as &$r) {
            $r['sub'] = empty($r['sub_id']) ? null : $r;
            $r['st'] = $this->stateFor($r['sub'], $settings);
        }
        return $rows;
    }

    public function getSubscriptionBySchool($schoolID)
    {
        return $this->db->get_where('school_subscription', array('school_id' => (int) $schoolID))->row();
    }

    // ---------------------------------------------------------------- subscription writes
    public function saveSubscription($schoolID, $data)
    {
        $existing = $this->getSubscriptionBySchool($schoolID);
        $row = array(
            'plan_name' => $data['plan_name'],
            'amount' => $data['amount'],
            'start_date' => $data['start_date'],
            'expiry_date' => $data['expiry_date'],
            'grace_days' => ($data['grace_days'] === '' || $data['grace_days'] === null) ? null : (int) $data['grace_days'],
            'notes' => $data['notes'],
        );
        $this->db->trans_start();
        if ($existing) {
            $this->db->where('id', $existing->id)->update('school_subscription', $row);
            $this->audit('subscription_updated', 'subscription', $existing->id, $schoolID, null, $this->pick($existing, $row), $row);
            $id = $existing->id;
        } else {
            $row['school_id'] = $schoolID;
            $this->db->insert('school_subscription', $row);
            $id = $this->db->insert_id();
            $this->audit('subscription_created', 'subscription', $id, $schoolID, null, null, $row);
        }
        $this->db->trans_complete();
        return $this->db->trans_status() ? $id : false;
    }

    public function setAdminStatus($sub, $status)
    {
        $this->db->trans_start();
        $this->db->where('id', $sub->id)->update('school_subscription', array('admin_status' => $status));
        $this->audit($status === 'suspended' ? 'subscription_suspended' : 'subscription_activated', 'subscription', $sub->id, $sub->school_id, null,
            array('admin_status' => $sub->admin_status), array('admin_status' => $status));
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function setPaymentStatus($sub, $status)
    {
        $this->db->trans_start();
        $this->db->where('id', $sub->id)->update('school_subscription', array('payment_status' => $status));
        $this->audit('subscription_payment_status', 'subscription', $sub->id, $sub->school_id, null,
            array('payment_status' => $sub->payment_status), array('payment_status' => $status));
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function extendExpiry($sub, $newExpiry, $note = '')
    {
        $this->db->trans_start();
        $this->db->where('id', $sub->id)->update('school_subscription', array('expiry_date' => $newExpiry));
        $this->audit('subscription_extended', 'subscription', $sub->id, $sub->school_id, null,
            array('expiry_date' => $sub->expiry_date), array('expiry_date' => $newExpiry, 'note' => $note));
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function addPayment($sub, $data, $extendTo = '', $markPaid = false)
    {
        $this->db->trans_start();
        $this->db->insert('subscription_payment', array(
            'subscription_id' => $sub->id,
            'school_id' => $sub->school_id,
            'amount' => $data['amount'],
            'payment_date' => $data['payment_date'],
            'method' => $data['method'],
            'reference_no' => $data['reference_no'],
            'note' => $data['note'],
            'recorded_by' => get_loggedin_id(),
        ));
        $paymentID = $this->db->insert_id();
        $this->audit('payment_recorded', 'payment', $paymentID, $sub->school_id, null, null, $data);
        if ($markPaid && $sub->payment_status !== 'paid') {
            $this->db->where('id', $sub->id)->update('school_subscription', array('payment_status' => 'paid'));
            $this->audit('subscription_payment_status', 'subscription', $sub->id, $sub->school_id, null, array('payment_status' => $sub->payment_status), array('payment_status' => 'paid'));
        }
        if (!empty($extendTo) && $extendTo !== $sub->expiry_date) {
            $this->db->where('id', $sub->id)->update('school_subscription', array('expiry_date' => $extendTo));
            $this->audit('subscription_extended', 'subscription', $sub->id, $sub->school_id, null, array('expiry_date' => $sub->expiry_date), array('expiry_date' => $extendTo, 'payment_id' => $paymentID));
        }
        $this->db->trans_complete();
        return $this->db->trans_status() ? $paymentID : false;
    }

    public function voidPayment($payment, $reason)
    {
        $this->db->trans_start();
        $this->db->where('id', $payment->id)->update('subscription_payment', array(
            'status' => 'void', 'void_reason' => $reason, 'voided_by' => get_loggedin_id(), 'voided_at' => date('Y-m-d H:i:s'),
        ));
        $this->audit('payment_voided', 'payment', $payment->id, $payment->school_id, null,
            array('status' => 'recorded', 'amount' => $payment->amount), array('status' => 'void', 'reason' => $reason));
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function saveSettings($data)
    {
        $old = $this->getSettings();
        $row = array(
            'default_grace_days' => (int) $data['default_grace_days'],
            'warning_days' => (int) $data['warning_days'],
            'support_message' => $data['support_message'],
        );
        $this->db->trans_start();
        $this->db->where('id', 1)->update('subscription_settings', $row);
        $this->audit('settings_updated', 'settings', 1, null, null,
            array('default_grace_days' => $old->default_grace_days, 'warning_days' => $old->warning_days, 'support_message' => $old->support_message), $row);
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    // ---------------------------------------------------------------- branch writes
    public function setBranchStatus($branch, $status)
    {
        $this->db->trans_start();
        $this->db->where('id', $branch->id)->update('branch', array(
            'status' => $status, 'status_changed_at' => date('Y-m-d H:i:s'), 'status_changed_by' => get_loggedin_id(),
        ));
        $this->audit($status ? 'branch_activated' : 'branch_deactivated', 'branch', $branch->id, $branch->school_id, $branch->id,
            array('status' => (int) $branch->status), array('status' => (int) $status));
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    private function pick($object, $row)
    {
        $out = array();
        foreach (array_keys($row) as $k) {
            $out[$k] = isset($object->$k) ? $object->$k : null;
        }
        return $out;
    }
}
