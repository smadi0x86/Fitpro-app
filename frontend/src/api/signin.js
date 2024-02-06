async function signInUser(email, password) {
  const url = "https://fitpro.smadi0x86.me/api/login";

  try {
    const response = await fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ email, password }),
    });

    const responseData = await response.json();
    if (response.ok) {
      console.log("Sign-in successful:", responseData);
      localStorage.setItem("jwtToken", responseData.token);
      localStorage.setItem("email", email);
      // Redirect to index.html
      window.location.href = "/";
    } else {
      console.error("Sign-in failed:", responseData);
      alert("Sign-in failed. Invalid Credentials");
    }
  } catch (error) {
    console.error("Error during sign-in:", error);
    alert("Error during sign-in.");
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("signInForm");
  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const email = document.getElementById("signInEmail").value;
    const password = document.getElementById("signInPassword").value;

    await signInUser(email, password);
  });
});
