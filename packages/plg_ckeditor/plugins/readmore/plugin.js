/*
Copyright (c) 2003-2009, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @file Plugin for inserting Joomla readmore
 */

		CKEDITOR.plugins.add( 'readmore',
		{
				requires  : [ 'fakeobjects' ],
				init : function( editor )
				{
						CKEDITOR.addCss(
								'#system-readmore' +
								'{' +
								'background-image: url(' + CKEDITOR.getUrl( this.path + 'images/readmore.gif' ) + ');' +
								'background-position: center center;' +
								'background-repeat: no-repeat;' +
								'clear: both;' +
								'display: block;' +
								'float: none;' +
								'width: 100%;' +
								'border-top: #999999 1px dotted;' +
								'border-bottom: #999999 1px dotted;' +
								'height: 5px;' +
								'}' +
								'#system-readmore' +
								'{' +
								'border-top: #FF0000 1px dotted;' +
								'border-bottom: #FF0000 1px dotted;' +
								'}'
								);
						// Register the toolbar buttons.
						editor.ui.addButton( 'ReadMore',
						{
								label : 'Insert Readmore',
								icon : this.path + 'images/readmoreButton.gif',
								command : 'readmore'
						});
						editor.addCommand( 'readmore',
						{
								exec : function()
								{

										var hrs = editor.document.getElementsByTag( 'hr' );
										for ( var i = 0, len = hrs.count() ; i < len ; i++ )
										{
												var hr = hrs.getItem( i );
												if ( hr.getId() == 'system-readmore')
												{
													alert('There is already a Read more... link that has been inserted. Only one such link is permitted. Use {pagebreak} to split the page up further');
													return;
												}
										}
										insertComment( 'readmore' );
								}
						} );

						function insertComment( text )
						{
							editor.insertHtml('<hr id="system-readmore" class="cke_joomla_' + text +'" />');
						}
				}
		});