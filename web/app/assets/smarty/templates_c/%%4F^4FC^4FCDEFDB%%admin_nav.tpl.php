<?php /* Smarty version 2.6.11, created on 2018-03-23 01:16:05
         compiled from admin_nav.tpl */ ?>
<div id="tqp_admin_nav" class="outlined_box">
	<?php $_from = $this->_tpl_vars['bootstrap']->getAdminBreadcrumb('AdminLeftNav'); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['nav_menu'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['nav_menu']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['menu_item']):
        $this->_foreach['nav_menu']['iteration']++;
?>
		<?php if (($this->_foreach['nav_menu']['iteration'] == $this->_foreach['nav_menu']['total'])): ?>
			<?php $this->assign('current', $this->_tpl_vars['menu_item']); ?>
		<?php endif; ?>
	<?php endforeach; endif; unset($_from); ?>
	<h1 id="tqp_admin_nav_title"><?php if ($this->_tpl_vars['current']['title'] == 'Main Menu'): ?>Top Quark<?php else:  echo $this->_tpl_vars['current']['title'];  endif; ?></h1>
	<div id="tqp_admin_left_nav">
<?php echo '';  $_from = $this->_tpl_vars['bootstrap']->getAdminBreadcrumb('AdminLeftNav'); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['nav_menu'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['nav_menu']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['menu_item']):
        $this->_foreach['nav_menu']['iteration']++;
 echo '';  if (! ($this->_foreach['nav_menu']['iteration'] <= 1)):  echo '&nbsp;|&nbsp;';  endif;  echo '';  if (! ($this->_foreach['nav_menu']['iteration'] == $this->_foreach['nav_menu']['total'])):  echo '<a href=\'';  echo $this->_tpl_vars['redirect_path'];  echo '';  echo $this->_tpl_vars['menu_item']['url'];  echo '\' target="_top">';  else:  echo '<strong>';  endif;  echo '';  echo $this->_tpl_vars['menu_item']['title'];  echo '';  if (! ($this->_foreach['nav_menu']['iteration'] == $this->_foreach['nav_menu']['total'])):  echo '</a>';  else:  echo '</strong>';  endif;  echo '';  if (($this->_foreach['nav_menu']['iteration'] == $this->_foreach['nav_menu']['total'])):  echo '';  $this->assign('current', $this->_tpl_vars['menu_item']);  echo '';  endif;  echo '';  endforeach; endif; unset($_from);  echo ''; ?>
			    
	</div>
<?php echo '<div id="tqp_admin_right_nav">';  $_from = $this->_tpl_vars['bootstrap']->getAdminBreadcrumb('AdminRightNav',true); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['nav_options'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['nav_options']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['menu_item']):
        $this->_foreach['nav_options']['iteration']++;
 echo '';  if (! ($this->_foreach['nav_options']['iteration'] <= 1)):  echo '&nbsp;-&nbsp;';  endif;  echo '<a href=\'';  echo $this->_tpl_vars['redirect_path'];  echo '';  echo $this->_tpl_vars['menu_item']['url'];  echo '\'  target="_top">';  echo $this->_tpl_vars['menu_item']['title'];  echo '</a>';  endforeach; endif; unset($_from);  echo ''; ?>
			    
	</div>
</div>