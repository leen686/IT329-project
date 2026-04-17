



const ingredientsContainer = document.getElementById("add-recipe-ingredients-container");
const stepsContainer = document.getElementById("add-recipe-steps-container");
const addIngredientBtn = document.getElementById("add-ingredient-btn");
const addStepBtn = document.getElementById("add-step-btn");

/* Add Ingredient */
addIngredientBtn.addEventListener("click", () => {
  const count = ingredientsContainer.children.length + 1;

  const row = document.createElement("div");
  row.className = "ingredient-row";

  const number = document.createElement("span");
  number.className = "ingredient-number";
  number.textContent = count + ".";

  const nameInput = document.createElement("input");
  nameInput.type = "text";
  nameInput.name = "ingredient_name[]";
  nameInput.className = "ingredient-name";
  nameInput.placeholder = "Ingredient name";
  nameInput.required = true;

  const qtyInput = document.createElement("input");
  qtyInput.type = "text";
  qtyInput.name = "ingredient_qty[]";
  qtyInput.className = "ingredient-qty";
  qtyInput.placeholder = "Quantity";
  qtyInput.required = true;

  row.appendChild(number);
  row.appendChild(nameInput);
  row.appendChild(qtyInput);

  ingredientsContainer.appendChild(row);
});

/* Add Step */
addStepBtn.addEventListener("click", () => {
  const count = stepsContainer.children.length + 1;

  const row = document.createElement("div");
  row.className = "step-row";

  const number = document.createElement("span");
  number.className = "step-number";
  number.textContent = count + ".";

  const stepInput = document.createElement("input");
  stepInput.type = "text";
  stepInput.name = "step[]";
  stepInput.className = "step-input";
  stepInput.placeholder = "Step description";
  stepInput.required = true;

  row.appendChild(number);
  row.appendChild(stepInput);

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
    window.location.href = "user.html";
  });
}