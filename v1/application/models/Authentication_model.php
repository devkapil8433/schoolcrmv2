<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Authentication_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('user_agent');
    }

    // checking login credential
    public function login_credential($username, $password)
    {
        $this->db->select('*');
        $this->db->from('login_credential');
        $this->db->where('username', $username);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            $verify_password = $this->app_lib->verify_password($password, $query->row()->password);
            if ($verify_password) {
                return $query->row();
            }
        }
        return false;
    }

    public function getUserNameByRoleID($roleID, $userID = '')
    {
        if ($roleID == 6) {
            $sql = "SELECT `name`,`email`,`mobileno`,`photo`,`branch_id` FROM `parent` WHERE `id` = " . $this->db->escape($userID);
            return $this->db->query($sql)->row_array();
        } elseif ($roleID == 7) {
            $sql = "SELECT `student`.`id`, `mobileno`, CONCAT_WS(' ',`student`.`first_name`, `student`.`last_name`) as `name`, `student`.`email`, `student`.`photo`, `enroll`.`branch_id` FROM `enroll` INNER JOIN `student` ON `student`.`id` = `enroll`.`student_id` WHERE `student`.`id` = " . $this->db->escape($userID) . " LIMIT 1";
            return $this->db->query($sql)->row_array();
        } else {
            $sql = "SELECT `name`,`mobileno`,`email`,`photo`,`branch_id` FROM `staff` WHERE `id` = " . $this->db->escape($userID);
            return $this->db->query($sql)->row_array();
        }
    }

    // password forgotten
    public function lose_password($username)
    {
        if (!empty($username)) {
            $this->db->select('*');
            $this->db->from('login_credential');
            $this->db->where('username', $username);
            $this->db->limit(1);
            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                $login_credential = $query->row();
                $getUser = $this->getUserNameByRoleID($login_credential->role, $login_credential->user_id);
                $key = hash('sha512', $login_credential->role . $login_credential->username . app_generate_hash());
                $query = $this->db->get_where('reset_password', array('login_credential_id' => $login_credential->id));
                if ($query->num_rows() > 0) {
                    $this->db->where('login_credential_id', $login_credential->id);
                    $this->db->delete('reset_password');
                }
                $arrayReset = array(
                    'key' => $key,
                    'login_credential_id' => $login_credential->id,
                    'username' => $login_credential->username,
                );
                $this->db->insert('reset_password', $arrayReset);
                // send email for forgot password
                $this->load->model('email_model');
                $arrayData = array(
                    'role' => $login_credential->role,
                    'branch_id' => $getUser['branch_id'],
                    'username' => $login_credential->username,
                    'name' => $getUser['name'],
                    'reset_url' => base_url('authentication/pwreset?key=' . $key),
                    'email' => $getUser['email'],
                );
                $this->email_model->sentForgotPassword($arrayData);
                return true;
            }
        }
        return false;
    }

    public function urlaliasToBranch($url_alias)
    {
        $saasExisting = $this->app_lib->isExistingAddon('saas');
        if ($saasExisting && $this->db->table_exists("custom_domain")) {
            $getDomain = $this->getCurrentDomain();
            if (!empty($getDomain)) {
                return $getDomain->school_id;
            }
        }

        $get = $this->db->select('branch_id')->where('url_alias', $url_alias)->get('front_cms_setting')->row_array();
        if (empty($url_alias) || empty($get)) {
            return null;
        } else {
            return $get['branch_id'];
        }
    }

    public function getSegment($id = '')
    {
        $segment = $this->uri->segment($id);
        if (empty($segment)) {
            return '';
        } else {
            return $segment . '/';
        }
    }

    public function getCurrentDomain()
    {
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $url = rtrim($url, '/');
        $domain = parse_url($url, PHP_URL_HOST);
        if (substr($domain, 0, 4) == 'www.') {
            $domain = str_replace('www.', '', $domain);
        }
        $getDomain = $this->db->select('school_id')->get_where('custom_domain', array('status' => 1, 'url' => $domain))->row();
        return $getDomain;
    }

    public function getDefaultLoginBranchId()
    {
        $cmsBranch = (int) get_global_setting('cms_default_branch');
        if ($cmsBranch > 0) {
            return $cmsBranch;
        }

        $row = $this->db->select('id')
            ->from('branch')
            ->where('status', 1)
            ->order_by('id', 'asc')
            ->limit(1)
            ->get()
            ->row_array();

        return !empty($row['id']) ? (int) $row['id'] : 0;
    }

    public function getSchoolDeatls($url_alias = '', $branch_id = 0)
    {
        $branchID = (int) $branch_id;
        $saasExisting = $this->app_lib->isExistingAddon('saas');
        if ($branchID < 1 && $saasExisting && $this->db->table_exists("custom_domain")) {
            $getDomain = $this->getCurrentDomain();
            if (!empty($getDomain)) {
                $branchID = (int) $getDomain->school_id;
            }
        }

        if ($branchID < 1 && !empty($url_alias)) {
            $branchID = (int) $this->urlaliasToBranch($url_alias);
        }

        if ($branchID < 1) {
            $branchID = $this->getDefaultLoginBranchId();
        }

        if ($branchID < 1) {
            return '';
        }

        $this->db->select('fs.facebook_url,fs.twitter_url,fs.linkedin_url,fs.youtube_url,branch.address,branch.school_name,branch.city,branch.state,branch.mobileno,branch.email');
        $this->db->from('branch');
        $this->db->join('front_cms_setting as fs', 'fs.branch_id = branch.id', 'left');
        $this->db->where('branch.id', $branchID);
        $get = $this->db->get()->row();

        if (empty($get)) {
            return '';
        }

        if (!empty($get->city) || !empty($get->state)) {
            $parts = array_filter(array($get->address, $get->city, $get->state));
            $get->address = implode(', ', $parts);
        }

        return $get;
    }

    function sessionSet($sessionData = [])
    {
        $this->session->set_userdata($sessionData);
        $this->db->update('login_credential', array('last_login' => date('Y-m-d H:i:s')), array('id' => $sessionData['loggedin_id']));
        // login Log details save in DB here
        $this->loginLog($sessionData['loggedin_userid'], $sessionData['loggedin_role_id'], $sessionData['loggedin_branch']);
    }

    function loginLog($userID = 0, $role = 0, $branchID = '')
    {
        if ($this->agent->is_browser()) {
            $browser = $this->agent->browser() . ' ' . $this->agent->version();
        } elseif ($this->agent->is_robot()) {
            $browser = $this->agent->robot();
        } elseif ($this->agent->is_mobile()) {
            $browser = $this->agent->mobile();
        } else {
            $browser = 'Unknown';
        }
        $ip_address = $this->input->ip_address();
        $data = array(
            'user_id' => $userID,
            'role' => $role,
            'ip' =>  ($ip_address == "::1" ? "127.0.0.1" : $ip_address),
            'platform' => $this->agent->platform(),
            'browser' => $browser,
            'timestamp'   => date('Y-m-d H:i:s'),
            'branch_id'   => $branchID,
        );
        $this->db->insert('login_log', $data);
    }

    public function getStudentLoginStatus($id = '')
    {
        $get = $this->db->select('IFNULL(student_login, 1) as login')->where('id', $id)->get('branch')->row()->login;
        return $get;
    }
    
    public function getParentLoginStatus($id = '')
    {
        $get = $this->db->select('IFNULL(parent_login, 1) as login')->where('id', $id)->get('branch')->row()->login;
        return $get;
    }

    public function getLoginSliderImages($branch_id = 0)
    {
        $images = array();
        $branch_id = (int) $branch_id;

        $images = $this->collectUniqueImageUrls(FCPATH . 'uploads/login_image/', 'uploads/login_image/', 'slider_*.jpg');

        if (empty($images) && $branch_id > 0) {
            $sliders = $this->db->select('image')
                ->from('front_cms_home')
                ->where(array('active' => 1, 'branch_id' => $branch_id, 'item_type' => 'slider'))
                ->order_by('id', 'asc')
                ->get()->result_array();

            foreach ($sliders as $slider) {
                if (!empty($slider['image'])) {
                    $file = FCPATH . 'uploads/frontend/slider/' . $slider['image'];
                    if (is_file($file)) {
                        $images[] = base_url('uploads/frontend/slider/' . $slider['image']);
                    }
                }
            }
        }

        if (empty($images)) {
            $images = $this->collectUniqueImageUrls(FCPATH . 'assets/login_page/image/', 'assets/login_page/image/', 'slider-*.jpg');
        }

        if (empty($images)) {
            $images = $this->collectUniqueImageUrls(FCPATH . 'uploads/frontend/slider/', 'uploads/frontend/slider/');
        }

        if (empty($images)) {
            $fallbacks = array(
                'uploads/frontend/home_page/featured-parallax1.jpg',
                'uploads/frontend/home_page/counter-parallax1.jpg',
                'uploads/frontend/about/service_parallax1.jpg',
            );
            foreach ($fallbacks as $relative) {
                if (is_file(FCPATH . $relative)) {
                    $images[] = base_url($relative);
                }
            }
        }

        return array_values(array_unique($images));
    }

    public function getLoginSideboxImage($branch_id = 0, $slider_images = array())
    {
        $candidates = array(
            FCPATH . 'assets/login_page/image/sidebox.jpg' => 'assets/login_page/image/sidebox.jpg',
            FCPATH . 'uploads/login_image/slider_1.jpg' => 'uploads/login_image/slider_1.jpg',
        );

        foreach ($candidates as $file => $relative) {
            if (is_file($file)) {
                return base_url($relative);
            }
        }

        if (!empty($slider_images[0])) {
            return $slider_images[0];
        }

        $fallbacks = array(
            'uploads/frontend/home_page/featured-parallax1.jpg',
            'uploads/frontend/about/service_parallax1.jpg',
        );

        foreach ($fallbacks as $relative) {
            if (is_file(FCPATH . $relative)) {
                return base_url($relative);
            }
        }

        return base_url('assets/login_page/image/sidebox.jpg');
    }

    private function collectUniqueImageUrls($directory, $urlPrefix, $pattern = '*.{jpg,jpeg,png,JPG,JPEG,PNG}')
    {
        $images = array();
        $seen = array();

        if (!is_dir($directory)) {
            return $images;
        }

        $files = glob(rtrim($directory, '/') . '/' . $pattern, GLOB_BRACE);
        if (empty($files)) {
            return $images;
        }

        sort($files);
        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }
            $hash = @md5_file($file);
            if ($hash === false || isset($seen[$hash])) {
                continue;
            }
            $seen[$hash] = true;
            $images[] = base_url($urlPrefix . basename($file));
        }

        return $images;
    }
}
