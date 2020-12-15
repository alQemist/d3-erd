<?php

class createCon  {
    var $host = 'localhost';
    var $user = 'root';
    var $pass = 'NewPassword';
    var $db = 'enterprise_architecture';
    var $myconn;

    function connect() {
        $con = mysqli_connect($this->host, $this->user, $this->pass, $this->db);
        if (!$con) {
            die('Could not connect to database!');
        } else {
            $this->myconn = $con;
            //echo 'Connection established!';}
            return $this->myconn;
        }

        function close() {
            mysqli_close($myconn);
            echo 'Connection closed!';
        }

    }

}

?>