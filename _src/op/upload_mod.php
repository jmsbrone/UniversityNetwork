<?php
$maxFileSize=25600;
$randomName=generateName();
$basePath='S:/servers/universityNetwork/v1.0/_dev/root/'; //указать абсолютный путь без папки принадлежности в виде C:/abc/
switch ($data['type']){
	case "add":
		$classID = $data['classID'];
        foreach($_FILES as $key => $file){
            $size=round($file["size"] / 1024);
            
            if($size > $maxFileSize || $size < 1){
                throw403();
            }
            //checkFormat($file['name'], $file["tmp_name"]);
			$format = end(explode(".", strtolower($file['name'])));
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
			if ($allow[$format]!=finfo_file($finfo, $file["tmp_name"]))  {
				throw403();
			}
            $format = end(explode(".", strtolower($file["name"])));
            
			$chars = 'abcdefghikjlmnopqrstwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
			$numChars = strlen($chars);
			$string = '';
			for ($i = 0; $i < 16; $i++) {
				$randomNameWE .= substr($chars, rand(1, $numChars) - 1, 1);
				}
            $randomName = $randomNameWE.".".$format;
            switch($format){
                case 'zip': case 'rar': 
                    $generatedFilePath = $basepath.'archives/'.$randomName;
                    $fileType='archives';
                    move_uploaded_file($file["tmp_name"], $generatedFilePath);
                    break;
                case 'doc': case 'txt': case 'docx': case 'pptx': case 'ppt': case 'djvu': case 'pdf':
                    $generatedFilePath = $basepath.'documents/'.$randomName;
                    $fileType='documents';
                    move_uploaded_file($file["tmp_name"], $generatedFilePath);
                    break;
                case 'jpg': case 'png': case 'bmp':
                    $generatedFilePath = $basePath.'images/'.$randomName;
                    $fileType='images';
                    move_uploaded_file($file["tmp_name"], $generatedFilePath);
                    
                    list($width_orig, $height_orig) = getimagesize($generatedFilePath); 
					if (($format=='jpg') || ($format=='png')) {				
						if ($format=='jpg') 
						{ 
							$image = imagecreatefromjpeg($generatedFilePath);
						}
						if ($format=='png') 
						{ 
							$image = imagecreatefrompng($generatedFilePath);
						}
						$filePath = $basePath.'images/'.$randomNameWE;
						foreach(array(128, 640, 1280) as $cropSize){
							if ($width_orig > $height_orig){
								$width = $cropSize;
								$height = $height_orig * $width / $width_orig;
							} else {
								$height = $cropSize;
								$width = $width_orig * $height / $height_orig;
							}
							if ($format=='jpg') 
							{ 
								$image_p = imagecreatetruecolor($width, $height);
								imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
								imagejpeg($image_p, $filePath.'_'.$cropSize.'.'.$format, 100);
							} 
							if ($format=='png') 
							{ 
								$image_p = imagecreatetruecolor($width, $height);
								imagesavealpha($image_p, true);
								$trans_colour = imagecolorallocatealpha($image_p, 0, 0, 0, 127);
								imagefill($image_p, 0, 0, $trans_colour);
								//$image = imagecreatefrompng($filename);
								imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
								imagepng($image_p,$filePath.'_'.$cropSize.'.'.$format);
							}  
                        imagedestroy($image_p);
						}
					}
                    break;
            }
            $query = "
                INSERT INTO `Uploads` (`UploadedBy`, `FileType`, `FileSizeKB`, `FileName`, `FileExtension`)
                    VALUES ($userID,'$fileType',$size,'$randomNameWE','$format');
                -- New upload ID
                SET @uplID = @@IDENTITY;
                -- Album for selected class
                SET @albID=(SELECT Albums_ID FROM AlbumClass WHERE `Classes_ID`=$classID);
                -- Appending to album
                INSERT INTO `AlbumFiles` (`Albums_ID`,`Uploads_ID`,`AddedBy`)
                    VALUES(@albID,@uplID,$userID)";
            runMultiQuery($query);
        }
		break;
			
	case "delete":
		$uploadID = $data['uploadID'];
		$query = "DELETE FROM `uploads` WHERE `uploads`.`ID` = $uploadID;";
		$query1 = "SELECT `UploadedBy` FROM `uploads` WHERE `ID` = $uploadID`"
		if ($result = $mysql->query($query1)) 
		{	$row = $result->fetch_row()
		} 
		else 
		{
			throw403('badquery1')
		}
		if ($row[0]==$userID) {	
		if(!($mysql->query($query))) {
			throw403();
		} 				
		$output = array('id' =>$uploadID);
		}
		break;
   }
?>
