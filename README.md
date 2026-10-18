# Ramadan Light 🌙

**A healthy recipe-sharing web application for Ramadan — Iftar meals, Suhoor ideas and light snacks.**

![PHP](https://img.shields.io/badge/PHP-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?logo=javascript&logoColor=black)
![jQuery](https://img.shields.io/badge/jQuery%20%26%20AJAX-0769AD?logo=jquery&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?logo=css3&logoColor=white)

### [▶ Try the live demo](https://leen686.github.io/RamadanLight-Demo/)

One-click access as a user or an admin, with sample data — no sign-up needed.

![Ramadan Light home page](https://raw.githubusercontent.com/leen686/RamadanLight-Demo/main/docs/index.jpg)

## Overview

During Ramadan, many people look for meals that are nourishing but not heavy. Ramadan Light gives them one place to share and discover healthy recipes, organised into **Iftar**, **Suhoor** and **Snacks**. Users interact with each other's recipes through likes, favourites and comments, and an admin moderates the community by reviewing reported content.

Developed by a five-member team for the **Advanced Web Technologies (IT329)** course at **King Saud University**, 2026.

## Highlights

- **Full-stack web application:** PHP back end, MySQL database, responsive HTML/CSS front end
- **Two roles with separate experiences:** users and admins, with session-based access control
- **Smooth interactions with AJAX:** category filtering, likes, favourites, reports, deleting recipes and admin actions update instantly, without reloading the page
- **Rich recipe content:** photos, ingredients, ordered steps, and a video file or video link
- **Community moderation:** reporting, dismissing reports, and blocking users

## Features

| Users | Admin |
|---|---|
| Sign up with an optional profile photo, and log in | Dashboard of all reported recipes and their creators |
| Personal dashboard with profile, recipe count and total likes | Dismiss a report |
| Browse recipes and filter them by category | Block a recipe's creator, which removes their account and recipes |
| View recipe details, ingredients, steps, video and comments | Blocked emails can no longer log in or sign up |
| Like, favourite, comment on and report recipes | View the list of blocked users |
| Add, edit and delete their own recipes | |

![User dashboard](https://raw.githubusercontent.com/leen686/RamadanLight-Demo/main/docs/user.jpg)

![Recipe page](https://raw.githubusercontent.com/leen686/RamadanLight-Demo/main/docs/view-other.jpg)

## Tech stack

| Layer | Technologies |
|---|---|
| Front end | HTML5, CSS3, JavaScript, jQuery |
| Back end | PHP (sessions, file uploads, AJAX endpoints) |
| Database | MySQL |
| Tools | NetBeans, Git and GitHub |

<details>
<summary><b>Project structure</b></summary>

| File | Purpose |
|---|---|
| `index.php` | Landing page |
| `signup.php`, `signup_action.php` | Account creation |
| `login.php`, `signout.php` | Login and logout; users and admins are redirected to their own pages |
| `user.php` | User dashboard |
| `viewrecipe.php` | Recipe details, likes, favourites, reports and comments |
| `my-recipes.php` | The user's own recipes |
| `add-recipe.php`, `edit-recipe.php`, `delete-recipe.php` | Recipe management |
| `admin.php`, `handle_report_action.php` | Admin dashboard and report handling |
| `filter_recipes.php`, `add_like.php`, `add_favourite.php`, `remove_favourite.php`, `add_report.php`, `add_comment.php` | AJAX and form endpoints |
| `db.php` | Database connection |
| `css.css`, `css2.css`, `js.js` | Styles and scripts |
| `images/`, `videos/` | Uploaded photos and videos |

</details>

<details>
<summary><b>Run it locally</b></summary>

1. Install a local PHP and MySQL environment such as XAMPP or MAMP.
2. Copy the project folder into the server's web directory (for example `htdocs`).
3. Create the database by running the SQL below in phpMyAdmin or the MySQL console.
4. Update the connection settings in `db.php` to match your MySQL server.
5. Open `http://localhost/<project-folder>/index.php`.
6. Sign up, then make your account an admin if needed:
   `UPDATE user SET userType = 'admin' WHERE emailAddress = 'your@email.com';`

```sql
CREATE DATABASE recipedb;
USE recipedb;

CREATE TABLE user (
  id INT AUTO_INCREMENT PRIMARY KEY,
  userType VARCHAR(10) NOT NULL DEFAULT 'user',
  firstName VARCHAR(50) NOT NULL,
  lastName VARCHAR(50) NOT NULL,
  emailAddress VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  photoFileName VARCHAR(255)
);

CREATE TABLE blockeduser (
  id INT AUTO_INCREMENT PRIMARY KEY,
  firstName VARCHAR(50),
  lastName VARCHAR(50),
  emailAddress VARCHAR(100)
);

CREATE TABLE recipecategory (
  id INT AUTO_INCREMENT PRIMARY KEY,
  categoryName VARCHAR(50) NOT NULL
);
INSERT INTO recipecategory (categoryName) VALUES ('Iftar'), ('Suhoor'), ('Snacks');

CREATE TABLE recipe (
  id INT AUTO_INCREMENT PRIMARY KEY,
  userID INT NOT NULL,
  categoryID INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  photoFileName VARCHAR(255),
  videoFilePath VARCHAR(255)
);

CREATE TABLE ingredients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  recipeID INT NOT NULL,
  ingredientName VARCHAR(100) NOT NULL,
  ingredientQuantity VARCHAR(50)
);

CREATE TABLE instructions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  recipeID INT NOT NULL,
  step TEXT NOT NULL,
  stepOrder INT NOT NULL
);

CREATE TABLE comment (
  id INT AUTO_INCREMENT PRIMARY KEY,
  recipeID INT NOT NULL,
  userID INT NOT NULL,
  comment TEXT NOT NULL,
  date DATE
);

CREATE TABLE likes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  userID INT NOT NULL,
  recipeID INT NOT NULL
);

CREATE TABLE favourites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  userID INT NOT NULL,
  recipeID INT NOT NULL
);

CREATE TABLE report (
  id INT AUTO_INCREMENT PRIMARY KEY,
  userID INT NOT NULL,
  recipeID INT NOT NULL
);
```

</details>

## Team

Developed by:

- Leen Almutairi
- Ranem Ibrahim
- Jana Alshehri
- Jana Aldakheel
- Latifah Alsaif

*Advanced Web Technologies (IT329) — King Saud University, 2026*
