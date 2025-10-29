// Simple image preview & light client-side checks
(function () {
  function preview(inputId, imgId) {
    var input = document.getElementById(inputId);
    var img = document.getElementById(imgId);
    if (!input || !img) return;

    input.addEventListener("change", function () {
      var file = this.files && this.files[0];
      if (!file) return;

      if (file.size > 2 * 1024 * 1024) {
        alert("Image too large (max 2MB).");
        this.value = "";
        return;
      }
      var allowed = ["image/jpeg", "image/png", "image/webp"];
      if (allowed.indexOf(file.type) === -1) {
        alert("Invalid image type. Use JPEG, PNG, or WEBP.");
        this.value = "";
        return;
      }

      var reader = new FileReader();
      reader.onload = function (e) {
        img.src = e.target.result;
      };
      reader.readAsDataURL(file);
    });
  }

  // Edit Profile avatar preview
  preview("profile_picture", "avatar-preview-img");

  // Register form basic checks
  var registerForm = document.getElementById("register-form");
  if (registerForm) {
    registerForm.addEventListener("submit", function (e) {
      var name = registerForm.querySelector('input[name="full_name"]');
      var email = registerForm.querySelector('input[name="email"]');
      var pw = registerForm.querySelector('input[name="password"]');
      if (!name || !email || !pw) return;

      if (!name.value.trim() || !email.value.trim() || !pw.value.trim()) {
        e.preventDefault();
        alert("Please fill in all required fields.");
      }
    });
  }

  // Create post form basic checks
  var postForm = document.getElementById("create-post-form");
  if (postForm) {
    postForm.addEventListener("submit", function (e) {
      var content = postForm.querySelector('textarea[name="content"]');
      if (content && !content.value.trim()) {
        e.preventDefault();
        alert("Post content is required.");
      }
    });
  }
})();
