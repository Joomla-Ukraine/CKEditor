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
* @copyright Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
* @license	 GNU/GPL
* CKEditor extension is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*/

defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

class plgEditorCKeditor extends CMSPlugin
{
	protected $pluginsName = [];

	protected $called = false;

	protected $buildVersion;

	/**
	 * plgEditorCKeditor constructor.
	 *
	 * @param $subject
	 * @param $config
	 *
	 * @since 5.0
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$this->app     = Factory::getApplication();
		$this->config  = Factory::getConfig();
		$this->session = Factory::getSession();
		$this->user    = Factory::getUser();
		$this->db      = Factory::getDBO();

		$this->language      = Factory::getLanguage();
		$this->language->load('com_ckeditor', JPATH_ADMINISTRATOR, 'en-GB', true);

		$this->buildVersion = '?v=@version@-' . date('YmdH');
	}

	/**
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	public function onInit()
	{
		$load = "<script>window.CKEDITOR_BASEPATH='" . Uri::root() . "plugins/editors/ckeditor/ckeditor/';</script>";

		if($this->params->get('CKEditorJs', 0) == 1 && is_dir('../plugins/editors/ckeditor/ckeditor/_source/') && file_exists('../plugins/editors/ckeditor/ckeditor/ckeditor_source.js'))
		{
			$load .= '<script src="' . Uri::root(true) . '/plugins/editors/ckeditor/ckeditor/ckeditor_source.js' . $this->buildVersion . '"></script>';
		}
		else
		{
			$load .= '<script src="' . Uri::root(true) . '/plugins/editors/ckeditor/ckeditor/ckeditor.js' . $this->buildVersion . '"></script>';
		}

		//set base href to works with default joomla editor
		$load .= '<script>';
		$load .= "CKEDITOR.config.baseHref = '" . Uri::root() . "';";

		if($this->params->get('LinkBrowserUrl', 1) == 0)
		{
			$load .= "\nvar linkBrowserUrl = 'relative';";
		}
		else
		{
			$load .= "\nvar linkBrowserUrl = 'absolute';";
		}

		//iterate in ckeditor/plugins directory and add add external to all plugins added by user
		$pluginsPath = '';
		foreach(glob(__DIR__ . '/plugins/*', GLOB_ONLYDIR) AS $dir)
		{
			$this->pluginsName[] = basename($dir);
			$pluginsPath         .= "\nCKEDITOR.plugins.addExternal('" . basename($dir) . "', '../plugins/" . basename($dir) . "/');";
		}

		$load .= $pluginsPath . '</script>';

		return $load;
	}

	/**
	 * @param $editor
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	public function onGetContent($editor)
	{
		return " CKEDITOR.instances.$editor.getData(); ";
	}

	/**
	 * @param $editor
	 * @param $html
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	public function onSetContent($editor, $html)
	{
		return " CKEDITOR.instances.$editor.setData($html); ";
	}

	/**
	 * @param      $name
	 * @param      $content
	 * @param      $width
	 * @param      $height
	 * @param      $col
	 * @param      $row
	 * @param bool $buttons
	 * @param null $id
	 * @param null $asset
	 * @param null $author
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	public function onDisplay($name, $content, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null)
	{
		if($this->app->getName() !== 'site')
		{
			HTMLHelper::_('behavior.modal', 'a.modal-button');
		}

		setcookie('ckfinder_app', $this->app->getClientId(), strtotime('+' . $this->config->get('lifetime') . ' minutes'), '/');

		$this->db->setQuery('SELECT template FROM #__template_styles WHERE home = 1 AND client_id=' . $this->app->getClientId());
		$templateName = $this->db->loadResult();

		$_width = '';
		if($width)
		{
			$_width = 'width:' . $width . ';';
		}

		$_height = '';
		if($height)
		{
			$_height = 'height:' . $height . ';';
		}

		$editor = '<textarea name="' . $name . '" id="' . $id . '" cols="' . $col . '" rows="' . $row . '"' . ($_width ? ' style="' . $_width . $_height . '"' : '') . '>' . $content . '</textarea>';

		$userid = $this->user->get('id');
		$gid    = Access::getGroupsByUser($userid);
		$access = $this->params->get('usergroup', [ '8' ]);

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
		elseif(is_array($gid) && in_array($access, $gid))
		{
			$access_true = true;
		}

		$frontend = '';
		if(!strpos(JPATH_BASE, 'administrator') && !$access_true)
		{
			$frontend = '_frontEnd';
		}

		$language = "defaultLanguage: '" . $this->params->get('language', 'en') . "',";
		if($this->params->get('CKEditorAutoLang', 0) == 0)
		{
			$language = "language: '" . $this->params->get('language', 'en') . "',";
		}

		$txtDirection = "contentsLangDirection: 'ltr',";
		if($this->params->get('CKEditorLangDir', 0) == 1)
		{
			$txtDirection = "contentsLangDirection: 'rtl',";
		}

		$scayt = 'scayt_autoStartup: true,';
		if($this->params->get('Scayt', 0) == 0)
		{
			$scayt = 'scayt_autoStartup: false,';
		}

		$entities = 'entities: true,';
		if($this->params->get('Entities', 1) == 0)
		{
			$entities = 'entities: false,';
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

		$customConfigPlugins = '';
		$skin                = $this->params->get('skin', 'moono');
		if($skin === 'v2' || $skin === 'office2003')
		{
			$skin = 'moono';
		}

		$editor .= "<script>
        var editor_name = '" . $name . "',
        attrstyle = document.getElementById('" . $id . "'),
        editorheight = (attrstyle.clientHeight+2);

		CKEDITOR.replace('" . $name . "', {
			resize_minWidth: '200',
			skin: '" . $skin . "',
			" . $language . '
			' . $txtDirection . '
			' . $scayt . '
			' . $entities . "\nextraPlugins: \"" . implode(',', $this->pluginsName) . $autogrow . $tableresize . $divarea . $customConfigPlugins . "\",
			customConfig: '../config.js',
			enterMode: " . $this->params->get('enterMode', '1') . ',
			shiftEnterMode: ' . $this->params->get('shiftEnterMode', '2') . '
		';

		if($this->params->get('CKEditorWidth', '') > 0 && $this->params->get('CKEditorWidth', '') !== '100%')
		{
			$editor .= ", width: '" . $this->params->get('CKEditorWidth', '') . "'";
		}

		if($this->params->get('CKEditorHeight', '') > 0)
		{
			$editor .= ", height: '" . $this->params->get('CKEditorHeight', '') . "'";
			$editor .= ", autoGrow_maxHeight: '" . $this->params->get('CKEditorHeight', '') . "'";
		}

		if($height)
		{
			$editor .= ", height: '" . $height . "'";
		}
		else
		{
			$editor .= ', height: editorheight';
		}

		if($this->params->get('Color', '') !== '')
		{
			$editor .= ", uiColor: '" . $this->params->get('Color', '') . "'";
		}

		// ACF settings
		if($this->params->get('ACF', 0) != 1)
		{
			$editor .= ', allowedContent: true';
		}

		//array with all elements added in toolbar
		$allElements = [];
		if($this->params->get('toolbar' . $frontend, 'Full') !== 'Full')
		{
			$toolbar = $this->params->get($this->params->get('toolbar' . $frontend, 'Full') . '_ToolBar', " 'Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink'");

			if(strpos($toolbar, '[') !== false || strpos($toolbar, ']') !== false)
			{
				$toolbar = str_replace([
					'[[',
					']]',
					'[',
					']'
				], [
					'[',
					']',
					'',
					';'
				], $toolbar);
			}

			$replace = [
				"'",
				'`',
				'"',
				"\n"
			];

			$replacement = [
				'',
				'',
				'',
				''
			];

			$toolbar = str_replace($replace, $replacement, $toolbar);

			$tmp = '';
			foreach(explode(',/,', $toolbar) AS $key => $line)
			{
				if($line)
				{
					if($key > 0)
					{
						$tmp .= ',/,' . $line;
					}
					else
					{
						$tmp .= $line;
					}
				}
			}

			$toolbar = $tmp;

			$data = '';

			$toolbar = str_replace([
				'/,;,/',
				',;,/'
			], [
				'/',
				',/'
			], $toolbar);

			foreach(explode(';', $toolbar) AS $menu)
			{
				if($menu !== '')
				{
					$data .= '[';

					$tmpArray = [];
					foreach(explode(',', $menu) AS $key => $value)
					{
						$allElements[] = trim($value);
						if($value !== '' && trim($value) !== '/')
						{
							$tmpArray[] = "'" . trim($value) . "'";
						}
						elseif(trim($value) === '/')
						{
							if($data[ strlen($data) - 1 ] === '[' && $key < 2)
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
					$data = preg_replace([
						"#,[^'\[]#",
						"#\[,#"
					], [
						']',
						'['
					], $data);

					$data .= '],';
				}
			}

			$data[ strlen($data) - 1 ] = ' ';
		}

		$style      = false;
		$style_file = trim($this->params->get('style', ''));

		//set style
		if($style_file !== '' && file_exists(__DIR__ . '/styles/' . $style_file))
		{
			$editor .= ",stylesCombo_stylesSet: 'default:" . Uri::root() . 'plugins/editors/ckeditor/styles/' . $style_file . $this->buildVersion . "'";
			$editor .= ",stylesSet: 'default:" . Uri::root() . 'plugins/editors/ckeditor/styles/' . $style_file . $this->buildVersion . "'";
			$style  = true;
		}

		$template      = false;
		$template_file = trim($this->params->get('template', ''));

		//set template
		if($template_file !== '' && file_exists(__DIR__ . '/templates/' . $template_file))
		{
			$editor   .= ",templates_files: ['" . Uri::root() . '/plugins/editors/ckeditor/templates/' . $template_file . $this->buildVersion . "']";
			$editor   .= ",templates: 'default'";
			$template = true;
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
		if($css_files !== '')
		{
			foreach(explode(';', $css_files) AS $file)
			{
				if(file_exists(__DIR__ . '/css/' . trim($file)))
				{
					$css .= ", '" . Uri::root() . 'plugins/editors/ckeditor/css/' . trim($file) . $this->buildVersion . "'";
				}
			}
		}

		if($this->params->get('templateCss', 0) == 1 && $templateName != null)
		{
			if($this->app->getClientId() == 1)
			{
				if(file_exists(JPATH_BASE . "/templates/$templateName/css/editor.css"))
				{
					$css .= ", '" . Uri::root() . "administrator/templates/$templateName/css/editor.css" . $this->buildVersion . "'";
				}
				else
				{
					$css .= ", '" . Uri::root() . "administrator/templates/$templateName/css/template.css" . $this->buildVersion . "'";
				}
			}
			elseif(file_exists(JPATH_BASE . "/templates/$templateName/css/editor.css"))
			{
				$css .= ", '" . Uri::root() . "templates/$templateName/css/editor.css" . $this->buildVersion . "'";
			}
			else
			{
				$css .= ", '" . Uri::root() . "templates/$templateName/css/template.css" . $this->buildVersion . "'";
			}
		}

		if($css !== '')
		{
			$editor .= ",contentsCss:  [ '" . Uri::root() . "plugins/editors/ckeditor/ckeditor/contents.css' {$css} ]";
		}

		if($this->params->get('filemanagers', '0') > 0)
		{
			$userid      = $this->user->get('id');
			$gid         = Access::getGroupsByUser($userid);
			$access      = $this->params->get('username_access', [ '8' ]);
			$user_access = $this->params->get('user_access_folder', [ '2' ]);

			$access_true   = false;
			$sessionActive = false;
			$this->session->set('FilesAccess', false); //default false - user can't use CKFinder

			$prefix = Uri::base();
			$base_prefix = Uri::base();
			if($this->params->get('FilesPathType', 0) == 1)
			{
				$prefix = '';
				$base_prefix = '/';
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
			elseif(is_array($gid) && in_array($access, $gid))
			{
				$access_true = true;
			}

			$user_access_true = false;
			if(is_array($user_access) && is_array($gid))
			{
				foreach($gid AS $key => $val)
				{
					if(in_array($val, $user_access))
					{
						$user_access_true = true;

						break;
					}
				}
			}
			elseif(is_array($gid) && in_array($user_access, $gid))
			{
				$user_access_true = true;
			}

			if($access_true && $this->session->getState() === 'active')
			{
				$sessionActive = true;

				$this->session->set('FilesAccess', true);
				$this->session->set('FilesMaxFilesSize', null);
				$this->session->set('FilesMaxImagesSize', null);
				$this->session->set('FilesResourceFiles', null);
				$this->session->set('FilesResourceImages', null);
				$this->session->set('FilesMaxImageWidth', null);
				$this->session->set('FilesMaxImageHeight', null);
				$this->session->set('FilesMaxThumbnailWidth', null);
				$this->session->set('FilesMaxThumbnailHeight', null);
				$this->session->set('CKFinderSettingsPlugins', null);
			}
		}

		if($this->params->get('filemanagers', '0') == 1)
		{
			if($access_true && $this->session->getState() === 'active')
			{
				$this->session->set('LicenseName', $this->params->get('CKFinderLicenseName', ''));
				$this->session->set('LicenseKey', $this->params->get('CKFinderLicenseKey', ''));
			}

			// if user can use CKFinder  display button
			if($access_true && $this->session->get('FilesAccess'))
			{
				$ckfinder_path = Uri::root() . 'plugins/editors/ckeditor/filemanagers/ckfinder/';

				$editor .= ",filebrowserBrowseUrl: '" . $ckfinder_path . "ckfinder.html',
					filebrowserImageBrowseUrl: '" . $ckfinder_path . "ckfinder.html?Type=Images',
					filebrowserFlashBrowseUrl: '" . $ckfinder_path . "ckfinder.html?Type=Files',
					filebrowserUploadUrl: '" . $ckfinder_path . "core/connector/php/connector.php?command=QuickUpload&type=Files',
					filebrowserImageUploadUrl: '" . $ckfinder_path . "core/connector/php/connector.php?command=QuickUpload&type=Images',
					filebrowserFlashUploadUrl: '" . $ckfinder_path . "core/connector/php/connector.php?command=QuickUpload&type=Files'";

				if(!defined('CKFINDER_PATH_BASE'))
				{
					define('CKFINDER_PATH_BASE', str_replace('/administrator', '', JPATH_BASE));
				}

				$saveDir = $this->params->get('FilesSaveImages', 'images');
				$saveDir = $this->_userFolder($saveDir, $user_access_true);

				$this->session->set('FilesImagesPath', CKFINDER_PATH_BASE . '/' . $saveDir . '/');
				$this->session->set('FilesImagesUrl', $prefix . str_replace('\\', '/', trim($saveDir, '/')) . '/');

				$chmod = octdec(trim($this->params->get('FilesSettingsChmod', '0755')));
				$old   = umask(0);

				$this->_saveDir($saveDir, CKFINDER_PATH_BASE, 'images', $chmod);

				//configure save path for files
				$saveDir = $this->params->get('FilesSaveFiles', 'files');
				$saveDir = $this->_userFolder($saveDir, $user_access_true);

				$this->session->set('FilesFilesPath', CKFINDER_PATH_BASE . '/' . $saveDir . '/');
				$this->session->set('FilesFilesUrl', $prefix . str_replace('\\', '/', trim($saveDir, '/')) . '/');

				$this->_saveDir($saveDir, CKFINDER_PATH_BASE, 'files', $chmod);

				//configure save path for thumbnails
				$saveDir = $this->params->get('FilesSaveThumbs', 'cache/_thumbs');
				$saveDir = $this->_userFolder($saveDir, $user_access_true);

				$this->session->set('FilesThumbsPath', CKFINDER_PATH_BASE . '/' . $saveDir . '/');
				$this->session->set('FilesThumbsUrl', $prefix . str_replace('\\', '/', trim($saveDir, '/')) . '/');

				$this->_saveDir($saveDir, CKFINDER_PATH_BASE, 'cache/_thumbs', $chmod);

				//return old umask settings
				umask($old);

				$this->_resourceFiles($this->session, $this->params);
				$this->_resourceImages($this->session, $this->params);
				$this->_maxFilesSize($this->session, $this->params);
				$this->_maxImagesSize($this->session, $this->params);
				$this->_maxImageWidth($this->session, $this->params);
				$this->_maxImageHeight($this->session, $this->params);
				$this->_maxThumbnailHWidth($this->session, $this->params);
				$this->_maxThumbnailHeight($this->session, $this->params);

				//plugins settings
				$plugins = [
					'imageresize' => $this->params->get('CKFinderImageResize', 1),
					'fileedit'    => $this->params->get('CKFinderFileEdit', 1),
					'zip'         => $this->params->get('CKFinderZip', 1),
				];

				$this->session->set('CKFinderSettingsPlugins', $plugins);
			}
		}

		if($this->params->get('filemanagers', '0') == 2)
		{
			// if user can use Responsive filemanager  display button
			if($access_true && $this->session->get('FilesAccess'))
			{
				$ckfinder_path = Uri::root() . 'plugins/editors/ckeditor/filemanagers/responsivefilemanager/';

				$editor .= ",filebrowserBrowseUrl: '" . $ckfinder_path . "dialog.php?type=2&editor=ckeditor&fldr=',
				filebrowserImageBrowseUrl: '" . $ckfinder_path . "dialog.php?type=1&editor=ckeditor&fldr=',
				filebrowserFlashBrowseUrl: '" . $ckfinder_path . "dialog.php?type=2&editor=ckeditor&fldr=',
				filebrowserUploadUrl: '" . $ckfinder_path . "dialog.php?type=2&editor=ckeditor&fldr=',					
				filebrowserImageUploadUrl: '" . $ckfinder_path . "dialog.php?type=1&editor=ckeditor&fldr=',
				filebrowserFlashUploadUrl: '" . $ckfinder_path . "dialog.php?type=2&editor=ckeditor&fldr='";


				$prefix = Uri::base();
				$base_prefix = '';
				if($this->params->get('FilesPathType', 0) == 1)
				{
					$prefix = '';
					$base_prefix = '/';
				}

				$this->session->set('FilesBaseUrl', $base_prefix);
				$this->session->set('FilesLang', str_replace('-', '_', $this->language->getTag()));

				$saveDir = $this->params->get('FilesSaveImages', 'images');
				$saveDir = $this->_userFolder($saveDir, $user_access_true);

				$this->session->set('FilesImagesPath', '../../../../../' . $saveDir . '/');
				$this->session->set('FilesImagesUrl', $prefix . str_replace('\\', '/', trim($saveDir, '/')) . '/');

				$chmod = octdec(trim($this->params->get('FilesSettingsChmod', '0755')));
				$old   = umask(0);

				$this->_saveDir($saveDir, JPATH_BASE, 'images', $chmod);

				//configure save path for files
				$saveDir = $this->params->get('FilesSaveFiles', 'files');
				$saveDir = $this->_userFolder($saveDir, $user_access_true);

				$this->session->set('FilesFilesPath', '../../../../../' . $saveDir . '/');
				$this->session->set('FilesFilesUrl', $prefix . str_replace('\\', '/', trim($saveDir, '/')) . '/');

				$this->_saveDir($saveDir, JPATH_BASE, 'files', $chmod);

				//configure save path for thumbnails
				$saveDir = $this->params->get('FilesSaveThumbs', 'cache/_thumbs');
				$saveDir = $this->_userFolder($saveDir, $user_access_true);

				$this->session->set('FilesThumbsPath', $saveDir);
				$this->session->set('FilesThumbsUrl', $prefix . str_replace('\\', '/', trim($saveDir, '/')) . '/');

				$this->_saveDir($saveDir, JPATH_BASE, 'cache/_thumbs', $chmod);

				//return old umask settings
				umask($old);

				$this->_resourceFiles($this->session, $this->params);
				$this->_resourceImages($this->session, $this->params);
				$this->_maxFilesSize($this->session, $this->params);
				$this->_maxImagesSize($this->session, $this->params);
				$this->_maxImageWidth($this->session, $this->params);
				$this->_maxImageHeight($this->session, $this->params);
				$this->_maxThumbnailHWidth($this->session, $this->params);
				$this->_maxThumbnailHeight($this->session, $this->params);
			}
		}

		$editor .= '});';

		if($this->params->get('cssbodyclass') !== '')
		{
			$editor .= "
        CKEDITOR.config.bodyClass = '" . $this->params->get('cssbodyclass') . "';";
		}

		if($this->params->get('msword', 1) == 1)
		{
			$editor .= "
        CKEDITOR.config.pasteFromWordCleanupFile = '../filter/word.js';
        CKEDITOR.config.pasteFromWordPromptCleanup = true;
        CKEDITOR.config.pasteFromWordNumberedHeadingToList = true;
        CKEDITOR.config.pasteFromWordPromptCleanup = true;
        CKEDITOR.config.pasteFromWordRemoveFontStyles = true;";
		}

		if($this->params->get('plaintext', 1) == 1)
		{
			$editor .= '
        CKEDITOR.config.forcePasteAsPlainText = true;';
		}

		if($this->params->get('keystrokes', 1) == 1)
		{
			$editor .= "
        CKEDITOR.config.keystrokes =
        [
            [ CKEDITOR.CTRL + 81 /*Q*/, 'blockquote' ],
            [ CKEDITOR.CTRL + 66 /*B*/, 'bold' ],
            [ CKEDITOR.CTRL + 56 /*8*/, 'bulletedlist' ],
            [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 56 /*8*/, 'bulletedListStyle' ],

            [ CKEDITOR.CTRL + 50 /*2*/, 'heading-h2' ],
            [ CKEDITOR.CTRL + 51 /*3*/, 'heading-h3' ],
            [ CKEDITOR.CTRL + 52 /*4*/, 'heading-h4' ],
            [ CKEDITOR.CTRL + 53 /*5*/, 'heading-h5' ],
            [ CKEDITOR.CTRL + 54 /*6*/, 'heading-h6' ],
            [ CKEDITOR.CTRL + 77 /*M*/, 'indent' ],
            [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 77 /*M*/, 'outdent' ],
            [ CKEDITOR.CTRL + 73 /*I*/, 'italic' ],
            [ CKEDITOR.CTRL + 55 /*7*/, 'numberedlist' ],
            [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 55 /*7*/, 'numberedListStyle' ],
            [ CKEDITOR.CTRL + 89 /*Y*/, 'redo' ],
            [ CKEDITOR.CTRL + 32 /*SPACE*/, 'removeFormat' ],
            [ CKEDITOR.CTRL + 65 /*A*/, 'selectall' ],
            [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 88 /*X*/, 'strike' ],
            [ CKEDITOR.CTRL + 188 /*COMMA*/, 'subscript' ],
            [ CKEDITOR.CTRL + 190 /*PERIOD*/, 'superscript' ],
            [ CKEDITOR.CTRL + 85 /*U*/, 'underline' ],
            [ CKEDITOR.CTRL + 90 /*Z*/, 'undo' ],
            [ CKEDITOR.ALT + 65 /*A*/, 'anchor' ],
            [ CKEDITOR.ALT + 68 /*D*/, 'creatediv' ],
            [ CKEDITOR.ALT + CKEDITOR.SHIFT + 68 /*D*/, 'editdiv' ],
            [ CKEDITOR.CTRL + 57 /*9*/, 'image' ],
            [ CKEDITOR.ALT + 73 /*I*/, 'image' ],
            [ CKEDITOR.CTRL + 75 /*K*/, 'link' ],
            [ CKEDITOR.ALT + 76 /*L*/, 'link' ],
            [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 75 /*K*/, 'unlink' ],
            [ CKEDITOR.ALT + CKEDITOR.SHIFT + 76 /*L*/, 'unlink' ],
            [ CKEDITOR.ALT + 86 /*V*/, 'pastetext' ],
            [ CKEDITOR.ALT + CKEDITOR.SHIFT + 86 /*V*/, 'pastefromword' ],
            [ CKEDITOR.ALT + 84 /*T*/, 'table' ],
            [ CKEDITOR.ALT + 8 /*Backspace*/, 'blur' ],
            [ CKEDITOR.ALT + 77 /*M*/, 'contextMenu' ],
        	[ CKEDITOR.SHIFT + 121 /*F10*/, 'contextMenu' ],
        	[ CKEDITOR.CTRL + CKEDITOR.SHIFT + 121 /*F10*/, 'contextMenu' ],
            [ CKEDITOR.ALT + 122 /*F11*/, 'elementsPathFocus' ],
            [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 70 /*F*/, 'find' ],
            [ CKEDITOR.ALT + 88 /*X*/, 'maximize' ],
            [ CKEDITOR.CTRL + 72 /*H*/, 'replace' ],
            [ CKEDITOR.ALT + 90 /*Z*/, 'source' ],
            [ CKEDITOR.ALT + 48 /*ZERO*/, 'toolbarCollapse' ],
            [ CKEDITOR.ALT + 121 /*F10*/, 'toolbarFocus' ],
        ];";
		}

		$editor .= "
        CKEDITOR.config.format_tags = 'p;h2;h3;h4;h5;h6';
        CKEDITOR.config.ignoreEmptyParagraph = false;
        CKEDITOR.config.fillEmptyBlocks = false;";

		if($this->params->get('outlineblocks', 1) == 1)
		{
			$editor .= '
			CKEDITOR.config.startupOutlineBlocks = true;';
		}

		if($this->params->get('disablespell', 1) == 1)
		{
			$editor .= '
			CKEDITOR.config.disableNativeSpellChecker = false;';
		}

		$string = $this->params->get('CKEditorCustomJs', '');
		$reg    = "#/\*.+\*/#Us";
		$string = preg_replace($reg, '', $string);

		$editor .= $string;

		$instanceReady = $this->CKEditorInstance();

		$editor .= $instanceReady . '</script>';
		$editor .= $this->_displayButtons($id, $buttons, $asset, $author);

		return $editor;
	}

	/**
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	public function CKEditorInstance()
	{
		$txt = "
    	CKEDITOR.on( 'instanceReady', function( ev ) {
    		var formater = [];
    		formater['indent'] = " . $this->params->get('CKEditorIndent', 1) . ";
    		formater['breakBeforeOpen'] = " . $this->params->get('CKEditorBreakBeforeOpener', 1) . ";
    		formater['breakAfterOpen'] = " . $this->params->get('CKEditorBreakAfterOpener', 1) . ";
    		formater['breakBeforeClose'] = " . $this->params->get('CKEditorBreakBeforeCloser', 0) . ";
    		formater['breakAfterClose'] = " . $this->params->get('CKEditorBreakAfterCloser', 1) . ';
    		
    		var pre_formater = ' . $this->params->get('CKEditorPre', 0) . ",
    		dtd = CKEDITOR.dtd;
    		
    		for ( var e in CKEDITOR.tools.extend( {}, dtd.\$nonBodyContent, dtd.\$block, dtd.\$listItem, dtd.\$tableContent ) ) {
    			ev.editor.dataProcessor.writer.setRules( e, formater);
    		}
    		
    		ev.editor.dataProcessor.writer.setRules( 'pre',	{
    			indent: pre_formater
    		});
    	});";

		return $txt;
	}

	/**
	 * @param $root
	 * @param $folder
	 * @param $chmod
	 *
	 *
	 * @since 5.0
	 */
	private function _saveDir($saveDir, $root, $folder, $chmod)
	{
		if($saveDir !== $folder && $saveDir !== '')
		{
			$dirs = explode('/', $saveDir);
			$path = $root;

			foreach($dirs AS $dir)
			{
				$path = $path . '/' . $dir;
				if(!is_dir($path) && !mkdir($path, $chmod) && !is_dir($path))
				{
					$this->app->enqueueMessage('Creating ' . $path . ' failed', 'message');
				}
			}
		}
		else
		{
			$saveDir = $root . '/' . $folder;
			if(!is_dir($root) && !mkdir($concurrentDirectory = $root, $chmod) && !is_dir($concurrentDirectory))
			{
				$this->app->enqueueMessage('Creating ' . $root . ' failed', 'message');
			}

			if(!is_dir($saveDir) && !mkdir($saveDir, $chmod) && !is_dir($saveDir))
			{
				$this->app->enqueueMessage('Creating ' . $saveDir . ' failed', 'message');
			}
		}
	}

	private function _userFolder($dir, $access)
	{
		if($access)
		{
			$dir = str_replace([
				'$id',
				'$username'
			], [
				$this->user->id,
				$this->user->username
			], $dir);

			$dir = $dir . '/upload/' . $this->user->id;
			$this->_make_dir($dir);

			$user_folders = (array) $this->params->get('user_folders');
			foreach($user_folders as $uf)
			{
				if($uf->user_folder)
				{
					$this->_make_dir($dir . '/' . $uf->user_folder);
					$this->_make_dir($dir . '/' . $uf->user_folder . '/' . date('Y/m'));
				}
			}
		}

		return $dir;
	}

	private function _resourceFiles($session, $param)
	{
		if($param->get('FilesResourceFiles', ''))
		{
			$extensions = explode(',', $param->get('FilesResourceFiles', ''));
			$extensions = array_unique($extensions);

			$results = [];
			foreach($extensions AS $extension)
			{
				if($extension)
				{
					$results[] = $extension;
				}
			}

			return $session->set('FilesResourceFiles', implode(',', $results));
		}

		return false;
	}

	private function _maxFilesSize($session, $param)
	{
		if($param->get('FilesMaxFilesSize', ''))
		{
			return $session->set('FilesMaxFilesSize', $param->get('FilesMaxFilesSize'));
		}

		return false;
	}

	private function _maxImagesSize($session, $param)
	{
		if($param->get('FilesMaxImagesSize', ''))
		{
			return $session->set('FilesMaxImagesSize', $param->get('FilesMaxImagesSize'));
		}

		return false;
	}

	private function _maxImageWidth($session, $param)
	{
		if((int) $param->get('FilesMaxImageWidth', 0))
		{
			return $session->set('FilesMaxImageWidth', $param->get('FilesMaxImageWidth', 0));
		}

		return false;
	}

	private function _maxImageHeight($session, $param)
	{
		if((int) $param->get('FilesMaxImageHeight', 0))
		{
			return $session->set('FilesMaxImageHeight', $param->get('FilesMaxImageHeight', 0));
		}

		return false;
	}

	private function _maxThumbnailHWidth($session, $param)
	{
		if((int) $param->get('FilesMaxThumbnailWidth', 0))
		{
			return $session->set('FilesMaxThumbnailWidth', $param->get('FilesMaxThumbnailWidth', 0));
		}

		return false;
	}

	private function _maxThumbnailHeight($session, $param)
	{
		if((int) $param->get('FilesMaxThumbnailHeight', 0))
		{
			return $session->set('FilesMaxThumbnailHeight', $param->get('FilesMaxThumbnailHeight', 0));
		}

		return false;
	}

	private function _resourceImages($session, $param)
	{
		if($param->get('FilesResourceImages', ''))
		{
			$extensions = explode(',', $param->get('FilesResourceImages', ''));
			$extensions = array_unique($extensions);

			$results = [];
			foreach($extensions AS $extension)
			{
				if($extension)
				{
					$results[] = $extension;
				}
			}

			return $session->set('FilesResourceImages', implode(',', $results));
		}

		return false;
	}

	/**
	 * @param $name
	 * @param $buttons
	 * @param $asset
	 * @param $author
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	private function _displayButtons($name, $buttons, $asset, $author)
	{
		$return = '';

		$args = [
			'name'  => $name,
			'event' => 'onGetInsertMethod'
		];

		$results = (array) $this->update($args);

		if($results)
		{
			foreach($results as $result)
			{
				if(is_string($result) && trim($result))
				{
					$return .= $result;
				}
			}
		}

		if(is_array($buttons) || (is_bool($buttons) && $buttons))
		{
			$buttons = $this->_subject->getButtons($name, $buttons, $asset, $author);
			$return  .= LayoutHelper::render('joomla.editors.buttons', $buttons);
			$return  = '<div style="clear:both;">' . $return . '</div>';
		}

		return $return;
	}

	/**
	 * @param $name
	 *
	 * @return bool
	 *
	 * @since 5.0
	 */
	public function onGetInsertMethod($name)
	{
		$document = Factory::getDocument();
		$url      = str_replace('administrator/', '', Uri::base());
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
	 * @since 5.0
	 */
	private function _toogleButton($name)
	{
		return LayoutHelper::render('joomla.tinymce.togglebutton', $name);
	}

	/**
	 * @param     $dir
	 * @param int $mode
	 *
	 * @return bool
	 *
	 * @since 5.0
	 */
	private function _make_dir($dir, $mode = 0777)
	{
		if(@mkdir($dir, $mode) || is_dir($dir))
		{
			if(!file_exists($indexfile = $dir . '/index.html'))
			{
				$indexcontent = '<!DOCTYPE html><title></title>';
				$file         = fopen($indexfile, 'wb');

				fwrite($file, $indexcontent);
				fclose($file);
			}

			return true;
		}

		if(!$this->_make_dir(dirname($dir)))
		{
			return false;
		}

		return @mkdir($dir, $mode);
	}
}