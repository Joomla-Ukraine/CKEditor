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

defined('_JEXEC') or die;

jimport('joomla.application.component.view');
jimport('joomla.form.form');
jimport('joomla.utilities.simplexml');

class ConfigViewConfig extends JViewLegacy
{
	/**
	 * @param null $tpl
	 *
	 *
	 * @since 5.0
	 */
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$db  = JFactory::getDBO();

		if(!file_exists('components/com_ckeditor/config.xml'))
		{
			$app->enqueueMessage('Form file missing.', 'message');
		}

		$xml = file_get_contents('components/com_ckeditor/config.xml');
		$xml = str_replace([
			'<config>',
			'</config>'
		], [
			'<form>',
			'</form>'
		], $xml);

		$form = new jForm('adminFormCKEditor');
		$form->load($xml);

		$client = JRequest::getWord('client', 'site');

		$row = JTable::getInstance('extension');

		$query = 'SELECT extension_id' . ' FROM #__extensions' . ' WHERE element = "ckeditor"';
		$db->setQuery($query);
		$id = $db->loadResult();

		// load the row from the db table
		$row->load(intval($id));

		$xml = JPATH_BASE . '/components/com_ckeditor/config.xml';
		if(!file_exists($xml))
		{
			$xml = JPATH_PLUGINS . '/editors/ckeditor.xml';
		}

		$formData = new JRegistry($row->params, 'xml');

		//if _source directory dosen't exist remove CKEditroJs option from XML params
		if(!is_dir(JPATH_PLUGINS . '/editors/ckeditor/ckeditor/_source'))
		{
			$form->removeField('CKEditorJs');
		}

		$form->bind($formData);

		$params = $formData;

		$this->assignRef('params', $formData);
		$this->assignRef('client', $client);
		$this->assignRef('form', $form);

		$configIni   = $this->parseConfigIni();
		$configCheck = $this->checkConfig($configIni);
		$this->assignRef('message', $configCheck);

		//define all CKEditor toolbar buttons
		$toolBars = [
			'Source'         => [
				'name'  => 'Source',
				'icon'  => '../images/source.png',
				'type'  => 'command',
				'title' => 'Source',
				'row'   => 1
			],
			'Save'           => [
				'name'  => 'Save',
				'icon'  => '../images/save.png',
				'type'  => 'command',
				'title' => 'Save',
				'row'   => 1
			],
			'NewPage'        => [
				'name'  => 'NewPage',
				'icon'  => '../images/newPage.png',
				'type'  => 'command',
				'title' => 'New Page',
				'row'   => 1
			],
			'Preview'        => [
				'name'  => 'Preview',
				'icon'  => '../images/preview.png',
				'type'  => 'command',
				'title' => 'Preview',
				'row'   => 1
			],
			'Templates'      => [
				'name'  => 'Templates',
				'icon'  => '../images/templates.png',
				'type'  => 'command',
				'title' => 'Templates',
				'row'   => 1
			],
			'Cut'            => [
				'name'  => 'Cut',
				'icon'  => '../images/cut.png',
				'type'  => 'command',
				'title' => 'Cut',
				'row'   => 1
			],
			'Copy'           => [
				'name'  => 'Copy',
				'icon'  => '../images/copy.png',
				'type'  => 'command',
				'title' => 'Copy',
				'row'   => 1
			],
			'Paste'          => [
				'name'  => 'Paste',
				'icon'  => '../images/paste.png',
				'type'  => 'command',
				'title' => 'Paste',
				'row'   => 1
			],
			'PasteText'      => [
				'name'  => 'PasteText',
				'icon'  => '../images/pastePlainText.png',
				'type'  => 'command',
				'title' => 'Paste as plain text',
				'row'   => 1
			],
			'PasteFromWord'  => [
				'name'  => 'PasteFromWord',
				'icon'  => '../images/pasteWord.png',
				'type'  => 'command',
				'title' => 'Paste from Word',
				'row'   => 1
			],
			'Print'          => [
				'name'  => 'Print',
				'icon'  => '../images/print.png',
				'type'  => 'command',
				'title' => 'Print',
				'row'   => 1
			],
			'SpellChecker'   => [
				'name'  => 'SpellChecker',
				'icon'  => '../images/checkSpelling.png',
				'type'  => 'command',
				'title' => 'Check Spelling',
				'row'   => 1
			],
			'Scayt'          => [
				'name'  => 'Scayt',
				'icon'  => '../images/checkSpelling.png',
				'type'  => 'command',
				'title' => 'Spell Check As you Type',
				'row'   => 1
			],
			'Undo'           => [
				'name'  => 'Undo',
				'icon'  => '../images/undo.png',
				'type'  => 'command',
				'title' => 'Undo',
				'row'   => 1
			],
			'Redo'           => [
				'name'  => 'Redo',
				'icon'  => '../images/redo.png',
				'type'  => 'command',
				'title' => 'Redo',
				'row'   => 1
			],
			'Find'           => [
				'name'  => 'Find',
				'icon'  => '../images/find.png',
				'type'  => 'command',
				'title' => 'Find',
				'row'   => 1
			],
			'Replace'        => [
				'name'  => 'Replace',
				'icon'  => '../images/replace.png',
				'type'  => 'command',
				'title' => 'Replace',
				'row'   => 1
			],
			'SelectAll'      => [
				'name'  => 'SelectAll',
				'icon'  => '../images/selectAll.png',
				'type'  => 'command',
				'title' => 'Select All',
				'row'   => 1
			],
			'RemoveFormat'   => [
				'name'  => 'RemoveFormat',
				'icon'  => '../images/removeFormat.png',
				'type'  => 'command',
				'title' => 'Remove Format',
				'row'   => 1
			],
			'Form'           => [
				'name'  => 'Form',
				'icon'  => '../images/form.png',
				'type'  => 'command',
				'title' => 'Form',
				'row'   => 1
			],
			'Checkbox'       => [
				'name'  => 'Checkbox',
				'icon'  => '../images/checkbox.png',
				'type'  => 'command',
				'title' => 'Checkbox',
				'row'   => 1
			],
			'Radio'          => [
				'name'  => 'Radio',
				'icon'  => '../images/radioButton.png',
				'type'  => 'command',
				'title' => 'Radio Button',
				'row'   => 1
			],
			'TextField'      => [
				'name'  => 'TextField',
				'icon'  => '../images/textField.png',
				'type'  => 'command',
				'title' => 'Text Field',
				'row'   => 1
			],
			'Textarea'       => [
				'name'  => 'Textarea',
				'icon'  => '../images/textarea.png',
				'type'  => 'command',
				'title' => 'Textarea',
				'row'   => 1
			],
			'Select'         => [
				'name'  => 'Select',
				'icon'  => '../images/selectionField.png',
				'type'  => 'command',
				'title' => 'Selection Field',
				'row'   => 1
			],
			'Button'         => [
				'name'  => 'Button',
				'icon'  => '../images/button.png',
				'type'  => 'command',
				'title' => 'Button',
				'row'   => 1
			],
			'ImageButton'    => [
				'name'  => 'ImageButton',
				'icon'  => '../images/imageButton.png',
				'type'  => 'command',
				'title' => 'Image Button',
				'row'   => 1
			],
			'HiddenField'    => [
				'name'  => 'HiddenField',
				'icon'  => '../images/hiddenField.png',
				'type'  => 'command',
				'title' => 'Hidden Field',
				'row'   => 1
			],
			'Bold'           => [
				'name'  => 'Bold',
				'icon'  => '../images/bold.png',
				'type'  => 'command',
				'title' => 'Bold',
				'row'   => 2
			],
			'Italic'         => [
				'name'  => 'Italic',
				'icon'  => '../images/italic.png',
				'type'  => 'command',
				'title' => 'Italic',
				'row'   => 2
			],
			'Underline'      => [
				'name'  => 'Underline',
				'icon'  => '../images/underline.png',
				'type'  => 'command',
				'title' => 'Underline',
				'row'   => 2
			],
			'Strike'         => [
				'name'  => 'Strike',
				'icon'  => '../images/strike.png',
				'type'  => 'command',
				'title' => 'Strike Through',
				'row'   => 2
			],
			'Subscript'      => [
				'name'  => 'Subscript',
				'icon'  => '../images/subscript.png',
				'type'  => 'command',
				'title' => 'Subscript',
				'row'   => 2
			],
			'Superscript'    => [
				'name'  => 'Superscript',
				'icon'  => '../images/superscript.png',
				'type'  => 'command',
				'title' => 'Superscript',
				'row'   => 2
			],
			'NumberedList'   => [
				'name'  => 'NumberedList',
				'icon'  => '../images/numberedList.png',
				'type'  => 'command',
				'title' => 'Insert/Remove Numbered List',
				'row'   => 2
			],
			'BulletedList'   => [
				'name'  => 'BulletedList',
				'icon'  => '../images/bulletedList.png',
				'type'  => 'command',
				'title' => 'Insert/Remove Bulleted List',
				'row'   => 2
			],
			'Outdent'        => [
				'name'  => 'Outdent',
				'icon'  => '../images/decreaseIndent.png',
				'type'  => 'command',
				'title' => 'Decrease Indent',
				'row'   => 2
			],
			'Indent'         => [
				'name'  => 'Indent',
				'icon'  => '../images/increaseIndent.png',
				'type'  => 'command',
				'title' => 'Increase Indent',
				'row'   => 2
			],
			'Blockquote'     => [
				'name'  => 'Blockquote',
				'icon'  => '../images/blockQuote.png',
				'type'  => 'command',
				'title' => 'Block Quote',
				'row'   => 2
			],
			'CreateDiv'      => [
				'name'  => 'CreateDiv',
				'icon'  => '../images/createDivContainer.png',
				'type'  => 'command',
				'title' => 'Create Div Container',
				'row'   => 2
			],
			'JustifyLeft'    => [
				'name'  => 'JustifyLeft',
				'icon'  => '../images/leftJustify.png',
				'type'  => 'command',
				'title' => 'Left Justify',
				'row'   => 2
			],
			'JustifyCenter'  => [
				'name'  => 'JustifyCenter',
				'icon'  => '../images/centerJustify.png',
				'type'  => 'command',
				'title' => 'Center Justify',
				'row'   => 2
			],
			'JustifyRight'   => [
				'name'  => 'JustifyRight',
				'icon'  => '../images/rightJustify.png',
				'type'  => 'command',
				'title' => 'Right Justify',
				'row'   => 2
			],
			'JustifyBlock'   => [
				'name'  => 'JustifyBlock',
				'icon'  => '../images/blockJustify.png',
				'type'  => 'command',
				'title' => 'Block Justify',
				'row'   => 2
			],
			'BidiLtr'        => [
				'name'  => 'BidiLtr',
				'icon'  => '../images/bidiLeft.png',
				'type'  => 'command',
				'title' => 'Text direction from left to right',
				'row'   => 2
			],
			'BidiRtl'        => [
				'name'  => 'BidiRtl',
				'icon'  => '../images/bidiRight.png',
				'type'  => 'command',
				'title' => 'Text direction from right to left',
				'row'   => 2
			],
			'Link'           => [
				'name'  => 'Link',
				'icon'  => '../images/link.png',
				'type'  => 'command',
				'title' => 'Link',
				'row'   => 2
			],
			'Unlink'         => [
				'name'  => 'Unlink',
				'icon'  => '../images/unlink.png',
				'type'  => 'command',
				'title' => 'Unlink',
				'row'   => 2
			],
			'Anchor'         => [
				'name'  => 'Anchor',
				'icon'  => '../images/anchor.png',
				'type'  => 'command',
				'title' => 'Anchor',
				'row'   => 2
			],
			'Image'          => [
				'name'  => 'Image',
				'icon'  => '../images/image.png',
				'type'  => 'command',
				'title' => 'Image',
				'row'   => 2
			],
			'Table'          => [
				'name'  => 'Table',
				'icon'  => '../images/table.png',
				'type'  => 'command',
				'title' => 'Table',
				'row'   => 2
			],
			'HorizontalRule' => [
				'name'  => 'HorizontalRule',
				'icon'  => '../images/horizontalLine.png',
				'type'  => 'command',
				'title' => 'Insert Horizontal Line',
				'row'   => 2
			],
			'Smiley'         => [
				'name'  => 'Smiley',
				'icon'  => '../images/smiley.png',
				'type'  => 'command',
				'title' => 'Smiley',
				'row'   => 2
			],
			'SpecialChar'    => [
				'name'  => 'SpecialChar',
				'icon'  => '../images/specialCharacter.png',
				'type'  => 'command',
				'title' => 'Inseert Special Character',
				'row'   => 2
			],
			'PageBreak'      => [
				'name'  => 'PageBreak',
				'icon'  => '../images/pageBreakPrinting.png',
				'type'  => 'command',
				'title' => 'Insert Page Break for Printing',
				'row'   => 2
			],
			'Styles'         => [
				'name'  => 'Styles',
				'icon'  => '../images/styles.png',
				'type'  => 'command',
				'title' => 'Formatting Styles',
				'row'   => 3
			],
			'Format'         => [
				'name'  => 'Format',
				'icon'  => '../images/format.png',
				'type'  => 'command',
				'title' => 'Paragraph Format',
				'row'   => 3
			],
			'Font'           => [
				'name'  => 'Font',
				'icon'  => '../images/font.png',
				'type'  => 'command',
				'title' => 'Font Name',
				'row'   => 3
			],
			'FontSize'       => [
				'name'  => 'FontSize',
				'icon'  => '../images/fontSize.png',
				'type'  => 'command',
				'title' => 'Font Size',
				'row'   => 3
			],
			'TextColor'      => [
				'name'  => 'TextColor',
				'icon'  => '../images/textColor.png',
				'type'  => 'command',
				'title' => 'Text Color',
				'row'   => 3
			],
			'BGColor'        => [
				'name'  => 'BGColor',
				'icon'  => '../images/backgroundColor.png',
				'type'  => 'command',
				'title' => 'Background Color',
				'row'   => 3
			],
			'Maximize'       => [
				'name'  => 'Maximize',
				'icon'  => '../images/maximize.png',
				'type'  => 'command',
				'title' => 'Maximize',
				'row'   => 3
			],
			'ShowBlocks'     => [
				'name'  => 'ShowBlocks',
				'icon'  => '../images/showBlocks.png',
				'type'  => 'command',
				'title' => 'Show Blocks',
				'row'   => 3
			],
			'ReadMore'       => [
				'name'  => 'ReadMore',
				'icon'  => '../images/readmoreButton.png',
				'type'  => 'command',
				'title' => 'Read more',
				'row'   => 3
			],
			'Iframe'         => [
				'name'  => 'Iframe',
				'icon'  => '../images/iframe.png',
				'type'  => 'command',
				'title' => 'IFrame',
				'row'   => 3
			],
			'About'          => [
				'name'  => 'About',
				'icon'  => '../images/about.png',
				'type'  => 'command',
				'title' => 'About',
				'row'   => 3
			],
		];

		if($configIni)
		{
			$toolBars = array_merge($toolBars, $configIni);
		}

		$this->assignRef('allToolbars', $toolBars);

		$toolbar = JRequest::getWord('cid', '');
		$default = JRequest::getWord('default', 'false');

		//check which toolbar edit and set default if  necessary
		if($toolbar === 'advanced')
		{
			if($default === 'true')
			{
				$param = str_replace(' ', '', 'Source,;,Save,NewPage,Preview,;,Templates,;,Cut,Copy,Paste,PasteText,PasteFromWord,;,Print,SpellChecker,Scayt,;,Undo,Redo,;,Find,Replace,;,SelectAll,RemoveFormat,;,/,Bold,Italic,Underline,Strike,;,Subscript,Superscript,;,NumberedList,BulletedList,;,Outdent,Indent,Blockquote,;,JustifyLeft,JustifyCenter,JustifyRight,JustifyBlock,;,BidiLtr,BidiRtl,;,Link,Unlink,Anchor,;,Image,Flash,Table,HorizontalRule,Smiley,SpecialChar,PageBreak,/,Styles,;,Format,;,Font,;,FontSize,TextColor,BGColor,;,Maximize,ShowBlocks,;,ReadMore,;,About');
				$this->assignRef('usedToolbars', $param);
			}
			else
			{
				$param = str_replace(' ', '', $params->get('Advanced_ToolBar'));
				$this->assignRef('usedToolbars', $param);
			}
		}
		else
		{
			if($default === 'true')
			{
				$param = str_replace(' ', '', 'Bold,Italic,Underline,Strike,;,NumberedList,BulletedList,;,Outdent,Indent,;,JustifyLeft,JustifyCenter,JustifyRight,JustifyBlock,;,Link,Unlink,Anchor,/,Styles,Format,;,Image,;,Subscript,Superscript,;,SpecialChar');
				$this->assignRef('usedToolbars', $param);
			}
			else
			{
				$param = str_replace(' ', '', $params->get('Basic_ToolBar'));
				$this->assignRef('usedToolbars', $param);
			}
		}

		$this->assignRef('toolbar', $toolbar);

		parent::display($tpl);
	}

	/**
	 *
	 * @return array|bool
	 *
	 * @since 5.0
	 */
	public function parseConfigIni()
	{
		$config = [];
		if(file_exists(JPATH_BASE . '/../plugins/editors/ckeditor/config.ini'))
		{
			$config = parse_ini_file(JPATH_BASE . '/../plugins/editors/ckeditor/config.ini', true);

			foreach($config AS $key => $plugin)
			{
				$tmp[ $plugin[ 'buttonName' ] ] = [
					'name'  => $plugin[ 'buttonName' ],
					'icon'  => $plugin[ 'image' ],
					'type'  => 'plugin',
					'title' => $plugin[ 'title' ] ? : $plugin[ 'buttonName' ],
					'row'   => 4
				];
			}

			$config = $tmp;
		}

		return $config;
	}

	/**
	 * @param $plugins
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	public function checkConfig($plugins)
	{
		$message = '';
		if(!$plugins)
		{
			return $message;
		}

		if(file_exists('../plugins/editors/ckeditor/config.js'))
		{
			$f    = fopen('../plugins/editors/ckeditor/config.js', 'rb');
			$file = fread($f, filesize('../plugins/editors/ckeditor/config.js'));
		}

		preg_match("#(config.extraPlugins.*=.*([\"']+.+[\"']+))#", $file, $matches);

		if(isset($matches[ 2 ]))
		{
			$tmp = str_replace([
				'"',
				"'"
			], '', $matches[ 2 ]);

			$tmp = explode(',', strtolower($tmp));

			foreach($plugins AS $plugin)
			{
				if(!in_array(strtolower($plugin[ 'name' ]), $tmp))
				{
					$message .= "Plugin: {$plugin['name']} is off. Turn it on in config.js file.<br />";
				}
			}
		}

		return $message;
	}
}