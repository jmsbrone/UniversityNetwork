<?php

# Global settings.
# Root folder.
$basePath = 'S:/servers/universityNetwork/v1.0/_dev/root/';
# Root folder for each file type.
$typeToFolder = array(
    'arh' => 'archives',
    'img' => 'images',
    'doc' => 'documents'
);
# Sizes of crop boxes.
$cropSizes = array(128, 640, 1280);

switch ($data['type']){
	case "add":
        # Taking input info.
		$classID = $data['classID'];
        
        # Checking available space.
        # Limit (KB) - 5GB.
        $maximumSpace = 5242880;
        $totalUploadedSize = $mysql->query("
            SELECT SUM(`uploads`.`fileSizeKB`) FROM `uploads` WHERE (`uploadedBy`) IN (
                SELECT `accounts_id` FROM `students` WHERE `groups_id` = $groupID
            )
        ")->fetch_row()[0];
        $available_space = $maximumSpace - $totalUploadedSize;
        
        # Settings for uploads.
        $maxFileSize = 25600; // maximum file size (in KB)
        
        # Failure counter.
        $failedUploads = 0;
        
        $output = array('files' => array());
        # Looping through uploaded files.
        foreach($_FILES as $key => $file){
            # File size (in KB).
            $size = round($file["size"] / 1024);
            if($size > $maxFileSize || $size < 1 || $size > $available_space){
                throw403('Недопустимый размер файла');
            }
            
            # Checking format.
			$format = end(explode(".", strtolower($file['name'])));
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$allow = array(	
                'jpg' => 'image/jpeg',
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
			if (!array_key_exists($format, $allow) || $allow[$format] != finfo_file($finfo, $file["tmp_name"])) throw403('Недопустимый формат файла');
            
            # Generating file name.
			$chars = 'abcdefghikjlmnopqrstwxyz1234567890';
			$maxIndex = strlen($chars) - 1;
            $maxLength = 16;
            $randomNameWE = ''; // file name without extension
			for ($i = 0; $i < $maxLength; $i++) {
				$randomNameWE .= $chars[rand(0, $maxIndex)];
            }
            $randomName = $randomNameWE.".".$format; // file name with extension
            
            # Setting type.
            switch($format){
                case 'zip': 
                case 'rar': 
                    $fileType = 'arh';
                    break;
                case 'doc': 
                case 'txt': 
                case 'docx': 
                case 'pptx': 
                case 'ppt': 
                case 'djvu': 
                case 'pdf':
                    $fileType = 'doc';
                    break;
                case 'jpg': 
                case 'jpeg':
                case 'png': 
                case 'bmp':
                    $fileType = 'img';
                    break;
            }
            # Generating path.
            $generatedFilePath = $basePath.$typeToFolder[$fileType].'/'.$randomName;
            
            # Adding upload to DB (without commiting in case of further failure).
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
            runMultiQuery($query, false);
            
            # Trying to move file in corresponding folder.
            if (!move_uploaded_file($file["tmp_name"], $generatedFilePath)){
                # File cannot be moved for some reasons. Rollback DB changes, increment failure counter, then procceed to next upload.
                $failureCount++;
                $mysql->query('ROLLBACK');
                continue;
            }
            
            # Additional work.
            
            if ($fileType == 'img'){
                # Making shadow (lower resolution) copies of images.
                # Getting original resolution.
                list($width_orig, $height_orig) = getimagesize($generatedFilePath);
                # Original resource.
                switch($format){
                    case 'jpg': case 'jpeg':
                        $image = imagecreatefromjpeg($generatedFilePath);
                        break;
                    case 'png':
                        $image = imagecreatefrompng($generatedFilePath);
                        break;
                }
                
                # Full path without extension.
                $filePath = $basePath.'images/'.$randomNameWE;
                
                # Cropping image to fit in said squares.
                foreach($cropSizes as $cropSize){
                    # New resolution.
                    if ($width_orig > $height_orig){
                        $width = $cropSize;
                        $height = $height_orig * $width / $width_orig;
                    } else {
                        $height = $cropSize;
                        $width = $width_orig * $height / $height_orig;
                    }
                    
                    if ($width > $width_orig) {
                        # Making copy instead of exploding image.
                        copy("$filepath.$format", "{$filepath}_$cropSize.$format");
                        continue;
                    }
                    
                    # Empty resource.
                    $image_p = imagecreatetruecolor($width, $height);
                    if ($format == 'png'){
                        imagesavealpha($image_p, true);
                        $trans_colour = imagecolorallocatealpha($image_p, 0, 0, 0, 127);
                        imagefill($image_p, 0, 0, $trans_colour);
                    }
                    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
                    
                    # Trying to make copy.
                    switch($format){
                        case 'jpg': case 'jpeg':
                            $cropSuccess = imagejpeg($image_p, $filePath.'_'.$cropSize.'.'.$format);
                            break;
                        case 'png':
                            $cropSuccess = imagepng($image_p,$filePath.'_'.$cropSize.'.'.$format);
                            break;
                    }
                    
                    # Freeing resources.
                    imagedestroy($image_p);
                    
                    # If making copy failed then revert entire upload progress.
                    if (!$cropSuccess){
                        $failureCount++;
                        $mysql->query('ROLLBACK');
                        # Deleting file.
                        unlink($filePath);
                        break;
                    }
                }
                
                if ($cropSuccess){
                    $mysql->query('COMMIT');
                    $output['files'][] = array(
                        'id' => $mysql->query("SELECT @uplID")->fetch_row()[0],
                        'name' => $randomNameWE,
                        'ext' => $format,
                        'type' => $fileType
                    );
                }
            } else  {
                $mysql->query('COMMIT');
                $output['files'][] = array(
                    'id' => $mysql->query("SELECT @uplID")->fetch_row()[0],
                    'name' => $randomNameWE,
                    'ext' => $format,
                    'type' => $fileType
                );
            }
        }
        $output['failureCount'] = $failureCount;
		break;
			
	case "delete":
        # Taking info.
		$uploadID = $data['uploadID'];
        
        # Checking if user can delete this upload.
        $result = $mysql->query("SELECT `fileName`, `fileExtension`, `fileType` FROM `uploads` WHERE `id` = $uploadID AND `uploadedBy` = $userID");
        if (!$result) throw403('Не удается удалить файл: Ошибка запроса. Если вы видите данное сообщение, обратитесь к администратору.'); //
        
        $uploadInfo = $result->fetch_row();
        if (!$uploadInfo) throw403('Не удается удалить файл: файл не найден либо вы пытаетесь удалить файл, добавленный не вами. Если файл был добавлен вами, обратитесь к администратору.');
        
        # Setting file information.
        list($filename, $format, $filetype) = $uploadInfo;
        
        # Deleting file from DB (without commit).
        runMultiQuery("DELETE FROM `uploads` WHERE `id` = $uploadID", false);
        
        # Deleting file physically.
        $fullPathWE = $basePath.typeToFolder[$fileType].'/'.$filename;
        if (!unlink($fullPathWE.'.'.$format)){
            $mysql->query("ROLLBACK");
            throw403('Не удается удалить файл: файл не найден на диске. Если вы видите данное сообщение, обратитесь к администратору.');
        }
        foreach($cropSizes as $cropSize){
            $path = $fulePathWE.'_'.$cropSize.'.'.$format;
            unlink($path);
        }
        
        $output = array();
		break;
   }
?>
