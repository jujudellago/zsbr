<?php /* Smarty version 2.6.11, created on 2018-03-23 02:29:50
         compiled from festivalapp.floated_lineup.tpl */ ?>
<p style="line-height:40px; float:right; display:none">
<a class="button blue big normal" href="#" title="Search a speaker">Search a speaker</a>
</p>
<div class="clearboth"></div>
<div id="search-speakers" >
<form method="get">
		<div>
			<h4>Search a speaker</h4>	
			<input type="text" value="" name="q" id="q" /><span id="filter-count">Enter text to filter the speakers list 
			</span>
		</div>
	</form>
	<div class="horizontal-line">
		
	</div>
</div>
<ul id="speakers-list"> 	
<?php $_from = $this->_tpl_vars['Lineup']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['ArtistID'] => $this->_tpl_vars['Artist']):
?> 
    <?php echo $this->_tpl_vars['Artist']->parameterizeAssociatedMedia(); ?>
 
    <?php $this->assign('Images', $this->_tpl_vars['Artist']->getParameter('ArtistAssociatedImages')); ?>
    <?php $this->assign('Thumb', 'Thumb'); ?>
[raw]
	<li class="artist-float">
		<span class="artist-content">
		<?php if ($this->_tpl_vars['Images'] != ""): ?>
			<?php $this->assign('Image', $this->_tpl_vars['Images'][0]); ?>
    		<a href="<?php echo $this->_tpl_vars['ArtistURL'];  echo $this->_tpl_vars['ArtistID']; ?>
"><img border="0" src="<?php echo $this->_tpl_vars['Image'][$this->_tpl_vars['Thumb']]; ?>
" /></a>
		<?php endif; ?>
		<span class="artist-name">
			<a href="<?php echo $this->_tpl_vars['ArtistURL'];  echo $this->_tpl_vars['ArtistID']; ?>
"><?php echo $this->_tpl_vars['Artist']->getParameter('ArtistFullName'); ?>
</a>
		</span>
		</span>
	</li>
[/raw]	
<?php endforeach; endif; unset($_from); ?>
</ul>