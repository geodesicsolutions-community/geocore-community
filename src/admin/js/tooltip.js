
var gjTooltip = {
	boxStyle: {border: '1px solid #FF4500', width: '300px', 'background-color': '#FFF1EC', color: 'inherit', position: 'absolute', 'z-index': 100000},
	titleStyle : {padding: '2px', 'font-weight': 'bold', 'background-color': '#FF4500', color: '#fff',cursor:'move'},
	textStyle : {padding: '3px'},
	tipBox : null,
	tipTitle: null,
	tipText: null,

	sticky : false,

	init : function () {
		if (gjTooltip.tipBox) {
			//already initialized..
			return;
		}
		gjTooltip.tipBox = jQuery('<div id="tipbox___"/>').css(gjTooltip.boxStyle);

		gjTooltip.tipTitle = jQuery('<div id="tip_title"/>').css(gjTooltip.titleStyle);

		gjTooltip.tipText = jQuery('<div id="tip_text"/>').css(gjTooltip.textStyle);


		gjTooltip.tipBox.prepend(gjTooltip.tipTitle, gjTooltip.tipText);

		jQuery('body').prepend(gjTooltip.tipBox);
		gjTooltip.tipBox.hide();
	},

	activate : function (elem, title, text) {
		gjTooltip.init();

		if (gjTooltip.sticky) {
			return;
		}
		gjTooltip.tipTitle.html(title);
		gjTooltip.tipText.html(text);

		var dim = jQuery(elem).offset();
		gjTooltip.tipBox.show('fast').css({
			left: (dim.left+25)+'px',
			top: (dim.top+15)+'px'
		});
	},

	deactivate : function () {
		if (gjTooltip.sticky) {
			//don't do nothin
			return;
		}
		gjTooltip.init();
		gjTooltip.tipBox.hide('fast');
	},

	hoverShow : function () {
		var next = jQuery(this).next().html(),
			title='', text='';
		if (''+parseInt(next)==next) {
			next=parseInt(next);
			title = Text[next][0];
			text=Text[next][1];
		} else {
			title = next;
			text = jQuery(this).next().next().html();
		}
		gjTooltip.activate(this,title,text);
	},

	toggle_sticky : function () {
		gjTooltip.sticky = !gjTooltip.sticky;

		//if we just made this sticky, make it bigger and add an X
		if(gjTooltip.sticky) {
			gjTooltip.tipBox.prepend('<div id="stickyTooltipCloseButton" onclick="gjTooltip.toggle_sticky();"><i class="fa fa-remove" style="font-size: 24px;"> </i></div>')
				.width(400);
			gjTooltip.tipTitle.html(jQuery('#tip_title').html().replace("(click to enlarge)", ""))
				.css('fontSize','12pt');

			//make it moveable
			gjTooltip.tipBox.draggable({
				handle : gjTooltip.tipTitle,
				//make it slightly see-through when dragging, for fun and profit
				opacity : 0.75
			});

			gjTooltip.tipText.css('fontSize','12pt');
		} else {
			gjTooltip.deactivate();
			//reset
			gjTooltip.tipBox.remove();
			gjTooltip.tipBox = gjTooltip.tipTitle = gjTooltip.tipText = null;
			//now next time something is done, it will reset everything
		}
	}
};

jQuery(document).ready(function () {
	jQuery('.tooltip').hover(gjTooltip.hoverShow,gjTooltip.deactivate)
		.click(gjTooltip.toggle_sticky).css({cursor: 'pointer'});
});

/**
 * EVERYTHING below here is here for LEGACY only, if we ever get around to killing
 * all the "old ways" of doing the tooltips we can finally get rid of this.
 */

function stm (elem, t, s) {
	//ignore s, we'll deal with it later

	gjTooltip.activate(elem, t[0], t[1]);
};

var tooltipInit = false;
function mig_hide () {
	gjTooltip.deactivate();
}
var toggle_sticky = gjTooltip.toggle_sticky;


var Style = new Array();
Style[1]=["white","#7BC342","","","",,"#3c3c3c","#F5FBF0","","","",,,,2,"#70B339",2,,,,,"",3,,,];

//define Text for ie7
var Text = [];

