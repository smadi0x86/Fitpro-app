// Wait for the DOM to fully load before running the script
document.addEventListener("DOMContentLoaded", () => {
  // Add event listeners to the membership selection buttons
  const buttons = document.querySelectorAll(".select-membership");
  buttons.forEach((button) => {
    button.addEventListener("click", (event) => {
      event.preventDefault();
      // Retrieve the membership type from the button data attributes
      const membershipType = event.target.getAttribute("data-membership-type");
      // Call the function to create a new membership
      createMembership(membershipType);
    });
  });

  // Select buttons for updating and deleting memberships
  const updateButton = document.getElementById("update-membership");
  const deleteButton = document.getElementById("delete-membership");
  // Select the button for getting membership details
  const getDetailsButton = document.getElementById("get-membership-details");

  // Attach event listeners to the buttons for respective operations
  updateButton.addEventListener("click", updateMembership);
  deleteButton.addEventListener("click", deleteMembership);
  getDetailsButton.addEventListener("click", getMembershipDetails);

  // Commented out the immediate call to get membership details on page load
  // getMembershipDetails();
});

// Function to create a new membership on the server
function createMembership(membershipType) {
  // Retrieve the user's email from local storage
  const userEmail = localStorage.getItem("email");
  // Send a POST request to create a new membership
  fetch("https://fitpro.smadi0x86.me/api/create-membership", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${localStorage.getItem("jwtToken")}`,
    },
    body: JSON.stringify({
      email: userEmail,
      membership_type: membershipType,
      is_premium: true,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      alert("Membership created successfully.");
      // Refresh the membership details to reflect the new membership
      getMembershipDetails();
    })
    .catch((error) => console.error("Error:", error));
}

// Function to retrieve and display the current membership details
function getMembershipDetails() {
  const userEmail = localStorage.getItem("email");
  // Check if the userEmail is available before making the request
  if (!userEmail) {
    console.error("User email is not available.");
    alert("Unable to retrieve user email from local storage.");
    return;
  }

  // Send a GET request to retrieve membership details
  fetch(`https://fitpro.smadi0x86.me/api/get-membership?email=${userEmail}`, {
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${localStorage.getItem("jwtToken")}`,
    },
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      if (data) {
        // Alert the user with the membership details
        const membershipDetails =
          `Membership Type: ${data.subscription_type || "N/A"}\n` +
          `Subscription End Date: ${data.subscription_end_date || "N/A"}\n` +
          `Is Premium: ${data.is_premium ? "Yes" : "No"}`;
        alert(membershipDetails);
      } else {
        alert("No membership details found.");
      }
    })
    .catch((error) => {
      console.error("Error fetching membership details:", error);
      alert(
        "There was an error retrieving your membership details. Please try again later."
      );
    });
}

function updateMembership() {
  const userEmail = localStorage.getItem("email");
  const newMembershipType = document.getElementById(
    "new-membership-type"
  ).value;
  const newSubscriptionEndDate = document.getElementById(
    "new-subscription-end-date"
  ).value;

  fetch("https://fitpro.smadi0x86.me/api/update-membership", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${localStorage.getItem("jwtToken")}`,
    },
    body: JSON.stringify({
      email: userEmail,
      new_membership_type: newMembershipType,
      new_subscription_end_date: newSubscriptionEndDate,
      is_premium: true,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      console.log("Membership Updated:", data);
      alert("Membership updated successfully.");
      getMembershipDetails();
    })
    .catch((error) => console.error("Error:", error));
}

function deleteMembership() {
  const userEmail = localStorage.getItem("email");
  fetch("https://fitpro.smadi0x86.me/api/delete-membership", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${localStorage.getItem("jwtToken")}`,
    },
    body: JSON.stringify({ email: userEmail }),
  })
    .then((response) => response.json())
    .then((data) => {
      console.log("Membership Deleted:", data);
      alert("Membership deleted successfully.");
    })
    .catch((error) => console.error("Error:", error));
}
