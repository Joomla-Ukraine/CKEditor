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

defined('_JEXEC') or die ('Restricted access');

jimport('joomla.application.component.view');
jimport('joomla.form.form');
jimport('joomla.utilities.simplexml');

class ConfigViewConfig extends JViewLegacy
{
	public function display($tpl = null)
	{
		$db = JFactory::getDBO();

		if(!file_exists('components/com_ckeditor/config.xml'))
		{
			JError::raiseError(500, 'Form file missing.');
		}

		$xml = file_get_contents('components/com_ckeditor/config.xml');
		$xml = str_replace(array(
			'<config>',
			'</config>'
		), array(
			'<form>',
			'</form>'
		), $xml);

		$form = new jForm('adminFormCKEditor');
		$form->load($xml);

		$client = JRequest::getWord('client', 'site');
		$lists  = array();
		$row    = JTable::getInstance('extension');

		$query = 'SELECT extension_id'
			. ' FROM #__extensions'
			. ' WHERE element = "ckeditor"';
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
		//bind data to form
		$form->bind($formData);

		$params = $formData;
		//$params->addElementPath(JPATH_COMPONENT . '/elements');
		//	$this->assignRef('params', $params);
		$this->assignRef('params', $formData);

		$this->assignRef('client', $client);
		$this->assignRef('form', $form);

		$configIni   = $this->parseConfigIni();
		$configCheck = $this->checkConfig($configIni);
		$this->assignRef('message', $configCheck);
		//define all CKEditor toolbar buttons
		$toolBars = array(
			'Source'         => array(
				'name'  => 'Source',
				'icon'  => '../images/source.png',
				'type'  => 'command',
				'title' => 'Source',
				'row'   => 1
			),
			'Save'           => array(
				'name'  => 'Save',
				'icon'  => '../images/save.png',
				'type'  => 'command',
				'title' => 'Save',
				'row'   => 1
			),
			'NewPage'        => array(
				'name'  => 'NewPage',
				'icon'  => '../images/newPage.png',
				'type'  => 'command',
				'title' => 'New Page',
				'row'   => 1
			),
			'Preview'        => array(
				'name'  => 'Preview',
				'icon'  => '../images/preview.png',
				'type'  => 'command',
				'title' => 'Preview',
				'row'   => 1
			),
			'Templates'      => array(
				'name'  => 'Templates',
				'icon'  => '../images/templates.png',
				'type'  => 'command',
				'title' => 'Templates',
				'row'   => 1
			),
			'Cut'            => array(
				'name'  => 'Cut',
				'icon'  => '../images/cut.png',
				'type'  => 'command',
				'title' => 'Cut',
				'row'   => 1
			),
			'Copy'           => array(
				'name'  => 'Copy',
				'icon'  => '../images/copy.png',
				'type'  => 'command',
				'title' => 'Copy',
				'row'   => 1
			),
			'Paste'          => array(
				'name'  => 'Paste',
				'icon'  => '../images/paste.png',
				'type'  => 'command',
				'title' => 'Paste',
				'row'   => 1
			),
			'PasteText'      => array(
				'name'  => 'PasteText',
				'icon'  => '../images/pastePlainText.png',
				'type'  => 'command',
				'title' => 'Paste as plain text',
				'row'   => 1
			),
			'PasteFromWord'  => array(
				'name'  => 'PasteFromWord',
				'icon'  => '../images/pasteWord.png',
				'type'  => 'command',
				'title' => 'Paste from Word',
				'row'   => 1
			),
			'Print'          => array(
				'name'  => 'Print',
				'icon'  => '../images/print.png',
				'type'  => 'command',
				'title' => 'Print',
				'row'   => 1
			),
			'SpellChecker'   => array(
				'name'  => 'SpellChecker',
				'icon'  => '../images/checkSpelling.png',
				'type'  => 'command',
				'title' => 'Check Spelling',
				'row'   => 1
			),
			'Scayt'          => array(
				'name'  => 'Scayt',
				'icon'  => '../images/checkSpelling.png',
				'type'  => 'command',
				'title' => 'Spell Check As you Type',
				'row'   => 1
			),
			//TODO sprawdzic ta opcje
			'Undo'           => array(
				'name'  => 'Undo',
				'icon'  => '../images/undo.png',
				'type'  => 'command',
				'title' => 'Undo',
				'row'   => 1
			),
			'Redo'           => array(
				'name'  => 'Redo',
				'icon'  => '../images/redo.png',
				'type'  => 'command',
				'title' => 'Redo',
				'row'   => 1
			),
			'Find'           => array(
				'name'  => 'Find',
				'icon'  => '../images/find.png',
				'type'  => 'command',
				'title' => 'Find',
				'row'   => 1
			),
			'Replace'        => array(
				'name'  => 'Replace',
				'icon'  => '../images/replace.png',
				'type'  => 'command',
				'title' => 'Replace',
				'row'   => 1
			),
			'SelectAll'      => array(
				'name'  => 'SelectAll',
				'icon'  => '../images/selectAll.png',
				'type'  => 'command',
				'title' => 'Select All',
				'row'   => 1
			),
			'RemoveFormat'   => array(
				'name'  => 'RemoveFormat',
				'icon'  => '../images/removeFormat.png',
				'type'  => 'command',
				'title' => 'Remove Format',
				'row'   => 1
			),
			'Form'           => array(
				'name'  => 'Form',
				'icon'  => '../images/form.png',
				'type'  => 'command',
				'title' => 'Form',
				'row'   => 1
			),
			'Checkbox'       => array(
				'name'  => 'Checkbox',
				'icon'  => '../images/checkbox.png',
				'type'  => 'command',
				'title' => 'Checkbox',
				'row'   => 1
			),
			'Radio'          => array(
				'name'  => 'Radio',
				'icon'  => '../images/radioButton.png',
				'type'  => 'command',
				'title' => 'Radio Button',
				'row'   => 1
			),
			'TextField'      => array(
				'name'  => 'TextField',
				'icon'  => '../images/textField.png',
				'type'  => 'command',
				'title' => 'Text Field',
				'row'   => 1
			),
			'Textarea'       => array(
				'name'  => 'Textarea',
				'icon'  => '../images/textarea.png',
				'type'  => 'command',
				'title' => 'Textarea',
				'row'   => 1
			),
			'Select'         => array(
				'name'  => 'Select',
				'icon'  => '../images/selectionField.png',
				'type'  => 'command',
				'title' => 'Selection Field',
				'row'   => 1
			),
			'Button'         => array(
				'name'  => 'Button',
				'icon'  => '../images/button.png',
				'type'  => 'command',
				'title' => 'Button',
				'row'   => 1
			),
			'ImageButton'    => array(
				'name'  => 'ImageButton',
				'icon'  => '../images/imageButton.png',
				'type'  => 'command',
				'title' => 'Image Button',
				'row'   => 1
			),
			'HiddenField'    => array(
				'name'  => 'HiddenField',
				'icon'  => '../images/hiddenField.png',
				'type'  => 'command',
				'title' => 'Hidden Field',
				'row'   => 1
			),
			'Bold'           => array(
				'name'  => 'Bold',
				'icon'  => '../images/bold.png',
				'type'  => 'command',
				'title' => 'Bold',
				'row'   => 2
			),
			'Italic'         => array(
				'name'  => 'Italic',
				'icon'  => '../images/italic.png',
				'type'  => 'command',
				'title' => 'Italic',
				'row'   => 2
			),
			'Underline'      => array(
				'name'  => 'Underline',
				'icon'  => '../images/underline.png',
				'type'  => 'command',
				'title' => 'Underline',
				'row'   => 2
			),
			'Strike'         => array(
				'name'  => 'Strike',
				'icon'  => '../images/strike.png',
				'type'  => 'command',
				'title' => 'Strike Through',
				'row'   => 2
			),
			'Subscript'      => array(
				'name'  => 'Subscript',
				'icon'  => '../images/subscript.png',
				'type'  => 'command',
				'title' => 'Subscript',
				'row'   => 2
			),
			'Superscript'    => array(
				'name'  => 'Superscript',
				'icon'  => '../images/superscript.png',
				'type'  => 'command',
				'title' => 'Superscript',
				'row'   => 2
			),
			'NumberedList'   => array(
				'name'  => 'NumberedList',
				'icon'  => '../images/numberedList.png',
				'type'  => 'command',
				'title' => 'Insert/Remove Numbered List',
				'row'   => 2
			),
			'BulletedList'   => array(
				'name'  => 'BulletedList',
				'icon'  => '../images/bulletedList.png',
				'type'  => 'command',
				'title' => 'Insert/Remove Bulleted List',
				'row'   => 2
			),
			'Outdent'        => array(
				'name'  => 'Outdent',
				'icon'  => '../images/decreaseIndent.png',
				'type'  => 'command',
				'title' => 'Decrease Indent',
				'row'   => 2
			),
			'Indent'         => array(
				'name'  => 'Indent',
				'icon'  => '../images/increaseIndent.png',
				'type'  => 'command',
				'title' => 'Increase Indent',
				'row'   => 2
			),
			'Blockquote'     => array(
				'name'  => 'Blockquote',
				'icon'  => '../images/blockQuote.png',
				'type'  => 'command',
				'title' => 'Block Quote',
				'row'   => 2
			),
			'CreateDiv'      => array(
				'name'  => 'CreateDiv',
				'icon'  => '../images/createDivContainer.png',
				'type'  => 'command',
				'title' => 'Create Div Container',
				'row'   => 2
			),
			'JustifyLeft'    => array(
				'name'  => 'JustifyLeft',
				'icon'  => '../images/leftJustify.png',
				'type'  => 'command',
				'title' => 'Left Justify',
				'row'   => 2
			),
			'JustifyCenter'  => array(
				'name'  => 'JustifyCenter',
				'icon'  => '../images/centerJustify.png',
				'type'  => 'command',
				'title' => 'Center Justify',
				'row'   => 2
			),
			'JustifyRight'   => array(
				'name'  => 'JustifyRight',
				'icon'  => '../images/rightJustify.png',
				'type'  => 'command',
				'title' => 'Right Justify',
				'row'   => 2
			),
			'JustifyBlock'   => array(
				'name'  => 'JustifyBlock',
				'icon'  => '../images/blockJustify.png',
				'type'  => 'command',
				'title' => 'Block Justify',
				'row'   => 2
			),
			'BidiLtr'        => array(
				'name'  => 'BidiLtr',
				'icon'  => '../images/bidiLeft.png',
				'type'  => 'command',
				'title' => 'Text direction from left to right',
				'row'   => 2
			),
			'BidiRtl'        => array(
				'name'  => 'BidiRtl',
				'icon'  => '../images/bidiRight.png',
				'type'  => 'command',
				'title' => 'Text direction from right to left',
				'row'   => 2
			),
			'Link'           => array(
				'name'  => 'Link',
				'icon'  => '../images/link.png',
				'type'  => 'command',
				'title' => 'Link',
				'row'   => 2
			),
			'Unlink'         => array(
				'name'  => 'Unlink',
				'icon'  => '../images/unlink.png',
				'type'  => 'command',
				'title' => 'Unlink',
				'row'   => 2
			),
			'Anchor'         => array(
				'name'  => 'Anchor',
				'icon'  => '../images/anchor.png',
				'type'  => 'command',
				'title' => 'Anchor',
				'row'   => 2
			),
			'Image'          => array(
				'name'  => 'Image',
				'icon'  => '../images/image.png',
				'type'  => 'command',
				'title' => 'Image',
				'row'   => 2
			),
			'Flash'          => array(
				'name'  => 'Flash',
				'icon'  => '../images/flash.png',
				'type'  => 'command',
				'title' => 'Flash',
				'row'   => 2
			),
			'Table'          => array(
				'name'  => 'Table',
				'icon'  => '../images/table.png',
				'type'  => 'command',
				'title' => 'Table',
				'row'   => 2
			),
			'HorizontalRule' => array(
				'name'  => 'HorizontalRule',
				'icon'  => '../images/horizontalLine.png',
				'type'  => 'command',
				'title' => 'Insert Horizontal Line',
				'row'   => 2
			),
			'Smiley'         => array(
				'name'  => 'Smiley',
				'icon'  => '../images/smiley.png',
				'type'  => 'command',
				'title' => 'Smiley',
				'row'   => 2
			),
			'SpecialChar'    => array(
				'name'  => 'SpecialChar',
				'icon'  => '../images/specialCharacter.png',
				'type'  => 'command',
				'title' => 'Inseert Special Character',
				'row'   => 2
			),
			'PageBreak'      => array(
				'name'  => 'PageBreak',
				'icon'  => '../images/pageBreakPrinting.png',
				'type'  => 'command',
				'title' => 'Insert Page Break for Printing',
				'row'   => 2
			),
			'Styles'         => array(
				'name'  => 'Styles',
				'icon'  => '../images/styles.png',
				'type'  => 'command',
				'title' => 'Formatting Styles',
				'row'   => 3
			),
			'Format'         => array(
				'name'  => 'Format',
				'icon'  => '../images/format.png',
				'type'  => 'command',
				'title' => 'Paragraph Format',
				'row'   => 3
			),
			'Font'           => array(
				'name'  => 'Font',
				'icon'  => '../images/font.png',
				'type'  => 'command',
				'title' => 'Font Name',
				'row'   => 3
			),
			'FontSize'       => array(
				'name'  => 'FontSize',
				'icon'  => '../images/fontSize.png',
				'type'  => 'command',
				'title' => 'Font Size',
				'row'   => 3
			),
			'TextColor'      => array(
				'name'  => 'TextColor',
				'icon'  => '../images/textColor.png',
				'type'  => 'command',
				'title' => 'Text Color',
				'row'   => 3
			),
			'BGColor'        => array(
				'name'  => 'BGColor',
				'icon'  => '../images/backgroundColor.png',
				'type'  => 'command',
				'title' => 'Background Color',
				'row'   => 3
			),
			'Maximize'       => array(
				'name'  => 'Maximize',
				'icon'  => '../images/maximize.png',
				'type'  => 'command',
				'title' => 'Maximize',
				'row'   => 3
			),
			'ShowBlocks'     => array(
				'name'  => 'ShowBlocks',
				'icon'  => '../images/showBlocks.png',
				'type'  => 'command',
				'title' => 'Show Blocks',
				'row'   => 3
			),
			'ReadMore'       => array(
				'name'  => 'ReadMore',
				'icon'  => '../images/readmoreButton.png',
				'type'  => 'command',
				'title' => 'Read more',
				'row'   => 3
			),
			'Iframe'         => array(
				'name'  => 'Iframe',
				'icon'  => '../images/iframe.png',
				'type'  => 'command',
				'title' => 'IFrame',
				'row'   => 3
			),
			'About'          => array(
				'name'  => 'About',
				'icon'  => '../images/about.png',
				'type'  => 'command',
				'title' => 'About',
				'row'   => 3
			),
		);
		if($configIni)
		{
			$toolBars = array_merge($toolBars, $configIni);
		}
		$this->assignRef('allToolbars', $toolBars);

		//get variables from GET
		$toolbar = JRequest::getWord('cid', '');
		$default = JRequest::getWord('default', 'false');

		//check which toolbar edit and set default if  necessary
		if($toolbar == 'advanced')
		{
			if($default == 'true')
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
			if($default == 'true')
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

	public function parseConfigIni()
	{
		$config = array();
		if(file_exists(JPATH_BASE . '/../plugins/editors/ckeditor/config.ini'))
		{
			$config = parse_ini_file(JPATH_BASE . '/../plugins/editors/ckeditor/config.ini', true);
			foreach($config AS $key => $plugin)
			{
				$tmp[ $plugin[ 'buttonName' ] ] = array(
					'name'  => $plugin[ 'buttonName' ],
					'icon'  => $plugin[ 'image' ],
					'type'  => 'plugin',
					'title' => $plugin[ 'title' ] ? : $plugin[ 'buttonName' ],
					'row'   => 4
				);
			}
			$config = $tmp;
		}

		return $config;
	}

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
			$tmp = str_replace(array(
				'"',
				"'"
			), '', $matches[ 2 ]);

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