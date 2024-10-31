<?php
/*
Copyright 2019  Responsive Data Table Plugin.® (email : minervainfotech@gmail.com)
This file is part of Responsive Data Table.
*/

function rdt_init()
{
	global $RDT_VER;
	if( $RDT_VER != get_option('rdt_curr_ver') )
	{
		rdt_db_init();
		$ver = get_option('rdt_curr_ver');
		update_option('rdt_prev_ver',$ver);
		update_option('rdt_curr_ver', sanitize_text_field($RDT_VER));
	}
	
}

function rdt_update_plugin()
{
	global $RDT_VER;
	if($RDT_VER > get_option('rdt_curr_ver'))
	{
		rdt_db_init();
		$ver = get_option('rdt_curr_ver');
		update_option('rdt_prev_ver',$ver);
		update_option('rdt_curr_ver', sanitize_text_field($RDT_VER));
	}
}

/**** Plugin Menu *****/

function rdt_admin_menu() 
{
	if ( current_user_can( 'rdt_table_view' ) ) 
	{
		$role = "rdt_table_view";
	} 
	else 
	{
		$role = "manage_options";
	}
	
	$queue = '';
	
	$plugin_name = __('Responsive Data Table', 'responsive_data_table');
	add_menu_page( $plugin_name, $plugin_name, 'administrator', 'rdt', 'rdt_options', 'dashicons-media-spreadsheet' );
	add_submenu_page('rdt', __( 'Add Table', 'rdt&ac=add_table' ), __('Add Table', 'rdt&ac=add_table'), $role, 'rdt&ac=add_table', 'rdt_options');
	
	

}

?>