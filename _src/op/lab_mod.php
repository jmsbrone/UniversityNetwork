<?php

//@_start
//@add: student
//@set: student
//@unset: student
//@modify: student
//@delete: student
//@_end

switch($data['type']){
    case 'class_list':
        $classID = $data['classID'];
        
        $query = "
            SELECT 
                `labs`.`assignments_id` as `id`,
                `labs`.`order` as `order`,
                `labs`.`theme` as `theme`,
                `labs`.`desc` as `desc`,
                `status`.`completed` as `completed`
            FROM `labs` INNER JOIN `assignmentStatus` as `status` ON `status`.`assignments_id` = `labs`.`assignments_id`
            WHERE `labs`.`assignments_id` IN (
                SELECT `id` FROM `assignments` WHERE `classes_id` = $classID
            ) AND `status`.`students_id` = $userID";
        
        $result = $mysql->query($query);
        if (!$result) throw403();
        $output = array();
        while($row = $result->fetch_assoc()) $output[] = $row;
        
        break;
    case 'list':
        $programID = $data['programID'];
        
        $query = "
            SELECT 
                `labs`.`assignments_id` as `id`,
                `labs`.`order` as `order`,
                `labs`.`theme` as `theme`,
                `labs`.`desc` as `desc`,
                `status`.`completed` as `completed`
            FROM `labs` INNER JOIN `assignmentStatus` as `status` ON `status`.`assignments_id` = `labs`.`assignments_id`
            WHERE `labs`.`assignments_id` IN (
                SELECT `id` FROM `assignments` WHERE `classes_id` IN (
                    SELECT `classes`.`id`
                    FROM `classes`
                    WHERE `classRules_id` IN (
                        SELECT `id`
                        FROM `classRules`
                        WHERE `subjects_id` = (
                            SELECT `subjects_id` FROM `groupSemesterProgram` WHERE `id` = $programID
                        ) AND `id` IN (
                            SELECT `rules_id` FROM `ruleGroup` WHERE `groups_id` = $groupID
                        )
                    )
                )
            ) AND `status`.`students_id` = $userID";
        
        $result = $mysql->query($query);
        if (!$result) throw403();
        $output = array();
        while($row = $result->fetch_assoc()) $output[] = $row;
        break;
    case 'add':
        $order = $data['order'];
        $theme = str_replace("'", "\'", $data['theme']);
        $desc = str_replace("'", "\'", $data['desc'] ?? '');
        $classID = $data['classID'] ?? -1;

        $query = "
            INSERT INTO `assignments` (`classes_id`) VALUES ($classID);
            
            SET @asgID = @@IDENTITY;
            
            INSERT INTO `labs` (`assignments_id`, `order`, `theme`, `desc`)
                VALUES (@asgID, $order, '$theme', '$desc');
            ";
        
        runMultiQuery($query);
        $output = array(
            'id' => $mysql->query("SELECT @asgID")->fetch_row()[0]
        );
        break;
    case 'set':
        $completed = 'true';
    case 'unset':
        $completed = $completed ?? 'false';
        $asgID = $data['asgID'];
        
        $query = "
            UPDATE `assignmentStatus`
            SET `Completed` = $completed
            WHERE `assignments_id` = $asgID AND `students_id` = $userID
            ";
        
        if (!$mysql->query($query)) throw403();
        $output = array();
        break;
    case 'modify':
        
        break;
    case 'delete':
        $asgID = $data['asgID'];
        
        if (!$mysql->query("DELETE FROM `assignments` WHERE `id` = $asgID")) throw403();
        $output = array();
        break;
}

?>