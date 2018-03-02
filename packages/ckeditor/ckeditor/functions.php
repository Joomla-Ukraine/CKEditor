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
* @copyright Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
* @license	 GNU/GPL
* CKEditor extension is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*/

/**
 * @param $licenseKey
 * @param $licenseName
 *
 * @return array
 *
 * @since 5.0
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

/**
 * @param $key
 * @param $licenseName
 *
 * @return bool
 *
 * @since 5.0
 */
function checkKey($key, $licenseName)
{
	$app = JFactory::getApplication();

	if(strlen($key) == 15 && ($key[ 0 ] == 'C' || $key[ 0 ] == 'D') && $key[ 13 ] == 'K' && ((int) (ord($key[ 10 ]) . ord($key[ 11 ])) % 3 == 0) && ((int) (ord($key[ 6 ]) . ord($key[ 7 ])) % 2 == 0) && $key[ 6 ] != '0' && $key[ 7 ] != '0' && $key[ 10 ] != '0' && $key[ 11 ] != '0')
	{
		if($key[ 0 ] == 'D' && $app->getName() == 'administrator')
		{
			JFactory::getApplication()->enqueueMessage(JText::_('DEVELOPER_LICENSE_MESSAGE'), 'message');
			JFactory::getApplication()->enqueueMessage(JText::_('DEVELOPER_LICENSE_NAME_MESSAGE') . '<strong>' . $licenseName . '</strong>', 'message');
		}

		return true;
	}

	return false;
}