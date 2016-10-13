<?php 
	switch ($data['type']){
		case 'add':
		$name = check_str($data['name']);
		$query = "INSERT INTO `Subjects` (`Name`,`Modified`) VALUES ('$name',CURRENT_TIMESTAMP);";	
			if($mysql->query($query)) {
				$output = array('id' => $mysql->insert_id, 'name' => $name);
			} 
			
			else {
				throw403();
			};
		break;
		
		case 'list':
		$query = "SELECT `ID`, `Name` FROM `Subjects`;";
		if ($result = $mysql->query($query)) {	
			$output = array();
			while ($row = $result->fetch_row()) {
				$output[] = array(
				'id' => $row[0],
				'name' => $row[1], 
				);
				
			};
			/* очищаем результирующий набор */
			$result->close();
			
		} 
		else 
			{
			throw403();
			} 	
		break;
		
		case 'modify':
		$name=check_str($data['name']);
		$subjectID = checkInt($data['subjectID']);
		$query = "-- Изменение данных предмета
		UPDATE `Subjects`
		SET `Name` = '$name',`Modified` = CURRENT_TIMESTAMP 
		WHERE `ID` = $subjectID;";	
			
			if(!($mysql->query($query))) {
				throw403();
				} 
			
			else {
				$output = array('id' => $mysql->insert_id, 'name' => $name);
			}
		break;
		
		
		case 'delete': 
		$subjectID = checkInt($data['subjectID']);
		$query = "DELETE FROM `Subjects` WHERE `ID` = $subjectID;
		INSERT INTO `dellog` (`Text`, `ID`) VALUES ('subject', $subjectID);";
		(runmultiquery($query)); 
		$output = array('id' =>$subjectID);
		break;
		
	}
?>
