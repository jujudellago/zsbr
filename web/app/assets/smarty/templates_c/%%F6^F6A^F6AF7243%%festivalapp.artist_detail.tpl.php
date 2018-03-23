<?php /* Smarty version 2.6.11, created on 2018-03-23 02:27:09
         compiled from festivalapp.artist_detail.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'vocabulary', 'festivalapp.artist_detail.tpl', 6, false),array('modifier', 'is_array', 'festivalapp.artist_detail.tpl', 40, false),array('modifier', 'count', 'festivalapp.artist_detail.tpl', 40, false),array('modifier', 'pluralize', 'festivalapp.artist_detail.tpl', 78, false),array('modifier', 'cat', 'festivalapp.artist_detail.tpl', 92, false),)), $this); ?>
<?php echo $this->_tpl_vars['Artist']->parameterizeAssociatedMedia(); ?>

<div class="artist-detail">
	<h2><?php echo $this->_tpl_vars['Artist']->getParameter('ArtistFullName'); ?>
</h2>
	<?php if ($this->_tpl_vars['Artist']->getParameter('ArtistShows') != ''): ?>
		<div class="simple-info schedule_box">
			<strong><?php echo ((is_array($_tmp='Artist')) ? $this->_run_mod_handler('vocabulary', true, $_tmp) : vocabulary($_tmp)); ?>
 Schedule</strong>
			<div class="artist-shows"><?php echo $this->_tpl_vars['Artist']->getParameter('ArtistShows'); ?>
</div>
		</div>
	<?php endif; ?>
	<?php $this->assign('Images', $this->_tpl_vars['Artist']->getParameter('ArtistAssociatedImages')); ?>
	<?php $this->assign('MP3s', $this->_tpl_vars['Artist']->getParameter('ArtistAssociatedMedia')); ?>
	<?php $this->assign('Thumb', 'Resized'); ?>
	<?php if ($this->_tpl_vars['Images'] != ""): ?>
		<?php $this->assign('Image', $this->_tpl_vars['Images'][0]); ?>
		<img border="0" src="<?php echo $this->_tpl_vars['Image'][$this->_tpl_vars['Thumb']]; ?>
"  class="portfolio-img speaker_bio"/>
	<?php endif; ?>

	<p class="artist-short-description">
			</p>
	<p class="artist-description">
		<?php echo $this->_tpl_vars['Artist']->getParameter('ArtistLongDescription'); ?>

	</p>
	<?php if ($this->_tpl_vars['Artist']->getParameter('ArtistWebsite') != ''): ?>
	<p class="artist-website">
		<?php echo $this->_tpl_vars['DOC_BASE']; ?>

		<ul class="slinks">
		<li class="pro"><a href="<?php echo $this->_tpl_vars['Artist']->getParameter('ArtistWebsiteURL'); ?>
" ><?php echo $this->_tpl_vars['Artist']->getParameter('ArtistWebsite'); ?>
</a></li>
		</ul>
	</p>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['Artist']->getParameter('ArtistVideo') != ''): ?>
	<div class="horizontal-line"></div>
	<h3>Interview</h3>
	<p class="artist-video">
		<iframe width="620" height="320" src="http://www.youtube.com/embed/<?php echo $this->_tpl_vars['Artist']->getParameter('ArtistVideo'); ?>
" frameborder="0" allowfullscreen></iframe>
	</p>
	<?php endif; ?>
	
	<?php if (((is_array($_tmp=$this->_tpl_vars['MP3s'])) ? $this->_run_mod_handler('is_array', true, $_tmp) : is_array($_tmp)) && count($this->_tpl_vars['MP3s'])): ?>
	    <p style='margin-bottom:0px;text-align:center'><b>Listen to music by <?php echo $this->_tpl_vars['Artist']->getParameter('ArtistFullName'); ?>
</b></p>
	    <div align='center'>
	        <!-- ********************************************************************************************************** -->
	        <!-- *  FLAM PLAYER BLOCK                                                                                     * -->
	        <!-- ********************************************************************************************************** -->
	        <object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000'
	        	codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0'
	        	width='300' 
	        	height='125'>
	        		<param name=movie value='<?php echo $this->_tpl_vars['flamPackage']->getPackageURL(); ?>
flam-player-npl.swf'>
	        		<param name=flashVars value='fp_root_url=<?php echo $this->_tpl_vars['flamPackage']->getPackageURL(); ?>
&ovr_color=0x<?php echo $this->_tpl_vars['package']->flamplayer_colour; ?>
&ovr_langage=en&ovr_playlist=default_playlist&ovr_author=<?php echo $this->_tpl_vars['Artist']->getParameter('ArtistID'); ?>
&ovr_order=date_music&ovr_order_direction=DESC&ovr_autoplay=0&ovr_loop_playlist=1&ovr_loop_tracks=0&ovr_shuffle=0'>
	        		<param name=menu value=false>
	        		<param name=quality value=best>
	        		<param name=wmode value=transparent>
	        		<param name=bgcolor value=#FFFFFF>

	        	<embed src='<?php echo $this->_tpl_vars['flamPackage']->getPackageURL(); ?>
flam-player-npl.swf'
	        		flashVars='fp_root_url=<?php echo $this->_tpl_vars['flamPackage']->getPackageURL(); ?>
&ovr_color=0x<?php echo $this->_tpl_vars['package']->flamplayer_colour; ?>
&ovr_langage=en&ovr_playlist=default_playlist&ovr_author=<?php echo $this->_tpl_vars['Artist']->getParameter('ArtistID'); ?>
&ovr_order=date_music&ovr_order_direction=DESC&ovr_autoplay=0&ovr_loop_playlist=1&ovr_loop_tracks=0&ovr_shuffle=0'
	        		menu=false
	        		quality=best
	        		wmode=transparent
	        		bgcolor=#FFFFFF
	        		width='300'
	        		height='125'
	        		type='application/x-shockwave-flash'
	        		pluginspage='http://www.macromedia.com/go/getflashplayer'>
	        	</embed>
	        </object>
	        <!-- ********************************************************************************************************** -->
	        <!-- *  FLAM PLAYER BLOCK END                                                                                 * -->
	        <!-- ********************************************************************************************************** -->
	        <p style='text-align:center'><a href="#" onclick="openPopup('<?php echo $this->_tpl_vars['flamPackage']->getPackageURL(); ?>
admin/return_player.php?fp_style=flam-player&fp_root_url=<?php echo $this->_tpl_vars['flamPackage']->getPackageURL(); ?>
&ovr_color=0x1a6018&ovr_langage=en&ovr_playlist=default_playlist&ovr_author=<?php echo $this->_tpl_vars['Artist']->getParameter('ArtistID'); ?>
&ovr_order=date_music&ovr_order_direction=DESC&ovr_autoplay=1&ovr_loop_playlist=1&ovr_loop_tracks=0&ovr_shuffle=0&width=300&height=315','flamPlayer','300','315');">Open player in new window</a> and listen while you browse</p>
	    </div>
	<?php endif; ?>

	<?php if (is_array($this->_tpl_vars['Artist']->getParameter('BandMemberShows')) && count($this->_tpl_vars['Artist']->getParameter('BandMemberShows'))): ?>
		<div class="artist-shows">
			<p>Members of <?php echo $this->_tpl_vars['Artist']->getParameter('ArtistFullName'); ?>
 appearing in <?php echo ((is_array($_tmp='Show')) ? $this->_run_mod_handler('pluralize', true, $_tmp) : pluralize($_tmp)); ?>
 of their own</p>
			<?php $_from = $this->_tpl_vars['Artist']->getParameter('BandMemberShows'); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['BandMemberShows']):
?>
				<?php echo $this->_tpl_vars['BandMemberShows']; ?>

			<?php endforeach; endif; unset($_from); ?>
		</div>
	<?php endif; ?>
	<?php if (is_array($this->_tpl_vars['TaggedImages']) && count($this->_tpl_vars['TaggedImages'])): ?>
		<h3><a href="<?php echo $this->_tpl_vars['TaggedImagesURL']; ?>
">Tagged Images</a></h3>
		<div class="artist-shows">
			<h1>Images of <?php echo $this->_tpl_vars['Artist']->getParameter('ArtistFullName'); ?>
</h1>
            <?php $this->assign('Displaying', 'Images'); ?>
            <?php $this->assign('Thumbnails', $this->_tpl_vars['TaggedImages']); ?>
            <?php $this->assign('ThumbnailsCount', count($this->_tpl_vars['TaggedImages'])); ?>
			<?php $this->assign('gallery_base', @CMS_INSTALL_URL); ?>
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ((is_array($_tmp=$this->_tpl_vars['GalleryDirectory'])) ? $this->_run_mod_handler('cat', true, $_tmp, '/smarty/gallery.thumbnails.tpl') : smarty_modifier_cat($_tmp, '/smarty/gallery.thumbnails.tpl')), 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		</div>
	<?php endif; ?>
</div>
