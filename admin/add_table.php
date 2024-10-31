<?php 
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); } 
if(!current_user_can('edit_others_pages'))
{
	die('You are not allowed to work.');
}

$responsive_table_data = array();
$def_rdt_border_color = $rdt_border_color = '#666';
$def_rdt_header_background_color = $rdt_header_background_color = '#666';
$def_rdt_row_background_color = $rdt_row_background_color = '#FFFFFF';
$def_rdt_alternate_row_background_color = $rdt_alternate_row_background_color = '#f7f7f7';
$def_rdt_font_color = $rdt_font_color = '#000';
$def_rdt_header_font_color = $rdt_header_font_color = '#FFFFFF';
$def_rdt_mobile_row_background_color = $rdt_mobile_row_background_color = '#FFF';
$def_rdt_mobile_font_color = $rdt_mobile_font_color = '#000';
$rdt_mobile_font_size = '12';
$responsive_layout_col = 767;
$table_name = '';
$responsive_display_full_table = 0;

if(isset($_GET['ID']) && $_GET['ID'] > 0)
{
	$egSqlTable = "SELECT * FROM `".RDT_MASTER_TABLE."` WHERE `ID` = '".absint($_GET['ID'])."'";
	$record = array();
	$recordTable = $wpdb->get_row($egSqlTable, ARRAY_A);


	$table_name = $recordTable['table_name'];
	
	$responsive_layout_col = $recordTable['responsive_width'];
	$responsive_display_full_table = $recordTable['responsive_display_full_table'];
	
	$rdt_border_color = $recordTable['border_color'];
	$rdt_header_background_color = $recordTable['header_background_color'];
	$rdt_row_background_color = $recordTable['row_bg_color'];
	$rdt_alternate_row_background_color = $recordTable['alternate_bg_color'];
	$rdt_font_color = $recordTable['font_color'];
	$rdt_header_font_color = $recordTable['header_font_color'];
	
	$rdt_mobile_row_background_color = $recordTable['mobile_row_bg_color'];
	$rdt_mobile_font_color = $recordTable['mobile_font_color'];
	$rdt_mobile_font_size  = $recordTable['mobile_font_size'];

	$egSqlTableColumn = "SELECT * FROM `".RDT_TABLE_COLUMN."` WHERE `table_id` = '".absint($_GET['ID'])."' AND `parent_id` = 0 AND column_status = 1 ORDER BY `ID`";
	$recordTableColumn = $wpdb->get_results($egSqlTableColumn, ARRAY_A);
	$rdt_columns = array();
	if(sizeof($recordTableColumn) > 0)
	{
		for($i = 0; $i < sizeof($recordTableColumn); $i++)
		{
			$rdt_columns[$recordTableColumn[$i]['ID']]['title'] = $recordTableColumn[$i]['column_title'];
			$rdt_columns[$recordTableColumn[$i]['ID']]['column_status'] = $recordTableColumn[$i]['column_status'];
			$rdt_columns[$recordTableColumn[$i]['ID']]['has_child'] = $recordTableColumn[$i]['has_child'];
			if($recordTableColumn[$i]['has_child'] == 1)
			{
				$egSqlTableColumnChild = "SELECT * FROM `".RDT_TABLE_COLUMN."` WHERE `table_id` = '".absint($_GET['ID'])."' AND `parent_id` = '".absint($recordTableColumn[$i]['ID'])."' AND column_status = 1 ORDER BY `ID`";
				$recordTableColumnChild = $wpdb->get_results($egSqlTableColumnChild, ARRAY_A);
				if(sizeof($recordTableColumnChild) > 0)
				{
					for($c = 0; $c < sizeof($recordTableColumnChild); $c++)
					{
						$rdt_columns[$recordTableColumn[$i]['ID']]['child'][$recordTableColumnChild[$c]['ID']]['title'] = $recordTableColumnChild[$c]['column_title'];
					}
				}
			}
		}
	}

}

?>
<div class="form-wrap">
    <div id="icon-plugins" class="icon32"></div>
    <h2><?php _e(RDT_PLUGIN_TITLE, 'rdt-plugin'); ?></h2>

	<div id="rdt-append-new-col" style="display:none;"></div>
    <div id="rdt-append-new-sub-col" style="display:none;">
    	<input placeholder="Sub Column Name" type="text" name="rdt_sub_columns[][]" value="">
    </div>
    
	<form name="frm_dynamic_table" method="post" action="<?php echo get_option('siteurl'); ?>/wp-admin/admin.php?page=rdt&amp;ac=manage_tables">
    <input type="hidden" name="hdn_dynamic_table" id="hdn_dynamic_table" value="Yes" />
    <input type="hidden" name="d_table_id" id="d_table_id" value="<?php echo $_GET['ID'];?>" />
    <input type="hidden" name="rdt_total_existing_columns" id="rdt_total_existing_columns" value="<?php echo sizeof($rdt_columns);?>" />
    	<div class="table-content-area">
        	<div class="table-content-row">
                <div class="table-content-header-col">
                    <label for="table_name">Table Name</label>
                    <input placeholder="Table Name" size="30" type="text" name="table_name" id="table_name" value="<?php echo esc_html($table_name);?>" />
                 </div>
             </div>
             <div class="table-content-row">
                 <div class="table-content-header-col">
                    <label style="display:inline;"><input type="radio" name="responsive_display_full_table" id="responsive_display_full_table_1" onclick="displayWidthText(1)" value="1" <?php if($responsive_display_full_table == 1) echo 'checked'; ?> /> Display full table in small screen</label> &nbsp; &nbsp;
                    <label style="display:inline;"><input type="radio" name="responsive_display_full_table" id="responsive_display_full_table_0" onclick="displayWidthText(0)" value="0" <?php if($responsive_display_full_table == 0) echo 'checked'; ?> /> Display responsive table in small screen</label>
                </div>
             </div>
             <div class="table-content-row">
                <div class="table-content-header-col">
                	<label for="responsive_layout_col" id="responsive_layout_col_text"><?php if($responsive_display_full_table == 1) echo 'Define Minimum Width'; else echo 'Define Width for responsive layout'; ?></label>
                    <input style="max-width:125px;" placeholder="Responsive Layouts width" type="number" name="responsive_layout_col" id="responsive_layout_col" value="<?php echo esc_html($responsive_layout_col);?>" /> [in Pixel]
                </div>
             </div>
            <div class="rdt-w3-border">
            <div class="rdt-w3-bar rdt-w3-border-bottom rdt-w3-light-grey intronav">
            <button class="rdt-w3-bar-item rdt-w3-button rdt-w3-dark-grey" type="button" id="btn1" onclick="rdt_openCity('columnsettings','btn1')">Column Settings</button>
            <button class="rdt-w3-bar-item rdt-w3-button" type="button" id="btn2" onclick="rdt_openCity('displaysettings','btn2')">Appearance Settings</button>
            </div>
             	<div id="columnsettings" class="rdt-city">
					<div class="rdt-table-content-row">
                     <div id="table-content-row-col">
                      <h3>Add Columns</h3>
                      <?php
                     // echo count($rdt_columns);
					 $i = 0;
                        if(isset($rdt_columns) && is_array($rdt_columns))
                        {
                            foreach($rdt_columns as $key => $col_vals)
                            {
                                $rdt_column = $rdt_columns[$key]['title'];
                        ?>
                                 <div class="woo-wholesale-row-inner" <?php if($rdt_columns[$key]['column_status'] == 0) echo 'style="display:none;"'; ?>>
                                 	<input type="hidden" name="rdt_columns_id[<?php echo $i;?>]" value="<?php echo $key;?>">
                                 	<input type="hidden" name="rdt_columns_status[<?php echo $i;?>]" rel="status" value="1">
                                    <input placeholder="Column Name" type="text" name="rdt_columns[<?php echo $i;?>]" rel="top_columns" value="<?php echo esc_attr($rdt_column);?>">
                                    <a href="javascript:void(0);" class="button remove-rdt-col"><?php _e('X', 'rdt-plugin'); ?></a>	
                                    	<select name="rdt_columns_subhead_count_<?php echo $i;?>" onchange="rdt_loadsubhead(this)">
                                        	<option value="0">0</option>
                                            <option value="1" <?php echo isset($rdt_columns[$key]['child']) && sizeof($rdt_columns[$key]['child'])==1?'selected':''?>>1</option>
                                            <option value="2" <?php echo isset($rdt_columns[$key]['child']) && sizeof($rdt_columns[$key]['child'])==2?'selected':''?>>2</option>
                                            <option value="3" <?php echo isset($rdt_columns[$key]['child']) && sizeof($rdt_columns[$key]['child'])==3?'selected':''?>>3</option>
                                            <option value="4" <?php echo isset($rdt_columns[$key]['child']) && sizeof($rdt_columns[$key]['child'])==4?'selected':''?>>4</option>
                                            <option value="5" <?php echo isset($rdt_columns[$key]['child']) && sizeof($rdt_columns[$key]['child'])==5?'selected':''?>>5</option>
                                        </select>
                                        <div style="display:inline-block;" class="subheadarea">
                                    <?php
									if($rdt_columns[$key]['has_child'] == 1)
									{
										$j = 0;
										foreach($rdt_columns[$key]['child'] as $childkey => $child_col_vals)
										{
									?>
                                    		<input placeholder="Sub Column Name" type="text" name="rdt_sub_columns[<?php echo $i;?>][<?php $j?>]" value="<?php echo esc_attr($rdt_columns[$key]['child'][$childkey]['title']);?>">
                                    <?php
											$j++;
										}
									}
									?>
                                    	</div>
                                    <?php
									if($rdt_columns[$key]['has_child'] == 1)
									{
										$j = 0;
										foreach($rdt_columns[$key]['child'] as $childkey => $child_col_vals)
										{
									?>
                                    		<input type="hidden" name="rdt_sub_columns_id[<?php echo $i;?>][<?php $j?>]" value="<?php echo $childkey;?>">
                                    <?php
											$j++;
										}
									}
									?>
                                </div>
                        <?php
								$i++;
                            }
                        }
                     ?>
                     </div>
                     <a href="javascript:void(0);" class="button" id="add-new-columns"> <?php _e('Add New Column', 'rdt-plugin'); ?> </a>
                 </div>
                </div>
             	<div id="displaysettings" class="rdt-city" style="display:none">
                    <div class="rdt-table-content-row">
                    <h3>Table Display Settings:</h3>
                    
                    <div class="woo-wholesale-row-inner">
                    <div class="rdt-tbl-appearance-title">Border Color:</div>
                    <div class="rdt-tbl-color-picker"><input type="text" value="<?php echo esc_attr($rdt_border_color);?>" name="rdt_border_color" class="rdt-color-picker" data-default-color="<?php echo $def_rdt_border_color;?>" /></div>
                    </div>
                    
                    <div class="woo-wholesale-row-inner">
                    <div class="rdt-tbl-appearance-title">Header Background Color:</div>
                    <div class="rdt-tbl-color-picker"><input type="text" value="<?php echo esc_attr($rdt_header_background_color);?>" name="rdt_header_background_color" class="rdt-color-picker" data-default-color="<?php echo $def_rdt_header_background_color;?>" /></div>
                    </div>
                    
                    <div class="woo-wholesale-row-inner">
                    <div class="rdt-tbl-appearance-title">Header Font Color:</div>
                    <div class="rdt-tbl-color-picker"><input type="text" value="<?php echo esc_attr($rdt_header_font_color);?>" name="rdt_header_font_color" class="rdt-color-picker" data-default-color="<?php echo $def_rdt_header_font_color;?>" /></div>
                    </div>
                    
                    <div class="woo-wholesale-row-inner">
                    <div class="rdt-tbl-appearance-title">Row Background Color:</div>
                    <div class="rdt-tbl-color-picker"><input type="text" value="<?php echo esc_attr($rdt_row_background_color);?>" name="rdt_row_background_color" class="rdt-color-picker" data-default-color="<?php echo $def_rdt_row_background_color;?>" /></div>
                    </div>
                    
                    <div class="woo-wholesale-row-inner">
                    <div class="rdt-tbl-appearance-title">Alternate Row Background Color:</div>
                    <div class="rdt-tbl-color-picker"><input type="text" value="<?php echo esc_attr($rdt_alternate_row_background_color);?>" name="rdt_alternate_row_background_color" class="rdt-color-picker" data-default-color="<?php echo $def_rdt_alternate_row_background_color;?>" /></div>
                    </div>
                    <div class="woo-wholesale-row-inner">
                    <div class="rdt-tbl-appearance-title">Font Color:</div>
                    <div class="rdt-tbl-color-picker"><input type="text" value="<?php echo esc_attr($rdt_font_color);?>" name="rdt_font_color" class="rdt-color-picker" data-default-color="<?php echo $def_rdt_font_color;?>" /></div>
                    </div>
                    
                    <div class="woo-wholesale-row-inner">
                    <div class="rdt-tbl-appearance-title">Row Background Color [For Mobile]:</div>
                    <div class="rdt-tbl-color-picker"><input type="text" value="<?php echo esc_attr($rdt_mobile_row_background_color);?>" name="rdt_mobile_row_background_color" class="rdt-color-picker" data-default-color="<?php echo $def_rdt_mobile_row_background_color;?>" /></div>
                    </div>
                    
                    <div class="woo-wholesale-row-inner">
                    <div class="rdt-tbl-appearance-title">Font Color [For Mobile]:</div>
                    <div class="rdt-tbl-color-picker"><input type="text" value="<?php echo esc_attr($rdt_mobile_font_color);?>" name="rdt_mobile_font_color" class="rdt-color-picker" data-default-color="<?php echo $def_rdt_mobile_font_color;?>" /></div>
                    </div>
                    <div class="woo-wholesale-row-inner">
                    <div class="rdt-tbl-appearance-title">Font Size [For Mobile in Pixel]:</div>
                    <div class="rdt-tbl-color-picker"><input style="width:70px" type="number" value="<?php echo esc_attr($rdt_mobile_font_size);?>" name="rdt_mobile_font_size" /></div>
                    </div>
                    
                    </div>
                </div>
             
             
        </div>
       <div class="table-content-row">
       <p class="submit"><input name="publish" lang="publish" class="button button-primary add-new-h2" value="Save" type="submit" onclick="return rdt_submit_table_settings()" /></p>
		</div>
        <?php wp_nonce_field('add_data_table', 'rdt_admin_nonce'); ?>
    </form>

</div>
<script type="text/javascript">
var current_columns = <?php echo $i;?>;
function rdt_openCity(cityName,btn) {
  var i;
  var x = document.getElementsByClassName("rdt-city");
  for (i = 0; i < x.length; i++) {
    x[i].style.display = "none"; 
  }
  document.getElementById(cityName).style.display = "block"; 

  element = document.getElementById(btn);
  arr = element.className.split(" ");
  if (arr.indexOf('rdt-w3-dark-grey') == -1) {
    element.className += " rdt-w3-dark-grey";
  }
  var btn2 = btn == "btn1" ? 'btn2' : 'btn1';
  element = document.getElementById(btn2);
  arr = element.className.split(" ");
  if (arr.indexOf('rdt-w3-dark-grey') > 0) {
    element.className = "rdt-w3-bar-item rdt-w3-button";
  }
}
function displayWidthText(val)
{
	if(val == 1)
	{
		document.getElementById('responsive_layout_col_text').innerHTML = 'Define Minimum Width';
	}
	else
	{
		document.getElementById('responsive_layout_col_text').innerHTML = 'Define Width for responsive layout';
	}
}
</script>