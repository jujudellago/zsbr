{* Smarty *}
<LINK REL=StyleSheet HREF='{$smarty.const.CMS_INSTALL_URL}admin/themes/{$theme}/css/style.css' TYPE='text/css'>
{if $smarty.const.CMS_PLATFORM eq 'WordPress'}
<link rel='stylesheet' id='colors-css'  href="{'wpurl'|get_bloginfo}/wp-admin/css/colors-{'admin_color'|get_user_option}.css?ver=20100610" type='text/css' media='all' />
<script type="text/javascript" src="{'wpurl'|get_bloginfo}/wp-content/markitup/jquery.markitup.js"></script>
<script type="text/javascript" src="{'wpurl'|get_bloginfo}/wp-content/markitup/sets/html/set.js"></script>
<link rel="stylesheet" type="text/css" href="{'wpurl'|get_bloginfo}/wp-content/markitup/skins/markitup/style.css" />
<link rel="stylesheet" type="text/css" href="{'wpurl'|get_bloginfo}/wp-content/markitup/sets/html/style.css" />
{/if}
<title>{$title}</title>
{if $includes_tabbed_form}
{include file="admin_head_TabbedForm.tpl"}
{/if}
{if $admin_head_extras}
{$admin_head_extras}
{/if}
{if $admin_start_function}
<script language="JavaScript" type="text/javascript">
jQuery(document).ready(function(){ldelim}
	{foreach from=$admin_start_function item=line}
	{$line}
	{/foreach}
{rdelim});
</script>
{/if}
<div id="tqp_admin_wrapper">
{if $hide_navigation}
{else}
{include file="admin_nav.tpl"}
{/if}