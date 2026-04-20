<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// بدء الجلسة عشان نخزن بيانات المستخدم بعد تسجيل الدخول
session_start();

// ربط الصفحة بقاعدة البيانات
include("db.php");

// متغير لعرض رسالة الخطأ إذا فشل تسجيل الدخول
$error = "";

if (isset($_POST['login'])) {

    // أخذ الإيميل والباسورد من الفورم
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];

    // الحالة 1: التحقق إذا المستخدم موجود في جدول blockeduser
    $blockedSql = "SELECT * FROM blockeduser WHERE emailAddress='$email'";
    $blockedResult = mysqli_query($conn, $blockedSql);

    if (mysqli_num_rows($blockedResult) > 0) {
        // إذا الإيميل موجود في blockeduser نمنع تسجيل الدخول
        $error = "This account is blocked.";
    } else {

        // الحالة 2: البحث عن المستخدم في جدول user باستخدام الإيميل
        $sql = "SELECT * FROM user WHERE emailAddress='$email'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) == 1) {

            // جلب بيانات المستخدم من قاعدة البيانات
            $row = mysqli_fetch_assoc($result);

            // الحالة 3: التحقق من الباسورد المشفر باستخدام password_verify
            if (password_verify($password, $row['password'])) {

                // إذا كان تسجيل الدخول صحيح نخزن البيانات في session
                $_SESSION['userID'] = $row['id'];
                $_SESSION['userType'] = $row['userType'];
                $_SESSION['firstName'] = $row['firstName'];
                $_SESSION['emailAddress'] = $row['emailAddress'];

                // الحالة 4: إذا كان المستخدم أدمن نوجهه إلى صفحة الأدمن
                if ($row['userType'] == "admin") {
                    header("Location: admin.php");
                    exit();
                } else {
                    // وإذا كان مستخدم عادي نوجهه إلى صفحة المستخدم
                    header("Location: user.php");
                    exit();
                }

            } else {
                // إذا الباسورد غلط تظهر رسالة خطأ موحدة
                $error = "Invalid email or password.";
            }

        } else {
            // إذا الإيميل غير موجود تظهر نفس رسالة الخطأ الموحدة
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

        <!-- هذه الرسالة تظهر إذا رجعنا للوجن من صفحة ثانية ومعنا error في الرابط -->
        <?php if (isset($_GET['error']) && !empty($_GET['error'])) { ?> 
         <p style="color:red; text-align:center; margin-bottom:15px;">
          <?php echo htmlspecialchars($_GET['error']); ?>
         </p>
        <?php } ?>
        
        <!-- هذه الرسالة تظهر إذا فشل تسجيل الدخول داخل نفس الصفحة -->
        <?php if (!empty($error)) { ?>
          <p style="color:red; text-align:center; margin-bottom:15px;"><?php echo $error; ?></p>
        <?php } ?>

        <!-- فورم تسجيل الدخول -->
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

          <!-- زر تسجيل الدخول -->
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