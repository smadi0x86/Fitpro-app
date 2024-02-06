const createSignInOrProfileSection = (isSignedIn, userProfilePic) => {
  return isSignedIn
    ? `
      <li class="nav-item dropdown ms-auto">
        <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class='bx bxs-user fs-4'></i>
          <i class='bx bx-chevron-down fs-4' ></i>
        </a>
        <ul class="dropdown-menu" aria-labelledby="profileDropdown" style="left: -90px;">
          <li><a class="dropdown-item" href="profile.html">My Profile</a></li>
          <li><button class="dropdown-item" onclick="signOut()">Sign Out</button></li>
        </ul>
      </li>`
    : `
       <!-- Sign In Button for Guests -->
      <li class="nav-item ms-auto">
        <button class="btn btn-info sign-in-btn" type="button">Sign In</button>
      </li>`;
};

const navbar = (isSignedIn, userProfilePic) => {
  const signInOrProfileHtml = createSignInOrProfileSection(
    isSignedIn,
    userProfilePic
  );

  return `
    <nav class="navbar navbar-expand-lg bg-light">
      <div class="container-fluid">
        <a class="navbar-brand" href="/">
          <img src="./assets/logo-9c511b38.png" alt="Logo" width="70" height="30">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
          aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav mx-auto">
            <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="company.html">Company</a></li>
            <li class="nav-item"><a class="nav-link" href="services.html">Services</a></li>
            <li class="nav-item"><a class="nav-link" href="shop.html">Shop</a></li>
            <li class="nav-item"><a class="nav-link" href="membership.html">Membership</a></li>
            ${signInOrProfileHtml}
          </ul>
        </div>
      </div>
    </nav>
  `;
};

// Event listener for Sign In button
document.addEventListener("DOMContentLoaded", () => {
  const signInButton = document.querySelector(".sign-in-btn");
  if (signInButton) {
    signInButton.addEventListener("click", () => {
      document.location.href = "signin.html";
    });
  }
});

export default navbar;
