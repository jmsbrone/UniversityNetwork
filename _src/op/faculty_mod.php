<?php

switch($data['type']){
    case 'list':
        $query = "
            -- Получение списка факультетов
            SELECT `ID`, `Name` FROM `Faculties`";
        if ($update) $query .= " WHERE UNIX_TIMESTAMP(`Modified`) > $time";
        if ($result = $mysql->query($query)) {
            $output = array();
            while($row = $result->fetch_row()){
                $output[] = array(
                    'id' => $row[0],
                    'name' => $row[1]
                );
            }
        } else throw403();
        break;
    case 'add':
        $name = checkString($data['name']);
        $query = "
            -- Добавление факультета
            INSERT INTO `Faculties` (`Name`)
            VALUES ('$name');";
        
        if ($mysql->query($query)){
            $output = array('id' => $mysql->insert_id, 'name' => $name);
        } else throw403();
        break;
    case 'modify':
        $facultyID = checkInt($data['facultyID']);
        $name = checkString($data['name']);
        $query = "-- Изменение факультета
            UPDATE `Faculties`
            SET `Name` = '$name',
            `Modified` = CURRENT_TIMESTAMP
            WHERE `ID` = $facultyID;";
        
        if ($mysql->query($query)){
            $output = array('id' => $facultyID, 'name' => $name);
        } else throw403();
        break;
    case 'delete':
        $facultyID = checkInt($data['facultyID']);
        $query = "-- Удаление факультета
            DELETE FROM `Faculties`
            WHERE `ID` = $facultyID;";        
        if (!$mysql->query($query)) throw403();
        
        // Output
        $output = array('id' => $facultyID);
        
        $query = "INSERT INTO `DelLog`(`ID`, `Text`) VALUES ($facultyID, 'faculty')";
        if (!$mysql->query($query)) throw403();
        break;
}
?>