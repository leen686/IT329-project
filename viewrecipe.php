<?php
session_start();
include("db.php");

if (!isset($_SESSION['userID']) || !isset($_SESSION['userType'])) {
    header("Location: login.php?error=You must login first");
    exit();
}

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    die("Recipe ID is missing.");
}

$recipeID = (int) $_GET["id"];
$currentUserID = (int) $_SESSION["userID"];
$currentUserType = $_SESSION["userType"];

/* recipe + creator + category */
$sqlRecipe = "
    SELECT 
        recipe.*,
        user.firstName,
        user.lastName,
        user.photoFileName AS creatorPhoto,
        recipecategory.categoryName
    FROM recipe
    JOIN user ON recipe.userID = user.id
    JOIN recipecategory ON recipe.categoryID = recipecategory.id
    WHERE recipe.id = $recipeID
";

$resultRecipe = mysqli_query($conn, $sqlRecipe);

if (!$resultRecipe || mysqli_num_rows($resultRecipe) == 0) {
    die("Recipe not found.");
}

$recipe = mysqli_fetch_assoc($resultRecipe);

/* ingredients */
$sqlIngredients = "SELECT * FROM ingredients WHERE recipeID = $recipeID";
$resultIngredients = mysqli_query($conn, $sqlIngredients);

/* instructions */
$sqlInstructions = "SELECT * FROM instructions WHERE recipeID = $recipeID ORDER BY stepOrder ASC";
$resultInstructions = mysqli_query($conn, $sqlInstructions);

/* comments with user data */
$sqlComments = "
    SELECT 
        comment.comment,
        comment.date,
        user.firstName,
        user.lastName,
        user.photoFileName
    FROM comment
    JOIN user ON comment.userID = user.id
    WHERE comment.recipeID = $recipeID
    ORDER BY comment.id DESC
";
$resultComments = mysqli_query($conn, $sqlComments);

/* check creator/admin */
$isCreator = ($currentUserID == $recipe["userID"]);
$isAdmin = ($currentUserType == "admin");

/* check favourite */
$isFavourite = false;
$sqlFavourite = "SELECT * FROM favourites WHERE userID = $currentUserID AND recipeID = $recipeID";
$resultFavourite = mysqli_query($conn, $sqlFavourite);
if ($resultFavourite && mysqli_num_rows($resultFavourite) > 0) {
    $isFavourite = true;
}

/* check like */
$isLiked = false;
$sqlLike = "SELECT * FROM likes WHERE userID = $currentUserID AND recipeID = $recipeID";
$resultLike = mysqli_query($conn, $sqlLike);
if ($resultLike && mysqli_num_rows($resultLike) > 0) {
    $isLiked = true;
}

/* check report */
$isReported = false;
$sqlReport = "SELECT * FROM report WHERE userID = $currentUserID AND recipeID = $recipeID";
$resultReport = mysqli_query($conn, $sqlReport);
if ($resultReport && mysqli_num_rows($resultReport) > 0) {
    $isReported = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ramadan Light | View Recipe</title>
  <link rel="stylesheet" href="css2.css" />
</head>
<body class="viewrecipe-page">

  <div class="navbar">
    <div class="wrap nav-row">
      <a class="brand" href="user.php">
        <img src="images/log.png" alt="Ramadan Light logo" class="brand-logo">
        <div>
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

  <div class="page-bg">
    <div class="wrap">
      <div class="card">

        <?php if (!$isCreator && !$isAdmin) { ?>
          <div class="top-actions">

            <?php if ($isFavourite) { ?>
             <button class="btn btn-active" disabled>Favourited ♥</button>
             <?php } else { ?>
               <form action="add_favourite.php" method="post" style="display:inline;">
               <input type="hidden" name="recipeID" value="<?php echo $recipeID; ?>">
                <button class="btn btn-outline" type="submit">Favourite ♥</button>
             </form>
            <?php } ?>

            <?php if ($isLiked) { ?>
             <button class="btn btn-active" disabled>Liked 👍</button>
               <?php } else { ?>
              <form action="add_like.php" method="post" style="display:inline;">
               <input type="hidden" name="recipeID" value="<?php echo $recipeID; ?>">
                <button class="btn btn-outline" type="submit">Like 👍</button>
               </form>
            <?php } ?>

            <?php if ($isReported) { ?>
               <button class="btn btn-active-danger" disabled>Reported ⚑</button>
             <?php } else { ?>
             <form action="add_report.php" method="post" style="display:inline;">
                <input type="hidden" name="recipeID" value="<?php echo $recipeID; ?>">
             <button class="btn btn-outline btn-danger" type="submit">Report ⚑</button>
             </form>
             <?php } ?>

          </div>
        <?php } ?>

        <h1 class="title"><?php echo htmlspecialchars($recipe["name"]); ?></h1>
        <img class="recipe-photo" src="images/<?php echo htmlspecialchars($recipe["photoFileName"]); ?>" alt="Recipe photo">

        <div class="creator">
          <span class="label">Recipe Creator:</span>
          <div class="creator-box">
            <img class="avatar"
                 src="images/<?php echo htmlspecialchars(!empty($recipe["creatorPhoto"]) ? $recipe["creatorPhoto"] : "default.png"); ?>"
                 alt="Creator photo">
            <span class="creator-name">
              <?php echo htmlspecialchars($recipe["firstName"] . " " . $recipe["lastName"]); ?>
            </span>
          </div>
        </div>

        <div class="box">
          <h2 class="section-title">Details</h2>
          <p><b>Category:</b> <?php echo htmlspecialchars($recipe["categoryName"]); ?></p>
          <p class="muted"><?php echo htmlspecialchars($recipe["description"]); ?></p>
        </div>

        <div class="box">
          <h2 class="section-title">Ingredients</h2>
          <ul class="list">
            <?php if ($resultIngredients && mysqli_num_rows($resultIngredients) > 0) { ?>
              <?php while ($ingredient = mysqli_fetch_assoc($resultIngredients)) { ?>
                <li>
                  <?php echo htmlspecialchars($ingredient["ingredientName"]); ?> —
                  <?php echo htmlspecialchars($ingredient["ingredientQuantity"]); ?>
                </li>
              <?php } ?>
            <?php } ?>
          </ul>
        </div>

        <div class="box">
          <h2 class="section-title">Instructions</h2>
          <ol class="list">
            <?php if ($resultInstructions && mysqli_num_rows($resultInstructions) > 0) { ?>
              <?php while ($instruction = mysqli_fetch_assoc($resultInstructions)) { ?>
                <li><?php echo htmlspecialchars($instruction["step"]); ?></li>
              <?php } ?>
            <?php } ?>
          </ol>
        </div>

        <?php if (!empty($recipe["videoFilePath"])) { ?>
          <div class="box" id="videoSection">
            <h2 class="section-title">Video</h2>
            <p class="muted">Video:</p>
            <a class="link" href="videos/<?php echo htmlspecialchars($recipe["videoFilePath"]); ?>" target="_blank" rel="noopener">
              videos/<?php echo htmlspecialchars($recipe["videoFilePath"]); ?>
            </a>
          </div>
        <?php } ?>

        <div class="box">
          <h2 class="section-title">Comments</h2>

          <form class="comment-form" action="add_comment.php" method="post">
            <input type="hidden" name="recipeID" value="<?php echo $recipeID; ?>">
            <input class="input" type="text" name="commentText" placeholder="Write a comment..." required>
            <button class="btn btn-primary" type="submit">Add Comment</button>
          </form>

          <?php if ($resultComments && mysqli_num_rows($resultComments) > 0) { ?>
            <?php while ($comment = mysqli_fetch_assoc($resultComments)) { ?>
              <div class="comment">
                <img class="avatar"
                     src="images/<?php echo htmlspecialchars(!empty($comment["photoFileName"]) ? $comment["photoFileName"] : "default.png"); ?>"
                     alt="User photo">
                <div class="comment-content">
                  <span class="comment-date"><?php echo htmlspecialchars($comment["date"]); ?></span>
                  <b><?php echo htmlspecialchars($comment["firstName"] . " " . $comment["lastName"]); ?></b>
                  <p class="muted"><?php echo htmlspecialchars($comment["comment"]); ?></p>
                </div>
              </div>
            <?php } ?>
          <?php } else { ?>
            <p class="muted">No comments yet.</p>
          <?php } ?>
        </div>

      </div>
    </div>
  </div>

  <div class="footer">
    <div class="wrap footer-row">
      <div class="footer-mark">☾</div>
      <div>© 2026 Ramadan Light — Nourishing bodies and souls.</div>
    </div>
  </div>
</body>
</html>