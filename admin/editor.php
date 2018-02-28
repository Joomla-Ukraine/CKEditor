<?php
/**
 * CKEditor for Joomla!
 *
 * @version       5.x
 * @package       CKEditor
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2014-2018 by Denys D. Nosov (https://joomla-ua.org)
 * @license       LICENSE.md
 *
 **/

/*
* @copyright Copyright (C) 2005-2014 Ryan Demmer. All rights reserved.
* @copyright Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
* @license	 GNU/GPL
* CKEditor extension is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*/

defined('_JEXEC') or die ('Restricted access');

class JContentEditorBridge extends JObject
{
	/**
	 *
	 * @return \JContentEditorBridge
	 *
	 * @since 5.0
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
	 *
	 *
	 * @since 5.0
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