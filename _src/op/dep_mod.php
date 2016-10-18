<?php 	
switch ($data['type']){ 								// выбор между add, list, delete, modify
	case 'add': 									// добавление 
		$name = check_str($data['name']); 					// берем из массива $data name
		$query = "INSERT INTO `Departments` (`Name`) 
			VALUES ('$name');";						// запрос на добавление
		if($mysql->query($query)) { 						// проверка на выполнение и выполнение запроса
			$output = array('id' => $mysql->insert_id, 'name' => $name); 	// Добавляем в массив output нужные данные
		} 			
		else 	{
			throw403();							 // ошибка
		}				
		break;
				
	case 'list': 									// показать 
		$query = "-- Получение списка аудиторий
			SELECT `ID`, `name` FROM `Departments`;"; 			// запрос на показ
		if ($result = $mysql->query($query)) {					// проверка на выполнение и выполнение запроса
			$output = array(); 						// иниц. массива 
			while ($row = $result->fetch_row())  { 				// выбираем 
				$output[] = array(
					'id' => $row[0], 				// id
					'name' => $row[1] 				// наименование
				);						
			}								
		} 	
		else {
			throw403(); 							// ошибка
		} 	
		break;
	case 'modify': 									// изменение
		$new_name=check_str($data['name']);					//берем из массива имя
		$ID = checkInt($data['depID']);						// берем из массива ID
		$query = "-- Изменение данных аудитории 
			UPDATE `Departments` 
			SET `name` = '$new_name' , `Modified` = CURRENT_TIMESTAMP 
			WHERE `id` = $ID;";						// запрос на измененение						
		if(!($mysql->query($query))) {						// проверка на невыполнение, если да, то ошибка							
			throw403();
		} 
		$output = array('id' => $ID, 'name' => $new_name);			// добавляем в массив данные							
		break;
					
	case 'delete': // удаление
		$ID = checkInt($data['depID']);						 // ID
		$query = "DELETE FROM `departments` WHERE `departments`.`ID` = $ID; 
			INSERT INTO `dellog` (`Text`, `ID`) VALUES ('dep', $ID);";	// запрос на удаление
		runMultiQuery($query); 							// запускаем функцию выполнения мультизапроса			
		$output = array('id' =>$ID);						// добавляем в массив ID					
		break;	 
}
		?>
