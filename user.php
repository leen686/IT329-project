<?php
session_start();
include("db.php");

if (!isset($_SESSION['userID']) || !isset($_SESSION['userType']) || $_SESSION['userType'] != "user") {
    header("Location: login.php?error=You must login as a regular user first");
    exit();
}

$userID = (int) $_SESSION['userID'];

/* user info */
$userSql = "SELECT * FROM user WHERE id = $userID";
$userResult = mysqli_query($conn, $userSql);

if (!$userResult || mysqli_num_rows($userResult) == 0) {
    header("Location: login.php?error=User not found");
    exit();
}

$user = mysqli_fetch_assoc($userResult);

$firstName = $user['firstName'];
$lastName = $user['lastName'];
$emailAddress = $user['emailAddress'];
$photoFileName = !empty($user['photoFileName']) ? $user['photoFileName'] : 'default.png';

/* total recipes */
$recipesCountSql = "SELECT COUNT(*) AS totalRecipes FROM recipe WHERE userID = $userID";
$recipesCountResult = mysqli_query($conn, $recipesCountSql);
$recipesCountRow = mysqli_fetch_assoc($recipesCountResult);
$totalRecipes = $recipesCountRow['totalRecipes'];

/* total likes for all this user's recipes */
$likesTotalSql = "
    SELECT COUNT(likes.recipeID) AS totalLikes
    FROM recipe
    LEFT JOIN likes ON recipe.id = likes.recipeID
    WHERE recipe.userID = $userID
";
$likesTotalResult = mysqli_query($conn, $likesTotalSql);
$likesTotalRow = mysqli_fetch_assoc($likesTotalResult);
$totalLikes = $likesTotalRow['totalLikes'];

/* categories */
$categoriesSql = "SELECT * FROM recipecategory ORDER BY id ASC";
$categoriesResult = mysqli_query($conn, $categoriesSql);

/* recipes list */
$selectedCategory = "all";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['category']) && $_POST['category'] != 'all') {
    $selectedCategory = (int) $_POST['category'];

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

/* favourites */
$favouritesSql = "
    SELECT recipe.id, recipe.name, recipe.photoFileName
    FROM favourites
    JOIN recipe ON favourites.recipeID = recipe.id
    WHERE favourites.userID = $userID
    ORDER BY recipe.id DESC
";
$favouritesResult = mysqli_query($conn, $favouritesSql);

function categoryClass($categoryName) {
    $name = strtolower(trim($categoryName));
    if ($name == 'main dishes') return 'userTagIftar';
    if ($name == 'healthy meals') return 'userTagSuhoor';
    if ($name == 'desserts') return 'userTagSnacks';
    return '';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Page • Ramadan Light</title>
  <link rel="stylesheet" href="css.css" />
</head>

<body>
  <div class="navbar">
    <div class="wrap nav-row">
      <div class="brand">
        <img
          src="images/log.png"
          alt="Ramadan Light logo"
          class="brand-logo"
        />
        <div class="brand-text">
          <div class="brand-name">Ramadan Light</div>
          <div class="brand-sub">HEALTHY RECIPES</div>
        </div>
      </div>

      <div class="nav-links">
        <a class="btn btn-primary" href="signout.php">Sign Out</a>
      </div>
    </div>
  </div>

  <main class="userContainer userBackground">
    <!-- Top Bar -->
    <section class="userTopBar">
      <div class="userTopLeft">
        <img
          class="userAvatar"
          src="images/<?php echo htmlspecialchars($photoFileName); ?>"
          alt="Profile image"
        />
        <div>
          <p class="userTextSecondary userMarginBottomSmall">
            Welcome back 👋
          </p>
          <h1 class="userTitleMain userMarginBottomSmall">
            <?php echo htmlspecialchars($firstName); ?>
          </h1>
        </div>
      </div>

      <div class="userTopRight">
        <div class="userStatsBox">
          <span class="userStatsLabel">Recipes</span>
          <span class="userStatsValue"><?php echo $totalRecipes; ?></span>
        </div>

        <div class="userStatsBox">
          <span class="userStatsLabel">Likes</span>
          <span class="userStatsValue"><?php echo $totalLikes; ?></span>
        </div>

        <a class="userButtonPrimary" href="my-recipes.php">My recipes</a>
      </div>
    </section>

    <!-- Profile -->
    <section class="userCard userMarginTop">
      <div class="userSectionHeader">
        <h2 class="userTitleSection">Profile</h2>
      </div>

      <div class="userInfoPills">
        <div class="userInfoPill">
          <span class="userTextSecondary userTextSmall">First name</span>
          <span class="userTextStrong"><?php echo htmlspecialchars($firstName); ?></span>
        </div>

        <div class="userInfoPill">
          <span class="userTextSecondary userTextSmall">Last name</span>
          <span class="userTextStrong"><?php echo htmlspecialchars($lastName); ?></span>
        </div>

        <div class="userInfoPill userInfoPillWide">
          <span class="userTextSecondary userTextSmall">Email</span>
          <span class="userTextStrong"><?php echo htmlspecialchars($emailAddress); ?></span>
        </div>
      </div>
    </section>

    <!-- Explore Recipes -->
    <section class="userCard userMarginTop">
      <div class="userSectionHeader">
        <div>
          <h2 class="userTitleSection">Explore recipes</h2>
          <p class="userTextSecondary userTextSmall userMarginTopSmall">
            Pick a category to explore tasty options.
          </p>
        </div>

        <form class="userFilterBar" method="POST" action="user.php">
          <select class="userSelectField" name="category">
            <option value="all">All Categories</option>
            <?php while ($category = mysqli_fetch_assoc($categoriesResult)) { ?>
              <option value="<?php echo $category['id']; ?>" <?php if ($selectedCategory == $category['id']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($category['categoryName']); ?>
              </option>
            <?php } ?>
          </select>
          <button class="userButtonPrimary" type="submit">Filter</button>
        </form>
      </div>

      <?php if ($recipesResult && mysqli_num_rows($recipesResult) > 0) { ?>
        <div class="userRecipeList">
          <?php while ($recipe = mysqli_fetch_assoc($recipesResult)) { ?>
            <article class="userRecipeRow">
              <a href="viewrecipe.php?id=<?php echo $recipe['id']; ?>">
                <img
                  class="userRecipeImage"
                  src="images/<?php echo htmlspecialchars($recipe['photoFileName']); ?>"
                  alt="<?php echo htmlspecialchars($recipe['name']); ?>"
                />
              </a>

              <div class="userRecipeMain">
                <a class="userRecipeTitle" href="viewrecipe.php?id=<?php echo $recipe['id']; ?>">
                  <?php echo htmlspecialchars($recipe['name']); ?>
                </a>
                <div class="userRecipeMeta">
                  <span class="userCategoryTag <?php echo categoryClass($recipe['categoryName']); ?>">
                    <?php echo htmlspecialchars($recipe['categoryName']); ?>
                  </span>
                  <span class="userMetaDot"></span>

                  <span class="userCreatorMini">
                    <img
                      class="userCreatorAvatar"
                      src="images/<?php echo htmlspecialchars($recipe['creatorPhoto']); ?>"
                      alt="Creator photo"
                    />
                    <span>
                      <?php echo htmlspecialchars($recipe['firstName'] . ' ' . $recipe['lastName']); ?>
                    </span>
                  </span>
                </div>
              </div>

              <div class="userLikesBox">
                <span class="userLikesLabel">Likes</span>
                <span class="userLikesValue"><?php echo $recipe['likesCount']; ?></span>
              </div>
            </article>
          <?php } ?>
        </div>
      <?php } else { ?>
        <p class="userTextSecondary">No recipes found for this category.</p>
      <?php } ?>
    </section>

    <!-- My Favourites -->
    <section class="userCard userMarginTop">
      <div class="userSectionHeader">
        <div>
          <h2 class="userTitleSection">My favourites</h2>
          <p class="userTextSecondary userTextSmall userMarginTopSmall">
            Your saved recipes, ready whenever you are.
          </p>
        </div>
      </div>

      <?php if ($favouritesResult && mysqli_num_rows($favouritesResult) > 0) { ?>
        <div class="userFavoritesGrid">
          <?php while ($fav = mysqli_fetch_assoc($favouritesResult)) { ?>
            <article class="userFavoriteCard">
              <a href="viewrecipe.php?id=<?php echo $fav['id']; ?>">
                <img
                  class="userFavoriteImage"
                  src="images/<?php echo htmlspecialchars($fav['photoFileName']); ?>"
                  alt="<?php echo htmlspecialchars($fav['name']); ?>"
                />
              </a>
              <div class="userFavoriteMain">
                <a class="userRecipeTitle" href="viewrecipe.php?id=<?php echo $fav['id']; ?>">
                  <?php echo htmlspecialchars($fav['name']); ?>
                </a>
                <span class="userTextSecondary userTextSmall">Saved recipe</span>
              </div>
              <a class="userButtonRemove" href="remove_favourite.php?recipeID=<?php echo $fav['id']; ?>">Remove</a>
            </article>
          <?php } ?>
        </div>
      <?php } else { ?>
        <p class="userTextSecondary">You do not have any favourites yet.</p>
      <?php } ?>
    </section>
  </main>

  <div class="footer">
    <div class="wrap footer-row">
      <div class="footer-mark">☾</div>
      <div>© 2026 Ramadan Light — Nourishing bodies and souls.</div>
    </div>
  </div>
</body>
</html>