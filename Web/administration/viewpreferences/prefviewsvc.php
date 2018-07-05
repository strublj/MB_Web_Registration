<?php

$params = $_REQUEST;

$action = isset($params['action']) != '' ? $params['action'] : '';
$preferencesCls = new Preferences();

$servername = "localhost";
$username = "firemtn_reguser";
$password = "F1r3M0unt@!n";
$dbname = "firemtn_mbreg";

$authToken = "";

if (!isset($_COOKIE['mbreg_user']) && $action != 'login') {
    $action = "authError";
} else {
    $authToken = $_COOKIE['mbreg_user'];
}

/*
 * Service call routing function based on $params['action'] passed
 */
switch ($action) {
    case 'checkAdmin':
        $preferencesCls->validateAdmin($servername, $username, $password, $dbname, $authToken);
        break;
    case 'getPreferencesByUnit':
        $preferencesCls->getPreferencesByUnit($servername, $username, $password, $dbname, $params);
        break;
    default:
        $preferencesCls->validateAdmin($servername, $username, $password, $dbname, $authToken);
        return;
}

class Preferences {

    protected $data = array();

    function __construct() {
        
    }
    
    /*
     * Gets the schedule information from the database for the given week and returns
     * a JSON object in this structure:
     * 
     *  Unit
     *      Scout
     *          Schedule
     */
    function getPreferencesByUnit($servername, $username, $password, $dbname, $params) {
        /*
            SELECT sc.first_name, sc.last_name, ut.unit_type, ut.unit_number, cs.start_session_id,  cs.number_of_sessions, mb.mb_name
            FROM schedule sh 
            JOIN scout sc ON sh.scout_id = sc.scout_id
            JOIN unit ut ON sc.unit_id = ut.unit_id
            JOIN week wk ON ut.week_id = wk.week_id
            JOIN class_session cs ON cs.class_session_id = sh.class_session_id
            JOIN merit_badge mb ON cs.merit_badge_id = mb.mb_id
            WHERE wk.week_number = 1
            ORDER BY ut.unit_number ASC, sc.last_name ASC, cs.start_session_id ASC
        
         */
        
        $week = $params['week'];
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        $sql = "SELECT ut.unit_id, cl.council, ut.unit_type, ut.unit_number, wk.week_number " .
                "FROM unit ut " .
                "JOIN council cl ON cl.council_id = ut.council_id " .
                "JOIN week wk ON ut.week_id = wk.week_id " .
                "WHERE wk.week_number = " . $week . " " . 
                "ORDER BY wk.week_number ASC, ut.unit_number ASC";

        $result = $conn->query($sql);
        
        $retArray = array();
        
        if($result->num_rows > 0)
        {
            while($row = $result->fetch_assoc()){
                //$row[unit_id]
                $retArray[] = array("unit_id" => $row[unit_id],
                                                "council_name" => $row[council],
                                                "unit_type" => $row[unit_type],
                                                "unit_number" => $row[unit_number],
                                                "week_number" => $row[week_number],
                                                "scout_list" => array());
            }
        }
        
        foreach ($retArray as &$unit) {
            $sql = "SELECT sc.scout_id, sc.first_name, sc.last_name, sc.age " .
                    "FROM scout sc " .
                    "JOIN unit ut ON sc.unit_id = ut.unit_id " .
                    "WHERE ut.unit_id = " . $unit['unit_id'] . " " .
                    "ORDER BY sc.last_name ASC";

            $result = $conn->query($sql);
            
            if($result->num_rows > 0)
            {
                while($row = $result->fetch_assoc()){
                    //$row[scout_id]
                    $unit['scout_list'][] = array(  "scout_id" => $row[scout_id],
                                                                "first_name" => $row[first_name],
                                                                "last_name" => $row[last_name],
                                                                "age" => $row[age],
                                                                "class_list" => array(  1 => "",
                                                                                        2 => "",
                                                                                        3 => "",
                                                                                        4 => "",
                                                                                        5 => "",
                                                                                        6 => ""));
                    
                    $sql2 = "SELECT 1 'Pref', mb.mb_id, mb.mb_name " .
                            "FROM scout sc " .
                            "LEFT JOIN merit_badge mb ON mb.mb_id = sc.pref1_mb_id " .
                            "WHERE sc.scout_id = " . $row[scout_id] . " " .
                            "UNION " .
                            "SELECT 2 'Pref', mb.mb_id, mb.mb_name " .
                            "FROM scout sc " .
                            "LEFT JOIN merit_badge mb ON mb.mb_id = sc.pref2_mb_id " .
                            "WHERE sc.scout_id = " . $row[scout_id] . " " .
                            "UNION " .
                            "SELECT 3 'Pref', mb.mb_id, mb.mb_name " .
                            "FROM scout sc " .
                            "LEFT JOIN merit_badge mb ON mb.mb_id = sc.pref3_mb_id " .
                            "WHERE sc.scout_id = " . $row[scout_id] . " " .
                            "UNION " .
                            "SELECT 4 'Pref', mb.mb_id, mb.mb_name " .
                            "FROM scout sc " .
                            "LEFT JOIN merit_badge mb ON mb.mb_id = sc.pref4_mb_id " .
                            "WHERE sc.scout_id = " . $row[scout_id] . " " .
                            "UNION " .
                            "SELECT 5 'Pref', mb.mb_id, mb.mb_name " .
                            "FROM scout sc " .
                            "LEFT JOIN merit_badge mb ON mb.mb_id = sc.pref5_mb_id " .
                            "WHERE sc.scout_id = " . $row[scout_id] . " " .
                            "UNION " .
                            "SELECT 6 'Pref', mb.mb_id, mb.mb_name " .
                            "FROM scout sc " .
                            "LEFT JOIN merit_badge mb ON mb.mb_id = sc.pref6_mb_id " .
                            "WHERE sc.scout_id = " . $row[scout_id] . " ";

                    $result2 = $conn->query($sql2);
                    
                    if($result2->num_rows > 0)
                    {
                        end($unit['scout_list']);
                        
                        while($row2 = $result2->fetch_assoc()){
                            $unit['scout_list'][key($unit['scout_list'])]['class_list'][$row2[Pref]] = array("mb_name" => $row2[mb_name]);
                        }              
                    }
                }
            }
            else  // When we don't have any Scouts for this unit registered yet, return one blank one for the UI table
            {
                $unit['scout_list'][] = array(  "scout_id" => $row[scout_id],
                                                                "first_name" => "",
                                                                "last_name" => "",
                                                                "class_list" => array(  1 => "",
                                                                                        2 => "",
                                                                                        3 => "",
                                                                                        4 => "",
                                                                                        5 => "",
                                                                                        6 => ""));
            }
        }
        unset($unit);
        
        $conn->close();
        
        $this->data = $retArray;
        echo json_encode($this->data);
    }
    
    function validateAdmin($servername, $username, $password, $dbname, $authToken) {
        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql = "SELECT ma.is_admin, ma.unit_id " .
                "FROM mbreg_account ma " .
                "WHERE ma.uuid = '" . $authToken . "'";
        $result = $conn->query($sql);

        $ret = array();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            if ($row[is_admin] == 1) {
                $hasUnit = "false";
                if(!empty($row[unit_id]))
                {
                    $hasUnit = "true";
                }
                
                $ret = array(
                    "success" => "true",
                    "hasUnit" => $hasUnit
                );
            } else {
                setcookie('mbreg_user', '', time() - 3600, '/', 'firemtn.org', FALSE, TRUE);
                $ret = array(
                    "success" => "false"
                );
            }
        } else {
            setcookie('mbreg_user', '', time() - 3600, '/', 'firemtn.org', FALSE, TRUE);
            $ret = array(
                "success" => "false"
            );
        }

        $conn->close();

        $this->data = $ret;
        echo json_encode($this->data);
    }

    function authenticationError() {
        setcookie('mbreg_user', '', time() - 3600, '/', 'firemtn.org', FALSE, TRUE);
        $error = array(
            "success" => "false",
            "error" => "No valid token"
        );
        $this->data = $error;
        echo json_encode($this->data);
    }

}


?>
	