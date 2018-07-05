<?php

$params = $_REQUEST;

$action = isset($params['action']) != '' ? $params['action'] : '';
$scoutCls = new Scout();

$servername = "localhost";
$username = "firemtn_reguser";
$password = "F1r3M0unt@!n";
$dbname = "firemtn_mbreg";

/*
 * Service call routing function based on $params['action'] passed
 */
switch ($action) {
    default:
        $scoutCls->passwordResetRequest($params, $servername, $username, $password, $dbname);
        return;
}

class Scout {

    protected $data = array();

    function __construct() {
        
    }

    /*
     * Sends email for password reset.
     */

    public function passwordResetRequest($params, $servername, $username, $password, $dbname) {
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        $sql = "SELECT email, uuid FROM mbreg_account WHERE login = '" . $params["username"] . "'";
        
        $result = $conn->query($sql);
        
        $ret = array();
        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            $email = $row[email];
            $uuid = $row[uuid];
            $ret = array(
                        "success" => "true"
                    );
            
            $to      = $email;
            $subject = 'Fire Mountain Merit Badge Registration Password Reset';
            $message = 'A password reset was requested for your Fire Mountain Merit Badge ' . 
                        'Registration account.  If you did not request this please disregard this email, ' . 
                        'otherwise click this link to reset your password: <br /><br />' .
                        '<a href="http://firemtn.org/mbregistration/reset?uuid=' . $uuid . '">http://firemtn.org/mbregistration/reset?uuid=' . $uuid . '</a>';
            $headers = 'From:mb_registration@firemtn.org' . "\r\n";
            $headers .= "Content-type: text/html;";
            mail($to, $subject, $message, $headers);
        }
        else {
            $ret = array(
                        "success" => "false"
                    );
        }
        
        $conn->close();

        $this->data = $ret;

        echo json_encode($this->data);
    }
    
}

?>
	