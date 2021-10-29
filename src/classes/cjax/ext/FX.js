var FX = new CJAX_FX();
FX.hook();

function CJAX_FX(){
	this.FXS = [];
	this.params = [];
	
	
	this.hook		=		function()
	{
		var buffer = EXTENSION.BUFFER['FX'];
		var hook = CJAX.xml('hook',buffer)
		var path = CJAX.xml('path',buffer).replace('__base__ext',CJAX.vars['basepath']+'');
		var path = path+'FX/';
		var f = CJAX.xml('constructor',buffer);
		var route = CJAX.xml('route',buffer);
		if(route =='long') FX.dir += f+'/';
		var path = path+f+'.js';
		var s = CJAX.xml('seconds',buffer);
		var params = CJAX.xml('params',buffer);
		var array = CJAX.xml('param',buffer,true);
		FX.params[f] = array;
		
		if(!CJAX.script.loaded(path)){
			try{
				CJAX.script.load(path,f,params);
				FX.FXS[f] = 1;
			}
			catch(e){ alert('-| unabled to load function: '+ f.toUpperCase()+ ' '+e); }
		} else {
			CJAX.script.action(f,params,s);
		}
	
	}
}