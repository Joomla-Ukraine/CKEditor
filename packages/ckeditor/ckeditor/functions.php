<?php
/*
* @copyright Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
* @license	 Licensed under CKEditor for Joomla License Agreement, version 1.0. For licensing, see http://ckeditor.com/ckeditor-for-joomla/license
*/
function checkPackageLicense($licenseKey, $licenseName)
{
	$ckfinderKey = null;
	$auth        = false;
	if(strlen($licenseKey) == 49)
	{
		$ckfinderKey = substr($licenseKey, -34, 34);
		$packageKey  = substr($licenseKey, 0, 15);
		$auth        = checkKey($packageKey, $licenseName);
	}

	return array(
		'authenticate' => $auth,
		'key'          => $ckfinderKey
	);
}

/*
* OMG it's so simple :O
* Simply change to "return true" to get rid of the demo message (...ehm what?).
* ...wait! If you don't need the commercial license, you can download the Open Source version
* of this extension ;)
*/
function checkKey($key, $licenseName)
{
	//getApplication
	$app = JFactory::getApplication();
	if(strlen($key) == 15 && ($key[ 0 ] == 'C' || $key[ 0 ] == 'D') && $key[ 13 ] == 'K' && ((int) (ord($key[ 10 ]) . ord($key[ 11 ])) % 3 == 0) && ((int) (ord($key[ 6 ]) . ord($key[ 7 ])) % 2 == 0) && $key[ 6 ] != '0' && $key[ 7 ] != '0' && $key[ 10 ] != '0' && $key[ 11 ] != '0')
	{
		if($key[ 0 ] == 'D' && $app->getName() == 'administrator')
		{
			JError::raiseNotice(100, JText::_('DEVELOPER_LICENSE_MESSAGE'));
			JError::raiseNotice(100, JText::_('DEVELOPER_LICENSE_NAME_MESSAGE') . '<strong>' . $licenseName . '</strong>');
		}

		return true;
	}

	return false;
}