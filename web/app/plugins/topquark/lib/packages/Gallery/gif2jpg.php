<?php

    include_once(dirname(__FILE__)."/../../Standard.php");
    $Bootstrap->usePackage('Gallery');

    $source = preg_replace("/^".preg_quote(RELATIVE_BASE_URL,"/")."/",DOC_BASE,$_GET['src']);

    $imageError = NULL;
    $imageData = getimagesize($source);
    $width = $imageData[0];
    $height = $imageData[1];
    $imgtype = $imageData[2];
    if (! ($imgtype==1 || $imgtype==2 || $imgtype==3) ) $imageError = TRUE;
    if ($width < $max_width) $imageError = TRUE;
    if ($height < $max_height) $imageError = TRUE;
    if (empty($imageError)) {
		list($wd, $ht, $tp, $at)=getimagesize($source);
        $img_src = imagecreatefromstring(file_get_contents($source));
 		$img_dst=imagecreatetruecolor($wd,$ht);        

		if ($_GET['bg'] != ""){
			$clr = rgb2hex2rgb($_GET['bg']);
		}
		if (!is_array($clr)){
			$clr['red']=255;
			$clr['green']=255;
			$clr['blue']=255;
		}
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', time()).' GMT');
        header('Content-Type: image/jpeg');

		$kek=imagecolorallocate($img_dst,$clr['red'],$clr['green'],$clr['blue']);
		imagefill($img_dst,0,0,$kek);
		imagecopyresampled($img_dst, $img_src, 0, 0, 0, 0, $wd, $ht, $wd, $ht);
        imagejpeg ($img_dst);
        imagedestroy($img_src);
        imagedestroy($img_dst);
    }

	function rgb2hex2rgb($c){
	   if(!$c) return false;
	   $c = trim($c);
	   $out = false;
	  if(preg_match("/^[0-9ABCDEFabcdef\#]+$/i", $c)){
	      $c = str_replace('#','', $c);
	      $l = strlen($c) == 3 ? 1 : (strlen($c) == 6 ? 2 : false);

	      if($l){
	         unset($out);
	         $out[0] = $out['r'] = $out['red'] = hexdec(substr($c, 0,1*$l));
	         $out[1] = $out['g'] = $out['green'] = hexdec(substr($c, 1*$l,1*$l));
	         $out[2] = $out['b'] = $out['blue'] = hexdec(substr($c, 2*$l,1*$l));
	      }else $out = false;

	   }elseif (preg_match("/^[0-9]+(,| |.)+[0-9]+(,| |.)+[0-9]+$/i", $c)){
	      $spr = str_replace(array(',',' ','.'), ':', $c);
	      $e = explode(":", $spr);
	      if(count($e) != 3) return false;
	         $out = '#';
	         for($i = 0; $i<3; $i++)
	            $e[$i] = dechex(($e[$i] <= 0)?0:(($e[$i] >= 255)?255:$e[$i]));

	         for($i = 0; $i<3; $i++)
	            $out .= ((strlen($e[$i]) < 2)?'0':'').$e[$i];

	         $out = strtoupper($out);
	   }else $out = false;

	   return $out;
	}
?>