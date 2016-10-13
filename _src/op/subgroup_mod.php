<?php 	
	switch ($data['type']){
			case 'select':
				$index = check_str_with_pattern($data['index'],"/[12]/");
				$programID = checkInt($data['programID']);
				$query_2="INSERT INTO `SubgroupStudent` (`Students_ID`, `Index`, `Program_ID`,`Modified`)
							VALUES ($userID, '$index', $programID,CURRENT_TIMESTAMP);";
				
							if($mysql->query($query_2)) 
								{
								$output = array('userID' => $userID,'index'=>$index,'programID'=>$programID);
								}
							else {
								throw403();
							}
						  
						  else {
						  throw403();
						  }
							   
					  }	
					else 	{
							throw403();
							};
					};
				break;
				
			
			 
		}
		?>
