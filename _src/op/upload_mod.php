<?php
function checkFormat($path){
	$format = end(explode(".", strtolower($path)));
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	$allow = array(	'jpg' => 'image/jpeg',
                    	'png' => 'image/png',
			'bmp' => 'image/x-ms-bmp',
			'doc' => 'application/msword',
                    	'txt' => 'text/plain',
			'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'ppt' => 'application/vnd.ms-powerpoint',
                    	'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'rar' => 'application/x-rar',
			'zip' => 'application/x-zip'
			);
	if (!array_key_exists($format, $allow)) {	
		throw403();
	}				
	if ($allow[$format]!=finfo_file($finfo, $path))  {
		throw403();
	}
return $path;	
}

/*******************************************
	function generateName
	Генерирует имя файла из 16 символов
	*********************************************/
function generateName($length = 16){
	$chars = 'abcdefghikjlmnopqrstwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	$numChars = strlen($chars);
	$string = '';
	for ($i = 0; $i < $length; $i++) {
		$string .= substr($chars, rand(1, $numChars) - 1, 1);
	}
	return $string;
}
/*******************************************
	function resizePicture
	Генерирует имя файла из 16 символов
	$filename - имя оригинала файла
	$size - размер, к которому нужно придти, 640x640, 120x120
	*********************************************/
function resizePicture($filename,$size){
  // задание максимальной ширины и высоты
	$width = $size;
	$height = $size;
	$format = end(explode(".", strtolower($filename)));
	$forma =  explode(".", $filename);
	list($width_orig, $height_orig) = getimagesize($filename);
	if ($width_orig>$width) {
		$ratio_orig = $width_orig/$height_orig;

		if ($width/$height > $ratio_orig) {
			$width = $height*$ratio_orig;
		} 
		else {
			$height = $width/$ratio_orig;
		}
		switch($format) {
			case "jpg":
				$image_p = imagecreatetruecolor($width, $height);
				$image = imagecreatefromjpeg($filename);
				imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
				imagejpeg($image_p,$forma[0]."_".$width.".".$format);	
				break;
			case "png":
				$png = imagecreatetruecolor($width, $height);
				imagesavealpha($png, true);
				$trans_colour = imagecolorallocatealpha($png, 0, 0, 0, 127);
				imagefill($png, 0, 0, $trans_colour);
				$image = imagecreatefrompng($filename);
				imagecopyresampled($png, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
				header("Content-type: image/png");
				imagepng($png,$forma[0]."_".$width.".".$format);
		}
	
		return $forma[0]."_".$width.".".$format;
	} 
	else {
		return $filename;
	}
}
$maxFileSize=28147497671065600; /// 300 МБ или 28147497671065600 байт
$randomName=generateName();
//$basePath=; - указать абсолютный путь без папки принадлежности в виде C:/abc/
switch ($data['type']){
	case "add":
		$ID = $data['classesID'];	
		if(($_FILES["filename"]["size"] > $maxFileSize) || ($_FILES["filename"]["size"] < 1)){
			throw403();
		}
		if(is_uploaded_file($_FILES["filename"]["tmp_name"])){
			$path = checkFormat($_FILES["filename"]["name"]);
			$format = end(explode(".", strtolower($_FILES["filename"]["name"])));
			$randomName=generateName().".".$format;
			$size=$_FILES["filename"]["size"];
			if (($format=='zip') || ($format=='rar')) {
				$randomName=$basepath.'archives/'.$randomName;
				$fileType='archives';
			}
			if (($format=='doc') || ($format=='txt')||($format=='docx') || ($format=='pptx') || ($format=='ppt')) {
				$randomName=$basepath.'documents/'.$randomName;
				$fileType='documents';
			} 
			if (($format=='jpg') || ($format=='png')||($format=='bmp')) {
				$randomName=$basepath.'images/'.$randomName;
				$fileType='images';
			} 
			move_uploaded_file($_FILES["filename"]["tmp_name"], $randomName);
			if (($format=='jpg') || ($format=='png')) {
				$pic128=resizePicture($randomName,128);
				$pic640=resizePicture($randomName,640);
				$pic1280=resizePicture($randomName,1280);
				$size128 = round(filesize($pic128)/1000);
				$size640 = round(filesize($pic640)/1000);
				$size1280 = round(filesize($pic1280)/1000);
			}
		} 
		else {
			throw403();
		}
		$query = "INSERT INTO `Uploads` (`UploadedBy`,`FileType`,`FileSize`,`FileName`,`FileExtension`)
				VALUES ($userID,$fileType,$size,$randomName,$format);
				
				SET @albID=(SELECT AlbumsID FROM AlbumClass WHERE `ClassesID`=$ID);
				
				SET @uplID=SELECT UploadsID FROM Uploads WHERE `FileName`=$randomName;
				
				INSERT INTO `AlbumFiles` (`AlbumsID`,`UploadsID`,`AddedBy`)
				VALUES(@albID,@uplID,$userID)";
		
		runMultiQuery($query);	
		break;
			
	case "delete":
		$uploadID = $data['uploadID'];
		$query = "DELETE FROM `uploads` WHERE `uploads`.`ID` = $uploadID;";
		if(!($mysql->query($query))) {
			throw403();
		} 				
		$output = array('id' =>$uploadID);						
		break;
   }
?>
