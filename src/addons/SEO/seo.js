
function continueClick () {
	if (!$('value1')) {
		return;
	}
	if(!$('value1').checked && !$('value2').checked) {
		alert('Please select one');
		return false;
	}
	if($('value1').checked) 
	{
		window.location='?mc=addon_cat_SEO&page=addon_SEO_main_config&step=4'; 
	} else {
		var out = '';
		out = 'Things you can do if the SEO is not working...<br />';
		out = out+'<ul style=list-style:upper-alpha;text-align:left>';
		out = out+'<li>Make sure you have updated your .htacces file</li>';
		out = out+'<li>Make sure your host supports mod_rewrite and ability to use htaccess files.</li>';
		out = out+'<li>Read the user manual for the SEO Addon for further troubleshooting.</li>';
		out = out+'</ul>';
		
		$('responses').update(out);
		$('continue_button').observe('click', function () { onClick = window.location = '?mc=addon_cat_SEO&page=addon_SEO_main_config&step=2'} );
		return false;
	}
}

//observe the select all button to select all of the htaccess contents.
Event.observe(window,'load',function () {
	if ($('htaccessSelectButton')) {
		$('htaccessSelectButton').observe('click', function () {
			if ($('htaccessTextarea')) {
				$('htaccessTextarea').activate();
			}
		});
	}
	
	if ($('htaccess_setup')) {
		new Ajax.Updater ('htaccess_setup','AJAX.php?controller=addon_SEO&action=WizardGenerateAll&inWizard=1');
	}
});
