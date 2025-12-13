/**
 * Enhanced search functionality with autocomplete dropdown.
 * Features: profile pictures, keyboard navigation, debouncing, loading states.
 */

document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.querySelector(".search-input");
  const form = document.querySelector(".search-form");

  if (!searchInput || !form) return;

  let selectedIndex = -1;
  let results = [];
  let debounceTimer = null;

  // Create dropdown container
  const dropdown = document.createElement("div");
  dropdown.className = "search-dropdown";
  dropdown.style.cssText = `
    position: absolute;
    left: 0;
    top: 100%;
    width: 100%;
    background-color: #fff;
    margin-top: 4px;
    border-radius: 4px;
    border: 2px solid #7f9db9;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    max-height: 320px;
    overflow-y: auto;
    z-index: 1000;
    font-family: Tahoma, sans-serif;
    font-size: 13px;
    display: none;
  `;
  form.style.position = "relative";
  form.appendChild(dropdown);

  // Prevent form submission on Enter key
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    // If an item is selected, navigate to it
    if (selectedIndex >= 0 && results[selectedIndex]) {
      window.location.href = `/user/profile/${results[selectedIndex].uuid}`;
    }
  });

  // Keyboard navigation
  searchInput.addEventListener("keydown", (e) => {
    const items = dropdown.querySelectorAll(".search-result-item");
    
    if (e.key === "ArrowDown") {
      e.preventDefault();
      selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
      updateSelection(items);
    } else if (e.key === "ArrowUp") {
      e.preventDefault();
      selectedIndex = Math.max(selectedIndex - 1, -1);
      updateSelection(items);
    } else if (e.key === "Enter") {
      e.preventDefault();
      if (selectedIndex >= 0 && results[selectedIndex]) {
        window.location.href = `/user/profile/${results[selectedIndex].uuid}`;
      }
    } else if (e.key === "Escape") {
      hideDropdown();
      searchInput.blur();
    }
  });

  function updateSelection(items) {
    items.forEach((item, index) => {
      if (index === selectedIndex) {
        item.classList.add("selected");
        item.scrollIntoView({ block: "nearest" });
      } else {
        item.classList.remove("selected");
      }
    });
  }

  // Debounced search
  searchInput.addEventListener("input", (e) => {
    const query = e.target.value.trim();
    
    clearTimeout(debounceTimer);
    
    if (query.length === 0) {
      hideDropdown();
      return;
    }

    if (query.length < 2) {
      showMessage("Type at least 2 characters...");
      return;
    }

    showLoading();
    
    debounceTimer = setTimeout(() => {
      fetchResults(query);
    }, 300);
  });

  async function fetchResults(query) {
    try {
      const response = await fetch(`/search/lookup?q=${encodeURIComponent(query)}`);
      results = await response.json();
      
      selectedIndex = -1;
      renderResults(query);
    } catch (error) {
      console.error("Error fetching search results:", error);
      showMessage("Error loading results. Please try again.");
    }
  }

  function renderResults(query) {
    dropdown.innerHTML = "";

    if (results.length === 0) {
      showMessage("No users found matching your search.");
      return;
    }

    results.forEach((user, index) => {
      const item = document.createElement("div");
      item.className = "search-result-item";
      item.style.cssText = `
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        cursor: pointer;
        border-bottom: 1px solid #e8e8e8;
        transition: background-color 0.15s;
      `;

      // Profile picture
      const avatar = document.createElement("img");
      const profilePic = user.profile_picture 
        ? `/uploads/avatars/${user.profile_picture}` 
        : "/images/default-avatar.png";
      avatar.src = profilePic;
      avatar.alt = user.full_name;
      avatar.style.cssText = `
        width: 36px;
        height: 36px;
        border-radius: 4px;
        border: 2px solid #7f9db9;
        object-fit: cover;
      `;
      avatar.onerror = () => { avatar.src = "/images/default-avatar.png"; };

      // User info container
      const info = document.createElement("div");
      info.style.cssText = "flex: 1; min-width: 0;";

      // Display name or full name with highlighted match
      const displayName = user.display_name || user.full_name;
      const name = document.createElement("div");
      name.innerHTML = highlightMatch(displayName, query);
      name.style.cssText = `
        font-weight: bold;
        color: #333;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      `;

      // Show full name if display name is different, otherwise show email
      const subtext = document.createElement("div");
      if (user.display_name && user.display_name !== user.full_name) {
        subtext.innerHTML = highlightMatch(user.full_name, query) + " · " + highlightMatch(user.email, query);
      } else {
        subtext.innerHTML = highlightMatch(user.email, query);
      }
      subtext.style.cssText = `
        font-size: 11px;
        color: #666;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      `;

      info.appendChild(name);
      info.appendChild(subtext);

      // View profile indicator
      const arrow = document.createElement("span");
      arrow.textContent = "→";
      arrow.style.cssText = `
        color: #999;
        font-size: 14px;
        opacity: 0;
        transition: opacity 0.15s;
      `;

      item.appendChild(avatar);
      item.appendChild(info);
      item.appendChild(arrow);

      item.addEventListener("mouseenter", () => {
        item.style.backgroundColor = "#f0f4f8";
        arrow.style.opacity = "1";
        selectedIndex = index;
        updateSelection(dropdown.querySelectorAll(".search-result-item"));
      });

      item.addEventListener("mouseleave", () => {
        item.style.backgroundColor = "";
        arrow.style.opacity = "0";
      });

      item.addEventListener("click", () => {
        window.location.href = `/user/profile/${user.uuid}`;
      });

      dropdown.appendChild(item);
    });

    // Remove border from last item
    const lastItem = dropdown.lastElementChild;
    if (lastItem) {
      lastItem.style.borderBottom = "none";
    }

    showDropdown();
  }

  function highlightMatch(text, query) {
    if (!query) return escapeHtml(text);
    
    const regex = new RegExp(`(${escapeRegex(query)})`, "gi");
    return escapeHtml(text).replace(regex, '<mark style="background: #fff3cd; padding: 0 2px; border-radius: 2px;">$1</mark>');
  }

  function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  function escapeRegex(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  }

  function showLoading() {
    dropdown.innerHTML = `
      <div style="padding: 15px; text-align: center; color: #666;">
        <span style="display: inline-block; animation: pulse 1s infinite;">��</span>
        Searching...
      </div>
    `;
    showDropdown();
  }

  function showMessage(message) {
    dropdown.innerHTML = `
      <div style="padding: 15px; text-align: center; color: #666;">
        ${message}
      </div>
    `;
    showDropdown();
  }

  function showDropdown() {
    dropdown.style.display = "block";
  }

  function hideDropdown() {
    dropdown.style.display = "none";
    selectedIndex = -1;
    results = [];
  }

  // Hide dropdown when clicking outside
  document.addEventListener("click", (e) => {
    if (!form.contains(e.target)) {
      hideDropdown();
    }
  });

  // Focus search on Ctrl+K or Cmd+K
  document.addEventListener("keydown", (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === "k") {
      e.preventDefault();
      searchInput.focus();
      searchInput.select();
    }
  });
});

// Add CSS animation for loading
const style = document.createElement("style");
style.textContent = `
  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
  }
  .search-result-item.selected {
    background-color: #f0f4f8 !important;
  }
  .search-result-item.selected span:last-child {
    opacity: 1 !important;
  }
`;
document.head.appendChild(style);
