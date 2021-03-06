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

use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;

defined('_JEXEC') or die;

class JContentEditorBridge extends CMSObject
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
		$jinput = Factory::getApplication()->input;
		$task   = $jinput->get('task', '', 'STR');

		if($task)
		{
			switch($task)
			{
				case 'plugin':
					$plugin = JRequest::getVar('plugin', 'cmd');
					Factory::getApplication()->enqueueMessage(JText::_('PLUGIN_NOT_FOUND'), 'message');
					exit ();
					break;
			}
		}
		else
		{
			Factory::getApplication()->enqueueMessage(JText::_('NO_TASK'), 'message');
		}
	}
}

$bridge = JContentEditorBridge::getInstance();
$bridge->load();