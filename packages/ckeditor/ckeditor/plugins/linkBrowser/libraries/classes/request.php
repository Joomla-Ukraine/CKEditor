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
defined( '_JEXEC' ) or die( 'Restricted access' );
class WFRequest extends JObject
{
	/**
	 * Constructor activating the default information of the class
	 *
	 * @access  protected
	 */
	function __construct()
	{
		parent::__construct();
	}
	/**
	 * Returns a reference to a plugin object
	 *
	 * This method must be invoked as:
	 *    <pre>  $advlink = &AdvLink::getInstance();</pre>
	 *
	 * @access  public
	 * @return  JCE  The editor object.
	 * @since 1.5
	 */
	function &getInstance()
	{
		static $instance;

		if (!is_object($instance)) {
			$instance = new WFRequest();
		}

		return $instance;
	}

	/**
	 * Setup an XHR function
	 *
	 * @access public
	 * @param array   An array containing the function and object
	 */
	function setXHR($function)
	{
		$object = new StdClass();

		if (is_array($function)) {
			$name 	= $function[1];
			$ref 	= $function[0];

			$object->fn 	= $name;
			$object->ref 	= $ref;

			$this->request[$name] = $object;
		} else {
			$object->fn = $function;
			$this->request[$function] = $object;
		}
	}

	function checkQuery($query)
	{
		if (is_string($query)) {
			$query = array($query);
		}

		// check for null byte
		foreach ($query as $key => $value) {
			if (is_array($value)) {
				return self::checkQuery($value);
			}

			if (is_array($key)) {
				return self::checkQuery($key);
			}

			if (strpos($key, '\u0000') !== false || strpos($value, '\u0000') !== false) {
				JError::raiseError(403, 'RESTRICTED');
			}
		}
	}

	/**
	 * Process an ajax call and return result
	 *
	 * @access public
	 * @return string
	 */
	function processXHR($array = false)
	{
		$json   = JRequest::getVar('json', '', 'POST', 'STRING', 2);
		$method = JRequest::getVar('method', '');

		if ($method == 'form' || $json) {
			// Check for request forgeries
			//WFToken::checkToken() or die('INVALID TOKEN');

			$output = array(
								"result" 	=> null,
				"text"		=> null,
				"error"		=> null
			);
			JError::setErrorHandling(E_ALL, 'callback', array('WFUtility', 'raiseError'));

			$fn   = JRequest::getVar('action');
			$args = array();

			if (!$method && $json) {
				$json 			= json_decode($json);
				$fn   			= isset($json->fn) ? $json->fn : JError::raiseError(500, 'NO FUNCTION CALL');
				$args 			= isset($json->args) ? $json->args : array();
			}

			// check query
			$this->checkQuery($args);

			// call function
			if (array_key_exists($fn, $this->request)) {
				$method = $this->request[$fn];

				// set default function call
				$call = null;

				if (!isset($method->ref)) {
					$call = $method->fn;
					if (!function_exists($call)) {
						JError::raiseError(500, 'FUNCTION "'. $call . '" DOES NOT EXIST');
					}
				} else {
					if (!method_exists($method->ref, $method->fn)) {
						JError::raiseError(500, 'METHOD "'. $method->ref . '::' . $method->fn . '" DOES NOT EXIST');
					}
					$call = array($method->ref, $method->fn);
				}

				if (!$call) {
					JError::raiseError(500, 'NO FUNCTION CALL');
				}

				if (!is_array($args)) {
					$result = call_user_func($call, $args);
				} else {
					$result = call_user_func_array($call, $args);
				}

			} else {
				if ($fn) {
					JError::raiseError(500, 'FUNCTION "'. addslashes($fn) . '" NOT REGISTERED');
				} else {
					JError::raiseError(500, 'NO FUNCTION CALL');
				}
			}

			$output = array(
							"result" => $result
			);

			// set output headers
			header('Content-Type: text/json');
			header('Content-Encoding: UTF-8');
			header("Expires: Mon, 4 April 1984 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");

			exit(json_encode($output));
		}
	}
}
?>