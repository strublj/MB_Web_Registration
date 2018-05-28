<?php

$params = $_REQUEST;

$action = isset($params['action']) != '' ? $params['action'] : '';
$scheduleCls = new Scheduler();

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
        $scheduleCls->validateAdmin($servername, $username, $password, $dbname, $authToken);
        break;
    case 'buildSchedule':
        $scheduleCls->buildSchedule($servername, $username, $password, $dbname, $params);
        break;
    default:
        $scheduleCls->validateAdmin($servername, $username, $password, $dbname, $authToken);
        return;
}

class Scheduler {

    protected $data = array();
    private $Classes = array();
    private $Scouts = array();

    function __construct() {
        
    }

    /*
     * Get the list of classes and scouts for the given week.
     * Build the schedule based on preferences and write to database.
     */
    function buildSchedule($servername, $username, $password, $dbname, $params) {
        $week = $params['week'];

        $this->buildClassArray($servername, $username, $password, $dbname);
        $this->buildScoutArray($servername, $username, $password, $dbname, $week);
        $this->generateSchedule();
        $this->writeScheduleToDB($servername, $username, $password, $dbname, $week);

        $this->data = $this->Scouts;
        echo json_encode($this->data);
    }

    /*
     * Delete the current schedule for the week, then
     * save the generated schedule from the $Scouts array to the database.
     */
    function writeScheduleToDB($servername, $username, $password, $dbname, $week) {
        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql = "DELETE FROM schedule " .
                "WHERE scout_id IN ( ".
                                    "SELECT sc.scout_id ".
                                    "FROM scout sc  ".
                                    "JOIN unit un ON sc.unit_id = un.unit_id " .
                                    "JOIN week wk ON un.week_id = wk.week_id " .
                                    "WHERE week_number = " . $week .")";
        $conn->query($sql);
        
        foreach ($this->Scouts as &$scout) {
            foreach ($scout->ScheduleArray as &$schedule) {
                $sql = "INSERT INTO schedule (scout_id, class_session_id) " .
                        "VALUES (" . $scout->ID . ", " . $schedule->ClassSessionID . ")";
                $conn->query($sql);
            }
            unset($schedule);
        }
        unset($scout);
        
        $conn->close();
    }

    /*
     * Iterate over the list of Scouts looking at preferences compared to available
     * classes and build the class schedule in to the global array.
     */
    function generateSchedule() {
        $made_schedule_change = 1;

        while ($made_schedule_change != 0) {
            $made_schedule_change = 0;
            foreach ($this->Scouts as &$scout) {                
                if ($scout->IsScheduleFull == 0) {
                    $prefSlot = 1;
                    while ($prefSlot != 0) {
                        switch ($prefSlot) {
                            case 1:
                                if (!empty($scout->Pref1_ID)) {
                                    if ($this->setIfOpen($scout->ID, $scout->Pref1_ID) == 1) {
                                        $prefSlot = -1;
                                        $made_schedule_change = 1;
                                    }
                                    $scout->Pref1_ID = NULL;
                                }
                                $prefSlot++;
                                break;
                            case 2:
                                if (!empty($scout->Pref2_ID)) {
                                    if ($this->setIfOpen($scout->ID, $scout->Pref2_ID) == 1) {
                                        $prefSlot = -1;
                                        $made_schedule_change = 1;
                                    }
                                    $scout->Pref2_ID = NULL;
                                }
                                $prefSlot++;
                                break;
                            case 3:
                                if (!empty($scout->Pref3_ID)) {
                                    if ($this->setIfOpen($scout->ID, $scout->Pref3_ID) == 1) {
                                        $prefSlot = -1;
                                        $made_schedule_change = 1;
                                    }
                                    $scout->Pref3_ID = NULL;
                                }
                                $prefSlot++;
                                break;
                            case 4:
                                if (!empty($scout->Pref4_ID)) {
                                    if ($this->setIfOpen($scout->ID, $scout->Pref4_ID) == 1) {
                                        $prefSlot = -1;
                                        $made_schedule_change = 1;
                                    }
                                    $scout->Pref4_ID = NULL;
                                }
                                $prefSlot++;
                                break;
                            case 5:
                                if (!empty($scout->Pref5_ID)) {
                                    if ($this->setIfOpen($scout->ID, $scout->Pref5_ID) == 1) {
                                        $prefSlot = -1;
                                        $made_schedule_change = 1;
                                    }
                                    $scout->Pref5_ID = NULL;
                                }
                                $prefSlot++;
                                break;
                            case 6:
                                if (!empty($scout->Pref6_ID)) {
                                    if ($this->setIfOpen($scout->ID, $scout->Pref6_ID) == 1) {
                                        $prefSlot = -1;
                                        $made_schedule_change = 1;
                                    }
                                    $scout->Pref6_ID = NULL;
                                }
                                $prefSlot++;
                                break;
                            default:
                                $scout->IsScheduleFull = 1;
                                $prefSlot = 0;
                                break;
                        }
                    }
                }
            }
            unset($scout);
        }
    }

    /*
     * Determines if the given classes has space available and if so sets the scouts schedule
     */
    function setIfOpen($scoutID, $classID) {
        $ret = 0;

        //Loop over each session for the given class
        foreach ($this->Classes[$classID]->SessionArray as &$session) {
            if ($session->SpaceLeft > 0) {    
                switch ($session->NumSessions) {
                    case 1:                        
                        if (empty($this->Scouts[$scoutID]->ScheduleArray[$session->SessionStart]->ClassSessionID)) {
                            $this->Scouts[$scoutID]->ScheduleArray[$session->SessionStart]->ClassSessionID = $session->ID;
                            $ret = 1;
                            $session->SpaceLeft--;
                        }
                        break;
                    case 2:
                        if (empty($this->Scouts[$scoutID]->ScheduleArray[$session->SessionStart]->ClassSessionID) &&
                                empty($this->Scouts[$scoutID]->ScheduleArray[($session->SessionStart) + 1]->ClassSessionID)) {
                            $this->Scouts[$scoutID]->ScheduleArray[$session->SessionStart]->ClassSessionID = $session->ID;
                            $this->Scouts[$scoutID]->ScheduleArray[($session->SessionStart) + 1]->ClassSessionID = $session->ID;
                            $ret = 1;
                            $session->SpaceLeft--;
                        }
                        break;
                    case 3:
                        if (empty($this->Scouts[$scoutID]->ScheduleArray[$session->SessionStart]->ClassSessionID) &&
                                empty($this->Scouts[$scoutID]->ScheduleArray[($session->SessionStart) + 1]->ClassSessionID) &&
                                empty($this->Scouts[$scoutID]->ScheduleArray[($session->SessionStart) + 2]->ClassSessionID)) {
                            $this->Scouts[$scoutID]->ScheduleArray[$session->SessionStart]->ClassSessionID = $session->ID;
                            $this->Scouts[$scoutID]->ScheduleArray[($session->SessionStart) + 1]->ClassSessionID = $session->ID;
                            $this->Scouts[$scoutID]->ScheduleArray[($session->SessionStart) + 2]->ClassSessionID = $session->ID;
                            $ret = 1;
                            $session->SpaceLeft--;
                        }
                        break;
                    case 4:                                        
                        if (empty($this->Scouts[$scoutID]->ScheduleArray[$session->SessionStart]->ClassSessionID) &&
                                empty($this->Scouts[$scoutID]->ScheduleArray[($session->SessionStart) + 1]->ClassSessionID) &&
                                empty($this->Scouts[$scoutID]->ScheduleArray[($session->SessionStart) + 2]->ClassSessionID) &&
                                empty($this->Scouts[$scoutID]->ScheduleArray[($session->SessionStart) + 3]->ClassSessionID)) {
                            $this->Scouts[$scoutID]->ScheduleArray[$session->SessionStart]->ClassSessionID = $session->ID;
                            $this->Scouts[$scoutID]->ScheduleArray[($session->SessionStart) + 1]->ClassSessionID = $session->ID;
                            $this->Scouts[$scoutID]->ScheduleArray[($session->SessionStart) + 2]->ClassSessionID = $session->ID;
                            $this->Scouts[$scoutID]->ScheduleArray[($session->SessionStart) + 3]->ClassSessionID = $session->ID;
                            $ret = 1;
                            $session->SpaceLeft--;
                            
                            error_log("AFTER -> " . json_encode($this->Scouts), 0);
                        }
                        break;
                }

                if ($ret == 1) {
                    break;
                }
            }
        }
        unset($session);

        return $ret;
    }

    /*
     * Set up the class $Classes array with all of the Classes and Session information
     */
    function buildClassArray($servername, $username, $password, $dbname) {
        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql = "SELECT mb.mb_id, mb.mb_name " .
                "FROM merit_badge mb ";
        $class_result = $conn->query($sql);

        if ($class_result->num_rows > 0) {
            while ($row = $class_result->fetch_assoc()) {
                $mbclass = new MBClass();
                $mbclass->ID = $row[mb_id];
                $mbclass->Name = $row[mb_name];
                $this->Classes[$mbclass->ID] = $mbclass;
            }
        }

        $sql = "SELECT cs.class_session_id, cs.merit_badge_id, cs.size_limit, cs.start_session_id, cs.number_of_sessions " .
                "FROM class_session cs ";
        $session_result = $conn->query($sql);

        if ($session_result->num_rows > 0) {
            while ($row = $session_result->fetch_assoc()) {
                $classsession = new ClassSession();
                $classsession->ID = $row[class_session_id];
                $classsession->NumSessions = $row[number_of_sessions];
                $classsession->SessionStart = $row[start_session_id];
                $classsession->SizeLimit = $row[size_limit];
                $classsession->SpaceLeft = $row[size_limit];
                $this->Classes[$row[merit_badge_id]]->SessionArray[$classsession->ID] = $classsession;
            }
        }

        $conn->close();
    }

    /*
     * Set up the class $Scouts array with all of the Scout preference information
     */
    function buildScoutArray($servername, $username, $password, $dbname, $week) {
        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql = "SELECT sc.scout_id, sc.pref1_mb_id, sc.pref2_mb_id, sc.pref3_mb_id, " .
                "sc.pref4_mb_id, sc.pref5_mb_id, sc.pref6_mb_id " .
                "FROM scout sc " .
                "JOIN unit un ON sc.unit_id = un.unit_id " .
                "JOIN week wk ON un.week_id = wk.week_id " .
                "WHERE week_number = " . $week . " " .
                "ORDER BY sc.first_name ASC";
        $scout_result = $conn->query($sql);

        if ($scout_result->num_rows > 0) {
            while ($row = $scout_result->fetch_assoc()) {
                $scout = new Scout();
                $scout->ID = $row[scout_id];
                $scout->Pref1_ID = $row[pref1_mb_id];
                $scout->Pref2_ID = $row[pref2_mb_id];
                $scout->Pref3_ID = $row[pref3_mb_id];
                $scout->Pref4_ID = $row[pref4_mb_id];
                $scout->Pref5_ID = $row[pref5_mb_id];
                $scout->Pref6_ID = $row[pref6_mb_id];
                $scout->IsScheduleFull = 0;
                $this->Scouts[$scout->ID] = $scout;
            }
        }

        $sql = "SELECT session_number " .
                "FROM session " .
                "ORDER BY session_number ASC";
        $session_result = $conn->query($sql);

        foreach ($this->Scouts as &$scout) {
            $scheduleArray = array();

            if ($session_result->num_rows > 0) {
                while ($row = $session_result->fetch_assoc()) {
                    $session = new ScheduleSession();
                    $session->ID = $row[session_number];
                    $scheduleArray[$session->ID] = $session;
                }
            }
            $scout->ScheduleArray = $scheduleArray;
            
            $session_result->data_seek(0);
        }
        unset($scout);

        $conn->close();
    }

    function validateAdmin($servername, $username, $password, $dbname, $authToken) {
        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql = "SELECT ma.is_admin " .
                "FROM mbreg_account ma " .
                "WHERE ma.uuid = '" . $authToken . "'";
        $result = $conn->query($sql);

        $ret = array();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            if ($row[is_admin] == 1) {
                $ret = array(
                    "success" => "true"
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

class MBClass {

    public $ID;
    public $Name;
    public $SessionArray = array();

    function __construct() {
        
    }

}

class ClassSession {

    public $ID;
    public $SizeLimit;
    public $SpaceLeft;
    public $SessionStart;
    public $NumSessions;

    function __construct() {
        
    }

}

class Scout {

    public $ID;
    public $Pref1_ID;
    public $Pref2_ID;
    public $Pref3_ID;
    public $Pref4_ID;
    public $Pref5_ID;
    public $Pref6_ID;
    public $IsScheduleFull;
    public $ScheduleArray = array();

    function __construct() {
        
    }

}

class ScheduleSession {

    public $ID;
    public $ClassSessionID;

    function __construct() {
        
    }

}

?>
	