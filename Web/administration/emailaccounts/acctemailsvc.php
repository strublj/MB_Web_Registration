<?php

$params = $_REQUEST;

$action = isset($params['action']) != '' ? $params['action'] : '';
$emailObj = new AccountEmail();

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
        $emailObj->validateAdmin($servername, $username, $password, $dbname, $authToken);
        break;
    case 'getEmailList':
        $emailObj->getEmailList($servername, $username, $password, $dbname, $params);
        break;
    case 'sendEmail':
        $emailObj->sendEmail($servername, $username, $password, $dbname);
        break;
    default:
        $emailObj->validateAdmin($servername, $username, $password, $dbname, $authToken);
        return;
}

class AccountEmail {

    protected $data = array();

    function __construct() {
        
    }

    function sendEmail($servername, $username, $password, $dbname) {
        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql =  "SELECT first_name, last_name, email " .
                "FROM mbreg_account " .
                "WHERE email_sent = 0 " .
                "ORDER BY last_name ASC";
        $result = $conn->query($sql);
        
        if($result->num_rows > 0)
        {
            while($row = $result->fetch_assoc()) {
                
                $to      = $row[email];
                $subject = 'Fire Mountain Merit Badge Registration Information';
                
                $message =  "On behalf of Camp Director, Rich Szymanski:  <br /><br />" .
                            "We are looking forward to your visit to Fire Mountain this summer!  The Merit Badge registration site is now available.  You will register at this address - <a href='http://firemtn.org/mbregistration/'>http://firemtn.org/mbregistration/</a>  On the registration page it will show your troop number and the week you are attending.  You will need to have each scout's full name, age and a list of the classes they would like in order of importance to them. <br /><br />" .
                            "Your login username will be the primary email address provided during registration (where you received this email). <br /><br />" .
                            "To set your password for the first time use the ‘Password reset’ function on the login page, or click here - <a href='http://firemtn.org/mbregistration/forgot/'>http://firemtn.org/mbregistration/forgot/</a>  <br /><br />" .
                            "When doing the password reset you will receive an email from ‘mb_registration@firemtn.org’ with the subject line ‘Fire Mountain Merit Badge Registration Password Reset’.  If you don’t receive the email after a few minutes check your Junk Mail folder. <br /><br />" .
                            "If you are not the individual who will be entering your troop's merit badge info, please forward this login information to which individual will be responsible for entering the scouts' merit badge choices. <br /><br />" .
                            "If you have technical problems with the registration process, please send a detailed description of the problem to  mb_registration@firemtn.org.  Please contact Colleen - ccrabfam@yahoo.com or Stacey - Stacey.Robert@scouting.org if you have any questions or need (non-technical!) assistance with the sign-ups. <br /><br />" .
                            "From all of us at Fire Mountain, we hope you enjoy your time at camp!";
                $headers = 'From:mb_registration@firemtn.org' . "\r\n";
                $headers .= "Content-type: text/html;";
                mail($to, $subject, $message, $headers);
        
            }
        }
        
        $sql =  "UPDATE mbreg_account " .
                "SET email_sent = 1 " .
                "WHERE email_sent = 0 ";
        $conn->query($sql);
        
        $conn->close();
        
        $ret = array("success" => "true");
        $this->data = $ret;
        echo json_encode($this->data);
    }
    
    function getEmailList($servername, $username, $password, $dbname, $params) {
        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql =  "SELECT first_name, last_name, email " .
                "FROM mbreg_account " .
                "WHERE email_sent = 0 " .
                "ORDER BY last_name ASC";
        $result = $conn->query($sql);

        $ret = array();
        
        if($result->num_rows > 0)
        {
            while($row = $result->fetch_assoc()) {
                $ret[] = array(
                    "first" => $row[first_name],
                    "last" => $row[last_name],
                    "email" => $row[email]
                );
            }
        }
        
        $conn->close();
        
        $firsts = array();
        $lasts = array();
        $emails = array();

        foreach ($ret as $person) {
            $firsts[] = $person['first'];
            $lasts[] = $person['last'];
            $emails[] = $person['email'];
        }

        // Check the 'sort' parameter passed by the bootgrid controller (has column ID as key, direction as value)
        if (!empty($params['sort'])) {
            switch (key($params['sort'])) {
                case 'first':
                    switch (current($params['sort'])) {
                        case 'asc':
                            array_multisort($firsts, SORT_ASC, $ret);
                            break;
                        default:
                            array_multisort($firsts, SORT_DESC, $ret);
                    }
                    break;
                case 'last':
                    switch (current($params['sort'])) {
                        case 'asc':
                            array_multisort($lasts, SORT_ASC, $ret);
                            break;
                        default:
                            array_multisort($lasts, SORT_DESC, $ret);
                    }
                    break;
                case 'email':
                    switch (current($params['sort'])) {
                        case 'asc':
                            array_multisort($emails, SORT_ASC, $ret);
                            break;
                        default:
                            array_multisort($emails, SORT_DESC, $ret);
                    }
                    break;
            }
        }
        else {
            array_multisort($lasts, SORT_ASC, $ret);
        }
        
        $json_data = array(
            "current" => 1,
            "rowCount" => -1, //-1 returns all rows (disables pagination)			
            "total" => count($ret),
            "rows" => $ret
        );
        
        echo json_encode($json_data);
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
	