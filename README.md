# Ramadan Light — Healthy Recipes for Ramadan

Ramadan Light is a recipe-sharing web application that helps people keep their Ramadan meals light, balanced and healthy. Users share and discover recipes for **Iftar**, **Suhoor** and **healthy snacks**, interact with each other's recipes, and an admin keeps the community safe by reviewing reported content.

The project was developed by a student team for the **IT329** course at **King Saud University**.

**Live demo:** https://leen686.github.io/RamadanLight-Demo/ — try it in your browser with sample data and one-click user and admin access ([demo source](https://github.com/leen686/RamadanLight-Demo)).

![Home page](https://raw.githubusercontent.com/leen686/RamadanLight-Demo/main/docs/index.jpg)

## Features

### Visitors
- Landing page introducing the three recipe categories
- Sign up with an optional profile photo, and log in

### Users
- Personal dashboard with profile details, number of recipes and total likes
- Browse all recipes and filter them by category without reloading the page (AJAX)
- View a recipe with its photo, creator, description, ingredients, steps, video and comments
- Like, favourite, report and comment on other users' recipes
- Manage saved favourites
- My Recipes: add, edit and delete recipes, with a photo and an optional video file or video link

### Admin
- View all reported recipes with their creators
- Dismiss a report, or block the recipe's creator — blocking removes the user and their recipes and prevents that email from logging in or signing up again
- View the list of blocked users

![User dashboard](https://raw.githubusercontent.com/leen686/RamadanLight-Demo/main/docs/user.jpg)

![Recipe page](https://raw.githubusercontent.com/leen686/RamadanLight-Demo/main/docs/view-other.jpg)

## Tech stack

- **Back end:** PHP with sessions and role-based access (user / admin)
- **Database:** MySQL
- **Front end:** HTML, CSS and JavaScript
- **Interactions:** jQuery and AJAX for filtering, likes, favourites, reports, deleting recipes and admin actions
- **Security:** passwords stored with `password_hash()` and checked with `password_verify()`; output escaped with `htmlspecialchars()`

## Pages

| Page | Purpose |
|---|---|
| `index.php` | Landing page |
| `signup.php` / `signup_action.php` | Create an account |
| `login.php` / `signout.php` | Log in and out; users and admins are sent to their own pages |
| `user.php` | User dashboard: profile, stats, recipes by category, favourites |
| `viewrecipe.php` | Recipe details, likes, favourites, reports and comments |
| `my-recipes.php` | The user's own recipes |
| `add-recipe.php` / `edit-recipe.php` / `delete-recipe.php` | Create, update and delete recipes |
| `admin.php` / `handle_report_action.php` | Admin dashboard and report handling |
| `filter_recipes.php`, `add_like.php`, `add_favourite.php`, `remove_favourite.php`, `add_report.php`, `add_comment.php` | Endpoints used by the pages |

## Database

The MySQL database (`recipedb`) contains these tables:

| Table | Holds |
|---|---|
| `user` | Users and admins (name, email, hashed password, photo, user type) |
| `blockeduser` | Blocked users' names and emails |
| `recipecategory` | Recipe categories (Iftar, Suhoor, Snacks) |
| `recipe` | Recipes (creator, category, name, description, photo, video) |
| `ingredients` | Ingredient names and quantities for each recipe |
| `instructions` | Ordered steps for each recipe |
| `comment` | Comments with their date |
| `likes`, `favourites`, `report` | Likes, saved recipes and reports by each user |

## Run locally

1. Install a local PHP and MySQL environment such as XAMPP or MAMP.
2. Put the project folder in the server's web directory (for example `htdocs`).
3. Create a MySQL database named `recipedb` with the tables above.
4. Update the connection settings in `db.php` to match your local MySQL server.
5. Open `http://localhost/<project-folder>/index.php` in your browser.

## Team

- Leen Almutairi
- Ranem Ibrahim
- Jana Alshehri
- Jana Aldakheel
- Latifah Alsaif
