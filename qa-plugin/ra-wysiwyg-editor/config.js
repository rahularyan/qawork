/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	
	config.removePlugins = 'elementspath, specialchar, spellchecker, tabletools, contextmenu, pastetext, pastefromword';
	config.disableNativeSpellChecker = false;
	
	// Define changes to default configuration here.
	// For complete reference see:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config


	config.toolbar = [
		{ name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat' ] },	
		{ name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent' ] },
		{ name: 'links', items: [ 'Link', 'Unlink'] },
		{ name: 'document', items: [ 'Source', 'CodeSnippet' ] }
		
	];
	
	config.extraAllowedContent= 'img[!src,alt,width,height]';

	// Remove some buttons provided by the standard plugins, which are
	// not needed in the Standard(s) toolbar.
	config.removeButtons = 'Underline,Subscript,Superscript';

	// Simplify the dialog windows.
	config.removeDialogTabs = 'image:advanced;link:advanced';
		
};
