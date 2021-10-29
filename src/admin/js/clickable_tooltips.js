var tipbox;
var tipboxToolbar;
var tipbox_large;
var tipbox_largeToolbar;
var stickit;

function initTooBars() {
	//have to re-init every time, cuz of something weird in IE?
	//tipboxToolbar = $('tipboxToolbar');
	tipboxToolbar = new Element('div',{id: 'tipboxToolbar'});
	tipboxToolbar.setStyle({
		fontSize: '100%',
		color: 'blue'
	});
	tipboxToolbar.insert (' (click for full details)');
	
	
	tipbox_largeToolbar = new Element('div',{id: 'tipbox_largeToolbar'});
	tipbox_largeToolbar.setStyle({
		backgroundColor: '#006699',
		color: 'white',
		fontWeight: 'bold',
		textAlign: 'right'
	});
	tipbox_largeToolbar.insert ('Addon Details &nbsp; ');
	var closeButton = new Element('span');
	closeButton.setStyle({
		color: 'red',
		cursor: 'pointer'
	}).update('X &nbsp;');
	closeButton.observe('click', function (event) {toggleStickIt(event);});
	tipbox_largeToolbar.insert(closeButton);
}

function initTooltips() {
	tipbox = $('tipbox');
	if (!tipbox) {
		//create tip box
		tipbox = new Element('div', {id: 'tipbox'});
		tipbox.setStyle({
			width:'300px',
			height:'150px',
			overflow:'hidden',
			fontSize: '80%',
			border: '3px solid #DDD',
			backgroundColor:'#F3F3F3',
			padding: '5px',
			opacity: 0.9
		});
		var parent = $('tooltip_fun_addons').up().up();
		parent.insert(tipbox);
	}
	tipbox_large = $('tipbox_large');
	if (!tipbox_large) {
		tipbox_large = new Element('div',{id: 'tipbox_large'});
		tipbox_large.setStyle({
			width:'650px',
			height:'350px',
			overflow:'auto',
			border: '3px solid #000000',
			backgroundColor:'#F3F3F3',
			opacity: 0.9,
			padding: '2px',
			fontSize: '120%'
		});
		parent.insert(tipbox_large);
	}
	
	tipbox.absolutize().hide();
	tipbox_large.absolutize().hide();
	
	stickit = 0;
	img = document.getElementsByTagName('td');
	for(i = 0; i < img.length; i++) {
		if(img[i].getAttribute('tooltip')) {
			img[i].onmouseover = showTooltip;
			img[i].onmousemove = updateTooltip;
			img[i].onmouseout = hideTooltip;
			img[i].onclick = toggleStickIt;
		}
	}
}

function toggleStickIt(e) {
	//hehe, make it so if they click, it locks the tooltip...
	stickit = stickit ? 0 : 1;
	makeBig(e);
	updateTooltip(e);
}
function showTooltip(e) {
	if (stickit) return 0;
	//make sure large tipbox is gone.
	tipbox_large.hide();
	if(!e) e = event;
	img = e.target ? e.target : e.srcElement;
	caption = img.getAttribute('tooltip');
	if (!caption) caption = img.parentNode.getAttribute('tooltip');
	
	//var smallerCap = '<span style=\"color:blue; font-size:150%;\"> ( click for full details )</span><br />'+caption;
	initTooBars();
	tipbox.update().insert(tipboxToolbar).insert(caption);
	updateTooltip(e);
}
function makeBig(e) {
	if (!stickit) {
		tipbox_large.hide();
		return 0;
	}
	if(!e) e = event;
	img = e.target ? e.target : e.srcElement;
	caption = img.getAttribute('tooltip');
	if (!caption) caption = img.parentNode.getAttribute('tooltip');
	
	initTooBars();
	tipbox_large.update(tipbox_largeToolbar).insert(caption);
	
	updateTooltip_large(e);
	tipbox_large.show();
	tipbox.hide();
	
}
function updateTooltip_large(e) {
	if (!stickit) return 0;
	if(!e) e = event;
	//offsetXL = 200;
	offsetYL = -20;
	if (! e.pageY) {
		//add scroll offset
		offsetYL += getScrollY();
		//offsetXL += getScrollX();
	}
	var ttop = e.pageY ? (e.pageY+offsetYL)+'px' : (e.clientY+offsetYL)+'px';
	var tleft = '300px';
	tipbox_large.setStyle({top: ttop, left: tleft});
	//tipbox_large.style.top = e.pageY ? (e.pageY+offsetYL)+'px' : (e.clientY+offsetYL)+'px';
	//tipbox_large.style.left = '200px';
}
function updateTooltip(e) {
	if (stickit) return 0;
	if(!e) e = event;
	offsetX = 15;
	offsetY = 0;
	if (! e.pageY) {
		//add scroll offset
		offsetY += getScrollY();
		offsetX += getScrollX();
	}
	tipbox.style.top = e.pageY ? (e.pageY+offsetY)+'px' : (e.clientY+offsetY)+'px';
	tipbox.style.left = e.pageX ? (e.pageX+offsetX)+'px' : (e.clientX+offsetX)+'px';
	tipbox.show();
}
function getScrollY() {
  var scrOfY = 0;
  if( typeof( window.pageYOffset ) == 'number' ) {
    //Netscape compliant
    scrOfY = window.pageYOffset;
  } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
    //DOM compliant
    scrOfY = document.body.scrollTop;
  } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
    //IE6 standards compliant mode
    scrOfY = document.documentElement.scrollTop;
  }
  return scrOfY;
}
function getScrollX() {
  var scrOfX = 0;
  if( typeof( window.pageYOffset ) == 'number' ) {
    //Netscape compliant
    scrOfX = window.pageXOffset;
  } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
    //DOM compliant
    scrOfX = document.body.scrollLeft;
  } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
    //IE6 standards compliant mode
    scrOfX = document.documentElement.scrollLeft;
  }
  return scrOfX;
}
function hideTooltip() {
	if (stickit) return 0;
	tipbox.hide();
}

window.onload = initTooltips;