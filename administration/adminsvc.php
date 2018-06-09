<?php

$params = $_REQUEST;

$action = isset($params['action']) != '' ? $params['action'] : '';
$adminCls = new Admin();

$servername = "localhost";
$username = "firemtn_reguser";
$password = "F1r3M0unt@!n";
$dbname = "firemtn_mbreg";

$authToken = "";

if(!isset($_COOKIE['mbreg_user']) && $action != 'login') {
    $action = "authError";
} else {
    $authToken = $_COOKIE['mbreg_user'];
}

/*
 * Service call routing function based on $params['action'] passed
 */
switch ($action) {
    case 'checkAdmin':
        $adminCls->validateAdmin($params, $servername, $username, $password, $dbname, $authToken);
        break;
    case 'unitCountByWeek':
        $adminCls->unitCountByWeek($servername, $username, $password, $dbname);
        break;
    case 'scoutCountByWeek':
        $adminCls->scoutCountByWeek($servername, $username, $password, $dbname);
        break;
    case 'preferenceCountByWeek':
        $adminCls->preferenceCountByWeek($servername, $username, $password, $dbname);
        break;
    default:
        $adminCls->validateAdmin($params, $servername, $username, $password, $dbname, $authToken);
        return;
}

class Admin {

    protected $data = array();

    function __construct() {
        
    }
    
    /*
     * Get JSON of sum of top 4 prferences for each class by week.
     * To be used for Google Charts on Dashboard.
     */
    function preferenceCountByWeek($servername, $username, $password, $dbname) {        
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql =  "SELECT mb.mb_id, mb.mb_name, count(sc1.scout_id) 'Scout_Count_1', count(sc2.scout_id) 'Scout_Count_2', " . 
                "count(sc3.scout_id) 'Scout_Count_3', count(sc4.scout_id) 'Scout_Count_4', count(sc5.scout_id) 'Scout_Count_5', " . 
                "count(sc6.scout_id) 'Scout_Count_6', count(sc7.scout_id) 'Scout_Count_7' " .
                "FROM merit_badge mb " .
                "LEFT JOIN scout sc1 ON (sc1.pref1_mb_id = mb.mb_id OR sc1.pref2_mb_id = mb.mb_id OR sc1.pref3_mb_id = mb.mb_id OR sc1.pref4_mb_id = mb.mb_id) " .
                    "AND (sc1.scout_id IN (SELECT sc11.scout_id FROM scout sc11 JOIN unit ut11 ON ut11.unit_id = sc11.unit_id JOIN week wk11 ON wk11.week_id = ut11.week_id WHERE wk11.week_number = 1)) " .
                "LEFT JOIN scout sc2 ON (sc2.pref1_mb_id = mb.mb_id OR sc2.pref2_mb_id = mb.mb_id OR sc2.pref3_mb_id = mb.mb_id OR sc2.pref4_mb_id = mb.mb_id) " . 
                    "AND (sc2.scout_id IN (SELECT sc22.scout_id FROM scout sc22 JOIN unit ut22 ON ut22.unit_id = sc22.unit_id JOIN week wk22 ON wk22.week_id = ut22.week_id WHERE wk22.week_number = 2)) " .
                "LEFT JOIN scout sc3 ON (sc3.pref1_mb_id = mb.mb_id OR sc3.pref2_mb_id = mb.mb_id OR sc3.pref3_mb_id = mb.mb_id OR sc3.pref4_mb_id = mb.mb_id) " . 
                    "AND (sc3.scout_id IN (SELECT sc33.scout_id FROM scout sc33 JOIN unit ut33 ON ut33.unit_id = sc33.unit_id JOIN week wk33 ON wk33.week_id = ut33.week_id WHERE wk33.week_number = 3)) " .
                "LEFT JOIN scout sc4 ON (sc4.pref1_mb_id = mb.mb_id OR sc4.pref2_mb_id = mb.mb_id OR sc4.pref3_mb_id = mb.mb_id OR sc4.pref4_mb_id = mb.mb_id) " . 
                    "AND (sc4.scout_id IN (SELECT sc44.scout_id FROM scout sc44 JOIN unit ut44 ON ut44.unit_id = sc44.unit_id JOIN week wk44 ON wk44.week_id = ut44.week_id WHERE wk44.week_number = 4)) " .
                "LEFT JOIN scout sc5 ON (sc5.pref1_mb_id = mb.mb_id OR sc5.pref2_mb_id = mb.mb_id OR sc5.pref3_mb_id = mb.mb_id OR sc5.pref4_mb_id = mb.mb_id) " .
                    "AND (sc5.scout_id IN (SELECT sc55.scout_id FROM scout sc55 JOIN unit ut55 ON ut55.unit_id = sc55.unit_id JOIN week wk55 ON wk55.week_id = ut55.week_id WHERE wk55.week_number = 5)) " .
                "LEFT JOIN scout sc6 ON (sc6.pref1_mb_id = mb.mb_id OR sc6.pref2_mb_id = mb.mb_id OR sc6.pref3_mb_id = mb.mb_id OR sc6.pref4_mb_id = mb.mb_id) " .
                    "AND (sc6.scout_id IN (SELECT sc66.scout_id FROM scout sc66 JOIN unit ut66 ON ut66.unit_id = sc66.unit_id JOIN week wk66 ON wk66.week_id = ut66.week_id WHERE wk66.week_number = 6)) " .
                "LEFT JOIN scout sc7 ON (sc7.pref1_mb_id = mb.mb_id OR sc7.pref2_mb_id = mb.mb_id OR sc7.pref3_mb_id = mb.mb_id OR sc7.pref4_mb_id = mb.mb_id) " .
                    "AND (sc7.scout_id IN (SELECT sc77.scout_id FROM scout sc77 JOIN unit ut77 ON ut77.unit_id = sc77.unit_id JOIN week wk77 ON wk77.week_id = ut77.week_id WHERE wk77.week_number = 7)) " .
                "GROUP BY mb.mb_id, mb.mb_name " .
                "HAVING count(sc1.scout_id) > 0 OR count(sc2.scout_id) > 0 OR count(sc3.scout_id) > 0 OR count(sc4.scout_id) > 0 OR count(sc5.scout_id) > 0 OR count(sc6.scout_id) > 0 OR count(sc7.scout_id) > 0 " .
                "ORDER BY mb.mb_name";
        $result = $conn->query($sql);
        
        $ret = array();
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()){
                    $sql2 = "SELECT mb.mb_id, mb.mb_name, sum(cs.size_limit) 'Size_Limit' " .
                            "FROM merit_badge mb " .
                            "JOIN class_session cs ON cs.merit_badge_id = mb.mb_id " .
                            "WHERE mb.mb_id = " . $row[mb_id] . " " .
                            "GROUP BY mb.mb_id, mb.mb_name " .
                            "ORDER BY mb.mb_name";
                    $result2 = $conn->query($sql2);
                    
                    $class_max = 0;
                    
                    if ($result2->num_rows > 0) {
                        $row2 = $result2->fetch_assoc();
                        
                        $class_max = $row2[Size_Limit];
                    }                    
                
                    $ret[] = array( "class_name" => $row[mb_name],
                                    "scout_count_1" => $row[Scout_Count_1],
                                    "scout_count_2" => $row[Scout_Count_2],
                                    "scout_count_3" => $row[Scout_Count_3],
                                    "scout_count_4" => $row[Scout_Count_4],
                                    "scout_count_5" => $row[Scout_Count_5],
                                    "scout_count_6" => $row[Scout_Count_6],
                                    "scout_count_7" => $row[Scout_Count_7],
                                    "size_limit" => $class_max);
                }
        }
        
        $conn->close();
        
        $this->data = $ret;
        echo json_encode($this->data);
    }
    
    /*
     * Get JSON of the number of Units per week.
     * To be used for Google Charts on Dashboard.
     */
    function scoutCountByWeek($servername, $username, $password, $dbname) {

        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql =  "SELECT wk.week_number 'Week', count(sc.scout_id) 'Scout_Count' " .
                "FROM week wk " .
                "LEFT JOIN unit ut ON ut.unit_id = wk.week_id " .
                "LEFT JOIN scout sc ON sc.unit_id = ut.unit_id " .
                "GROUP BY wk.week_number " .
                "ORDER BY wk.week_number";
        $result = $conn->query($sql);
        
        $ret = array();
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()){
                    $ret[] = array(  "week" => $row[Week],
                                    "scout_count" => $row[Scout_Count]);
                }
        }
        
        $conn->close();
        
        $this->data = $ret;
        echo json_encode($this->data);
    }
    
    /*
     * Get JSON of the number of Units per week.
     * To be used for Google Charts on Dashboard.
     */
    function unitCountByWeek($servername, $username, $password, $dbname) {

        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql =  "SELECT wk.week_number 'Week', count(ut.unit_id) 'Unit_Count' " .
                "FROM unit ut " .
                "JOIN week wk ON wk.week_id = ut.week_id " .
                "GROUP BY wk.week_number " .
                "ORDER BY wk.week_number";
        $result = $conn->query($sql);
        
        $ret = array();
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()){
                    $ret[] = array(  "week" => $row[Week],
                                    "unit_count" => $row[Unit_Count]);
                }
        }
        
        $conn->close();
        
        $this->data = $ret;
        echo json_encode($this->data);
    }
    
    function validateAdmin($params, $servername, $username, $password, $dbname, $authToken) {
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
        }
        else
        {
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
	