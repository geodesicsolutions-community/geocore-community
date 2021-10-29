/**
# Author :  Carlos Galindo
# Website: cjax.net
# Date :  2008 3:56:38 AM 
# Copyright 2008
**/

var collection = [];

function invalidate(params)
{
	var field = CJAX.xml('param1',params);
	var error = CJAX.xml('param2',params);
	
	if(!error) error ='';
	if(collection['invalidate_use']){
		if(collection['invalidate_label'])
		{
			var L = CJAX.is_element(collection['invalidate_label'],false);
			if(L)
			{
				CJAX.remove(L);
			}
			if(collection['invalidate_element'])
			{
			  element = CJAX.is_element(collection['invalidate_element'],false);
			  if(element)
			  {
			  	element.style.borderColor = collection['invalidate_original_color'];
			  }
			}
		}
	// delete collection['invalidate_user'];
	CJAX.EventCache.flush();
	}
	var element = CJAX.is_element(field,false);
	if(!element) return false;
	var err = CJAX.xml('error',field);
	err = CJAX.decode(error);
	err =  "<div class='invalidate'>"+err+"</div>";
	if(!element)return false;
	
	collection['invalidate_element'] = element.id;
	collection['invalidate_label'] = element.id+'_reponse';
	
	collection['invalidate_original_color'] = element.style.borderColor;
	element.style.borderColor = 'Red';
	element.focus();
	//we would like to restore the error if the user inputs a valid value
	var exists = CJAX.is_element(collection['invalidate_label'],false);
	if(exists)
	{
		//remove html tags and just display text
		err = CJAX.remove_html(err);
		alert(err);
		element.style.borderColor = 'blue';
		
 			setTimeout ( function() { CJAX.$(collection['invalidate_element'],false).style.borderColor = 'Red'; }, 500 );
	}
	else
	{ 
	
		var parent = CJAX.get.property.parent(element);
		parent_id = element.id+'_parent';
		var position = CJAX.get.property.position(element);
		var invalidate_label = CJAX.create.div(collection['invalidate_label'],parent_id,false);
		CJAX.update(invalidate_label,err);
		//if(!parent.style.position) parent.style.position ='relative';
		invalidate_label.style.position = 'absolute';
		invalidate_label.style.left = position['left']+150+'px';
		invalidate_label.style.top = position['top']+'px';
		
		element.focus();
		CJAX.set.event(element,'blur',function() 
		{
			var element = CJAX.$(collection['invalidate_element']);
			if(element.value !='')
			{
				element.style.borderColor = collection['invalidate_original_color'];
				CJAX.hide(collection['invalidate_label'],false);
			}
			collection['element_focus'] = 1;
		});
		
		
		if(!collection['window_focus']){
			CJAX.set.event(window,'focus',function() 
			{
				if(collection['invalidate_label'])
				{
					var L = CJAX.is_element(collection['invalidate_label'],false);
					if(L)
					{
						CJAX.remove(L);
					}
					if(collection['invalidate_element'])
					{
					  var element = CJAX.is_element(collection['invalidate_element'],false);
					  if(element)
					  {
					  	element.style.borderColor = collection['invalidate_original_color'];
					  }
					}
				}
				
				collection['window_focus'] = 1;
			});
		}
		collection['invalidate_use'] = 1;
	}
}