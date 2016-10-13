<?php

switch($data['type']){
    case 'register':
        $login = checkString($_POST['login']);
        $pswHash = password_hash(checkString($_POST['psw']), PASSWORD_DEFAULT);
        $hash = checkString($_POST['hash']);
        
        runMultiQuery("
            UPDATE `Accounts`
            SET `Login` = '$login',
            `PswHash` = '$pswHash',
            `RegisterTimestamp` = CURRENT_TIMESTAMP
            WHERE `Accounts`.`ID` = (
                SELECT `Accounts_ID` FROM `Invites` WHERE `Hash` LIKE '$hash'
            );
            
            UPDATE `Invites` SET `Used` = TRUE WHERE `Hash` LIKE '$hash';
        ");
        
        $data['type'] = 'login';
        require "auth_req.php";
        break;
    case 'list':
        $query = "
        SELECT `id`, `hash`, `name`, `used`
        FROM `Managers` INNER JOIN `Invites` ON `Invites`.`Accounts_ID` = `Managers`.`Accounts_ID`";
        if ($result = $mysql->query($query)){
            $output = array();
            while($row = $result->fetch_assoc()){
                $output[] = $row;
            }
        } else {
            http_response_code(500);
            die;
        }
        break;
    case 'invite':
        $name = checkString($data['name']);
        $hash = generateHash();
        if ($mysql->query("SELECT * FROM `Managers` WHERE `Name` LIKE '$name'")->fetch_row()) throw403();
        $query = "
            -- Создание аккаунта
            INSERT INTO `Accounts` (`AccountType`)
            VALUES ('manager');
            SET @ManagerID = @@IDENTITY;

            -- Добавление аккаунта в таблицу
            INSERT INTO `Managers` (`Accounts_ID`, `Name`)
            VALUES (@ManagerID, '$name');

            -- Создание приглашения
            INSERT INTO `Invites` (`Accounts_ID`, `Hash`)
            VALUES (@ManagerID, '$hash');";
        
        runMultiQuery($query);
        $output = array(
            'name' => $name,
            'hash' => $hash,
            'used' => false
        );
        break;
    case 'delete':
        $id = checkInt($data['id']);
        
        $query = "
            -- Удаление аккаунта
            DELETE FROM `Accounts`
            WHERE `ID` = $manager_id";
        
        if ($mysql->query($query)){
            $output = array('id' => $id);
        } else throw403();
        break;
}

?>