/**
 * @license Copyright (c) 2014-2016, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

( function() {
	'use strict';

	CKEDITOR.plugins.aychecker.quickFixes.get( { langCode: 'nl',
		name: 'AttributeRename',
		callback: function( AttributeRename ) {
			/**
			 * QuickFix renaming an attribute {@link #attributeName} to another name
			 * {@link #attributeTargetName} using a proposed default value
			 * based on the value of {@link #attributeTargetName}.
			 *
			 * @member CKEDITOR.plugins.aychecker.quickFix
			 * @class AttributeRenameDefault
			 * @constructor
			 * @param {CKEDITOR.plugins.aychecker.Issue} issue Issue QuickFix is created for.
			 */
			function AttributeRenameDefault( issue ) {
				AttributeRename.call( this, issue );
			}

			AttributeRenameDefault.prototype = new AttributeRename();

			AttributeRenameDefault.prototype.constructor = AttributeRenameDefault;

			AttributeRenameDefault.prototype.getProposedValue = function() {
				var element = this.issue.element;
				return element.getAttribute( this.attributeTargetName ) ||
					element.getAttribute( this.attributeName ) || '';
			};

			AttributeRenameDefault.prototype.lang = {};
			CKEDITOR.plugins.aychecker.quickFixes.add( 'nl/AttributeRenameDefault', AttributeRenameDefault );
		}
	} );
}() );
