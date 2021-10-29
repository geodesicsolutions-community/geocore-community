var plugins = new plugins();

function plugins() {
	this.plugins = [];
	this.params = [];
	this.hook		=		function(buffer) {
		var f = buffer.f('method');
		var base = buffer.vars('base')+'/plugins/'+f+'.js';
		
		var s = buffer.xml('seconds');
		var params = '<params>'+buffer.array('params')+'</params>';
		params = params.append('ctype','extension_child');
		if(!base.loaded()){
			try {
				plugins[f] = CJAX.script.load(base,f,params);
			}
			catch(e){ alert('Plugin: unabled to load function: '+ f + '() '+e); }
		} else {
			CJAX.load.action(f,params,s);
		}
	}
	
}