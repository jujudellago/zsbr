{* Smarty *}
<html>
<head>
<LINK REL=StyleSheet HREF='themes/{$theme}/css/style.css' TYPE='text/css'>
<style type="text/css"> 
<!--
 /*IE and NN6x styles, this won't import in Netscape 4.x*/
   @import url('themes/{$theme}/css/style_breaks_netscape.css');
-->   
</style>
<title>{$title}</title>
</head>
<body>
<br>
<center>
<div class='outlined_box'>
<p>{$message}
{include file="admin_form.tpl"}
</div>
Return to <a href='{$return_to_url}'>{$display_url}</a>
</center>
</body>
</html>