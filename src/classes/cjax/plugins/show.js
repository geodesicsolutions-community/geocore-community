
function show (params)
{
	var param = params.xml('param1');
	if ($ && $(param)) {
		//use prototype
		$(param).show();
		return;
	}
	var elem = CJAX.$(param);
	if (elem) {
		elem.style.display = '';
	}
}