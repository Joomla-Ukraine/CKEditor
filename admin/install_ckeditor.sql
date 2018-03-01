
DELETE FROM #__extensions WHERE element = 'ckeditor' AND name = 'Editor - CKEditor';




INSERT INTO #__extensions (`extension_id`,`name`,`type`,`element`,`folder`,`client_id`,`enabled`,`access`,`protected`,`manifest_cache`,`params`,`custom_data`,`system_data`,`checked_out`,`checked_out_time`,`ordering`,`state`)
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
			'{"toolbar":"Advanced","toolbar_frontEnd":"Basic","skin":"moono","CKEditorWidth":"","CKEditorHeight":"500","CKEditorAutoGrow":"0","CKEditorTableResize":"1","Basic_ToolBar":"Bold,Italic,;,NumberedList,BulletedList,;,Subscript,Superscript,;,JustifyLeft,JustifyCenter,JustifyRight,;,Link,Unlink,Anchor,\\/,Cut,Paste,PasteFromWord,PasteText,RemoveFormat,AutoCorrect,;,Styles,Format,;,Image,Youtube","Advanced_ToolBar":"Source,Maximize,;,Cut,Copy,Paste,PasteText,PasteFromWord,RemoveFormat,AutoCorrect,;,Undo,Redo,;,Find,Replace,;,ShowBlocks,SelectAll,\\/,Blockquote,Bold,Italic,;,Subscript,Superscript,NumberedList,BulletedList,;,JustifyLeft,JustifyCenter,JustifyRight,;,Table,\\/,Link,Unlink,Anchor,Styles,Format,;,Image,Iframe,Youtube,;,ReadMore","CKEditorAutoLang":"1","language":"en","CKEditorLangDir":"0","Color":"","enterMode":"1","shiftEnterMode":"2","templateCss":"0","style":"","template":"","css":"","DivBased":"0","LinkBrowserUrl":"0","Scayt":"0","Entities":"0","ACF":"0","CKEditorIndent":"0","CKEditorBreakBeforeOpener":"0","CKEditorBreakAfterOpener":"0","CKEditorBreakBeforeCloser":"0","CKEditorBreakAfterCloser":"0","CKEditorPre":"0","CKEditorCustomJs":"CKEDITOR.config.bodyClass = ''css-editor'';\r\nCKEDITOR.config.disableNativeSpellChecker = false;\r\nCKEDITOR.config.format_tags = ''p;h2;h3;h4;h5;h6'';\r\n\r\nCKEDITOR.config.ignoreEmptyParagraph = true;\r\nCKEDITOR.config.templates_replaceContent = false;\r\n\r\nCKEDITOR.config.image_prefillDimensions = false;\r\n\r\nCKEDITOR.config.image2_prefillDimensions = false;\r\nCKEDITOR.config.image2_alignClasses = [ ''align-left'', ''align-center'', ''align-right'' ];\r\n\r\nCKEDITOR.config.pasteFromWordNumberedHeadingToList = true;\r\nCKEDITOR.config.pasteFromWordPromptCleanup = true;\r\nCKEDITOR.config.pasteFromWordRemoveFontStyles = true;\r\nCKEDITOR.config.pasteFromWordRemoveStyles = true;\r\nCKEDITOR.config.startupOutlineBlocks = true;\r\nCKEDITOR.config.forcePasteAsPlainText = true;\r\n\r\nCKEDITOR.config.keystrokes =\r\n[\r\n    [ CKEDITOR.CTRL + 81 \/*Q*\/, ''blockquote'' ],\r\n    [ CKEDITOR.CTRL + 66 \/*B*\/, ''bold'' ],\r\n    [ CKEDITOR.CTRL + 56 \/*8*\/, ''bulletedlist'' ],\r\n    [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 56 \/*8*\/, ''bulletedListStyle'' ],\r\n\r\n    \/\/ COMMAND FOR HEADINGS via button plugins\r\n    [ CKEDITOR.CTRL + 49 \/*1*\/, ''heading-h1'' ],\r\n    [ CKEDITOR.CTRL + 50 \/*2*\/, ''heading-h2'' ],\r\n    [ CKEDITOR.CTRL + 51 \/*3*\/, ''heading-h3'' ],\r\n    [ CKEDITOR.CTRL + 52 \/*4*\/, ''heading-h4'' ],\r\n    [ CKEDITOR.CTRL + 53 \/*5*\/, ''heading-h5'' ],\r\n    [ CKEDITOR.CTRL + 54 \/*6*\/, ''heading-h6'' ],\r\n    [ CKEDITOR.CTRL + 77 \/*M*\/, ''indent'' ],\r\n    [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 77 \/*M*\/, ''outdent'' ],\r\n    [ CKEDITOR.CTRL + 73 \/*I*\/, ''italic'' ],\r\n    [ CKEDITOR.CTRL + 55 \/*7*\/, ''numberedlist'' ],\r\n    [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 55 \/*7*\/, ''numberedListStyle'' ],\r\n    [ CKEDITOR.CTRL + 89 \/*Y*\/, ''redo'' ],\r\n    [ CKEDITOR.CTRL + 32 \/*SPACE*\/, ''removeFormat'' ],\r\n    [ CKEDITOR.CTRL + 65 \/*A*\/, ''selectall'' ],\r\n    [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 88 \/*X*\/, ''strike'' ],\r\n    [ CKEDITOR.CTRL + 188 \/*COMMA*\/, ''subscript'' ],\r\n    [ CKEDITOR.CTRL + 190 \/*PERIOD*\/, ''superscript'' ],\r\n    [ CKEDITOR.CTRL + 85 \/*U*\/, ''underline'' ],\r\n    [ CKEDITOR.CTRL + 90 \/*Z*\/, ''undo'' ],\r\n\r\n    \/\/ Insert\r\n    [ CKEDITOR.ALT + 65 \/*A*\/, ''anchor'' ],\r\n    [ CKEDITOR.ALT + 68 \/*D*\/, ''creatediv'' ],\r\n    [ CKEDITOR.CTRL + 57 \/*9*\/, ''image'' ],\r\n    [ CKEDITOR.ALT + 73 \/*I*\/, ''image'' ],\r\n    [ CKEDITOR.CTRL + 75 \/*K*\/, ''link'' ],\r\n    [ CKEDITOR.ALT + 76 \/*L*\/, ''link'' ],\r\n    [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 75 \/*K*\/, ''unlink'' ],\r\n    [ CKEDITOR.ALT + CKEDITOR.SHIFT + 76 \/*L*\/, ''unlink'' ],\r\n\r\n    [ CKEDITOR.ALT + 86 \/*V*\/, ''pastetext'' ],\r\n    [ CKEDITOR.ALT + CKEDITOR.SHIFT + 86 \/*V*\/, ''pastefromword'' ],\r\n\r\n    [ CKEDITOR.ALT + 67 \/*C*\/, ''specialchar'' ],\r\n    [ CKEDITOR.ALT + 84 \/*T*\/, ''table'' ],\r\n\r\n    \/\/ Other - dialogs, views, etc.\r\n    [ CKEDITOR.ALT + 8 \/*Backspace*\/, ''blur'' ],\r\n    [ CKEDITOR.ALT + 77 \/*M*\/, ''contextMenu'' ],\r\n    [ CKEDITOR.SHIFT + 121 \/*F10*\/, ''contextMenu'' ],\r\n    [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 121 \/*F10*\/, ''contextMenu'' ],\r\n\r\n    [ CKEDITOR.ALT + 122 \/*F11*\/, ''elementsPathFocus'' ],\r\n    [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 70 \/*F*\/, ''find'' ],\r\n    [ CKEDITOR.ALT + 88 \/*X*\/, ''maximize'' ],\r\n    [ CKEDITOR.CTRL + 113 \/*F2*\/, ''preview'' ],\r\n    [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 80 \/*P*\/, ''print'' ],\r\n    [ CKEDITOR.CTRL + 72 \/*H*\/, ''replace'' ],\r\n    [ CKEDITOR.ALT + 83 \/*S*\/, ''scaytcheck'' ],\r\n    [ CKEDITOR.ALT + 66 \/*B*\/, ''showblocks'' ],\r\n    [ CKEDITOR.ALT + CKEDITOR.SHIFT + 84 \/*T*\/, ''showborders'' ],\r\n    [ CKEDITOR.ALT + 90 \/*Z*\/, ''source'' ],\r\n    [ CKEDITOR.ALT + 48 \/*ZERO*\/, ''toolbarCollapse'' ],\r\n    [ CKEDITOR.ALT + 121 \/*F10*\/, ''toolbarFocus'' ],\r\n];","ckfinder":"1","CKFinderPathType":"0","username_access":["6","7","3","4","5","10","12","8"],"CKFinderSaveFiles":"files","CKFinderSaveImages":"images","CKFinderSaveFlash":"files","CKFinderSaveThumbs":"cache\\/_thumbs","CKFinderResourceFiles":"","CKFinderResourceImages":"","CKFinderResourceFlash":"","CKFinderMaxFilesSize":"8M","CKFinderMaxImagesSize":"8M","CKFinderMaxFlashSize":"8M","CKFinderMaxImageWidth":"1200","CKFinderMaxImageHeight":"1200","CKFinderMaxThumbnailWidth":"180","CKFinderMaxThumbnailHeight":"180","CKFinderFileEdit":"1","CKFinderImageResize":"1","CKFinderZip":"1","CKFinderSettingsChmod":"0755","PackageLicenseName":"","PackageLicenseKey":"","option":"com_ckeditor","client":"site","type":"config","task":"apply","rows":"Bold,Italic,;,NumberedList,BulletedList,;,Subscript,Superscript,;,JustifyLeft,JustifyCenter,JustifyRight,;,Link,Unlink,Anchor,\\/,Cut,Paste,PasteFromWord,PasteText,RemoveFormat,AutoCorrect,;,Styles,Format,;,Image,Youtube","toolbarGroup":""}',
			'',
			'',
			0,
			'0000-00-00 12:00:00',
			0,
			0
		);
