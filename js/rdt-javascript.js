/** Append new column **/

jQuery(document).ready(function(){
	
	jQuery('#add-new-columns').click(function(){
		var slab_row = '<div class="woo-wholesale-row-inner">'
						+'<input type="text" name="rdt_columns['+current_columns+']" rel="top_columns" placeholder="Column Name" value="">'
						+'	<a href="javascript:void(0);" class="button remove-rdt-col">X</a>	'
						+'	<select name="rdt_columns_subhead_count_'+current_columns+'" onchange="rdt_loadsubhead(this)">'
						+'		<option value="0">0</option>'
						+'		<option value="1">1</option>'
						+'		<option value="2">2</option>'
						+'		<option value="3">3</option>'
						+'		<option value="4">4</option>'
						+'		<option value="5">5</option>'
						+'	</select>'
						+'	<div style="display:inline-block;" class="subheadarea"></div>'
					+'</div>';
		jQuery('#table-content-row-col').append(slab_row);
		current_columns++;
	});	
	
	
	jQuery('#add-new-data-row').click(function(){
		var data_row = jQuery('#rdt-append-new-row').html();
		var tc = jQuery('#total_records').val();

		var data_row_formatted = jQuery('#rdt-append-new-row').html();
		jQuery('#total_records').val(parseInt(jQuery('#total_records').val())+1);
		jQuery('#append_more_row').append(data_row_formatted);
		jQuery('#rdt-append-new-row').html(data_row);
		if(parseInt(jQuery('#total_records').val()) > 0)
		{
			jQuery('#SaveTableData').show();
		}
	});	
	

  jQuery('.rdt-color-picker').wpColorPicker();
  /*jQuery('.rdt-color-picker2').wpColorPicker();
  jQuery('.rdt-color-picker3').wpColorPicker();
  jQuery('.rdt-color-picker4').wpColorPicker();*/
  
});


/** Remove existing column **/
jQuery(document).on('click', '.remove-rdt-col' ,function(){
	jQuery(this).parent('div').css('display','none');
	jQuery(this).parent('div').children('input[rel="status"]').val(0);
});
jQuery(document).on('click', '#selectAll' ,function(){
	jQuery('input[name="delete_rows[]"]').prop('checked', jQuery(this).prop('checked'));
	jQuery('#selectAll2').prop('checked', jQuery(this).prop('checked'));
});
jQuery(document).on('click', '#selectAll2' ,function(){
	jQuery('input[name="delete_rows[]"]').prop('checked', jQuery(this).prop('checked'));
	jQuery('#selectAll').prop('checked', jQuery(this).prop('checked'));
});

function rdt_checkSelected()
{
	var flag = 0;
	jQuery('input[name="delete_rows[]"]').each(function() {
        if (jQuery(this).is(":checked")) {
            flag = 1;
        }
    });
	if(flag == 0)
	{
		alert("Please check atleast one row.");
		return false;
	}
	else
	{
		return true;
	}
}

function rdt_loadsubhead(obj)
{
	tarval = jQuery(obj).val();
	var existing_subhead_col = jQuery(obj).parent('div').children('.subheadarea').html();
	
	var inputs = jQuery('#table-content-row-col input[rel="top_columns"]'); // get all input elements on the page
	var inputindex = inputs.index(jQuery(obj).parent('div').children('input[rel="top_columns"]'));
	
	subhead_col = '<input placeholder="Sub Column Name" type="text" name="rdt_sub_columns['+(inputindex)+'][]" value="">';

	var existing_total = jQuery(obj).parent('div').children('.subheadarea').children().length;
	if(tarval > existing_total)
	{
		for(i = existing_total; i < tarval; i++)
		{
			jQuery(obj).parent('div').children('.subheadarea').append(subhead_col);
		}
	}
	else
	{
		for(i = existing_total; i > tarval; i--)
		{
			jQuery(obj).parent('div').children('.subheadarea').children().last().remove();
		}
	}
}

function rdt_submit_table_settings()
{
	if(document.frm_dynamic_table.table_name.value=="")
	{
		alert("Please enter table name.")
		document.frm_dynamic_table.table_name.focus();
		return false;
	}
	
	if(document.frm_dynamic_table.responsive_layout_col.value=="")
	{
		alert("Please enter width for responsive layouts.")
		document.frm_dynamic_table.responsive_layout_col.focus();
		return false;
	}
}

function rdt_delete_table(id)
{
	if(confirm("Do you want to delete this table?"))
	{
		document.frm_leads_list.action="admin.php?page=rdt&amp;ac=manage_tables&mode=del&sid="+id;
		document.frm_leads_list.submit();
	}
}