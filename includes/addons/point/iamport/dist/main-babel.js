"use strict";

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var ButtonFields = function () {
	function ButtonFields(setting) {
		_classCallCheck(this, ButtonFields);

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

	_createClass(ButtonFields, [{
		key: "isRequired",
		value: function isRequired(required) {
			if (required === "true") {
				return "required";
			}
		}
	}, {
		key: "renderDefaultHTML",
		value: function renderDefaultHTML() {
			var defaultHTML = '<p class="custom-input ' + this.required + '" name="' + this.content + '">';
			defaultHTML += '<label>' + this.content + '<span class="iamport-checkbox-alert"></span>';
			defaultHTML += '<span class="iamport-checkbox-alert-message" style="display:none">필수입력입니다</span></label>';

			return defaultHTML;
		}
	}, {
		key: "renderPlaceholder",
		value: function renderPlaceholder(placeholder) {
			if (placeholder) return placeholder;else return '';
		}
	}, {
		key: "getName",
		value: function getName() {
			return this.nameValue;
		}
	}, {
		key: "getHolderName",
		value: function getHolderName() {
			return this.content;
		}
	}, {
		key: "getElement",
		value: function getElement() {
			return this.htmlElement;
		}
	}, {
		key: "getValue",
		value: function getValue() {
			return "";
		}
	}]);

	return ButtonFields;
}();
'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _get = function get(object, property, receiver) { if (object === null) object = Function.prototype; var desc = Object.getOwnPropertyDescriptor(object, property); if (desc === undefined) { var parent = Object.getPrototypeOf(object); if (parent === null) { return undefined; } else { return get(parent, property, receiver); } } else if ("value" in desc) { return desc.value; } else { var getter = desc.get; if (getter === undefined) { return undefined; } return getter.call(receiver); } };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

// @codekit-prepend "../button-fields.js";

var TextFields = function (_ButtonFields) {
	_inherits(TextFields, _ButtonFields);

	function TextFields() {
		_classCallCheck(this, TextFields);

		return _possibleConstructorReturn(this, (TextFields.__proto__ || Object.getPrototypeOf(TextFields)).apply(this, arguments));
	}

	_createClass(TextFields, [{
		key: 'renderHTML',
		value: function renderHTML() {
			var inputField = '<input type="text" data-imp-field="' + this.content + '" data-for="' + this.dataFor + '"';
			inputField += ' placeholder="' + this.placeholder + '" name="' + this.nameValue + '" value="' + this.value + '"';

			if (this.readOnly) inputField += ' readonly';

			this.htmlElement = jQuery(this.defaultHTML + inputField + '/></p>');
			return this.htmlElement;
		}
	}, {
		key: 'validate',
		value: function validate(domElement) {
			var targetElement = domElement.find('input');
			var targetValue = targetElement.val();

			if (targetValue) {
				this.extraKey = targetElement.attr('data-imp-field');
				this.extraValue = targetValue;

				return true;
			} else {
				return false;
			}
		}
	}, {
		key: 'getValue',
		value: function getValue() {
			var element = this.getElement();
			if (element) return element.val();

			return _get(TextFields.prototype.__proto__ || Object.getPrototypeOf(TextFields.prototype), 'getValue', this).call(this);
		}
	}, {
		key: 'getElement',
		value: function getElement() {
			if (this.htmlElement) return this.htmlElement.find("input");

			return null;
		}
	}]);

	return TextFields;
}(ButtonFields);
'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _get = function get(object, property, receiver) { if (object === null) object = Function.prototype; var desc = Object.getOwnPropertyDescriptor(object, property); if (desc === undefined) { var parent = Object.getPrototypeOf(object); if (parent === null) { return undefined; } else { return get(parent, property, receiver); } } else if ("value" in desc) { return desc.value; } else { var getter = desc.get; if (getter === undefined) { return undefined; } return getter.call(receiver); } };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

// @codekit-prepend "../button-fields.js";

var SelectFields = function (_ButtonFields) {
	_inherits(SelectFields, _ButtonFields);

	function SelectFields() {
		_classCallCheck(this, SelectFields);

		return _possibleConstructorReturn(this, (SelectFields.__proto__ || Object.getPrototypeOf(SelectFields)).apply(this, arguments));
	}

	_createClass(SelectFields, [{
		key: 'renderHTML',
		value: function renderHTML() {
			var html = '<select data-imp-field="' + this.content + '" name="' + this.nameValue + '">';

			this.options.forEach(function (option) {
				html += '<option value="' + option + '">' + option + '</option>';
			});

			this.htmlElement = jQuery(this.defaultHTML + html + '</select></p>');
			return this.htmlElement;
		}
	}, {
		key: 'validate',
		value: function validate(domElement) {
			var targetElement = domElement.find('select');

			this.extraKey = targetElement.attr('data-imp-field');
			this.extraValue = targetElement.val();

			return true;
		}
	}, {
		key: 'getValue',
		value: function getValue() {
			if (this.htmlElement) return this.htmlElement.find('select').val();

			return _get(SelectFields.prototype.__proto__ || Object.getPrototypeOf(SelectFields.prototype), 'getValue', this).call(this);
		}
	}]);

	return SelectFields;
}(ButtonFields);
'use strict';

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

// @codekit-prepend "../model/custom-fields/custom-types/text-fields.js";
// @codekit-prepend "../model/custom-fields/custom-types/select-fields.js";

var _iamportButtonFields = iamportButtonFields,
    uuidList = _iamportButtonFields.uuidList,
    userCode = _iamportButtonFields.userCode,
    isLoggedIn = _iamportButtonFields.isLoggedIn,
    configuration = _iamportButtonFields.configuration,
    device = _iamportButtonFields.device;


jQuery(function ($) {
	var body = $('body');

	var uuidToArrayForRenderHTML = [];
	/* ----------버튼 식별자 만들기---------- */
	uuidList.forEach(function (uuid, key) {
		var uuidToString = '#' + uuid + '-popup'; // 아임포트 결제버튼 식별자
		var uuidToStringForNext = '#iamport-survey-box-' + uuid + ' #iamport-end-survey-button'; // 다음 버튼 식별자
		var uuidToStringForGoBack = '#' + uuid + ' #iamport-go-back'; // 뒤로가기 버튼 식별자
		var uuidToStringForPay = '#' + uuid + ' #iamport-payment-submit'; // 결제하기 버튼 식별자

		// let targetId = null; // 누른 모달의 ID
		// let customFields = null; // [iamport_payment_button_field] 리스트

		// let iamportBox = null; // 결제하기 모달
		// let iamportTargetBox = null;
		// let iamportLoginBox = null; // 로그인 모달
		// let iamportSurveyBox = null; // 설문조사 모달

		var requiredLength = 0; // 필수입력 필드 길이

		/* ---------- 숏코드가 생성한 아임포트 결제버튼을 눌렀을때 ---------- */
		$(uuidToString).click(function (e) {
			requiredLength = 0; // 필수입력 필드 길이 초기화

			var buttonContext = window["iamportButtonContext_" + uuid];
			var customFields = buttonContext.customFields; //localize string으로 global영역에 존재
			var iamportBox = $('#' + uuid);
			var iamportTargetBox = iamportBox;
			var iamportLoginBox = $('#iamport-login-box');
			var iamportSurveyBox = $('#iamport-survey-box-' + uuid);
			var deviceType = device;

			if (configuration['login_required'] && !isLoggedIn) {
				// 로그인이 안 되어 있으면 iamportLoginBox를 연다
				iamportTargetBox = iamportLoginBox;
				deviceType = "";
			} else if (customFields) {
				// 로그인이 되어있고 custom field가 있으면 surveyBox를 연다
				iamportTargetBox = iamportSurveyBox;

				// customFields를 만들어 modalContent안에 append한다
				customFields.forEach(function (targetField) {
					if (!targetField['domAdded']) {
						var type = targetField.type;

						var modalContentBox = $('#iamport-survey-box-' + uuid + ' .iamport-modal-content');

						var field = null;
						switch (type) {
							case "select":
								{
									field = new SelectFields(targetField);
									break;
								}
							default:
								{
									field = new TextFields(targetField);
									break;
								}
						}

						var fieldHTML = field.renderHTML();
						modalContentBox.append(fieldHTML);

						targetField['domAdded'] = true;

						targetField['domClass'] = field;
						targetField['domElement'] = modalContentBox.find('p').last();
					}

					if (targetField['domClass'].required === "required") {
						requiredLength++;
					}
				});
			} else {
				setPaymentBtnBusy(iamportBox.find('#iamport-payment-submit'), false);

				if (!uuidToArrayForRenderHTML[uuid]) {
					renderPaymentHTML(iamportBox, buttonContext);
					uuidToArrayForRenderHTML[uuid] = true;
				}

				IMP.init(userCode);
			}

			// 모달의 위치를 결정한다
			setIamportModalBox($, iamportTargetBox, deviceType);

			// background에 dimmedScreen을 깐다
			$('.dimmed-background').css({ "display": "block" });

			return false;
		});

		var extraFields = {};
		var fileFields = new FormData();
		/* ---------- 다음 버튼 눌렀을때 ---------- */
		$(uuidToStringForNext).click(function () {
			var validatedCount = 0;
			var buttonContext = window["iamportButtonContext_" + uuid];
			var customFields = buttonContext.customFields; //localize string으로 global영역에 존재
			var iamportBox = $('#' + uuid);
			var iamportSurveyBox = $('#iamport-survey-box-' + uuid);

			for (var i = customFields.length - 1; i >= 0; i--) {
				var _customFields$i = customFields[i],
				    domClass = _customFields$i.domClass,
				    domElement = _customFields$i.domElement;
				var required = domClass.required;

				/* ==================== REFECTOR: validate와 setExtraValue를 구분 ==================== */

				var validated = domClass.validate(domElement);
				var missedFields = domElement.find('span.iamport-checkbox-alert-message');

				if (!validated && required == "required") {
					missedFields.css({ 'display': 'inline-block' });
					// Invalid 항목으로 스크롤 이동
					iamportSurveyBox.find('.iamport-modal-container')[0].scrollTop = domElement[0].offsetTop;
				} else if (domClass.extraValue) {
					if (_typeof(domClass.extraValue) === "object") {
						fileFields.append(domClass.extraKey, domClass.extraValue);
					} else {
						extraFields[domClass.extraKey] = domClass.extraValue;
					}

					missedFields.css({ 'display': 'none' });
					if (required == "required") validatedCount++;
				}
			}
			fileFields.append('extra_fields', JSON.stringify(extraFields));

			// Check all validated
			if (requiredLength === 0 || validatedCount === requiredLength) {
				iamportSurveyBox.css({ "display": "none" });

				if (!uuidToArrayForRenderHTML[uuid]) {
					renderPaymentHTML(iamportBox, buttonContext);
					uuidToArrayForRenderHTML[uuid] = true;
				}

				iamportSurveyBox.find('[data-for]').each(function (idx, elem) {
					var $elem = $(elem);
					var elemVar = $elem.val();
					var dataFor = $elem.attr('data-for');

					if (elemVar) {
						if (dataFor === "phone") dataFor = "tel";
						iamportBox.find('input[name="buyer_' + dataFor + '"]').val(elemVar);
					}
				});

				setIamportModalBox($, iamportBox, device);

				IMP.init(userCode);
			}

			return false;
		});

		/* ---------- X버튼 또는 dimmedScreen 눌렀을때 ---------- */
		$('.iamport-modal-close, .dimmed-background').click(function () {
			body.removeClass('modal-open');

			$('.iamport-modal').css({ "display": "none" });
			$('.dimmed-background').css({ "display": "none" });

			// input alert 초기화
			$('span.iamport-checkbox-alert-message').css({ "display": "none" });
		});

		/* ---------- 뒤로가는 화살표 버튼 눌렀을때 ---------- */
		$(uuidToStringForGoBack).click(function () {
			var modalContainer = iamportSurveyBox.find('.iamport-modal-container');
			var iamportBox = $('#' + uuid);
			var iamportSurveyBox = $('#iamport-survey-box-' + uuid);

			$(iamportBox).css({ "display": "none" });
			setIamportModalBox($, iamportSurveyBox, device);

			modalContainer[0].scrollTop = 0;

			return false;
		});

		/* ---------- 결제하기 버튼 눌렀을때 ---------- */
		$(uuidToStringForPay).click(function () {
			var iamportBox = $('#' + uuid);
			var buttonContext = window["iamportButtonContext_" + uuid];
			var modalContainer = iamportBox.find('.iamport-modal-container');
			var inputValues = {};
			// const inputFields = modalContainer.find('input');
			var inputFields = modalContainer.data("inputFields");
			var amountField = modalContainer.data("amountField");
			var inputFieldsLength = inputFields.length;

			for (var i = 0; i < inputFieldsLength; i++) {
				var inputField = inputFields[i];

				var inputValue = inputField.getValue();
				var dataImpField = inputField.getHolderName();
				var inputContainer = iamportBox.find('p[name="' + dataImpField + '"]');
				var alertMessage = inputContainer.find('span.iamport-checkbox-alert-message');

				// 전화번호 필드는 숫자만 입력 가능하도록
				var inputName = inputField.getName();
				if (inputName === "buyer_tel") {
					inputValue = inputValue.replace(/[^0-9-]/g, '');
				}

				if (inputValue) {
					alertMessage.css({ "display": "none" });
				} else {
					alertMessage.css({ "display": "inline-block" });
					modalContainer[0].scrollTop = inputContainer[0].offsetTop;

					return false;
				}

				//값을 담아서 전달
				inputValues[inputName] = inputValue;
			}

			//order_amount 필드 전달
			var raw_amount = amountField.getValue() || '';
			var matched = raw_amount.match(/\((.*?)\)/g);

			inputValues[amountField.getName()] = parseInt(raw_amount.replace(/[^0-9]/g, ''));
			if (matched) inputValues.amount_label = matched[0];

			setPaymentBtnBusy(iamportBox.find('#iamport-payment-submit'), true);

			return iamportAjaxCall($, iamportBox, fileFields, inputValues, buttonContext);
		});
	});
});

/* ---------- payment 모달내부 결제금액 및 결제수단 돔 엘리먼트 그리기 ---------- */
function renderPaymentHTML(iamportBox, buttonContext) {
	jQuery(function ($) {
		var modalContainer = iamportBox.find('.iamport-modal-container');
		var modalContentBox = iamportBox.find('.iamport-modal-content');
		var fieldLists = buttonContext.fieldLists;
		var inputFields = [];

		// 결제자 이름/이메일/전화번호
		Object.values(fieldLists).map(function (eachField) {
			var required = eachField.required,
			    content = eachField.content,
			    value = eachField.value,
			    name = eachField.name,
			    placeholder = eachField.placeholder;


			var contents = {
				required: required,
				content: content,
				value: value,
				placeholder: placeholder,
				"nameValue": name
			};

			var field = new TextFields(contents);
			modalContentBox.append(field.renderHTML());
			inputFields.push(field);
		});

		//추후 값 읽기를 위해 inputFields세팅
		modalContainer.data("inputFields", inputFields);

		// 결제금액
		var payAmountContents = {
			"required": "true",
			"content": "결제금액",
			"nameValue": "order_amount"
		};

		var targetId = buttonContext.uuid;
		var targetAmountArr = buttonContext.amountArr;
		var amountArrLength = targetAmountArr.length;

		var payAmountField = null;
		if (amountArrLength > 1) {
			var payAmountOptions = [];
			targetAmountArr.map(function (amount) {
				var value = amount.value,
				    label = amount.label;

				var amountValue = value + '원';
				if (label) {
					amountValue += ' (' + label + ')';
				}
				payAmountOptions.push(amountValue);
			});
			payAmountContents.options = payAmountOptions;
			payAmountField = new SelectFields(payAmountContents);
		} else {
			if (amountArrLength === 1) {
				var _targetAmountArr$ = targetAmountArr[0],
				    value = _targetAmountArr$.value,
				    label = _targetAmountArr$.label;


				payAmountContents.value = value + '원';
				payAmountContents.readOnly = true;
				if (label) {
					payAmountContents.value += ' (' + label + ')';
				}
			}
			payAmountField = new TextFields(payAmountContents);
		}

		modalContentBox.prepend(payAmountField.renderHTML());
		//결제금액 field reference
		modalContainer.data("amountField", payAmountField);

		// 결제수단
		var paymethodContents = {
			"required": "true",
			"content": "결제수단",
			"options": buttonContext.payMethods,
			"nameValue": "pay_method"
		};
		var paymethodField = new SelectFields(paymethodContents);
		modalContentBox.prepend(paymethodField.renderHTML());
	});
}

/* ---------- 결제하기 버튼 상태 정하기 ---------- */
function setPaymentBtnBusy($button, busy) {
	if (busy) {
		$button.attr('data-progress', 'true').text('결제 중입니다...');
	} else {
		$button.attr('data-progress', null).text('결제하기');
	}
}

/* ---------- 모달의 위치 정하기 ---------- */
function setIamportModalBox($, element, device) {
	var offset = $(document).scrollTop();
	var viewportHeight = $(window).height();

	var elementHeight = element.outerHeight();

	if (device === "mobile") {
		element.css({
			"display": "block",
			"top": offset
		});

		var elementHeaderHeight = element.find('div.iamport-modal-header').outerHeight();
		var elementBottomHeight = element.find('p.button-holder').outerHeight();
		var containerHeight = document.documentElement.clientHeight - elementHeaderHeight - elementBottomHeight;

		// const elementContainer = element.find('div.iamport-modal-container');
		// elementContainer.css({
		// 	// "height": containerHeight,
		// 	"max-height": containerHeight
		// });
	} else {
		var elementTop = 0;
		var elementMarginBottom = 0;
		var defaultMargin = 50;

		if (viewportHeight >= elementHeight) {
			elementTop = offset + viewportHeight / 2 - elementHeight / 2;
		} else {
			elementTop = offset + defaultMargin;
			elementMarginBottom = defaultMargin;
		}

		element.css({
			"display": "block",
			"top": elementTop,
			"margin-bottom": elementMarginBottom
		});
	}

	if (viewportHeight > elementHeight || device === "mobile") {
		$('body').addClass("modal-open");
	}
}

/* ---------- 아임포트 일반결제 ---------- */
function iamportAjaxCall($, iamportBox, fileFields, inputValues, buttonContext) {
	if (iamportBox.find('#iamport-payment-submit').attr('data-progress') != 'true') return false;

	var order_title = buttonContext.orderTitle;
	var _iamportButtonFields2 = iamportButtonFields,
	    payMethodsToEn = _iamportButtonFields2.payMethodsToEn;

	var pay_method = payMethodsToEn[iamportBox.find('select[name="pay_method"]').val()];
	var buyer_name = inputValues.buyer_name || "";
	var buyer_email = inputValues.buyer_email || "";
	var buyer_tel = (inputValues.buyer_tel || "").replace(/[^0-9-]/g, '');
	var order_amount = inputValues.order_amount || -1;
	var amount_label = inputValues.amount_label;
	var redirect_after = iamportBox.find('#iamport-payment-submit').attr('data-redirect-after');
	var payButton = iamportBox.find('#iamport-payment-submit');

	if (order_amount < 0) {
		alert('결제금액이 올바르지 않습니다.');
		return false;
	}

	fileFields.append('action', 'get_order_uid');
	fileFields.append('order_title', order_title);
	fileFields.append('pay_method', pay_method);
	fileFields.append('buyer_name', buyer_name);
	fileFields.append('buyer_email', buyer_email);
	fileFields.append('buyer_tel', buyer_tel);
	fileFields.append('order_amount', order_amount);
	fileFields.append('redirect_after', redirect_after);
	if (amount_label !== null) {
		fileFields.append('amount_label', amount_label);
	}

	$.ajax({
		method: 'POST',
		url: iamportButtonFields['adminUrl'],
		contentType: false,
		processData: false,
		data: fileFields
	}).done(function (rsp) {
		iamportBox.css({ "display": "none" });

		var param = {
			name: order_title,
			pay_method: pay_method,
			amount: order_amount,
			buyer_name: buyer_name,
			buyer_email: buyer_email,
			buyer_tel: buyer_tel,
			merchant_uid: rsp.order_uid,
			m_redirect_url: rsp.thankyou_url
		};

		var pgConfig = configuration['pg_for_payment'];
		if (pgConfig[pay_method] && pgConfig[pay_method] !== 'default') {
			param.pg = pgConfig[pay_method];
		}

		//가상계좌 입금기한 적용
		if (rsp.vbank_due) {
			param.vbank_due = rsp.vbank_due;
		}

		IMP.request_pay(param, function (callback) {
			$('.dimmed-background').css({ "display": "block" });
			var resultBox = $('#iamport-result-box');

			if (callback.success) {
				resultBox.find('.main-title').text('결제완료 처리중');
				resultBox.find('.sub-title').text('');
				resultBox.find('.content').html('잠시만 기다려주세요. 결제완료 처리중입니다.');

				location.href = rsp.thankyou_url;
			} else {
				resultBox.find('.main-title').text('결제실패');
				resultBox.find('.sub-title').html('다음과 같은 사유로 결제에 실패하였습니다.');
				resultBox.find('.content').html(callback.error_msg);
			}

			setIamportModalBox($, resultBox, device);
			setPaymentBtnBusy(payButton, false);
		});

		$('.dimmed-background').css({ "display": "none" });
	}).fail(function () {
		setPaymentBtnBusy(payButton, false);
	});

	return false;
}
// @codekit-prepend "../controller/custom-fields.js";
"use strict";
