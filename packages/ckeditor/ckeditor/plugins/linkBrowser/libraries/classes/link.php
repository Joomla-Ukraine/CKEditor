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
//defined('_CKE_EXT') or die('Restricted access');
// Set flag that this is an extension parent
defined( '_JEXEC' ) or die( 'Restricted access' );
// Load class dependencies

require_once (dirname(__FILE__).DIRECTORY_SEPARATOR.'plugin.php');
require_once (dirname(__FILE__).DIRECTORY_SEPARATOR.'browser.php');
require_once (dirname(__FILE__).DIRECTORY_SEPARATOR.'document.php');
//require_once (dirname(__FILE__).DIRECTORY_SEPARATOR.'tabs.php');
require_once (dirname(__FILE__).DIRECTORY_SEPARATOR.'view.php');
require_once (dirname(__FILE__).DIRECTORY_SEPARATOR.'extensions.php');
require_once (dirname(__FILE__).DIRECTORY_SEPARATOR.'token.php');
require_once (dirname(__FILE__).DIRECTORY_SEPARATOR.'utility.php');
//require_once (dirname(__FILE__).DIRECTORY_SEPARATOR.'popups.php');


class WFLinkPlugin extends WFEditorPlugin
{
	/*
	 *  @var varchar
	 */
	var $extensions = array();

	var $popups 	= array();

	var $tabs 		= array();
	/**
	 * Constructor activating the default information of the class
	 *
	 * @access	protected
	 */
	function __construct()
	{

		parent::__construct();

		$this->setXHR(array($this, 'getLinks'));

		// check the user/group has editor permissions
		$this->checkPlugin() or die(JError::raiseError(403, JText::_('ERROR_403')));

	}
	/**
	 * Returns a reference to a plugin object
	 *
	 * This method must be invoked as:
	 * 		<pre>  $advlink = &AdvLink::getInstance();</pre>
	 *
	 * @access	public
	 * @return	JCE  The editor object.
	 * @since	1.5
	 */
	function &getInstance()
	{
		static $instance;

		if (!is_object($instance)) {
			$instance = new WFLinkPlugin();
		}
		return $instance;
	}

	function display()
	{

		parent::display();

		$document =& WFDocument::getInstance();

		//$document->addScript(array('link'), 'plugins');
		$document->addStyleSheet(array('link'), 'plugins');

//		$settings = $this->getSettings();

	//	$document->addScriptDeclaration('LinkDialog.settings='.json_encode($settings).';');

		/*$tabs =& WFTabs::getInstance(array(
			'base_path' => CKE_PLUGINS
		));

		// Add tabs
		$tabs->addTab('link', 1);
		$tabs->addTab('advanced', $this->getParam('link_tabs_advanced', 1));
*/
		// Load Popups instance
		/*$popups =& WFPopupsExtension::getInstance(array(
					'text' => false
		));
*/
		$browser =& $this->getBrowser();

		$browser->display();

	}

	function getBrowser()
	{
		static $browser;

		if (!is_object($browser)) {
			$browser =& WFBrowserExtension::getInstance('link');
		}

		return $browser;
	}

	function getLinks($args)
	{

		$browser =& $this->getBrowser();
		return $browser->getLinks($args);
	}

	function getLinkBrowser()
	{
		$browser =& $this->getBrowser();
		return $browser->getLinkBrowser();
	}

	function getSettings()
	{
		$params = $this->getParams();

		$settings = array(
			'params' => array(
				'defaults' => $this->getDefaults()
		),
			'file_browser'	=> $params->get('link_file_browser', 1)
		);

		return parent::getSettings($settings);
	}

	function getDefaults()
	{
		$defaults = array(
			'targetlist' => 'default'
			);
			return parent::getDefaults($defaults);
	}
}