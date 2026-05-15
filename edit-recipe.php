<?php
session_start();
include("db.php");

if (!isset($_SESSION['userID']) || $_SESSION['userType'] != "user") {
    header("Location: login.php?error=You must login first");
    exit();
}

$userID = (int) $_SESSION['userID'];

if (!isset($_GET['id'])) {
    header("Location: my-recipes.php");
    exit();
}

$recipeID = (int) $_GET['id'];

$recipeSql = "SELECT * FROM recipe WHERE id = $recipeID";
$recipeResult = mysqli_query($conn, $recipeSql);

if (mysqli_num_rows($recipeResult) == 0) {
    die("Recipe not found");
}

$recipe = mysqli_fetch_assoc($recipeResult);

if ($recipe['userID'] != $userID) {
    die("You are not allowed to edit this recipe");
}

$categoriesResult = mysqli_query($conn, "SELECT * FROM recipecategory");

$ingredientsResult = mysqli_query($conn, "
    SELECT * FROM ingredients WHERE recipeID = $recipeID
");

$stepsResult = mysqli_query($conn, "
    SELECT * FROM instructions WHERE recipeID = $recipeID ORDER BY stepOrder ASC
");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $categoryID = (int) $_POST['category'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    // PHOTO
$photoName = $recipe['photoFileName'];

if (!empty($_FILES['photo']['name'])) {

    if (
        !empty($recipe['photoFileName']) &&
        file_exists("images/" . $recipe['photoFileName'])
    ) {
        unlink("images/" . $recipe['photoFileName']);
    }

    $tempPhotoName = $_FILES['photo']['name'];

    $newPhotoName = "recipe_" . $recipeID . "_" . $tempPhotoName;

    move_uploaded_file(
        $_FILES['photo']['tmp_name'],
        "images/" . $newPhotoName
    );

    $photoName = $newPhotoName;
}

// VIDEO
$videoPath = $recipe['videoFilePath'];

// upload new video file
if (!empty($_FILES['video_file']['name'])) {

    // delete old uploaded video only
    if (
        !empty($recipe['videoFilePath']) &&
        !filter_var($recipe['videoFilePath'], FILTER_VALIDATE_URL) &&
        file_exists("videos/" . $recipe['videoFilePath'])
    ) {

        unlink("videos/" . $recipe['videoFilePath']);
    }

    // original filename
    $videoName = basename($_FILES['video_file']['name']);

    // new filename
    $newVideoName = "recipe_" . $recipeID . "_" . $videoName;

    move_uploaded_file(
        $_FILES['video_file']['tmp_name'],
        "videos/" . $newVideoName
    );

    $videoPath = $newVideoName;
}

// user entered URL
elseif (!empty($_POST['video_url'])) {

    // delete old uploaded video if exists
    if (
        !empty($recipe['videoFilePath']) &&
        !filter_var($recipe['videoFilePath'], FILTER_VALIDATE_URL) &&
        file_exists("videos/" . $recipe['videoFilePath'])
    ) {

        unlink("videos/" . $recipe['videoFilePath']);
    }

    $videoPath = mysqli_real_escape_string($conn, $_POST['video_url']);
}

// user removed URL
elseif (
    empty($_POST['video_url']) &&
    !empty($recipe['videoFilePath']) &&
    filter_var($recipe['videoFilePath'], FILTER_VALIDATE_URL)
) {

    $videoPath = "";
}

    mysqli_query($conn, "
        UPDATE recipe 
        SET categoryID=$categoryID,
            name='$name',
            description='$description',
            photoFileName='$photoName',
            videoFilePath='$videoPath'
        WHERE id=$recipeID
    ");

    mysqli_query($conn, "DELETE FROM ingredients WHERE recipeID=$recipeID");
    mysqli_query($conn, "DELETE FROM instructions WHERE recipeID=$recipeID");

    foreach ($_POST['ingredient_name'] as $i => $ingName) {
        $ingQty = $_POST['ingredient_qty'][$i];

        if (!empty($ingName)) {
            mysqli_query($conn, "
                INSERT INTO ingredients (recipeID, ingredientName, ingredientQuantity)
                VALUES ($recipeID, '$ingName', '$ingQty')
            ");
        }
    }

    foreach ($_POST['step'] as $i => $stepText) {
        $order = $i + 1;

        if (!empty($stepText)) {
            mysqli_query($conn, "
                INSERT INTO instructions (recipeID, step, stepOrder)
                VALUES ($recipeID, '$stepText', $order)
            ");
        }
    }

    header("Location: my-recipes.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Ramadan Light | Edit Recipe</title>
  <link rel="stylesheet" href="css.css" />
</head>

<body id="edit-recipe-page">

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
<h1 id="edit-recipe-title">Edit Recipe</h1>

 <main class="page" id="edit-recipe-content">
<form id="edit-recipe-form" method="POST" enctype="multipart/form-data">

<input type="hidden" name="id" value="<?php echo $recipeID; ?>">

<!-- name -->
<label>Recipe name *</label>
<input type="text" name="name" value="<?php echo htmlspecialchars($recipe['name']); ?>" required>

<!-- category -->
<label>Category *</label>
<select name="category" required>
<?php while ($cat = mysqli_fetch_assoc($categoriesResult)) { ?>
<option value="<?php echo $cat['id']; ?>"
<?php if ($cat['id'] == $recipe['categoryID']) echo "selected"; ?>>
<?php echo htmlspecialchars($cat['categoryName']); ?>
</option>
<?php } ?>
</select>

<!-- description -->
<label>Description *</label>
<textarea name="description" required><?php echo htmlspecialchars($recipe['description']); ?></textarea>

<!-- photo -->
<label>Photo </label>

<div id="edit-recipe-photo-row">
  <input type="file" id="edit-recipe-photo" name="photo" accept="image/*" >

  <div id="edit-recipe-current-photo">
    <span>Current Photo:</span>

    <img
      src="images/<?php echo htmlspecialchars($recipe['photoFileName']); ?>"
      alt="Recipe photo"
      id="edit-recipe-photo-preview"
    >
  </div>
</div>

<!-- ingredients -->
<h3>Ingredients *</h3>

<div id="edit-recipe-ingredients-container">
<?php $i = 1; while ($ing = mysqli_fetch_assoc($ingredientsResult)) { ?>
  
  <div class="ingredient-row">
    <span class="ingredient-number"><?php echo $i++; ?></span>

    <input 
      type="text" 
      name="ingredient_name[]" 
      class="ingredient-name"
      value="<?php echo $ing['ingredientName']; ?>" 
      required
    >

    <input 
      type="text" 
      name="ingredient_qty[]" 
      class="ingredient-qty"
      value="<?php echo $ing['ingredientQuantity']; ?>" 
      required
    >
  </div>

<?php } ?>
</div>

<!-- steps -->
<h3>Steps *</h3>

<div id="edit-recipe-steps-container">
<?php $i = 1; while ($step = mysqli_fetch_assoc($stepsResult)) { ?>

  <div class="step-row">
    <span class="step-number"><?php echo $i++; ?></span>

    <input 
      type="text" 
      name="step[]" 
      class="step-input"
      value="<?php echo $step['step']; ?>" 
      required
    >
  </div>

<?php } ?>
</div>

<!-- video -->
<label>Video or URL (optional)</label>

<div id="edit-recipe-video-row">
  <input type="file" id="edit-recipe-video-file" name="video_file" accept="video/*">

  <div id="edit-recipe-current-video">
    <span>Current Video:</span>

    <?php if (!empty($recipe['videoFilePath'])) { ?>

        <?php if (filter_var($recipe['videoFilePath'], FILTER_VALIDATE_URL)) { ?>
            <a href="<?php echo htmlspecialchars($recipe['videoFilePath']); ?>" target="_blank">
              View Video Link
            </a>

        <?php } else { ?>
            <video width="200" controls>
              <source src="videos/<?php echo htmlspecialchars($recipe['videoFilePath']); ?>">
              Your browser does not support video.
            </video>
        <?php } ?>

    <?php } else { ?>
        No Video Uploaded
    <?php } ?>

  </div>
</div>

<input
  type="url"
  id="edit-recipe-video-url"
  name="video_url"
  placeholder="https://youtube.com/..."
  value="<?php 
    if (!empty($recipe['videoFilePath']) && filter_var($recipe['videoFilePath'], FILTER_VALIDATE_URL)) {
        echo htmlspecialchars($recipe['videoFilePath']);
    }
  ?>"
>

<!-- actions -->
      <div class="actions" id="edit-recipe-actions">
        <button type="submit" class="edit-recipe-btn-primary" id="edit-recipe-save">
          Save
        </button>
        <a href="my-recipes.php" class="edit-recipe-btn-secondary" id="cancel-edit-recipe">
          Cancel
        </a>
      </div>

</form>
 </main>
 
<div class="footer">
    <div class="wrap footer-row">
      <div class="footer-mark">☾</div>
      <div>© 2026 Ramadan Light — Nourishing bodies and souls.</div>
    </div>
  </div>
  
  <script src="js.js"></script>
  
  <script>
const videoFileInput = document.querySelector('input[name="video_file"]');
const videoURLInput = document.querySelector('input[name="video_url"]');

// disable URL if file selected
videoFileInput.addEventListener('change', () => {

    if (videoFileInput.value !== "") {

        videoURLInput.value = "";
        videoURLInput.disabled = true;

    } else {

        videoURLInput.disabled = false;
    }
});

// disable file if URL entered
videoURLInput.addEventListener('input', () => {

    if (videoURLInput.value !== "") {

        videoFileInput.value = "";
        videoFileInput.disabled = true;

    } else {

        videoFileInput.disabled = false;
    }
});
</script>
</body>
</html>