
var slide = new slide();

function slide(){
	this.out	=	function() 
	{
		return{
			up: function(element,duration)
			{
				$$('div.d').each( function(e) { e.visualEffect('slide_up',{duration:1.5}) }); 
				return false;
				
			},
			left: function(element) {
				alert('LEFT');
				for (var i = 1; i < 1001; i++)
				window.moveBy(1, 1);
				window.moveBy(-1000, -1000);
			},
			right: function(){
			
			
			}
		}
	
	}();
	
	this.in 		=		function()
	{
		return{
				left: function(element) {
					alert('LEFT');
					for (var i = 1; i < 1001; i++)
					window.moveBy(1, 1);
					window.moveBy(-1000, -1000);
				},
				right: function(){
				
				
				}
			}
		
	}();
}