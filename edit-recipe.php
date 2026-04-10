<?php
session_start();
include("db.php");

if (!isset($_SESSION['userID']) || $_SESSION['userType'] != "user") {
    header("Location: login.php");
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

$recipe = mysqli_fetch_assoc($recipe);

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

    $photoName = $recipe['photoFileName'];

    if (!empty($_FILES['photo']['name'])) {
        $newPhoto = time() . "_" . $_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], "images/" . $newPhoto);
        $photoName = $newPhoto;
    }

    $videoPath = $recipe['videoFilePath'];

    if (!empty($_FILES['video_file']['name'])) {
        $videoName = time() . "_" . $_FILES['video_file']['name'];
        move_uploaded_file($_FILES['video_file']['tmp_name'], "videos/" . $videoName);
        $videoPath = $videoName;
    } elseif (!empty($_POST['video_url'])) {
        $videoPath = mysqli_real_escape_string($conn, $_POST['video_url']);
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
  <title>Edit Recipe</title>
</head>

<body id="edit-recipe-page">

<h1 id="edit-recipe-title">Edit Recipe</h1>

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
  <input type="file" id="edit-recipe-photo" name="photo" accept="image/*" required>

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
<?php while ($ing = mysqli_fetch_assoc($ingredientsResult)) { ?>
<input type="text" name="ingredient_name[]" value="<?php echo $ing['ingredientName']; ?>" required>
<input type="text" name="ingredient_qty[]" value="<?php echo $ing['ingredientQuantity']; ?>" required><br>
<?php } ?>

<!-- steps -->
<h3>Steps *</h3>
<?php while ($step = mysqli_fetch_assoc($stepsResult)) { ?>
<input type="text" name="step[]" value="<?php echo $step['step']; ?>" required><br>
<?php } ?>

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
>

<!-- actions -->
<button type="submit">Save</button>

</form>

</body>
</html>