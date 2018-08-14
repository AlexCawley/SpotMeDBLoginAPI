<?php

    require_once 'include/DB_Functions.php';
    $db = new DB_Functions();

    $response = array("error" => FALSE);
     
    if (isset($_POST['user_id'])) 
    {
        $user_id = $_POST['user_id'];
     
        $contacts = $db->getAllContactsByUserId($user_id);
     
        if ($contacts) 
        {
            $response["error"] = FALSE;
            $response["error_msg"] = "Contacts queried successfully";
            while($row = mysqli_fetch_array($contacts, MYSQL_NUM))
            {
                $response["contact"]["name"] = $row["name"];
                $response["contact"]["email"] = $row["email"];
                $response["contact"]["updated_at"] = $row["updated_at"];
                $response["contact"]["created_at"] = $row["created_at"];
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