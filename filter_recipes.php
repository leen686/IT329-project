<?php
session_start();
include("db.php");

header('Content-Type: application/json');

if (!isset($_SESSION['userID']) || !isset($_SESSION['userType']) || $_SESSION['userType'] != 'user') {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);
    exit();
}

$selectedCategory = isset($_POST['category']) ? trim($_POST['category']) : 'all';

if ($selectedCategory != 'all') {
    $selectedCategory = (int)$selectedCategory;

    $recipesSql = "
        SELECT 
            recipe.id,
            recipe.name,
            recipe.photoFileName,
            recipe.categoryID,
            recipecategory.categoryName,
            user.firstName,
            user.lastName,
            user.photoFileName AS creatorPhoto,
            COUNT(likes.recipeID) AS likesCount
        FROM recipe
        JOIN recipecategory ON recipe.categoryID = recipecategory.id
        JOIN user ON recipe.userID = user.id
        LEFT JOIN likes ON recipe.id = likes.recipeID
        WHERE recipe.categoryID = $selectedCategory
        GROUP BY recipe.id
        ORDER BY recipe.id DESC
    ";
} else {
    $recipesSql = "
        SELECT 
            recipe.id,
            recipe.name,
            recipe.photoFileName,
            recipe.categoryID,
            recipecategory.categoryName,
            user.firstName,
            user.lastName,
            user.photoFileName AS creatorPhoto,
            COUNT(likes.recipeID) AS likesCount
        FROM recipe
        JOIN recipecategory ON recipe.categoryID = recipecategory.id
        JOIN user ON recipe.userID = user.id
        LEFT JOIN likes ON recipe.id = likes.recipeID
        GROUP BY recipe.id
        ORDER BY recipe.id DESC
    ";
}

$recipesResult = mysqli_query($conn, $recipesSql);

$recipes = [];

if ($recipesResult && mysqli_num_rows($recipesResult) > 0) {
    while ($recipe = mysqli_fetch_assoc($recipesResult)) {
        $recipes[] = [
            "id" => $recipe["id"],
            "name" => $recipe["name"],
            "photoFileName" => $recipe["photoFileName"],
            "categoryName" => $recipe["categoryName"],
            "firstName" => $recipe["firstName"],
            "lastName" => $recipe["lastName"],
            "creatorPhoto" => $recipe["creatorPhoto"],
            "likesCount" => $recipe["likesCount"]
        ];
    }
}

echo json_encode([
    "success" => true,
    "recipes" => $recipes
]);
exit();
?>