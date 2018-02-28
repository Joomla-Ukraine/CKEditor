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
defined('_CKE_EXT') or die('Restricted access');
?>
<div id="link-browser">
	<fieldset>
		<legend><?php echo JText::_('LINK_BROWSER');?></legend>
		<div id="link-options" class="tree">
			<ul class="root"><?php echo $this->list;?></ul>
		</div>
	</fieldset>
</div>
