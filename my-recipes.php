<?php
session_start();
include("db.php");

if (!isset($_SESSION['userID']) || !isset($_SESSION['userType'])) {
        $_SESSION['error'] = "Please log in first.";
    header("Location: login.php");
    exit();
}

if ($_SESSION['userType'] != 'user') {
        $_SESSION['error'] = "Access denied. Users only";
    header("Location: login.php");
    exit();
}

$userID = (int) $_SESSION['userID'];

$recipesQuery = "
    SELECT 
        recipe.id,
        recipe.name,
        recipe.photoFileName,
        recipe.videoFilePath,
        recipecategory.categoryName
    FROM recipe
    JOIN recipecategory ON recipe.categoryID = recipecategory.id
    WHERE recipe.userID = $userID
";
$recipesResult = mysqli_query($conn, $recipesQuery);
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Recipes</title>
  <link rel="stylesheet" href="css.css">
</head>

<body>

<div class="navbar">
  <div class="wrap nav-row">
    <a class="brand" href="user.php">
      <img src="images/log.png" alt="Ramadan Light logo" class="brand-logo">
      <div class="brand-text">
        <div class="brand-name">Ramadan Light</div>
        <div class="brand-sub">HEALTHY RECIPES</div>
      </div>
    </a>

    <div class="nav-links">
      <a class="link" href="user.php">Home</a>
      <a class="btn btn-primary" href="signout.php">Log out</a>
    </div>
  </div>
</div>

<div class="mr-container">

  <div class="mr-header">
    <h1 class="mr-title">My Recipes</h1>
    <p class="mr-subtitle">Here are all the recipes you added.</p>
  </div>

  <div class="mr-actions">
    <a href="add-recipe.php" class="mr-btn-primary">+ Add new recipe</a>
  </div>

  <div class="mr-table-box">

    <?php if (mysqli_num_rows($recipesResult) > 0) { ?>
      <table class="mr-table">
        <tr>
          <th>Recipe</th>
          <th>Ingredients</th>
          <th>Instructions</th>
          <th>Video</th>
          <th>Likes</th>
          <th>Edit</th>
          <th>Delete</th>
        </tr>

        <?php while ($recipe = mysqli_fetch_assoc($recipesResult)) { ?>

          <?php
          $recipeID = (int) $recipe['id'];

          $ingredientsQuery = "
              SELECT ingredientName, ingredientQuantity
              FROM ingredients
              WHERE recipeID = $recipeID
          ";
          $ingredientsResult = mysqli_query($conn, $ingredientsQuery);

          $instructionsQuery = "
              SELECT step
              FROM instructions
              WHERE recipeID = $recipeID
              ORDER BY stepOrder ASC
          ";
          $instructionsResult = mysqli_query($conn, $instructionsQuery);

          $likesQuery = "
              SELECT COUNT(*) AS totalLikes
              FROM likes
              WHERE recipeID = $recipeID
          ";
          $likesResult = mysqli_query($conn, $likesQuery);
          $likesData = mysqli_fetch_assoc($likesResult);
          ?>

          <tr>
            <td>
              <a href="viewrecipe.php?id=<?php echo $recipe['id']; ?>" class="mr-recipe-link">
                <div class="mr-recipe-cell">
                  <img src="images/<?php echo ($recipe['photoFileName'] != '' ? $recipe['photoFileName'] : 'default-recipe.jpg'); ?>" alt="<?php echo htmlspecialchars($recipe['name']); ?>" class="mr-recipe-img">
                  <div>
                    <div class="mr-recipe-name"><?php echo htmlspecialchars($recipe['name']); ?></div>
                    <div class="mr-recipe-cat">Category: <?php echo htmlspecialchars($recipe['categoryName']); ?></div>
                  </div>
                </div>
              </a>
            </td>

            <td>
              <ul class="mr-list">
                <?php while ($ingredient = mysqli_fetch_assoc($ingredientsResult)) { ?>
                  <li>
                    <?php echo htmlspecialchars($ingredient['ingredientName']); ?>
                    <?php if ($ingredient['ingredientQuantity'] != '') { ?>
                      — <?php echo htmlspecialchars($ingredient['ingredientQuantity']); ?>
                    <?php } ?>
                  </li>
                <?php } ?>
              </ul>
            </td>

            <td>
              <ol class="mr-list">
                <?php while ($instruction = mysqli_fetch_assoc($instructionsResult)) { ?>
                  <li><?php echo htmlspecialchars($instruction['step']); ?></li>
                <?php } ?>
              </ol>
            </td>

            <td>
              <?php if (!empty($recipe['videoFilePath'])) { ?>
                <a href="videos/<?php echo htmlspecialchars($recipe['videoFilePath']); ?>" class="mr-link" target="_blank">View Video</a>
              <?php } else { ?>
                <span class="mr-link">no video for recipe</span>
              <?php } ?>
            </td>

            <td><?php echo $likesData['totalLikes']; ?></td>

            <td>
              <a href="edit-recipe.php?id=<?php echo $recipe['id']; ?>" class="mr-link">Edit</a>
            </td>

            <td>
              <a href="delete-recipe.php?id=<?php echo $recipe['id']; ?>" class="mr-link-danger" onclick="return confirm('Are you sure you want to delete this recipe?');">Delete</a>
            </td>
          </tr>

        <?php } ?>
      </table>
    <?php } else { ?>
      <p>You have no recipes.</p>
    <?php } ?>

  </div>

</div>

<div class="footer">
  <div class="wrap footer-row">
    <div class="footer-mark">☾</div>
    <div>©️ 2026 Ramadan Light — Nourishing bodies and souls.</div>
  </div>
</div>

</body>
</html>