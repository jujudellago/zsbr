{* Smarty *}
<div class="tqp_admin_form">
{if $form_attr}
{$form->display($form_attr)}
{else}
{$form->display()}
{/if}
<br>
</div>