<?php

function rdt_display_table_data($atts)
{
	ob_start();
	global $wpdb;
	extract( shortcode_atts( array('id' => 1), $atts ) );
	$form_id = $id;
	$child_available = 0;
	$egSql = "SELECT * FROM ".RDT_MASTER_TABLE." WHERE `ID` = '".sanitize_text_field($form_id)."'";
	$table_data = $wpdb->get_row($egSql, ARRAY_A);
	
	if(!is_array($table_data))
	{
		//$error_message = '<h3>Please create table and define table columns to provide data.</h3>';
	}
	else
	{
		$table_name = isset($atts['title']) ? $atts['title'] : $table_data['table_name']; 
		
		$responsive_layout_col = $table_data['responsive_width'];
		$responsive_layout_col = $responsive_layout_col == '' ? '767' : $responsive_layout_col;
		$responsive_display_full_table = $table_data['responsive_display_full_table'];
		
		$rdt_border_color = $table_data['border_color'];
		$rdt_header_background_color = $table_data['header_background_color'];
		$rdt_row_background_color = $table_data['row_bg_color'];
		$rdt_alternate_row_background_color = $table_data['alternate_bg_color'];
		$rdt_font_color = $table_data['font_color'];
		$rdt_header_font_color = $table_data['header_font_color'];
		
		$rdt_mobile_row_bgcolor = $table_data['mobile_row_bg_color'];
		$rdt_mobile_fontColor = $table_data['mobile_font_color'];
		$rdt_mobile_fontSize  = $table_data['mobile_font_size'];
		
		$egSqlTableColumn = "SELECT * FROM `".RDT_TABLE_COLUMN."` WHERE `table_id` = '".absint($form_id)."' AND `parent_id` = 0 AND column_status = 1";
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
					$egSqlTableColumnChild = "SELECT * FROM `".RDT_TABLE_COLUMN."` WHERE `table_id` = '".absint($form_id)."' AND `parent_id` = '".absint($recordTableColumn[$i]['ID'])."' AND column_status = 1";
					$recordTableColumnChild = $wpdb->get_results($egSqlTableColumnChild, ARRAY_A);
					if(sizeof($recordTableColumnChild) > 0)
					{
						for($c = 0; $c < sizeof($recordTableColumnChild); $c++)
						{
							$rdt_columns[$recordTableColumn[$i]['ID']]['child'][$recordTableColumnChild[$c]['ID']]['title'] = $recordTableColumnChild[$c]['column_title'];
							
							$egSqlChildColumnData = "SELECT `column_data`, `ID` FROM `".RDT_TABLE_DATA."` WHERE `column_id` = '".absint($recordTableColumnChild[$c]['ID'])."'";
							$egRecordsChildColumnData = $wpdb->get_results($egSqlChildColumnData, ARRAY_A);
							if(is_array($egRecordsChildColumnData) && sizeof($egRecordsChildColumnData) > 0)
							{
								$d = 0;
								for($n = 0; $n < sizeof($egRecordsChildColumnData); $n++)
								{
									$column_db_data[$recordTableColumnChild[$c]['ID']][$d]['data'] = $egRecordsChildColumnData[$n]['column_data'];
									$column_db_data[$recordTableColumnChild[$c]['ID']][$d]['ID'] = $egRecordsChildColumnData[$n]['ID'];
									$column_db_data[$recordTableColumnChild[$c]['ID']][$d]['column_name'] = $recordTableColumnChild[$c]['column_title'];
									$column_db_data[$recordTableColumnChild[$c]['ID']][$d]['parent_column'] = $recordTableColumn[$i]['column_title'];
									$d++;
								}
								$total_records = sizeof($egRecordsChildColumnData);
							}
						}
					}
				}
				else
				{
					$egSqlColumnData = "SELECT `column_data`, `ID` FROM `".RDT_TABLE_DATA."` WHERE `column_id` = '".absint($recordTableColumn[$i]['ID'])."'";
					$egRecordsColumnData = $wpdb->get_results($egSqlColumnData, ARRAY_A);
					if(is_array($egRecordsColumnData) && sizeof($egRecordsColumnData) > 0)
					{
						$d = 0;
						for($n = 0; $n < sizeof($egRecordsColumnData); $n++)
						{
							$column_db_data[$recordTableColumn[$i]['ID']][$d]['data'] = $egRecordsColumnData[$n]['column_data'];
							$column_db_data[$recordTableColumn[$i]['ID']][$d]['ID'] = $egRecordsColumnData[$n]['ID'];
							$column_db_data[$recordTableColumn[$i]['ID']][$d]['column_name'] = $recordTableColumn[$i]['column_title'];
							$column_db_data[$recordTableColumn[$i]['ID']][$d]['parent_column'] = '';
							$d++;
						}
						$total_records = sizeof($egRecordsColumnData);
					}
				}
			}
		}
		else
		{
			//$error_message = '<h3>Please create table columns.</h3>';
		}
	}

	if(sizeof($column_db_data) > 0)
	{
?>
<style>
	.rdt-front-table-data-<?php echo $form_id;?> th, .rdt-front-table-data-<?php echo $form_id;?> td, .rdt-front-table-data-<?php echo $form_id;?> .rdt-responsive-col td{border:1px solid <?php echo esc_html($rdt_border_color);?>; padding:0 10px; color:<?php echo esc_html($rdt_font_color);?>}
	.rdt-front-table-data-<?php echo $form_id;?> th{color:<?php echo esc_html($rdt_header_font_color);?>; text-align:center;}
	.rdt-table-full-display-<?php echo $form_id;?>{width:100%; overflow: auto; -webkit-overflow-scrolling: auto; overflow-y: hidden;}
	.rdt-table-full-display-<?php echo $form_id;?> table{margin:0;}
	.rdt-responsive-div{width:100%; overflow:hidden; display:block;}
	div.rdt-table-full-display-<?php echo $form_id;?>::-webkit-scrollbar{height: 10px;}
	div.rdt-table-full-display-<?php echo $form_id;?>::-webkit-scrollbar-track {
		-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
	}
	
	div.rdt-table-full-display-<?php echo $form_id;?>::-webkit-scrollbar-thumb {
		border-radius: 10px;
		-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.5);
	}
	.rdt-table-full-display-<?php echo $form_id;?> .rdt-front-table-data-<?php echo $form_id;?>{min-width:<?php echo esc_html($responsive_layout_col);?>px;}
	<?php if($responsive_display_full_table == 0) {?>
		@media only screen and (max-width: <?php echo esc_html($responsive_layout_col);?>px) {
			.rdt-front-table-data-<?php echo $form_id;?> .rdt-desktop-col{display:none;}
			.rdt-front-table-data-<?php echo $form_id;?> .rdt-responsive-col{display:table-row;}
			.rdt-front-table-data-<?php echo $form_id;?>{border:none;}
			.rdt-front-table-data-<?php echo $form_id;?> td{border:none;}
			.rdt-front-table-data-<?php echo $form_id;?> .rdt-responsive-col td td{border:1px solid <?php echo esc_html($rdt_border_color);?>; -ms-word-break: break-all; word-break: break-all;}
			.rdt-responsive-table-data-<?php echo $form_id;?> tr td:first-child{width:170px; font-weight:bold;}
		}
		@media only screen and (max-width: 480px) {
			.rdt-responsive-table-data-<?php echo $form_id;?> tr td:first-child{width:120px;}
		}
	<?php
	}
	?>
	
</style>
<?php if($responsive_display_full_table == 1) {?><div class="rdt-responsive-div"><div class="rdt-table-full-display-<?php echo $form_id;?>"><?php }?>
<table width="100%" class="rdt-front-table-data-<?php echo $form_id;?>" cellpadding="0" cellspacing="0">
<caption><?php echo $table_name;?></caption>
<thead>
<tr class="rdt-desktop-col" style="background-color:<?php echo esc_html($rdt_header_background_color);?>">
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
             <th scope="col" <?php echo $span?> style="background-color:<?php echo esc_html($rdt_header_background_color);?>"><?php echo esc_html($rdt_column);?></th>
    <?php
        }
    }
 ?>
</tr>
<?php
if($child_available==1)
{
?>
<tr class="rdt-desktop-col" style="background-color:<?php echo esc_html($rdt_header_background_color);?>">
<?php
    if(isset($rdt_columns) && is_array($rdt_columns))
    {
        foreach($rdt_columns as $key => $col_vals)
        {
			if($rdt_columns[$key]['has_child'] == 0) continue;
            foreach($rdt_columns[$key]['child'] as $childkey => $childval)
			{
    ?>
             	<th><?php echo esc_html($rdt_columns[$key]['child'][$childkey]['title']);?></th>
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
<tbody>
<?php

    	if(is_array($column_db_data))
		{
			$allowed_html = array(
				'a'      => array(
					'href'  => array(),
					'title' => array(),
				),
				'br'     => array(),
				'strong' => array(),
			);

			for($i = 0; $i < $total_records; $i++)
			{
				if($i%2 == 0)
				$tdRowBgColor = $rdt_row_background_color;
				else
				$tdRowBgColor = $rdt_alternate_row_background_color;

				$data_print_arr = array();
			?>
            	<tr class="rdt-desktop-col" style="background-color:<?php echo esc_html($tdRowBgColor);?>">
				<?php
                foreach($column_db_data as $key => $val)
                {
                 ?>
                    <td align="center"><span><?php echo wp_kses($val[$i]['data'], $allowed_html);?></span></td>
                 <?php
                }
                ?>
            	</tr>
                <tr class="rdt-responsive-col">
                 	<td colspan="<?php echo sizeof($column_db_data);?>">
                    	<table width="100%" class="rdt-front-table-data-<?php echo $form_id;?> rdt-responsive-table-data-<?php echo $form_id;?>" cellpadding="0" cellspacing="0">
                        <?php
							$j = 0;
							$parent_column = '';
							foreach($column_db_data as $key => $val)
							{
								if($j%2 == 0)
								$tdRowBgColor = $rdt_row_background_color;
								else
								$tdRowBgColor = $rdt_alternate_row_background_color;

								if($val[$i]['parent_column'] != $parent_column)
								{
									$parent_column = $val[$i]['parent_column'];
									if($parent_column != '')
									{
								?>
                                    <tr style="background-color:<?php echo esc_html($rdt_header_background_color);?>">
                                        <td colspan="2" style="color:<?php echo esc_html($rdt_header_font_color);?>"><?php echo esc_html($val[$i]['parent_column']);?></td>
                                    </tr>
                                <?php
									}
								}
							?>
                            	<tr style="background-color:<?php echo esc_html($tdRowBgColor);?>">
                                	<td valign="middle"><?php echo esc_html($val[$i]['column_name']);?></td>
									<td valign="middle"><span><?php echo wp_kses($val[$i]['data'], $allowed_html);?></span></td>
                                </tr>
							<?
								$j++;
							}
						 ?>
                        </table>
                    </td>
                 </tr>
            <?php
			}
		}
?>
</tbody>
</table>
<?php if($responsive_display_full_table == 1) {?></div></div><?php }?>
<?
	}
	return ob_get_clean();
}

add_shortcode('rdt', "rdt_display_table_data" );
?>