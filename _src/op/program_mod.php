<?php

switch($data['type']){
    case 'list':
        $groupID = checkInt($data['groupID']);
        $semesterID = checkInt($data['semesterID']);
        
        $query = "
            -- Получение программы группы
            SELECT `groupsemesterprogram`.`id`, `subjects_ID` as `subjectID`, `examType`, CONCAT(`Profs`.`Surname`, ' ', `Profs`.`Name`, ' ', `Profs`.`Lastname`) as `profName`
            FROM `GroupSemesterProgram` INNER JOIN `Profs` ON `Profs`.`ID` = `GroupSemesterProgram`.`Profs_ID`
            WHERE `Groups_ID` = $groupID AND `Semester_ID` = $semesterID;";
        
        if ($result = $mysql->query($query)){
            $output = array();
            while($row = $result->fetch_assoc()){
                $output[] = $row;
            }
        } else throw403();
        break;
    case 'add':
        $groupID = checkInt($data['groupID']);
        $semesterID = checkInt($data['semesterID']);
        $subjectID = checkInt($data['subjectID']);
        $examType = checkString($data['examType']);
        $profID = checkInt($data['profID']);
        
        $query = "
            -- Добавление в программу семестра
            INSERT INTO `GroupSemesterProgram` (`groups_id`, `subjects_id`, `semester_id`, `examType`, `profs_id`)
            VALUES ($groupID, $subjectID, $semesterID, '$examType', $profID)";
        
        if (!$mysql->query($query)) throw403();
        $output = array(
            'id' => $mysql->insert_id,
            'groupID' => $groupID,
            'semesterID' => $semesterID,
            'subjectID' => $subjectID,
            'examType' => $examType,
            'profID' => $profID
        );
        break;
    case 'modify':
        $programID = checkInt($data['programID']);
        $examType = checkString($data['examType']);
        
        $query = "
            -- Изменение данных программы
            UPDATE `GroupSemesterProgram`
            SET `ExamType` = '$examType`
            WHERE `ID` = $programID;";
        
        if ($mysql->query($query)){
            $output = array(
                'id' => $programID,
                'examType' => $examType,
                'subDivision' => $subDivision
            );
        } else throw403();
        break;
    case 'delete':
        $programID = checkInt($data['programID']);
        $query = "DELETE FROM `GroupSemesterProgram` WHERE `ID` = $programID";
        
        if ($mysql->query($query)){
            $output = array(
                'id' => $programID
            );
        } else throw403();
        break;
}

?>