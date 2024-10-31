<?php 
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); } 
if(!current_user_can('edit_others_pages'))
{
	die('You are not allowed to work.');
}

$egmail_errors = array();
$egmail_success = '';
$egmail_error_found = FALSE;
$show_aweber_list = false;

// Delete Table
if ((isset($_GET['mode']) && sanitize_text_field($_GET['mode']) == 'del') && (isset($_GET['sid']) && intval($_GET['sid']) > 0))
{
	if ( !check_admin_referer( 'delete_table', 'rdt_admin_nonce' ) ) {
		// This nonce is not valid.
		die( 'Security check' ); 
	}
	$sid = intval($_GET['sid']);
	$egSqlTableColumn = "SELECT * FROM `".RDT_TABLE_COLUMN."` WHERE `table_id` = '".$sid."'";
	$recordTableColumn = $wpdb->get_results($egSqlTableColumn, ARRAY_A);
	if(sizeof($recordTableColumn) > 0)
	{
		for($i = 0; $i < sizeof($recordTableColumn); $i++)
		{
			$egDataSql = $wpdb->prepare("DELETE FROM `".RDT_TABLE_DATA."` WHERE `column_id` = %d", array($recordTableColumn[$i]['ID']));
			$wpdb->query($egDataSql);
		}
	}
	$egColumnSql = $wpdb->prepare("DELETE FROM `".RDT_TABLE_COLUMN."` WHERE `table_id` = %d", array($sid));
	$wpdb->query($egColumnSql);

	$egTableSql = $wpdb->prepare("DELETE FROM `".RDT_MASTER_TABLE."` WHERE `ID` = %d", array($sid));
	$wpdb->query($egTableSql);

	//	Success message
	$egmail_success_msg = TRUE;
	$egmail_success = "Table has been deleted successfully.";
}


// Form submitted and validation
if(isset($_POST['hdn_dynamic_table']) && sanitize_text_field($_POST['hdn_dynamic_table']) == 'Yes')
{
	global $wpdb;
	if ( !check_admin_referer( 'add_data_table', 'rdt_admin_nonce' ) ) {
		// This nonce is not valid.
		die( 'Security check' ); 
	}

	//--> Validation Start
	$form['table_name'] = isset($_POST['table_name']) ? stripslashes_deep($_POST['table_name']) : '';
	if ($form['table_name'] == '')
	{
		$egmail_errors[] = __('Please enter table name.', 'rdt-plugin');
		$egmail_error_found = TRUE;
	}

	$form['responsive_layout_col'] = isset($_POST['responsive_layout_col']) ? intval($_POST['responsive_layout_col']) : '';
	$form['responsive_display_full_table'] = isset($_POST['responsive_display_full_table']) ? intval($_POST['responsive_display_full_table']) : 0;
	if ($form['responsive_layout_col'] == '')
	{
		$form['responsive_display_full_table'] = 1;
	}

	//--> Table Display Settings
	$form['rdt_border_color'] = isset($_POST['rdt_border_color']) ? stripslashes_deep($_POST['rdt_border_color']) : '#666';
	$form['rdt_header_background_color'] = isset($_POST['rdt_header_background_color']) ? stripslashes_deep($_POST['rdt_header_background_color']) : '#000';
	$form['rdt_row_background_color'] = isset($_POST['rdt_row_background_color']) ? stripslashes_deep($_POST['rdt_row_background_color']) : '#999';
	$form['rdt_alternate_row_background_color'] = isset($_POST['rdt_alternate_row_background_color']) ? stripslashes_deep($_POST['rdt_alternate_row_background_color']) : '#888';
	$form['rdt_font_color'] = isset($_POST['rdt_font_color']) ? stripslashes_deep($_POST['rdt_font_color']) : '#000';
	$form['rdt_header_font_color'] = isset($_POST['rdt_header_font_color']) ? stripslashes_deep($_POST['rdt_header_font_color']) : '#000';

	$form['rdt_mobile_row_background_color'] = isset($_POST['rdt_mobile_row_background_color']) ? stripslashes_deep($_POST['rdt_mobile_row_background_color']) : '#ff00001f';
	$form['rdt_mobile_font_color'] = isset($_POST['rdt_mobile_font_color']) ? stripslashes_deep($_POST['rdt_mobile_font_color']) : '#000';
	$form['rdt_mobile_font_size'] = isset($_POST['rdt_mobile_font_size']) ? intval($_POST['rdt_mobile_font_size']) : '16';
	$rdt_total_existing_columns = isset($_POST['rdt_total_existing_columns']) ? stripslashes_deep($_POST['rdt_total_existing_columns']) : 0;


	$form['d_table_id'] = isset($_POST['d_table_id']) ? intval($_POST['d_table_id']) : 0;

	$prefix = "_";
	if($egmail_error_found == FALSE && $form['d_table_id'] <=0)
	{
		$egSql = $wpdb->prepare("INSERT INTO `".RDT_MASTER_TABLE."` (`table_name`,`responsive_width`, `border_color`, `header_background_color`, `header_font_color`, `row_bg_color`, `alternate_bg_color`, `font_color`, `mobile_row_bg_color`, `mobile_font_color`, `mobile_font_size`, `responsive_display_full_table`)  VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)", array($form['table_name'], $form['responsive_layout_col'], $form['rdt_border_color'], $form['rdt_header_background_color'], $form['rdt_header_font_color'], $form['rdt_row_background_color'], $form['rdt_alternate_row_background_color'], $form['rdt_font_color'], $form['rdt_mobile_row_background_color'], $form['rdt_mobile_font_color'], $form['rdt_mobile_font_size'], $form['responsive_display_full_table']));
		$wpdb->query($egSql);
		$table_id = $wpdb->insert_id;

		if(isset($_POST['rdt_columns']) && is_array($_POST['rdt_columns']))
		{
			$rdt_columns = array_map( 'sanitize_text_field', wp_unslash( $_POST['rdt_columns'] ) );
			for($i = 0; $i < sizeof($rdt_columns); $i++)
			{
				$has_child = intval($_POST['rdt_columns_subhead_count_'.$i]) == 0 ? 0 : 1;
				$egHeadingSql = $wpdb->prepare("INSERT INTO `".RDT_TABLE_COLUMN."` (`table_id`,`parent_id`, `has_child`, `column_title`, `column_status`) VALUES(%s, %d, %s, %s, %d)", array($table_id, 0, $has_child, stripslashes_deep(wp_filter_nohtml_kses($rdt_columns[$i])), 1));
				$wpdb->query($egHeadingSql);
				$column_id = $wpdb->insert_id;

				if($has_child == 1)
				{
					if(isset($_POST['rdt_sub_columns'][$i]) && is_array($_POST['rdt_sub_columns'][$i]))
					{
						for($j = 0; $j < sizeof($_POST['rdt_sub_columns'][$i]); $j++)
						{
							$egSubHeadingSql = $wpdb->prepare("INSERT INTO `".RDT_TABLE_COLUMN."` (`table_id`,`parent_id`, `has_child`, `column_title`, `column_status`) VALUES(%s, %d, %s, %s, %d)", array($table_id, $column_id, 0, stripslashes_deep(wp_filter_nohtml_kses($_POST['rdt_sub_columns'][$i][$j])), 1));
							$wpdb->query($egSubHeadingSql);
						}
					}
				}
			}
		}
		$egmail_success = "Table has been created successfully.";
	}
	else
	{
		//$print = print_r($_POST,true); die($print);
		$egSql = $wpdb->prepare("UPDATE `".RDT_MASTER_TABLE."` SET `table_name` = %s,`responsive_width` = %s, `border_color` = %s, `header_background_color` = %s, `header_font_color` = %s, `row_bg_color` = %s, `alternate_bg_color` = %s, `font_color` = %s, `mobile_row_bg_color` = %s, `mobile_font_color` = %s, `mobile_font_size` = %s, `responsive_display_full_table` = %s WHERE ID = %d", array($form['table_name'], $form['responsive_layout_col'], $form['rdt_border_color'], $form['rdt_header_background_color'], $form['rdt_header_font_color'], $form['rdt_row_background_color'], $form['rdt_alternate_row_background_color'], $form['rdt_font_color'], $form['rdt_mobile_row_background_color'], $form['rdt_mobile_font_color'], $form['rdt_mobile_font_size'], $form['responsive_display_full_table'], $form['d_table_id']));
		$wpdb->query($egSql);
		$table_id = $form['d_table_id'];

		if(isset($_POST['rdt_columns']) && is_array($_POST['rdt_columns']))
		{
			for($i = 0; $i < sizeof($_POST['rdt_columns']); $i++)
			{
				$has_child = intval($_POST['rdt_columns_subhead_count_'.$i]) == 0 ? 0 : 1;
				if(isset($_POST['rdt_columns_id'][$i]) && $_POST['rdt_columns_id'][$i] > 0)
				{
					$column_id = intval($_POST['rdt_columns_id'][$i]);
					$column_status = stripslashes_deep($_POST['rdt_columns_status'][$i]);
					$egHeadingSql = $wpdb->prepare("UPDATE `".RDT_TABLE_COLUMN."` SET `has_child` = %d, `column_title` = %s, `column_status` = %d WHERE `ID` = %d", array($has_child, stripslashes_deep(wp_filter_nohtml_kses($_POST['rdt_columns'][$i])), $column_status, $column_id));
					$wpdb->query($egHeadingSql);
				}
				else
				{
					$egHeadingSql = $wpdb->prepare("INSERT INTO `".RDT_TABLE_COLUMN."` (`table_id`,`parent_id`, `has_child`, `column_title`, `column_status`) VALUES(%s, %d, %s, %s, %d)", array($table_id, 0, $has_child, stripslashes_deep(wp_filter_nohtml_kses($_POST['rdt_columns'][$i])), 1));
					$wpdb->query($egHeadingSql);
					$column_id = $wpdb->insert_id;
				}

				$egHeadingSql = $wpdb->prepare("UPDATE `".RDT_TABLE_COLUMN."` SET `column_status` = 0 WHERE `parent_id` = %d", array($column_id));
				$wpdb->query($egHeadingSql);

				if($has_child == 1)
				{
					if(isset($_POST['rdt_sub_columns'][$i]) && is_array($_POST['rdt_sub_columns'][$i]))
					{
						for($j = 0; $j < sizeof($_POST['rdt_sub_columns'][$i]); $j++)
						{
							if(isset($_POST['rdt_sub_columns_id'][$i][$j]) && intval($_POST['rdt_sub_columns_id'][$i][$j]) > 0)
							{
								$egSubHeadingSql = $wpdb->prepare("UPDATE `".RDT_TABLE_COLUMN."` SET `column_title` = %s, `column_status` = '1' WHERE `ID` = %d", array(stripslashes_deep(wp_filter_nohtml_kses($_POST['rdt_sub_columns'][$i][$j])), intval($_POST['rdt_sub_columns_id'][$i][$j])));
								$wpdb->query($egSubHeadingSql);
							}
							else
							{
								$egSubHeadingSql = $wpdb->prepare("INSERT INTO `".RDT_TABLE_COLUMN."` (`table_id`,`parent_id`, `has_child`, `column_title`, `column_status`) VALUES(%s, %d, %s, %s, %d)", array($table_id, $column_id, 0, stripslashes_deep(wp_filter_nohtml_kses($_POST['rdt_sub_columns'][$i][$j])), 1));
								$wpdb->query($egSubHeadingSql);
							}
						}
					}
				}
			}

			//Rearrange data while column settings changed
			$sqlCol = "SELECT * FROM `".RDT_TABLE_COLUMN."` WHERE `table_id` = '".intval($form['d_table_id'])."' AND parent_id = 0";
			$queryCol = $wpdb->get_results($sqlCol, ARRAY_A);
			for($i = 0; $i < sizeof($queryCol); $i++)
			{
				if($queryCol[$i]['has_child'] == 0)
				{
					$sqlSubColumn = "SELECT * FROM `".RDT_TABLE_COLUMN."` WHERE `parent_id` = '".$queryCol[$i]['ID']."'";
					$rowSubColumn = $wpdb->get_results($sqlSubColumn, ARRAY_A);
					if(sizeof($rowSubColumn) > 0)
					{
						$sqlChkData = "SELECT COUNT(*) AS counter FROM `".RDT_TABLE_DATA."` WHERE `column_id` = '".$rowSubColumn[0]['ID']."'";
						$queryChkData = $wpdb->get_row($sqlChkData, ARRAY_A);
						if($queryChkData['counter'] > 0)
						{
							//assign first column data to parent data
							$egHeadingSql = $wpdb->prepare("UPDATE `".RDT_TABLE_DATA."` SET `column_id` = '".$queryCol[$i]['ID']."' WHERE `column_id` = '".$rowSubColumn[0]['ID']."'");
							$wpdb->query($egHeadingSql);

							for($j = 0; $j < sizeof($rowSubColumn); $j++)
							{
								$egHeadingSql = $wpdb->prepare("DELETE FROM `".RDT_TABLE_DATA."` WHERE `column_id` = '".$rowSubColumn[$j]['ID']."'");
								$wpdb->query($egHeadingSql);
							}

							$egHeadingSql = $wpdb->prepare("DELETE FROM `".RDT_TABLE_COLUMN."` WHERE `parent_id` = '".$queryCol[$i]['ID']."'");
							$wpdb->query($egHeadingSql);
						}
					}
				}
				else
				{
					$sqlParentData = "SELECT COUNT(*) AS counter FROM `".RDT_TABLE_DATA."` WHERE `column_id` = '".$queryCol[$i]['ID']."'";
					$rowParentData = $wpdb->get_row($sqlParentData, ARRAY_A);
					if($rowParentData['counter'] > 0)
					{
						$sqlSubColumn = "SELECT * FROM `".RDT_TABLE_COLUMN."` WHERE `parent_id` = '".$queryCol[$i]['ID']."'";
						$rowSubColumn = $wpdb->get_results($sqlSubColumn, ARRAY_A);
						$wpdb->query("UPDATE `".RDT_TABLE_DATA."` SET `column_id` = '".$rowSubColumn[0]['ID']."' WHERE `column_id` = '".$queryCol[$i]['ID']."'");
					}

					$sqlSubColumn = "SELECT * FROM `".RDT_TABLE_COLUMN."` WHERE `parent_id` = '".$queryCol[$i]['ID']."'";
					$rowSubColumn = $wpdb->get_results($sqlSubColumn, ARRAY_A);
					for($c = 0; $c < sizeof($rowSubColumn); $c++)
					{
						if($c == 0)
						{
							$sqlChkData = "SELECT COUNT(*) AS counter FROM `".RDT_TABLE_DATA."` WHERE `column_id` = '".$rowSubColumn[$c]['ID']."'";
							$queryChkData = $wpdb->get_row($sqlChkData, ARRAY_A);
							$total_data = $queryChkData['counter'];
							if($total_data == 0) break;
						}
						else
						{
							$sqlChkData = "SELECT COUNT(*) AS counter FROM `".RDT_TABLE_DATA."` WHERE `column_id` = '".$rowSubColumn[$c]['ID']."'";
							$queryChkData = $wpdb->get_row($sqlChkData, ARRAY_A);
							if($queryChkData['counter'] == 0)
							{
								for($d = 0; $d < sizeof($total_data); $d++)
								{
									$egDataSql = $wpdb->prepare("INSERT INTO `".RDT_TABLE_DATA."` (`column_id`,`column_data`) VALUES(%d, '')", array(stripslashes_deep($rowSubColumn[$c]['ID'])));
									$wpdb->query($egDataSql);
								}
							}
						}
					}
				}
			}
		}
		$egmail_success = "Table has been updated successfully.";
	}
}
?>
<div class="wrap">
<div id="icon-plugins" class="icon32"></div>
<h2><?php _e(RDT_PLUGIN_TITLE, 'rdt-plugin');?> <a class="add-new-h2" href="<?php echo get_option('siteurl'); ?>/wp-admin/admin.php?page=rdt&ac=add_table"><?php _e('Add New Table', 'mi-subscription-list'); ?></a></h2>

<?php
if ($egmail_error_found == FALSE && isset($egmail_success) != ''){
?>
	  <div style="width:100%; float:left; margin-left:0; box-sizing:border-box;" class="updated fade"><p><strong><?php echo $egmail_success; ?> </strong></p></div>
<?php
}
?>

<form name="frm_leads_list" method="post">
<table width="100%" class="widefat" id="straymanage">
<thead>
  <tr>
    <th class="check-column" scope="col" style="padding:0; vertical-align:middle"></th>
    <th scope="col">#</th>
    <th scope="col">Table</th>
    <th scope="col">Shortcode</th>
    <th scope="col">Action</th>
  </tr>
</thead>
<tfoot>
  <tr>
    <th class="check-column" scope="col"></th>
    <th scope="col">#</th>
    <th scope="col">Table</th>
    <th scope="col">Shortcode</th>
    <th scope="col">Action</th>
  </tr>
</tfoot>
<tbody>
  <?php
	$egSql = "SELECT * FROM ".RDT_MASTER_TABLE." WHERE 1";
	
	$egSql = $egSql . " ORDER BY ID DESC";
	$egRecord = array();
	$total_record = count($wpdb->get_results($egSql, ARRAY_A));
	
	//--> Pagination Section
	$items_per_page = 20;
	$page = isset( $_GET['cpage'] ) ? abs( (int) stripslashes_deep($_GET['cpage']) ) : 1;
	$offset = ( $page * $items_per_page ) - $items_per_page;
	$limit_query    =   " LIMIT ".$items_per_page." OFFSET ".$offset;   
	$egRecord =   $wpdb->get_results($egSql.$limit_query,ARRAY_A); // return OBJECT
	if(count($egRecord) > 0)
	{
		$i = 0;
		foreach ($egRecord as $record)
		{
    ?>
          <tr class="<?php if ($i&1) { echo'alternate'; } else { echo ''; }?>">
                <td align="left"></td>
                <td><?php echo  ++$i;?></td>
                <td><?php echo $record['table_name']; ?></td>
                <td><input style="width:90%" onfocus="this.select();" type="text" value='[rdt id="<?php echo $record['ID'];?>" title="<?php echo esc_attr($record['table_name']);?>"]' readonly="readonly" /></td>
                <td width="220"><a href="<?php echo get_option('siteurl'); ?>/wp-admin/admin.php?page=rdt&ac=add_table&ID=<?php echo $record['ID']; ?>"><?php _e('Settings', 'rdt-plugin'); ?></a> | <a href="<?php echo get_option('siteurl'); ?>/wp-admin/admin.php?page=rdt&ac=manage_table_data&ID=<?php echo $record['ID']; ?>"><?php _e('Manage Table Data', 'rdt-plugin'); ?></a> | <a onClick="javascript:rdt_delete_table('<?php echo $record['ID']; ?>')" href="javascript:void(0);"><?php _e('Delete', 'rdt-plugin'); ?></a> </td>        
          </tr>
<?php
		}
	}
	else
	{
	?>
    <tr>
        <th colspan="5" style="text-align:center;">No record found.</th>
      </tr>
    <?php
	}
?>
</tbody>
</table>
      
    <?php wp_nonce_field('delete_table','rdt_admin_nonce'); ?>
    <div class="tablenav">
		<div class="alignleft">
			
		</div>
        <div class="alignright">
        	<?php
				$big = 999999999; // need an unlikely integer
				echo $pagelink = paginate_links( array(
					'base' => add_query_arg( 'cpage', '%#%' ),
					'format' => '',
					'prev_text' => __('&laquo;'),
					'next_text' => __('&raquo;'),
					'current' => $page,
					'before_page_number'		 => '<span class="pipe-sep">|</span>',
					'total' => ceil($total_record / $items_per_page)
				) );
				?>
        </div>
    </div>
	</form>
</div>
