/********************************
* Element Collection
********************************/
elementCollectionClass = function (){
	this.elementCollection = new Array();
	
	this.add = function(el,collection_name){
		var c = collection_name.split(" ");
		for (var i in c){
			if (!(c[i] in this.elementCollection)){
				this.elementCollection[c[i]] = new Array();
			}
			this.elementCollection[c[i]].push(el);
			if (el.attr != undefined && el.attr('id')){
				jQuery('#'+el.attr('id')).addClass(c[i]);
			}
		}
	}
	
	this.get = function(collection_name){
		if (collection_name in this.elementCollection){
			return this.elementCollection[collection_name];
		}
		else{
			return new Array();
		}
	}	
	
	this.set = function(collection_name,collection){
		this.elementCollection[collection_name] = collection;
	}
	
	this.getOne = function(collection_name){
		if (collection_name in this.elementCollection){
			return this.elementCollection[collection_name][0];
		}
		else{
			return null;
		}
	}
	
	this.each = function(collection_name,func){
		jQuery.each(this.get(collection_name),func);
	}
	
	this.remove = function(collection_name){
		var c = collection_name.split(" ");
		var new_ec = new Array();
		for (var j in this.elementCollection){
			if (jQuery.inArray(j,c) == -1){
				new_ec[j] = this.get(j);
			}
		}
		this.elementCollection = new_ec;
	}
	
	this.cleanup = function(collection_name){
		// This proved to be more difficult than I imagined.
		// When an element is removed from the DOM, it is not automatically
		// removed from the elementCollection.  I tried things like looking
		// for the element's parent, to see if it was orphaned, but for 
		// whatever reason, it wasn't consistent.  Finally, I decided to 
		// empty the collection and reset it from the DOM.  That's what I'm 
		// doing here. 
		var c = collection_name.split(" ");
		for (var i in c){
			this.elementCollection[c[i]] = new Array();
			var that = this;
			jQuery('.'+c[i]).each(function(k,el){
				that.elementCollection[c[i]].push(jQuery(el));
			});
		}
	}
}

// elementCollection is a way to cache jQuery dom elements
// Internet Explorer is brutal on jQuery('.class_name') iterations
// on large doms.  Hopefully elementCollection will be a way to 
// speed that up.  Hopefully.
var elementCollection = new elementCollectionClass();

callbackManagerClass = function (){
	this.callbacks = new elementCollectionClass();
	
	this.register = function(what,callback){
		this.callbacks.add(callback,what);
	}
	
	this.unregister = function(what){
		this.callbacks.remove(what);
	}
	
	this.run = function(what,default_return,arguments){
		var _type = objectType(what);
		var _return = default_return;
		var c;
		if (_type == 'regexp'){
			for(var i in this.callbacks.elementCollection){
				if (i.match(what)){
					c = this.callbacks.get(i);
					if (c.length){
						for (var j in c){
							if (arguments){
							    _return = c[j](i,_return,arguments);
							}
							else{
							    _return = c[j](i,_return);
							}
						}
					}
				}
			}
		}
		else{
			c = this.callbacks.get(what);
			if (c.length){
				for (var i in c){
					if (arguments){
					    _return = c[i](what,_return,arguments);
					}
					else{
					    _return = c[i](what,_return);
					}
				}
			}
		}
		return _return;
	}
	
	this.has = function(what){
		var _type = objectType(what);
		if (_type == 'regexp'){
			for(var i in this.callbacks.elementCollection){
				if (i.match(what)){
					return true;
				}
			}
		}
		else{
			return this.callbacks.get(what) > 0;
		}
	}
}

var callbackManager = new callbackManagerClass();

objectType = function( obj ) {
	if (typeof obj === "undefined") {
			return "undefined";

	// consider: typeof null === object
	}
	if (obj === null) {
			return "null";
	}

	var type = Object.prototype.toString.call( obj )
		.match(/^\[object\s(.*)\]$/)[1] || '';

	switch (type) {
			case 'Number':
					if (isNaN(obj)) {
							return "nan";
					} else {
							return "number";
					}
			case 'String':
			case 'Boolean':
			case 'Array':
			case 'Date':
			case 'RegExp':
			case 'Function':
					return type.toLowerCase();
	}
	if (typeof obj === "object") {
			return "object";
	}
	return undefined;
}

function PageQuery(q) {
	if(q.length > 1) this.q = q.substring(1, q.length);
	else this.q = null;
	this.keyValuePairs = new Array();
	if(q) {
		for(var i=0; i < this.q.split("&").length; i++) {
			this.keyValuePairs[i] = this.q.split("&")[i];
		}
	}
	this.getKeyValuePairs = function() { return this.keyValuePairs; }
	this.getValue = function(s) {
		for(var j=0; j < this.keyValuePairs.length; j++) {
			if(this.keyValuePairs[j].split("=")[0] == s)
			return this.keyValuePairs[j].split("=")[1];
		}
		return false;
	}
	this.getParameters = function() {
		var a = new Array(this.getLength());
		for(var j=0; j < this.keyValuePairs.length; j++) {
			a[j] = this.keyValuePairs[j].split("=")[0];
		}
		return a;
	}
	this.getLength = function() { return this.keyValuePairs.length; }
}

function queryString(key){
	var page = new PageQuery(window.location.hash);
	return unescape(page.getValue(key));
}
function displayItem(key){
	if(queryString(key)=='false'){
		document.write("you didn't enter a ?name=value querystring item.");
	}else{
		document.write(queryString(key));
	}	
}