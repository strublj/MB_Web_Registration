<?php

$params = $_REQUEST;

$action = isset($params['action']) != '' ? $params['action'] : '';
$scoutCls = new Scout();

$servername = "localhost";
$username = "firemtn_reguser";
$password = "F1r3M0unt@!n";
$dbname = "firemtn_mbreg";

$salt1 = "p@!gH";
$salt2 = "ABx$3";

/*
 * Service call routing function based on $params['action'] passed
 */
switch ($action) {
    default:
        $scoutCls->passwordReset($params, $servername, $username, $password, $dbname);
        return;
}

class Scout {
    
    protected $data = array();

    private $salt1 = "p@!gH";
    private $salt2 = "ABx$3";
    
    function __construct() {
        
    }

    /**
     * Prepares the given string to be passed to a MySQL query.
     * @param string $var
     * @return string Ready to be passed to MySQL
     */
    function sanitizeMySQL($var, $conn)
    {
        $var = mysqli_real_escape_string($conn, $var);
        $var = $this->sanitizeString($var);
        return $var;
    }
    
    /**
    * Uses several PHP functions to sanitize the given string.
    * @param string $var
    * @return string Sanitized string
    */
   function sanitizeString($var)
   {
       $var = stripslashes($var);
       $var = htmlentities($var);
       $var = strip_tags($var);
       return $var;
   }
    
    /*
     * Updates password in the database.
     */
    public function passwordReset($params, $servername, $username, $password, $dbname) {
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        $new_password = md5($salt1 . $params["password"]. $salt2);
        
        $sql = "UPDATE mbreg_account SET password = '" . $this->sanitizeMySQL($new_password, $conn) . "' WHERE uuid = '" . $params["uuid"] . "'";
        
        $conn->query($sql);
        
        $ret = array(
                        "success" => "true"
                    );
        
        $conn->close();

        $this->data = $ret;

        echo json_encode($this->data);
    }
}
?>
	