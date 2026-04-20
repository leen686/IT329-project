<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("db.php");

$error = "";

if (isset($_POST['login'])) {

    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];

    // 1) check if user is blocked
    $blockedSql = "SELECT * FROM blockeduser WHERE emailAddress='$email'";
    $blockedResult = mysqli_query($conn, $blockedSql);

    if (mysqli_num_rows($blockedResult) > 0) {
        $error = "This account is blocked.";
    } else {

        // 2) get user by email
        $sql = "SELECT * FROM user WHERE emailAddress='$email'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) == 1) {

            $row = mysqli_fetch_assoc($result);

            // 3) verify hashed password
            if (password_verify($password, $row['password'])) {

                $_SESSION['userID'] = $row['id'];
                $_SESSION['userType'] = $row['userType'];
                $_SESSION['firstName'] = $row['firstName'];
                $_SESSION['emailAddress'] = $row['emailAddress'];

                if ($row['userType'] == "admin") {
                    header("Location: admin.php");
                    exit();
                } else {
                    header("Location: user.php");
                    exit();
                }

            } else {
                $error = "Invalid email or password ";
            }

        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ramadan Light | Login</title>
  <link rel="stylesheet" href="css.css" />
</head>
<body>

  <div class="navbar">
    <div class="wrap nav-row">
      <a class="brand" href="index.html">
        <img src="images/log.png" alt="Ramadan Light logo" class="brand-logo">
        <div class="brand-text">
          <div class="brand-name">Ramadan Light</div>
          <div class="brand-sub">HEALTHY RECIPES</div>
        </div>
      </a>

      <div class="nav-links">
        <a class="link" href="index.html">Home</a>
        <a class="btn btn-primary" href="signUp.html">Sign up</a>
      </div>
    </div>
  </div>

  <div class="page-bg">
    <div class="wrap">
      <div class="form-card">
        <h1 class="form-title">Login</h1>
        <p class="form-sub">Welcome back! Let’s keep it healthy today.</p>
      <!-- اضافه عشان لما ما يقدر اليوزر يدخل يطلع له رساله واضحه-->
        <?php if (isset($_GET['error']) && !empty($_GET['error'])) { ?> 
         <p style="color:red; text-align:center; margin-bottom:15px;">
          <?php echo htmlspecialchars($_GET['error']); ?>
         </p>
        <?php } ?>
        
        <?php if (!empty($error)) { ?>
          <p style="color:red; text-align:center; margin-bottom:15px;"><?php echo $error; ?></p>
        <?php } ?>

        <form method="POST" action="login.php">
          <div class="field">
            <label>Email Address</label>
            <div class="input-row">
              <span class="mini-icon">✉</span>
              <input type="email" name="email" placeholder="Enter your email" required />
            </div>
          </div>

          <div class="field">
            <label>Password</label>
            <div class="input-row">
              <span class="mini-icon">🔒</span>
              <input type="password" name="password" placeholder="Enter your password" required />
            </div>
          </div>

          <button class="btn btn-primary full" type="submit" name="login">
            Login →
          </button>
        </form>

        <div class="divider">
          <div class="line"></div>
          <div class="divider-text">New to Ramadan Light?</div>
          <div class="line"></div>
        </div>

        <div class="center">
          <a class="link" href="signUp.html">Sign up</a>
        </div>
      </div>
    </div>
  </div>

</body>
</html>