CKEDITOR.plugins.add('tooltip', {
	init: function (editor) {
		CKEDITOR.dialog.add('TooltipDialog', function (instance) {
			return {
				title: 'Tooltip',
				contents: [{
						id: 'tab-tooltip',
						elements: [
							{
								id: 'input-src',
								type: 'text',
								label: 'Source text',
								autofocus: 'autofocus',
								// Validation checking whether the field is not empty.
								validate: CKEDITOR.dialog.validate.notEmpty("Abbreviation field cannot be empty."),
								// Called by the main setupContent method call on dialog initialization.
								setup: function (element) {
									this.setValue(element.getText());
								},
								// Called by the main commitContent method call on dialog confirmation.
								commit: function (element) {
									element.setText(this.getValue());
								}
							},
							{
								id: 'input-tooltip',
								type: 'text',
								label: 'Tooltip text',
								// Require the title attribute to be enabled.
								requiredContent: 'abbr[title]',
								validate: CKEDITOR.dialog.validate.notEmpty("Explanation field cannot be empty."),
								// Called by the main setupContent method call on dialog initialization.
								setup: function (element) {
									this.setValue(element.getAttribute("title"));
								},
								// Called by the main commitContent method call on dialog confirmation.
								commit: function (element) {
									element.setAttribute("title", this.getValue());
								}
							}
						]
					}],
				onShow: function () {
					// Get the selection from the editor.
					var selection = editor.getSelection();

					// Get the element at the start of the selection.
					var elem = selection.getStartElement();

					// Get the <abbr> element closest to the selection, if it exists.
					if (elem)
						elem = elem.getAscendant('abbr', true);

					// Create a new <abbr> element if it does not exist.
					if (!elem || elem.getName() != 'abbr') {
						elem = editor.document.createElement('abbr');

						// Flag the insertion mode for later use.
						this.insertMode = true;
					}
					else
						this.insertMode = false;

					// Store the reference to the <abbr> element in an internal property, for later use.
					this.element = elem;

					// Invoke the setup methods of all dialog window elements, so they can load the element attributes.
					if (!this.insertMode)
						this.setupContent(this.element);
				},
				onOk: function () {

					// The context of this function is the dialog object itself.
					// http://docs.ckeditor.com/#!/api/CKEDITOR.dialog
					var dialog = this;

					// Create a new <abbr> element.
					var abbr = this.element;

					// Invoke the commit methods of all dialog window elements, so the <abbr> element gets modified.
					this.commitContent(abbr);

					// Finally, if in insert mode, insert the element into the editor at the caret position.
					if (this.insertMode)
						editor.insertElement(abbr);
				}
			};
		});

		editor.addCommand('Tooltip', new CKEDITOR.dialogCommand('TooltipDialog', {
			// Allow the abbr tag with an optional title attribute.
			allowedContent: 'abbr[title]',
			// Require the abbr tag to be allowed for the feature to work.
			requiredContent: 'abbr'
		}));

		editor.ui.addButton('Tooltip', {
			label: 'Insert Tooltip',
			command: 'Tooltip',
			icon: this.path + 'icons/tooltip.png',
			toolbar: 'insert,100',
			type: 'button'
		});
	}
});
