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
      onAdd: function(value, text, $selected) {
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
    *  Questions page
  /* ------------------------------------------------------------------------- */ (function($){
  
  /*  Search filter
  /* --------------------------------------------------- */
  var main_filter = {
    category : $('.question-filter__category select'),
    year : $('.question-filter__year select'),
    session : $('.question-filter__session select'),
    selected : $('.question-filter__did_select'),
    answered : $('.question-filter__has_answer')
  };
  
  if ( main_filter.category.length > 0 ) {
    main_filter.category.SumoSelect({ 
      placeholder : '카테고리 리스트',
      search: true,
      selectAll : true,
      searchText: '여기에 입력하세요'
    });
  }
  if ( main_filter.year.length > 0 ) {
    main_filter.year.SumoSelect({
      placeholder : '년도',
      search : false,
      selectAll : true,
      searchText : '검색'
    });
  }
  if ( main_filter.session.length > 0 ) {
    main_filter.session.SumoSelect({
      placeholder : '회차',
      selectAll : true,
      search : false
    });
  }
  if ( main_filter.selected.length > 0 ) {
    main_filter.selected.checkbox();
  }
  if ( main_filter.answered.length > 0 ) {
    main_filter.answered.checkbox();
  } })(jQuery);
  
  
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
  
});