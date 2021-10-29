function security_image_loader()
{
	toggleImageType();
	
	$('imageType_system').observe('click',toggleImageType);
	$('imageType_recaptcha').observe('click',toggleImageType);
	
	
	return true;
}

var toggleImageType = function () {
	$$('.built_in_images').invoke(($('imageType_system').checked)? 'show':'hide');
	$$('.reCAPTCHA_images').invoke(($('imageType_recaptcha').checked)? 'show':'hide');
};


function advFormDefault()
{
	$('allowedChars').value = '2346789ABCDEFGHJKLMNPRTWXYZ';
	$('lines').value = '3';
	$('numGrid').value = '8';
	$('numNoise').value = '150';
	$('distort').value = '.5';
	$('refreshUrl').value = 'addons/security_image/reload.gif';
	
	$('rmin').value = $('gmin').value = $('bmin').value = 0;
	$('rmax').value = $('gmax').value = $('bmax').value = 150;
}

var imageUrl = null;

function changeSecurityImage()
{
	var a = new Date();
	var load_image = new Image();
	var new_image = new Image();
	load_image.src = '../addons/security_image/loader.gif';
	var x = $('addon_security_image').offsetWidth + 'px';
	var y = $('addon_security_image').offsetHeight + 'px';

	$('addon_security_image').style.width = x;
	$('addon_security_image').style.height = y;

	secure_image = $('addon_security_image').getElementsByTagName('img')[0];
	if (imageUrl == null) {
		imageUrl = secure_image.src;
	}
	secure_image.src = load_image.src;
	
	new_image.src = imageUrl+'&time='+a.getTime();

	Event.observe(new_image, 'load', function(){
		setTimeout(	function(){
			secure_image.src = new_image.src;
			} , 250);
	});		
}

Event.observe (window,'load',security_image_loader);
