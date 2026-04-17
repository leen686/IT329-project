
<?php
session_start();
include("db.php");

if (!isset($_SESSION['userID']) || $_SESSION['userType'] != "user") {
    header("Location: login.php?error=You must login first");
    exit();
}

$userID = (int) $_SESSION['userID'];

$categoriesSql = "SELECT * FROM recipecategory ORDER BY id ASC";
$categoriesResult = mysqli_query($conn, $categoriesSql);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $categoryID = (int) $_POST['category'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $photoName = $_FILES['photo']['name'];
    $tempName = $_FILES['photo']['tmp_name'];

    $newPhotoName = time() . "_" . $photoName;
    move_uploaded_file($tempName, "images/" . $newPhotoName);

    $videoPath = "";

    if (!empty($_FILES['video_file']['name'])) {

        $videoName = time() . "_" . $_FILES['video_file']['name'];
        move_uploaded_file($_FILES['video_file']['tmp_name'], "videos/" . $videoName);
        $videoPath = $videoName;

            } elseif (!empty($_POST['video_url'])) {

                $videoPath = mysqli_real_escape_string($conn, $_POST['video_url']);
                    }

    $insertRecipe = "
        INSERT INTO recipe (userID, categoryID, name, description, photoFileName, videoFilePath)
        VALUES ($userID, $categoryID, '$name', '$description', '$newPhotoName', '$videoPath')
    ";
    mysqli_query($conn, $insertRecipe);

    $recipeID = mysqli_insert_id($conn);

    if (isset($_POST['ingredient_name'])) {
        for ($i = 0; $i < count($_POST['ingredient_name']); $i++) {

            $ingName = mysqli_real_escape_string($conn, $_POST['ingredient_name'][$i]);
            $ingQty = mysqli_real_escape_string($conn, $_POST['ingredient_qty'][$i]);

            if (!empty($ingName)) {
                mysqli_query($conn, "
                    INSERT INTO ingredients (recipeID, ingredientName, ingredientQuantity)
                    VALUES ($recipeID, '$ingName', '$ingQty')
                ");
            }
        }
    }

    if (isset($_POST['step'])) {
        for ($i = 0; $i < count($_POST['step']); $i++) {

            $stepText = mysqli_real_escape_string($conn, $_POST['step'][$i]);
            $order = $i + 1;

            if (!empty($stepText)) {
                mysqli_query($conn, "
                    INSERT INTO instructions (recipeID, step, stepOrder)
                    VALUES ($recipeID, '$stepText', $order)
                ");
            }
        }
    }

    header("Location: my-recipes.php ");
    exit();
}
?>


<!DOCTYPE html>

<html lang="en">
    
<head>
  <meta charset="UTF-8" />
  <title>Ramadan Light | Add Recipe</title>
  <link rel="stylesheet" href="css.css" />
</head>
<body id="add-recipe-page">

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

  <!-- Page Content -->
  
 <h1 id="add-recipe-title">Add New Recipe</h1>
 
  <main class="page" id="add-recipe-content">

    <form class="card" id="add-recipe-form" method="POST" enctype="multipart/form-data" action="">
      
      <label for="add-recipe-name">Recipe name *</label>
      <input type="text" name="name" id="add-recipe-name" required />

      <label for="add-recipe-category">Category *</label>
      <select id="add-recipe-category" name="category" required>
            <option value="">Select Category</option>
        <?php while ($cat = mysqli_fetch_assoc($categoriesResult)) { ?>
            <option value="<?php echo $cat['id']; ?>">
                <?php echo $cat['categoryName']; ?>
            </option>
        <?php } ?>
      </select>

      <label for="add-recipe-description">Description *</label>
      <textarea id="add-recipe-description" rows="3" name="description" required></textarea>

      <label for="add-recipe-photo">Photo *</label>
      <input type="file" id="add-recipe-photo" accept="image/*" name="photo" required />

      <!-- Ingredients -->
      <label>Ingredients *</label>
<div id="add-recipe-ingredients-container">
  <div class="ingredient-row">
    <span class="ingredient-number">1.</span>
    <input
      type="text"
      class="ingredient-name"
      name="ingredient_name[]"
      placeholder="Ingredient name"
      required
    />
    <input
      type="text"
      class="ingredient-qty"
      name="ingredient_qty[]"
      placeholder="Quantity"
      required
    />
  </div>
</div>

<button
  type="button"
  class="add-recipe-btn-secondary"
  id="add-ingredient-btn">
  + Add new ingredient
</button>

      <!-- Instructions -->
      <label>Instructions *</label>
<div id="add-recipe-steps-container">
  <div class="step-row">
    <span class="step-number">1.</span>
    <input
      type="text"
      class="step-input"
       name="step[]"
      placeholder="Step description"
      required
    />
  </div>
</div>

<button
  type="button"
  class="add-recipe-btn-secondary"
  id="add-step-btn">
  + Add new step
</button>

	  <label for="add-recipe-video-file">Video or URL (optional)</label>
		<input
		type="file"
		id="add-recipe-video-file"
                name="video_file"
		accept="video/*"
		/>
      <input
        type="url"
        id="add-recipe-video"
        name="video_url"
        placeholder="https://youtube.com/..." />

      <div class="actions" id="add-recipe-actions">
        <button type="submit" class="add-recipe-btn-primary" id="add-recipe-submit-recipe">
          Add Recipe
        </button>
        <a href="my-recipes.php" class="add-recipe-btn-secondary" id="cancel-add-recipe">
          Cancel
        </a>
      </div>

    </form>
  </main>
 
 <script>
const videoFileInput = document.querySelector('input[name="video_file"]');
const videoURLInput = document.querySelector('input[name="video_url"]');

videoFileInput.addEventListener('change', () => {
    if (videoFileInput.value !== "") {
        videoURLInput.value = "";
        videoURLInput.disabled = true;
    } else {
        videoURLInput.disabled = false;
    }
});

videoURLInput.addEventListener('input', () => {
    if (videoURLInput.value !== "") {
        videoFileInput.value = "";
        videoFileInput.disabled = true;
    } else {
        videoFileInput.disabled = false;
    }
});
</script>

  <div class="footer">
    <div class="wrap footer-row">
      <div class="footer-mark">☾</div>
      <div>© 2026 Ramadan Light — Nourishing bodies and souls.</div>
    </div>
  </div>
  
  <script src="js.js"></script>
</body>
</html>

