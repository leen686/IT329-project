const statusEl = document.getElementById("status");
const msgEl = document.getElementById("msg");
const nameEl = document.getElementById("name");
const listEl = document.getElementById("list");

const btnHello = document.getElementById("btnHello");
const btnToggle = document.getElementById("btnToggle");
const btnReset = document.getElementById("btnReset");

// اختبار أن الـ JS اشتغلp
statusEl.textContent = "JS شغال ✅";

btnHello.addEventListener("click", () => {
  const name = nameEl.value.trim();
  msgEl.textContent = name ? هلا : { name };
  ("اكتب اسمك أول 😅");
});

btnToggle.addEventListener("click", () => {
  document.body.classList.toggle("dark");
});

btnReset.addEventListener("click", () => {
  nameEl.value = "";
  msgEl.textContent = "تم الريست ✅";
  listEl.innerHTML = "";
});

// تعبئة قائمة تلقائيًا (اختبار إضافي)
["Home", "Login", "Signup", "My Recipes", "Add Recipe"].forEach((item) => {
  const li = document.createElement("li");
  li.textContent = item;
  listEl.appendChild(li);
});
