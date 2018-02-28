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


//defined('_CKE_EXT') or die('Restricted access');
//defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * JCE class
 *
 * @static
 * @package		JCE
 * @since	1.5
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
class WFEditor extends JObject
{
	/**
	 * @var varchar
	 */
	var $version = '2.0';

	/**
	 *  @var boolean
	 */
	var $_debug = false;
	/**
	 * Constructor activating the default information of the class
	 *
	 * @access	protected
	 */
	function __construct($config = array())
	{
		$this->setProperties($config);
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

		if (!is_object($instance)) {
			$instance = new WFEditor();
		}
		return $instance;
	}
	/**
	 * Get the current version
	 * @return Version
	 */
	function getVersion()
	{
		return $this->version;
	}

	/**
	 * Get the Super Administrator status
	 *
	 * Determine whether the user is a Super Administrator
	 *
	 * @return boolean
	 */
	function isSuperAdmin()
	{
		$user =& JFactory::getUser();

		if (JOOMLA15) {
			return (strtolower($user->usertype) == 'superadministrator' || strtolower($user->usertype) == 'super administrator' || $user->gid == 25) ? true : false;
		}
		return false;
	}

	/**
	 * Get an appropriate editor profile
	 * @return $profile Object
	 */
	public function getProfile()
	{
		static $profile;

		if (!is_object($profile)) {
			$mainframe = &JFactory::getApplication();

			$db		=& JFactory::getDBO();
			$user	=& JFactory::getUser();
			$option = $this->getComponentOption();

			$query = 'SELECT *'
			. ' FROM #__jce_profiles'
			. ' WHERE published = 1'
			. ' ORDER BY ordering ASC'
			;
			$db->setQuery($query);
			$profiles = $db->loadObjectList();

			if ($option == 'com_jce') {
				$cid = JRequest::getVar('cid');

				if ($cid) {
					$component 	= JCEVersionHelper::getComponent($cid);
					$option 	= $component->option;
				} else {
					$option = '';
				}
			}

			$area = $mainframe->isAdmin() ? 2 : 1;

			foreach ($profiles as $item) {
				// Set area default as Front-end / Back-end
				if (!isset($item->area) || $item->area == '') {
					$item->area = 0;
				}

				if ($item->area == $area || $item->area == 0) {
					$components = in_array($option, explode(',', $item->components));
					// Check user
					if (in_array($user->id, explode(',', $item->users))) {
						if ($item->components) {
							if ($components) {
								$profile = $item;
								return $profile;
							}
						} else {
							$profile = $item;
							return $profile;
						}
					}

						$keys 	= array_values($user->groups);


					if ($item->types) {
						$groups = array_intersect($keys, explode(',', $item->types));

						if (!empty($groups)) {
							// Check components
							if ($item->components) {
								if ($components) {
									$profile = $item;
									return $profile;
								}
							} else {
								$profile = $item;
								return $profile;
							}
						}
					}

					// Check components only
					if ($item->components && $components) {
						$profile = $item;
						return $profile;
					}
				}
			}

			return null;
		}

		return $profile;
	}

	function getComponentOption()
	{
		$option = JRequest::getVar('option', '');

		switch ($option) {
			case 'com_section':
				$option = 'com_content';
				break;
			case 'com_categories':
				$section = JRequest::getVar('section');
				if ($section) {
					$option = $section;
				}
				break;
		}

		return $option;
	}

	public function getParams()
	{
		static $params;

		if (!is_object($params)) {
		//TODO move that file

			require_once('parameter.php');

//			$profile 	= $this->getProfile();
			$params 	= new WFParameter(/*$profile->params*/);
		}

		return $params;
	}

	/**
	 * Remove linebreaks and carriage returns from a parameter value
	 *
	 * @return The modified value
	 * @param string	The parameter value
	 */
	function cleanParam($param)
	{
		if (is_array($param)) {
			$param = implode('|', $param);
		}
		return trim(preg_replace('/\n|\r|\t(\r\n)[\s]+/', '', $param));
	}

	public function getParam($key, $fallback = '', $default = '')
	{
		$params = self::getParams();

		if (strpos($key, '.') === false && strpos($key, '_') !== false) {
			$key = preg_replace('#([^_]+)_#', '$1.', $key);
		}

		// get a parameter
		$param 	= $params->get($key, $fallback);

		if (is_array($param)) {
			$param = implode('|', $param);
		}

		$value = self::cleanParam($param);

		return ($value == $default) ? '' : $value;
	}

	function getSharedParam($key, $default = '')
	{
		$plugin = JRequest::getVar('plugin');

		if ($plugin) {
			$pp = $this->getParam($plugin . '.' . $key);

			if ($pp !== '') {
				return $pp;
			}
		}

		return $this->getParam('editor.' . $key, $default);
	}

	/**
	 * Load a language file
	 *
	 * @param string $prefix Language prefix
	 * @param object $path[optional] Base path
	 */
	function loadLanguage($prefix, $path = JPATH_SITE)
	{
		$language =& JFactory::getLanguage();
		$language->load($prefix, $path);
	}

	/**
	 * Load the language files for the current plugin
	 */
	function loadLanguages()
	{
		$this->loadLanguage('com_jce');
		$this->loadPluginLanguage();
	}

	/**
	 * Return the curernt language code
	 *
	 * @access public
	 * @return language code
	 */
	function getLanguageDir()
	{
		$language =& JFactory::getLanguage();
		return $language->isRTL() ? 'rtl' : 'ltr';
	}

	/**
	 * Return the curernt language code
	 *
	 * @access public
	 * @return language code
	 */
	function getLanguageTag()
	{
		$language =& JFactory::getLanguage();
		if ($language->isRTL()) {
			return 'en-GB';
		}
		return $language->getTag();
	}

	/**
	 * Return the curernt language code
	 *
	 * @access public
	 * @return language code
	 */
	function getLanguage()
	{
		$tag = $this->getLanguageTag();
		if (file_exists(JPATH_SITE .DS. 'language' .DS. $tag .DS. $tag .'.com_jce.xml')) {
			return substr($tag, 0, strpos($tag, '-'));
		}
		return 'en';
	}

	/**
	 * Named wrapper to check access to a feature
	 *
	 * @access 			public
	 * @param string	The feature to check, eg: upload
	 * @param string	The defalt value
	 * @return 			string
	 */
	function checkUser()
	{
		return $this->getProfile();
	}
	/**
	 * XML encode a string.
	 *
	 * @access	public
	 * @param 	string	String to encode
	 * @return 	string	Encoded string
	 */
	function xmlEncode($string)
	{
		return preg_replace(array('/&/', '/</', '/>/', '/\'/', '/"/'), array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;'), $string);
	}
	/**
	 * XML decode a string.
	 *
	 * @access	public
	 * @param 	string	String to decode
	 * @return 	string	Decoded string
	 */
	function xmlDecode($string)
	{
		return preg_replace(array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;'), array('/&/', '/</', '/>/', '/\'/', '/"/'), $string);
	}

	function log($file, $msg)
	{
		jimport('joomla.error.log');
		$log = &JLog::getInstance($file);
		$log->addEntry(array('comment' => 'LOG: '.$msg));
	}
}
?>