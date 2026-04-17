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
$commentText = mysqli_real_escape_string($conn, trim($_POST["commentText"] ?? ""));

if ($recipeID <= 0 || empty($commentText)) {
    header("Location: viewrecipe.php?id=" . $recipeID);
    exit();
}

$today = date("Y-m-d");

$sql = "INSERT INTO comment (recipeID, userID, comment, date) VALUES ($recipeID, $userID, '$commentText', '$today')";
mysqli_query($conn, $sql);

header("Location: viewrecipe.php?id=" . $recipeID);
exit();
?>