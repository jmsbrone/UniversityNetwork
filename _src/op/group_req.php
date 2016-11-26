<?php

switch($data['type']){
    case 'subject_list':
        if ($accountType == 'manager'){
            $groupID = checkInt($data['groupID']);
        }
        $semesterID = checkInt($data['semesterID']);
        
        $query = "
            SELECT
                `groupProgram`.*,
                `subgroup`.`students_id` AS `studentID`,
                `subgroup`.`index` AS `subgroup`
            FROM
                (SELECT 
                    `program`.`id` AS `id`,
                    `subjects`.`id` AS `subjectID`,
                    `subjects`.`name` AS `subjectName`,
                    `profs`.`surname` AS `profSurname`,
                    `profs`.`name` AS `profName`,
                    `profs`.`lastname` AS `profLastname`,
                    `program`.`groups_id` AS `groupID`,
                    `program`.`semester_id` as `semesterID`,
                    ((SELECT COUNT(`albums_id`) FROM `subjectAlbums` WHERE `subjects_id` = `subjects`.`id`) + (
                        SELECT COUNT(`albums_id`) 
                        FROM `albumClass`
                        WHERE `classes_id` IN (
                            SELECT `id`
                            FROM `classes`
                            WHERE `classes`.`classRules_id` IN (
                                SELECT `id`
                                FROM `classRules`
                                WHERE `subjects_id` = `subjects`.`id`
                            )
                        ) AND EXISTS (
                            SELECT * FROM `albumFiles` WHERE `albums_id` = `albumClass`.`albums_id`
                        )
                    )
                    ) as `albumCount`
                FROM
                    (`GroupSemesterProgram` AS `program`
                INNER JOIN `profs` ON `profs`.`id` = `program`.`profs_id`)
                INNER JOIN `subjects` ON `program`.`subjects_id` = `subjects`.`id`
                WHERE
                    `program`.`groups_id` = 1) AS `groupProgram`
                    LEFT JOIN
                `SUbgroupStudent` AS `subgroup` ON `subgroup`.`program_id` = `groupProgram`.`id`
            WHERE
                `groupProgram`.`semesterID` = $semesterID
                    AND (`subgroup`.`students_id` = $userID
                    OR `groupProgram`.`groupID` = $groupID)
            ORDER BY `subjectName`";
        
        if ($result = $mysql->query($query)){
            $output = array();
            while($row = $result->fetch_assoc()){
                $output[] = $row;
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