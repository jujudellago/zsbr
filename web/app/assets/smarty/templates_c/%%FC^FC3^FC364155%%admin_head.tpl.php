<?php /* Smarty version 2.6.11, created on 2018-03-23 01:16:05
         compiled from admin_head.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'get_bloginfo', 'admin_head.tpl', 4, false),array('modifier', 'get_user_option', 'admin_head.tpl', 4, false),)), $this); ?>
<LINK REL=StyleSheet HREF='<?php echo @CMS_INSTALL_URL; ?>
admin/themes/<?php echo $this->_tpl_vars['theme']; ?>
/css/style.css' TYPE='text/css'>
<?php if (@CMS_PLATFORM == 'WordPress'): ?>
<link rel='stylesheet' id='colors-css'  href="<?php echo ((is_array($_tmp='wpurl')) ? $this->_run_mod_handler('get_bloginfo', true, $_tmp) : get_bloginfo($_tmp)); ?>
/wp-admin/css/colors-<?php echo ((is_array($_tmp='admin_color')) ? $this->_run_mod_handler('get_user_option', true, $_tmp) : get_user_option($_tmp)); ?>
.css?ver=20100610" type='text/css' media='all' />
<script type="text/javascript" src="<?php echo ((is_array($_tmp='wpurl')) ? $this->_run_mod_handler('get_bloginfo', true, $_tmp) : get_bloginfo($_tmp)); ?>
/wp-content/markitup/jquery.markitup.js"></script>
<script type="text/javascript" src="<?php echo ((is_array($_tmp='wpurl')) ? $this->_run_mod_handler('get_bloginfo', true, $_tmp) : get_bloginfo($_tmp)); ?>
/wp-content/markitup/sets/html/set.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo ((is_array($_tmp='wpurl')) ? $this->_run_mod_handler('get_bloginfo', true, $_tmp) : get_bloginfo($_tmp)); ?>
/wp-content/markitup/skins/markitup/style.css" />
<link rel="stylesheet" type="text/css" href="<?php echo ((is_array($_tmp='wpurl')) ? $this->_run_mod_handler('get_bloginfo', true, $_tmp) : get_bloginfo($_tmp)); ?>
/wp-content/markitup/sets/html/style.css" />
<?php endif; ?>
<title><?php echo $this->_tpl_vars['title']; ?>
</title>
<?php if ($this->_tpl_vars['includes_tabbed_form']): ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin_head_TabbedForm.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>
<?php if ($this->_tpl_vars['admin_head_extras']): ?>
<?php echo $this->_tpl_vars['admin_head_extras']; ?>

<?php endif; ?>
<?php if ($this->_tpl_vars['admin_start_function']): ?>
<script language="JavaScript" type="text/javascript">
jQuery(document).ready(function(){
	<?php $_from = $this->_tpl_vars['admin_start_function']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['line']):
?>
	<?php echo $this->_tpl_vars['line']; ?>

	<?php endforeach; endif; unset($_from); ?>
});
</script>
<?php endif; ?>
<div id="tqp_admin_wrapper">
<?php if ($this->_tpl_vars['hide_navigation']): ?>
<?php else: ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin_nav.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>