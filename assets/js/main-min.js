window.redirect_wait_time = 10;

/**
 * Common AnsPress functions and constructor.
 * @author Rahul Aryan
 * @license GPL 3+
 * @since 4.0
 */

// For preventing global namespace pollution, keep everything in AnsPress object.
window.AnsPress = _.extend({
	models: {},
	views: {},
	collections: {},
	modals: {},
	loadTemplate: function(id){
		if(jQuery('#apTemplate').length==0)
			jQuery('<script id="apTemplate" type="text/html"></script>').appendTo('body');

		jQuery.get(apTemplateUrl + '/' + id + ".html", function(html){
			var tempCont = jQuery('#apTemplate');
			tempCont.text(html + tempCont.text());
			AnsPress.trigger('templateLoaded');
		});
	},
	getTemplate: function(templateId){
		return function(){
			if(jQuery('#apTemplate').length==0)
				return '';

			var regex = new RegExp("#START BLOCK "+templateId+" #([\\S\\s]*?)#END BLOCK "+templateId+" #", "g");
			var match = regex.exec(jQuery('#apTemplate').text());

			if(match == null)
				return '';

			if(match[1]) return match[1];
		}
	},
	isJSONString: function(str) {
		try {
			return jQuery.parseJSON(str);
		} catch (e) {
			return false;
		}
	},
	ajaxResponse: function(data){
		data = jQuery(data);
		if( typeof data.filter('#ap-response') === 'undefined' ){
			console.log('Not a valid AnsPress ajax response.');
			return {};
		}
		var parsedJSON = this.isJSONString(data.filter('#ap-response').html());
		if(!parsedJSON || parsedJSON === 'undefined' || !_.isObject(parsedJSON))
			return {};

		return parsedJSON;
	},
	ajax: function(options){
		var self = this;
		options = _.defaults(options, {
			url: ajaxurl,
			method: 'POST',
		});

		// Convert data to query string if object.
		if(_.isString(options.data))
			options.data = jQuery.apParseParams(options.data);

		if(typeof options.data.action === 'undefined')
			options.data.action = 'ap_ajax';

		var success = options.success;
		delete options.success;
		options.success = function(data){
			console.dir( data );

			var context = options.context||null;
			var parsedData = self.ajaxResponse(data);
			if(parsedData.snackbar){
				AnsPress.trigger('snackbar', parsedData)
			}

			if(typeof success === 'function'){
				data = jQuery.isEmptyObject(parsedData) ? data : parsedData;
				success(data, context);
			}
		};

		return jQuery.ajax(options);
	},
	uniqueId: function() {
		return jQuery('.ap-uid').length;
	},
	showLoading: function(elm) {
		/*hide any existing loading icon*/
		AnsPress.hideLoading(elm);
		var customClass = jQuery(elm).data('loadclass')||'';
		var isText = jQuery(elm).is('input[type="text"]');
		var uid = this.uniqueId();

		if(jQuery(elm).is('button')||jQuery(elm).is('.ap-btn')){
			jQuery(elm).addClass('show-loading');
			$loading = jQuery('<span class="ap-loading-span"></span>');
			$loading.height(jQuery(elm).height());
			$loading.width(jQuery(elm).height());
			jQuery(elm).append($loading);
		} else {
			var el = jQuery('<div class="ap-loading-icon ap-uid '+customClass+ (isText ? ' is-text' : '') +'" id="apuid-' + uid + '"><i></i></div>');
			jQuery('body').append(el);
			var offset = jQuery(elm).offset();
			var height = jQuery(elm).outerHeight();
			var width = isText ? 40 : jQuery(elm).outerWidth();
			el.css({
				top: offset.top,
				left: isText ? offset.left + jQuery(elm).outerWidth() - 40 : offset.left,
				height: height,
				width: width
			});

			jQuery(elm).data('loading', '#apuid-' + uid);
			return '#apuid-' + uid;
		}
	},

	hideLoading: function(elm) {
		if(jQuery(elm).is('button')||jQuery(elm).is('.ap-btn')){
			jQuery(elm).removeClass('show-loading');
			jQuery(elm).find('.ap-loading-span').remove();
			jQuery(elm).prop('disabled', false);
		}else if( 'all' == elm ){
			jQuery('.ap-loading-icon').hide();
		}else{
			jQuery(jQuery(elm).data('loading')).hide();
		}
	},
	getUrlParam: function(key) {
		var qs = jQuery.apParseParams(window.location.href);
		if(typeof key !== 'undefined')
			return typeof qs[key] !== 'undefined' ? qs[key] : null;

		return qs;
	},
	modal: function(name, args){
		args = args||{};
		if(typeof this.modals[name] !== 'undefined'){
			return this.modals[name];
		}

		this.modals[name] = new AnsPress.views.Modal(_.extend({
			id: 'ap-modal-' + name,
			title: aplang.loading,
			content: '',
			size: 'medium'
		}, args));

		jQuery('#anspress').append(this.modals[name].render().$el);
		return this.modals[name];
	},
	hideModal: function(name, runCb){
		if(typeof runCb === 'undefined')
			runCb = true;

		if(typeof this.modals[name] !== 'undefined'){
			this.modals[name].hide(runCb);
			delete this.modals[name];
		}
	},
	removeHash: function(){
		var scrollV, scrollH, loc = window.location;
		// Prevent scrolling by storing the page's current scroll offset
		scrollV = document.body.scrollTop;
		scrollH = document.body.scrollLeft;

    if ('pushState' in history){

			history.pushState('', document.title, loc.pathname + loc.search);
			Backbone.history.navigate('/');
		} else {
			loc.hash = '';
		}
		// Restore the scroll offset, should be flicker free
		document.body.scrollTop = scrollV;
		document.body.scrollLeft = scrollH;

	},

	loadCSS: function(href){
		var cssLink = document.createElement('link');
		cssLink.rel = 'stylesheet';
		cssLink.href = href;
		var head = document.getElementsByTagName('head')[0];
		head.parentNode.insertBefore(cssLink, head);
	}
}, Backbone.Events);

_.templateSettings = {
	evaluate:    /<#([\s\S]+?)#>/g,
	interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
	escape:      /\{\{([^\}]+?)\}\}(?!\})/g,
};

(function($){
	//pass in just the context as a $(obj) or a settings JS object
	$.fn.autogrow = function(opts) {
		var that = $(this).css({
			overflow: 'hidden',
			resize: 'none'
		}) //prevent scrollies
		,
		selector = that.selector,
		defaults = {
				context: $(document) //what to wire events to
				,
				animate: true //if you want the size change to animate
				,
				speed: 50 //speed of animation
				,
				fixMinHeight: true //if you don't want the box to shrink below its initial size
				,
				cloneClass: 'autogrowclone' //helper CSS class for clone if you need to add special rules
				,
				onInitialize: false //resizes the textareas when the plugin is initialized
			};
			opts = $.isPlainObject(opts) ? opts : {
				context: opts ? opts : $(document)
			};
			opts = $.extend({}, defaults, opts);
			that.each(function(i, elem) {
				var min, clone;
				elem = $(elem);
			//if the element is "invisible", we get an incorrect height value
			//to get correct value, clone and append to the body.
			if (elem.is(':visible') || parseInt(elem.css('height'), 10) > 0) {
				min = parseInt(elem.css('height'), 10) || elem.innerHeight();
			} else {
				clone = elem.clone().addClass(opts.cloneClass).val(elem.val()).css({
					position: 'absolute',
					visibility: 'hidden',
					display: 'block'
				});
				$('body').append(clone);
				min = clone.innerHeight();
				clone.remove();
			}
			if (opts.fixMinHeight) {
				elem.data('autogrow-start-height', min); //set min height
			}
			elem.css('height', min);
			if (opts.onInitialize && elem.length) {
				resize.call(elem[0]);
			}
		});
			opts.context.on('keyup paste focus', selector, resize);

			function resize(e) {
				var box = $(this),
				oldHeight = box.innerHeight(),
				newHeight = this.scrollHeight,
				minHeight = box.data('autogrow-start-height') || 0,
				clone;
			if (oldHeight < newHeight) { //user is typing
				this.scrollTop = 0; //try to reduce the top of the content hiding for a second
				opts.animate ? box.stop().animate({
					height: newHeight
				}, opts.speed) : box.innerHeight(newHeight);
			} else if (!e || e.which == 8 || e.which == 46 || (e.ctrlKey && e.which == 88)) { //user is deleting, backspacing, or cutting
				if (oldHeight > minHeight) { //shrink!
					//this cloning part is not particularly necessary. however, it helps with animation
					//since the only way to cleanly calculate where to shrink the box to is to incrementally
					//reduce the height of the box until the $.innerHeight() and the scrollHeight differ.
					//doing this on an exact clone to figure out the height first and then applying it to the
					//actual box makes it look cleaner to the user
					clone = box.clone()
					//add clone class for extra css rules
					.addClass(opts.cloneClass)
					//make "invisible", remove height restriction potentially imposed by existing CSS
					.css({
						position: 'absolute',
						zIndex: -10,
						height: ''
					})
					//populate with content for consistent measuring
					.val(box.val());
					box.after(clone); //append as close to the box as possible for best CSS matching for clone
					do { //reduce height until they don't match
					newHeight = clone[0].scrollHeight - 1;
					clone.innerHeight(newHeight);
				} while (newHeight === clone[0].scrollHeight);
					newHeight++; //adding one back eliminates a wiggle on deletion
					clone.remove();
					box.focus(); // Fix issue with Chrome losing focus from the textarea.
					//if user selects all and deletes or holds down delete til beginning
					//user could get here and shrink whole box
					newHeight < minHeight && (newHeight = minHeight);
					oldHeight > newHeight && opts.animate ? box.stop().animate({
						height: newHeight
					}, opts.speed) : box.innerHeight(newHeight);
				} else { //just set to the minHeight
					box.innerHeight(minHeight);
				}
			}
		}
		return that;
	};

	jQuery.fn.apScrollTo = function(elem, toBottom, speed) {
		toBottom = toBottom||false;
		var parentPos = $(this).scrollTop() - $(this).offset().top;
		var top = toBottom ? $(this).offset().top + $(this).height() : $(this).offset().top;
		$('html, body').stop();
		$('html, body').animate({
			scrollTop: top
		}, speed == undefined ? 1000 : speed);

		if(elem != undefined)
			$(this).animate({
				scrollTop: parentPos + $(elem).offset().top
			}, speed == undefined ? 1000 : speed);

		return this;
	};

	AnsPress.views.Snackbar = Backbone.View.extend({
		id: 'ap-snackbar',
		template: '<div class="ap-snackbar<# if(success){ #> success<# } #>">{{message}}</div>',
		hover: false,
		initialize: function(){
			AnsPress.on('snackbar', this.show, this);
		},
		events: {
			'mouseover': 'toggleHover',
			'mouseout': 'toggleHover',
		},
		show: function(data){
			var self = this;
			this.data = data.snackbar;
			this.data.success = data.success;
			this.$el.removeClass('snackbar-show');
			this.render();
			setTimeout(function(){
				self.$el.addClass('snackbar-show');
			}, 0);
			this.hide();
		},
		toggleHover:function(){
			clearTimeout(this.hoveTimeOut);
			this.hover = !this.hover;
			if(!this.hover)
				this.hide();
		},
		hide: function(){
			var self = this;
			if(!self.hover)
				this.hoveTimeOut = setTimeout(function(){
					self.$el.removeClass('snackbar-show');
				}, 5000);
		},
		render: function(){
			if(this.data){
				var t = _.template(this.template);
				this.$el.html(t(this.data));
			}
			return this;
		}
	});

	AnsPress.views.Modal = Backbone.View.extend({
		className: 'ap-modal',
		template: "<div class=\"ap-modal-body<# if(typeof size !== 'undefined'){ #> ap-modal-{{size}}<# } #>\"><div class=\"ap-modal-header\"><# if(typeof title !== 'undefined' ){ #><strong>{{title}}</strong><# } #><a href=\"#\" ap=\"close-modal\" class=\"ap-modal-close\"><i class=\"apicon-x\"></i></a></div><div class=\"ap-modal-content\"><# if(typeof content !== 'undefined'){ #>{{{content}}}<# } #></div><div class=\"ap-modal-footer\"><# if(typeof buttons !== 'undefined'){ #><# _.each(buttons, function(btn){ #><a class=\"ap-modal-btn <# if(typeof btn.class !== 'undefined') { #>{{btn.class}}<# } #>\" href=\"#\" <# if(typeof btn.cb !== 'undefined') { #>ap=\"{{btn.cb}}\" apquery=\"{{btn.query}}\"<# } #>>{{btn.label}}</a><# }); #><# } #></div></div><div class=\"ap-modal-backdrop\"></div>",
		events: {
			'click [ap="close-modal"]': 'clickHide',
			'click [ap="modal-click"]': 'clickAction',
		},
		initialize: function(opt){
			opt.title = opt.title||aplang.loading;
			this.data = opt;
		},
		render: function(){
			$('html').css('overflow', 'hidden');
			var t = _.template(this.template);
			this.$el.html(t(this.data));
			return this;
		},
		clickHide: function(e){
			e.preventDefault();
			this.hide();
		},
		hide: function(runCb){
			if(typeof runCb === 'undefined')
				runCb = true;
			this.remove();
			$('html').css('overflow', '');
			if(this.data.hideCb&&runCb) this.data.hideCb(this); // Callback
			var name = this.data.id.replace('ap-modal-', '');
			if(typeof AnsPress.modals[name] !== 'undefined')
				delete AnsPress.modals[name];
		},
		setContent: function(html){
			this.$el.find('.ap-modal-content').html(html);
		},
		setTitle: function(title){
			this.$el.find('.ap-modal-header strong').text(title);
		},
		setFooter: function(content){
			this.$el.find('.ap-modal-footer').html(content);
		},
		clickAction: function(e){
			e.preventDefault();
			var targ = $(e.target);
			q = targ.data('apquery');

			if(q.cb){
				q.element = targ;
				AnsPress.trigger(q.cb, q);
			}
		}
	});

	var re = /([^&=]+)=?([^&]*)/g;
	var decode = function (str) {
			return decodeURIComponent(str.replace(/\+/g, ' '));
	};
	$.apParseParams = function (query) {
		// recursive function to construct the result object
		function createElement(params, key, value) {
			key = key + '';
			// if the key is a property
			if (key.indexOf('.') !== -1) {
				// extract the first part with the name of the object
				var list = key.split('.');
				// the rest of the key
				var new_key = key.split(/\.(.+)?/)[1];
				// create the object if it doesnt exist
				if (!params[list[0]]) params[list[0]] = {};
				// if the key is not empty, create it in the object
				if (new_key !== '') {
						createElement(params[list[0]], new_key, value);
				} else console.warn('parseParams :: empty property in key "' + key + '"');
			} else
			// if the key is an array
			if (key.indexOf('[') !== -1) {
				// extract the array name
				var list = key.split('[');
				key = list[0];
				// extract the index of the array
				var list = list[1].split(']');
				var index = list[0]
				// if index is empty, just push the value at the end of the array
				if (index == '') {
					if (!params) params = {};
					if (!params[key] || !$.isArray(params[key])) params[key] = [];
					params[key].push(value);
				} else
				// add the value at the index (must be an integer)
				{
					if (!params) params = {};
					if (!params[key] || !$.isArray(params[key])) params[key] = [];
					params[key][parseInt(index)] = value;
				}
			} else
			// just normal key
			{
					if (!params) params = {};
					params[key] = value;
			}
		}
		// be sure the query is a string
		query = query + '';
		if (query === '') query = window.location + '';
		var params = {}, e;
		if (query) {
			// remove # from end of query
			if (query.indexOf('#') !== -1) {
					query = query.substr(0, query.indexOf('#'));
			}

			// remove ? at the begining of the query
			if (query.indexOf('?') !== -1) {
					query = query.substr(query.indexOf('?') + 1, query.length);
			} else return {};
			// empty parameters
			if (query == '') return {};
			// execute a createElement on every key and value
			while (e = re.exec(query)) {
				var key = decode(e[1]);
				var value = decode(e[2]);
				createElement(params, key, value);
			}
		}
		return params;
	};
})(jQuery);

(function($){
	AnsPress.Common = {
		init: function(){
			AnsPress.on('showImgPreview', this.showImgPreview);
			AnsPress.on('formPosted', this.imageUploaded);
			AnsPress.on('ajaxBtnDone', this.uploadModal);
			AnsPress.on('ajaxBtnDone', this.commentModal);

			AnsPress.on('showModal', this.showModal);
		},
		readUrl: function(input, el) {
			if (input.files && input.files[0]) {
				var reader = new FileReader();
				reader.onload = function(e) {
					AnsPress.trigger('showImgPreview', e.target.result, el.find('.ap-upload-list'));
				}
				reader.readAsDataURL(input.files[0]);
			}
		},
		uploadModal: function(data){
			if(data.action != 'ap_upload_modal' || ! data.html)
				return;

			$modal = AnsPress.modal('imageUpload', {
				title: data.title,
				content: data.html,
				size: 'small',
			});

			var file = $modal.$el.find('input[type="file"]');
			file.on('change', function(){
				$modal.$el.find('.ap-img-preview').remove();
				AnsPress.Common.readUrl(this, $modal.$el);
			});
		},
		showImgPreview: function(src, el){
			$('<img class="ap-img-preview" src="'+src+'" />').appendTo(el);
		},
		imageUploaded: function(data){
			if(data.action!=='ap_image_upload' || typeof tinymce === 'undefined')
				return;

			if(data.files)
				$.each(data.files, function(old, newFile){
					tinymce.activeEditor.insertContent('<img src="'+newFile+'" />');
				});

			AnsPress.hideModal('imageUpload');
		},
		showModal: function(modal){
			modal.size = modal.size||'medium';
			AnsPress.modal(modal.name, {
				title: modal.title,
				content: modal.content,
				size: modal.size,
			});
		}
	};
})(jQuery);

jQuery(document).ready(function($){
	AnsPress.Common.init();

	var apSnackbarView = new AnsPress.views.Snackbar();
	$('body').append(apSnackbarView.render().$el);

	$( document ).click(function (e) {
		e.stopPropagation();
		if (!$(e.target).is('.ap-dropdown-toggle') && !$(e.target).closest('.open').is('.open') && !$(e.target).closest('form').is('form')) {
				$('.ap-dropdown').removeClass('open');
		}
	});

	// Dropdown toggle
	$('body').on('click', '.ap-dropdown-toggle, .ap-dropdown-menu > a', function(e){
		e.preventDefault();
		$('.ap-dropdown').not($(this).closest('.ap-dropdown')).removeClass('open');
		$(this).closest('.ap-dropdown').toggleClass('open');
	});

	$('body').on('click', '.ap-droptogg', function(e){
		e.preventDefault();
		$(this).closest('.ap-dropdown').removeClass('open');
		$(this).closest('#noti-dp').hide();
	});

	// Ajax button.
	$('body').on('click', '[apajaxbtn]', function(e){
		var self = this;
		e.preventDefault();

		if($(this).attr('aponce') != 'false' && $(this).is('.loaded'))
			return;

		var self = $(this);
		var query = JSON.parse(self.attr('apquery'));

		AnsPress.showLoading(self);
		AnsPress.ajax({
			data: query,
			success: function(data){
				if($(this).attr('aponce')!= 'false')
					$(self).addClass('loaded');

				AnsPress.hideLoading(e.target);

				AnsPress.trigger('ajaxBtnDone', data);

				if(typeof data.btn !== 'undefined')
					if(data.btn.hide) self.hide();

				if(typeof data.cb !== 'undefined')
					AnsPress.trigger(data.cb, data, e.target);

				// Open modal.
				if(data.modal){
					AnsPress.trigger('showModal', data.modal);
				}
			}
		})
	});

	function apAddRepeatField(el, values){
		values = values||false;
		var args = $(el).data('args');
		args['index'] = $(el).find('[datarepeatid]').length;
		var template = $('#'+args.key+'-template').text();

		var t = _.template(template);
		t = t(args);
		var regex = /(class|id|for)="([^"]+)"/g;

		var t = t.replace(regex, function(match, group) {
			return match.replace(/[[\]]/g, '');
		});

		var html = $('<div class="ap-repeatable-item" datarepeatid="'+args.index+'">'+ t +'<a href="#" class="ap-repeatable-delete">'+args.label_delete+'</a></div>');
		$.each(values, function(childName, v){
			html.find('[name="'+args.key+'['+args.index+']['+childName+']"]').val(v);
		});

		var errors = $('#'+args.key+'-errors');

		if ( errors.length > 0 ) {
			var errors_json = JSON.parse(errors.html());
			$.each(errors_json, function(i, err){
				$.each(err, function(field, messages){
					var fieldWrap = html.find('[name="'+args.key+'['+i+']['+field+']"]').closest('.ap-form-group');
					fieldWrap.addClass('ap-have-errors');
					var errContain = $('<div class="ap-field-errors"></div>');
					$.each(messages, function(code, msg){
						errContain.append('<span class="ap-field-error code-'+code+'">'+msg+'</span>');
					})
					$(errContain).insertAfter(fieldWrap.find('label'));
				});
			});
		}

		$(el).find('.ap-fieldrepeatable-item').append(html);
	}

	$('[data-role="ap-repeatable"]').each(function(){
		var self = this;


		$(this).find('.ap-repeatable-add').on('click', function(e){
			e.preventDefault();

			var self = $(this);
			var query = JSON.parse(self.attr('apquery'));
			AnsPress.showLoading(self);

			$count = $('[name="'+query.id+'-groups"]');
			query.current_groups = $count.val();
			$count.val(parseInt(query.current_groups)+1);

			$nonce = $('[name="'+query.id+'-nonce"]');
			query.current_nonce = $nonce.val();

			AnsPress.ajax({
				data: query,
				success: function(data){
					AnsPress.hideLoading(e.target);
					$(data.html).insertBefore(self);
					$nonce.val(data.nonce);
				}
			})
		});

		$(this).on('click', '.ap-repeatable-delete', function(e){
			e.preventDefault();
			$(this).closest('.ap-form-group').remove();
		});

	});

	$('body').on('click', '.ap-form-group', function(){
		$(this).removeClass('ap-have-errors');
	});

	$('body').on('click', 'button.show-loading', function(e){
		e.preventDefault();
	});

	$('body').on( 'submit', '[apform]', function(e){
		e.preventDefault();
		var self = $(this);
		var submitBtn = $(this).find('button[type="submit"]');

		if(submitBtn.length>0)
			AnsPress.showLoading(submitBtn);

    $(this).ajaxSubmit({
			url: ajaxurl,
			beforeSerialize: function() {
				if(typeof tinymce !== 'undefined')
					tinymce.triggerSave();

				$('.ap-form-errors, .ap-field-errors').remove();
				$('.ap-have-errors').removeClass('ap-have-errors');
			},
			success: function(data) {
				setTimeout(function(){
					if(submitBtn.length>0)
						AnsPress.hideLoading(submitBtn);

					data = AnsPress.ajaxResponse(data);
					if(data.snackbar){
						AnsPress.trigger('snackbar', data)
					}

					if(typeof grecaptcha !== 'undefined' && typeof widgetId1 !== 'undefined')
						grecaptcha.reset(widgetId1);

					AnsPress.trigger('formPosted', data);

					if(typeof data.form_errors !== 'undefined'){
						$formError = $('<div class="ap-form-errors"></div>').prependTo(self);

						$.each(data.form_errors, function(i, err){
							$formError.append('<span class="ap-form-error ecode-'+i+'">'+err+'</div>');
						});

						$.each(data.fields_errors, function(i, errs){
							$('.ap-field-'+i).addClass('ap-have-errors');
							$('.ap-field-'+i).find('.ap-field-errorsc').html('<div class="ap-field-errors"></div>');

							$.each(errs.error, function(code, err){
								$('.ap-field-' + i).find('.ap-field-errors').append('<span class="ap-field-error ecode-'+code+'">'+err+'</span>');
							});
						});

						self.apScrollTo();
					} else if(typeof data.hide_modal !== undefined){
						// Hide modal
						AnsPress.hideModal(data.hide_modal);
					}

					if(typeof data.redirect !== 'undefined'){
						window.location = data.redirect;
					}
				}, window.redirect_wait_time);
			}
		});
	});
	$(document).keyup(function(e) {
		if (e.keyCode == 27) {
			$lastModal = $('.ap-modal').last();
			if ( $lastModal.length>0 ){
				$name = $lastModal.attr('id').replace('ap-modal-', '');
				AnsPress.hideModal($name);
			}
		}
	});

	AnsPress.on( 'loadedMoreActivities', function(data, e){
		$(data.html).insertAfter($('.ap-activities:last-child'));
		$(e).closest('.ap-activity-item').remove();
	});

	AnsPress.tagsPreset = {
		tags: {
			delimiter: ',',
			valueField: 'term_id',
			labelField: 'name',
			searchField: 'name',
			persist: false,
			render: {
				option: function(item, escape) {
					return '<div class="ap-tag-sugitem">' +
						'<span class="name">' + escape(item.name) + '</span>' +
						'<span class="count">' + escape(item.count) + '</span>' +
						'<span class="description">' + escape(item.description) + '</span>' +
					'</div>';
				}
			},
			create: false,
			maxItems: 4
		}
	}

	AnsPress.tagElements = function ($el){
		var type = $el.data('type');
		var jsoptions = $el.data('options');
		var options = $('#'+jsoptions.id+'-options').length > 0 ? JSON.parse($('#'+jsoptions.id+'-options').html()) : {};
		var defaults = AnsPress.tagsPreset[type];
		defaults.options = options;
		defaults.maxItems = jsoptions.maxItems;

		if(false !== jsoptions.create){
			defaults.create = function(input) {
				return {
					term_id: input,
					name: input,
					description: '',
					count: 0,
				}
			};
		}

		defaults.load = function(query, callback) {
			if (!query.length) return callback();
			jQuery.ajax({
				url: ajaxurl,
				type: 'GET',
				dataType: 'json',
				data: {
					action: 'ap_search_tags',
					q: query,
					__nonce: jsoptions.nonce,
					form: jsoptions.form,
					field: jsoptions.field,
				},
				error: function() {
					callback();
				},
				success: function(res) {
					callback(res);
				}
			});
		};
		$el.selectize(defaults);
	}

	$('[aptagfield]').each(function(){
		AnsPress.tagElements($(this));
	});

	$('#anspress').on('click', '.ap-remove-parent', function(e){
		e.preventDefault();
		$(this).parent().remove();
	})
});

window.AnsPress.Helper = {
	toggleNextClass: function(el){
		jQuery(el).closest('.ap-field-type-group').find('.ap-fieldgroup-c').toggleClass('show');
	}
};

(function ($) {
	AnsPress.views.AskView = Backbone.View.extend({
		initialize: function () {},

		events: {
			'keyup [data-action="suggest_similar_questions"]': 'questionSuggestion'
		},

		suggestTimeout: null,
		questionSuggestion: function (e) {
			var self = this;
			if (disable_q_suggestion || false)
				return;

			var title = $(e.target).val();
			var inputField = this;
			if (title.length == 0)
				return;

			if (self.suggestTimeout != null) clearTimeout(self.suggestTimeout);

			self.suggestTimeout = setTimeout(function () {
				self.suggestTimeout = null;
				AnsPress.ajax({
					data: {
						ap_ajax_action: 'suggest_similar_questions',
						__nonce: ap_nonce,
						value: title
					},
					success: function (data) {
						$("#similar_suggestions").remove();
						if(data.html && $("#similar_suggestions").length===0)
							$(e.target).parent().append('<div id="similar_suggestions"></div>');

						$("#similar_suggestions").html(data.html);
					}
				});
			}, 800);
		}
	});

	var askView = new AnsPress.views.AskView({
		el: '#ap-ask-page'
	});
})(jQuery);

(function($) {
	AnsPress.models.Filter = Backbone.Model.extend({
		defaults: {
			active: false,
      label: '',
      value: ''
		}
	});

  AnsPress.collections.Filters = Backbone.Collection.extend({
    model: AnsPress.models.Filter
  });

  AnsPress.activeListFilters = $('#ap_current_filters').length > 0 ? JSON.parse($('#ap_current_filters').html()) : {};
  AnsPress.views.Filter = Backbone.View.extend({
    //tagName: 'li',
    id: function(){
      return this.model.id;
    },
    nameAttr: function(){
      if(this.multiple) return ''+this.model.get('key')+'[]';
      return this.model.get('key');
    },
    isActive: function(){
      if(this.model.get('active'))
        return this.model.get('active');

      if(this.active)
        return this.active;

      var get_value = AnsPress.getUrlParam(this.model.get('key'));
      if(!_.isEmpty(get_value)){
        var value = this.model.get('value');
        if(!_.isArray(get_value) && get_value === value)
          return true;
        if(_.contains(get_value, value)){
          this.active = true;
          return true;
        }
      }

      this.active = false;
      return false;
    },
    className: function(){
      return this.isActive() ? 'active' : '';
    },
    inputType: function(){
      return this.multiple ? 'checkbox' : 'radio';
    },
    initialize: function(options){
      this.model = options.model;
      this.multiple = options.multiple;
      this.listenTo(this.model, 'remove', this.removed);
    },
    template: '<label><input type="{{inputType}}" name="{{name}}" value="{{value}}"<# if(active){ #> checked="checked"<# } #>/><i class="apicon-check"></i>{{label}}</label>',
    events: {
      'change input': 'clickFilter'
    },
    render: function(){
      var t = _.template(this.template);
      var json = this.model.toJSON();
      json.name = this.nameAttr();
      json.active = this.isActive();
      json.inputType = this.inputType();
      this.removeHiddenField();
      this.$el.html(t(json));
      return this;
    },
    removeHiddenField: function(){
      $('input[name="'+this.nameAttr()+'"][value="'+this.model.get('value')+'"]').remove();
    },
    clickFilter: function(e){
      e.preventDefault();
      $(e.target).closest('form').submit();
    },
    removed: function(){
      this.remove();
    }
  });

  AnsPress.views.Filters = Backbone.View.extend({
    className: 'ap-dropdown-menu',
    searchTemplate: '<div class="ap-filter-search"><input type="text" search-filter placeholder="'+aplang.search+'" /></div>',
    template: '<button class="ap-droptogg apicon-x"></button><filter-items></filter-items>',
    initialize: function(options){
      this.model = options.model;
      this.multiple = options.multiple;
      this.filter = options.filter;
      this.nonce = options.nonce;
      this.listenTo(this.model, 'add', this.added);
    },
    events: {
      'keypress [search-filter]': 'searchInput'
    },
    renderItem: function(filter){
      var view = new AnsPress.views.Filter({model: filter, multiple: this.multiple});
      this.$el.find('filter-items').append(view.render().$el);
    },
    render: function(){
      var self = this;
      if(this.multiple)
        this.$el.append(this.searchTemplate);

      this.$el.append(this.template);
      this.model.each(function(filter){
        self.renderItem(filter);
      });
      return this;
    },
    search: function(q, e){
      var self = this;

      var args = { __nonce: this.nonce, ap_ajax_action: 'load_filter_'+this.filter, search: q, filter: this.filter };

      AnsPress.showLoading(e);
			AnsPress.ajax({
				data: args,
				success: function(data){
          AnsPress.hideLoading(e);
          if(data.success){
            self.nonce = data.nonce;
            while (model = self.model.first()) {
              model.destroy();
            }
            self.model.add(data.items);
          }
				}
			});
    },
    searchInput: function(e){
      var self = this;
      clearTimeout(this.searchTO);
      this.searchTO = setTimeout(function(){
        self.search($(e.target).val(), e.target);
      }, 600);
    },
    added: function(model){
      this.renderItem(model);
    }
  });

  AnsPress.views.List = Backbone.View.extend({
    el: '#ap-filters',
    initialize: function(){

    },
    events: {
      'click [ap-filter]:not(.loaded)': 'loadFilter',
      'click #ap-filter-reset': 'resetFilter'
    },
    loadFilter: function(e){
      e.preventDefault();
			var self = this;
			AnsPress.showLoading(e.currentTarget);
      var q = $.parseJSON($(e.currentTarget).attr('apquery'));
			q.ap_ajax_action = 'load_filter_'+q.filter;

			AnsPress.ajax({
				data: q,
				success: function(data){
					AnsPress.hideLoading(e.currentTarget);
          $(e.currentTarget).addClass('loaded');
					var filters = new AnsPress.collections.Filters(data.items);
          var view = new AnsPress.views.Filters({model: filters, multiple: data.multiple, filter: q.filter, nonce: data.nonce});
          $(e.currentTarget).after(view.render().$el);
				}
			});
    },
    resetFilter: function(e){
      $('#ap-filters input[type="hidden"]').remove();
      $('#ap-filters input[type="checkbox"]').prop('checked', false);
    }
  });

  $(document).ready(function(){
    new AnsPress.views.List();
  });

})(jQuery);

/**
 * Javascript code for AnsPress fontend
 * @since 4.0.0
 * @package AnsPress
 * @author Rahul Aryan
 * @license GPL 3+
 */

(function($) {
	AnsPress.models.Action = Backbone.Model.extend({
		defaults: {
			cb: '',
			post_id: '',
			title: '',
			label: '',
			query: '',
			active: false,
			header: false,
			href: '#',
			count: '',
			prefix: '',
			checkbox: '',
			multiple: false
		}
	});

	AnsPress.collections.Actions = Backbone.Collection.extend({
		model: AnsPress.models.Action
	});

	AnsPress.views.Action = Backbone.View.extend({
		id: function(){
			return this.postID;
		},
		className: function(){
			var klass = '';
			if(this.model.get('header')) klass += ' ap-dropdown-header';
			if(this.model.get('active')) klass += ' active';
			return klass;
		},
		tagName: 'li',
		template: "<# if(!header){ #><a href=\"{{href}}\" title=\"{{title}}\">{{{prefix}}}{{label}}<# if(count){ #><b>{{count}}</b><# } #></a><# } else { #>{{label}}<# } #>",
		initialize: function(options){
			this.model = options.model;
			this.postID = options.postID;
			this.model.on('change', this.render, this);
			this.listenTo(this.model, 'remove', this.removed);
		},
		events: {
			'click a': 'triggerAction'
		},
		render: function(){
			var t = _.template(this.template);
			this.$el.html(t(this.model.toJSON()));
			this.$el.attr('class', this.className());
			return this;
		},
		triggerAction: function(e){
			var q = this.model.get('query');
			if(_.isEmpty(q))
				return;

			e.preventDefault();
			var self = this;
			AnsPress.showLoading(e.target);
			var cb = this.model.get('cb');
			q.ap_ajax_action = 'action_'+cb;

			AnsPress.ajax({
				data: q,
				success: function(data){
					setTimeout(function(){
						AnsPress.hideLoading(e.target);
						if(data.redirect) window.location = data.redirect;

						if(data.success && ( cb=='status' || cb=='toggle_delete_post'))
							AnsPress.trigger('changedPostStatus', {postID: self.postID, data:data, action:self.model});

						if(data.action){
							self.model.set(data.action);
						}
						self.renderPostMessage(data);
						if(data.deletePost) AnsPress.trigger('deletePost', data.deletePost);
						if(data.answersCount) AnsPress.trigger('answerCountUpdated', data.answersCount);
					}, window.redirect_wait_time);
				}
			});
		},
		renderPostMessage: function(data){
			if(!_.isEmpty(data.postmessage))
				$('[apid="'+this.postID+'"]').find('postmessage').html(data.postmessage);
			else
				$('[apid="'+this.postID+'"]').find('postmessage').html('');
		},
		removed: function(){
      this.remove();
    }
	});


	AnsPress.views.Actions = Backbone.View.extend({
		id: function(){
			return this.postID;
		},
		searchTemplate: '<div class="ap-filter-search"><input type="text" search-filter placeholder="'+aplang.search+'" /></div>',
		tagName: 'ul',
		className: 'ap-actions',
		events: {
			'keyup [search-filter]': 'searchInput'
		},
		initialize: function(options){
			this.model = options.model;
			this.postID = options.postID;
			this.multiple = options.multiple;
			this.action = options.action;
			this.nonce = options.nonce;

			AnsPress.on('changedPostStatus', this.postStatusChanged, this);
			this.listenTo(this.model, 'add', this.added);
		},
		renderItem: function(action){
			var view = new AnsPress.views.Action({ model: action, postID: this.postID });
			this.$el.append(view.render().$el);
		},
		render: function(){
			var self = this;
			if(this.multiple)
        this.$el.append(this.searchTemplate);

			this.model.each(function(action){
				self.renderItem(action);
			});

			return this;
		},
		postStatusChanged: function(args){
			if(args.postID !== this.postID) return;

			// Remove post status class
			$("#post-"+this.postID).removeClass( function() {
				return this.className.split(' ').filter(function(className) {return className.match(/status-/)}).join(' ');
			});

			$("#post-"+this.postID).addClass('status-'+args.data.newStatus);
			var activeStatus = this.model.where({cb: 'status', active: true });
			activeStatus.forEach(function(status){
				status.set({active: false});
			});
		},
		searchInput: function(e){
      var self = this;

      clearTimeout(this.searchTO);
      this.searchTO = setTimeout(function(){
        self.search($(e.target).val(), e.target);
      }, 600);
    },
		search: function(q, e){
      var self = this;

      var args = { nonce: this.nonce, ap_ajax_action: this.action, search: q, filter: this.filter, post_id: this.postID };

      AnsPress.showLoading(e);
			AnsPress.ajax({
				data: args,
				success: function(data){
					console.log(data);
          AnsPress.hideLoading(e);
          if(data.success){
            self.nonce = data.nonce;
						//self.model.reset();
            while (m = self.model.first()) {
               self.model.remove(m);
            }
            self.model.add(data.actions);
          }
				}
			});
    },
		added: function(model){
      this.renderItem(model);
    }
	});

	AnsPress.models.Post = Backbone.Model.extend({
		idAttribute: 'ID',
		defaults:{
			actionsLoaded: false,
			hideSelect: ''
		}
	});

	AnsPress.views.Post = Backbone.View.extend({
		idAttribute: 'ID',
		templateId: 'answer',
		tagName: 'div',
		actions: { view: {}, model: {} },
		id: function(){
			return 'post-' + this.model.get('ID');
		},
		initialize: function(options){
			this.listenTo(this.model, 'change:vote', this.voteUpdate);
			this.listenTo(this.model, 'change:hideSelect', this.selectToggle);
		},
		events: {
			'click [ap="select-answer-modal-open"]' : 'selectAnswerModalOpen',
			'click [ap-vote] > a': 'voteClicked',
			'click [ap="actiontoggle"]:not(.loaded)': 'postActions',
		},
		voteClicked: function(e){
			e.preventDefault();
			// disable이면 클릭이 안되지
			if($(e.target).is('.disable'))
				return;

			self = this;
			var type = $(e.target).is('.vote-up') ? 'vote_up' : 'vote_down';
			var originalValue = _.clone(self.model.get('vote'));
			var vote = _.clone(originalValue);

			if(type === 'vote_up')
				vote.net = ( vote.active === 'vote_up' ? vote.net - 1 : vote.net + 1);
			else
				vote.net = ( vote.active === 'vote_down' ? vote.net + 1 : vote.net - 1);

			self.model.set('vote', vote);
			var q = $.parseJSON($(e.target).parent().attr('ap-vote'));
			q.ap_ajax_action = 'vote';
			q.type = type;

			AnsPress.ajax({
				data: q,
				success: function(data) {
					if (data.success && _.isObject(data.voteData))
						self.model.set('vote', data.voteData);
						if ( typeof data.allow_cancel_vote !== 'undefined' && ! data.allow_cancel_vote ) {
							self.$el.find('.vote-up').addClass( 'disable' );
							self.$el.find('.vote-down').addClass( 'disable' );
						}
					else
						self.model.set('vote', originalValue); // Restore original value on fail
				}
			})
		},
		voteUpdate: function(post){
			var self = this;
			this.$el.find('[ap="votes_net"]').text(this.model.get('vote').net);
			_.each(['up', 'down'], function(e){
				self.$el.find('.vote-'+e).removeClass('voted disable').addClass(self.voteClass('vote_'+e));
			});
		},
		voteClass: function(type){
			type = type||'vote_up';
			var curr = this.model.get('vote').active;
			var klass = '';
			if(curr === 'vote_up' && type === 'vote_up')
				klass = 'active';

			if(curr === 'vote_down' && type === 'vote_down')
				klass = 'active';

			// active가 없다는거는, 지금 새로 눌렀다는거야
			if(type !== curr && curr !== '')
				klass += ' disable';

			return klass + ' prist';
		},
		render: function(){
			var attr = this.$el.find('[ap-vote]').attr('ap-vote');
			this.model.set('vote', $.parseJSON(attr), {silent: true});
			return this;
		},
		postActions: function(e){
			var self = this;
			var q = $.parseJSON($(e.target).attr('apquery'));
			if(typeof q.ap_ajax_action === 'undefined')
				q.ap_ajax_action = 'post_actions';

			AnsPress.ajax({
				data: q,
				success: function(data){
					AnsPress.hideLoading(e.target);
					$(e.target).addClass('loaded');
					self.actions.model = new AnsPress.collections.Actions(data.actions);
					self.actions.view = new AnsPress.views.Actions({ model: self.actions.model, postID: self.model.get('ID') });
					self.$el.find('postActions .ap-actions').html(self.actions.view.render().$el);
				}
			});
		},
		selectAnswer: function(e, modal, cell){
			e.preventDefault();
			var self = this;
			var q = $.parseJSON($(e.target).attr('apquery'));
			q.action = 'ap_toggle_best_answer';
			console.dir( q );
			var positive_btn = modal.find( '.positive.button' );
			AnsPress.showLoading(positive_btn);
			AnsPress.ajax({
				data: q,
				success: function(data){
					AnsPress.hideLoading(positive_btn);
					modal.modal('hide');
					if(data.success){
						if(data.selected){
							cell.addClass('best-answer');
							AnsPress.trigger('answerToggle', [self.model, true]);
							if ( (typeof data.allow_unselect_answer !== 'undefined') && ! data.allow_unselect_answer ) {
								$(e.target).remove();
							} else {
								$(e.target).addClass('active').text(data.label);
							}
						} else if ( (typeof data.allow_unselect_answer !== 'undefined') && data.allow_unselect_answer ) {
							cell.removeClass('best-answer');
							$(e.target).removeClass('active').text(data.label);
							AnsPress.trigger('answerToggle', [self.model, false]);
						}
					}
				}
			});
		},
		selectAnswerModalOpen: function(e){
			var self = this;
			var cell = $(e.target).closest('.ap-cell');
			var modal = $('.select-answers-modal');
			modal.modal({
				closable : true,
				onApprove : function(){
					self.selectAnswer(e, modal, cell);
					return false;
				}
			}).modal('show');
		},
		selectToggle: function(){
			if(this.model.get('hideSelect'))
				this.$el.find('[ap="select-answer-modal-open"]').addClass('hide');
			else
				this.$el.find('[ap="select-answer-modal-open"]').removeClass('hide');
		}
	});

	AnsPress.collections.Posts = Backbone.Collection.extend({
		model: AnsPress.models.Post,
		initialize: function(){
			var loadedPosts = [];
			$('[ap="question"],[ap="answer"]').each(function(e){
				loadedPosts.push({ 'ID' : $(this).attr('apId')});
			});
			this.add(loadedPosts);
		}
	});

	AnsPress.views.SingleQuestion = Backbone.View.extend({
		initialize: function(){
			this.listenTo(this.model, 'add', this.renderItem);
			AnsPress.on('answerToggle', this.answerToggle, this);
			AnsPress.on('deletePost', this.deletePost, this);
			AnsPress.on('answerCountUpdated', this.answerCountUpdated, this);
			AnsPress.on('formPosted', this.formPosted, this);
			this.listenTo(AnsPress, 'commentApproved', this.commentApproved);
			this.listenTo(AnsPress, 'commentDeleted', this.commentDeleted);
			this.listenTo(AnsPress, 'commentCount', this.commentCount);
			this.listenTo(AnsPress, 'formPosted', this.submitComment);
		},
		events: {
			'click [ap="loadEditor"]': 'loadEditor',
		},
		renderItem: function(post){
			var view = new AnsPress.views.Post({ model: post, el: '[apId="'+post.get('ID')+'"]' });
			view.render();
		},
		render: function(){
			var self = this;
			this.model.each(function(post){
				self.renderItem(post);
			});

			return self;
		},
		loadEditor: function(e){
			var self = this;
			AnsPress.showLoading(e.target);

			AnsPress.ajax({
				data: $(e.target).data('apquery'),
				success: function(data){
					AnsPress.hideLoading(e.target);
					$('#ap-form-main').html(data);
					$(e.target).closest('.ap-minimal-editor').removeClass('ap-minimal-editor');
				}
			});
		},
		/**
		 * Handles answer form submission.
		 */
		formPosted: function(data){
			if(data.success && data.form === 'answer'){
				AnsPress.trigger('answerFormPosted', data);
				$('apanswersw').show();
				tinymce.remove();

				// Clear editor contents
				$('#ap-form-main').html('');
				$('#answer-form-c').addClass('ap-minimal-editor');

				// Append answer to the list.
				$('apanswers').append($(data.html).hide());
				$(data.div_id).slideDown(300);
				$(data.div_id).apScrollTo(null, true);
				this.model.add({'ID': data.ID});
				AnsPress.trigger('answerCountUpdated', data.answersCount);

				// reactive MathJax
				if ( window.MathJax ) {
					var math = $(data.div_id)[0];
					if ( math ) {
						MathJax.Hub.Queue(["Typeset", MathJax.Hub, math]);
					}
				}
			}
		},
		answerToggle: function(args){
			this.model.forEach(function(m, i) {
				if(args[0] !== m)
					m.set('hideSelect', args[1]);
			});
		},
		deletePost: function(postID){
			this.model.remove(postID);
			$('#post-'+postID).slideUp(400, function(){
				$(this).remove();
			});
		},
		answerCountUpdated: function(counts){
			$('[ap="answers_count_t"]').text(counts.text);
		},
		commentApproved: function(data, elm){
			$('#comment-' + data.comment_ID ).removeClass('unapproved');
			$(elm).remove();
			if(data.commentsCount)
				AnsPress.trigger('commentCount', {count: data.commentsCount, postID: data.post_ID });
		},
		commentDeleted: function(data, elm){
			$(elm).closest('apcomment').css('background', 'red');
			setTimeout(function(){
				$(elm).closest('apcomment').remove();
			}, 1000);
			if(data.commentsCount)
				AnsPress.trigger('commentCount', {count: data.commentsCount, postID: data.post_ID });
		},
		commentCount: function(args){
			var find = $('[apid="'+args.postID+'"]');
			find.find('[ap-commentscount-text]').text(args.count.text);
			if(args.count.unapproved > 0 )
				find.find('[ap-un-commentscount]').addClass('have');
			else
				find.find('[ap-un-commentscount]').removeClass('have');

			find.find('[ap-un-commentscount]').text(args.count.unapproved);
		},
		submitComment: function(data){
			if(!('new-comment' !== data.action || 'edit-comment' !== data.action))
				return;

			if(data.success){
				AnsPress.hideModal('commentForm');
				if(data.action === 'new-comment')
					$('#comments-'+data.post_id).html(data.html);

				if(data.action === 'edit-comment'){
					$old = $('#comment-'+data.comment_id);
					$(data.html).insertAfter($old);
					$old.remove();

					$('#comment-'+data.comment_id).css('backgroundColor', 'rgba(255, 235, 59, 1)');
					setTimeout(function(){
						$('#comment-'+data.comment_id).removeAttr('style');
					}, 500)
				}

				if(data.commentsCount)
					AnsPress.trigger('commentCount', {count: data.commentsCount, postID: data.post_id });
			}
		}
	});

  var AnsPressRouter = Backbone.Router.extend({
		routes: {
			'comment/:commentID': 'commentRoute',
			//'comment/:commentID/edit': 'editCommentsRoute',
			'comments/:postID/all': 'commentsRoute',
			'comments/:postID': 'commentsRoute',
		},
		commentRoute: function (commentID) {
			self = this;

			AnsPress.hideModal('comment', false);
			$modal = AnsPress.modal('comment', {
				content: '',
				size: 'medium',
				hideCb: function(){
					AnsPress.removeHash();
				}
			});
			$modal.$el.addClass('single-comment');
			AnsPress.showLoading($modal.$el.find('.ap-modal-content'));
			AnsPress.ajax({
				data: {comment_id: commentID, ap_ajax_action: 'load_comments'},
				success: function(data){
					if(data.success){
						$modal.setTitle(data.modal_title);
						$modal.setContent(data.html);
						AnsPress.hideLoading($modal.$el.find('.ap-modal-content'));
					}
				}
			});
		},

		commentsRoute: function(postId, paged){
			self = this;
			AnsPress.ajax({
				data: {post_id: postId, ap_ajax_action: 'load_comments'},
				success: function(data){
					$('#comments-'+postId).html(data.html);
				}
			});
		},
		editCommentsRoute: function(commentID){
			self = this;
			AnsPress.hideModal('commentForm', false);
			AnsPress.modal('commentForm', {
				hideCb: function(){
					AnsPress.removeHash();
				}
			});

			AnsPress.showLoading(AnsPress.modal('commentForm').$el.find('.ap-modal-content'));
			AnsPress.ajax({
				data: {comment: commentID, ap_ajax_action: 'comment_form'},
				success: function(data){
					AnsPress.hideLoading(AnsPress.modal('commentForm').$el.find('.ap-modal-content'));
					AnsPress.modal('commentForm').setTitle(data.modal_title);
					AnsPress.modal('commentForm').setContent(data.html);
				}
			});
		}
  });

	$('[ap="actiontoggle"]').click(function(){
		if(!$(this).is('.loaded'))
			AnsPress.showLoading(this);
	});

	$(document).ready(function(){
		var apposts = new AnsPress.collections.Posts();
		var singleQuestionView = new AnsPress.views.SingleQuestion({ model: apposts, el: '#anspress' });
		singleQuestionView.render();

		var anspressRouter = new AnsPressRouter();
		if(!Backbone.History.started)
			Backbone.history.start();
	});

})(jQuery);


(function($){
	'use strict';

	function apSanitizeTitle(str) {
	  str = str.replace(/^\s+|\s+$/g, ''); // trim
	  str = str.toLowerCase();

	  // remove accents, swap ñ for n, etc
	  var from = "ãàáäâẽèéëêìíïîõòóöôùúüûñç·/_,:;";
	  var to   = "aaaaaeeeeeiiiiooooouuuunc------";

	  /*for (var i=0, l=from.length ; i<l ; i++) {
	  	str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
	  }*/

	  str = str.replace(/\s+/g, '-') // collapse whitespace and replace by -
	    .replace(/-+/g, '-'); // collapse dashes

	    return str;
	}

	function apAddTag(str, container){
		str = str.replace(/,/g, '');
		str = str.trim();
		str = apSanitizeTitle(str);
		
		if( str.length > 0 ){

			var htmlTag = {
				element : 'li',
				class : 'ap-tagssugg-item',
				itemValueClass : 'ap-tag-item-value',
				button : {
					class : 'ap-tag-add',
					icon : 'apicon-plus',
				},
				input : '',
				accessibilityText : apTagsTranslation.addTag
			}
			
			// Add tag to the main container (holder list), 
			// Else add tag to a specific container (suggestion list)
			if(!container){
				
				var container = '#ap-tags-holder';
				htmlTag.button.class = 'ap-tag-remove';
				htmlTag.button.icon = 'apicon-x';
				htmlTag.input = '<input type="hidden" name="tags[]" value="'+str+'" />';
				htmlTag.accessibilityText = apTagsTranslation.deleteTag;
				
				var exist_el = false;
				$(container).find('.'+htmlTag.class).find('.'+htmlTag.itemValueClass).each(function(index, el) {
					if(apSanitizeTitle($(this).text()) == str)
						exist_el = $(this);
				});
				if (exist_el !== false) { // If the element already exist, stop and dont add tag
					exist_el.animate({opacity: 0}, 100, function(){
						exist_el.animate({opacity: 1}, 400);
					});
					return; 
				}
				
				if (!$('#tags').is(':focus'))
					$('#tags').val('').focus();
					
				$('#ap-tags-suggestion').hide();
				
				// Message for screen reader
				// Timeout used to resolve a bug with JAWS and IE...
				setTimeout(function() {
					$('#ap-tags-aria-message').text(str + " " + apTagsTranslation.tagAdded);
				}, 250);
			}
			
			var html = $('<'+htmlTag.element+' class="'+htmlTag.class+'" title="'+htmlTag.accessibilityText+'"><button role="button" class="'+htmlTag.button.class+'"><span class="'+htmlTag.itemValueClass+'">'+str+'</span><i class="'+htmlTag.button.icon+'"></i></button>'+htmlTag.input+'</'+htmlTag.element+'>');
			html.appendTo(container).fadeIn(300);
			
		}
	}

	function apTagsSuggestion(value){
		if(typeof window.tagsquery !== 'undefined'){
			window.tagsquery.abort();
		}
		window.tagsquery = jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action:'ap_tags_suggestion',
				q: value
			},
			context:this,
			dataType:'json',
			success: function(data){
				AnsPress.hideLoading(this);
				
				console.log(data);
				
				$('#ap-tags-suggestion').html('');
				
				if(!data.status)
					return;

				if (!$('#ap-tags-suggestion').is(':visible')) {
					$('#ap-tags-suggestion').show();
				}
						
				if(data['items']){
					$.each(data['items'], function(index, val) {
						val = decodeURIComponent(val);
						var holderItems = [];
						$("#ap-tags-holder .ap-tag-item-value").each(function() {
							holderItems.push($(this).text())
						});
						if ($.inArray(val, holderItems)<0) // Show items that was not already inside the holder list
							apAddTag(val, '#ap-tags-suggestion');
					});
				}
				
				// Message for screen reader
				// Timeout used to resolve a bug with JAWS and IE...
				setTimeout(function() {
					$('#ap-tags-aria-message').text(apTagsTranslation.suggestionsAvailable);
				}, 250);
			}
		});
	}

	$(document).ready(function(){

		$('#tags').on('apAddNewTag',function(e){
			e.preventDefault();
			apAddTag($(this).val().trim(','));
			$(this).val('');
		});

		$('#tags').on('keydown', function(e) {
			if(e.keyCode == 13) { // Prevent submit form on Enter
			  	e.preventDefault();
			  	return false;
			}
			if(e.keyCode == 38 || e.keyCode == 40) {
				var inputs = $('#ap-tags-suggestion').find('.ap-tag-add');
				var focused = $('#ap-tags-suggestion').find('.focus');
				var index = inputs.index(focused);
				
				if(index != -1) {
					if(e.keyCode == 38) // up arrow
						index--;
					if(e.keyCode == 40) // down arrow
						index++;
				}
				else {
					if(e.keyCode == 38) // up arrow
						index = inputs.length-1;
					if(e.keyCode == 40) // down arrow
						index = 0;
				}
				
				if (index >= inputs.length)
					index = -1;
				
				inputs.removeClass('focus');
				
				if(index != -1) {
					inputs.eq(index).addClass('focus');
					$(this).val(inputs.eq(index).find('.ap-tag-item-value').text());
				} 
				else {
					$(this).val($(this).attr('data-original-value'));
				}
			}
		});

		$('#tags').on('keyup focus', function(e) {
			e.preventDefault();
			var val = $(this).val().trim();
			clearTimeout(window.tagtime);
			if(e.keyCode != 9 && e.keyCode != 37 && e.keyCode != 38 && e.keyCode != 39 && e.keyCode != 40) { // Do nothing on Tab and arrows keys
				if(e.keyCode == 13 || e.keyCode == 188 ) { // "Enter" or ","
					clearTimeout(window.tagtime);
					$(this).trigger('apAddNewTag');
				} else {
					$(this).attr('data-original-value', $(this).val());
					window.tagtime = setTimeout(function() {
						apTagsSuggestion(val);
					}, 200);
				}
			}
		});
		
		$('#ap-tags-suggestion').on('click', '.ap-tagssugg-item', function(e) {
			apAddTag($(this).find('.ap-tag-item-value').text());
			$(this).remove();
		});

		$('body').on('click focusin', function(e) {
			if ($('#ap-tags-suggestion').is(':visible') && $(e.target).parents('#ap-tags-add').length <= 0)
			  	$('#ap-tags-suggestion').hide();
		});
		
		$('body').on('click', '.ap-tagssugg-item', function(event) {
			var itemValue = $(this).find('.ap-tag-item-value').text();
			
			// Message for screen reader
			// Timeout used to resolve a bug with JAWS and IE...
			setTimeout(function() {
				$('#ap-tags-aria-message').text(itemValue + " " + apTagsTranslation.tagRemoved);
			}, 250);
			
			$(this).remove();
			$('#ap-tags-list-title').focus();
		});
		
		// Message used by screen reader to get suggestions list or a confirmation when a tag is added
		$('body').append('<div role="status" id="ap-tags-aria-message" aria-live="polite" aria-atomic="true" class="sr-only"></div>');
	})

})(jQuery)

jQuery(document).ready(function () {

  /* ------------------------------------------------------------------------- *
    *  Header
  /* ------------------------------------------------------------------------- */ (function($){
  var login_trigger__btn = $('.login-trigger__btn');
  var login_modal = $('.login-modal');
  if ( login_trigger__btn.length > 0 && login_modal.length > 0 ) {
    login_trigger__btn.on( 'click', function(){
      login_modal.modal({
        centered: true
      })
      .modal('show');
    });
  }
  var user_nav__dropdown = $('.user-nav > .dropdown');
  if ( user_nav__dropdown.length > 0 ) {
    user_nav__dropdown.dropdown();
  }
  var mobile_menu = $('#mobile-menu');
  if ( mobile_menu.length > 0 ) {
    mobile_menu.mmenu(
      {
        "extensions": [
          "pagedim-black"
        ]
      },
      {
        offCanvas : {
          pageSelector: '#page'
        }
      } 
    );
    var API = mobile_menu.data('mmenu');
    var trigger = $('.mobile-menu-trigger > .wrap');
    API.bind( "open:finish", function(){
      if ( !trigger.hasClass('active') ) {
        trigger.addClass('active');
      }
    } );
    API.bind( "close:finish", function(){
      if ( trigger.hasClass('active') ) {
        trigger.removeClass('active');
      }
    } );
    trigger.on('click', function(){
      API.open();
    });
  
    setTimeout(function(){
      mobile_menu.removeClass( 'd-none' );
    }, 500);
  }
  
  })(jQuery);
  
  
  /* ------------------------------------------------------------------------- *
    *  Front Page
  /* ------------------------------------------------------------------------- */ (function($){
  
  /*  Category search
  /* --------------------------------------------------- */
  var front_category_search = $('.front-category-search select');
  if ( front_category_search.length > 0 ) {
    front_category_search.dropdown({
      maxSelections : 3,
      onAdd: function(value, text, $did_select) {
        // console.dir( $('.ui.label.transition.visible') );
      },
      onLabelCreate : function(value, text) {
        var label = this[0];
        // console.dir(label);
        // var no_space_text = label.textContent.replace(/u200D/g, '');
  
        // |&emsp|&nbsp
        // u2003 => emspace
        // u200D => zero width joiner
        // var no_space_text = text.match(/u2003;/);
        // console.log(no_space_text);
        // label.innerHTML = no_space_text + "<i class='delete icon'></i>";
        // var no_space_text = label.textContent.match(/[\u2003 ]+/);
        var no_space_text = label.textContent.replace(/[^\S]+/g, '');
        label.textContent = no_space_text;
        return $(label);
      }
    });
  }  })(jQuery);
  
  /* ------------------------------------------------------------------------- *
    *  Question list page
  /* ------------------------------------------------------------------------- */ (function($){
  
  /*  Search filter
  /* --------------------------------------------------- */
  var main_filter = {
    category : $('.question-filter__category select'),
    year : $('.question-filter__year select'),
    session : $('.question-filter__session select'),
    did_select : $('.question-filter__did_select select'),
    has_answer : $('.question-filter__has_answer select')
  };
  
  if ( main_filter.category.length > 0 ) {
    main_filter.category.SumoSelect({ 
      placeholder : '카테고리 리스트',
      search: true,
      selectAll : false,
    });
  }
  if ( main_filter.year.length > 0 ) {
    main_filter.year.SumoSelect({
      placeholder : '년도',
      search : false,
      selectAll : false,
    });
  }
  if ( main_filter.session.length > 0 ) {
    main_filter.session.SumoSelect({
      placeholder : '회차',
      selectAll : false,
      search : false
    });
  }
  if ( main_filter.did_select.length > 0 ) {
    main_filter.did_select.SumoSelect({
      placeholder : '채택여부'
    });
  }
  if ( main_filter.has_answer.length > 0 ) {
    main_filter.has_answer.SumoSelect({
      placeholder : '답변여부'
    });
  } 

})(jQuery);
  
  
  /* ------------------------------------------------------------------------- *
    *  Licence map
  /* ------------------------------------------------------------------------- */ (function($){
  var licence_map = $('.licence-map .ui.menu .item');
  if ( licence_map.length > 0 ) {
    licence_map.tab();
  } })(jQuery);
  
  
  /* ------------------------------------------------------------------------- *
    *  Ask
  /* ------------------------------------------------------------------------- */ (function($){
  
  /*  Search filter
  /* --------------------------------------------------- */
  })(jQuery);

  /* ------------------------------------------------------------------------- *
    *  Single Question
  /* ------------------------------------------------------------------------- */ (function($){

    /*  wish list
    /* --------------------------------------------------- */
    // Add to wishlist button.
    $('[apwish]').click(function(e){
      e.preventDefault();
      var self = $(this);
      var query = JSON.parse(self.attr('apquery'));
      if ( ! self.hasClass('show-loading') ) {
        AnsPress.showLoading(self);
        AnsPress.ajax({
          data: query,
          success: function(data){
            if(data.label) self.text(data.label);
            if (data.status == 'deleted') { 
              self.removeClass('active') 
            } else {
              self.addClass('active');
            }
            AnsPress.hideLoading(self);
          }
        });
      }
    });
  
    /*  buy answer
    /* --------------------------------------------------- */
    var purchase_answers = {
      button : $('.purchase-answers-button'),
      modal : $('.purchase-answers-modal'),
      positive_btn : $('.purchase-answers-modal .button.positive'),
    }
    if ( purchase_answers.button.length > 0 && purchase_answers.modal.length > 0 ) {
      purchase_answers.button.on('click', function(){
        purchase_answers.modal.modal({
          closable : true,
          onApprove : function(){
            var self = purchase_answers.positive_btn;
            var query = JSON.parse(self.attr('apquery'));
            if ( ! self.hasClass('show-loading') ) {
              AnsPress.showLoading(self);
              AnsPress.ajax({
                data: query,
                success: function(data){
                  if(data.label) self.text(data.label);
                  if (data.status == 'deleted') {
                    self.removeClass('active')
                  } else {
                    self.addClass('active');
                  }
                  AnsPress.hideLoading(self);
                  if(typeof data.redirect !== 'undefined'){
                    setTimeout( function(){
                      window.location = data.redirect;
                    }, 600 );
                  }
                }
              });
            }
            return false;
          }
        }).modal('show');
      });
    }

    var select_answer_modal_btn = $('.ap-btn-select-answer-modal');
    var select_answer_modal = $('.select-answers-modal');
    // stop
    if ( select_answer_modal_btn.length < 0 && select_answer_modal.length > 0 ) {
      select_answer_modal_btn.on( 'click', function(){
        select_answer_modal.modal({
          closable : true,
          onApprove : function(e){
            alert( "Select Answer" );
            var self = this;
            var q = $.parseJSON($(e).attr('apquery'));
            q.action = 'ap_toggle_best_answer';
            AnsPress.showLoading(e);
            AnsPress.ajax({
              data: q,
              success: function(data){
                AnsPress.hideLoading(e);
                if(data.success){
                  if(data.selected){
                    var cell = selected_answer_modal_btn.closest( 'ap-cell' );
                    cell.addClass('best-answer');
                    AnsPress.trigger('answerToggle', [self.model, true]);
                    if ( (typeof data.allow_unselect_answer !== 'undefined') && ! data.allow_unselect_answer ) {
                      $(e.target).remove();
                    } else {
                      $(e.target).addClass('active').text(data.label);
                    }
                  } else if ( (typeof data.allow_unselect_answer !== 'undefined') && data.allow_unselect_answer ) {
                    self.$el.removeClass('best-answer');
                    $(e.target).removeClass('active').text(data.label);
                    AnsPress.trigger('answerToggle', [self.model, false]);
                  }
                }
              }
            });
            return false;
          }
        }).modal('show');
      } );
    }
  })(jQuery);

});

// @codekit-prepend "common.js", "ask.js", "list.js", "question.js", "tags.js", "theme.js";


