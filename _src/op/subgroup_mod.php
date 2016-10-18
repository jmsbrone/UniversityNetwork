<?php 	
switch ($data['type']){
    case 'select':
        $index = $data['index'];
        $programID = $data['programID'];
        $query = "
            DELETE FROM `subgroupStudent`
            WHERE `students_ID` = $userID AND `program_ID` = $programID;
            
            INSERT INTO `SubgroupStudent` (`Students_ID`, `Index`, `Program_ID`)
            VALUES ($userID, '$index', $programID);";
        
        runMultiQuery($query);        
        $output = array();
        break;
}
?>
