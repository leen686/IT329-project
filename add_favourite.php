<?php
session_start();
include("db.php");

if (!isset($_SESSION['userID'])) {
    echo "false";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo "false";
    exit();
}

$userID = (int) $_SESSION['userID'];
$recipeID = (int) ($_POST["recipeID"] ?? 0);

if ($recipeID <= 0) {
    echo "false";
    exit();
}

$success = false;

$checkSql = "SELECT * FROM favourites WHERE userID = $userID AND recipeID = $recipeID";
$checkResult = mysqli_query($conn, $checkSql);

if ($checkResult && mysqli_num_rows($checkResult) == 0) {
    $insertSql = "INSERT INTO favourites (userID, recipeID) VALUES ($userID, $recipeID)";
    $success = mysqli_query($conn, $insertSql);
} else if ($checkResult && mysqli_num_rows($checkResult) > 0) {
    $success = true;
}

echo $success ? "true" : "false";
exit();
?>
