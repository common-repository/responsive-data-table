<?php
//uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {exit ();}
else
{
	global $wpdb;
	$wpdb->prefix;
	
	include_once ('functions/database-function.php');
	
	delete_option('rdt_prev_ver');
	delete_option('rdt_curr_ver');
	
	$wpdb->query("DROP TABLE `".RDT_MASTER_TABLE."`");
	$wpdb->query("DROP TABLE `".RDT_TABLE_COLUMN."`");
	$wpdb->query("DROP TABLE `".RDT_TABLE_DATA."`");
}
?>