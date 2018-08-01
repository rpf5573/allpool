// @codekit-prepend "../button-fields.js";

export class TextFields extends ButtonFields {

	renderHTML() {
		let inputField = '<input type="text" data-imp-field="' + this.content + '" data-for="' + this.dataFor + '"';
		inputField += ' placeholder="' + this.placeholder + '" name="' + this.nameValue + '" value="' + this.value + '"';

		if ( this.readOnly )	inputField += ' readonly';

		this.htmlElement = jQuery(this.defaultHTML + inputField + '/></p>');
		return this.htmlElement;
	}

	validate(domElement) {
		const targetElement = domElement.find('input');
		const targetValue = targetElement.val();

		if ( targetValue ) {
			this.extraKey = targetElement.attr('data-imp-field');
			this.extraValue = targetValue;

			return true;
		} else {
			return false;
		}
	}

	getValue() {
		let element = this.getElement();
		if ( element )	return element.val();
		
		return super.getValue();
	}

	getElement() {
		if ( this.htmlElement )	return this.htmlElement.find("input");

		return null;
	}

}