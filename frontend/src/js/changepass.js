document
  .getElementById("changePasswordForm")
  .addEventListener("submit", function (event) {
    event.preventDefault();

    const currentPassword = document.getElementById("currentPassword").value;
    const newPassword = document.getElementById("newPassword").value;
    const confirmNewPassword =
      document.getElementById("confirmNewPassword").value;
    const messageElement = document.getElementById("passwordChangeMessage");

    if (newPassword !== confirmNewPassword) {
      messageElement.textContent = "New passwords do not match.";
      return;
    }

    const jwtToken = localStorage.getItem("jwtToken");
    const email = localStorage.getItem("email"); // Retrieve email from localStorage

    fetch("/api/change-password", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: "Bearer " + jwtToken,
      },
      body: JSON.stringify({
        email, // Send email instead of userId
        currentPassword,
        newPassword,
      }),
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(
            "Network response was not ok: " + response.statusText
          );
        }
        return response.json();
      })
      .then((data) => {
        if (data.message) {
          messageElement.textContent = "Password changed successfully.";
          // Log out the user by clearing all local storage and redirecting
          localStorage.clear();
          window.location.href = "/signin";
        } else {
          messageElement.textContent =
            "Failed to change password: " + data.error;
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        messageElement.textContent =
          "Failed to change password: " + error.message;
      });
  });
