<?php 
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); } 
if(!current_user_can('edit_others_pages'))
{
	die('You are not allowed to work.');
}

if(isset($_GET['ID']) && intval($_GET['ID']) <= 0)
{
	die('You are not allowed to work.');
}

?>
<?php
$egmail_errors = array();
$egmail_success = '';
$egmail_error_found = FALSE;
$show_aweber_list = false;

// Form submitted and validation
if(isset($_POST['hdn_page_action']) && sanitize_text_field($_POST['hdn_page_action']) == 'Save')
{
	if ( !check_admin_referer( 'manage_data', 'rdt_admin_nonce' ) ) {
		// This nonce is not valid.
		die( 'Security check' ); 
	}
	$id = intval($_POST['ID']);
	$egSql = "SELECT * FROM ".RDT_MASTER_TABLE." WHERE `ID` = '".$id."'";
	$table_data = $wpdb->get_row($egSql, ARRAY_A);
	if(!is_array($table_data))
	{
		die('<h3>Table doesn\'t exists..</h3>');
	}
	if(isset($_POST['RemoveTableData']))
	{
		if(isset($_POST['delete_rows']))
		{
			$delete_rows = array_map( 'sanitize_text_field', wp_unslash( $_POST['delete_rows'] ) );
			foreach($delete_rows as $val)
			{
				$ids = explode(",", $val);
				foreach($ids as $dataid)
				{
					$egDataSql = $wpdb->prepare("DELETE FROM `".RDT_TABLE_DATA."` WHERE `ID` = %d", array(stripslashes_deep($dataid)));
					$wpdb->query($egDataSql);
				}
			}
		}
		//wp_redirect('admin.php?page=rdt&ac=manage_table_data&dm=s&ID='.$_GET['ID']);
		$_GET['dm'] = "s";
	}
	else
	{
		if(is_array($_POST['column_data']))
		{
			foreach($_POST['column_data'] as $key => $val)
			{
				$egDataDelSql = $wpdb->prepare("DELETE FROM `".RDT_TABLE_DATA."` WHERE `column_id` = %d", array(stripslashes_deep($key)));
				$wpdb->query($egDataDelSql);
				for($i = 1; $i < sizeof($val); $i++)
				{
					$egDataSql = $wpdb->prepare("INSERT INTO `".RDT_TABLE_DATA."` (`column_id`,`column_data`) VALUES(%d, %s)", array(stripslashes_deep($key), stripslashes_deep($val[$i])));
					$wpdb->query($egDataSql);
				}
			}
		}
		//wp_redirect('admin.php?page=rdt&ac=manage_table_data&dm=sd&ID='.$_GET['ID']);
		$_GET['dm'] = "sd";
	}
}

$total_records = 0;
if(isset($_GET['ID']) && intval($_GET['ID']) > 0)
{
	$id = intval($_GET['ID']);
	$child_available = 0;
	$error_message = '';
	$egSql = "SELECT * FROM ".RDT_MASTER_TABLE." WHERE `ID` = '".$id."'";
	$table_data = $wpdb->get_row($egSql, ARRAY_A);
	if(!is_array($table_data))
	{
		$error_message = '<h3>Please create table and define table columns to provide data.</h3>';
	}
	else
	{
		$egSqlTableColumn = "SELECT * FROM `".RDT_TABLE_COLUMN."` WHERE `table_id` = '".$id."' AND `parent_id` = 0 AND column_status = 1 ORDER BY `ID`";
		$recordTableColumn = $wpdb->get_results($egSqlTableColumn, ARRAY_A);
		$rdt_columns = array();
		$column_db_data = array();
		if(sizeof($recordTableColumn) > 0)
		{
			for($i = 0; $i < sizeof($recordTableColumn); $i++)
			{
				$rdt_columns[$recordTableColumn[$i]['ID']]['title'] = $recordTableColumn[$i]['column_title'];
				$rdt_columns[$recordTableColumn[$i]['ID']]['column_status'] = $recordTableColumn[$i]['column_status'];
				$rdt_columns[$recordTableColumn[$i]['ID']]['has_child'] = $recordTableColumn[$i]['has_child'];
				if($recordTableColumn[$i]['has_child'] == 1)
				{
					$child_available = 1;
					$egSqlTableColumnChild = "SELECT * FROM `".RDT_TABLE_COLUMN."` WHERE `table_id` = '".$id."' AND `parent_id` = '".absint($recordTableColumn[$i]['ID'])."' AND column_status = 1 ORDER BY `ID`";
					$recordTableColumnChild = $wpdb->get_results($egSqlTableColumnChild, ARRAY_A);
					if(sizeof($recordTableColumnChild) > 0)
					{
						for($c = 0; $c < sizeof($recordTableColumnChild); $c++)
						{
							$rdt_columns[$recordTableColumn[$i]['ID']]['child'][$recordTableColumnChild[$c]['ID']]['title'] = $recordTableColumnChild[$c]['column_title'];
							
							$egSqlChildColumnData = "SELECT `column_data`, `ID` FROM `".RDT_TABLE_DATA."` WHERE `column_id` = '".absint($recordTableColumnChild[$c]['ID'])."' ORDER BY `ID`";
							$egRecordsChildColumnData = $wpdb->get_results($egSqlChildColumnData, ARRAY_A);
							if(is_array($egRecordsChildColumnData) && sizeof($egRecordsChildColumnData) > 0)
							{
								$d = 0;
								for($n = 0; $n < sizeof($egRecordsChildColumnData); $n++)
								{
									$column_db_data[$recordTableColumnChild[$c]['ID']][$d]['data'] = $egRecordsChildColumnData[$n]['column_data'];
									$column_db_data[$recordTableColumnChild[$c]['ID']][$d]['ID'] = $egRecordsChildColumnData[$n]['ID'];
									$d++;
								}
								$total_records = sizeof($egRecordsChildColumnData);
							}
						}
					}
				}
				else
				{
					$egSqlColumnData = "SELECT `column_data`, `ID` FROM `".RDT_TABLE_DATA."` WHERE `column_id` = '".absint($recordTableColumn[$i]['ID'])."' ORDER BY `ID`";
					$egRecordsColumnData = $wpdb->get_results($egSqlColumnData, ARRAY_A);
					if(is_array($egRecordsColumnData) && sizeof($egRecordsColumnData) > 0)
					{
						$d = 0;
						for($n = 0; $n < sizeof($egRecordsColumnData); $n++)
						{
							$column_db_data[$recordTableColumn[$i]['ID']][$d]['data'] = $egRecordsColumnData[$n]['column_data'];
							$column_db_data[$recordTableColumn[$i]['ID']][$d]['ID'] = $egRecordsColumnData[$n]['ID'];
							$d++;
						}
						$total_records = sizeof($egRecordsColumnData);
					}
				}
			}
		}
		else
		{
			$error_message = '<h3>Please create table columns.</h3>';
		}
	}
}
//print_r($column_db_data); die;
if(isset($_GET['dm']))
{
	$dm = sanitize_text_field($_GET['dm']);
	if($dm == "s")
	{
		$egmail_success = "Selected row(s) deleted successfully.";
	}
	if($dm == "sd")
	{
		$egmail_success = "Data saved successfully.";
	}
}
?>
<div class="wrap">
<div id="icon-plugins" class="icon32"></div>
<h2><?php _e(RDT_PLUGIN_TITLE, 'rdt-plugin');?></h2>
<h3>Manage data for "<?php echo $table_data['table_name'];?>"</h3>
<?php

if (isset($egmail_success) && $egmail_success != ''){
?>
	  <div style="width:100%; float:left; margin-left:0; box-sizing:border-box;" class="updated fade"><p><strong><?php echo $egmail_success; ?> </strong></p></div>
<?php
}
if (isset($error_message) && $error_message != ''){
	echo $error_message;
}
?>

<form name="frm_leads_list" method="post" action="admin.php?page=rdt&ac=manage_table_data&ID=<?php echo intval($_GET['ID']);?>">
<input type="hidden" name="ID" value="<?php echo intval($_GET['ID']);?>" />
<input type="hidden" name="total_records" id="total_records" value="<?php echo $total_records; ?>" />
<input type="hidden" name="hdn_page_action" value="Save"  />
<table width="100%" class="widefat rdt-table-data" id="straymanage">
<thead>
<tr>
<?php
    if(isset($rdt_columns) && is_array($rdt_columns))
    {
        foreach($rdt_columns as $key => $col_vals)
        {
            $rdt_column = isset($rdt_columns[$key]['title']) ? $rdt_columns[$key]['title']: '';
			
			$span = '';
			if($child_available==1)
			{
				if($rdt_columns[$key]['has_child']==1)
				{
					$span = 'colspan="'.sizeof($rdt_columns[$key]['child']).'"';
				}
				else
				{
					$span = 'rowspan="2"';
				}
			}
    ?>
             <th scope="col" style="text-align:center; border-right:1px solid #e1e1e1;" <?php echo $span?>><?php echo $rdt_column;?></th>
    <?php
        }
    }
 ?>
	<td rowspan="2"><input type="checkbox" name="selectAll" id="selectAll" value="1" style="margin:0;" /></td>
</tr>
<?php
if($child_available==1)
{
?>
<tr>
<?php
    if(isset($rdt_columns) && is_array($rdt_columns))
    {
        foreach($rdt_columns as $key => $col_vals)
        {
			if($rdt_columns[$key]['has_child'] == 0) continue;
            foreach($rdt_columns[$key]['child'] as $childkey => $childval)
			{
    ?>
             	<th scope="col" style="text-align:center; border-right:1px solid #e1e1e1;"><?php echo $rdt_columns[$key]['child'][$childkey]['title'];?></th>
    <?php
			}
        }
    }
?>
</tr>
<?php
}
?>
</thead>
<tbody width="100%" class="widefat" id="rdt-append-new-row" style="display:none">
    <tr>
         <?php
		 $col = 0;
		 	if(isset($rdt_columns) && is_array($rdt_columns))
			{
				foreach($rdt_columns as $key => $col_vals)
				{
					
					if($rdt_columns[$key]['has_child'] == 0)
					{
						$col++;
					?>
                    	<td style=" border-right:1px solid #e1e1e1;border-bottom:1px solid #e1e1e1;"><input type="text" name="column_data[<?php echo $key?>][]" placeholder="<?php echo esc_attr($rdt_columns[$key]['title']);?>" value="" /></td>
                    <?php
					}
					if(isset($rdt_columns[$key]['child']) && is_array($rdt_columns[$key]['child']))
					{
						foreach($rdt_columns[$key]['child'] as $childkey => $childval)
						{
							$col++;
				?>
							<td style=" border-right:1px solid #e1e1e1;border-bottom:1px solid #e1e1e1;"><input type="text" name="column_data[<?php echo $childkey?>][]" placeholder="<?php echo esc_attr($rdt_columns[$key]['child'][$childkey]['title']);?>" value="" /></td>
				<?php
						}
					}
				}
			}
         ?>
    	<td style=" border-right:1px solid #e1e1e1;border-bottom:1px solid #e1e1e1;">&nbsp;</td>
    </tr>
</tbody>
<tbody id="append_more_row">
<?php

    	if(is_array($column_db_data))
		{
			for($i = 0; $i < $total_records; $i++)
			{
			?>
            	<tr>
                	
				<?php
				$k = 0;
				$delids = array();
                foreach($column_db_data as $key => $val)
                {
					$k++;
                 ?>
                    <td style=" border-right:1px solid #e1e1e1;border-bottom:1px solid #e1e1e1;"><input type="text" name="column_data[<?php echo $key?>][]" value="<?php echo esc_attr($val[$i]['data']);?>" /></td>
                 <?php
				 	$delids[] = $val[$i]['ID'];
                }
                ?>
                	<td style="border-bottom:1px solid #e1e1e1;"><input type="checkbox" name="delete_rows[]" value="<?php echo implode(",",$delids);?>" style="margin:0;" /></td>
            	</tr>
            <?php
			}
		}
?>
</tbody>

<tbody>
    <tr style="border-top:1px solid #ccc">
    	<td align="left" colspan="<?php echo $col+1;?>">
        	<a href="javascript:void(0);" style="margin-right:10px" class="button" id="add-new-data-row"> <?php _e('Add Data Row', 'rdt-plugin'); ?> </a>
    		<input name="publish" lang="publish" <?php if($total_records <= 0){?> style="display:none" <?php } ?> id="SaveTableData" class="button button-primary" value="Save Table Data" type="submit" />
            <?php if($total_records > 0){?>
            	<input name="RemoveTableData" lang="remove" id="RemoveTableData" class="button" value="Remove Selected" type="submit" onclick="return rdt_checkSelected();" style="margin-right:10px; float:right; background-color:#ddd" />
            <?php }?>
        </td>
    </tr>
</tbody>
</table>
      
    <?php wp_nonce_field('manage_data', 'rdt_admin_nonce'); ?>
</form>	
	

</div>
