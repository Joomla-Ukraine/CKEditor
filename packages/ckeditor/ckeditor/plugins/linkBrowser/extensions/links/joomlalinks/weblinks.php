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
defined('_CKE_EXT') or die('Restricted access');
class JoomlalinksWeblinks extends JObject
{

		var $_option = 'com_weblinks';
		/**
		 * Constructor activating the default information of the class
		 *
		 * @access	protected
		 */
		function __construct($options = array())
		{
		}

		/**
		 * Returns a reference to a editor object
		 *
		 * This method must be invoked as:
		 * 		<pre>  $browser = &JContentEditor::getInstance();</pre>
		 *
		 * @access	public
		 * @return	JCE  The editor object.
		 * @since	1.5
		 */
		function & getInstance()
		{
				static $instance;

				if (!is_object($instance)) {
						$instance = new JoomlalinksWeblinks();
				}
				return $instance;
		}
		function getOption()
		{
				return $this->_option;
		}
		function getList()
		{
				$wf =& WFEditorPlugin::getInstance();

				if ($wf->checkAccess('weblinks', '1')) {
						return '<li id="index.php?option=com_weblinks&view=categories"><div class="tree-row"><div class="tree-image"></div><span class="folder weblink nolink"><a href="javascript:;">'.JText::_('WEBLINKS').'</a></span></div></li>';
				}
		}


		public function getLinks($args)
	{
		$wf = WFEditorPlugin::getInstance();

		$items = array();

		switch ($args->view) {
			// Get all WebLink categories
			default:
			case 'categories':
				$categories = WFLinkBrowser::getCategory('com_weblinks');

				foreach ($categories as $category) {
					$itemid = WFLinkBrowser::getItemId('com_weblinks', array('categories' => null, 'category' => $category->id));

					$items[] = array(
						'id'		=>	'index.php?option=com_weblinks&view=category&id=' . $category->id . $itemid,
						'name'		=>	$category->title . ' / ' . $category->alias,
						'class'		=>	'folder weblink'
					);
				}
				break;
			// Get all links in the category
			case 'category':
				require_once(JPATH_SITE.DS.'components'.DS.'com_weblinks'.DS.'helpers'.DS.'route.php');


					$categories = WFLinkBrowser::getCategory('com_weblinks', $args->id);
					//var_dump($categories);
					if (count($categories)) {
						foreach ($categories as $category) {
							$children 	= WFLinkBrowser::getCategory('com_weblinks', $category->id);
							if ($children) {
								$id = 'index.php?option=com_weblinks&view=category&id=' . $category->id;
							} else {
								$itemid = WFLinkBrowser::getItemId('com_weblinks', array('categories' => null, 'category' => $category->slug));
								$id 	= 'index.php?option=com_weblinks&view=category&id='. $category->slug . $itemid;
							}

							$items[] = array(
								'id'		=>	$id,
								'name'		=>	$category->title . ' / ' . $category->alias,
								'class'		=>	'folder weblink'
							);
						}
					}


				$weblinks = self::_weblinks($args->id);

				foreach ($weblinks as $weblink) {
					$items[] = array(
						'id'		=>	WeblinksHelperRoute::getWeblinkRoute($weblink->id, $args->id),
						'name'		=>	$weblink->title . ' / ' . $weblink->alias,
						'class'		=>	'file'
					);
				}
				break;
		}
	//	var_dump($items);
		return $items;
	}
	function _weblinks($id)
	{
		$db		= JFactory::getDBO();
		$user	= JFactory::getUser();

		$where 	= '';

		if (method_exists('JUser', 'getAuthorisedViewLevels')) {
			$where .= ' AND state = 1';
			$where .= ' AND access IN ('.implode(',', $user->getAuthorisedViewLevels()).')';
		} else {
			$where .= ' AND published = 1';
		}

		$query = 'SELECT title, id, alias'
		. ' FROM #__weblinks'
		. ' WHERE catid = '.(int) $id
		. $where
		. ' ORDER BY title'
		;

	//	var_dump($query);
		$db->setQuery($query, 0);
		return $db->loadObjectList();
	}
}
?>
