<?php
/**
 * CKEditor for Joomla!
 *
 * @version        5.x
 * @package        CKEditor
 * @author         Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C)  2014-2019 by Denys D. Nosov (https://joomla-ua.org)
 * @license        GNU General Public License version 2 or later
 *
 * @since          5.0
 **/

/*
* @copyright Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
* @license	 GNU/GPL
* CKEditor extension is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*/

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

class plgEditorCKeditor extends CMSPlugin
{
	public $pluginsName = [];
	public $called = false;
	public $buildVersion = '?v=5.16.6';

	/**
	 * plgEditorCKeditor constructor.
	 *
	 * @param $subject
	 * @param $config
	 *
	 * @throws \Exception
	 * @since          5.0
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$this->app     = Factory::getApplication();
		$this->config  = Factory::getConfig();
		$this->session = Factory::getSession();
		$this->user    = Factory::getUser();
		$this->db      = Factory::getDBO();

		$language = Factory::getLanguage();
		$language->load('com_ckeditor', JPATH_ADMINISTRATOR, 'en-GB', true);
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
		$load .= '<script src="' . Uri::root(true) . '/plugins/editors/ckeditor/ckeditor/ckeditor.js' . $this->buildVersion . '"></script>';

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

		$pluginsPath = '';
		foreach(glob(__DIR__ . '/plugins/*', GLOB_ONLYDIR) as $dir)
		{
			$this->pluginsName[] = basename($dir);
			$pluginsPath         .= "\nCKEDITOR.plugins.addExternal('" . basename($dir) . "', '../plugins/" . basename($dir) . "/', 'plugin.js" . $this->buildVersion . "');";
		}

		$load .= $pluginsPath;
		$load .= '</script>';

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

		/*
		 * Access to editor and filemanager
		 */
		$userid        = $this->user->get('id');
		$gid           = Access::getGroupsByUser($userid);
		$access_editor = $this->params->get('usergroup');

		// Access to editor
		$access_true = false;
		if(is_array($access_editor) && is_array($gid))
		{
			foreach($gid AS $key => $val)
			{
				if(in_array($val, $access_editor))
				{
					$access_true = true;

					break;
				}
			}
		}
		elseif(is_array($gid) && in_array($access_editor, $gid))
		{
			$access_true = true;
		}

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

		$skin = $this->params->get('skin', 'moono');
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

		if($this->params->get('CKEditorHeight') > 0)
		{
			$editor .= ", height: '" . $this->params->get('CKEditorHeight', '') . "'";
			$editor .= ", autoGrow_maxHeight: '" . $this->params->get('CKEditorHeight', '') . "'";
		}

		if($height > 0)
		{
			$editor .= ", height: '" . $height . "'";
		}
		else
		{
			$editor .= ', height: editorheight';
		}

		if($this->params->get('Color', '') != '')
		{
			$editor .= ", uiColor: '" . $this->params->get('Color', '') . "'";
		}

		if($this->params->get('ACF', 0) != 1)
		{
			$editor .= ', allowedContent: true';
		}

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

			$replace     = [
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
			$toolbar     = str_replace($replace, $replacement, $toolbar);
			$tmp         = '';

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
			$data    = '';
			$toolbar = str_replace([
				'/,;,/',
				',;,/'
			], [
				'/',
				',/'
			], $toolbar);

			foreach(explode(';', $toolbar) AS $menu)
			{
				if($menu != '')
				{
					$data .= '[';

					$tmpArray = [];
					foreach(explode(',', $menu) AS $key => $value)
					{
						if($value != '' && trim($value) !== '/')
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

		$style_file = trim($this->params->get('style', ''));
		if($style_file != '')
		{
			$editor .= ",stylesCombo_stylesSet: 'default:" . trim($style_file) . $this->buildVersion . "'";
			$editor .= ",stylesSet: 'default:" . trim($style_file) . $this->buildVersion . "'";
		}

		$template_file = trim($this->params->get('template', ''));
		if($template_file != '')
		{
			$editor .= ",templates_files: ['" . trim($template_file) . $this->buildVersion . "']";
			$editor .= ",templates: 'default'";
		}

		if(isset($data))
		{
			$editor .= ",toolbar :[{$data}]";
		}

		$css       = '';
		$css_files = trim($this->params->get('css', ''));
		if($css_files != '')
		{
			foreach(explode(';', $css_files) AS $file)
			{
				$css .= ", '" . trim($file) . $this->buildVersion . "'";
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

		if($css != '')
		{
			$editor .= ",contentsCss:  [ '" . Uri::root() . "plugins/editors/ckeditor/ckeditor/contents.css' {$css} ]";
		}

		if($this->params->get('ckfinder', '1') == 1)
		{
			$this->session->set('CKFinder3Access', false);

			$username_access    = $this->params->get('username_access');
			$user_access_folder = $this->params->get('user_access_folder');

			// Access to CKFinder
			$user_access_true = false;
			if(is_array($username_access) && is_array($gid))
			{
				foreach($username_access as $key => $val)
				{
					if(in_array($val, $gid))
					{
						$user_access_true = true;

						break;
					}
				}
			}
			elseif(is_array($gid) && in_array($username_access, $gid))
			{
				$user_access_true = true;
			}

			// Access only to user folder
			$user_folder_access_true = false;
			if(is_array($user_access_folder) && is_array($gid))
			{
				foreach($user_access_folder as $key => $val)
				{
					if(in_array($val, $gid))
					{
						$user_folder_access_true = true;

						break;
					}
				}
			}
			elseif(is_array($gid) && in_array($username_access, $gid))
			{
				$user_folder_access_true = true;
			}

			// Enable CKFinder
			if($user_access_true && $this->params->get('ckfinder', '0') == 1)
			{
				if($this->session->getState() === 'active')
				{
					$this->session->set('CKFinder3LicenseName', null);
					$this->session->set('CKFinder3LicenseKey', null);
					$this->session->set('CKFinder3Access', true);
					$this->session->set('CKFinder3MaxFilesSize', null);
					$this->session->set('CKFinder3MaxImagesSize', null);
					$this->session->set('CKFinder3ResourceFiles', null);
					$this->session->set('CKFinder3ResourceImages', null);
					$this->session->set('CKFinder3MaxImageWidth', null);
					$this->session->set('CKFinder3MaxImageHeight', null);
					$this->session->set('CKFinder3MaxThumbnailWidth', null);
					$this->session->set('CKFinder3MaxThumbnailHeight', null);
					$this->session->set('CKFinder3SettingsChmod', null);
					$this->session->set('CKFinder3HideFolders', null);
					$this->session->set('CKFinder3PathType', null);
					$this->session->set('CKFinder3RootFolder', null);
				}

				$ckfinder_path = Uri::root() . 'plugins/editors/ckeditor/ckfinder/';
				$chmod         = octdec(trim($this->params->get('CKFinderSettingsChmod', '0755')));
				$root_folder   = str_replace('/administrator', '', JPATH_BASE);

				$this->session->set('CKFinder3RootFolder', $root_folder);

				$editor .= ",filebrowserBrowseUrl: '" . $ckfinder_path . "ckfinder.html" . $this->buildVersion . "',
					filebrowserImageBrowseUrl: '" . $ckfinder_path . "ckfinder.html" . $this->buildVersion . "&Type=Images',
					filebrowserUploadUrl: '" . $ckfinder_path . "core/connector/php/connector.php" . $this->buildVersion . "&command=QuickUpload&type=Files',
					filebrowserImageUploadUrl: '" . $ckfinder_path . "core/connector/php/connector.php" . $this->buildVersion . "&command=QuickUpload&type=Images'";

				if(!defined('CKFINDER_PATH_BASE'))
				{
					define('CKFINDER_PATH_BASE', str_replace([
						'\administrator',
						'/administrator'
					], '', JPATH_BASE));
				}

				// Save Images
				$saveDir = $this->params->get('CKFinderSaveImages', 'images');
				if($user_folder_access_true)
				{
					$saveDir = str_replace([
						'$id',
						'$username'
					], [
						$this->user->id,
						$this->user->username
					], $saveDir);

					$saveDir .= '/upload/' . $this->user->id;
					$this->_make_dir($saveDir, $chmod);

					$user_folders = (array) $this->params->get('user_folders');
					foreach($user_folders as $uf)
					{
						if($uf->user_folder)
						{
							$this->_make_dir($saveDir . '/' . $uf->user_folder, $chmod);
							$this->_make_dir($saveDir . '/' . $uf->user_folder . '/' . date('Y/m'), $chmod);
						}
					}
				}

				$this->session->set('CKFinder3ImagesPath', $saveDir);

				// Save Files
				$saveDir = $this->params->get('CKFinderSaveFiles', 'files');
				if($user_folder_access_true)
				{
					$saveDir = str_replace([
						'$id',
						'$username'
					], [
						$this->user->id,
						$this->user->username
					], $saveDir);

					$saveDir .= '/upload/' . $this->user->id;
					$this->_make_dir($saveDir, $chmod);
				}

				$this->session->set('CKFinder3FilesPath', $saveDir);

				// Save Thumbs
				$saveDir = $this->params->get('CKFinderSaveThumbs', 'cache/_thumbs');
				if($user_folder_access_true)
				{
					$saveDir = str_replace([
						'$id',
						'$username'
					], [
						$this->user->id,
						$this->user->username
					], $saveDir);

					$saveDir .= '/upload/' . $this->user->id;
					$this->_make_dir($saveDir, $chmod);
				}

				$this->session->set('CKFinder3ThumbsPath', $saveDir);

				// Resource Files
				if($this->params->get('CKFinderResourceFiles', 'files'))
				{
					$extensions = explode(',', $this->params->get('CKFinderResourceFiles', ''));
					$extensions = array_unique($extensions);

					$results = [];
					foreach($extensions AS $extension)
					{
						if($extension)
						{
							$results[] = $extension;
						}
					}

					$this->session->set('CKFinder3ResourceFiles', implode(',', $results));
				}

				// Resource Images
				if($this->params->get('CKFinderResourceImages', ''))
				{
					$extensions = explode(',', $this->params->get('CKFinderResourceImages', ''));
					$extensions = array_unique($extensions);

					$results = [];
					foreach($extensions AS $extension)
					{
						if($extension)
						{
							$results[] = $extension;
						}
					}

					$this->session->set('CKFinder3ResourceImages', implode(',', $results));
				}

				// License
				if($this->params->get('CKFinderLicenseName') && $this->params->get('CKFinderLicenseKey'))
				{
					$this->session->set('CKFinder3LicenseName', trim($this->params->get('CKFinderLicenseName')));
					$this->session->set('CKFinder3LicenseKey', trim($this->params->get('CKFinderLicenseKey')));
				}

				// Prefix URL
				switch($this->params->get('CKFinderPathType', 0))
				{
					case '1':
						$prefix = '';
						break;

					case '2':
						$prefix = str_replace('/administrator', '/', Uri::base(true));
						break;

					default:
					case '0':
						$prefix = str_replace('/administrator', '', Uri::base());
						break;
				}

				$this->session->set('CKFinder3PathType', $prefix);

				// Chmod
				if($this->params->get('CKFinderSettingsChmod', '0755'))
				{
					$this->session->set('CKFinder3SettingsChmod', $chmod);
				}

				// Hide Folders
				if($this->params->get('CKFinderHideFolders', ''))
				{
					$this->session->set('CKFinder3HideFolders', $this->params->get('CKFinderHideFolders'));
				}

				// Uploads Size
				if($this->params->get('CKFinderMaxImagesSize', ''))
				{
					$this->session->set('CKFinder3MaxImagesSize', $this->params->get('CKFinderMaxImagesSize'));
				}

				if($this->params->get('CKFinderMaxFilesSize', ''))
				{
					$this->session->set('CKFinder3MaxFilesSize', $this->params->get('CKFinderMaxFilesSize'));
				}

				// Image Size
				if((int) $this->params->get('CKFinderMaxImageWidth', 0))
				{
					$this->session->set('CKFinder3MaxImageWidth', (int) $this->params->get('CKFinderMaxImageWidth', 0));
				}

				if((int) $this->params->get('CKFinderMaxImageHeight', 0))
				{
					$this->session->set('CKFinder3MaxImageHeight', (int) $this->params->get('CKFinderMaxImageHeight', 0));
				}

				// Thumbnail Size
				if((int) $this->params->get('CKFinderMaxThumbnailWidth', 0))
				{
					$this->session->set('CKFinder3MaxThumbnailWidth', (int) $this->params->get('CKFinderMaxThumbnailWidth', 0));
				}

				if((int) $this->params->get('CKFinderMaxThumbnailHeight', 0))
				{
					$this->session->set('CKFinder3MaxThumbnailHeight', (int) $this->params->get('CKFinderMaxThumbnailHeight', 0));
				}
			}
		}

		$editor .= '});';

		if($this->params->get('cssbodyclass') != '')
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
            [ CKEDITOR.ALT + 67 /*C*/, 'specialchar' ],
            [ CKEDITOR.ALT + 84 /*T*/, 'table' ],
            [ CKEDITOR.ALT + 8 /*Backspace*/, 'blur' ],
            [ CKEDITOR.ALT + 77 /*M*/, 'contextMenu' ],
        	[ CKEDITOR.SHIFT + 121 /*F10*/, 'contextMenu' ],
        	[ CKEDITOR.CTRL + CKEDITOR.SHIFT + 121 /*F10*/, 'contextMenu' ],
            [ CKEDITOR.ALT + 122 /*F11*/, 'elementsPathFocus' ],
            [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 70 /*F*/, 'find' ],
            [ CKEDITOR.ALT + 88 /*X*/, 'maximize' ],
            [ CKEDITOR.CTRL + 113 /*F2*/, 'preview' ],
            [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 80 /*P*/, 'print' ],
            [ CKEDITOR.CTRL + 72 /*H*/, 'replace' ],
            [ CKEDITOR.ALT + 83 /*S*/, 'scaytcheck' ],
            [ CKEDITOR.ALT + 66 /*B*/, 'showblocks' ],
            [ CKEDITOR.ALT + CKEDITOR.SHIFT + 84 /*T*/, 'showborders' ],
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

		$editor .= $instanceReady;
		$editor .= '</script>';

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
		$return  = '';
		$args    = [
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
			$return  .= '<div style="clear:both;">';
			$return  .= LayoutHelper::render('joomla.editors.buttons', $buttons);
			$return  .= '</div>';
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
			return true;
		}

		if(!$this->_make_dir(dirname($dir)))
		{
			return false;
		}

		return @mkdir($dir, $mode);
	}
}