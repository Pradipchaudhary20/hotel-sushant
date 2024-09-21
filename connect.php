<?php

function connect(){
    $servername = "localhost";
    $username = "root";
    $password = "";
    $db_name = "hotel_db";
    // 
    $conn = new mysqli($servername, $username, $password, $db_name);
    if ($conn->connect_error) {
        die("<span class='error'> Failed to connect! </span>");
    }else return $conn;
}
?>