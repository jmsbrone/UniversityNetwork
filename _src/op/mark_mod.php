<?php 
switch ($data['type']){
	case 'add': 												// добавление
		/* Блок взятия переменных из data*/
		$mark = check_str($data['mark']); 								// взятие из data оценки
		$studentID = checkInt($data['studentID']); 							// взятие из data ID студента			
		$programID = checkInt(data['programID']); 							// взятие из data ID программы
		$profID =  checkInt($data['profID']); 								// взятие из data ID препода
		/* ////////////////// */
		$query = "-- Добавление оценки
			INSERT INTO `StudentResults` (`Students_ID`, `Programs_ID`, `Profs_ID`, `Mark`,`Modified`)
			VALUES ($studentID, $programID, $profID, '$mark',CURRENT_TIMESTAMP);"; 			// запрос на добавление
		if($mysql->query($query)) { 									// проверка на выполнение запроса и выполнение запроса
			$output = array('ID' => $mysql->insert_id, 'studentID' => $studentID, 'programID' => $programID, 'profID' => $profID, 'mark' => $mark);
		} 			
		else {
			throw403(); 										// Ошибка
		}
		break;
		
	case 'list':
		$studentID = checkInt($data['studentID']); // взятие из data ID студента 
		$query = "SELECT `ID`, `Programs_ID`, `Profs_ID`, `Mark` 
			FROM `StudentResults` 
			WHERE `Students_ID` = $studentID;"; 							// запрос на показать 
		if ($result = $mysql->query($query)) {								// проверка на выполнение запроса и выполнение запр
			$output = array(); 									// иниц массива
			while ($row = $result->fetch_row()) {
				$output[] = array(
					'id' => $row[0],
					'programID' => $row[1] ,
					'profID' => $row[2],
					'mark' => $row[3]
					);		
			}	
		} else {
			throw403(); 										// ошибка
		} 	
		break;	
		
	case 'delete':
		$noteID = checkInt($data['noteID']);
		$query = "DELETE FROM `StudentResults` WHERE `ID` = $noteID;
			INSERT INTO `dellog` (`Text`, `ID`) VALUES ('mark', $noteID);"; 			// запрос на удаление
		runMultiQuery($query); 										// запуск мультизапроса
		$output = array('ID' =>$noteID);	
		break; 
		}
		?>
