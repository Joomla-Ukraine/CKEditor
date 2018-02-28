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
defined( '_JEXEC' ) or die( 'Restricted access' );

class WFParameter
{
  var $data 	= null;

  var $key 	= null;

  function __construct($data = null, $path = '', $key = null)
  {

    $this->data = new StdClass();

    if ($data) {
      if (!is_object($data)) {
        $data = json_decode($data);

        if ($key) {
              $this->key = $key;
          $data = isset($data->$key) ? $data->$key : $data;
              }
      }

      $this->bind($this->data, $data);
    }
  }
  /**
   * Method to recursively bind data to a parent object.
   *
   * @param	object	$parent	The parent object on which to attach the data values.
   * @param	mixed	$data	An array or object of data to bind to the parent object.
   *
   * @return	void
   * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
   */
  public function bind(&$parent, $data)
  {
    // Ensure the input data is an array.
    if (is_object($data)) {
      $data = get_object_vars($data);
    } else {
      $data = (array) $data;
    }

    foreach ($data as $k => $v) {
      if (self::is_assoc($v) || is_object($v)) {
        $parent->$k = new stdClass();
        $this->bind($parent->$k, $v);
      } else {
        $parent->$k = $v;
      }
    }
  }

  /**
   * Get a parameter value.
   *
   * @param	string	Registry path (e.g. editor.width)
   * @param	mixed	Optional default value, returned if the internal value is null.
   * @return	mixed	Value of entry or null
   * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
   */
  public function get($path, $default = null)
  {
    // Initialise variables.
    $result = $default;

    // Explode the registry path into an array
    $nodes = explode('.', $path);

    // Initialize the current node to be the registry root.
    $node = $this->data;
    $found = false;
    // Traverse the registry to find the correct node for the result.
    foreach ($nodes as $n) {
      if (isset($node->$n)) {
        $node = $node->$n;
        $found = true;
      } else {
        $found = false;
        break;
      }
    }
    if ($found && $node !== null && $node !== '') {
      $result = $node;
    }

    return $result;
  }

  /**
   * Render all parameters
   *
   * @access	public
   * @param	string	The name of the control, or the default text area if a setup file is not found
   * @return	array	Aarray of all parameters, each as array Any array of the label, the form element and the tooltip
   * @since	1.5
   */
  function getParams($name = 'params', $group = '_default')
  {
    if (!isset($this->_xml[$group])) {
      return false;
    }
    $results = array();
    foreach ($this->_xml[$group]->children() as $param)  {
      $results[] = $this->getParam($param, $name);

      // get sub-parameters
      if ($param->attributes('parameters')) {

        // load manifest files for extensions
        $files = JFolder::files(JPATH_SITE.DS.$param->attributes('parameters'), '\.xml$', false, true);
        foreach ($files as $file) {
          $results[] = new WFParameter($this->data, $file, $this->key);
        }
      }
    }
    return $results;
  }

  /**
   * Render a parameter type
   *
   * @param	object	A param tag node
   * @param	string	The control name
   * @return	array	Any array of the label, the form element and the tooltip
   * @since	1.5
   */
  function getParam(&$node, $control_name = 'params', $group = '_default')
  {
    //get the type of the parameter
    $type = $node->attributes('type');

    $element =& $this->loadElement($type);

    // error happened
    if ($element === false) {
      $result = array();
      $result[0] = $node->attributes('name');
      $result[1] = JText::_('Element not defined for type').' = '.$type;
      $result[5] = $result[0];
      return $result;
    }

    //get value
    $value = $this->get($node->attributes('name'), $node->attributes('default'), $group);
    return $element->render($node, $value, $control_name);
  }

  function render($name = 'params', $group = '_default')
  {
    $params = $this->getParams($name, $group);
    $html 	= '<ul class="adminformlist">';

    foreach ($params as $item) {
      if (is_a($item, 'WFParameter')) {

        foreach ($item->getGroups() as $group => $num) {
          $id 	= $group;
          $class 	= '';

          $xml = $item->_xml[$group];

          if ($xml->attributes('parent')) {
            $parent = $xml->attributes('parent');
            $class 	= ' class="'. $parent .'"';
            $id		= $parent . '_' . $group;
          }

          $html .= '<div data-type="'. $group .'"'.$class.'>';
          $html .= '<h4>' . JText::_('JCE_' . strtoupper($id) . '_TITLE') . '</h4>';
          $html .= $item->render($name, $group);
          $html .= '</div>';
        }
      } else {
        $html .= '<li>' . $item[0] . $item[1];
      }
    }

    $html .= '</li></ul>';

    return $html;
  }

  /**
   * Check if a parent attribute is set. If it is, this parameter groups is included by the parent
   */
  function hasParent()
  {
    foreach ($this->_xml as $name => $group)  {
      if ($group->attributes('parent')) {
        return true;
      }
    }

    return false;
  }

  /**
   * Method to determine if an array is an associative array.
   *
   * @param	array		An array to test.
   * @return	boolean		True if the array is an associative array.
   * @link	http://www.php.net/manual/en/function.is-array.php#98305
   */
  private function is_assoc($array) {
      return (is_array($array) && (count($array)==0 || 0 !== count(array_diff_key($array, array_keys(array_keys($array))) )));
  }
}
