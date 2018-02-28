<?php

defined('_JEXEC') or die('Restricted access');

define('_JEXEC', 1);
define('JPATH_BASE', __DIR__ . "/../../../../../..");
define("MAX_SIZE", "500");

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

$mainframe = JFactory::getApplication('administrator');
$mainframe->initialise();

$editor_area = $_GET[ 'editor' ];

$user = JFactory::getUser();
$lang = JFactory::getLanguage();

$lang->load('plg_editors_ckeditor', JPATH_ADMINISTRATOR);

$cck_jumultithumbs = JPATH_BASE . '/plugins/cck_field_typo/jumultithumbs/jumultithumbs.php';
if(file_exists($cck_jumultithumbs))
{
	$mainframe = JFactory::getApplication('site');
	$js = 'window.parent.CKEDITOR.dialog.getCurrent().hide();';
	$tag = 'var tag = "{gallery images/" + folder + title + cssclass + "}";';
}
else
{
	$mainframe = JFactory::getApplication('administrator');
	$js = 'window.parent.SqueezeBox.close();';
	$tag = 'var tag = "{gallery " + folder + title + cssclass + "}";';
	$editor_area = preg_replace("#jform\[(.*?)\]#is", "jform_\\1", $editor_area);
}

$mainframe->initialise();

$language = mb_strtolower($lang->getTag());

$doc = JFactory::getDocument();

if($user->get('id') < 1)
{
	?>
	<!DOCTYPE html>
	<html lang="<?php echo $language; ?>">
	<head>
		<meta content="charset=utf-8" />
		<link href="../../../../../../../media/jui/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
	</head>
	<body>
	<dl id="system-message">
		<dt class="error"><?php echo JText::_('CK_JUMTG_NOTICE'); ?></dt>
		<dd class="message error fade">
			<ul>
				<li><?php echo JText::_('CK_JUMTG_LOGIN'); ?></li>
			</ul>
		</dd>
	</dl>
	</body>
	</html>
	<?php

	return;
}

$rootfolder = 'images/gallery/';

?>
<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title><?php echo JText::_('PLG_JUMULTITHUMB_GALLERY_INSERT_TAG'); ?></title>

	<link href="<?php echo JURI::root(true); ?>/../../../../../../../administrator/templates/isis/css/template.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo JURI::base(); ?>assets/jqueryFileTree.css" rel="stylesheet" type="text/css" />

	<script src="<?php echo JURI::base(true); ?>/../../../../../../../media/jui/js/jquery.min.js"></script>
	<script src="<?php echo JURI::base(); ?>assets/jqueryFileTree.js?v3"></script>

	<script>
        jQuery.noConflict();
        jQuery(document).ready(function () {
            jQuery('.selects').fileTree({
                root: '<?php echo $rootfolder; ?>',
                script: 'assets/jqueryFileTree.php',
                expandSpeed: 1000,
                collapseSpeed: 1000,
                multiFolder: false
            });
        });

        function insertJUGallery() {
            var folder = document.getElementById("folder").value;
            var title = document.getElementById("title").value;
            var cssclass = document.getElementById("cssclass").value;

            if (folder != '') {
                folder = "" + folder + "";
            }

            if (title == '' && cssclass != '') {
                title = "|";
            }
            else if (title != '') {
                title = "|" + title;
            }
            else {
                title == "";
            }

            if (cssclass != '') {
                cssclass = "|" + cssclass;
            }

            <?php echo $tag; ?>
            window.parent.jInsertEditorText(tag, '<?php echo $editor_area; ?>');
            <?php echo $js; ?>

            return false;
        }

        document.write(unescape('%3Cscript src="/plugins/editors/ckeditor/ckfinder/ckfinder.js""%3E%3C/script%3E'));

        window.onload = function () {
            Element.prototype.appendBefore = function (element) {
                element.parentNode.insertBefore(this, element);
            }, false;

            Element.prototype.appendAfter = function (element) {
                element.parentNode.insertBefore(this, element.nextSibling);
            }, false;

            var NewElement = document.createElement('span');
            NewElement.innerHTML = ' <a onclick="BrowseServer();" class="btn btn-primary"><?php echo JText::_('CK_JUMTG_UPLOAD_PHOTOS'); ?></a>';

            NewElement.appendAfter(document.getElementById('img_gall'));
        }

        function BrowseServer() {
            var finder = new CKFinder();
            finder.selectActionFunction = SetFileField;
            finder.popup();
        }

        function SetFileField(fileUrl) {
            var str = fileUrl;
            var str2 = str.replace("http://" + window.location.host + "/", "");
            document.getElementById('img_gall').value = str2;
        }
	</script>

	<style>
		body {
			background: transparent;
		}

		fieldset {
			margin: 5px 0 !important;
		}
	</style>
</head>
<body>
<form class="form-horizontal">
	<div class="control-group">
		<label class="control-label"><?php echo JText::_('CK_JUMTG_FOLDER'); ?>:</label>
		<div class="controls">
			<div class="selects"></div>
			<br>
			<input id="folder" class="folderurl uneditable-input" name="selectfolder" disabled="disabled" style="width:30%">
			<span id="img_gall"></span>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label"><?php echo JText::_('CK_JUMTG_GAL_TITLE'); ?>:</label>
		<div class="controls">
			<input type="text" id="title" name="title" size="60" value="" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label"><?php echo JText::_('CK_JUMTG_GAL_CSS'); ?>:</label>
		<div class="controls">
			<input type="text" id="cssclass" name="cssclass" size="60" />
		</div>
	</div>
	<div class="control-group">
		<div class="controls">
			<button onclick="insertJUGallery();" class="btn btn-success"><?php echo JText::_('CK_JUMTG_TAG'); ?></button>
		</div>
	</div>
</form>
</body>
</html>