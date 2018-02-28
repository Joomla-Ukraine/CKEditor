<?php
/*
* This plugin uses parts of JCE extension by Ryan Demmer.
* @copyright	Copyright (C) 2005 - 2011 Ryan Demmer. All rights reserved.
* @copyright	Copyright (C) 2003 - 2011, CKSource - Frederico Knabben. All rights reserved.
* @license		GNU/GPL
* CKEditor extension is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*/
// no direct access

echo __FILE__."<br>";
//defined('_CKE_EXT') or die('Restricted access');

//TODO fix this to more sense
require_once(dirname(__FILE__).DS.'../'.DS.'browser.php');

class WFLinkBrowser extends WFBrowserExtension
{
	/*
	*  @var varchar
	*/
	var $extensions = array();
	/**
	* Constructor activating the default information of the class
	*
	* @access	protected
	*/
	function __construct()
	{
		parent::__construct();

		$extensions = WFExtensions::loadExtensions(array(
			'types'	=>	array('links')
		));
				//TODO test this
		// Load all link extensions
		/*foreach($extensions['links'] as $link) {
			$this->extensions[] = &$this->getLinkExtension($link);
		}  */
	}
	/**
	 * Returns a reference to a plugin object
	 *
	 * This method must be invoked as:
	 * 		<pre>  $advlink = &AdvLink::getInstance();</pre>
	 *
	 * @access	public
	 * @return	JCE  The editor object.
	 * @since	1.5
	 */
	function &getInstance()
	{
		static $instance;

		if (!is_object($instance)) {
			$instance = new WFLinkBrowser();
		}

		return $instance;
	}

	function display()
	{
		$document =& WFDocument::getInstance();
		$document->addScript(array('tree'));

		$document->addStyleSheet(array('tree'));

		foreach ($this->extensions as $extension) {
			$extension->display();
		}
	}

	function &getLinkExtension($name)
	{
		static $links;

		if (!isset( $links )) {
			$links = array();
		}

		if (empty($links[$name])) {
			$classname 		= ucfirst($name);
			$links[$name]	= new $classname();
		}

		return $links[$name];
	}

		function getLinkBrowser()
	{
		$view = $this->getView('links');

		$list = '';

		foreach ($this->extensions as $extension) {
			if ($extension->isEnabled()) {
				$list .= $extension->getList();
			}
			}

		$view->assign('list', $list);
		$view->display();
	}

		function getLinks($args)
	{
			foreach ($this->extensions as $extension) {
					if (in_array($args->option, $extension->getOption())) {
							$items = $extension->getLinks($args);
					}
			}
			$array = array();
			$result = array();
			if (isset($items)) {
					foreach ($items as $item) {
							$array[] = array(
					'id'	=>	isset($item['id']) 	? WFEditor::xmlEncode($item['id']) 	: '',
								'url'	=>	isset($item['url']) ? WFEditor::xmlEncode($item['url']) : '',
									'name'	=>	WFEditor::xmlEncode($item['name']), 'class'=>$item['class']);
					}
					$result = array('folders'=>$array);
			}
			return $result;
	}

	/**
	 * Category function used by many extensions
	 *
	 * @access	public
	 * @return	Category list object.
	 * @since	1.5
	 */
	function getCategory($section, $parent = 1)
	{
		$db			=& JFactory::getDBO();
		$user		=& JFactory::getUser();
		$wf 		=& WFEditorPlugin::getInstance();

		$query = 'SELECT id AS slug, id AS id, title, alias';

		if ($wf->getSharedParam('category_alias', 1) == 1) {
			$query .= ', CASE WHEN CHAR_LENGTH(alias) THEN CONCAT_WS(":", id, alias) ELSE id END as slug';
		}
		// Joomla! 1.5 section / category
		if (JOOMLA15) {
			$where  = ' WHERE section = '.$db->Quote($section);
			$where .= ' AND access <= '.(int) $user->get('aid');
		} else {
			$where  = ' WHERE parent_id = '.(int) $parent;
			$where .= ' AND extension = '.$db->Quote($section);
			$where .= ' AND access IN ('.implode(',', $user->authorisedLevels()).')';

			if (!$wf->checkAccess('static', 1)) {
				$where .= ' AND path != '.$db->Quote('uncategorised');
			}
		}

		$query .= ' FROM #__categories'
		. $where
		. ' AND published = 1'
		. ' ORDER BY title'
		;
		$db->setQuery($query);

		return $db->loadObjectList();
	}
	/**
	 * (Attempt to) Get an Itemid
	 *
	 * @access	public
	 * @return	Category list object.
	 * @since	1.5
	 */
	function getItemId($component, $needles = array())
	{
		$match = null;

		require_once(JPATH_SITE.DS.'includes'.DS.'application.php');

		$tag 		= JOOMLA15 ? 'componentid' : 'component_id';

		$component 	=& JComponentHelper::getComponent($component);
		$menu 		=& JSite::getMenu();
		$items 		= $menu->getItems($tag, $component->id);

		if ($items) {
			foreach ($needles as $needle => $id) {
				foreach ($items as $item) {
					if ((@$item->query['view'] == $needle) && (@$item->query['id'] == $id)) {
						$match = $item->id;
						break;
					}
				}
				if (isset($match)) {
					break;
				}
			}
		}
		return $match ? '&Itemid='.$match : '';
	}
}
