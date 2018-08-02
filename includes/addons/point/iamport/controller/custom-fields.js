// @codekit-prepend "../model/custom-fields/custom-types/text-fields.js";
// @codekit-prepend "../model/custom-fields/custom-types/select-fields.js";

const { uuidList, userCode, isLoggedIn, configuration, device } = iamportButtonFields;

jQuery(($) => {
	const body = $('body');

	let uuidToArrayForRenderHTML = [];
	/* ----------버튼 식별자 만들기---------- */
	uuidList.forEach((uuid, key) => {
		let uuidToString = '#' + uuid + '-popup'; // 아임포트 결제버튼 식별자
		let uuidToStringForNext = '#iamport-survey-box-' + uuid + ' #iamport-end-survey-button'; // 다음 버튼 식별자
		let uuidToStringForGoBack = '#' + uuid + ' #iamport-go-back'; // 뒤로가기 버튼 식별자
		let uuidToStringForPay = '#' + uuid + ' #iamport-payment-submit'; // 결제하기 버튼 식별자

		// let targetId = null; // 누른 모달의 ID
		// let customFields = null; // [iamport_payment_button_field] 리스트

		// let iamportBox = null; // 결제하기 모달
		// let iamportTargetBox = null;
		// let iamportLoginBox = null; // 로그인 모달
		// let iamportSurveyBox = null; // 설문조사 모달

		let requiredLength = 0; // 필수입력 필드 길이
		
		/* ---------- 숏코드가 생성한 아임포트 결제버튼을 눌렀을때 ---------- */
		$(uuidToString).click((e) => {	
			requiredLength = 0; // 필수입력 필드 길이 초기화

			let buttonContext = window["iamportButtonContext_" + uuid];
			let customFields = buttonContext.customFields; //localize string으로 global영역에 존재
			let iamportBox = $('#' + uuid);
			let iamportTargetBox = iamportBox;
			let iamportLoginBox = $('#iamport-login-box');
			let iamportSurveyBox = $('#iamport-survey-box-' + uuid);
			let deviceType = device;
			
			if ( configuration['login_required'] && !isLoggedIn ) {
				// 로그인이 안 되어 있으면 iamportLoginBox를 연다
				iamportTargetBox = iamportLoginBox;
				deviceType = "";
			} else if ( customFields ) {
				// 로그인이 되어있고 custom field가 있으면 surveyBox를 연다
				iamportTargetBox = iamportSurveyBox;

				// customFields를 만들어 modalContent안에 append한다
				customFields.forEach((targetField) => {
					if ( !targetField['domAdded'] ) {
						const { type } = targetField;
						let modalContentBox = $('#iamport-survey-box-' + uuid + ' .iamport-modal-content');

						let field = null;
						switch(type) {
							case "select": {
								field = new SelectFields(targetField);
								break;
							}
							default: {
								field = new TextFields(targetField);
								break;
							}
						}	

						const fieldHTML = field.renderHTML();
						modalContentBox.append(fieldHTML);

						targetField['domAdded'] = true;

						targetField['domClass'] = field;
						targetField['domElement'] = modalContentBox.find('p').last();
					} 

					if ( targetField['domClass'].required === "required" ) {
						requiredLength++;
					}
				});
			} else {
				setPaymentBtnBusy(iamportBox.find('#iamport-payment-submit'), false);

				if ( !uuidToArrayForRenderHTML[uuid] ) {
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
		
		let extraFields = {};
		let fileFields = new FormData(); 
		/* ---------- 다음 버튼 눌렀을때 ---------- */
		$(uuidToStringForNext).click(() => {
			let validatedCount = 0;
			let buttonContext = window["iamportButtonContext_" + uuid];
			let customFields = buttonContext.customFields; //localize string으로 global영역에 존재
			let iamportBox = $('#' + uuid);
			let iamportSurveyBox = $('#iamport-survey-box-' + uuid);

			for ( let i = customFields.length - 1; i >= 0; i-- ) {
				let { domClass, domElement } = customFields[i];
				const { required } = domClass;
				
				/* ==================== REFECTOR: validate와 setExtraValue를 구분 ==================== */
				const validated = domClass.validate(domElement);
				let missedFields = domElement.find('span.iamport-checkbox-alert-message');
				
				if ( !validated && required == "required" ) {
					missedFields.css({'display': 'inline-block'});
					// Invalid 항목으로 스크롤 이동
					iamportSurveyBox.find('.iamport-modal-container')[0].scrollTop = domElement[0].offsetTop;
				} else if ( domClass.extraValue ) {
					if ( typeof domClass.extraValue === "object" ) {             
					    fileFields.append(domClass.extraKey, domClass.extraValue);
					} else {
						extraFields[domClass.extraKey] = domClass.extraValue;
					}

					missedFields.css({'display': 'none'});
					if ( required == "required") 
						validatedCount++;
				}
			}
			fileFields.append('extra_fields', JSON.stringify(extraFields));

			// Check all validated
			if ( requiredLength === 0 || validatedCount === requiredLength ) {
				iamportSurveyBox.css({ "display": "none" });

				if ( !uuidToArrayForRenderHTML[uuid] ) {
					renderPaymentHTML(iamportBox, buttonContext);
					uuidToArrayForRenderHTML[uuid] = true;
				}

				iamportSurveyBox.find('[data-for]').each((idx, elem) => {
					const $elem = $(elem);
					const elemVar = $elem.val();
					let dataFor = $elem.attr('data-for');
					
					if ( elemVar ) {
						if ( dataFor === "phone" ) dataFor = "tel";
						iamportBox.find('input[name="buyer_' + dataFor + '"]').val(elemVar);
					}
				});

				setIamportModalBox($, iamportBox, device);

				IMP.init(userCode);
			}
			
			return false;
		});


		/* ---------- X버튼 또는 dimmedScreen 눌렀을때 ---------- */
		$('.iamport-modal-close, .dimmed-background').click(() => {
			body.removeClass('modal-open');

			$('.iamport-modal').css({ "display": "none" });
			$('.dimmed-background').css({ "display": "none" });

			// input alert 초기화
			$('span.iamport-checkbox-alert-message').css({ "display": "none" });
		});


		/* ---------- 뒤로가는 화살표 버튼 눌렀을때 ---------- */
		$(uuidToStringForGoBack).click(() => {
			let modalContainer = iamportSurveyBox.find('.iamport-modal-container');
			let iamportBox = $('#' + uuid);
			let iamportSurveyBox = $('#iamport-survey-box-' + uuid);

			$(iamportBox).css({ "display": "none" });
			setIamportModalBox($, iamportSurveyBox, device);

			modalContainer[0].scrollTop = 0 ;
			
			return false;
		});


		/* ---------- 결제하기 버튼 눌렀을때 ---------- */
		$(uuidToStringForPay).click(() => {
			let iamportBox = $('#' + uuid);
			let buttonContext = window["iamportButtonContext_" + uuid];
			let modalContainer = iamportBox.find('.iamport-modal-container');
			let inputValues = {};
			// const inputFields = modalContainer.find('input');
			const inputFields = modalContainer.data("inputFields");
			const amountField = modalContainer.data("amountField");
			const inputFieldsLength = inputFields.length;

			for ( let i = 0; i < inputFieldsLength; i++ ) {
				const inputField = inputFields[i];
				
				let inputValue = inputField.getValue();
				let dataImpField = inputField.getHolderName();
				let inputContainer = iamportBox.find('p[name="' + dataImpField + '"]');
				let alertMessage = inputContainer.find('span.iamport-checkbox-alert-message');
				
				// 전화번호 필드는 숫자만 입력 가능하도록
				const inputName = inputField.getName();
				if ( inputName === "buyer_tel" ) {
					inputValue = inputValue.replace(/[^0-9-]/g, '');
				}
				
				if ( inputValue ) {
					alertMessage.css({ "display": "none" });
				} else {
					alertMessage.css({ "display": "inline-block" });
					modalContainer[0].scrollTop = inputContainer[0].offsetTop;

					return false;
				}

				//값을 담아서 전달
				inputValues[ inputName ] = inputValue;
			}

			//order_amount 필드 전달
			const raw_amount = amountField.getValue() || '';
			const matched = raw_amount.match(/\((.*?)\)/g);

			inputValues[ amountField.getName() ] = parseInt( raw_amount.replace(/[^0-9]/g, '') );
			if ( matched )	inputValues.amount_label = matched[0];

			setPaymentBtnBusy(iamportBox.find('#iamport-payment-submit'), true);
			
			return iamportAjaxCall($, iamportBox, fileFields, inputValues, buttonContext);
		});

	});

});

/* ---------- payment 모달내부 결제금액 및 결제수단 돔 엘리먼트 그리기 ---------- */
function renderPaymentHTML(iamportBox, buttonContext) {
	jQuery(($) => {
		let modalContainer = iamportBox.find('.iamport-modal-container');
		let modalContentBox = iamportBox.find('.iamport-modal-content');
		let fieldLists = buttonContext.fieldLists;
		let inputFields = [];
		
		// 결제자 이름/이메일/전화번호
		Object.values(fieldLists).map((eachField) => {
			const { required, content, value, name, placeholder } = eachField;

			const contents = {
				required,
				content,
				value,
				placeholder,
				"nameValue": name
      };
      
      const field = new TextFields(contents);
      modalContentBox.append(field.renderHTML());
      inputFields.push(field);
		});

		//추후 값 읽기를 위해 inputFields세팅
		modalContainer.data("inputFields", inputFields);

		// 결제금액
		let payAmountContents = {
			"required": "true",
			"content": "결제금액",
			"nameValue": "order_amount"
		}
		
		const targetId = buttonContext.uuid;
		const targetAmountArr = buttonContext.amountArr;
		const amountArrLength = targetAmountArr.length;

		let payAmountField = null;
		if ( amountArrLength > 1 ) {
			let payAmountOptions = [];
			targetAmountArr.map((amount) => {

				const { value, label } = amount;
				let amountValue = value + '원'; 
				if ( label ) {
					amountValue += ' (' + label + ')';
				}
				payAmountOptions.push(amountValue);
			});
			payAmountContents.options = payAmountOptions;
			payAmountField = new SelectFields(payAmountContents);
		} else {
			if ( amountArrLength === 1 ) {
				const { value, label } = targetAmountArr[0];

				payAmountContents.value = value + '원';
				payAmountContents.readOnly = true;
				if ( label ) {
					payAmountContents.value += ' (' + label + ')';
				}
			}
			payAmountField = new TextFields(payAmountContents); 
		}
		
		modalContentBox.prepend(payAmountField.renderHTML());
		//결제금액 field reference
		modalContainer.data("amountField", payAmountField);

		// 결제수단
		const paymethodContents = {
			"required": "true",
			"content": "결제수단",
			"options": 	buttonContext.payMethods,
			"nameValue": "pay_method"
		}
		const paymethodField = new SelectFields(paymethodContents);
		modalContentBox.prepend(paymethodField.renderHTML());
	});
}

/* ---------- 결제하기 버튼 상태 정하기 ---------- */
function setPaymentBtnBusy($button, busy) {
	if ( busy ) {
		$button.attr('data-progress', 'true').text('결제 중입니다...');
	} else {
		$button.attr('data-progress', null).text('결제하기');
	}
}

/* ---------- 모달의 위치 정하기 ---------- */
function setIamportModalBox($, element, device) {
	const offset = $(document).scrollTop();
	const viewportHeight = $(window).height();

	const elementHeight = element.outerHeight();
	
	if ( device === "mobile" ) {
		element.css({ 
			"display": "block", 
			"top": offset
		});
		
		const elementHeaderHeight = element.find('div.iamport-modal-header').outerHeight();
		const elementBottomHeight = element.find('p.button-holder').outerHeight();
		const containerHeight = document.documentElement.clientHeight - elementHeaderHeight - elementBottomHeight;

		// const elementContainer = element.find('div.iamport-modal-container');
		// elementContainer.css({
		// 	// "height": containerHeight,
		// 	"max-height": containerHeight
		// });
		
	} else {
		let elementTop = 0;
		let elementMarginBottom = 0;
		const defaultMargin = 50;

		if ( viewportHeight >= elementHeight ) {
			elementTop = offset  + (viewportHeight/2) - (elementHeight/2);
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
	
	if ( viewportHeight > elementHeight || device === "mobile" ) {
		$('body').addClass("modal-open");
	}
}

/* ---------- 아임포트 일반결제 ---------- */
function iamportAjaxCall($, iamportBox, fileFields, inputValues, buttonContext) {
	if ( iamportBox.find('#iamport-payment-submit').attr('data-progress') != 'true' )	return false;

	const order_title = buttonContext.orderTitle;
	const { payMethodsToEn } = iamportButtonFields;
	const pay_method = payMethodsToEn[iamportBox.find('select[name="pay_method"]').val()];
	const buyer_name = inputValues.buyer_name || "";
	const buyer_email = inputValues.buyer_email || "";
	const buyer_tel = (inputValues.buyer_tel || "").replace(/[^0-9-]/g, '');
	const order_amount = inputValues.order_amount || -1;
	const amount_label = inputValues.amount_label;
	const redirect_after = iamportBox.find('#iamport-payment-submit').attr('data-redirect-after');
	const payButton = iamportBox.find('#iamport-payment-submit');

	if ( order_amount < 0 ) {
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
	if ( amount_label !== null ) {
		fileFields.append('amount_label', amount_label);
	}
	
	$.ajax({
		method: 'POST',
		url: iamportButtonFields['adminUrl'],
        contentType: false,
        processData: false,
		data: fileFields
	}).done((rsp) => {
		iamportBox.css({"display": "none"});
		
		let param = {
			name: order_title,
			pay_method: pay_method,
			amount: order_amount,
			buyer_name: buyer_name,
			buyer_email: buyer_email,
			buyer_tel: buyer_tel,
			merchant_uid: rsp.order_uid,
			m_redirect_url: rsp.thankyou_url,
		};

		const pgConfig = configuration['pg_for_payment'];
		const danalConfig = configuration['etc'];
		if ( pgConfig[pay_method] && pgConfig[pay_method] !== 'default' ) {
			param.pg = pgConfig[pay_method];
		}

		//가상계좌 입금기한 적용
		if ( rsp.vbank_due ) {
			param.vbank_due = rsp.vbank_due;
		}
		
		IMP.request_pay(param, (callback) => {
			$('.dimmed-background').css({ "display": "block" });
			let resultBox = $('#iamport-result-box');

			if ( callback.success ) {
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

	}).fail(() => {
		setPaymentBtnBusy(payButton, false);
	});

	return false;
}