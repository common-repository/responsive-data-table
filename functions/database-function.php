<?php
/*
Copyright 2019  Responsive Data Table Plugin.® (email : minervainfotech@gmail.com)
This file is part of Responsive Data Table.
*/

global $wpdb;

define ('RDT_MASTER_TABLE', $wpdb->prefix.'rdt_table');
define ('RDT_TABLE_COLUMN', $wpdb->prefix.'rdt_table_column');
define ('RDT_TABLE_DATA', $wpdb->prefix.'rdt_table_data');

/* -------Create plugin tables */
function rdt_db_init()
{
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	
	/* ------------------Option table */
	$rdt_table = RDT_MASTER_TABLE;
	$sql = "CREATE TABLE $rdt_table 
	(
		ID int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY  (ID),
		table_name varchar(255),
		responsive_width varchar(255),
		border_color varchar(255),
		header_background_color  varchar(255),
		header_font_color varchar(255),
		row_bg_color varchar(255),
		alternate_bg_color varchar(255),
		font_color varchar(255),
		mobile_row_bg_color varchar(255),
		mobile_font_color varchar(255),
		mobile_font_size varchar(255),
		responsive_display_full_table int(11)
	)$charset_collate;";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	/* --------------My Income Table */
	$rdt_table_column = RDT_TABLE_COLUMN;

	$sql = "CREATE TABLE $rdt_table_column 
	(
		ID int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY  (ID),
		table_id int(11),
		parent_id int(11),
		has_child int(11),
		column_title  varchar(255),
		column_status int(11)
	)$charset_collate;";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	/* --------------Post Snippet Table */
	
	$rdt_table_data = RDT_TABLE_DATA;
	
	$sql = "CREATE TABLE $rdt_table_data 
	(
		ID int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY  (ID),
		column_id int(11),
		column_data TEXT
	)$charset_collate;";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}
?>