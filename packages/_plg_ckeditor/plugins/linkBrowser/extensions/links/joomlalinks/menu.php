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
class JoomlalinksMenu extends JObject
{

    var $_option = 'com_menu';
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
            $instance = new JoomlalinksMenu();
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

        if ($wf->checkAccess('menu', '1')) {
            return '<li id="index.php?option=com_menu"><div class="tree-row"><div class="tree-image"></div><span class="folder menu nolink"><a href="javascript:;">'.JText::_('MENU').'</a></span></div></li>';
        }
    }
  function getLinks($args)
  {
    $items 	= array();
    $view	= isset($args->view) ? $args->view : '';
    switch ($view) {
      // create top-level (non-linkable) menu types
      default:
        $types = self::_types();
        foreach ($types as $type) {
          $items[] = array(
            'id'		=>	'index.php?option=com_menu&view=menu&type=' . $type->id,
            'name'		=>	$type->title,
            'class'		=>	'folder menu nolink'
          );
        }
        break;
      // get menus and sub-menus
      case 'menu':
        $type 	= isset($args->type) ? $args->type : 0;
        $id 	= $type ? 0 : $args->id;

        $menus = self::_menu($id, $type);

        foreach ($menus as $menu) {
          $link = $menu->link;

          switch ($menu->type) {
            case 'separator':
              // No further action needed.
              continue;

            case 'url':
              if ((strpos($link, 'index.php?') !== false) && (strpos($link, 'Itemid=') === false)) {
                // If this is an internal Joomla link, ensure the Itemid is set.
                $link .= '&Itemid='.$menu->id;
              }
              break;

            case 'alias':
              $params = new JParameter($menu->params);

              // If this is an alias use the item id stored in the parameters to make the link.
              $link = 'index.php?Itemid='.$params->get('aliasoptions');
              break;

            default:
              $link .= '&Itemid='.$menu->id;
              break;
          }

          $children 	= self::_children($menu->id);
          $title 		= isset($menu->name) ? $menu->name : $menu->title;

          if (strpos($link, 'index.php?option') !== false && strpos($link, 'Itemid=') === false) {
            $link = $menu->link . '&Itemid=' . $menu->id;
          }

          $items[] = array(
            'id'		=>	$children ? 'index.php?option=com_menu&view=menu&id=' . $menu->id : $link,
            'url'		=>	$link,
            'name'		=>	$title . ' / ' . $menu->alias,
            'class'		=>	$children ? 'folder menu' : 'file'
          );
        }
        break;
      // get menu items
      case 'submenu':
        $menus = self::_menu($args->id);
        foreach ($menus as $menu) {
          if ($menu->type == 'menulink') {
            //$menu = AdvlinkMenu::_alias($menu->id);
          }

          $link = $menu->link;
          $title 	= isset($menu->name) ? $menu->name : $menu->title;

          if (strpos($link, 'index.php?option') !== false && strpos($link, 'Itemid=') === false) {
            $link = $menu->link . '&Itemid=' . $menu->id;
          }

          $items[] = array(
            'id'		=>	$link,
            'name'		=>	$title . ' / ' . $menu->alias,
            'class'		=>	$children ? 'folder menu' : 'file'
          );
        }
        break;
    }
    return $items;
  }
  function _types()
  {
    $db	= JFactory::getDBO();

    $query = 'SELECT *'
    . ' FROM #__menu_types'
    ;

    $db->setQuery($query, 0);
    return $db->loadObjectList();
  }
  function _alias($id)
  {
    $db		= JFactory::getDBO();
    $user	= JFactory::getUser();

    $query = 'SELECT params'
    . ' FROM #__menu'
    . ' WHERE id = '.(int) $id
    ;

    $db->setQuery($query, 0);
    $params = new JParameter($db->loadResult());

    $query = 'SELECT id, name, link, alias'
    . ' FROM #__menu'
    . ' WHERE published = 1'
    . ' AND id = '.(int) $params->get('menu_item')
    . ' AND access <= '.(int) $user->get('aid')
    . ' ORDER BY name'
    ;

    $db->setQuery($query, 0);
    return $db->loadObject();
  }
  function _children($id)
  {
    $db		= JFactory::getDBO();
    $user	= JFactory::getUser();

    $where  = '';


      $groups	= implode(',', $user->getAuthorisedViewLevels());
      $where .= ' AND menutype != '.$db->Quote('_adminmenu');
      $where .= ' AND access IN ('.$groups.')';
      if ($id) {
        $where .= ' AND parent_id = '.(int) $id;
      }


    $query = 'SELECT COUNT(id)'
    . ' FROM #__menu'
    . ' WHERE published = 1'
    . $where
    ;

    $db->setQuery($query, 0);
    return $db->loadResult();
  }
  function _menu($parent = 0, $type = 0)
  {
    $db		= JFactory::getDBO();
    $user	= JFactory::getUser();

    $join   = '';
    $where  = ' WHERE m.published = 1';
    $order  = '';

    if ($type) {
      $join 	.= ' INNER JOIN #__menu_types AS s ON s.id = '. intval($type);
      $where 	.= ' AND m.menutype = s.menutype';
    }

    // Joomla! 1.6+
    if (method_exists('JUser', 'getAuthorisedViewLevels')) {
      $groups	= implode(',', $user->getAuthorisedViewLevels());
      $where 	.= ' AND m.access IN ('.$groups.')';

      if (!$parent) {
        $parent = 1;
      }
      $where .= ' AND m.parent_id = '.(int) $parent;

      $order  .= ' ORDER BY m.lft asc';
    }

    $query = 'SELECT m.*'
    . ' FROM #__menu AS m'
    . $join
    . $where
    . $order
    ;

    $db->setQuery($query, 0);
    return $db->loadObjectList();
  }
}
?>
