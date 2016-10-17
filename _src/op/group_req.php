<?php

switch($data['type']){
    case 'subject_list':
        if ($accountType == 'manager'){
            $groupID = checkInt($data['groupID']);
        }
        $semesterID = checkInt($data['semesterID']);
        
        $query = "
            SELECT `groupsemesterprogram`.`ID`, `subjects`.`id`, `subjects`.`name`, `ExamType`
            FROM `GroupSemesterProgram` INNER JOIN `subjects` ON `subjects`.`id` = `groupSemesterProgram`.`subjects_id`
            WHERE `Groups_ID` = $groupID AND `Semester_ID` = $semesterID";
        
        if ($result = $mysql->query($query)){
            $output = array();
            while($row = $result->fetch_row()){
                $output[] = array(
                    'id' => $row[0],
                    'subjectID' => $row[1],
                    'subjectName' => $row[2],
                    'examType' => $row[3]
                );
            }
        } else throw403();
        break;
    case 'list':
        if ($result = $mysql->query("SELECT `ID`, `Name`, `Year` FROM `Groups`")) {
            $output = array();
            while($row = $result->fetch_row()){
                $output = array(
                    'id' => $row[0],
                    'name' => $row[1],
                    'year' => $row[2]
                );
            }
        } else throw403();
        break;
}

?>