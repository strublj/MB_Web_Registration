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
        $sql_classes =  "SELECT mb.mb_id, mb.mb_name " . 
                        "FROM merit_badge mb " .
                        "ORDER BY mb.mb_name";
        $result_classes = $conn->query($sql_classes);
        
        $ret = array();
        
        if ($result_classes->num_rows > 0) {
            while($row_class = $result_classes->fetch_assoc()){
                    $sql_size = "SELECT mb.mb_id, mb.mb_name, sum(cs.size_limit) 'Size_Limit' " .
                            "FROM merit_badge mb " .
                            "JOIN class_session cs ON cs.merit_badge_id = mb.mb_id " .
                            "WHERE mb.mb_id = " . $row_class[mb_id] . " " .
                            "GROUP BY mb.mb_id, mb.mb_name " .
                            "ORDER BY mb.mb_name";
                    $result_size = $conn->query($sql_size);
                    
                    $class_max = 0;
                    
                    if ($result_size->num_rows > 0) {
                        $row_size = $result_size->fetch_assoc();
                        
                        $class_max = $row_size[Size_Limit];
                    }
                    
                    $sql_week1 =    "SELECT mb.mb_id, mb.mb_name, count(mb.mb_id) 'sum' " .
                                    "FROM merit_badge mb " .
                                    "LEFT JOIN scout sc1 ON ((sc1.pref1_mb_id = mb.mb_id OR sc1.pref2_mb_id = mb.mb_id OR sc1.pref3_mb_id = mb.mb_id OR sc1.pref4_mb_id = mb.mb_id) AND sc1.unit_id IN " .
                                        "(SELECT unit_id FROM unit WHERE week_id = 1)) " .
                                    "WHERE mb.mb_id = " . $row_class[mb_id] . " ";
                    $result_week1 = $conn->query($sql_week1);
                    
                    $week1_pref = 0;
                    
                    if ($result_week1->num_rows > 0) {
                        $row_week1 = $result_week1->fetch_assoc();
                        
                        $week1_pref = $row_week1[sum];
                    }
                    
                    $sql_week2 =    "SELECT mb.mb_id, mb.mb_name, count(mb.mb_id) 'sum' " .
                                    "FROM merit_badge mb " .
                                    "LEFT JOIN scout sc1 ON ((sc1.pref1_mb_id = mb.mb_id OR sc1.pref2_mb_id = mb.mb_id OR sc1.pref3_mb_id = mb.mb_id OR sc1.pref4_mb_id = mb.mb_id) AND sc1.unit_id IN " .
                                        "(SELECT unit_id FROM unit WHERE week_id = 2)) " .
                                    "WHERE mb.mb_id = " . $row_class[mb_id] . " ";
                    $result_week2 = $conn->query($sql_week2);
                    
                    $week2_pref = 0;
                    
                    if ($result_week2->num_rows > 0) {
                        $row_week2 = $result_week2->fetch_assoc();
                        
                        $week2_pref = $row_week2[sum];
                    }
                
                    $sql_week3 =    "SELECT mb.mb_id, mb.mb_name, count(mb.mb_id) 'sum' " .
                                    "FROM merit_badge mb " .
                                    "LEFT JOIN scout sc1 ON ((sc1.pref1_mb_id = mb.mb_id OR sc1.pref2_mb_id = mb.mb_id OR sc1.pref3_mb_id = mb.mb_id OR sc1.pref4_mb_id = mb.mb_id) AND sc1.unit_id IN " .
                                        "(SELECT unit_id FROM unit WHERE week_id = 3)) " .
                                    "WHERE mb.mb_id = " . $row_class[mb_id] . " ";
                    $result_week3 = $conn->query($sql_week3);
                    
                    $week3_pref = 0;
                    
                    if ($result_week3->num_rows > 0) {
                        $row_week3 = $result_week3->fetch_assoc();
                        
                        $week3_pref = $row_week3[sum];
                    }
                    
                    $sql_week4 =    "SELECT mb.mb_id, mb.mb_name, count(mb.mb_id) 'sum' " .
                                    "FROM merit_badge mb " .
                                    "LEFT JOIN scout sc1 ON ((sc1.pref1_mb_id = mb.mb_id OR sc1.pref2_mb_id = mb.mb_id OR sc1.pref3_mb_id = mb.mb_id OR sc1.pref4_mb_id = mb.mb_id) AND sc1.unit_id IN " .
                                        "(SELECT unit_id FROM unit WHERE week_id = 4)) " .
                                    "WHERE mb.mb_id = " . $row_class[mb_id] . " ";
                    $result_week4 = $conn->query($sql_week4);
                    
                    $week4_pref = 0;
                    
                    if ($result_week4->num_rows > 0) {
                        $row_week4 = $result_week4->fetch_assoc();
                        
                        $week4_pref = $row_week4[sum];
                    }
                    
                    $sql_week5 =    "SELECT mb.mb_id, mb.mb_name, count(mb.mb_id) 'sum' " .
                                    "FROM merit_badge mb " .
                                    "LEFT JOIN scout sc1 ON ((sc1.pref1_mb_id = mb.mb_id OR sc1.pref2_mb_id = mb.mb_id OR sc1.pref3_mb_id = mb.mb_id OR sc1.pref4_mb_id = mb.mb_id) AND sc1.unit_id IN " .
                                        "(SELECT unit_id FROM unit WHERE week_id = 5)) " .
                                    "WHERE mb.mb_id = " . $row_class[mb_id] . " ";
                    $result_week5 = $conn->query($sql_week5);
                    
                    $week5_pref = 0;
                    
                    if ($result_week5->num_rows > 0) {
                        $row_week5 = $result_week5->fetch_assoc();
                        
                        $week5_pref = $row_week5[sum];
                    }
                    
                    $sql_week6 =    "SELECT mb.mb_id, mb.mb_name, count(mb.mb_id) 'sum' " .
                                    "FROM merit_badge mb " .
                                    "LEFT JOIN scout sc1 ON ((sc1.pref1_mb_id = mb.mb_id OR sc1.pref2_mb_id = mb.mb_id OR sc1.pref3_mb_id = mb.mb_id OR sc1.pref4_mb_id = mb.mb_id) AND sc1.unit_id IN " .
                                        "(SELECT unit_id FROM unit WHERE week_id = 6)) " .
                                    "WHERE mb.mb_id = " . $row_class[mb_id] . " ";
                    $result_week6 = $conn->query($sql_week6);
                    
                    $week6_pref = 0;
                    
                    if ($result_week6->num_rows > 0) {
                        $row_week6 = $result_week6->fetch_assoc();
                        
                        $week6_pref = $row_week6[sum];
                    }
                    
                    $sql_week7 =    "SELECT mb.mb_id, mb.mb_name, count(mb.mb_id) 'sum' " .
                                    "FROM merit_badge mb " .
                                    "LEFT JOIN scout sc1 ON ((sc1.pref1_mb_id = mb.mb_id OR sc1.pref2_mb_id = mb.mb_id OR sc1.pref3_mb_id = mb.mb_id OR sc1.pref4_mb_id = mb.mb_id) AND sc1.unit_id IN " .
                                        "(SELECT unit_id FROM unit WHERE week_id = 7)) " .
                                    "WHERE mb.mb_id = " . $row_class[mb_id] . " ";
                    $result_week7 = $conn->query($sql_week7);
                    
                    $week7_pref = 0;
                    
                    if ($result_week7->num_rows > 0) {
                        $row_week7 = $result_week7->fetch_assoc();
                        
                        $week7_pref = $row_week7[sum];
                    }
                    
                    $ret[] = array( "class_name" => $row_class[mb_name],
                                    "scout_count_1" => $week1_pref,
                                    "scout_count_2" => $week2_pref,
                                    "scout_count_3" => $week3_pref,
                                    "scout_count_4" => $week4_pref,
                                    "scout_count_5" => $week5_pref,
                                    "scout_count_6" => $week6_pref,
                                    "scout_count_7" => $week7_pref,
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
                "LEFT JOIN unit ut ON ut.week_id = wk.week_id " .
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
	