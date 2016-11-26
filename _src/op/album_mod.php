<?php

switch($data['type']){
    case 'add':
        $programID = $data['programID'] ?? -1;
        $name = str_replace("'","\'", $data['name']);
        
        $query = "
            SET @subjectID = (SELECT `subjects_id` FROM `groupsemesterprogram` WHERE `id` = $programID`);
            
            INSERT INTO `albums` (`name`, `createdBy`)
                VALUES ('$name', $userID);
            SET @albumID = @@IDENTITY;
            
            INSERT INTO `subjectAlbums` (`subjects_id`, `albums_id`)
                VALUES (@subjectID, @albumID);
            ";

        runMultiQuery($query);
        $output = array(
            'id' => $mysql->query('SELECT @albumID')->fetch_row()[0]
        );
        break;
    case 'modify':
        $albumID = $data['albumID'] ?? -1;
        $name = str_replace("'","\'", $data['name']);
        
        $query = "
            UPDATE `albums`
            SET `name` = '$name'
            WHERE `id` = `$albumID AND `createdBy` = $userID
            ";
        if (!$mysql->query($query)) throw403('Невозможно редактировать данные. Вы не являетесь автором.');
        $output = array();
        break;
    case 'delete':
        $albumID = $data['albumID'] ?? -1;
        
        $query = "
            DELETE FROM `albums`
                WHERE `id` = $albumID AND `createdBy` = $userID
            ";
        if (!$mysql->query($query)) throw403('Невозможно удалить альбом. Вы не являетесь автором.');
        $output = array();
        break;
}

?>