<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Fees_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('sms_model');
    }

    public function getDiscountById($discount_id = '')
    {
        return $this->db->where('id', $discount_id)->get('fees_discount')->row_array();
    }

    public function getStudentFeeDiscounts()
    {
        $this->db->select("sfd.id as sfd_id, sfd.applied_on, s.register_no, CONCAT(s.first_name, ' ', s.last_name) as student_name, c.name as class_name, se.name as section_name, ft.fee_name as fees_type, fd.name as discount_name, fd.discount_type, fd.percentage, fd.fixed_amount");
        $this->db->from('student_fee_discount sfd');
        $this->db->join('fees_discount fd', 'fd.id = sfd.discount_id', 'inner');
        $this->db->join('fees_type ft', 'ft.id = fd.fee_type_id', 'left');
        $this->db->join('student s', 's.id = sfd.student_id', 'left');
        $this->db->join('enroll e', 'e.student_id = sfd.student_id and e.session_id = sfd.session_id', 'left');
        $this->db->join('class c', 'c.id = e.class_id', 'left');
        $this->db->join('section se', 'se.id = e.section_id', 'left');
        if (!is_superadmin_loggedin()) {
            $this->db->where('fd.branch_id', get_loggedin_branch_id());
        }
        $this->db->order_by('sfd.id', 'desc');
        return $this->db->get()->result_array();
    }

    public function getPreviousSessionBalance($enrollID = '', $session_id = '', $with_fine = 0)
    {
        $total_balance = 0;
        $total_fine = 0;
        $variable = $this->db->where(array('student_id' => $enrollID, 'session_id' => $session_id))->get('fee_allocation')->result();
        foreach ($variable as $key => $allocation) {
            $groupsDetails = $this->db->select('fee_type_id')->where('fee_groups_id', $allocation->group_id)->get('fee_groups_details')->result();
            foreach ($groupsDetails as $k => $type) {
                $fine = $this->feeFineCalculation($allocation->id, $type->fee_type_id);
                $b = $this->getBalance($allocation->id, $type->fee_type_id);
                $total_balance += $b['balance'];
                $total_fine += abs($fine - $b['fine']);
            }
        }
        if ($with_fine == 1) {
            return round($total_balance + $total_fine);
        } else {
            return ($total_balance);
        }
    }

    public function feeFineCalculation($allocationID, $typeID)
    {
        $this->db->select('fd.amount,fd.due_date,f.*');
        $this->db->from('fee_allocation as a');
        $this->db->join('fee_groups_details as fd', 'fd.fee_groups_id = a.group_id and fd.fee_type_id = ' . $this->db->escape($typeID), 'left');
        $this->db->join('fee_fine as f', 'f.group_id = fd.fee_groups_id and f.type_id = fd.fee_type_id', 'inner');
        $this->db->where('a.id', $allocationID);
        $this->db->where('f.session_id', get_session_id());
        $getDB = $this->db->get()->row_array();
        if (is_array($getDB) && count($getDB)) {
            $dueDate = $getDB['due_date'];
            if (strtotime($dueDate) < strtotime(date('Y-m-d'))) {
                $feeAmount = $getDB['amount'];
                $feeFrequency = $getDB['fee_frequency'];
                $fineValue = $getDB['fine_value'];
                if ($getDB['fine_type'] == 1) {
                    $fineAmount = $fineValue;
                } else {
                    $fineAmount = ($feeAmount / 100) * $fineValue;
                }
                $now = time(); // or your date as well
                $dueDate = strtotime($dueDate);
                $datediff = $now - $dueDate;
                $overDay = round($datediff / (60 * 60 * 24));
                if ($feeFrequency != 0) {
                    $fineAmount = ($overDay / $feeFrequency) * $fineAmount;
                }
                return $fineAmount;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    public function getStudentAllocationList($classID = '', $sectionID = '', $groupID = '', $branchID = '')
    {
        $sql = "SELECT e.*, s.photo, CONCAT_WS(' ',s.first_name, s.last_name) as fullname, s.gender, s.register_no, s.parent_id, s.email, s.mobileno, IFNULL(fa.id, 0) as allocation_id FROM enroll as e INNER JOIN student as s ON e.student_id = s.id LEFT JOIN login_credential as l ON l.user_id = s.id AND l.role = '7' LEFT JOIN fee_allocation as fa ON fa.student_id = e.id AND fa.group_id = " . $this->db->escape($groupID) . " AND fa.session_id= " . $this->db->escape(get_session_id()) . " WHERE e.class_id = " . $this->db->escape($classID) . " AND e.branch_id = " . $this->db->escape($branchID) . " AND e.session_id = " . $this->db->escape(get_session_id());
        if ($sectionID != 'all') {
            $sql .= " AND e.section_id =" . $this->db->escape($sectionID);
        }
        $sql .= " AND l.active = '1' ORDER BY s.id ASC";
        return $this->db->query($sql)->result_array();
    }

    public function getInvoiceStatus($enrollID = '')
    {
        $status = 'unpaid';
        $sessionID = get_session_id();
        $invNo = 0;

        $enroll = $this->db->select('student_id, class_id')
            ->where('id', $enrollID)
            ->get('enroll')
            ->row_array();

        $totalAmount = 0;
        $totalPaid = 0;
        $totalBalance = 0;

        // Current fee system: student_fee_allocation
        if (!empty($enroll)) {
            $this->db->select('IFNULL(SUM(sfa.amount), 0) as total, IFNULL(SUM(sfa.paid), 0) as paid, IFNULL(SUM(sfa.balance), 0) as balance, MIN(sfa.id) as inv_no', false);
            $this->db->from('student_fee_allocation as sfa');
            $this->db->where('sfa.student_id', $enroll['student_id']);
            $this->db->where('sfa.class_id', $enroll['class_id']);
            $sfa = $this->db->get()->row_array();
            if (!empty($sfa)) {
                $totalAmount = (float) $sfa['total'];
                $totalPaid = (float) $sfa['paid'];
                $totalBalance = (float) $sfa['balance'];
                $invNo = empty($sfa['inv_no']) ? 0 : str_pad($sfa['inv_no'], 4, '0', STR_PAD_LEFT);
            }
        }

        // Fallback: legacy fee_allocation
        if ($totalAmount <= 0) {
            $sql = "SELECT SUM(`fee_groups_details`.`amount` + `fee_allocation`.`prev_due`) as `total`, min(`fee_allocation`.`id`) as `inv_no` FROM `fee_allocation` LEFT JOIN `fee_groups_details` ON `fee_groups_details`.`fee_groups_id` = `fee_allocation`.`group_id` LEFT JOIN `fees_type` ON `fees_type`.`id` = `fee_groups_details`.`fee_type_id` WHERE `fee_allocation`.`student_id` = " . $this->db->escape($enrollID) . " AND `fee_allocation`.`session_id` = " . $this->db->escape($sessionID);
            $balance = $this->db->query($sql)->row_array();
            $totalAmount = (float) ($balance['total'] ?? 0);
            $invNo = empty($balance['inv_no']) ? $invNo : str_pad($balance['inv_no'], 4, '0', STR_PAD_LEFT);

            $this->db->select("IFNULL(SUM(fee_payment_history.amount), 0) as amount, IFNULL(SUM(fee_payment_history.discount), 0) as discount");
            $this->db->from("fee_payment_history");
            $this->db->join("fee_allocation", "fee_allocation.id = fee_payment_history.allocation_id", "left");
            $this->db->where("fee_allocation.student_id", $enrollID);
            $this->db->where("fee_allocation.session_id", $sessionID);
            $paid = $this->db->get()->row_array();
            $totalPaid = (float) ($paid['amount'] ?? 0) + (float) ($paid['discount'] ?? 0);
            $totalBalance = max(0, $totalAmount - $totalPaid);
        }

        // Transport fees
        $this->db->select("IFNULL(SUM(transport_stoppage_point.route_fare), 0) as amount");
        $this->db->from('transport_fee_details');
        $this->db->join('transport_stoppage_point', 'transport_stoppage_point.id = transport_fee_details.stoppage_point_id', 'inner');
        $this->db->where('transport_fee_details.enroll_id', $enrollID);
        $trans_amount = (float) $this->db->get()->row()->amount;
        $totalAmount += $trans_amount;

        $this->db->select("IFNULL(SUM(h.amount), 0) as amount, IFNULL(SUM(h.discount), 0) as discount");
        $this->db->from("fee_payment_history as h");
        $this->db->join("transport_fee_details as tfd", "tfd.id = h.transport_fee_details_id", "inner");
        $this->db->where("tfd.enroll_id", $enrollID);
        $transPaid = $this->db->get()->row_array();
        $transPaidTotal = (float) ($transPaid['amount'] ?? 0) + (float) ($transPaid['discount'] ?? 0);
        $totalPaid += $transPaidTotal;
        $totalBalance += max(0, $trans_amount - $transPaidTotal);

        if ($totalPaid <= 0) {
            $status = 'unpaid';
        } elseif ($totalBalance <= 0 && $totalAmount > 0) {
            $status = 'total';
        } elseif ($totalPaid > 0) {
            $status = 'partly';
        }

        return array('status' => $status, 'invoice_no' => $invNo);
    }

    public function getInvoiceDetails($enrollID = '')
    {
        $sql = "SELECT `fee_allocation`.`group_id`,`fee_allocation`.`prev_due`,`fee_allocation`.`id` as `allocation_id`, `fees_type`.`fee_name` as `name`, 0 as `system`, `fee_groups_details`.`amount`, `fee_groups_details`.`due_date`, `fee_groups_details`.`fee_type_id` FROM `fee_allocation` LEFT JOIN
        `fee_groups_details` ON `fee_groups_details`.`fee_groups_id` = `fee_allocation`.`group_id` LEFT JOIN `fees_type` ON `fees_type`.`id` = `fee_groups_details`.`fee_type_id` WHERE
        `fee_allocation`.`student_id` = " . $this->db->escape($enrollID) . " AND `fee_allocation`.`session_id` = " . $this->db->escape(get_session_id()) . " ORDER BY `fee_allocation`.`group_id` ASC, `fees_type`.`id` ASC";
        $student = array();
        $r = $this->db->query($sql)->result_array();
        foreach ($r as $key => $value) {
            if (!empty($value['system']) && $value['system'] == 1) {
                $value['amount'] = $value['prev_due'];
            }
            $student[] = $value;
        }
        return $student;
    }

    public function getInvoiceBasic($enrollID = '')
    {
        $sessionID = get_session_id();
        $this->db->select('s.id,s.register_no,s.photo,s.mobileno,e.branch_id,e.id as enroll_id,e.roll,s.first_name,s.last_name,s.stoppage_point_id,s.email as student_email,s.current_address as student_address,c.name as class_name,b.school_name,b.email as school_email,b.mobileno as school_mobileno,b.address as school_address,p.father_name,se.name as section_name,sc.name as category');
        $this->db->from('enroll as e');
        $this->db->join('student as s', 's.id = e.student_id', 'inner');
        $this->db->join('class as c', 'c.id = e.class_id', 'left');
        $this->db->join('section as se', 'se.id = e.section_id', 'left');
        $this->db->join('student_category as sc', 'sc.id = s.category_id', 'left');
        $this->db->join('parent as p', 'p.id = s.parent_id', 'left');
        $this->db->join('branch as b', 'b.id = e.branch_id', 'left');
        if (!is_superadmin_loggedin()) {
            $this->db->where('e.branch_id', get_loggedin_branch_id());
        }
        $this->db->where('e.id', $enrollID);
        $this->db->where('e.session_id', $sessionID);
        return $this->db->get()->row_array();
    }

    public function getStudentFeeDeposit($allocationID, $typeID)
    {
        $sqlDeposit = "SELECT IFNULL(SUM(`amount`), '0.00') as `total_amount`, IFNULL(SUM(`discount`), '0.00') as `total_discount`, IFNULL(SUM(`fine`), '0.00') as `total_fine` FROM `fee_payment_history` WHERE `allocation_id` = " . $this->db->escape($allocationID) . " AND `type_id` = " . $this->db->escape($typeID);
        return $this->db->query($sqlDeposit)->row_array();
    }

    public function getPaymentHistory($allocationID, $groupID)
    {
        $this->db->select('h.*,t.fee_name as name,t.fee_name as fee_code,pt.name as payvia');
        $this->db->from('fee_payment_history as h');
        $this->db->join('fees_type as t', 't.id = h.type_id', 'left');
        $this->db->join('payment_types as pt', 'pt.id = h.pay_via', 'left');
        $this->db->where('h.allocation_id', $allocationID);
        $this->db->order_by('h.id', 'asc');
        return $this->db->get()->result_array();
    }

    public function typeSave($data = array())
    {
        $arrayData = array(
            'branch_id' => $this->application_model->get_branch_id(),
            'name' => $data['type_name'],
            'fee_code' => strtolower(str_replace(' ', '-', $data['type_name'])),
            'description' => $data['description'],
        );
        if (!isset($data['type_id'])) {
            $this->db->insert('fees_type', $arrayData);
        } else {
            $this->db->where('id', $data['type_id']);
            $this->db->update('fees_type', $arrayData);
        }
    }

    // add partly of the fee
    public function add_fees($data = array(), $id = '')
    {
        $total_due = get_type_name_by_id('fee_invoice', $id, 'total_due');
        $payment_amount = $data['amount'];
        if (($payment_amount <= $total_due) && ($payment_amount > 0)) {
            $arrayHistory = array(
                'fee_invoice_id' => $id,
                'collect_by' => get_user_stamp(),
                'remarks' => $data['remarks'],
                'method' => $data['method'],
                'amount' => $payment_amount,
                'date' => date("Y-m-d"),
                'session_id' => get_session_id(),
            );
            $this->db->insert('payment_history', $arrayHistory);

            if ($total_due <= $payment_amount) {
                $this->db->where('id', $id);
                $this->db->update('fee_invoice', array('status' => 2));
            } else {
                $this->db->where('id', $id);
                $this->db->update('fee_invoice', array('status' => 1));
            }
            $this->db->where('id', $id);
            $this->db->set('total_paid', 'total_paid + ' . $payment_amount, false);
            $this->db->set('total_due', 'total_due - ' . $payment_amount, false);
            $this->db->update('fee_invoice');

            // send payment confirmation sms
            $arrayHistory['student_id'] = $data['student_id'];
            $arrayHistory['timestamp'] = date("Y-m-d");
            $this->sms_model->send_sms($arrayHistory, 2);
            return true;
        } else {
            return false;
        }
    }

    public function getInvoiceList()
    {
        $branchID = $this->application_model->get_branch_id();
        $class_id = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');

        // getting a list of classes assigned to a teacher
        $assigned_cs_list = $this->app_lib->get_ownClassSection();

        // List enrolled students by class/section (current fee system uses student_fee_allocation)
        $this->datatables->select('e.id as enroll_id,e.student_id,e.roll,s.first_name,s.last_name,s.register_no,s.mobileno,c.name as class_name,se.name as section_name');
        $this->datatables->from('enroll as e');
        $this->datatables->join('student as s', 's.id = e.student_id', 'inner');
        $this->datatables->join('class as c', 'c.id = e.class_id', 'left');
        $this->datatables->join('section as se', 'se.id = e.section_id', 'left');
        $this->datatables->where('e.branch_id', $branchID);
        $this->datatables->where('e.session_id', get_session_id());
        $this->datatables->where('s.active', 1);
        if (!empty($class_id)) {
            $this->datatables->where('e.class_id', $class_id);
        }
        if (!empty($section_id)) {
            $this->datatables->where('e.section_id', $section_id);
        }
        // filter classes by teacher assigned classes
        if ($assigned_cs_list != false && !empty($assigned_cs_list)) {
            $this->datatables->group_start();
            foreach ($assigned_cs_list as $class_key => $class_value) {
                foreach ($class_value as $section_key => $section_value) {
                    $this->datatables->or_group_start();
                    $this->datatables->where('e.class_id', $class_key);
                    $this->datatables->where('e.section_id', $section_value);
                    $this->datatables->group_end();

                }
            }
            $this->datatables->group_end();
        }
        $this->datatables->search_value('s.first_name,s.last_name,s.register_no,s.mobileno');
        $this->datatables->column_order('e.id,s.first_name,c.id,se.id,s.register_no,e.roll,s.mobileno,e.id');
        $this->datatables->group_by('e.id');
        $this->datatables->order_by('e.id', 'asc');
        $results = $this->datatables->generate();
        $records = array();
        $records = json_decode($results);
        $data = array();
        foreach ($records->data as $key => $record) {
            $full_name = $record->first_name . ' ' . $record->last_name;
            // actions btn
            $actions = '<button type="button" data-loading-text="<i class=\'fas fa-spinner fa-spin\'></i>" data-placement="top" data-toggle="tooltip" data-original-title="' . translate('email') . " " . translate('invoice') . '" class="btn btn-default icon btn-circle" onclick="pdf_sendByemail(' . "'" . $record->enroll_id . "'" . ', this)"><i class="fa-solid fa-envelope"></i></button>';
            if (get_permission('collect_fees', 'is_add')) {
                $actions .= '<a href="' . base_url('fees/invoice/' . $record->enroll_id) . '" class="btn btn-default btn-circle"><i class="far fa-arrow-alt-circle-right"></i> ' . translate('collect') . '</a>';
            }
            if (get_permission('invoice', 'is_delete')) {
                $actions .= btn_delete('fees/invoice_delete/' . $record->enroll_id);
            }

            // dt-data array
            $row = array();
            $row[] = '<div class="checked-area"><div class="checkbox-replace"><label class="i-checks"><input type="checkbox" name="student_id[]" value="' . $record->enroll_id . '"><i></i></label></div></div>';
            $row[] = '<a class="hidden-print" href="' . base_url('student/profile/') . $record->enroll_id . '">' . $full_name . '</a>' . '<span class="visible-print">' . $full_name . '</span>';
            $row[] = $record->class_name;
            $row[] = $record->section_name;
            $row[] = $record->register_no;
            $row[] = $record->roll;
            $row[] = $record->mobileno;
            // Fee group: show first fee type only; rest collapsible via View more
            $feegroup = $this->getfeeGroup($record->enroll_id);
            $groupList = '';
            if (!empty($feegroup)) {
                $names = array_values(array_map(function ($v) {
                    return html_escape($v['name']);
                }, $feegroup));
                $visible = array_shift($names);
                $groupList = '<div class="fee-group-wrap">';
                $groupList .= '<div class="fee-group-visible">- ' . $visible . '</div>';
                if (!empty($names)) {
                    $uid = 'fg_' . $record->enroll_id;
                    $groupList .= '<div class="fee-group-more" id="' . $uid . '" style="display:none;">';
                    foreach ($names as $name) {
                        $groupList .= '- ' . $name . '<br>';
                    }
                    $groupList .= '</div>';
                    $groupList .= '<a href="javascript:void(0);" class="fee-group-toggle text-primary" data-target="' . $uid . '" data-more="' . translate('view_more') . '" data-less="' . translate('view_less') . '">' . translate('view_more') . '</a>';
                }
                $groupList .= '</div>';
            }
            $row[] = $groupList;
            // fees status
            $labelmode = '';
            $status = $this->getInvoiceStatus($record->enroll_id)['status'];
            if ($status == 'unpaid') {
                $status = translate('unpaid');
                $labelmode = 'label-danger-custom';
            } elseif ($status == 'partly') {
                $status = translate('partly_paid');
                $labelmode = 'label-info-custom';
            } elseif ($status == 'total') {
                $status = translate('total_paid');
                $labelmode = 'label-success-custom';
            }
            $row[] = "<span class='value label " . $labelmode . " '>" . $status . "</span>";
            $row[] = $actions;

            $data[] = $row;
        }
        $json_data = array(
            "draw" => intval($records->draw),
            "recordsTotal" => intval($records->recordsTotal),
            "recordsFiltered" => intval($records->recordsFiltered),
            "data" => $data,
        );
        return json_encode($json_data);
    }

    public function getDueInvoiceDT_list($class_id = '', $section_id = '', $feegroup_id = '', $fee_feetype_id = '')
    {
        $get_session_id = get_session_id();
        if ($feegroup_id == 'transport') {
            $this->datatables->select('IFNULL(SUM(h.amount), 0) as total_amount, IFNULL(SUM(h.discount), 0) as total_discount, sp.route_fare as full_amount,ff.due_date, e.student_id, e.id as enroll_id,e.roll, s.first_name, s.last_name, s.register_no, s.mobileno, c.name as class_name, se.name as section_name');
            $this->datatables->from('transport_fee_details as fa');
            $this->datatables->join('fee_payment_history as h', 'h ON h.transport_fee_details_id = fa.id', 'left');
            $this->datatables->join('transport_stoppage_point as sp', 'sp.id = fa.stoppage_point_id', 'left');
            $this->datatables->join('transport_fee_fine as ff', 'ff.id = fa.transport_fee_fine_id', 'inner');
            $this->datatables->join('enroll as e', 'e.id = fa.enroll_id', 'inner');;
            $this->datatables->where('fa.transport_fee_fine_id', $fee_feetype_id);
            $this->datatables->where('sp.session_id', $get_session_id);
            $this->datatables->group_by('fa.enroll_id');
            $this->datatables->search_value('s.first_name,s.register_no,e.roll,s.mobileno,ff.due_date');
        } else {
            $this->datatables->select('IFNULL(SUM(h.amount), 0) as total_amount, IFNULL(SUM(h.discount), 0) as total_discount, gd.amount as full_amount, fa.prev_due as prev_due, gd.due_date, e.student_id, e.id as enroll_id,e.roll, s.first_name, s.last_name, s.register_no, s.mobileno, c.name as class_name, se.name as section_name');
            $this->datatables->from('fee_allocation as fa');
            $this->datatables->join('fee_payment_history as h', 'h ON h.allocation_id = fa.id and h.type_id =' . $this->db->escape($fee_feetype_id), 'left');
            $this->datatables->join('fee_groups_details as gd', 'gd.fee_groups_id = fa.group_id and gd.fee_type_id =' . $this->db->escape($fee_feetype_id), 'inner');
            $this->datatables->join('enroll as e', 'e.id = fa.student_id', 'inner');
            $this->datatables->where('fa.group_id', $feegroup_id);
            $this->datatables->where('fa.session_id', $get_session_id);
            $this->datatables->group_by('fa.student_id');
            $this->datatables->search_value('s.first_name,s.register_no,e.roll,s.mobileno,gd.due_date');
        }
        $this->datatables->join('student as s', 's.id = e.student_id', 'left');
        $this->datatables->join('class as c', 'c.id = e.class_id', 'left');
        $this->datatables->join('section as se', 'se.id = e.section_id', 'left');
        $this->datatables->where('e.class_id', $class_id);
        if ($section_id != 'all') {
            $this->datatables->where('e.section_id', $section_id);
        }
        $this->datatables->column_order('fa.id,s.first_name,s.register_no,e.roll,s.mobileno,enroll_id,due_date,full_amount,total_amount,total_discount,full_amount');
        $this->datatables->order_by('e.id', 'asc');
        $results = $this->datatables->generate();
        return $results;
    }

    public function getDueReport($class_id = '', $section_id = '')
    {
        $this->db->select('fa.id as allocation_id,sum(gd.amount + fa.prev_due) as total_fees,e.id as enroll_id,e.roll,s.first_name,s.last_name,s.register_no,s.mobileno,c.name as class_name,se.name as section_name');
        $this->db->from('fee_allocation as fa');
        $this->db->join('fee_groups_details as gd', 'gd.fee_groups_id = fa.group_id', 'left');
        $this->db->join('enroll as e', 'e.id = fa.student_id and e.session_id = ' . $this->db->escape(get_session_id()), 'inner');
        $this->db->join('student as s', 's.id = e.student_id', 'left');
        $this->db->join('class as c', 'c.id = e.class_id', 'left');
        $this->db->join('section as se', 'se.id = e.section_id', 'left');
        $this->db->where('fa.session_id', get_session_id());
        $this->db->where('s.active', 1);
        $this->db->where('e.class_id', $class_id);
        if (!empty($section_id)) {
            $this->db->where('e.section_id', $section_id);
        }
        $this->db->group_by('fa.student_id');
        $this->db->order_by('e.id', 'asc');
        $result = $this->db->get()->result_array();
        foreach ($result as $key => $value) {
            $result[$key]['payment'] = $this->getPaymentDetails($value['enroll_id']);
        }
        return $result;
    }

    public function getPaymentDetails($student_id = '')
    {
        $this->db->select('IFNULL(SUM(amount), 0) as total_paid, IFNULL(SUM(discount), 0) as total_discount, IFNULL(SUM(fine), 0) as total_fine');
        $this->db->from('fee_allocation');
        $this->db->join('fee_payment_history', 'fee_payment_history.allocation_id = fee_allocation.id', 'left');
        $this->db->where('fee_allocation.student_id', $student_id);
        return $this->db->get()->row_array();
    }

    public function getStuPaymentHistory($classID = '', $SectionID = '', $paymentVia = '', $start = '', $end = '', $branchID = '', $onlyFine = false)
    {
        $this->db->select('h.*,ft.fee_name as type_name,e.student_id,e.roll,s.first_name,s.last_name,s.register_no,s.mobileno,c.name as class_name,se.name as section_name,pt.name as pay_via,h.transport_fee_details_id');
        $this->db->from('fee_payment_history as h');
        $this->db->join('fee_allocation as fa', 'fa.id = h.allocation_id', 'inner');
        $this->db->join('fees_type as ft', 'ft.id = h.type_id', 'left');
        $this->db->join('enroll as e', 'e.id = fa.student_id', 'inner');
        $this->db->join('student as s', 's.id = e.student_id', 'inner');
        $this->db->join('class as c', 'c.id = e.class_id', 'left');
        $this->db->join('section as se', 'se.id = e.section_id', 'left');
        $this->db->join('payment_types as pt', 'pt.id = h.pay_via', 'left');
        $this->db->where('fa.session_id', get_session_id());
        $this->db->where('e.session_id', get_session_id());
        $this->db->where('s.active', 1);
        $this->db->where('h.date  >=', $start);
        $this->db->where('h.date <=', $end);
        $this->db->where('e.branch_id', $branchID);
        if ($onlyFine == true) {
            $this->db->where('h.fine !=', 0);
        }
        if (!empty($classID)) {
            $this->db->where('e.class_id', $classID);
        }
        if (!empty($SectionID)) {
            $this->db->where('e.section_id', $SectionID);
        }
        if ($paymentVia != 'all') {
            if ($paymentVia == 'online') {
                $this->db->where('h.collect_by', 'online');
            } else {
                $this->db->where('h.collect_by !=', 'online');
            }
        }
        $this->db->order_by('h.id', 'asc');
        $result = $this->db->get()->result_array();

        if (moduleIsEnabled('transport')) {
            $this->db->select('h.*,ff.month as type_name,e.student_id,e.roll,s.first_name,s.last_name,s.register_no,s.mobileno,c.name as class_name,se.name as section_name,pt.name as pay_via,h.transport_fee_details_id');
            $this->db->from('fee_payment_history as h');
            $this->db->join('transport_fee_details as fa', 'fa.id = h.transport_fee_details_id', 'inner');
            $this->db->join('transport_fee_fine as ff', 'ff.id = fa.transport_fee_fine_id', 'inner');
            $this->db->join('enroll as e', 'e.id = fa.enroll_id and e.session_id =  ' . $this->db->escape(get_session_id()), 'inner');
            $this->db->join('student as s', 's.id = e.student_id', 'inner');
            $this->db->join('class as c', 'c.id = e.class_id', 'left');
            $this->db->join('section as se', 'se.id = e.section_id', 'left');
            $this->db->join('payment_types as pt', 'pt.id = h.pay_via', 'left');
            $this->db->where('ff.session_id', get_session_id());
            $this->db->where('s.active', 1);
            $this->db->where('h.date  >=', $start);
            $this->db->where('h.date <=', $end);
            $this->db->where('e.branch_id', $branchID);
            if ($onlyFine == true) {
                $this->db->where('h.fine !=', 0);
            }
            if (!empty($classID)) {
                $this->db->where('e.class_id', $classID);
            }
            if (!empty($SectionID)) {
                $this->db->where('e.section_id', $SectionID);
            }
            if ($paymentVia != 'all') {
                if ($paymentVia == 'online') {
                    $this->db->where('h.collect_by', 'online');
                } else {
                    $this->db->where('h.collect_by !=', 'online');
                }
            }
            $this->db->order_by('h.id', 'asc');
            $result2 = $this->db->get()->result_array();
        } else {
            $result2 = [];
        }


        if (empty($result)) {
            $result_output = $result2;
        } elseif (empty($result2)) {
            $result_output = $result;
        } else {
            $result_output = array_merge($result, $result2);
        }


        return $result_output;
    }

    public function getStuPaymentReport($classID = '', $sectionID = '', $enrollID = '', $typeID = '', $start = '', $end = '', $branchID = '')
    {
        if (!empty($typeID)) {
            $type = explode("|", $typeID);
            $group = $type[0];
            $type_id = $type[1];
           
        }
        if (empty($group) || $group != "transport" ) {
            $this->db->select('h.*,gd.due_date,ft.fee_name as type_name,e.student_id,e.roll,s.first_name,s.last_name,s.register_no,pt.name as pay_via,h.transport_fee_details_id');
            $this->db->from('fee_payment_history as h');
            $this->db->join('fee_allocation as fa', 'fa.id = h.allocation_id', 'inner');
            $this->db->join('fees_type as ft', 'ft.id = h.type_id', 'left');
            $this->db->join('fee_groups_details as gd', 'gd.fee_groups_id = fa.group_id and gd.fee_type_id = h.type_id', 'left');
            $this->db->join('enroll as e', 'e.id = fa.student_id and e.session_id =  ' . $this->db->escape(get_session_id()), 'inner');
            $this->db->join('student as s', 's.id = e.student_id', 'inner');
            $this->db->join('payment_types as pt', 'pt.id = h.pay_via', 'left');
            $this->db->where('fa.session_id', get_session_id());
            $this->db->where('s.active', 1);
            $this->db->where('h.date >=', $start);
            $this->db->where('h.date <=', $end);
            $this->db->where('e.branch_id', $branchID);
            $this->db->where('e.class_id', $classID);
            if (!empty($type_id)) {
                $this->db->where('h.type_id', $type_id);
            }
            if (!empty($enrollID)) {
                $this->db->where('e.id', $enrollID);
            }
            $this->db->where('e.section_id', $sectionID);
            $this->db->order_by('h.id', 'asc');
            $result1 = $this->db->get()->result_array();
        } else {
             $result1 = [];
        }

        if (empty($group) || $group == "transport" ) {
            $this->db->select('h.*,ff.due_date,ff.month as type_name,e.student_id,e.roll,s.first_name,s.last_name,s.register_no,pt.name as pay_via,h.transport_fee_details_id');
            $this->db->from('fee_payment_history as h');
            $this->db->join('transport_fee_details as fa', 'fa.id = h.transport_fee_details_id', 'inner');
            $this->db->join('transport_fee_fine as ff', 'ff.id = fa.transport_fee_fine_id', 'inner');
            $this->db->join('enroll as e', 'e.id = fa.enroll_id and e.session_id =  ' . $this->db->escape(get_session_id()), 'inner');
            $this->db->join('student as s', 's.id = e.student_id', 'inner');
            $this->db->join('payment_types as pt', 'pt.id = h.pay_via', 'left');
            $this->db->where('ff.session_id', get_session_id());
            $this->db->where('s.active', 1);
            $this->db->where('h.date >=', $start);
            $this->db->where('h.date <=', $end);
            $this->db->where('e.branch_id', $branchID);
            $this->db->where('e.class_id', $classID);
            if (!empty($enrollID)) {
                $this->db->where('e.id', $enrollID);
            }
            if (!empty($type_id)) {
                $this->db->where('fa.transport_fee_fine_id', $type_id);
            }
            $this->db->where('e.section_id', $sectionID);
            $this->db->order_by('h.id', 'asc');
            $result2 = $this->db->get()->result_array();
        } else {
            $result2 = [];
        }
        $result_value2 = array_merge($result1, $result2);
        return $result_value2;
    }

    public function getfeeGroup($studentID = '')
    {
        // $studentID here is enroll_id (legacy naming)
        $enroll = $this->db->select('student_id, class_id')
            ->where('id', $studentID)
            ->get('enroll')
            ->row_array();

        if (!empty($enroll)) {
            $this->db->select('ft.fee_name as name');
            $this->db->from('student_fee_allocation as sfa');
            $this->db->join('fees_type as ft', 'ft.id = sfa.fee_type_id', 'inner');
            $this->db->where('sfa.student_id', $enroll['student_id']);
            $this->db->where('sfa.class_id', $enroll['class_id']);
            $this->db->group_by('ft.id');
            $this->db->order_by('ft.fee_name', 'asc');
            $rows = $this->db->get()->result_array();
            if (!empty($rows)) {
                return $rows;
            }
        }

        // Fallback: legacy fee_allocation groups
        $this->db->select('g.name');
        $this->db->from('fee_allocation as fa');
        $this->db->join('fee_groups as g', 'g.id = fa.group_id', 'inner');
        $this->db->where('fa.student_id', $studentID);
        $this->db->where('fa.session_id', get_session_id());
        return $this->db->get()->result_array();
    }

    public function reminderSave($data = array())
    {
        $arrayData = array(
            'frequency' => $data['frequency'],
            'days' => $data['days'],
            'student' => (isset($data['chk_student']) ? 1 : 0),
            'guardian' => (isset($data['chk_guardian']) ? 1 : 0),
            'message' => $data['message'],
            'dlt_template_id' => $data['dlt_template_id'],
            'branch_id' => $data['branch_id'],
        );
        if (!isset($data['reminder_id'])) {
            $this->db->insert('fees_reminder', $arrayData);
        } else {
            $this->db->where('id', $data['reminder_id']);
            $this->db->update('fees_reminder', $arrayData);
        }
    }

    public function getFeeReminderByDate($date = '', $branch_id = '')
    {
        $this->db->select('fee_groups_details.*,fees_type.name');
        $this->db->from('fee_groups_details');
        $this->db->join('fees_type', 'fees_type.id = fee_groups_details.fee_type_id', 'inner');
        $this->db->where('fee_groups_details.due_date', $date);
        $this->db->where('fees_type.branch_id', $branch_id);
        $this->db->order_by('fee_groups_details.id', 'asc');
        return $this->db->get()->result_array();
    }

    public function getStudentsListReminder($groupID = '', $typeID = '')
    {
        $sessionID = get_type_name_by_id('global_settings', 1, 'session_id');
        $this->db->select('a.id as allocation_id,CONCAT_WS(" ",s.first_name, s.last_name) as child_name,s.mobileno as child_mobileno,pr.name as guardian_name,pr.mobileno as guardian_mobileno');
        $this->db->from('fee_allocation as a');
        $this->db->join('student as s', 's.id = a.student_id', 'inner');
        $this->db->join('parent as pr', 'pr.id = s.parent_id', 'left');
        $this->db->where('a.group_id', $groupID);
        $this->db->where('a.session_id', $sessionID);
        $result = $this->db->get()->result_array();
        foreach ($result as $key => $value) {
            $result[$key]['payment'] = $this->getPaymentDetailsByTypeID($value['allocation_id'], $typeID);
        }
        return $result;
    }

    public function getPaymentDetailsByTypeID($allocationID, $typeID)
    {
        $this->db->select('IFNULL(SUM(amount), 0) as total_paid, IFNULL(SUM(discount), 0) as total_discount');
        $this->db->from('fee_payment_history');
        $this->db->where('allocation_id', $allocationID);
        $this->db->where('type_id', $typeID);
        return $this->db->get()->row_array();
    }

    public function depositAmountVerify($amount = '')
    {
        if ($amount != "") {
            $typeID = $this->input->post('fees_type');
            if (empty($typeID)) {
                return true;
            }
            $feesType = explode("|", $typeID);
            if ($feesType[0] == 'transport') {
                $remainAmount = $this->getTransportBalance($feesType[1]);
            } else {
                $remainAmount = $this->getBalance($feesType[0], $feesType[1]);
            }
            if ($remainAmount['balance'] < $amount) {
                $this->form_validation->set_message('deposit_verify', '{field} cannot be greater than the remaining.');
                return false;
            } else {
                return true;
            }
        }
        return true;
    }

    public function getBalance($allocationID, $typeID)
    {
        $allocation = $this->db->where('id', $allocationID)->get('student_fee_allocation')->row_array();
        if (!empty($allocation)) {
            return array(
                'balance' => (float) ($allocation['balance'] ?? 0),
                'fine' => (float) ($allocation['fine_calculated_amount'] ?? $allocation['fine'] ?? 0),
            );
        }

        $feeAllocation = $this->db->where('id', $allocationID)->get('fee_allocation')->row_array();
        if (empty($feeAllocation)) {
            return array('balance' => 0, 'fine' => 0);
        }

        $groupsID = $feeAllocation['group_id'];
        $totalAmountRow = $this->db->select('amount')
            ->where(array('fee_groups_id' => $groupsID, 'fee_type_id' => $typeID))
            ->get('fee_groups_details')
            ->row_array();
        if (!empty($totalAmountRow)) {
            $totalAmount = (float) $totalAmountRow['amount'];
        } else {
            $totalAmount = (float) ($feeAllocation['prev_due'] ?? 0);
        }

        $this->db->select('IFNULL(sum(p.amount), 0) as total_amount,IFNULL(sum(p.discount), 0) as total_discount,IFNULL(sum(p.fine), 0) as total_fine');
        $this->db->from('fee_payment_history as p');
        $this->db->where('p.allocation_id', $allocationID);
        $this->db->where('p.type_id', $typeID);
        $paid = $this->db->get()->row_array();
        $balance = $totalAmount - ((float) $paid['total_amount'] + (float) $paid['total_discount']);
        $total_fine = (float) $paid['total_fine'];
        return array('balance' => $balance, 'fine' => $total_fine);
    }

    public function isStudentFeeAllocation($allocationID)
    {
        if (empty($allocationID)) {
            return false;
        }
        return (bool) $this->db->select('id')
            ->where('id', $allocationID)
            ->get('student_fee_allocation')
            ->row('id');
    }

    public function getNextFeeCollectReceiptNo($branchID = '')
    {
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        $this->db->select_max('receipt_no');
        if (!empty($branchID)) {
            $this->db->where('branch_id', $branchID);
        }
        $lastReceipt = (int) $this->db->get('fee_collect')->row('receipt_no');
        return $lastReceipt + 1;
    }

    public function getStudentLastBalanceForEnroll($enrollID = '')
    {
        $enroll = $this->db->select('student_id, session_id')
            ->where('id', $enrollID)
            ->get('enroll')
            ->row_array();
        if (empty($enroll)) {
            return 0;
        }

        $row = $this->db->get_where('student_last_balance', array(
            'student_id' => $enroll['student_id'],
            'session_id' => $enroll['session_id'],
        ))->row_array();

        return (float) ($row['last_balance'] ?? 0);
    }

    public function resolveStudentIdFromEnroll($enrollOrStudentID = '')
    {
        $enroll = $this->db->select('student_id')
            ->where('id', $enrollOrStudentID)
            ->get('enroll')
            ->row_array();
        if (!empty($enroll)) {
            return (int) $enroll['student_id'];
        }
        return (int) $enrollOrStudentID;
    }

    public function saveLastBalanceCollect($data = array())
    {
        $enrollID = $data['enroll_id'] ?? $data['student_id'] ?? 0;
        $enroll = $this->db->select('student_id, session_id')
            ->where('id', $enrollID)
            ->get('enroll')
            ->row_array();
        if (empty($enroll)) {
            return false;
        }

        $branchID = !empty($data['branch_id']) ? $data['branch_id'] : $this->application_model->get_branch_id();
        $receiptNo = !empty($data['receipt_no'])
            ? (int) $data['receipt_no']
            : $this->getNextFeeCollectReceiptNo($branchID);
        $collectData = array(
            'branch_id' => $branchID,
            'student_id' => $enroll['student_id'],
            'allocation_id' => null,
            'fee_type_id' => null,
            'trans_fd_id' => null,
            'receipt_no' => $receiptNo,
            'fee_type' => 'last_balance',
            'date' => $data['date'],
            'paid_amount' => (float) ($data['amount'] ?? 0),
            'discount' => (float) ($data['discount'] ?? 0),
            'fine' => (float) ($data['fine'] ?? 0),
            'pay_via' => $data['pay_via'],
            'account_id' => $data['account_id'] ?? null,
            'remarks' => $data['remarks'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->db->insert('fee_collect', $collectData);
        $collectID = $this->db->insert_id();

        $currentBalance = $this->getStudentLastBalanceForEnroll($enrollID);
        $newBalance = max(0, $currentBalance - (float) ($data['applied_amount'] ?? 0));
        $this->saveStudentLastBalance($enroll['student_id'], $newBalance, $enroll['session_id']);

        return $collectID;
    }

    public function saveStudentFeeCollect($data = array())
    {
        $branchID = !empty($data['branch_id']) ? $data['branch_id'] : $this->application_model->get_branch_id();
        $studentID = $this->resolveStudentIdFromEnroll($data['student_id'] ?? 0);
        $receiptNo = !empty($data['receipt_no'])
            ? (int) $data['receipt_no']
            : $this->getNextFeeCollectReceiptNo($branchID);
        $collectData = array(
            'branch_id' => $branchID,
            'student_id' => $studentID,
            'allocation_id' => !empty($data['allocation_id']) ? $data['allocation_id'] : null,
            'fee_type_id' => !empty($data['type_id']) ? $data['type_id'] : null,
            'trans_fd_id' => !empty($data['trans_fd_id']) ? $data['trans_fd_id'] : null,
            'receipt_no' => $receiptNo,
            'fee_type' => !empty($data['fee_type']) ? $data['fee_type'] : 'general',
            'date' => $data['date'],
            'paid_amount' => (float) ($data['amount'] ?? 0),
            'discount' => (float) ($data['discount'] ?? 0),
            'fine' => (float) ($data['fine'] ?? 0),
            'pay_via' => $data['pay_via'],
            'account_id' => $data['account_id'] ?? null,
            'remarks' => $data['remarks'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->db->insert('fee_collect', $collectData);
        $collectID = $this->db->insert_id();

        if (!empty($data['allocation_id'])) {
            $allocation = $this->db->where('id', $data['allocation_id'])->get('student_fee_allocation')->row_array();
            if (!empty($allocation)) {
                $appliedAmount = (float) ($data['applied_amount'] ?? 0);
                $paid = (float) ($allocation['paid'] ?? 0) + $appliedAmount;
                $balance = max(0, (float) ($allocation['amount'] ?? 0) - $paid);
                $this->db->where('id', $allocation['id'])->update('student_fee_allocation', array(
                    'paid' => $paid,
                    'balance' => $balance,
                ));
            }
        }

        return $collectID;
    }

    // voucher transaction save function
    public function saveTransaction($data = array(), $payment_historyID = '')
    {
        $branchID = $this->application_model->get_branch_id();
        $accountID = $data['account_id'];
        $date = $data['date'];
        $amount = $data['amount'];

        // get the current balance of the selected account
        $qbal = $this->app_lib->get_table('accounts', $accountID, true);
        $cbal = $qbal['balance'];
        $bal = $cbal + $amount;
        // query system voucher head / insert
        $arrayHead = array(
            'name' => 'Student Fees Collection',
            'type' => 'income',
            'system' => 1,
            'branch_id' => $branchID,
        );
        $this->db->where($arrayHead);
        $query = $this->db->get('voucher_head');
        if ($query->num_rows() > 0) {
            $voucher_headID = $query->row()->id;
        } else {
            $this->db->insert('voucher_head', $arrayHead);
            $voucher_headID = $this->db->insert_id();
        }
        // query system transactions / insert
        $arrayTransactions = array(
            'account_id' => $accountID,
            'voucher_head_id' => $voucher_headID,
            'type' => 'deposit',
            'system' => 1,
            'date' => date("Y-m-d", strtotime($date)),
            'branch_id' => $branchID,
        );
        $this->db->where($arrayTransactions);
        $query = $this->db->get('transactions');
        if ($query->num_rows() == 1) {
            $transactionsID = $query->row()->id;
            $this->db->set('amount', 'amount+' . $amount, false);
            $this->db->set('cr', 'cr+' . $amount, false);
            $this->db->set('bal', $bal);
            $this->db->where('id', $transactionsID);
            $this->db->update('transactions');
        } else {
            $arrayTransactions['ref'] = '';
            $arrayTransactions['amount'] = $amount;
            $arrayTransactions['dr'] = 0;
            $arrayTransactions['cr'] = $amount;
            $arrayTransactions['bal'] = $bal;
            $arrayTransactions['pay_via'] = 5;
            $arrayTransactions['description'] = date("d-M-Y", strtotime($date)) . " Total Fees Collection";
            $this->db->insert('transactions', $arrayTransactions);
            $transactionsID = $this->db->insert_id();
        }

        $this->db->where('id', $accountID);
        $this->db->update('accounts', array('balance' => $bal));

        // insert transactions links details in DB
        $arrayLinkDetails = array(
            'payment_id' => $payment_historyID,
            'transactions_id' => $transactionsID,
        );
        $this->db->insert('transactions_links_details', $arrayLinkDetails);
    }

    public function carryForwardDue($data = array())
    {
        $type_name = "Previous Session Balance";
        $group_name = "Due Record";
        $branchID = $data['branch_id'];
        $sessionID = $data['session_id'];
        $fee_type_id = 0;
        $fee_group_id = 0;

        $arrayType = array(
            'name' => $type_name,
            'branch_id' => $branchID,
            'system' => 1,
        );
        $fee_type_exists = $this->checkExistsData('fees_type', $arrayType);
        if (!$fee_type_exists) {
            $arrayType['fee_code'] = 'previous-balance';
            $this->db->insert('fees_type', $arrayType);
            $fee_type_id = $this->db->insert_id();
        } else {
            $fee_type_id = $fee_type_exists->id;
        }

        $arrayGroup = array(
            'name' => $group_name,
            'branch_id' => $branchID,
            'session_id' => $sessionID,
            'system' => 1,
        );
        $fee_group_exists = $this->checkExistsData('fee_groups', $arrayGroup);
        if (!$fee_group_exists) {
            $this->db->insert('fee_groups', $arrayGroup);
            $fee_group_id = $this->db->insert_id();
        } else {
            $fee_group_id = $fee_group_exists->id;
        }

        $arrayGroupsDetails = array(
            'fee_groups_id' => $fee_group_id,
            'fee_type_id' => $fee_type_id,
        );
        $fee_group_details_exists = $this->checkExistsData('fee_groups_details', $arrayGroupsDetails);
        if (!$fee_group_details_exists) {
            $arrayGroupsDetails['amount'] = 0;
            $arrayGroupsDetails['due_date'] = $data['due_date'];
            $this->db->insert('fee_groups_details', $arrayGroupsDetails);
        }

        $arrayAllocation = array(
            'student_id' => $data['student_id'],
            'group_id' => $fee_group_id,
            'branch_id' => $branchID,
            'session_id' => $sessionID,
        );
        $fee_allocation_exists = $this->checkExistsData('fee_allocation', $arrayAllocation);
        if (!$fee_allocation_exists) {
            $arrayAllocation['prev_due'] = $data['prev_due'];
            $this->db->insert('fee_allocation', $arrayAllocation);
        } else {
            $arrayAllocation['prev_due'] = $data['prev_due'];
            $this->db->where('id', $fee_allocation_exists->id);
            $this->db->update('fee_allocation', $arrayAllocation);
        }
    }

    public function checkExistsData($table = '', $data = array())
    {
        $this->db->where($data);
        $query = $this->db->get($table);
        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return false;
        }
    }

    public function getStudentTransportFees($enroll_id = '', $stoppage_point_id = '')
    {
        if (!empty($enroll_id) && !empty($stoppage_point_id)) {
            $this->db->select("transport_fee_details.*,transport_fee_fine.month,transport_fee_fine.due_date,transport_stoppage_point.route_fare");
            $this->db->from('transport_fee_details');
            $this->db->join('transport_fee_fine', 'transport_fee_fine.id = transport_fee_details.transport_fee_fine_id', 'inner');
            $this->db->join('transport_stoppage_point', 'transport_stoppage_point.id = transport_fee_details.stoppage_point_id', 'inner');
            $this->db->where('transport_fee_details.enroll_id', $enroll_id);
            $this->db->where('transport_fee_details.stoppage_point_id', $stoppage_point_id);
            $this->db->order_by('transport_fee_details.id', 'asc');
            $result = $this->db->get()->result();
            return $result;
        }
        return [];
    }

    public function getStudentTransportFeeDeposit($transport_fee_details_id)
    {
        $sqlDeposit = "SELECT IFNULL(SUM(`amount`), '0.00') as `total_amount`, IFNULL(SUM(`discount`), '0.00') as `total_discount`, IFNULL(SUM(`fine`), '0.00') as `total_fine` FROM `fee_payment_history` WHERE `transport_fee_details_id` = " . $this->db->escape($transport_fee_details_id);
        return $this->db->query($sqlDeposit)->row_array();
    }

    public function transportFeeFineCalculation($fee_details_id = '')
    {
        $this->db->select('ff.*,fd.stoppage_point_id,ts.route_fare');
        $this->db->from('transport_fee_fine as ff');
        $this->db->join('transport_fee_details as fd', 'ff.id = fd.transport_fee_fine_id', 'inner');
        $this->db->join('transport_stoppage_point as ts', 'ts.id = fd.stoppage_point_id', 'inner');
        $this->db->where('fd.id', $fee_details_id);
        $getDB = $this->db->get()->row_array();
        if (is_array($getDB) && count($getDB)) {
            $dueDate = $getDB['due_date'];
            if ((strtotime($dueDate) < strtotime(date('Y-m-d'))) && !empty($getDB['fine_type'])) {
                $feeAmount = $getDB['route_fare'];
                $feeFrequency = $getDB['fee_frequency'];
                $fineValue = $getDB['fine_value'];
                if ($getDB['fine_type'] == 1) {
                    $fineAmount = $fineValue;
                } else {
                    $fineAmount = ($feeAmount / 100) * $fineValue;
                }
                $now = time(); // or your date as well
                $dueDate = strtotime($dueDate);
                $datediff = $now - $dueDate;
                $overDay = round($datediff / (60 * 60 * 24));
                if ($feeFrequency != 0) {
                    $fineAmount = ($overDay / $feeFrequency) * $fineAmount;
                }
                return $fineAmount;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    public function getTransportBalance($fee_details_id)
    {
        $this->db->select("transport_fee_details.stoppage_point_id,transport_stoppage_point.route_fare");
        $this->db->from('transport_fee_details');
        $this->db->join('transport_stoppage_point', 'transport_stoppage_point.id = transport_fee_details.stoppage_point_id', 'inner');
        $this->db->where('transport_fee_details.id', $fee_details_id);
        $result = $this->db->get()->row();
        $route_fare = $result->route_fare;

        $this->db->select('IFNULL(sum(p.amount), 0) as total_amount,IFNULL(sum(p.discount), 0) as total_discount,IFNULL(sum(p.fine), 0) as total_fine');
        $this->db->from('fee_payment_history as p');
        $this->db->where('p.transport_fee_details_id', $fee_details_id);
        $paid = $this->db->get()->row_array();
        $balance = $route_fare - ($paid['total_amount'] + $paid['total_discount']);
        $total_fine = $paid['total_fine'];
        return array('balance' => $balance, 'fine' => $total_fine);
    }

    public function getTransportPaymentHistory($transport_fee_details_id)
    {
        $this->db->select('h.*,t.fee_name as name,t.fee_name as fee_code,pt.name as payvia');
        $this->db->from('fee_payment_history as h');
        $this->db->join('fees_type as t', 't.id = h.type_id', 'left');
        $this->db->join('payment_types as pt', 'pt.id = h.pay_via', 'left');
        $this->db->where('h.transport_fee_details_id', $transport_fee_details_id);
        $this->db->order_by('h.id', 'asc');
        return $this->db->get()->result_array();
    }

    public function get_students_by_class($class_id, $branch_id = '')
    {
        $this->db->select('
            s.id,
            s.first_name,
            s.last_name,
            s.register_no,
            e.id as enroll_id,
            e.class_id,
            e.roll,
            se.name as section_name
        ', false);
        $this->db->from('student s');
        $this->db->join('enroll e', 'e.student_id = s.id', 'inner');
        $this->db->join('section se', 'se.id = e.section_id', 'left');
        $this->db->where('e.class_id', $class_id);
        $this->db->where('e.session_id', get_session_id());
        $this->db->where('s.active', 1);
        if (empty($branch_id) && !is_superadmin_loggedin()) {
            $branch_id = get_loggedin_branch_id();
        }
        if (!empty($branch_id)) {
            $this->db->where('e.branch_id', $branch_id);
        }
        $this->db->order_by('s.first_name', 'asc');

        $query = $this->db->get();

        if ($query === false) {
            log_message('error', print_r($this->db->error(), true));
            return [];
        }

        return $query->result_array();
    }

    public function getPaidStudentsRegister($branchID = '', $fromDate = '', $toDate = '', $feeTypeIds = array())
    {
        if (empty($branchID) || empty($fromDate) || empty($toDate)) {
            return array();
        }

        $paymentTypeMap = array();
        $paymentTypes = $this->db->select('id, name')->get('payment_types')->result_array();
        foreach ($paymentTypes as $paymentType) {
            $paymentTypeMap[$paymentType['id']] = $paymentType['name'];
        }

        $sessionID = get_session_id();
        $feeTypeIds = is_array($feeTypeIds) ? array_filter($feeTypeIds) : array();
        $rows = array();

        // New collections (fee_collect)
        if ($this->db->table_exists('fee_collect')) {
            $this->db->select('fc.id, fc.date, fc.paid_amount as amount, fc.discount, fc.fine, fc.pay_via, fc.receipt_no,
                s.first_name, s.last_name, s.register_no,
                c.name as class_name, se.name as section_name, e.roll as roll_no, e.id as enroll_id,
                COALESCE(ft.fee_name, IF(fc.fee_type = "transport", "Transport Fee", IF(fc.fee_type = "last_balance", "Last Year Balance", "Fee"))) as fee_name', false);
            $this->db->from('fee_collect as fc');
            $this->db->join('student as s', 's.id = fc.student_id', 'inner');
            $this->db->join('enroll as e', 'e.student_id = fc.student_id AND e.session_id = ' . $this->db->escape($sessionID), 'inner');
            $this->db->join('class as c', 'c.id = e.class_id', 'left');
            $this->db->join('section as se', 'se.id = e.section_id', 'left');
            $this->db->join('fees_type as ft', 'ft.id = fc.fee_type_id', 'left');
            $this->db->where('fc.branch_id', $branchID);
            $this->db->where('e.branch_id', $branchID);
            $this->db->where('s.active', 1);
            $this->db->where('fc.date >=', $fromDate);
            $this->db->where('fc.date <=', $toDate);
            if (!empty($feeTypeIds)) {
                $this->db->where_in('fc.fee_type_id', $feeTypeIds);
            }
            $this->db->order_by('fc.date', 'desc');
            $this->db->order_by('e.id', 'asc');
            $this->db->order_by('fc.id', 'asc');
            $collectRows = $this->db->get()->result_array();
            foreach ($collectRows as $row) {
                $row['receipt_label'] = 'RCP-' . str_pad((string) $row['receipt_no'], 5, '0', STR_PAD_LEFT);
                $rows[] = $row;
            }
        }

        // Legacy / transport collections (fee_payment_history)
        $this->db->select('h.id, h.date, h.amount, h.discount, h.fine, h.pay_via,
            s.first_name, s.last_name, s.register_no,
            c.name as class_name, se.name as section_name, e.roll as roll_no, e.id as enroll_id,
            COALESCE(ft.fee_name, "Transport Fee") as fee_name', false);
        $this->db->from('fee_payment_history as h');
        $this->db->join('fee_allocation as fa', 'fa.id = h.allocation_id', 'left');
        $this->db->join('transport_fee_details as tfd', 'tfd.id = h.transport_fee_details_id', 'left');
        $this->db->join('enroll as e', 'e.id = COALESCE(fa.student_id, tfd.enroll_id)', 'inner', false);
        $this->db->join('student as s', 's.id = e.student_id', 'inner');
        $this->db->join('class as c', 'c.id = e.class_id', 'left');
        $this->db->join('section as se', 'se.id = e.section_id', 'left');
        $this->db->join('fees_type as ft', 'ft.id = h.type_id', 'left');
        $this->db->where('e.branch_id', $branchID);
        $this->db->where('e.session_id', $sessionID);
        $this->db->where('s.active', 1);
        $this->db->where('h.date >=', $fromDate);
        $this->db->where('h.date <=', $toDate);
        if (!empty($feeTypeIds)) {
            $this->db->where_in('h.type_id', $feeTypeIds);
        }
        $this->db->order_by('h.date', 'desc');
        $this->db->order_by('e.id', 'asc');
        $this->db->order_by('h.id', 'asc');
        $historyRows = $this->db->get()->result_array();
        foreach ($historyRows as $row) {
            $row['receipt_label'] = 'RCP-' . str_pad((string) $row['id'], 5, '0', STR_PAD_LEFT);
            $rows[] = $row;
        }

        if (empty($rows)) {
            return array();
        }

        $grouped = array();
        foreach ($rows as $row) {
            $groupKey = $row['enroll_id'] . '_' . $row['date'];
            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = array(
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'register_no' => $row['register_no'],
                    'class_name' => $row['class_name'],
                    'section_name' => $row['section_name'],
                    'roll_no' => $row['roll_no'],
                    'date' => $row['date'],
                    'receipts' => array(),
                    'fee_names' => array(),
                    'payment_methods' => array(),
                    'items' => array(),
                );
            }

            $payVia = $row['pay_via'];
            if (is_numeric($payVia) && isset($paymentTypeMap[$payVia])) {
                $paymentMethod = $paymentTypeMap[$payVia];
            } else {
                $paymentMethod = ucfirst((string) $payVia);
            }

            $receiptLabel = $row['receipt_label'];
            if ($receiptLabel !== '' && !in_array($receiptLabel, $grouped[$groupKey]['receipts'], true)) {
                $grouped[$groupKey]['receipts'][] = $receiptLabel;
            }
            $grouped[$groupKey]['fee_names'][] = $row['fee_name'];
            if ($paymentMethod !== '' && !in_array($paymentMethod, $grouped[$groupKey]['payment_methods'], true)) {
                $grouped[$groupKey]['payment_methods'][] = $paymentMethod;
            }
            $grouped[$groupKey]['items'][] = array(
                'fee_name' => $row['fee_name'],
                'fine' => (float) $row['fine'],
                'discount' => (float) $row['discount'],
                'paid_amount' => (float) $row['amount'],
                'receipt_no' => $receiptLabel,
                'date' => $row['date'],
                'payment_method' => $paymentMethod,
            );
        }

        $result = array();
        foreach ($grouped as $group) {
            $totalFine = 0;
            $totalDiscount = 0;
            $totalPaid = 0;
            foreach ($group['items'] as $item) {
                $totalFine += $item['fine'];
                $totalDiscount += $item['discount'];
                $totalPaid += $item['paid_amount'];
            }

            $result[] = array(
                'date' => $group['date'],
                'receipt_no' => implode(', ', $group['receipts']),
                'register_no' => $group['register_no'],
                'first_name' => $group['first_name'],
                'last_name' => $group['last_name'],
                'class_name' => $group['class_name'],
                'section_name' => $group['section_name'],
                'roll_no' => $group['roll_no'],
                'fee_name' => implode("\n", $group['fee_names']),
                'payment_method' => implode(', ', $group['payment_methods']),
                'total_fine_sum' => $totalFine,
                'total_discount_sum' => $totalDiscount,
                'total_paid_sum' => $totalPaid,
                'items' => array_values($group['items']),
                'item_count' => count($group['items']),
                'fee_count' => count($group['items']),
            );
        }

        return $result;
    }

    private function normalizeFeeNameKey($name)
    {
        $key = strtolower($name);
        $key = preg_replace('/\s+/', '_', $key);
        $key = preg_replace('/_+/', '_', $key);
        return trim($key, '_');
    }

    private function branchFeeTypeFilter($alias = 'ft')
    {
        if (!is_superadmin_loggedin()) {
            $this->db->where($alias . '.branch_id', get_loggedin_branch_id());
        }
    }

    public function get_used_fee_types()
    {
        $this->db->select('DISTINCT ft.id, ft.fee_name', false);
        $this->db->from('fee_master_classwise fmc');
        $this->db->join('fees_type ft', 'ft.id = fmc.fee_type_id', 'inner');
        $this->branchFeeTypeFilter('ft');
        return $this->db->order_by('ft.id', 'asc')->get()->result_array();
    }

    public function get_class_wise_fee_structure()
    {
        $this->db->select('fmc.class_id, ft.fee_name, SUM(fmc.amount) as amount');
        $this->db->from('fee_master_classwise fmc');
        $this->db->join('fees_type ft', 'ft.id = fmc.fee_type_id', 'inner');
        $this->branchFeeTypeFilter('ft');
        $this->db->group_by(['fmc.class_id', 'ft.fee_name']);
        $rows = $this->db->get()->result_array();
        $result = array();
        foreach ($rows as $row) {
            $result[$row['class_id']][$this->normalizeFeeNameKey($row['fee_name'])] = (float) $row['amount'];
        }
        return $result;
    }

    public function get_class_wise_fee_structure_for_report()
    {
        return $this->get_class_wise_fee_type_balances();
    }

    /**
     * Class-wise SUM of remaining student balances per fee type.
     * Example: First Class → Admission Fee = SUM(balance) of all First Class students' admission allocations.
     */
    public function get_class_wise_fee_type_balances($session_id = '', $branch_id = '')
    {
        if (empty($session_id)) {
            $session_id = get_session_id();
        }
        if (empty($branch_id) && !is_superadmin_loggedin()) {
            $branch_id = get_loggedin_branch_id();
        }

        $this->db->select('e.class_id, ft.fee_name, SUM(sfa.balance) as total', false);
        $this->db->from('student_fee_allocation sfa');
        $this->db->join('enroll e', 'e.student_id = sfa.student_id AND e.class_id = sfa.class_id AND e.session_id = ' . $this->db->escape($session_id), 'inner');
        $this->db->join('student s', 's.id = sfa.student_id', 'inner');
        $this->db->join('fees_type ft', 'ft.id = sfa.fee_type_id', 'inner');
        $this->db->where('s.active', 1);
        if (!empty($branch_id)) {
            $this->db->where('e.branch_id', $branch_id);
        }
        $this->db->group_by(['e.class_id', 'sfa.fee_type_id', 'ft.fee_name']);
        $rows = $this->db->get()->result_array();

        $result = array();
        foreach ($rows as $row) {
            $result[$row['class_id']][$this->normalizeFeeNameKey($row['fee_name'])] = (float) $row['total'];
        }
        return $result;
    }

    public function get_class_wise_transport_fee()
    {
        $sessionID = get_session_id();
        $this->db->select('e.class_id, MAX(sp.route_fare) as route_fare');
        $this->db->from('enroll e');
        $this->db->join('transport_fee_details tfd', 'tfd.enroll_id = e.id', 'left');
        $this->db->join('transport_stoppage_point sp', 'sp.id = tfd.stoppage_point_id', 'left');
        $this->db->where('e.session_id', $sessionID);
        if (!is_superadmin_loggedin()) {
            $this->db->where('e.branch_id', get_loggedin_branch_id());
        }
        $this->db->group_by('e.class_id');
        $rows = $this->db->get()->result_array();
        $result = array();
        foreach ($rows as $row) {
            $result[$row['class_id']] = !empty($row['route_fare']) ? (float) $row['route_fare'] : '-';
        }
        return $result;
    }

    public function get_class_wise_transport_fee_for_estimation($session_id = '', $branch_id = '')
    {
        return $this->get_class_wise_transport_balances($session_id, $branch_id);
    }

    /**
     * Class-wise SUM of remaining transport balances for all students in each class.
     */
    public function get_class_wise_transport_balances($session_id = '', $branch_id = '')
    {
        if (empty($session_id)) {
            $session_id = get_session_id();
        }
        if (empty($branch_id) && !is_superadmin_loggedin()) {
            $branch_id = get_loggedin_branch_id();
        }

        $historyPaidSql = "(SELECT transport_fee_details_id AS fd_id,
                IFNULL(SUM(amount), 0) + IFNULL(SUM(discount), 0) AS total_paid
            FROM fee_payment_history
            WHERE transport_fee_details_id IS NOT NULL
            GROUP BY transport_fee_details_id)";

        $collectPaidSql = "0";
        if ($this->db->table_exists('fee_collect')) {
            $collectPaidSql = "IFNULL((SELECT SUM(fc.paid_amount) + SUM(fc.discount)
                FROM fee_collect fc
                WHERE fc.fee_type = 'transport'
                  AND fc.trans_fd_id = tfd.id), 0)";
        }

        $sql = "SELECT e.class_id,
                SUM(GREATEST(0,
                    IFNULL(sp.route_fare, 0)
                    - IFNULL(hp.total_paid, 0)
                    - {$collectPaidSql}
                )) AS total
            FROM enroll e
            INNER JOIN student s ON s.id = e.student_id AND s.active = 1
            INNER JOIN transport_fee_details tfd ON tfd.enroll_id = e.id
            INNER JOIN transport_stoppage_point sp ON sp.id = tfd.stoppage_point_id
            LEFT JOIN {$historyPaidSql} hp ON hp.fd_id = tfd.id
            WHERE e.session_id = ?";

        $params = array($session_id);
        if (!empty($branch_id)) {
            $sql .= " AND e.branch_id = ?";
            $params[] = $branch_id;
        }
        $sql .= " GROUP BY e.class_id";

        $rows = $this->db->query($sql, $params)->result_array();
        $result = array();
        foreach ($rows as $row) {
            $result[$row['class_id']] = (float) $row['total'];
        }
        return $result;
    }

    public function get_class_wise_set_totals()
    {
        return array();
    }

    public function get_class_wise_set_amounts()
    {
        return array();
    }

    public function get_class_wise_assigned_amount($session_id = '', $branch_id = '')
    {
        if (empty($session_id)) {
            $session_id = get_session_id();
        }
        if (empty($branch_id) && !is_superadmin_loggedin()) {
            $branch_id = get_loggedin_branch_id();
        }

        $this->db->select('e.class_id, SUM(sfa.amount) as total');
        $this->db->from('student_fee_allocation sfa');
        $this->db->join('enroll e', 'e.student_id = sfa.student_id AND e.class_id = sfa.class_id', 'inner');
        $this->db->join('student s', 's.id = sfa.student_id', 'inner');
        $this->db->where('e.session_id', $session_id);
        $this->db->where('s.active', 1);
        if (!empty($branch_id)) {
            $this->db->where('e.branch_id', $branch_id);
        }
        $this->db->group_by('e.class_id');
        return $this->mapClassTotals($this->db->get()->result_array());
    }

    public function get_class_wise_received_amount($session_id = '', $branch_id = '')
    {
        if (empty($session_id)) {
            $session_id = get_session_id();
        }
        if (empty($branch_id) && !is_superadmin_loggedin()) {
            $branch_id = get_loggedin_branch_id();
        }

        $this->db->select('e.class_id, SUM(sfa.paid) as total');
        $this->db->from('student_fee_allocation sfa');
        $this->db->join('enroll e', 'e.student_id = sfa.student_id AND e.class_id = sfa.class_id', 'inner');
        $this->db->join('student s', 's.id = sfa.student_id', 'inner');
        $this->db->where('e.session_id', $session_id);
        $this->db->where('s.active', 1);
        if (!empty($branch_id)) {
            $this->db->where('e.branch_id', $branch_id);
        }
        $this->db->group_by('e.class_id');
        return $this->mapClassTotals($this->db->get()->result_array());
    }

    public function get_class_wise_last_balance($session_id = '', $branch_id = '')
    {
        if (empty($session_id)) {
            $session_id = get_session_id();
        }
        if (empty($branch_id) && !is_superadmin_loggedin()) {
            $branch_id = get_loggedin_branch_id();
        }

        $this->db->select('e.class_id, SUM(slb.last_balance) as total');
        $this->db->from('student_last_balance slb');
        $this->db->join('enroll e', 'e.student_id = slb.student_id AND e.session_id = slb.session_id', 'inner');
        $this->db->join('student s', 's.id = slb.student_id', 'inner');
        $this->db->where('slb.session_id', $session_id);
        $this->db->where('s.active', 1);
        if (!empty($branch_id)) {
            $this->db->where('e.branch_id', $branch_id);
        }
        $this->db->group_by('e.class_id');
        return $this->mapClassTotals($this->db->get()->result_array());
    }

    /**
     * Student-wise remaining balances per fee type within one class.
     */
    public function get_student_wise_fee_type_balances($class_id, $session_id = '', $branch_id = '')
    {
        if (empty($class_id)) {
            return array();
        }
        if (empty($session_id)) {
            $session_id = get_session_id();
        }
        if (empty($branch_id) && !is_superadmin_loggedin()) {
            $branch_id = get_loggedin_branch_id();
        }

        $this->db->select('sfa.student_id, ft.fee_name, SUM(sfa.balance) as total', false);
        $this->db->from('student_fee_allocation sfa');
        $this->db->join('enroll e', 'e.student_id = sfa.student_id AND e.class_id = sfa.class_id AND e.session_id = ' . $this->db->escape($session_id), 'inner');
        $this->db->join('student s', 's.id = sfa.student_id', 'inner');
        $this->db->join('fees_type ft', 'ft.id = sfa.fee_type_id', 'inner');
        $this->db->where('sfa.class_id', $class_id);
        $this->db->where('e.class_id', $class_id);
        $this->db->where('s.active', 1);
        if (!empty($branch_id)) {
            $this->db->where('e.branch_id', $branch_id);
        }
        $this->db->group_by(['sfa.student_id', 'sfa.fee_type_id', 'ft.fee_name']);
        $rows = $this->db->get()->result_array();

        $result = array();
        foreach ($rows as $row) {
            $result[$row['student_id']][$this->normalizeFeeNameKey($row['fee_name'])] = (float) $row['total'];
        }
        return $result;
    }

    public function get_student_wise_last_balance($class_id, $session_id = '', $branch_id = '')
    {
        if (empty($class_id)) {
            return array();
        }
        if (empty($session_id)) {
            $session_id = get_session_id();
        }
        if (empty($branch_id) && !is_superadmin_loggedin()) {
            $branch_id = get_loggedin_branch_id();
        }

        $this->db->select('slb.student_id, SUM(slb.last_balance) as total');
        $this->db->from('student_last_balance slb');
        $this->db->join('enroll e', 'e.student_id = slb.student_id AND e.session_id = slb.session_id', 'inner');
        $this->db->join('student s', 's.id = slb.student_id', 'inner');
        $this->db->where('slb.session_id', $session_id);
        $this->db->where('e.class_id', $class_id);
        $this->db->where('s.active', 1);
        if (!empty($branch_id)) {
            $this->db->where('e.branch_id', $branch_id);
        }
        $this->db->group_by('slb.student_id');
        return $this->mapStudentTotals($this->db->get()->result_array());
    }

    public function get_student_wise_assigned_amount($class_id, $session_id = '', $branch_id = '')
    {
        if (empty($class_id)) {
            return array();
        }
        if (empty($session_id)) {
            $session_id = get_session_id();
        }
        if (empty($branch_id) && !is_superadmin_loggedin()) {
            $branch_id = get_loggedin_branch_id();
        }

        $this->db->select('sfa.student_id, SUM(sfa.amount) as total');
        $this->db->from('student_fee_allocation sfa');
        $this->db->join('enroll e', 'e.student_id = sfa.student_id AND e.class_id = sfa.class_id', 'inner');
        $this->db->join('student s', 's.id = sfa.student_id', 'inner');
        $this->db->where('e.session_id', $session_id);
        $this->db->where('sfa.class_id', $class_id);
        $this->db->where('e.class_id', $class_id);
        $this->db->where('s.active', 1);
        if (!empty($branch_id)) {
            $this->db->where('e.branch_id', $branch_id);
        }
        $this->db->group_by('sfa.student_id');
        return $this->mapStudentTotals($this->db->get()->result_array());
    }

    public function get_student_wise_received_amount($class_id, $session_id = '', $branch_id = '')
    {
        if (empty($class_id)) {
            return array();
        }
        if (empty($session_id)) {
            $session_id = get_session_id();
        }
        if (empty($branch_id) && !is_superadmin_loggedin()) {
            $branch_id = get_loggedin_branch_id();
        }

        $this->db->select('sfa.student_id, SUM(sfa.paid) as total');
        $this->db->from('student_fee_allocation sfa');
        $this->db->join('enroll e', 'e.student_id = sfa.student_id AND e.class_id = sfa.class_id', 'inner');
        $this->db->join('student s', 's.id = sfa.student_id', 'inner');
        $this->db->where('e.session_id', $session_id);
        $this->db->where('sfa.class_id', $class_id);
        $this->db->where('e.class_id', $class_id);
        $this->db->where('s.active', 1);
        if (!empty($branch_id)) {
            $this->db->where('e.branch_id', $branch_id);
        }
        $this->db->group_by('sfa.student_id');
        return $this->mapStudentTotals($this->db->get()->result_array());
    }

    public function get_student_wise_transport_balances($class_id, $session_id = '', $branch_id = '')
    {
        if (empty($class_id)) {
            return array();
        }
        if (empty($session_id)) {
            $session_id = get_session_id();
        }
        if (empty($branch_id) && !is_superadmin_loggedin()) {
            $branch_id = get_loggedin_branch_id();
        }

        $historyPaidSql = "(SELECT transport_fee_details_id AS fd_id,
                IFNULL(SUM(amount), 0) + IFNULL(SUM(discount), 0) AS total_paid
            FROM fee_payment_history
            WHERE transport_fee_details_id IS NOT NULL
            GROUP BY transport_fee_details_id)";

        $collectPaidSql = "0";
        if ($this->db->table_exists('fee_collect')) {
            $collectPaidSql = "IFNULL((SELECT SUM(fc.paid_amount) + SUM(fc.discount)
                FROM fee_collect fc
                WHERE fc.fee_type = 'transport'
                  AND fc.trans_fd_id = tfd.id), 0)";
        }

        $sql = "SELECT e.student_id,
                SUM(GREATEST(0,
                    IFNULL(sp.route_fare, 0)
                    - IFNULL(hp.total_paid, 0)
                    - {$collectPaidSql}
                )) AS total
            FROM enroll e
            INNER JOIN student s ON s.id = e.student_id AND s.active = 1
            INNER JOIN transport_fee_details tfd ON tfd.enroll_id = e.id
            INNER JOIN transport_stoppage_point sp ON sp.id = tfd.stoppage_point_id
            LEFT JOIN {$historyPaidSql} hp ON hp.fd_id = tfd.id
            WHERE e.session_id = ?
              AND e.class_id = ?";

        $params = array($session_id, $class_id);
        if (!empty($branch_id)) {
            $sql .= " AND e.branch_id = ?";
            $params[] = $branch_id;
        }
        $sql .= " GROUP BY e.student_id";

        return $this->mapStudentTotals($this->db->query($sql, $params)->result_array());
    }

    private function mapClassTotals($rows)
    {
        $result = array();
        foreach ($rows as $row) {
            $result[$row['class_id']] = (float) $row['total'];
        }
        return $result;
    }

    private function mapStudentTotals($rows)
    {
        $result = array();
        foreach ($rows as $row) {
            $result[$row['student_id']] = (float) $row['total'];
        }
        return $result;
    }

    public function getStudentFeeDetails($enroll_id = '')
    {
        $enroll = $this->db->select('e.student_id, e.class_id')
            ->from('enroll e')
            ->where('e.id', $enroll_id)
            ->get()->row_array();
        if (empty($enroll)) {
            return array();
        }

        $lastBalance = $this->db->select('last_balance')
            ->where(array('student_id' => $enroll['student_id'], 'session_id' => get_session_id()))
            ->get('student_last_balance')
            ->row('last_balance');

        $this->db->select('sfa.*, ft.fee_name');
        $this->db->from('student_fee_allocation sfa');
        $this->db->join('fees_type ft', 'ft.id = sfa.fee_type_id', 'left');
        $this->db->where('sfa.student_id', $enroll['student_id']);
        $this->db->where('sfa.class_id', $enroll['class_id']);
        $rows = $this->db->order_by('sfa.month', 'asc')->get()->result_array();

        // Paid discount / fine / receipts from fee_collect (after pay)
        $collectMap = array();
        if ($this->db->table_exists('fee_collect') && !empty($rows)) {
            $allocationIds = array_column($rows, 'id');
            $this->db->select('fc.allocation_id,
                IFNULL(SUM(fc.paid_amount), 0) as paid_amount,
                IFNULL(SUM(fc.discount), 0) as paid_discount,
                IFNULL(SUM(fc.fine), 0) as paid_fine,
                GROUP_CONCAT(DISTINCT CONCAT("RCP-", LPAD(fc.receipt_no, 5, "0")) ORDER BY fc.id SEPARATOR ", ") as receipt_nos,
                GROUP_CONCAT(DISTINCT pt.name ORDER BY fc.id SEPARATOR ", ") as pay_methods', false);
            $this->db->from('fee_collect fc');
            $this->db->join('payment_types pt', 'pt.id = fc.pay_via', 'left');
            $this->db->where_in('fc.allocation_id', $allocationIds);
            $this->db->group_by('fc.allocation_id');
            foreach ($this->db->get()->result_array() as $cRow) {
                $collectMap[$cRow['allocation_id']] = $cRow;
            }
        }

        $result = array();
        foreach ($rows as $index => $row) {
            $row['allocation_id'] = $row['id'];
            $collect = $collectMap[$row['id']] ?? array();

            $row['paid_amount'] = (float) ($row['paid'] ?? 0);
            $row['discount'] = (float) ($collect['paid_discount'] ?? 0); // after pay
            $row['fine'] = (float) ($collect['paid_fine'] ?? 0); // after pay
            $row['receipt_no'] = !empty($collect['receipt_nos']) ? $collect['receipt_nos'] : '-';
            $row['payment_method_name'] = !empty($collect['pay_methods']) ? $collect['pay_methods'] : '-';

            // Before-pay (configured / due) fine
            $fineType = strtolower(trim((string) ($row['fine_type'] ?? '')));
            $configuredFine = (float) ($row['fine_calculated_amount'] ?? 0);
            if ($configuredFine <= 0) {
                if (in_array($fineType, array('percentage', 'percent'), true)) {
                    $configuredFine = ((float) $row['amount'] * (float) $row['per_val']) / 100;
                } elseif (in_array($fineType, array('fixed', 'fix amount', 'fix_amount', 'amount'), true)) {
                    $configuredFine = (float) ($row['amt_val'] ?? 0);
                }
            }
            $row['fine_due'] = $configuredFine;
            $row['discount_due'] = (float) ($row['discount_fixed'] ?? 0);
            if (!empty($row['discount_type']) && $row['discount_type'] === 'percentage') {
                $row['discount_due'] = ((float) $row['amount'] * (float) ($row['discount_percentage'] ?? 0)) / 100;
            }

            // Remaining due fine after payments already collected
            $row['fine_remaining'] = max(0, $row['fine_due'] - $row['fine']);
            $row['discount_remaining'] = max(0, $row['discount_due'] - $row['discount']);

            $row['discount_type'] = $row['discount_type'] ?? '';
            $row['discount_percentage'] = $row['discount_percentage'] ?? 0;
            $row['discount_fixed'] = $row['discount_fixed'] ?? 0;

            if ($index === 0 && $lastBalance !== null) {
                $row['previous_last_balance'] = (float) $lastBalance;
            }
            $result[] = $row;
        }
        return $result;
    }

    /**
     * Resolve configured (before-pay) fine for an allocation row.
     */
    public function resolveConfiguredFine($row)
    {
        $configuredFine = (float) ($row['fine_calculated_amount'] ?? $row['fine_due'] ?? 0);
        if ($configuredFine > 0) {
            return $configuredFine;
        }
        $fineType = strtolower(trim((string) ($row['fine_type'] ?? '')));
        $amount = (float) ($row['amount'] ?? 0);
        if (in_array($fineType, array('percentage', 'percent'), true)) {
            return ($amount * (float) ($row['per_val'] ?? 0)) / 100;
        }
        if (in_array($fineType, array('fixed', 'fix amount', 'fix_amount', 'amount'), true)) {
            return (float) ($row['amt_val'] ?? 0);
        }
        return 0;
    }

    /**
     * Monthly paid totals + receipt numbers for fees card columns.
     * Returns: [monthNo => ['paid' => float, 'discount' => float, 'fine' => float, 'receipts' => [...]]]
     */
    public function getStudentMonthlyCollections($student_id = '')
    {
        $result = array();
        if (empty($student_id) || !$this->db->table_exists('fee_collect')) {
            return $result;
        }

        $this->db->select('fc.receipt_no, fc.paid_amount, fc.discount, fc.fine, fc.fee_type, fc.date, sfa.month as allocation_month, tff.month as transport_month', false);
        $this->db->from('fee_collect as fc');
        $this->db->join('student_fee_allocation as sfa', 'sfa.id = fc.allocation_id', 'left');
        $this->db->join('transport_fee_details as tfd', 'tfd.id = fc.trans_fd_id', 'left');
        $this->db->join('transport_fee_fine as tff', 'tff.id = tfd.transport_fee_fine_id', 'left');
        $this->db->where('fc.student_id', $student_id);
        $this->db->order_by('fc.id', 'asc');
        $rows = $this->db->get()->result_array();

        foreach ($rows as $row) {
            $allocMonths = array_values(array_filter(array_map('intval', explode(',', (string) ($row['allocation_month'] ?? '')))));
            if (count($allocMonths) === 1) {
                $monthNo = $allocMonths[0];
            } elseif (!empty($row['transport_month'])) {
                $monthNo = (int) $row['transport_month'];
            } elseif (($row['fee_type'] ?? '') === 'last_balance') {
                $monthNo = 4;
            } else {
                $monthNo = (int) date('n', strtotime($row['date']));
            }

            if ($monthNo < 1 || $monthNo > 12) {
                continue;
            }

            if (!isset($result[$monthNo])) {
                $result[$monthNo] = array('paid' => 0, 'discount' => 0, 'fine' => 0, 'receipts' => array());
            }

            $result[$monthNo]['paid'] += (float) ($row['paid_amount'] ?? 0);
            $result[$monthNo]['discount'] += (float) ($row['discount'] ?? 0);
            $result[$monthNo]['fine'] += (float) ($row['fine'] ?? 0);
            $receiptLabel = 'RCP-' . str_pad((string) $row['receipt_no'], 5, '0', STR_PAD_LEFT);
            if (!in_array($receiptLabel, $result[$monthNo]['receipts'], true)) {
                $result[$monthNo]['receipts'][] = $receiptLabel;
            }
        }

        return $result;
    }

    public function getStudentSalesBills($student_id = '')
    {
        if (!$this->db->table_exists('sales_bill') || empty($student_id)) {
            return array();
        }
        $this->db->where('user_id', $student_id);
        if (!is_superadmin_loggedin()) {
            $this->db->where('branch_id', get_loggedin_branch_id());
        }
        return $this->db->order_by('date', 'asc')->get('sales_bill')->result();
    }

    public function getFeeMasterList()
    {
        $this->db->select('fm.id, fm.branch_id, ft.fee_name as fees_type, fm.student_category, fmc.fine_type, fmc.per_val, fmc.amt_val, GROUP_CONCAT(DISTINCT CONCAT(c.name, ": ", fmc.amount) ORDER BY c.name SEPARATOR ", ") as class_amounts', false);
        $this->db->from('fee_master fm');
        $this->db->join('fee_master_classwise fmc', 'fmc.fee_master_id = fm.id', 'left');
        $this->db->join('fees_type ft', 'ft.id = fm.fee_types', 'left');
        $this->db->join('class c', 'c.id = fmc.class_id', 'left');
        if (!is_superadmin_loggedin()) {
            $this->db->where('fm.branch_id', get_loggedin_branch_id());
        }
        $this->db->group_by('fm.id');
        $rows = $this->db->order_by('fm.id', 'desc')->get()->result_array();
        foreach ($rows as &$row) {
            $row['student_cat'] = $this->formatStudentCategories($row['student_category']);
        }
        return $rows;
    }

    public function getFeeMasterAllocationDetails($fee_master_id = '')
    {
        $this->db->select('class_id, amount, due_date');
        $this->db->from('fee_master_classwise');
        $this->db->where('fee_master_id', $fee_master_id);
        return $this->db->get()->result_array();
    }

    public function saveFeeMaster($data = array())
    {
        $feeType = $this->db->get_where('fees_type', array('id' => $data['fee_type_id']))->row_array();
        if (empty($feeType)) {
            return false;
        }

        $master = array(
            'branch_id' => $data['branch_id'],
            'fee_types' => $data['fee_type_id'],
            'student_category' => json_encode(array_values((array) $data['student_category'])),
        );
        $this->db->insert('fee_master', $master);
        $masterID = $this->db->insert_id();
        $months = $feeType['month'];

        foreach ((array) $data['classes'] as $index => $classID) {
            $amount = isset($data['amount'][$index]) ? (float) $data['amount'][$index] : 0;
            if ($amount <= 0) {
                continue;
            }
            $dueDate = null;
            if (!empty($data['due_day'][$index])) {
                $monthPart = strtok((string) $months, ',');
                $dueDate = date('Y-m-d', strtotime(date('Y') . '-' . trim($monthPart) . '-' . str_pad($data['due_day'][$index], 2, '0', STR_PAD_LEFT)));
            }
            $fineAmount = isset($data['fine_amount'][$index]) ? (float) $data['fine_amount'][$index] : 0;
            $this->db->insert('fee_master_classwise', array(
                'class_id' => $classID,
                'fee_master_id' => $masterID,
                'fee_type_id' => $data['fee_type_id'],
                'student_category_id' => json_encode(array_values((array) $data['student_category'])),
                'amount' => $amount,
                'month' => $months,
                'due_date' => $dueDate,
                'fine_type' => $data['fine_type'],
                'per_val' => (int) ($data['per_val'] ?? 0),
                'fine_calculated_amount' => (int) $fineAmount,
                'amt_val' => (int) ($data['amt_val'] ?? 0),
            ));
        }

        $this->allocateFeeMasterToStudents($masterID);
        return $masterID;
    }

    public function updateFeeMaster($feeMasterID, $data = array())
    {
        $feeType = $this->db->get_where('fees_type', array('id' => $data['fee_type_id']))->row_array();
        if (empty($feeType) || empty($feeMasterID)) {
            return false;
        }

        $this->db->where('id', $feeMasterID)->update('fee_master', array(
            'branch_id' => $data['branch_id'],
            'fee_types' => $data['fee_type_id'],
            'student_category' => json_encode(array_values((array) $data['student_category'])),
        ));
        $this->db->where('fee_master_id', $feeMasterID)->delete('fee_master_classwise');

        $data['classes'] = $data['classes'] ?? array();
        $this->saveFeeMasterClasswiseRows($feeMasterID, $data, $feeType['month']);
        $this->allocateFeeMasterToStudents($feeMasterID);
        return true;
    }

    /**
     * Push fee_master_classwise rows into student_fee_allocation for matching enrolled students.
     */
    public function allocateFeeMasterToStudents($feeMasterID = '')
    {
        if (empty($feeMasterID)) {
            return 0;
        }

        $master = $this->db->get_where('fee_master', array('id' => $feeMasterID))->row_array();
        if (empty($master)) {
            return 0;
        }

        $classwiseRows = $this->db->get_where('fee_master_classwise', array('fee_master_id' => $feeMasterID))->result_array();
        if (empty($classwiseRows)) {
            return 0;
        }

        $sessionID = get_session_id();
        $inserted = 0;
        $batch = array();

        foreach ($classwiseRows as $record) {
            $monthsArr = array_filter(array_map('trim', explode(',', (string) $record['month'])));
            if (empty($monthsArr)) {
                continue;
            }

            $categories = json_decode($record['student_category_id'], true);
            if (!is_array($categories)) {
                $categories = array();
            }
            $categories = array_map('strval', $categories);

            $this->db->select('e.student_id, s.category_id');
            $this->db->from('enroll e');
            $this->db->join('student s', 's.id = e.student_id', 'inner');
            $this->db->where('e.class_id', $record['class_id']);
            $this->db->where('e.session_id', $sessionID);
            $this->db->where('e.branch_id', $master['branch_id']);
            $this->db->where('s.active', 1);
            $students = $this->db->get()->result_array();

            foreach ($students as $student) {
                $studentCategory = (string) ($student['category_id'] ?? '');
                if (!empty($categories) && !in_array($studentCategory, $categories, true)) {
                    continue;
                }

                $fineCalculated = (float) ($record['fine_calculated_amount'] ?? 0);
                if ($record['fine_type'] == 'percentage' && !empty($record['per_val'])) {
                    $fineCalculated = ((float) $record['amount'] * (float) $record['per_val']) / 100;
                } elseif (($record['fine_type'] == 'fix amount' || $record['fine_type'] == 'fixed') && !empty($record['amt_val'])) {
                    $fineCalculated = (float) $record['amt_val'];
                }

                foreach ($monthsArr as $month) {
                    $month = (string) (int) $month;
                    if ($month === '0') {
                        continue;
                    }

                    $exists = $this->db->get_where('student_fee_allocation', array(
                        'student_id' => $student['student_id'],
                        'class_id' => $record['class_id'],
                        'fee_master_id' => $feeMasterID,
                        'month' => $month,
                    ))->row_array();

                    if (!empty($exists)) {
                        // Refresh unpaid rows so structure edits apply
                        if ((float) ($exists['paid'] ?? 0) <= 0) {
                            $this->db->where('id', $exists['id'])->update('student_fee_allocation', array(
                                'fee_type_id' => $record['fee_type_id'],
                                'student_category_id' => $record['student_category_id'],
                                'amount' => $record['amount'],
                                'balance' => $record['amount'],
                                'due_date' => $record['due_date'],
                                'fine_type' => $record['fine_type'] ?? '',
                                'per_val' => (int) ($record['per_val'] ?? 0),
                                'fine_calculated_amount' => $fineCalculated,
                                'amt_val' => (int) ($record['amt_val'] ?? 0),
                            ));
                        }
                        continue;
                    }

                    $batch[] = array(
                        'student_id' => $student['student_id'],
                        'class_id' => $record['class_id'],
                        'fee_type_id' => $record['fee_type_id'],
                        'student_category_id' => $record['student_category_id'],
                        'fee_master_id' => $feeMasterID,
                        'amount' => $record['amount'],
                        'paid' => 0,
                        'balance' => $record['amount'],
                        'month' => $month,
                        'due_date' => $record['due_date'],
                        'fine_type' => $record['fine_type'] ?? '',
                        'per_val' => (int) ($record['per_val'] ?? 0),
                        'fine_calculated_amount' => $fineCalculated,
                        'amt_val' => (int) ($record['amt_val'] ?? 0),
                        'created_at' => date('Y-m-d H:i:s'),
                    );

                    if (count($batch) >= 200) {
                        $this->db->insert_batch('student_fee_allocation', $batch);
                        $inserted += count($batch);
                        $batch = array();
                    }
                }
            }
        }

        if (!empty($batch)) {
            $this->db->insert_batch('student_fee_allocation', $batch);
            $inserted += count($batch);
        }

        return $inserted;
    }

    public function allocateAllFeeMastersToStudents($branchID = '')
    {
        $this->db->select('id');
        $this->db->from('fee_master');
        if (!empty($branchID)) {
            $this->db->where('branch_id', $branchID);
        }
        $masters = $this->db->get()->result_array();
        $total = 0;
        foreach ($masters as $master) {
            $total += $this->allocateFeeMasterToStudents($master['id']);
        }
        return $total;
    }

    /**
     * Assign all matching fee structure rows to one student (used on admission).
     */
    public function allocateFeesForStudent($studentID, $classID, $branchID = '', $studentCategory = null)
    {
        if (empty($studentID) || empty($classID)) {
            return 0;
        }

        if ($studentCategory === null) {
            $studentCategory = $this->db->select('category_id')
                ->where('id', $studentID)
                ->get('student')
                ->row('category_id');
        }
        $studentCategory = (string) $studentCategory;

        $this->db->select('fmc.*');
        $this->db->from('fee_master_classwise fmc');
        $this->db->join('fee_master fm', 'fm.id = fmc.fee_master_id', 'inner');
        $this->db->where('fmc.class_id', $classID);
        if (!empty($branchID)) {
            $this->db->where('fm.branch_id', $branchID);
        }
        $records = $this->db->get()->result_array();
        if (empty($records)) {
            return 0;
        }

        $batch = array();
        $inserted = 0;
        foreach ($records as $record) {
            $categories = json_decode($record['student_category_id'], true);
            if (!is_array($categories)) {
                $categories = array();
            }
            $categories = array_map('strval', $categories);
            if (!empty($categories) && $studentCategory !== '' && !in_array($studentCategory, $categories, true)) {
                continue;
            }

            $monthsArr = array_filter(array_map('trim', explode(',', (string) $record['month'])));
            if (empty($monthsArr)) {
                continue;
            }

            $fineCalculated = (float) ($record['fine_calculated_amount'] ?? 0);
            if ($record['fine_type'] == 'percentage' && !empty($record['per_val'])) {
                $fineCalculated = ((float) $record['amount'] * (float) $record['per_val']) / 100;
            } elseif (($record['fine_type'] == 'fix amount' || $record['fine_type'] == 'fixed') && !empty($record['amt_val'])) {
                $fineCalculated = (float) $record['amt_val'];
            }

            foreach ($monthsArr as $month) {
                $month = (string) (int) $month;
                if ($month === '0') {
                    continue;
                }

                $exists = $this->db->get_where('student_fee_allocation', array(
                    'student_id' => $studentID,
                    'class_id' => $classID,
                    'fee_master_id' => $record['fee_master_id'],
                    'month' => $month,
                ))->row_array();
                if (!empty($exists)) {
                    continue;
                }

                $batch[] = array(
                    'student_id' => $studentID,
                    'class_id' => $classID,
                    'fee_type_id' => $record['fee_type_id'],
                    'student_category_id' => $record['student_category_id'],
                    'fee_master_id' => $record['fee_master_id'],
                    'amount' => $record['amount'],
                    'paid' => 0,
                    'balance' => $record['amount'],
                    'month' => $month,
                    'due_date' => $record['due_date'],
                    'fine_type' => $record['fine_type'] ?? '',
                    'per_val' => (int) ($record['per_val'] ?? 0),
                    'fine_calculated_amount' => $fineCalculated,
                    'amt_val' => (int) ($record['amt_val'] ?? 0),
                    'created_at' => date('Y-m-d H:i:s'),
                );
            }
        }

        if (!empty($batch)) {
            $this->db->insert_batch('student_fee_allocation', $batch);
            $inserted = count($batch);
        }
        return $inserted;
    }

    private function saveFeeMasterClasswiseRows($feeMasterID, $data, $months)
    {
        foreach ((array) $data['classes'] as $index => $classID) {
            $amount = isset($data['amount'][$index]) ? (float) $data['amount'][$index] : 0;
            if ($amount <= 0) {
                continue;
            }
            $dueDate = null;
            if (!empty($data['due_day'][$index])) {
                $monthPart = strtok((string) $months, ',');
                $dueDate = date('Y-m-d', strtotime(date('Y') . '-' . trim($monthPart) . '-' . str_pad($data['due_day'][$index], 2, '0', STR_PAD_LEFT)));
            }
            $fineAmount = isset($data['fine_amount'][$index]) ? (float) $data['fine_amount'][$index] : 0;
            $this->db->insert('fee_master_classwise', array(
                'class_id' => $classID,
                'fee_master_id' => $feeMasterID,
                'fee_type_id' => $data['fee_type_id'],
                'student_category_id' => json_encode(array_values((array) $data['student_category'])),
                'amount' => $amount,
                'month' => $months,
                'due_date' => $dueDate,
                'fine_type' => $data['fine_type'],
                'per_val' => (int) ($data['per_val'] ?? 0),
                'fine_calculated_amount' => (int) $fineAmount,
                'amt_val' => (int) ($data['amt_val'] ?? 0),
            ));
        }
        return true;
    }

    private function formatStudentCategories($json)
    {
        $ids = json_decode($json, true);
        if (empty($ids) || !is_array($ids)) {
            return $json;
        }
        $names = $this->db->select('name')->where_in('id', $ids)->get('student_category')->result_array();
        return implode(', ', array_column($names, 'name'));
    }

    public function getFeesLastStudents($classID = '', $sectionID = '')
    {
        $sessionID = get_session_id();
        // Current balance = last-year carry forward + outstanding allocated fees (amount + fine - paid)
        $this->db->select("s.id as student_id, s.register_no, CONCAT(s.first_name, ' ', s.last_name) as student_name, c.name as class_name, se.name as section_name, p.name as father_name, IFNULL(MAX(slb.last_balance), 0) as last_balance, (IFNULL(MAX(slb.last_balance), 0) + IFNULL(SUM(GREATEST(IFNULL(sfa.balance, 0), (IFNULL(sfa.amount, 0) + IFNULL(sfa.fine_calculated_amount, 0) - IFNULL(sfa.paid, 0)))), 0)) as current_balance, IFNULL(SUM(GREATEST(IFNULL(sfa.balance, 0), (IFNULL(sfa.amount, 0) + IFNULL(sfa.fine_calculated_amount, 0) - IFNULL(sfa.paid, 0)))), 0) as fee_outstanding", false);
        $this->db->from('enroll e');
        $this->db->join('student s', 's.id = e.student_id', 'inner');
        $this->db->join('class c', 'c.id = e.class_id', 'left');
        $this->db->join('section se', 'se.id = e.section_id', 'left');
        $this->db->join('parent p', 'p.id = s.parent_id', 'left');
        $this->db->join('student_last_balance slb', 'slb.student_id = s.id AND slb.session_id = e.session_id', 'left');
        $this->db->join('student_fee_allocation sfa', 'sfa.student_id = s.id AND sfa.class_id = e.class_id', 'left');
        $this->db->where('e.session_id', $sessionID);
        $this->db->where('e.class_id', $classID);
        $this->db->where('e.section_id', $sectionID);
        $this->db->where('s.active', 1);
        $branchID = $this->application_model->get_branch_id();
        if (!empty($branchID)) {
            $this->db->where('e.branch_id', $branchID);
        } elseif (!is_superadmin_loggedin()) {
            $this->db->where('e.branch_id', get_loggedin_branch_id());
        }
        $this->db->group_by('s.id');
        $rows = $this->db->order_by('s.first_name', 'asc')->get()->result_array();
        foreach ($rows as &$row) {
            $row['last_balance'] = number_format((float) ($row['last_balance'] ?? 0), 2, '.', '');
            $row['current_balance'] = number_format((float) ($row['current_balance'] ?? 0), 2, '.', '');
            $row['fee_outstanding'] = number_format((float) ($row['fee_outstanding'] ?? 0), 2, '.', '');
        }
        unset($row);
        return $rows;
    }

    public function saveStudentLastBalance($studentID, $balance, $sessionID = '')
    {
        if (empty($sessionID)) {
            $sessionID = get_session_id();
        }
        $existing = $this->db->get_where('student_last_balance', array('student_id' => $studentID, 'session_id' => $sessionID))->row_array();
        $payload = array(
            'student_id' => $studentID,
            'session_id' => $sessionID,
            'last_balance' => (float) $balance,
            'updated_at' => date('Y-m-d H:i:s'),
        );
        if ($existing) {
            $this->db->where('id', $existing['id'])->update('student_last_balance', $payload);
        } else {
            $payload['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('student_last_balance', $payload);
        }
        return true;
    }

    public function getFeesReport($classID = '', $sectionID = '')
    {
        $monthMap = array(4 => 'april', 5 => 'may', 6 => 'june', 7 => 'july', 8 => 'august', 9 => 'september', 10 => 'october', 11 => 'november', 12 => 'december', 1 => 'january', 2 => 'february', 3 => 'march');
        $students = $this->getFeesLastStudents($classID, $sectionID);
        if (empty($students)) {
            return array();
        }

        $studentIds = array_column($students, 'student_id');

        // Books / material amounts from sales bills
        $booksMap = array();
        if ($this->db->table_exists('sales_bill') && !empty($studentIds)) {
            $this->db->select('user_id, IFNULL(SUM(total), 0) as books_amount', false);
            $this->db->from('sales_bill');
            $this->db->where_in('user_id', $studentIds);
            $this->db->group_by('user_id');
            foreach ($this->db->get()->result_array() as $row) {
                $booksMap[$row['user_id']] = (float) $row['books_amount'];
            }
        }

        // Annual charges: admission / annual fee types (once-a-year heads)
        $annualMap = array();
        if (!empty($studentIds)) {
            $this->db->select('sfa.student_id, IFNULL(SUM(sfa.amount), 0) as annual_charges', false);
            $this->db->from('student_fee_allocation sfa');
            $this->db->join('fees_type ft', 'ft.id = sfa.fee_type_id', 'left');
            $this->db->where_in('sfa.student_id', $studentIds);
            $this->db->group_start();
            $this->db->like('ft.fee_name', 'admission');
            $this->db->or_like('ft.fee_name', 'annual');
            $this->db->group_end();
            $this->db->group_by('sfa.student_id');
            foreach ($this->db->get()->result_array() as $row) {
                $annualMap[$row['student_id']] = (float) $row['annual_charges'];
            }
        }

        foreach ($students as &$student) {
            $studentId = $student['student_id'];
            $student['last_amount'] = (float) ($student['last_balance'] ?? 0);
            $student['books_amount'] = (float) ($booksMap[$studentId] ?? 0);
            $student['annual_charges'] = (float) ($annualMap[$studentId] ?? 0);

            foreach ($monthMap as $monthNo => $key) {
                $unpaid = $this->db->select('IFNULL(SUM(IFNULL(amount, 0) + IFNULL(fine_calculated_amount, 0) - IFNULL(paid, 0)), 0) as unpaid', false)
                    ->where(array('student_id' => $studentId, 'month' => (string) $monthNo))
                    ->get('student_fee_allocation')
                    ->row('unpaid');
                $student[$key] = (float) ($unpaid ?? 0);
            }
        }
        unset($student);

        return $students;
    }

    public function getFeeDefaulters($filters = array())
    {
        $sessionID = get_session_id();
        $this->db->select("s.id as student_id, s.register_no, s.first_name, s.last_name, e.roll as roll_no, c.name as class_name, se.name as section_name, GROUP_CONCAT(DISTINCT ft.fee_name ORDER BY ft.fee_name SEPARATOR '\n') as fee_name, GROUP_CONCAT(sfa.amount ORDER BY ft.fee_name SEPARATOR '\n') as total_fee, GROUP_CONCAT(sfa.paid ORDER BY ft.fee_name SEPARATOR '\n') as total_paid, GROUP_CONCAT((IFNULL(sfa.amount, 0) + IFNULL(sfa.fine_calculated_amount, 0) - IFNULL(sfa.paid, 0)) ORDER BY ft.fee_name SEPARATOR '\n') as outstanding, GROUP_CONCAT(IFNULL(sfa.fine_calculated_amount, 0) ORDER BY ft.fee_name SEPARATOR '\n') as fine", false);
        $this->db->from('student_fee_allocation sfa');
        $this->db->join('student s', 's.id = sfa.student_id', 'inner');
        $this->db->join('enroll e', 'e.student_id = s.id AND e.class_id = sfa.class_id AND e.session_id = ' . $this->db->escape($sessionID), 'inner', false);
        $this->db->join('class c', 'c.id = e.class_id', 'left');
        $this->db->join('section se', 'se.id = e.section_id', 'left');
        $this->db->join('fees_type ft', 'ft.id = sfa.fee_type_id', 'left');
        $this->db->where('e.session_id', $sessionID);
        $this->db->where('s.active', 1);
        // Outstanding: balance column, or computed amount+fine-paid
        $this->db->where('(IFNULL(sfa.balance, 0) > 0 OR (IFNULL(sfa.amount, 0) + IFNULL(sfa.fine_calculated_amount, 0) - IFNULL(sfa.paid, 0)) > 0)', null, false);
        if (!empty($filters['class_id'])) {
            $this->db->where('e.class_id', $filters['class_id']);
        }
        if (!empty($filters['section_id'])) {
            $sectionIds = is_array($filters['section_id'])
                ? array_filter($filters['section_id'])
                : array($filters['section_id']);
            if (count($sectionIds) === 1) {
                $this->db->where('e.section_id', reset($sectionIds));
            } elseif (!empty($sectionIds)) {
                $this->db->where_in('e.section_id', $sectionIds);
            }
        }
        if (!empty($filters['month'])) {
            $months = array();
            foreach ((array) $filters['month'] as $month) {
                if ($month === '' || $month === null) {
                    continue;
                }
                // DB stores months as 1..12 (no leading zero); form may send 01..12
                $normalized = (int) $month;
                if ($normalized >= 1 && $normalized <= 12) {
                    $months[] = $normalized;
                }
            }
            $months = array_values(array_unique($months));
            if (!empty($months)) {
                // Match allocation month (single or comma-separated) OR due_date month
                $this->db->group_start();
                foreach ($months as $i => $m) {
                    $mEsc = $this->db->escape((string) $m);
                    $mPad = $this->db->escape(str_pad((string) $m, 2, '0', STR_PAD_LEFT));
                    $clause = "(sfa.month = {$mEsc} OR sfa.month = {$mPad} OR FIND_IN_SET({$mEsc}, REPLACE(sfa.month, ' ', '')) OR FIND_IN_SET({$mPad}, REPLACE(sfa.month, ' ', '')) OR MONTH(sfa.due_date) = {$m})";
                    if ($i === 0) {
                        $this->db->where($clause, null, false);
                    } else {
                        $this->db->or_where($clause, null, false);
                    }
                }
                $this->db->group_end();
            }
        }
        if (!empty($filters['feeTypes'])) {
            $feeTypes = array_filter((array) $filters['feeTypes']);
            if (!empty($feeTypes)) {
                $this->db->where_in('sfa.fee_type_id', $feeTypes);
            }
        }
        $branchID = $this->application_model->get_branch_id();
        if (!empty($branchID)) {
            $this->db->where('e.branch_id', $branchID);
        } elseif (!is_superadmin_loggedin()) {
            $this->db->where('e.branch_id', get_loggedin_branch_id());
        }
        $this->db->group_by('s.id');
        $this->db->order_by('s.first_name', 'asc');
        $rows = $this->db->get()->result_array();
        if (!empty($filters['min_due']) || !empty($filters['max_due'])) {
            $rows = array_values(array_filter($rows, function ($row) use ($filters) {
                $due = array_sum(array_map('floatval', preg_split("/\n|,/", (string) $row['outstanding'])));
                if (!empty($filters['min_due']) && $due < (float) $filters['min_due']) {
                    return false;
                }
                if (!empty($filters['max_due']) && $due > (float) $filters['max_due']) {
                    return false;
                }
                return true;
            }));
        }
        return $rows;
    }

    public function submitFamilyFeesPayments($payments = array())
    {
        foreach ($payments as $payment) {
            if (empty($payment['student_id'])) {
                continue;
            }
            if (!empty($payment['field'])) {
                $this->db->where('student_id', $payment['student_id']);
                if (!empty($payment['month'])) {
                    $this->db->where('month', (string) $payment['month']);
                }
                if ($payment['field'] === 'discount') {
                    $this->db->set('balance', 'GREATEST(balance - ' . (float) $payment['amount'] . ', 0)', false);
                } elseif ($payment['field'] === 'fine_calculated_amount') {
                    $this->db->set('fine_calculated_amount', (float) $payment['amount']);
                    $this->db->set('balance', 'balance + ' . (float) $payment['amount'], false);
                }
                $this->db->update('student_fee_allocation');
                continue;
            }

            $this->db->where(array(
                'student_id' => $payment['student_id'],
                'fee_type_id' => $payment['fee_type_id'],
                'month' => (string) $payment['month'],
            ));
            $row = $this->db->get('student_fee_allocation')->row_array();
            if (empty($row)) {
                continue;
            }
            $paid = (float) $row['paid'] + (float) $payment['amount'];
            $balance = max(0, (float) $row['amount'] - $paid);
            $this->db->where('id', $row['id'])->update('student_fee_allocation', array(
                'paid' => $paid,
                'balance' => $balance,
            ));
        }
        return true;
    }
}
