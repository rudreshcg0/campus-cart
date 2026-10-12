/**
 * Campus Cart - Global Utility Scripts (main.js)
 */

document.addEventListener("DOMContentLoaded", function () {
  // 1. Live image preview for Sell page
  const imageUrlInput = document.getElementById("productImageUrl");
  const imagePreviewContainer = document.getElementById("imagePreviewBox");

  if (imageUrlInput && imagePreviewContainer) {
    function updatePreview() {
      const url = imageUrlInput.value.trim();
      if (url) {
        imagePreviewContainer.innerHTML = `<img src="${url}" alt="Preview" onerror="this.onerror=null; this.parentElement.innerHTML='Invalid image URL';">`;
      } else {
        imagePreviewContainer.innerHTML = '<span>Image preview will appear here</span>';
      }
    }

    imageUrlInput.addEventListener("input", updatePreview);
    if (imageUrlInput.value) {
      updatePreview();
    }
  }

  // 2. Auto-dismiss alerts after 5 seconds with fade
  const alerts = document.querySelectorAll(".cc-alert");
  if (alerts.length > 0) {
    setTimeout(function () {
      alerts.forEach(function (alert) {
        alert.style.transition = "opacity 0.4s ease, transform 0.4s ease";
        alert.style.opacity = "0";
        alert.style.transform = "translateY(-5px)";
        setTimeout(function () {
          alert.remove();
        }, 400);
      });
    }, 4500);
  }

  // 3. Smooth scroll strictly for internal #categories anchor links (e.g. from navbar)
  const categoryHashLinks = document.querySelectorAll('a[href="#categories"]');
  categoryHashLinks.forEach(function (link) {
    link.addEventListener("click", function (e) {
      const target = document.getElementById("categories");
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: "smooth", block: "start" });
      }
    });
  });
});
