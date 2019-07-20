<?php
/**
 * CKEditor for Joomla!
 *
 * @version       5.x
 * @package       CKEditor
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2014-2019 by Denys D. Nosov (https://joomla-ua.org)
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

defined('_JEXEC') or die('Restricted access');

define('CKE_PATH', JPATH_PLUGINS . '/editors/ckeditor');
define('CKE_PLUGINS', CKE_PATH . '/plugins');
define('CKE_LIBRARIES', CKE_PATH . '/libraries');
define('CKE_CLASSES', CKE_LIBRARIES . '/classes');

$jinput = JFactory::getApplication()->input;
$task   = $jinput->get('task', '', 'STR');

if($task === 'plugin' || $task === 'help')
{
	require_once __DIR__ . '/editor.php';

	exit();
}

header('Location: index.php');