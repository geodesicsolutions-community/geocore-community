
Event.observe(window,'load',function() {initSideMenu();});

var currentCategory = [null];
var maxCategoryWidth = 0;
var initSideMenuDepth = 0;

//buttons used for min/expand
var minimizedMenuIcon = new Element('img',{src: 'admin_images/design/menu_expand.gif', alt: 'Click to Expand', title: 'Click to Expand'})
	.setStyle({cursor: 'pointer', margin: '5px 2px'})
	.observe('click',function() {toggleExpandMenu();});
var maximizedMenuIcon = new Element('img',{src: 'admin_images/design/menu_collapse.gif', alt: 'Click to Minimize', title: 'Click to Minimize'})
	.setStyle({cursor: 'pointer', margin: '5px 2px'})
	.observe('click',function() {toggleExpandMenu();});

//buttons used for dock/undock
var dockedMenuIcon = new Element('img',{src: 'admin_images/design/fullscreen_maximize.gif', alt: 'Click to Un-Dock', title: 'Click to Un-Dock'})
	.setStyle({cursor: 'pointer', margin: '5px 2px'})
	.observe('click',function() {toggleDockMenu();});
var unDockedMenuIcon = new Element('img',{src: 'admin_images/design/fullscreen_minimize.gif', alt: 'Click to Dock', title: 'Click to Dock'})
	.setStyle({cursor: 'pointer', margin: '5px 2px'})
	.observe('click',function() {toggleDockMenu();});

function initSideMenu ()
{
	elem = $('category_home');
	if (!elem) {
		//side menu does not exist
		return;
	}
	//attach the buttons
	$('menuControlButtons').insert(maximizedMenuIcon)
		.insert(dockedMenuIcon);
	
	//run through categories recursively
	initSideMenuSub(elem);
	
	//set the legend to not have cursor pointer
	$('category_home').setStyle({cursor: 'default'});
	
	//after they are all hidden/shown, slide out the menu
	
	//new Effect.Grow($('menuTop'),{duration: defaultDuration});
	
	var docked = getCookie('sideMenuDocked');
	var sideMenu = $('sideMenu');
	var tempSpeed = defaultDuration;
	defaultDuration = 0;
	if (docked == 'undocked') {
		var x = getCookie('sideMenuX');
		var y = getCookie('sideMenuY');
		//un-dock it
		toggleDockMenu();
		
		sideMenu.setStyle({left: x+'px', top: y+'px'});
		dropSideMenu();
	}
	
	$('menuTop').show();
	
	var collapsed = getCookie('sideMenuCollapse');
	if (collapsed == 'min') {
		//collapse the mofo too, have to do it after it is shown or weird things happen
		sideMenuDockAnimation = 0;
		toggleExpandMenu();
	}
	
	defaultDuration = tempSpeed;
	
	gjUtil.lightbox.onOpen(function () {
		//changes the z-index so it is behind the overlay
		jQuery('.sideMenu').addClass('side_menu_low');
	});
	gjUtil.lightbox.onClose(function () {
		//changes the z-index so it is back to normal
		jQuery('.sideMenu').removeClass('side_menu_low');
	});
}
function initSideMenuSub (elem)
{
	var elemContents = $(elem.identify()+'_contents');
	
	//ok we assume we are starting shown...  so if this is not a current directory, then hide ourself
	if (!elem.hasClassName('menu_category_current')) {
		if (!elem.hasClassName('menu_category') || !elemContents) {
			//this is a page link, just go to the next whatever if there is one
			if (elem.next()) {
				initSideMenuSub(elem.next());
			}
			return;
		}
		//not the current one, so hide contents
		elemContents.hide();
	} else {
		currentCategory[getCatLevel(elemContents)] = elemContents;
	}
	Event.observe(elem,'click',menuClick);
	
	//recursivelywalk the menu, setting everything to hidden or shown that needs it.
	//first walk ourself all the way down into the contents of this category
	if (elemContents.down()) {
		//if there is one object below this one and it is a category, then set the elem to it
		//(or if the first item is the home link)
		initSideMenuSub(elemContents.down());
	}
	//next, walk sideways to all the categories at the same level as this
	if (elem.next() && elem.next().next() && (elem.next().next().hasClassName('menu_category') || elem.next().next().hasClassName('menu_category_current'))){
		initSideMenuSub(elem.next().next());
	}
}

var doingEffectOnCat = 0;

function menuClick (event)
{
	//keep from doing more than 1 category open/close animation at once, to avoid
	//weird things from happening
	if (doingEffectOnCat) return;
	var elem = $(Event.element(event));
	if (elem.identify() == 'category_home') {
		return;
	}
	doingEffectOnCat = 1;
	var timeout = (defaultDuration * 1000) + 5;
	setTimeout('doingEffectOnCat = 0;',timeout);
	
	
	
	var elemContents = elem.next();
	if (elemContents && elemContents.hasClassName('menu_category_contents')) {
		//it's a good click
		
		if (elemContents.visible()){
			//hide it
			closeCat(elemContents);
		} else {
			//show it
			openCat(elemContents);
		}
	}
}

function closeCat (elem, ignoreEffect)
{
	new Effect.toggle (elem, 'blind', {duration: defaultDuration});
	var level = getCatLevel(elem);
	if (currentCategory[level]) {
		for (var index = level, len = currentCategory.length; index < len; ++index) {
			currentCategory[index] = null;
		}
	}
}
function openCat (elem)
{
	new Effect.toggle (elem, 'blind', {duration: defaultDuration});
	
	var level = getCatLevel(elem);
	var realLevel = level;
	
	if (!currentCategory[level] && (level == 1 || (currentCategory[(level - 1)] && currentCategory[(level - 1)].identify() == elem.up().identify()))) {
		//opening a new sub-category
		currentCategory[level] = elem;
	}
	
	if (!(currentCategory[level] && currentCategory[level].up() && elem.up() && elem.up().identify() == currentCategory[level].up().identify())) {
		//whatever is being opened is a different main tree than what is currently opened
		level = 1;
	}
	if (currentCategory[level] && currentCategory[level].identify() != elem.identify()) {
		closeCat (currentCategory[level]);
	}
	
	//now set the current cat level
	currentCategory[realLevel] = elem;
}
function getCatLevel(elem)
{
	if (elem.identify() == 'category_home_contents') {
		//this is the top level...
		return 0;
	}
	var level = 1;
	while (elem.identify() != 'category_home_contents' && elem.up() && elem.up().identify() != 'category_home_contents' && elem.up().identify() != 'body') {
		elem = elem.up();
		level++;
	}
	return level;
}

var sideMenuDocked=1;
var sideMenuMoveEvent = null;

var sideMenuDockAnimation = 0;
function toggleDockMenu()
{
	if (sideMenuDockAnimation) return;
	
	var timeout = defaultDuration * 1000;
	var sideMenu = $('sideMenu');
	var legend = $('category_home');
	var bodyHtml = $('bodyHtml');
	var buttons = $('menuControlButtons');
	
	if (sideMenuDocked) {
		sideMenuDockAnimation = 1;
		//side menu is docked, so un-dock it
		sideMenuDocked = 0;
		
		//make it movable
		sideMenuMoveEvent = new Draggable(sideMenu,{
			zindex: 2000,
			handle: legend,
			onEnd: function() { dropSideMenu();}
		});
		//drop it right now to save the position
		dropSideMenu();
		
		//make the main body big
		new Effect.Morph(bodyHtml, {
			style: "left: 0px;",
			duration: defaultDuration
		});
		
		sideMenu.addClassName('sideMenuUndocked');
		
		//set legend icon to be movable
		legend.setStyle({cursor: 'move'});
		var dockedButton = unDockedMenuIcon;
		
		document.cookie = 'sideMenuDocked=undocked';
	} else {
		//side menu is un-docked so dock it
		sideMenuDocked = 1;
		
		//remove ability to move around
		sideMenuMoveEvent.destroy();
		
		//and last, if it's shrunken, un-shrink it
		var elem = $('category_home_contents');
		if (!elem.visible()) {
			toggleExpandMenu();
		}
		sideMenuDockAnimation = 1;
		//move it back to normal location
		new Effect.Morph(sideMenu, {
			style: 'top: 95px;'+
				'left: 5px;',
			duration: defaultDuration
		});
		
		//make the main body smaller
		new Effect.Morph(bodyHtml, {
			style: "left: 210px;",
			duration: defaultDuration
		});
		sideMenu.removeClassName('sideMenuUndocked');
		
		//and set legend to not be movable
		legend.setStyle({cursor: 'default'});
		var dockedButton = dockedMenuIcon;
		document.cookie = 'sideMenuDocked=docked';
	}
	//update the icons
	//Directly after a dock/undock, the menu will ALWAYS be maximized.
	var minButton = maximizedMenuIcon;
	$('menuControlButtons').update(minButton)
		.insert(dockedButton);
	
	setTimeout('sideMenuDockAnimation = 0;',timeout);
}

function toggleExpandMenu()
{
	if (sideMenuDockAnimation) return;
	
	var timeout = defaultDuration * 1000;
	var elem = $('category_home_contents');
	var minimized = (elem.visible())? 1: 0;
	if (elem.visible()) {
		document.cookie = 'sideMenuCollapse=min';
		if (sideMenuDocked) {
			//also un-dock
			toggleDockMenu();
		}
	} else {
		document.cookie = 'sideMenuCollapse=max';
	}
	
	sideMenuDockAnimation = 1;
	
	new Effect.toggle (elem, 'blind', {duration: defaultDuration});
	
	setTimeout('fixSideMenuFieldset();',timeout);
	
	//change the icon buttons
	var dockedButton = (sideMenuDocked)? dockedMenuIcon: unDockedMenuIcon;
	var minButton = (minimized)? minimizedMenuIcon: maximizedMenuIcon;
	
	$('menuControlButtons').update(minButton)
		.insert(dockedButton);
}

function fixSideMenuFieldset()
{
	//figure out what the height is:
	$('sideMenu').setStyle({height: 'auto'});
	sideMenuDockAnimation = 0;
	
}

function dropSideMenu()
{
	var sideMenu = $('sideMenu');
	var location = sideMenu.positionedOffset();
	var x = ((location.left < 0 )? 0: location.left);
	var y = ((location.top < 0 )? 0: location.top);
	
	if (x != location.left || y != location.top) {
		sideMenu.setStyle({left: x+'px', top: y+'px'});
	}
	
	document.cookie = 'sideMenuX='+x;
	document.cookie = 'sideMenuY='+y;
}
