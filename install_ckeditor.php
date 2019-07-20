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

defined('_JEXEC') or die('Restricted access');

class com_ckeditorInstallerScript
{
	/**
	 * @param $parent
	 *
	 *
	 * @since 5.0
	 */
	public function install($parent)
	{
		$parent->getParent()->setRedirectURL('index.php?option=com_ckeditor');
	}

	/**
	 * @param $parent
	 *
	 * @return mixed
	 *
	 * @since 5.0
	 */
	public function uninstall($parent)
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		jimport('joomla.installer.installer');
		$installer = JInstaller::getInstance();

		$editor_result = JText::_('Success');

		if(JFolder::exists(dirname($installer->getPath('extension_site')) . '/../plugins/editors'))
		{
			$editor_result = JText::_('Error');

			if(JFolder::delete(dirname($installer->getPath('extension_site')) . '/../plugins/editors/ckeditor'))
			{
				$editor_result = JText::_('Success');
			}
		}

		return $editor_result;
	}

	/**
	 * @param $type
	 * @param $parent
	 *
	 * @return string
	 *
	 * @throws \RuntimeException
	 * @throws \InvalidArgumentException
	 * @since 5.0
	 */
	public function preflight($type, $parent)
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		jimport('joomla.installer.installer');

		$installer = JInstaller::getInstance();

		$source   = $installer->getPath('source');
		$packages = $source . '/packages';

		$folders = [
			JPATH_SITE . '/plugins/editors/ckeditor'
		];

		$html = '';

		foreach($folders AS $folder)
		{
			if(is_dir($folder))
			{
				$this->unlinkRecursive($folder, 1);

				$html .= '<p style="color: green;">Delete: ' . $folder . '</p>';
			}
		}

		if(is_dir($packages))
		{
			$editor = JFolder::files($packages, 'plg_ckeditor.zip', false, true);
		}

		$editor_result = JText::_('Error');

		if(!empty($editor) && is_file($editor[ 0 ]))
		{
			$packagePath = dirname($editor[ 0 ]) . '/ckeditor';

			$jArchive = new Joomla\Archive\Archive();

			if(!$jArchive->extract($editor[ 0 ], $packagePath))
			{
				$editor_result = JText::_('EDITOR EXTRACT ERROR');
			}
			else
			{
				$installer = JInstaller::getInstance();

				if(JFolder::copy($packagePath, dirname($installer->getPath('extension_site')) . '/../plugins/editors', '', true))
				{
					$editor_result = JText::_('Success');
				}
				else
				{
					$editor_result = JText::_('Error');
				}
			}
		}

		$html .= $editor_result;

		return $html;
	}

	/**
	 * @param $dir
	 * @param $deleteRootToo
	 *
	 *
	 * @since 5.0
	 */
	public function unlinkRecursive($dir, $deleteRootToo)
	{
		if(!$dh = @opendir($dir))
		{
			return;
		}

		while(false !== ($obj = readdir($dh)))
		{
			if($obj == '.' || $obj == '..')
			{
				continue;
			}

			if(!@unlink($dir . '/' . $obj))
			{
				$this->unlinkRecursive($dir . '/' . $obj, true);
			}
		}

		closedir($dh);

		if($deleteRootToo == 1)
		{
			@rmdir($dir);
		}
	}
}