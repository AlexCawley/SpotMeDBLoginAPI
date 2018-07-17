<?php
 
    require_once 'include/DB_Functions.php';
    $db = new DB_Functions();
     
    $response = array("error" => FALSE);
     
    if (isset($_POST['name']) && isset($_POST['user_id'])) 
    {
        $name = $_POST['name'];
        $user_id = $_POST['user_id'];
        $email = null;

        if(isset($_POST['email']))
        {
            $email = $_POST['email'];
        }

        $contact = $db->storeContact($name, $email, $user_id);
        if ($contact) 
        {
            $response["error"] = FALSE;
            $response["uid"] = $contact["unique_id"];
            $response["contact"]["name"] = $contact["name"];
            $response["contact"]["email"] = $contact["email"];
            $response["contact"]["created_at"] = $contact["created_at"];
            $response["contact"]["updated_at"] = $contact["updated_at"];
            echo json_encode($response);
        } 
        else 
        {
            $response["error"] = TRUE;
            $response["error_msg"] = "Unknown error occurred in storing contact";
            echo json_encode($response);
        }
    } 
    else 
    {
        $response["error"] = TRUE;
        $response["error_msg"] = "Required parameters, name or user_id, missing";
        echo json_encode($response);
    }
    
?>