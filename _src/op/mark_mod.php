<?php 
	switch ($data['type']){
			case 'add':
				$mark = check_str($data['mark']);
				$studentID = checkInt($data['studentID']);
				$programID = checkInt(data['programID']);
				$profID =  checkInt($data['profID']);
				$query = "-- Добавление оценки
							INSERT INTO `StudentResults` (`Students_ID`, `Programs_ID`, `Profs_ID`, `Mark`,`Modified`)
							VALUES ($studentID, $programID, $profID, '$mark',CURRENT_TIMESTAMP);";
					if($mysql->query($query)) {
						$output = array('ID' => $mysql->insert_id, 'studentID' => $studentID, 'programID' => $programID, 'profID' => $profID, 'mark' => $mark);
					} 
							
					else 	{
						throw403();
					};
				break;
				
			case 'list':
				$studentID = checkInt($data['studentID']);
				$query = "SELECT `ID`, `Programs_ID`, `Profs_ID`, `Mark` 
						FROM `StudentResults` 
						WHERE `Students_ID` = $studentID;";
				if ($result = $mysql->query($query)) {	
					$output = array();
					 while ($row = $result->fetch_row()) {
						$output[] = array(
						'id' => $row[0],
						'programID' => $row[1] ,
						'profID' => $row[2],
						'mark' => $row[3]
						);
						
					 };
					 /* очищаем результирующий набор */
					$result->close();
					
				} else {
					throw403();
				  } 	



			
				break;						
			case 'delete':
				$noteID = checkInt($data['noteID']);
				$query = "DELETE FROM `StudentResults` WHERE `ID` = $noteID;
				INSERT INTO `dellog` (`Text`, `ID`) VALUES ('mark', $noteID);";
					runmultiquery($query);
					else {
						$output = array('ID' =>$noteID);
					};
					;
				break;
			 
		}
		?>
