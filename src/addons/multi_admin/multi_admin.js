
function updateChecks(thisCheck, skipSame) {
	if (!thisCheck) return;
	thisCheck = $(thisCheck);
	if (!skipSame) {
		//make sure all pages named the same thing are also checked/un-checked
		var these = document.getElementsByName(thisCheck.name);
		for (var l=0; l < these.length && these.length > 1; l++){
			these[l].checked = thisCheck.checked;
		}
	}
	if (!thisCheck.hasClassName('displayBox')) {
		//checkbox is an update box, don't do the rest
		return;
	}
	//Do stuff to the update version.
	var those = document.getElementsByName(thisCheck.name.replace('display[','update['));
	for (var m = 0; m < those.length; m++){
		var elem = $(those[m]);
		if (!elem.hasClassName('updateBox')){
			return;
		}
		if (thisCheck.checked){
			//display is checked, so un-disable the update
			elem.disabled = false;
		} else {
			//display is un-checked so un-check and disable the update
			elem.disabled = true;
			elem.checked = false;
		}
	}
}

function initMultiAdminPermissions(){
	var displays = $$('.displayBox');
	for (var i = 0; i < displays.length; i++){
		updateChecks(displays[i]);
	}
}
function checkAll(elem){
	var elem = $(elem)
	var checked = elem.checked;
	if (elem.hasClassName('displayBox')){
		var all = $$('.displayBox');
	} else {
		var all = $$('.updateBox');
	}
	for (var i=0; i<all.length; i++){
		if (!all[i].disabled) {
			all[i].checked = checked;
			updateChecks(all[i],1);
		}
	}
}

Event.observe(window,'load',function () { initMultiAdminPermissions () ;});
