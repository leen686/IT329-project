<?php
session_start();
include("db.php");

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: my-recipes.php");
    exit();
}

$recipeID = (int) $_GET['id'];
$userID = (int) $_SESSION['userID'];

$checkQuery = "SELECT * FROM recipe WHERE id = $recipeID AND userID = $userID";
$checkResult = mysqli_query($conn, $checkQuery);

if (mysqli_num_rows($checkResult) == 0) {
    header("Location: my-recipes.php");
    exit();
}

$checkQuery = "SELECT * FROM recipe WHERE id = $recipeID AND userID = $userID";
$checkResult = mysqli_query($conn, $checkQuery);

if (mysqli_num_rows($checkResult) == 0) {
    header("Location: my-recipes.php");
    exit();
}


// 1. Get image + video
$getFiles = "SELECT photoFileName, videoFilePath FROM recipe WHERE id = $recipeID";
$result = mysqli_query($conn, $getFiles);
$data = mysqli_fetch_assoc($result);

// 2. Delete image
if (!empty($data['photoFileName'])) {
    $photoPath = "images/" . $data['photoFileName'];
    if (file_exists($photoPath)) {
        unlink($photoPath);
    }
}

// 3. Delete video
if (!empty($data['videoFilePath'])) {
    $videoPath = "videos/" . $data['videoFilePath'];
    if (file_exists($videoPath)) {
        unlink($videoPath);
    }
}

// 4. Delete related data
mysqli_query($conn, "DELETE FROM ingredients WHERE recipeID = $recipeID");
mysqli_query($conn, "DELETE FROM instructions WHERE recipeID = $recipeID");
mysqli_query($conn, "DELETE FROM comment WHERE recipeID = $recipeID");
mysqli_query($conn, "DELETE FROM likes WHERE recipeID = $recipeID");
mysqli_query($conn, "DELETE FROM favourites WHERE recipeID = $recipeID");
mysqli_query($conn, "DELETE FROM report WHERE recipeID = $recipeID");

// 5. Delete recipe
mysqli_query($conn, "DELETE FROM recipe WHERE id = $recipeID");

// 6. Redirect back
header("Location: my-recipes.php");
exit();
?>