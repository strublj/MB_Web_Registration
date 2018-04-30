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
    default:
        $adminCls->validateAdmin($params, $servername, $username, $password, $dbname, $authToken);
        return;
}

class Admin {

    protected $data = array();

    function __construct() {
        
    }

    function validateAdmin($params, $servername, $username, $password, $dbname, $authToken) {
        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql =  "SELECT ma.is_admin " .
                "FROM mbreg_account ma " .
                "WHERE ma.uuid = '" . $authToken  . "'";
        $result = $conn->query($sql);
        
        $ret = array();
        
        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            
            if($row[is_admin] == 1)
            {
                $ret = array(
                    "success" => "true"
                ); 
            }
            else
            {
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
	