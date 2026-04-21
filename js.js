



const ingredientsContainer = document.getElementById("add-recipe-ingredients-container");
const stepsContainer = document.getElementById("add-recipe-steps-container");
const addIngredientBtn = document.getElementById("add-ingredient-btn");
const addStepBtn = document.getElementById("add-step-btn");

/* Add Ingredient */
addIngredientBtn.addEventListener("click", () => {
  const count = ingredientsContainer.children.length + 1;

  const row = document.createElement("div");
  row.className = "ingredient-row";

  row.innerHTML = `
    <span class="ingredient-number">${count}.</span>
    <input type="text" name="ingredient_name[]" class="ingredient-name" placeholder="Ingredient name" required>
    <input type="text" name="ingredient_qty[]" class="ingredient-qty" placeholder="Quantity" required>
  `;

  ingredientsContainer.appendChild(row);
});

/* Add Step */
addStepBtn.addEventListener("click", () => {
  const count = stepsContainer.children.length + 1;

  const row = document.createElement("div");
  row.className = "step-row";

  row.innerHTML = `
    <span class="step-number">${count}.</span>
    <input type="text" name="step[]" class="step-input" placeholder="Step description" required>
  `;

  stepsContainer.appendChild(row);
});
























// Signup: preview image + redirect to user page (default avatar if none)
var signupForm = document.getElementById("signupForm");
var profileImage = document.getElementById("profileImage");
var avatarPreview = document.getElementById("avatarPreview");

if (profileImage && avatarPreview) {
  profileImage.addEventListener("change", function () {
    if (profileImage.files && profileImage.files[0]) {
      avatarPreview.src = URL.createObjectURL(profileImage.files[0]);
    } else {
      avatarPreview.src = "images/default-avatar.png";
    }
  });
}

if (signupForm) {
  signupForm.addEventListener("submit", function (e) {
    e.preventDefault();
    // Phase 1: no validation logic, just go to user page after submit
    window.location.href = "user.php";
  });
}