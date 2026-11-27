<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Jitendra Debugger — CI4-style toolbar for EdSchool (subclass prefix MY_).
 */
class MY_Profiler extends CI_Profiler
{
	/** @var array Keys / substrings to redact from config & server dumps */
	protected $_secret_patterns = array(
		'password', 'passwd', 'secret', 'encryption_key', 'api_key', 'apikey',
		'token', 'auth', 'credential', 'private', 'aws_access', 'aws_secret',
		'client_secret', 'smtp_pass', 'db_pass', 'authorization',
	);

	/**
	 * @param array $config
	 */
	public function __construct($config = array())
	{
		if ( ! in_array('files', $this->_available_sections, TRUE))
		{
			$this->_available_sections[] = 'files';
		}
		parent::__construct($config);
	}

	/**
	 * @return string
	 */
	public function run()
	{
		$charset = config_item('charset') ?: 'UTF-8';
		$css_href = base_url('assets/css/ci-debug-toolbar.css');
		$js_href = base_url('assets/js/ci-debug-toolbar.js');

		$data = array(
			'timeline' => $this->_collect_benchmarks(),
			'database' => $this->_collect_queries(),
			'files'    => $this->_collect_files(),
			'request'  => $this->_collect_request(),
			'routes'   => $this->_collect_routes(),
			'headers'  => $this->_collect_headers(),
			'cookies'  => $this->_collect_cookies(),
			'session'  => $this->_collect_session(),
			'user'     => $this->_collect_user(),
			'logs'     => $this->_collect_logs(),
			'hooks'    => $this->_collect_hooks(),
			'config'   => $this->_collect_config(),
			'vars'     => $this->_collect_vars(),
		);

		$total_time = isset($this->CI->benchmark)
			? $this->CI->benchmark->elapsed_time('total_execution_time_start', 'total_execution_time_end')
			: '';
		if ($total_time === '' && ! empty($data['timeline']['total']))
		{
			$total_time = $data['timeline']['total'];
		}
		$memory = $this->_memory_usage();
		$peak = function_exists('memory_get_peak_usage') ? $this->_format_bytes((int) memory_get_peak_usage()) : $memory;

		$tabs = array(
			'timeline' => array('label' => 'Timeline', 'badge' => $total_time !== '' ? $total_time.'s' : ''),
			'database' => array('label' => 'Database', 'badge' => (string) $data['database']['count']),
			'files'    => array('label' => 'Files', 'badge' => (string) $data['files']['total']),
			'views'    => array('label' => 'Views', 'badge' => (string) $data['files']['view_count']),
			'routes'   => array('label' => 'Routes', 'badge' => ''),
			'request'  => array('label' => 'Request', 'badge' => htmlspecialchars((string) $data['request']['method'], ENT_QUOTES, $charset)),
			'headers'  => array('label' => 'Headers', 'badge' => (string) count($data['headers']['request'])),
			'cookies'  => array('label' => 'Cookies', 'badge' => (string) count($data['cookies'])),
			'session'  => array('label' => 'Session', 'badge' => (string) count($data['session'])),
			'user'     => array('label' => 'User', 'badge' => ! empty($data['user']['logged_in']) ? 'in' : 'out'),
			'logs'     => array('label' => 'Logs', 'badge' => (string) count($data['logs']['lines'])),
			'hooks'    => array('label' => 'Hooks', 'badge' => (string) $data['hooks']['count']),
			'config'   => array('label' => 'Config', 'badge' => (string) count($data['config'])),
			'vars'     => array('label' => 'Vars', 'badge' => ''),
		);

		$html = "\n"
			.'<link rel="stylesheet" href="'.htmlspecialchars($css_href, ENT_QUOTES, $charset).'">'."\n"
			.'<div id="toolbarContainer" class="ci-debug-toolbar" data-open="0">'
			.'<button type="button" id="ciDebugToolbarIcon" class="ci-debug-toolbar__icon" title="Jitendra Debugger" aria-label="Open Jitendra Debugger">'
			.'<span class="ci-debug-toolbar__icon-mark">J</span>'
			.'</button>'
			.'<div id="debug-bar" class="ci-debug-toolbar__bar" hidden>'
			.'<div class="ci-debug-toolbar__toolbar">'
			.'<div class="ci-debug-toolbar__brand" title="Jitendra Debugger">'
			.'<span class="ci-debug-toolbar__brand-mark">J</span>'
			.'<span class="ci-debug-toolbar__brand-text">Jitendra</span>'
			.'</div>';

		foreach ($tabs as $id => $tab)
		{
			$html .= '<button type="button" class="ci-debug-toolbar__tab" data-tab="'.htmlspecialchars($id, ENT_QUOTES, $charset).'">'
				.'<span class="ci-debug-toolbar__tab-label">'.htmlspecialchars($tab['label'], ENT_QUOTES, $charset).'</span>';
			if ($tab['badge'] !== '')
			{
				$html .= '<span class="ci-debug-toolbar__badge">'.htmlspecialchars($tab['badge'], ENT_QUOTES, $charset).'</span>';
			}
			$html .= '</button>';
		}

		$html .= '<div class="ci-debug-toolbar__meta">'
			.'<span title="Total execution time">'.htmlspecialchars($total_time !== '' ? $total_time.' s' : '—', ENT_QUOTES, $charset).'</span>'
			.'<span title="Memory / peak">'.htmlspecialchars($memory.' / '.$peak, ENT_QUOTES, $charset).'</span>'
			.'<span title="Queries">'.(int) $data['database']['count'].' Q</span>'
			.'</div>'
			.'<button type="button" class="ci-debug-toolbar__views-toggle" id="ciDebugViewsToggle" title="Show which view file renders each section on the page" aria-pressed="false">'
			.'Views on page'
			.'</button>'
			.'<button type="button" class="ci-debug-toolbar__close" id="ciDebugToolbarClose" title="Close Jitendra Debugger" aria-label="Close Jitendra Debugger">&times;</button>'
			.'</div>';

		$html .= $this->_panel_timeline($data['timeline'], $charset);
		$html .= $this->_panel_database($data['database'], $charset);
		$html .= $this->_panel_files($data['files'], $charset, FALSE);
		$html .= $this->_panel_files($data['files'], $charset, TRUE);
		$html .= $this->_panel_routes($data['routes'], $charset);
		$html .= $this->_panel_request($data['request'], $charset);
		$html .= $this->_panel_headers($data['headers'], $charset);
		$html .= $this->_panel_cookies($data['cookies'], $charset);
		$html .= $this->_panel_session($data['session'], $charset);
		$html .= $this->_panel_user($data['user'], $charset);
		$html .= $this->_panel_logs($data['logs'], $charset);
		$html .= $this->_panel_hooks($data['hooks'], $charset);
		$html .= $this->_panel_config($data['config'], $charset);
		$html .= $this->_panel_vars($data['vars'], $charset);

		$html .= '</div></div>'."\n"
			.'<script src="'.htmlspecialchars($js_href, ENT_QUOTES, $charset).'" defer></script>'."\n";

		return $html;
	}

	/** @return string */
	protected function _compile_files()
	{
		return '';
	}

	/** @return array */
	protected function _collect_benchmarks()
	{
		$profile = array();
		$total = '';
		$max = 0.0;
		if ( ! isset($this->CI->benchmark->marker) || ! is_array($this->CI->benchmark->marker))
		{
			return array('rows' => $profile, 'total' => $total, 'max' => $max);
		}
		foreach ($this->CI->benchmark->marker as $key => $val)
		{
			if (preg_match('/(.+?)_end$/i', $key, $match)
				&& isset($this->CI->benchmark->marker[$match[1].'_end'], $this->CI->benchmark->marker[$match[1].'_start']))
			{
				$label = ucwords(str_replace(array('_', '-'), ' ', $match[1]));
				$time = $this->CI->benchmark->elapsed_time($match[1].'_start', $key);
				$time_f = (float) $time;
				if ($time_f > $max)
				{
					$max = $time_f;
				}
				$profile[] = array('label' => $label, 'time' => $time, 'time_f' => $time_f);
				if (stripos($match[1], 'total_execution_time') !== FALSE)
				{
					$total = $time;
				}
			}
		}
		return array('rows' => $profile, 'total' => $total, 'max' => $max);
	}

	/** @return array */
	protected function _collect_queries()
	{
		$dbs = array();
		$conn_info = array();
		foreach (get_object_vars($this->CI) as $name => $cobject)
		{
			if (is_object($cobject) && $cobject instanceof CI_DB)
			{
				$dbs[get_class($this->CI).':$'.$name] = $cobject;
			}
			elseif (is_object($cobject) && $cobject instanceof CI_Model)
			{
				foreach (get_object_vars($cobject) as $mname => $mobject)
				{
					if ($mobject instanceof CI_DB)
					{
						$dbs[get_class($cobject).':$'.$mname] = $mobject;
					}
				}
			}
		}

		$rows = array();
		$count = 0;
		$total_time = 0.0;
		$dup_map = array();
		$slow = array();

		foreach ($dbs as $name => $db)
		{
			$conn_info[] = array(
				'name' => $name,
				'database' => isset($db->database) ? $db->database : '',
				'hostname' => isset($db->hostname) ? $db->hostname : '',
				'dbdriver' => isset($db->dbdriver) ? $db->dbdriver : '',
				'char_set' => isset($db->char_set) ? $db->char_set : '',
			);
			if (empty($db->queries) || ! is_array($db->queries))
			{
				continue;
			}
			foreach ($db->queries as $key => $sql)
			{
				$time = isset($db->query_times[$key]) ? (float) $db->query_times[$key] : 0.0;
				$total_time += $time;
				$count++;
				$norm = preg_replace('/\s+/', ' ', trim((string) $sql));
				if ( ! isset($dup_map[$norm]))
				{
					$dup_map[$norm] = 0;
				}
				$dup_map[$norm]++;
				$row = array(
					'db'   => $name,
					'sql'  => $sql,
					'time' => number_format($time, 4),
					'time_f' => $time,
					'dup'  => 0,
					'slow' => ($time >= 0.05),
				);
				$rows[] = $row;
				if ($time >= 0.05)
				{
					$slow[] = $row;
				}
			}
		}

		$dup_count = 0;
		foreach ($rows as $i => $row)
		{
			$norm = preg_replace('/\s+/', ' ', trim((string) $row['sql']));
			$c = isset($dup_map[$norm]) ? (int) $dup_map[$norm] : 1;
			$rows[$i]['dup'] = $c;
			if ($c > 1)
			{
				$dup_count++;
			}
		}

		usort($slow, function ($a, $b) {
			return $b['time_f'] <=> $a['time_f'];
		});

		return array(
			'count' => $count,
			'total_time' => number_format($total_time, 4),
			'rows' => $rows,
			'duplicate_query_rows' => $dup_count,
			'slow' => array_slice($slow, 0, 10),
			'connections' => $conn_info,
		);
	}

	/** @return array */
	protected function _collect_files()
	{
		$app = $this->_normalize_path(APPPATH);
		$base = $this->_normalize_path(BASEPATH);
		$root = $this->_normalize_path(FCPATH);
		$view = defined('VIEWPATH') ? $this->_normalize_path(VIEWPATH) : ($app.'views/');

		$groups = array(
			'Views' => array(), 'Controllers' => array(), 'Models' => array(),
			'Helpers' => array(), 'Libraries' => array(), 'Config / Hooks' => array(),
			'Core / System' => array(), 'Vendor' => array(), 'Other' => array(),
		);

		foreach (get_included_files() as $file)
		{
			$path = $this->_normalize_path($file);
			$display = $this->_display_path($path, $root, $app, $base);
			$size = @filesize($file);
			$row = array(
				'path' => $display,
				'size' => ($size !== FALSE) ? $this->_format_bytes((int) $size) : '',
			);

			if ($this->_path_under($path, $view) || preg_match('#/(application/)?views/#', $path))
			{
				$groups['Views'][] = $row;
			}
			elseif (preg_match('#/controllers/#', $path))
			{
				$groups['Controllers'][] = $row;
			}
			elseif (preg_match('#/models/#', $path))
			{
				$groups['Models'][] = $row;
			}
			elseif (preg_match('#/helpers/#', $path))
			{
				$groups['Helpers'][] = $row;
			}
			elseif (preg_match('#/libraries/#', $path) || preg_match('#/core/(MS_|MY_)#', $path))
			{
				$groups['Libraries'][] = $row;
			}
			elseif (preg_match('#/(config|hooks)/#', $path))
			{
				$groups['Config / Hooks'][] = $row;
			}
			elseif ($this->_path_under($path, $base) || strpos($path, '/system/') !== FALSE)
			{
				$groups['Core / System'][] = $row;
			}
			elseif (strpos($path, '/vendor/') !== FALSE)
			{
				$groups['Vendor'][] = $row;
			}
			else
			{
				$groups['Other'][] = $row;
			}
		}

		foreach ($groups as $k => $list)
		{
			usort($groups[$k], function ($a, $b) {
				return strcasecmp($a['path'], $b['path']);
			});
		}

		$view_vars = array();
		try
		{
			if (isset($this->CI->load))
			{
				$ref = new ReflectionClass($this->CI->load);
				if ($ref->hasProperty('_ci_cached_vars'))
				{
					$p = $ref->getProperty('_ci_cached_vars');
					$p->setAccessible(TRUE);
					$cached = $p->getValue($this->CI->load);
					if (is_array($cached))
					{
						foreach ($cached as $k => $v)
						{
							$view_vars[$k] = $this->_describe_value($v);
						}
					}
				}
			}
		}
		catch (Exception $e)
		{
			/* ignore */
		}

		return array(
			'total' => count(get_included_files()),
			'view_count' => count($groups['Views']),
			'groups' => $groups,
			'summary' => $this->_ci_loader_summary(),
			'view_vars' => $view_vars,
		);
	}

	/** @return array */
	protected function _collect_request()
	{
		$uri = '';
		if (isset($this->CI->uri) && method_exists($this->CI->uri, 'uri_string'))
		{
			$uri = (string) $this->CI->uri->uri_string();
		}
		$class = isset($this->CI->router->class) ? $this->CI->router->class : '';
		$method = isset($this->CI->router->method) ? $this->CI->router->method : '';

		$server_keys = array(
			'HTTP_HOST', 'SERVER_NAME', 'SERVER_ADDR', 'SERVER_PORT', 'REMOTE_ADDR',
			'REQUEST_URI', 'QUERY_STRING', 'REQUEST_METHOD', 'HTTP_USER_AGENT',
			'HTTP_REFERER', 'HTTP_ACCEPT', 'HTTP_ACCEPT_LANGUAGE', 'CONTENT_TYPE',
			'HTTPS', 'SERVER_SOFTWARE', 'SCRIPT_NAME',
		);
		$server = array();
		foreach ($server_keys as $k)
		{
			if (isset($_SERVER[$k]))
			{
				$server[$k] = $this->_redact_value($k, $_SERVER[$k]);
			}
		}

		return array(
			'uri' => $uri !== '' ? $uri : '(root)',
			'controller' => $class.'/'.$method,
			'method' => isset($_SERVER['REQUEST_METHOD']) ? (string) $_SERVER['REQUEST_METHOD'] : 'GET',
			'is_ajax' => ($this->CI->input && $this->CI->input->is_ajax_request()) ? 'yes' : 'no',
			'is_cli' => is_cli() ? 'yes' : 'no',
			'ip' => isset($this->CI->input) ? (string) $this->CI->input->ip_address() : '',
			'user_agent' => isset($this->CI->input) ? (string) $this->CI->input->user_agent() : '',
			'get' => $_GET,
			'post' => $_POST,
			'files' => $this->_summarize_files_upload($_FILES),
			'server' => $server,
		);
	}

	/** @return array */
	protected function _collect_routes()
	{
		$RTR = isset($this->CI->router) ? $this->CI->router : NULL;
		$URI = isset($this->CI->uri) ? $this->CI->uri : NULL;
		$out = array(
			'class' => $RTR ? (string) $RTR->class : '',
			'method' => $RTR ? (string) $RTR->method : '',
			'directory' => ($RTR && isset($RTR->directory)) ? (string) $RTR->directory : '',
			'default_controller' => ($RTR && isset($RTR->default_controller)) ? (string) $RTR->default_controller : '',
			'uri_string' => ($URI && method_exists($URI, 'uri_string')) ? (string) $URI->uri_string() : '',
			'ruri_string' => ($URI && method_exists($URI, 'ruri_string')) ? (string) $URI->ruri_string() : '',
			'segments' => ($URI && isset($URI->segments) && is_array($URI->segments)) ? $URI->segments : array(),
			'rsegments' => ($URI && isset($URI->rsegments) && is_array($URI->rsegments)) ? $URI->rsegments : array(),
			'base_url' => function_exists('base_url') ? base_url() : '',
			'site_url' => function_exists('site_url') ? site_url() : '',
			'index_page' => (string) config_item('index_page'),
		);

		$route_file = array();
		if (isset($this->CI->router) && isset($this->CI->router->routes) && is_array($this->CI->router->routes))
		{
			$i = 0;
			foreach ($this->CI->router->routes as $from => $to)
			{
				if ($i++ >= 80)
				{
					$route_file['…'] = '(truncated)';
					break;
				}
				$route_file[(string) $from] = is_scalar($to) ? (string) $to : json_encode($to);
			}
		}
		$out['routes'] = $route_file;
		return $out;
	}

	/** @return array */
	protected function _collect_headers()
	{
		$req = array();
		if (function_exists('getallheaders'))
		{
			$h = getallheaders();
			if (is_array($h))
			{
				foreach ($h as $k => $v)
				{
					$req[$k] = $this->_redact_value($k, $v);
				}
			}
		}
		else
		{
			foreach ($_SERVER as $k => $v)
			{
				if (strpos($k, 'HTTP_') === 0)
				{
					$label = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($k, 5)))));
					$req[$label] = $this->_redact_value($label, $v);
				}
			}
		}

		$res = array();
		if (function_exists('headers_list'))
		{
			foreach (headers_list() as $line)
			{
				$parts = explode(':', $line, 2);
				$key = trim($parts[0]);
				$val = isset($parts[1]) ? trim($parts[1]) : '';
				$res[$key] = $this->_redact_value($key, $val);
			}
		}
		$status = function_exists('http_response_code') ? (string) http_response_code() : '';

		return array('request' => $req, 'response' => $res, 'status' => $status);
	}

	/** @return array */
	protected function _collect_cookies()
	{
		$out = array();
		foreach ($_COOKIE as $k => $v)
		{
			if (is_array($v) || is_object($v))
			{
				$out[$k] = $this->_describe_value($v);
			}
			else
			{
				$s = (string) $v;
				if ($this->_is_secret_key($k) || strlen($s) > 120)
				{
					$out[$k] = $this->_redact_value($k, $s);
				}
				else
				{
					$out[$k] = $s;
				}
			}
		}
		ksort($out);
		return $out;
	}

	/** @return array */
	protected function _collect_session()
	{
		if ( ! isset($this->CI->session) || ! is_object($this->CI->session))
		{
			return array();
		}
		$data = $this->CI->session->userdata();
		if ( ! is_array($data))
		{
			return array();
		}
		$out = array();
		foreach ($data as $k => $v)
		{
			$out[$k] = $this->_is_secret_key($k) ? '********' : $this->_describe_value($v);
		}
		return $out;
	}

	/** @return array */
	protected function _collect_user()
	{
		$sess = isset($this->CI->session) ? $this->CI->session : NULL;
		$logged = FALSE;
		if (function_exists('is_loggedin')) {
			$logged = (bool) is_loggedin() || (function_exists('is_student_loggedin') && is_student_loggedin()) || (function_exists('is_parent_loggedin') && is_parent_loggedin());
		} elseif ($sess) {
			$logged = (bool) $sess->userdata('loggedin');
		}
		return array(
			'logged_in' => $logged,
			'loggedin_id' => $sess ? $sess->userdata('loggedin_id') : NULL,
			'loggedin_userid' => $sess ? $sess->userdata('loggedin_userid') : NULL,
			'loggedin_role_id' => $sess ? $sess->userdata('loggedin_role_id') : NULL,
			'loggedin_type' => $sess ? $sess->userdata('loggedin_type') : NULL,
			'loggedin_branch' => $sess ? $sess->userdata('loggedin_branch') : NULL,
			'name' => $sess ? $sess->userdata('name') : NULL,
			'session_id' => ($sess && method_exists($sess, 'session_id')) ? $sess->session_id() : (session_id() ?: ''),
		);
	}

	/** @return array */
	protected function _collect_logs()
	{
		$path = APPPATH.'logs/log-'.date('Y-m-d').'.php';
		$lines = array();
		$level_counts = array('ERROR' => 0, 'DEBUG' => 0, 'INFO' => 0, 'OTHER' => 0);
		if (is_file($path) && is_readable($path))
		{
			$raw = @file($path, FILE_IGNORE_NEW_LINES);
			if (is_array($raw))
			{
				$raw = array_values(array_filter($raw, function ($l) {
					return $l !== '' && strpos($l, '<?php') !== 0 && strpos($l, 'defined(') !== 0;
				}));
				$slice = array_slice($raw, -80);
				foreach ($slice as $line)
				{
					$level = 'OTHER';
					if (preg_match('/\b(ERROR|DEBUG|INFO)\b/', $line, $m))
					{
						$level = $m[1];
					}
					if (isset($level_counts[$level]))
					{
						$level_counts[$level]++;
					}
					else
					{
						$level_counts['OTHER']++;
					}
					$lines[] = array('level' => $level, 'line' => $line);
				}
			}
		}
		return array(
			'path' => 'application/logs/log-'.date('Y-m-d').'.php',
			'lines' => $lines,
			'counts' => $level_counts,
		);
	}

	/** @return array */
	protected function _collect_hooks()
	{
		$hooks = array();
		$count = 0;
		$enabled = FALSE;
		if (isset($this->CI->hooks) && is_object($this->CI->hooks))
		{
			$enabled = ! empty($this->CI->hooks->enabled);
			if (isset($this->CI->hooks->hooks) && is_array($this->CI->hooks->hooks))
			{
				foreach ($this->CI->hooks->hooks as $point => $defs)
				{
					$list = array();
					if (isset($defs[0]) && is_array($defs[0]))
					{
						foreach ($defs as $d)
						{
							$list[] = $this->_format_hook_def($d);
							$count++;
						}
					}
					else
					{
						$list[] = $this->_format_hook_def($defs);
						$count++;
					}
					$hooks[$point] = $list;
				}
			}
		}
		return array('enabled' => $enabled, 'count' => $count, 'hooks' => $hooks);
	}

	/**
	 * @param mixed $def
	 * @return string
	 */
	protected function _format_hook_def($def)
	{
		if ( ! is_array($def))
		{
			return (string) $def;
		}
		$class = isset($def['class']) ? $def['class'] : '';
		$fn = isset($def['function']) ? $def['function'] : '';
		$file = isset($def['filename']) ? $def['filename'] : '';
		return trim($class.'.'.$fn.($file !== '' ? ' ('.$file.')' : ''), '. ');
	}

	/** @return array */
	protected function _collect_config()
	{
		$out = array();
		if ( ! isset($this->CI->config) || ! is_object($this->CI->config))
		{
			return $out;
		}
		$all = isset($this->CI->config->config) && is_array($this->CI->config->config)
			? $this->CI->config->config
			: array();

		$prefer = array(
			'base_url', 'index_page', 'charset', 'log_threshold', 'composer_autoload',
			'sess_driver', 'sess_cookie_name', 'sess_expiration', 'csrf_protection',
			'csrf_token_name', 'cookie_prefix', 'cookie_domain', 'cookie_path',
			'global_xss_filtering', 'compress_output', 'time_reference', 'language',
			'subclass_prefix', 'enable_hooks', 'allow_get_array',
		);
		foreach ($prefer as $k)
		{
			if (array_key_exists($k, $all))
			{
				$out[$k] = $this->_redact_value($k, $all[$k]);
			}
		}
		// A few loaded named configs when present
		foreach (array('aws_config', 's3_direct_upload', 'database') as $named)
		{
			/* skip dumping full database credentials */
		}
		$out['ENVIRONMENT'] = defined('ENVIRONMENT') ? ENVIRONMENT : '';
		$out['FCPATH'] = defined('FCPATH') ? FCPATH : '';
		$out['APPPATH'] = defined('APPPATH') ? APPPATH : '';
		return $out;
	}

	/** @return array */
	protected function _collect_vars()
	{
		$opcache = function_exists('opcache_get_status') ? @opcache_get_status(FALSE) : FALSE;
		$extensions = get_loaded_extensions();
		sort($extensions);

		return array(
			'memory' => $this->_memory_usage(),
			'memory_peak' => function_exists('memory_get_peak_usage') ? $this->_format_bytes((int) memory_get_peak_usage()) : 'n/a',
			'php' => PHP_VERSION,
			'sapi' => PHP_SAPI,
			'ci' => (defined('CI_VERSION') ? CI_VERSION : '3.x'),
			'env' => defined('ENVIRONMENT') ? ENVIRONMENT : '',
			'timezone' => date_default_timezone_get(),
			'opcache' => (is_array($opcache) && ! empty($opcache['opcache_enabled'])) ? 'enabled' : 'disabled',
			'extensions' => $extensions,
			'summary' => $this->_ci_loader_summary(),
			'view_vars' => array(),
		);
	}

	/** @return string */
	protected function _memory_usage()
	{
		return function_exists('memory_get_usage')
			? $this->_format_bytes((int) memory_get_usage())
			: 'n/a';
	}

	protected function _panel_timeline($data, $charset)
	{
		$html = '<div class="ci-debug-toolbar__panel" data-panel="timeline" hidden><h2>Timeline</h2>';
		if (empty($data['rows']))
		{
			return $html.'<p class="ci-debug-toolbar__empty">No benchmark markers.</p></div>';
		}
		$max = ! empty($data['max']) ? (float) $data['max'] : 0.0;
		$html .= '<table class="ci-debug-toolbar__table"><thead><tr><th>Marker</th><th>Time</th><th>Share</th></tr></thead><tbody>';
		foreach ($data['rows'] as $row)
		{
			$pct = ($max > 0) ? min(100, round(($row['time_f'] / $max) * 100)) : 0;
			$html .= '<tr><td>'.htmlspecialchars($row['label'], ENT_QUOTES, $charset).'</td>'
				.'<td class="ci-debug-toolbar__num">'.htmlspecialchars($row['time'], ENT_QUOTES, $charset).' s</td>'
				.'<td><div class="ci-debug-toolbar__barline"><span style="width:'.$pct.'%"></span></div></td></tr>';
		}
		return $html.'</tbody></table></div>';
	}

	protected function _panel_database($data, $charset)
	{
		$html = '<div class="ci-debug-toolbar__panel" data-panel="database" hidden>'
			.'<h2>Database <span class="ci-debug-toolbar__muted">'.$data['count'].' queries · '.$data['total_time'].' s';
		if ( ! empty($data['duplicate_query_rows']))
		{
			$html .= ' · '.$data['duplicate_query_rows'].' duplicate rows';
		}
		$html .= '</span></h2>';

		if ( ! empty($data['connections']))
		{
			$html .= '<h3>Connections</h3><table class="ci-debug-toolbar__table"><thead><tr><th>Handle</th><th>Driver</th><th>Host</th><th>Database</th></tr></thead><tbody>';
			foreach ($data['connections'] as $c)
			{
				$html .= '<tr><td><code>'.htmlspecialchars($c['name'], ENT_QUOTES, $charset).'</code></td>'
					.'<td>'.htmlspecialchars($c['dbdriver'], ENT_QUOTES, $charset).'</td>'
					.'<td>'.htmlspecialchars($c['hostname'], ENT_QUOTES, $charset).'</td>'
					.'<td>'.htmlspecialchars($c['database'], ENT_QUOTES, $charset).'</td></tr>';
			}
			$html .= '</tbody></table>';
		}

		if ( ! empty($data['slow']))
		{
			$html .= '<h3>Slowest (≥ 50 ms)</h3><table class="ci-debug-toolbar__table"><thead><tr><th>Time</th><th>SQL</th></tr></thead><tbody>';
			foreach ($data['slow'] as $row)
			{
				$html .= '<tr class="is-slow"><td class="ci-debug-toolbar__num">'.htmlspecialchars($row['time'], ENT_QUOTES, $charset).'</td>'
					.'<td><code class="ci-debug-toolbar__sql">'.htmlspecialchars($row['sql'], ENT_QUOTES, $charset).'</code></td></tr>';
			}
			$html .= '</tbody></table>';
		}

		if (empty($data['rows']))
		{
			return $html.'<p class="ci-debug-toolbar__empty">No queries.</p></div>';
		}

		$html .= '<h3>All queries</h3><table class="ci-debug-toolbar__table"><thead><tr><th>Time</th><th>Flags</th><th>SQL</th></tr></thead><tbody>';
		foreach ($data['rows'] as $row)
		{
			$flags = '';
			if ( ! empty($row['slow']))
			{
				$flags .= '<span class="ci-debug-toolbar__pill is-warn">slow</span>';
			}
			if (isset($row['dup']) && $row['dup'] > 1)
			{
				$flags .= '<span class="ci-debug-toolbar__pill is-dup">×'.$row['dup'].'</span>';
			}
			$cls = ! empty($row['slow']) ? ' class="is-slow"' : '';
			$html .= '<tr'.$cls.'><td class="ci-debug-toolbar__num">'.htmlspecialchars($row['time'], ENT_QUOTES, $charset).'</td>'
				.'<td>'.$flags.'</td>'
				.'<td><code class="ci-debug-toolbar__sql">'.htmlspecialchars($row['sql'], ENT_QUOTES, $charset).'</code></td></tr>';
		}
		return $html.'</tbody></table></div>';
	}

	protected function _panel_files($data, $charset, $views_only)
	{
		$panel = $views_only ? 'views' : 'files';
		$title = $views_only ? 'Views' : 'Files';
		$html = '<div class="ci-debug-toolbar__panel" data-panel="'.$panel.'" hidden>'
			.'<h2>'.htmlspecialchars($title, ENT_QUOTES, $charset)
			.' <span class="ci-debug-toolbar__muted">'
			.($views_only ? $data['view_count'].' views' : $data['total'].' included')
			.'</span></h2>';

		if ($views_only)
		{
			$html .= '<p class="ci-debug-toolbar__summary">'
				.'Use <strong>Views on page</strong> in the toolbar to outline each rendered section in the live UI '
				.'(same as CodeIgniter 4 view hints).'
				.'</p>';
		}

		if ( ! $views_only && ! empty($data['summary']))
		{
			$html .= '<p class="ci-debug-toolbar__summary">'.htmlspecialchars($data['summary'], ENT_QUOTES, $charset).'</p>';
		}

		if ($views_only && ! empty($data['view_vars']))
		{
			$html .= '<h3>View variables (types)</h3>'.$this->_kv_table($data['view_vars'], $charset);
		}

		$groups = $data['groups'];
		if ($views_only)
		{
			$groups = array('Views' => isset($groups['Views']) ? $groups['Views'] : array());
		}

		foreach ($groups as $label => $rows)
		{
			if (empty($rows))
			{
				continue;
			}
			$html .= '<h3>'.htmlspecialchars($label, ENT_QUOTES, $charset).' ('.count($rows).')</h3>';
			$html .= '<table class="ci-debug-toolbar__table"><thead><tr><th>Path</th><th>Size</th></tr></thead><tbody>';
			foreach ($rows as $row)
			{
				$html .= '<tr><td><code>'.htmlspecialchars($row['path'], ENT_QUOTES, $charset).'</code></td>'
					.'<td class="ci-debug-toolbar__num">'.htmlspecialchars($row['size'], ENT_QUOTES, $charset).'</td></tr>';
			}
			$html .= '</tbody></table>';
		}

		if ($views_only && empty($groups['Views']))
		{
			$html .= '<p class="ci-debug-toolbar__empty">No views loaded.</p>';
		}

		return $html.'</div>';
	}

	protected function _panel_routes($data, $charset)
	{
		$html = '<div class="ci-debug-toolbar__panel" data-panel="routes" hidden><h2>Routes</h2>'
			.'<table class="ci-debug-toolbar__table"><tbody>'
			.'<tr><th>Matched</th><td><code>'.htmlspecialchars($data['directory'].$data['class'].'/'.$data['method'], ENT_QUOTES, $charset).'</code></td></tr>'
			.'<tr><th>URI</th><td><code>'.htmlspecialchars($data['uri_string'], ENT_QUOTES, $charset).'</code></td></tr>'
			.'<tr><th>Routed URI</th><td><code>'.htmlspecialchars($data['ruri_string'], ENT_QUOTES, $charset).'</code></td></tr>'
			.'<tr><th>Default controller</th><td><code>'.htmlspecialchars($data['default_controller'], ENT_QUOTES, $charset).'</code></td></tr>'
			.'<tr><th>base_url</th><td><code>'.htmlspecialchars($data['base_url'], ENT_QUOTES, $charset).'</code></td></tr>'
			.'</tbody></table>';
		$html .= '<h3>URI segments</h3>'.$this->_kv_table($data['segments'], $charset);
		$html .= '<h3>Routed segments</h3>'.$this->_kv_table($data['rsegments'], $charset);
		$html .= '<h3>Route map (sample)</h3>'.$this->_kv_table($data['routes'], $charset);
		return $html.'</div>';
	}

	protected function _panel_request($data, $charset)
	{
		$html = '<div class="ci-debug-toolbar__panel" data-panel="request" hidden><h2>Request</h2>'
			.'<table class="ci-debug-toolbar__table"><tbody>'
			.'<tr><th>Method</th><td>'.htmlspecialchars($data['method'], ENT_QUOTES, $charset).'</td></tr>'
			.'<tr><th>URI</th><td><code>'.htmlspecialchars($data['uri'], ENT_QUOTES, $charset).'</code></td></tr>'
			.'<tr><th>Controller</th><td><code>'.htmlspecialchars($data['controller'], ENT_QUOTES, $charset).'</code></td></tr>'
			.'<tr><th>AJAX</th><td>'.htmlspecialchars($data['is_ajax'], ENT_QUOTES, $charset).'</td></tr>'
			.'<tr><th>CLI</th><td>'.htmlspecialchars($data['is_cli'], ENT_QUOTES, $charset).'</td></tr>'
			.'<tr><th>IP</th><td>'.htmlspecialchars($data['ip'], ENT_QUOTES, $charset).'</td></tr>'
			.'<tr><th>User agent</th><td>'.htmlspecialchars($data['user_agent'], ENT_QUOTES, $charset).'</td></tr>'
			.'</tbody></table>';
		$html .= '<h3>$_GET</h3>'.$this->_kv_table($data['get'], $charset);
		$html .= '<h3>$_POST</h3>'.$this->_kv_table($data['post'], $charset);
		$html .= '<h3>$_FILES</h3>'.$this->_kv_table($data['files'], $charset);
		$html .= '<h3>Server</h3>'.$this->_kv_table($data['server'], $charset);
		return $html.'</div>';
	}

	protected function _panel_headers($data, $charset)
	{
		$html = '<div class="ci-debug-toolbar__panel" data-panel="headers" hidden><h2>Headers'
			.' <span class="ci-debug-toolbar__muted">HTTP '.htmlspecialchars($data['status'], ENT_QUOTES, $charset).'</span></h2>';
		$html .= '<h3>Request</h3>'.$this->_kv_table($data['request'], $charset);
		$html .= '<h3>Response</h3>'.$this->_kv_table($data['response'], $charset);
		return $html.'</div>';
	}

	protected function _panel_cookies($data, $charset)
	{
		return '<div class="ci-debug-toolbar__panel" data-panel="cookies" hidden><h2>Cookies</h2>'
			.$this->_kv_table($data, $charset).'</div>';
	}

	protected function _panel_session($data, $charset)
	{
		return '<div class="ci-debug-toolbar__panel" data-panel="session" hidden><h2>Session</h2>'
			.$this->_kv_table($data, $charset).'</div>';
	}

	protected function _panel_user($data, $charset)
	{
		$html = '<div class="ci-debug-toolbar__panel" data-panel="user" hidden><h2>User / Auth</h2>'
			.'<table class="ci-debug-toolbar__table"><tbody>';
		foreach ($data as $k => $v)
		{
			if (is_bool($v))
			{
				$v = $v ? 'yes' : 'no';
			}
			elseif ($v === NULL || $v === '')
			{
				$v = '—';
			}
			$html .= '<tr><th>'.htmlspecialchars((string) $k, ENT_QUOTES, $charset).'</th>'
				.'<td>'.htmlspecialchars((string) $v, ENT_QUOTES, $charset).'</td></tr>';
		}
		return $html.'</tbody></table></div>';
	}

	protected function _panel_logs($data, $charset)
	{
		$html = '<div class="ci-debug-toolbar__panel" data-panel="logs" hidden><h2>Logs'
			.' <span class="ci-debug-toolbar__muted">'.htmlspecialchars($data['path'], ENT_QUOTES, $charset).'</span></h2>';
		if ( ! empty($data['counts']))
		{
			$html .= '<p class="ci-debug-toolbar__summary">';
			foreach ($data['counts'] as $lvl => $n)
			{
				if ($n < 1)
				{
					continue;
				}
				$html .= '<span class="ci-debug-toolbar__pill is-'.strtolower($lvl).'">'.$lvl.': '.$n.'</span> ';
			}
			$html .= '</p>';
		}
		if (empty($data['lines']))
		{
			return $html.'<p class="ci-debug-toolbar__empty">No log lines for today (or log unreadable).</p></div>';
		}
		$html .= '<table class="ci-debug-toolbar__table"><thead><tr><th>Level</th><th>Message</th></tr></thead><tbody>';
		foreach ($data['lines'] as $row)
		{
			$html .= '<tr class="is-log-'.strtolower($row['level']).'">'
				.'<td><span class="ci-debug-toolbar__pill is-'.strtolower($row['level']).'">'
				.htmlspecialchars($row['level'], ENT_QUOTES, $charset).'</span></td>'
				.'<td><code>'.htmlspecialchars($row['line'], ENT_QUOTES, $charset).'</code></td></tr>';
		}
		return $html.'</tbody></table></div>';
	}

	protected function _panel_hooks($data, $charset)
	{
		$html = '<div class="ci-debug-toolbar__panel" data-panel="hooks" hidden><h2>Hooks'
			.' <span class="ci-debug-toolbar__muted">'
			.($data['enabled'] ? 'enabled' : 'disabled').' · '.$data['count'].' registered'
			.'</span></h2>';
		if (empty($data['hooks']))
		{
			return $html.'<p class="ci-debug-toolbar__empty">No hooks registered.</p></div>';
		}
		foreach ($data['hooks'] as $point => $list)
		{
			$html .= '<h3>'.htmlspecialchars($point, ENT_QUOTES, $charset).'</h3><ul class="ci-debug-toolbar__list">';
			foreach ($list as $item)
			{
				$html .= '<li><code>'.htmlspecialchars($item, ENT_QUOTES, $charset).'</code></li>';
			}
			$html .= '</ul>';
		}
		return $html.'</div>';
	}

	protected function _panel_config($data, $charset)
	{
		return '<div class="ci-debug-toolbar__panel" data-panel="config" hidden><h2>Config'
			.' <span class="ci-debug-toolbar__muted">secrets redacted</span></h2>'
			.$this->_kv_table($data, $charset).'</div>';
	}

	protected function _panel_vars($data, $charset)
	{
		$html = '<div class="ci-debug-toolbar__panel" data-panel="vars" hidden><h2>System</h2>'
			.'<table class="ci-debug-toolbar__table"><tbody>'
			.'<tr><th>Memory</th><td>'.htmlspecialchars($data['memory'], ENT_QUOTES, $charset).'</td></tr>'
			.'<tr><th>Peak memory</th><td>'.htmlspecialchars($data['memory_peak'], ENT_QUOTES, $charset).'</td></tr>'
			.'<tr><th>PHP</th><td>'.htmlspecialchars($data['php'].' ('.$data['sapi'].')', ENT_QUOTES, $charset).'</td></tr>'
			.'<tr><th>CodeIgniter</th><td>'.htmlspecialchars($data['ci'], ENT_QUOTES, $charset).'</td></tr>'
			.'<tr><th>Environment</th><td>'.htmlspecialchars($data['env'], ENT_QUOTES, $charset).'</td></tr>'
			.'<tr><th>Timezone</th><td>'.htmlspecialchars($data['timezone'], ENT_QUOTES, $charset).'</td></tr>'
			.'<tr><th>OPcache</th><td>'.htmlspecialchars($data['opcache'], ENT_QUOTES, $charset).'</td></tr>'
			.'</tbody></table>';
		if ( ! empty($data['summary']))
		{
			$html .= '<h3>Loaded</h3><p class="ci-debug-toolbar__summary">'.htmlspecialchars($data['summary'], ENT_QUOTES, $charset).'</p>';
		}
		if ( ! empty($data['extensions']))
		{
			$html .= '<h3>PHP extensions ('.count($data['extensions']).')</h3>'
				.'<p class="ci-debug-toolbar__summary"><code>'
				.htmlspecialchars(implode(', ', $data['extensions']), ENT_QUOTES, $charset)
				.'</code></p>';
		}
		return $html.'</div>';
	}

	protected function _kv_table($data, $charset)
	{
		if ( ! is_array($data) || empty($data))
		{
			return '<p class="ci-debug-toolbar__empty">Empty</p>';
		}
		$html = '<table class="ci-debug-toolbar__table"><thead><tr><th>Key</th><th>Value</th></tr></thead><tbody>';
		foreach ($data as $key => $val)
		{
			if (is_array($val) || is_object($val))
			{
				$val = print_r($val, TRUE);
			}
			$html .= '<tr><td><code>'.htmlspecialchars((string) $key, ENT_QUOTES, $charset).'</code></td>'
				.'<td><pre class="ci-debug-toolbar__pre">'.htmlspecialchars((string) $val, ENT_QUOTES, $charset).'</pre></td></tr>';
		}
		return $html.'</tbody></table>';
	}

	/** @param mixed $v @return string */
	protected function _describe_value($v)
	{
		if (is_null($v))
		{
			return 'NULL';
		}
		if (is_bool($v))
		{
			return $v ? 'true' : 'false';
		}
		if (is_string($v))
		{
			$len = strlen($v);
			return 'string('.$len.')'.($len > 0 && $len <= 80 ? ' '.json_encode($v) : '');
		}
		if (is_int($v) || is_float($v))
		{
			return gettype($v).' '.$v;
		}
		if (is_array($v))
		{
			return 'array('.count($v).')';
		}
		if (is_object($v))
		{
			return 'object('.get_class($v).')';
		}
		return gettype($v);
	}

	/** @param array $files @return array */
	protected function _summarize_files_upload($files)
	{
		$out = array();
		if ( ! is_array($files))
		{
			return $out;
		}
		foreach ($files as $k => $f)
		{
			if (is_array($f) && isset($f['name']))
			{
				$out[$k] = (is_array($f['name']) ? count($f['name']).' files' : $f['name'])
					.(isset($f['size']) && ! is_array($f['size']) ? ' ('.$this->_format_bytes((int) $f['size']).')' : '');
			}
			else
			{
				$out[$k] = $this->_describe_value($f);
			}
		}
		return $out;
	}

	/** @param string $key @return bool */
	protected function _is_secret_key($key)
	{
		$k = strtolower((string) $key);
		foreach ($this->_secret_patterns as $p)
		{
			if (strpos($k, $p) !== FALSE)
			{
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * @param string $key
	 * @param mixed  $val
	 * @return mixed
	 */
	protected function _redact_value($key, $val)
	{
		if (is_array($val) || is_object($val))
		{
			return $this->_describe_value($val);
		}
		if ($this->_is_secret_key($key))
		{
			return '********';
		}
		return $val;
	}

	protected function _ci_loader_summary()
	{
		$parts = array();
		$load = isset($this->CI->load) ? $this->CI->load : NULL;
		if ( ! is_object($load))
		{
			return '';
		}
		try
		{
			$ref = new ReflectionClass($load);
			foreach (array('_ci_models' => 'Models', '_ci_helpers' => 'Helpers') as $prop => $label)
			{
				if ( ! $ref->hasProperty($prop))
				{
					continue;
				}
				$p = $ref->getProperty($prop);
				$p->setAccessible(TRUE);
				$val = $p->getValue($load);
				if (is_array($val) && ! empty($val))
				{
					$names = ($prop === '_ci_models') ? array_values($val) : array_keys($val);
					$parts[] = $label.': '.implode(', ', $names);
				}
			}
		}
		catch (Exception $e)
		{
			/* ignore */
		}
		if (function_exists('is_loaded'))
		{
			$libs = is_loaded();
			if (is_array($libs) && ! empty($libs))
			{
				$parts[] = 'Libraries: '.implode(', ', array_keys($libs));
			}
		}
		return implode(' · ', $parts);
	}

	protected function _normalize_path($path)
	{
		return str_replace('\\', '/', (string) $path);
	}

	protected function _path_under($path, $prefix)
	{
		$prefix = rtrim($prefix, '/').'/';
		return strpos($path, $prefix) === 0 || $path === rtrim($prefix, '/');
	}

	protected function _display_path($path, $root, $app, $base)
	{
		foreach (array($root, $app, $base) as $prefix)
		{
			$prefix = rtrim($prefix, '/').'/';
			if (strpos($path, $prefix) === 0)
			{
				$rel = substr($path, strlen($prefix));
				if ($prefix === rtrim($app, '/').'/')
				{
					return 'application/'.$rel;
				}
				if ($prefix === rtrim($base, '/').'/')
				{
					return 'system/'.$rel;
				}
				return $rel;
			}
		}
		return $path;
	}

	protected function _format_bytes($bytes)
	{
		if ($bytes < 1024)
		{
			return $bytes.' B';
		}
		if ($bytes < 1048576)
		{
			return number_format($bytes / 1024, 1).' KB';
		}
		return number_format($bytes / 1048576, 2).' MB';
	}
}
