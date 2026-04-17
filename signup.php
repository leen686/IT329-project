<?php
session_start();

$error = "";
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ramadan Light | Sign up</title>
  <link rel="stylesheet" href="css2.css" />
</head>
<body class="signup-page">

  <div class="navbar">
    <div class="wrap nav-row">
      <a class="brand" href="index.php">
        <img src="images/log.png" alt="Ramadan Light logo" class="brand-logo">
        <div class="brand-text">
          <div class="brand-name">Ramadan Light</div>
          <div class="brand-sub">HEALTHY RECIPES</div>
        </div>
      </a>

      <div class="nav-links">
        <a class="link" href="index.php">Home</a>
      </div>
    </div>
  </div>

  <div class="page-bg">
    <div class="wrap">

      <div class="form-card">
        <h1 class="form-title">Sign up</h1>
        <p class="form-sub">Create a new user account.</p>

        <?php if (!empty($error)) { ?>
          <p style="color: red; margin-bottom: 15px;">
            <?php echo htmlspecialchars($error); ?>
          </p>
        <?php } ?>

        <form action="signup_action.php" method="post" enctype="multipart/form-data">
          <div class="two-cols">
            <div class="field">
              <label>First name *</label>
              <input type="text" name="firstName" placeholder="Enter your first name" required />
            </div>

            <div class="field">
              <label>Last name *</label>
              <input type="text" name="lastName" placeholder="Enter your last name" required />
            </div>
          </div>

          <div class="field">
            <label>Email Address *</label>
            <div class="input-row">
              <span class="mini-icon">✉</span>
              <input type="email" name="emailAddress" placeholder="Enter your email" required />
            </div>
          </div>

          <div class="field">
            <label>Password *</label>
            <div class="input-row">
              <span class="mini-icon">🔒</span>
              <input type="password" name="password" placeholder="Enter your password" required />
            </div>
          </div>

          <div class="field">
            <label>Profile image (optional)</label>
            <input type="file" name="profileImage" accept="image/*" />
          </div>

          <button class="btn btn-primary full" type="submit">Sign up →</button>
          <a class="btn btn-outline full" href="index.php">Cancel</a>
        </form>

        <div class="divider">
          <div class="line"></div>
          <div class="divider-text">Already have an account?</div>
          <div class="line"></div>
        </div>

        <div class="center">
          <a class="link" href="login.php">Login</a>
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