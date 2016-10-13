<?php

switch($data['type']){
    case 'list':
        if (isset($data['groupID'])){
            $groupID = checkInt($data['groupID']);
        }
        $output = array(
            'rules' => array(),
            'rooms' => array(),
            'profs' => array(),
            'groups' => array()
        );
        if ($accountType == 'manager'){
            // Rules
            $query = "SELECT DISTINCT `id`  FROM `rulesList`";
            if (isset($groupID)){
                $condition = " WHERE `Groups_ID` = $groupID";
            } else if (isset($data['subjectID'])){
                $condition =" WHERE `Subjects_ID` = {$data['subjectID']}";
            }
            $query .= $condition;
            $result = $mysql->query($query);
            if (!$result) throw403();
            
            $rules_id = array();
            while($row = $result->fetch_row()){
                $rules_id[] = $row[0];
            }
            
            $output = array();
            foreach($rules_id as $ruleID){
                $container = array();
                $query = "
                    SELECT DISTINCT `ID` as `id`, `weekDay` as `weekDay`, `weekType` as `weekType`, `classType` as `classType`, `SubgroupIndex` as `subgroup`, `Subjects_ID` as `subjectID`, `order` as `order`
                    FROM `rulesList`
                    WHERE `id` = $ruleID";
                if ($result = $mysql->query($query)){
                    if ($row = $result->fetch_assoc()){
                        $container = $row;
                    }
                } else throw403();
                
                // Dependencies.
                $fields = array('rooms' => 'Rooms_id','groups' => 'Groups_id', 'profs' => 'Profs_id');

                foreach($fields as $c => $f){
                    $query = "
                        SELECT DISTINCT `$f` as `id`, `$c`.`name` as `name`
                        FROM `rulesList` INNER JOIN `$c` ON `$c`.`id` = `rulesList`.`$f`
                        WHERE `rulesList`.`id`=$ruleID";
                    $container[$c] = array();
                    if ($result = $mysql->query($query)){
                        while($row = $result->fetch_assoc()){
                            $container[$c][] = $row;
                        }
                    } else throw403();
                }
                $output[] = $container;
            }
        } else {
            // Rules
            $rules = array();
            $result = $mysql->query("SELECT `id` FROM `ruleslist` WHERE `groups_id` = $groupID");
            if (!$result) throw403();
            while($row = $result->fetch_row()){
                $rules[] = $row[0];
            }
            $output = array();
            $dependencies = array(
                'rooms' => array('Name' => 'name'), 
                'profs' => array('Surname' => 'surname', 'Name' => 'name', 'Lastname' => 'lastname')
            );
            // Semester start time
            $startTime = $mysql->query("SELECT `startTimestamp` FROM `semester` WHERE `id`={$_SESSION['semesterID']}")->fetch_row()[0];
            foreach($rules as $ruleID){
                $rule = $mysql->query("SELECT `ClassType` as `classType`, `SubgroupIndex` as `subgroup`, `Subjects_ID` as `subjectID` FROM `rulesList` WHERE `id` = $ruleID")->fetch_assoc();
                
                $classes = array();
                $result = $mysql->query("SELECT `ID` as `id`, `StartTime` as `startTimestamp` FROM `classes` WHERE `classrules_id` = $ruleID AND `startTime` > '$startTime'");
                if (!$result) throw403();
                if ($result->num_rows == 0) continue;
                while($row = $result->fetch_assoc()){
                    $classes[] = $row;
                }
                
                $rule['classes'] = $classes;
                $rule['rooms'] = array();
                $rule['profs'] = array();
                
                foreach($dependencies as $table => $fields){
                    $query = "SELECT `ID` as `id`";
                    foreach($fields as $field => $alias) $query.=",`$field` AS `$alias`";
                    $query .= " 
                    FROM `$table` 
                    WHERE `id` IN (
                        SELECT DISTINCT `{$table}_id`
                        FROM `rulesList`
                        WHERE `Groups_id` = $groupID
                    )";
                    $result = $mysql->query($query);
                    if (!$result) throw403();
                    while($row = $result->fetch_assoc()){
                        $rule[$table][] = $row;
                    }
                }
                
                $output[] = $rule;
            }
        }
        break;
    case 'modify':
        $ruleID = checkInt($data['ruleID']);
        $modify = true;
    case 'add':
        $roomsID = $data['rooms_id'];
        $profsID = $data['profs_id'];
        $groupsID = $data['groups_id'];
        $dates = $data['dates'];
        $weekDay = checkInt($data['weekDay']);
        $weekType = checkInt($data['weekType']);
        $classType = checkString($data['classType']);
        $subgroup = $data['subgroup'] ?? 'NULL';
        $order = checkInt($data['order']);
        $subjectID = checkInt($data['subjectID']);
        
        if ($modify){
            $query = "
                SET @RuleID = $ruleID;

                UPDATE `classRules`
                SET `weekDay` = $weekDay, 
                `weektype`= $weekType, 
                `classtype` = '$classType', 
                `subgroupIndex` = $subgroup, 
                `order` = $order,
                `subjects_id` = $subjectID
                WHERE `id` = @RuleID;";
        } else {
            $query = "
                INSERT INTO `ClassRules`(`weekDay`, `weektype`, `classtype`, `subgroupIndex`, `order`, `subjects_id`)
                VALUES ($weekDay, $weekType, '$classType', ".($subgroup ?? 'NULL').", $order, $subjectID);

                SET @RuleID = @@IDENTITY;";
        }
        
        $heritage = array('room' => $roomsID, 'prof' => $profsID, 'group' => $groupsID);
        foreach($heritage as $c => $arr){
            if ($modify) $query .= "DELETE FROM `Rule{$c}` WHERE `rules_ID`=@RuleID;";
            foreach($arr as $entityID) $query.="INSERT INTO `Rule{$c}` VALUES (@RuleID, $entityID);";
        }
        if ($modify) $query.="DELETE FROM `classes` WHERE `classrules_id` = @RuleID;";
        foreach($dates as $date){
            $query .= "INSERT INTO `Classes`(`classRules_id`, `startTime`) VALUES (@RuleID, '$date');";
        }
        
        runMultiQuery($query);
        $output = array(
            'ruleID' => $ruleID ? $ruleID : $mysql->query("SELECT @RuleID")->fetch_row()[0],
            'rooms' => $roomsID,
            'profs' => $profsID,
            'groups' => $groupsID,
            'dates' => $dates,
            'weekDay' => $weekDay,
            'weekType' => $weekType,
            'classType' => $classType,
            'subgroup' => $subgroup,
            'order' => $order
        );
        break;
    case 'delete':
        $ruleID = checkInt($data['ruleID']);
        
        if (!$mysql->query("DELETE FROM `ClassRules` WHERE `ID` = $ruleID")) throw403();
        $output = $ruleID;
        break;
}

?>