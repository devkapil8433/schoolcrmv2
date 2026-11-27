<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Fees extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('fees_model');
        $this->load->model('email_model');
        $this->load->library('datatables');
        if (!moduleIsEnabled('student_accounting')) {
            access_denied();
        }
    }

    public function index()
    {
        redirect(base_url('fees/type'));
    }

    /* fees type form validation rules */
    protected function type_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $this->form_validation->set_rules('type_name', translate('name'), 'trim|required|callback_unique_type');
    }

    /* fees type control — legacy route, redirect to current fee type page */
    public function type()
    {
        if (!get_permission('fees_type', 'is_view') && !get_permission('feetype', 'is_view')) {
            access_denied();
        }
        redirect(base_url('fees_structure/feetype'));
    }

    public function type_edit($id = '')
    {
        if (!get_permission('fees_type', 'is_edit')) {
            access_denied();
        }

        if ($_POST) {
            $this->type_validation();
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                $this->fees_model->typeSave($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('fees/type');
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }
        $this->data['category'] = $this->app_lib->getTable('fees_type', array('t.id' => $id), true);
        $this->data['title'] = translate('fees_type');
        $this->data['sub_page'] = 'fees/type_edit';
        $this->data['main_menu'] = 'fees';
        $this->load->view('layout/index', $this->data);
    }

    public function type_delete($id = '')
    {
        if (get_permission('fees_type', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('id', $id);
            $this->db->delete('fees_type');
        }
    }

    public function unique_type($name)
    {
        $branchID = $this->application_model->get_branch_id();
        $typeID = $this->input->post('type_id');
        if (!empty($typeID)) {
            $this->db->where_not_in('id', $typeID);
        }
        $this->db->where(array('name' => $name, 'branch_id' => $branchID));
        $uniform_row = $this->db->get('fees_type')->num_rows();
        if ($uniform_row == 0) {
            return true;
        } else {
            $this->form_validation->set_message("unique_type", translate('already_taken'));
            return false;
        }
    }

    public function group($branch_id = '')
    {
        if (!get_permission('fees_group', 'is_view')) {
            access_denied();
        }
        if ($_POST) {
            if (!get_permission('fees_group', 'is_add')) {
                ajax_access_denied();
            }
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            }
            $this->form_validation->set_rules('name', translate('group_name'), 'trim|required');
            $elems = $this->input->post('elem');
            $sel = 0;
            if (count($elems)) {
                foreach ($elems as $key => $value) {
                    if (isset($value['fees_type_id'])) {
                        $sel++;
                        $this->form_validation->set_rules('elem[' . $key . '][due_date]', translate('due_date'), 'trim|required');
                        $this->form_validation->set_rules('elem[' . $key . '][amount]', translate('amount'), 'trim|required|greater_than[0]');
                    }
                }
            }
            if ($this->form_validation->run() !== false) {
                if ($sel != 0) {
                    $arrayGroup = array(
                        'name' => $this->input->post('name'),
                        'description' => $this->input->post('description'),
                        'session_id' => get_session_id(),
                        'branch_id' => $this->application_model->get_branch_id(),
                    );
                    $this->db->insert('fee_groups', $arrayGroup);
                    $groupID = $this->db->insert_id();
                    foreach ($elems as $key => $row) {
                        if (isset($row['fees_type_id'])) {
                            $arrayData = array(
                                'fee_groups_id' => $groupID,
                                'fee_type_id' => $row['fees_type_id'],
                                'due_date' => date("Y-m-d", strtotime($row['due_date'])),
                                'amount' => $row['amount'],
                            );
                            $this->db->where(array('fee_groups_id' => $groupID, 'fee_type_id' => $row['fees_type_id']));
                            $query = $this->db->get("fee_groups_details");
                            if ($query->num_rows() == 0) {
                                $this->db->insert('fee_groups_details', $arrayData);
                            }
                        }
                    }
                    set_alert('success', translate('information_has_been_saved_successfully'));
                } else {
                    set_alert('error', 'At least one type has to be selected.');
                }
                $url = base_url('fees/group');
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }
        $this->data['branch_id'] = $branch_id;
        $this->data['categorylist'] = $this->app_lib->getTable('fee_groups', array('t.session_id' => get_session_id(), 't.system' => 0));
        $this->data['title'] = translate('fees_group');
        $this->data['sub_page'] = 'fees/group';
        $this->data['main_menu'] = 'fees';
        $this->load->view('layout/index', $this->data);
    }

    public function group_edit($id = '')
    {
        if (!get_permission('fees_group', 'is_edit')) {
            access_denied();
        }
        if ($_POST) {
            $this->form_validation->set_rules('name', translate('group_name'), 'trim|required');
            $elems = $this->input->post('elem');
            $sel = array();
            if (count($elems)) {
                foreach ($elems as $key => $value) {
                    if (isset($value['fees_type_id'])) {
                        $sel[] = $value['fees_type_id'];
                        $this->form_validation->set_rules('elem[' . $key . '][due_date]', translate('due_date'), 'trim|required');
                        $this->form_validation->set_rules('elem[' . $key . '][amount]', translate('amount'), 'trim|required|greater_than[0]');
                    }
                }
            }
            if ($this->form_validation->run() !== false) {
                if (count($sel)) {
                    $groupID = $this->input->post('group_id');
                    $arrayGroup = array(
                        'name' => $this->input->post('name'),
                        'description' => $this->input->post('description'),
                    );
                    $this->db->where('id', $groupID);
                    $this->db->update('fee_groups', $arrayGroup);
                    foreach ($elems as $key => $row) {
                        if (isset($row['fees_type_id'])) {
                            $arrayData = array(
                                'fee_groups_id' => $groupID,
                                'fee_type_id' => $row['fees_type_id'],
                                'due_date' => date("Y-m-d", strtotime($row['due_date'])),
                                'amount' => $row['amount'],
                            );
                            $this->db->where(array('fee_groups_id' => $groupID, 'fee_type_id' => $row['fees_type_id']));
                            $query = $this->db->get("fee_groups_details");
                            if ($query->num_rows() == 0) {
                                $this->db->insert('fee_groups_details', $arrayData);
                            } else {
                                $this->db->where('id', $query->row()->id);
                                $this->db->update('fee_groups_details', $arrayData);
                            }
                        }
                    }
                    $this->db->where_not_in('fee_type_id', $sel);
                    $this->db->where('fee_groups_id', $groupID);
                    $this->db->delete('fee_groups_details');
                    set_alert('success', translate('information_has_been_updated_successfully'));
                } else {
                    set_alert('error', 'At least one type has to be selected.');
                }
                $url = base_url('fees/group');
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }
        $this->data['group'] = $this->app_lib->getTable('fee_groups', array('t.id' => $id), true);
        $this->data['title'] = translate('fees_group');
        $this->data['sub_page'] = 'fees/group_edit';
        $this->data['main_menu'] = 'fees';
        $this->load->view('layout/index', $this->data);
    }

    public function group_delete($id)
    {
        if (get_permission('fees_group', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('id', $id);
            $this->db->delete('fee_groups');
            if ($this->db->affected_rows() > 0) {
                $this->db->where('fee_groups_id', $id);
                $this->db->delete('fee_groups_details');
            }
        }
    }

    /* fees type form validation rules */
    protected function fine_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $this->form_validation->set_rules('group_id', translate('group_name'), 'trim|required');
        $this->form_validation->set_rules('fine_type_id', translate('fees_type'), 'trim|required|callback_check_feetype');
        $this->form_validation->set_rules('fine_type', translate('fine_type'), 'trim|required');
        $this->form_validation->set_rules('fine_value', translate('fine') . " " . translate('value'), 'trim|required|numeric|greater_than[0]');
        $this->form_validation->set_rules('fee_frequency', translate('late_fee_frequency'), 'trim|required');
    }

    public function fine_setup()
    {
        if (!get_permission('fees_fine_setup', 'is_view')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        if ($_POST) {
            if (!get_permission('fees_fine_setup', 'is_add')) {
                ajax_access_denied();
            }
            $this->fine_validation();
            if ($this->form_validation->run() !== false) {
                $insertData = array(
                    'group_id' => $this->input->post('group_id'),
                    'type_id' => $this->input->post('fine_type_id'),
                    'fine_value' => $this->input->post('fine_value'),
                    'fine_type' => $this->input->post('fine_type'),
                    'fee_frequency' => $this->input->post('fee_frequency'),
                    'branch_id' => $branchID,
                    'session_id' => get_session_id(),
                );
                $this->db->insert('fee_fine', $insertData);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = array('status' => 'success');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }
        $this->data['finelist'] = $this->app_lib->getTable('fee_fine');
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('fine_setup');
        $this->data['main_menu'] = 'fees';
        $this->data['sub_page'] = 'fees/fine_setup';
        $this->load->view('layout/index', $this->data);
    }

    public function fine_setup_edit($id = '')
    {
        if (!get_permission('fees_fine_setup', 'is_edit')) {
            access_denied();
        }

        if ($_POST) {
            $branchID = $this->application_model->get_branch_id();
            $this->fine_validation();
            if ($this->form_validation->run() !== false) {
                $insertData = array(
                    'group_id' => $this->input->post('group_id'),
                    'type_id' => $this->input->post('fine_type_id'),
                    'fine_value' => $this->input->post('fine_value'),
                    'fine_type' => $this->input->post('fine_type'),
                    'fee_frequency' => $this->input->post('fee_frequency'),
                    'branch_id' => $branchID,
                    'session_id' => get_session_id(),
                );
                $this->db->where('id', $id);
                $this->db->update('fee_fine', $insertData);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $url = base_url('fees/fine_setup');
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }
        $this->data['fine'] = $this->app_lib->getTable('fee_fine', array('t.id' => $id), true);
        $this->data['title'] = translate('fine_setup');
        $this->data['sub_page'] = 'fees/fine_setup_edit';
        $this->data['main_menu'] = 'fees';
        $this->load->view('layout/index', $this->data);
    }

    public function check_feetype($id)
    {
        $groupID = $this->input->post('group_id');
        $fineID = $this->input->post('fine_id');
        if (!empty($fineID)) {
            $this->db->where_not_in('id', $fineID);
        }
        $this->db->where('group_id', $groupID);
        $this->db->where('type_id', $id);
        $query = $this->db->get('fee_fine');
        if ($query->num_rows() > 0) {
            $this->form_validation->set_message("check_feetype", translate('already_taken'));
            return false;
        } else {
            return true;
        }
    }

    public function fine_delete($id)
    {
        if (get_permission('fees_fine_setup', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('id', $id);
            $this->db->delete('fee_fine');
        }
    }

    public function allocation()
    {
        if (!get_permission('fees_allocation', 'is_add')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        if (isset($_POST['search'])) {
            $this->data['class_id'] = $this->input->post('class_id');
            $this->data['section_id'] = $this->input->post('section_id');
            $this->data['fee_group_id'] = $this->input->post('fee_group_id');
            $this->data['branch_id'] = $branchID;
            $this->data['studentlist'] = $this->fees_model->getStudentAllocationList($this->data['class_id'], $this->data['section_id'], $this->data['fee_group_id'], $branchID);
        }
        if (isset($_POST['save'])) {
            $student_array = $this->input->post('stu_operations');
            $student_ids = $this->input->post('student_ids');
            $student_sel_array = isset($student_array) ? $student_array : array();
            $delStudent = array_diff($student_ids, $student_sel_array);
            $fee_groupID = $this->input->post('fee_group_id');
            foreach ($student_array as $key => $value) {
                $arrayData = array(
                    'student_id' => $value,
                    'group_id' => $fee_groupID,
                    'session_id' => get_session_id(),
                    'branch_id' => $branchID,
                );
                $this->db->where($arrayData);
                $q = $this->db->get('fee_allocation');
                if ($q->num_rows() == 0) {
                    $this->db->insert('fee_allocation', $arrayData);
                }
            }
            if (!empty($delStudent)) {
                $this->db->where_in('student_id', $delStudent);
                $this->db->where('group_id', $fee_groupID);
                $this->db->where('session_id', get_session_id());
                $this->db->delete('fee_allocation');
            }
            set_alert('success', translate('information_has_been_saved_successfully'));
            redirect(base_url('fees/allocation'));
        }
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('fees_allocation');
        $this->data['sub_page'] = 'fees/allocation';
        $this->data['main_menu'] = 'fees';
        $this->load->view('layout/index', $this->data);
    }

    public function allocation_save()
    {
        if (!get_permission('fees_allocation', 'is_add')) {
            access_denied();
        }
        if ($_POST) {
            $branchID = $this->application_model->get_branch_id();
            $student_array = $this->input->post('stu_operations');
            $student_ids = $this->input->post('student_ids');
            $student_sel_array = isset($student_array) ? $student_array : array();
            $delStudent = array_diff($student_ids, $student_sel_array);
            $fee_groupID = $this->input->post('fee_group_id');
            if (!empty($student_sel_array)) {
                foreach ($student_array as $key => $value) {
                    $arrayData = array(
                        'student_id' => $value,
                        'group_id' => $fee_groupID,
                        'session_id' => get_session_id(),
                        'branch_id' => $branchID,
                    );
                    $this->db->where($arrayData);
                    $q = $this->db->get('fee_allocation');
                    if ($q->num_rows() == 0) {
                        $this->db->insert('fee_allocation', $arrayData);
                    }
                }
            }
            if (!empty($delStudent)) {
                $this->db->where_in('student_id', $delStudent);
                $this->db->where('group_id', $fee_groupID);
                $this->db->where('session_id', get_session_id());
                $this->db->delete('fee_allocation');
            }

            $message = translate('information_has_been_saved_successfully');
            $array = array('status' => 'success', 'message' => $message);
            echo json_encode($array);
        }
    }

    /* student fees invoice search user interface */
    public function invoice_list()
    {
        if (!get_permission('invoice', 'is_view')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        if (empty($branchID) && !is_superadmin_loggedin()) {
            $branchID = get_loggedin_branch_id();
        }

        if ($_POST && $this->input->is_ajax_request()) {
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required');
            }
            $this->form_validation->set_rules('class_id', translate('class'), 'trim');
            $this->form_validation->set_rules('section_id', translate('section'), 'trim');
            if ($this->form_validation->run() == true) {
                $export_title = get_type_name_by_id('branch', $branchID) . ' - ' . translate('invoice_list');
                $array = array('status' => 'success', 'export_title' => $export_title, 'error' => '');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('payments_history');
        $this->data['sub_page'] = 'fees/invoice_list';
        $this->data['main_menu'] = 'fees';
        $this->load->view('layout/index', $this->data);
    }

    public function getInvoiceListDT()
    {
        if ($_POST) {
            if (get_permission('invoice', 'is_view')) {
                $submit_btn = $this->input->post('submit_btn');
                if (empty($submit_btn)) {
                    $json_data = array(
                        "draw"                => intval(0),
                        "recordsTotal"        => intval(0),
                        "recordsFiltered"     => intval(0),
                        "data"                => [],
                    );
                    echo json_encode($json_data);
                } else {
                    echo $this->fees_model->getInvoiceList();
                }
            }
        }
    }

    public function invoice_delete($enrollID = '')
    {
        if (!get_permission('invoice', 'is_delete')) {
            access_denied();
        }

        if (!is_superadmin_loggedin()) {
            $this->db->where('branch_id', get_loggedin_branch_id());
        }
        $this->db->where('student_id', $enrollID);
        $result = $this->db->get('fee_allocation')->result_array();
        foreach ($result as $key => $value) {
            $this->db->where('allocation_id', $value['id']);
            $this->db->delete('fee_payment_history');
        }

        if (!is_superadmin_loggedin()) {
            $this->db->where('branch_id', get_loggedin_branch_id());
        }
        $this->db->where('student_id', $enrollID);
        $this->db->delete('fee_allocation');
    }

    /* invoice user interface with information are controlled here */
    public function invoice($enrollID = '')
    {
        if (!get_permission('invoice', 'is_view')) {
            access_denied();
        }
        $basic = $this->fees_model->getInvoiceBasic($enrollID);
        if (empty($basic))
            redirect(base_url('dashboard'));

        if (moduleIsEnabled('transport')) {
            $this->data['transport_fees'] = $this->fees_model->getStudentTransportFees($enrollID, $basic['stoppage_point_id']);
        }
        $this->data['invoice'] = $this->fees_model->getInvoiceStatus($enrollID);
        $this->data['basic'] = $basic;
        $this->data['title'] = translate('invoice_history');
        $this->data['main_menu'] = 'fees';
        $this->data['sub_page'] = 'fees/collect';
        $this->load->view('layout/index', $this->data);
    }

    public function invoicePrint()
    {
        if (!get_permission('invoice', 'is_view')) {
            access_denied();
        }
        if ($_POST) {
            $this->data['student_array'] = $this->input->post('student_id');
            echo $this->load->view('fees/invoicePrint', $this->data, true);
        }
    }

    public function invoicePDFdownload()
    {
        if (!get_permission('invoice', 'is_view')) {
            access_denied();
        }
        if ($_POST) {
            $this->data['student_array'] = $this->input->post('student_id');
            $html = $this->load->view('fees/invoicePDFdownload', $this->data, true);

            $this->load->library('html2pdf');
            $this->html2pdf->mpdf->WriteHTML(file_get_contents(base_url('assets/vendor/bootstrap/css/bootstrap.min.css')), 1);
            $this->html2pdf->mpdf->WriteHTML(file_get_contents(base_url('assets/css/custom-style.css')), 1);
            $this->html2pdf->mpdf->WriteHTML(file_get_contents(base_url('assets/css/ramom.css')), 1);
            $this->html2pdf->mpdf->WriteHTML($html);
            $this->html2pdf->mpdf->SetDisplayMode('fullpage');
            $this->html2pdf->mpdf->baseScript        = 1;
            $this->html2pdf->mpdf->autoScriptToLang  = true;
            $this->html2pdf->mpdf->autoLangToFont    = true;
            header("Content-Type: application/pdf");
            echo $this->html2pdf->mpdf->Output('', "S");
        }
    }

    public function pdf_sendByemail()
    {
        if (!get_permission('invoice', 'is_view')) {
            access_denied();
        }
        if ($_POST) {
            $this->data['student_array'] = [$this->input->post('enrollID')];
            $html = $this->load->view('fees/invoicePDFdownload', $this->data, true);
            $this->load->library('html2pdf');
            $this->html2pdf->mpdf->WriteHTML(file_get_contents(base_url('assets/vendor/bootstrap/css/bootstrap.min.css')), 1);
            $this->html2pdf->mpdf->WriteHTML(file_get_contents(base_url('assets/css/custom-style.css')), 1);
            $this->html2pdf->mpdf->WriteHTML(file_get_contents(base_url('assets/css/ramom.css')), 1);
            $this->html2pdf->mpdf->WriteHTML($html);
            $this->html2pdf->mpdf->SetDisplayMode('fullpage');
            $this->html2pdf->mpdf->autoScriptToLang  = true;
            $this->html2pdf->mpdf->baseScript        = 1;
            $this->html2pdf->mpdf->autoLangToFont    = true;

            $file = $this->html2pdf->mpdf->Output(time() . '.pdf', "S");
            $data['file'] = $file;
            $data['enroll_id'] = $this->input->post('enrollID');
            $response = $this->email_model->emailPDF_Fee_invoice($data);
            if ($response == true) {
                $array = array('status' => 'success', 'message' => translate('mail_sent_successfully'));
            } else {
                $array = array('status' => 'error', 'message' => translate('something_went_wrong'));

            }
            echo json_encode($array);
        }
    }

    public function due_invoice()
    {
        if (!get_permission('due_invoice', 'is_view')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        if ($_POST) {
                if (is_superadmin_loggedin()) {
                    $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required');
                }
                $this->form_validation->set_rules('class_id', translate('class'), 'trim|required');
                $this->form_validation->set_rules('section_id', translate('section'), 'trim|required');
                $this->form_validation->set_rules('fees_type', translate('fees_type'), 'trim|required');
                if ($this->form_validation->run() == true) {
                    $export_title = get_type_name_by_id('branch', $branchID) . ' - ' . translate('due_invoice') . " " . translate('list');
                    $array = array('status' => 'success', 'export_title' => $export_title,'error' => '');
                } else {
                    $error = $this->form_validation->error_array();
                    $array = array('status' => 'fail','error' => $error);
                }
                echo json_encode($array);
                exit();
        }
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('payments_history');
        $this->data['sub_page'] = 'fees/due_invoice';
        $this->data['main_menu'] = 'fees';
        $this->load->view('layout/index', $this->data);
    }

    public function getDueInvoiceListDT()
    {
        if ($_POST) {
            if (get_permission('due_invoice', 'is_view')) {
                $branchID = $this->application_model->get_branch_id();
                $class_id = $this->input->post('class_id');
                $section_id = $this->input->post('section_id');
                $submit_btn = $this->input->post('submit_btn');

                if (empty($submit_btn)) {
                    $json_data = array(
                        "draw"                => intval(0),
                        "recordsTotal"        => intval(0),
                        "recordsFiltered"     => intval(0),
                        "data"                => [],
                    );
                    echo json_encode($json_data);
                } else {
                    $feegroup = explode("|", $this->input->post('fees_type'));
                    $feegroup_id = $feegroup[0];
                    $fee_feetype_id = $feegroup[1];

                    $results = $this->fees_model->getDueInvoiceDT_list($class_id, $section_id, $feegroup_id, $fee_feetype_id);
                    $records = array();
                    $records = json_decode($results);
                    $dt_data = array();
                    foreach ($records->data as $key => $record) {

                        $paid = $record->total_amount + $record->total_discount;
                        $prev_due = empty($record->prev_due) ? 0 : $record->prev_due;
                        if ((float)($record->full_amount + $prev_due) <= (float)$paid) {

                        } else {
                            // actions btn
                            $actions = "";
                            if (get_permission('collect_fees', 'is_add')) {
                                $actions .= '<a href="' . base_url('fees/invoice/' . $record->enroll_id) . '" class="btn btn-default btn-circle"><i class="far fa-arrow-alt-circle-right"></i> ' . translate('collect') . '</a>';
                            }
                            if (get_permission('invoice', 'is_delete')) {
                                $actions .=  btn_delete('fees/invoice_delete/' . $record->enroll_id);
                            }

                            // getting fees group list
                            $feegroup = $this->fees_model->getfeeGroup($record->enroll_id);
                            $groupList = '';
                            foreach ($feegroup as $key => $value) {
                                $groupList .= "- " . $value['name'] . "<br>";
                            }

                            // dt-data array 
                            $row   = array();
                            $row[] = '<div class="checked-area"><div class="checkbox-replace"><label class="i-checks"><input type="checkbox" name="student_id[]" value="' . $record->enroll_id  . '"><i></i></label></div></div>';
                            $row[] = $record->first_name . ' ' . $record->last_name;
                            $row[] = $record->register_no;
                            $row[] = $record->roll;
                            $row[] = $record->mobileno;
                            $row[] = $groupList;
                            $row[] = _d($record->due_date);
                            $row[] = currencyFormat($record->full_amount);
                            $row[] = currencyFormat($record->total_amount);
                            $row[] = currencyFormat($record->total_discount);
                            $row[] = currencyFormat($record->full_amount - $paid);
                            $row[] = $actions;
                            $dt_data[] = $row;
                        }
                    }
                    $json_data = array(
                        "draw"                => intval($records->draw),
                        "recordsTotal"        => intval($records->recordsTotal),
                        "recordsFiltered"     => intval($records->recordsFiltered),
                        "data"                => $dt_data,
                    );
                    echo json_encode($json_data);
                }
            }
        }
    }

    public function fee_add()
    {
        if (!get_permission('collect_fees', 'is_add')) {
            ajax_access_denied();
        }
        $this->form_validation->set_rules('fees_type', translate('fees_type'), 'trim|required');
        $this->form_validation->set_rules('date', translate('date'), 'trim|required');
        $this->form_validation->set_rules('amount', translate('amount'), array('trim', 'required', 'numeric', 'greater_than[0]', array('deposit_verify', array($this->fees_model, 'depositAmountVerify'))));
        $this->form_validation->set_rules('discount_amount', translate('discount'), array('trim', 'numeric', array('deposit_verify', array($this->fees_model, 'depositAmountVerify'))));
        $this->form_validation->set_rules('pay_via', translate('payment_method'), 'trim|required');
        if ($this->form_validation->run() !== false) {
            $feesType = explode("|", $this->input->post('fees_type'));
            $amount = $this->input->post('amount');
            $fineAmount = $this->input->post('fine_amount');
            $discountAmount = $this->input->post('discount_amount');
            $date = $this->input->post('date');
            $payVia = $this->input->post('pay_via');
            $arrayFees = array(
                'allocation_id' => $feesType[0],
                'type_id' => $feesType[1],
                'collect_by' => get_loggedin_user_id(),
                'amount' => ($amount - $discountAmount),
                'discount' => $discountAmount,
                'fine' => $fineAmount,
                'pay_via' => $payVia,
                'remarks' => $this->input->post('remarks'),
                'date' => $date,
            );
            // transport fees data processing
            if ($feesType[0] == 'transport') {
                $arrayFees['allocation_id'] = NULL;
                $arrayFees['type_id'] = NULL;
                $arrayFees['transport_fee_details_id'] = $feesType[1];
            }
            $this->db->insert('fee_payment_history', $arrayFees);
            $payment_historyID = $this->db->insert_id();

            // transaction voucher save function
            if (isset($_POST['account_id'])) {
                $arrayTransaction = array(
                    'account_id' => $this->input->post('account_id'),
                    'amount' => ($amount + $fineAmount) - $discountAmount,
                    'date' => $date,
                );
                $this->fees_model->saveTransaction($arrayTransaction, $payment_historyID);
            }

            // send payment confirmation sms
            if (isset($_POST['guardian_sms'])) {
                $arrayData = array(
                    'student_id' => $this->input->post('student_id'),
                    'amount' => ($amount + $fineAmount) - $discountAmount,
                    'paid_date' => _d($date),
                );
                $this->sms_model->send_sms($arrayData, 2);
            }
            set_alert('success', translate('information_has_been_saved_successfully'));
            $array = array('status' => 'success');
        } else {
            $error = $this->form_validation->error_array();
            $array = array('status' => 'fail', 'url' => '', 'error' => $error);
        }
        echo json_encode($array);
    }

    public function getBalanceByType()
    {
        $input = $this->input->post('typeID');
        if (empty($input)) {
            $balance = 0;
            $fine = 0;
        } else {
            $feesType = explode("|", $input);
            if ($feesType[0] == 'transport') {
                $fine = $this->fees_model->transportFeeFineCalculation($feesType[1], $feesType[2]);
                $b = $this->fees_model->getTransportBalance($feesType[1]);
                $balance = $b['balance'];
                $fine = abs($fine - $b['fine']);
            } else {
                $fine = $this->fees_model->feeFineCalculation($feesType[0], $feesType[1]);
                $b = $this->fees_model->getBalance($feesType[0], $feesType[1]);
                $balance = $b['balance'];
                $fine = abs($fine - $b['fine']);
            }
        }
        echo json_encode(array('balance' => $balance, 'fine' => $fine));
    }

    public function getTypeByBranch()
    {
        $html = "";
        $branchID = $this->application_model->get_branch_id();
        $typeID = (isset($_POST['type_id']) ? $_POST['type_id'] : 0);
        if (!empty($branchID)) {
            $this->db->where('session_id', get_session_id());
            $this->db->where('branch_id', $branchID);
            $result = $this->db->get('fee_groups')->result_array();

            if (moduleIsEnabled('transport')) {
                $this->db->where('branch_id', $branchID);
                $this->db->where('session_id', get_session_id());
                $this->db->order_by('month', 'asc');
                $transport_results = $this->db->get('transport_fee_fine')->result();
            }
            if (count($result)) {
                $html .= "<option value=''>" . translate('select') . "</option>";
                foreach ($result as $row) {
                    $html .= '<optgroup label="' . $row['name'] . '">';
                    $this->db->where('fee_groups_id', $row['id']);
                    $resultdetails = $this->db->get('fee_groups_details')->result_array();
                    foreach ($resultdetails as $t) {
                        $sel = ($t['fee_groups_id'] . "|" . $t['fee_type_id'] == $typeID ? ' selected ' : '');
                        $html .= '<option value="' . $t['fee_groups_id'] . "|" . $t['fee_type_id'] . '"' . $sel . '>' . get_type_name_by_id('fees_type', $t['fee_type_id']) . '</option>';
                    }
                    $html .= '</optgroup>';
                }
                if (!empty($transport_results)) {
                    $getMonths = $this->app_lib->getMonthslist();
                    $html .= '<optgroup label="' . translate('transport_fees') . '">';
                    foreach ($transport_results as $t_key => $t_value) {
                        $sel = ("transport|" . $t_value->id == $typeID ? ' selected ' : '');
                        $html .= '<option value="' . "transport|" . $t_value->id . '"' . $sel . '>' . translate('transport_fees') ." - ". $getMonths[$t_value->month] . '</option>';
                    }
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_branch_first') . '</option>';
        }
        echo $html;
    }

    public function getGroupByBranch()
    {
        $html = "";
        $branch_id = $this->application_model->get_branch_id();
        if (!empty($branch_id)) {
            $result = $this->db->select('id,name')
                ->where(array('branch_id' => $branch_id, 'session_id' => get_session_id(), 'system' => 0))
                ->get('fee_groups')->result_array();
            if (count($result)) {
                $html .= "<option value=''>" . translate('select') . "</option>";
                foreach ($result as $row) {
                    $html .= '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_branch_first') . '</option>';
        }
        echo $html;
    }

    public function getTypeByGroup()
    {
        $html = "";
        $groupID = $this->input->post('group_id');
        $typeID = (isset($_POST['type_id']) ? $_POST['type_id'] : 0);
        if (!empty($groupID)) {
            $this->db->select('t.id,t.name');
            $this->db->from('fee_groups_details as gd');
            $this->db->join('fees_type as t', 't.id = gd.fee_type_id', 'left');
            $this->db->where('gd.fee_groups_id', $groupID);
            $result = $this->db->get()->result_array();
            if (count($result)) {
                $html .= "<option value=''>" . translate('select') . "</option>";
                foreach ($result as $row) {
                    $sel = ($row['id'] == $typeID ? 'selected' : '');
                    $html .= '<option value="' . $row['id'] . '" ' . $sel . '>' . $row['name'] . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('first_select_the_group') . '</option>';
        }
        echo $html;
    }

    protected function reminder_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $this->form_validation->set_rules('frequency', translate('frequency'), 'trim|required');
        $this->form_validation->set_rules('days', translate('days'), 'trim|required|numeric');
        $this->form_validation->set_rules('message', translate('message'), 'trim|required');
    }

    public function reminder()
    {
        if (!get_permission('fees_reminder', 'is_view')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        if ($_POST) {
            if (!get_permission('fees_reminder', 'is_add')) {
                ajax_access_denied();
            }
            $this->reminder_validation();
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                $post['branch_id'] = $branchID;
                $this->fees_model->reminderSave($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = array('status' => 'success');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }
        $this->data['branch_id'] = $branchID;
        $this->data['reminderlist'] = $this->app_lib->getTable('fees_reminder');
        $this->data['title'] = translate('fees_reminder');
        $this->data['main_menu'] = 'fees';
        $this->data['sub_page'] = 'fees/reminder';
        $this->load->view('layout/index', $this->data);
    }

    public function edit_reminder($id = '')
    {
        if (!get_permission('fees_reminder', 'is_edit')) {
            ajax_access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        if ($_POST) {
            $this->reminder_validation();
            if ($this->form_validation->run() !== false) {
                $post = $this->input->post();
                $post['branch_id'] = $branchID;
                $this->fees_model->reminderSave($post);
                $url = base_url('fees/reminder');
                set_alert('success', translate('information_has_been_updated_successfully'));
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }
        $this->data['reminder'] = $this->app_lib->getTable('fees_reminder', array('t.id' => $id), true);
        $this->data['title'] = translate('fees_reminder');
        $this->data['main_menu'] = 'fees';
        $this->data['sub_page'] = 'fees/edit_reminder';
        $this->load->view('layout/index', $this->data);
    }

    public function reminder_delete($id = '')
    {
        if (get_permission('fees_reminder', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('id', $id);
            $this->db->delete('fees_reminder');
        }
    }

    public function due_report()
    {
        if (!get_permission('fees_reports', 'is_view')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        if ($this->input->post('search')) {
            $this->data['class_id'] = $this->input->post('class_id');
            $this->data['section_id'] = $this->input->post('section_id');
            $this->data['invoicelist'] = $this->fees_model->getDueReport($this->data['class_id'], $this->data['section_id']);
        }
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('due_fees_report');
        $this->data['sub_page'] = 'fees/due_report';
        $this->data['main_menu'] = 'fees_repots';
        $this->load->view('layout/index', $this->data);
    }

    public function payment_history()
    {
        if (!get_permission('fees_reports', 'is_view')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        if ($this->input->post('search')) {
            $classID = $this->input->post('class_id');
            $paymentVia = $this->input->post('payment_via');
            $daterange = explode(' - ', $this->input->post('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->data['invoicelist'] = $this->fees_model->getStuPaymentHistory($classID, "", $paymentVia, $start, $end, $branchID);
        }
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('fees_payment_history');
        $this->data['sub_page'] = 'fees/payment_history';
        $this->data['main_menu'] = 'fees_repots';
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/daterangepicker/daterangepicker.css',
            ),
            'js' => array(
                'vendor/moment/moment.js',
                'vendor/daterangepicker/daterangepicker.js',
            ),
        );
        $this->load->view('layout/index', $this->data);
    }

    public function student_fees_report()
    {
        if (!get_permission('fees_reports', 'is_view')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        if ($this->input->post('search')) {
            $classID = $this->input->post('class_id');
            $sectionID = $this->input->post('section_id');
            $enroll_id = $this->input->post('enroll_id');
            $typeID = $this->input->post('fees_type');
            $daterange = explode(' - ', $this->input->post('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->data['invoicelist'] = $this->fees_model->getStuPaymentReport($classID, $sectionID, $enroll_id, $typeID, $start, $end, $branchID);
        }
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student_fees_report');
        $this->data['sub_page'] = 'fees/student_fees_report';
        $this->data['main_menu'] = 'fees_repots';
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/daterangepicker/daterangepicker.css',
            ),
            'js' => array(
                'vendor/moment/moment.js',
                'vendor/daterangepicker/daterangepicker.js',
            ),
        );
        $this->load->view('layout/index', $this->data);
    }

    public function fine_report()
    {
        if (!get_permission('fees_reports', 'is_view')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        if ($this->input->post('search')) {
            $classID = $this->input->post('class_id');
            $sectionID = $this->input->post('section_id');
            $paymentVia = $this->input->post('payment_via');
            $daterange = explode(' - ', $this->input->post('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->data['invoicelist'] = $this->fees_model->getStuPaymentHistory($classID, $sectionID, $paymentVia, $start, $end, $branchID, true);
        }
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('fees_fine_reports');
        $this->data['sub_page'] = 'fees/fine_report';
        $this->data['main_menu'] = 'fees_repots';
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/daterangepicker/daterangepicker.css',
            ),
            'js' => array(
                'vendor/moment/moment.js',
                'vendor/daterangepicker/daterangepicker.js',
            ),
        );
        $this->load->view('layout/index', $this->data);
    }

    public function paymentRevert()
    {
        if (!get_permission('fees_revert', 'is_delete')) {
            $array = array('status' => 'error', 'message' => translate('access_denied'));
            echo json_encode($array);
            exit();
        }
        $array = array('status' => 'success', 'message' => translate('information_deleted'));
        $ids = $this->input->post('id');
        foreach ($ids as $key => $value) {

            $feeDetails = $this->db->select('id,amount,fine')->where('id', $value)->get('fee_payment_history')->row();
            if (!empty($feeDetails)) {

                $amount = ($feeDetails->amount + $feeDetails->fine);

                $sql = "SELECT `transactions`.`account_id`, `transactions_links_details`.`transactions_id` FROM `transactions_links_details` INNER JOIN `transactions` ON `transactions`.`id` = `transactions_links_details`.`transactions_id` WHERE `transactions_links_details`.`payment_id` = " . $this->db->escape($value);
                $transactionsDetails = $this->db->query($sql)->row();
                if (!empty($transactionsDetails)) {

                    $sql = "UPDATE `transactions` SET `amount` = `amount` - $amount, `cr` = `cr` - $amount, `bal` = `bal` - $amount WHERE `id` = " . $this->db->escape($transactionsDetails->transactions_id);
                    $this->db->query($sql);

                    $sql = "UPDATE `accounts` SET `balance` = `balance` - $amount WHERE `id` = " . $this->db->escape($transactionsDetails->account_id);
                    $this->db->query($sql);

                    /*$this->db->set('amount', 'amount+' . $amount, false);
                    $this->db->set('cr', 'cr-' . $amount, false);
                    $this->db->set('bal', 'bal-' . $amount, false);
                    $this->db->where('id', $transactionsDetails->transactions_id);
                    $this->db->update('transactions');

                    $this->db->set('balance', 'balance-' . $amount, false);
                    $this->db->where('id', $transactionsDetails->account_id);
                    $this->db->update('accounts');*/
                }
                $this->db->where('id', $value);
                $this->db->delete('fee_payment_history');
            }
        }
        echo json_encode($array);
    }

    public function fee_fully_paid()
    {
        if (!get_permission('collect_fees', 'is_add')) {
            ajax_access_denied();
        }
        $this->form_validation->set_rules('date', translate('date'), 'trim|required');
        $this->form_validation->set_rules('pay_via', translate('payment_method'), 'trim|required');
        if ($this->form_validation->run() !== false) {
            $date = $this->input->post('date');
            $payVia = $this->input->post('pay_via');
            $invoiceID = $this->input->post('invoice_id');
            $basic = $this->fees_model->getInvoiceBasic($invoiceID);
            if (empty($basic))
                ajax_access_denied();

            $allocations = $this->fees_model->getInvoiceDetails($invoiceID);
            $totalBalance = 0;
            $totalFine = 0;

            foreach ($allocations as $row) {
                $fine = $this->fees_model->feeFineCalculation($row['allocation_id'], $row['fee_type_id']);
                $b = $this->fees_model->getBalance($row['allocation_id'], $row['fee_type_id']);
                $fine = abs($fine - $b['fine']);
                if ($b['balance'] != 0) {
                    $totalBalance += $b['balance'];
                    $totalFine += $fine;
                    $arrayFees = array(
                        'allocation_id' => $row['allocation_id'],
                        'type_id' => $row['fee_type_id'],
                        'collect_by' => get_loggedin_user_id(),
                        'amount' => $b['balance'],
                        'discount' => 0,
                        'fine' => $fine,
                        'pay_via' => $payVia,
                        'remarks' => $this->input->post('remarks'),
                        'date' => $date,
                    );
                    $this->db->insert('fee_payment_history', $arrayFees);
                }
            }

            if (moduleIsEnabled('transport')) {
                $transport_fees = $this->fees_model->getStudentTransportFees($invoiceID, $basic['stoppage_point_id']);
                foreach ($transport_fees as $key => $value) {
                    $fine = $this->fees_model->transportFeeFineCalculation($value->id);
                    $b = $this->fees_model->getTransportBalance($value->id);
                    $balance = $b['balance'];
                    $fine = abs($fine - $b['fine']);
                
                    if ($b['balance'] != 0) {
                        $totalBalance += $b['balance'];
                        $totalFine += $fine;
                        $arrayFees = array(
                            'allocation_id' => NULL,
                            'type_id' => NULL,
                            'transport_fee_details_id' => $value->id,
                            'collect_by' => get_loggedin_user_id(),
                            'amount' => $b['balance'],
                            'discount' => 0,
                            'fine' => $fine,
                            'pay_via' => $payVia,
                            'remarks' => $this->input->post('remarks'),
                            'date' => $date,
                        );
                        $this->db->insert('fee_payment_history', $arrayFees);
                    }
                }
            }

            // transaction voucher save function
            if (isset($_POST['account_id'])) {
                $arrayTransaction = array(
                    'account_id' => $this->input->post('account_id'),
                    'amount' => ($totalBalance + $totalFine),
                    'date' => $date,
                );
                $this->fees_model->saveTransaction($arrayTransaction);
            }

            // send payment confirmation sms
            if (isset($_POST['guardian_sms'])) {
                $arrayData = array(
                    'student_id' => $this->input->post('student_id'),
                    'amount' => ($totalBalance + $totalFine),
                    'paid_date' => $date,
                );
                $this->sms_model->send_sms($arrayData, 2);
            }
            set_alert('success', translate('information_has_been_saved_successfully'));
            $array = array('status' => 'success');
        } else {
            $error = $this->form_validation->error_array();
            $array = array('status' => 'fail', 'url' => '', 'error' => $error);
        }
        echo json_encode($array);
    }

    public function printFeesPaymentHistory()
    {
        if ($_POST) {
            $record = $this->input->post('data');
            $record_array = json_decode($record, true);
            $this->db->where_in('id', array_column($record_array, 'payment_id'));
            $paymentHistory = $this->db->select("sum(amount) as total_amount,sum(discount) as total_discount,sum(fine) as total_fine")->get('fee_payment_history')->row_array();
            $this->data['total_paid'] = $paymentHistory['total_amount'];
            $this->data['total_discount'] = $paymentHistory['total_discount'];
            $this->data['total_fine'] = $paymentHistory['total_fine'];
            $this->load->view('fees/printFeesPaymentHistory', $this->data);
        }
    }

    public function printFeesInvoice()
    {
        if ($_POST) {
            $record = $this->input->post('data');
            $record_array = json_decode($record);
            $total_fine = 0;
            $total_discount = 0;
            $total_paid = 0;
            $total_balance = 0;
            $total_amount = 0;
            foreach ($record_array as $key => $value) {
                if ($value->feeType == 'general') {
                    $deposit = $this->fees_model->getStudentFeeDeposit($value->allocationID, $value->feeTypeID);
                } elseif ($value->feeType == 'transport') {
                    $deposit = $this->fees_model->getStudentTransportFeeDeposit($value->trans_fd_id);
                }
                $full_amount = $value->feeAmount;
                $type_discount = $deposit['total_discount'];
                $type_fine = $deposit['total_fine'];
                $type_amount = $deposit['total_amount'];
                $balance = $full_amount - ($type_amount + $type_discount);
                $total_discount += $type_discount;
                $total_fine += $type_fine;
                $total_paid += $type_amount;
                $total_balance += $balance;
                $total_amount += $full_amount;
            }
            $this->data['total_amount'] = $total_amount;
            $this->data['total_paid'] = $total_paid;
            $this->data['total_discount'] = $total_discount;
            $this->data['total_fine'] = $total_fine;
            $this->data['total_balance'] = $total_balance;
            $this->load->view('fees/printFeesInvoice', $this->data);
        }
    }

    public function payReceiptPrint()
    {
        if ($_POST) {
            if (!get_permission('collect_fees', 'is_add')) {
                ajax_access_denied();
            }
            $studentID = $this->input->post('student_id');
            $record = $this->input->post('data');
            $this->data['studentID'] = $studentID;
            $this->data['record'] = $record;
            $this->load->view('fees/paySlipPrint', $this->data);
        }
    }

    public function selectedFeesPay()
    {
        if (!get_permission('collect_fees', 'is_add')) {
            ajax_access_denied();
        }

        $items = $this->input->post('collect_fees');
        foreach ($items as $key => $value) {
            $this->form_validation->set_rules('collect_fees[' . $key . '][date]', translate('date'), 'trim|required');
            $this->form_validation->set_rules('collect_fees[' . $key . '][pay_via]', translate('payment_method'), 'trim|required');
            $this->form_validation->set_rules('collect_fees[' . $key . '][amount]', translate('amount'), 'trim|required|numeric|greater_than[0]');
            $this->form_validation->set_rules('collect_fees[' . $key . '][discount_amount]', translate('discount'), 'trim|numeric');
            $this->form_validation->set_rules('collect_fees[' . $key . '][fine_amount]', translate('fine'), 'trim|numeric');
            if (isset($value['account_id'])) {
                $this->form_validation->set_rules('collect_fees[' . $key . '][account_id]', translate('account'), 'trim|required');
            }

            if ($value['fee_type'] == 'general') {
                $remainAmount = $this->fees_model->getBalance($value['allocation_id'], $value['type_id']);
                if ($remainAmount['balance'] < $value['amount']) {
                    $error = array('collect_fees[' . $key . '][amount]' => 'Amount cannot be greater than the remaining.');
                    $array = array('status' => 'fail', 'error' => $error);
                    echo json_encode($array);
                    exit;
                }

                $remainAmount = $this->fees_model->getBalance($value['allocation_id'], $value['type_id']);
                if ($remainAmount['balance'] < $value['discount_amount']) {
                    $error = array('collect_fees[' . $key . '][discount_amount]' => 'Amount cannot be greater than the remaining.');
                    $array = array('status' => 'fail', 'error' => $error);
                    echo json_encode($array);
                    exit;
                }
            } elseif($value['fee_type'] == 'transport') {
                // transport fees data processing
                $remainAmount = $this->fees_model->getTransportBalance($value['trans_fd_id']);
                if ($remainAmount['balance'] < $value['amount']) {
                    $error = array('collect_fees[' . $key . '][amount]' => 'Amount cannot be greater than the remaining.');
                    $array = array('status' => 'fail', 'error' => $error);
                    echo json_encode($array);
                    exit;
                }

                $remainAmount = $this->fees_model->getTransportBalance($value['trans_fd_id']);
                if ($remainAmount['balance'] < $value['discount_amount']) {
                    $error = array('collect_fees[' . $key . '][discount_amount]' => 'Amount cannot be greater than the remaining.');
                    $array = array('status' => 'fail', 'error' => $error);
                    echo json_encode($array);
                    exit;
                }  
            }
        }

        if ($this->form_validation->run() !== false) {
            $studentID = $this->input->post('student_id');
            foreach ($items as $key => $value) {
                $amount = $value['amount'];
                $fineAmount = $value['fine_amount'];
                $discountAmount = $value['discount_amount'];
                $date = $value['date'];
                $payVia = $value['pay_via'];
                $arrayFees = array(
                    'allocation_id' => $value['allocation_id'],
                    'type_id' => $value['type_id'],
                    'collect_by' => get_loggedin_user_id(),
                    'amount' => ($amount - $discountAmount),
                    'discount' => $discountAmount,
                    'fine' => $fineAmount,
                    'pay_via' => $payVia,
                    'remarks' => $value['remarks'],
                    'date' => $date,
                );
                // transport fees data processing
                if ($value['fee_type'] == 'transport') {
                    $arrayFees['allocation_id'] = NULL;
                    $arrayFees['type_id'] = NULL;
                    $arrayFees['transport_fee_details_id'] = $value['trans_fd_id'];
                }
                $this->db->insert('fee_payment_history', $arrayFees);

                // transaction voucher save function
                if (isset($value['account_id'])) {
                    $arrayTransaction = array(
                        'account_id' => $value['account_id'],
                        'amount' => ($amount + $fineAmount) - $discountAmount,
                        'date' => $date,
                    );
                    $this->fees_model->saveTransaction($arrayTransaction);
                }
                // send payment confirmation sms
                $arrayData = array(
                    'student_id' => $studentID,
                    'amount' => ($amount + $fineAmount) - $discountAmount,
                    'paid_date' => _d($date),
                );
                $this->sms_model->send_sms($arrayData, 2);
            }
            set_alert('success', translate('information_has_been_saved_successfully'));
            $array = array('status' => 'success');
        } else {
            $error = $this->form_validation->error_array();
            $array = array('status' => 'fail', 'error' => $error);
        }
        echo json_encode($array);
    }

    public function selectedFeesCollect()
    {
        if ($_POST) {
            $record = $this->input->post('data');
            $record_array = json_decode($record);
            $this->data['student_id'] = $this->input->post('student_id');
            $this->data['branch_id'] = $this->application_model->get_branch_id();
            $this->data['record_array'] = $record_array;
            $this->load->view('fees/selectedFeesCollect', $this->data);
        }
    }

    public function fetch()
    {
        if (!get_permission('feetype', 'is_view') && !get_permission('fees_type', 'is_view')) {
            echo json_encode(array());
            return;
        }
        if (!is_superadmin_loggedin()) {
            $this->db->where('branch_id', get_loggedin_branch_id());
        }
        $result = $this->db->order_by('id', 'asc')->get('fees_type')->result_array();
        echo json_encode($result);
    }

    public function save_fee()
    {
        if (!get_permission('feetype', 'is_add') && !get_permission('fees_type', 'is_add')) {
            echo json_encode(array('status' => 'fail', 'message' => translate('access_denied')));
            return;
        }

        $branchID = is_superadmin_loggedin() ? $this->input->post('branch_id') : get_loggedin_branch_id();
        $feeName = trim($this->input->post('fee_name'));
        $categories = $this->input->post('student_category');
        $frequency = trim($this->input->post('frequency'));
        $months = $this->input->post('months');

        if (empty($branchID) || empty($feeName) || empty($categories) || empty($frequency) || empty($months)) {
            echo json_encode(array('status' => 'fail', 'message' => translate('please_fill_all_the_fields')));
            return;
        }

        $arrayData = array(
            'branch_id' => $branchID,
            'fee_name' => $feeName,
            'student_category' => json_encode(array_values((array) $categories)),
            'frequency' => $frequency,
            'month' => implode(',', array_values((array) $months)),
        );
        $this->db->insert('fees_type', $arrayData);
        echo json_encode(array('status' => 'success', 'message' => translate('information_has_been_saved_successfully')));
    }

    public function update_fee()
    {
        if (!get_permission('feetype', 'is_edit') && !get_permission('fees_type', 'is_edit')) {
            echo json_encode(array('status' => 'fail', 'message' => translate('access_denied')));
            return;
        }

        $feeID = $this->input->post('fee_id');
        $branchID = is_superadmin_loggedin() ? $this->input->post('editbranch_id') : get_loggedin_branch_id();
        $feeName = trim($this->input->post('fee_name'));
        $categories = $this->input->post('student_category');
        $frequency = trim($this->input->post('frequency'));
        $months = $this->input->post('months');

        if (empty($feeID) || empty($branchID) || empty($feeName) || empty($categories) || empty($frequency) || empty($months)) {
            echo json_encode(array('status' => 'fail', 'message' => translate('please_fill_all_the_fields')));
            return;
        }

        if (!is_superadmin_loggedin()) {
            $this->db->where('branch_id', get_loggedin_branch_id());
        }
        $this->db->where('id', $feeID);
        $this->db->update('fees_type', array(
            'branch_id' => $branchID,
            'fee_name' => $feeName,
            'student_category' => json_encode(array_values((array) $categories)),
            'frequency' => $frequency,
            'month' => implode(',', array_values((array) $months)),
        ));
        echo json_encode(array('status' => 'success', 'message' => translate('information_has_been_updated_successfully')));
    }

    public function delete_fee_type()
    {
        if (!get_permission('feetype', 'is_delete') && !get_permission('fees_type', 'is_delete')) {
            echo json_encode(array('status' => 'fail', 'message' => translate('access_denied')));
            return;
        }

        $feeID = $this->input->post('id');
        if (empty($feeID)) {
            echo json_encode(array('status' => 'fail', 'message' => translate('invalid_request')));
            return;
        }

        if (!is_superadmin_loggedin()) {
            $this->db->where('branch_id', get_loggedin_branch_id());
        }
        $this->db->where('id', $feeID);
        $this->db->delete('fees_type');
        echo json_encode(array('status' => 'success', 'message' => translate('information_has_been_deleted_successfully')));
    }

    public function fetch_paid_students()
    {
        if (!get_permission('fees_register', 'is_view')) {
            echo json_encode(array('status' => 'fail', 'data' => array(), 'message' => translate('access_denied')));
            return;
        }

        $branchID = $this->input->post('branch_id');
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }

        $fromDate = $this->input->post('from_date');
        $toDate = $this->input->post('to_date');
        $feeTypes = $this->input->post('feeTypes');

        if (empty($fromDate) || empty($toDate)) {
            echo json_encode(array('status' => 'fail', 'data' => array(), 'message' => 'Please select both From and To dates.'));
            return;
        }

        $data = $this->fees_model->getPaidStudentsRegister($branchID, $fromDate, $toDate, $feeTypes);
        echo json_encode(array('status' => 'success', 'data' => $data));
    }

    public function fetchFeeDiscount()
    {
        if (!get_permission('feediscount', 'is_view')) {
            echo json_encode(array());
            return;
        }
        $this->db->select('fd.*, fd.fixed_amount as amount, b.name as branch_name, ft.fee_name as fees_type');
        $this->db->from('fees_discount fd');
        $this->db->join('branch b', 'b.id = fd.branch_id', 'left');
        $this->db->join('fees_type ft', 'ft.id = fd.fee_type_id', 'left');
        if (!is_superadmin_loggedin()) {
            $this->db->where('fd.branch_id', get_loggedin_branch_id());
        }
        $result = $this->db->order_by('fd.id', 'asc')->get()->result_array();
        echo json_encode($result);
    }

    /* build sanitized fees_discount payload from POST */
    private function discountPostData()
    {
        $discountType = $this->input->post('discount_type');
        if (empty($discountType)) {
            $discountType = 'none';
        }
        $percentage = $this->input->post('percentage');
        $fixedAmount = $this->input->post('fixed_amount');
        return array(
            'branch_id' => is_superadmin_loggedin() ? $this->input->post('branch_id') : get_loggedin_branch_id(),
            'fee_type_id' => $this->input->post('fee_type_id'),
            'name' => trim($this->input->post('name')),
            'discount_code' => trim($this->input->post('discount_code')),
            'discount_type' => $discountType,
            'percentage' => ($percentage === '' || $percentage === null) ? null : $percentage,
            'fixed_amount' => ($fixedAmount === '' || $fixedAmount === null) ? null : $fixedAmount,
            'description' => $this->input->post('description'),
        );
    }

    public function save_discount()
    {
        if (!get_permission('feediscount', 'is_add')) {
            echo json_encode(array('status' => 'fail', 'message' => translate('access_denied')));
            return;
        }
        $data = $this->discountPostData();
        if (empty($data['branch_id']) || empty($data['fee_type_id']) || $data['name'] === '' || $data['discount_code'] === '') {
            echo json_encode(array('status' => 'fail', 'message' => translate('please_fill_all_the_fields')));
            return;
        }
        $this->db->insert('fees_discount', $data);
        echo json_encode(array('status' => 'success', 'message' => translate('information_has_been_saved_successfully')));
    }

    public function update_discount()
    {
        if (!get_permission('feediscount', 'is_edit')) {
            echo json_encode(array('status' => 'fail', 'message' => translate('access_denied')));
            return;
        }
        $discountID = $this->input->post('discount_id');
        $data = $this->discountPostData();
        if (empty($discountID) || empty($data['branch_id']) || empty($data['fee_type_id']) || $data['name'] === '' || $data['discount_code'] === '') {
            echo json_encode(array('status' => 'fail', 'message' => translate('please_fill_all_the_fields')));
            return;
        }
        if (!is_superadmin_loggedin()) {
            $this->db->where('branch_id', get_loggedin_branch_id());
        }
        $this->db->where('id', $discountID)->update('fees_discount', $data);
        echo json_encode(array('status' => 'success', 'message' => translate('information_has_been_updated_successfully')));
    }

    public function delete_discount_fee()
    {
        if (!get_permission('feediscount', 'is_delete')) {
            echo json_encode(array('status' => 'fail', 'message' => translate('access_denied')));
            return;
        }
        $id = $this->input->post('id');
        if (empty($id)) {
            echo json_encode(array('status' => 'fail', 'message' => translate('invalid_request')));
            return;
        }
        if (!is_superadmin_loggedin()) {
            $this->db->where('branch_id', get_loggedin_branch_id());
        }
        $this->db->where('id', $id)->delete('fees_discount');
        echo json_encode(array('status' => 'success', 'message' => translate('information_has_been_deleted_successfully')));
    }

    public function search_students_for_discount()
    {
        if (!get_permission('feediscount', 'is_view')) {
            echo json_encode(array());
            return;
        }
        $classID = $this->input->post('class_id');
        $sectionID = $this->input->post('section_id');
        if (empty($classID) || empty($sectionID)) {
            echo json_encode(array());
            return;
        }
        $this->db->select("e.student_id, s.register_no, CONCAT(s.first_name, ' ', s.last_name) as student_name, c.name as class_name, se.name as section_name");
        $this->db->from('enroll e');
        $this->db->join('student s', 's.id = e.student_id', 'left');
        $this->db->join('class c', 'c.id = e.class_id', 'left');
        $this->db->join('section se', 'se.id = e.section_id', 'left');
        $this->db->where('e.class_id', $classID);
        $this->db->where('e.section_id', $sectionID);
        $this->db->where('e.session_id', get_session_id());
        $result = $this->db->order_by('s.first_name', 'asc')->get()->result_array();
        echo json_encode($result);
    }

    public function apply_discount()
    {
        if (!get_permission('feediscount', 'is_add')) {
            echo json_encode(array('status' => 'fail', 'message' => translate('access_denied')));
            return;
        }
        $discountID = $this->input->post('discount_id');
        $studentIDs = $this->input->post('student_ids');
        if (empty($discountID) || empty($studentIDs)) {
            echo json_encode(array('status' => 'fail', 'message' => translate('please_fill_all_the_fields')));
            return;
        }
        $sessionID = get_session_id();
        $count = 0;
        foreach ((array) $studentIDs as $studentID) {
            $exists = $this->db->where(array('student_id' => $studentID, 'discount_id' => $discountID, 'session_id' => $sessionID))
                ->get('student_fee_discount')->num_rows();
            if ($exists == 0) {
                $this->db->insert('student_fee_discount', array(
                    'student_id' => $studentID,
                    'discount_id' => $discountID,
                    'session_id' => $sessionID,
                ));
                $count++;
            }
        }
        echo json_encode(array('status' => 'success', 'message' => $count . ' ' . translate('information_has_been_saved_successfully')));
    }

    public function removeStudentFeeDiscount()
    {
        if (!get_permission('feediscount', 'is_delete')) {
            echo json_encode(array('status' => 'fail', 'message' => translate('access_denied')));
            return;
        }
        $id = $this->input->post('id');
        if (empty($id)) {
            echo json_encode(array('status' => 'fail', 'message' => translate('invalid_request')));
            return;
        }
        $this->db->where('id', $id)->delete('student_fee_discount');
        echo json_encode(array('status' => 'success', 'message' => translate('information_has_been_deleted_successfully')));
    }

    private function feeMasterPostData()
    {
        return array(
            'branch_id' => is_superadmin_loggedin() ? $this->input->post('branch_id') : get_loggedin_branch_id(),
            'fee_type_id' => $this->input->post('fee_types'),
            'student_category' => $this->input->post('student_category'),
            'classes' => $this->input->post('classes'),
            'amount' => $this->input->post('amount'),
            'due_day' => $this->input->post('due_day'),
            'fine_amount' => $this->input->post('fine_amount'),
            'fine_type' => $this->input->post('fine_type') ?? '',
            'per_val' => $this->input->post('per_val'),
            'amt_val' => $this->input->post('amt_val'),
        );
    }

    public function fetch_categories()
    {
        echo json_encode($this->db->order_by('id', 'asc')->get('student_category')->result());
    }

    public function save_feeMaster()
    {
        if (!get_permission('feemaster', 'is_add')) {
            echo json_encode(array('status' => 'fail', 'message' => translate('access_denied')));
            return;
        }
        $data = $this->feeMasterPostData();
        if (empty($data['branch_id']) || empty($data['fee_type_id']) || empty($data['student_category'])) {
            echo json_encode(array('status' => 'fail', 'message' => translate('please_fill_all_the_fields')));
            return;
        }
        $this->fees_model->saveFeeMaster($data);
        echo json_encode(array('status' => 'success', 'message' => translate('information_has_been_saved_successfully')));
    }

    public function fetchFeeMaster()
    {
        if (!get_permission('feemaster', 'is_view')) {
            echo json_encode(array());
            return;
        }
        echo json_encode($this->fees_model->getFeeMasterList());
    }

    public function delete_fee_master()
    {
        if (!get_permission('feemaster', 'is_delete')) {
            echo json_encode(array('status' => 'fail', 'message' => translate('access_denied')));
            return;
        }
        $id = $this->input->post('id');
        if (empty($id)) {
            echo json_encode(array('status' => 'fail', 'message' => translate('invalid_request')));
            return;
        }
        if (!is_superadmin_loggedin()) {
            $this->db->where('branch_id', get_loggedin_branch_id());
        }
        $this->db->where('id', $id)->delete('fee_master');
        $this->db->where('fee_master_id', $id)->delete('fee_master_classwise');
        echo json_encode(array('status' => 'success', 'message' => translate('information_has_been_deleted_successfully')));
    }

    public function getFeeTypeMonth()
    {
        $feeTypeID = $this->input->post('fee_type_id');
        $row = $this->db->select('month')->where('id', $feeTypeID)->get('fees_type')->row_array();
        if (empty($row)) {
            echo json_encode(array('status' => 'fail'));
            return;
        }
        echo json_encode(array('status' => 'success', 'month' => $row['month']));
    }

    public function get_fee_allocation_details()
    {
        $feeMasterID = $this->input->post('fee_master_id');
        echo json_encode($this->fees_model->getFeeMasterAllocationDetails($feeMasterID));
    }

    public function update_fee_master()
    {
        if (!get_permission('feemaster', 'is_edit')) {
            echo json_encode(array('status' => 'fail', 'message' => translate('access_denied')));
            return;
        }
        $feeMasterID = $this->input->post('fee_master_id');
        $data = $this->feeMasterPostData();
        if (empty($feeMasterID) || empty($data['fee_type_id'])) {
            echo json_encode(array('status' => 'fail', 'message' => translate('please_fill_all_the_fields')));
            return;
        }
        $this->fees_model->updateFeeMaster($feeMasterID, $data);
        echo json_encode(array('status' => 'success', 'message' => translate('information_has_been_updated_successfully')));
    }

    public function fetch_fees_last()
    {
        if (!get_permission('fees_register', 'is_view')) {
            echo json_encode(array());
            return;
        }
        $classID = $this->input->post('class_id');
        $sectionID = $this->input->post('section_id');
        if (empty($classID) || empty($sectionID)) {
            echo json_encode(array());
            return;
        }
        echo json_encode($this->fees_model->getFeesLastStudents($classID, $sectionID));
    }

    public function save_last_balance()
    {
        if (!get_permission('fees_register', 'is_edit')) {
            echo json_encode(array('status' => false));
            return;
        }
        $studentID = $this->input->post('student_id');
        $balance = $this->input->post('balance');
        if (empty($studentID)) {
            echo json_encode(array('status' => false));
            return;
        }
        $this->fees_model->saveStudentLastBalance($studentID, $balance);
        echo json_encode(array('status' => true));
    }

    public function fetch_fees_report()
    {
        if (!get_permission('fees_report', 'is_view')) {
            echo json_encode(array());
            return;
        }
        $classID = $this->input->post('class_id');
        $sectionID = $this->input->post('section_id');
        if (empty($classID) || empty($sectionID)) {
            echo json_encode(array());
            return;
        }
        echo json_encode($this->fees_model->getFeesReport($classID, $sectionID));
    }

    public function fetch_defaulters()
    {
        if (!get_permission('fees_defaulter', 'is_view')) {
            echo json_encode(array('data' => array()));
            return;
        }
        $filters = array(
            'month' => $this->input->post('month'),
            'class_id' => $this->input->post('class_id'),
            'section_id' => $this->input->post('section_id'),
            'feeTypes' => $this->input->post('feeTypes'),
            'min_due' => $this->input->post('min_due'),
            'max_due' => $this->input->post('max_due'),
        );
        echo json_encode(array('data' => $this->fees_model->getFeeDefaulters($filters)));
    }

    public function submitFamilyFees()
    {
        if (!get_permission('reciepts', 'is_add')) {
            echo json_encode(array('status' => 'fail', 'message' => translate('access_denied')));
            return;
        }
        $paymentsRaw = $this->input->post('payments');
        $payments = is_array($paymentsRaw) ? $paymentsRaw : json_decode($paymentsRaw, true);
        if (empty($payments)) {
            echo json_encode(array('status' => 'fail', 'message' => translate('please_fill_all_the_fields')));
            return;
        }
        $this->fees_model->submitFamilyFeesPayments($payments);
        echo json_encode(array('status' => 'success', 'message' => translate('information_has_been_saved_successfully')));
    }
}
