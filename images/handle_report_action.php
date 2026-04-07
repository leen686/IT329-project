<?php
session_start();
include("db.php");

if (!isset($_POST['recipeID']) || !isset($_POST['reportID']) || !isset($_POST['action'])) {
    header("Location: admin.php");
    exit();
}

$recipeID = (int) $_POST['recipeID'];
$reportID = (int) $_POST['reportID'];
$action = $_POST['action'];

if ($action == "dismiss") {
    $deleteReportQuery = "DELETE FROM report WHERE id = $reportID";
    mysqli_query($conn, $deleteReportQuery);

    header("Location: admin.php");
    exit();
}

if ($action == "block") {
    $userQuery = "
        SELECT user.id, user.firstName, user.lastName, user.emailAddress
        FROM recipe
        JOIN user ON recipe.userID = user.id
        WHERE recipe.id = $recipeID
    ";
    $userResult = mysqli_query($conn, $userQuery);
    $userData = mysqli_fetch_assoc($userResult);

    if ($userData) {
        $blockedFirstName = mysqli_real_escape_string($conn, $userData['firstName']);
        $blockedLastName = mysqli_real_escape_string($conn, $userData['lastName']);
        $blockedEmail = mysqli_real_escape_string($conn, $userData['emailAddress']);
        $blockedUserID = (int) $userData['id'];

        $insertBlockedQuery = "
            INSERT INTO blockeduser (firstName, lastName, emailAddress)
            VALUES ('$blockedFirstName', '$blockedLastName', '$blockedEmail')
        ";
        mysqli_query($conn, $insertBlockedQuery);

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

            mysqli_query($conn, "DELETE FROM ingredients WHERE recipeID = $currentRecipeID");
            mysqli_query($conn, "DELETE FROM instructions WHERE recipeID = $currentRecipeID");
            mysqli_query($conn, "DELETE FROM comment WHERE recipeID = $currentRecipeID");
            mysqli_query($conn, "DELETE FROM likes WHERE recipeID = $currentRecipeID");
            mysqli_query($conn, "DELETE FROM favourites WHERE recipeID = $currentRecipeID");
            mysqli_query($conn, "DELETE FROM report WHERE recipeID = $currentRecipeID");
            mysqli_query($conn, "DELETE FROM recipe WHERE id = $currentRecipeID");
        }

        mysqli_query($conn, "DELETE FROM user WHERE id = $blockedUserID");
    }

    header("Location: admin.php");
    exit();
}

header("Location: admin.php");
exit();
?>