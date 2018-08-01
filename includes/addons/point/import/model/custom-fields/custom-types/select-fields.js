// @codekit-prepend "../button-fields.js";

export class SelectFields extends ButtonFields  {

	renderHTML() {
		let html = '<select data-imp-field="' + this.content + '" name="' + this.nameValue + '">';

		this.options.forEach(option => {
			html += '<option value="' + option + '">' + option + '</option>';
		});
		
		this.htmlElement = jQuery(this.defaultHTML + html + '</select></p>');
		return this.htmlElement;
	}

	validate(domElement) {
		const targetElement = domElement.find('select');

		this.extraKey = targetElement.attr('data-imp-field');
		this.extraValue = targetElement.val();

		return true;
	}

	getValue() {
		if ( this.htmlElement )	return this.htmlElement.find('select').val();

		return super.getValue();
	}

}