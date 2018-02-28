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

require_once JPATH_COMPONENT . '/config/controller.php';

$controller = new ConfigController(array(
	'base_path' => JPATH_COMPONENT . '/config'
));

$controller->execute(JRequest::getCmd('task'));
$controller->redirect();