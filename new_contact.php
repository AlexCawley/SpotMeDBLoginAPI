<?php
 
    require_once 'include/DB_Functions.php';
    $db = new DB_Functions();
     
    $response = array();
     
    if (isset($_POST['user_id']) && isset($_POST['name']) && isset($_POST['email'])) 
    {
        $user_id = $_POST['user_id'];
        $name = $_POST['name'];
        $email = $_POST['email'];

        $result = $db->storeContact($name, $email, $user_id); 

        if ($result) 
        {
            $response["error"] = FALSE;
            $response["message"] = "Contact stored successfully";
        } 
        else 
        {
            $response["error"] = TRUE;
            $response["message"] = "Unknown error occurred in storing contact";
        }
    } 
    else 
    {
        $response["error"] = TRUE;
        $response["message"] = "Required parameters, name, email, or user_id, missing";
    }

    echo json_encode($response);
    
?>