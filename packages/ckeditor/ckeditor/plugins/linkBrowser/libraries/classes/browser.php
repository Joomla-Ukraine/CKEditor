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

defined('_CKE_EXT') or die('Restricted access');
class WFBrowserExtension extends JObject
{
	/*
	*  @var varchar
	*/
	var $extensions = array();
	/**
	* Constructor activating the default information of the class
	*
	* @access	protected
	*/
	function __construct()
	{
		parent::__construct();
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
	function &getInstance($type)
	{
		static $instance;

		if (!is_object($instance)) {
		//TODO move that folder
			require_once(dirname(__FILE__).DS.'..'.DS.'..'.DS.'extensions'.DS.'browser'.DS.$type.'.php');

			$classname 	= 'WF'.ucfirst($type).'Browser';

			if (class_exists($classname)) {
				$instance = new $classname();

			} else {
				$instance = new WFBrowserExtension();
			}
		}

		return $instance;
	}

	function getView($layout)
	{
		$view = new WFView(array(
			'name'		=> 'browser',
			'layout' 	=>	$layout
		));

		return $view;
	}
}