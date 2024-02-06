async function registerUser(username, email, password, confirmPassword) {
  const url = "https://fitpro.smadi0x86.me/api/register";

  try {
    const response = await fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ username, email, password, confirmPassword }),
    });

    const responseData = await response.json();
    if (response.ok) {
      console.log("Registration successful:", responseData);
      window.location.href = "email-sent.html";
    } else {
      // Display error message to the user
      console.error("Registration failed:", responseData);
      alert("Registration failed: " + responseData.error);
    }
  } catch (error) {
    console.error("Error during registration:", error);
    alert("An error occurred during registration.");
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("registrationForm");
  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const username = document.getElementById("registerUsername").value;
    const email = document.getElementById("registerEmail").value;
    const password = document.getElementById("registerPassword").value;
    const confirmPassword = document.getElementById(
      "registerConfirmPassword"
    ).value;

    await registerUser(username, email, password, confirmPassword);
  });
});
