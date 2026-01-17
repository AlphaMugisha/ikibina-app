// Check for saved user preference, if any, on load of the website
const currentTheme = localStorage.getItem('theme') ? localStorage.getItem('theme') : null;

// If present, apply it
if (currentTheme) {
    document.documentElement.setAttribute('data-theme', currentTheme);
}

// Function to switch theme
const switchTheme = () => {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    let targetTheme = "light";

    if (currentTheme === "light" || !currentTheme) {
        targetTheme = "dark";
    }

    document.documentElement.setAttribute('data-theme', targetTheme);
    localStorage.setItem('theme', targetTheme);
}

// Add event listener to the button (wait for DOM to load)
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('theme-toggle');
    if(toggleBtn){
        toggleBtn.addEventListener('click', switchTheme);
    }
});
// Wait for the whole page (images, styles) to load
window.addEventListener("load", function () {
    const loader = document.getElementById("app-loader");
    if (loader) {
        // Add the class that fades it out
        loader.classList.add("loader-hidden");
        
        // Remove it from the HTML entirely after the fade finishes
        loader.addEventListener("transitionend", function () {
            document.body.removeChild(loader);
        });
    }
});
// Function to hide the loader
function hideLoader() {
    const loader = document.getElementById("loader-wrapper");
    if (loader && !loader.classList.contains("loader-hidden")) {
        loader.classList.add("loader-hidden");
        
        // Remove from DOM after fade out
        setTimeout(() => {
            if (loader.parentNode) loader.parentNode.removeChild(loader);
        }, 500);
    }
}

// 1. Normal Load: When the page is fully ready
window.addEventListener("load", hideLoader);

// 2. Safety Net: Force hide after 3 seconds (in case page is slow)
setTimeout(hideLoader, 3000);
// Function to hide the loader
function hideLoader() {
    const loader = document.getElementById("loader-wrapper");
    // Only run if the loader exists and isn't hidden yet
    if (loader && !loader.classList.contains("loader-hidden")) {
        loader.classList.add("loader-hidden");
        
        // Wait for the fade-out (0.5s) then remove it from HTML
        setTimeout(() => {
            if (loader.parentNode) loader.parentNode.removeChild(loader);
        }, 500);
    }
}

// 1. Normal: Hide when page is fully loaded
window.addEventListener("load", hideLoader);

// 2. Backup: Force hide after 2 seconds (Safe fallback)
setTimeout(hideLoader, 2000);