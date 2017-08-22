<?php
function afl_refered_members () {
	echo afl_eps_page_header();

	afl_content_wrapper_begin();
		afl_refered_members_callback();
	afl_content_wrapper_end();
}

function afl_refered_members_callback () {
	$uid = get_current_user_id();

	if (isset($_GET['uid'])) {
		$uid = $_GET['uid'];
	}

	//get user downlines details based on the uid
	$data = afl_get_user_downlines($uid);

	wp_register_script( 'jquery-data-table',  EPSAFFILIATE_PLUGIN_ASSETS.'plugins/dataTables/js/jquery.dataTables.min.js');
	wp_enqueue_script( 'jquery-data-table' );

	wp_register_script( 'jquery-data-bootstrap-table',  EPSAFFILIATE_PLUGIN_ASSETS.'plugins/dataTables/js/dataTables.bootstrap.min.js');
	wp_enqueue_script( 'jquery-data-bootstrap-table' );

	wp_enqueue_style( 'plan-develoepr', EPSAFFILIATE_PLUGIN_ASSETS.'plugins/dataTables/css/dataTables.bootstrap.min.css');

	
	// wp_enqueue_scripts( 'jquery-data-table', EPSAFFILIATE_PLUGIN_ASSETS.'js/dataTables.bootstrap.min.js');
	// wp_enqueue_scripts( 'jquery-data-table', EPSAFFILIATE_PLUGIN_ASSETS.'js/jquery.dataTables.min.js');

	?>
	<div class="data-filters"></div>

	<table id="refered-members" class="table table-striped table-bordered dt-responsive nowrap refered-members" cellspacing="0" width="100%">
	        <thead>
	            <tr>
	                <th>Userid</th>
	                <th>User name</th>
	                <th>Level</th>
	                <th>Relative Position</th>
	                <th>Rank</th>
	                <th>Created on</th>
	            </tr>
	        </thead>
	    </table>
	<?php 
}