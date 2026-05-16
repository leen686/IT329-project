<?php    
$host = "sql206.infinityfree.com";
$user = "if0_41940050";
$password = "Alhilal12";
$database = "if0_41940050_recipedb";
$port = 3306;
$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>