<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Wrap views with DEBUG-VIEW comments (CI4-style) so the debugger can
 * highlight which UI section came from which view file.
 */
class MY_Loader extends CI_Loader
{
	/** @var int */
	protected $_debug_view_count = 0;

	/**
	 * {@inheritdoc}
	 */
	public function view($view, $vars = array(), $return = FALSE)
	{
		if ( ! $this->_should_wrap_debug_views())
		{
			return parent::view($view, $vars, $return);
		}

		$html = parent::view($view, $vars, TRUE);
		$html = $this->_wrap_debug_view($view, $html);

		if ($return === TRUE)
		{
			return $html;
		}

		// Nested views: echo into the parent view's output buffer.
		// Top-level views: append to the Output class like CI3 normally does.
		if (ob_get_level() > $this->_ci_ob_level)
		{
			echo $html;
		}
		else
		{
			$CI =& get_instance();
			$CI->output->append_output($html);
		}

		return $this;
	}

	/**
	 * @return bool
	 */
	protected function _should_wrap_debug_views()
	{
		if (defined('CI_LOCALHOST') && CI_LOCALHOST)
		{
			return TRUE;
		}

		if (is_cli())
		{
			return FALSE;
		}

		$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
		if (in_array($ip, array('127.0.0.1', '::1'), TRUE))
		{
			return TRUE;
		}

		$host = isset($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : '';
		$host = preg_replace('/:\d+$/', '', $host);

		return in_array($host, array('localhost', '127.0.0.1', '::1'), TRUE);
	}

	/**
	 * @param string $view
	 * @param string $html
	 * @return string
	 */
	protected function _wrap_debug_view($view, $html)
	{
		$this->_debug_view_count++;

		$path = str_replace('\\', '/', (string) $view);
		$path = ltrim($path, '/');
		if (substr($path, -4) !== '.php')
		{
			$path .= '.php';
		}
		if (strpos($path, 'application/views/') !== 0)
		{
			$path = 'application/views/'.$path;
		}

		$label = $this->_debug_view_count.' '.$path;

		// Comment format must match CI4 toolbar.js parsing:
		// nodeValue[12] === 'S' for START, path = substring(18, length-1)
		return '<!-- DEBUG-VIEW START '.$label.' -->'."\n"
			.$html."\n"
			.'<!-- DEBUG-VIEW ENDED '.$label.' -->'."\n";
	}
}
