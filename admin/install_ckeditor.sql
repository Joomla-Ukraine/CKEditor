DELETE FROM
  #__extensions
WHERE
  element = 'ckeditor'
  AND
  name = 'Editor - CKEditor';

INSERT INTO
  #__extensions (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`)
VALUES (
  NULL,
  'Editor - CKEditor',
  'plugin',
  'ckeditor',
  'editors',
  0,
  1,
  1,
  0,
  '',
  '{"usergroup":["6","7","8"],"toolbar":"Advanced","toolbar_frontEnd":"Basic","skin":"moono","CKEditorWidth":"","CKEditorHeight":"500","CKEditorAutoGrow":"0","CKEditorTableResize":"1","jumtgallery":"images\/gallery\/","Basic_ToolBar":"aychecker,AutoCorrect,;,Cut,Paste,PasteText,RemoveFormat,PasteFromWord,;,Bold,Italic,;,NumberedList,BulletedList,Smiley,;,Styles,Format,;,Link,Unlink,Anchor,;,Image,Youtube,jusocial,;,ReadMore","Advanced_ToolBar":"Source,Maximize,aychecker,ShowBlocks,;,Cut,Copy,Paste,PasteText,PasteFromWord,RemoveFormat,AutoCorrect,;,Blockquote,Bold,Italic,;,NumberedList,BulletedList,Superscript,Subscript,;,JustifyLeft,JustifyCenter,JustifyRight,;,Undo,Redo,;,Find,Replace,\/,Link,Unlink,Anchor,;,Styles,Format,;,Image,Youtube,Video,jusocial,;,Table,ReadMore","CKEditorAutoLang":"1","language":"en","CKEditorLangDir":"0","Color":"","enterMode":"1","shiftEnterMode":"2","templateCss":"0","style":"","template":"","css":"","DivBased":"0","LinkBrowserUrl":"0","Scayt":"0","Entities":"0","ACF":"0","msword":"1","outlineblocks":"1","plaintext":"1","keystrokes":"1","CKEditorIndent":"0","CKEditorBreakBeforeOpener":"0","CKEditorBreakAfterOpener":"0","CKEditorBreakBeforeCloser":"0","CKEditorBreakAfterCloser":"0","CKEditorPre":"0","CKEditorCustomJs":"CKEDITOR.config.templates_replaceContent = false;\\r\\n\\r\\nCKEDITOR.config.image_prefillDimensions = false;\\r\\nCKEDITOR.config.image2_prefillDimensions = false;\\r\\nCKEDITOR.config.image2_alignClasses = [ ''align-left'', ''align-center'', ''align-right'' ];","ckfinder":"1","CKFinderPathType":"0","username_access":["6","7","3","4","5","8"],"CKFinderSaveFiles":"files","CKFinderSaveImages":"images","CKFinderSaveFlash":"files","CKFinderSaveThumbs":"cache\/_thumbs","CKFinderResourceFiles":"","CKFinderResourceImages":"","CKFinderResourceFlash":"","CKFinderMaxFilesSize":"8M","CKFinderMaxImagesSize":"8M","CKFinderMaxFlashSize":"8M","CKFinderMaxImageWidth":"1200","CKFinderMaxImageHeight":"1200","CKFinderMaxThumbnailWidth":"180","CKFinderMaxThumbnailHeight":"180","CKFinderFileEdit":"1","CKFinderImageResize":"1","CKFinderZip":"1","CKFinderSettingsChmod":"0755","PackageLicenseName":"","PackageLicenseKey":"","option":"com_ckeditor","client":"site","type":"config","task":"apply","rows":"Source,Maximize,aychecker,ShowBlocks,;,Cut,Copy,Paste,PasteText,PasteFromWord,RemoveFormat,AutoCorrect,;,Blockquote,Bold,Italic,;,NumberedList,BulletedList,Superscript,Subscript,;,JustifyLeft,JustifyCenter,JustifyRight,;,Undo,Redo,;,Find,Replace,\/,Link,Unlink,Anchor,;,Styles,Format,;,Image,Youtube,Video,jusocial,;,Table,ReadMore","toolbarGroup":"advanced"}',
  '',
  '',
  0,
  '0000-00-00 12:00:00',
  0,
  0
);