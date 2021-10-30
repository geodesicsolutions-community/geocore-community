
var extension = new extension();

function extension() {
	this.extensions = [];
	this.buffer = [];
	this.hook		=		function(buffer)
	{
		this.buffer[e] = buffer;
		var e = buffer.xml('extension');
		
		var s = buffer.xml('seconds');
		var base = buffer.xml('base').replace('__base__',CJAX.vars['base'])
		base += e+'.js';
		
		if(base.loaded()) {
			var method = buffer.xml('method');
			CJAX.script.action(e+'.'+method,buffer,s);
		} else {
			extension.load(base,e,buffer);	
		}
	}
	
	
	this.load		=	function(base,e,buffer)
	{
		return CJAX.script.load(base,e,buffer);
	}
	
	this.loaded		=	function(path) {
		return CJAX.script.loaded(path);
	}

}