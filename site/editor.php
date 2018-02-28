<?php
/*
* This file uses parts of JCE extension by Ryan Demmer.
* @copyright	Copyright (C) 2005-2014 Ryan Demmer. All rights reserved.
* @copyright	Copyright (C) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
* @license		GNU/GPL
* CKEditor extension is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*/


defined('_JEXEC') or die ('Restricted access');

class JContentEditorBridge extends JObject
{
	/**
	 * Returns a reference to a editor object
	 *
	 * This method must be invoked as:
	 *    <pre>  $bridge = &JContentEditorBridge::getInstance();</pre>
	 *
	 * @access  public
	 * @return  The bridge object.
	 * @since   1.5.7
	 */
	public function getInstance()
	{
		static $instance;

		if(!is_object($instance))
		{
			$instance = new JContentEditorBridge();
		}

		return $instance;
	}

	/**
	 * Load Plugin files
	 */
	public function load()
	{
		$task = JRequest::getCmd('task');
		if($task)
		{
			switch($task)
			{
				case 'plugin':
					$plugin = JRequest::getVar('plugin', 'cmd');
					JError::raiseError(500, JText::_('PLUGIN_NOT_FOUND'));
					exit ();
					break;
			}
		}
		else
		{
			JError::raiseError(500, JText::_('NO_TASK'));
		}
	}
}

$bridge = &JContentEditorBridge::getInstance();
$bridge->load();