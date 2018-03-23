<?php /* Smarty version 2.6.11, created on 2018-03-23 01:16:05
         compiled from admin_index.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'strpos', 'admin_index.tpl', 6, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin_head.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php if ($this->_tpl_vars['display'] == 'MAIN_MENU'): ?>
	<div id="tqp_main_menu">
		<?php $_from = $this->_tpl_vars['menu_items']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['menu_item']):
?>
		<div class="tqp_main_menu_item">
			<h3><a href='<?php echo $this->_tpl_vars['bootstrap']->admin_url;  if (((is_array($_tmp=$this->_tpl_vars['bootstrap']->admin_url)) ? $this->_run_mod_handler('strpos', true, $_tmp, '?') : strpos($_tmp, '?'))): ?>&amp;<?php else: ?>?<?php endif; ?>package=<?php echo $this->_tpl_vars['menu_item']->package_name; ?>
&<?php echo $this->_tpl_vars['admin_page_parm']; ?>
=<?php echo $this->_tpl_vars['menu_item']->main_menu_page; ?>
'><?php echo $this->_tpl_vars['menu_item']->package_title; ?>
</a></h3>
			<div class="tqp_main_menu_item_desc"><?php echo $this->_tpl_vars['menu_item']->package_description; ?>
</div>
		</div>
		<?php endforeach; endif; unset($_from); ?>
	</div>
<?php endif; ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin_foot.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>