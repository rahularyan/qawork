// Register a new CKEditor plugin.
// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.resourceManager.html#add
CKEDITOR.plugins.add( 'oEmbed',
{
	// The plugin initialization logic goes inside this method.
	// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.pluginDefinition.html#init
	init: function( editor )
	{
		// Create an editor command that stores the dialog initialization command.
		// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.command.html
		// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.dialogCommand.html
		editor.addCommand( 'oEmbedDialog', new CKEDITOR.dialogCommand( 'oEmbedDialog' ) );
 
		// Create a toolbar button that executes the plugin command defined above.
		// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.ui.html#addButton
		editor.ui.addButton( 'oEmbed',
		{
			// Toolbar button tooltip.
			label: 'Insert a Link',
			// Reference to the plugin command name.
			command: 'oEmbedDialog',
			// Button's icon file path.
			icon: this.path + 'images/icon.png'
		} );
 
		// Add a new dialog window definition containing all UI elements and listeners.
		// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.dialog.html#.add
		// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.dialog.dialogDefinition.html
		CKEDITOR.dialog.add( 'oEmbedDialog', function( editor )
		{
			return {
				// Basic properties of the dialog window: title, minimum size.
				// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.dialog.dialogDefinition.html
				title : 'Add an oEmbed link',
				minWidth : 400,
				minHeight : 200,
				// Dialog window contents.
				// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.dialog.definition.content.html
				contents :
				[
					{
						// Definition of the Settings dialog window tab (page) with its id, label and contents.
						// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.dialog.contentDefinition.html
						id : 'general',
						label : 'Settings',
						elements :
						[
							// Dialog window UI element: HTML code field.
							// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.ui.dialog.html.html
							{
								type : 'html',
								// HTML code to be shown inside the field.
								// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.ui.dialog.html.html#constructor
								html : 'This dialog window lets add oEmbed supported links.'
							},
							
							// Dialog window UI element: a text input field for the link URL.
							// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.ui.dialog.textInput.html
							{
								type : 'text',
								id : 'url',
								label : 'URL',
								validate : CKEDITOR.dialog.validate.notEmpty( 'The link must have a URL.' ),
								required : true,
								commit : function( data )
								{
									data.url = this.getValue();
								}
							}
							
						]
					}
				],
				onOk : function()
				{

					var dialog = this,
						data = {},
						link = editor.document.createElement( 'a' );

					this.commitContent( data );

					link.setAttribute( 'href', data.url );
					link.setAttribute( 'class', 'oembed' );

					link.setHtml( data.url );

					editor.insertElement( link );
				}
			};
		} );
	}
} );