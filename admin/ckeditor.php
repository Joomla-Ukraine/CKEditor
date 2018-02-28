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

defined('_JEXEC') or die('Restricted access');

define('CKE_PATH', JPATH_PLUGINS . '/editors/ckeditor');
define('CKE_PLUGINS', CKE_PATH . '/plugins');
define('CKE_LIBRARIES', CKE_PATH . '/libraries');
define('CKE_CLASSES', CKE_LIBRARIES . '/classes');

$task = JRequest::getCmd('task');

if($task == 'plugin' || $task == 'help')
{
	require_once __DIR__ . '/editor.php';

	exit();
}

require_once __DIR__ . '/config/config.php';