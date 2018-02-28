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

// no direct access
defined('_JEXEC') or die('Restricted access');

if(!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

define('CKE_PATH', JPATH_PLUGINS . DS . 'editors' . DS . 'ckeditor');
define('CKE_PLUGINS', CKE_PATH . DS . 'plugins');
define('CKE_LIBRARIES', CKE_PATH . DS . 'libraries');
define('CKE_CLASSES', CKE_LIBRARIES . DS . 'classes');
$task = JRequest::getCmd('task');

/*
if (!$task)
{
	$db =& JFactory::getDBO();
	$db->setQuery('SELECT id FROM #__plugins WHERE element="ckeditor"');
	$id = $db->loadResult();
	if ((int)$id > 0)
	{
	header("Location: index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]=$id");
	exit();
	}else{
		die('You have to install CKEditor plugin');
	}

}*/

/*
 * Editor or plugin request.
 */
if($task == 'plugin' || $task == 'help')
{
	require_once(dirname(__FILE__) . DS . 'editor.php');

	exit();
}

header('Location: index.php');
