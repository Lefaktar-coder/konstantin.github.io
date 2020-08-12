<?php
error_reporting(85);

$p_file = $_GET['file'];
$p_width = (int)$_GET['width'];

preg_match("/(jpg|png)$/ui", $p_file, $pm);
$p_ext = str_replace("jpg", "jpeg", strtolower($pm[1]));

$p_cache = "cache/".md5(filesize("images/".$p_file).filemtime("images/".$p_file))."_".$p_width."_".$p_file;
if(trim($_SERVER['HTTP_IF_MODIFIED_SINCE'])==gmdate("D, d M Y H:i:s", filemtime($p_cache))." GMT"){header($_SERVER['SERVER_PROTOCOL']." 304 Not Modified"); exit;}


if($p_ext&&file_exists("images/".$p_file)){
	if(!file_exists($p_cache)){mkdir("cache/");
		if($p_ext=="jpeg"){$img = imagecreatefromjpeg("images/".$p_file);}
		if($p_ext=="png"){$img = imagecreatefrompng("images/".$p_file);}
		if(!$img){$img = imagecreatefromjpeg("images/".$p_file);}
		if(!$img){$img = imagecreatefrompng("images/".$p_file);}
		
		$img_w = imagesx($img); $img_h = imagesy($img);
		if(!$p_width||$p_width>$img_w){$p_width = $img_w;}
		
		$img2_wh = $p_width/$img_w;
		$img2_w = round($img2_wh*$img_w);
		$img2_h = round($img2_wh*$img_h);
		
		$img2 = imagecreatetruecolor($img2_w, $img2_h); imagealphablending($img2, false); imagesavealpha($img2, true);
		imagecopyresampled($img2, $img, 0, 0, 0, 0, $img2_w, $img2_h, $img_w, $img_h);
		
		if($p_ext=="jpeg"){imagejpeg($img2, $p_cache, 75);}
		if($p_ext=="png"){imagepng($img2, $p_cache, 9);}
		
		imagedestroy($img); imagedestroy($img2);
	}
	
	ob_end_clean(); ob_start();
	header_remove("Transfer-Encoding");
	header("Content-Type: image/".$p_ext);
	header("Content-Length: ".filesize($p_cache));
	header("Expires: ".gmdate("D, d M Y H:i:s", time()+86400)." GMT");
	header("Last-Modified: ".gmdate("D, d M Y H:i:s", filemtime($p_cache))." GMT");
	
	readfile($p_cache); exit;
}else{header($_SERVER['SERVER_PROTOCOL']." 404 Not Found");}
?>