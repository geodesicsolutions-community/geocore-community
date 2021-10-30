


var CP = new CJAX_PLUGINS();
CP.hook();

function CJAX_PLUGINS(){
	this.PLUGINS = [];
	this.params = [];
	this.dir;
	this.hook		=		function(buffer)
	{
		if(!buffer) buffer = EXTENSION.BUFFER['plugins'];
		CP.dir = CJAX.vars['basepath']+'/plugins/';
		var f = CJAX.xml('constructor',buffer);
		var route = CJAX.xml('route',buffer);
		if(route =='long') CP.dir += f+'/';
		var path = CP.dir+f+'.js';
		var s = CJAX.xml('seconds',buffer);
		var params = CJAX.xml('params',buffer);
		var array = CJAX.xml('param',buffer,true);
		this.params[f] = array;
		var _function = CJAX.xml('contructor',buffer);
		if(!CJAX.script.loaded(path)){
			try{
					var r = CJAX.script.load(path,f,params);
					CP.PLUGINS[f] = 1;
			}
			catch(e){ alert('unabled to load function: '+ f.toUpperCase()+ ' '+e); }
		} else {
			CJAX.load.action(f,params,s);
			
		}
	}
	
	this.params 		=		function(tag,data)
	{
		var param = CJAX.xml(tag,data);
		if(param) return CJAX.decode(param);
		return '';
	}
		
}