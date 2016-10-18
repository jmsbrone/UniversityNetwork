<?php switch ($data['type']){
	case 'add': // добавление
		$name = check_str($data['name']); // взятие имени из data
		$surname = check_str($data['surname']);// взятие фамилии из data
		$lastname = check_str($data['lastname']);// взятие отчества из data
		$depID = checkInt($data['depID']);// взятие кафедры ID из data
		$query = "-- Добавление преподавателя
			INSERT INTO `Profs` (`Surname`, `Name`, `Lastname`, `Departments_ID`,`Modified`)
			VALUES ('$surname', '$name', '$lastname', '$depID',CURRENT_TIMESTAMP)"; // запрос на добавление
		if($mysql->query($query)) {
			$output = array('id' => $mysql->insert_id, 'name' => $name,'surname'=>$surname,'lastname'=>$lastname,'depId'=>$depID);
		} 				
		else {
			throw403();
		};
		break;	
	case 'list': // показать 
		$query = (isset($data['depID'])) ? $query = " SELECT `ID`, `Surname`, `Name`, `Lastname` FROM `Profs` WHERE `Departments_ID` = {$data['depID']}" : $query = "SELECT `ID`, `Surname`, `Name`, `Lastname` FROM `Profs`";
		if ($result = $mysql->query($query)) {	
			$output = array();
			while ($row = $result->fetch_row()) {
				$output[] = array(
					'id' => $row[0],
					'surname' => $row[1], 
					'name' =>$row[2], 
					'lastname' =>$row[3], 
					);	
			};					
		}
		else {
			throw403();
		} 	
		break;	
	case 'modify':
		$new_name=check_str($data['name']);
		$new_surname = check_str($data['surname']);
		$new_lastname = check_str($data['lastname']);
		$profID = checkInt($data['profID']);
		$query = "-- Изменение данных преподавателя
			UPDATE `profs` 
			SET `name` = '$new_name', `surname` = '$new_surname', `lastname` = '$new_lastname',`Modified` = CURRENT_TIMESTAMP 
			WHERE `profs`.`ID` = $profID";
		if(!($mysql->query($query))) {
			throw403();
		} 
		$output = array('id' => $profID, 'name' => $new_name, 'lastname' => $new_lastname, 'surname' => $new_surname);	
		break;	
		
	case 'delete':
		$profID = checkInt($data['profID']);			
		$query = "DELETE FROM `Profs` WHERE `ID` = $profID;
			INSERT INTO `dellog` (`Text`, `ID`) VALUES ('prof', $profID);";
		runMultiQuery($query);	
		$output = array('id' =>$profID);										
		break;	 
		}
?>
