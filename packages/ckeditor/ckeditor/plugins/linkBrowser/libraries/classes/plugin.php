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
defined('_CKE_EXT') or die('Restricted access');
defined( '_JEXEC' ) or die( 'Restricted access' );
// Needed for cyrillic languages?
header("Content-type: text/html; charset=utf-8");
jimport('joomla.html.parameter');
//require_once( JURI::root().DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'joomla'.DIRECTORY_SEPARATOR.'html' .DIRECTORY_SEPARATOR. 'parameter.php');
require_once( dirname(__FILE__).DIRECTORY_SEPARATOR. 'editor.php');
require_once (dirname(__FILE__).DIRECTORY_SEPARATOR.'request.php');

/**
 * JCE class
 *
 * @static
 * @package		JCE
 * @since	1.5
 */

class WFEditorPlugin extends WFEditor
{
	/*
	 *  @var array
	 */
	var $plugin = null;
	/*
	 *  @var varchar
	 */
	var $url = array();
	/*
	 *  @var varchar
	 */
	var $request = null;
	/*
	 *  @var array
	 */
	var $scripts = array();
	/*
	 *  @var array
	 */
	var $styles = array();
	/*
	 *  @var array
	 */
	var $head = array();
	/*
	 *  @var array
	 */
	var $alerts = array();
	/**
	 * Constructor activating the default information of the class
	 *
	 * @access	protected
	 */
	function __construct()
	{

		// Call parent
		parent::__construct();

		$db =& JFactory::getDBO();

		$plugin = JRequest::getVar('plugin');

		if ($plugin) {
			if (is_dir(CKE_PLUGINS . DS . $plugin)) {

				$this->set('name', 	$plugin);
				$this->set('type', 		JRequest::getVar('type', 'standard'));

			/*	if (!defined('CKE_PLUGINS')) {
					define('CKE_PLUGINS', CKE_PLUGINS . DS . $plugin);
				}*/

				// set variables for view
				$this->set('layout', 		'link');
				$this->set('base_path', 	dirname(__FILE__).DS.'..'.DS.'..'.DS.'tmpl'.DS.'browser'.DS.'tmpl');
				$this->set('template_path',CKE_PLUGINS .DS.'linkBrowser'.DS. 'tmpl'.DS.'linkBrowser'.DS.'tmpl');
			}
		} else {
			die(JError::raiseError(403, JText::_('ERROR_403')));
		}
	}
	/**
	 * Returns a reference to a editor object
	 *
	 * This method must be invoked as:
	 * 		<pre>  $browser = &JCE::getInstance();</pre>
	 *
	 * @access	public
	 * @return	JCE  The editor object.
	 * @since	1.5
	 */
	function & getInstance()
	{
		static $instance;

		if (!is_object($instance)) {
			$instance = new WFEditorPlugin();
		}

		return $instance;
	}

	function execute()
	{

		if (JRequest::getVar('json', '', 'POST', 'STRING', 2) || JRequest::getCmd('action') == 'upload') {
			$this->processXHR();
		} else {

			$this->display();

			$document =& WFDocument::getInstance();
			$document->pack();

			// create plugin view
			$view = new WFView(array(
				'base_path'		=> $this->get('base_path'),
				'template_path'	=> $this->get('template_path'),
				'name' 			=> 'link',//$this->get('name'),
				'layout'		=> $this->get('layout')
			));

			$view->assign('plugin', $this);
			// set body output

			$document->setBody($view->loadTemplate());
			$document->render();
		}
	}

	function display()
	{

		jimport('joomla.filesystem.folder');

		// get UI Theme
		$theme = $this->getParam('editor_dialog_theme', 'ckeditor');

		$document =& WFDocument::getInstance(array(
			'version' => $this->getVersion(),
			'title'	  => 'Link Browser'
		));


		//$document->addScript(array('tiny_mce_popup'), 'libraries');

		// jquery versions
		$jquery = array();
		//TODO fix this

		$files = JFolder::files(JPATH_COMPONENT_ADMINISTRATOR.DS.'media'.DS.'js'.DS.'jquery', '\.js$');

		foreach ($files as $file) {
			$jquery[] = 'jquery/' . $file;
		}

		$document->addScript($jquery, 'component');

		$document->addScript(array(
			//'tiny_mce_utils',
			'plugin'
		), 'libraries');

	//$document->addScript(array('select', 'tips'), 'component');

		$ui = JFolder::files(JPATH_COMPONENT_ADMINISTRATOR.DS.'media'.DS.'css'.DS.'jquery'.DS.$theme, '\.css$');

		$document->addStyleSheet(array('jquery/'.$theme.'/'.basename($ui[0], '.css')), 'component');

		$document->addStyleSheet(array(
	//		'plugin'
		), 'libraries');

		// Load Plugin language
		$this->loadPluginLanguage();

	}

	function getName()
	{
		return $this->get('name');
	}

	function getDefaults($defaults)
	{
		if (!$defaults) {
			return '';
		}

		$params = $this->getParams();
		$ret = array();

		foreach ($defaults as $k => $v) {
			$p = $params->get($k, $v);
			$ret[$k] = $p;

		}
		return $ret;
	}

	/**
	 * Named wrapper to check access to a feature
	 *
	 * @access 			public
	 * @param string	The feature to check, eg: upload
	 * @param string	The defalt value
	 * @return 			string
	 */
	function checkAccess($option, $default = '')
	{
		return $this->getSharedParam($option, $default);
	}
	/**
	 * Check the user is in an authorized group
	 * Check the users group is authorized to use the plugin
	 *
	 * @access 			public
	 * @return 			boolean
	 */
	function checkPlugin()
	{
//		$profile = $this->getProfile();
			return true;
	//	return is_object($profile);
	}
	/**
	 * Returns a an array of Help topics
	 *
	 * @access	public
	 * @return	Array
	 * @since	1.5
	 */
	function getHelpTopics()
	{
		// Load plugin xml file
		$result = '';

		$file = JCE_EDITOR_PLUGIN .DS. $this->getName(). ".xml";
		$result .= '<dl><dt><span>'. JText::_('JCE '.strtoupper($this->getName()).' TITLE') .'<span></dt>';

		if (file_exists($file)) {
			$xml =& JFactory::getXMLParser('Simple');

			if ($xml->loadFile($file)) {
				$root 	=& $xml->document;
				$topics = $root->getElementByPath('help');

				if ($topics) {
					foreach ($topics->children() as $topic) {
						$result .= '<dd id="'. $topic->attributes('key') .'"><a href="javascript:;" onclick="helpDialog.loadFrame(this.parentNode.id)">'. trim(JText::_($topic->attributes('title'))) .'</a></dd>';
					}
				}
			}
		}

		$result .= '</dl>';

		if ($this->plugin->type == 'manager') {
			$file = JCE_EDITOR_LIBRARIES .DS. "xml" .DS. "help" .DS. "manager.xml";
			$result .= '<dl><dt><span>'. JText::_('JCE MANAGER HELP') .'<span></dt>';
			if (file_exists($file)) {
				$xml =& JFactory::getXMLParser('Simple');
				if ($xml->loadFile($file)) {
					$root =& $xml->document;
					if ($root) {
						foreach ($root->children() as $topic) {
							$result .= '<dd id="'. $topic->attributes('key') .'"><a href="javascript:;" onclick="helpDialog.loadFrame(this.parentNode.id)">'. JText::_($topic->attributes('title')) .'</a></dd>';
						}
					}
				}
			}
			$result .= '</dl>';
		}
		return $result;
	}

	/**
	 * Load current plugin language file
	 */
	function loadPluginLanguage()
	{
		$this->loadLanguage('com_jce_'. trim($this->get('name')));

	}

	/**
	 * Add an alert array to the stack
	 *
	 * @param object $class[optional] Alert classname
	 * @param object $title[optional] Alert title
	 * @param object $text[optional]  Alert text
	 */
	function addAlert($class = 'info', $title = '', $text = '')
	{
		$this->alerts[] = array(
			'class' => $class,
			'title'	=> $title,
			'text'	=> $text
		);
	}
	/**
	 * Get current alerts
	 * @return object Alerts as object
	 */
	function getAlerts($decode = true)
	{
		if ($decode) {
			return json_encode($this->alerts);
		}
		return $this->alerts;

	}

	/**
	 * Convert a url to path
	 *
	 * @access	public
	 * @param	string 	The url to convert
	 * @return	Full path to file
	 * @since	1.5
	 */
	function urlToPath($url)
	{
		$document =& WFDocument::getInstance();
		return $document->urlToPath($url);
	}

	/**
	 * Returns an image url
	 *
	 * @access	public
	 * @param	string 	The file to load including path and extension eg: libaries.image.gif
	 * @return	Image url
	 * @since	1.5
	 */
	function image($image, $root = 'libraries') {
		$document =& WFDocument::getInstance();

		return $document->image($image, $root);
	}
	/**
	 * Load a plugin extension
	 *
	 * @access	public
	 * @since	1.5
	 */
	function getExtensions($arguments) {
		return array();
	}
	/**
	 * Load & Call an extension
	 *
	 * @access	public
	 * @since	1.5
	 */
	function loadExtensions($config=array()) {
		return array();
	}
	/**
	 * Setup an ajax function
	 *
	 * @access public
	 * @param array		An array containing the function and object
	 */
	function setXHR($function) {
		$request =& WFRequest::getInstance();
		$request->setXHR($function);
	}

	/**
	 * Process an ajax call and return result
	 *
	 * @access public
	 * @return string
	 */
	function processXHR($array = false) {
		$request =& WFRequest::getInstance();
		$request->processXHR($array);
	}

	function getSettings($settings = array())
	{
		$default = array(
			'alerts'	=>  json_decode($this->getAlerts()),
			// Plugin parameters
			'params'	=> array ()
		);

		$settings = array_merge_recursive($default, $settings);
		return $settings;
	}
}
?>