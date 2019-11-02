<?php

use Joomla\CMS\Factory;

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

$session = Factory::getSession();

$config = [];

$config[ 'authentication' ] = function ()
{
	$session = Factory::getSession();
	if($session->getState() === 'active' && $session->get('CKFinder3Access') === true)
	{
		if($session->get('CKFinder3LicenseName') != '' && $session->get('CKFinder3LicenseKey') != '')
		{
			return true;
		}
	}

	return false;
};

$config[ 'licenseName' ] = trim($session->get('CKFinder3LicenseName'));
$config[ 'licenseKey' ]  = trim($session->get('CKFinder3LicenseKey'));

$config[ 'privateDir' ] = [
	'backend' => 'default',
	'tags'    => 'logs/tags',
	'logs'    => 'logs',
	'cache'   => 'cache',
	'thumbs'  => $session->get('CKFinder3ThumbsPath') ? $session->get('CKFinder3ThumbsPath') : 'cache/_thumbs',
];

/*============================ Images and Thumbnails ==================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_images

$config[ 'images' ] = [
	'maxWidth'  => $session->get('CKFinder3MaxImageWidth') ? $session->get('CKFinder3MaxImageWidth') : 1600,
	'maxHeight' => $session->get('CKFinder3MaxImageHeight') ? $session->get('CKFinder3MaxImageHeight') : 1200,
	'quality'   => 80,
	'sizes'     => [
		'small'  => [ 'width' => 480, 'height' => 320, 'quality' => 80 ],
		'medium' => [ 'width' => 600, 'height' => 480, 'quality' => 80 ],
		'large'  => [ 'width' => 800, 'height' => 600, 'quality' => 80 ]
	]
];

//'baseUrl'            => str_replace('plugins/editors/ckeditor/ckfinder/core/connector/php', '', Uri::base(true)),

$config[ 'backends' ][] = [
	'name'               => 'default',
	'adapter'            => 'local',
	'baseUrl'            => '',
	//  'root'         => '', // Can be used to explicitly set the CKFinder user files directory.
	'chmodFiles'         => 0777,
	'chmodFolders'       => 0755,
	'filesystemEncoding' => 'UTF-8'
];

/*================================ Resource Types =====================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_resourceTypes

$config[ 'defaultResourceTypes' ] = 'Images,Files';

$config[ 'resourceTypes' ][] = [
	'name'              => 'Images',
	'directory'         => $session->get('CKFinder3ImagesPath') ? $session->get('CKFinder3ImagesPath') : 'images',
	'maxSize'           => 0,
	'allowedExtensions' => $session->get('CKFinder3ResourceImages') ? $session->get('CKFinder3ResourceImages') : 'bmp,gif,jpeg,jpg,png',
	'deniedExtensions'  => '',
	'backend'           => 'default',
	'lazyLoad'          => true
];


$config[ 'resourceTypes' ][] = [
	'name'              => 'Files', // Single quotes not allowed.
	'directory'         => $session->get('CKFinder3FilesPath') ? $session->get('CKFinder3FilesPath') : 'files',
	'maxSize'           => $session->get('CKFinder3MaxFilesSize') ? $session->get('CKFinder3MaxFilesSize') : 0,
	'allowedExtensions' => $session->get('CKFinder3ResourceFiles') ? $session->get('CKFinder3ResourceFiles') : '7z,aiff,asf,avi,bmp,csv,doc,docx,fla,flv,gif,gz,gzip,jpeg,jpg,mid,mov,mp3,mp4,mpc,mpeg,mpg,ods,odt,pdf,png,ppt,pptx,qt,ram,rar,rm,rmi,rmvb,rtf,sdc,swf,sxc,sxw,tar,tgz,tif,tiff,txt,vsd,wav,wma,wmv,xls,xlsx,zip',
	'deniedExtensions'  => '',
	'backend'           => 'default',
	'lazyLoad'          => true
];


/*================================ Access Control =====================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_roleSessionVar

$config[ 'roleSessionVar' ] = 'CKFinder_UserRole';

// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_accessControl
$config[ 'accessControl' ][] = [
	'role'         => '*',
	'resourceType' => '*',
	'folder'       => '/',

	'FOLDER_VIEW'   => true,
	'FOLDER_CREATE' => true,
	'FOLDER_RENAME' => true,
	'FOLDER_DELETE' => true,

	'FILE_VIEW'   => true,
	'FILE_CREATE' => true,
	'FILE_RENAME' => true,
	'FILE_DELETE' => true,

	'IMAGE_RESIZE'        => true,
	'IMAGE_RESIZE_CUSTOM' => true
];


/*================================ Other Settings =====================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html

$config[ 'overwriteOnUpload' ]        = false;
$config[ 'checkDoubleExtension' ]     = true;
$config[ 'disallowUnsafeCharacters' ] = true;
$config[ 'secureImageUploads' ]       = true;
$config[ 'checkSizeAfterScaling' ]    = true;
$config[ 'htmlExtensions' ]           = [ 'html', 'htm', 'xml', 'js' ];
$config[ 'hideFolders' ]              = [ '.*', 'CVS', '__thumbs' ];
$config[ 'hideFiles' ]                = [ '.*' ];
$config[ 'forceAscii' ]               = false;
$config[ 'xSendfile' ]                = false;
$config[ 'debug' ]                    = false;

/*==================================== Plugins ========================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_plugins

$config[ 'pluginsDirectory' ] = __DIR__ . '/plugins';
$config[ 'plugins' ]          = [];

/*================================ Cache settings =====================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_cache

$config[ 'cache' ] = [
	'imagePreview' => 24 * 3600,
	'thumbnails'   => 24 * 3600 * 365,
	'proxyCommand' => 0
];

$config[ 'tempDirectory' ]     = JPATH_BASE . '/tmp';
$config[ 'sessionWriteClose' ] = true;
$config[ 'csrfProtection' ]    = true;
$config[ 'headers' ]           = [];

return $config;