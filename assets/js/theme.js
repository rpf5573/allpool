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
        extensions: [
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

  var go_to_bottom_btn = $('.go-to-bottom-btn');
  if ( go_to_bottom_btn.length > 0 ) {
    go_to_bottom_btn.on( 'click', function(){
      $('html, body').animate({
        scrollTop: $('.alpool-guide').offset().top
      }, 1500);
    } );
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

  var question_filter = $('.question-filter');
  if ( question_filter.length > 0 ) {
    question_filter.animate({
      opacity : 1
    }, 2000, function(){
      
    });
  }
  
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

  /* ------------------------------------------------------------------------- *
   *  Mypage
  /* ------------------------------------------------------------------------- */(function($){
    var nickname = {
      edit_btn : $('.ap-user-info-edit-btn.--nickname'),
      modal : $('.ap-user-info-edit-modal.--nickname'),
      input : $('.ap-user-info-edit-modal.--nickname input'),
    }
    var password = {
      edit_btn : $('.ap-user-info-edit-btn.--password'),
      modal : $('.ap-user-info-edit-modal.--password'),
    }

    var email = {
      edit_btn : $('.ap-user-info-edit-btn.--email'),
      modal : $('.ap-user-info-edit-modal.--email'),
      input : $('.ap-user-info-edit-modal.--email input'),
    }

    function nickname_check( str ) {
      if(str.length < 2 || str.length > 10) {
        alert("2~10자의 한글, 영문, 숫자만 사용할 수 있습니다.");
        return false;
      }
      var chk = /[0-9]|[a-z]|[A-Z]|[가-힣]/;
      for( var i = 0; i <= str.length -1 ; i++ )
      {
        if(chk.test(str.charAt(i))) {
        }
        else {
          alert("2~10자의 한글, 영문, 숫자만 사용할 수 있습니다.");
          return false;
        }
      }
     
      return true;
    }

    function email_check( email ) {
      var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
      var result = re.test(email);
      if ( ! result ) {
        alert( "올바른 형식의 이메일을 입력해 주세요" );
        return false;
      }
      
      return true;
    }

    if ( nickname.edit_btn.length > 0 && password.edit_btn.length > 0 && email.edit_btn.length > 0 ) {
      nickname.edit_btn.on( 'click', function(){
        var label = nickname.edit_btn.parent(".ap-user-nickname").find( 'span[itemprop="name"]' );
        // remove white spaces
        var value = label.text().replace(/\s+/g, '');
        nickname.input.val( value );
        nickname.modal.modal({
          onApprove : function(btn){
            var new_nickname = nickname.input.val();
            if ( ! nickname_check( new_nickname ) ) {
              return false;
            }
            AnsPress.showLoading( btn );
            var query = JSON.parse(btn.attr('apquery'));
            query.nickname = new_nickname;
            console.dir( query );
            AnsPress.ajax({
              url: ajaxurl,
              data: query,
              context: this,
              type: 'POST',
              success: function (data) {
                AnsPress.hideLoading( btn );
                if ( data.success ) {
                  window.location.href = data.redirect;
                }
              }
            });
            return false;
          }
        }).modal( 'show' );
      } );
      password.edit_btn.on( 'click', function(){
        password.modal.modal({
          onApprove : function(){
            return false;
          }
        }).modal( 'show' );
      });
      email.edit_btn.on( 'click', function(){
        var label = email.edit_btn.parent(".ap-user-email").find( 'span[itemprop="email"]' );
        // remove white spaces
        var value = label.text().replace(/\s+/g, '');
        email.input.val( value );
        email.modal.modal({
          onApprove : function(btn){
            var new_email = email.input.val();
            if ( ! email_check( new_email ) ) {
              return false;
            }
            AnsPress.showLoading( btn );
            var query = JSON.parse(btn.attr('apquery'));
            query.email = new_email;
            console.dir( query );
            AnsPress.ajax({
              url: ajaxurl,
              data: query,
              context: this,
              type: 'POST',
              success: function (data) {
                AnsPress.hideLoading( btn );
                if ( data.success ) {
                  window.location.href = data.redirect;
                }
              }
            });
            return false;
          }
        }).modal( 'show' );
      } );
    }

    // lazy show
    var user_desktop_buttons = $('.ap-user-desktop-buttons');
    if ( user_desktop_buttons.length > 0 ) {
      setTimeout( function(){
        user_desktop_buttons.css( 'display', 'flex' );
      }, 100 );
    }

  })(jQuery);

});