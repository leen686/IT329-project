<?php
session_start();
include("db.php");

header('Content-Type: application/json');

if (!isset($_SESSION['userID']) || !isset($_SESSION['userType']) || $_SESSION['userType'] != 'user') {
    echo json_encode(false);
    exit();
}

if (!isset($_POST['recipeID'])) {
    echo json_encode(false);
    exit();
}

$userID = (int) $_SESSION['userID'];
$recipeID = (int) $_POST['recipeID'];

$sql = "DELETE FROM favourites WHERE userID = $userID AND recipeID = $recipeID";
$result = mysqli_query($conn, $sql);

if ($result) {
    echo json_encode(true);
} else {
    echo json_encode(false);
}
exit();
?>