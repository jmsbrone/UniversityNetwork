<?php

switch($data['type']){
    case 'list':
        if (isset($data['profileID'])){
            $profileID = checkInt($data['profileID']);
            $query = "
            -- Список групп направления
            SELECT `id`, `name`, `year`, `PresidentID` as `presID`
            FROM `Groups` 
            WHERE `Profiles_ID` = $profileID;";
        } else {
            $query = "
            SELECT `id`, `name`, `year`, `PresidentID` as `presID`, `Profiles_ID` as `profileID`
            FROM `Groups`";
            if ($update) $query .=" WHERE UNIX_TIMESTAMP(`Modified`) > $time";
        }
        if ($result = $mysql->query($query)){
            $output = array();
            while($row = $result->fetch_assoc()){
                $output[] = $row;
            }
        } else throw403();
        break;
    case 'program_list':
        $semesterID = checkInt($data['semesterID']);
        $subjectID = checkInt($data['subjectID']);
        
        $result = $mysql->query("
            SELECT `groups`.`id`, `groups`.`name`
            FROM `groups`
            WHERE `id` IN (
                SELECT `groups_id` FROM `groupsemesterprogram` WHERE `semester_id` = $semesterID AND `subjects_id` = $subjectID
            )
        ");
        if (!$result) throw403();
        $output = array();
        while($row = $result->fetch_assoc()) $output[] = $row;
        break;
    case 'add':
        $profileID = checkInt($data['profileID']);
        $name = checkString($data['name']);
        $year = checkString($data['year']);
        
        $query = "
            -- Добавление группы
            INSERT INTO `Groups` (`Profiles_ID`, `Name`, `Year`)
            VALUES ($profileID, '$name', $year);";
        
        if ($mysql->query($query)){
            $output = array('id' => $mysql->insert_id, 'name' => $name);
        } else throw403();
        break;
    case 'modify':
        $groupID = checkInt($data['groupID']);
        $name = checkString($data['name']);
        
        $query = "
            -- Изменение группы
            UPDATE `Groups`
            SET `Name` = '$name',
            `modified` = CURRENT_TIMESTAMP
            WHERE `ID` = $groupID;";

        if ($mysql->query($query)){
            $output = array('id' => $groupID, 'name' => $name);
        } else throw403();
        break;
    case 'delete':
        $groupID = checkInt($data['groupID']);
        
        runMultiQuery("
            -- Удаление группы
            DELETE FROM `Groups` WHERE `ID` = $groupID;
            INSERT INTO `DelLog`(`ID`, `Text`) VALUES ($groupID, 'group')
        ");
        break;
}
?>