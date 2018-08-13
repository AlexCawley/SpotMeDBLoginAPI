<?php

    require_once 'include/DB_Functions.php';
    $db = new DB_Functions();

    $response = array("error" => FALSE);
     
    if (isset($_POST['user_id'])) 
    {
        $user_id = $_POST['user_id'];
     
        $contacts = $db->getAllContactsByUserId($user_id);
     
        if ($contacts != false) 
        {
            while ($row = mysql_fetch_array($contacts, MYSQL_NUM)) 
            {
                $response["error"] = false;
                $response["uid"] = $row["unique_id"];
                $response["contact"]["name"] = $row["name"];
                $response["contact"]["email"] = $row["email"];
                $response["contact"]["created_at"] = $row["created_at"];
                $response["contact"]["updated_at"] = $row["updated_at"];
                $response["contact"]["user_id"] = $row["user_id"];
            }
            echo json_encode($response);
        } 
        else 
        {
            $response["error"] = TRUE;
            $response["error_msg"] = "Error occured finding all contacts";
            echo json_encode($response);
        }
    } 
    else 
    {
        $response["error"] = TRUE;
        $response["error_msg"] = "Required parameters user_id is missing!";
        echo json_encode($response);
    }
?>