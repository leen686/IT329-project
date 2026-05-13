<?php
session_start();
include("db.php");

if (!isset($_SESSION['userID'])) {
    header("Location: login.php?error=You must login as a regular user first");
    exit();
}

if (!isset($_SESSION['userType']) || $_SESSION['userType'] != 'user') {
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

/* categories for dropdown */
$categoriesSql = "SELECT * FROM recipecategory ORDER BY id ASC";
$categoriesResult = mysqli_query($conn, $categoriesSql);

/* default recipes on first page load */
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

/* category style helper */
function categoryClass($categoryName) {
    $name = strtolower(trim($categoryName));
    if ($name == 'iftar') return 'userTagIftar';
    if ($name == 'suhoor') return 'userTagSuhoor';
    if ($name == 'snacks') return 'userTagSnacks';
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

        <!-- AJAX category filter-->
        <form class="userFilterBar" id="recipeFilterForm">
          <select class="userSelectField" name="category" id="categorySelect">
            <option value="all">All Categories</option>
            <?php while ($category = mysqli_fetch_assoc($categoriesResult)) { ?>
              <option value="<?php echo $category['id']; ?>">
                <?php echo htmlspecialchars($category['categoryName']); ?>
              </option>
            <?php } ?>
          </select>
        </form>
      </div>

      <!-- Recipes list using AJAX -->
      <div class="userRecipeList" id="recipesContainer">
        <?php if ($recipesResult && mysqli_num_rows($recipesResult) > 0) { ?>
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
        <?php } else { ?>
          <p class="userTextSecondary">No recipes found for this category.</p>
        <?php } ?>
      </div>
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

      <!-- Favourite recipes, remove is handled with AJAX -->
      <div id="favouritesContainer">
        <?php if ($favouritesResult && mysqli_num_rows($favouritesResult) > 0) { ?>
          <div class="userFavoritesGrid" id="favouritesGrid">
            <?php while ($fav = mysqli_fetch_assoc($favouritesResult)) { ?>
              <article class="userFavoriteCard favouriteCard" id="favourite-<?php echo $fav['id']; ?>">
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

                <a 
                  class="userButtonRemove removeFavouriteBtn" 
                  href="#" 
                  data-recipe-id="<?php echo $fav['id']; ?>"
                >
                  Remove
                </a>
              </article>
            <?php } ?>
          </div>
        <?php } else { ?>
          <p class="userTextSecondary" id="noFavouritesMessage">You do not have any favourites yet.</p>
        <?php } ?>
      </div>
    </section>
  </main>

  <div class="footer">
    <div class="wrap footer-row">
      <div class="footer-mark">☾</div>
      <div>©️ 2026 Ramadan Light — Nourishing bodies and souls.</div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <script>
    // Update category tag class based on returned JSON category name
    function getCategoryClass(categoryName) {
      const name = categoryName.trim().toLowerCase();

      if (name === 'iftar') return 'userTagIftar';
      if (name === 'suhoor') return 'userTagSuhoor';
      if (name === 'snacks') return 'userTagSnacks';

      return '';
    }

    // Update recipes HTML using JSON response from PHP
    function renderRecipes(recipes) {
      let html = '';

      if (!recipes.length) {
        html = '<p class="userTextSecondary">No recipes found for this category.</p>';
      } else {
        $.each(recipes, function(index, recipe) {
          const categoryClass = getCategoryClass(recipe.categoryName);

          html += `
            <article class="userRecipeRow">
              <a href="viewrecipe.php?id=${recipe.id}">
                <img
                  class="userRecipeImage"
                  src="images/${recipe.photoFileName}"
                  alt="${recipe.name}"
                />
              </a>

              <div class="userRecipeMain">
                <a class="userRecipeTitle" href="viewrecipe.php?id=${recipe.id}">
                  ${recipe.name}
                </a>

                <div class="userRecipeMeta">
                  <span class="userCategoryTag ${categoryClass}">
                    ${recipe.categoryName}
                  </span>
                  <span class="userMetaDot"></span>

                  <span class="userCreatorMini">
                    <img
                      class="userCreatorAvatar"
                      src="images/${recipe.creatorPhoto}"
                      alt="Creator photo"
                    />
                    <span>${recipe.firstName} ${recipe.lastName}</span>
                  </span>
                </div>
              </div>

              <div class="userLikesBox">
                <span class="userLikesLabel">Likes</span>
                <span class="userLikesValue">${recipe.likesCount}</span>
              </div>
            </article>
          `;
        });
      }

      $('#recipesContainer').html(html);
    }

    $(document).ready(function() {
      // Send AJAX request when category changes
      $('#categorySelect').on('change', function() {
        const category = $(this).val();

        $.ajax({
          url: 'filter_recipes.php',
          type: 'POST',
          data: { category: category },
          dataType: 'json',
          success: function(response) {
            if (response.success) {
              renderRecipes(response.recipes);
            }
          },
          error: function() {
            alert('Something went wrong while filtering recipes.');
          }
        });
      });

      // Send AJAX request to remove favourite
      $(document).on('click', '.removeFavouriteBtn', function(e) {
        e.preventDefault();

        const recipeID = $(this).data('recipe-id');
        const card = $('#favourite-' + recipeID);

        $.ajax({
          url: 'remove_favourite.php',
          type: 'POST',
          data: { recipeID: recipeID },
          dataType: 'json',
          success: function(response) {
            if (response === true) {
              card.remove();

              if ($('#favouritesGrid .favouriteCard').length === 0) {
                $('#favouritesContainer').html(
                  '<p class="userTextSecondary" id="noFavouritesMessage">You do not have any favourites yet.</p>'
                );
              }
            } else {
              alert('Failed to remove recipe from favourites.');
            }
          },
          error: function() {
            alert('Something went wrong while removing favourite.');
          }
        });
      });
    });
  </script>
</body>
</html>