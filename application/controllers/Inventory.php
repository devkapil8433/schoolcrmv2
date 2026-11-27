<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Inventory extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('datatables');
        $this->load->model('inventory_model');
        if (!moduleIsEnabled('inventory')) {
            access_denied();
        }
    }

    public function index()
    {
        $this->product();
    }

    /* product form validation rules */
    protected function product_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $this->form_validation->set_rules('product_name', translate('product') . " " . translate('name'), 'trim|required');
        $this->form_validation->set_rules('product_code', translate('product') . " " . translate('code'), 'trim|required');
        $this->form_validation->set_rules('product_category', translate('product') . " " . translate('category'), 'trim|required');
        $this->form_validation->set_rules('purchase_unit', translate('purchase_unit'), 'trim|required|numeric');
        $this->form_validation->set_rules('sales_unit', translate('sales_unit'), 'trim|required|numeric');
        $this->form_validation->set_rules('unit_ratio', translate('unit_ratio'), 'trim|required|numeric');
        $this->form_validation->set_rules('purchase_price', translate('purchase_price'), 'trim|required|numeric');
        $this->form_validation->set_rules('sales_price', translate('sales_price'), 'trim|required|numeric');
    }

    // add new product
    public function product()
    {
        // check access permission
        if (!get_permission('product', 'is_view')) {
            access_denied();
        }
        if ($_POST) {
            if (!get_permission('product', 'is_add')) {
                ajax_access_denied();
            }
            $this->product_validation();
            if ($this->form_validation->run() == true) {
                // save product information in the database
                $post = $this->input->post();
                $this->inventory_model->save_product($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = array('status' => 'success');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit;
        }

        $branchID = $this->application_model->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['productlist'] = $this->inventory_model->get_product_list();
        $this->data['unitlist'] = $this->app_lib->getSelectByBranch('product_unit', $branchID);
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/product';
        $this->data['main_menu'] = 'inventory';
        $this->load->view('layout/index', $this->data);
    }

    // update existing product
    public function product_edit($id)
    {
        // check access permission
        if (!get_permission('product', 'is_edit')) {
            access_denied();
        }
        if ($_POST) {
            $this->product_validation();
            if ($this->form_validation->run() == true) {
                $post = $this->input->post();
                $this->inventory_model->save_product($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $array = array('status' => 'success', 'url' => base_url('inventory/product'));
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit;
        }

        $this->data['product'] = $this->app_lib->getTable('product', array('t.id' => $id), true);
        $this->data['categorylist'] = $this->app_lib->getSelectByBranch('product_category', $this->data['product']['branch_id']);
        $this->data['unitlist'] = $this->app_lib->getSelectByBranch('product_unit', $this->data['product']['branch_id']);
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/product_edit';
        $this->data['main_menu'] = 'inventory';
        $this->load->view('layout/index', $this->data);
    }

    // delete product from database
    // public function product_delete($id)
    // {
    //     // check access permission
    //     if (!get_permission('product', 'is_delete')) {
    //         access_denied();
    //     }

    //     if (!is_superadmin_loggedin()) {
    //         $this->db->where('branch_id', get_loggedin_branch_id());
    //     }
    //     $this->db->where('id', $id);
    //     $this->db->delete('product');
    // }
    public function product_delete($id)
    {
        if (!get_permission('product', 'is_delete')) {
            access_denied();
        }

        $used_in = [];

        // 🔹 Purchase
        if ($this->db->table_exists('purchase_issues_details')) {
            $count = $this->db
                ->where('product_id', $id)
                ->count_all_results('purchase_issues_details');
            if ($count > 0) {
                $used_in[] = 'Purchases';
            }
        }

        // 🔹 Sales
        if ($this->db->table_exists('sales_bill_details')) {
            $count = $this->db
                ->where('product_id', $id)
                ->count_all_results('sales_bill_details');
            if ($count > 0) {
                $used_in[] = 'Sales';
            }
        }

        // 🔹 Sets
        if ($this->db->table_exists('inventory_set_items')) {
            $count = $this->db
                ->where('product_id', $id)
                ->count_all_results('inventory_set_items');
            if ($count > 0) {
                $used_in[] = 'Sets';
            }
        }

        // 🔹 Product Issue
        if ($this->db->table_exists('product_issue_details')) {
            $count = $this->db
                ->where('product_id', $id)
                ->count_all_results('product_issue_details');
            if ($count > 0) {
                $used_in[] = 'Product Issue';
            }
        }

        // ❌ If used anywhere → block delete
        if (!empty($used_in)) {
            echo json_encode([
                'status'  => 'fail',
                'message' => 'This product cannot be deleted because it is used in: ' . implode(', ', $used_in)
            ]);
            return;
        }

        // ✅ Delete allowed
        if (!is_superadmin_loggedin()) {
            $this->db->where('branch_id', get_loggedin_branch_id());
        }

        $this->db->where('id', $id);
        $this->db->delete('product');

        echo json_encode([
            'status'  => 'success',
            'message' => 'Product deleted successfully.'
        ]);
    }
    public function product_usage($product_id)
    {
        $data = [
            'purchase' => 0,
            'sales'    => 0,
            'sets'     => 0,
        ];

        // Purchases
        $q = $this->db->query(
            "SELECT COUNT(*) AS total FROM purchase_issues_details WHERE product_id = ?",
            [$product_id]
        );
        if ($q) {
            $data['purchase'] = (int) $q->row()->total;
        }

        // Sales
        $q = $this->db->query(
            "SELECT COUNT(*) AS total FROM sales_bill_details WHERE product_id = ?",
            [$product_id]
        );
        if ($q) {
            $data['sales'] = (int) $q->row()->total;
        }

        // Sets
        $q = $this->db->query(
            "SELECT COUNT(*) AS total FROM inventory_set_items WHERE product_id = ?",
            [$product_id]
        );
        if ($q) {
            $data['sets'] = (int) $q->row()->total;
        }

        echo json_encode($data);
    }



    // add category from database
    public function category()
    {
        if (isset($_POST['category'])) {
            if (!get_permission('product_category', 'is_add')) {
                access_denied();
            }
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required');
            }
            $this->form_validation->set_rules('category_name', 'Category Name', 'trim|required|callback_unique_category');
            if ($this->form_validation->run() !== false) {
                $arrayCategory = array(
                    'name' => $this->input->post('category_name'),
                    'branch_id' => $this->application_model->get_branch_id(),
                );
                $this->db->insert('product_category', $arrayCategory);
                set_alert('success', translate('information_has_been_saved_successfully'));
                redirect(base_url('inventory/category'));
            }
        }
        $this->data['categorylist'] = $this->app_lib->getTable('product_category');
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/category';
        $this->data['main_menu'] = 'inventory';
        $this->load->view('layout/index', $this->data);
    }

    public function category_edit()
    {
        // check access permission
        if (!get_permission('product_category', 'is_edit')) {
            access_denied();
        }
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required');
        }
        $this->form_validation->set_rules('category_name', 'Category Name', 'trim|required|callback_unique_category');
        if ($this->form_validation->run() !== false) {
            $arrayCategory = array(
                'name' => $this->input->post('category_name'),
                'branch_id' => $this->application_model->get_branch_id(),
            );
            $category_id = $this->input->post('category_id');
            $this->db->where('id', $category_id);
            $this->db->update('product_category', $arrayCategory);
            set_alert('success', translate('information_has_been_updated_successfully'));
        }
        redirect(base_url('inventory/category'));
    }

    // delete category from database
    public function category_delete($id)
    {
        // check access permission
        if (!get_permission('product_category', 'is_delete')) {
            access_denied();
        }
        $this->db->where('id', $id);
        $this->db->delete('product_category');
    }

    // duplicate category name check in db
    public function unique_category($name)
    {
        $branch_id = $this->application_model->get_branch_id();
        $category_id = $this->input->post('category_id');
        if (!empty($category_id)) {
            $this->db->where_not_in('id', $category_id);
        }
        $this->db->where('name', $name);
        $this->db->where('branch_id', $branch_id);
        $query = $this->db->get('product_category');
        if ($query->num_rows() > 0) {
            if (!empty($category_id)) {
                set_alert('error', "The Category name are already used");
            } else {
                $this->form_validation->set_message("unique_category", "The %s name are already used.");
            }
            return false;
        } else {
            return true;
        }
    }

    // add new supplier member
    public function supplier()
    {
        // check access permission
        if (!get_permission('product_supplier', 'is_view')) {
            access_denied();
        }
        if ($_POST) {
            if (!get_permission('product_supplier', 'is_add')) {
                ajax_access_denied();
            }
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            }
            $this->form_validation->set_rules('supplier_name', translate('supplier_name'), 'trim|required');
            $this->form_validation->set_rules('contact_number', translate('contact_number'), 'trim|required|numeric');
            if ($this->form_validation->run() == true) {
                $post = $this->input->post();
                $this->inventory_model->save_supplier($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = array('status' => 'success');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }

        $this->data['supplierlist'] = $this->app_lib->getTable('product_supplier');
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/supplier';
        $this->data['main_menu'] = 'inventory';
        $this->load->view('layout/index', $this->data);
    }

    // update existing supplier member
    public function supplier_edit($id)
    {
        // check access permission
        if (!get_permission('product_supplier', 'is_edit')) {
            access_denied();
        }
        if ($_POST) {
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            }
            $this->form_validation->set_rules('supplier_name', translate('supplier_name'), 'trim|required');
            $this->form_validation->set_rules('contact_number', translate('contact_number'), 'trim|required');
            if ($this->form_validation->run() == true) {
                $post = $this->input->post();
                $this->inventory_model->save_supplier($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $array = array('status' => 'success', 'url' => base_url('inventory/supplier'));
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }

        $this->data['supplier'] = $this->app_lib->getTable('product_supplier', array('t.id' => $id), true);
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/supplier_edit';
        $this->data['main_menu'] = 'inventory';
        $this->load->view('layout/index', $this->data);
    }

    // delete existing supplier member
    public function supplier_delete($id)
    {
        // check access permission
        if (!get_permission('product_supplier', 'is_delete')) {
            access_denied();
        }
        if (!is_superadmin_loggedin()) {
            $this->db->where('branch_id', get_loggedin_branch_id());
        }
        $this->db->where('id', $id);
        $this->db->delete('product_supplier');
    }

    public function unit()
    {
        if (isset($_POST['unit'])) {
            if (!get_permission('product_unit', 'is_add')) {
                access_denied();
            }
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required');
            }
            $this->form_validation->set_rules('unit_name', 'Unit Name', 'trim|required|callback_unique_unit');
            if ($this->form_validation->run() !== false) {
                $arrayUnit = array(
                    'name' => $this->input->post('unit_name'),
                    'branch_id' => $this->application_model->get_branch_id(),
                );
                $this->db->insert('product_unit', $arrayUnit);
                set_alert('success', translate('information_has_been_saved_successfully'));
                redirect(base_url('inventory/unit'));
            }
        }
        $this->data['unitlist'] = $this->inventory_model->get('product_unit', '', false, true);
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/unit';
        $this->data['main_menu'] = 'inventory';
        $this->load->view('layout/index', $this->data);
    }

    public function unit_edit()
    {
        if (!get_permission('product_unit', 'is_edit')) {
            access_denied();
        }
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required');
        }
        $this->form_validation->set_rules('unit_name', 'Unit Name', 'trim|required|callback_unique_unit');
        if ($this->form_validation->run() !== false) {
            $unit_id = $this->input->post('unit_id');
            $arrayUnit = array(
                'name' => $this->input->post('unit_name'),
                'branch_id' => $this->application_model->get_branch_id(),
            );
            $this->db->where('id', $unit_id);
            $this->db->update('product_unit', $arrayUnit);
            set_alert('success', translate('information_has_been_updated_successfully'));
        }
        redirect(base_url('inventory/unit'));
    }

    public function unit_delete($id)
    {
        if (!get_permission('product_unit', 'is_delete')) {
            access_denied();
        }
        if (!is_superadmin_loggedin()) {
            $this->db->where('branch_id', get_loggedin_branch_id());
        }
        $this->db->where('id', $id);
        $this->db->delete('product_unit');
    }

    public function unitDetails()
    {
        if (get_permission('product_unit', 'is_edit')) {
            $id = $this->input->post('id');
            $this->db->where('id', $id);
            $query = $this->db->get('product_unit');
            $result = $query->row_array();
            echo json_encode($result);
        }
    }

    public function unique_unit($name)
    {
        $branchID = $this->application_model->get_branch_id();
        $unit_id = $this->input->post('unit_id');
        if (!empty($unit_id)) {
            $this->db->where_not_in('id', $unit_id);
        }
        $this->db->where(array('name' => $name, 'branch_id' => $branchID));
        $uniform_row = $this->db->get('student_category')->num_rows();
        if ($uniform_row == 0) {
            return true;
        } else {
            $this->form_validation->set_message("unique_unit", translate('already_taken'));
            return false;
        }
    }

    // add new product purchase bill
    public function purchase()
    {
        if (!get_permission('product_purchase', 'is_view')) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['productlist'] = $this->inventory_model->getProductByBranch($branchID);
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/purchase';
        $this->data['main_menu'] = 'inventory';
        $this->load->view('layout/index', $this->data);
    }

    public function getpurchaselistDT()
    {
        if (get_permission('product_purchase', 'is_view')) {
            if ($_POST) {
                $results = $this->inventory_model->getPurchaseList();
                $results = json_decode($results);
                $data = array();
                $status_list = array(
                    '1' => translate('ordered'),
                    '2' => translate('received'),
                    '3' => translate('pending')
                );
                if (!empty($results->data)) {
                    foreach ($results->data as $key => $val) {
                        $labelMode = "";
                        $status = $val->payment_status;
                        if ($status == 1) {
                            $status = translate('unpaid');
                            $labelMode = 'label-danger-custom';
                        } elseif ($status == 2) {
                            $status = translate('partly_paid');
                            $labelMode = 'label-info-custom';
                        } elseif ($status == 3 || $val->due == 0) {
                            $status = translate('total_paid');
                            $labelMode = 'label-success-custom';
                        }
                        // action button
                        $action = "";
                        if (get_permission('purchase_payment', 'is_add')) {
                            $action .= '<a href="' . base_url('inventory/purchase_bill/' . $val->id) . '" class="btn btn-circle icon btn-default" data-toggle="tooltip" data-original-title="' . translate('bill_view') . '"> <i class="fas fa-credit-card"></i></a>';
                        }
                        if (get_permission('product_purchase', 'is_edit')) {
                            if ($val->purchase_status != 2) {
                                $action .= '<button class="btn btn-circle icon btn-default" data-toggle="tooltip" data-original-title="' . translate('make_received') . '" onclick="confirmStock(' . "'" . $val->id . "'" . ')"><i class="far fa-plus-square"></i></button>';
                            }
                            $action .= '<a href="' . base_url('inventory/purchase_edit/' . $val->id) . '" class="btn btn-circle icon btn-default" data-toggle="tooltip" data-original-title="' . translate('edit') . '"><i class="fas fa-pen-nib"></i></a>';
                        }
                        if (get_permission('product_purchase', 'is_delete')) {
                            $action .= btn_delete('inventory/purchase_delete/' . $val->id);
                        }
                        // dt-data array 
                        $row = array();
                        if (is_superadmin_loggedin()) {
                            $row[] = get_type_name_by_id('branch', $val->branch_id);
                        }
                        $row[] = $val->bill_no;
                        $row[] = $val->supplier_name;
                        $row[] = $status_list[$val->purchase_status];
                        $row[] = "<span class='label " . $labelMode . "'>" . $status . "</span>";
                        $row[] = _d($val->date);
                        $row[] = currencyFormat($val->total - $val->discount);
                        $row[] = currencyFormat($val->paid);
                        $row[] = currencyFormat($val->due);
                        $row[] = $val->remarks;
                        $row[] = $action;
                        $data[] = $row;
                    }
                }
                $json_data = array(
                    "draw"                => intval($results->draw),
                    "recordsTotal"        => intval($results->recordsTotal),
                    "recordsFiltered"     => intval($results->recordsFiltered),
                    "data"                => $data,
                );
                echo json_encode($json_data);
            }
        }
    }

    public function purchaseItems()
    {
        $branchID = $this->application_model->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['productlist'] = $this->inventory_model->getProductByBranch($branchID);
        echo $this->load->view('inventory/purchaseItems', $this->data, true);
    }

    public function getPurchasePrice()
    {
        $id = $this->input->post('id');
        $price = $this->db->select('IFNULL(purchase_price,0) as price,purchase_unit_id')->where('id', $id)->get('product')->row_array();
        $unit = $this->db->select('name')->where('id', $price['purchase_unit_id'])->get('product_unit')->row();
        echo json_encode(['price' => $price['price'], 'unit' => $unit->name]);
    }

    /* purchase form validation rules */
    protected function purchase_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $this->form_validation->set_rules('supplier_id', 'Supplier', 'trim|required');
        $this->form_validation->set_rules('bill_no', 'Bill No', 'trim|required');
        $this->form_validation->set_rules('purchase_status', 'Purchase Status', 'trim|required');
        $this->form_validation->set_rules('date', 'Date', 'trim|required');
        $items = $this->input->post('purchases');
        if (!empty($items)) {
            foreach ($items as $key => $value) {
                $this->form_validation->set_rules('purchases[' . $key . '][product]', 'Product', 'trim|required');
                $this->form_validation->set_rules('purchases[' . $key . '][quantity]', 'Quantity', 'trim|required');
            }
        }
    }

    public function purchase_save()
    {
        if (!get_permission('product_purchase', 'is_add')) {
            access_denied();
        }
        if ($_POST) {
            $this->purchase_validation();
            if ($this->form_validation->run() == false) {
                $msg = array(
                    'supplierID' => form_error('supplier_id'),
                    'bill_no' => form_error('bill_no'),
                    'purchase_status' => form_error('purchase_status'),
                    'date' => form_error('date'),
                    'delivery_time' => form_error('delivery_time'),
                    'payment_amount' => form_error('payment_amount'),
                );
                if (is_superadmin_loggedin()) {
                    $msg['branch_id'] = form_error('branch_id');
                }
                $items = $this->input->post('purchases');
                if (!empty($items)) {
                    foreach ($items as $key => $value) {
                        $msg['product' . $key] = form_error('purchases[' . $key . '][product]');
                        $msg['quantity' . $key] = form_error('purchases[' . $key . '][quantity]');
                    }
                }
                $array = array('status' => 'fail', 'url' => '', 'error' => $msg);
            } else {
                $data = $this->input->post();
                $this->inventory_model->save_purchase($data);
                $url = base_url('inventory/purchase');
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = array('status' => 'success', 'url' => $url, 'error' => '');
            }
            echo json_encode($array);
        }
    }

    public function purchaseMakeReceived($id = '')
    {
        if (!get_permission('product_purchase', 'is_eit')) {
            access_denied();
        }
        if (!empty($id)) {
            $r = $this->db->select('count(id) as cid')->where(['id' => $id, 'purchase_status !=' => 2])->get('purchase_bill')->row()->cid;
            if ($r > 0) {
                $bill_details = $this->db->where('purchase_bill_id', $id)->get('purchase_bill_details')->result();
                foreach ($bill_details as $key => $value) {
                    $unit_ratio = $this->db->select('unit_ratio')->where('id', $value->product_id)->get('product')->row()->unit_ratio;
                    $sql = "UPDATE `product` SET `available_stock` = `available_stock` + " . ($value->quantity * $unit_ratio) . " WHERE `id` = " . $this->db->escape($value->product_id);
                    $this->db->query($sql);
                }
                $this->db->where('id', $id);
                $this->db->update('purchase_bill', ['purchase_status' => 2]);
            }
        }
    }

    public function purchase_edit_save()
    {
        if (!get_permission('product_purchase', 'is_edit')) {
            access_denied();
        }
        if ($_POST) {
            // validate inputs
            $this->form_validation->set_rules('supplier_id', 'Supplier', 'trim|required');
            $this->form_validation->set_rules('bill_no', 'Bill No', 'trim|required');
            $this->form_validation->set_rules('purchase_status', 'Purchase Status', 'trim|required');
            $this->form_validation->set_rules('date', 'Date', 'trim|required');
            $items = $this->input->post('purchases');
            foreach ($items as $key => $value) {
                $this->form_validation->set_rules('purchases[' . $key . '][product]', 'Product', 'trim|required');
                $this->form_validation->set_rules('purchases[' . $key . '][quantity]', 'Quantity', 'trim|required');
            }
            if ($this->form_validation->run() == false) {
                $msg = array(
                    'supplierID' => form_error('supplier_id'),
                    'bill_no' => form_error('bill_no'),
                    'purchase_status' => form_error('purchase_status'),
                    'date' => form_error('date'),
                    'delivery_time' => form_error('delivery_time'),
                    'payment_amount' => form_error('payment_amount'),
                );
                foreach ($items as $key => $value) {
                    $msg['product' . $key] = form_error('purchases[' . $key . '][product]');
                    $msg['quantity' . $key] = form_error('purchases[' . $key . '][quantity]');
                }
                $array = array('status' => 'fail', 'url' => '', 'error' => $msg);
            } else {

                $purchase_bill_id = $this->input->post('purchase_bill_id');
                $supplier_id = $this->input->post('supplier_id');
                $bill_no = $this->input->post('bill_no');
                $purchase_status = $this->input->post('purchase_status');
                $grand_total = $this->input->post('grand_total');
                $discount = $this->input->post('total_discount');
                $purchase_paid = $this->input->post('purchase_paid');
                $net_total = $this->input->post('net_grand_total');
                $date = $this->input->post('date');
                $remarks = $this->input->post('remarks');
                if ($net_total <= $purchase_paid) {
                    $payment_status = 3;
                } else {
                    $payment_status = 2;
                }
                $array_invoice = array(
                    'supplier_id' => $supplier_id,
                    'store_id' => null,
                    'bill_no' => $bill_no,
                    'remarks' => $remarks,
                    'total' => $grand_total,
                    'discount' => $discount,
                    'due' => ($net_total - $purchase_paid),
                    'purchase_status' => $purchase_status,
                    'payment_status' => $payment_status,
                    'date' => date('Y-m-d', strtotime($date)),
                    'modifier_id' => get_loggedin_user_id(),
                );
                $this->db->where('id', $purchase_bill_id);
                $this->db->update('purchase_bill', $array_invoice);

                $purchases = $this->input->post('purchases');
                foreach ($purchases as $key => $value) {
                    $array_product = array(
                        'purchase_bill_id' => $purchase_bill_id,
                        'product_id' => $value['product'],
                        'unit_price' => $value['unit_price'],
                        'discount' => $value['discount'],
                        'quantity' => $value['quantity'],
                        'sub_total' => $value['sub_total'],
                    );

                    if (isset($value['old_product_id'])) {
                        if ($value['old_product_id'] == $value['product']) {
                            $unit_ratio = $this->db->select('unit_ratio')->where('id', $value['old_product_id'])->get('product')->row()->unit_ratio;
                            if (isset($value['old_quantity'])) {
                                if ($value['quantity'] >= $value['old_quantity']) {
                                    $stock = floatval(($value['quantity'] * $unit_ratio) - ($value['old_quantity'] * $unit_ratio));
                                    $this->inventory_model->stock_upgrade($stock, $value['product']);
                                } else {
                                    $stock = floatval(($value['old_quantity'] * $unit_ratio) - ($value['quantity'] * $unit_ratio));
                                    $this->inventory_model->stock_upgrade($stock, $value['product'], false);
                                }
                            }
                        } else {
                            $unit_ratio = $this->db->select('unit_ratio')->where('id', $value['old_product_id'])->get('product')->row()->unit_ratio;
                            $newunit_ratio = $this->db->select('unit_ratio')->where('id', $value['product'])->get('product')->row()->unit_ratio;
                            $this->inventory_model->stock_upgrade(($value['old_quantity'] * $unit_ratio), $value['old_product_id'], false);
                            $this->inventory_model->stock_upgrade(($value['quantity'] * $newunit_ratio), $value['product']);
                        }
                    }

                    if (isset($value['old_bill_details_id'])) {
                        $this->db->where('id', $value['old_bill_details_id']);
                        $this->db->update('purchase_bill_details', $array_product);
                    } else {
                        $unit_ratio = $this->db->select('unit_ratio')->where('id', $value['product'])->get('product')->row()->unit_ratio;
                        $this->inventory_model->stock_upgrade(($value['quantity'] * $unit_ratio), $value['product']);
                        $this->db->insert('purchase_bill_details', $array_product);
                    }
                }
                $url = base_url('inventory/purchase');
                set_alert('success', translate('information_has_been_updated_successfully'));
                $array = array('status' => 'success', 'url' => $url, 'error' => '');
            }
            echo json_encode($array);
        }
    }

    // update existing product purchase bill
    public function purchase_edit($id)
    {
        if (!get_permission('product_purchase', 'is_edit')) {
            access_denied();
        }

        $this->data['purchaselist'] = $this->app_lib->getTable('purchase_bill', array('t.id' => $id), true);
        $branchID = $this->data['purchaselist']['branch_id'];
        $this->data['branch_id'] = $branchID;
        $this->data['productlist'] = $this->inventory_model->getProductByBranch($branchID);
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/purchase_edit';
        $this->data['main_menu'] = 'inventory';
        $this->load->view('layout/index', $this->data);
    }

    // delete product purchase bill from database
    public function purchase_delete($id)
    {
        if (!get_permission('product_purchase', 'is_delete')) {
            access_denied();
        }

        $getStock = $this->db->get_where('purchase_bill_details', array('purchase_bill_id' => $id))->result();
        foreach ($getStock as $key => $value) {
            $unit_ratio = $this->db->select('unit_ratio')->where('id', $value->product_id)->get('product')->row()->unit_ratio;
            $this->inventory_model->stock_upgrade(($value->quantity * $unit_ratio), $value->product_id, false);
        }

        $this->db->where('id', $id);
        $this->db->delete('purchase_bill');

        $this->db->where('purchase_bill_id', $id);
        $this->db->delete('purchase_bill_details');

        //delete purchase payment history from database
        $this->db->where('purchase_bill_id', $id);
        $this->db->delete('purchase_payment_history');
    }

    public function purchase_bill($id = '')
    {
        if (!get_permission('purchase_payment', 'is_add')) {
            access_denied();
        }
        $this->data['billdata'] = $this->inventory_model->get_invoice($id);
        if (empty($this->data['billdata'])) {
            access_denied();
        }
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/dropify/css/dropify.min.css',
            ),
            'js' => array(
                'vendor/dropify/js/dropify.min.js',
            ),
        );
        $this->data['payvia_list'] = $this->app_lib->getSelectList('payment_types');
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/purchase_bill';
        $this->data['main_menu'] = 'inventory';
        $this->load->view('layout/index', $this->data);
    }

    // purchase partially payment add
    public function add_payment()
    {
        if (!get_permission('purchase_payment', 'is_add')) {
            access_denied();
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            $data['getbill'] = $this->db->select('id,due')->where('id', $data['purchase_bill_id'])->get('purchase_bill')->row_array();
            $this->form_validation->set_rules('paid_date', 'Paid Date', 'trim|required');
            $this->form_validation->set_rules('payment_amount', 'Payment Amount', 'trim|required|numeric|greater_than[1]|callback_payment_validation');
            $this->form_validation->set_rules('pay_via', 'Pay Via', 'trim|required');
            $this->form_validation->set_rules('attach_document', translate('attach_document'), 'callback_fileHandleUpload[attach_document]');
            if ($this->form_validation->run() !== false) {
                $this->inventory_model->save_payment($data);
                set_alert('success', translate('payment_successfull'));
                if (get_permission('purchase_payment', 'is_view')) {
                    $this->session->set_flashdata('active_tab', 2);
                }
                $array = array('status' => 'success');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
        }
    }

    // payment amount validation
    public function payment_validation($amount)
    {
        $bill_id = $this->input->post('purchase_bill_id');
        $due_amount = $this->db->select('due')->where('id', $bill_id)->get('purchase_bill')->row()->due;
        if ($amount <= $due_amount) {
            return true;
        } else {
            $this->form_validation->set_message("payment_validation", "Payment Amount Is More Than The Due Amount.");
            return false;
        }
    }

    /* store form validation rules */
    protected function store_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $this->form_validation->set_rules('store_name', translate('name'), 'trim|required');
        $this->form_validation->set_rules('store_code', translate('store_code'), 'trim|required');
        $this->form_validation->set_rules('mobileno', translate('mobile_no'), 'trim|required|numeric');
    }

    /* add new store member */
    public function store()
    {
        // check access permission
        if (!get_permission('product_store', 'is_view')) {
            access_denied();
        }
        if ($_POST) {
            if (!get_permission('product_store', 'is_add')) {
                ajax_access_denied();
            }
            $this->store_validation();
            if ($this->form_validation->run() == true) {
                $post = $this->input->post();
                $this->inventory_model->save_store($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = array('status' => 'success');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }

        $this->data['storelist'] = $this->app_lib->getTable('product_store');
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/store';
        $this->data['main_menu'] = 'inventory';
        $this->load->view('layout/index', $this->data);
    }

    // update existing store member
    public function store_edit($id)
    {
        // check access permission
        if (!get_permission('product_store', 'is_edit')) {
            access_denied();
        }
        if ($_POST) {
            $this->store_validation();
            if ($this->form_validation->run() == true) {
                $post = $this->input->post();
                $this->inventory_model->save_store($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $array = array('status' => 'success', 'url' => base_url('inventory/store'));
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
            exit();
        }

        $this->data['store'] = $this->app_lib->getTable('product_store', array('t.id' => $id), true);
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/store_edit';
        $this->data['main_menu'] = 'inventory';
        $this->load->view('layout/index', $this->data);
    }

    // delete existing store
    public function store_delete($id)
    {
        // check access permission
        if (!get_permission('product_store', 'is_delete')) {
            access_denied();
        }
        if (!is_superadmin_loggedin()) {
            $this->db->where('branch_id', get_loggedin_branch_id());
        }
        $this->db->where('id', $id);
        $this->db->delete('product_store');
    }

    /* sales form validation rules */
    protected function sales_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $this->form_validation->set_rules('role_id', translate('role'), 'trim|required');
        $this->form_validation->set_rules('sale_to', translate('sale_to'), 'trim|required');
        $this->form_validation->set_rules('date', translate('date'), 'trim|required');
        $this->form_validation->set_rules('bill_no', translate('bill_no'), 'trim|required|numeric');
        $this->form_validation->set_rules('payment_amount', translate('payment_amount'), 'trim|numeric|callback_sales_amount');
        $payment_amount = $this->input->post('payment_amount');
        if (!empty($payment_amount)) {
            $this->form_validation->set_rules('pay_via', translate('pay_via'), 'trim|required');
        }
        $items = $this->input->post('sales');
        if (!empty($items)) {
            foreach ($items as $key => $value) {
                $this->form_validation->set_rules('sales[' . $key . '][category]', translate('category'), 'trim|required');
                $this->form_validation->set_rules('sales[' . $key . '][product]', translate('product'), 'trim|required');
                $this->form_validation->set_rules('sales[' . $key . '][quantity]', translate('quantity'), 'trim|required');
            }
        }
    }

    public function sales()
    {
        if (!get_permission('product_sales', 'is_view')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        $this->load->model('inventory_model');
        $this->data['sets'] = $this->inventory_model->get_all_sets();
        $this->data['branch_id'] = $branchID;
        $this->data['categorylist'] = $this->app_lib->getSelectByBranch('product_category', $branchID);
        $this->data['payvia_list'] = $this->app_lib->getSelectList('payment_types');
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/sales';
        $this->data['main_menu'] = 'inventory';
        $this->load->view('layout/index', $this->data);
    }
    public function get_set_items()
    {
        $set_id = $this->input->post('set_id');

        $items = $this->db
            ->select('isi.*, p.name as product_name, p.category_id')
            ->from('inventory_set_items isi')
            ->join('product p', 'p.id = isi.product_id')
            ->where('isi.set_id', $set_id)
            ->get()
            ->result_array();

        echo json_encode($items);
    }
    public function get_sets_by_class()
    {
        $class_id  = $this->input->post('class_id');
        $branch_id = $this->input->post('branch_id');

        if (empty($class_id)) {
            echo json_encode([]);
            return;
        }

        $sets = $this->inventory_model->get_sets_by_class($class_id, $branch_id);

        echo json_encode($sets);
    }


    public function set()
    {
        if (!get_permission('product_set', 'is_view')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['categorylist'] = $this->app_lib->getSelectByBranch('product_category', $branchID);
        $this->data['payvia_list'] = $this->app_lib->getSelectList('payment_types');
        $this->data['setlist'] = $this->inventory_model->getSetList();
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/set';
        $this->data['main_menu'] = 'inventory';
        $this->load->view('layout/index', $this->data);
    }
    public function save_set()
    {
        $post = $this->input->post();

        // 1️⃣ save set master
        $setData = [
            'branch_id' => $post['branch_id'],
            'class_id'  => $post['class_id'],
            'set_name'  => $post['set_name'],
        ];

        $this->db->insert('inventory_sets', $setData);
        $set_id = $this->db->insert_id();

        // 2️⃣ save set items
        if (!empty($post['sales'])) {
            foreach ($post['sales'] as $row) {

                if (empty($row['product'])) continue;

                $itemData = [
                    'set_id'      => $set_id,
                    'category_id' => $row['category'],
                    'product_id'  => $row['product'],
                    'unit_price'  => $row['unit_price'],
                    'quantity'    => $row['quantity'],
                    'discount'    => $row['discount'],
                    'total_price' => $row['sub_total'],
                ];

                $this->db->insert('inventory_set_items', $itemData);
            }
        }

        echo json_encode([
            'status' => 'success',
            'url' => base_url('inventory/set')
        ]);
    }
    public function delete_set($id)
    {
        if (empty($id)) {
            echo json_encode([
                'status' => 'fail',
                'message' => 'Invalid Set ID'
            ]);
            return;
        }

        $this->db->trans_start();

        // 1️⃣ delete set items first
        $this->db->where('set_id', $id)->delete('inventory_set_items');

        // 2️⃣ delete set master
        $this->db->where('id', $id)->delete('inventory_sets');

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode([
                'status' => 'fail',
                'message' => 'Failed to delete set'
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'message' => 'Set deleted successfully'
            ]);
        }
    }
    public function set_edit($id)
    {
        if (!get_permission('product_set', 'is_edit')) {
            access_denied();
        }

        $set = $this->db->where('id', $id)->get('inventory_sets')->row_array();
        if (!$set) {
            show_404();
        }

        $branchID = $set['branch_id'];

        $this->data['set'] = $set;

        $this->data['set_items'] = $this->db
            ->where('set_id', $id)
            ->get('inventory_set_items')
            ->result_array();

        // dropdown data
        $this->data['branch_id']    = $branchID;
        $this->data['branchlist']   = $this->app_lib->getSelectList('branch');
        $this->data['classlist']    = $this->app_lib->getClass($branchID);
        $this->data['categorylist'] = $this->app_lib->getSelectByBranch('product_category', $branchID);

        $this->data['title']     = translate('inventory');
        $this->data['sub_page']  = 'inventory/set_edit';
        $this->data['main_menu'] = 'inventory';

        $this->load->view('layout/index', $this->data);
    }


    public function update_set($set_id)
    {
        $post = $this->input->post();

        if (empty($set_id)) {
            echo json_encode([
                'status' => 'fail',
                'message' => 'Invalid set ID'
            ]);
            return;
        }

        // 1️⃣ update set master
        $setData = [
            'branch_id' => $post['branch_id'],
            'class_id'  => $post['class_id'],
            'set_name'  => $post['set_name'],
        ];

        $this->db->where('id', $set_id);
        $this->db->update('inventory_sets', $setData);

        // 2️⃣ delete old set items
        $this->db->where('set_id', $set_id);
        $this->db->delete('inventory_set_items');

        // 3️⃣ insert updated set items
        if (!empty($post['sales'])) {
            foreach ($post['sales'] as $row) {

                if (empty($row['product'])) {
                    continue;
                }

                $itemData = [
                    'set_id'      => $set_id,
                    'category_id' => $row['category'],
                    'product_id'  => $row['product'],
                    'unit_price'  => $row['unit_price'],
                    'quantity'    => $row['quantity'],
                    'discount'    => $row['discount'],
                    'total_price' => $row['sub_total'], // same as save_set
                ];

                $this->db->insert('inventory_set_items', $itemData);
            }
        }

        echo json_encode([
            'status' => 'success',
            'url' => base_url('inventory/set')
        ]);
    }
    public function getSetListDT()
{
    if (!get_permission('product_set', 'is_view')) {
        access_denied();
    }

    $list = $this->inventory_model->getSetList();

    echo json_encode([
        "data" => $list
    ]);
}





    public function getSaleslistDT()
    {
        if (get_permission('product_sales', 'is_view')) {
            if ($_POST) {
                $results = $this->inventory_model->getSalesListDT();
                $results = json_decode($results);
                $data = array();
                if (!empty($results->data)) {
                    foreach ($results->data as $key => $val) {
                        $labelMode = "";
                        $status = $val->payment_status;
                        if ($status == 1) {
                            $status = translate('unpaid');
                            $labelMode = 'label-danger-custom';
                        } elseif ($status == 2) {
                            $status = translate('partly_paid');
                            $labelMode = 'label-info-custom';
                        } elseif ($status == 3 || $val->due == 0) {
                            $status = translate('total_paid');
                            $labelMode = 'label-success-custom';
                        }
                        // action btn
                        $action = '<a href="' . base_url('inventory/sales_invoice/' . $val->id) . '" class="btn btn-circle icon btn-default" data-toggle="tooltip" data-original-title="' . translate('bill_view') . '"> <i class="fas fa-credit-card"></i></a>';
                        // 🔹 EDIT BUTTON
if (get_permission('product_sales', 'is_edit')) {
    $action .= '<a href="' . base_url('inventory/sales_edit/' . $val->id) . '" 
    class="btn btn-circle icon btn-primary" 
    data-toggle="tooltip" 
    data-original-title="' . translate('edit') . '">
    <i class="fas fa-edit"></i>
    </a>';
}
                        if (get_permission('product_sales', 'is_delete')) {
                            $action .= btn_delete('inventory/sales_delete/' . $val->id);
                        }
                        // dt-data array 
                        $row = array();
                        if (is_superadmin_loggedin()) {
                            $row[] = get_type_name_by_id('branch', $val->branch_id);
                        }
                        $row[] = $val->bill_no;
                        $row[] = $val->role_name;
                        $row[] = $this->application_model->getUserNameByRoleID($val->role_id, $val->user_id)['name'];
                        $row[] = "<span class='label " . $labelMode . "'>" . $status . "</span>";
                        $row[] = _d($val->date);
                        $row[] = currencyFormat($val->total - $val->discount);
                        $row[] = currencyFormat($val->paid);
                        $row[] = currencyFormat($val->due);
                        $row[] = $val->remarks;
                        $row[] = $action;
                        $data[] = $row;
                    }
                }
                $json_data = array(
                    "draw"                => intval($results->draw),
                    "recordsTotal"        => intval($results->recordsTotal),
                    "recordsFiltered"     => intval($results->recordsFiltered),
                    "data"                => $data,
                );
                echo json_encode($json_data);
            }
        }
    }

    // public function sales_save()
    // {
    //     if (!get_permission('product_sales', 'is_add')) {
    //         access_denied();
    //     }
    //     if ($_POST) {
    //         $this->sales_validation();
    //         if ($this->form_validation->run() == false) {
    //             $msg = array(
    //                 'bill_no' => form_error('bill_no'),
    //                 'payment_amount' => form_error('payment_amount'),
    //                 'pay_via' => form_error('pay_via'),
    //                 'roleID' => form_error('role_id'),
    //                 'receiverID' => form_error('sale_to'),
    //                 'date' => form_error('date'),
    //             );
    //             if (is_superadmin_loggedin()) {
    //                 $msg['branchID'] = form_error('branch_id');
    //             }
    //             $items = $this->input->post('sales');
    //             if (!empty($items)) {
    //                 foreach ($items as $key => $value) {
    //                     $msg['category' . $key] = form_error('sales[' . $key . '][category]');
    //                     $msg['product' . $key] = form_error('sales[' . $key . '][product]');
    //                     $msg['quantity' . $key] = form_error('sales[' . $key . '][quantity]');
    //                 }
    //             }
    //             $array = array('status' => 'fail', 'url' => '', 'error' => $msg);
    //         } else {
    //             $data = $this->input->post();
    //             $this->inventory_model->save_sales($data);
    //             $url = base_url('inventory/sales');
    //             set_alert('success', translate('information_has_been_saved_successfully'));
    //             $array = array('status' => 'success', 'url' => $url, 'error' => '');
    //         }
    //         echo json_encode($array);
    //     }
    // }
    public function sales_save()
{
    if (!get_permission('product_sales', 'is_add')) {
        access_denied();
    }

    if ($_POST) {

        $this->sales_validation();

        if ($this->form_validation->run() == false) {

            $msg = array(
                'bill_no' => form_error('bill_no'),
                'payment_amount' => form_error('payment_amount'),
                'pay_via' => form_error('pay_via'),
                'roleID' => form_error('role_id'),
                'receiverID' => form_error('sale_to'),
                'date' => form_error('date'),
            );

            if (is_superadmin_loggedin()) {
                $msg['branchID'] = form_error('branch_id');
            }

            $items = $this->input->post('sales');
            if (!empty($items)) {
                foreach ($items as $key => $value) {
                    $msg['category' . $key] = form_error('sales[' . $key . '][category]');
                    $msg['product' . $key] = form_error('sales[' . $key . '][product]');
                    $msg['quantity' . $key] = form_error('sales[' . $key . '][quantity]');
                }
            }

            echo json_encode([
                'status' => 'fail',
                'url'    => '',
                'error'  => $msg
            ]);
            return;
        }

        // ===============================
        // STOCK & QUANTITY VALIDATION
        // ===============================
        $data  = $this->input->post();
        $items = $data['sales'];

        foreach ($items as $key => $item) {

            if (empty($item['product'])) {
                continue;
            }

            // ❌ Quantity must be greater than 0
            if ((int)$item['quantity'] <= 0) {
                echo json_encode([
                    'status' => 'fail',
                    'error'  => [
                        'quantity' . $key => 'Quantity must be greater than 0'
                    ]
                ]);
                return;
            }

            // 🔍 Check available stock
            $product = $this->db
                ->select('available_stock')
                ->where('id', $item['product'])
                ->get('product')
                ->row();

            // ❌ Product not found or insufficient stock
            if (!$product || $product->available_stock < $item['quantity']) {
                echo json_encode([
                    'status' => 'fail',
                    'error'  => [
                        'product' . $key => 'Insufficient stock for this product'
                    ]
                ]);
                return;
            }
        }

        // ===============================
        // SAVE SALE
        // ===============================
        $this->inventory_model->save_sales($data);

        $url = base_url('inventory/sales');
        set_alert('success', translate('information_has_been_saved_successfully'));

        echo json_encode([
            'status' => 'success',
            'url'    => $url,
            'error'  => ''
        ]);
    }
}
public function sales_edit($id)
{
    // 🔹 Sale main data
    $data['sale'] = $this->db
        ->where('id', $id)
        ->get('sales_bill')
        ->row();

    $this->load->view('inventory/sales_edit', $data);
}



    public function getSaleprice()
    {
        $id = $this->input->post('id');
        $price = $this->db->select('IFNULL(sales_price,0) as salesprice,available_stock,sales_unit_id')->where('id', $id)->get('product')->row_array();
        $unit = $this->db->select('name')->where('id', $price['sales_unit_id'])->get('product_unit')->row();
        echo json_encode(['price' => $price['salesprice'], 'unit' => $unit->name, 'availablestock' => translate('available_stock_quantity') . " : " . $price['available_stock']]);
    }

    public function saleItems()
    {
        $branchID = $this->application_model->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['categorylist'] = $this->app_lib->getSelectByBranch('product_category', $branchID);
        echo $this->load->view('inventory/saleItems', $this->data, true);
    }

    public function getProductByCategory()
    {
        $category_id = $this->input->post('category_id');
        $selected_id = $this->input->post('selected_id');
        $branchID = $this->application_model->get_branch_id();
        $productlist = $this->db->select('id,name,code')->where(['branch_id' => $branchID, 'category_id' => $category_id])->get('product')->result_array();
        $html = "<option value=''>" . translate('select') . "</option>";
        foreach ($productlist as $product) {
            $selected = ($product['id'] == $selected_id ? 'selected' : '');
            $html .= "<option value='" . $product['id'] . "' " . $selected . ">" . $product['name'] . " (" . $product['code'] . ")</option>";
        }
        echo $html;
    }

    // check valid received amount
    public function sales_amount($amount)
    {
        if (!empty($amount)) {
            $net_payable = $this->input->post('net_payable_amount');
            if ($net_payable < $amount) {
                $this->form_validation->set_message('sales_amount', "Invalid Received Amount.");
                return false;
            }
        }
        return true;
    }

    public function sales_invoice($id = '')
    {
        if (!get_permission('product_sales', 'is_view')) {
            access_denied();
        }
        $this->data['billdata'] = $this->inventory_model->getSalesInvoice($id);
        if (empty($this->data['billdata'])) {
            access_denied();
        }
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/dropify/css/dropify.min.css',
            ),
            'js' => array(
                'vendor/dropify/js/dropify.min.js',
            ),
        );
        $this->data['payvia_list'] = $this->app_lib->getSelectList('payment_types');
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/sales_invoice';
        $this->data['main_menu'] = 'inventory';
        $this->load->view('layout/index', $this->data);
    }

    // sales partially payment add
    public function add_sales_payment()
    {
        if (!get_permission('sales_payment', 'is_add')) {
            access_denied();
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            $data['getbill'] = $this->db->select('id,due')->where('id', $data['sales_bill_id'])->get('sales_bill')->row_array();
            $this->form_validation->set_rules('paid_date', 'Paid Date', 'trim|required');
            $this->form_validation->set_rules('payment_amount', 'Payment Amount', 'trim|required|numeric|greater_than[1]|callback_sales_amount_validation');
            $this->form_validation->set_rules('pay_via', 'Pay Via', 'trim|required');
            $this->form_validation->set_rules('attach_document', translate('attach_document'), 'callback_fileHandleUpload[attach_document]');
            if ($this->form_validation->run() !== false) {
                $this->inventory_model->save_sales_payment($data);
                set_alert('success', translate('payment_successfull'));
                if (get_permission('purchase_payment', 'is_view')) {
                    $this->session->set_flashdata('active_tab', 2);
                }
                $array = array('status' => 'success');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
        }
    }

    // payment amount validation
    public function sales_amount_validation($amount)
    {
        $bill_id = $this->input->post('sales_bill_id');
        $due_amount = $this->db->select('due')->where('id', $bill_id)->get('sales_bill')->row()->due;
        if ($amount <= $due_amount) {
            return true;
        } else {
            $this->form_validation->set_message("sales_amount_validation", "Payment Amount Is More Than The Due Amount.");
            return false;
        }
    }

    // delete product sales bill from database
    public function sales_delete($id)
    {
        if (!get_permission('product_sales', 'is_delete')) {
            access_denied();
        }
        $getStock = $this->db->get_where('sales_bill_details', array('sales_bill_id' => $id))->result();
        foreach ($getStock as $key => $value) {
            $this->inventory_model->stock_upgrade(($value->quantity), $value->product_id);
        }

        $this->db->where('id', $id);
        $this->db->delete('sales_bill');

        $this->db->where('sales_bill_id', $id);
        $this->db->delete('sales_bill_details');

        $this->db->where('sales_bill_id', $id);
        $this->db->delete('sales_bill_details');
    }

    /* issue form validation rules */
    protected function issue_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $this->form_validation->set_rules('role_id', translate('role'), 'trim|required');
        $this->form_validation->set_rules('sale_to', translate('sale_to'), 'trim|required');
        $this->form_validation->set_rules('date_of_issue', translate('date_of_issue'), 'trim|required');
        $this->form_validation->set_rules('due_date', translate('due_date'), 'trim|required');
        $items = $this->input->post('sales');
        if (!empty($items)) {
            foreach ($items as $key => $value) {
                $this->form_validation->set_rules('sales[' . $key . '][category]', translate('category'), 'trim|required');
                $this->form_validation->set_rules('sales[' . $key . '][product]', translate('product'), 'trim|required');
                $this->form_validation->set_rules('sales[' . $key . '][quantity]', translate('quantity'), 'trim|required');
            }
        }
    }

    public function issue()
    {
        if (!get_permission('product_issue', 'is_view')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['categorylist'] = $this->app_lib->getSelectByBranch('product_category', $branchID);
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/issue';
        $this->data['main_menu'] = 'inventory';
        $this->load->view('layout/index', $this->data);
    }

    public function getIssuelistDT()
    {
        if (get_permission('product_issue', 'is_view')) {
            if ($_POST) {
                $results = $this->inventory_model->getIssueListDT();
                $results = json_decode($results);
                $data = array();
                if (!empty($results->data)) {
                    foreach ($results->data as $key => $val) {
                        // action btn
                        $action = '<button class="btn btn-default btn-circle icon" data-toggle="tooltip" data-original-title="' . translate('details') . '" data-loading-text="<i class=\'fas fa-spinner fa-spin\'></i>" onclick="getIssueDetails(' . "'" . $val->id . "'" . ', this)"><i class="fas fa-bars"></i></button>';
                        if (get_permission('product_issue', 'is_delete')) {
                            $action .= btn_delete('inventory/issue_delete/' . $val->id);
                        }
                        // dt-data array 
                        $row = array();
                        if (is_superadmin_loggedin()) {
                            $row[] = get_type_name_by_id('branch', $val->branch_id);
                        }
                        $row[] = $val->role_name;
                        if ($val->role_id == 7) {
                            $row[] = $val->stu_name;
                            $row[] = $val->stu_mobileno;
                        } else {
                            $row[] = $val->issuer_sn;
                            $row[] = $val->staff_mobileno;
                        }
                        $row[] = _d($val->date_of_issue);
                        $row[] = _d($val->due_date);
                        $row[] = ($val->status == 0 ? '<span class="label label-danger-custom">' . translate('not_returned') . '</span>' : _d($val->return_date));
                        $row[] = $val->staff_name;
                        $row[] = $action;
                        $data[] = $row;
                    }
                }
                $json_data = array(
                    "draw"                => intval($results->draw),
                    "recordsTotal"        => intval($results->recordsTotal),
                    "recordsFiltered"     => intval($results->recordsFiltered),
                    "data"                => $data,
                );
                echo json_encode($json_data);
            }
        }
    }

    public function issue_save()
    {
        if (!get_permission('product_issue', 'is_add')) {
            access_denied();
        }
        if ($_POST) {
            $this->issue_validation();
            if ($this->form_validation->run() == false) {
                $msg = array(
                    'date_of_issue' => form_error('date_of_issue'),
                    'due_date' => form_error('due_date'),
                    'roleID' => form_error('role_id'),
                    'receiverID' => form_error('sale_to'),
                );
                if (is_superadmin_loggedin()) {
                    $msg['branchID'] = form_error('branch_id');
                }
                $items = $this->input->post('sales');
                if (!empty($items)) {
                    foreach ($items as $key => $value) {
                        $msg['category' . $key] = form_error('sales[' . $key . '][category]');
                        $msg['product' . $key] = form_error('sales[' . $key . '][product]');
                        $msg['quantity' . $key] = form_error('sales[' . $key . '][quantity]');
                    }
                }
                $array = array('status' => 'fail', 'url' => '', 'error' => $msg);
            } else {
                $data = $this->input->post();
                $this->inventory_model->save_issue($data);
                $url = base_url('inventory/issue');
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = array('status' => 'success', 'url' => $url, 'error' => '');
            }
            echo json_encode($array);
        }
    }

    public function issueItems()
    {
        $branchID = $this->application_model->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['categorylist'] = $this->app_lib->getSelectByBranch('product_category', $branchID);
        echo $this->load->view('inventory/issueItems', $this->data, true);
    }

    // delete product issue from database
    public function issue_delete($id)
    {
        if (!get_permission('product_issue', 'is_delete')) {
            access_denied();
        }
        $getStock = $this->db->get_where('product_issues_details', array('issues_id' => $id))->result();
        foreach ($getStock as $key => $value) {
            $this->inventory_model->stock_upgrade(($value->quantity), $value->product_id);
        }

        $this->db->where('id', $id);
        $this->db->delete('product_issues');

        $this->db->where('issues_id', $id);
        $this->db->delete('product_issues_details');
    }

    public function returnProduct()
    {
        if ($_POST) {
            if (!get_permission('product_issue', 'is_add')) {
                ajax_access_denied();
            }
            $id = $this->input->post('issue_id');
            $getStock = $this->db->get_where('product_issues_details', array('issues_id' => $id))->result();
            foreach ($getStock as $key => $value) {
                $this->inventory_model->stock_upgrade(($value->quantity), $value->product_id);
            }

            $this->db->where('id', $id);
            $this->db->update('product_issues', ['status' => 1, 'return_date' => date("Y-m-d")]);

            set_alert('success', translate('information_has_been_saved_successfully'));
            $array = array('status' => 'success');
            echo json_encode($array);
        }
    }

    public function getIssueDetails()
    {
        if (get_permission('product_issue', 'is_view')) {
            $this->data['salary_id'] = $this->input->post('id');
            $this->load->view('inventory/issue_modalView', $this->data);
        }
    }

    // inventory reports
    public function stockreport()
    {
        if (!get_permission('inventory_report', 'is_view')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        if (isset($_POST['search'])) {
            $category_id = $this->input->post('category_id');

            $this->data['results'] = $this->inventory_model->get_stock_product_wisereport($branchID, $category_id);
        }
        $this->data['title'] = translate('inventory');
        $this->data['categorylist'] = $this->app_lib->getSelectByBranch('product_category',  $branchID, true);
        $this->data['sub_page'] = 'inventory/stockreport';
        $this->data['main_menu'] = 'inventory_report';
        $this->load->view('layout/index', $this->data);
    }

    public function purchase_report()
    {
        if (!get_permission('inventory_report', 'is_view')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        if (isset($_POST['search'])) {
            $supplier_id = $this->input->post('supplier_id');
            $payment_status = $this->input->post('payment_status');
            $daterange = explode(' - ', $this->input->post('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->data['daterange'] = $daterange;
            $this->data['results'] = $this->inventory_model->get_purchase_report($branchID, $supplier_id, $payment_status, $start, $end);
        }
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/daterangepicker/daterangepicker.css',
            ),
            'js' => array(
                'vendor/moment/moment.js',
                'vendor/daterangepicker/daterangepicker.js',
            ),
        );
        $this->data['title'] = translate('inventory');
        $this->data['supplierlist'] = $this->app_lib->getSelectByBranch('product_supplier', $branchID, true);
        $this->data['sub_page'] = 'inventory/purchase_report';
        $this->data['main_menu'] = 'inventory_report';
        $this->load->view('layout/index', $this->data);
    }

    public function sales_report()
    {
        if (!get_permission('inventory_report', 'is_view')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        if (isset($_POST['search'])) {
            $supplier_id = $this->input->post('supplier_id');
            $payment_status = $this->input->post('payment_status');
            $daterange = explode(' - ', $this->input->post('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->data['daterange'] = $daterange;
            $this->data['results'] = $this->inventory_model->get_sales_report($branchID, $payment_status, $start, $end);
        }
        $this->data['title'] = translate('inventory');
        $this->data['supplierlist'] = $this->app_lib->getSelectByBranch('product_supplier', $branchID, true);
        $this->data['sub_page'] = 'inventory/sales_report';
        $this->data['main_menu'] = 'inventory_report';
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

    public function issues_report()
    {
        if (!get_permission('inventory_report', 'is_view')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        if (isset($_POST['search'])) {
            $supplier_id = $this->input->post('supplier_id');
            $payment_status = $this->input->post('payment_status');
            $daterange = explode(' - ', $this->input->post('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->data['daterange'] = $daterange;
            $this->data['results'] = $this->inventory_model->getIssuesreport($branchID, $start, $end);
        }
        $this->data['title'] = translate('inventory');
        $this->data['supplierlist'] = $this->app_lib->getSelectByBranch('product_supplier', $branchID, true);
        $this->data['sub_page'] = 'inventory/issues_report';
        $this->data['main_menu'] = 'inventory_report';
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

    public function getDataByBranch()
    {
        $html = "";
        $table = $this->input->post('table');
        $branch_id = $this->application_model->get_branch_id();
        if (!empty($branch_id)) {
            $result = $this->db->select('id,name')->where('branch_id', $branch_id)->get($table)->result_array();
            if (count($result)) {
                $html .= "<option value=''>" . translate('select') . "</option>";
                $html .= "<option value='all'>" . translate('all_select') . "</option>";
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

    public function getProductUnitDetails()
    {
        if (get_permission('product_unit', 'is_edit')) {
            $id = $this->input->post('id');
            $this->db->where('id', $id);
            $query = $this->db->get('product_unit');
            $result = $query->row_array();
            echo json_encode($result);
        }
    }
    public function import_products()
    {
        if (empty($_FILES['csv_file']['name'])) {
            show_error('No file selected');
        }

        $file = $_FILES['csv_file']['tmp_name'];

        if (($handle = fopen($file, 'r')) === false) {
            show_error('Unable to open CSV file');
        }

        // Skip CSV header row
        fgetcsv($handle);

        $imported = 0;

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {

            /*
         | CSV INDEX MAP
         | 0 => branch
         | 1 => product_name
         | 2 => product_code
         | 3 => category
         | 4 => unit
         | 5 => unit_ratio
         | 6 => purchase_price
         | 7 => sales_price
         | 8 => available_stock
         | 9 => remark
         */

            // ---------- SANITIZE INPUT ----------
            $branch_name   = trim($row[0]);
            $product_name  = trim($row[1]);
            $product_code  = trim($row[2]);
            $category_name = trim($row[3]);
            $unit_name     = trim($row[4]);

            // ---------- LOOKUP MASTER IDS ----------
            $branch_id   = $this->get_id('branch', 'name', $branch_name);
            $category_id = $this->get_id('product_category', 'name', $category_name);
            $unit_id     = $this->get_id('product_unit', 'name', $unit_name);

            // Skip row if any lookup fails
            if (!$branch_id || !$category_id || !$unit_id) {
                continue;
            }

            // ---------- PREPARE PRODUCT DATA ----------
            $data = [
                'name'             => $product_name,
                'code'             => $product_code,
                'category_id'      => $category_id,
                'purchase_unit_id' => $unit_id,
                'sales_unit_id'    => $unit_id,
                'unit_ratio'       => (float) $row[6],
                'purchase_price'   => (float) $row[7],
                'sales_price'      => (float) $row[8],
                'available_stock'  => (int) $row[9],
                'remarks'          => trim($row[10]),
                'branch_id'        => $branch_id,
                'created_at'       => date('Y-m-d H:i:s'),
            ];

            // ---------- INSERT ----------
            $this->db->insert('product', $data);

            if ($this->db->affected_rows() > 0) {
                $imported++;
            }
        }

        fclose($handle);

        // optional: success message
        $this->session->set_flashdata(
            'success',
            "{$imported} products imported successfully"
        );

        // redirect back to product page
        redirect('inventory/product'); // change URL if needed

        exit;
    }


    public function get_id($table, $column, $value)
    {
        $value = trim($value);

        $query = $this->db
            ->select('id')
            ->from($table)
            ->where("LOWER($column)", strtolower($value))
            ->limit(1)
            ->get();

        if (!$query || $query->num_rows() === 0) {
            return null;
        }

        return (int) $query->row()->id;
    }
}
