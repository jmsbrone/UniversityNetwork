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
			'zip' => 'application/x-zip',
		       'pdf' =>  'application/pdf',
			'djvu' => 'image/x.djvu'
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
			if (($format=='doc') || ($format=='txt')||($format=='docx') || ($format=='pptx') || ($format=='ppt') || ($format='pdf') || ($format='djvu')) {
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
				SET @uplID = @@IDENTITY;
				
				SET @albID=(SELECT AlbumsID FROM AlbumClass WHERE `ClassesID`=$ID);
				
				
				
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
	case "listsub":
		$subjID=$data['subjectID'];
		$query = "-- Получение списка альбомов для предмета
			SELECT * FROM `albums` 
			WHERE `id` IN (SELECT `Albums_ID` FROM `SubjectAlbums` WHERE `Subjects_ID` LIKE $subjID;)";
		if ($result = $mysql->query($query)) {	
			$output = array();
			while ($row = $result->fetch_row())  {
				$output[] = array(
				'albumID' => $row[0], 
				'name' => $row[1]
				);						
			};					
		} 	
		else {
				throw403();
		} 
		break;	
	case "listlesson":
		$query = "-- SELECT * FROM `albums` 
					WHERE `id` IN (SELECT `albums_id` FROM `subjectAlbums` WHERE `subjects_id` = $subjectID) 
					OR `id` IN (SELECT `albums_id` FROM `albumClass`
					WHERE `classes_id` 
					IN (SELECT `classes`.`id` FROM `classes` 
					INNER JOIN `classRules` ON `classes`.`rules_id` = `classRules`.`id` 
					WHERE `classRules`.`subjects_id` = $subjectID));";
		if ($result = $mysql->query($query)) {	
			$output = array();
			while ($row = $result->fetch_row())  {
				$output[] = array(
				'albumID' => $row[0], 
				'name' => $row[1]
				);						
			};					
		} 	
		else {
				throw403();
		} 
		break;
	case "list":
		$albID = $data['albumID'];
		$query = "-- Получение списка всех документов альбома
				SELECT `Uploads_ID` FROM `AlbumFiles` WHERE `Albums_ID` LIKE $albID;";
		if ($result = $mysql->query($query)) {	
			$outputing = array();
			while ($row = $result->fetch_row())  {
				$outputing[] = array(
				'uplID' => $row[0], 
				);
			};					
		} 	
		else {
				throw403();
		}
		
		$documents = array();
		$images = array();
		$archives = array();
		for ($i = 0; $i < count($outputing); $i = $i + 1) {
			query = "-- SELECT `FileType`,`FileSize`,`FileName`,`FileExtension` FROM `Uploads` WHERE `ID` LIKE $outputing[i]['uplID'];";
			if ($result1 = $mysql->query($query)) {	
				$result = array();
				while ($row = $result1->fetch_row())  {
					$result[] = array(
					'FileType' => $row[0], 
					'FileSize' => $row[1], 
					'FileName' => $row[2], 
					'FileExtension' => $row[3]
					);
				};
				switch ($result[i]['FileType']){
					case "archives":	
						$archives[] = array(
						'uplID' => $outputing[i]['uplID'],
						'FileSize' => $result[i]['FileType'],
						'FileName' =>$result[i]['FileName'],
						'FileExtension' => $result[i]['FileExtension']
						)
					break;
					case "images":
						$images[] = array(
						'uplID' => $outputing[i]['uplID'],
						'FileSize' => $result[i]['FileType'],
						'FileName' =>$result[i]['FileName'],
						'FileExtension' => $result[i]['FileExtension']
						);
					break;
					case "documents":
						$documents[] = array(
						'uplID' => $outputing[i]['uplID'],
						'FileSize' => $result[i]['FileType'],
						'FileName' =>$result[i]['FileName'],
						'FileExtension' => $result[i]['FileExtension']
						);
					break;
				}
			}
			else {
					throw403();
			} 
		$output = array ('archives' => $archives, 'documents' => $documents, 'images' => $images) 
  	 	}
   	break;
		
}
?>
