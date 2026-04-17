<?php
session_start();
include("db.php");

if (!isset($_SESSION['userID'])) {
    header("Location: login.php?error=You must login first");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: user.php");
    exit();
}

$userID = (int) $_SESSION['userID'];
$recipeID = (int) ($_POST["recipeID"] ?? 0);

if ($recipeID <= 0) {
    header("Location: user.php");
    exit();
}

$checkSql = "SELECT * FROM likes WHERE userID = $userID AND recipeID = $recipeID";
$checkResult = mysqli_query($conn, $checkSql);

if ($checkResult && mysqli_num_rows($checkResult) == 0) {
    $insertSql = "INSERT INTO likes (userID, recipeID) VALUES ($userID, $recipeID)";
    mysqli_query($conn, $insertSql);
}

header("Location: viewrecipe.php?id=" . $recipeID);
exit();
?>