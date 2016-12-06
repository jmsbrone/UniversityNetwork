<?php

switch($data['type']){
    case 'client':
        file_put_contents('S:/php7/request_times.txt', "{$data['request']}, {$data['startTime']}, {$data['endTime']}
" ,FILE_APPEND | LOCK_EX);
        $output = array();
        break;
}

?>