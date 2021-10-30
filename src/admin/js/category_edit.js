// 7.3beta2-102-g09573a0

Event.observe(window, 'load', function () {
	if ($('which_head_html')) {
		$('which_head_html').observe('change', function () {
			var which_html = this.getValue();
			$$('div.head_html').each(function (elem) {
				elem[((which_html=='cat'||which_html=='cat+default')? 'appear':'fade')]();
			});
		});
	}
});