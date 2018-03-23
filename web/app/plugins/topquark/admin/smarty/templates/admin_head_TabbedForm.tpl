{* Smarty *}
{literal}
<script language="JavaScript" type="text/javascript">
<!--

	function localGetElementsByTagName(tagName) {
		var eleArray;
		if (window.opera) eleArray = document.body.getElementsByTagName(tagName);
		else if (document.getElementsByTagName) eleArray = document.getElementsByTagName(tagName);
		else if (document.all) eleArray = document.all.tags(tagName);
		else if (document.layers) {
			eleArray = new Array();
			nnGetAllLayers(window, eleArray, 0);
		}
		return eleArray;
	}

	function nnGetAllLayers(parent, layerArray, nextIndex) {
		var i, layer;
		for (i = 0; i < parent.document.layers.length; i++) {
			layer = parent.document.layers[i];
			layerArray[nextIndex++] = layer;
			if (layer.document.layers.length) nextIndex = nnGetAllLayers(layer, layerArray, nextIndex);
		}
		return nextIndex;
	}

	function enableButtons() {
		var buttons = localGetElementsByTagName("input");

		var i = 0;
		while (buttons[i]) {
			if (buttons[i].type == "submit" || buttons[i].type == "button") {
				buttons[i].disabled = false;
			}
			i++;
		}
	}
	
	-->
	</script>
{/literal}	
<script language="JavaScript" type="text/javascript">
	var Sections=new Array()
{section name=tab_sections loop=$Tabs}
	Sections[{$smarty.section.tab_sections.index}] = 'group_{$Tabs[tab_sections]->getID()}' ;
{if $Tabs[tab_sections]->isDefault() or $default == ""}
{assign var="default" value=$Tabs[tab_sections]->getID()}
{/if}
{/section}
	section_tabs = new configSection('group_{$default}',Sections) ;

{literal}
<!-- This Javascript and the Tabs are inspired by the Horde Forms code -->

        function configSection(inittab,sections) {
            this.sections = sections;

            this.toggle = function(id) {
			document.getElementById(this.oldtab).style.display 	= 'none';
			document.getElementById('tab_' + this.oldtab).className = 'tab';
			document.getElementById(id).style.display 		= 'inline';
			document.getElementById('tab_' + id).className 		= 'tab-hi';
			
			this.oldtab=id;
			this.currentSectionNr= this.getTabByName(id);
            }

			this.getTabByNr = function(nr) {
			for (var itemNr=0; itemNr <= this.sections.length; itemNr++) {
				if (this.sections[itemNr] == this.sections[nr]) {
					return (this.sections[itemNr]);
				}
			}
		}

		this.getTabByName = function(name) {
			for (var itemNr=0; itemNr <= this.sections.length; itemNr++) {
				if (this.sections[itemNr] == name) {
					return (itemNr);
				}
			}
		}

		this.nextTab = function() {
			if (this.currentSectionNr < this.sections.length-1) {
				nextTab=this.getTabByNr(this.currentSectionNr+1);
				this.toggle(nextTab);
			}
		}

		this.prevTab = function() {
			if (this.currentSectionNr >0) {
				prevTab=this.getTabByNr(this.currentSectionNr-1);
				this.toggle(prevTab);
			}
		}

		// Init Values

                this.oldtab=inittab;
		this.currentSectionNr= this.getTabByName(inittab);

	}

	function switchto(tab){
		document.getElementById('active_tab').value = tab;
		section_tabs.toggle(tab);
	}


</script>	
{/literal}
{if $includes_subtabs}
{literal}
<script language="JavaScript" type="text/javascript">
    var _global = this;
    
    function subswitchto(tab){
        var parent_tab = document.{/literal}{$form->getName()}{literal}.active_tab.value;
        this[parent_tab + '_section_tabs'].toggle(tab);
    }
</script>
{/literal}
{/if}
