<?php

$params = $_REQUEST;

$action = isset($params['action']) != '' ? $params['action'] : '';
$scoutCls = new Scout();

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
    case 'add':
        $scoutCls->insertScoutRegistration($params, $servername, $username, $password, $dbname, $authToken);
        break;
    case 'edit':
        $scoutCls->updateScoutRegistration($params, $servername, $username, $password, $dbname);
        break;
    case 'classList':
        $scoutCls->getListOfClasses($params, $servername, $username, $password, $dbname);
        break;
    case 'rankList':
        $scoutCls->getListOfRanks($params, $servername, $username, $password, $dbname);
        break;
    case 'getScoutReg':
        $scoutCls->getIndividualScoutRegistration($params, $servername, $username, $password, $dbname);
        break;
    case 'getUnitInfo':
        $scoutCls->getUnitInformation($params, $servername, $username, $password, $dbname, $authToken);
        break;
    case 'delete':
        $scoutCls->deleteScoutRegistration($params, $servername, $username, $password, $dbname);
        break;
    case 'login':
        $scoutCls->login($params, $servername, $username, $password, $dbname);
        break;
    case 'logout':
        $scoutCls->logout($authToken);
        break;
    case 'authError':
        $scoutCls->authenticationError();
        break;
    default:
        $scoutCls->getScoutRegistrations($params, $servername, $username, $password, $dbname, $authToken);
        return;
}

class Scout {

    protected $data = array();

    private $salt1 = "p@!gH";
    private $salt2 = "ABx$3";
    
    function __construct() {
        
    }

    /*
     * Returns the list of all the classes.
     */

    public function getListOfClasses($params, $servername, $username, $password, $dbname) {
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        if(empty($params["age"]))
        {
            $sql = "SELECT mb_id, mb_name FROM merit_badge ORDER BY mb_name ASC";
        }
        else
        {
            $sql = "SELECT mb_id, mb_name FROM merit_badge WHERE " . $params["age"] . " >= min_age OR min_age IS NULL ORDER BY mb_name ASC";
        }
        $result = $conn->query($sql);
        
        if($result->num_rows > 0)
        {
            $class_list = array();
            while($row = $result->fetch_assoc()){
                $class_list[] = array("class_id" => $row[mb_id],
                                      "class_name" => $row[mb_name]);
            }
        }
        $conn->close();

        $classids = array();
        $classnames = array();
        foreach ($class_list as $class) {
            $classids[] = $class['class_id'];
            $classnames[] = $class['class_name'];
        }

        array_multisort($classnames, SORT_ASC, $class_list);

        $this->data = $class_list;

        echo json_encode($this->data);
    }
    
    /*
     * Returns the list of all the classes.
     */

    public function getListOfRanks($params, $servername, $username, $password, $dbname) {
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql = "SELECT rank_id, rank_name FROM bsa_rank ORDER BY rank_id ASC";
        $result = $conn->query($sql);
        
        if($result->num_rows > 0)
        {
            $rank_list = array();
            while($row = $result->fetch_assoc()){
                $rank_list[] = array("rank_id" => $row[rank_id],
                                      "rank_name" => $row[rank_name]);
            }
        }
        $conn->close();

        $rankids = array();
        $ranknames = array();
        foreach ($rank_list as $rank) {
            $rankids[] = $rank['rank_id'];
            $ranknames[] = $rank['rank_name'];
        }

        array_multisort($rankids, SORT_ASC, $rank_list);

        $this->data = $rank_list;

        echo json_encode($this->data);
    }

    /*
     * Passthrough function to get all of the registration entries
     */

    public function getScoutRegistrations($params, $servername, $username, $password, $dbname, $authToken) {

        $this->data = $this->getAllRegistrations($params, $servername, $username, $password, $dbname, $authToken);
        echo json_encode($this->data);
    }

    /*
     * Returns the list of all registrations (will need to look at troop in query)
     * 
     * Also handles sorting and searches on the grid controller:
     * $params['searchPhrase'] holds search string
     * $params['sort'] has key of column name, value of direction
     */

    function getAllRegistrations($params, $servername, $username, $password, $dbname, $authToken) {

        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql = "SELECT sc.scout_id, sc.first_name, sc.last_name, br.rank_name, " .
                       "sc.age, mb1.mb_name AS mb_pref1, mb2.mb_name AS mb_pref2, " .
                       "mb3.mb_name AS mb_pref3, mb4.mb_name AS mb_pref4, mb5.mb_name AS mb_pref5, mb6.mb_name AS mb_pref6 " .
                "FROM scout sc " .
                "JOIN unit ut ON ut.unit_id = sc.unit_id " .
                "JOIN mbreg_account ma ON ma.unit_id = ut.unit_id " .
                "LEFT JOIN bsa_rank br ON sc.rank_id = br.rank_id " .
                "LEFT JOIN merit_badge mb1 ON sc.pref1_mb_id = mb1.mb_id " .
                "LEFT JOIN merit_badge mb2 ON sc.pref2_mb_id = mb2.mb_id " .
                "LEFT JOIN merit_badge mb3 ON sc.pref3_mb_id = mb3.mb_id " .
                "LEFT JOIN merit_badge mb4 ON sc.pref4_mb_id = mb4.mb_id " .
                "LEFT JOIN merit_badge mb5 ON sc.pref5_mb_id = mb5.mb_id " .
                "LEFT JOIN merit_badge mb6 ON sc.pref6_mb_id = mb6.mb_id " .
                "WHERE ma.uuid = '" . $authToken . "' " .
                "ORDER BY sc.last_name ASC";
        $result = $conn->query($sql);
        
        $reg_list = array();
        
        if($result->num_rows > 0)
        {
            while($row = $result->fetch_assoc()){
                $reg_list[] = array(
                                "scout_id" => $row[scout_id],
                                "scout_first" => $row[first_name],
                                "scout_last" => $row[last_name],
                                "scout_age" => $row[age],
                                "scout_rank" => $row[rank_name],
                                "scout_pref1" => $row[mb_pref1],
                                "scout_pref2" => $row[mb_pref2],
                                "scout_pref3" => $row[mb_pref3],
                                "scout_pref4" => $row[mb_pref4],
                                "scout_pref5" => $row[mb_pref5],
                                "scout_pref6" => $row[mb_pref6]
                            );
            }
        }
        $conn->close();

        $first = array();
        $last = array();
        $age = array();
        $rank = array();
        $pref1 = array();
        $pref2 = array();
        $pref3 = array();
        $pref4 = array();
        $pref5 = array();
        $pref6 = array();
        foreach ($reg_list as $reg) {
            $first[] = $reg['scout_first'];
            $last[] = $reg['scout_last'];
            $age[] = $reg['scout_age'];
            $rank[] = $reg['scout_rank'];
            $pref1[] = $reg['scout_pref1'];
            $pref2[] = $reg['scout_pref2'];
            $pref3[] = $reg['scout_pref3'];
            $pref4[] = $reg['scout_pref4'];
            $pref5[] = $reg['scout_pref5'];
            $pref6[] = $reg['scout_pref6'];
        }

        // Check the 'sort' parameter passed by the bootgrid controller (has column ID as key, direction as value)
        if (!empty($params['sort'])) {
            switch (key($params['sort'])) {
                case 'scout_first':
                    switch (current($params['sort'])) {
                        case 'asc':
                            array_multisort($first, SORT_ASC, $reg_list);
                            break;
                        default:
                            array_multisort($first, SORT_DESC, $reg_list);
                    }
                    break;
                case 'scout_last':
                    switch (current($params['sort'])) {
                        case 'asc':
                            array_multisort($last, SORT_ASC, $reg_list);
                            break;
                        default:
                            array_multisort($last, SORT_DESC, $reg_list);
                    }
                    break;
                case 'scout_age':
                    switch (current($params['sort'])) {
                        case 'asc':
                            array_multisort($age, SORT_ASC, $reg_list);
                            break;
                        default:
                            array_multisort($age, SORT_DESC, $reg_list);
                    }
                    break;
                case 'scout_rank':
                    switch (current($params['sort'])) {
                        case 'asc':
                            array_multisort($rank, SORT_ASC, $reg_list);
                            break;
                        default:
                            array_multisort($rank, SORT_DESC, $reg_list);
                    }
                    break;
                case 'scout_pref1':
                    switch (current($params['sort'])) {
                        case 'asc':
                            array_multisort($pref1, SORT_ASC, $reg_list);
                            break;
                        default:
                            array_multisort($pref1, SORT_DESC, $reg_list);
                    }
                    break;
                case 'scout_pref2':
                    switch (current($params['sort'])) {
                        case 'asc':
                            array_multisort($pref2, SORT_ASC, $reg_list);
                            break;
                        default:
                            array_multisort($pref2, SORT_DESC, $reg_list);
                    }
                    break;
                case 'scout_pref3':
                    switch (current($params['sort'])) {
                        case 'asc':
                            array_multisort($pref3, SORT_ASC, $reg_list);
                            break;
                        default:
                            array_multisort($pref3, SORT_DESC, $reg_list);
                    }
                    break;
                case 'scout_pref4':
                    switch (current($params['sort'])) {
                        case 'asc':
                            array_multisort($pref4, SORT_ASC, $reg_list);
                            break;
                        default:
                            array_multisort($pref4, SORT_DESC, $reg_list);
                    }
                    break;
                case 'scout_pref5':
                    switch (current($params['sort'])) {
                        case 'asc':
                            array_multisort($pref5, SORT_ASC, $reg_list);
                            break;
                        default:
                            array_multisort($pref5, SORT_DESC, $reg_list);
                    }
                    break;
                case 'scout_pref6':
                    switch (current($params['sort'])) {
                        case 'asc':
                            array_multisort($pref6, SORT_ASC, $reg_list);
                            break;
                        default:
                            array_multisort($pref6, SORT_DESC, $reg_list);
                    }
                    break;
            }
        }
        // Default sorting is on the first name (per user story)
        else {
            array_multisort($last, SORT_ASC, $reg_list);
        }

        $json_data = array(
            "current" => 1,
            "rowCount" => -1, //-1 returns all rows (disables pagination)			
            "total" => count($reg_list),
            "rows" => $reg_list
        );

        return $json_data;
    }
    
    function getIndividualScoutRegistration($params, $servername, $username, $password, $dbname) {
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql = "SELECT sc.scout_id, sc.first_name, sc.last_name, sc.rank_id, br.rank_name, " .
                       "sc.age, sc.pref1_mb_id, mb1.mb_name AS mb_pref1, sc.pref2_mb_id, mb2.mb_name AS mb_pref2, " .
                       "sc.pref3_mb_id, mb3.mb_name AS mb_pref3, sc.pref4_mb_id, mb4.mb_name AS mb_pref4, " .
                        "sc.pref5_mb_id, mb5.mb_name AS mb_pref5, sc.pref6_mb_id, mb6.mb_name AS mb_pref6 " .
                "FROM scout sc " .
                "LEFT JOIN bsa_rank br ON sc.rank_id = br.rank_id " .
                "LEFT JOIN merit_badge mb1 ON sc.pref1_mb_id = mb1.mb_id " .
                "LEFT JOIN merit_badge mb2 ON sc.pref2_mb_id = mb2.mb_id " .
                "LEFT JOIN merit_badge mb3 ON sc.pref3_mb_id = mb3.mb_id " .
                "LEFT JOIN merit_badge mb4 ON sc.pref4_mb_id = mb4.mb_id " .
                "LEFT JOIN merit_badge mb5 ON sc.pref5_mb_id = mb5.mb_id " .
                "LEFT JOIN merit_badge mb6 ON sc.pref6_mb_id = mb6.mb_id " .
                "WHERE sc.scout_id = " . $params['scoutId'];
        $result = $conn->query($sql);
        
        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
               
            $registration = array(
                "scout_id" => $params['scoutId'],
                "scout_first" => $row[first_name],
                "scout_last" => $row[last_name],
                "scout_age" => $row[age],
                "scout_rank_id" => $row[rank_id],
                "scout_rank" => $row[rank_name],
                "scout_pref1_id" => $row[pref1_mb_id],
                "scout_pref1" => $row[mb_pref1],
                "scout_pref2_id" => $row[pref2_mb_id],
                "scout_pref2" => $row[mb_pref2],
                "scout_pref3_id" => $row[pref3_mb_id],
                "scout_pref3" => $row[mb_pref3],
                "scout_pref4_id" => $row[pref4_mb_id],
                "scout_pref4" => $row[mb_pref4],
                "scout_pref5_id" => $row[pref5_mb_id],
                "scout_pref5" => $row[mb_pref5],
                "scout_pref6_id" => $row[pref6_mb_id],
                "scout_pref6" => $row[mb_pref6]
            );
        }
        else
        {
            $registration = array(
                "scout_id" => $params['scoutId'],
                "scout_first" => "",
                "scout_last" => "",
                "scout_age" => "",
                "scout_rank_id" => "",
                "scout_rank" => "",
                "scout_pref1_id" => "",
                "scout_pref1" => "",
                "scout_pref2_id" => "",
                "scout_pref2" => "",
                "scout_pref3_id" => "",
                "scout_pref3" => "",
                "scout_pref4_id" => "",
                "scout_pref4" => "",
                "scout_pref5_id" => "",
                "scout_pref5" => "",
                "scout_pref6_id" => "",
                "scout_pref6" => ""
            );
        }
        $conn->close();

        $this->data = $registration;
        echo json_encode($this->data);
    }
    
    /*
     * Add new registration data
     * 
     * Data in $params:
     * $params["age"] => Age of Scout from form
     * $params["first"] => First name of Scout from form
     * $params["last"] => Last name of Scout from form
     * $params["rank"] => Rank of Scout from form
     * $params["pref1"] => Scout's first MB preference ID
     * $params["pref2"] => Scout's second MB preference ID
     * $params["pref3"] => Scout's third MB preference ID
     * $params["pref4"] => Scout's fourth MB preference ID
     * $params["pref5"] => Scout's fifth MB preference ID
     * $params["pref6"] => Scout's sixth MB preference ID
     */
    function insertScoutRegistration($params, $servername, $username, $password, $dbname, $authToken) {
        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql =  "INSERT INTO scout " . 
                "(first_name, last_name, age, rank_id, unit_id, pref1_mb_id, pref2_mb_id, pref3_mb_id, pref4_mb_id, pref5_mb_id, pref6_mb_id) " .
                "SELECT '" . $params["first"] . "', '" . $params["last"] . "', " . $params["age"] . 
                ", " . (empty($params["rank"]) ? 'NULL' : $params["rank"]) . ", ma.unit_id, " . (empty($params["pref1"]) ? 'NULL' : $params["pref1"]) . 
                ", " . (empty($params["pref2"]) ? 'NULL' : $params["pref2"]) . ", " . (empty($params["pref3"]) ? 'NULL' : $params["pref3"]) . 
                ", " . (empty($params["pref4"]) ? 'NULL' : $params["pref4"]) . ", " . (empty($params["pref5"]) ? 'NULL' : $params["pref5"]) . 
                ", " . (empty($params["pref6"]) ? 'NULL' : $params["pref6"]) . " FROM mbreg_account ma WHERE ma.uuid = '" . $authToken . "'";
        $conn->query($sql);
        $conn->close();
        
        $ret = array(
                "success" => "true"
            );
        $this->data = $ret;
        echo json_encode($this->data);
    }

    /*
     * Updated existing registration data
     * 
     * Data in $params:
     * $params["edit_scout_id"] => Scout ID
     * $params["edit_first"] => First name of Scout from form
     * $params["edit_last"] => Last name of Scout from form
     * $params["edit_age"] => Age of Scout from form
     * $params["edit_rank"] => Scouts BSA Rank ID
     * $params["edit_pref1"] => Scout's first MB preference ID
     * $params["edit_pref2"] => Scout's second MB preference ID
     * $params["edit_pref3"] => Scout's third MB preference ID
     * $params["edit_pref4"] => Scout's fourth MB preference ID
     * $params["edit_pref5"] => Scout's fifth MB preference ID
     * $params["edit_pref6"] => Scout's fifth MB preference ID
     */
    function updateScoutRegistration($params, $servername, $username, $password, $dbname) {
        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql =  "UPDATE scout SET " . 
                "first_name = '" . $params["edit_first"] . "', " . 
                "last_name = '" . $params["edit_last"] . "', " .
                "rank_id = " . (empty($params["edit_rank"]) ? 'NULL' : $params["edit_rank"]) . ", " .
                "age = " . $params["edit_age"] . ", " .
                "pref1_mb_id = " . (empty($params["edit_pref1"]) ? 'NULL' : $params["edit_pref1"]) . ", " .
                "pref2_mb_id = " . (empty($params["edit_pref2"]) ? 'NULL' : $params["edit_pref2"]) . ", " .
                "pref3_mb_id = " . (empty($params["edit_pref3"]) ? 'NULL' : $params["edit_pref3"]) . ", " .
                "pref4_mb_id = " . (empty($params["edit_pref4"]) ? 'NULL' : $params["edit_pref4"]) . ", " .
                "pref5_mb_id = " . (empty($params["edit_pref5"]) ? 'NULL' : $params["edit_pref5"]) . ", " .
                "pref6_mb_id = " . (empty($params["edit_pref6"]) ? 'NULL' : $params["edit_pref6"]) . " " .
                "WHERE scout_id = " . $params['edit_scout_id'];
        $conn->query($sql);
        $conn->close();        
        
        $ret = array(
                "success" => "true"
            );
        $this->data = $ret;
        echo json_encode($this->data);
    }

    /*
     * Delete existing registration entry
     * 
     * Data in $params:
     * $params["scout_id"] => The Scout/Registration ID value
     */
    function deleteScoutRegistration($params, $servername, $username, $password, $dbname) {
        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql =  $sql = "DELETE FROM scout WHERE scout_id = " . $params['delete_scout_id'];
        $conn->query($sql);
        $conn->close();
        
        $ret = array(
                "success" => "true"
            );
        $this->data = $ret;
        echo json_encode($this->data);
    }

    function getUnitInformation($params, $servername, $username, $password, $dbname, $authToken) {
        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql =  "SELECT cn.council, cn.council_code, ut.unit_number, ut.unit_type, we.week_number, we.week_date_text " .
                "FROM unit ut " .
                "JOIN council cn ON cn.council_id = ut.council_id " .
                "JOIN week we ON ut.week_id = we.week_id " .
                "JOIN mbreg_account ma ON ut.unit_id = ma.unit_id " .
                "WHERE ma.uuid = '" . $authToken  . "'";
        $result = $conn->query($sql);
        
        $unit_info = array();
        
        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            
            $unit_info = array(
                "success" => "true",
                "council" => $row[council],
                "council_code" => $row[council_code],
                "unit_number" => $row[unit_number],
                "unit_type" => $row[unit_type],
                "week_number" => $row[week_number],
                "week_date_text" => $row[week_date_text]
            );
        }
        else
        {
            $unit_info = array(
                "success" => "true",
                "council" => '',
                "council_code" => '',
                "unit_number" => '',
                "unit_type" => '',
                "week_number" => '',
                "week_date_text" => ''
            );
        }
        
        $conn->close();
        
        $this->data = $unit_info;
        echo json_encode($this->data);
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
    
    function login($params, $servername, $username, $password, $dbname) {
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        $login_password = md5($salt1 . $params['password'] . $salt2);
        
        $sql = "SELECT uuid, is_admin FROM mbreg_account WHERE login = '" . $params['user'] . "' AND password = '" . $this->sanitizeMySQL($login_password, $conn) . "'";
        $result = $conn->query($sql);
        
        $login = array();
        
        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            setcookie('mbreg_user', $row[uuid], time() + 3600, '/', 'firemtn.org', FALSE, TRUE);
            $login = array(
                "valid" => "true",
                "msg" => "Login success",
                "admin" => $row[is_admin]
            );
        }
        else
        {
            $login = array(
                "valid" => "false",
                "msg" => "Invalid login",
                "admin" => 0
            );
        }
        $conn->close();
        
        $this->data = $login;
        echo json_encode($this->data);
    }
    
    function logout() {
        setcookie('mbreg_user', '', time() - 3600, '/', 'firemtn.org', FALSE, TRUE);
        $logout = array(
                "success" => "true"
            );
        $this->data = $logout;
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
	