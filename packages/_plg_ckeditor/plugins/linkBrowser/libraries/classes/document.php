<?php
/*
* This plugin uses parts of JCE extension by Ryan Demmer.
* @copyright	Copyright (C) 2005 - 2011 Ryan Demmer. All rights reserved.
* @copyright	Copyright (C) 2003 - 2011, CKSource - Frederico Knabben. All rights reserved.
* @license		GNU/GPL
* CKEditor extension is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
class WFDocument extends JObject
{
	var $_scripts 	= array();

	var $_script 	= array();

	var $_styles 	= array();

	var $_head 		= array();

	var $_url 		= array();

	var $_modules 	= array();

	var $_title		= '';

	var $_body		= '';

	/**
	 * Constructor activating the default information of the class
	 *
	 * @access  protected
	 */
	function __construct($config = array())
	{
		parent::__construct();

		// set document title
		if (isset($config['title'])) {
			$this->setTitle($config['title']);
		}
		$this->setProperties($config);

	}
	/**
	 * Returns a reference to a plugin object
	 *
	 * This method must be invoked as:
	 *    <pre>  $advlink = &AdvLink::getInstance();</pre>
	 *
	 * @access  public
	 * @return  JCE  The editor object.
	 * @since 1.5
	 */
	function &getInstance($config = array())
	{
		static $instance;

		if (!is_object($instance)) {
			$instance = new WFDocument($config);
		}
		return $instance;
	}

	function setTitle($title)
	{
		$this->_title = $title;
	}

	function getTitle()
	{
		return $this->_title;
	}

	function getURL($relative = false)
	{
		if ($relative) {
			return JURI::root(true) . '/plugins/editors/ckeditor/plugins/linkBrowser';
		}

		return JURI::root() . '/plugins/editors/ckeditor/plugins/linkBrowser';
	}

	/**
	 * Return the curernt language code
	 *
	 * @access public
	 * @return language code
	 */
	function getLanguageDir()
	{
		$language =& JFactory::getLanguage();
		return $language->isRTL() ? 'rtl' : 'ltr';
	}
	/**
	 * Return the curernt language code
	 *
	 * @access public
	 * @return language code
	 */
	function getLanguageTag()
	{
		$language =& JFactory::getLanguage();
		if ($language->isRTL()) {
			return 'en-GB';
		}
		return $language->getTag();
	}
	/**
	 * Return the curernt language code
	 *
	 * @access public
	 * @return language code
	 */
	function getLanguage()
	{
		$tag = $this->getLanguageTag();
		if (file_exists(JPATH_SITE .DS. 'language' .DS. $tag .DS. $tag .'.com_ckeditor.xml')) {
			return substr($tag, 0, strpos($tag, '-'));
		}
		return 'en';
	}

	/**
	 * Returns a JCE resource url
	 *
	 * @access  public
	 * @param string  The path to resolve eg: libaries
	 * @param boolean Create a relative url
	 * @return  full url
	 * @since 1.5
	 */
	function getBaseURL($path, $type = '')
	{
		static $url;

		if (!isset($url)) {
			$url = array();
		}

		$signature = serialize(array($type, $path));

		// Check if value is already stored
		if (!isset($url[$signature])) {
			$plugin = JRequest::getVar('plugin');

			$base = $this->getURL(true) . '/';

			switch ($path) {
				// JCE root folder
				case 'ckeditor':
					$pre = $base . '';
					break;
					// JCE libraries resource folder
				default:
				case 'libraries':
					$pre = $base . 'libraries/' . $type;
					break;
				case 'uploader':
					$pre = $base . 'uploader/' . $type;
					break;
					// TinyMCE folder
				case 'tiny_mce':
					$pre = $base ;
					break;
					// JCE current plugin folder
				case 'plugins':
					$pre = $base . 'libraries/' . $type;
					//$pre = $base . 'tiny_mce/plugins/' . $plugin . '/' . $type;
					break;
					// Extensions folder
				case 'extensions':
					$pre = $base . 'extensions/'.$type;

					break;
				case 'joomla':
					return JURI::root(true);
					break;
				case 'media':
					return JURI::root(true) . '/media/system';
					break;
				case 'component':
					$pre = JURI::root(true) . '/administrator/components/com_ckeditor/media/' . $type;
					break;
				default:
					$pre = $base . $path;
					break;
			}

			// Store url
			$url[$signature] = $pre;
		}

		return $url[$signature];
	}

	/**
	 * Convert a url to path
	 *
	 * @access  public
	 * @param string  The url to convert
	 * @return  Full path to file
	 * @since 1.5
	 */
	function urlToPath($url)
	{
		jimport('joomla.filesystem.path');
		$bool = strpos($url, JURI::root()) === false;
		return WFUtility::makePath(JPATH_SITE, JPath::clean(str_replace(JURI::root($bool), '', $url)));
	}

	/**
	 * Returns an image url
	 *
	 * @access  public
	 * @param string  The file to load including path and extension eg: libaries.image.gif
	 * @return  Image url
	 * @since 1.5
	 */
	function image($image, $root = 'libraries')
	{
		$parts = explode('.', $image);
		$parts = preg_replace('#[^A-Z0-9-_]#i', '', $parts);

		$ext  = array_pop($parts);
		$name = array_pop($parts);
		$path = implode('/', $parts);

		return $this->getBaseURL($root) . "/" . $path . "/img/" . $name . "." . $ext;
	}

	function removeScript($file, $root = 'libraries')
	{
		$file = $this->buildScriptPath($file, $root);
		unset($this->_scripts[$file]);
	}
	function removeCss($file, $root = 'libraries')
	{
		$file = $this->buildStylePath($file, $root);
		unset($this->_styles[$file]);
	}

	function buildScriptPath($file, $root)
	{

		$file = preg_replace('#[^A-Z0-9-_\/\.]#i', '', $file);
		// get base dir
				$base = dirname($file);
				// remove extension if present
				$file = basename($file, '.js');
		// strip . and trailing /
		$file = trim(trim($base, '.'), '/') . '/' . $file . '.js';
		// remove leading and trailing slashes
		$file = trim($file, '/');
		// create path
		$file = $this->getBaseURL($root, 'js') . '/' . $file;

		return $file;
	}

	function buildStylePath($file, $root)
	{
		$file = preg_replace('#[^A-Z0-9-_\/\.]#i', '', $file);
		// get base dir
				$base = dirname($file);
				// remove extension if present
				$file = basename($file, '.css');
		// strip . and trailing /
		$file = trim(trim($base, '.'), '/') . '/' . $file . '.css';
		// remove leading and trailing slashes
		$file = trim($file, '/');
		// create path
		$file = $this->getBaseURL($root, 'css') . '/' . $file;

		return $file;
	}

	/**
	 * Loads a javascript file
	 *
	 * @access  public
	 * @param string  The file to load including path eg: libaries.manager
	 * @param boolean Debug mode load src file
	 * @return  echo script html
	 * @since 1.5
	 */
	function addScript($files, $root = 'libraries', $type = 'text/javascript')
	{
		$files = (array) $files;
		foreach ($files as $file) {
						if (strpos($file, 'http://') !== false) {
				$this->_scripts[$file] = $type;
			} else {
				$file = $this->buildScriptPath($file, $root);
				// store path
				$this->_scripts[$file] = $type;
			}
		}
	}
	/**
	 * Loads a css file
	 *
	 * @access  public
	 * @param string The file to load including path eg: libaries.manager
	 * @param string Root folder
	 * @return  echo css html
	 * @since 1.5
	 */
	function addStyleSheet($files, $root = 'libraries', $type = 'text/css')
	{
		$files = (array) $files;

		jimport('joomla.environment.browser');
		$browser =& JBrowser::getInstance();

		foreach ($files as $file) {
			$url = $this->buildStylePath($file, $root);
			// store path
						$this->_styles[$url] = $type;

						if ($browser->getBrowser() == 'msie') {
								// All versions
								$file = $file . '_ie.css';
								$path = $this->urlToPath($url);

								if (file_exists(dirname($path) . DS . $file)) {
									$this->_styles[dirname($url) . '/' . $file] = $type;
								}
					}
		}
	}

	function addScriptDeclaration($content, $type = 'text/javascript')
	{
		if (!isset($this->_script[strtolower($type)])) {
			$this->_script[strtolower($type)] = $content;
		} else {
			$this->_script[strtolower($type)] .= chr(13) . $content;
		}
	}

	function getScripts()
	{
		return $this->_scripts;
	}

	function getStyleSheets()
	{
		return $this->_styles;
	}

	/**
	 * Setup head data
	 *
	 * @access  public
	 * @since 1.5
	 */
	function setHead($data)
	{
		if (is_array($data)) {
			$this->_head = array_merge($this->_head, $data);
		} else {
			$this->_head[] = $data;
		}
	}

	/**
	 * Render document head data
	 */
	private function getHead()
	{
		$output = '';

		$output .= '<title>' . $this->getTitle() .  '</title>' . "\n";

		$layout 	= JRequest::getVar('layout');
		$item		= JRequest::getVar($layout);
		$dialog		= '';
		if (JRequest::getVar('dialog')) {
			$dialog = '&dialog=' . JRequest::getVar('dialog');
		}

		require_once(dirname(__FILE__) .  DS . 'parameter.php');

		// Get Component / Global params
		$component 	=& JComponentHelper::getComponent('com_ckeditor');
		$params 	= new WFParameter($component->params);

		// Render scripts
		$stamp 	= preg_match('/\d+/', $this->version) ? '?version=' . $this->get('version', '000000') : '';

		if ($params->get('editor.compress_javascript', 0)) {
			$script = JURI::base(true) . '/index.php?option=com_ckeditor&view=editor&layout='.$layout.'&'.$layout.'=' . $item . $dialog . '&task=pack';
			$output .= "\t\t<script type=\"text/javascript\" src=\"" . $script . "\"></script>\n";
		} else {
			foreach ($this->_scripts as $src => $type) {
				$output .= "\t\t<script type=\"" . $type . "\" src=\"" . $src . $stamp . "\"></script>\n";
			}
		}

		if ($params->get('editor.compress_css', 0)) {
			$file = JURI::base(true) . '/index.php?option=com_ckeditor&view=editor&layout='.$layout.'&'.$layout.'=' . $item . $dialog . '&task=pack&type=css';

			$output .= "\t\t<link href=\"" . $file . "\" rel=\"stylesheet\" type=\"text/css\" />\n";
		} else {
			foreach ($this->_styles as $k => $v) {
				$output .= "\t\t<link href=\"" . $k . $stamp . "\" rel=\"stylesheet\" type=\"" . $v . "\" />\n";
			}
		}

		// Script declarations
		foreach ($this->_script as $type => $content) {

			$output .= "\t\t<script type=\"" . $type . "\">" . $content . "</script>";
		}

		// Other head data
		foreach ($this->_head as $head) {

			$output .= "\t" . $head . "\n";
		}

		return $output;
	}

	public function setBody($data = '')
	{
		$this->_body = $data;
	}

	private function getBody()
	{
		return $this->_body;
	}

	function loadData()
	{
		//get the file content
		ob_start();
		require_once(CKE_PLUGINS .DS. 'linkBrowser' .DS. 'tmpl' .DS. 'index.php');
		$data = ob_get_contents();
		ob_end_clean();

		return $data;
	}

	/**
	 * Render the document
	 */
	function render()
	{
		// assign language
		$this->language 	= $this->getLanguage();
		$this->direction 	= $this->getLanguageDir();

		// load template data
		$output = $this->loadData();
		$output = $this->parseData($output);

		exit($output);
	}

	private function parseData($data)
	{
		$data = preg_replace_callback('#<!-- \[head\] -->#', array($this, 'getHead'), $data);
		$data = preg_replace_callback('#<!-- \[body\] -->#', array($this, 'getBody'), $data);

		return $data;
	}

	/**
	 * Overridden pack function for plugins
	 */
	public function pack()
	{
		if (JRequest::getCmd('task') == 'pack') {
			require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'parameter.php');
			require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'packer.php');

			$component 	=& JComponentHelper::getComponent('com_ckeditor');
			$params 	= new WFParameter($component->params);

			$cache    = $params->get('editor.compress_cache', 0);
			$compress = $params->get('editor.compress_minify', 0);
			$gzip     = $params->get('editor.compress_gzip', 0);

			$type = JRequest::getWord('type', 'javascript');

			// javascript
			$packer = new WFPacker(array(
								'type' => $type
			));

			$files = array();

			switch ($type) {
				case 'javascript':
					foreach ($this->getScripts() as $script => $type) {
						$script .= preg_match('/\.js$/', $script) ? '' : '.js';

						$files[] = $this->urlToPath($script);
					}
					break;
				case 'css':
					foreach ($this->getStyleSheets() as $style => $type) {
						$style .= preg_match('/\.css$/', $style) ? '' : '.css';

						$files[] = $this->urlToPath($style);
					}
					break;
			}

			$packer->setFiles($files);
			$packer->pack($cache, $compress, $gzip);
		}
	}
}
?>