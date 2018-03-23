{* Smarty *}
<div id="tqp_admin_nav" class="outlined_box">
	{foreach name=nav_menu from=$bootstrap->getAdminBreadcrumb('AdminLeftNav') item=menu_item}
		{if $smarty.foreach.nav_menu.last}
			{assign var=current value=$menu_item}
		{/if}
	{/foreach}
	<h1 id="tqp_admin_nav_title">{if $current.title eq 'Main Menu'}Top Quark{else}{$current.title}{/if}</h1>
	<div id="tqp_admin_left_nav">
{strip}			    
		{foreach name=nav_menu from=$bootstrap->getAdminBreadcrumb('AdminLeftNav') item=menu_item}
    		{if !$smarty.foreach.nav_menu.first}
    			&nbsp;|&nbsp;
    		{/if}
    		{if !$smarty.foreach.nav_menu.last}
    			<a href='{$redirect_path}{$menu_item.url}' target="_top">
			{else}
				<strong>
    		{/if}
    		{$menu_item.title}
    		{if !$smarty.foreach.nav_menu.last}
    			</a>
			{else}
				</strong>
    		{/if}
    		{if $smarty.foreach.nav_menu.last}
    			{assign var=current value=$menu_item}
    		{/if}
		{/foreach}
{/strip}			    
	</div>
{strip}			    
	<div id="tqp_admin_right_nav">
		{foreach name=nav_options from=$bootstrap->getAdminBreadcrumb('AdminRightNav',true) item=menu_item}
    		{if !$smarty.foreach.nav_options.first}
    			&nbsp;-&nbsp;
    		{/if}
    		<a href='{$redirect_path}{$menu_item.url}'  target="_top">{$menu_item.title}</a>
		{/foreach}
{/strip}			    
	</div>
</div>
