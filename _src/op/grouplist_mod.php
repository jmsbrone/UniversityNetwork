<?php

switch($data['type']){
    case 'list':
        if ($accountType == 'manager'){
            $groupID = checkInt($data['groupID']);
            $query = "
                -- Выбор списка группы ( с приглашениями)
                SELECT `Students`.`Accounts_ID`, `Surname`, `Name`, `Lastname`, `Hash`
                FROM `Students` INNER JOIN `Invites` ON `Invites`.`Accounts_ID` = `Students`.`Accounts_ID`
                WHERE `Groups_ID` = $groupID;";
            
            if ($result = $mysql->query($query)){
                $output = array();
                while($row = $result->fetch_row()){
                    $output[] = array(
                        'id' => $row[0],
                        'surname' => $row[1],
                        'name' => $row[2],
                        'lastname' => $row[3],
                        'hash' => $row[4]
                    );
                }
            } else throw403();
        } else {
            $query = "
                -- Выбор списка группы
                SELECT `Accounts_ID`, `Surname`, `Name`, `Lastname`
                FROM `Students`
                WHERE `Groups_ID` = $groupID;";

            if ($result = $mysql->query($query)){
                $output = array();
                while($row = $result->fetch_row()){
                    $output[] = array(
                        'id' => $row[0],
                        'surname' => $row[1],
                        'name' => $row[2],
                        'lastname' => $row[3]
                    );
                }
            } else throw403();
        }
        break;
    case 'add':
        $groupID = checkInt($data['groupID']);
        $surname = checkString($data['surname']);
        $name = checkString($data['name']);
        $lastname = checkString($data['surname']);
        $hash = generateHash();
        $query = "
            -- Создание аккаунта
            INSERT INTO `Accounts` (`AccountType`)
            VALUES ('student');
            SET @AccountID = @@IDENTITY;

            -- Создание приглашения
            INSERT INTO `Invites` (`Accounts_ID`, `Hash`)
            VALUES (@AccountID, '$hash');

            -- Создание студента
            INSERT INTO `Students` (`Accounts_ID`, `Surname`, `Name`, `Lastname`, `Groups_ID`, `AddedBy`)
            VALUES (@AccountID, '$surname', '$name', '$lastname', $groupID, $userID);";
        
        runMultiQuery($query);
        if ($result = $mysql->query("SELECT @AccountID;")){
            $output = array(
                'id' => $result->fetch_row()[0], 
                'groupID' => $groupID,
                'surname' => $surname,
                'name' => $name,
                'lastname' => $lastname,
                'hash' => $hash
            );
        } else throw403();
        break;
    case 'modify':
        $studentID = checkInt($data['studentID']);
        $surname = checkString($data['surname']);
        $name = checkString($data['name']);
        $lastname = checkString($data['surname']);
        
        $query = "
            -- Изменение данных студента
            UPDATE `Students`
            SET `Surname` = '$surname',
            `Name` = '$name',
            `Lastname` = '$lastname',
            `Modified` = CURRENT_TIMESTAMP
            WHERE `Accounts_ID` = $studentID";
        
        if ($mysql->query($query)){
            $output = array(
                'studentID' => $studentID,
                'surname' => $surname,
                'name' => $name,
                'lastname' => $lastname
            );
        } else throw403();
        break;
    case 'delete':
        $studentID = checkInt($data['studentID']);
        $query = "/*Удаление студента */ DELETE FROM `Students` WHERE `Accounts_ID` = $studentID";
        if ($mysql->query($query)){
            $output = array('studentID' => $studentID);
        } else throw403();
        break;
    case 'pr_change':
        $studentID = checkInt($data['studentID']);
        $groupID = checkInt($data['groupID']);
        
        $query = "
            -- Изменение старосты
            UPDATE `Groups`
            SET `PresidentID` = $studentID,
            `Modified` = CURRENT_TIMESTAMP
            WHERE `ID` = $groupID";
        
        if ($mysql->query($query)){
            $output = array('status' => true);
        } else throw403();
        break;
}

?>