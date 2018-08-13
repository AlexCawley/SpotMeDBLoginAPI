<?php

class DB_CONNECT {
 
	private $conn;

    function __construct() 
    {
        $this->connect();
    }

    function __destruct() 
    {
    }
 
    function connect() 
    {
        require_once 'config.php';
 
        $this->conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE) or die(mysql_error());
 
        return $this->conn;
    }
 
}
 
?>