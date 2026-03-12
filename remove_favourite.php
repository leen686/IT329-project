<?php
session_start();
include("db.php");

if (!isset($_SESSION['userID']) || !isset($_SESSION['userType']) || $_SESSION['userType'] != "user") {
    header("Location: login.php");
    exit();
}

if (isset($_GET['recipeID'])) {
    $userID = (int) $_SESSION['userID'];
    $recipeID = (int) $_GET['recipeID'];

    $sql = "DELETE FROM favourites WHERE userID = $userID AND recipeID = $recipeID";
    mysqli_query($conn, $sql);
}

header("Location: user.php");
exit();
?>