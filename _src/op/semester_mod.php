<?php 

	switch ($data['type']){
			case 'add':
				$year = checkInt($data['year']);
				$season = checkString($data['season']);
				$startTime= checkString($data['startTime']);
				$endTime = checkString($data['endTime']);
				$query = "-- Добавление семестра
							INSERT INTO `Semester` (`Year`, `Season`, `StartTimestamp`, `EndTimestamp`,`Modified`)
							VALUES ($year, '$season', '$startTime', '$endTime',CURRENT_TIMESTAMP);";				
					if($mysql->query($query)) 
							
							{$output = array('id' => $mysql->insert_id, 'season' => $season,'year'=>$year,'startTimestamp'=>$startTime,'endTimestamp'=>$endTime);} 
							
					else 	{
								throw403();
							};					
				break;
				
			case 'list':
				$query = "SELECT `id`, `Year`, `Season`, UNIX_TIMESTAMP(`StartTimestamp`), UNIX_TIMESTAMP(`EndTimestamp`) FROM `Semester`;;";
				if ($result = $mysql->query($query)) {	
					 $output = array();
					 while ($row = $result->fetch_row())  
					 {
						$output[] = array(
						'id' => $row[0],
						'year' => $row[1],
						'season' => $row[2], 
						'startTimestamp' =>$row[3],
						'endTimestamp' =>$row[4], 
						);
						
					 };				
					 /* очищаем результирующий набор */
					$result->close();					
				}
					else {throw403();} 	
				break;
											
			case 'delete':
				$semesterid = checkInt($data['semesterID']);
				$query = "DELETE FROM `Semester` WHERE `id` = $semesterid;
				INSERT INTO `dellog` (`Text`, `id`) VALUES ('semester', $semesterid);";
				RunMultiQuery($query);					
					$output = array('id' =>$semesterid);					
				break;
			 
		}
		?>
