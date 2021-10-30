

function initDebugMessages()
{
	var elements = $$('div.debugMessages');
	var bottom = $('bodyHtml');
	for (var i = 0; i < elements.length; i++) {
		bottom.insert(elements[i]);
	}
}

setTimeout('initDebugMessages()',5000);//give it time to load down to include prototype