// Login: redirect based on which button was pressed (user/admin)
var loginForm = document.getElementById("loginForm");
if (loginForm) {
  loginForm.addEventListener("submit", function (e) {
    e.preventDefault();

    var role = e.submitter.getAttribute("data-role");
    if (role === "admin") {
      window.location.href = "admin.html";
    } else {
      window.location.href = "user.html";
    }
  });
}

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