/**
 * Function to enable/disable sub category settings.
 **/
function toggle_display_sub_categories(){
	//figure out which ones to toggle to what
	
	if (document.getElementById('sub_cat_on').checked){
		//Display sub categories currently turned on.
		var test = document.getElementsByTagName('input');
		//change the disabled fields
		for (var i=0; i < test.length; i++){
			if (test[i].id == 'surround_pre' || test[i].id == 'surround_post' || test[i].getAttribute('name') == 'c[module_sub_category_nav_prefix]' || test[i].getAttribute('name') == 'c[module_sub_category_nav_separator]'){
				test[i].disabled = false;
			}
		}
		
		
		text_color = document.getElementsByTagName("span");
		for (var b=0; b<text_color.length; b++){
			if (text_color[b].getAttribute('name') == "sub_cat_setting"){
				text_color[b].className="enabled_text";
				//alert (text_color[b].className);
			}
		}
		
	} else {
		//Display sub categories currently turned on.
		var test = document.getElementsByTagName('input');
		//change the disabled fields
		for (var i=0; i < test.length; i++){
			if (test[i].id == 'surround_pre' || test[i].id == 'surround_post' || test[i].getAttribute('name') == 'c[module_sub_category_nav_prefix]' || test[i].getAttribute('name') == 'c[module_sub_category_nav_separator]'){
				test[i].disabled = true;
			}
		}
		
		
		text_color = document.getElementsByTagName("span");
		for (var b=0; b<text_color.length; b++){
			if (text_color[b].getAttribute('name') == "sub_cat_setting"){
				text_color[b].className="disabled_text";
				//alert (text_color[b].className);
			}
		}
	}
	return true;
}