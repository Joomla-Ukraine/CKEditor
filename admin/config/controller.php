<?php
/*
Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
This script is part of CKEditor's  link Browser extension for Joomla.
This plugin uses parts of JCE extension by Ryan Demmer
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.controller' );

/**
 * Plugins Component Controller
 *
 * @package		Joomla
 * @subpackage	Plugins
 * @since 1.5
 */
class ConfigController extends JControllerLegacy
{
	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );
		$this->registerTask( 'apply', 'save');
	}

	function display( $cachable = false, $urlparams = false )
	{
			parent::display($cachable , $urlparams );
	}

	function cancel( )
	{
		$this->setRedirect( JRoute::_( 'index.php') );
		//$this->setRedirect( JRoute::_( 'index.php?option=com_ckeditor&client='. $client, false ) );
	}

	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		$db 	=& JFactory::getDBO();
		$row 	=& JTable::getInstance('extension');

		$task 	= $this->getTask();

		$client = JRequest::getWord( 'client', 'site' );

		$query = 'SELECT extension_id'
		. ' FROM #__extensions'
		. ' WHERE element = "ckeditor"'
		;
		$db->setQuery( $query );
		$id = $db->loadResult();

		$row->load( intval( $id ) );
		$post = JRequest::get('post');

		$toolbar = $post['toolbarGroup'];
		array_shift($_POST);
		$post['params'] = $_POST;
		if ($toolbar == 'advanced')
		{
			$post['params']['Advanced_ToolBar'] = $post['rows'];
		}else{
			$post['params']['Basic_ToolBar'] = $post['rows'];
		}
		$post['params']['CKEditorCustomJs'] = stripslashes($post['params']['CKEditorCustomJs']);
		$post['type'] = 'plugin';

		if (!$row->bind($post)) {
			JError::raiseError(500, $row->getError() );
		}
		if (!$row->check()) {
			JError::raiseError(500, $row->getError() );
		}
		if (!$row->store()) {
			JError::raiseError(500, $row->getError() );
		}
		$row->checkin();

		if ($client == 'admin') {
			$where = "client_id=1";
		} else {
			$where = "client_id=0";
		}

		//$x = $row->reorder( 'folder = '.$db->Quote( $row->folder ).' AND ordering > -10000 AND ordering < 10000 AND ( '.$where.' )' );

		$msg = JText::sprintf( 'SAVED' );

		switch ( $task )
		{
			case 'apply':
				$this->setRedirect( 'index.php?option=com_ckeditor&type=config&client='. $client, $msg );
				break;

			case 'save':
			default:
				$this->setRedirect( 'index.php', $msg );
				break;
		}
	}
}