<?php
session_start();
include("db.php");

if (!isset($_SESSION['userID']) || !isset($_SESSION['userType'])) {
    header("Location: login.php?error=" . urlencode("Please log in first."));
    exit();
}

if ($_SESSION['userType'] != 'admin') {
    header("Location: login.php?error=" . urlencode("Access denied. Admins only."));
    exit();
}

$adminID = $_SESSION['userID'];

$adminQuery = "SELECT firstName, lastName, emailAddress FROM user WHERE id = $adminID";
$adminResult = mysqli_query($conn, $adminQuery);
$admin = mysqli_fetch_assoc($adminResult);

$reportsQuery = "
    SELECT 
        report.id AS reportID,
        report.recipeID,
        recipe.name AS recipeName,
        user.firstName,
        user.lastName,
        user.photoFileName AS userPhoto
    FROM report
    JOIN recipe ON report.recipeID = recipe.id
    JOIN user ON recipe.userID = user.id
";
$reportsResult = mysqli_query($conn, $reportsQuery);

$blockedQuery = "SELECT firstName, lastName, emailAddress FROM blockeduser";
$blockedResult = mysqli_query($conn, $blockedQuery);
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Page</title>
  <link rel="stylesheet" href="css.css">
</head>

<body>

  <div class="navbar">
    <div class="wrap nav-row">
      <a class="brand" href="admin.php">
        <img src="images/log.png" alt="Ramadan Light logo" class="brand-logo">
        <div class="brand-text">
          <div class="brand-name">Ramadan Light</div>
          <div class="brand-sub">HEALTHY RECIPES</div>
        </div>
      </a>

      <div class="nav-links">
        <a class="btn btn-primary" href="signout.php">Sign out</a>
      </div>
    </div>
  </div>

  <div class="ad-container">

    <div class="ad-welcome-text">
      Welcome <?php echo $admin['firstName']; ?>
    </div>

    <div class="ad-info-box">
      <h2 class="ad-section-title">My Information</h2>
      <p>Name: <?php echo $admin['firstName'] . " " . $admin['lastName']; ?></p>
      <p>Email address: <?php echo $admin['emailAddress']; ?></p>
    </div>

    <div class="ad-reports-box">
      <h2 class="ad-section-title">Reported Recipes</h2>

      <?php if (mysqli_num_rows($reportsResult) > 0) { ?>
        <table class="ad-table">
          <tr>
            <th>Recipe Name</th>
            <th>Recipe Creator</th>
            <th>Action</th>
          </tr>

          <?php while ($report = mysqli_fetch_assoc($reportsResult)) { ?>
            <tr>
              <td>
                  <a href="viewrecipe.php?id=<?php echo $report['recipeID']; ?>" class="ad-recipe-link"> 
                  <?php echo $report['recipeName']; ?>
                </a>
              </td>

              <td>
                <div class="ad-creator">
                  <img src="images/<?php echo ($report['userPhoto'] != '' ? $report['userPhoto'] : 'avatar.png'); ?>" alt="User photo" class="ad-user-img">
                  <span><?php echo $report['firstName'] . " " . $report['lastName']; ?></span>
                </div>
              </td>

              <td>
                <form action="handle_report_action.php" method="post" class="ad-action-form">
                  <input type="hidden" name="recipeID" value="<?php echo $report['recipeID']; ?>">
                  <input type="hidden" name="reportID" value="<?php echo $report['reportID']; ?>">
                  
                  

                  <label>
                    <input type="radio" name="action" value="block" required> Block User
                  </label>

                  <label>
                    <input type="radio" name="action" value="dismiss"> Dismiss Report
                  </label>

                  <input type="submit" value="Submit">
                </form>
              </td>
            </tr>
          <?php } ?>
        </table>
      <?php } else { ?>
        <p>No reports found.</p>
      <?php } ?>
    </div>

    <div class="ad-blocked-box">
      <h2 class="ad-section-title">Blocked Users List</h2>

      <?php if (mysqli_num_rows($blockedResult) > 0) { ?>
        <table class="ad-table">
          <tr>
            <th>Name</th>
            <th>Email Address</th>
          </tr>

          <?php while ($blockedUser = mysqli_fetch_assoc($blockedResult)) { ?>
            <tr>
              <td><?php echo $blockedUser['firstName'] . " " . $blockedUser['lastName']; ?></td>
              <td><?php echo $blockedUser['emailAddress']; ?></td>
            </tr>
          <?php } ?>
        </table>
      <?php } else { ?>
        <p>No blocked users found.</p>
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