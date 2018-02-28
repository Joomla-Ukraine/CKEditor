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

defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

class ConfigController extends JControllerLegacy
{
	/**
	 * ConfigController constructor.
	 *
	 * @param array $default
	 */
	public function __construct($default = array())
	{
		parent::__construct($default);
		$this->registerTask('apply', 'save');
	}

	/**
	 * @param bool $cachable
	 * @param bool $urlparams
	 *
	 *
	 * @since 5.0
	 */
	public function display($cachable = false, $urlparams = false)
	{
		parent::display($cachable, $urlparams);
	}

	/**
	 *
	 *
	 * @since 5.0
	 */
	public function cancel()
	{
		$this->setRedirect(JRoute::_('index.php'));
	}

	/**
	 *
	 *
	 * @since 5.0
	 */
	public function save()
	{
		JRequest::checkToken() or die('Invalid Token');

		$db  = JFactory::getDBO();
		$row = JTable::getInstance('extension');

		$task = $this->getTask();

		$client = JRequest::getWord('client', 'site');

		$query = 'SELECT extension_id'
			. ' FROM #__extensions'
			. ' WHERE element = "ckeditor"';
		$db->setQuery($query);
		$id = $db->loadResult();

		$row->load(intval($id));
		$post = JRequest::get('post');

		$toolbar = $post[ 'toolbarGroup' ];

		array_shift($_POST);

		$post[ 'params' ] = $_POST;
		if($toolbar == 'advanced')
		{
			$post[ 'params' ][ 'Advanced_ToolBar' ] = $post[ 'rows' ];
		}
		else
		{
			$post[ 'params' ][ 'Basic_ToolBar' ] = $post[ 'rows' ];
		}

		$post[ 'params' ][ 'CKEditorCustomJs' ] = stripslashes($post[ 'params' ][ 'CKEditorCustomJs' ]);
		$post[ 'type' ]                         = 'plugin';

		if(!$row->bind($post))
		{
			JError::raiseError(500, $row->getError());
		}

		if(!$row->check())
		{
			JError::raiseError(500, $row->getError());
		}

		if(!$row->store())
		{
			JError::raiseError(500, $row->getError());
		}

		$row->checkin();

		if($client == 'admin')
		{
			$where = 'client_id=1';
		}
		else
		{
			$where = 'client_id=0';
		}

		$msg = JText::sprintf('SAVED');

		switch($task)
		{
			case 'apply':
				$this->setRedirect('index.php?option=com_ckeditor&type=config&client=' . $client, $msg);
				break;

			case 'save':
			default:
				$this->setRedirect('index.php', $msg);
				break;
		}
	}
}