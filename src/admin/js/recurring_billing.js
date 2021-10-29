// 6.0.7-3-gce41f93
//Recurring container, hold methods dealing with recurring stuff
var recurring = {
	statusRefresh : function (id) {
		
		if (!id && $('recurringId')) {
			id = $('recurringId').getValue();
		}
		if (!id) {
			alert('Error: could not find ID!');
			return;
		}
		
		new Ajax.Request('AJAX.php?controller=Recurring&action=refresh&id='+id, {
			method: 'get',
			onSuccess: recurring.statusRefreshResponse
		});
	},
	cancelUrlExtra : '',
	
	statusCancel : function (id) {
		
		if (!id && $('recurringId')) {
			id = $('recurringId').getValue();
		}
		if (!id) {
			alert('Error: could not find ID!');
			return;
		}
		
		if (!confirm('Are you sure you want to cancel this recurring billing, stopping all future payments?')) {
			return;
		}
		
		new Ajax.Request('AJAX.php?controller=Recurring&action=cancel&id='+id+recurring.cancelUrlExtra, {
			method: 'get',
			onSuccess: recurring.statusCancelResponse
		});
		//reset cancel URL extra
		recurring.cancelUrlExtra = '';
	},
	
	noWork : function () {
		geoUtil.addError('Not yet functional.');
	},
	
	applySelectedChanges : function (action) {
		if (action) {
			action.stop();
		}
		
		var theaction = $('batch_status').getValue();
		
		if (!theaction || theaction == '--Choose--') {
			geoUtil.addError('Please choose an action to apply to the selected recurring billing(s).');
			return;
		}
		if (theaction == 'cancel' && !confirm('Are you sure you want to cancel the selected recurring billings?')) {
			return;
		}
		
		var batchForm = $('batchForm');
		batchForm.request ({
			onSuccess: recurring.batchResponse
		});
	},
	
	statusRefreshResponse : function (transport) {
		var data = transport.responseJSON;
		if (!data) {
			geoUtil.addError('problem with data.  transport: '+transport);
			return;
		}
		recurring.commonResponse(data, true);
	},
	
	statusCancelResponse : function (transport) {
		var data = transport.responseJSON;
		if (!data) {
			geoUtil.addError('problem with data.  transport: '+transport);
			return;
		}
		recurring.commonResponse(data, true);
	},
	
	batchResponse : function (transport) {
		var dataAll = transport.responseJSON;
		if (!dataAll) {
			geoUtil.addError('problem with data.  transport: '+transport);
			return;
		}
		if (dataAll.error) {
			geoUtil.addError(dataAll.error);
			return;
		}
		dataAll.recurrings.each(function (data) {
			recurring.commonResponse(data, false);
		});
		geoUtil.addMessage('Batch action applied.');
	},
	
	commonResponse : function (data, showMessage) {
		if (data.error) {
			geoUtil.addError(data.error);
			return;
		}
		if (data.status) {
			var statusId = 'statusValue';
			if ($(statusId+data.id)) {
				statusId = statusId+data.id;
			}
			if ($(statusId)) {
				//update status
				$(statusId).update(data.status);
			}
		}
		
		if (data.paidUntil) {
			var paidId = 'paidUntilValue';
			if ($(paidId+data.id)) {
				paidId = paidId+data.id;
			}
			if ($(paidId)) {			
				//update paid until
				$(paidId).update(data.paidUntil);
			}
		}
		if (showMessage) {
			if (data.action == 'refresh') {			
				geoUtil.addMessage ('Status checked with payment gateway, status is '+data.status+data.extraInfo);
			} else if (data.action == 'cancel') {
				geoUtil.addMessage ('Settings Saved, the recurring billing has been canceled and no further auto payments should be charged.');
			} else if (data.action == 'batch') {
				//err actually the action won't be batch...
				
			} else {
				//what were we doing again?
				alert('Finished.');
			}
		}
	}
};
