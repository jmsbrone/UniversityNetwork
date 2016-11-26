<?php

//@_start
//@add: student
//@set: student
//@unset: student
//@modify: student
//@delete: student
//@_end

$asgTable = 'cg_paper';

switch($data['type']){
    case 'class_list':
        $classID = $data['classID'];
        
        $query = "
            SELECT 
                `asgTable`.`assignments_id` as `id`,
                `asgTable`.`theme` as `theme`,
                `asgTable`.`desc` as `desc`,
                `status`.`completed` as `completed`
            FROM `$asgTable` as `asgTable` INNER JOIN `assignmentStatus` as `status` ON `status`.`assignments_id` = `asgTable`.`assignments_id`
            WHERE `asgTable`.`assignments_id` IN (
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
                `asgTable` .`assignments_id` as `id`,
                `asgTable` .`theme` as `theme`,
                `asgTable` .`desc` as `desc`,
                `status`.`completed` as `completed`
            FROM `$asgTable` as `asgTable` INNER JOIN `assignmentStatus` as `status` ON `status`.`assignments_id` = `asgTable`.`assignments_id`
            WHERE `asgTable` .`assignments_id` IN (
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
            ) AND `status`.`students_id` = $userID
            ORDER BY `completed` ASC, `modified`";
        
        $result = $mysql->query($query);
        if (!$result) throw403();
        $output = array();
        while($row = $result->fetch_assoc()) $output[] = $row;
        break;
    case 'add':
        $theme = str_replace("'", "\'", $data['theme']);
        $desc = str_replace("'", "\'", $data['desc'] ?? '');
        $classID = $data['classID'] ?? -1;

        $query = "
            INSERT INTO `assignments` (`classes_id`) VALUES ($classID);
            
            SET @asgID = @@IDENTITY;
            
            INSERT INTO `$asgTable` (`assignments_id`, `theme`, `desc`)
                VALUES (@asgID, '$theme', '$desc');
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
            SET `completed` = $completed
            WHERE `assignments_id` = $asgID AND `students_id` = $userID
            ";
        
        if (!$mysql->query($query)) throw403();
        $output = array();
        break;
    case 'modify':
        $asgID = $data['id'];
        $theme = str_replace("'", "\'", $data['theme']);
        $desc = str_replace("'", "\'", $data['desc'] ?? '');
        
        $query = "
            UPDATE `$asgTable`
            SET `theme` = '$theme',
                `desc` = '$desc'
            WHERE `assignments_id` = $asgID
            ";
        if (!$mysql->query($query)) throw403();
        if ($mysql->affected_rows == 0) throw403();
        $output = array();
        break;
    case 'delete':
        if ($_SESSION['accountType'] != 'president') throw403();
        $asgID = $data['id'] ?? -1;
        
        if (!$mysql->query("DELETE FROM `$asgTable` WHERE `assignments_id` = $asgID")) throw403();
        if ($mysql->affected_rows == 0) throw403("Invalid parameter");
        $mysql->query("DELETE FROM `assignments` WHERE `id` = $asgID");
        $output = array();
        break;
}

?>