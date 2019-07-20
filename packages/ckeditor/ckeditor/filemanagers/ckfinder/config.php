<?php
/*
 * CKFinder : configuration file for Joomla!
 * Replace the original config.php file provided with CKFinder with this file.
 */

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);

$rootFolder = explode(DS, __DIR__);
array_splice($rootFolder, -4);
$base_folder = implode(DS, $rootFolder);
define('JPATH_BASE', str_replace('\plugins', '', $base_folder));

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

if($_COOKIE[ 'ckfinder_app' ] == 1)
{
	$mainframe = JFactory::getApplication('administrator');
}
else
{
	$mainframe = JFactory::getApplication('site');
}
$mainframe->initialise();

function CheckAuthentication()
{
	$session = JFactory::getSession();
	if($session->getState() === 'active' && $session->get('FilesAccess') == true)
	{
		$GLOBALS[ 'config' ][ 'LicenseName' ] = $_SERVER[ 'HTTP_HOST' ];

		$str_dom                             = strlen($_SERVER[ 'HTTP_HOST' ]);
		$characters                          = 'ZYXWVUTSRQPNMLKJHGFEDCBA987654321';
		$GLOBALS[ 'config' ][ 'LicenseKey' ] = '2' . $characters[ $str_dom % 33 + (int) ($str_dom / 33) ] . '11EFGH1JK11NOPQRSTUVWXY81';

		return true;
	}

	return false;
}

$session = JFactory::getSession();
$baseUrl = 'cache/';
$baseDir = JPATH_BASE . '/cache';

$config[ 'Thumbnails' ] = [
	'url'          => $session->get('FilesThumbsUrl') ? $session->get('FilesThumbsUrl') : $baseUrl . '_thumbs',
	'directory'    => $session->get('FilesThumbsPath') ? $session->get('FilesThumbsPath') : $baseDir . '_thumbs',
	'enabled'      => true,
	'directAccess' => false,
	'maxWidth'     => $session->get('FilesMaxThumbnailWidth') ? $session->get('FilesMaxThumbnailWidth') : 180,
	'maxHeight'    => $session->get('FilesMaxThumbnailHeight') ? $session->get('FilesMaxThumbnailHeight') : 180,
	'bmpSupported' => false,
	'quality'      => 75
];

$config[ 'Images' ] = [
	'maxWidth'  => $session->get('FilesMaxImageWidth') ? $session->get('FilesMaxImageWidth') : 1600,
	'maxHeight' => $session->get('FilesMaxImageHeight') ? $session->get('FilesMaxImageHeight') : 1200,
	'quality'   => 75
];

$config[ 'RoleSessionVar' ] = 'CKFinder_UserRole';

$config[ 'AccessControl' ][] = [
	'role'         => '*',
	'resourceType' => '*',
	'folder'       => '/',

	'folderView'   => true,
	'folderCreate' => true,
	'folderRename' => true,
	'folderDelete' => true,

	'fileView'   => true,
	'fileUpload' => true,
	'fileRename' => true,
	'fileDelete' => true
];

$config[ 'DefaultResourceTypes' ] = '';

$config[ 'ResourceType' ][] = [
	'name'              => 'Files',
	// Single quotes not allowed
	'url'               => $session->get('FilesFilesUrl') ? $session->get('FilesFilesUrl') : $baseUrl . 'files',
	'directory'         => $session->get('FilesFilesPath') ? $session->get('FilesFilesPath') : $baseDir . 'files',
	'maxSize'           => $session->get('FilesMaxFilesSize') ? $session->get('FilesMaxFilesSize') : 0,
	'allowedExtensions' => $session->get('FilesResourceFiles') ? $session->get('FilesResourceFiles') : '7z,aiff,asf,avi,bmp,csv,doc,fla,flv,gif,gz,gzip,jpeg,jpg,mid,mov,mp3,mp4,mpc,mpeg,mpg,ods,odt,pdf,png,ppt,pxd,qt,ram,rar,rm,rmi,rmvb,rtf,sdc,sitd,swf,sxc,sxw,tar,tgz,tif,tiff,txt,vsd,wav,wma,wmv,xls,zip',
	'deniedExtensions'  => ''
];

$config[ 'ResourceType' ][] = [
	'name'              => 'Images',
	'url'               => $session->get('FilesImagesUrl') ? $session->get('FilesImagesUrl') : $baseUrl . 'images',
	'directory'         => $session->get('FilesImagesPath') ? $session->get('FilesImagesPath') : $baseDir . 'images',
	'maxSize'           => $session->get('FilesMaxImagesSize') ? $session->get('FilesMaxImagesSize') : 0,
	'allowedExtensions' => $session->get('FilesResourceImages') ? $session->get('FilesResourceImages') : 'bmp,gif,jpeg,jpg,png',
	'deniedExtensions'  => ''
];

$config[ 'ResourceType' ][] = [
	'name'              => 'Flash',
	'url'               => $session->get('FilesFlashUrl') ? $session->get('FilesFlashUrl') : $baseUrl . 'flash',
	'directory'         => $session->get('FilesFlashPath') ? $session->get('FilesFlashPath') : $baseDir . 'flash',
	'maxSize'           => $session->get('FilesMaxFlashSize') ? $session->get('FilesMaxFlashSize') : 0,
	'allowedExtensions' => $session->get('FilesResourceFlash') ? $session->get('FilesResourceFlash') : 'swf,flv',
	'deniedExtensions'  => ''
];

/*
Due to security issues with Apache modules, it is recommended to leave the
following setting enabled.

How does it work? Suppose the following:

- If "php" is on the denied extensions list, a file named foo.php cannot be
uploaded.
- If "rar" (or any other) extension is allowed, one can upload a file named
foo.rar.
- The file foo.php.rar has "rar" extension so, in theory, it can also be
uploaded.

In some conditions Apache can treat the foo.php.rar file just like any PHP
script and execute it.

If CheckDoubleExtension is enabled, each part of the file name after a dot is
checked, not only the last part. In this way uploading foo.php.rar would be
denied, because "php" is on the denied extensions list.
 */
$config[ 'CheckDoubleExtension' ] = true;

/*
Increases the security on IIS web server.
If enabled, CKFinder will disallow creating folders and uploading files that contain characters
in their names, that are not safe under IIS web server.
*/
$config[ 'DisallowUnsafeCharacters' ] = false;
/*
If you have iconv enabled (visit http://php.net/iconv for more information),
you can use this directive to specify the encoding of file names in your
system. Acceptable values can be found at:
http://www.gnu.org/software/libiconv/

Examples:
$config['FilesystemEncoding'] = 'CP1250';
$config['FilesystemEncoding'] = 'ISO-8859-2';
 */
if(function_exists('iconv'))
{
	$config[ 'FilesystemEncoding' ] = iconv_get_encoding('internal_encoding');
}
else
{
	$config[ 'FilesystemEncoding' ] = 'UTF-8';
}

/*
Perform additional checks for image files
If set to true, validate image size.
 */
$config[ 'SecureImageUploads' ] = true;

/*
Indicates that the file size (maxSize) for images must be checked only
after scaling them. Otherwise, it is checked right after uploading.
 */
$config[ 'CheckSizeAfterScaling' ] = true;

/*
For security, HTML is allowed in the first Kb of data for files having the
following extensions only.
 */
$config[ 'HtmlExtensions' ] = [
	'html',
	'htm',
	'xml',
	'js'
];

/*
Folders to not display in CKFinder, no matter what their location is.
No paths are accepted, only the folder name.
The * and ? wildcards are accepted.
 */
$config[ 'HideFolders' ] = [
	'.svn',
	'CVS'
];

/*
Files to not display in CKFinder, no matter what their location is.
No paths are accepted, only the file name, including extension.
The * and ? wildcards are accepted.
 */
$config[ 'HideFiles' ] = [ '.*' ];

/*
After a file is uploaded, sometimes it is required to change its permissions
so that it was possible to access it at the later time.
If possible, it is recommended to set more restrictive permissions, like 0755.
Set to 0 to disable this feature.
Note: not needed on Windows-based servers.
 */
$config[ 'ChmodFiles' ] = 0755;

/*
See comments above.
Used when creating folders that do not exist.
 */
$config[ 'ChmodFolders' ] = 0755;

/*
Force ASCII names for files and folders.
If enabled, characters with diactric marks, like å, ä, ö, ć, č, đ, or š,
will automatically be converted to ASCII letters.
 */
$config[ 'ForceAscii' ] = true;

$plugins = $session->get('CKFinderSettingsPlugins');

if($plugins[ 'imageresize' ])
{
	include_once __DIR__ . '/plugins/imageresize/plugin.php';
}

if($plugins[ 'fileedit' ])
{
	include_once __DIR__ . '/plugins/fileeditor/plugin.php';
}

$config[ 'plugin_imageresize' ][ 'smallThumb' ]  = '90x90';
$config[ 'plugin_imageresize' ][ 'mediumThumb' ] = '120x120';
$config[ 'plugin_imageresize' ][ 'largeThumb' ]  = '180x180';