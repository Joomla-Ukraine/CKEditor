<?php
/*
* @copyright Copyright (c) 2014-2016, Joomla! Ukraine - Denys Nosov. All rights reserved.
* @copyright Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
* @license	 GNU/GPL
* CKEditor extension is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

if(!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

class plgEditorCKeditor extends JPlugin
{
	public $pluginsName = array();

	public $called = false;

	/**
	 * plgEditorCKeditor constructor.
	 *
	 * @param $subject
	 * @param $config
	 */
	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$language = JFactory::getLanguage();
		$language->load('com_ckeditor', JPATH_ADMINISTRATOR, 'en-GB', true);

		return true;
	}

	function onInit()
	{
		$vcssjs = '?v5.8';

		$document = JFactory::getDocument();
		$load     = "\t<script>window.CKEDITOR_BASEPATH='" . JURI::root() . "plugins/editors/ckeditor/ckeditor/';</script>\n";

		if($this->params->get('CKEditorJs', 0) == 1 && is_dir('../plugins/editors/ckeditor/ckeditor/_source/') && file_exists('../plugins/editors/ckeditor/ckeditor/ckeditor_source.js'))
		{
			$load .= "\t<script src=\"" . JURI::root(true) . '/plugins/editors/ckeditor/ckeditor/ckeditor_source.js' . $vcssjs . "\"></script>\n";
		}
		else
		{
			$load .= "\t<script src=\"" . JURI::root(true) . '/plugins/editors/ckeditor/ckeditor/ckeditor.js' . $vcssjs . "\"></script>\n";
		}

		//set base href to works with default joomla editor
		$load .= "\t<script>";
		$load .= "\n\t\tCKEDITOR.config.baseHref = '" . JURI::root() . "';";

		if($this->params->get('LinkBrowserUrl', 1) == 0)
		{
			$load .= "\n\t\tvar linkBrowserUrl = 'relative';";
		}
		else
		{
			$load .= "\n\t\tvar linkBrowserUrl = 'absolute';";
		}

		//iterate in ckeditor/plugins directory and add add external to all plugins added by user
		$pluginsPath = '';

		foreach(glob(dirname(__FILE__) . "/plugins/*", GLOB_ONLYDIR) AS $dir)
		{
			if($this->params->get('LinkBrowser', 1) == 0 && basename($dir) == "linkBrowser")
			{
				continue;
			}

			$this->pluginsName[] = basename($dir);
			$pluginsPath         .= "\n\t\tCKEDITOR.plugins.addExternal('" . basename($dir) . "', '../plugins/" . basename($dir) . "/');";
		}

		$load .= $pluginsPath . "\n\t</script>";

		return $load;
	}

	function onGetContent($editor)
	{
		return " CKEDITOR.instances.$editor.getData(); ";
	}

	function onSetContent($editor, $html)
	{
		return " CKEDITOR.instances.$editor.setData($html); ";
	}

	function onDisplay($name, $content, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null)
	{
		$app = JFactory::getApplication();

		if($app->getName() != 'site')
		{
			JHTML::_('behavior.modal', 'a.modal-button');;
		}

		setcookie('ckfinder_app', $app->getClientId(), time() + 60 * 60 * 24, '/');

		$session = JFactory::getSession();
		$user    = JFactory::getUser();
		$db      = JFactory::getDBO();

		$db->setQuery('SELECT template FROM #__template_styles WHERE home = 1 AND client_id=' . $app->getClientId());
		$templateName = $db->loadResult();

		if($width)
		{
			$_width = 'width:' . $width . ';';
		}

		if($height)
		{
			$_height = 'height:' . $height . ';';
		}

		$editor = '<textarea name="' . $name . '" id="' . $id . '" cols="' . $col . '" rows="' . $row . '" style="' . $_width . $_height . '">' . $content . '</textarea>';

		$userid = $user->get('id');
		$gid    = JAccess::getGroupsByUser($userid);
		$access = $this->params->get('usergroup', array( '8' ));

		$access_true = false;
		if(is_array($access) && is_array($gid))
		{
			foreach($gid AS $key => $val)
			{
				if(in_array($val, $access))
				{
					$access_true = true;

					break;
				}
			}
		}
		else
		{
			if(is_array($gid) && in_array($access, $gid))
			{
				$access_true = true;
			}
		}

		$frontend = '';
		if(!strpos(JPATH_BASE, 'administrator') && !$access_true)
		{
			$frontend = '_frontEnd';
		}

		if($this->params->get('CKEditorAutoLang', 0) == 0)
		{
			$language = "language: '" . $this->params->get('language', 'en') . "',";
		}
		else
		{
			$language = "defaultLanguage: '" . $this->params->get('language', 'en') . "',";
		}

		if($this->params->get('CKEditorLangDir', 0) == 1)
		{
			$txtDirection = "contentsLangDirection: 'rtl',";
		}
		else
		{
			$txtDirection = "contentsLangDirection: 'ltr',";
		}

		if($this->params->get('Scayt', 0) == 0)
		{
			$scayt = "scayt_autoStartup: false,";
		}
		else
		{
			$scayt = "scayt_autoStartup: true,";
		}

		if($this->params->get('Entities', 1) == 0)
		{
			$entities = "entities: false,";
		}
		else
		{
			$entities = "entities: true,";
		}

		$autogrow = '';
		if($this->params->get('CKEditorAutoGrow', 0) == 1)
		{
			$autogrow = ',autogrow';
		}

		$tableresize = '';
		if($this->params->get('CKEditorTableResize', 0) == 1)
		{
			$tableresize = ',tableresize';
		}

		$divarea = '';
		if($this->params->get('DivBased', 0) == 1)
		{
			$divarea = ',divarea';
		}

		$string              = $this->params->get('CKEditorCustomJs', '');
		$reg                 = "#/\*.+\*/#Us";
		$string              = preg_replace($reg, '', $string);
		$customConfigPlugins = preg_match('/CKEDITOR.config.extraPlugins = [\'|"](.+)[\'|"]/i', $string, $matches);
		$customConfigPlugins = '';

		if(isset($matches[ 1 ]))
		{
			$customConfigPlugins = ',' . $matches[ 1 ];
			$string              = preg_replace('/CKEDITOR.config.extraPlugins = [\'|"](.+)[\'|"]/i', '', $string);
			$this->params->set('CKEditorCustomJs', $string);
		}

		$skin = $this->params->get('skin', 'moono');
		if($skin == 'v2' || $skin == 'office2003')
		{
			$skin = 'moono';
		}

		$editor .= "<script>

        var editor_name = '" . $name . "';
        var attrstyle = document.getElementById('" . $id . "');
        var editorheight = (attrstyle.clientHeight+2);

		CKEDITOR.replace('" . $name . "',
		{
			resize_minWidth: '200',
			skin: '" . $skin . "',
			" . $language . "
			" . $txtDirection . "
			" . $scayt . "
			" . $entities . "\n\t\t\textraPlugins: \"" . implode(',', $this->pluginsName) . $autogrow . $tableresize . $divarea . $customConfigPlugins . "\",
			customConfig: '../config.js',
			enterMode: " . $this->params->get('enterMode', '1') . ",
			shiftEnterMode: " . $this->params->get('shiftEnterMode', '2') . "
		";

		if($this->params->get('CKEditorWidth', '') > 0 && $this->params->get('CKEditorWidth', '') != '100%')
		{
			$editor .= ", width: '" . $this->params->get('CKEditorWidth', '') . "'";
		}

		if($this->params->get('CKEditorHeight', '') > 0)
		{
			$editor .= ", height: '" . $this->params->get('CKEditorHeight', '') . "'";
			$editor .= ", autoGrow_maxHeight: '" . $this->params->get('CKEditorHeight', '') . "'";
		}
		else
		{
			if($height)
			{
				$editor .= ", height: '" . $height . "'";
			}
			else
			{
				$editor .= ", height: editorheight";
			}
		}

		if($this->params->get('Color', '') != '')
		{
			$editor .= ", uiColor: '" . $this->params->get('Color', '') . "'";
		}
		// ACF settings
		if($this->params->get('ACF', 0) != 1)
		{
			$editor .= ", allowedContent: true";
		}

		//array with all elements added in toolbar
		$allElements = array();
		if($this->params->get('toolbar' . $frontend, 'Full') != 'Full')
		{
			$toolbar = $this->params->get($this->params->get('toolbar' . $frontend, 'Full') . '_ToolBar', " 'Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink'");

			if(strpos($toolbar, '[') !== false || strpos($toolbar, ']') !== false)
			{
				$toolbar = str_replace(array(
					"[[",
					"]]",
					"[",
					"]"
				), array(
					"[",
					"]",
					"",
					";"
				), $toolbar);
			}

			$replace = array(
				"'",
				"`",
				'"',
				"\n"
			);

			$replacement = array(
				'',
				'',
				'',
				''
			);

			$toolbar = str_replace($replace, $replacement, $toolbar);

			$tmp = '';
			foreach(explode(',/,', $toolbar) AS $key => $line)
			{
				if($line)
				{
					if($key > 0)
					{
						$tmp .= ",/," . $line;
					}
					else
					{
						$tmp .= $line;
					}
				}
			}

			$toolbar = $tmp;

			$data = '';

			$toolbar = str_replace(array(
				'/,;,/',
				',;,/'
			), array(
				'/',
				',/'
			), $toolbar);

			foreach(explode(';', $toolbar) AS $menu)
			{
				if($menu != '')
				{
					$data     .= "[";
					$tmpArray = array();
					foreach(explode(',', $menu) AS $key => $value)
					{
						$allElements[] = trim($value);
						if($value != '' && trim($value) != '/')
						{
							$tmpArray[] = "'" . trim($value) . "'";
						}
						elseif(trim($value) == '/')
						{
							//special case for "/" character
							//if "/" character is first in array -> move it before "[" character
							if($data[ strlen($data) - 1 ] == "[" && $key < 2)
							{
								$data[ strlen($data) - 1 ] = ']';
								$data                      .= ",'" . trim($value) . "',[";
							}
							else
							{
								$tmpArray[] = "],'" . trim($value) . "',[";
							}
						}
					}

					$data .= implode(',', $tmpArray);

					//correct data formating -> change ',]'' to ']' and '[,' to '['
					$data = preg_replace(array(
						"#,[^'\[]#",
						"#\[,#"
					), array(
						']',
						'['
					), $data);

					$data .= "],";
				}
			}

			//strip last ',' charcter
			$data[ strlen($data) - 1 ] = ' ';
		}

		$style      = false;
		$style_file = trim($this->params->get('style', ''));

		//set style
		if($style_file != '')
		{
			if(file_exists(dirname(__FILE__) . '/styles/' . $style_file))
			{
				$editor .= ",stylesCombo_stylesSet: 'default:" . JURI::root() . "plugins/editors/ckeditor/styles/" . $style_file . "'";
				$editor .= ",stylesSet: 'default:" . JURI::root() . "plugins/editors/ckeditor/styles/" . $style_file . "'";
				$style  = true;
			}
		}

		$template      = false;
		$template_file = trim($this->params->get('template', ''));

		//set template
		if($template_file != '')
		{
			if(file_exists(dirname(__FILE__) . "/templates/" . $template_file))
			{
				$editor   .= ",templates_files: ['" . JURI::root() . "/plugins/editors/ckeditor/templates/" . $template_file . "']";
				$editor   .= ",templates: 'default'";
				$template = true;
			}
		}

		//add toolbar to editor
		//add Styles and Templates button only if they aren't defined in toolbar by user and required files exists
		if(isset($data))
		{
			$editor .= ",toolbar :[{$data}]";
		}

		//set css files
		$css       = '';
		$css_files = trim($this->params->get('css', ''));

		if($css_files != '')
		{
			foreach(explode(';', $css_files) AS $file)
			{
				if(file_exists(dirname(__FILE__) . '/css/' . trim($file)))
				{
					$css .= ", '" . JURI::root() . "plugins/editors/ckeditor/css/" . trim($file) . "'";
				}
			}
		}

		if($this->params->get('templateCss', 0) == 1 && $templateName != null)
		{
			if($app->getClientId() == 1)
			{
				if(file_exists(JPATH_BASE . "/templates/$templateName/css/editor.css"))
				{
					$css .= ", '" . JURI::root() . "administrator/templates/$templateName/css/editor.css$vcssjs'";
				}
				else
				{
					$css .= ", '" . JURI::root() . "administrator/templates/$templateName/css/template.css$vcssjs'";
				}
			}
			else
			{
				if(file_exists(JPATH_BASE . "/templates/$templateName/css/editor.css"))
				{
					$css .= ", '" . JURI::root() . "templates/$templateName/css/editor.css$vcssjs'";
				}
				else
				{
					$css .= ", '" . JURI::root() . "templates/$templateName/css/template.css$vcssjs'";
				}
			}
		}

		if($css != '')
		{
			$editor .= ",contentsCss:  [ '" . JURI::root() . "plugins/editors/ckeditor/ckeditor/contents.css' {$css} ]";
		}

		if($this->params->get('ckfinder', '1') == 1)
		{
			$userid = $user->get('id');
			$gid    = JAccess::getGroupsByUser($userid);
			$access = $this->params->get('username_access', array( '8' ));

			$access_true   = false;
			$sessionActive = false;
			$session->set('CKFinderAccess', false); //default false - user can't use CKFinder

			if($this->params->get('CKFinderPathType', 0) == 1)
			{
				$prefix = '';
			}
			else
			{
				$prefix = JURI::root();
			}

			if(is_array($access) && is_array($gid))
			{
				foreach($gid AS $key => $val)
				{
					if(in_array($val, $access))
					{
						$access_true = true;
						break;
					}
				}
			}
			else
			{
				if(is_array($gid) && in_array($access, $gid))
				{
					$access_true = true;
				}
			}

			if($access_true)
			{
				if($session->getState() == 'active')
				{
					$sessionActive = true;
					$session->set('LicenseName', $this->params->get('CKFinderLicenseName', ''));
					$session->set('LicenseKey', $this->params->get('CKFinderLicenseKey', ''));
					$session->set('CKFinderAccess', true); //user can use CKFinder

					//set used sessions variables to default values
					$session->set('CKFinderMaxFilesSize', null);
					$session->set('CKFinderMaxFlashSize', null);
					$session->set('CKFinderMaxImagesSize', null);
					$session->set('CKFinderResourceFiles', null);
					$session->set('CKFinderResourceImages', null);
					$session->set('CKFinderResourceFlash', null);
					$session->set('CKFinderMaxImageWidth', null);
					$session->set('CKFinderMaxImageHeight', null);
					$session->set('CKFinderMaxThumbnailWidth', null);
					$session->set('CKFinderMaxThumbnailHeight', null);
					$session->set('CKFinderSettingsPlugins', null);
				}
			}

			// if user can use CKFinder  display button
			if($access_true && $session->get('CKFinderAccess') && $this->params->get('ckfinder', '0') != 0)
			{
				if(file_exists(JPATH_BASE . "/../plugins//editors/ckeditor/ckfinder/ckfinder/ckfinder.php"))
				{
					$ckfinder_path = "plugins/editors/ckeditor/ckfinder/ckfinder/";
				}
				else
				{
					$ckfinder_path = "plugins/editors/ckeditor/ckfinder/";
				}

				$editor .= ",filebrowserBrowseUrl: '" . JURI::root() . $ckfinder_path . "ckfinder.html',
					filebrowserImageBrowseUrl: '" . JURI::root() . $ckfinder_path . "ckfinder.html?Type=Images',
					filebrowserFlashBrowseUrl: '" . JURI::root() . $ckfinder_path . "ckfinder.html?Type=Flash',
					filebrowserUploadUrl: '" . JURI::root() . $ckfinder_path . "core/connector/php/connector.php?command=QuickUpload&type=Files',
					filebrowserImageUploadUrl: '" . JURI::root() . $ckfinder_path . "core/connector/php/connector.php?command=QuickUpload&type=Images',
					filebrowserFlashUploadUrl: '" . JURI::root() . $ckfinder_path . "core/connector/php/connector.php?command=QuickUpload&type=Flash'";

				if(!defined('CKFINDER_PATH_BASE'))
				{
					define('CKFINDER_PATH_BASE', str_replace(DS . 'administrator', '', JPATH_BASE));
				}

				//configure save path for images
				//crete necessary folders if they don't exists
				$saveDir = $this->params->get('CKFinderSaveImages', 'media/ckfinder/images');
				$saveDir = str_replace('/', DS, $saveDir);
				$saveDir = str_replace(array(
					'$id',
					'$username'
				), array(
					$user->id,
					$user->username
				), $saveDir);

				$session->set('CKFinderImagesPath', CKFINDER_PATH_BASE . DS . str_replace('/', DS, $saveDir) . DS);
				$session->set('CKFinderImagesUrl', $prefix . str_replace('\\', '/', trim($saveDir, '/')) . '/');

				$chmod = octdec(trim($this->params->get('CKFinderSettingsChmod', '0755')));
				$old   = umask(0);

				if($saveDir != 'media/ckfinder/images' && $saveDir != '')
				{
					$dirs = explode(DS, $saveDir);
					$path = CKFINDER_PATH_BASE;
					foreach($dirs AS $dir)
					{
						$path = $path . DS . $dir;
						if(!is_dir($path))
						{
							if(!mkdir($path, $chmod, $chmod))
							{
								JError::raiseError(500, "Creating " . $path . ' failed');
							}
						}
					}
				}
				else
				{
					$saveDir = CKFINDER_PATH_BASE . DS . 'media' . DS . 'ckfinder' . DS . 'images';
					$saveDir = str_replace('/', DS, $saveDir);
					if(!is_dir(CKFINDER_PATH_BASE . DS . 'media' . DS . 'ckfinder'))
					{
						if(!mkdir(CKFINDER_PATH_BASE . DS . 'media' . DS . 'ckfinder', $chmod))
						{
							JError::raiseError(500, "Creating " . CKFINDER_PATH_BASE . DS . 'media' . DS . 'ckfinder failed');
						}
					}

					if(!is_dir($saveDir))
					{
						if(!mkdir($saveDir, $chmod))
						{
							JError::raiseError(500, "Creating " . $saveDir . ' failed');
						}
					}
				}

				//configure save path for flash files
				//crete necessary folders if they don't exists
				$saveDir = $this->params->get('CKFinderSaveFlash', 'media' . DS . 'ckfinder' . DS . 'flash');
				$saveDir = str_replace('/', DS, $saveDir);
				$saveDir = str_replace(array(
					'$id',
					'$username'
				), array(
					$user->id,
					$user->username
				), $saveDir);
				$session->set('CKFinderFlashPath', CKFINDER_PATH_BASE . DS . str_replace('/', DS, $saveDir) . DS);
				$session->set('CKFinderFlashUrl', $prefix . str_replace('\\', '/', trim($saveDir, '/')) . '/');

				if($saveDir != 'media' . DS . 'ckfinder' . DS . 'flash' && $saveDir != '')
				{
					$dirs = explode(DS, $saveDir);
					$path = CKFINDER_PATH_BASE;
					foreach($dirs AS $dir)
					{
						$path = $path . DS . $dir;
						if(!is_dir($path))
						{
							if(!mkdir($path, $chmod))
							{
								JError::raiseError(500, "Creating " . $path . ' failed');
							}
						}
					}
				}
				else
				{
					$saveDir = CKFINDER_PATH_BASE . DS . 'media' . DS . 'ckfinder' . DS . 'flash';
					$saveDir = str_replace('/', DS, $saveDir);
					if(!is_dir(CKFINDER_PATH_BASE . DS . 'media' . DS . 'ckfinder'))
					{
						if(!mkdir(CKFINDER_PATH_BASE . DS . 'media' . DS . 'ckfinder', $chmod))
							JError::raiseError(500, "Creating " . CKFINDER_PATH_BASE . DS . 'media' . DS . 'ckfinder failed');
					}

					if(!is_dir($saveDir))
					{
						if(!mkdir($saveDir, $chmod))
							JError::raiseError(500, "Creating " . $saveDir . ' failed');
					}
				}

				//configure save path for files
				//crete necessary folders if they don't exists
				$saveDir = $this->params->get('CKFinderSaveFiles', 'media' . DS . 'ckfinder' . DS . 'files');
				$saveDir = str_replace('/', DS, $saveDir);
				$saveDir = str_replace(array(
					'$id',
					'$username'
				), array(
					$user->id,
					$user->username
				), $saveDir);
				$session->set('CKFinderFilesPath', CKFINDER_PATH_BASE . DS . str_replace('/', DS, $saveDir) . DS);
				$session->set('CKFinderFilesUrl', $prefix . str_replace('\\', '/', trim($saveDir, '/')) . '/');

				if($saveDir != 'media' . DS . 'ckfinder' . DS . 'files' && $saveDir != '')
				{
					$dirs = explode(DS, $saveDir);
					$path = CKFINDER_PATH_BASE;
					foreach($dirs AS $dir)
					{
						$path = $path . DS . $dir;
						if(!is_dir($path))
						{
							if(!mkdir($path, $chmod))
								JError::raiseError(500, "Creating " . $path . ' failed');
						}
					}
				}
				else
				{
					$saveDir = CKFINDER_PATH_BASE . DS . 'media' . DS . 'ckfinder' . DS . 'files';
					$saveDir = str_replace('/', DS, $saveDir);
					if(!is_dir(CKFINDER_PATH_BASE . DS . 'media' . DS . 'ckfinder'))
					{
						if(!mkdir(CKFINDER_PATH_BASE . DS . 'media' . DS . 'ckfinder', $chmod))
							JError::raiseError(500, "Creating " . CKFINDER_PATH_BASE . DS . 'media' . DS . 'ckfinder failed');
					}

					if(!is_dir($saveDir))
					{
						if(!mkdir($saveDir, $chmod))
							JError::raiseError(500, "Creating " . $saveDir . ' failed');
					}
				}

				//configure save path for thumbnails
				//crete necessary folders if they don't exists
				$saveDir = $this->params->get('CKFinderSaveThumbs', 'media' . DS . 'ckfinder' . DS . '_thumbs');
				$saveDir = str_replace('/', DS, $saveDir);
				$saveDir = str_replace(array(
					'$id',
					'$username'
				), array(
					$user->id,
					$user->username
				), $saveDir);
				$session->set('CKFinderThumbsPath', CKFINDER_PATH_BASE . DS . str_replace('/', DS, $saveDir) . DS);
				$session->set('CKFinderThumbsUrl', $prefix . str_replace('\\', '/', trim($saveDir, '/')) . '/');

				if($saveDir != 'media' . DS . 'ckfinder' . DS . '_thumbs' && $saveDir != '')
				{
					$dirs = explode(DS, $saveDir);
					$path = CKFINDER_PATH_BASE;
					foreach($dirs AS $dir)
					{
						$path = $path . DS . $dir;
						if(!is_dir($path))
						{
							if(!mkdir($path, $chmod))
								JError::raiseError(500, "Creating " . $path . ' failed');
						}
					}
				}
				else
				{
					$saveDir = CKFINDER_PATH_BASE . DS . 'media' . DS . 'ckfinder' . DS . '_thumbs';
					$saveDir = str_replace('/', DS, $saveDir);
					if(!is_dir(CKFINDER_PATH_BASE . DS . 'media' . DS . 'ckfinder'))
					{
						if(!mkdir(CKFINDER_PATH_BASE . DS . 'media' . DS . 'ckfinder', $chmod))
							JError::raiseError(500, "Creating " . CKFINDER_PATH_BASE . DS . 'media' . DS . 'ckfinder failed');
					}

					if(!is_dir($saveDir))
					{
						if(!mkdir($saveDir, $chmod))
							JError::raiseError(500, "Creating " . $saveDir . ' failed');
					}
				}

				//return old umask settings
				umask($old);

				//settings for Resource Files
				if($this->params->get('CKFinderResourceFiles', ''))
				{
					$extensions = explode(',', $this->params->get('CKFinderResourceFiles', ''));
					$extensions = array_unique($extensions);
					$results    = array();
					foreach($extensions AS $extension)
					{
						if($extension)
							$results[] = $extension;
					}
					$session->set('CKFinderResourceFiles', implode(',', $results));
				}

				//settings for Resource Images
				if($this->params->get('CKFinderResourceImages', ''))
				{
					$extensions = explode(',', $this->params->get('CKFinderResourceImages', ''));
					$extensions = array_unique($extensions);
					$results    = array();
					foreach($extensions AS $extension)
					{
						if($extension)
							$results[] = $extension;
					}
					$session->set('CKFinderResourceImages', implode(',', $results));
				}

				//settings for Resource Flash
				if($this->params->get('CKFinderResourceFlash', ''))
				{
					$extensions = explode(',', $this->params->get('CKFinderResourceFlash', ''));
					$extensions = array_unique($extensions);
					$results    = array();
					foreach($extensions AS $extension)
					{
						if($extension)
							$results[] = $extension;
					}
					$session->set('CKFinderResourceFlash', implode(',', $results));
				}

				//set max Flash files size
				if($this->params->get('CKFinderMaxFlashSize', ''))
				{
					$session->set('CKFinderMaxFlashSize', $this->params->get('CKFinderMaxFlashSize'));
				}

				//set max Images files size
				if($this->params->get('CKFinderMaxImagesSize', ''))
				{
					$session->set('CKFinderMaxImagesSize', $this->params->get('CKFinderMaxImagesSize'));
				}

				//set max Files files size
				if($this->params->get('CKFinderMaxFilesSize', ''))
				{
					$session->set('CKFinderMaxFilesSize', $this->params->get('CKFinderMaxFilesSize'));
				}

				//set max image width
				if((int) $this->params->get('CKFinderMaxImageWidth', 0))
				{
					$session->set('CKFinderMaxImageWidth', (int) $this->params->get('CKFinderMaxImageWidth', 0));
				}

				//set max image height
				if((int) $this->params->get('CKFinderMaxImageHeight', 0))
				{
					$session->set('CKFinderMaxImageHeight', (int) $this->params->get('CKFinderMaxImageHeight', 0));
				}

				//set max thumbnail width
				if((int) $this->params->get('CKFinderMaxThumbnailWidth', 0))
				{
					$session->set('CKFinderMaxThumbnailWidth', (int) $this->params->get('CKFinderMaxThumbnailWidth', 0));
				}

				//set max thumbnail height
				if((int) $this->params->get('CKFinderMaxThumbnailHeight', 0))
				{
					$session->set('CKFinderMaxThumbnailHeight', (int) $this->params->get('CKFinderMaxThumbnailHeight', 0));
				}

				//plugins settings
				$plugins = array(
					'imageresize' => $this->params->get('CKFinderImageResize', 1),
					'fileedit'    => $this->params->get('CKFinderFileEdit', 1),
					'zip'         => $this->params->get('CKFinderZip', 1),
				);
				$session->set('CKFinderSettingsPlugins', $plugins);

			} //end CKFinder config
		}

		$editor .= "});";

		//add custom javascript config from user,  very danger option
		$string = $this->params->get('CKEditorCustomJs', '');
		$reg    = "#/\*.+\*/#Us";
		$string = preg_replace($reg, '', $string);
		$editor .= $string;

		$instanceReady = $this->CKEditorInstance();

		$editor .= $instanceReady . "</script>";
		$editor .= $this->_displayButtons($id, $buttons, $asset, $author);

		return $editor;
	}

	function CKEditorInstance()
	{
		$txt = "
    	CKEDITOR.on( 'instanceReady', function( ev )
    	{
    		var formater = [];
    		formater['indent'] = " . $this->params->get('CKEditorIndent', 1) . ";
    		formater['breakBeforeOpen'] = " . $this->params->get('CKEditorBreakBeforeOpener', 1) . ";
    		formater['breakAfterOpen'] = " . $this->params->get('CKEditorBreakAfterOpener', 1) . ";
    		formater['breakBeforeClose'] = " . $this->params->get('CKEditorBreakBeforeCloser', 0) . ";
    		formater['breakAfterClose'] = " . $this->params->get('CKEditorBreakAfterCloser', 1) . ";
    		var pre_formater = " . $this->params->get('CKEditorPre', 0) . ";
    		var dtd = CKEDITOR.dtd;
    		for ( var e in CKEDITOR.tools.extend( {}, dtd.\$nonBodyContent, dtd.\$block, dtd.\$listItem, dtd.\$tableContent ) ) {
    			ev.editor.dataProcessor.writer.setRules( e, formater);
    		}
    		ev.editor.dataProcessor.writer.setRules( 'pre',	{
    			indent: pre_formater
    		});
    	});
        ";

		return $txt;
	}

	/*function returns instanceReady event from CKEditor
	 * @return variable with javascript code , define CKEDitor instanceReady event
	 */

	/**
	 * Displays the editor buttons.
	 *
	 * @param   string $name    The editor name
	 * @param   mixed  $buttons [array with button objects | boolean true to display buttons]
	 * @param   string $asset   The object asset
	 * @param   object $author  The author.
	 *
	 * @return  string HTML
	 */
	private function _displayButtons($name, $buttons, $asset, $author)
	{
		$return = '';

		$args = array(
			'name'  => $name,
			'event' => 'onGetInsertMethod'
		);

		$results = (array) $this->update($args);

		if($results)
		{
			foreach($results as $result)
			{
				if(is_string($result) && trim($result))
					$return .= $result;
			}
		}

		if(is_array($buttons) || (is_bool($buttons) && $buttons))
		{
			$buttons = $this->_subject->getButtons($name, $buttons, $asset, $author);
			$return  .= JLayoutHelper::render('joomla.editors.buttons', $buttons);
			$return  = '<div style="clear:both;">' . $return . '</div>';
		}

		return $return;
	}

	/**
	 * @param $name
	 *
	 * @return bool
	 *
	 * @since version
	 */
	function onGetInsertMethod($name)
	{
		$document = JFactory::getDocument();
		$url      = str_replace('administrator/', '', JURI::base());
		$js       = "function jInsertEditorText( text,editor ) {text = text.replace( /<img src=\"/, '<img src=\"" . $url . "' ); CKEDITOR.instances[editor].insertHtml( text);}";

		if(!$this->called)
		{
			$document->addScriptDeclaration($js);
			$this->called = true;
		}

		return true;
	}

	/**
	 * @param $name
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	private function _toogleButton($name)
	{
		return JLayoutHelper::render('joomla.tinymce.togglebutton', $name);
	}
}