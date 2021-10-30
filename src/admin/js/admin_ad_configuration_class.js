// 6.0.7-3-gce41f93

Event.observe(window, 'load', function () {
	//functionality for the check all checkbox on the hidden fields
	if ($('hiddenCheckAll')) {
		$('hiddenCheckAll').observe('click', function () {
			var checkIt = this.checked;
			$$('input.hiddenFields').each (function (element) {
				element.checked = checkIt; 
			});
		});
	}
});