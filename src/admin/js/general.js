
//A collection of general javascript functions for use in Geodesic Solutions admin.

function logout (element_on_page)
{
	elem = $(element_on_page);
	//Logout requires sending a post, to prevent the user from book-marking the logout button.
	
	var hidden = new Element('input', {'type': 'hidden','name': 'page_action','value': 'logout'});
	//create the form, add the hidden field
	var form = new Element('form', {'method': 'post','action': 'index.php'}).update(hidden);
	
	//add the form to the page
	elem.insert(form);
	form.submit();
}
