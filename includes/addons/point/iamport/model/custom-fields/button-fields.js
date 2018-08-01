export class ButtonFields {	
	constructor(setting) {
		this.required = this.isRequired(setting.required);
		this.content = setting["content"];
		this.options = setting["options"];
		this.defaultHTML = this.renderDefaultHTML();
		this.placeholder = this.renderPlaceholder(setting.placeholder);
		this.dataFor = setting["data-for"];
		this.extraKey = "";
		this.extraValue = "";
		this.nameValue = setting["nameValue"];
		this.value = setting["value"] ? setting["value"] : "";
		this.htmlElement = null;
		this.readOnly = setting.readOnly || false;
	}

	isRequired(required) {
		if ( required === "true" ) {
			return "required";
		}
	}

	renderDefaultHTML() {
		let defaultHTML = '<p class="custom-input ' + this.required + '" name="' + this.content + '">';
		defaultHTML += '<label>' + this.content + '<span class="iamport-checkbox-alert"></span>';
		defaultHTML += '<span class="iamport-checkbox-alert-message" style="display:none">필수입력입니다</span></label>';

		return defaultHTML;
	}

	renderPlaceholder(placeholder) {
		if ( placeholder ) return placeholder;
		else return '';
	}

	getName() {
		return this.nameValue;
	}

	getHolderName() {
		return this.content;
	}

	getElement() {
		return this.htmlElement;
	}

	getValue() {
		return "";
	}

} 