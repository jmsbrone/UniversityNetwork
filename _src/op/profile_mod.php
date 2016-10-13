<?php

switch($data['type']){
    case 'list':
        if (isset($data['depID'])){
            $depID = checkInt($data['depID']);
            $query = "
            -- Список направлений факультета
            SELECT `id`, `name`, `short` 
            FROM `Profiles` 
            WHERE `Departments_ID` = $depID;";
        } else {
            $query = " -- Список всех направлений
            SELECT `id`, `name`, `short`, `Departments_ID` as `depID`
            FROM `Profiles` 
            ORDER BY `depID`";
        }
        if ($result = $mysql->query($query)){
            $output = array();
            while($row = $result->fetch_assoc()){
                $output[] = $row;
            }
        } else throw403();
        break;
    case 'add':
        $depID = checkInt($data['depID']);
        $name = checkString($data['name']);
        $short = checkString($data['shortName']);
        $query = "
            -- Добавление направления
            INSERT INTO `Profiles` (`Departments_ID`, `Name`, `Short`)
            VALUES ($depID, '$name', '$short');";
        
        if ($mysql->query($query)){
            $output = array('id' => $mysql->insert_id, 'name' => $name, 'short' => $short);
        } else throw403();
        break;
    case 'modify':
        $profileID = checkInt($data['profileID']);
        $name = checkString($data['name']);
        $short = checkString($data['shortName']);
        $query = "
            -- Изменение направления
            UPDATE `Profiles`
            SET `name` = '$name',
            `short` = '$short',
            `modified` = CURRENT_TIMESTAMP
            WHERE `ID` = $profileID;";

        if ($mysql->query($query)){
            $output = array('id' => $mysql->insert_id, 'name' => $name, 'abbr' => $short);
        } else throw403();
        
        break;
    case 'delete':
        $profileID = checkInt($data['profileID']);
        runMultiQuery("
            DELETE FROM `Profiles` WHERE `ID` = $profileID;
            INSERT INTO `DelLog`(`ID`, `Text`) VALUES ($profileID, 'profile')"
        );
        
        $output = array('id' => $profileID);
        break;
}

?>