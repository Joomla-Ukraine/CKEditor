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
class JoomlalinksContent extends JObject {

  var $_option = 'com_content';
  /**
  * Constructor activating the default information of the class
  *
  * @access	protected
  */
  function __construct($options = array()){
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
  function &getInstance()
  {
    static $instance;

    if ( !is_object( $instance ) ){
      $instance = new JoomlalinksContent();
    }
    return $instance;
  }

  public function getOption()
  {
    return $this->_option;
  }

    public function getList()
  {
    $wf =& WFEditorPlugin::getInstance();

    if ($wf->checkAccess('content', 1)) {
      return '<li id="index.php?option=com_content"><div class="tree-row"><div class="tree-image"></div><span class="folder content nolink"><a href="javascript:;">' . JText::_('CONTENT') . '</a></span></div></li>';
    }
  }

  public function getLinks($args)
  {
    $wf = WFEditorPlugin::getInstance();

    require_once(JPATH_SITE .DS. 'components' .DS. 'com_content' .DS. 'helpers' .DS. 'route.php');

    $items 		= array();
    $view		= isset($args->view) ? $args->view : '';

    switch ($view) {
      // get top-level sections / categories
      default:
        $sections = self::_getSection();

        foreach ($sections as $section) {
          $url = '';
          $id = ContentHelperRoute::getCategoryRoute($section->slug);
          if (strpos($id, 'index.php?Itemid=') !== false) {
            $url 	= $id;
            $id 	= 'index.php?option=com_content&view=category&id=' . $section->id;
          }

          $items[] = array(
            'url'		=>  $url,
            'id'		=>	$id,
            'name'		=>	$section->title,
            'class'		=>	'folder content'
          );
        }
        // Check Static/Uncategorized permissions
        break;
      // get categories in section or sub-categories (Joomla! 1.6+)
      case 'section':
        $articles = array();
          $categories = WFLinkBrowser::getCategory('com_content', $args->id);
          // get any articles in this category (in Joomla! 1.6+ a category can contain sub-categories and articles)
          $articles = self::_getArticles($args->id);


        foreach ($categories as $category) {
          $url 	= '';
          $id 	= ContentHelperRoute::getCategoryRoute($category->id, $args->id);

          if (strpos($id, 'index.php?Itemid=') !== false) {
            $url 	= $id;
            $id 	= 'index.php?option=com_content&view=category&id=' . $category->id;
          }

          $items[] = array(
            'url'		=> $url,
            'id'		=>	$id,
            'name'		=>	$category->title . ' / ' . $category->alias,
            'class'		=>	'folder content'
          );
        }

        if (!empty($articles)) {
          // output article links
          foreach ($articles as $article) {
            $id = ContentHelperRoute::getArticleRoute($article->slug, $article->catslug);
            $items[] = array(
              'id' 	=> $id,
              'name' 	=> $article->title . ' / ' . $article->alias,
              'class'	=> 'file'
            );
          }
        }

        break;
      // get articles and / or sub-categories
      case 'category':
        // get any articles in this category (in Joomla! 1.6+ a category can contain sub-categories and articles)
        $articles = self::_getArticles($args->id);


          // get sub-categories
          $categories = WFLinkBrowser::getCategory('com_content', $args->id);

          if (count($categories)) {
            foreach ($categories as $category) {
              // check for sub-categories
              $sub 	= WFLinkBrowser::getCategory('com_content', $category->id);

              $url 	= '';
              $id 	= ContentHelperRoute::getCategoryRoute($category->id, $args->id);

              // get sub-categories
              if (count($sub)) {
                $id 	= 'index.php?option=com_content&view=section&id=' . $category->id;
                $url 	= $id;
              // no sub-categories, get articles for category
              } else {
                // no com_content, might be link like index.php?ItemId=1
                if (strpos($id, 'index.php?Itemid=') !== false) {
                  $url 	= $id;
                  $id 	= 'index.php?option=com_content&view=category&id=' . $category->id;
                }
              }

              $items[] = array(
                'url'		=> 	$url,
                'id'		=>	$id,
                'name'		=>	$category->title . ' / ' . $category->alias,
                'class'		=>	'folder content'
              );
            }
          }


        // output article links
        foreach ($articles as $article) {
          $id = ContentHelperRoute::getArticleRoute($article->slug, $article->catslug);


          $items[] = array(
            'id' 	=> $id,
            'name' 	=> $article->title . ' / ' . $article->alias,
            'class'	=> 'file'
          );
        }

        break;
      case 'uncategorized':
        $statics = self::_getUncategorized();
        foreach ($statics as $static) {
          $items[] = array(
            'id' 	=> ContentHelperRoute::getArticleRoute($static->id),
            'name' 	=> 	$static->title . ' / ' . $static->alias,
            'class'	=>	'file'
          );
        }
        break;
    }
    return $items;
  }

  private function _getSection()
  {
    $db		= JFactory::getDBO();
    $user	= JFactory::getUser();

    if (isset($user->gid)) {
      $query = 'SELECT id, title, alias'
      . ' FROM #__sections'
      . ' WHERE published = 1'
      . ' AND access <= '.(int) $user->get('aid')
      //. ' GROUP BY id'
      . ' ORDER BY title'
      ;

      $db->setQuery($query);
      return $db->loadObjectList();
    } else {
      return WFLinkBrowser::getCategory('com_content');
    }
  }

  private function _getArticles($id)
  {
    $db			= JFactory::getDBO();
    $user		= JFactory::getUser();
    $wf 		= WFEditorPlugin::getInstance();

    if (isset($user->gid)) {
      $query = 'SELECT a.id AS slug, b.id AS catslug, a.alias, a.title AS title, u.id AS sectionid';
    } else {
      $query = 'SELECT a.id AS slug, b.id AS catslug, a.alias, a.title AS title';
    }

    if ($wf->getParam('joomlalinks.article_alias', 1) == 1) {
      $query .= ', CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug';
      $query .= ', CASE WHEN CHAR_LENGTH(b.alias) THEN CONCAT_WS(":", b.id, b.alias) ELSE b.id END as catslug';
    }

    $join 	= '';
    $where 	= '';

    if (isset($user->gid)) {
      $join 	.= ' INNER JOIN #__sections AS u ON u.id = a.sectionid';
      $where  .= ' AND a.access <= '.(int) $user->get('aid');
    } else {
      $where	.= ' AND a.access IN ('.implode(',', $user->getAuthorisedViewLevels()).')';
    }

    $query .= ' FROM #__content AS a'
    . ' INNER JOIN #__categories AS b ON b.id = '.(int) $id
    . $join
    . ' WHERE a.catid = '.(int) $id
    . ' AND a.state = 1'
    . $where
    . ' ORDER BY a.title'
    ;

    $db->setQuery($query, 0);
    return $db->loadObjectList();
  }

  private function _getUncategorized()
  {
    $db		= JFactory::getDBO();
    $user	= JFactory::getUser();

    $where 	= '';

    if (method_exists('JUser', 'getAuthorisedViewLevels')) {
      $where	.= ' AND access IN ('.implode(',', $user->getAuthorisedViewLevels()).')';
    } else {
      $where  .= ' AND access <= '.(int) $user->get('aid') . ' AND sectionid = 0';
    }

    $query = 'SELECT id, title, alias'
    . ' FROM #__content'
    . ' WHERE state = 1'
    . $where
    . ' AND catid = 0'
    . ' ORDER BY title'
    ;
    $db->setQuery($query, 0);
    return $db->loadObjectList();
  }
}
?>