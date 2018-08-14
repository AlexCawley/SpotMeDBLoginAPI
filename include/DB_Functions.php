<?php

/*------------------------------------------------------------------------------------------------*
 *  All functions to query against database                                                       *
 *------------------------------------------------------------------------------------------------*/ 
class DB_Functions 
{
    /*--------------------------------------------------------------------------------------------*
     *  Member Variables                                                                          *
     *--------------------------------------------------------------------------------------------*/
    private $conn;
 
    /*--------------------------------------------------------------------------------------------*
     *  Constructor                                                                               *
     *--------------------------------------------------------------------------------------------*/
    function __construct() 
    {
        // Get the connection
        require_once 'DB_Connect.php';

        // Create a new connection
        $db = new DB_Connect();
        $this->conn = $db->connect();
    }
 
    function __destruct() 
    {
    }
 
    /*--------------------------------------------------------------------------------------------*
     *                                                                                            *
     *  storeUser                                                                                 *
     *                                                                                            *
     *--------------------------------------------------------------------------------------------*
     *  Stores a user in the database                                                             *
     *--------------------------------------------------------------------------------------------*/
    public function storeUser($name, $email, $password) 
    {
        // Generate a unique id for the new user (23 characters long)
        $uuid = uniqid('', true);

        // Encrypt the password
        $hash = $this->hashSSHA($password);
        $encrypted_password = $hash["encrypted"];
        $salt = $hash["salt"];
 
        // Initializs the insert statement
        $stmt = $this->conn->prepare("INSERT INTO users(unique_id, name, email, encrypted_password, salt, created_at) VALUES(?, ?, ?, ?, ?, NOW())");

        // Bind the passed data and the encryption salt to the insert statement
        $stmt->bind_param("sssss", $uuid, $name, $email, $encrypted_password, $salt);

        // Exexute and close the query
        $result = $stmt->execute();
        $stmt->close();

        /*----------------------------------------------------------------------------------------*
         *  If the query returned a success                                                       *
         *----------------------------------------------------------------------------------------*/
        if ($result) 
        {
            // Prepare a query to fetch the user that was just inserted
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
            
            // Bind the email to the query
            $stmt->bind_param("s", $email);

            // Execute and close the query
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
 
            // Return the user found, the same user that was just added
            return $user;
        } 

        /*----------------------------------------------------------------------------------------*
         *  Else if the query returned a failue                                                   *
         *----------------------------------------------------------------------------------------*/
        else 
        {
            // Indicate an error occurred
            return false;
        }
    }

    /*--------------------------------------------------------------------------------------------*
     *                                                                                            *
     *  storeContact                                                                              *
     *                                                                                            *
     *--------------------------------------------------------------------------------------------*
     *  Stores a contact in the database                                                          *
     *--------------------------------------------------------------------------------------------*/
    public function storeContact($name, $email, $user_id) 
    {
        // Generate a unique id (23 character long)
        $uuid = uniqid('', true);
 
        // Initializs the insert statement
        $stmt = $this->conn->prepare("INSERT INTO contacts (unique_id, name, email, created_at, updated_at, user_id) VALUES(?, ?, ?, NOW(), NULL, ?)");

        // Bind the passed data to the insert statement
        $stmt->bind_param("ssss", $uuid, $name, $email, $user_id);

        // Exexute and close the query
        $result = $stmt->execute();
        $stmt->close();

        /*----------------------------------------------------------------------------------------*
         *  If the query returned a success                                                       *
         *----------------------------------------------------------------------------------------*/
        if ($result) 
        {
            // Prepare a query to fetch the contact that was just inserted
            $stmt = $this->conn->prepare("SELECT * FROM contacts WHERE name = ? AND user_id = ?");

            // Bind the arguments to the query
            $stmt->bind_param("ss", $name, $user_id);

            // Execute and close the query
            $stmt->execute();
            $contact = $stmt->get_result()->fetch_assoc();
            $stmt->close();
 
            // Return the contact found, the same user that was just added
            return $contact;
        }

        /*----------------------------------------------------------------------------------------*
         *  Else if the query returned a failue                                                   *
         *----------------------------------------------------------------------------------------*/ 
        else 
        {
            // Indicate an error occurred
            return false;
        }
    }

    /*--------------------------------------------------------------------------------------------*
     *                                                                                            *
     *  getUserByEmailAndPassword                                                                 *
     *                                                                                            *
     *--------------------------------------------------------------------------------------------*
     *  Returns a user from the database matching the email an password                           *
     *--------------------------------------------------------------------------------------------*/
    public function getUserByEmailAndPassword($email, $password) 
    {
        // Initialize the select statment
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
 
        // Bind the email that was passed to the select
        $stmt->bind_param("s", $email);

        // Execute the query
        $result = $stmt ->execute();

        /*----------------------------------------------------------------------------------------*
         *  If the query returned a success                                                       *
         *----------------------------------------------------------------------------------------*/
        if ($result) 
        {
            // Get the user that was found and close the statment
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
 
            // Get the encryption salt assigned to the user
            $salt = $user['salt'];

            // Get the encrypted password associated with the user
            $encrypted_password = $user['encrypted_password'];

            // Encrypt the password
            $hash = $this->checkHashSSHA($salt, $password);

            /*------------------------------------------------------------------------------------*
             *  If the encypted password matches the hash                                         *
             *------------------------------------------------------------------------------------*/
            if ($encrypted_password == $hash) 
            {
                // Returen the user found
                return $user;
            }
        }

        /*----------------------------------------------------------------------------------------*
         *  Else if the query returned a failue                                                   *
         *----------------------------------------------------------------------------------------*/ 
        else 
        {
            // Indicate an error occured
            return NULL;
        }
    }

    public function getAllContactsByUserId($user_id) 
    {
        $contacts = mysqli_query($this->conn, "SELECT * FROM contacts WHERE user_id = '$user_id'") or die (mysqli_error($this->conn));
 
        if ($contacts) 
        {
            return $contacts;
        }
        else 
        {
            return NULL;
        }
    }

    /*--------------------------------------------------------------------------------------------*
     *                                                                                            *
     *  userExists                                                                                *
     *                                                                                            *
     *--------------------------------------------------------------------------------------------*
     *  Checks to see if a user with the email passed already exists                              *
     *--------------------------------------------------------------------------------------------*/
    public function userExists($email) 
    {
        // Initialize the select statment
        $stmt = $this->conn->prepare("SELECT email from users WHERE email = ?");
 
        // Bind the email passed to teh query
        $stmt->bind_param("s", $email);
 
        // Execute the query and store the result
        $stmt->execute();
        $stmt->store_result();
 
        /*----------------------------------------------------------------------------------------*
         *  If the query returned at least 1 row                                                  *
         *----------------------------------------------------------------------------------------*/ 
        if ($stmt->num_rows > 0) 
        {
            // Close the statment and return indicating the email is associated with an account
            $stmt->close();
            return true;
        } 

        /*----------------------------------------------------------------------------------------*
         *  If the query returned at least 0 rows                                                 *
         *----------------------------------------------------------------------------------------*/ 
        else 
        {
            // Close the statment and return indicating the email is not associated with an account
            $stmt->close();
            return false;
        }
    }

    /*--------------------------------------------------------------------------------------------*
     *                                                                                            *
     *  hashSSHA                                                                                  *
     *                                                                                            *
     *--------------------------------------------------------------------------------------------*
     *  encodes a password using ssha                                                             *
     *--------------------------------------------------------------------------------------------*/
    public function hashSSHA($password) 
    {
        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }

    /*--------------------------------------------------------------------------------------------*
     *                                                                                            *
     *  checkHashSSHA                                                                             *
     *                                                                                            *
     *--------------------------------------------------------------------------------------------*
     *  checks to see if the salt and password match                                              *
     *--------------------------------------------------------------------------------------------*/
    public function checkHashSSHA($salt, $password) 
    {
        $hash = base64_encode(sha1($password . $salt, true) . $salt);
 
        return $hash;
    }
 
}
 
?>