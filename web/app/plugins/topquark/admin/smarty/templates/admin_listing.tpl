{* Smarty *}
{if $ObjectListWidth != ""}<div style='width:{$ObjectListWidth};margin:auto'>{/if}
{if $ObjectList}
{$ObjectListPageNavigation}
<table width=100% class="ObjectListTable widefat fixed" cellpadding="{$ObjectListCellPadding}" align="{if $ObjectListAlign != ""}{$ObjectListAlign}{else}center{/if}">
	<thead>
		<tr>
{foreach from=$ObjectListHeader item=Column}
			<th scope="col" class="" style="" width="{$Column.Width}">{$Column.Data}</th>
{/foreach}
		</tr>
	</thead>
	<tbody>
{foreach from=$ObjectList item=Object}
	<tr class="{cycle values=",alternate"}">
	{foreach from=$Object item=Column}
	{if is_array($Column.Data)}
		<td width='{$Column.Width}' {$Column.Data.Extras}>{$Column.Data.Data}</td>
	{else}
		<td width='{$Column.Width}' valign=top>{$Column.Data}</td>
	{/if}
	{/foreach}
	</tr>
{/foreach}
	</tbody>
</table>
{else}
{if $ObjectEmptyString != ""}{$ObjectEmptyString}
{else}
	Nothing to display!
{/if}
{/if}
{if $ObjectListWidth != ""}</div>{/if}
