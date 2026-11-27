<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Profiler Sections
| -------------------------------------------------------------------------
| Shown at the bottom of HTML responses when enable_profiler(TRUE) is set
| (see MY_Controller — enabled on localhost only).
|
|	http://codeigniter.com/user_guide/general/profiling.html
|
*/

$config['benchmarks']         = TRUE;
$config['controller_info']    = TRUE;
$config['memory_usage']       = TRUE;
$config['uri_string']         = TRUE;
$config['get']                = TRUE;
$config['post']               = TRUE;
$config['queries']            = TRUE;
$config['files']              = TRUE;
$config['http_headers']       = FALSE;
/* Config dumps encryption keys / AWS secrets — keep off */
$config['config']             = FALSE;
$config['session_data']       = TRUE;
/* Show SQL lists expanded (guest home alone can exceed 50 queries) */
$config['query_toggle_count'] = 500;
/* End of file profiler.php */
/* Location: ./application/config/profiler.php */
