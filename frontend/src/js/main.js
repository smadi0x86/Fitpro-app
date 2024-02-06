import "../scss/styles.scss";
import * as bootstrap from "bootstrap";
import navbar from "./navbar";
import footer from "./footer";

const jwtToken = localStorage.getItem("jwtToken");
const userProfilePic = jwtToken ? "../assets/images/profile.png" : "";

// Render the navbar with user sign-in status
const navbarElement = document.getElementById("navbar");
if (navbarElement) {
  navbarElement.innerHTML = navbar(!!jwtToken, userProfilePic);
}

// Render the footer
const footerElement = document.getElementById("footer");
if (footerElement) {
  footerElement.innerHTML = footer;
}

// Initialize Bootstrap components like tooltips
document.addEventListener("DOMContentLoaded", () => {
  const tooltips = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  tooltips.map((tooltip) => new bootstrap.Tooltip(tooltip));
});

// Define the signOut function in the global scope to be callable from HTML
window.signOut = function () {
  localStorage.removeItem("jwtToken");
  localStorage.removeItem("email");
  window.location.href = "/";
};
