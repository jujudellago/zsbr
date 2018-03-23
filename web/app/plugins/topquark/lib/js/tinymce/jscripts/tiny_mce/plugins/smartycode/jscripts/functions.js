function init() {
	tinyMCEPopup.resizeToInnerSize();
	
	var div_pattern = 'Dependent_([^_ ]*)';
    var re = new RegExp(div_pattern, 'i'); 
	var all_elements;
	var m;
	var bypass;
	var already_checked = new Array();
	
	var existingTag = getExistingTag();
	if (existingTag != null){
		if (getURLVar('package') == ''){
			window.location = 'smartycode.php?package=' + existingTag['package'] + '&function=' + existingTag['function'];
		}
		else{
			var element;
			for (v in existingTag){
				element = document.getElementById(v);
				if (element != undefined){
					element.value = existingTag[v];
				}
			}
			
			var cancelButton = document.getElementById('CancelButton');
			if (cancelButton != undefined){
				cancelButton.onclick= tinyMCEPopup.close;
			}
		}
	}

	var elements = document.getElementsByTagName('tr');
	for (e = 0; e < elements.length; e++){
		m = re.exec(elements[e].className);
		bypass = false;
		if (m != null){
			/*
			// Keeping this code around, cause it's useful for debugging
			
			all_elements = all_elements + "\n\n" + e + ': ' + elements[e].className;
			for (i = 0; i < m.length; i++){
				all_elements = all_elements + "\n   " + m[i];
			}
			*/
			for (j = 0; j < already_checked.length; j++){
				if (already_checked[j] == m[1]){
					bypass = true;
				}
			}
			
			if (!bypass){
				already_checked.push(m[1]);
				updateDependents(m[1]);
			}
		}
	}
	
	
}
init();

function getExistingTag(){
	var re = new RegExp('\{(paint|retrieve)(.*)package=[\'\"]([^\'\"]*)[\'\"]([^\}]*)\}','i');
	var parm = new RegExp('([^= ]*)=[\'\"]([^\'\"]*)[\'\"]','i');
	var elm = tinyMCE.activeEditor.selection.getNode().innerHTML; // the element the cursor position is in
	var tag = null;
	
	m = re.exec(elm);
	if (m != null){
		tag = new Array();
		tag["tag"] = m[0];
		tag["function"] = m[1];
		tag["package"] = m[3];
		var other_code = m[2];
		if (m[4] != undefined){
			other_code+= m[4];
		}
		
		var str;
		p = parm.exec(other_code);
		while (other_code != '' && p != null){
			tag[p[1]] = p[2];
			other_code = other_code.replace(p[0],"").trim();
			p = parm.exec(other_code);
		}
		
		/*
		var str = 'Length: ' + m.length;
		for (i = 0; i < m.length; i++){
			str+= "\n\n" + i + ": " + m[i];
		}
		*/
	}
	return tag;
}

String.prototype.trim = function () {
    return this.replace(/^\s*/, "").replace(/\s*$/, "");
}

function insertSmartyCode() {
	var elements;
	var searchTags = new Array('select','input');  // should I add in 'tr'?
	var smarty_code;
	
	smarty_code = '{' + document.getElementById('function').value;
	smarty_code = smarty_code + " package='" + document.SmartyCodeForm.package.value + "'";
	
	for (s in searchTags){
		elements = document.getElementsByTagName(searchTags[s]);
		for (var e = 0; e < elements.length; e++){
			if (elements[e].style != undefined){
				if (elements[e].style.display != 'none' && elements[e].type != 'hidden' && elements[e].type != 'button'){
					if (document.getElementById(elements[e].id).value != ''){
						smarty_code = smarty_code + ' ' + elements[e].id + '=\'' + document.getElementById(elements[e].id).value + '\'';
					}
				}
			}
		}
	}
	
	smarty_code+= "}";
	
	var existingTag = getExistingTag();
	if (existingTag == null){
		tinyMCE.execCommand('mceInsertContent', false, '<span>' + smarty_code + '</span>');
	}
	else{
		tinyMCE.activeEditor.selection.getNode().innerHTML = tinyMCE.activeEditor.selection.getNode().innerHTML.replace(existingTag['tag'],smarty_code);
	}
	
	tinyMCEPopup.close();
}

function updateDependents(parm){
	var elements;
	var searchTags = new Array('input','select','tr');  // should I add in 'tr'?
	var div_pattern = 'Dependent_' + parm + '_';
    var re = new RegExp(div_pattern + '([^ ]*)', 'i'); 
	var re_plain = new RegExp(div_pattern, 'i');
	var search;
	var display;
	
	/* Debug Code */
	var str;
	var display_str = false;
	str = 'Pattern: ' + re_plain.source;
	str = str + "\n\nParm: " + parm;
	str = str + "\n\nParm Value: " + document.getElementById(parm).value;

	for (s in searchTags){
		elements = document.getElementsByTagName(searchTags[s]);
		for (var e = 0; e < elements.length; e++){
			/* Debug Code */
			if (elements[e].className > ''){
				display_str = true;
				str = str + "\n\n" + elements[e].className;
			}
			if (re_plain.test(elements[e].className)){
				search = elements[e].className.split(' ');
				display = false;
				for (se in search){
					if (re.test(search[se]) && RegExp.lastParen == document.getElementById(parm).value){
						display = true;
					}
				}
				if (display){
					if (searchTags[s] == 'tr'){
						elements[e].style.display = '';
					}
					else{
						elements[e].style.display = 'block';
					}
				}
				else{
					elements[e].style.display = 'none';
				}
			}
		}
	}
	
	/* Debug Code */
	if (display_str){
		//alert(str);
	}
	
}

function getURLVar(urlVarName) {
	//divide the URL in half at the '?'
	var urlHalves = String(document.location).split('?');
	var urlVarValue = '';
	if(urlHalves[1]){
		//load all the name/value pairs into an array
		var urlVars = urlHalves[1].split('&');
		//loop over the list, and find the specified url variable
		for(i=0; i<=(urlVars.length); i++){
			if(urlVars[i]){
				//load the name/value pair into an array
				var urlVarPair = urlVars[i].split('=');
				if (urlVarPair[0] && urlVarPair[0] == urlVarName) {
					//I found a variable that matches, load it's value into the return variable
					urlVarValue = urlVarPair[1];
				}
			}
		}
	}
	return urlVarValue;   
}

