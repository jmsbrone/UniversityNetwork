<?php 

########################################################################
# Functions.
#

// Die with 403 and msg.
function throw403($msg){
    http_response_code(403);
    if (isset($msg)) die($msg);
    
    global $mysql;
    global $query;
    die($mysql->error." query:".$query);
}

// Check functions.
require_once "check.php";

// Placeholder
function checkInt($myVar){
    if (isset($myVar)) return $myVar;
    throw403('missing parameter');
}

function checkString($myVar){
    if (isset($myVar)) return $myVar;
    throw403('missing parameter');
}

function _checkDate($myVar){
    if (isset($myVar)) return $myVar;
    throw403('missing parameter');
}

// Codes
function generateHash(){
    $length = 7;
    $result = '';
    $alphabet = '0123456789abcdefghijklmnopqrstuvwxyz';
    $maxRand = strlen($alphabet) - 1;
    for($i = 0; $i < $length; ++$i){
        $result .= $alphabet[rand(0, $maxRand)];
    }
    return $result;
}
/*
function generateToken(){
    $length = 64;
    $result = '';
    $alphabet = '0123456789abcdef';
    $maxRand = strlen($alphabet) - 1;
    for($i = 0; $i < $length; ++$i){
        $result .= $alphabet[rand(0, $maxRand)];
    }
    return $result;
}
*/
function runMultiQuery($query){
    global $mysql;
    if (!$mysql->query("START TRANSACTION")) throw403('Cant start transaction.');
    if ($mysql->multi_query($query)){
        do {
            $mysql->store_result();
        } while($mysql->next_result());
        if ($mysql->errno != 0) {
            $error = $mysql->error;
            $mysql->query("ROLLBACK");
            throw403($error." for query: ".$query);
        }
        $mysql->query("COMMIT");
    } else throw403($mysql->error);
}

// Creates array with queries for getting new/updated/deleted/etc. rows of specific table.
function composeNote($table, $type){
    global $time;
    return array(
        'updated' => "SELECT * FROM `$table` WHERE UNIX_TIMESTAMP(`Modified`) > $time",
        'deleted' => "SELECT `ID` FROM `DelLog` WHERE `Text` LIKE '$type' AND UNIX_TIMESTAMP(`Time`) > $time"
    );
}

#########################################################################
# Execution start.
$start_exec_time = microtime(true);
$start_memory_usage = memory_get_usage();
session_start();

# Configuraion of MySQL. Connects to database and creates $mysql variable.
require_once "db_connect.php.dsf";

#########################################################################
# Setup.
#
date_default_timezone_set('UTC');
// Executed database queries log.
$queryLog = 'query_log.txt';

#########################################################################
# Taking data from $_GET, $_POST, $_SESSION.
#
if (!isset($_GET['rtype'])){
    throw403('Invalid method.');
}

$update = $_GET['update'];
if ($_SESSION['lastTimestamp']){
    $time = $_SESSION['lastTimestamp'];
} else $time = time();

if (isset($_POST['json'])){
    $_POST = json_decode($_POST['json'], true);
}

# Masks.
$requestMasks = array(
    'faculty_mod_list' => 30,
    'faculty_mod_add' => 8,
    'faculty_mod_modify' => 8,
    'faculty_mod_delete' => 24,
    'profile_mod_list' => 30,
    'profile_mod_add' => 8,
    'profile_mod_modify' => 8,
    'profile_mod_delete' => 8,
    'group_mod_list' => 24,
    'group_mod_add' => 8,
    'group_mod_modify' => 8,
    'group_mod_delete' => 24,
    'group_mod_program_list' => 8,
    'grouplist_mod_add' => 8,
    'grouplist_mod_list' => 30,
    'grouplist_mod_modify' => 8,
    'grouplist_mod_pr_change' => 8,
    'grouplist_mod_delete' => 24,
    'program_mod_list' => 24,
    'program_mod_add' => 8,
    'program_mod_modify' => 8,
    'program_mod_delete' => 24,
    'group_req_subject_list' => 14,
    'group_req_list' => 30,
    'manager_mod_register' => 31,
    'manager_mod_invite' => 16,
    'manager_mod_list' => 16,
    'manager_mod_delete' => 16,
    'student_mod_add' => 8,
    'student_mod_register' => 31,
    'student_mod_modify' => 8,
    'student_mod_delete' => 24,
    'auth_req_login' => 31,
    'dep_mod_list' => 30,
    'dep_mod_add' => 8,
    'dep_mod_modify' => 8,
    'dep_mod_delete' => 24,
    'prof_mod_list' => 24,
    'prof_mod_add' => 8,
    'prof_mod_modify' => 8,
    'prof_mod_delete' => 24,
    'schedule_mod_list' => 30,
    'schedule_mod_add' => 8,
    'schedule_mod_modify' => 8,
    'schedule_mod_delete' => 24,
    'subject_mod_list' => 24,
    'subject_mod_add' => 8,
    'subject_mod_modify' => 8,
    'subject_mod_delete' => 24,
    'subgroup_mod_select' => 6,
    'assignment_mod_list' => 6,
    'assignment_mod_add' => 4,
    'auth_req_invite_check' => 31,
    'auth_req_signout' => 31,
    'assignment_mod_toggle' => 6,
    'assignment_mod_modify' => 4,
    'assignment_mod_delete' => 4,
    'room_mod_list' => 14,
    'room_mod_add' => 8,
    'room_mod_modify' => 8,
    'room_mod_delete' => 8,
    'semester_mod_list' => 30,
    'semester_mod_add' => 8,
    'semester_mod_delete' => 24,
    'mark_mod_add' => 8,
    'mark_mod_list' => 14,
    'mark_mod_delete' => 8,
    'attendance_mod_add' => 4,
    'attendance_mod_list' => 6,
    'attendance_mod_delete' => 4,
    'attendance_mod_grouplist' => 6,
    'upload_req_request' => 6,
    'upload_req_complete' => 6
);

# Composing type.
$type = $_GET['rtype'].'_'.$_GET['type'];
$data = array_merge($_GET, $_POST);

$accountMask = $_SESSION['accountMask'] ?? 1;
$groupID = $_SESSION['groupID'];
$accountType = $_SESSION['accountType'];

if (($requestMasks[$type] & $accountMask) == 0) {
    trigger_error('invalid mask');
    throw403("Denied for {$_SESSION['id']} mask=$accountMask type=$type requestmask={$requestMasks[$type]}.");
}

# At this point request is authorized.
if ($update){
    switch($accountType){
        case 'admin':
        // Session list
            break;
        case 'manager':
            // Dependencies.
            $queries = array(
                'faculties' => composeNote('faculties', 'faculty'),
                'specialities' => composeNote('specialities', 'spec'),
                'groups' => composeNote('groups', 'group'),
                'students' => composeNote('students', 'student'),
                'program' => composeNote('groupsemesterprogram', 'program'),
                'semester' => composeNote('semester', 'semester'),
                'subjects' => composeNote('subjects', 'subject'),
                'rooms' => composeNote('rooms', 'room'),
                'rules' => composeNote('classrules', 'rule'),
                'dep' => composeNote('departments', 'dep'),
                'prof' => composeNote('profs', 'prof'),
                'results' => composeNote('studentresults', 'result')
            );
            $output = array();
            foreach($queries as $bundle => $note){
                $updatedRows = array();
                $deletedRows = array();
                
                // Updated rows.
                $result = $mysql->query($note['updated']);
                if (!$result){
                    http_response_code(500);
                    die;
                }
                while($row = $result->fetch_assoc()){
                    $updatedRows[] = $row;
                }
                $result->free();
                
                // Deleted rows.
                $result = $mysql->query($note['deleted']);
                if (!$result){
                    http_response_code(500);
                    die;
                }
                while($row = $result->fetch_assoc()){
                    $deletedRows[] = $row;
                }
                $result->free();
                $output[$bundle] = array('updated' => $updatedRows, 'deleted' => $deletedRows);
            }
            break;
        // Groups, schedule, attachments, subjects.            
        case 'president':
        case 'student': 
            $list = array('assignment_mod.php', 'attendance_mod.php', 'dep_mod.php', 'faculty_mod.php', 'group_req.php', 'grouplist_mod.php', 'mark_mod.php', 'room_mod.php', 'schedule_mod.php', 'semester_mod.php', 'profile_mod.php');
            break;
    }
    foreach($list as $script){
        $data = array('type' => 'list');
        require $script;
    }
} else require $_GET['rtype'].".php";

# Requests populate variable $output as assossiative/index array of data.

# Request is completed.

$_SESSION['lastTimestamp'] = time();
$end_exec_time = microtime(true);
$end_memory_usage = memory_get_usage();
$peak_memory = memory_get_peak_usage();

file_put_contents('S:/php7/usage_info.txt', "{$_GET['rtype']} {$_GET['type']} $start_exec_time $end_exec_time $start_memory_usage $end_memory_usage $peak_memory
", FILE_APPEND | LOCK_EX);

die(json_encode($output));
?>