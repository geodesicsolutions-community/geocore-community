
var homePage = {
	init : function () {
		if ($('paidSupportToggle')) {
			$('paidSupportToggle').observe('click', homePage.paidSupportClick);
		}

		if ($('downloadToggle')) {
			$('downloadToggle').observe('click', homePage.downloadClick);
		}

		if ($('freeSupportToggle')) {
			$('freeSupportToggle').observe('click', homePage.freeSupportClick);
		}
	},

	paidSupportClick : function (action) {
		action.stop();

		Effect.toggle($('paidSupport_Links'), 'appear', {beforeStart : homePage.afterPaidToggle});
	},

	afterPaidToggle : function () {
		if (!$('paidSupport_Links').visible()) {
			$('paidSupportToggle').update('Hide Options');
		} else {
			$('paidSupportToggle').update('Show Options');
		}
	},

	downloadClick : function (action) {
		action.stop();

		Effect.toggle($('download_Links'), 'appear', {beforeStart : homePage.afterDownloadToggle});
	},

	afterDownloadToggle : function () {
		if (!$('download_Links').visible()) {
			$('downloadToggle').update('Hide Options');
		} else {
			$('downloadToggle').update('Show Options');
		}
	},

	freeSupportClick : function (action) {
		action.stop();

		Effect.toggle($('freeSupport_Links'), 'appear', {beforeStart : homePage.afterfreeToggle});
	},

	afterfreeToggle : function () {
		if (!$('freeSupport_Links').visible()) {
			$('freeSupportToggle').update('Hide Options');
		} else {
			$('freeSupportToggle').update('Show Options');
		}
	}
};

Event.observe(window,'load',function () {
	homePage.init();
});
