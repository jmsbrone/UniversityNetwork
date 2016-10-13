<?php

switch($data['type']){
    case 'add':
        $groupID = checkInt($data['groupID']);
        $surname = checkString($data['surname']);
        $name = checkString($data['name']);
        $lastname = checkString($data['lastname']);
        $hash = generateHash();
        
        $query = "
            -- Создание аккаунта
            INSERT INTO `Accounts` (`AccountType`)
            VALUES (`student`);
            SET @StudentID = @@IDENTITY;

            -- Создание студента
            INSERT INTO `Students` (`Accounts_ID`, `Surname`, `Name`, `Lastname`, `Groups_ID`)
            VALUES (@StudentID, '$surname', '$name', '$lastname', $groupID);

            -- Создание приглашения
            INSERT INTO `Invites` (`Accounts_ID`, `Hash`)
            VALUES (@StudentID, '$hash');";
        
        runMultiQuery($query);
        $output = array(
            'id' => $mysql->query("SELECT @StudentID")->fetch_row()[0],
            'groupID' => $groupID,
            'surname' => $surname,
            'name' => $name,
            'lastname' => $lastname,
            'hash' => $hash
        );
        break;
    case 'register':
        $login = checkString($data['login']);
        $pswHash = password_hash(checkString($data['psw']), PASSWORD_DEFAULT);
        $hash = checkString($data['hash']);
        
        $query = "
            -- Обновление аккаунта
            UPDATE `Accounts`
            SET `Login` = '$login',
            `PswHash` = '$pswHash',
            `RegisterTimestamp` = CURRENT_TIMESTAMP
            WHERE `Accounts`.`ID` = (
                SELECT `Accounts_ID` FROM `Invites` WHERE `Hash` LIKE '$hash'
            );";
        
        if ($mysql->query($query)){
            if ($mysql->affected_rows == 0) throw403();
            $mysql->query("UPDATE `Invites` SET `Used` = TRUE WHERE `Hash` LIKE '$hash'");
            $output = array();
        } else throw403();
        break;
    case 'modify':
        $studentID = checkInt($data['studentID']);
        $surname = checkString($data['surname']);
        $name = checkString($data['name']);
        $lastname = checkString($data['lastname']);
        
        $query = "
            -- Изменение данных студента
            UPDATE `Students`
            SET `Surname` = '$surname',
            `Name` = '$name',
            `Lastname` = '$lastname'
            WHERE `ID` = $studentID;";
        
        if ($mysql->query($query)){
            if ($mysql->affected_rows == 0) throw403();
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
        
        $query = "
            -- Удаление аккаунта
            DELETE FROM `Accounts`
            WHERE `ID` = $studentID";
        
        if ($mysql->query($query)){
            $output = array('id' => $studentID);
        } else throw403();
        break;
}

?>