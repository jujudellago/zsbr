<table cellpadding=10>
	<tr>
		<td valign="top">
			<img id="edit-image-original" {$resized_info.3} src="{$Image->getGalleryDirectory()}{$Image->getParameter('GalleryImageResized')}" />
		</td>
		<td valign="top">
			<div id="edit-image-preview-wrapper" style="width:{$thumb_info.0}px;height:{$thumb_info.1}px;overflow:hidden;">
				<img {$thumb_info.3} src="{$Image->getGalleryDirectory()}{$Image->getParameter('GalleryImageThumb')}" id="edit-image-thumb" style="display:inline">
				<img src="{$Image->getGalleryDirectory()}{$Image->getParameter('GalleryImageResized')}" id="edit-image-preview" alt="Preview" style="display:none"/>
			</div>
			Thumbnail: To edit, drag mask on bigger image
			<div id="edit-image-save" class="button" style="margin-top:15px;text-align:center">Save</div>
		</td>
	</tr>
</table>
