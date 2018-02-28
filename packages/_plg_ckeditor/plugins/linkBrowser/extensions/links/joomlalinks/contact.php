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
class JoomlalinksContact extends JObject
{

		var $_option = 'com_contact';
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
						$instance = new JoomlalinksContact();
				}
				return $instance;
		}
		public function getOption()
		{
				return $this->_option;
		}
		public function getList()
		{
				//Reference to JConentEditor (JCE) instance
				$wf =& WFEditorPlugin::getInstance();

				if ($wf->checkAccess('contacts', '1')) {
						return '<li id="index.php?option=com_contact"><div class="tree-row"><div class="tree-image"></div><span class="folder contact nolink"><a href="javascript:;">'.JText::_('CONTACTS').'</a></span></div></li>';
				}
		}
		function getLinks($args)
	{
		$items 	= array();
		$view	= isset($args->view) ? $args->view : '';
		switch ($view) {
			default:

				$categories = WFLinkBrowser::getCategory('com_contact');


				foreach ($categories as $category) {
					$itemid 	= WFLinkBrowser::getItemId('com_contact', array('categories' => null, 'category' => $category->slug));
					$items[] 	= array(
						'id' 	=> 'index.php?option=com_contact&view=category&catid='. $category->slug . $itemid,
						'name' 	=> $category->title . ' / ' . $category->alias,
						'class'	=> 'folder contact'
					);
				}
				break;
			case 'category':

					$categories = WFLinkBrowser::getCategory('com_contact', $args->catid);

					foreach ($categories as $category) {
						$children = WFLinkBrowser::getCategory('com_contact', $category->id);

						if ($children) {
							$id = 'index.php?option=com_contact&view=category&catid=' . $category->id;
						} else {
							$itemid = WFLinkBrowser::getItemId('com_contact', array('categories' => null, 'category' => $category->slug));
							$id 	= 'index.php?option=com_contact&view=category&catid='. $category->slug . $itemid;
						}

						$items[] = array(
							'id'		=>	$id,
							'name'		=>	$category->title . ' / ' . $category->alias,
							'class'		=>	'folder content'
						);
					}


				$contacts = self::_contacts($args->catid);

				foreach ($contacts as $contact) {
					$catid 		= $args->catid ? '&catid='. $args->catid : '';
					$itemid 	= WFLinkBrowser::getItemId('com_contact', array('categories' => null, 'category' => $catid));
					$items[] 	= array(
						'id' 	=> 'index.php?option=com_contact&view=contact'. $catid .'&id='.$contact->id . $itemid. '-' .$contact->alias,
						'name' 	=> $contact->name . ' / ' . $contact->alias,
						'class'	=> 'file'
					);
				}
				break;
		}
		return $items;
	}
	function _contacts($id)
	{
		$db		= JFactory::getDBO();
		$user	= JFactory::getUser();

		$where 	= '';

		if (method_exists('JUser', 'getAuthorisedViewLevels')) {
			$where	.= ' AND access IN ('.implode(',', $user->getAuthorisedViewLevels()).')';
		} else {
			$where  .= ' AND access <= '.(int) $user->get('aid');
		}

		$query	= 'SELECT id, name, alias'
		. ' FROM #__contact_details'
		. ' WHERE catid = '.(int) $id
		. ' AND published = 1'
		. $where
		. ' ORDER BY name'
		;
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}
?>