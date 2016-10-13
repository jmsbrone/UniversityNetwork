<?php

switch($data['type']){
    case 'login':
        $login = $data['login'];
        $psw = $data['psw'];

        $result = $mysql->query("SELECT `ID`, `PswHash` FROM `Accounts` WHERE `Login` LIKE '$login'");
        if (!$result) throw403();
        
        if ($row = $result->fetch_row()){
            $accountID = $row[0];
            $hash = $row[1];
            $result->free();
        } else throw403();

        if (!password_verify($psw, $hash)) throw403();

        $accountType = $mysql->query("SELECT `accountType` FROM `Accounts` WHERE `ID`=$accountID")->fetch_row()[0];

        switch($accountType){
            case 'admin':
                $accountMask = 16;
                break;
            case 'manager':
                $accountMask = 8;
                break;
            case 'student':
                // Fetching group.
                $groupID = $mysql->query("SELECT `Groups_ID` FROM `Students` WHERE `Accounts_ID` = $accountID")->fetch_row()[0];
                $accountMask = 2;
                if ($mysql->query("SELECT `PresidentID` FROM `Groups` WHERE `ID` = $groupID")->fetch_row()[0]) {
                    $accountType = 'president';  
                    $accountMask = 4;
                }
                $curtime = date('Y-m-d');
                $semesterID = $mysql->query("SELECT `id` FROM `semester` WHERE '$curtime' BETWEEN `startTimestamp` AND `endTimestamp`")->fetch_row()[0];
                break;
        }
        
        $_SESSION['userID'] = $accountID;
        $_SESSION['accountType'] = $accountType;
        $_SESSION['accountMask'] = $accountMask;
        $_SESSION['groupID'] = $groupID;
        $_SESSION['semesterID'] = $semesterID;
        
        $output = array(
            'accountType' => $accountType,
            'semesterID' => $semesterID
        );
        break;
    case 'invite_check':
        $hash = $data['hash'];
        $row = $mysql->query("SELECT `AccountType`, `ID`
            FROM `Accounts`
            WHERE `Accounts`.`ID` = (
                SELECT `Accounts_ID` FROM `Invites` WHERE `Hash` LIKE '$hash' AND `Used` = FALSE
            )")->fetch_row();
        switch($row[0]){
            default: throw403(); break;
            case 'manager':
                $output = array(
                    'type' => 'manager',
                    'name' => $mysql->query("SELECT `Name` FROM `Managers` WHERE `Accounts_ID` = {$row[1]}")->fetch_row()[0]
                );
                break;
            case 'student':
                $result = $mysql->query("SELECT `Surname`, `Name`, `Lastname`, `Groups_ID` FROM `Students` WHERE `Accounts_ID` = {$row[1]}");
                if (!$result) throw403();
                $studentRow = $result->fetch_row();
                $groupRow = $mysql->query("SELECT `Name` FROM `Groups` WHERE `ID` = {$studentRow[3]}")->fetch_row();
                $output = array(
                    'type' => 'student',
                    'surname' => $studentRow[0],
                    'name' => $studentRow[1],
                    'lastname' => $studentRow[2],
                    'groupName' => $groupRow[0]
                );
                break;
        }
        break;
    case 'signout':
        $_SESSION = array();
        break;
}
?>