/**
 * Fetches user profiles from the server and displays them as suggestions.
 * Matches the visual style of the notifications dropdown.
 */

document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.querySelector(".search-input");
  const form = document.querySelector(".search-form");

  // Prevent form submission on Enter key
  form.addEventListener("submit", (e) => {
    e.preventDefault();
  });

  // Prevent Enter key from triggering form submission while typing
  searchInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
    }
  });

  /**
   * Creates and appends the dropdown container to the form.
   * Initially hidden with no border, styled to match the notification dropdown.
   */
  const dropdown = document.createElement("div");
  dropdown.style.position = "absolute";
  dropdown.style.left = searchInput.offsetLeft + "px";
  dropdown.style.top = searchInput.offsetTop + searchInput.offsetHeight + "px";
  dropdown.style.width = searchInput.offsetWidth + "px";
  dropdown.style.backgroundColor = "#fff";
  dropdown.style.marginTop = "10px";
  dropdown.style.borderRadius = "6px";
  dropdown.style.border = "none"; // Border hidden by default
  dropdown.style.boxShadow = "0 4px 6px rgba(0,0,0,0.2)";
  dropdown.style.maxHeight = "200px";
  dropdown.style.overflowY = "auto";
  dropdown.style.zIndex = 1000;
  dropdown.style.fontFamily = "Tahoma, sans-serif";
  dropdown.style.fontSize = "13px";
  dropdown.style.display = "none"; // Hidden initially

  form.appendChild(dropdown);

  /**
   * Listens for input in the search field and fetches matching users.
   * Populates and displays the dropdown with results.
   */
  searchInput.addEventListener("input", async (e) => {
    const query = e.target.value.trim();

    if (query.length === 0) {
      dropdown.style.display = "none";
      dropdown.style.border = "none"; // Remove border when hidden
      return;
    }

    try {
      const response = await fetch(
        `/search/lookup?q=${encodeURIComponent(query)}`
      );
      const results = await response.json();

      dropdown.innerHTML = "";

      if (results.length === 0) {
        const noResult = document.createElement("div");
        noResult.textContent = "No results found.";
        noResult.style.padding = "10px";
        noResult.style.cursor = "default";
        dropdown.appendChild(noResult);
      } else {
        results.forEach((user) => {
          const item = document.createElement("div");
          item.textContent = `${user.full_name} (${user.email})`;
          item.style.padding = "10px";
          item.style.cursor = "pointer";
          item.style.borderBottom = "1px solid #f0f0f0";

          item.addEventListener("mouseover", () => {
            item.style.backgroundColor = "#FAFAF9";
          });

          item.addEventListener("mouseout", () => {
            item.style.backgroundColor = "#fff";
          });

          item.addEventListener("click", () => {
            window.location.href = `/user/profile/${user.uuid}`;
          });

          dropdown.appendChild(item);
        });
      }

      dropdown.style.display = "block";
      dropdown.style.border = "2px solid #7f9db9"; // Show border when visible
    } catch (error) {
      console.error("Error fetching search results:", error);
      dropdown.style.display = "none";
      dropdown.style.border = "none"; // Ensure border removed on failure
    }
  });

  /**
   * Hides the dropdown if a click occurs outside the form.
   */
  document.addEventListener("click", (e) => {
    if (!form.contains(e.target)) {
      dropdown.style.display = "none";
      dropdown.style.border = "none"; // Remove border when hiding
    }
  });

  /**
   * Recalculates the dropdown's position and width on window resize
   * to keep it aligned with the search input.
   */
  window.addEventListener("resize", () => {
    dropdown.style.left = searchInput.offsetLeft + "px";
    dropdown.style.top =
      searchInput.offsetTop + searchInput.offsetHeight + "px";
    dropdown.style.width = searchInput.offsetWidth + "px";
  });
});
