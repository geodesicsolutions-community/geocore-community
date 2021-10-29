//price_plan_items.js
/**************************************************************************
Geodesic Classifieds & Auctions Platform 18.02
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
/*
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## ##    7.4.6-11-g316131f
## 
##################################
*/


//js code to do fancy stuff for payment gateway page
configuresOpen = 0;
function configureItem (item, price_plan, category)
{
	jQuery('#container_'+item).show();
	jQuery.get('AJAX.php',
		{
			controller: 'price_plan_items',
			action: 'display_config',
			item: item,
			price_plan_id: price_plan,
			category_id: category,
		},
		function(result) {
			jQuery('#container_'+item).html(result);
			jQuery('#row_for'+item).css('border-color','#006699');
			var save_btn = "<a id='save_"+item+"' href='javascript:void(0);' onclick=\"saveItem('"+item+"','"+price_plan+"','"+category+"');\" class='mini_button'>Save</a>";
			var cancel_btn = "<a id='cancel_"+item+"' href='javascript:void(0);' onclick=\"cancelItem('"+item+"','"+price_plan+"','"+category+"');\" class='mini_cancel' style='margin-left: 4px;'>Cancel</a>";
			jQuery('#update_config_'+item).html(save_btn+cancel_btn);
			configuresOpen++;
		}
	);
		
}

function cancelItem (item, price_plan, category)
{
	jQuery('#container_'+item).hide();
	jQuery('#row_for'+item).css('border-color','#EAEAEA');
	jQuery('#update_config_'+item).html("<a href='javascript:void(0);' onclick=\"configureItem('"+item+"','"+price_plan+"', '"+category+"')\" class='mini_button'>Configure</a>");
	configuresOpen--;
}

function saveItem (item, price_plan, category)
{
	jQuery('#container_'+item).hide();
	jQuery.post('AJAX.php?controller=price_plan_items&action=save&item='+item+'&price_plan_id='+price_plan+'&category_id='+category,
		jQuery('#form_'+item).serialize(),
		function(result) {
			gjUtil.addMessage('<div style="text-align: center; font-size: 16pt; font-weight: bold;">'+result+'</div>', 1500);
			jQuery('#row_for'+item).css('border-color','#EAEAEA');
			jQuery('#update_config_'+item).html("<a href='javascript:void(0);' onclick=\"configureItem('"+item+"','"+price_plan+"', '"+category+"')\" class='mini_button'>Configure</a>");
			configuresOpen--;
		}
	);
}