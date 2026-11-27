<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Student extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helpers('download');
        $this->load->library('datatables');
        $this->load->helpers('custom_fields');
        $this->load->model('student_model');
        $this->load->model('email_model');
        $this->load->model('sms_model');
        $this->load->model('student_fields_model');
        $this->load->model('transport_fees_model');
        $this->load->model('Student_model');
        $this->load->model('Parents_model');
    }

    public function index()
    {
        redirect(base_url('student/view'));
    }

    /* student form validation rules */
    protected function student_validation()
    {
        $branchID = $this->application_model->get_branch_id();
        $getBranch = $this->getBranchDetails();
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required');
        }
        $this->form_validation->set_rules('year_id', translate('academic_year'), 'trim|required');
        $this->form_validation->set_rules('first_name', translate('first_name'), 'trim|required');
        $this->form_validation->set_rules('class_id', translate('class'), 'trim|required');
        $this->form_validation->set_rules('section_id', translate('section'), 'trim|required');
        $this->form_validation->set_rules('register_no', translate('register_no'), 'trim|required|callback_unique_registerid');
        // checking profile photo format
        $this->form_validation->set_rules('user_photo', translate('profile_picture'), 'callback_photoHandleUpload[user_photo]');

        // system fields validation rules
        $validArr = array();
        $validationArr = $this->student_fields_model->getStatusArr($branchID);
        foreach ($validationArr as $key => $value) {
            if ($value->status && $value->required) {
                $validArr[$value->prefix] = 1;
            }
        }
        if (isset($validArr['admission_date'])) {
            $this->form_validation->set_rules('admission_date', translate('admission_date'), 'trim|required');
        }
        if (isset($validArr['student_photo'])) {
            if (isset($_FILES["user_photo"]) && empty($_FILES["user_photo"]['name']) && empty($_POST['old_user_photo'])) {
                $this->form_validation->set_rules('user_photo', translate('profile_picture'), 'required');
            }
        }
        if (isset($validArr['roll'])) {
            $this->form_validation->set_rules('roll', translate('roll'), 'trim|numeric|required|callback_unique_roll');
        } else {
            $this->form_validation->set_rules('roll', translate('roll'), 'trim|numeric|callback_unique_roll');
        }
        if (isset($validArr['last_name'])) {
            $this->form_validation->set_rules('last_name', translate('last_name'), 'trim|required');
        }
        if (isset($validArr['gender'])) {
            $this->form_validation->set_rules('gender', translate('gender'), 'trim|required');
        }
        if (isset($validArr['birthday'])) {
            $this->form_validation->set_rules('birthday', translate('birthday'), 'trim|required');
        }
        if (isset($validArr['category'])) {
            $this->form_validation->set_rules('category_id', translate('category'), 'trim|required');
        }
        if (isset($validArr['religion'])) {
            $this->form_validation->set_rules('religion', translate('religion'), 'trim|required');
        }
        if (isset($validArr['caste'])) {
            $this->form_validation->set_rules('caste', translate('caste'), 'trim|required');
        }
        if (isset($validArr['blood_group'])) {
            $this->form_validation->set_rules('blood_group', translate('blood_group'), 'trim|required');
        }
        if (isset($validArr['mother_tongue'])) {
            $this->form_validation->set_rules('mother_tongue', translate('mother_tongue'), 'trim|required');
        }
        if (isset($validArr['present_address'])) {
            $this->form_validation->set_rules('current_address', translate('present_address'), 'trim|required');
        }
        if (isset($validArr['permanent_address'])) {
            $this->form_validation->set_rules('permanent_address', translate('permanent_address'), 'trim|required');
        }
        if (isset($validArr['city'])) {
            $this->form_validation->set_rules('city', translate('city'), 'trim|required');
        }
        if (isset($validArr['state'])) {
            $this->form_validation->set_rules('state', translate('state'), 'trim|required');
        }
        if (isset($validArr['student_email'])) {
            $this->form_validation->set_rules('email', translate('email'), 'trim|required|valid_email');
        }
        if (isset($validArr['student_mobile_no'])) {
            $this->form_validation->set_rules('mobileno', translate('mobile_no'), 'trim|required|numeric');
        }
        if (isset($validArr['previous_school_details'])) {
            $this->form_validation->set_rules('school_name', translate('school_name'), 'trim|required');
            $this->form_validation->set_rules('qualification', translate('qualification'), 'trim|required');
        }

        if ($getBranch['stu_generate'] == 0 || isset($_POST['student_id'])) {
            $this->form_validation->set_rules('username', translate('username'), 'trim|required|callback_unique_username');
            if (!isset($_POST['student_id'])) {
                $this->form_validation->set_rules('password', translate('password'), 'trim|required|min_length[4]');
                $this->form_validation->set_rules('retype_password', translate('retype_password'), 'trim|required|matches[password]');
            }
        }

        // custom fields validation rules
        $class_slug = $this->router->fetch_class();
        $customFields = getCustomFields($class_slug);
        foreach ($customFields as $fields_key => $fields_value) {
            if ($fields_value['required']) {
                $fieldsID   = $fields_value['id'];
                $fieldLabel = $fields_value['field_label'];
                $this->form_validation->set_rules("custom_fields[student][" . $fieldsID . "]", $fieldLabel, 'trim|required');
            }
        }
    }

    /* student admission information are prepared and stored in the database here */
    public function add()
    {
        // check access permission
        if (!get_permission('student', 'is_add')) {
            access_denied();
        }

        // check saas student add limit
        if ($this->app_lib->isExistingAddon('saas')) {
            if (!checkSaasLimit('student')) {
                set_alert('error', translate('update_your_package'));
                redirect(site_url('dashboard'));
            }
        }
        $getBranch = $this->getBranchDetails();
        $branchID = $this->application_model->get_branch_id();
        $this->load->model('classes_model');
        $this->load->model('section_model');
        $this->load->model('student_model');

        $this->data['classes'] = $this->classes_model->get_all_classes();
        $this->data['sections'] = $this->section_model->get_all_sections();
        //  $this->data['students'] = $this->student_model->get_all_students();
        //  print_r($this->student_model->get_all_students());die();
        //  $this->data['students'] = $this->student_model->get_students_by_class();

        $this->data['getBranch'] = $getBranch;
        $this->data['branch_id'] = $branchID;
        $this->data['sub_page'] = 'student/add';
        $this->data['main_menu'] = 'admission';
        $this->data['register_id'] = $this->student_model->regSerNumber($branchID);
        $this->data['title'] = translate('create_admission');
        $this->data['extra_js'] = [
            '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>',
        ];
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/dropify/css/dropify.min.css',
                'vendor/bootstrap-multiselect/css/bootstrap-multiselect.css',
                'vendor/bootstrap-multiselect/css/multistep.css',

            ),
            'js' => array(
                'js/student.js',
                'js/multistep.js',
                'vendor/dropify/js/dropify.min.js',
                'vendor/bootstrap-multiselect/js/bootstrap-multiselect.js',
            ),
        );
        $this->load->view('layout/index', $this->data);
    }

    // public function save()
    // {
    //     if ($_POST) {
    //         // check access permission
    //         if (!get_permission('student', 'is_add')) {
    //             ajax_access_denied();
    //         }

    //         // check saas student add limit
    //         if ($this->app_lib->isExistingAddon('saas')) {
    //             if (!checkSaasLimit('student')) {
    //                 ajax_access_denied();
    //             }
    //         }

    //         $getBranch = $this->getBranchDetails();
    //         $branchID = $this->application_model->get_branch_id();

    //         $this->student_validation();
    //         if (!isset($_POST['guardian_chk'])) {

    //             // system fields validation rules
    //             $validArr = array();
    //             $validationArr = $this->student_fields_model->getStatusArr($branchID);
    //             foreach ($validationArr as $key => $value) {
    //                 if ($value->status && $value->required) {
    //                     $validArr[$value->prefix] = 1;
    //                 }
    //             }

    //             if (isset($validArr['guardian_name'])) {
    //                 $this->form_validation->set_rules('grd_name', translate('name'), 'trim|required');
    //             }
    //             if (isset($validArr['guardian_relation'])) {
    //                 $this->form_validation->set_rules('grd_relation', translate('relation'), 'trim|required');
    //             }
    //             if (isset($validArr['father_name'])) {
    //                 $this->form_validation->set_rules('father_name', translate('father_name'), 'trim|required');
    //             }
    //             if (isset($validArr['mother_name'])) {
    //                 $this->form_validation->set_rules('mother_name', translate('mother_name'), 'trim|required');
    //             }
    //             if (isset($validArr['guardian_occupation'])) {
    //                 $this->form_validation->set_rules('grd_occupation', translate('occupation'), 'trim|required');
    //             }
    //             if (isset($validArr['guardian_income'])) {
    //                 $this->form_validation->set_rules('grd_income', translate('occupation'), 'trim|required|numeric');
    //             }
    //             if (isset($validArr['guardian_education'])) {
    //                 $this->form_validation->set_rules('grd_education', translate('education'), 'trim|required');
    //             }
    //             if (isset($validArr['guardian_email'])) {
    //                 $this->form_validation->set_rules('grd_email', translate('email'), 'trim|required');
    //             }
    //             if (isset($validArr['guardian_mobile_no'])) {
    //                 $this->form_validation->set_rules('grd_mobileno', translate('mobile_no'), 'trim|required|numeric');
    //             }
    //             if (isset($validArr['guardian_address'])) {
    //                 $this->form_validation->set_rules('grd_address', translate('address'), 'trim|required');
    //             }
    //             if (isset($validArr['guardian_photo'])) {
    //                 if (isset($_FILES["guardian_photo"]) && empty($_FILES["guardian_photo"]['name'])) {
    //                     $this->form_validation->set_rules('guardian_photo', translate('guardian_picture'), 'required');
    //                 }
    //             }
    //             if (isset($validArr['guardian_city'])) {
    //                 $this->form_validation->set_rules('grd_city', translate('city'), 'trim|required');
    //             }
    //             if (isset($validArr['guardian_state'])) {
    //                 $this->form_validation->set_rules('grd_state', translate('state'), 'trim|required');
    //             }

    //             if ($getBranch['grd_generate'] == 0) {
    //                 if (isset($validArr['grd_username'])) {
    //                     $this->form_validation->set_rules('grd_username', translate('username'), 'trim|required|callback_get_valid_guardian_username');
    //                 }
    //                 if (isset($validArr['grd_password'])) {
    //                     $this->form_validation->set_rules('grd_password', translate('password'), 'trim|required');
    //                     $this->form_validation->set_rules('grd_retype_password', translate('retype_password'), 'trim|required|matches[grd_password]');
    //                 }
    //             }
    //         } else {
    //             $this->form_validation->set_rules('parent_id', translate('guardian'), 'required');
    //         }

    //         // transport validation 
    //         $route_id = $this->input->post('route_id');
    //         $transport_fees_month = $this->input->post('transport_fees_month');
    //         $stoppage_point_id = $this->input->post('stoppage_point_id');
    //         if (!empty($transport_fees_month) || !empty($route_id)) {
    //             $this->form_validation->set_rules('route_id', translate('transport_route'), 'required');
    //             $this->form_validation->set_rules('vehicle_id', translate('vehicle_no'), 'required');
    //             $this->form_validation->set_rules('stoppage_point_id', translate('pickup_point'), 'required');
    //         }

    //         if ($this->form_validation->run() == true) {
    //             $post = $this->input->post();
    //             //save all student information in the database file
    //             $studentData = $this->student_model->save($post, $getBranch);
    //             $studentID = $studentData['student_id'];
    //             //save student enroll information in the database file
    //             $arrayEnroll = array(
    //                 'student_id' => $studentID,
    //                 'class_id' => $post['class_id'],
    //                 'section_id' => $post['section_id'],
    //                 'roll' => (isset($post['roll']) ? $post['roll'] : 0),
    //                 'session_id' => $post['year_id'],
    //                 'branch_id' => $branchID,
    //             );
    //             $this->db->insert('enroll', $arrayEnroll);
    //             $enrollID = $this->db->insert_id();


    //             // Fetch unique fee allocations for this class/session/branch
    //             $this->db->select('sfa.fee_type_id, sfa.fee_master_id, sfa.student_category_id,
    //                sfa.amount, sfa.due_date, sfa.fine_type, sfa.per_val,
    //                sfa.fine_calculated_amount, sfa.amt_val');
    //             $this->db->from('student_fee_allocation as sfa');
    //             $this->db->join('enroll as e', 'e.student_id = sfa.student_id AND e.class_id = sfa.class_id');
    //             $this->db->where('e.class_id', $post['class_id']);
    //             $this->db->where('e.session_id', $post['year_id']);
    //             $this->db->where('e.branch_id', $branchID);
    //             $this->db->group_by(['sfa.fee_type_id', 'sfa.fee_master_id', 'sfa.student_category_id']);
    //             // ensures uniqueness
    //             $existingAllocations = $this->db->get()->result_array();


    //             if (!empty($existingAllocations)) {
    //                 $allocationData = [];
    //                 foreach ($existingAllocations as $alloc) {
    //                     // Prevent inserting duplicate for this student
    //                     $exists = $this->db->get_where('student_fee_allocation', [
    //                         'student_id'          => $studentID,
    //                         'class_id'            => $post['class_id'],
    //                         'fee_type_id'         => $alloc['fee_type_id'],
    //                         'fee_master_id'       => $alloc['fee_master_id'],
    //                         'student_category_id' => $alloc['student_category_id']
    //                     ])->row_array();

    //                     if ($exists) {
    //                         continue; // already assigned to this student
    //                     }

    //                     $allocationData[] = [
    //                         'student_id'          => $studentID,
    //                         'class_id'            => $post['class_id'],
    //                         'fee_type_id'         => $alloc['fee_type_id'],
    //                         'student_category_id' => $alloc['student_category_id'],
    //                         'fee_master_id'       => $alloc['fee_master_id'],
    //                         'amount'              => $alloc['amount'],
    //                         'paid'                => 0,
    //                         'balance'             => $alloc['amount'],
    //                         'due_date'            => $alloc['due_date'],
    //                         'fine_type'           => $alloc['fine_type'],
    //                         'per_val'             => $alloc['per_val'],
    //                         'fine_calculated_amount' => $alloc['fine_calculated_amount'],
    //                         'amt_val'             => $alloc['amt_val'],
    //                         'created_at'          => date('Y-m-d H:i:s')
    //                     ];
    //                 }

    //                 if (!empty($allocationData)) {
    //                     $this->db->insert_batch('student_fee_allocation', $allocationData);
    //                 }
    //             }






    //             // Handle sibling data
    //             $siblingsData = $this->input->post('siblings_data');
    //             if (!empty($siblingsData)) {
    //                 $siblings = json_decode($siblingsData, true); // decode JSON array
    //                 if (!empty($siblings)) {
    //                     foreach ($siblings as $siblingId) {
    //                         $this->db->insert('student_siblings', [
    //                             'student_id' => $studentID,   // newly admitted student
    //                             'sibling_id' => $siblingId   // existing student as sibling
    //                         ]);
    //                     }
    //                 }
    //             }


    //             // transport data save in the database file 
    //             if (!empty($transport_fees_month)) {
    //                 $transportFees = [];
    //                 foreach ($transport_fees_month as $key => $fineID) {
    //                     $transportFees[] = array(
    //                         'enroll_id' => $enrollID,
    //                         'transport_fee_fine_id' => $fineID,
    //                         'stoppage_point_id' => $stoppage_point_id,
    //                     );
    //                 }
    //                 $this->db->where('stoppage_point_id !=', $stoppage_point_id);
    //                 $this->db->where('enroll_id', $enrollID);
    //                 $this->db->delete('transport_fee_details');
    //                 if (!empty($transportFees))
    //                     $this->db->insert_batch('transport_fee_details', $transportFees);
    //             }

    //             // handle custom fields data
    //             $class_slug = $this->router->fetch_class();
    //             $customField = $this->input->post("custom_fields[$class_slug]");
    //             if (!empty($customField)) {
    //                 saveCustomFields($customField, $studentID);
    //             }

    //             // send student admission email
    //             $this->email_model->studentAdmission($studentData);
    //             // send account activate sms
    //             $this->sms_model->send_sms($arrayEnroll, 1);

    //             set_alert('success', translate('information_has_been_saved_successfully'));
    //             $url = base_url('student/add');
    //             $array = array('status' => 'success', 'url' => $url);
    //         } else {
    //             $error = $this->form_validation->error_array();
    //             $array = array('status' => 'fail', 'error' => $error);
    //         }
    //         echo json_encode($array);
    //     }
    // }
    public function save()
    {
        if ($_POST) {
            // check access permission
            if (!get_permission('student', 'is_add')) {
                ajax_access_denied();
            }

            // check saas student add limit
            if ($this->app_lib->isExistingAddon('saas')) {
                if (!checkSaasLimit('student')) {
                    ajax_access_denied();
                }
            }

            $getBranch = $this->getBranchDetails();
            $branchID = $this->application_model->get_branch_id();

            $this->student_validation();
            if (!isset($_POST['guardian_chk'])) {
                // system fields validation rules
                $validArr = array();
                $validationArr = $this->student_fields_model->getStatusArr($branchID);
                foreach ($validationArr as $key => $value) {
                    if ($value->status && $value->required) {
                        $validArr[$value->prefix] = 1;
                    }
                }

                if (isset($validArr['guardian_name'])) {
                    $this->form_validation->set_rules('grd_name', translate('name'), 'trim|required');
                }
                if (isset($validArr['guardian_relation'])) {
                    $this->form_validation->set_rules('grd_relation', translate('relation'), 'trim|required');
                }
                if (isset($validArr['father_name'])) {
                    $this->form_validation->set_rules('father_name', translate('father_name'), 'trim|required');
                }
                if (isset($validArr['mother_name'])) {
                    $this->form_validation->set_rules('mother_name', translate('mother_name'), 'trim|required');
                }
                if (isset($validArr['guardian_occupation'])) {
                    $this->form_validation->set_rules('grd_occupation', translate('occupation'), 'trim|required');
                }
                if (isset($validArr['guardian_income'])) {
                    $this->form_validation->set_rules('grd_income', translate('occupation'), 'trim|required|numeric');
                }
                if (isset($validArr['guardian_education'])) {
                    $this->form_validation->set_rules('grd_education', translate('education'), 'trim|required');
                }
                if (isset($validArr['guardian_email'])) {
                    $this->form_validation->set_rules('grd_email', translate('email'), 'trim|required');
                }
                if (isset($validArr['guardian_mobile_no'])) {
                    $this->form_validation->set_rules('grd_mobileno', translate('mobile_no'), 'trim|required|numeric');
                }
                if (isset($validArr['guardian_address'])) {
                    $this->form_validation->set_rules('grd_address', translate('address'), 'trim|required');
                }
                if (isset($validArr['guardian_photo'])) {
                    if (isset($_FILES["guardian_photo"]) && empty($_FILES["guardian_photo"]['name'])) {
                        $this->form_validation->set_rules('guardian_photo', translate('guardian_picture'), 'required');
                    }
                }
                if (isset($validArr['guardian_city'])) {
                    $this->form_validation->set_rules('grd_city', translate('city'), 'trim|required');
                }
                if (isset($validArr['guardian_state'])) {
                    $this->form_validation->set_rules('grd_state', translate('state'), 'trim|required');
                }

                if ($getBranch['grd_generate'] == 0) {
                    if (isset($validArr['grd_username'])) {
                        $this->form_validation->set_rules('grd_username', translate('username'), 'trim|required|callback_get_valid_guardian_username');
                    }
                    if (isset($validArr['grd_password'])) {
                        $this->form_validation->set_rules('grd_password', translate('password'), 'trim|required');
                        $this->form_validation->set_rules('grd_retype_password', translate('retype_password'), 'trim|required|matches[grd_password]');
                    }
                }
            } else {
                $this->form_validation->set_rules('parent_id', translate('guardian'), 'required');
            }

            // transport validation 
            $route_id = $this->input->post('route_id');
            $transport_fees_month = $this->input->post('transport_fees_month');
            $stoppage_point_id = $this->input->post('stoppage_point_id');
            if (!empty($transport_fees_month) || !empty($route_id)) {
                $this->form_validation->set_rules('route_id', translate('transport_route'), 'required');
                $this->form_validation->set_rules('vehicle_id', translate('vehicle_no'), 'required');
                $this->form_validation->set_rules('stoppage_point_id', translate('pickup_point'), 'required');
            }

            if ($this->form_validation->run() == true) {
                $post = $this->input->post();
                // save all student information in the database file
                $studentData = $this->student_model->save($post, $getBranch);
                $studentID = $studentData['student_id'];

                // save student enroll information
                $arrayEnroll = array(
                    'student_id' => $studentID,
                    'class_id' => $post['class_id'],
                    'section_id' => $post['section_id'],
                    'roll' => (isset($post['roll']) ? $post['roll'] : 0),
                    'session_id' => $post['year_id'],
                    'branch_id' => $branchID,
                );
                $this->db->insert('enroll', $arrayEnroll);
                $enrollID = $this->db->insert_id();

                /*
             * ==============================
             * Fee Allocation Logic
             * ==============================
             */

                // Step 1: Try to copy existing allocations from other students of this class/session/branch
                /*
 * ==============================
 * Fee Allocation Logic
 * ==============================
 */
                // $studentCategory = $this->db->select('category_id') ->from('student') ->where('id', $studentID) ->get() ->row('category_id');
                // // Step 1: Try to copy existing allocations from other students of this class/session/branch
                // $this->db->select('sfa.fee_type_id, sfa.fee_master_id, sfa.student_category_id,
                //     sfa.amount, sfa.due_date, sfa.fine_type, sfa.per_val,
                //     sfa.fine_calculated_amount, sfa.amt_val');
                // $this->db->from('student_fee_allocation as sfa');
                // $this->db->join('enroll as e', 'e.student_id = sfa.student_id AND e.class_id = sfa.class_id');
                // $this->db->where('e.class_id', $post['class_id']);
                // $this->db->where('e.section_id', $post['section_id']);
                // $this->db->where('e.session_id', $post['year_id']);
                // $this->db->where('e.branch_id', $branchID);
                // $this->db->group_by(['sfa.fee_type_id', 'sfa.fee_master_id', 'sfa.student_category_id']);
                // $this->db->where("JSON_CONTAINS(sfa.student_category_id, '\"$studentCategory\"')", NULL, FALSE);
                // $existingAllocations = $this->db->get()->result_array();
                $studentCategory = $this->db->select('category_id')
                    ->from('student')
                    ->where('id', $studentID)
                    ->get()
                    ->row('category_id');

                $this->db->select('sfa.fee_type_id, sfa.fee_master_id, sfa.student_category_id,
    sfa.amount, sfa.due_date, sfa.fine_type, sfa.per_val,
    sfa.fine_calculated_amount, sfa.amt_val, sfa.month');

                $this->db->from('student_fee_allocation as sfa');
                $this->db->join('enroll as e', 'e.student_id = sfa.student_id AND e.class_id = sfa.class_id');

                $this->db->where('e.class_id', $post['class_id']);
                $this->db->where('e.section_id', $post['section_id']);
                $this->db->where('e.session_id', $post['year_id']);
                $this->db->where('e.branch_id', $branchID);

                $this->db->where("JSON_CONTAINS(sfa.student_category_id, '\"$studentCategory\"')", NULL, FALSE);

                // ⭐ IMPORTANT: include month in grouping
                $this->db->group_by([
                    'sfa.fee_type_id',
                    'sfa.fee_master_id',
                    'sfa.student_category_id',
                    'sfa.month'
                ]);

                $existingAllocations = $this->db->get()->result_array();

                $allocationData = [];

                if (!empty($existingAllocations)) {
                    // Case 1: Copy existing allocations
                    foreach ($existingAllocations as $alloc) {
                        $exists = $this->db->get_where('student_fee_allocation', [
                            'student_id'          => $studentID,
                            'class_id'            => $post['class_id'],
                            'fee_type_id'         => $alloc['fee_type_id'],
                            'fee_master_id'       => $alloc['fee_master_id'],
                            'student_category_id' => $alloc['student_category_id']
                        ])->row_array();
                        //  echo "ttt\n";
                        // print_r($exists);
                        // exit;
                        if ($exists) continue; // already assigned

                        $allocationData[] = [
                            'student_id'          => $studentID,
                            'class_id'            => $post['class_id'],
                            'fee_type_id'         => $alloc['fee_type_id'],
                            'student_category_id' => $alloc['student_category_id'],
                            'fee_master_id'       => $alloc['fee_master_id'],
                            'amount'              => $alloc['amount'],
                            'paid'                => 0,
                            'balance'             => $alloc['amount'],
                            'due_date'            => $alloc['due_date'] ?? null,
                            'fine_type'           => $alloc['fine_type'] ?? '',
                            'per_val'             => $alloc['per_val'] ?? 0,
                            'fine_calculated_amount' => $alloc['fine_calculated_amount'] ?? 0,
                            'amt_val'             => $alloc['amt_val'] ?? 0,
                            'created_at'          => date('Y-m-d H:i:s')
                        ];
                    }
                    //  echo "<pre>";
                    // print_r($allocationData);
                    // echo "</pre>";
                    // exit;
                    if (!empty($allocationData)) {
                        $this->db->insert_batch('student_fee_allocation', $allocationData);
                    }
                } else {
                    // Case 2: No existing allocations, pull from fee_master_classwise
                    $this->db->select('*');
                    $this->db->from('fee_master_classwise');
                    $this->db->where('class_id', $post['class_id']);
                    $feeMasterRecords = $this->db->get()->result_array();

                    if (!empty($feeMasterRecords)) {
                        $currentYear = date('Y');

                        foreach ($feeMasterRecords as $record) {
                            // Split comma-separated months into array
                            $monthsArr = explode(',', $record['month']);

                            foreach ($monthsArr as $month) {
                                $month = trim($month); // remove any extra spaces
                                $due_date = null;
                                if (!empty($record['due_date'])) {
                                    $due_date = date('Y-m-d', strtotime("$currentYear-$month-" . $record['due_date']));
                                }

                                $fine_calculated_amount = 0;
                                if ($record['fine_type'] == 'percentage' && !empty($record['per_val'])) {
                                    $fine_calculated_amount = ($record['amount'] * $record['per_val'] / 100);
                                } elseif ($record['fine_type'] == 'fix amount' && !empty($record['amt_val'])) {
                                    $fine_calculated_amount = $record['amt_val'];
                                } elseif (!empty($record['fine_amount'])) {
                                    $fine_calculated_amount = $record['fine_amount'];
                                }

                                $allocationData[] = [
                                    'student_id'          => $studentID,
                                    'class_id'            => $record['class_id'],
                                    'fee_type_id'         => $record['fee_type_id'],
                                    'fee_master_id'       => $record['fee_master_id'],
                                    'student_category_id' => $record['student_category_id'],
                                    'amount'              => $record['amount'],
                                    'paid'                => 0,
                                    'balance'             => $record['amount'],
                                    'due_date'            => $due_date,
                                    'fine_type'           => $record['fine_type'] ?? '',
                                    'per_val'             => $record['per_val'] ?? 0,
                                    'fine_calculated_amount' => $fine_calculated_amount ?? 0,
                                    'amt_val'             => $record['amt_val'] ?? 0,
                                    'month'               => $month,
                                    'created_at'          => date('Y-m-d H:i:s')
                                ];
                            }
                        }

                        if (!empty($allocationData)) {
                            $this->db->insert_batch('student_fee_allocation', $allocationData);
                        }
                    }
                }


                /*
             * ==============================
             * Sibling Logic
             * ==============================
             */
                $siblingsData = $this->input->post('siblings_data');
                if (!empty($siblingsData)) {
                    $siblings = json_decode($siblingsData, true);
                    if (!empty($siblings)) {
                        foreach ($siblings as $siblingId) {
                            $this->db->insert('student_siblings', [
                                'student_id' => $studentID,
                                'sibling_id' => $siblingId
                            ]);
                        }
                    }
                }

                /*
             * ==============================
             * Transport Logic
             * ==============================
             */
                if (!empty($transport_fees_month)) {
                    $transportFees = [];
                    foreach ($transport_fees_month as $key => $fineID) {
                        $transportFees[] = array(
                            'enroll_id' => $enrollID,
                            'transport_fee_fine_id' => $fineID,
                            'stoppage_point_id' => $stoppage_point_id,
                            'month' => $fineID
                        );
                    }
                    $this->db->where('stoppage_point_id !=', $stoppage_point_id);
                    $this->db->where('enroll_id', $enrollID);
                    $this->db->delete('transport_fee_details');
                    if (!empty($transportFees)) {
                        $this->db->insert_batch('transport_fee_details', $transportFees);
                    }
                }

                /*
             * ==============================
             * Custom Fields
             * ==============================
             */
                $class_slug = $this->router->fetch_class();
                $customField = $this->input->post("custom_fields[$class_slug]");
                if (!empty($customField)) {
                    saveCustomFields($customField, $studentID);
                }

                /*
             * ==============================
             * Notifications
             * ==============================
             */
                $this->email_model->studentAdmission($studentData);
                $this->sms_model->send_sms($arrayEnroll, 1);

                set_alert('success', translate('information_has_been_saved_successfully'));
                $url = base_url('student/add');
                $array = array('status' => 'success', 'url' => $url);
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
        }
    }



    /* csv file to import student information and stored in the database here */
    public function csv_import()
    {
        // check access permission
        if (!get_permission('multiple_import', 'is_add')) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();
        if (isset($_POST['save'])) {
            $err_msg = "";
            $i = 0;
            $this->load->library('csvimport');
            // form validation rules
            if (is_superadmin_loggedin() == true) {
                $this->form_validation->set_rules('branch_id', 'Branch', 'trim|required');
            }
            $this->form_validation->set_rules('class_id', 'Class', 'trim|required');
            $this->form_validation->set_rules('section_id', 'Section', 'trim|required');
            if (isset($_FILES["userfile"]) && empty($_FILES['userfile']['name'])) {
                $this->form_validation->set_rules('userfile', 'CSV File', 'required');
            }
            if ($this->form_validation->run() == true) {
                $classID = $this->input->post('class_id');
                $sectionID = $this->input->post('section_id');
                $csv_array = $this->csvimport->get_array($_FILES["userfile"]["tmp_name"]);
                if ($csv_array) {
                    $columnHeaders = array('FirstName', 'LastName', 'BloodGroup', 'Gender', 'Birthday', 'MotherTongue', 'Religion', 'Caste', 'Phone', 'City', 'State', 'PresentAddress', 'PermanentAddress', 'CategoryID', 'Roll', 'RegisterNo', 'AdmissionDate', 'StudentEmail', 'StudentUsername', 'StudentPassword', 'GuardianName', 'GuardianRelation', 'FatherName', 'MotherName', 'GuardianOccupation', 'GuardianMobileNo', 'GuardianAddress', 'GuardianEmail', 'GuardianUsername', 'GuardianPassword');
                    $csvData = array();
                    foreach ($csv_array as $row) {
                        if ($i == 0) {
                            $csvData = array_keys($row);
                        }
                        $csv_chk = array_diff($columnHeaders, $csvData);
                        if (count($csv_chk) <= 0) {
                            $schoolSettings = $this->student_model->get('branch', array('id' => $branchID), true, false, 'unique_roll');
                            $unique_roll = $schoolSettings['unique_roll'];

                            $r = $this->csvCheckExistsData($row['StudentUsername'], $row['Roll'], $row['RegisterNo'], $classID, $sectionID, $branchID, $unique_roll);
                            if ($r['status'] == false) {
                                $err_msg .= $row['FirstName'] . ' ' . $row['LastName'] . " - Imported Failed : " . $r['message'] . "<br>";
                            } else {
                                $this->student_model->csvImport($row, $classID, $sectionID, $branchID);
                                $i++;
                            }
                        } else {
                            set_alert('error', translate('invalid_csv_file'));
                            redirect(base_url("student/csv_import"));
                        }
                    }
                    if ($err_msg != null) {
                        $this->session->set_flashdata('csvimport', $err_msg);
                    }
                    if ($i > 0) {
                        set_alert('success', $i . ' Students Have Been Successfully Added');
                    }
                    redirect(base_url("student/csv_import"));
                } else {
                    set_alert('error', translate('invalid_csv_file'));
                    redirect(base_url("student/csv_import"));
                }
            }
        }
        $this->data['title'] = translate('multiple_import');
        $this->data['branch_id'] = $branchID;
        $this->data['sub_page'] = 'student/multi_add';
        $this->data['main_menu'] = 'admission';
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/dropify/css/dropify.min.css',
            ),
            'js' => array(
                'vendor/dropify/js/dropify.min.js',
            ),
        );
        $this->load->view('layout/index', $this->data);
    }

    /* showing disable authentication student list */
    public function disable_authentication()
    {
        // check access permission
        if (!get_permission('student_disable_authentication', 'is_view')) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();
        if (isset($_POST['search'])) {
            $classID = $this->input->post('class_id');
            $sectionID = $this->input->post('section_id');
            $this->data['students'] = $this->application_model->getStudentListByClassSection($classID, $sectionID, $branchID, true);
        }

        if (isset($_POST['auth'])) {
            if (!get_permission('student_disable_authentication', 'is_add')) {
                access_denied();
            }
            $stafflist = $this->input->post('views_bulk_operations');
            if (isset($stafflist)) {
                foreach ($stafflist as $id) {
                    $this->db->where(array('role' => 7, 'user_id' => $id));
                    $this->db->update('login_credential', array('active' => 1));

                    $this->db->where('id', $id);
                    $this->db->update('student', array('active' => 1));
                }
                set_alert('success', translate('information_has_been_updated_successfully'));
            } else {
                set_alert('error', 'Please select at least one item');
            }
            redirect(base_url('student/disable_authentication'));
        }
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('deactivate_account');
        $this->data['sub_page'] = 'student/disable_authentication';
        $this->data['main_menu'] = 'student';
        $this->load->view('layout/index', $this->data);
    }

    // add new student category
    public function category()
    {
        if (isset($_POST['category'])) {
            if (!get_permission('student_category', 'is_add')) {
                access_denied();
            }
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            }
            $this->form_validation->set_rules('category_name', translate('category_name'), 'trim|required|callback_unique_category');
            if ($this->form_validation->run() !== false) {
                $arrayData = array(
                    'name' => $this->input->post('category_name'),
                    'branch_id' => $this->application_model->get_branch_id(),
                );
                $this->db->insert('student_category', $arrayData);
                set_alert('success', translate('information_has_been_saved_successfully'));
                redirect(base_url('student/category'));
            }
        }
        $this->data['title'] = translate('student') . " " . translate('details');
        $this->data['sub_page'] = 'student/category';
        $this->data['main_menu'] = 'admission';
        $this->load->view('layout/index', $this->data);
    }
    public function get_student_categories()
    {
        $this->load->database();
        $query = $this->db->get('student_category');
        echo json_encode($query->result());
    }


    // update existing student category
    public function category_edit()
    {
        if (!get_permission('student_category', 'is_edit')) {
            ajax_access_denied();
        }
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $this->form_validation->set_rules('category_name', translate('category_name'), 'trim|required|callback_unique_category');
        if ($this->form_validation->run() !== false) {
            $category_id = $this->input->post('category_id');
            $arrayData = array(
                'name' => $this->input->post('category_name'),
                'branch_id' => $this->application_model->get_branch_id(),
            );
            $this->db->where('id', $category_id);
            $this->db->update('student_category', $arrayData);
            set_alert('success', translate('information_has_been_updated_successfully'));
            $array  = array('status' => 'success');
        } else {
            $error = $this->form_validation->error_array();
            $array = array('status' => 'fail', 'error' => $error);
        }
        echo json_encode($array);
    }

    // delete student category from database
    public function category_delete($id)
    {
        if (get_permission('student_category', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('id', $id);
            $this->db->delete('student_category');
        }
    }

    // student category details send by ajax
    public function categoryDetails()
    {
        if (get_permission('student_category', 'is_edit')) {
            $id = $this->input->post('id');
            $this->db->where('id', $id);
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $query = $this->db->get('student_category');
            $result = $query->row_array();
            echo json_encode($result);
        }
    }

    /* validate here, if the check student category name */
    public function unique_category($name)
    {
        $branchID = $this->application_model->get_branch_id();
        $category_id = $this->input->post('category_id');
        if (!empty($category_id)) {
            $this->db->where_not_in('id', $category_id);
        }
        $this->db->where(array('name' => $name, 'branch_id' => $branchID));
        $uniform_row = $this->db->get('student_category')->num_rows();
        if ($uniform_row == 0) {
            return true;
        } else {
            $this->form_validation->set_message("unique_category", translate('already_taken'));
            return false;
        }
    }

    /* showing student list by class and section */
    public function view()
    {
        // check access permission
        if (!get_permission('student', 'is_view')) {
            access_denied();
        }

        $branchID = $this->application_model->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('student_list');
        $this->data['main_menu'] = 'student';
        $this->data['sub_page'] = 'student/view';
        $this->data['headerelements'] = array(
            'js' => array(
                'js/student.js'
            ),
        );
        $this->load->view('layout/index', $this->data);
    }

    /* function to check search parameter validation */
    public function filter_validation()
    {
        if (get_permission('student', 'is_view')) {
            if ($_POST) {
                if (is_superadmin_loggedin()) {
                    $this->form_validation->set_rules('branch_id', translate('branch'), 'trim|required');
                }

                $this->form_validation->set_rules('class_id', translate('class'), 'trim');
                $this->form_validation->set_rules('section_id', translate('section'), 'trim');
                if ($this->form_validation->run() == true) {
                    $branchID = $this->application_model->get_branch_id();
                    $classID = $this->input->post('class_id');
                    $sectionID = $this->input->post('section_id');
                    $filter = array('branch_id' => $branchID, 'class_id' => $classID, 'section_id' => $sectionID);
                    $export_title = get_type_name_by_id('branch', $branchID) . " " . translate('student_list');
                    $ajax_thead =  $this->load->view("student/view_ajax_thead", array('branch_id' => $branchID), true);
                    echo json_encode(array('status' => 'success', 'thead' => $ajax_thead, 'filter' => $filter, 'export_title' => $export_title, 'error' => ''));
                } else {
                    $error = $this->form_validation->error_array();
                    $array = array('status' => 'fail', 'error' => $error);
                    echo json_encode(array('status' => 'fail', 'error' => $error));
                }
            }
        }
    }

    public function getStudentListDT()
    {
        if ($_POST) {
            if (get_permission('student', 'is_view')) {
                echo $this->student_model->studentListDT();
            }
        }
    }

    /**
     * Staff-only: open the student portal as this enrollment (same session as normal student login).
     */
    public function login_as_student($enroll_id = '')
    {
        if (!get_permission('student', 'is_view')) {
            access_denied();
        }
        if (is_student_loggedin() || is_parent_loggedin()) {
            redirect(base_url('dashboard'));
        }
        $enroll_id = (int) $enroll_id;
        if ($enroll_id < 1) {
            show_404();
        }
        $this->db->where('id', $enroll_id);
        $this->db->where('session_id', get_session_id());
        $enroll = $this->db->get('enroll')->row_array();
        if (empty($enroll) || !$this->staff_can_access_enroll_row($enroll)) {
            set_alert('error', translate('access_denied'));
            redirect(base_url('student/view'));
        }
        $student_id = (int) $enroll['student_id'];
        $credential = $this->db->get_where('login_credential', array('user_id' => $student_id, 'role' => 7))->row();
        if (empty($credential) || !$credential->active) {
            set_alert('error', translate('inactive_account'));
            redirect(base_url('student/view'));
        }

        $this->load->model('authentication_model');
        $backup = $this->session->userdata();
        unset($backup['impersonate_backup']);
        $backup['_impersonate_return'] = 'student/view';
        $this->session->set_userdata('impersonate_backup', $backup);

        $this->db->select('student.id, student.mobileno, CONCAT_WS(" ", student.first_name, student.last_name) as name, student.email, student.photo');
        $this->db->where('student.id', $student_id);
        $getUser = $this->db->get('student')->row_array();
        $getUser['branch_id'] = $enroll['branch_id'];

        $getConfig = $this->db->select('translation,session_id')->get_where('global_settings', array('id' => 1))->row();
        $language = $getConfig->translation;
        $sessionID = $enroll['session_id'];
        if ($this->app_lib->isExistingAddon('saas')) {
            $schoolSettings = $this->db->select('id,translation')->where(array('id' => $getUser['branch_id'], 'status' => 1))->get('branch')->row();
            if (empty($schoolSettings)) {
                $this->session->unset_userdata('impersonate_backup');
                set_alert('error', translate('inactive_school'));
                redirect(base_url('student/view'));
            }
            $language = $schoolSettings->translation;
        }

        $this->session->set_userdata('student_id', $student_id);
        $this->session->set_userdata('enrollID', $enroll_id);
        $isRTL = $this->app_lib->getRTLStatus($language);
        $sessionData = array(
            'name' => $getUser['name'],
            'logger_photo' => $getUser['photo'],
            'loggedin_branch' => $getUser['branch_id'],
            'loggedin_email' => $getUser['email'],
            'loggedin_id' => $credential->id,
            'loggedin_userid' => $credential->user_id,
            'loggedin_role_id' => $credential->role,
            'loggedin_type' => 'student',
            'set_lang' => $language,
            'is_rtl' => $isRTL,
            'set_session_id' => $sessionID,
            'loggedin' => true,
        );
        $this->authentication_model->sessionSet($sessionData);
        redirect(base_url('dashboard'));
    }

    /**
     * @param array $enroll enroll table row
     */
    private function staff_can_access_enroll_row($enroll)
    {
        if (!is_superadmin_loggedin()) {
            if ((int) $enroll['branch_id'] !== (int) get_loggedin_branch_id()) {
                return false;
            }
        }
        $assigned_cs_list = $this->app_lib->get_ownClassSection();
        if ($assigned_cs_list === false || empty($assigned_cs_list)) {
            return true;
        }
        $class_id = (int) $enroll['class_id'];
        $section_id = (int) $enroll['section_id'];
        foreach ($assigned_cs_list as $class_key => $sections) {
            if ((int) $class_key !== $class_id) {
                continue;
            }
            foreach ($sections as $sec_id) {
                if ((int) $sec_id === $section_id) {
                    return true;
                }
            }
        }
        return false;
    }

    /* profile preview and information are updating here */
    public function profile($id = '')
    {
        // check access permission
        if (!get_permission('student', 'is_edit')) {
            access_denied();
        }
        $this->load->model('fees_model');
        $this->load->model('exam_model');
        $getStudent = $this->student_model->getSingleStudent($id, true);
        if (isset($_POST['update'])) {
            $this->session->set_flashdata('profile_tab', 1);
            $this->data['branch_id'] = $this->application_model->get_branch_id();
            $this->student_validation();
            // transport validation 
            $route_id = $this->input->post('route_id');
            $transport_fees_month = $this->input->post('transport_fees_month');
            $stoppage_point_id = $this->input->post('stoppage_point_id');
            if (!empty($transport_fees_month) || !empty($route_id)) {
                $this->form_validation->set_rules('route_id', translate('transport_route'), 'required');
                $this->form_validation->set_rules('vehicle_id', translate('vehicle_no'), 'required');
                $this->form_validation->set_rules('stoppage_point_id', translate('pickup_point'), 'required');
            }

            $this->form_validation->set_rules('parent_id', translate('guardian'), 'required');
            if ($this->form_validation->run() == true) {
                $post = $this->input->post();
                //save all student information in the database file
                $studentID = $this->student_model->save($post);
                //save student enroll information in the database file
                $arrayEnroll = array(
                    'class_id' => $this->input->post('class_id'),
                    'section_id' => $this->input->post('section_id'),
                    'roll' => $this->input->post('roll'),
                    'session_id' => $this->input->post('year_id'),
                    'branch_id' => $this->data['branch_id'],
                );
                $this->db->where('id', $getStudent['enrollid']);
                $this->db->update('enroll', $arrayEnroll);

                // transport data save in the database file 
                if (!empty($transport_fees_month)) {
                    $transportFees = [];
                    foreach ($transport_fees_month as $key => $fineID) {
                        $transportFees[] = array(
                            'enroll_id' => $getStudent['enrollid'],
                            'transport_fee_fine_id' => $fineID,
                            'stoppage_point_id' => $stoppage_point_id,
                            'month' => $fineID
                        );
                    }
                    $this->transport_fees_model->feeAssignUpdate($transportFees, $getStudent['enrollid']);
                } else {
                    $this->db->where('enroll_id', $getStudent['enrollid']);
                    $this->db->delete('transport_fee_details');
                }

                // handle custom fields data
                $class_slug = $this->router->fetch_class();
                $customField = $this->input->post("custom_fields[$class_slug]");
                if (!empty($customField)) {
                    saveCustomFields($customField, $id);
                }
                set_alert('success', translate('information_has_been_updated_successfully'));
                redirect(base_url('student/profile/' . $id));
            }
        }
        $this->data['student'] = $getStudent;
        $this->data['title'] = translate('student_profile');
        $this->data['sub_page'] = 'student/profile';
        $this->data['main_menu'] = 'student';
        $this->data['headerelements'] = array(
            'css' => array(
                'vendor/dropify/css/dropify.min.css',
                'vendor/bootstrap-multiselect/css/bootstrap-multiselect.css',

            ),
            'js' => array(
                'js/student.js',
                'vendor/dropify/js/dropify.min.js',
                'vendor/bootstrap-multiselect/js/bootstrap-multiselect.js',
            ),
        );
        $this->load->view('layout/index', $this->data);
    }

    // student document details are create here / ajax
    public function document_create()
    {
        if (!get_permission('student', 'is_edit')) {
            ajax_access_denied();
        }
        $this->form_validation->set_rules('document_title', translate('document_title'), 'trim|required');
        $this->form_validation->set_rules('document_category', translate('document_category'), 'trim|required');
        if (isset($_FILES['document_file']['name']) && empty($_FILES['document_file']['name'])) {
            $this->form_validation->set_rules('document_file', translate('document_file'), 'required');
        }
        if ($this->form_validation->run() !== false) {
            $insert_doc = array(
                'student_id' => $this->input->post('patient_id'),
                'title' => $this->input->post('document_title'),
                'type' => $this->input->post('document_category'),
                'remarks' => $this->input->post('remarks'),
            );

            // uploading file using codeigniter upload library
            $config['upload_path'] = './uploads/attachments/documents/';
            $config['allowed_types'] = 'gif|jpg|png|pdf|docx|csv|txt';
            $config['max_size'] = '2048';
            $config['encrypt_name'] = true;
            $this->upload->initialize($config);
            if ($this->upload->do_upload("document_file")) {
                $insert_doc['file_name'] = $this->upload->data('orig_name');
                $insert_doc['enc_name'] = $this->upload->data('file_name');
                $this->db->insert('student_documents', $insert_doc);
                set_alert('success', translate('information_has_been_saved_successfully'));
            } else {
                set_alert('error', strip_tags($this->upload->display_errors()));
            }
            $this->session->set_flashdata('documents_details', 1);
            echo json_encode(array('status' => 'success'));
        } else {
            $error = $this->form_validation->error_array();
            echo json_encode(array('status' => 'fail', 'error' => $error));
        }
    }

    // student document details are update here / ajax
    public function document_update()
    {
        if (!get_permission('student', 'is_edit')) {
            ajax_access_denied();
        }
        // validate inputs
        $this->form_validation->set_rules('document_title', translate('document_title'), 'trim|required');
        $this->form_validation->set_rules('document_category', translate('document_category'), 'trim|required');
        if ($this->form_validation->run() !== false) {
            $document_id = $this->input->post('document_id');
            $insert_doc = array(
                'title' => $this->input->post('document_title'),
                'type' => $this->input->post('document_category'),
                'remarks' => $this->input->post('remarks'),
            );
            if (isset($_FILES["document_file"]) && !empty($_FILES['document_file']['name'])) {
                $config['upload_path'] = './uploads/attachments/documents/';
                $config['allowed_types'] = 'gif|jpg|png|pdf|docx|csv|txt';
                $config['max_size'] = '2048';
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if ($this->upload->do_upload("document_file")) {
                    $exist_file_name = $this->input->post('exist_file_name');
                    $exist_file_path = FCPATH . 'uploads/attachments/documents/' . $exist_file_name;
                    if (file_exists($exist_file_path)) {
                        unlink($exist_file_path);
                    }
                    $insert_doc['file_name'] = $this->upload->data('orig_name');
                    $insert_doc['enc_name'] = $this->upload->data('file_name');
                    set_alert('success', translate('information_has_been_updated_successfully'));
                } else {
                    set_alert('error', strip_tags($this->upload->display_errors()));
                }
            }
            $this->db->where('id', $document_id);
            $this->db->update('student_documents', $insert_doc);
            echo json_encode(array('status' => 'success'));
            $this->session->set_flashdata('documents_details', 1);
        } else {
            $error = $this->form_validation->error_array();
            echo json_encode(array('status' => 'fail', 'error' => $error));
        }
    }

    // student document details are delete here
    public function document_delete($id)
    {
        if (get_permission('student', 'is_edit')) {
            $enc_name = $this->db->select('enc_name')->where('id', $id)->get('student_documents')->row()->enc_name;
            $file_name = FCPATH . 'uploads/attachments/documents/' . $enc_name;
            if (file_exists($file_name)) {
                unlink($file_name);
            }
            $this->db->where('id', $id);
            $this->db->delete('student_documents');
            $this->session->set_flashdata('documents_details', 1);
        }
    }

    public function document_details()
    {
        $id = $this->input->post('id');
        $this->db->where('id', $id);
        $query = $this->db->get('student_documents');
        $result = $query->row_array();
        echo json_encode($result);
    }

    // file downloader
    public function documents_download()
    {
        $encrypt_name = urldecode($this->input->get('file'));
        if (preg_match('/^[^.][-a-z0-9_.]+[a-z]$/i', $encrypt_name)) {
            $file_name = $this->db->select('file_name')->where('enc_name', $encrypt_name)->get('student_documents')->row()->file_name;
            if (!empty($file_name)) {
                $this->load->helper('download');
                force_download($file_name, file_get_contents('./uploads/attachments/documents/' . $encrypt_name));
            }
        }
    }

    /* sample csv downloader */
    public function csv_Sampledownloader()
    {
        $this->load->helper('download');
        $data = file_get_contents('uploads/multi_student_sample.csv');
        force_download("multi_student_sample.csv", $data);
    }

    /* validate here, if the check multi admission  email and roll */
    public function csvCheckExistsData($student_username = '', $roll = '', $registerno = '', $class_id = '', $section_id = '', $branchID = '', $unique_roll = '')
    {
        $array = array();
        if (!empty($roll)) {

            if ($unique_roll != 0) {
                if ($unique_roll == 2) {
                    $this->db->where('section_id', $section_id);
                }
                $this->db->where(array('roll' => $roll, 'class_id' => $class_id, 'branch_id' => $branchID));
                $rollQuery = $this->db->get('enroll');
                if ($rollQuery->num_rows() > 0) {
                    $array['status'] = false;
                    $array['message'] = "Roll Already Exists.";
                    return $array;
                }
            }
        }
        if ($student_username !== '') {
            $this->db->where('username', $student_username);
            $query = $this->db->get_where('login_credential');
            if ($query->num_rows() > 0) {
                $array['status'] = false;
                $array['message'] = "Student Username Already Exists.";
                return $array;
            }
        }
        if ($registerno !== '') {
            $this->db->where('register_no', $registerno);
            $query = $this->db->get_where('student');
            if ($query->num_rows() > 0) {
                $array['status'] = false;
                $array['message'] = "Student Register No Already Exists.";
                return $array;
            }
        } else {
            $array['status'] = false;
            $array['message'] = "Register No Is Required.";
            return $array;
        }

        $array['status'] = true;
        return $array;
    }

    // unique valid username verification is done here
    public function unique_username($username)
    {
        if ($this->input->post('student_id')) {
            $student_id = $this->input->post('student_id');
            $login_id = $this->app_lib->get_credential_id($student_id, 'student');
            $this->db->where_not_in('id', $login_id);
        }
        $this->db->where('username', $username);
        $query = $this->db->get('login_credential');
        if ($query->num_rows() > 0) {
            $this->form_validation->set_message("unique_username", translate('already_taken'));
            return false;
        } else {
            return true;
        }
    }

    /* unique valid guardian email address verification is done here */
    public function get_valid_guardian_username($username)
    {
        $this->db->where('username', $username);
        $query = $this->db->get('login_credential');
        if ($query->num_rows() > 0) {
            $this->form_validation->set_message("get_valid_guardian_username", translate('username_has_already_been_used'));
            return false;
        } else {
            return true;
        }
    }

    /* unique valid student roll verification is done here */
    public function unique_roll($roll)
    {
        if (empty($roll)) {
            return true;
        }
        $branchID = $this->application_model->get_branch_id();
        $schoolSettings = $this->student_model->get('branch', array('id' => $branchID), true, false, 'unique_roll');
        $unique_roll = $schoolSettings['unique_roll'];
        if (empty($unique_roll) && $unique_roll == 0) {
            return true;
        }

        $classID = $this->input->post('class_id');
        $sectionID = $this->input->post('section_id');
        if ($this->uri->segment(3)) {
            $studentID = $this->db->select('student_id')->where('id', $this->uri->segment(3))->get('enroll')->row()->student_id;
            $this->db->where_not_in('student_id', $studentID);
        }
        if ($unique_roll == 2) {
            $this->db->where('section_id', $sectionID);
        }
        $this->db->where(array('roll' => $roll, 'class_id' => $classID, 'branch_id' => $branchID, 'session_id' => get_session_id()));
        $q = $this->db->get('enroll')->num_rows();
        if ($q == 0) {
            return true;
        } else {
            $this->form_validation->set_message("unique_roll", translate('already_taken'));
            return false;
        }
    }

    /* unique valid register ID verification is done here */
    public function unique_registerid($register)
    {
        $branchID = $this->application_model->get_branch_id();
        if ($this->uri->segment(3)) {
            $studentID = $this->db->select('student_id')->where('id', $this->uri->segment(3))->get('enroll')->row()->student_id;
            $this->db->where_not_in('id', $studentID);
        }
        $this->db->where('register_no', $register);
        $query = $this->db->get('student')->num_rows();
        if ($query == 0) {
            return true;
        } else {
            $this->form_validation->set_message("unique_registerid", translate('already_taken'));
            return false;
        }
    }

    public function search()
    {
        // check access permission
        if (!get_permission('student', 'is_view')) {
            access_denied();
        }

        $search_text = $this->input->post('search_text');
        $this->data['query'] = $this->student_model->getSearchStudentList(trim($search_text));
        $this->data['title'] = translate('searching_results');
        $this->data['sub_page'] = 'student/search';
        $this->data['main_menu'] = '';
        $this->load->view('layout/index', $this->data);
    }

    /* student password change here */
    public function change_password()
    {
        if (get_permission('student', 'is_edit')) {
            if (!isset($_POST['authentication'])) {
                $this->form_validation->set_rules('password', translate('password'), 'trim|required|min_length[4]');
            } else {
                $this->form_validation->set_rules('date', translate('date'), 'trim|required');
                $this->form_validation->set_rules('reason_id', translate('disable_reason'), 'trim|required');
            }
            if ($this->form_validation->run() !== false) {
                $studentID = $this->input->post('student_id');
                $password = $this->input->post('password');
                if (!isset($_POST['authentication'])) {
                    $this->db->where('role', 7);
                    $this->db->where('user_id', $studentID);
                    $this->db->update('login_credential', array('password' => $this->app_lib->pass_hashed($password)));
                } else {
                    $this->db->where('role', 7);
                    $this->db->where('user_id', $studentID);
                    $this->db->update('login_credential', array('active' => 0));

                    $this->db->where('id', $studentID);
                    $this->db->update('student', array('active' => 0));

                    // insert disable reason history in DB
                    $insertData = array(
                        'student_id' => $studentID,
                        'reason_id' => $this->input->post('reason_id'),
                        'note' => $this->input->post('note'),
                        'date' => date("Y-m-d", strtotime($this->input->post('date'))),
                    );
                    $this->db->insert('disable_reason_details', $insertData);
                }
                set_alert('success', translate('information_has_been_updated_successfully'));
                $array  = array('status' => 'success');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
        }
    }

    // student quick details
    public function quickDetails()
    {
        $id = $this->input->post('enroll_id');
        $this->db->select('student.*,enroll.student_id,enroll.roll,student_category.name as cname');
        $this->db->from('enroll');
        $this->db->join('student', 'student.id = enroll.student_id', 'inner');
        $this->db->join('student_category', 'student_category.id = student.category_id', 'left');
        $this->db->where('enroll.id', $id);
        $row = $this->db->get()->row();
        $data['photo'] = get_image_url('student', $row->photo);
        $data['full_name'] = $row->first_name . " " . $row->last_name;
        $data['student_category'] = $row->cname;
        $data['register_no'] = $row->register_no;
        $data['roll'] = $row->roll;
        $data['gender'] = translate($row->gender);
        $data['admission_date'] = empty($row->admission_date) ? "N/A" : _d($row->admission_date);
        $data['birthday'] = empty($row->birthday) ? "N/A" : _d($row->birthday);
        $data['blood_group'] = empty($row->blood_group) ? "N/A" : $row->blood_group;
        $data['religion'] = empty($row->religion) ? "N/A" : $row->religion;
        $data['email'] = $row->email;
        $data['mobileno'] = empty($row->mobileno) ? "N/A" : $row->mobileno;
        $data['state'] = empty($row->state) ? "N/A" : $row->state;
        $data['address'] = empty($row->current_address) ? "N/A" : $row->current_address;
        echo json_encode($data);
    }

    /* student information delete here */
    public function delete_data($eid = '', $sid = '')
    {
        if (get_permission('student', 'is_delete')) {
            $branchID = get_type_name_by_id('enroll', $eid, 'branch_id');
            // Check student restrictions
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('student_id', $sid)->delete('enroll');
            if ($this->db->affected_rows() > 0) {
                $this->db->where('id', $sid)->delete('student');
                $this->db->where(array('user_id' => $sid, 'role' => 7))->delete('login_credential');

                $r = $this->db->select('id')->where('student_id', $sid)->get('fee_allocation')->result_array();
                $this->db->where_in('student_id', $sid)->delete('fee_allocation');
                $r = array_column($r, 'id');
                if (!empty($r)) {
                    $this->db->where_in('allocation_id', $r)->delete('fee_payment_history');
                }

                $get_field = $this->db->where(array('form_to' => 'student', 'branch_id' => $branchID))->get('custom_field')->result_array();
                $field_id = array_column($get_field, 'id');
                $this->db->where('relid', $sid);
                $this->db->where_in('field_id', $field_id);
                $this->db->delete('custom_fields_values');
            }
        }
    }

    public function bulk_delete()
    {
        if ($_POST) {
            $status = 'success';
            $message = translate('information_deleted');
            if (get_permission('student', 'is_delete')) {
                $arrayID = $this->input->post('array_id');
                foreach ($arrayID as $key => $row) {
                    $branchID = $this->student_model->get('enroll', ['student_id' => $row], true, false, 'branch_id')['branch_id'];
                    if (!is_superadmin_loggedin()) {
                        $branchID = get_loggedin_branch_id();
                    }
                    $get_field = $this->db->where(array('form_to' => 'student', 'branch_id' => $branchID))->get('custom_field')->result_array();
                    $field_id = array_column($get_field, 'id');
                    $this->db->where('relid', $row);
                    $this->db->where_in('field_id', $field_id);
                    $this->db->delete('custom_fields_values');
                }

                if (!is_superadmin_loggedin()) {
                    $this->db->where('branch_id', get_loggedin_branch_id());
                }
                $this->db->where_in('student_id', $arrayID)->delete('enroll');
                if ($this->db->affected_rows() > 0) {
                    $this->db->where_in('id', $arrayID)->delete('student');
                    $this->db->where_in('user_id', $arrayID)->where('role', 7)->delete('login_credential');

                    $r = $this->db->select('id')->where_in('student_id', $arrayID)->get('fee_allocation')->result_array();
                    $this->db->where_in('student_id', $arrayID)->delete('fee_allocation');
                    $r = array_column($r, 'id');
                    if (!empty($r)) {
                        $this->db->where_in('allocation_id', $r)->delete('fee_payment_history');
                    }
                }
            } else {
                $message = translate('access_denied');
                $status = 'error';
            }
            echo json_encode(array('status' => $status, 'message' => $message));
        }
    }


    /* student login credential list by class and section */
    public function login_credential_reports()
    {
        // check access permission
        if (!get_permission('student', 'is_view')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        if (isset($_POST['search'])) {
            $classID = $this->input->post('class_id');
            $sectionID = $this->input->post('section_id');
            $this->data['students'] = $this->application_model->getStudentListByClassSection($classID, $sectionID, $branchID, false, true);
        }
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('login_credential');
        $this->data['main_menu'] = 'student_repots';
        $this->data['sub_page'] = 'student/login_credential_reports';
        $this->load->view('layout/index', $this->data);
    }

    public function password_reset($type)
    {
        if ($_POST) {
            $this->form_validation->set_rules('new_password', 'New Password', 'trim|required|min_length[4]');
            $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|required|min_length[4]|matches[new_password]');
            if ($this->form_validation->run() == true) {
                $new_password = $this->input->post('new_password');
                if (!empty($type)) {
                    if ($type == 'student') {
                        $student_id = $this->input->post('student_id');
                        if (!is_superadmin_loggedin()) {
                            $chkID = $this->db->select('id')->where(['student_id' => $student_id, 'branch_id' => get_loggedin_branch_id()])->get('enroll')->row();
                            if (empty($chkID)) {
                                exit;
                            }
                        }
                        $this->db->where('user_id', $student_id);
                        $this->db->where('role', 7);
                    }
                    if ($type == 'parent') {
                        $parent_id = $this->input->post('parent_id');
                        if (!is_superadmin_loggedin()) {
                            $chkID = $this->db->select('id')->where(['id' => $parent_id, 'branch_id' => get_loggedin_branch_id()])->get('parent')->row();
                            if (empty($chkID)) {
                                exit;
                            }
                        }
                        $this->db->where('user_id', $parent_id);
                        $this->db->where('role', 6);
                    }
                    $this->db->update('login_credential', array('password' => $this->app_lib->pass_hashed($new_password)));
                }
                set_alert('success', translate('information_has_been_updated_successfully'));
                $array = array('status' => 'success');
            } else {
                $error = $this->form_validation->error_array();
                $array = array('status' => 'fail', 'error' => $error);
            }
            echo json_encode($array);
        }
    }

    /* student admission list by date */
    public function admission_reports()
    {
        // check access permission
        if (!get_permission('student', 'is_view')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        if (isset($_POST['search'])) {
            $classID = $this->input->post('class_id');
            $sectionID = $this->input->post('section_id');
            $daterange = explode(' - ', $this->input->post('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->data['start'] = $start;
            $this->data['end'] = $end;
            $this->data['students'] = $this->student_model->getStudentList($classID, $sectionID, $branchID, false, $start, $end)->result_array();
        }
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('admission_reports');
        $this->data['main_menu'] = 'student_repots';
        $this->data['sub_page'] = 'student/admission_reports';
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

    public function classsection_reports()
    {
        // check access permission
        if (!get_permission('student', 'is_view')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('class_&_section');
        $this->data['main_menu'] = 'student_repots';
        $this->data['sub_page'] = 'student/classsection_reports';
        $this->load->view('layout/index', $this->data);
    }

    // add new student deactivate reason
    public function disable_reason()
    {
        if (isset($_POST['disable_reason'])) {
            if (!get_permission('disable_reason', 'is_add')) {
                access_denied();
            }
            if (is_superadmin_loggedin()) {
                $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
            }
            $this->form_validation->set_rules('name', translate('reason'), 'trim|required');
            if ($this->form_validation->run() !== false) {
                $arrayData = array(
                    'name' => $this->input->post('name'),
                    'branch_id' => $this->application_model->get_branch_id(),
                );
                $this->db->insert('disable_reason', $arrayData);
                set_alert('success', translate('information_has_been_saved_successfully'));
                redirect(base_url('student/disable_reason'));
            }
        }
        $this->data['title'] = translate('deactivate_reason');
        $this->data['categorylist'] = $this->app_lib->getTable('disable_reason');
        $this->data['sub_page'] = 'student/disable_reason';
        $this->data['main_menu'] = 'student';
        $this->load->view('layout/index', $this->data);
    }

    // update existing student deactivate reason
    public function disable_reason_edit()
    {
        if (!get_permission('disable_reason', 'is_edit')) {
            ajax_access_denied();
        }
        if (is_superadmin_loggedin()) {
            $this->form_validation->set_rules('branch_id', translate('branch'), 'required');
        }
        $this->form_validation->set_rules('name', translate('reason'), 'trim|required');
        if ($this->form_validation->run() !== false) {
            $category_id = $this->input->post('reason_id');
            $arrayData = array(
                'name' => $this->input->post('name'),
                'branch_id' => $this->application_model->get_branch_id(),
            );
            $this->db->where('id', $category_id);
            $this->db->update('disable_reason', $arrayData);
            set_alert('success', translate('information_has_been_updated_successfully'));
            $array  = array('status' => 'success');
        } else {
            $error = $this->form_validation->error_array();
            $array = array('status' => 'fail', 'error' => $error);
        }
        echo json_encode($array);
    }

    // delete student deactivate reason from database
    public function disable_reason_delete($id)
    {
        if (get_permission('disable_reason', 'is_delete')) {
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $this->db->where('id', $id);
            $this->db->delete('disable_reason');
        }
    }

    // student disable reason details send by ajax
    public function disableReasonDetails()
    {
        if (get_permission('disable_reason', 'is_edit')) {
            $id = $this->input->post('id');
            $this->db->where('id', $id);
            if (!is_superadmin_loggedin()) {
                $this->db->where('branch_id', get_loggedin_branch_id());
            }
            $query = $this->db->get('disable_reason');
            $result = $query->row_array();
            echo json_encode($result);
        }
    }

    public function sibling_report()
    {
        // check access permission
        if (!get_permission('student', 'is_view')) {
            access_denied();
        }
        $branchID = $this->application_model->get_branch_id();
        if (isset($_POST['search'])) {
            $classID = $this->input->post('class_id');
            $sectionID = $this->input->post('section_id');
            $getParentsList = $this->student_model->getParentList($classID, $sectionID, $branchID);
            $list = array();
            foreach ($getParentsList as $key => $parent) {
                if (intval($parent['child']) > 1) {
                    $getParentsList[$key]['student'] = $this->student_model->getSiblingListByClass($parent['parent_id'], $classID, $sectionID);
                    $list[] = $getParentsList[$key];
                }
            }
            $this->data['students'] = $list;
        }
        $this->data['branch_id'] = $branchID;
        $this->data['title'] = translate('sibling_report');
        $this->data['main_menu'] = 'student_repots';
        $this->data['sub_page'] = 'student/sibling_report';
        $this->load->view('layout/index', $this->data);
    }

    public function get_students_by_class_section()
    {
        $class_id   = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');

        $students = $this->student_model->getStudentsByClassSection($class_id, $section_id);

        $options = "<option value=''>Select Student</option>";
        foreach ($students as $stu) {
            $options .= "<option value='" . $stu['id'] . "'>" . $stu['first_name'] . " " . $stu['last_name'] . "</option>";
        }

        echo $options;
    }

    public function live_search()
    {
        $query = $this->input->post('query');
        $branch_id = $this->input->post('branch_id');
        // $this->db->select('*');
        $this->db->select('student.register_no, student.first_name, student.last_name, student.mobileno,enroll.id as enroll_id,enroll.branch_id, class.name as class_name, section.name as section_name,sfa.id AS allocation_id,
        sfa.fee_type_id,
        sfa.amount,
        sfa.due_date,
        ft.fee_name');

        $this->db->from('student');
        $this->db->join('enroll', 'enroll.student_id = student.id', 'left');
        // $this->db->join('parent', 'parent.id = student.parent_id', 'left');
        $this->db->join('class', 'class.id = enroll.class_id', 'left');
        $this->db->join('section', 'section.id = enroll.section_id', 'left');
        $this->db->join('student_fee_allocation AS sfa', 'sfa.student_id = student.id', 'inner');
        $this->db->join('fees_type AS ft', 'ft.id = sfa.fee_type_id', 'inner');


        if (!empty($branch_id)) {
            $this->db->where('enroll.branch_id', $branch_id);
        }
        if (!empty($query)) {
            $this->db->group_start();
            $this->db->like('register_no', $query);
            $this->db->or_like('first_name', $query);
            $this->db->or_like('last_name', $query);
            $this->db->or_like('mobileno', $query);
            $this->db->group_end();
        }
        $this->db->group_by('student.id');
        $result = $this->db->get()->result_array();

        $output = '<table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Admission No</th>
                        <th>Student Name</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Mobile</th>
                        <th>Action</th>
                    </tr>
                </thead><tbody>';

        if (!empty($result)) {
            foreach ($result as $row) {
                $invoice_url = base_url('fees/invoice/' . $row['enroll_id']);
                $profile_url = base_url('student/profile/' . $row['enroll_id']);
                $output .= '<tr>
                        <td><a href="' . $invoice_url . '">' . $row['register_no'] . '</a></td>
                        <td><a href="' . $profile_url . '">' . $row['first_name'] . ' ' . $row['last_name'] . '</a></td>
                        <td>' . $row['class_name'] . '</td>
                        <td>' . $row['section_name'] . '</td>
                        <td>' . $row['mobileno'] . '</td>
                        <td><a href="' . $invoice_url . '">' . 'Collect' . '</a></td>
                    </tr>';
            }
        } else {
            $output .= '<tr><td colspan="5" class="text-center">No students found</td></tr>';
        }
        $output .= '</tbody></table>';

        // print_r($result);exit;
        echo $output;
    }
    public function live_search_for_fees_card()
    {
        $query     = $this->input->post('query');
        $branch_id = $this->input->post('branch_id');

        $this->db->select('student.id, student.first_name, student.last_name, enroll.id as enroll_id');
        $this->db->from('student');
        $this->db->join('enroll', 'enroll.student_id = student.id', 'left');

        if ($branch_id) {
            $this->db->where('enroll.branch_id', $branch_id);
        }

        if ($query) {
            $this->db->group_start();
            $this->db->like('student.first_name', $query);
            $this->db->or_like('student.last_name', $query);
            $this->db->or_like('student.register_no', $query);
            $this->db->group_end();
        }

        $this->db->limit(10);
        $students = $this->db->get()->result_array();

        if (empty($students)) {
            echo '<div class="list-group-item">No student found</div>';
            return;
        }

        foreach ($students as $s) {
            echo '
        <a href="javascript:void(0)" 
           class="list-group-item list-group-item-action student-select"
           data-enroll="' . $s['enroll_id'] . '">
           ' . $s['first_name'] . ' ' . $s['last_name'] . '
        </a>';
        }
    }

    // public function live_search()
    // {
    //     $query = $this->input->post('query', true);

    //     $this->db->select('
    //         student.id                AS student_id,
    //         student.register_no,
    //         student.first_name,
    //         student.last_name,
    //         student.mobileno,
    //         enroll.id                 AS enroll_id,
    //         class.name                AS class_name,
    //         section.name              AS section_name,
    //         sfa.id                    AS allocation_id,
    //         sfa.fee_type_id,
    //         sfa.amount,
    //         sfa.due_date,
    //         ft.fee_name
    //     ');
    //     $this->db->from('student');
    //     $this->db->join('enroll',  'enroll.student_id = student.id', 'inner');
    //     $this->db->join('class',   'class.id = enroll.class_id',     'left');
    //     $this->db->join('section', 'section.id = enroll.section_id', 'left');

    //     // only students that HAVE allocations
    //     $this->db->join('student_fee_allocation AS sfa', 'sfa.student_id = student.id', 'inner');
    //     $this->db->join('fee_types AS ft',               'ft.id = sfa.fee_type_id',     'inner');

    //     if (!empty($query)) {
    //         $this->db->group_start()
    //                  ->like('student.register_no', $query)
    //                  ->or_like('student.first_name', $query)
    //                  ->or_like('student.last_name',  $query)
    //                  ->or_like('student.mobileno',   $query)
    //                  ->group_end();
    //     }

    //     // optional ordering (by due date, then register no)
    //     $this->db->order_by('sfa.due_date', 'ASC')
    //              ->order_by('student.register_no', 'ASC');

    //     $rows = $this->db->get()->result_array();

    //     // build result table
    //     $html = '<table class="table table-bordered table-striped">
    //         <thead>
    //             <tr>
    //                 <th>Admission No</th>
    //                 <th>Student Name</th>
    //                 <th>Class</th>
    //                 <th>Section</th>
    //                 <th>Mobile</th>
    //                 <th>Fee Type</th>
    //                 <th>Amount</th>
    //                 <th>Due Date</th>
    //                 <th>Action</th>
    //             </tr>
    //         </thead><tbody>';

    //     if (!empty($rows)) {
    //         foreach ($rows as $r) {
    //             // detail/collect page needs allocation_id + fee_type_id
    //             $collect_url = base_url('fees/invoice/' . $r['allocation_id'] . '/' . $r['fee_type_id']);
    //             $profile_url = base_url('student/profile/' . $r['student_id']);

    //             $html .= '<tr>
    //                 <td>'.htmlspecialchars($r['register_no']).'</td>
    //                 <td><a href="'.$profile_url.'">'.htmlspecialchars($r['first_name'].' '.$r['last_name']).'</a></td>
    //                 <td>'.htmlspecialchars($r['class_name']).'</td>
    //                 <td>'.htmlspecialchars($r['section_name']).'</td>
    //                 <td>'.htmlspecialchars($r['mobileno']).'</td>
    //                 <td>'.htmlspecialchars($r['fee_name']).'</td>
    //                 <td>'.htmlspecialchars(number_format((float)$r['amount'], 2)).'</td>
    //                 <td>'.htmlspecialchars($r['due_date']).'</td>
    //                 <td>
    //                     <a href="'.$collect_url.'" class="btn btn-sm btn-primary">Collect</a>
    //                 </td>
    //             </tr>';
    //         }
    //     } else {
    //         $html .= '<tr><td colspan="9" class="text-center">No students found</td></tr>';
    //     }

    //     $html .= '</tbody></table>';
    //     echo $html;
    // }

    // public function searchParent()
    // {
    //     $parent_name = $this->input->get('parent_name');
    //     $parents = $this->Parents_model->get_parents_by_name($parent_name);

    //     if (empty($parents)) {
    //         echo json_encode(['error' => 'No parent found with this name.']);
    //         return;
    //     }

    //     $allStudents = [];
    //     $allFeeTypes = [];

    //     foreach ($parents as $parent) {
    //         $students = $this->Student_model->get_students_by_parent($parent->id);

    //         foreach ($students as $stu) {
    //             // Fetch fee types for this student
    //             $fees = $this->Student_model->get_student_fees($stu->id);

    //             $feeData = [];
    //             foreach ($fees as $f) {
    //                 $feeData[$f->fee_type] = $f->amount;
    //                 $allFeeTypes[$f->fee_type] = true; // collect unique fee types
    //             }

    //             $stu->fees = $feeData;
    //             $allStudents[] = $stu;
    //         }
    //     }

    //     echo json_encode([
    //         'parents'   => $parents,
    //         'students'  => $allStudents,
    //         'fee_types' => array_keys($allFeeTypes) // headers
    //     ]);
    // }
    public function searchParent()
    {
        $parent_name = $this->input->get('parent_name');
        $parents = $this->Parents_model->get_parents_by_name($parent_name);

        if (empty($parents)) {
            echo json_encode(['error' => 'No parent found with this name.']);
            return;
        }

        $allStudents = [];
        $monthsSet = [];   // unique months present across children
        $feeTypesSet = []; // unique fee types across children (optional)

        foreach ($parents as $parent) {
            $students = $this->Student_model->get_students_by_parent($parent->id);
            foreach ($students as $stu) {
                // get allocations / fee types for this student
                $feeRows = $this->Student_model->get_student_fees($stu->id);
                // print_r($feeRows);exit;
                $stu_fees = [];

                foreach ($feeRows as $fr) {
                    // fr->month may be "1,2,3" or "4"
                    $monthsList = array_filter(array_map('trim', explode(',', $fr->month)));
                    foreach ($monthsList as $m) {
                        if ($m === '') continue;
                        $mInt = (int) $m;
                        $stu_fees[] = (object) [
                            'fee_type_id' => $fr->fee_type_id,
                            'fee_type'    => $fr->fee_type,
                            'allocation_id' => $fr->allocation_id,
                            'month'       => $mInt,
                            'amount'      => (float) $fr->amount,
                            'fine_calculated_amount' => (float) $fr->fine_calculated_amount
                        ];
                        // print_r($stu_fees);exit;
                        $monthsSet[$mInt] = true;
                        $feeTypesSet[$fr->fee_type_id] = $fr->fee_type;
                    }
                }

                // attach fees (expanded per-month) to the student object
                $stu->fees = $stu_fees;
                // (optional) keep allocated/paid/balance fields if your get_students_by_parent returns them
                $allStudents[] = $stu;
            }
        }

        // prepare months array sorted ascending
        $months = array_keys($monthsSet);
        sort($months, SORT_NUMERIC);

        // optionally prepare fee_types array
        $fee_types = [];
        foreach ($feeTypesSet as $id => $name) {
            $fee_types[] = ['id' => (int)$id, 'name' => $name];
        }

        echo json_encode([
            'parents'   => $parents,
            'students'  => $allStudents,
            'months'    => $months,
            'fee_types' => $fee_types
        ]);
    }
}
