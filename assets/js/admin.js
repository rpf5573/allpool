// @codekit-prepend "common.js";

/* on start */

'use strict';

(function($) {
  AnsPress.views.Answer = Backbone.Model.extend({
    defaults: {
      ID: '',
      content: '',
      deleteNonce: '',
      comments: '',
      activity : '',
      author: '',
      editLink: '',
			trashLink: '',
      status: '',
      selected: '',
			avatar: '',
    }
  });

  AnsPress.collections.Answers = Backbone.Collection.extend({
    url: ajaxurl+'?action=ap_ajax&ap_ajax_action=get_all_answers&question_id='+currentQuestionID,
    model: AnsPress.views.Answer
  });

  AnsPress.views.Answer = Backbone.View.extend({
    className: 'ap-ansm clearfix',
    id: function(){
      return this.model.get('ID');
    },
    initialize: function(options){
      if(options.model)
        this.model = options.model;
    },
    template: function(){
      return $('#ap-answer-template').html()
    },
    render: function(){
      if(this.model){
				console.dir( this.model );
				// console.dir( this.$el );
				var t = _.template(this.template());
				this.$el.html(t(this.model.toJSON()));
				if ( this.model.attributes.status == 'trash' ) {
					//console.dir( this.$el.find('.answer-actions').remove() );
					this.$el.find('.answer-actions > .edit').remove();
					this.$el.find('.answer-actions > .trash').remove();
					this.$el.find('.answer-actions > .select_answer').remove();
					this.$el.find('.answer-actions > .clone').remove();
				} else {
					this.$el.find('.answer-actions > .delete').remove();
					this.$el.find('.answer-actions > .untrash').remove();
				}

				if ( this.model.attributes.selected !== "0" ) {
					this.$el.addClass('selected');
					// var checkbox = this.$el.find('.answer-actions .select_answer_checkbox')
					this.$el.find('.answer-actions .select_answer_checkbox').prop('checked', true);
				}
      }
      return this;
    }
  });

  AnsPress.views.Answers = Backbone.View.extend({
    initialize: function(options){
      this.model = options.model;
      this.model.on('add', this.answerFetched, this);
    },
    renderItem: function(ans){
      var view = new AnsPress.views.Answer({model: ans});
      this.$el.append(view.render().$el);
    },
    render: function(){
      var self = this;
      if(this.model){
        this.model.each(function(ans){
          self.renderItem(ans);
        });
      }

      return this;
    },
    answerFetched: function(answer){
      this.renderItem(answer);
    }
  });

  if( currentQuestionID ) {
    var answers = new AnsPress.collections.Answers();
    var answersView = new AnsPress.views.Answers({model: answers, el: '#answers-list'});
    answersView.render();
    answers.fetch();
  }

})(jQuery);

jQuery(function () {
	jQuery.fn.apAjaxQueryString = function () {
		var query = jQuery(this).data('query').split("::");

		var newQuery = {};

		newQuery['action'] = 'ap_ajax';
		newQuery['ap_ajax_action'] = query[0];
		newQuery['__nonce'] = query[1];
		newQuery['args'] = {};

		var newi = 0;
		jQuery.each(query,function(i){
			if(i != 0 && i != 1){
				newQuery['args'][newi] = query[i];
				newi++;
			}
		});

		return newQuery;
	};

	/* create document */
	APjs.admin = new APjs.admin();
	/* need to call init manually with jQuery */
	APjs.admin.initialize();
});

/* namespace */
window.APjs = {};
APjs.admin = function () {};

(function($){
	APjs.admin.prototype = {

		/* automatically called */
		initialize: function () {
			this.renameTaxo();
			this.editPoints();
			this.savePoints();
			this.deleteFlag();
			this.ajaxBtn();
			this.statistic();
		},

		renameTaxo: function () {
			jQuery('.ap-rename-taxo').click(function (e) {
				e.preventDefault();
				jQuery.ajax({
					url: ajaxurl,
					data: {
						action: 'ap_taxo_rename'
					},
					context: this,
					success: function (data) {
						jQuery(this).closest('.error').remove();
						location.reload();
					}
				});
				return false;
			});
		},
		editPoints: function () {
			jQuery('.wp-admin').on('click', '[data-action="ap-edit-reputation"]', function (e) {
				e.preventDefault();
				var id = jQuery(this).attr('href');
				jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {
						action: 'ap_edit_reputation',
						id: id
					},
					context: this,
					dataType: 'json',
					success: function (data) {
						if (data['status']) {
							jQuery('#ap-reputation-edit').remove();
							jQuery('#anspress-reputation-table').hide();
							jQuery('#anspress-reputation-table').after(data['html']);
						}
					}
				});
			});
		},
		savePoints: function () {
			jQuery('.wp-admin').on('submit', '[data-action="ap-save-reputation"]', function (e) {
				e.preventDefault();
				jQuery('.button-primary', this).attr('disabled', 'disabled');
				var id = jQuery(this).attr('href');
				jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					cache: false,
					data: jQuery(this).serialize({
						checkboxesAsBools: true
					}),
					context: this,
					dataType: 'json',
					success: function (data) {
						if (data['status']) {
							jQuery('.wrap').empty().html(data['html']);
						}
					}
				});

				return false;
			});
		},
		deleteFlag: function () {
			jQuery('[data-action="ap-delete-flag"]').click(function (e) {
				e.preventDefault();
				jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: jQuery(this).attr('href'),
					context: this,
					success: function (data) {
						jQuery(this).closest('.flag-item').remove();
					}
				});
			});
		},
		ajaxBtn: function () {
			$('.ap-ajax-btn').on('click', function (e) {
				e.preventDefault();
				var q = $(this).apAjaxQueryString();
				console.dir( q );
				$.ajax({
					url: ajaxurl,
					data: q,
					context: this,
					type: 'POST',
					success: function (data) {
						if (typeof $(this).data('cb') !== 'undefined') {
							var cb = $(this).data("cb");
							if (typeof APjs.admin[cb] === 'function') {
								APjs.admin[cb](data, this);
							}
						}
					}
				});

			});
		},
		replaceText: function (data, elm) {
			$(elm).closest('li').find('strong').text(data);
		},
		statistic: function() {
			// top : sticky_header_bar_offset
			// init sticky header
			// position: 'absolute',
    	// 	scrollContainer: function($table){
			// 		var the = $table.closest('.wrapper');
			// 		console.dir( the );
			// 		return the;
			// 	}
			var sticky_header_bar_offset = $('#wpadminbar')[0].clientHeight;
			$(".ap-list-table.terms").floatThead({
				position: 'absolute',
    		scrollContainer: function($table){
					return $table.closest('.wrapper');
				}
			});

			var btns = $('.yas-table-open-btn');
			if ( btns.length > 0 ) {
				btns.on('click', function(e){
					var self = $(this);
					var query = JSON.parse(self.attr('apquery'));
					
					var loading = new Loading({
						discription:	query.term_name,
						defaultApply: true,
					});

					function scrollFromLeft( position ) {
						$('#wpbody').animate({
							scrollLeft : position
						}, 1000);
					}

					$.ajax({
						type: "POST",
						url: ajaxurl,
						data: query,
						success: function (response) {

							// remove existing yas table first
							var group_table_container = $('.statistic-group-table-container');
							var table_container = group_table_container.children( '.statistic-table-container' );
							if ( table_container.length == 2 ) {
								$(table_container[1]).remove();
							}

							// append yas table to right
							group_table_container.addClass('width-double');
							group_table_container.append( response );

							var yas_form = $('.list-table-form.yas');

							// ready for navigation
							var btn_go_to_yas = $('.go-to-yas');
							btn_go_to_yas.css( 'display', 'block' );
							btn_go_to_yas.on( 'click', function(){
								scrollFromLeft(yas_form.offset().left);
							} );

							var btn_back_to_terms = $('.back-to-terms');
							btn_back_to_terms.css( 'display', 'block' );
							btn_back_to_terms.on( 'click', function(){
								scrollFromLeft(0);
							} );

							// scroll to right automatically
							setTimeout( function(){
								loading.out();
								scrollFromLeft(yas_form.offset().left);
							}, 300 );

							$(".ap-list-table.yas").floatThead({
								position: 'absolute',
								scrollContainer: function($table){
									return $table.closest('.wrapper');
								}
							});
							
						}
					});
				});
			}
		}
	}

	$(document).ready(function() {
		$('#select-question-for-answer').on('keyup', function () {
			if (jQuery.trim(jQuery(this).val()) == '')
				return;
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'ap_ajax',
					ap_ajax_action: 'suggest_similar_questions',
					value: jQuery(this).val(),
					is_admin: true
				},
				success: function (data) {
					var textJSON = jQuery(data).filter('#ap-response').html();
					if (typeof textJSON !== 'undefined' && textJSON.length > 2) {
						data = JSON.parse(textJSON);
					}
					console.log(data);
					if (typeof data['html'] !== 'undefined')
						jQuery('#similar_suggestions').html(data['html']);
				},
				context: this,
			});
		});

		$('[data-action="ap_media_uplaod"]').click(function (e) {
			e.preventDefault();
			$btn = jQuery(this);
			var image = wp.media({
				title: jQuery(this).data('title'),
				// mutiple: true if you want to upload multiple files at once
				multiple: false
			}).open().on('select', function (e) {
				// This will return the selected image from the Media Uploader, the result is an object
				var uploaded_image = image.state().get('selection').first();
				// We convert uploaded_image to a JSON object to make accessing it easier
				// Output to the console uploaded_image
				var image_url = uploaded_image.toJSON().url;
				var image_id = uploaded_image.toJSON().id;

				// Let's assign the url value to the input field
				jQuery($btn.data('urlc')).val(image_url);
				jQuery($btn.data('idc')).val(image_id);

				if (!jQuery($btn.data('urlc')).prev().is('img'))
					jQuery($btn.data('urlc')).before('<img id="ap_category_media_preview" src="' + image_url + '" />');
				else
					jQuery($btn.data('urlc')).prev().attr('src', image_url);
			});
		});

		$('[data-action="ap_media_remove"]').click(function (e) {
			e.preventDefault();
			$('input[data-action="ap_media_value"]').val('');
			$('img[data-action="ap_media_value"]').remove();
		});

		$('.checkall').click(function () {
			var checkbox = $(this).closest('.ap-tools-ck').find('input[type="checkbox"]:not(.checkall)');
			checkbox.prop('checked', $(this).prop("checked"));
		})

		$('#' + $('#ap-tools-selectroles').val()).slideDown();

		$('#ap-tools-selectroles').change(function () {
			var id = '#' + $(this).val();
			$('.ap-tools-roleitem').hide();
			$(id).fadeIn(300);
		})

	});

})(jQuery);

// Question Category Disable
(function($){
	if ( typeof expert_categories !== 'undefined' ) {
		var checkboxes = $('#taxonomy-question_category li input[type=checkbox]');
		if ( checkboxes.length > 0 ) {
			checkboxes.attr('disabled', 'true');
			// expert_categories = Object.values( expert_categories );
			console.dir( expert_categories );
			alert( expert_categories );
			if ( expert_categories.length > 0 ) {
				expert_categories.forEach(function(id){
					checkboxes.each(function(){
						if ( this.id == ('in-question_category-' + id ) || this.id == ('in-popular-question_category-' + id ) ) {
							$(this).removeAttr('disabled');
						}
					});
				});
			}
		}
	}
})(jQuery);

function select_answer(checkbox) {
	var checkboxes = jQuery('.answer-actions .select_answer_checkbox');
	if ( checkbox.checked ) {
		jQuery.each( checkboxes, function(key, el){
			el.checked = false;
		} );
		checkbox.checked = true;
	}
}
