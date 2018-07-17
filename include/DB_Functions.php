<?php
 
class DB_Functions 
{
 
    private $conn;
 
    function __construct() 
    {
        require_once 'DB_Connect.php';
        // connecting to database
        $db = new Db_Connect();
        $this->conn = $db->connect();
    }
 
    function __destruct() 
    {
    }
 
    /**
     * Storing new user
     * returns user details
     */
    public function storeUser($name, $email, $password) 
    {
        $uuid = uniqid('', true);
        $hash = $this->hashSSHA($password);
        $encrypted_password = $hash["encrypted"]; // encrypted password
        $salt = $hash["salt"]; // salt
 
        $stmt = $this->conn->prepare("INSERT INTO users(unique_id, name, email, encrypted_password, salt, created_at) VALUES(?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssss", $uuid, $name, $email, $encrypted_password, $salt);
        $result = $stmt->execute();
        $stmt->close();
 
        // check for successful store
        if ($result) 
        {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
 
            return $user;
        } 
        else 
        {
            return false;
        }
    }

    /**
     * Storing new contact
     * returns contact details
     */
    public function storeContact($name, $email, $user_id) 
    {
        $uuid = uniqid('', true);
 
        $stmt = $this->conn->prepare("INSERT INTO contatcs(unique_id, name, email, created_at, updated_at, user_id) VALUES(?, ?, ?, NOW(), NOW(), ?)");
        $stmt->bind_param("sssss", $uuid, $name, $email, $user_id);
        $result = $stmt->execute();
        $stmt->close();
 
        if ($result) 
        {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE name = ? AND user_id = ");
            $stmt->bind_param("ss", $name, $user_id);
            $stmt->execute();
            $contact = $stmt->get_result()->fetch_assoc();
            $stmt->close();
 
            return $contact;
        } 
        else 
        {
            return false;
        }
    }
 
    /**
     * Get user by email and password
     */
    public function getUserByEmailAndPassword($email, $password) 
    {
 
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
 
        $stmt->bind_param("s", $email);
 
        if ($stmt->execute()) 
        {
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
 
            // verifying user password
            $salt = $user['salt'];
            $encrypted_password = $user['encrypted_password'];
            $hash = $this->checkhashSSHA($salt, $password);
            // check for password equality
            if ($encrypted_password == $hash) {
                // user authentication details are correct
                return $user;
            }
        }
        else 
        {
            return NULL;
        }
    }

     /**
     * Get contact by name and userid
     */
    public function getContactByNameAndUserId($name, $user_id) 
    {
 
        $stmt = $this->conn->prepare("SELECT * FROM contatcs WHERE name = ? AND user_id = ?");
 
        $stmt->bind_param("ss", $name, $user_id);
 
        if ($stmt->execute()) 
        {
            $contact = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $contact;
        }
        else 
        {
            return NULL;
        }
    }

     /**
     * Get all contacts associated with a single user id
     */
    public function getAllContactsByUserId($user_id) 
    {
 
        $stmt = $this->conn->prepare("SELECT * FROM contacts WHERE user_id = ?");
 
        $stmt->bind_param("s", $user_id);
 
        if ($stmt->execute()) 
        {
            $contact = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $contact;
        }
        else 
        {
            return NULL;
        }
    }
 
    /**
     * Check user if exists or not
     */
    public function userExists($email) 
    {
        $stmt = $this->conn->prepare("SELECT email from users WHERE email = ?");
 
        $stmt->bind_param("s", $email);
 
        $stmt->execute();
 
        $stmt->store_result();
 
        if ($stmt->num_rows > 0) 
        {
            // user existed 
            $stmt->close();
            return true;
        } 
        else 
        {
            // user does not exist
            $stmt->close();
            return false;
        }
    }
 
    /**
     * Encrypting password
     * @param password
     * returns salt and encrypted password
     */
    public function hashSSHA($password) 
    {
        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }
 
    /**
     * Decrypting password
     * @param salt, password
     * returns hash string
     */
    public function checkHashSSHA($salt, $password) 
    {
        $hash = base64_encode(sha1($password . $salt, true) . $salt);
 
        return $hash;
    }
 
}
 
?>