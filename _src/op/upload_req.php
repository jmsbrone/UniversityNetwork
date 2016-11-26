<?php
# Global settings.
# Root folder for each file type.
$typeToFolder = array(
    'arh' => 'archives',
    'img' => 'images',
    'doc' => 'documents'
);

switch ($data['type']){
	case "subject_albums":
        $programID = $data['programID'];
        $subjectID = $mysql->query("SELECT `subjects_id` FROM `groupSemesterProgram` WHERE `id` = $programID")->fetch_row()[0] ?? -1;

        $output = array();
        $result = $mysql->query("
            SELECT `id` as `id`, `name` as `name`, `modified` as `time` 
            FROM `albums`
            WHERE `id` IN (
                SELECT `albums_id` FROM `subjectAlbums` WHERE `subjects_id` = $subjectID
            )
        ");
        if (!$result) throw403();
        $output['original'] = array();
        while($row = $result->fetch_assoc()){
            $output['original'][] = $row;
        }
        
        $result = $mysql->query("
            SELECT 
                `albums`.`id` as `id`, 
                `albums`.`name` as `name`, 
                `classes`.`startTime` as `time`
            FROM (
                `albums` INNER JOIN `albumClass` 
                ON `albumClass`.`albums_id` = `albums`.`id`)
                    INNER JOIN `classes` ON `classes`.`id` = `albumClass`.`classes_id`
            WHERE `classes`.`id` IN (
                SELECT `id` 
                FROM `classes`
                WHERE `rules_id` IN (
                    SELECT `id` FROM `classRules` WHERE `classRules`.`subjects_id` = $subjectID
                )
            )
        ");
        if (!$result) throw403();
        $output['classAlbums'] = array();
        while($row = $result->fetch_assoc()){
            $output['classAlbums'][] = $row;
        }
		break;
        
    case 'class_files':
        $classID = $data['classID'];
        $result = $mysql->query("
            SELECT `albums_id` FROM `albumClass` WHERE `classes_id` = $classID
        ");
        if (!$result) throw403();
        if ($row = $result->fetch_row()){
            $albumID = $row[0];
        } else throw403();
        
	case 'album_files':
		$albumID = $albumID ?? $data['albumID'];
		
		$query = "
            SELECT 
                `Uploads_ID` as `id`,
                `uploadedBy` as `author`,
                `fileType` as `type`,
                `fileName` as `name`,
                `title` as `title`,
                `fileExtension` as `ext`
            FROM (`albums` INNER JOIN `AlbumFiles` ON `albumFiles`.`albums_id` = `albums`.`id`)
            INNER JOIN `uploads` ON `albumFiles`.`uploads_id` = `uploads`.`id`
            WHERE `Albums_ID` LIKE $albumID
        ";
        $result = $mysql->query($query);
        if (!$result) throw403();
        
        $output = array();
        while($row = $result->fetch_assoc()){
            $row['folder'] = $typeToFolder[$row['type']];
            $output[] = $row;
        }
	break;
}
?>