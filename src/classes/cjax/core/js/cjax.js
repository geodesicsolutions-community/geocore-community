/**############################################**
#	CJAX FRAMEWORK 2.1					#
#	ajax made easy with cjax					#
#	File: cjax.js								#
#	-- DO NOT REMOVE THIS -- 					#
#	-- AUTHOR COPYRIGHT MUST REMAIN INTACT --	#
#	CJAX FRAMEWORK	2.1					#	
#	Written by: Carlos Galindo					#
#	Website: www.cjax.net							#		
#	Email: @email@								#
#	Date: 9/21/2007								#
#	Last Updated:  05/22/2008					#
**#############################################*/

var CJAX = new CJAX_FRAMEWORK();	
	CJAX.initiate();
	
function CJAX_FRAMEWORK() {
	this.name		=	'cjax';
	this.debug = false;
	this.load_mode;
	var CJAX_CACHE = [];
	var _FUNCTION;
	var __file__;
	var __base__;
	var __ext__;
	var parameters;
	this.COMPONENTS = [];
	this.EXTENSIONS = [];
	this.vars = [];
	
	this.onStartEvents		=		function() {
		__base__ = CJAX.vars['base'] = CJAX.get.basepath();
		CJAX.vars['loaded'] = [];
		__construct();
	}
	
	this.message = function( buffer ) {
		var div = CJAX.create.div('cjax_message');
		var data = CJAX.xml('data',buffer);
		data = CJAX.decode( data );
		var secs = CJAX.xml('secs',buffer);
		var action = CJAX.xml('action',buffer);
		div.style.position ='absolute';
		var ctop = (screen.height /4);
		//768
		div.style.top = CJAX.getY()+ctop+'px';
		div.style.left = '50%'; div.innerHTML = data;
		//div.style.width = div.offsetWidth+'px'; 
		div.style.marginLeft = '-'+(div.offsetWidth / 2)+'px';
		div.style.visibility = 'visible';
		if( secs ){setTimeout(function(){
			CJAX.is_element('cjax_message',false).innerHTML='';},secs*1000);
		}
	}
		
	this.remove_html 		=		function( data ) {
		var data = data.replace(/(<([^>]+)>)/ig,"");
		data = data.replace(/(&([^\s].*);)+/ig,""); 
		return data;
	}
	
	this.create		=		function() {
		return{
			script: function( path ) {
				if(!CJAX.script.loaded( path )) {
					return CJAX.script.load( path );
				}
			},
			div:function(id,parent,append) {
				if(typeof append == 'undefined') var append = true;
				var element = CJAX.is_element(id,false);
				if(!parent || parent == 'body') {
					parent = CJAX.elem_docs( 'body' )[0];
				} else {
					if( !parent ) parent = CJAX.is_element(parent,false);
				}
				if( !parent )return false;
				if(element && parent){	
					if( append ) {
						parent.appendChild( element );
					} else {
						CJAX.elem_docs( 'body' )[0].appendChild( element );
					}
					return element;
				}
				var div = document.createElement( 'div' );
				div.setAttribute('id',id);
				 
				if( append ) { 
					parent.appendChild( div );
				} else {
					CJAX.elem_docs( 'body' )[0].appendChild( div );
				}
				return div;
			},
			span:function(id,parent) {
				var element = CJAX.is_element( id );
				if(!parent || parent == 'body') parent = CJAX.elem_docs( 'body' );
				else parent = CJAX.is_element(parent,false);
				if( !parent )return false;
				if(element && parent)
				{
					parent.appendChild( element );
					return element;
				}
				var div = document.createElement( 'span' );
				div.setAttribute('id',id);
				parent.appendChild( div );
				return div;
			},
			textbox:function(id,parent,label) {
				//make sure the element doesnt exist before it tries to create it
				var elem = CJAX.elem_doc(id,false);
				if( elem ){return elem;}
				var parent = CJAX.elem_doc( parent );
				if( label ){
					var olabel = document.createElement( 'LABEL' );
					olabel.setAttribute('id','label_'+id); 
					olabel.setAttribute('for',id); 
					olabel.innerHTML = label;
					parent.appendChild( olabel ); 
				}
				var textbox = document.createElement( 'INPUT' );
				textbox.setAttribute('type','text');
				textbox.setAttribute('id',id);
				parent.appendChild( textbox ); 
				return textbox;
			},
			iframe:function(id,parent_object) {
				var iframe = document.createElement("iframe");
					iframe.name = id;
					iframe.id = id;
					if(parent_object) {
						var parent = CJAX.is_element(parent_object);
						parent.appendChild(iframe);
					}
					
					return iframe;
			},
			form:function(id,parent_object) {
				var frm = document.createElement("form");
					frm.id =  id;
					frm.name = id;
					
					if(parent_object) {
						var parent = CJAX.is_element(parent_object);
						parent.appendChild(frm);
					}
				return frm;
			}
	}
	}();
	
	this.click		=		function( buffer ) {
		var item = CJAX.xml('element',buffer)
		elem = CJAX.$( item ); 
		if( elem )elem.click();
	}
		
	this.textbox		=		function( buffer ) {
	  	var id = CJAX.xml('element',buffer);
	  	var parent = CJAX.xml('parent',buffer); 
	  	var label = CJAX.xml('label',buffer);
	  	var textbox = CJAX.create.textbox(id,parent,label);
	 	if( textbox ) {
		  var value = CJAX.xml('value',buffer);
		  var _class = CJAX.xml('class',buffer);  
	 	}
	}
	 
	this.get		=		function() {
		return {
			dirname : function (path,loops) {
				if (!path) return '';
				path = path.match( /(.*)[\/\\]/ )[1];
				if( loops ){
					for(var i = 0; i < loops-1; i++){
					try{
						path = path.match( /(.*)[\/\\]/ )[1];
						}
						catch( e ) {}
					}
				}
			    return path;
			 }
			,basepath : function () {
				var path = CJAX.get.script.self();
				path = CJAX.get.dirname(path,3);
				return path;
			}
			,scripts : {
				src : function () {
						var paths = [];
						var script;
						var scripts = CJAX.elem_docs( 'script' );
						for( var i = 0; i < scripts.length; i++ ){
							script = scripts[i];
							if(script.src) paths[i] = script.src;
						}
						return paths;
					}
			},
			script : {
				self: function () {
						var script;
						var scripts = CJAX.elem_docs( 'script' );
						for( var i = 0; i < scripts.length; i++ ){
							script = scripts[i];
							if(script.id=='cjax') return script.src;
						}
						return paths;
				}
			},
			value : function(elem,verbose) {
				var type = (typeof elem);
				if( typeof verbose == 'undefined') { verbose = true; }
				if( type.indexOf( 'object' ) == -1) {var elem = CJAX.$(elem,verbose);}
				return elem.value;
			},
			property : {
				readonly: function(elem,verbose) {
					var type = (typeof elem);
					if( typeof verbose == 'undefined') { verbose = true; }
					if( type.indexOf( 'object' ) == -1) {var elem = CJAX.$(elem,verbose);}
					return elem.readOnly;
				}
				,
				enabled: function(elem,verbose) {
					var type = (typeof elem);
					if( typeof verbose == 'undefined') { verbose = true; }
					if( type.indexOf( 'object' ) == -1) {var elem = CJAX.$(elem,verbose);}
					return (elem.disabled)? false : true;
				},
				disabled: function(elem,verbose) {
					var type = (typeof elem);
					if( typeof verbose == 'undefined') { verbose = true; }
					if( type.indexOf( 'object' ) == -1) {var elem = CJAX.$(elem,verbose);}
					return elem.disabled;
				},style : function(elem,verbose) {
					var type = (typeof elem);
					if( typeof verbose == 'undefined') { verbose = true; }
					if( type.indexOf( 'object' ) == -1) {var elem = CJAX.$(elem,verbose);}
					return elem.style;
				}
				, parent : function(elem,verbose) {
					var type = (typeof elem);
					if( typeof verbose == 'undefined') { verbose = true; }
					if( type.indexOf( 'object' ) == -1) {var elem = CJAX.$(elem,verbose);}
					return elem.parentNode;
				}, position : function(elem,verbose) {
					var type = (typeof elem);
					if( typeof verbose == 'undefined') { verbose = true; }
					if( type.indexOf( 'object' ) == -1) {var elem = CJAX.$(elem,verbose);}
					var pos = [];
					var  curleft = curtop = curright = curdown = 0;
					if ( elem.offsetParent ) {
						do {
								curleft += elem.offsetLeft;
								curtop += elem.offsetTop;
						} while (elem = elem.offsetParent);		
					
						
						
						pos['left'] = curleft;
						pos['top'] = curtop;
						return pos;
					}
				}
			}
			,
			properties : function(elem,verbose) {
				var type = (typeof elem);
				if( typeof verbose == 'undefined') { verbose = true; }
				if( type.indexOf( 'object' ) == -1) {var elem = CJAX.$(elem,verbose);}
				var p = [];
				p['id'] = elem.id;
				p['name'] = elem.name;
				p['readonly'] = elem.readOnly;
				p['disabled'] = CJAX.elements.disabled(elem,false);
				p['enabled'] = p['disabled']? false: true;
				p['value'] = elem.value;
				return p;
			},
			cache : function( key ) {
				return CJAX.cache.get( key );
			}
		}
	}();
	
	
	this.set				=			function() {
		return {
			value : function(elem,value,verbose){
				if( !elem ) return false;
				var elem = CJAX.is_element(elem,verbose);
				if( elem ) { elem.value = value; return true; }
				return false;
			}
			,type: function(elem,new_type,verbose){
				if( !elem ) return false;
				var elem = CJAX.is_element(elem,verbose);
				if( elem ) { elem.type = new_type; return true;}
				return false;
			}
			,name : function(elem,new_name,verbose){
				if( !elem ) return false;
				var elem = CJAX.is_element(elem,verbose);
				if( elem ) {elem.name = new_name; return true;}
				return false;
			},style : function(elem,new_name,verbose){
				/**
				*TODO
				**/
			}
			,property: {
					focus : 
					function(elem,verbose){
						if( !elem ) return false;
						var elem = CJAX.is_element(elem,verbose);
						if(elem && window.focus())
						{
							elem.focus();
							return true;
						}
						return false;
					}
			}
			,event : function(elem,event,f){
				if( !elem ) return false;
				var elem = CJAX.is_element( elem );
				CJAX.addEvent(elem,event, 
				(typeof eval( f ) === 'function')? f: function( f ){
					eval( f );
				}
				);
			}
		}
	}();
	

	
	this.initiate			=			function() {
		var cjax_css = this.addCSSRule('.'+this.name,this.name);
		if(cjax_css) {
			cjax_css.style.position = 'relative';
			cjax_css.style.visibility = 'hidden';
		}
		CJAX.set.event(window,'load',CJAX.onStartEvents);
	}
	
	this.cache			=		function() {
		return{
			all : CJAX_CACHE
			,
			add : function(key, value,replace){
					if(typeof replace == 'undefined') var replace = true;
					if(CJAX_CACHE['cache_close'] == 1) {
						return false;
					}
					if((!CJAX_CACHE[key] && replace===true) && value) {
						CJAX_CACHE[key]=value;
					}
				}
			,
			get : function( key ){
					if(CJAX_CACHE[key]) {
						return CJAX_CACHE[key];
					} else {
						return '';
					}
			},
			flush : function(){
				var i, item;
				for(i = CJAX_CACHE.length - 1; i >= 0; i = i - 1){
					item = CJAX_CACHE[i];
					if(delete item[0]){};
					if(item[1].substring(0, 2) != "on"){
						item[1] = "on" + item[1];
					};
					delete item[0][item[1]];
				};
			},
			close : function(){
				CJAX_CACHE['cache_close'] = 1;
			},
			open : function()
			{
				CJAX_CACHE['cache_close'] = 0;
			}
		}
	}();
	
	
	this.set_value		=		function ( buffer ) {
		var elem = CJAX.xml('element',buffer);
		var _value = CJAX.xml('value',buffer);
		var obj = CJAX.$( elem );
		if( !obj ) return false;
		for(x in CJAX.ElementList.get_return()) {
			if(x === obj.type) {
				switch ( CJAX.ElementList.get_return()[x] ) {
					case 'string':
						CJAX.set.value(obj,_value);
					break;
					case 'boolean':
						var check = (_value == 'true' || _value==1 || _value===true)? true:false;
						obj.checked = check;
					break;
				}
				return true;
			} 
		}
	}
	
	this.clone 			=		function(element) {
		var obj =CJAX.is_element(element); 
		return obj.cloneNode(true);
	}
	
	this.getElement		=		function(elements,element) {
		var obj;
		for(e in  elements) {
			if(CJAX.is_element(elements[e])) {
				obj = elements[e];
				if(obj.id || obj.name) {
					if(obj.id == element || obj.name==element) {
						return obj;
					}
				}
			}
		}
	}
	
	this.elements		=		function(element) {
	
		var collect = [];
		for(x in element.childNodes) {
			collect[x] = element.childNodes[x];
		}
		
		return collect;
	}
	
	this.ElementList		=		function() {
		var elems = [];
		return {
			get_return : function() {
				elems['text']			= 'string';
				elems['select-one'] 	= 'string';
				elems['select-multiple']= 'string';
				elems['password']		= 'string';
				elems['hidden']			= 'string';
				elems['textarea']		= 'string';
				elems['button']			= 'string';
				elems['submit']			= 'string';
				elems['checkbox']		= 'boolean';
				elems['radio']			= 'boolean';
				return elems;
			},
			types : function () {
				elems[1]='text';
				elems[2]='select-one';
				elems[3]='select-multiple';
				elems[4]='password';
				elems[5]='hidden';
				elems[6]='textarea';
				elems[7]='button';
				elems[8]='submit';
				elems[9]='checkbox';
				elems[10]='radio';
				return elems;				
			}
		}
	
	}();
	
	this.focus			=		function( buffer ) { 
		var elem = CJAX.is_element(buffer,false);
		if(elem && window.focus()){
			elem.focus();
		}
	}
	
	this.addEvent			=			function( obj, type, fn ) {
		if (obj.addEventListener) {
			try{
				obj.addEventListener( type, fn, false );
				}
				catch( e ){alert( e );}
			CJAX.EventCache.add(obj, type, fn);
		}
		else if (obj.attachEvent) {
			obj["e"+type+fn] = fn;
			obj[type+fn] = function() { obj["e"+type+fn]( window.event ); }
			obj.attachEvent( "on"+type, obj[type+fn] );
			CJAX.EventCache.add(obj, type, fn);
		}
		else {
			obj["on"+type] = obj["e"+type+fn];
		}
	}
	
	this.EventCache			=			function(){
		var listEvents = [];
		return {
			listEvents : listEvents,
			add : function(node, sEventName, fHandler){
				listEvents.push( arguments );
			},
			flush : function( event_id ){
				if(typeof event_id =='undefined') var event_id;
				var i, item;
				for(i = listEvents.length - 1; i >= 0; i = i - 1){
					item = listEvents[i];
					if(item[0].removeEventListener){
						item[0].removeEventListener(item[1], item[2], item[3]);
					};
					if(item[1].substring(0, 2) != "on"){
						item[1] = "on" + item[1];
					};
					if(item[0].detachEvent){
						//item[0].detachEvent(item[1], item[2]);
						item[0].detachEvent(item[1], item[0][eventtype+item[2]]);
					};
					item[0][item[1]] = null;
				};
			}
		};
	}();
	


	this.AddEventTo		=		function( buffer ) {
		var elem = CJAX.xml('element',buffer);
		var method = unescape(CJAX.xml('method',buffer));
		method = method.replace (/\+/gi, "");
		while(method.indexOf( 'PLUSSIGN' ) != -1) {
			method = method.replace ('PLUSSIGN', "+");
		}
		var evento = CJAX.xml('event',buffer);
		if( !evento ) evento = 'onload';
		if(elem == 'window') {
			try{
				CJAX.addEvent(window,evento,  eval( method )  );
				}catch( e ){/*alert( e );*/}
				return true;
		}
		if( !elem ) return false;
		CJAX.addEvent(CJAX.$( elem ),evento,function() {
				eval( method );
		});
	}
	
	this.remove		=		function( buffer ) {
		var element = CJAX.is_element(buffer,false);
		if( !element ) return false;
		element.parentNode.removeChild( element );
	}
	
	this.is_cjax		=		function() {
		if( !CJAX.xml( this.name) ){ return false; }
		return true;
	}
		
	this.replace_txt		=		function ( clear ) {
		if(typeof clear == 'undefined') var clear = false;
		if (!CJAX.is_cjax()){ return; }
		var myvars = new Array();
		var hold = CJAX.source;
		var len;
		var i = 0;
		var value;
		var tags_addition =  25+6+7;
		var ar = 0;
		if( clear ) tags_addition = 6+7;
		while(CJAX.xml(CJAX.name)) {
			value = CJAX.xml(CJAX.name);
			len = value.length;
			string_len = len+tags_addition;//24 characters for the xml notation 6 and 7 for the tag length
			if( value ) {
				i++;
				CJAX.process( value );
				CJAX.source = CJAX.source.substr(string_len,CJAX.source.length);
				CJAX.source.replace(value,'');
			}
			i++;
		}
		if( clear ) CJAX.source = '';
		return true;
	}
	
	this.exe_html		=		function( params ) {
		var destino = CJAX.xml('url',params);
		var text = CJAX.xml('text',params);
		if( !text ) text = 'Loading...';
		var mode  = (CJAX.xml('mode',params)? CJAX.xml('mode',params):'get');
		var image = CJAX.xml('image',params);
		var element =  CJAX.xml('element',params);
		if (element == "REDIRECT"){
			window.location = destino;
			return;
		}
		if( element ){
			var element = CJAX.is_element( element );
			if( !element ) return false;
		}
		CJAX.HTTP_REQUEST_INSTANCE = this.AJAX ();
		if (mode.toLowerCase()  == "get") {
			CJAX.HTTP_REQUEST_INSTANCE.open (mode, destino+"&cjax=1"); //ms="+new Date().getTime());
		}
		else
		{
			if (CJAX.HTTP_REQUEST_INSTANCE.overrideMimeType) {
				//http_request.overrideMimeType('text/xml');
				CJAX.HTTP_REQUEST_INSTANCE.overrideMimeType('text/html');
			}
				CJAX.HTTP_REQUEST_INSTANCE.open (mode, destino,true);
				CJAX.HTTP_REQUEST_INSTANCE.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				CJAX.HTTP_REQUEST_INSTANCE.setRequestHeader("Content-length", destino.length);
				CJAX.HTTP_REQUEST_INSTANCE.setRequestHeader("Connection", "Keep-Alive");
		}
		
		CJAX.HTTP_REQUEST_INSTANCE.onreadystatechange		=		function() {
			if(CJAX.HTTP_REQUEST_INSTANCE.readyState < 4) {
				//document.body.style.cursor = 'wait';	
				if ( !image )image = "cjax/core/images/loading.gif'";
				if( element ) element.innerHTML = "<span><img src='"+image+"/>&nbsp;"+text+"</div>";
			}
			try{
				if (CJAX.HTTP_REQUEST_INSTANCE.readyState == 4 && CJAX.HTTP_REQUEST_INSTANCE.status == 200) {
					var txt =  unescape(CJAX.HTTP_REQUEST_INSTANCE.responseText);
					CJAX.source = txt;
					CJAX.replace_txt();
				
					if( element ){
			      		element.innerHTML = txt;
			      	}
					if (CJAX.debug) {
						var win = window.open('<pre>'+txt+'</pre>', "","width=500","height=400");
					}
				}
				else if (CJAX.HTTP_REQUEST_INSTANCE.readyState == 4 && CJAX.HTTP_REQUEST_INSTANCE.status == 404) {
					var msg = 'CJAX Error: File not found '+destino;
					if( element )
					{
						if(!element.type) { alert( msg ); return false; }
						element.innerHTML = msg; 
					}		
				}
			}
			catch( err )
			{ alert('CJAX: Error - '+err.description); }
		}
	CJAX.HTTP_REQUEST_INSTANCE.send ( null );
	}
		
	this.call_server		=		function( params ) {
		this.source = params;
		var destino = CJAX.xml( 'url' );
		this.text = CJAX.xml( 'text' );
		this.mode  = (CJAX.xml( 'mode' )? CJAX.xml( 'mode' ):'get');
		this.image = CJAX.xml( 'image' );
		this.ajax = false;
		var id_obj =  CJAX.xml( 'element' );
		if (id_obj == "REDIRECT"){
			window.location = destino;
			return;
		}
		if(id_obj) {
			var obj = CJAX.$(id_obj);
			if( !obj ) return false;
		}
		CJAX.HTTP_REQUEST_INSTANCE = this.AJAX ();
		if (this.mode.toLowerCase()  == "get") {
			CJAX.HTTP_REQUEST_INSTANCE.open (this.mode, destino+"&cjax=1"); //ms="+new Date().getTime());
		} else {
			if (CJAX.HTTP_REQUEST_INSTANCE.overrideMimeType) CJAX.HTTP_REQUEST_INSTANCE.overrideMimeType('text/html');
				CJAX.HTTP_REQUEST_INSTANCE.open (this.mode, destino,true);
				CJAX.HTTP_REQUEST_INSTANCE.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				CJAX.HTTP_REQUEST_INSTANCE.setRequestHeader("Content-length", destino.length);
				CJAX.HTTP_REQUEST_INSTANCE.setRequestHeader("Connection", "Keep-Alive");
		}
		
			CJAX.HTTP_REQUEST_INSTANCE.onreadystatechange		=		function() {
				if(CJAX.HTTP_REQUEST_INSTANCE.readyState < 4) {
					//document.body.style.cursor = 'wait';
								
					if (this.image == '') {
			 			this.image = "<img src='cjax/core/images/loading.gif'/>";
					} else {
						this.image = "<img src='"+this.image+"'/>" ;
					}
					if( obj ) obj.innerHTML = "<span>"+this.image+"&nbsp;"+this.text+"</div>";
				}
				
				try{
					if (CJAX.HTTP_REQUEST_INSTANCE.readyState == 4 && CJAX.HTTP_REQUEST_INSTANCE.status == 200) {
						var txt =  unescape(CJAX.HTTP_REQUEST_INSTANCE.responseText);
						CJAX.source = txt;
						CJAX.replace_txt();
						//txt = txt.replace (/\+/gi, " ");
						if( obj ) {
							obj.style.position = "relative";
				      		obj.innerHTML = txt;
				      	}
						if (CJAX.debug) alert( txt );
					}
					else if (CJAX.HTTP_REQUEST_INSTANCE.readyState == 4 && CJAX.HTTP_REQUEST_INSTANCE.status == 404) {
						if( obj ) {
							obj.innerHTML = '<div>Error: File not found '+destino+'</div>'; 
						}		
					}
				}
				catch( err ) {
					var error = ('CJAX: Error - '+err.description);
					if(CJAX.debug) alert( error );
				}
			}
		CJAX.HTTP_REQUEST_INSTANCE.send ( null );
	}
	
	this.get_function		=		function() {
		return CJAX.xml( 'do' );
	}
	
	String.prototype.xml		=		function(tag,loop) {
		return CJAX.xml(tag,this,loop);
	}
	

	String.prototype.array		=		function( tag ) {
		if(typeof tag=='undefined') var tag = 'param';
		var vars = CJAX.xml(tag,this,true);
		return vars;
	}
	
	String.prototype.f			=		function(f) {
		if(CJAX.defined(f)){
			return  this.xml( f );
		}
		return  this.xml( 'do' );
	}
	
	String.prototype.vars		=		function(str) {
		return CJAX.vars[str];
	}
	
	String.prototype.loaded		=		function() {
		return CJAX.defined(CJAX.vars['loaded'][this.value])
	}
	
	String.prototype.append		=		function(tag,value) {
		return this.concat(this,'<'+tag+'>'+value+'</'+tag+'>'); 
	}
	
	String.prototype.clear	=	function ( str ){
		this.value = '';
		return true;
	}
	//this doesn't jive at all on IE7 with Prototype.
	/*
	if (typeof String.prototype.trim == 'undefined') {
		//only do if not already defined, so it works with prototype framework
		String.prototype.trim		=		function( data ) {
		    if ( !data ) return;
		    while (data[0] == ' ' || data[0] == '\n') data = data.substr( 1 ); var l = data.length-1;
		    while (l > 0 && data[l] == ' ' || data[l] == '\n') l--;
		    return this.substring(0, l+1);
		}
	}
	*/
	Array.prototype.in_array = function ( obj ) {
		var len = this.length;
		for (x in this) {
			if (x == obj ){ return true;}
		}
		return false;
	}
	
	this.defined		=		function(obj) {
		return (typeof obj!='undefined')? true:false;
	}
	
	this.script		=		function() {
		return {
			loaded : function ( path ,force) {
				if(!CJAX.defined(path)) {
					return false;
				}
				//Loaded by CJAX
				if(!CJAX.defined(force)) {
					return (path.loaded())? true:false;
				} 
				//Loaded on the document
				var scripts = CJAX.elem_docs( 'script' );
				var s;
				if(scripts.length){
					for(var i = 0; i < scripts.length; i++ ){
						s = scripts[i];
						if(s.src==path) return s;
					}
				}
				return false;
				
			},
			load: function(script,f,parameters,secs,bypass) {
				if(!CJAX.defined(parameters)) {
					var parameters = '';
				}
				var type =  CJAX.xml('ctype',parameters);
				if(CJAX.defined(bypass)) {
					var s = CJAX.script.loaded( script ,'function');
					if ( s )  return s;
				}
				if(typeof secs =='undefined') var secs = 0; else CJAX.seconds = secs;
				CJAX.defined(f);
				
				if(eval('typeof '+f) =='function') {
					try {
						if ( CJAX.seconds ) {
								setTimeout(f+'("'+parameters+'")',CJAX.seconds*1000);
							} else {
								eval(f+'("'+parameters+'")');
							}
						}
						catch( e ){
							alert('CJAX.script.load() - unabled to load function: '+ f.toUpperCase()+ ' '+e); 
						}
						return true;
				}
				var head = CJAX.elem_docs( 'head' )[0];
				var s = document.createElement( 'script' );
				s.type = 'text/javascript';
				s.src= script;
				
				if ( f )  {
					s.onreadystatechange = function () {
						try{
							if (CJAX.seconds) {
							//alert('f:'+f+ '  seconds: '+CJAX.seconds);
								setTimeout(f+'("'+parameters+'")',CJAX.seconds*1000);
							} else {
								if (type == 'extension_system') {
									eval (f+'.hook("'+parameters+'")');
								} else {
									eval(f+'("'+parameters+'")');
								}
							}
						}
						catch( e ){
							alert('unabled to load function: '+ f+ ' error name:'+e.name+' Error message: '+e.message); 
						}
					}
				   	s.onload =  function () {
	   					try {
								if(CJAX.seconds) {
									setTimeout(f+'("'+parameters+'")',CJAX.seconds*1000);
								} else {
								switch ( type ) {
									case 'extension_system':
									if(parameters)
									eval(f+'.hook("'+parameters+'")');
										break;
									case 'extension_child':
										eval(f+'("'+parameters+'")');
										//let go break on purpose
										break;
									default:
										eval(f+'("'+parameters+'")');
								}
							}
						}
						catch( e ){
							alert('unabled to load function: '+type+' : '+ f+ ' '+e); 
						}
				   	}
				}
				if(!CJAX.vars['loaded'][script]) {
					CJAX.vars['loaded'][script] = [];
					CJAX.vars['loaded'][script]['function'] = [];
					CJAX.vars['loaded'][script]['function']['src'] = script;
					CJAX.vars['loaded'][script]['function']['function'] = f;
				}
				head.appendChild( s );
				return s;
			},
			action : function(f,p,s){
				try {
					if ( s ) {
						setTimeout(f+'("'+p+'")',s*1000);
					}else{
						eval(f+'("'+p+'")');
					}
				}
				catch( e ){ alert('unabled to load function: '+ f.toUpperCase()+ ' '+e); }
			}
		}
	}();	
	
	this.process		=		function( buffer ) {
		if(!CJAX.is_cjax()) return false;
		if(typeof buffer =='undefined') var buffer = '';
		CJAX.method = CJAX.get_function();
		if(!CJAX.method) return false;
		var PREFIX = 'CJAX.';
		var f = _FUNCTION = PREFIX+CJAX.method;
		var ext = CJAX.xml('extension',buffer);
		if(CJAX.xml('debug',buffer)) CJAX.debug = true;
		if( ext ){
			var f = _FUNCTION = CJAX.method;
			extension.hook( buffer );
		} else {
			if(typeof eval( f ) === 'function' ) {
				try{
					if(CJAX.xml('seconds',buffer)){
						setTimeout(PREFIX+CJAX.method+"('"+buffer+"')",CJAX.xml('seconds',buffer)*1000);
					} else {
						eval(PREFIX+CJAX.method+'("'+buffer+'")');
					}
				}
				catch( e ){ alert('unabled to load function: '+ CJAX.method.toUpperCase()+ ' '+e); }
			}
		}
	}
		
	this.xml		=		function (start , buffer , loop) {
		var source = CJAX.source;
		if(buffer && buffer!=null) source = buffer;
		if(typeof loop =='undefined') var loop = 0;
		if(typeof start=='undefined') return '';
		if(!source || !start) return '';
		var real_var = start;
		var end = '</'+start+'>';
		start = '<'+start+'>';
		var loc_start = source.indexOf( start );
		var start_len = start.length;
		var end_len = end.length;
		var loc_end = source.indexOf( end );
		var middle = loc_end - loc_start - end_len +1;
		if (loc_start == -1 || loc_end ==-1) return false;
		var _new_var = source.substr(loc_start+start_len,middle);
		var string_len = loc_start+start_len+_new_var.length+start_len;
		if(loop != 0) {
			var myarr = [];
			var i = 0;
			var value;
			var hold = source;
			string_len = loc_start+start_len+_new_var.length+end_len;
			while(CJAX.xml(real_var,hold) && hold) {
				value = CJAX.xml(real_var,hold);
				hold = hold.substr(string_len,hold.length);
				myarr[i] = value;
				i++;
			}
			return (myarr)?myarr:'';
		}
		return _new_var;
	}
	
	this.hide	=	function(buffer,verbose) {
		if( !verbose ) var verbose = true;
		var elem = CJAX.is_element(buffer,verbose);
		if( !elem ) {
			elem = CJAX.xml('element',buffer);
			elem = CJAX.$( elem ); 
		}
		if( elem ) elem.style.display = 'none'; 
	}
	
	this.show	=	function(buffer,verbose) {
		if( !verbose ) var verbose = true;
		var elem = CJAX.is_element(buffer,false);
		if( !elem ) {
			elem = CJAX.xml('element',buffer);
			elem =  CJAX.$(elem,false);
		}
		if( elem ) { elem.style.display = 'block'; }
	}
	
	this.addCSSRule		=		function(rule,title)  {
		if(CJAX.rule == rule){ return CJAX.getCSSRule( rule ); }
		var styles = document.styleSheets;
		var s;
		var position;
		var create = true;
		var has_length = false;
		for (var i = 0; i < styles.length; i++ ) {
			has_length = true;
			s = styles[i];
			//already exists, no need to create.
			if( s.title == title ){ create = false; break; }
		}
		
		if ( create || !has_length) {
			var s = document.createElement( 'style' );
			s.type = 'text/css';
			s.rel = 'stylesheet';
			s.media = 'screen';
			s.title = title;
			var style_doc = CJAX.elem_docs( "head" )[0];
			if( i> 0 ) {	//there are other stylesheets and we need to get before
				//them, or  else it won't work.
				try{
					//style_doc.firstChild.firstChild;
					style_doc.parentNode.insertBefore(s,style_doc);
				}
				catch( e ){
					/*need to catch exection of Ie will give me an error*/
					alert('caught: '+e);
				}
			} else {	//this is the only styleshee,  awesome!
				style_doc.appendChild( s );
				//style_doc.firstChild.insertBefore(this.style,position);
			}
			//first for FF/ 2 for IE
			s = s.sheet?s.sheet:s.styleSheet;
			
			if (s.addRule) {
				s.addRule(rule, null,0);
			} else {
				s.insertRule(rule+' { }', 0);
			}
		return CJAX.getCSSRule( rule );
		}
	}
	
	this.getCSSRule		=		function( rule ) {
		var rule = rule.toLowerCase();
		var sheets = document.styleSheets;
		if ( !sheets ) { return false; }
		for ( var i=0; i<sheets.length; i++ ) {
			var sheet		=	sheets[i];
			var style_rule	=	sheet.cssRules;
			var style_sheet	=	sheet.rules;
			var cssRule;
			var e			=	0;
			try {
				do {
					this.rule	=	(style_rule)?style_rule[e]:style_sheet[e];e++;
					if (this.rule.selectorText.toLowerCase()==rule){ return this.rule; }
				}
			while (	cssRule	)
			}	catch( e ) { return false; }
		}	
	}

	this.turn		=		function( elem ) {
		if( elem ) {
			if (!elem.type) return false;
			switch ( elem.type ) {
				case 'checkbox':
				case 'radio':
					return true;
				break;
				default:
				return false;
			}
		}
		return false;
	}
	
	this.passvalue		=		function(elem,verbose) {
		if ( typeof verbose=='undefined' ) var verbose = true;
		var obj = CJAX.elem_doc(elem,verbose);
		if ( obj ) {
			switch ( obj.type ) {
				case 'text':
				case 'select-one':
				case 'select-multiple':
				case 'password':
				case 'textarea':
				case 'hidden':
					return escape(obj.value);
				break;
				case 'checkbox':
				case 'radio':
				return (obj.checked)? 1:0;
				break;
			}
		}
	}
	
	this.AJAX		=		function() {
		xmlhttp = false;
		try{
			xmlhttp = new ActiveXObject ("Msxml2.XMLHTTP");
		}
		catch ( e ){
			try{
				xmlhttp = new ActiveXObject ("Microsoft.XMLHTTP");
			}
			catch ( e ){
				xmlhttp = false;
			}
		}
		if (!xmlhttp && typeof XMLHttpRequest!='undefined') xmlhttp = new XMLHttpRequest ();
		return xmlhttp;
	}

	this.create_element		=		function ( buffer ) {
		var elem = CJAX.xml('element',buffer);
		var _parent = CJAX.xml('parent',buffer);
		var _type = CJAX.xml('type',buffer);
		var _class = CJAX.xml('class',buffer);
		var _html = CJAX.xml('html',buffer);
		var obj = CJAX.$(elem,false);
		if( obj ) return obj;
		if(_parent =='') return;
		var parent_elem = CJAX.$(_parent);
		if(!parent_elem) return;
		if(_type=='') return;
		var newelem = document.createElement(_type);
		if(_class) newelem.setAttribute('class',_class);
		newelem.setAttribute('id',elem);
		if(_html) newelem.innerHTML = _html;
		parent_elem.appendChild( newelem );
		return newelem;
	}

	this.intval		=		function( number ) {
		 var ret =  isNaN( number )? false:true;
		 if( ret ) { return number; } else { return 0; } 
	}
	
	this.wait		=		function( buffer ) {
		CJAX.waiting = true;
		CJAX.seconds = CJAX.intval(CJAX.xml('seconds',buffer)) * 1000;
		return true;
	}
	
	this.updateContent		=		function( buffer ) {
		var elem = CJAX.xml('element',buffer);
		var data = unescape(CJAX.xml('data',buffer));
		data = data.replace (/\+/gi, " ");
		var obj = CJAX.$( elem );
		if( obj ) obj.innerHTML = data;
	}
	
	this.decode = function( data ) {
	 	if( !data ) return '';
		
	 	return unescape ( data );
	 	
	 	//Remove below once the above has been tested a while.
	 	
	 	data = unescape( data );
		 data = data.replace(/\+/gi, " ");
		 data = data.replace(/~Q~/gi, "\"");
		 data = data.replace(/~NL~/gi, "\n");
		 data = data.replace(/~BS~/gi, "\\");
		 data = data.replace(/~FS~/gi, "/");
		 data = data.replace(/~T~/gi, "	");
		 data = data.replace(/~SQ~/gi, "'"); 
		 data = data.replace(/~S~/gi, " ");
		 data = data.replace(/(~ADD~)+/gi, "\+");
		 return data;
	 }   
		
	this.is_element			=			function(element,verbose) {
		if( typeof verbose === 'undefined') { var verbose = true; }
		if( verbose ) if( !element ){alert('invalid input on function: '+_FUNCTION+' :  not an element ');return;}
		var _element;
		var type = (typeof element);
		if( type.indexOf( 'object' ) != -1) return element;
		_element = CJAX.xml('element',element);
		if(_element) return CJAX.$(_element,false);
		_element = CJAX.$(element,verbose);
		if(_element) return _element;
		return;
	}
	
	this.update		=		function(buffer,data) {
		var element = CJAX.is_element(buffer,false);
		if( !element ) return false;
		if( !data ) var data = CJAX.xml('data',buffer);
		data = CJAX.decode( data );
		element.innerHTML = data;
	}
	
	this.load_script = function ( params ) {
		if(typeof params =='undefined' || !params) return false;
		var path = CJAX.xml('script',params);
		path = path.replace('__domain__',CJAX.get.dirname(document.baseURI));
		var s = CJAX.script.load( path );
	}
	
	this.load_function = function ( params ) {
		if(typeof params =='undefined' || !params) return false;
		var seconds = CJAX.xml('seconds',params);
		var f_name = CJAX.xml('function',params);
		eval (f_name+'();');
	};
	
	this.applyClassToType		=		function() {
		var tag = CJAX.xml( 'tag' );
		var type = CJAX.xml( 'type' );
		var _class = CJAX.xml( 'class' );
		var elems = CJAX.elem_docs((tag? tag:'input'));
		if( !elems )return false;
		if( !type ) return false;
		if(!_class) return false;
		var elem;
		for ( var c = 0; c < elems.length; c++){
			elem = elems[c];
			if(elem.type == type){
				elem.className = _class;
			}
		}
	}
	
	this.applyClass		=		function() {
		var elems = CJAX.elem_docs(CJAX.xml('elem_tag'));
		var _class = CJAX.xml( 'class' );
		if(elems.length){
			if(_class){
				for(var c = 0; c < elems.length; c++){
					elems[c].className = _class;
				}
			}
		}
	}
	
	this.alert		=		function ( buffer ) {
		var msg = CJAX.xml('msg',buffer);
		smg = CJAX.decode( msg );
		alert( smg );
	}
	
	this.location		=		function( buffer ) {
		var destination = CJAX.xml('url',buffer);
		window.location = destination;	
	}
	
	function var_dump( obj ) {
		if(typeof obj == "object") {
			return "Type: "+typeof( obj )+((obj.constructor) ? "\nConstructor: "+obj.constructor : "")+"\nValue: " + obj;
		} else {
			return "Type: "+typeof( obj )+"\nValue: "+obj;
		}
	}
	
	this.getSelectedRadio		=		function( buttonGroup ) {
	   if (buttonGroup[0]) {
	      for (var i=0; i<buttonGroup.length; i++) {
	         if (buttonGroup[i].checked) {
	            return i
	         }
	      }
	   } else {
	      if (buttonGroup.checked) { return 0; } 
	   }
	   return -1;
	} 
	
	this.getSelectedRadioValue		=		function( buttonGroup ) {
	   var i = CJAX.getSelectedRadio( buttonGroup );
	   if (i == -1) {
	      return "";
	   } else {
	      if (buttonGroup[i]) {
	         return buttonGroup[i].value;
	      } else {
	         return buttonGroup.value;
	      }
	   }
	}
	
	this.is_array		=		function( element ) {
		if(element.length) return true;
		return false;
	}

	this.form_get_elements_url		=		function( frm_object ) {
		var frm_url = '';
		var elem;
		var value;
		var c = 0;
		var form =  CJAX.is_element(frm_object).getElementsByTagName("*");
		for(var n = 0; n < form.length; n++){
			if(form[n].id =='undefined' && form[n].name =='undefined') continue;
			c++;
			elem = form[n];
			elem_id = elem.id;
			elem_type = elem.type;
			elem_value = elem.value;
			elem_len = elem.length;
			elem_id = elem.id;
			if((elem.id && elem.name !='') || (elem.id == 'undefined' && elem.name !='')){
				elem_id = elem.name; 
			}
			switch ( elem.type ) {
				case 'checkbox':
					value = ((elem.checked)? 1:0);
				break;
						case 'text':
						case 'select-one':
						case 'textarea':
							value = elem.value;
				break;
				case 'radio':
				
					if(CJAX.getSelectedRadio( elem ) === -1)continue;
					
					value = CJAX.getSelectedRadioValue( elem );
					break;
				default:
					value = encodeURI(elem.value);
			}
			if(value !='undefined' && elem_id) frm_url += "&"+elem_id + "="+ value;
		}
		return frm_url;
	}

	this.exe_form		=		function( params ) {
		var destino = CJAX.xml('url',params);
		var frm = CJAX.xml('form',params);
		var container = CJAX.xml('container',params);
		var text = CJAX.xml('text',params);
		if( !text ) text = 'Loading...';
		var image = CJAX.xml('image',params);
		var mode  = CJAX.xml('method',params);
		if( !mode ) mode = 'get';
		if(frm != '') {
			var url ='';
			var elem_value = '';
			var is_my_radio = new Array( 10 );
			var splitter;
			var assign = '=';
			form = document.forms[frm]
			if( !form ) {
				var url = CJAX.form_get_elements_url( frm );
				if( !url ){ alert('CJAX: Please specify a form name'); return false; }
			} else {
				var elems =  form.elements? form.elements: elems;
				var form_len = elems.length;
				for (var n=0; n < form_len; n++) {
					splitter = '&';
					elem  = elems[n];
					elem_id = elem.id;
					elem_type = elem.type;
					elem_name =  elem.name;
					elem_value = elem.value;
					elem_len = elem.length;
					if(!elem_type)continue;
					if(elem_id && elem_name)elem_id = elem_name;
					if(!elem_id && elem_name)elem_id = elem_name;
					switch ( elem_type ) {
						case 'checkbox':
							elem_value = ((elem.checked)? 1:0);
						break;
						case 'text':
						case 'select-one':
						case 'textarea':
							assign='=';
							elem_value = elem.value;
						break;
						case 'radio':					
							if(CJAX.getSelectedRadio( elem ) != -1) {
								if(CJAX.getSelectedRadioValue( elem )) 
								elem_value = CJAX.getSelectedRadioValue( elem ); assign='=';
							}else{
								splitter =''; elem_id =''; elem_value =''; assign='';
							}
							break;
						default:
							elem_value = elem.value;
					}
					url += splitter;
					url += elem_id + assign + encodeURI(elem_value);
					assign = '=';
				}
			}
		  	destino += url;
	  	}

		if ( container == 'REDIRECT' ){
			window.location = destino;
			return;
		}
		if( container ){
			container = CJAX.$( container );
			if( !container )return false;
		}
		if(!CJAX.HTTP_REQUEST_INSTANCE) CJAX.HTTP_REQUEST_INSTANCE = CJAX.AJAX();
		if (mode.toLowerCase()  == "get") {
			CJAX.HTTP_REQUEST_INSTANCE.open (mode, destino+"&cjax=1");   //ms="+new Date().getTime());			
		} else {
			if (CJAX.HTTP_REQUEST_INSTANCE.overrideMimeType) {
				CJAX.HTTP_REQUEST_INSTANCE.overrideMimeType('text/xml');
			}
			CJAX.HTTP_REQUEST_INSTANCE.open ('POST', destino,true);
			CJAX.HTTP_REQUEST_INSTANCE.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			CJAX.HTTP_REQUEST_INSTANCE.setRequestHeader("Content-length", destino.length);
			if(destino.length > 1500) {
				CJAX.HTTP_REQUEST_INSTANCE.setRequestHeader("Connection", "Keep-Alive");
			} else {
				CJAX.HTTP_REQUEST_INSTANCE.setRequestHeader("Connection", "Closed");
			}
		}
		CJAX.HTTP_REQUEST_INSTANCE.onreadystatechange = function () {
			if(CJAX.HTTP_REQUEST_INSTANCE.readyState <= 4) {				
				if (image == '') {
		 			image = "<img src='cjax/core/images/loading.gif'/>";
				} else {
					image = "<img src='"+image+"'/>" ;
				}
				if( container ) container.innerHTML = "<span>"+image+"&nbsp;"+text+"</span>";  
			}		
			if (CJAX.HTTP_REQUEST_INSTANCE.readyState == 4 && CJAX.HTTP_REQUEST_INSTANCE.status == 200) {
				var txt =  unescape(CJAX.HTTP_REQUEST_INSTANCE.responseText);
				CJAX.source = txt;
				CJAX.replace_txt();
				if( container ) container.innerHTML = txt;	
	     		if (CJAX.debug) alert(container.innerHTML);
			}else if (CJAX.HTTP_REQUEST_INSTANCE.readyState == 4 && CJAX.HTTP_REQUEST_INSTANCE.status == 404) {
				container.innerHTML = '<div>Error: File not found '+destino+'</div>'; 					
			}
		}
		CJAX.HTTP_REQUEST_INSTANCE.send ( null );
	}
	
	this.$					=		function(e,v) {
		return CJAX.elem_doc(e,v);
	}
	
	this.elem_doc		=		function(id_obj,verbose) {
		var type = (typeof elem);
		if( typeof verbose == 'undefined' && CJAX.debug) { verbose = true; }
		if( type.indexOf( 'object' ) == -1) {var elem = document.getElementById(id_obj);}
		if(typeof id_obj == 'undefined' || id_obj===null) {
			if( verbose ) alert('Element not found'); 
		 	return false;
		}
		
		if( !elem ){
			if( verbose ) alert('CJAX: Element "'+id_obj+'" not found on document');
			return false;
		}
		return elem;
	}
	
	this.elem_docs		=		function(id_obj,verbose) {
		if(typeof verbose =='undefined') verbose = true;	
		var obj = document.getElementsByTagName(id_obj);
		if( !obj ) {
			if( verbose ) alert('CJAX: Element '+id_obj+' not found on document');
			return;
		}			
		return obj;
	}
	
	
	this.getY		=		function () {
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
		
	this.getX 	=	 function() {
		var scrOfX = 0;
		if( typeof( window.pageYOffset ) == 'number' ) {
		//Netscape 
			compliantscrOfX = window.pageXOffset;
		} else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
		//DOM 
			compliantscrOfX = document.body.scrollLeft;
		} else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
		//IE6 standards compliant 
			modescrOfX = document.documentElement.scrollLeft;}return scrOfX;
	} 
	
	function __construct() {
		if(CJAX.script.load(__base__+"/components/extensions.js")) CJAX.COMPONENTS['extensions'] = 1;
		
		//pre-load certain files/plugins for IE to load properly
		CJAX.script.load(__base__+"/extensions/plugins.js");
		CJAX.script.load(__base__+"/plugins/popup.js");
		
		CJAX.script.load(__base__+'/core/classes/cjax.js.php');
	}
}