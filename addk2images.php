<?php
header('X-Accel-Buffering: no'); //turn off NGINX buffering
header( 'Content-type: text/plain; charset=utf-8' ); //send textonly
ini_set('display_errors', 1); //show what's wrong
ini_set('display_startup_errors', 1); //show what's wrong
ini_set('output_buffering',0); //try to send output as soon as possible
set_time_limit(300); //run for 5 minutes
error_reporting(E_ALL); //report all errors
ob_start(); //so we can push results per image;

//get Joomla Configuration
$webroot = __DIR__ . '/';
include('configuration.php');
$j = new JConfig;
$dbtype = 'mysqli';
$host = $j->host;
$user = $j->user;
$password = $j->password;
$db = $j->db;
$prefix = $j->dbprefix;

//get K2 image sizes
$db = new MySQLi($host,$user,$password,$db);
$sql = 'SELECT `params` FROM `' . $prefix . 'extensions` WHERE `name` = "COM_K2";';
$stmt = $db->prepare($sql);
$stmt->execute();
$stmt->bind_result($params);
$stmt->store_result();
$stmt->fetch();
$stmt->close();
$params = json_decode($params,true);
$imagesizes = array('Generic'=>$params['itemImageGeneric'],'L'=>$params['itemImageL'],'M'=>$params['itemImageM'],'S'=>$params['itemImageS'],'XL'=>$params['itemImageXL'],'XS'=>$params['itemImageXS']);
print_r($imagesizes);

//get Joomla articles
$sql = 'select `id`,`title`,`images` from `' . $prefix . 'content`';
$stmt=$db->prepare($sql);
$stmt->execute();
$stmt->bind_result($id,$title,$imgjson);
$stmt->store_result();

//process each article
while($stmt->fetch()) {
	$imgarray = json_decode($imgjson,true);
	$img = '';
	print $title;
	if($imgarray['image_intro']<>'') $img = $webroot . $imgarray['image_intro'];
	if($img<>'' && file_exists($img)) {
		print PHP_EOL . $img;
		$ext = substr($img,-4);
		$newfile = md5('Image'.$id); //the name of the new src image
		$newimg = $webroot.'media/k2/items/src/'.$newfile.$ext;
		
		//copy image to K2 src folder
		if(copy($img,$newimg)) {
			print PHP_EOL . 'COPIED!';	
				print PHP_EOL . $newimg;
				if(strtolower($ext)=='.jpg') {
					$source = imagecreatefromjpeg($newimg);
				} elseif (strtolower($ext)=='.png') {
					$source = imagecreatefrompng($newimg);
				} elseif (strtolower($ext)=='.gif') {
					$source = imagecreatefromgif($newimg);
				}
				
				list($width, $height) = getimagesize($newimg);
			
			//prepare K2 cache folder
			foreach($imagesizes as $variant => $varwidth) {
				$newsize = $webroot.'media/k2/items/cache/'.$newfile.'_'.$variant.$ext;
				print PHP_EOL . $newsize;
				$ratio = $width/$height;
				$newwidth = $varwidth;
				$newheight = round($newwidth/$ratio,0);
				$destination = imagecreatetruecolor($newwidth, $newheight);
				imagecopyresampled($destination, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
				if(strtolower($ext)=='.jpg') {
					imagejpeg($destination,$newsize);
				} elseif (strtolower($ext)=='.png') {
					imagepng($destination,$newsize);
				} elseif (strtolower($ext)=='.png') {
					imagegif($destination,$newsize);
				}
			}
		} 
		else print PHP_EOL . '!!!ERROR!!!';
		flush();
		ob_flush();
	} else print PHP_EOL . 'NO IMAGE';
	print PHP_EOL . PHP_EOL;
}
$stmt->close();
$db->close();
print PHP_EOL . '**************DONE**************';
ob_end_flush();
?>
