<?php

$params = $_REQUEST;

$action = isset($params['action']) != '' ? $params['action'] : '';
$sessionMgmtCls = new SessionMgmt();

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
        $sessionMgmtCls->validateAdmin($servername, $username, $password, $dbname, $authToken);
        break;
    case 'getClasses':
        $sessionMgmtCls->getListOfClasses($servername, $username, $password, $dbname);
        break;
    case 'getClass':
        $sessionMgmtCls->getClass($servername, $username, $password, $dbname, $params);
        break;
    case 'getSessions':
        $sessionMgmtCls->getSessions($servername, $username, $password, $dbname, $params);
        break;
    case 'addUpdateClass':
        $sessionMgmtCls->addUpdateClass($servername, $username, $password, $dbname, $params, $authToken);
        break;
    case 'deleteClass':
        $sessionMgmtCls->deleteClass($servername, $username, $password, $dbname, $params);
        break;
    case 'sessionList':
        $sessionMgmtCls->sessionList($servername, $username, $password, $dbname);
        break;
    case 'getSessionDetails':
        $sessionMgmtCls->getSessionDetails($servername, $username, $password, $dbname, $params);
        break;
    case 'addUpdateSession':
        $sessionMgmtCls->addUpdateSession($servername, $username, $password, $dbname, $params, $authToken);
        break;
    case 'deleteSession':
        $sessionMgmtCls->deleteSession($servername, $username, $password, $dbname, $params);
        break;
    default:
        $sessionMgmtCls->validateAdmin($servername, $username, $password, $dbname, $authToken);
        return;
}

class SessionMgmt {

    protected $data = array();

    function __construct() {
        
    }
    
    /*
     * Get the list of classes to manage
     */
    public function getListOfClasses($servername, $username, $password, $dbname) {
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql = "SELECT mb.mb_id, mb.mb_name FROM merit_badge mb ORDER BY mb.mb_name ASC";
        $result = $conn->query($sql);
        
        if($result->num_rows > 0)
        {
            $week_list = array();
            while($row = $result->fetch_assoc()){
                $week_list[] = array("mb_id" => $row[mb_id],
                                      "mb_name" => $row[mb_name]);
            }
        }
        $conn->close();

        $this->data = $week_list;

        echo json_encode($this->data);
    }
    
        public function sessionList($servername, $username, $password, $dbname) {
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql = "SELECT session_id, session_number FROM session ORDER BY session_number ASC";
        $result = $conn->query($sql);
        
        if($result->num_rows > 0)
        {
            $session_list = array();
            while($row = $result->fetch_assoc()){
                $session_list[] = array("session_id" => $row[session_id],
                                      "session_name" => $row[session_number]);
            }
        }
        $conn->close();

        $this->data = $session_list;

        echo json_encode($this->data);
    }
    
    public function getSessionDetails($servername, $username, $password, $dbname, $params) {
        $session_id = $params['sessionId'];
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql = "SELECT cs.class_session_id, cs.start_session_id, ss.session_number, cs.number_of_sessions, cs.size_limit " .
                "FROM class_session cs " .
                "JOIN session ss ON ss.session_id = cs.start_session_id " .
                "WHERE cs.class_session_id = " . $session_id . " ";
        $result = $conn->query($sql);
        
        if($result->num_rows > 0)
        {
            $session_details = array();
            while($row = $result->fetch_assoc()){
                $session_details = array("class_session_id" => $row[class_session_id],
                                        "start_session_id" => $row[start_session_id],
                                        "start_session_name" => $row[session_number],
                                        "number_session_id" => $row[number_of_sessions],
                                        "number_session_name" => $row[number_of_sessions],
                                        "size_limit" => $row[size_limit]);
            }
        }
        $conn->close();

        $this->data = $session_details;

        echo json_encode($this->data);
    }
    
    public function getClass($servername, $username, $password, $dbname, $params) {
        $mb_id = $params['classId'];
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql = "SELECT mb.mb_id, mb.mb_name, mb.eagle_req, mb.stem, mb.pre_req, " .
                "mb.area, mb.location, mb.cost, mb.difficulty, mb.min_age, mb.min_rank_id, ra.rank_name, mb.mb_notes " .
                "FROM merit_badge mb " .
                "LEFT JOIN bsa_rank ra ON ra.rank_id = mb.min_rank_id " .
                "WHERE mb.mb_id = " . $mb_id . " " ;
        $result = $conn->query($sql);
        
        $class_list = array();
        
        if($result->num_rows > 0)
        {
            while($row = $result->fetch_assoc()){
                $class_list = array("mb_id" => $row[mb_id],
                                      "mb_name" => $row[mb_name],
                                      "eagle_req" => $row[eagle_req],
                                      "stem" => $row[stem],
                                      "pre_req" => $row[pre_req],
                                      "area" => $row[area],
                                      "location" => $row[location],
                                      "cost" => $row[cost],
                                      "difficulty" => $row[difficulty],
                                      "min_age" => $row[min_age],
                                      "min_rank_id" => $row[min_rank_id],
                                      "min_rank_name" => $row[rank_name],
                                      "mb_notes" => $row[mb_notes]);
            }
        }
        $conn->close();
        
        $this->data = $class_list;

        echo json_encode($this->data);
    }
    
    /*
     * Get the list of sessions for the given class.
     */
    public function getSessions($servername, $username, $password, $dbname, $params) {        
        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql = "SELECT cs.class_session_id, ss.session_number, cs.number_of_sessions, cs.size_limit " .
                "FROM class_session cs " .
                "JOIN session ss ON ss.session_id = cs.start_session_id " .
                "WHERE cs.merit_badge_id = " . (empty($params['classId']) ? '0' : $params['classId']) . " " .
                "ORDER BY cs.start_session_id";
        $result = $conn->query($sql);
        
        $session_list = array();
        
        if($result->num_rows > 0)
        {
            while($row = $result->fetch_assoc()){
                $session_list[] = array(
                                "class_session_id" => $row[class_session_id],
                                "start_session" => $row[session_number],
                                "number_sessions" => $row[number_of_sessions],
                                "size_limit" => $row[size_limit]
                            );
            }
        }
        $conn->close();

        $this->data = $session_list;
        echo json_encode($this->data);
    }
    
    /*
     * If the mb_id is 0 then create a new entry in the merit_badge table
     * otherwise update the existing information for the given mb_id.
     */
    public function addUpdateClass($servername, $username, $password, $dbname, $params, $authToken) {
        $mb_id = $params['mb_id'];
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if($mb_id == 0)
        {
            $sql =  "INSERT INTO merit_badge " . 
                "(mb_name, area, location, cost, min_age, difficulty, pre_req, min_rank_id, eagle_req, stem, mb_notes) " .
                "SELECT '" . addslashes($params['mb_name']) . "' " . 
                ", " . (empty($params['area']) ? 'NULL' : "'" . addslashes($params['area']) . "' ") .
                ", " . (empty($params['location']) ? 'NULL' : "'". addslashes($params['location']) . "' ") .
                ", " . (empty($params['cost']) ? 'NULL' : $params['cost']) .
                ", " . (empty($params['min_age']) ? 'NULL' : $params['min_age']) .
                ", " . (empty($params['difficulty']) ? 'NULL' : "'". addslashes($params['difficulty']) . "' ") .
                ", " . (empty($params['pre_req']) ? 'NULL' : "'". addslashes($params['pre_req']) . "' ") .
                ", " . (empty($params['min_rank_id']) ? 'NULL' : $params['min_rank_id']) .
                ", " . $params['eagle_req'] .
                ", " . $params['stem'] .
                ", " . (empty($params['mb_notes']) ? 'NULL' : "'". addslashes($params['mb_notes']) . "' ") .
                " FROM mbreg_account ma WHERE ma.uuid = '" . $authToken . "'";
        }
        else
        {
            $sql =  "UPDATE merit_badge SET " . 
                "mb_name = '" . addslashes($params['mb_name']) . "' " . 
                ", area = " . (empty($params['area']) ? 'NULL' : "'". addslashes($params['area']) . "' ") .
                ", location = " . (empty($params['location']) ? 'NULL' : "'". addslashes($params['location']) . "' ") .
                ", cost = " .(empty($params['cost']) ? 'NULL' : $params['cost']) .
                ", min_age = " . (empty($params['min_age']) ? 'NULL' : $params['min_age']) .
                ", difficulty = " . (empty($params['difficulty']) ? 'NULL' : "'". addslashes($params['difficulty']) . "' ") .
                ", pre_req = " . (empty($params['pre_req']) ? 'NULL' : "'". addslashes($params['pre_req']) . "' ") .
                ", min_rank_id = " . (empty($params['min_rank_id']) ? 'NULL' : $params['min_rank_id']) .
                ", eagle_req = " . $params['eagle_req'] .
                ", stem = " . $params['stem'] .
                ", mb_notes = " . (empty($params['mb_notes']) ? 'NULL' : "'". addslashes($params['mb_notes']) . "' ") .
                " WHERE mb_id = " . $params['mb_id'];
        }
        
        $conn->query($sql);
        $conn->close();
        
        $ret = array(
                "success" => "true"
            );
        $this->data = $ret;
        echo json_encode($this->data);
    }
    
    /*
     * If the mb_id is 0 then create a new entry in the class_session table
     * otherwise update the existing information for the given class_session_id.
     */
    public function addUpdateSession($servername, $username, $password, $dbname, $params, $authToken) {
        $class_session_id = $params['class_session_id'];
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if($class_session_id == 0)
        {
            $sql =  "INSERT INTO class_session " . 
                "(merit_badge_id, start_session_id, number_of_sessions, size_limit) " .
                "SELECT " . $params['mb_id'] . " " . 
                ", " . $params['start_session_id'] . " " .
                ", " . $params['number_sessions'] . " " .
                ", " . $params['size_limit'] . " " .
                " FROM mbreg_account ma WHERE ma.uuid = '" . $authToken . "'";
        }
        else
        {
            $sql =  "UPDATE class_session SET " . 
                "merit_badge_id = " . $params['mb_id'] . " " . 
                ", start_session_id = " . $params['start_session_id'] . " " . 
                ", number_of_sessions = " . $params['number_sessions'] . " " . 
                ", size_limit = " . $params['size_limit'] . " " . 
                " WHERE class_session_id = " . $class_session_id . " ";
        }
        
        $conn->query($sql);
        $conn->close();
        
        $ret = array(
                "success" => "true"
            );
        $this->data = $ret;
        echo json_encode($this->data);
    }
    
    /*
     * Delete the given class from both the class_session table and merit_badge.
     */
    public function deleteClass($servername, $username, $password, $dbname, $params) {
        $mb_id = $params['mb_id'];
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        $sql = "DELETE FROM class_session WHERE merit_badge_id = " . $mb_id;
        $conn->query($sql);
        $sql = "DELETE FROM merit_badge WHERE mb_id = " . $mb_id;
        $conn->query($sql);
        
        $conn->close();
        
        $ret = array(
                "success" => "true"
            );
        $this->data = $ret;
        echo json_encode($this->data);
    }
    
    /*
     * Delete the given Class Session from the class_session table
     */
    public function deleteSession($servername, $username, $password, $dbname, $params) {
        $class_session_id = $params['session_id'];
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        $sql = "DELETE FROM class_session WHERE class_session_id = " . $class_session_id;
        $conn->query($sql);
        
        $conn->close();
        
        $ret = array(
                "success" => "true"
            );
        $this->data = $ret;
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
	