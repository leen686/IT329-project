<?php

session_start();
include 'db.php';

mysqli_set_charset($conn, "utf8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: signup.php");
    exit();
}

//* 1) Get form data */

$firstName = trim($_POST["firstName"] ?? "");
$lastName = trim($_POST["lastName"] ?? "");
$emailAddress = trim($_POST["emailAddress"] ?? "");
$userPassword = trim($_POST["password"] ?? "");

/* 2) Basic validation  (D1)*/
if (empty($firstName) || empty($lastName) || empty($emailAddress) || empty($userPassword)) {
    header("Location: signup.php?error=" . urlencode("Please fill in all required fields."));
    exit();
}

/* 3) Check if email already exists in user table  (D2)*/
$sqlUser = "SELECT id FROM `user` WHERE emailAddress = ?";
$stmtUser = mysqli_prepare($conn, $sqlUser);

if (!$stmtUser) {
    die("Prepare failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmtUser, "s", $emailAddress);
mysqli_stmt_execute($stmtUser);
$resultUser = mysqli_stmt_get_result($stmtUser);

if (mysqli_num_rows($resultUser) > 0) {
    header("Location: signup.php?error=" . urlencode("This email is already registered."));
    exit();
}

/* 4) Check if email already exists in blockeduser table  (D2) */
$sqlBlocked = "SELECT id FROM blockeduser WHERE emailAddress = ?";
$stmtBlocked = mysqli_prepare($conn, $sqlBlocked);

if (!$stmtBlocked) {
    die("Prepare failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmtBlocked, "s", $emailAddress);
mysqli_stmt_execute($stmtBlocked);
$resultBlocked = mysqli_stmt_get_result($stmtBlocked);

if (mysqli_num_rows($resultBlocked) > 0) {
    header("Location: signup.php?error=" . urlencode("This email is already registered."));
    exit();
}

/* 5) Hash password (D3) */
$hashedPassword = password_hash($userPassword, PASSWORD_DEFAULT);

/* 6) Handle profile image (D4) */
$photoFileName = "default.png";

if (isset($_FILES["profileImage"]) && $_FILES["profileImage"]["error"] === 0) {
    $uploadDir = "images/";
    $originalName = $_FILES["profileImage"]["name"];
    $tmpName = $_FILES["profileImage"]["tmp_name"];

    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExtensions = ["jpg", "jpeg", "png", "webp"];

    if (in_array($extension, $allowedExtensions)) {
        $newFileName = time() . "_" . uniqid() . "." . $extension;
        $destination = $uploadDir . $newFileName;

        if (move_uploaded_file($tmpName, $destination)) {
            $photoFileName = $newFileName;
        }
    }
}

/* 7) Insert new user */
$userType = "user";

$sqlInsert = "INSERT INTO `user` (userType, firstName, lastName, emailAddress, password, photoFileName)
              VALUES (?, ?, ?, ?, ?, ?)";

$stmtInsert = mysqli_prepare($conn, $sqlInsert);

if (!$stmtInsert) {
    die("Prepare failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmtInsert, "ssssss", $userType, $firstName, $lastName, $emailAddress, $hashedPassword, $photoFileName);

if (mysqli_stmt_execute($stmtInsert)) {
    $newUserId = mysqli_insert_id($conn);
/* (D6) */
    $_SESSION["userID"] = $newUserId;
    $_SESSION["userType"] = $userType;

    header("Location: user.php");
    exit();
} else {
    header("Location: signup.php?error=" . urlencode("Something went wrong. Please try again."));
    exit();
}


?>
