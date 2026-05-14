<?php
session_start();
include("db.php");

if (!isset($_SESSION['userID']) || !isset($_SESSION['userType'])) {
    echo "false";
    exit();
}

if ($_SESSION['userType'] != 'admin') {
    echo "false";
    exit();
}

if (!isset($_POST['recipeID']) || !isset($_POST['reportID']) || !isset($_POST['action'])) {
    echo "false";
    exit();
}
//1. Receive AJAX data
$recipeID = (int) $_POST['recipeID'];
$reportID = (int) $_POST['reportID'];
$action   = $_POST['action'];
//2. Show dismiss action
if ($action == "dismiss") {

    $deleteReport = mysqli_query($conn, "DELETE FROM report WHERE id = $reportID");

    if ($deleteReport) {
        echo "true";
    } else {
        echo "false";
    }

    exit();
}
//3. Show block action starts here
if ($action == "block") {

    $userQuery = "
        SELECT user.id, user.firstName, user.lastName, user.emailAddress, user.userType
        FROM recipe
        JOIN user ON recipe.userID = user.id
        WHERE recipe.id = $recipeID
    ";
    $userResult = mysqli_query($conn, $userQuery);
    $userData = mysqli_fetch_assoc($userResult);

    if (!$userData) {
        echo "false";
        exit();
    }

    if ($userData['userType'] == 'admin') {
        echo "false";
        exit();
    }

    $blockedUserID = (int) $userData['id'];
    $blockedFirstName = mysqli_real_escape_string($conn, $userData['firstName']);
    $blockedLastName  = mysqli_real_escape_string($conn, $userData['lastName']);
    $blockedEmail     = mysqli_real_escape_string($conn, $userData['emailAddress']);
//insert into blocked user
    mysqli_query($conn, "
        INSERT INTO blockeduser (firstName, lastName, emailAddress)
        VALUES ('$blockedFirstName', '$blockedLastName', '$blockedEmail')
    ");

    $recipesQuery = "SELECT id, photoFileName, videoFilePath FROM recipe WHERE userID = $blockedUserID";
    $recipesResult = mysqli_query($conn, $recipesQuery);

    while ($recipe = mysqli_fetch_assoc($recipesResult)) {
        $currentRecipeID = (int) $recipe['id'];

        if (!empty($recipe['photoFileName'])) {
            $photoPath = "images/" . $recipe['photoFileName'];
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }

        if (!empty($recipe['videoFilePath'])) {
            $videoPath = "videos/" . $recipe['videoFilePath'];
            if (file_exists($videoPath)) {
                unlink($videoPath);
            }
        }
//delete related data
        mysqli_query($conn, "DELETE FROM ingredients WHERE recipeID = $currentRecipeID");
        mysqli_query($conn, "DELETE FROM instructions WHERE recipeID = $currentRecipeID");
        mysqli_query($conn, "DELETE FROM comment WHERE recipeID = $currentRecipeID");
        mysqli_query($conn, "DELETE FROM likes WHERE recipeID = $currentRecipeID");
        mysqli_query($conn, "DELETE FROM favourites WHERE recipeID = $currentRecipeID");
        mysqli_query($conn, "DELETE FROM report WHERE recipeID = $currentRecipeID");
        mysqli_query($conn, "DELETE FROM recipe WHERE id = $currentRecipeID");
    }

    mysqli_query($conn, "DELETE FROM report WHERE id = $reportID");

    $deleteUser = mysqli_query($conn, "DELETE FROM user WHERE id = $blockedUserID");
// sends true or false back to AJAX
    if ($deleteUser) {
        echo "true";
    } else {
        echo "false";
    }

    exit();
}

echo "false";
exit();
?>