// 7.2.4-16-gb73e84f


var geoAdminCart = {
	verifiedUrl : '',
	
	init : function () {
		if (!jQuery('#userSearch').length) {
			//not on user search step...
			return;
		}
		jQuery('#userSearch').autocomplete({
			source : function (request, response) {
				jQuery.getJSON('index.php?page=admin_cart_select_user&auto_save_ajax=1', {
					userSearch : request.term
				}, response);
			},
			focus : function (event,ui) {
				jQuery('#userSearch').val(ui.item.username);
				jQuery('#userIdInput').val(ui.item.id);
			},
			select : function (event, ui) {
				jQuery('#userSearch').val(ui.item.username);
				jQuery('#userIdInput').val(ui.item.id);
				jQuery('#selectUserForm').submit();
			}
		})
		.data('ui-autocomplete')._renderItem = function (ul,item) {
			var userDisplay = '<a><strong>'+item.username+' ('+item.id+')</strong><br>';
			if (item.verified=='yes' && geoAdminCart.verifiedUrl) {
				userDisplay += '<img src="'+geoAdminCart.verifiedUrl+'" alt=""><br>';
			}
			userDisplay += item.firstname+' '+item.lastname+'<br>';
			if (item.address) {
				userDisplay += item.address+'<br>';
			}
			if (item.city) {
				userDisplay += item.city+', ';
			}
			if (item.state) {
				userDisplay += item.state+', ';
			}
			if (item.country) {
				userDisplay += item.country;
			}
			userDisplay += '</a>';
			return jQuery('<li>')
				.append(userDisplay)
				.appendTo(ul);
		};
		
		return;
	}
};

jQuery(geoAdminCart.init);
