//7.5.3-36-gea36ae7
var opts = new Array(4);
var initList = function ()
{
	opts[0]	= '`geodesic_userdata`.username'; 	// sorting style;
	opts[1] = 'ASC';		// sorting style
	opts[2] = 25; 			// number to show;
	opts[3]	= 1;			// page;

	// Loading Image
	Ajax.Responders.register({
		onCreate: function() {
			$('result_body').innerHTML = "<tr>\n<td valign=center align=center colspan=100%><img src='admin_images/loading.gif'><br />Loading...</td>\n</tr>";
			Ajax.activeRequestCount++;
		},
		onComplete: function() {
	  		Ajax.activeRequestCount--;
		}
	});
	
	// Get the default table
	geo_ajaxTable( 'result' );
};

Event.observe(window,'load',initList);

function geo_ajaxTable( table )
{
		
	data = getData();
	new Ajax.Updater( $(table+'_body'), 'AJAX.php',{
			method: 'get',
			onSuccess: function(){ 
				new Ajax.Updater( $(table+'_footer'), 'AJAX.php',{
					method: 'get',
					parameters: {
						controller: 'ListUsers',
						action: 'getNumUsers',
						data: data
					}});
				new Ajax.Updater( $(table+'_pagination'), 'AJAX.php',{
					method: 'get',
					parameters: {
						controller: 'ListUsers',
						action: 'getPages',
						data: data
					}});
			},
			parameters: {
				controller: 'ListUsers',
				action: 'getUsers',
				data: data
			}});
}

function geo_sortTable( theOrder, table, column )
{
	// What table does the item come from
	switch(theOrder)
	{
		case 'status': 					table_prefix = '`geodesic_logins`'; break;
		case 'price_plan_id':			table_prefix = '`geodesic_user_groups_price_plans`'; break;
 		case 'auction_price_plan_id': 	table_prefix = '`geodesic_user_groups_price_plans`'; break;
 		default:						table_prefix = '`geodesic_userdata`';
 	}
 	
 	// What direction are we looking
	if( table_prefix+'.'+theOrder == opts[0] ) {
		if( opts[1] == 'ASC' ) {
			setData(1,'DESC');
			arrow = 'down.gif';
		} else {
			setData(1,'ASC');
			arrow = 'up.gif';
		}
	} else {
		setData( 3,1 ); // set page to 1
		setData(1,'ASC');
		arrow = 'up.gif';
	}
	
	// Set what we are looking for
	setData( 0,table_prefix+'.'+theOrder );	
	
	// Remove other arrows
	old_arrow = $('dir_arrow');
	
	$(old_arrow.parentNode.id).removeClassName('sorting_col');
	old_arrow.parentNode.removeChild(old_arrow);

	
	// Add Directional Arrow
	column = $(theOrder);
	new_arrow = document.createElement('img');
	new_arrow.src = 'admin_images/admin_arrow_'+arrow;
	new_arrow.id = 'dir_arrow';
	column.appendChild( new_arrow );
	$(theOrder).addClassName('sorting_col'); 
	
	// Get the data
	geo_ajaxTable( 'result' );
}

function geo_getPage( page ){ setData(3,page); geo_ajaxTable('result'); }
function geo_setUsers(numUsers) { setData(2,numUsers); setData(3,1); geo_ajaxTable('result'); }

function setData( item, data ){	opts[item] = data; }
function getData(){ return Object.toJSON(opts); }

	