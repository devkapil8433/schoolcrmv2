<?php
defined('BASEPATH') or exit('No direct script access allowed');

class fees_structure extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Fees_model');
        $this->load->model('fees_model');
        $this->load->model('Inventory_model');
        if (!moduleIsEnabled('model')) {
            access_denied();
        }
    }

    //      public function enquiry()
    // {
    //     // redirect(base_url('fees_structure/enquiry'));
    //     echo "Hello";
    // }

    public function enquiry()
    {
        if (!get_permission('fees_structure', 'is_view')) {
            access_denied();
        }
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css',
                'https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css', // ✅ Bootstrap 5 compatible
                'css/feeCollect/feeCollect.css',
            ),
            'js' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js',
                'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', // Core DataTables
                'https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js', // ✅ Bootstrap 5 integration
                'js/feeCollect/feeCollect.js',
            ),
        );

        // $this->data['result'] = $this->app_lib->getTable('offline_fees_payments');
        $this->data['title'] = translate('fees_structure');
        $this->data['sub_page'] = 'fees_structure/enquiry';
        $this->data['main_menu'] = 'fees_structure';
        $this->load->view('layout/index', $this->data);
    }

    public function offlinebankpayment()
    {
        if (!get_permission('offline-bank-payment', 'is_view')) {
            access_denied();
        }
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css',
                'https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css', // ✅ Bootstrap 5 compatible
                'css/feeCollect/offlinePayment.css'
            ),
            'js' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js',
                'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', // Core DataTables
                'https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js', // ✅ Bootstrap 5 integration
                'js/feeCollect/offlinePayment.js',
            ),
        );

        // $this->data['result'] = $this->app_lib->getTable('offline_fees_payments');
        $this->data['title'] = translate('offlinebankpayment');
        $this->data['sub_page'] = 'fees_structure/offline_bank_payment';
        $this->data['main_menu'] = 'fees_structure';
        $this->load->view('layout/index', $this->data);
    }

    public function searchpayment()
    {
        if (!get_permission('searchpayment', 'is_view')) {
            access_denied();
        }
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css',
                'css/feeCollect/searchPayment.css'
            ),
            'js' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js',
            ),
        );

        // $this->data['result'] = $this->app_lib->getTable('offline_fees_payments');
        $this->data['title'] = translate('searchpayment');
        $this->data['sub_page'] = 'fees_structure/searchpayment';
        $this->data['main_menu'] = 'fees_structure';
        $this->load->view('layout/index', $this->data);
    }

    public function due_FeeSearch()
    {
        if (!get_permission('due_feesearch', 'is_view')) {
            access_denied();
        }
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css',
                'css/feeCollect/dueFee.css'
            ),
            'js' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js',
            ),
        );

        // $this->data['result'] = $this->app_lib->getTable('offline_fees_payments');
        $this->data['title'] = translate('due_feesearch');
        $this->data['sub_page'] = 'fees_structure/due_feesearch';
        $this->data['main_menu'] = 'fees_structure';
        $this->load->view('layout/index', $this->data);
    }

    public function feemaster()
    {
        if (!get_permission('feemaster', 'is_view')) {
            access_denied();
        }
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css',
                'css/feeCollect/feeMaster.css',
            ),
            'js' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js',
            ),
        );

        $this->load->model('classes_model');
        $this->data['branches'] = $this->db->select('id,name')->from('branch')->get()->result_array();
        $this->data['classes'] = $this->classes_model->get_all_classes();
        // $this->data['result'] = $this->app_lib->getTable('offline_fees_payments');
        $this->data['title'] = translate('fee_master');
        $this->data['sub_page'] = 'fees_structure/feemaster';
        $this->data['main_menu'] = 'fees_structure';
        $this->load->view('layout/index', $this->data);
    }

    public function fees_carry_forward()
    {
        if (!get_permission('due_feesearch', 'is_view')) {
            access_denied();
        }
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css',
                'css/feeCollect/feeCarryForward.css',
            ),
            'js' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js',
            ),
        );

        // $this->data['result'] = $this->app_lib->getTable('offline_fees_payments');
        $this->data['title'] = translate('fees_carry_forward');
        $this->data['sub_page'] = 'fees_structure/fees_carry_forward';
        $this->data['main_menu'] = 'fees_structure';
        $this->load->view('layout/index', $this->data);
    }
    public function feegroup()
    {
        if (!get_permission('feegroup', 'is_view')) {
            access_denied();
        }
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css',
                'css/feeCollect/feeGroup.css',
            ),
            'js' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js',
            ),
        );

        // $this->data['result'] = $this->app_lib->getTable('offline_fees_payments');
        $this->data['title'] = translate('fee_group');
        $this->data['sub_page'] = 'fees_structure/feegroup';
        $this->data['main_menu'] = 'fees_structure';
        $this->load->view('layout/index', $this->data);
    }

    public function feetype()
    {
        if (!get_permission('feetype', 'is_view') && !get_permission('fees_type', 'is_view')) {
            access_denied();
        }
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css',
                // 'https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css',
                'css/feeCollect/feeType.css',
            ),
            'js' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js',
                //  'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', // Core DataTables
                // 'https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js', 
            ),
        );

        // $this->data['result'] = $this->app_lib->getTable('offline_fees_payments');
        $this->data['branches'] = $this->db->select('id,name')->from('branch')->get()->result_array();
        $this->data['title'] = translate('fee_type');
        $this->data['sub_page'] = 'fees_structure/feetype';
        $this->data['main_menu'] = 'fees_structure';
        $this->load->view('layout/index', $this->data);
    }

    public function feediscount()
    {
        if (!get_permission('feediscount', 'is_view')) {
            access_denied();
        }
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css',
                'css/feeCollect/feeDiscount.css',
            ),
            'js' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js',
            ),
        );

        $this->data['branches'] = $this->db->select('id,name')->from('branch')->get()->result_array();
        // $this->data['result'] = $this->app_lib->getTable('offline_fees_payments');
        $this->data['title'] = translate('fee_discount');
        $this->data['sub_page'] = 'fees_structure/feediscount';
        $this->data['main_menu'] = 'fees_structure';
        $this->load->view('layout/index', $this->data);
    }


    public function feereminder()
    {
        if (!get_permission('feereminder', 'is_view')) {
            access_denied();
        }
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css',
                'css/feeCollect/feeReminder.css',
            ),
            'js' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js',
            ),
        );

        // $this->data['result'] = $this->app_lib->getTable('offline_fees_payments');
        $this->data['title'] = translate('fee_reminder');
        $this->data['sub_page'] = 'fees_structure/feereminder';
        $this->data['main_menu'] = 'fees_structure';
        $this->load->view('layout/index', $this->data);
    }


    public function familyReciepts()
    {
        if (!get_permission('reciepts', 'is_view')) {
            access_denied();
        }
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css',
                'css/feeCollect/reciepts.css',
            ),
            'js' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js',
            ),
        );

        // $this->data['result'] = $this->app_lib->getTable('offline_fees_payments');
        $this->data['title'] = translate('Family Reciepts');
        $this->data['sub_page'] = 'fees_structure/family-reciepts';
        $this->data['main_menu'] = 'fees_structure';
        $this->load->view('layout/index', $this->data);
    }

    public function fees_register()
    {
        if (!get_permission('fees_register', 'is_view')) {
            access_denied();
        }
        $branchID = $this->input->post('branch_id');
        if (empty($branchID) && !is_superadmin_loggedin()) {
            $branchID = get_loggedin_branch_id();
        }
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css',
                'css/feeCollect/fees_register.css',
            ),
            'js' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js',
                'vendor/chartjs/chart.min.js',
            ),
        );
        $this->data['branch_id'] = $branchID;
        // $this->data['result'] = $this->app_lib->getTable('offline_fees_payments');
        $this->data['title'] = translate('Fees Register');
        $this->data['sub_page'] = 'fees_structure/fees_register';
        $this->data['main_menu'] = 'fees_structure';
        $this->load->view('layout/index', $this->data);
    }
    public function fees_defaulter()
    {
        if (!get_permission('fees_defaulter', 'is_view')) {
            access_denied();
        }
        $branchID = $this->input->post('branch_id');
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css',
                'css/feeCollect/fees_register.css',
            ),
            'js' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js',
            ),
        );
        $this->load->model('classes_model');
        $this->load->model('section_model');
        $this->data['branch_id'] = $branchID;

        // $this->data['result'] = $this->app_lib->getTable('offline_fees_payments');
        $this->data['classes'] = $this->classes_model->get_all_classes();
        $this->data['sections'] = $this->section_model->get_all_sections();
        $this->data['title'] = translate('Fees Defaulters');
        $this->data['sub_page'] = 'fees_structure/fees_defaulter';
        $this->data['main_menu'] = 'fees_structure';
        $this->load->view('layout/index', $this->data);
    }

    public function fees_reports()
    {
        if (!get_permission('fees_report', 'is_view')) {
            access_denied();
        }
        $branchID = $this->input->post('branch_id');
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css',
                'css/feeCollect/fees_report.css',
            ),
            'js' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js',
            ),
        );
        $this->load->model('classes_model');
        $this->load->model('section_model');
        $this->data['branch_id'] = $branchID;

        // $this->data['result'] = $this->app_lib->getTable('offline_fees_payments');
        $this->data['classes'] = $this->classes_model->get_all_classes();
        $this->data['sections'] = $this->section_model->get_all_sections();
        $this->data['title'] = translate('Fees Report');
        $this->data['sub_page'] = 'fees_structure/fees_report';
        $this->data['main_menu'] = 'fees_structure';
        $this->load->view('layout/index', $this->data);
    }
    public function fees_last()
    {
        if (!get_permission('fees_register', 'is_view')) {
            access_denied();
        }
        $branchID = $this->input->post('branch_id');
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css',
                'css/feeCollect/fees_register.css',
            ),
            'js' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js',
            ),
        );
        $this->load->model('classes_model');
        $this->load->model('section_model');
        $this->data['branch_id'] = $branchID;
        $this->data['classes'] = $this->classes_model->get_all_classes();
        $this->data['sections'] = $this->section_model->get_all_sections();
        $this->data['title'] = translate('Fees Last');
        $this->data['sub_page'] = 'fees_structure/fees_last';
        $this->data['main_menu'] = 'fees_structure';
        $this->load->view('layout/index', $this->data);
    }
    public function assign_discount($discount_id)
    {
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.css',
                'css/feeCollect/fees_register.css',
            ),
            'js' => array(
                'vendor/bootstrap-fileupload/bootstrap-fileupload.min.js',
            ),
        );
        $this->load->model('classes_model');
        $this->load->model('section_model');
        $this->data['discount'] = $this->Fees_model->getDiscountById($discount_id);
        $this->data['classes'] = $this->classes_model->get_all_classes();
        $this->data['sections'] = $this->section_model->get_all_sections();
        $this->data['title'] = translate('Assign Fees Discount');
        $this->data['sub_page'] = 'fees_structure/assign_discount';
        $this->data['main_menu'] = 'fees_structure';
        $this->load->view('layout/index', $this->data);
    }

    public function feediscountStudents()
    {
        if (!get_permission('feediscount', 'is_view')) {
            access_denied();
        }

        $this->load->model('Fees_model');

        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/datatables/dataTables.bootstrap5.min.css',
                'css/feeCollect/feeDiscount.css',
            ),
            'js' => array(
                'vendor/datatables/jquery.dataTables.min.js',
                'vendor/datatables/dataTables.bootstrap5.min.js',
            ),
        );

        // 🔥 fetch applied discount data
        $this->data['discount_students'] =
            $this->Fees_model->getStudentFeeDiscounts();

        $this->data['title'] = translate('fee_discount_students');
        $this->data['sub_page'] = 'fees_structure/feediscountStudents';
        $this->data['main_menu'] = 'fees_structure';

        $this->load->view('layout/index', $this->data);
    }
    public function fees_cards()
    {
        if (!get_permission('invoice', 'is_view')) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();

        // Handle POST filter (optional — no JSON output)
        if ($_POST) {
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required');
            }
            $this->form_validation->set_rules('class_id', translate('class'), 'trim');
            $this->form_validation->set_rules('section_id', translate('section'), 'trim');

            if ($this->form_validation->run() == true) {
                // You can fetch filtered data here
                $export_title = get_type_name_by_id('branch', $branchID) . ' - ' . translate('invoice_list');
                $this->data['export_title'] = $export_title;
            } else {
                set_alert('error', 'Please check your filter inputs');
            }
        }

        // Load your required data (same as your add() function style)
        $this->load->model('classes_model');
        $this->load->model('section_model');
        $this->data['classes'] = $this->classes_model->get_all_classes();
        $this->data['sections'] = $this->section_model->get_all_sections();

        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('fees_card');
        $this->data['sub_page'] = 'fees_structure/fees_cards';
        $this->data['main_menu'] = 'fees_structure';

        $this->load->view('layout/index', $this->data);
    }
    public function getStudentFeesCardTable()
    {
        $enroll_id = $this->input->post('enroll_id');

        if (!$enroll_id) {
            echo '<div class="alert alert-danger">Invalid Student</div>';
            return;
        }

        $basic = $this->fees_model->getInvoiceBasic($enroll_id);
        if (empty($basic)) {
            echo '<div class="alert alert-danger">No fee data found</div>';
            return;
        }

        $allocations = $this->fees_model->getStudentFeeDetails($enroll_id);
        $transport_fees = moduleIsEnabled('transport')
            ? $this->fees_model->getStudentTransportFees($enroll_id, $basic['stoppage_point_id'])
            : [];

        $salesBills = $this->fees_model->getStudentSalesBills($basic['id'] ?? null);
        $monthlyCollections = $this->fees_model->getStudentMonthlyCollections($basic['id'] ?? null);

        /* ================== OUTPUT START ================== */?>

        <div class="bill-info">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%; margin-bottom:10px;">
                <div style="flex: 1; margin-right: 20px;">
                    <!-- Your original table code here -->
                    <table class="table table-bordered table-up" style="width:100%; font-size: 15px;">
                        <tbody>
                            <!-- <tr>
												<td><strong>Date :</strong> <?= date('d-m-Y') ?></td>
												<td><strong>Rec No :</strong> <?= $basic['invoice_no'] ?? '' ?></td>
											</tr> -->
                            <tr>
                                <td><strong>Name :</strong> <?= $basic['first_name'] . ' ' . $basic['last_name'] ?></td>
                                <td><strong>Class :</strong> <?= $basic['class_name'] . " (" . $basic['section_name'] . ")" ?></td>
                            </tr>
                            <tr>
                                <td><strong>Father’s Name :</strong> <?= $basic['father_name'] ?? '' ?></td>
                                <td><strong>Registration No :</strong> <?= $basic['register_no'] ?></td>
                            </tr>
                            <tr>
                                <td><strong>Category :</strong> <?= $basic['category'] ?? '' ?></td>
                                <td><strong>Roll No :</strong> <?= $basic['roll'] ?? '' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Mobile :</strong> <?= $basic['mobileno'] ?? '' ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div style="width: 170px; text-align: center;">
                    <?php if (!empty($basic['photo'])): ?>
                        <img src="<?= base_url('uploads/images/student/' . $basic['photo']); ?>" style="width:190px; height:190px; border:1px solid #000; padding:3px;">
                    <?php else: ?>
                        <div style="width:190px; height:190px; border:1px solid #000; display:flex; align-items:center; justify-content:center;">
                            <span>No Photo</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        <div class="table-responsive br-none">
            <table class="table invoice-items table-hover mb-none table-bordered fees-card-table">

                <thead>
                    <tr class="text-dark">
                        <th id="cell-count" class="text-weight-semibold hidden-print">
                            <div class="checkbox-replace">
                            </div>
                        </th>
                        <th id="cell-count" class="text-weight-semibold hidden-print">months</th>
                        <th id="cell-item" class="text-weight-semibold"><?= translate("fees") ?></th>
                        <th id="cell-price" class="text-weight-semibold"><?= translate("material") ?></th>
                        <th id="cell-price" class="text-weight-semibold"><?= translate("transport") ?></th>
                        <th id="cell-price" class="text-weight-semibold"><?= translate("exam") ?></th>
                        <th id="cell-price" class="text-weight-semibold"><?= translate("last_balance") ?></th>
                        <th id="cell-price" class="text-weight-semibold"><?= translate("total_of_this_month") ?></th>
                        <th id="cell-price" class="text-weight-semibold"><?= translate("paid") ?></th>
                        <th id="cell-price" class="text-weight-semibold"><?= translate("reciept_no") ?></th>
                        <th id="cell-total" class="text-center text-weight-semibold"><?= translate("balance") ?></th>
                    </tr>
                </thead>




                <tbody id="invoiceSummary">
                    <?php
                    $allMonths = [
                        4 => 'April',
                        5 => 'May',
                        6 => 'June',
                        7 => 'July',
                        8 => 'August',
                        9 => 'September',
                        10 => 'October',
                        11 => 'November',
                        12 => 'December',
                        1 => 'January',
                        2 => 'February',
                        3 => 'March'
                    ];

                    $transportFees = $transport_fees;
                    $g_fees = $g_material = $g_transport = $g_exam = 0;
                    $g_lastBal = $g_total = $g_paid = $g_balance = 0;
                    $currentMonth = (int) date('n');
                    $academicIndex = function ($monthNum) {
                        return $monthNum >= 4 ? $monthNum - 4 : $monthNum + 8;
                    };

                    foreach ($allMonths as $monthNo => $monthName):

                        $fees = $material = $transport = $exam = 0;
                        $lastBal = $paid = $total = $balance = 0;
                        $receiptNos = [];
                        $isFutureMonth = $academicIndex((int) $monthNo) > $academicIndex($currentMonth);

                        /* ===== LAST YEAR BALANCE (APRIL ONLY) ===== */
                        if ($monthNo == 4 && !empty($allocations[0]['previous_last_balance'])) {
                            $lastBal = (float) $allocations[0]['previous_last_balance'];
                            $total  += $lastBal;
                            $balance += $lastBal;
                        }

                        /* ===== ASSIGNED FEES ===== */
                        foreach ($allocations as $row) {
                            $assignedMonths = array_filter(array_map('intval', explode(',', (string) ($row['month'] ?? ''))));
                            if (!in_array((int) $monthNo, $assignedMonths, true)) {
                                continue;
                            }

                            $amount = (float) $row['amount'];
                            $bal    = max(0, (float) $row['balance']);

                            if (stripos((string) ($row['fee_name'] ?? ''), 'exam') !== false) {
                                $exam += $amount;
                            } else {
                                $fees += $amount;
                            }

                            $total   += $amount;
                            $balance += $bal;
                        }

                        /* ===== TRANSPORT ===== */
                        foreach ($transportFees as $t) {
                            if ((int) $t->month === (int) $monthNo) {
                                $tfAmount = (float) $t->route_fare;
                                $tfPaid = 0;
                                $tfDeposit = $this->fees_model->getStudentTransportFeeDeposit($t->id);
                                if (!empty($tfDeposit)) {
                                    $tfPaid = (float) $tfDeposit['total_amount'] + (float) $tfDeposit['total_discount'];
                                }
                                $transport += $tfAmount;
                                $total     += $tfAmount;
                                $balance   += max(0, $tfAmount - $tfPaid);
                            }
                        }

                        /* ===== MATERIAL / BOOKS ===== */
                        foreach ($salesBills as $bill) {
                            if ((int) date('n', strtotime($bill->date)) === (int) $monthNo) {
                                $material += (float) $bill->total;
                                $total    += (float) $bill->total;
                                $balance  += (float) $bill->due;
                            }
                        }

                        /* ===== PAID + RECEIPT (from fee_collect) ===== */
                        if (!empty($monthlyCollections[$monthNo])) {
                            $paid = (float) ($monthlyCollections[$monthNo]['paid'] ?? 0);
                            $receiptNos = $monthlyCollections[$monthNo]['receipts'] ?? [];
                        }
                        foreach ($salesBills as $bill) {
                            if ((int) date('n', strtotime($bill->date)) === (int) $monthNo) {
                                $paid += (float) $bill->paid;
                                if (!empty($bill->receipt_no)) {
                                    $receiptNos[] = $bill->receipt_no;
                                } elseif (!empty($bill->invoice_no)) {
                                    $receiptNos[] = $bill->invoice_no;
                                }
                            }
                        }
                        $receiptNos = array_values(array_unique(array_filter($receiptNos)));

                        $g_fees      += $fees;
                        $g_material  += $material;
                        $g_transport += $transport;
                        $g_exam      += $exam;
                        $g_lastBal   += $lastBal;
                        $g_total     += $total;
                        $g_paid      += $paid;
                        $g_balance   += $balance;
                    ?>
                        <tr<?= $isFutureMonth ? ' class="month-blurred"' : '' ?>>
                            <td class="hidden-print"></td>
                            <td><strong><?= $monthName ?></strong></td>
                            <td><?= $fees ?: '-' ?></td>
                            <td><?= $material ?: '-' ?></td>
                            <td><?= $transport ?: '-' ?></td>
                            <td><?= $exam ?: '-' ?></td>
                            <td><?= $lastBal ?: '-' ?></td>
                            <td><strong><?= $total ?: '-' ?></strong></td>
                            <td><?= $paid ?: '-' ?></td>
                            <td>
                                <?php if (empty($receiptNos)): ?>
                                    -
                                <?php elseif (count($receiptNos) === 1): ?>
                                    <?= html_escape($receiptNos[0]) ?>
                                <?php else: ?>
                                    <?php
                                    $firstReceipt = $receiptNos[0];
                                    $extraReceipts = array_slice($receiptNos, 1);
                                    $extraCount = count($extraReceipts);
                                    ?>
                                    <?= html_escape($firstReceipt) ?>
                                    <div class="receipt-more" style="display:none;">
                                        <?= implode('<br>', array_map('html_escape', $extraReceipts)) ?>
                                    </div>
                                    <a href="javascript:void(0)" class="toggle-receipts" data-more="<?= $extraCount ?>">Show more (<?= $extraCount ?>)</a>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= $balance ?: '-' ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="background:#f5f7fa; font-size:16px; font-weight:700; border-top:3px solid #333;">
                        <td class="hidden-print"></td>
                        <td><strong>GRAND TOTAL</strong></td>
                        <td><?= $g_fees ?: '-' ?></td>
                        <td><?= $g_material ?: '-' ?></td>
                        <td><?= $g_transport ?: '-' ?></td>
                        <td><?= $g_exam ?: '-' ?></td>
                        <td><?= $g_lastBal ?: '-' ?></td>
                        <td><strong><?= $g_total ?: '-' ?></strong></td>
                        <td><?= $g_paid ?: '-' ?></td>
                        <td>-</td>
                        <td><strong><?= $g_balance ?: '-' ?></strong></td>
                    </tr>
                </tbody>


            </table>
        </div>

     <?php
    }

    public function fees_structure()
    {
        if (!get_permission('invoice', 'is_view')) {
            access_denied();
        }

        $this->load->model('classes_model');
        $this->load->model('fees_model');

        $this->data['classes'] = $this->classes_model->get_all_classes();
        $this->data['fee_types']     = $this->fees_model->get_used_fee_types();
        $this->data['fee_structure'] = $this->fees_model->get_class_wise_fee_structure();
        $this->data['transportFees'] = $this->fees_model->get_class_wise_transport_fee();
        $this->data['sets'] = $this->Inventory_model->get_all_sets(); // id, set_name
        $this->data['classSetTotals'] = $this->fees_model->get_class_wise_set_totals();
        $this->data['setProducts']   = $this->Inventory_model->get_set_products_grouped();
        $branchID = $this->input->post('branch_id');
        $this->data['branch_id'] = $branchID;


        $this->data['title'] = 'Fee Structure';
        $this->data['sub_page'] = 'fees_structure/fees_structure';
        $this->data['main_menu'] = 'fees_structure';

        $this->load->view('layout/index', $this->data);
    }

    public function class_students($class_id)
    {
        if (!get_permission('invoice', 'is_view')) {
            access_denied();
        }

        $this->load->model('fees_model');
        $this->load->model('student_model');
        $this->load->model('Inventory_model');

        $this->data['fee_types'] = $this->fees_model->get_used_fee_types();
        $this->data['fee_structure'] = $this->fees_model->get_class_wise_fee_structure();
        $this->data['transportFees'] = $this->fees_model->get_class_wise_transport_fee();
        $this->data['students'] = $this->fees_model->get_students_by_class($class_id);

        $sets = $this->Inventory_model->get_all_sets();
        $groupedSets = [];
        foreach ($sets as $set) {
            $groupedSets[$set['set_name']][] = $set;
        }

        $this->data['groupedSets'] = $groupedSets;
        $this->data['classSetTotals'] = $this->fees_model->get_class_wise_set_totals();
        $this->data['setProducts'] = $this->Inventory_model->get_set_products_grouped();

        $this->data['title'] = 'Class Wise Student Fees';
        $this->data['sub_page'] = 'fees_structure/class_students';
        $this->data['main_menu'] = 'fees_structure';

        $this->load->view('layout/index', $this->data);
    }

    public function fee_estimation_report()
    {
        if (!get_permission('invoice', 'is_view')) {
            access_denied();
        }

        $this->load->model('classes_model');
        $this->load->model('fees_model');

        $branchID = $this->application_model->get_branch_id();
        if ($this->input->post('branch_id')) {
            $branchID = $this->input->post('branch_id');
        }
        $session_id = get_session_id();

        $this->data['classes'] = $this->classes_model->get_all_classes();
        $this->data['fee_types'] = $this->fees_model->get_used_fee_types();
        // Class-wise SUM of remaining balances per fee type (not catalog rates)
        $this->data['fee_structure'] = $this->fees_model->get_class_wise_fee_type_balances($session_id, $branchID);
        $this->data['class_received'] = $this->fees_model->get_class_wise_received_amount($session_id, $branchID);
        $this->data['class_assigned'] = $this->fees_model->get_class_wise_assigned_amount($session_id, $branchID);
        $this->data['transportFees'] = $this->fees_model->get_class_wise_transport_balances($session_id, $branchID);
        $this->data['classSetAmounts'] = $this->fees_model->get_class_wise_set_amounts();
        $this->data['sets'] = $this->Inventory_model->get_all_sets();
        $this->data['class_last_balance'] = $this->fees_model->get_class_wise_last_balance($session_id, $branchID);
        $this->data['branch_id'] = $branchID;

        $this->data['title'] = 'Fee Estimation Report';
        $this->data['sub_page'] = 'fees_structure/fees_estimation_report';
        $this->data['main_menu'] = 'fees_structure';

        $this->load->view('layout/index', $this->data);
    }

    public function fee_estimation_class_students($class_id = '')
    {
        if (!get_permission('invoice', 'is_view')) {
            access_denied();
        }

        $class_id = (int) $class_id;
        if ($class_id <= 0) {
            set_alert('error', translate('invalid_request'));
            redirect(base_url('fees_structure/fee_estimation_report'));
        }

        $this->load->model('classes_model');
        $this->load->model('fees_model');

        $branchID = $this->input->get('branch_id');
        if (empty($branchID)) {
            $branchID = $this->application_model->get_branch_id();
        }
        $session_id = get_session_id();

        $class = $this->db->select('id, name')->where('id', $class_id)->get('class')->row_array();
        if (empty($class)) {
            set_alert('error', translate('invalid_request'));
            redirect(base_url('fees_structure/fee_estimation_report'));
        }

        $this->data['class'] = $class;
        $this->data['students'] = $this->fees_model->get_students_by_class($class_id, $branchID);
        $this->data['fee_types'] = $this->fees_model->get_used_fee_types();
        $this->data['fee_structure'] = $this->fees_model->get_student_wise_fee_type_balances($class_id, $session_id, $branchID);
        $this->data['student_received'] = $this->fees_model->get_student_wise_received_amount($class_id, $session_id, $branchID);
        $this->data['student_assigned'] = $this->fees_model->get_student_wise_assigned_amount($class_id, $session_id, $branchID);
        $this->data['transportFees'] = $this->fees_model->get_student_wise_transport_balances($class_id, $session_id, $branchID);
        $this->data['studentSetAmounts'] = array();
        $this->data['sets'] = $this->Inventory_model->get_all_sets();
        $this->data['student_last_balance'] = $this->fees_model->get_student_wise_last_balance($class_id, $session_id, $branchID);
        $this->data['branch_id'] = $branchID;

        $this->data['title'] = 'Fee Estimation - ' . $class['name'];
        $this->data['sub_page'] = 'fees_structure/fees_estimation_class_students';
        $this->data['main_menu'] = 'fees_structure';

        $this->load->view('layout/index', $this->data);
    }
}
