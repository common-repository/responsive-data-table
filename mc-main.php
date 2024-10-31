<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Responsive Data Table
 * Description:       By using this plugin content writer can create table structure for desktop and mobile display.
 * Version:           1.3
 * Author:            Minerva Infotech
 * Author URI:        http://minervainfotech.com
 * License:           GPLv2
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       Minerva
*/

global $RDT_VER;
$RDT_VER = "1.3";

define('RDT_PATH', plugins_url().'/'. basename(dirname(__FILE__)).'/');
define('RDT_PHYSICAL_PATH', plugin_dir_path(__FILE__ ));
define('RDT_PLUGIN_TITLE', "Responsive Data Table");

include_once ('functions/core-function.php');
include_once ('functions/database-function.php');
include_once ('display-table.php');

global $post;

register_activation_hook( __FILE__, 'rdt_init' );

function rdt_options() 
{ 
    global $wpdb;

    $current_page = isset($_GET['ac']) ? $_GET['ac'] : '';

    switch($current_page)
    {
        case 'add_table':
            include('admin/add_table.php');
            break;
		case 'manage_tables':
            include('admin/manage_tables.php');
            break;
		case 'manage_table_data':
            include('admin/manage_table_data.php');
            break;
        default:
            include('admin/manage_tables.php');
            break;
    }
}

add_action("init", "rdt_update_plugin", 2);
add_action('admin_menu', 'rdt_admin_menu');

global $wp;

function rdt_admin_styles() 
{
	wp_register_style('rdt_stylesheet', plugins_url('css/rdt-style.css', __FILE__));
	wp_enqueue_style('rdt_stylesheet');

	wp_register_script('custom-script', plugins_url('js/rdt-javascript.js', __FILE__) );
	wp_enqueue_script('custom-script');
}
add_action( 'admin_enqueue_scripts', 'rdt_admin_styles' );


function rdt_front_style() 
{
	wp_register_style('rdt_stylesheet', plugins_url('css/rdt-front-style.css', __FILE__));
	wp_enqueue_style('rdt_stylesheet');
}

add_action('wp_enqueue_scripts', 'rdt_front_style');


add_action( 'admin_enqueue_scripts', 'rdt_enqueue_color_picker' );
function rdt_enqueue_color_picker() 
{
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'my-script-handle', plugins_url('my-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
}
?>