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
    default:
        $scoutCls->fullClassListDetails($params, $servername, $username, $password, $dbname);
        return;
}

class Scout {

    protected $data = array();
    
    function __construct() {
        
    }

    /*
     * Returns the list of all the classes being offered
     * 
     * Also handles sorting and searches on the grid controller:
     */

    function fullClassListDetails($params, $servername, $username, $password, $dbname) {

        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql = 'SELECT mb_name,
                        case eagle_req 
                            when 1 then "Yes" 
                            else "No"
                         end as is_eagle_req,
                        case stem 
                            when 1 then "Yes" 
                            else "No"
                         end as is_stem,
                        area,
                        cost,
                        min_age,
                        pre_req,
                        mb_notes
                FROM merit_badge
                ORDER BY mb_name';
        $result = $conn->query($sql);
        
        $reg_list = array();
        
        if($result->num_rows > 0)
        {
            while($row = $result->fetch_assoc()){
                $reg_list[] = array(
                                "mb_name" => $row[mb_name],
                                "is_eagle_req" => $row[is_eagle_req],
                                "is_stem" => $row[is_stem],
                                "area" => $row[area],
                                "cost" => $row[cost],
                                "min_age" => $row[min_age],
                                "pre_req" => $row[pre_req],
                                "mb_notes" => $row[mb_notes]
                            );
            }
        }
        $conn->close();

        $mb_name = array();
        $eagle_req = array();
        $stem = array();
        $area = array();
        $cost = array();
        $age = array();
        $pre = array();
        $notes = array();

        foreach ($reg_list as $reg) {
            $mb_name[] = $reg['mb_name'];
            $eagle_req[] = $reg['is_eagle_req'];
            $stem[] = $reg['is_stem'];
            $area[] = $reg['area'];
            $cost[] = $reg['cost'];
            $age[] = $reg['min_age'];
            $pre[] = $reg['pre_req'];
            $notes[] = $reg['mb_notes'];
        }

        // Check the 'sort' parameter passed by the bootgrid controller (has column ID as key, direction as value)
        if (!empty($params['sort'])) {
            switch (key($params['sort'])) {
                case 'mb_name':
                    switch (current($params['sort'])) {
                        case 'asc':
                            array_multisort($mb_name, SORT_ASC, $reg_list);
                            break;
                        default:
                            array_multisort($mb_name, SORT_DESC, $reg_list);
                    }
                    break;
                case 'is_eagle_req':
                    switch (current($params['sort'])) {
                        case 'asc':
                            array_multisort($eagle_req, SORT_ASC, $reg_list);
                            break;
                        default:
                            array_multisort($eagle_req, SORT_DESC, $reg_list);
                    }
                    break;
                case 'is_stem':
                    switch (current($params['sort'])) {
                        case 'asc':
                            array_multisort($stem, SORT_ASC, $reg_list);
                            break;
                        default:
                            array_multisort($stem, SORT_DESC, $reg_list);
                    }
                    break;
                case 'area':
                    switch (current($params['sort'])) {
                        case 'asc':
                            array_multisort($area, SORT_ASC, $reg_list);
                            break;
                        default:
                            array_multisort($area, SORT_DESC, $reg_list);
                    }
                    break;
                case 'cost':
                    switch (current($params['sort'])) {
                        case 'asc':
                            array_multisort($cost, SORT_ASC, $reg_list);
                            break;
                        default:
                            array_multisort($cost, SORT_DESC, $reg_list);
                    }
                    break;
                case 'min_age':
                    switch (current($params['sort'])) {
                        case 'asc':
                            array_multisort($age, SORT_ASC, $reg_list);
                            break;
                        default:
                            array_multisort($age, SORT_DESC, $reg_list);
                    }
                    break;
                case 'pre_req':
                    switch (current($params['sort'])) {
                        case 'asc':
                            array_multisort($pre, SORT_ASC, $reg_list);
                            break;
                        default:
                            array_multisort($pre, SORT_DESC, $reg_list);
                    }
                    break;
                case 'mb_notes':
                    switch (current($params['sort'])) {
                        case 'asc':
                            array_multisort($notes, SORT_ASC, $reg_list);
                            break;
                        default:
                            array_multisort($notes, SORT_DESC, $reg_list);
                    }
                    break;
            }
        }
        // Default sorting is on the first name (per user story)
        else {
            array_multisort($mb_name, SORT_ASC, $reg_list);
        }

        $json_data = array(
            "current" => 1,
            "rowCount" => -1, //-1 returns all rows (disables pagination)			
            "total" => count($reg_list),
            "rows" => $reg_list
        );
        
        echo json_encode($json_data);
    }
}

?>
	