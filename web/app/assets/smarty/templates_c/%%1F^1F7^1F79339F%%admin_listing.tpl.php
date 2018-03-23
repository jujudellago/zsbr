<?php /* Smarty version 2.6.11, created on 2018-03-23 01:16:12
         compiled from admin_listing.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'admin_listing.tpl', 15, false),)), $this); ?>
<?php if ($this->_tpl_vars['ObjectListWidth'] != ""): ?><div style='width:<?php echo $this->_tpl_vars['ObjectListWidth']; ?>
;margin:auto'><?php endif; ?>
<?php if ($this->_tpl_vars['ObjectList']): ?>
<?php echo $this->_tpl_vars['ObjectListPageNavigation']; ?>

<table width=100% class="ObjectListTable widefat fixed" cellpadding="<?php echo $this->_tpl_vars['ObjectListCellPadding']; ?>
" align="<?php if ($this->_tpl_vars['ObjectListAlign'] != ""):  echo $this->_tpl_vars['ObjectListAlign'];  else: ?>center<?php endif; ?>">
	<thead>
		<tr>
<?php $_from = $this->_tpl_vars['ObjectListHeader']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['Column']):
?>
			<th scope="col" class="" style="" width="<?php echo $this->_tpl_vars['Column']['Width']; ?>
"><?php echo $this->_tpl_vars['Column']['Data']; ?>
</th>
<?php endforeach; endif; unset($_from); ?>
		</tr>
	</thead>
	<tbody>
<?php $_from = $this->_tpl_vars['ObjectList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['Object']):
?>
	<tr class="<?php echo smarty_function_cycle(array('values' => ",alternate"), $this);?>
">
	<?php $_from = $this->_tpl_vars['Object']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['Column']):
?>
	<?php if (is_array ( $this->_tpl_vars['Column']['Data'] )): ?>
		<td width='<?php echo $this->_tpl_vars['Column']['Width']; ?>
' <?php echo $this->_tpl_vars['Column']['Data']['Extras']; ?>
><?php echo $this->_tpl_vars['Column']['Data']['Data']; ?>
</td>
	<?php else: ?>
		<td width='<?php echo $this->_tpl_vars['Column']['Width']; ?>
' valign=top><?php echo $this->_tpl_vars['Column']['Data']; ?>
</td>
	<?php endif; ?>
	<?php endforeach; endif; unset($_from); ?>
	</tr>
<?php endforeach; endif; unset($_from); ?>
	</tbody>
</table>
<?php else: ?>
<?php if ($this->_tpl_vars['ObjectEmptyString'] != ""):  echo $this->_tpl_vars['ObjectEmptyString']; ?>

<?php else: ?>
	Nothing to display!
<?php endif; ?>
<?php endif; ?>
<?php if ($this->_tpl_vars['ObjectListWidth'] != ""): ?></div><?php endif; ?>