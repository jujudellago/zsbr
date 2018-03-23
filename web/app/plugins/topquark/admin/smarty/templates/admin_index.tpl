{* Smarty *}{include file="admin_head.tpl"}
{if $display == "MAIN_MENU"}
	<div id="tqp_main_menu">
		{foreach from=$menu_items item=menu_item}
		<div class="tqp_main_menu_item">
			<h3><a href='{$bootstrap->admin_url}{if $bootstrap->admin_url|strpos:'?'}&amp;{else}?{/if}package={$menu_item->package_name}&{$admin_page_parm}={$menu_item->main_menu_page}'>{$menu_item->package_title}</a></h3>
			<div class="tqp_main_menu_item_desc">{$menu_item->package_description}</div>
		</div>
		{/foreach}
	</div>
{/if}
{include file="admin_foot.tpl"}
