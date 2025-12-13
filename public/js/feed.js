/**
 * Feed JavaScript - Handles interactive features on the feed page
 *
 * @package Skavoo
 */

document.addEventListener("DOMContentLoaded", function () {
  // Auto-resize textarea
  const textareas = document.querySelectorAll(".post-textarea");
  textareas.forEach((textarea) => {
    textarea.addEventListener("input", function () {
      this.style.height = "auto";
      this.style.height = Math.min(this.scrollHeight, 200) + "px";
    });
  });

  // Close dropdowns when clicking outside
  document.addEventListener("click", function (e) {
    if (!e.target.closest(".dropdown")) {
      document.querySelectorAll(".dropdown-menu").forEach((menu) => {
        menu.classList.remove("show");
      });
    }
  });

  // Auto-hide flash messages after 5 seconds
  const flashMessages = document.querySelectorAll(".flash");
  flashMessages.forEach((flash) => {
    setTimeout(() => {
      flash.style.opacity = "0";
      flash.style.transform = "translateY(-10px)";
      setTimeout(() => flash.remove(), 300);
    }, 5000);
  });
});

/**
 * Toggle comments section visibility
 * @param {number} postId
 */
function toggleComments(postId) {
  const section = document.getElementById("comments-" + postId);
  if (section) {
    const isVisible = section.style.display !== "none";
    section.style.display = isVisible ? "none" : "block";

    // Focus on comment input when opening
    if (!isVisible) {
      const input = section.querySelector(".comment-input");
      if (input) {
        input.focus();
      }
    }
  }
}

/**
 * Toggle dropdown menu
 * @param {HTMLElement} button
 */
function toggleDropdown(button) {
  event.stopPropagation();
  const menu = button.nextElementSibling;

  // Close all other dropdowns first
  document.querySelectorAll(".dropdown-menu").forEach((m) => {
    if (m !== menu) m.classList.remove("show");
  });

  menu.classList.toggle("show");
}

/**
 * Preview media before upload
 * @param {HTMLInputElement} input
 */
function previewMedia(input) {
  const preview = document.getElementById("media-preview");
  const previewImage = document.getElementById("preview-image");
  const previewVideo = document.getElementById("preview-video");

  if (input.files && input.files[0]) {
    const file = input.files[0];
    const reader = new FileReader();

    reader.onload = function (e) {
      if (file.type.startsWith("video/")) {
        previewImage.style.display = "none";
        previewVideo.src = e.target.result;
        previewVideo.style.display = "block";
      } else {
        previewVideo.style.display = "none";
        previewImage.src = e.target.result;
        previewImage.style.display = "block";
      }
      preview.style.display = "block";
    };

    reader.readAsDataURL(file);
  }
}

/**
 * Remove media preview and clear input
 */
function removeMediaPreview() {
  const preview = document.getElementById("media-preview");
  const previewImage = document.getElementById("preview-image");
  const previewVideo = document.getElementById("preview-video");
  const input = document.getElementById("media-input");

  previewImage.src = "";
  previewImage.style.display = "none";
  previewVideo.src = "";
  previewVideo.style.display = "none";
  preview.style.display = "none";
  input.value = "";
}

/**
 * Share post - Copy link to clipboard
 * @param {number} postId
 */
function sharePost(postId) {
  const url = window.location.origin + "/post/" + postId;

  if (navigator.clipboard) {
    navigator.clipboard
      .writeText(url)
      .then(() => {
        showToast("Link copied to clipboard!");
      })
      .catch(() => {
        fallbackCopyToClipboard(url);
      });
  } else {
    fallbackCopyToClipboard(url);
  }
}

/**
 * Fallback for copying to clipboard
 * @param {string} text
 */
function fallbackCopyToClipboard(text) {
  const textArea = document.createElement("textarea");
  textArea.value = text;
  textArea.style.position = "fixed";
  textArea.style.left = "-999999px";
  document.body.appendChild(textArea);
  textArea.select();

  try {
    document.execCommand("copy");
    showToast("Link copied to clipboard!");
  } catch (err) {
    showToast("Failed to copy link");
  }

  document.body.removeChild(textArea);
}

/**
 * Show a toast notification
 * @param {string} message
 */
function showToast(message) {
  // Remove existing toasts
  document.querySelectorAll(".toast").forEach((t) => t.remove());

  const toast = document.createElement("div");
  toast.className = "toast";
  toast.textContent = message;
  document.body.appendChild(toast);

  // Trigger animation
  setTimeout(() => toast.classList.add("show"), 10);

  // Remove after 3 seconds
  setTimeout(() => {
    toast.classList.remove("show");
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

/**
 * Character counter for post textarea
 */
document.addEventListener("DOMContentLoaded", function () {
  const textarea = document.querySelector(".post-textarea");
  if (textarea) {
    const maxLength = textarea.getAttribute("maxlength") || 5000;

    textarea.addEventListener("input", function () {
      const remaining = maxLength - this.value.length;

      // Show warning when approaching limit
      if (remaining < 100) {
        let counter = this.parentElement.querySelector(".char-counter");
        if (!counter) {
          counter = document.createElement("span");
          counter.className = "char-counter";
          this.parentElement.appendChild(counter);
        }
        counter.textContent = remaining + " characters remaining";
        counter.style.color = remaining < 50 ? "#dc2626" : "#f59e0b";
      } else {
        const counter = this.parentElement.querySelector(".char-counter");
        if (counter) counter.remove();
      }
    });
  }
});
