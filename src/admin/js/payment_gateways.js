//payment_gateways.php
/*
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    7.3.3-31-gc54c7f1
## 
##################################
*/


//js code to do fancy stuff for payment gateway page
var payGateway = {
	init : function () {
		jQuery('.gatewayConfigure').click(payGateway.configureClick);
		jQuery('.gatewaySave').click(payGateway.saveClick);
		jQuery('.gatewayCancel').click(payGateway.cancelClick);
		jQuery('#frm_all_settings').submit(payGateway.saveAllClick);
		jQuery('.enabledGateway').click(payGateway.enabledClick);
		jQuery('.order_column a').click(payGateway.orderClick);
	},
	
	configureClick : function () {
		var name = jQuery(this).closest('li').find('input.gateway_name').val();
		var group = jQuery('#payGroup').val();
		
		jQuery('#row_for'+name).css({borderColor : '#006699'});
		
		jQuery.ajax({
			dataType: 'json',
			url : 'AJAX.php?controller=PaymentGateways&action=configure&group='+group+'&item='+name,
			type : 'GET',
			success : function (data, textStatus) {
				if (data.error) {
					//error message!
					geoUtil.addError(data.error);
					payGateway.cancel(name);
					return false;
				}
				if (!data.settings) {
					//something else wrong?
					payGateway.cancel(name);
					return false;
				}
				var settings = jQuery('<div id="form_'+name+'"/>').append(data.settings);
				
				jQuery('#container_'+name).empty()
					.append(settings)
					.show('fast')
					//allow tooltips to work
					.find('img.tooltip').hover(gjTooltip.hoverShow,gjTooltip.deactivate)
					.click(gjTooltip.toggle_sticky).css({cursor: 'pointer'});
				
				jQuery('#update_config_'+name).find('a.gatewaySave,a.gatewayCancel').show('fast');
				jQuery('#update_config_'+name+' a.gatewayConfigure').hide('fast');
			},
			error : function () {
				//error!  Cancel it
				geoUtil.addError('Error: Request failed when attempting to retrieve settings!');
				payGateway.cancel(name);
			}
		});
		return false;
	},
	
	saveClick : function () {
		var name = jQuery(this).closest('li').find('input.gateway_name').val();
		var group = jQuery('#payGroup').val();
		
		jQuery.ajax({
			dataType: 'json',
			url : 'AJAX.php?controller=PaymentGateways&action=save&group='+group+'&item='+name,
			type : 'POST',
			data : jQuery('#frm_all_settings').serialize(),
			success : function (data, textStatus) {
				if (data.error) {
					//error message!
					geoUtil.addError(data.error);
					payGateway.cancel(name);
					return false;
				}
				if (data.admin_messages) {
					payGateway.showAdminMessages(data.admin_messages);
				}
				payGateway.cancel(name);
			},
			error : function () {
				//error!  Cancel it
				geoUtil.addError('Error: Request failed when attempting to save!');
				payGateway.cancel(name);
			}
		});
		
		return false;
	},
	
	cancelClick : function () {
		payGateway.cancel(jQuery(this).closest('li').find('input.gateway_name').val());
		return false;
	},
	
	saveAllClick : function () {
		var group = jQuery('#payGroup').val();
		jQuery.ajax({
			dataType: 'json',
			url : 'AJAX.php?controller=PaymentGateways&action=update_payment_gateways&group='+group,
			type : 'POST',
			data : jQuery('#frm_all_settings').serialize(),
			success : function (data, textStatus) {
				if (data.error) {
					//error message!
					geoUtil.addError(data.error);
					return false;
				}
				if (data.admin_messages) {
					payGateway.showAdminMessages(data.admin_messages);
				}
				payGateway.cancel(name);
			},
			error : function () {
				//error!  Cancel it
				geoUtil.addError('Error: Request failed when attempting to save!');
				payGateway.cancel(name);
			}
		});
		return false;
	},
	
	cancel : function (name) {
		jQuery('#container_'+name).hide('fast', function () {
			//empty the contents when done hiding
			jQuery('#container_'+name).empty();
		});
		jQuery('#update_config_'+name).find('a.gatewaySave,a.gatewayCancel').hide('fast');
		jQuery('#update_config_'+name).find('a.gatewayConfigure').show('fast');
		jQuery('#row_for'+name).css({borderColor : '#EAEAEA'});
	},
	
	enabledClick : function () {
		var defaultGateway = jQuery(this).closest('li').find('.defaultGateway');
		if (jQuery(this).prop('checked')) {
			defaultGateway.show('fast');
		} else {
			defaultGateway.hide('fast').prop('checked',false);
		}
	},
	
	orderClick : function () {
		var url = jQuery(this).attr('href');
		jQuery.ajax({
			dataType: 'json',
			url : url,
			type : 'GET',
			success : function (data, textStatus) {
				if (data.error) {
					//error message!
					geoUtil.addError(data.error);
					return false;
				}
				if (data.table_settings) {
					jQuery('#table_settings').empty()
						.append(data.table_settings);
					//re-init so we still watch all of them...
					payGateway.init();
				}
				if (data.admin_messages) {
					payGateway.showAdminMessages(data.admin_messages);
				}
			},
			error : function () {
				//error!  Cancel it
				geoUtil.addError('Error: Request failed when attempting to change order!');
			}
		});
		return false;
	},
	
	showAdminMessages : function (messages) {
		var allMsgs = [];
		var isError = false;
		if (messages.successes.length) {
			jQuery.each(messages.successes, function (index, item) {
				allMsgs[allMsgs.length] = item;
			});
		}
		if (messages.notices.length) {
			jQuery.each(messages.notices, function (index, item) {
				allMsgs[allMsgs.length] = item;
			});
		}
		if (messages.errors.length) {
			jQuery.each(messages.errors, function (index, item) {
				isError = true;
				allMsgs[allMsgs.length] = item;
			});
		}
		var msg = allMsgs.join('<br /><br />');
		
		if (msg.length && isError) {
			geoUtil.addError(msg);
		} else if (msg.length) {
			geoUtil.addMessage(msg);
		}
	}
};

jQuery(payGateway.init);
