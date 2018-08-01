<?php

class AP_Admin_Custom_Settings {
  public static function giveup_copyright() {
    add_settings_section(  
      'giveup_copyright_section', // Section ID 
      '저작권 양도 관련 내용', // Section Title
      array(__CLASS__, 'giveup_copyright_section'), // Callback
      'general' // What Page?  This makes the section show up on the General Settings Page
    );

    add_settings_field( // Option 1
      'giveup_copyright', // Option ID
      '저작권 양도', // Label
      array(__CLASS__, 'giveup_copyright_field'), // !important - This is where the args go!
      'general', // Page it will be displayed (General Settings)
      'giveup_copyright_section', // Name of our section
      array( // The $args
        'giveup_copyright' // Should match Option ID
      )
    ); 

    // 이렇게 해야만, 서버에 저장시켜주나봐...
    register_setting('general','giveup_copyright', 'esc_attr');
	}
	
  public static function giveup_copyright_section() { // Section Callback
    echo '<p>회원가입시에 보여집니다</p>';
  }

  public static function giveup_copyright_field($args) {  // Textbox Callback
    $option = get_option($args[0]);
    echo '<textarea type="text" rows="10" cols="50" id="'. $args[0] .'" name="'. $args[0] .'" />' . $option . '</textarea>';
  }
}