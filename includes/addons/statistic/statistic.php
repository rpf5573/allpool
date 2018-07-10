<?php

require_once 'class-term-statistic-list-table.php';

class AP_Statistic {

  public static function add_statistic_submenu() {
		//add_submenu_page( 'anspress', __( 'Questions Category', 'anspress-question-answer' ), __( 'Category', 'anspress-question-answer' ), 'manage_options', 'edit-tags.php?taxonomy=question_category' );
    add_submenu_page( 'anspress', __( 'Statistic', 'anspress-question-answer' ), __( 'Statistic', 'anspress-question-answer' ), 'delete_pages', 'ap_statistic', array( __CLASS__, 'display_statistic_page' ) );
	}
	
  /**
	 * Control the output of question selection.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public static function display_statistic_page() {
		//Create an instance of our package class...
    $statistic_list_table = new AP_Term_Statistic_List_Table();
    //Fetch, prepare, sort, and filter our data...
		$statistic_list_table->prepare_items();
		
		
    ?>
    <div>
			<h2>List Table Test</h2>
			<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
			<form id="statistic-list-table-form" method="get">
					<!-- For plugins, we also need to ensure that the form posts back to our current page -->
					<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
					<!-- Now we can render the completed list table -->
					<?php $statistic_list_table->display() ?>
			</form>
    </div>
    <?php
	}
}