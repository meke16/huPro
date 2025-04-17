<?php 

$conn = new mysqli("localhost" , "root" , "" ,"hu" );

if(!$conn) {
    echo die("". mysqli_error($conn));
}
?>